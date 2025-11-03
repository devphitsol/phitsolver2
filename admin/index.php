<?php
// Add custom PHP include path
ini_set("include_path", '/home/qiimy7odbu3s/php:' . ini_get("include_path"));

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// 공유 설정 로드
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

use App\Controllers\UserController;
use App\Controllers\BlogController;
use App\Controllers\ProductController;
use App\Controllers\PurchaseController;

// Environment variables are loaded by the Database class



// Simple authentication check
$action = $_GET['action'] ?? '';
if (!isset($_SESSION['admin_logged_in']) && $action !== 'login') {
    header('Location: index.php?action=login');
    exit;
}

// Check password change requirements for logged-in users
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] && $action !== 'login') {
    $userId = $_SESSION['admin_user_id'] ?? null;
    if ($userId) {
        $passwordMiddleware = new \App\Utils\PasswordChangeMiddleware();
        if (!$passwordMiddleware->checkPasswordChangeRequired($userId, 'index.php')) {
            // User was redirected to password change page
            exit;
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php?action=login');
    exit;
}

// Helper function for image upload
function handleImageUpload($file) {
    $uploadDir = __DIR__ . '/public/uploads/blog/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);

    // Validate file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Please upload JPG, PNG, GIF, or WebP images only.'];
    }

    // Validate file size (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'File size must be less than 5MB.'];
    }

    $filename = uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to upload image.'];
    }
}

// Handle login
if ($action === 'login') {
    $userController = new UserController();
    $userController->login();
    exit;
}

// Route handling
$method = $_GET['method'] ?? 'index';
$id = $_GET['id'] ?? null;

// Set current action for sidebar highlighting
$currentAction = $action ?: 'dashboard';

