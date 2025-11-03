<?php
require_once '../config/session.php';

// Check if user is logged in and is a business customer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'business') {
    header('Location: login.php');
    exit;
}

require_once '../vendor/autoload.php';
use App\Models\User;
use App\Models\Product;

// Helper function to get web-accessible URL
function getWebUrl($imageName) {
    if (empty($imageName)) {
        
        return null;
    }
    
    
    
    // Check if it's already a full URL
    if (filter_var($imageName, FILTER_VALIDATE_URL)) {
        
        return $imageName;
    }
    
    // Remove 'public/uploads/products/' prefix if it exists
    $cleanImageName = $imageName;
    if (strpos($imageName, 'public/uploads/products/') === 0) {
        $cleanImageName = substr($imageName, strlen('public/uploads/products/'));
        error_log("getWebUrl: Removed prefix, clean name: " . $cleanImageName);
    }
    
    // Check if file exists in uploads directory
    $uploadsDir = __DIR__ . '/../admin/public/uploads/products/';
    $absolutePath = $uploadsDir . $cleanImageName;
    
    error_log("getWebUrl: Checking file existence at: " . $absolutePath);
    
    if (file_exists($absolutePath)) {
        $webUrl = '../admin/public/uploads/products/' . $cleanImageName;
        error_log("getWebUrl: File exists, returning web URL: " . $webUrl);
        return $webUrl;
    }
    
    // If file doesn't exist, still return the web path (browser will show 404)
    $webUrl = '../admin/public/uploads/products/' . $cleanImageName;
    error_log("getWebUrl: File not found, returning web URL anyway: " . $webUrl);
    return $webUrl;
}

// Helper function to get correct image URL
function getProductImageUrl($product) {
    // Debug: Log product data for troubleshooting
    error_log("Product data for image URL: " . json_encode([
        'name' => $product['name'] ?? 'N/A',
        'main_image' => $product['main_image'] ?? 'N/A',
        'images' => $product['images'] ?? 'N/A',
        'image_url' => $product['image_url'] ?? 'N/A',
        'image' => $product['image'] ?? 'N/A'
    ]));
    
    // Priority 1: Check for main_image first (new main image selection feature)
    if (!empty($product['main_image'])) {
        error_log("Processing main_image: " . $product['main_image']);
        $webUrl = getWebUrl($product['main_image']);
        error_log("Main image web URL: " . $webUrl);
        return $webUrl;
    }
    
    // Priority 2: Check for images array
    if (!empty($product['images']) && is_array($product['images']) && count($product['images']) > 0) {
        $imageName = $product['images'][0];
        error_log("Processing image from images array: " . $imageName);
        $webUrl = getWebUrl($imageName);
        error_log("Images array web URL: " . $webUrl);
        return $webUrl;
    }
    
    // Priority 3: Check for image_url
    if (!empty($product['image_url'])) {
        error_log("Processing image_url: " . $product['image_url']);
        $webUrl = getWebUrl($product['image_url']);
        error_log("Image URL web URL: " . $webUrl);
        return $webUrl;
    }
    
    // Priority 4: Check for single image field
    if (!empty($product['image'])) {
        error_log("Processing single image field: " . $product['image']);
        $webUrl = getWebUrl($product['image']);
        error_log("Single image web URL: " . $webUrl);
        return $webUrl;
    }
    
    error_log("No image found for product: " . ($product['name'] ?? 'Unknown'));
    // Return a default placeholder image
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xMDAgNzBDMTE2LjU2OSA3MCAxMzAgODMuNDMxIDMwIDEwMEMxMzAgMTE2LjU2OSAxMTYuNTY5IDEzMCAxMDAgMTMwQzgzLjQzMSAxMzAgNzAgMTE2LjU2OSA3MCAxMEM3MCA4My40MzEgODMuNDMxIDcwIDEwMCA3MFoiIGZpbGw9IiNEMUQ1REIiLz4KPHBhdGggZD0iTTEwMCAxMTBDMTA1LjUyMyAxMTAgMTEwIDEwNS41MjMgMTEwIDEwMEMxMTAgOTQuNDc3IDEwNS41MjMgOTAgMTAwIDkwQzk0LjQ3NyA5MCA5MCA5NC40NzcgOTAgMTAwQzkwIDEwNS41MjMgOTQuNDc3IDExMCAxMDAgMTEwWiIgZmlsbD0iI0E1QjVCRCIvPgo8L3N2Zz4K';
}

