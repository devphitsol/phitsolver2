<?php
/** @phpstan-ignore-file */
require_once '../config/session.php';

// Check if user is logged in and is a business customer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'business') {
    header('Location: login.php');
    exit;
}

// Check if purchase ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'Purchase order ID is required.';
    header('Location: purchased-products.php');
    exit;
}

require_once '../vendor/autoload.php';
use App\Models\User;
use App\Models\Purchase;
use MongoDB\BSON\ObjectId as MongoObjectId;

$userModel = new User();
$user = $userModel->getById($_SESSION['user_id']);

// Get company ID from user data
$companyId = $user['_id'] ?? null;

if (!$companyId) {
    $_SESSION['error'] = 'Company information not found.';
    header('Location: partners-dashboard.php');
    exit;
}

// Get purchase details
$purchaseModel = new Purchase();
$purchase = $purchaseModel->getById($_GET['id']);

// Security check: Ensure the purchase belongs to the logged-in company
// Support both string and ObjectId stored company_id
$belongsToCompany = false;
if ($purchase) {
    $purchaseCompanyId = $purchase['company_id'] ?? null;
    if ($purchaseCompanyId !== null) {
        if ($purchaseCompanyId === $companyId) {
            $belongsToCompany = true;
        } elseif ($purchaseCompanyId instanceof MongoObjectId) {
            $belongsToCompany = ((string)$purchaseCompanyId) === (string)$companyId;
        } elseif (is_string($purchaseCompanyId) && $companyId instanceof MongoObjectId) {
            $belongsToCompany = $purchaseCompanyId === (string)$companyId;
        }
    }
}
if (!$purchase || !$belongsToCompany) {
    $_SESSION['error'] = 'Purchase order not found or access denied.';
    header('Location: purchased-products.php');
    exit;
}

