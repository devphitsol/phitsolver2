<?php
require_once '../config/session.php';

// Check if user is logged in and is a business customer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'business') {
    header('Location: login.php');
    exit;
}

require_once '../vendor/autoload.php';
use App\Models\User;
use App\Models\Purchase;

$userModel = new User();
$user = $userModel->getById($_SESSION['user_id']);

// Get company ID from user data
$companyId = $user['_id'] ?? null;

if (!$companyId) {
    $_SESSION['error'] = 'Company information not found.';
    header('Location: partners-dashboard.php');
    exit;
}

// Get purchases for this company
$purchaseModel = new Purchase();
$purchases = $purchaseModel->getByCompanyId($companyId);

// Helper functions
function formatDate($date) {
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

// Process purchases to flatten the structure for table display
$tableData = [];
foreach ($purchases as $purchase) {
    if (isset($purchase['purchase_items']) && is_array($purchase['purchase_items'])) {
        foreach ($purchase['purchase_items'] as $item) {
            $tableData[] = [
                'purchase' => $purchase,
                'item' => $item,
                'transaction_type' => $purchase['transaction_type'] ?? 'purchase',
                'product_name' => $item['product_name'] ?? 'Unknown Product',
                'quantity' => $item['quantity'] ?? 1,
                'payment_method' => $purchase['payment_method'] ?? 'Not specified',
                'reminder_payment' => $purchase['reminder_payment'] ?? null,
                'grand_total' => $purchase['grand_total'] ?? 0,
                'status' => $purchase['purchase_status'] ?? 'pending'
            ];
        }
    } else {
        // Handle legacy single-product purchases
        $tableData[] = [
            'purchase' => $purchase,
            'item' => $purchase,
            'transaction_type' => $purchase['transaction_type'] ?? 'purchase',
            'product_name' => $purchase['product_name'] ?? 'Unknown Product',
            'quantity' => $purchase['quantity'] ?? 1,
            'payment_method' => $purchase['payment_method'] ?? 'Not specified',
            'reminder_payment' => $purchase['reminder_payment'] ?? null,
            'grand_total' => $purchase['grand_total'] ?? 0,
            'status' => $purchase['purchase_status'] ?? 'pending'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchased Products - PHITSOL</title>
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
        
        .purchased-products-table {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            padding: var(--spacing-6);
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
        }
        
        .table-header-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .table-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .table-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .purchased-table {
            margin: 0;
        }
        
        .purchased-table th {
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200);
            font-weight: 600;
            color: var(--gray-700);
            padding: var(--spacing-4);
        }
        
        .purchased-table td {
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-100);
            padding: var(--spacing-4);
        }
        
        .purchased-table tbody tr:hover {
            background: var(--gray-50);
        }
        .purchased-table tbody tr { cursor: pointer; }
        
        .transaction-type-badge {
            background: var(--info-100);
            color: var(--info-800);
            border: 1px solid var(--info-200);
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius-sm);
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .view-btn {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-normal);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .view-btn:hover {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }
        

        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        @media (max-width: 768px) {
            .table-header {
                flex-direction: column;
                text-align: center;
                gap: var(--spacing-3);
            }
            
            .purchased-table {
                font-size: 0.875rem;
            }
            
            .purchased-table th,
            .purchased-table td {
                padding: var(--spacing-3);
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
                <h1 class="header-title">Purchased Products</h1>
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
            <!-- Purchased Products Table -->
            <div class="purchased-products-table">
                <div class="table-header">
                    <div class="table-header-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h3>Purchased Products</h3>
                        <p>Your purchase history from Admin > Purchase Management</p>
                    </div>
                </div>
                
                <?php if (!empty($tableData)): ?>
                    <div class="table-responsive">
                        <table class="table purchased-table">
                            <thead>
                                <tr>
                                    <th>Transaction Type</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Payment Method</th>
                                    <th>Reminder for Payment</th>
                                    <th>Grand Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tableData as $row): ?>
                                    <tr onclick="window.location.href='view-purchase-order.php?id=<?php echo $row['purchase']['_id']; ?>'">
                                        <td>
                                            <span class="transaction-type-badge">
                                                <?php echo getTransactionTypeIcon($row['transaction_type']); ?>
                                                <?php echo getTransactionTypeLabel($row['transaction_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['product_name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo htmlspecialchars($row['quantity']); ?> units
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['payment_method']) && $row['payment_method'] !== 'Not specified'): ?>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($row['payment_method']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Not specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['reminder_payment'])): ?>
                                                <small class="text-info">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    <?php echo formatDate($row['reminder_payment']); ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">Not set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                <?php echo formatCurrency($row['grand_total']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php echo getStatusBadge($row['status']); ?>
                                        </td>
                                        <td>
                                            <a href="view-purchase-order.php?id=<?php echo $row['purchase']['_id']; ?>" 
                                               class="view-btn" 
                                               title="View Purchase Order Details"
                                               onclick="event.stopPropagation();">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <i class="fas fa-box-open fa-2x mb-3"></i>
                        <h5 class="mb-2">No purchases found</h5>
                        <p class="mb-0">Your purchase history from Admin &gt; Purchase Management will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/unified-layout.js?v=<?php echo time(); ?>"></script>
    
    <script>
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
    </script>
</body>
</html>