// Get user information
$userId = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->getById($userId);

if (!$user) {
    header('Location: login.php');
    exit;
}

// 계정 ?�보 추출
$company = $user['company'] ?? '-';
$contact = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$email = $user['email'] ?? '-';
$status = ucfirst($user['status'] ?? 'Pending');
$lastLogin = isset($user['last_login']) && $user['last_login'] ?
    (is_object($user['last_login']) && method_exists($user['last_login'], 'toDateTime')
        ? $user['last_login']->toDateTime()->format('Y-m-d H:i')
        : date('Y-m-d H:i', strtotime($user['last_login']))) : 'N/A';

// Get products for catalogue
$productModel = new Product();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Get all products (no company filter for catalogue)
$result = $productModel->getAll($page, 12, $search); // 12 products per page
$products = $result['products'];
$totalProducts = $result['total'];
$totalPages = $result['totalPages'];

// Debug: Log product data for troubleshooting

foreach ($products as $index => $product) {
    error_log("Product $index: " . json_encode([
        'name' => $product['name'] ?? 'N/A',
        'main_image' => $product['main_image'] ?? 'N/A',
        'images' => $product['images'] ?? 'N/A',
        'image_url' => $product['image_url'] ?? 'N/A',
        'image' => $product['image'] ?? 'N/A'
    ]));
}

// Get categories for filter
$categories = $productModel->getCategories();

// Helpers for list/table rendering (no images)
function formatCurrency($amount) {
    $value = is_numeric($amount) ? (float)$amount : 0;
    return '₱' . number_format($value, 2);
}

function shortText($text, $limit = 120) {
    $text = trim((string)($text ?? ''));
    if ($text === '') return '-';
    return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
}

