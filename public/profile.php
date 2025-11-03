<?php
require_once '../config/session.php';

// Check if user is logged in and is a business customer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'business') {
    header('Location: login.php');
    exit;
}

require_once '../vendor/autoload.php';
use App\Models\User;

$userModel = new User();
$user = $userModel->getById($_SESSION['user_id']);

// 계정 ?�보 추출
$company = $user['company'] ?? '-';
$contact = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$email = $user['email'] ?? '-';
$status = ucfirst($user['status'] ?? 'Pending');
$lastLogin = isset($user['last_login']) && $user['last_login'] ?
    (is_object($user['last_login']) && method_exists($user['last_login'], 'toDateTime')
        ? $user['last_login']->toDateTime()->format('Y-m-d H:i')
        : date('Y-m-d H:i', strtotime($user['last_login']))) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Partners Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/partners-layout.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Profile-specific styles */
        .profile-card {
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
        
        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
        }
        
        .profile-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .card-header {
            text-align: center;
            margin-bottom: var(--spacing-8);
            position: relative;
        }
        
        .card-title {
            font-size: var(--font-size-3xl);
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: var(--spacing-2);
            letter-spacing: -0.025em;
        }
        
        .card-subtitle {
            color: var(--gray-600);
            font-size: var(--font-size-lg);
            font-weight: 500;
        }
        
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-8);
        }
        
        .info-section {
            background: var(--gray-50);
            padding: var(--spacing-6);
            border-radius: var(--border-radius-md);
            border: 1px solid var(--gray-200);
            transition: all var(--transition-fast);
        }
        
        .info-section:hover {
            background: white;
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }
        
        .section-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .info-value {
            color: #1e293b;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .back-btn:hover {
            background: #2563eb;
            color: white;
        }
        @media (max-width: 768px) {
            .partners-layout {
                grid-template-areas: 
                    "header"
                    "main";
                grid-template-rows: 80px 1fr;
                grid-template-columns: 1fr;
            }
            .partners-sidebar {
                display: none;
            }
            .partners-header {
                padding: 0 1rem;
            }
            .header-title {
                font-size: 1rem;
            }
            .user-info {
                display: none;
            }
            .partners-main {
                padding: 1rem;
            }
            .profile-card {
                padding: 1.5rem;
            }
            .profile-info {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="partners-sidebar">
        <div class="sidebar-header">
            <a href="partners-dashboard.php" class="sidebar-brand">
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
                <a href="profile.php" class="sidebar-link active">
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
                <a href="product-catalogue.php" class="sidebar-link">
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
                    <h1 class="header-title">Profile</h1>
                    <p class="text-muted mb-0">Manage your account information</p>
                </div>
            </div>
            <div class="header-user">
                <div class="new-user-info">
                    <div class="user-trigger">
                        <div class="user-icon">
                            <span><?php echo strtoupper(substr($contact, 0, 1)); ?></span>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                    <div class="new-user-dropdown">
                        <div class="user-info-display">
                            <div class="user-info-header">
                                <div class="user-info-avatar">
                                    <span><?php echo strtoupper(substr($contact, 0, 1)); ?></span>
                                </div>
                                <div class="user-info-details">
                                    <div class="user-info-name"><?php echo htmlspecialchars($contact); ?></div>
                                    <div class="user-info-email"><?php echo htmlspecialchars($email); ?></div>
                                </div>
                            </div>
                            <div class="user-info-body">
                                <div class="user-info-item">
                                    <i class="fas fa-building"></i>
                                    <span><?php echo htmlspecialchars($company); ?></span>
                                </div>
                                <div class="user-info-item">
                                    <i class="fas fa-circle status-<?php echo strtolower($status); ?>"></i>
                                    <span>Status: <?php echo htmlspecialchars($status); ?></span>
                                </div>
                                <div class="user-info-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Last Login: <?php echo htmlspecialchars($lastLogin); ?></span>
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
                <div class="profile-card">
                    <div class="card-header">
                        <h1 class="card-title">Profile</h1>
                        <p class="card-subtitle">Your account information and settings</p>
                    </div>
                    
                    <div class="profile-info">
                        <div class="info-section">
                            <h3 class="section-title">
                                <i class="fas fa-user"></i>
                                Personal Information
                            </h3>
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($contact); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email Address</span>
                                <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Username</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['username'] ?? '-'); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h3 class="section-title">
                                <i class="fas fa-building"></i>
                                Business Information
                            </h3>
                            <div class="info-item">
                                <span class="info-label">Company</span>
                                <span class="info-value"><?php echo htmlspecialchars($company); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Account Status</span>
                                <span class="status-badge status-<?php echo strtolower($user['status'] ?? 'pending'); ?>">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Login</span>
                                <span class="info-value"><?php echo htmlspecialchars($lastLogin); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This is your profile information. For any changes or support, please contact the administrator.
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="partners-dashboard.php" class="back-btn">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/unified-layout.js?v=<?php echo time(); ?>"></script>
</body>
</html> 