try {
    switch ($action) {
        case 'dashboard':
            $pageTitle = 'Dashboard';
            ob_start();
            include 'views/dashboard-content.php';
            $pageContent = ob_get_clean();
            include 'views/layout.php';
            break;
            
            
        case 'blog':
            require_once 'controllers/UnifiedBlogController.php';
            $controller = new UnifiedBlogController();
            
            switch ($method) {
                case 'index':
                    $pageTitle = 'Blog Management';
                    $data = $controller->index();
                    $posts = $data['posts'];
                    $totalPosts = $data['totalPosts'];
                    $publishedPosts = $data['publishedPosts'];
                    $draftPosts = $data['draftPosts'];
                    ob_start();
                    include 'views/blog/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                case 'create':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $data = [
                            'title' => $_POST['title'] ?? '',
                            'content' => $_POST['content'] ?? '',
                            'excerpt' => $_POST['excerpt'] ?? '',
                            'category' => $_POST['category'] ?? 'Uncategorized',
                            'tags' => !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [],
                            'status' => $_POST['status'] ?? 'draft'
                        ];
                        
                        // Handle file upload
                        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                            $uploadResult = handleImageUpload($_FILES['featured_image']);
                            if ($uploadResult['success']) {
                                $data['featured_image'] = $uploadResult['filename'];
                            }
                        }
                        
                        $result = $controller->create($data);
                        if ($result['success']) {
                            $_SESSION['success'] = $result['message'];
                            header('Location: index.php?action=blog');
                            exit;
                        } else {
                            $_SESSION['error'] = $result['message'];
                        }
                    }
                    
                    $pageTitle = 'Create Blog Post';
                    $categories = $controller->getCategories();
                    ob_start();
                    include 'views/blog/create-content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                case 'edit':
                    if ($id) {
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $data = [
                                'title' => $_POST['title'] ?? '',
                                'content' => $_POST['content'] ?? '',
                                'excerpt' => $_POST['excerpt'] ?? '',
                                'category' => $_POST['category'] ?? 'Uncategorized',
                                'tags' => !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [],
                                'status' => $_POST['status'] ?? 'draft'
                            ];
                            
                            // Handle file upload
                            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                                $uploadResult = handleImageUpload($_FILES['featured_image']);
                                if ($uploadResult['success']) {
                                    $data['featured_image'] = $uploadResult['filename'];
                                }
                            }
                            
                            $result = $controller->update($id, $data);
                            if ($result['success']) {
                                $_SESSION['success'] = $result['message'];
                                header('Location: index.php?action=blog');
                                exit;
                            } else {
                                $_SESSION['error'] = $result['message'];
                            }
                        }
                        
                        $pageTitle = 'Edit Blog Post';
                        $post = $controller->getById($id);
                        $categories = $controller->getCategories();
                        if (!$post) {
                            $_SESSION['error'] = 'Blog post not found.';
                            header('Location: index.php?action=blog');
                            exit;
                        }
                        ob_start();
                        include 'views/blog/edit-content.php';
                        $pageContent = ob_get_clean();
                        include 'views/layout.php';
                    } else {
                        header('Location: index.php?action=blog');
                    }
                    break;
                    
                case 'delete':
                    if ($id) {
                        $controller->delete($id);
                    } else {
                        header('Location: index.php?action=blog');
                    }
                    break;
                    
                case 'toggle-status':
                    if ($id) {
                        $controller->toggleStatus($id);
                    } else {
                        header('Location: index.php?action=blog');
                    }
                    break;
                    
                default:
                    $pageTitle = 'Blog Management';
                    $data = $controller->index();
                    $posts = $data['posts'];
                    $totalPosts = $data['totalPosts'];
                    $publishedPosts = $data['publishedPosts'];
                    $draftPosts = $data['draftPosts'];
                    ob_start();
                    include 'views/blog/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
            }
            break;
            
        case 'users':
            $controller = new UserController();
            
            switch ($method) {
                case 'index':
                    $pageTitle = 'User Management';
                    $users = $controller->getUsers();
                    $userCount = $controller->getUserCount();
                    $businessCount = $controller->getBusinessCount();
                    $pendingCount = $controller->getPendingCount();
                    ob_start();
                    include 'views/users/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                case 'create':
                    $pageTitle = 'Create User';
                    $controller->create();
                    break;
                    
                case 'edit':
                    if ($id) {
                        $pageTitle = 'Edit User';
                        $controller->edit($id);
                    } else {
                        header('Location: index.php?action=users');
                    }
                    break;
                    
                case 'delete':
                    if ($id) {
                        $controller->delete($id);
                    } else {
                        header('Location: index.php?action=users');
                    }
                    break;
                    
                case 'toggle-status':
                    if ($id) {
                        $controller->toggleStatus($id);
                    } else {
                        header('Location: index.php?action=users');
                    }
                    break;
                    
                case 'approve':
                    if ($id) {
                        $controller->approve($id);
                    } else {
                        header('Location: index.php?action=users');
                    }
                    break;
                    
                case 'change-password':
                    $controller->changePassword();
                    break;
                    
                default:
                    $pageTitle = 'User Management';
                    $users = $controller->getUsers();
                    $userCount = $controller->getUserCount();
                    $businessCount = $controller->getBusinessCount();
                    $pendingCount = $controller->getPendingCount();
                    ob_start();
                    include 'views/users/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
            }
            break;
            
        case 'company':
            $pageTitle = 'Company Management';
            
            // Check if method parameter exists
            $method = $_GET['method'] ?? 'list';
            
                         if ($method === 'edit' && isset($_GET['id'])) {
                 // Load company edit page
                 ob_start();
                 include 'views/company/company.php';
                 $pageContent = ob_get_clean();
                 include 'views/layout.php';
            } elseif ($method === 'update_documents' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // Handle documents update
                $userController = new UserController();
                $result = $userController->updateUserDocuments($_POST);
                
                // Return JSON response
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            } elseif ($method === 'update_company_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // Handle company profile update
                $userController = new UserController();
                $result = $userController->updateCompanyProfile($_POST);
                
                // Return JSON response
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            } elseif ($method === 'update_contact_persons' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // Handle contact persons update
                $userController = new UserController();
                $result = $userController->updateContactPersons($_POST);
                
                // Return JSON response
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            } else {
                // Load company list page
                ob_start();
                include 'views/company/company-detail.php';
                $pageContent = ob_get_clean();
                include 'views/layout.php';
            }
            break;
            
        case 'products':
            $controller = new ProductController();
            
            // Debug logging
            error_log("Admin index.php - products action, method: $method, company_id: " . ($_GET['company_id'] ?? 'not set'));
            
            switch ($method) {
                case 'index':
                    $pageTitle = 'Product Management';
                    $data = $controller->index();
                    $products = $data['products'];
                    $totalProducts = $data['totalProducts'];
                    $activeProducts = $data['activeProducts'];
                    $inactiveProducts = $data['inactiveProducts'];
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $search = $data['search'];
                    $categories = $data['categories'];
                    $companyId = $data['companyId'];
                    ob_start();
                    include 'views/products/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                case 'create':
                    $pageTitle = 'Create Product';
                    $data = $controller->create();
                    $categories = $data['categories'];
                    ob_start();
                    include 'views/products/create-content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                case 'store':
                    $controller->store();
                    break;
                    
                case 'edit':
                    if ($id) {
                        $pageTitle = 'Edit Product';
                        $data = $controller->edit($id);
                        $product = $data['product'];
                        $categories = $data['categories'];
                        ob_start();
                        include 'views/products/edit-content.php';
                        $pageContent = ob_get_clean();
                        include 'views/layout.php';
                    } else {
                        header('Location: index.php?action=products');
                    }
                    break;
                    
                case 'update':
                    if ($id) {
                        $controller->update($id);
                    } else {
                        header('Location: index.php?action=products');
                    }
                    break;
                    
                case 'delete':
                    if ($id) {
                        $controller->delete($id);
                    } else {
                        header('Location: index.php?action=products');
                    }
                    break;
                    
                case 'toggle-status':
                    if ($id) {
                        $controller->toggleStatus($id);
                    } else {
                        header('Location: index.php?action=products');
                    }
                    break;
                    
                case 'search':
                    $pageTitle = 'Search Products';
                    $data = $controller->search();
                    $products = $data['products'];
                    $searchQuery = $data['searchQuery'];
                    $totalResults = $data['totalResults'];
                    $categories = $data['categories'];
                    ob_start();
                    include 'views/products/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                default:
                    $pageTitle = 'Product Management';
                    $data = $controller->index();
                    $products = $data['products'];
                    $totalProducts = $data['totalProducts'];
                    $activeProducts = $data['activeProducts'];
                    $inactiveProducts = $data['inactiveProducts'];
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $search = $data['search'];
                    $categories = $data['categories'];
                    $companyId = $data['companyId'];
                    ob_start();
                    include 'views/products/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
            }
            break;
            
        case 'support-messages':
            $supportMessageModel = new \App\Models\SupportMessage();
            
            switch ($method) {
                case 'index':
                    $pageTitle = 'Support Messages';
                    $messages = $supportMessageModel->getAll();
                    $pendingCount = $supportMessageModel->getPendingCount();
                    $repliedCount = $supportMessageModel->getRepliedCount();
                    $totalCount = $supportMessageModel->getCount();
                    ob_start();
                    include 'views/support-messages/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                case 'show':
                    if ($id) {
                        $pageTitle = 'View Support Message';
                        $message = $supportMessageModel->getById($id);
                        if ($message) {
                            ob_start();
                            include 'views/support-messages/view-content.php';
                            $pageContent = ob_get_clean();
                            include 'views/layout.php';
                        } else {
                            header('Location: index.php?action=support-messages');
                        }
                    } else {
                        header('Location: index.php?action=support-messages');
                    }
                    break;
                    
                case 'reply':
                    if ($id) {
                        // Handle POST request for reply submission
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $reply = trim($_POST['reply'] ?? '');
                            
                            if (empty($reply)) {
                                $_SESSION['error'] = 'Reply message is required';
                            } else {
                                try {
                                    $supportMessageModel->addAdminReply($id, $reply);
                                    $_SESSION['success'] = 'Reply sent successfully. User has been notified via email.';
                                } catch (\Exception $e) {
                                    error_log('Admin reply error: ' . $e->getMessage());
                                    $_SESSION['error'] = 'An error occurred while sending the reply. Please try again.';
                                }
                            }
                            header('Location: index.php?action=support-messages');
                            exit;
                        }
                        
                        // Handle GET request to show reply form
                        $pageTitle = 'Reply to Support Message';
                        $message = $supportMessageModel->getById($id);
                        if ($message) {
                            ob_start();
                            include 'views/support-messages/reply-content.php';
                            $pageContent = ob_get_clean();
                            include 'views/layout.php';
                        } else {
                            header('Location: index.php?action=support-messages');
                        }
                    } else {
                        header('Location: index.php?action=support-messages');
                    }
                    break;
                    
                case 'delete':
                    if ($id) {
                        try {
                            $supportMessageModel->delete($id);
                            $_SESSION['success'] = 'Support message deleted successfully';
                        } catch (\Exception $e) {
                            $_SESSION['error'] = 'Failed to delete message';
                        }
                    }
                    header('Location: index.php?action=support-messages');
                    break;
                    
                default:
                    $pageTitle = 'Support Messages';
                    $messages = $supportMessageModel->getAll();
                    $pendingCount = $supportMessageModel->getPendingCount();
                    $repliedCount = $supportMessageModel->getRepliedCount();
                    $totalCount = $supportMessageModel->getCount();
                    ob_start();
                    include 'views/support-messages/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
            }
            break;
            
        case 'purchases':
            $controller = new PurchaseController();
            
            switch ($method) {
                case 'index':
                    $pageTitle = 'Purchase Management';
                    $data = $controller->index();
                    $purchases = $data['purchases'];
                    $totalPurchases = $data['totalPurchases'];
                    $pendingPurchases = $data['pendingPurchases'];
                    $completedPurchases = $data['completedPurchases'];
                    $cancelledPurchases = $data['cancelledPurchases'];
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $search = $data['search'];
                    $companyId = $data['companyId'];
                    $productId = $data['productId'];
                    $paymentMethods = $data['paymentMethods'];
                    $purchaseStatuses = $data['purchaseStatuses'];
                    ob_start();
                    include 'views/purchases/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                case 'create':
                    $pageTitle = 'Create Purchase Order';
                    $data = $controller->create();
                    $products = $data['products'];
                    $companyId = $data['companyId'];
                    $productId = $data['productId'];
                    $paymentMethods = $data['paymentMethods'];
                    $purchaseStatuses = $data['purchaseStatuses'];
                    ob_start();
                    include 'views/purchases/create-content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                case 'store':
                    $controller->store();
                    break;
                    
                case 'edit':
                    if ($id) {
                        $pageTitle = 'Edit Purchase Order';
                        $data = $controller->edit($id);
                        $purchase = $data['purchase'];
                        $products = $data['products'];
                        $paymentMethods = $data['paymentMethods'];
                        $purchaseStatuses = $data['purchaseStatuses'];
                        ob_start();
                        include 'views/purchases/edit-content.php';
                        $pageContent = ob_get_clean();
                        include 'views/layout.php';
                    } else {
                        header('Location: index.php?action=purchases');
                    }
                    break;
                    
                case 'view':
                    if ($id) {
                        $pageTitle = 'View Purchase Order';
                        $data = $controller->view($id);
                        $purchase = $data['purchase'];
                        $companyInfo = $data['companyInfo'];
                        ob_start();
                        include 'views/purchases/view-content.php';
                        $pageContent = ob_get_clean();
                        include 'views/layout.php';
                    } else {
                        header('Location: index.php?action=purchases');
                    }
                    break;
                    
                case 'update':
                    if ($id) {
                        $controller->update($id);
                    } else {
                        header('Location: index.php?action=purchases');
                    }
                    break;
                    
                case 'delete':
                    if ($id) {
                        $controller->delete($id);
                    } else {
                        header('Location: index.php?action=purchases');
                    }
                    break;
                    
                case 'search':
                    $pageTitle = 'Search Purchase Orders';
                    $data = $controller->search();
                    $purchases = $data['purchases'];
                    $searchQuery = $data['searchQuery'];
                    $totalResults = $data['totalResults'];
                    $companyId = $data['companyId'];
                    ob_start();
                    include 'views/purchases/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
                    
                default:
                    $pageTitle = 'Purchase Management';
                    $data = $controller->index();
                    $purchases = $data['purchases'];
                    $totalPurchases = $data['totalPurchases'];
                    $pendingPurchases = $data['pendingPurchases'];
                    $completedPurchases = $data['completedPurchases'];
                    $cancelledPurchases = $data['cancelledPurchases'];
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $search = $data['search'];
                    $companyId = $data['companyId'];
                    $productId = $data['productId'];
                    $paymentMethods = $data['paymentMethods'];
                    $purchaseStatuses = $data['purchaseStatuses'];
                    ob_start();
                    include 'views/purchases/content.php';
                    $pageContent = ob_get_clean();
                    include 'views/layout.php';
                    break;
            }
            break;
            

            
        default:
            include 'views/dashboard.php';
            break;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}
?> 