// Helper functions
function formatDateForDisplay($date) {
    if (!$date) return 'N/A';
    if (is_object($date) && method_exists($date, 'toDateTime')) {
        return $date->toDateTime()->format('M j, Y');
    } else {
        return date('M j, Y', strtotime($date));
    }
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function calculateDisplayTotal($item, $transactionType) {
    if ($transactionType === 'purchase') {
        return formatCurrency($item['net_amount'] ?? 0);
    } else {
        // For rental/rent to own, show monthly total
        return formatCurrency($item['monthly_total'] ?? 0);
    }
}

function calculateFinancialSummary($purchase) {
    $transactionType = $purchase['transaction_type'] ?? 'purchase';
    $items = $purchase['purchase_items'] ?? [];
    
    $subtotal = 0;
    $totalVat = 0;
    $totalDiscount = 0;
    $grandTotal = 0;
    
    foreach ($items as $item) {
        if ($transactionType === 'purchase') {
            $subtotal += $item['net_amount'] ?? 0;
        } else {
            // For rental/rent to own, use monthly total
            $subtotal += $item['monthly_total'] ?? 0;
        }
    }
    
    $totalVat = $purchase['total_vat'] ?? 0;
    $totalDiscount = $purchase['total_discount'] ?? 0;
    $grandTotal = $purchase['grand_total'] ?? $subtotal;
    
    return [
        'subtotal' => $subtotal,
        'total_vat' => $totalVat,
        'total_discount' => $totalDiscount,
        'grand_total' => $grandTotal
    ];
}

function getTransactionTypeLabel($type) {
    $labels = [
        'purchase' => 'Purchase',
        'rental' => 'Rental',
        'rent_to_own' => 'Rent to Own'
    ];
    return $labels[$type] ?? 'Purchase';
}

function getTransactionTypeIcon($type) {
    $icons = [
        'purchase' => '?��',
        'rental' => '?��',
        'rent_to_own' => '?��'
    ];
    return $icons[$type] ?? '?��';
}

function getStatusBadge($status) {
    $statusColors = [
        'pending' => 'bg-warning',
        'processing' => 'bg-info',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        'delivered' => 'bg-success',
        'shipped' => 'bg-primary',
        'returned' => 'bg-danger'
    ];
    
    $color = $statusColors[strtolower($status)] ?? 'bg-secondary';
    return "<span class=\"badge {$color}\">" . ucfirst($status) . "</span>";
}

$transactionType = $purchase['transaction_type'] ?? 'purchase';
$financialSummary = calculateFinancialSummary($purchase);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Purchase Order - PHITSOL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/partners-layout.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* CSS Variables for compatibility */
        :root {
            --spacing-1: 0.25rem;
            --spacing-2: 0.5rem;
            --spacing-3: 0.75rem;
            --spacing-4: 1rem;
            --spacing-5: 1.25rem;
            --spacing-6: 1.5rem;
            --spacing-8: 2rem;
            --spacing-10: 2.5rem;
            --spacing-12: 3rem;
            --spacing-16: 4rem;
            --spacing-20: 5rem;
            --border-radius-sm: 0.375rem;
            --border-radius-lg: 0.75rem;
            --transition-normal: 0.3s ease;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --primary-500: #0ea5e9;
            --primary-600: #0284c7;
            --success-500: #22c55e;
            --success-700: #15803d;
            --info-100: #dbeafe;
            --info-200: #bfdbfe;
            --info-800: #1e40af;
        }
        
        .purchase-detail-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            margin-bottom: var(--spacing-6);
            overflow: hidden;
        }
        
        .purchase-header {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            padding: var(--spacing-6);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .purchase-header-left {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
        }
        
        .purchase-header-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .purchase-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .purchase-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all var(--transition-normal);
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .purchase-content {
            padding: var(--spacing-6);
        }
        
        .info-section {
            margin-bottom: var(--spacing-8);
        }
        
        .info-section h4 {
            color: var(--gray-800);
            font-weight: 600;
            margin-bottom: var(--spacing-4);
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: var(--spacing-2);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-4);
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 500;
            margin-bottom: var(--spacing-1);
        }
        
        .info-value {
            font-size: 1rem;
            color: var(--gray-900);
            font-weight: 600;
        }
        
        .products-table {
            margin: 0;
        }
        
        .products-table th {
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200);
            font-weight: 600;
            color: var(--gray-700);
        }
        
        .products-table td {
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .serial-view-btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-normal);
        }
        
        .serial-view-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }
        
        .financial-summary {
            background: var(--gray-50);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-6);
            margin-top: var(--spacing-6);
        }
        
        .financial-summary h4 {
            color: var(--gray-800);
            font-weight: 600;
            margin-bottom: var(--spacing-4);
        }
        
        .financial-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-3) 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .financial-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--primary-700);
        }
        
        @media (max-width: 768px) {
            .purchase-header {
                flex-direction: column;
                text-align: center;
                gap: var(--spacing-4);
            }
            
            .purchase-header-left {
                flex-direction: column;
                gap: var(--spacing-3);
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .products-table {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="partners-sidebar">
        <div class="sidebar-header">
            <a href="partners-dashboard.php" class="sidebar-brand">
                <img src="assets/img/logo_white.png" alt="PHITSOL" class="phitsol-logo">
                <span class="sidebar-brand-text">Partners Portal</span>
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
                <a href="purchased-products.php" class="sidebar-link active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Purchased Products</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="product-catalogue.php" class="sidebar-link">
                    <i class="fas fa-boxes"></i>
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
        <!-- Header -->
        <header class="partners-header">
            <div class="header-left">
                <h1 class="header-title">View Purchase Order</h1>
            </div>
            
            <div class="header-user">
                <div class="new-user-info">
                    <div class="user-trigger">
                        <div class="user-icon">
                            <span><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?></span>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                    <div class="new-user-dropdown">
                        <div class="user-info-display">
                            <div class="user-info-header">
                                <div class="user-info-avatar">
                                    <span><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?></span>
                                </div>
                                <div class="user-info-details">
                                    <div class="user-info-name"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
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
            </div>
        </header>

        <!-- Page Content -->
        <div class="main-content">
            <!-- Purchase Order Details -->
            <div class="purchase-detail-card">
                <div class="purchase-header">
                    <div class="purchase-header-left">
                        <div class="purchase-header-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div>
                            <h3>Purchase Order Details</h3>
                            <p>PO Number: <?php echo htmlspecialchars($purchase['po_number'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    <a href="purchased-products.php" class="back-btn">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Purchased Products
                    </a>
                </div>
                
                <div class="purchase-content">
                    <!-- Order Information -->
                    <div class="info-section">
                        <h4><i class="fas fa-info-circle me-2"></i>Order Information</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Transaction Type</span>
                                <span class="info-value">
                                    <?php echo getTransactionTypeIcon($transactionType); ?>
                                    <?php echo getTransactionTypeLabel($transactionType); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Order Date</span>
                                <span class="info-value"><?php echo formatDateForDisplay($purchase['order_date']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Purchase Order Date</span>
                                <span class="info-value"><?php echo formatDateForDisplay($purchase['purchase_order_date']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Delivery Date</span>
                                <span class="info-value"><?php echo formatDateForDisplay($purchase['delivery_date']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Purchase Order Number</span>
                                <span class="info-value"><?php echo htmlspecialchars($purchase['po_number'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Invoice Number</span>
                                <span class="info-value"><?php echo htmlspecialchars($purchase['invoice'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Payment Method</span>
                                <span class="info-value"><?php echo htmlspecialchars($purchase['payment_method'] ?? 'Not specified'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Payment Terms</span>
                                <span class="info-value"><?php echo htmlspecialchars($purchase['payment_terms'] ?? 'Not specified'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Reminder for Payment</span>
                                <span class="info-value"><?php echo formatDateForDisplay($purchase['reminder_payment']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Warranty Period</span>
                                <span class="info-value"><?php echo htmlspecialchars($purchase['warranty_period'] ?? 'Not specified'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value"><?php echo getStatusBadge($purchase['purchase_status'] ?? 'pending'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="info-section">
                        <h4><i class="fas fa-boxes me-2"></i>Products</h4>
                        <?php if (!empty($purchase['purchase_items'])): ?>
                            <div class="table-responsive">
                                <table class="table products-table">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <?php if ($transactionType !== 'purchase'): ?>
                                                <th>Rental Period</th>
                                                <th>Per Unit Price</th>
                                                <th>Monthly Total</th>
                                            <?php else: ?>
                                                <th>Unit Price</th>
                                            <?php endif; ?>
                                            <th>Net Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($purchase['purchase_items'] as $index => $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['product_name'] ?? 'Unknown Product'); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo htmlspecialchars($item['quantity'] ?? '1'); ?> units
                                                    </span>
                                                </td>
                                                <?php if ($transactionType !== 'purchase'): ?>
                                                    <td>
                                                        <?php echo htmlspecialchars($item['rental_period'] ?? 'N/A'); ?> months
                                                    </td>
                                                    <td>
                                                        <?php echo formatCurrency($item['per_unit_price'] ?? 0); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo formatCurrency($item['monthly_total'] ?? 0); ?>
                                                    </td>
                                                <?php else: ?>
                                                    <td>
                                                        <?php echo formatCurrency($item['unit_price'] ?? 0); ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td>
                                                    <strong class="text-success">
                                                        <?php echo calculateDisplayTotal($item, $transactionType); ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $serialNumbers = $item['serial_numbers'] ?? [];
                                                    $serialCount = count($serialNumbers);
                                                    $buttonText = $serialCount > 0 ? "Serial ({$serialCount})" : "Serial";
                                                    $buttonClass = $serialCount > 0 ? "btn-outline-success" : "btn-outline-secondary";
                                                    ?>
                                                    <button type="button" 
                                                            class="btn btn-sm <?php echo $buttonClass; ?> serial-view-btn" 
                                                            onclick="viewSerialNumbers(<?php echo $index; ?>, '<?php echo htmlspecialchars($item['product_name'] ?? 'N/A'); ?>', <?php echo htmlspecialchars(json_encode($serialNumbers)); ?>)"
                                                            title="View Serial Numbers">
                                                        <i class="fas fa-barcode me-1"></i><?php echo $buttonText; ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No products found in this purchase order.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Financial Summary -->
                    <div class="financial-summary">
                        <h4><i class="fas fa-calculator me-2"></i>Financial Summary</h4>
                        <div class="financial-item">
                            <span>Subtotal:</span>
                            <span><?php echo formatCurrency($financialSummary['subtotal']); ?></span>
                        </div>
                        <?php if ($financialSummary['total_vat'] > 0): ?>
                            <div class="financial-item">
                                <span>VAT:</span>
                                <span><?php echo formatCurrency($financialSummary['total_vat']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($financialSummary['total_discount'] > 0): ?>
                            <div class="financial-item">
                                <span>Discount:</span>
                                <span><?php echo formatCurrency($financialSummary['total_discount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="financial-item">
                            <span>Grand Total:</span>
                            <span><?php echo formatCurrency($financialSummary['grand_total']); ?></span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if (!empty($purchase['notes'])): ?>
                        <div class="info-section">
                            <h4><i class="fas fa-sticky-note me-2"></i>Notes</h4>
                            <div class="alert alert-light">
                                <?php echo nl2br(htmlspecialchars($purchase['notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Serial Number View Modal -->
    <div class="modal fade" id="serialNumberViewModal" tabindex="-1" aria-labelledby="serialNumberViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serialNumberViewModalLabel">
                        <i class="fas fa-barcode me-2"></i>
                        Serial Numbers - <span id="modalProductName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="serialNumbersList">
                        <!-- Serial numbers will be populated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/unified-layout.js?v=<?php echo time(); ?>"></script>
    
    <script>
    // Passed-through identifiers for deep links to Contact Support
    const purchaseId = '<?php echo htmlspecialchars($_GET['id'], ENT_QUOTES); ?>';
    const poNumber = '<?php echo htmlspecialchars($purchase['po_number'] ?? '', ENT_QUOTES); ?>';
    
    // User Dropdown Functions
    function toggleNewUserDropdown() {
        const userInfo = document.querySelector('.new-user-info');
        const userTrigger = document.querySelector('.user-trigger');
        
        if (userInfo.classList.contains('active')) {
            closeNewUserDropdown();
        } else {
            openNewUserDropdown();
        }
    }
    
    function openNewUserDropdown() {
        const userInfo = document.querySelector('.new-user-info');
        const userTrigger = document.querySelector('.user-trigger');
        
        userInfo.classList.add('active');
        userTrigger.classList.add('active');
    }
    
    function closeNewUserDropdown() {
        const userInfo = document.querySelector('.new-user-info');
        const userTrigger = document.querySelector('.user-trigger');
        
        userInfo.classList.remove('active');
        userTrigger.classList.remove('active');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userInfo = document.querySelector('.new-user-info');
        const userTrigger = document.querySelector('.user-trigger');
        
        if (!userInfo.contains(event.target) && !userTrigger.contains(event.target)) {
            closeNewUserDropdown();
        }
    });
    
    // Serial Number Viewing Functions
    function viewSerialNumbers(itemIndex, productName, serialNumbers) {
        // Set the product name in the modal
        document.getElementById('modalProductName').textContent = productName;
        
        // Get the serial numbers list container
        const serialNumbersList = document.getElementById('serialNumbersList');
        
        if (serialNumbers && serialNumbers.length > 0) {
            // Create a table to display serial numbers
            let html = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Found <strong>${serialNumbers.length}</strong> serial number(s). Click a serial number to contact support with details prefilled.
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Serial Number</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            serialNumbers.forEach((serialNumber, index) => {
                const link = `contact-support.php?purpose=${encodeURIComponent('REQUEST SERVICE')}`
                    + `&serial=${encodeURIComponent(serialNumber)}`
                    + `&product=${encodeURIComponent(productName)}`
                    + `&po_number=${encodeURIComponent(poNumber)}`
                    + `&purchase_id=${encodeURIComponent(purchaseId)}`;
                html += `
                    <tr>
                        <td class="text-center fw-bold">${index + 1}</td>
                        <td>
                            <a href="${link}" class="text-decoration-none" title="Contact Support for this serial number">
                                <code class="bg-light px-2 py-1 rounded">${serialNumber}</code>
                                <i class="fas fa-arrow-right ms-2" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            serialNumbersList.innerHTML = html;
        } else {
            // No serial numbers found
            serialNumbersList.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>No serial numbers found</strong><br>
                    No serial numbers have been recorded for this product.
                </div>
            `;
        }
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('serialNumberViewModal'));
        modal.show();
    }
    </script>
</body>
</html>