function availabilityMeta($product) {
    $status = strtolower($product['status'] ?? 'inactive');
    $stock = $product['stock'] ?? null;
    if (is_numeric($stock)) {
        if ((int)$stock > 0) {
            return ['label' => 'In Stock', 'class' => 'bg-success'];
        }
        return ['label' => 'Out of Stock', 'class' => 'bg-danger'];
    }
    // Fallback to status when stock unknown
    if ($status === 'active') {
        return ['label' => 'Available', 'class' => 'bg-success'];
    }
    return ['label' => 'Unavailable', 'class' => 'bg-secondary'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalogue - Partners Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/partners-layout.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Product Catalogue-specific styles */
        .product-catalogue-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            padding: var(--spacing-8);
            margin-bottom: var(--spacing-6);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }
        
        .product-catalogue-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
        }
        
        .product-catalogue-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .product-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-6);
            margin-bottom: var(--spacing-4);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
            height: 100%;
            border: 1px solid var(--gray-200);
        }
        
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-200);
        }
        
        .product-image-container {
            position: relative;
            width: 100%;
            height: 200px;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            margin-bottom: var(--spacing-4);
            background-color: #f8f9fa;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--border-radius-md);
            transition: transform var(--transition-normal);
        }
        
        .product-image:hover {
            transform: scale(1.05);
        }
        
        .placeholder-image {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            object-fit: contain;
        }
        
        .no-image-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: var(--border-radius-md);
            color: var(--gray-500);
        }
        
        .no-image-placeholder i {
            font-size: 2rem;
            margin-bottom: var(--spacing-2);
        }
        
        .product-status-badge {
            position: absolute;
            top: var(--spacing-2);
            right: var(--spacing-2);
            z-index: 10;
        }
        
        .status-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .status-active {
            color: #16a34a;
        }
        
        .status-inactive {
            color: #dc2626;
        }
        
        .product-actions {
            position: absolute;
            bottom: var(--spacing-2);
            right: var(--spacing-2);
            display: flex;
            gap: var(--spacing-1);
            z-index: 10;
            opacity: 0;
            transform: translateY(10px);
            transition: all var(--transition-normal);
        }
        
        .product-image-container:hover .product-actions {
            opacity: 1;
            transform: translateY(0);
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: var(--border-radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-sm);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all var(--transition-normal);
        }
        
        .action-btn:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 1);
        }
        
        /* List View Styles */
        .product-item.list-view .product-card {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
            padding: var(--spacing-4);
        }
        
        .product-item.list-view .product-image-container {
            width: 120px;
            height: 120px;
            flex-shrink: 0;
            margin-bottom: 0;
        }
        
        .product-item.list-view .product-info {
            flex: 1;
            min-width: 0;
        }
        
        .product-item.list-view .product-name {
            font-size: var(--font-size-xl);
            margin-bottom: var(--spacing-2);
        }
        
        .product-item.list-view .product-description {
            margin-bottom: var(--spacing-3);
        }
        
        .product-item.list-view .product-details {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
            margin-top: var(--spacing-3);
            padding-top: var(--spacing-3);
            border-top: 1px solid var(--gray-200);
        }
        
        .product-item.list-view .product-price {
            margin-bottom: 0;
        }
        
        .product-item.list-view .product-features {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }
        
        .product-info {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-name {
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--spacing-2);
            line-height: 1.3;
        }
        
        .product-category {
            background: linear-gradient(135deg, var(--primary-50), var(--primary-100));
            color: var(--primary-700);
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-sm);
            font-weight: 600;
            display: inline-block;
            margin-bottom: var(--spacing-3);
        }
        
        .product-description {
            color: var(--gray-600);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-4);
            line-height: 1.5;
        }
        
        .product-details {
            margin-top: auto;
            padding-top: var(--spacing-3);
            border-top: 1px solid var(--gray-200);
        }
        
        .product-price {
            font-size: var(--font-size-xl);
            font-weight: 700;
            color: var(--success-600);
            margin-bottom: var(--spacing-2);
        }
        
        .product-sku {
            color: var(--gray-500);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-2);
        }
        
        .product-stock {
            color: var(--gray-600);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-3);
            display: flex;
            align-items: center;
            gap: var(--spacing-1);
        }
        
        .product-features {
            margin-top: var(--spacing-3);
            padding-top: var(--spacing-3);
            border-top: 1px solid var(--gray-200);
        }
        

        
        .product-status {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-sm);
            font-weight: 600;
            display: inline-block;
        }
        
        .status-active { 
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        
        .status-inactive { 
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .search-filter-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-6);
            margin-bottom: var(--spacing-6);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }
        
        .pagination-custom .page-link {
            border-radius: var(--border-radius-md);
            margin: 0 2px;
            border: 1px solid var(--gray-200);
            color: var(--primary-600);
            transition: all var(--transition-normal);
        }
        
        .pagination-custom .page-link:hover {
            background: var(--primary-50);
            border-color: var(--primary-300);
        }
        
        .pagination-custom .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            border-color: var(--primary-500);
            color: white;
        }
        
        .no-image-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            border-radius: var(--border-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
            font-size: var(--font-size-3xl);
            margin-bottom: var(--spacing-4);
        }
        
        .summary-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-6);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
            border: 1px solid var(--gray-200);
            height: 100%;
        }
        
        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--spacing-4);
            color: white;
            font-size: var(--font-size-xl);
        }
        
        .summary-content h3 {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            margin-bottom: var(--spacing-2);
            color: var(--gray-900);
        }
        
        .summary-content p {
            color: var(--gray-600);
            font-weight: 500;
            margin: 0;
        }
        
        .bg-primary { background: linear-gradient(135deg, var(--primary-500), var(--primary-600)) !important; }
        .bg-success { background: linear-gradient(135deg, #16a34a, #15803d) !important; }
        .bg-info { background: linear-gradient(135deg, #0891b2, #0e7490) !important; }
        .bg-warning { background: linear-gradient(135deg, #d97706, #b45309) !important; }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-12);
            color: var(--gray-600);
        }
        
        .empty-state i {
            font-size: var(--font-size-4xl);
            color: var(--gray-400);
            margin-bottom: var(--spacing-4);
        }
        
        .empty-state h4 {
            color: var(--gray-700);
            margin-bottom: var(--spacing-2);
        }
        
        /* New Product Layout Styles */
        .products-summary {
            background: var(--gray-50);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-4);
            border: 1px solid var(--gray-200);
            margin-bottom: var(--spacing-4);
        }
        
        .product-image-container {
            position: relative;
            overflow: hidden;
            border-radius: var(--border-radius-md);
            margin-bottom: var(--spacing-4);
        }
        
        .product-status-badge {
            position: absolute;
            top: var(--spacing-2);
            right: var(--spacing-2);
            z-index: 2;
        }
        
        .status-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
            display: inline-block;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }
        
        .product-actions {
            position: absolute;
            bottom: var(--spacing-2);
            right: var(--spacing-2);
            display: flex;
            gap: var(--spacing-1);
            opacity: 0;
            transform: translateY(10px);
            transition: all var(--transition-normal);
        }
        
        .product-card:hover .product-actions {
            opacity: 1;
            transform: translateY(0);
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-sm);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-details {
            margin-top: var(--spacing-3);
        }
        
        .product-stock {
            color: var(--gray-600);
            font-size: var(--font-size-sm);
            margin-top: var(--spacing-1);
        }
        
        .product-features {
            margin-top: var(--spacing-2);
            padding-top: var(--spacing-2);
            border-top: 1px solid var(--gray-200);
        }
        
        /* List View Styles */
        .product-item.list-view {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .product-item.list-view .product-card {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
        }
        
        .product-item.list-view .product-image-container {
            flex: 0 0 200px;
            margin-bottom: 0;
        }
        
        .product-item.list-view .product-info {
            flex: 1;
        }
        
        .product-item.list-view .product-details {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
            margin-top: var(--spacing-2);
        }
        
        .product-item.list-view .product-price {
            margin-bottom: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .product-item.list-view .product-card {
                flex-direction: column;
                align-items: stretch;
            }
            
            .product-item.list-view .product-image-container {
                flex: none;
                margin-bottom: var(--spacing-4);
            }
            
            .product-item.list-view .product-details {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-2);
            }
        }
        /* Simple product table styles */
        .product-table th { background: var(--gray-50); color: var(--gray-700); }
        .product-table td, .product-table th { vertical-align: middle; }
        .product-table tbody tr { cursor: pointer; }
        .product-table td.description { max-width: 520px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="partners-sidebar">
        <div class="sidebar-header">
            <a href="/">
                <img 
                    src="assets/img/logo_white.png" 
                    alt="PHITSOL Logo" 
                    class="phitsol-logo"
                    id="phitsol-logo"
                >
            </a>
        </div>
        
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="partners-dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="profile.php" class="sidebar-link">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="company-profile.php" class="sidebar-link">
                    <i class="fas fa-building"></i>
                    <span>Company Profile</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="purchased-products.php" class="sidebar-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Purchased Products</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="product-catalogue.php" class="sidebar-link active">
                    <i class="fas fa-book"></i>
                    <span>Product Catalogue</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="contact-support.php" class="sidebar-link">
                    <i class="fas fa-headset"></i>
                    <span>Contact Support</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="partners-main">
        <!-- Partners Header -->
        <div class="partners-header">
            <div class="header-left">
                <div>
                    <h1 class="header-title">Product Catalogue</h1>
                    <p class="text-muted mb-0">Browse available products and services</p>
                </div>
            </div>
            
            <div class="header-user">
                <div class="new-user-info">
                    <div class="user-trigger">
                        <div class="user-icon">
                            <span><?php echo strtoupper(substr($user['name'] ?? $user['company_name'] ?? 'U', 0, 1)); ?></span>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                    <div class="new-user-dropdown">
                        <div class="user-info-display">
                            <div class="user-info-header">
                                <div class="user-info-avatar">
                                    <span><?php echo strtoupper(substr($user['name'] ?? $user['company_name'] ?? 'U', 0, 1)); ?></span>
                                </div>
                                <div class="user-info-details">
                                    <div class="user-info-name"><?php echo htmlspecialchars($user['name'] ?? $user['company_name'] ?? 'User'); ?></div>
                                    <div class="user-info-email"><?php echo htmlspecialchars($user['email'] ?? '-'); ?></div>
                                </div>
                            </div>
                            <div class="user-info-body">
                                <div class="user-info-item">
                                    <i class="fas fa-building"></i>
                                    <span><?php echo htmlspecialchars($user['company'] ?? '-'); ?></span>
                                </div>
                                <div class="user-info-item">
                                    <i class="fas fa-circle status-<?php echo strtolower($user['status'] ?? 'pending'); ?>"></i>
                                    <span>Status: <?php echo htmlspecialchars(ucfirst($user['status'] ?? 'Pending')); ?></span>
                                </div>
                                <div class="user-info-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Last Login: <?php echo isset($user['last_login']) && $user['last_login'] ? (is_object($user['last_login']) && method_exists($user['last_login'], 'toDateTime') ? $user['last_login']->toDateTime()->format('Y-m-d H:i') : date('Y-m-d H:i', strtotime($user['last_login']))) : 'N/A'; ?></span>
                                </div>
                            </div>
                            <div class="user-info-footer">
                                <a href="logout.php" class="logout-btn">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <button id="mobileMenuToggle" class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-icon bg-primary">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo $totalProducts; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo count(array_filter($products, function($p) { return $p['status'] === 'active'; })); ?></h3>
                            <p>Active Products</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-icon bg-info">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo count($categories); ?></h3>
                            <p>Categories</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-icon bg-warning">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo $page; ?></h3>
                            <p>Page <?php echo $page; ?> of <?php echo $totalPages; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="search-filter-card">
                        <form method="GET" action="product-catalogue.php" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search Products</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search by name, short description, or category">
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                                <?php echo $category === $cat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>
                                        Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <a href="product-catalogue.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product Catalogue -->
            <div class="row">
                <div class="col-12">
                    <div class="product-catalogue-card">
                        <div class="card-body position-relative" style="z-index: 2;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="card-title text-dark mb-0">
                                    <i class="fas fa-book me-2"></i>
                                    Available Products
                                </h2>
                                <div class="header-actions">
                                    <a href="purchased-products.php" class="btn btn-outline-primary">
                                        <i class="fas fa-shopping-cart me-1"></i>
                                        View Purchases
                                    </a>
                                </div>
                            </div>

                            <!-- Information Alert -->
                            <div class="alert alert-info mb-4" style="background-color: rgba(13, 202, 240, 0.1); border: 1px solid rgba(13, 202, 240, 0.2); color: #0c5460;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div>
                                        <strong>Product Catalogue:</strong> Browse all available products and services. 
                                        Contact the admin team to place orders for any products you're interested in.
                                    </div>
                                </div>
                            </div>

                            <?php if (empty($products)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <h4>No Products Found</h4>
                                    <p>
                                        <?php if (!empty($search) || !empty($category)): ?>
                                            No products match your search criteria. Try adjusting your filters.
                                        <?php else: ?>
                                            No products are currently available in the catalogue.
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($search) || !empty($category)): ?>
                                        <a href="product-catalogue.php" class="btn btn-primary">
                                            <i class="fas fa-times me-1"></i>
                                            Clear Filters
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <!-- Products Summary -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="products-summary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="summary-info">
                                                    <span class="text-muted">
                                                        Showing <strong><?php echo count($products); ?></strong> of <strong><?php echo $totalProducts; ?></strong> products
                                                        <?php if (!empty($search) || !empty($category)): ?>
                                                            for "<strong><?php echo htmlspecialchars($search ?: $category); ?></strong>"
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <!-- View options removed for simple list UI -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Simple Products Table (image-free) -->
                                <div class="table-responsive">
                                    <table class="table product-table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-start">Product Name</th>
                                                <th class="text-start">Short Description</th>
                                                <th class="text-start">Category</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-center">Availability</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($products as $product): ?>
                                                <?php $meta = availabilityMeta($product); ?>
                                                <?php $rowUrl = 'contact-support.php?product=' . urlencode($product['name'] ?? ''); ?>
                                                <tr onclick="window.location.href='<?php echo $rowUrl; ?>'">
                                                    <td class="text-start fw-semibold">
                                                        <?php echo htmlspecialchars($product['name'] ?? '-'); ?>
                                                    </td>
                                                    <td class="text-start text-muted short-description">
                                                        <?php echo htmlspecialchars(shortText($product['short_description'] ?? '', 120)); ?>
                                                    </td>
                                                    <td class="text-start">
                                                        <?php echo htmlspecialchars($product['category'] ?? '-'); ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?php echo formatCurrency($product['price'] ?? 0); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge <?php echo $meta['class']; ?>">
                                                            <?php echo $meta['label']; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <nav aria-label="Product pagination">
                                                <ul class="pagination pagination-custom justify-content-center">
                                                    <?php if ($page > 1): ?>
                                                        <li class="page-item">
                                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                                                                <i class="fas fa-chevron-left"></i>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                                                                <?php echo $i; ?>
                                                            </a>
                                                        </li>
                                                    <?php endfor; ?>
                                                    
                                                    <?php if ($page < $totalPages): ?>
                                                        <li class="page-item">
                                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                                                                <i class="fas fa-chevron-right"></i>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/unified-layout.js?v=<?php echo time(); ?>"></script>
    
    <script>
        // No additional JS needed for simple table view
    </script>
</body>
</html>