<?php
require_once '../config/session.php';

// Check if user is logged in and is a business customer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'business') {
    header('Location: login.php');
    exit;
}

require_once '../vendor/autoload.php';
use App\Models\User;
use App\Models\SupportMessage;

$userModel = new User();
$user = $userModel->getById($_SESSION['user_id']);

// Profile info
$company = $user['company'] ?? '-';
$contact = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$email = $user['email'] ?? '-';
$status = ucfirst($user['status'] ?? 'Pending');
$lastLogin = isset($user['last_login']) && $user['last_login'] ?
    (is_object($user['last_login']) && method_exists($user['last_login'], 'toDateTime')
        ? $user['last_login']->toDateTime()->format('Y-m-d H:i')
        : date('Y-m-d H:i', strtotime($user['last_login']))) : 'N/A';

// Support summary
$supportModel = new SupportMessage();
$supportMessages = $supportModel->getByUserId($_SESSION['user_id']);
$pendingCount = 0;
$repliedCount = 0;
$recentMessages = [];
foreach ($supportMessages as $msg) {
    if ($msg['status'] === 'pending') $pendingCount++;
    if ($msg['status'] === 'replied') $repliedCount++;
}
$recentMessages = array_slice($supportMessages, 0, 2);

function formatSupportDate($date) {
    if (!$date) return 'N/A';
    if (is_object($date) && method_exists($date, 'toDateTime')) {
        return $date->toDateTime()->format('Y-m-d H:i');
    } else {
        return date('Y-m-d H:i', strtotime($date));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partners Dashboard - PHITSOL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/partners-layout.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Enhanced Dashboard Design System */
        
        /* Main Layout */
        .dashboard-container {
            padding: var(--spacing-6);
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Stats Grid - Using summary-content style */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-8);
        }
        
        .stats-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-6);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
            border: 1px solid var(--gray-200);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .stats-icon {
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
        
        .stats-content h3 {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            margin-bottom: var(--spacing-2);
            color: var(--gray-900);
        }
        
        .stats-content p {
            color: var(--gray-600);
            font-weight: 500;
            margin: 0;
        }
        
        /* Gradient Backgrounds */
        .bg-primary { background: linear-gradient(135deg, var(--primary-500), var(--primary-600)) !important; }
        .bg-success { background: linear-gradient(135deg, #16a34a, #15803d) !important; }
        .bg-info { background: linear-gradient(135deg, #0891b2, #0e7490) !important; }
        .bg-warning { background: linear-gradient(135deg, #d97706, #b45309) !important; }
        .bg-purple { background: linear-gradient(135deg, #7c3aed, #6d28d9) !important; }
        .bg-pink { background: linear-gradient(135deg, #ec4899, #db2777) !important; }
        
        /* Main Content Cards */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-6);
        }
        
        .content-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            padding: var(--spacing-6);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }
        
        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
        }
        
        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        /* Card Headers */
        .card-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
            margin-bottom: var(--spacing-5);
            padding-bottom: var(--spacing-4);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .card-header i {
            font-size: var(--font-size-xl);
            color: var(--primary-600);
            width: 24px;
            text-align: center;
        }
        
        .card-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }
        
        /* Content Sections */
        .content-section {
            margin-bottom: var(--spacing-4);
        }
        
        .content-section:last-child {
            margin-bottom: 0;
        }
        
        .section-label {
            color: var(--gray-600);
            font-size: var(--font-size-sm);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: var(--spacing-1);
        }
        
        .section-value {
            color: var(--gray-900);
            font-weight: 600;
            font-size: var(--font-size-base);
            margin-bottom: var(--spacing-2);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-active {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
        }
        
        .status-inactive {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }
        
        /* Action Links */
        .action-link {
            margin-top: var(--spacing-4);
            padding: var(--spacing-3) var(--spacing-4);
            border-radius: var(--border-radius-md);
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-2);
            box-shadow: var(--shadow-sm);
        }
        
        .action-link:hover {
            background: linear-gradient(135deg, var(--primary-700), var(--primary-800));
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            color: white;
        }
        
        /* Support List */
        .support-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .support-item {
            border-bottom: 1px solid var(--gray-200);
            padding: var(--spacing-3) 0;
        }
        
        .support-item:last-child {
            border-bottom: none;
        }
        
        .support-subject {
            font-weight: 600;
            color: var(--gray-900);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-1);
        }
        
        .support-meta {
            color: var(--gray-600);
            font-size: var(--font-size-xs);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        
        .support-status {
            font-size: var(--font-size-xs);
            font-weight: 600;
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--border-radius-full);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-replied {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
        }
        
        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            padding: var(--spacing-6);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-6);
            text-align: center;
        }
        
        .welcome-title {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            margin-bottom: var(--spacing-2);
        }
        
        .welcome-subtitle {
            font-size: var(--font-size-base);
            opacity: 0.9;
            margin: 0;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: var(--spacing-4);
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: var(--spacing-4);
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: var(--spacing-4);
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: var(--spacing-3);
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: var(--spacing-3);
            }
            
            .stats-card {
                padding: var(--spacing-4);
            }
            
            .content-card {
                padding: var(--spacing-4);
            }
            
            .welcome-section {
                padding: var(--spacing-4);
            }
            
            .welcome-title {
                font-size: var(--font-size-xl);
            }
            
            .stats-icon {
                width: 50px;
                height: 50px;
                font-size: var(--font-size-lg);
                margin-bottom: var(--spacing-3);
            }
            
            .stats-content h3 {
                font-size: var(--font-size-xl);
            }
            
            .card-title {
                font-size: var(--font-size-base);
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-container {
                padding: var(--spacing-2);
            }
            
            .stats-card,
            .content-card {
                padding: var(--spacing-3);
            }
            
            .welcome-section {
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
                <a href="partners-dashboard.php" class="sidebar-link active">
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
                    <h1 class="header-title">Dashboard</h1>
                    <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($contact); ?></p>
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
            <div class="dashboard-container">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($contact); ?>!</h1>
                    <p class="welcome-subtitle">Here's your dashboard overview and quick access to important information.</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <!-- Account Status Stats -->
                    <div class="stats-card">
                        <div class="stats-icon bg-primary">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $status; ?></h3>
                            <p>Account Status</p>
                        </div>
                    </div>
                    
                    <!-- Documents Stats -->
                    <div class="stats-card">
                        <div class="stats-icon bg-success">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stats-content">
                            <?php 
                            $documents = $user['documents'] ?? [];
                            if ($documents instanceof \MongoDB\Model\BSONDocument) {
                                $documents = $documents->getArrayCopy();
                            } elseif (!is_array($documents)) {
                                $documents = [];
                            }
                            $submittedCount = array_sum(array_map('intval', $documents));
                            ?>
                            <h3><?php echo $submittedCount; ?>/<?php echo count($documents); ?></h3>
                            <p>Documents Submitted</p>
                        </div>
                    </div>
                    
                    <!-- Support Stats -->
                    <div class="stats-card">
                        <div class="stats-icon bg-info">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $pendingCount; ?></h3>
                            <p>Pending Support</p>
                        </div>
                    </div>
                    
                    <!-- Last Login Stats -->
                    <div class="stats-card">
                        <div class="stats-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo htmlspecialchars($lastLogin); ?></h3>
                            <p>Last Login</p>
                        </div>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Profile Information Card -->
                    <div class="content-card">
                        <div class="card-header">
                            <i class="fas fa-user"></i>
                            <h2 class="card-title">Profile Information</h2>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Full Name</div>
                            <div class="section-value"><?php echo htmlspecialchars($contact); ?></div>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Company</div>
                            <div class="section-value"><?php echo htmlspecialchars($company); ?></div>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Email Address</div>
                            <div class="section-value"><?php echo htmlspecialchars($email); ?></div>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Account Status</div>
                            <span class="status-badge status-<?php echo strtolower($user['status'] ?? 'pending'); ?>"><?php echo $status; ?></span>
                        </div>
                        <a href="profile.php" class="action-link">
                            <i class="fas fa-arrow-right"></i> View Profile
                        </a>
                    </div>
                    
                    <!-- Company Profile Card -->
                    <div class="content-card">
                        <div class="card-header">
                            <i class="fas fa-building"></i>
                            <h2 class="card-title">Company Profile</h2>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Company Name</div>
                            <div class="section-value"><?php echo htmlspecialchars($user['company_name'] ?? $user['company'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Business Permit</div>
                            <div class="section-value"><?php echo htmlspecialchars($user['business_permit'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="content-section">
                            <div class="section-label">TIN Number</div>
                            <div class="section-value"><?php echo htmlspecialchars($user['tin_number'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Documents Status</div>
                            <div class="section-value">
                                <?php 
                                $documents = $user['documents'] ?? [];
                                if ($documents instanceof \MongoDB\Model\BSONDocument) {
                                    $documents = $documents->getArrayCopy();
                                } elseif (!is_array($documents)) {
                                    $documents = [];
                                }
                                $submittedCount = array_sum(array_map('intval', $documents));
                                echo $submittedCount . ' of ' . count($documents) . ' documents submitted';
                                ?>
                            </div>
                        </div>
                        <a href="company-profile.php" class="action-link">
                            <i class="fas fa-arrow-right"></i> View Company Profile
                        </a>
                    </div>
                    
                    <!-- Support & Inquiries Card -->
                    <div class="content-card">
                        <div class="card-header">
                            <i class="fas fa-envelope"></i>
                            <h2 class="card-title">Support & Inquiries</h2>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Pending Inquiries</div>
                            <div class="section-value"><?php echo $pendingCount; ?> inquiries</div>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Replied Inquiries</div>
                            <div class="section-value"><?php echo $repliedCount; ?> inquiries</div>
                        </div>
                        <div class="content-section">
                            <div class="section-label">Recent Inquiries</div>
                            <ul class="support-list">
                                <?php if (empty($recentMessages)): ?>
                                    <li class="support-item">
                                        <div class="support-subject">No recent inquiries</div>
                                        <div class="support-meta">You haven't submitted any support requests yet.</div>
                                    </li>
                                <?php else: ?>
                                    <?php foreach ($recentMessages as $msg): ?>
                                        <li class="support-item">
                                            <div class="support-subject"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                            <div class="support-meta">
                                                <?php echo htmlspecialchars($msg['purpose']); ?> · <?php echo formatSupportDate($msg['created_at'] ?? null); ?>
                                                <span class="support-status status-<?php echo $msg['status']; ?>"><?php echo ucfirst($msg['status']); ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <a href="contact-support.php" class="action-link">
                            <i class="fas fa-arrow-right"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </main>
    </div>
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/unified-layout.js?v=<?php echo time(); ?>"></script>
    <script>
        // Initialize modern UI features
        document.addEventListener('DOMContentLoaded', function() {
            if (window.modernUnifiedLayout) {
        
            }
        });
    </script>
</body>
</html> 