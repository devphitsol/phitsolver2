<?php

namespace App\Controllers;

// Use the User model with proper namespace
use App\Models\User;

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Display user management page
     */
    public function index()
    {
        // Get filter parameters from request
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $role = isset($_GET['role']) ? trim($_GET['role']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $sortBy = isset($_GET['sortBy']) ? trim($_GET['sortBy']) : 'created_at';
        $sortOrder = isset($_GET['sortOrder']) ? (int)$_GET['sortOrder'] : -1;
        
        // Validate parameters
        $page = max(1, $page);
        $limit = max(1, min(100, $limit)); // Limit between 1 and 100
        $sortOrder = in_array($sortOrder, [-1, 1]) ? $sortOrder : -1;
        
        // Get users with filters and pagination
        $filterOptions = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $userResult = $this->userModel->getUsersWithFilters($filterOptions);
        $users = $userResult['users'];
        
        // Get statistics
        $userCount = $this->userModel->getCount();
        $activeUserCount = $this->userModel->getActiveCount();
        $adminUserCount = $this->userModel->getAdminCount();
        $maxUsers = $this->userModel->getMaxUsers();
        $employeeCount = $this->userModel->getEmployeeCount();
        $businessUserCount = $this->userModel->getBusinessUserCount();
        $pendingCount = $this->userModel->getPendingCount();
        
        // Get available filter options
        $availableRoles = $this->userModel->getAvailableRoles();
        $availableStatuses = $this->userModel->getAvailableStatuses();
        
        // Pass pagination data to view
        $pagination = [
            'currentPage' => $userResult['currentPage'],
            'totalPages' => $userResult['totalPages'],
            'totalCount' => $userResult['totalCount'],
            'limit' => $userResult['limit'],
            'hasNextPage' => $userResult['hasNextPage'],
            'hasPrevPage' => $userResult['hasPrevPage']
        ];
        
        include __DIR__ . '/../../admin/views/users/index.php';
    }

    /**
     * Show create user form
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateUserData($_POST);
            
            if (empty($data['errors'])) {
                try {
                    $userId = $this->userModel->create($data['data']);
                    if ($userId) {
                        $_SESSION['success'] = 'User created successfully!';
                        header('Location: index.php?action=users');
                        exit;
                    } else {
                        $_SESSION['error'] = 'Failed to create user.';
                    }
                } catch (\Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
            }
            
            if (!empty($data['errors'])) {
                $_SESSION['error'] = implode(', ', $data['errors']);
            }
        }
        
        // Get user count for the form
        $userCount = $this->userModel->getCount();
        $maxUsers = $this->userModel->getMaxUsers();
        
        ob_start();
        include __DIR__ . '/../../admin/views/users/create-content.php';
        $pageContent = ob_get_clean();
        include __DIR__ . '/../../admin/views/layout.php';
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: index.php?action=users');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateUserData($_POST, true); // true for update mode
            
            if (empty($data['errors'])) {
                try {
                    if ($this->userModel->update($id, $data['data'])) {
                        $_SESSION['success'] = 'User updated successfully!';
                        header('Location: index.php?action=users');
                        exit;
                    } else {
                        $_SESSION['error'] = 'Failed to update user.';
                    }
                } catch (\Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
            }
            
            if (!empty($data['errors'])) {
                $_SESSION['error'] = implode(', ', $data['errors']);
            }
        }
        
        ob_start();
        include __DIR__ . '/../../admin/views/users/edit-content.php';
        $pageContent = ob_get_clean();
        include __DIR__ . '/../../admin/views/layout.php';
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: index.php?action=users');
            exit;
        }

        // Prevent deleting the current admin user
        if (isset($_SESSION['admin_user_id']) && $_SESSION['admin_user_id'] == $id) {
            $_SESSION['error'] = 'You cannot delete your own account.';
            header('Location: index.php?action=users');
            exit;
        }

        if ($this->userModel->delete($id)) {
            $_SESSION['success'] = 'User deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete user.';
        }
        
        header('Location: index.php?action=users');
        exit;
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: index.php?action=users');
            exit;
        }

        // Prevent deactivating the current admin user
        if (isset($_SESSION['admin_user_id']) && $_SESSION['admin_user_id'] == $id) {
            $_SESSION['error'] = 'You cannot deactivate your own account.';
            header('Location: index.php?action=users');
            exit;
        }

        if ($this->userModel->toggleStatus($id)) {
            $_SESSION['success'] = 'User status updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update user status.';
        }
        
        header('Location: index.php?action=users');
        exit;
    }

    /**
     * Approve business customer account
     */
    public function approve($id)
    {
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: index.php?action=users');
            exit;
        }

        // Only allow approval of pending business customers
        if ($user['role'] !== 'business' || $user['status'] !== 'pending') {
            $_SESSION['error'] = 'Only pending business customer accounts can be approved.';
            header('Location: index.php?action=users');
            exit;
        }

        if ($this->userModel->approveBusinessAccount($id)) {
            // Log the approval action
            error_log("Business account approved: User ID {$id} by admin " . ($_SESSION['admin_username'] ?? 'unknown'));
            
            // Get user details for notification
            $approvedUser = $this->userModel->getById($id);
            if ($approvedUser) {
                $_SESSION['success'] = "Business customer account approved successfully! {$approvedUser['name']} can now access the Partners Portal.";
            } else {
                $_SESSION['success'] = 'Business customer account approved successfully!';
            }
        } else {
            $_SESSION['error'] = 'Failed to approve business customer account.';
        }
        
        header('Location: index.php?action=users');
        exit;
    }

    /**
     * Get all users for layout
     */
    public function getUsers()
    {
        return $this->userModel->getAll();
    }

    /**
     * Get user count for layout
     */
    public function getUserCount()
    {
        return $this->userModel->getCount();
    }

    /**
     * Get business user count for layout
     */
    public function getBusinessCount()
    {
        return $this->userModel->getBusinessUserCount();
    }

    /**
     * Get all business users for layout
     */
    public function getBusinessUsers()
    {
        return $this->userModel->getBusinessUsers();
    }

    /**
     * Get pending user count for layout
     */
    public function getPendingCount()
    {
        return $this->userModel->getPendingCount();
    }

    /**
     * Get max users allowed for layout
     */
    public function getMaxUsers()
    {
        return $this->userModel->getMaxUsers();
    }

    /**
     * Get user by ID for layout
     */
    public function getUserById($id)
    {
        return $this->userModel->getById($id);
    }

    /**
     * Authenticate user login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Username and password are required.';
            } else {
                $user = $this->userModel->authenticate($username, $password);
                
                if ($user) {
                    // Handle both MongoDB ObjectId and MySQL integer ID
                    $userId = isset($user['id']) ? (string)$user['id'] : (string)$user['_id'];
                    
                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user_id'] = $userId;
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_full_name'] = $user['name'];
                    $_SESSION['admin_role'] = $user['role'];
                    
                    // Set login success message
                    $_SESSION['success'] = 'Welcome back, ' . htmlspecialchars($user['name']) . '! You have successfully logged in to the admin dashboard.';
                    
                    // Debug: Log the success message
                    error_log('Login success message set: ' . $_SESSION['success']);
                    
                    // Check if password change is required
                    $passwordMiddleware = new \App\Utils\PasswordChangeMiddleware();
                    if (!$passwordMiddleware->checkPasswordChangeRequired($userId, 'index.php')) {
                        // User was redirected to password change page
                        exit;
                    }
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $_SESSION['error'] = 'Invalid username or password.';
                    
                    // Log failed login attempt
                    $auditLogger = new \App\Utils\AuditLogger();
                    $auditLogger->logFailedLogin($username, 'Invalid credentials');
                }
            }
        }
        
        include __DIR__ . '/../../admin/views/login.php';
    }

    /**
     * Logout user
     */
    public function logout()
    {
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }

    /**
     * Validate user data
     */
    public function validateUserData($data, $isUpdate = false)
    {
        $errors = [];
        $validatedData = [];

        // Validate username
        if (empty($data['username'])) {
            $errors[] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } else {
            $validatedData['username'] = trim($data['username']);
        }

        // Validate email
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } else {
            $validatedData['email'] = trim($data['email']);
        }

        // Validate password (only required for new users)
        if (!$isUpdate || !empty($data['password'])) {
            if (empty($data['password'])) {
                $errors[] = 'Password is required';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            } else {
                $validatedData['password'] = $data['password'];
            }
        }

        // Validate full name
        if (empty($data['name'])) {
            $errors[] = 'Full name is required';
        } else {
            $validatedData['name'] = trim($data['name']);
        }

        // Optional fields
        $validatedData['role'] = $data['role'] ?? 'employee';
        $validatedData['status'] = $data['status'] ?? 'active';
        
        // Additional optional fields
        if (!empty($data['phone'])) {
            $validatedData['phone'] = trim($data['phone']);
        }
        if (!empty($data['company'])) {
            $validatedData['company'] = trim($data['company']);
        }
        if (!empty($data['address'])) {
            $validatedData['address'] = trim($data['address']);
        }

        return [
            'data' => $validatedData,
            'errors' => $errors
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        return [
            'total_users' => $this->userModel->getCount(),
            'active_users' => $this->userModel->getActiveCount(),
            'admin_users' => $this->userModel->getAdminCount(),
            'max_users' => $this->userModel->getMaxUsers()
        ];
    }

    public function updateUserDocuments($data) {
        try {
            $userId = $data['user_id'] ?? null;
            
            if (!$userId) {
                return ['success' => false, 'message' => 'User ID is required'];
            }
            
            // Prepare documents data
            $documents = [
                'company_profile' => isset($data['company_profile']) && $data['company_profile'] === '1',
                'business_permit' => isset($data['business_permit']) && $data['business_permit'] === '1',
                'bir_2303' => isset($data['bir_2303']) && $data['bir_2303'] === '1',
                'gis' => isset($data['gis']) && $data['gis'] === '1',
                'audited_financial' => isset($data['audited_financial']) && $data['audited_financial'] === '1',
                'proof_of_payment' => isset($data['proof_of_payment']) && $data['proof_of_payment'] === '1',
                'valid_id' => isset($data['valid_id']) && $data['valid_id'] === '1',
                'corporate_secretary' => isset($data['corporate_secretary']) && $data['corporate_secretary'] === '1',
                'credit_investigation' => isset($data['credit_investigation']) && $data['credit_investigation'] === '1',
                'peza_certification' => isset($data['peza_certification']) && $data['peza_certification'] === '1'
            ];
            
            // Update documents in database
            $result = $this->userModel->updateUserDocuments($userId, $documents);
            
            if ($result) {
                return ['success' => true, 'message' => 'Documents updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update documents'];
            }
        } catch (\Exception $e) {
            error_log("Error updating user documents: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating documents: ' . $e->getMessage()];
        }
    }

    /**
     * Update company profile information
     */
    public function updateCompanyProfile($data) {
        try {
            $userId = $data['user_id'] ?? null;
            
            if (!$userId) {
                return ['success' => false, 'message' => 'User ID is required'];
            }
            
            // Prepare company profile data
            $profileData = [
                'company_name' => $data['company_name'] ?? '',
                'company_address' => $data['company_address'] ?? '',
                'date_of_incorporation' => $data['date_of_incorporation'] ?? '',
                'tin_number' => $data['tin_number'] ?? '',
                'business_permit' => $data['business_permit'] ?? '',
                'email' => $data['email_address'] ?? '',
                'contact_number' => $data['contact_number'] ?? '',
                'website_url' => $data['website_url'] ?? ''
            ];
            
            // Update company profile in database
            $result = $this->userModel->updateProfile($userId, $profileData);
            
            if ($result) {
                return ['success' => true, 'message' => 'Company profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update company profile'];
            }
        } catch (\Exception $e) {
            error_log("Error updating company profile: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating company profile: ' . $e->getMessage()];
        }
    }

    /**
     * Update contact persons information
     */
    public function updateContactPersons($data) {
        try {
            $userId = $data['user_id'] ?? null;
            
            if (!$userId) {
                return ['success' => false, 'message' => 'User ID is required'];
            }
            
            // Prepare contact persons data
            $contactData = [
                'name' => $data['authorized_representative'] ?? '',
                'position' => $data['position_title'] ?? '',
                'contact_number' => $data['representative_contact'] ?? '',
                'email' => $data['representative_email'] ?? '',
                'secondary_contact_name' => $data['secondary_contact_name'] ?? '',
                'secondary_contact_position' => $data['secondary_contact_position'] ?? '',
                'secondary_contact_number' => $data['secondary_contact_number'] ?? '',
                'secondary_contact_email' => $data['secondary_contact_email'] ?? ''
            ];
            
            // Update contact persons in database
            $result = $this->userModel->updateProfile($userId, $contactData);
            
            if ($result) {
                return ['success' => true, 'message' => 'Contact information updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update contact information'];
            }
        } catch (\Exception $e) {
            error_log("Error updating contact persons: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating contact information: ' . $e->getMessage()];
        }
    }

    /**
     * Export users to CSV
     */
    public function export()
    {
        try {
            // Get filter parameters from request
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $role = isset($_GET['role']) ? trim($_GET['role']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            $sortBy = isset($_GET['sortBy']) ? trim($_GET['sortBy']) : 'created_at';
            $sortOrder = isset($_GET['sortOrder']) ? (int)$_GET['sortOrder'] : -1;
            
            // Get all users with filters (no pagination for export)
            $filterOptions = [
                'page' => 1,
                'limit' => 10000, // Large limit to get all users
                'search' => $search,
                'role' => $role,
                'status' => $status,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder
            ];
            
            $userResult = $this->userModel->getUsersWithFilters($filterOptions);
            $users = $userResult['users'];
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Create CSV output
            $output = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($output, [
                'ID', 'Name', 'Email', 'Username', 'Role', 'Status', 
                'Last Login', 'Created At', 'Updated At'
            ]);
            
            // Add user data
            foreach ($users as $user) {
                $lastLogin = $user['last_login'] ? 
                    (is_object($user['last_login']) ? $user['last_login']->toDateTime()->format('Y-m-d H:i:s') : $user['last_login']) : 
                    '';
                
                $createdAt = $user['created_at'] ? 
                    (is_object($user['created_at']) ? $user['created_at']->toDateTime()->format('Y-m-d H:i:s') : $user['created_at']) : 
                    '';
                
                $updatedAt = $user['updated_at'] ? 
                    (is_object($user['updated_at']) ? $user['updated_at']->toDateTime()->format('Y-m-d H:i:s') : $user['updated_at']) : 
                    '';
                
                fputcsv($output, [
                    (string) $user['_id'],
                    $user['name'] ?? 'N/A',
                    $user['email'],
                    $user['username'],
                    $user['role'],
                    $user['status'],
                    $lastLogin,
                    $createdAt,
                    $updatedAt
                ]);
            }
            
            fclose($output);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to export users: ' . $e->getMessage();
            header('Location: index.php?action=users');
            exit;
        }
    }

    /**
     * Change user password
     */
    public function changePassword()
    {
        header('Content-Type: application/json');
        
        try {
            // Check if user is logged in
            if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ]);
                return;
            }
            
            // Get POST data
            $currentPassword = $_POST['currentPassword'] ?? '';
            $newPassword = $_POST['newPassword'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
            
            // Validate input
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'All password fields are required'
                ]);
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                echo json_encode([
                    'success' => false,
                    'message' => 'New password and confirmation do not match'
                ]);
                return;
            }
            
            if (strlen($newPassword) < 8) {
                echo json_encode([
                    'success' => false,
                    'message' => 'New password must be at least 8 characters long'
                ]);
                return;
            }
            
            // Get current user ID
            $userId = $_SESSION['admin_user_id'] ?? null;
            if (!$userId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User session not found'
                ]);
                return;
            }
            
            // Verify current password and update password
            $result = $this->userModel->changePassword($userId, $currentPassword, $newPassword);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Password changed successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while changing password'
            ]);
        }
    }

    /**
     * API endpoint to get users with filters and pagination
     */
    public function getUsersAPI()
    {
        header('Content-Type: application/json');
        
        try {
            // Get filter parameters from request
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $role = isset($_GET['role']) ? trim($_GET['role']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            $sortBy = isset($_GET['sortBy']) ? trim($_GET['sortBy']) : 'created_at';
            $sortOrder = isset($_GET['sortOrder']) ? (int)$_GET['sortOrder'] : -1;
            
            // Validate parameters
            $page = max(1, $page);
            $limit = max(1, min(100, $limit));
            $sortOrder = in_array($sortOrder, [-1, 1]) ? $sortOrder : -1;
            
            // Get users for API response
            $filterOptions = [
                'page' => $page,
                'limit' => $limit,
                'search' => $search,
                'role' => $role,
                'status' => $status,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder
            ];
            
            $result = $this->userModel->getUsersForAPI($filterOptions);
            
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
} 