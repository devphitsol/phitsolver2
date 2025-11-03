<?php
/** @phpstan-ignore-file */
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

// 계정 ?�보 추출
$company = $user['company'] ?? '-';
$contact = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$email = $user['email'] ?? '-';
$status = ucfirst($user['status'] ?? 'Pending');
$lastLogin = isset($user['last_login']) && $user['last_login'] ?
    (is_object($user['last_login']) && method_exists($user['last_login'], 'toDateTime')
        ? $user['last_login']->toDateTime()->format('Y-m-d H:i')
        : date('Y-m-d H:i', strtotime($user['last_login']))) : 'N/A';

// Get user's support messages
$supportMessageModel = new SupportMessage();
$userMessages = $supportMessageModel->getByUserId($_SESSION['user_id']);

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');

        // Validate input
        if (empty($subject)) {
            $errors[] = 'Subject is required';
        }

        if (empty($message)) {
            $errors[] = 'Message is required';
        }

        if (empty($purpose)) {
            $errors[] = 'Purpose is required';
        }



        if (empty($errors)) {
            try {
                $utcDateTimeClass = '\\MongoDB\\BSON\\UTCDateTime';
                $createdAt = date('c');
                if (class_exists($utcDateTimeClass)) {
                    $createdAt = new $utcDateTimeClass();
                }
                $supportData = [
                    'user_id' => $_SESSION['user_id'],
                    'user_name' => $contact,
                    'user_email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                    'purpose' => $purpose,
                    'status' => 'pending',
                    'created_at' => $createdAt
                ];

                $result = $supportMessageModel->create($supportData);
                
                if ($result) {
                    $success = true;
                    // Clear form data
                    $_POST = [];
                } else {
                    $errors[] = 'Failed to send message. Please try again.';
                }
            } catch (\Exception $e) {
                error_log('Support message error: ' . $e->getMessage());
                $errors[] = 'An error occurred while sending your message. Please try again.';
            }
        }
    }
}

// Handle URL parameters for pre-filling form (from Serial Number links)
$prefillPurpose = $_GET['purpose'] ?? '';
$prefillSerial = $_GET['serial'] ?? '';
$prefillProduct = $_GET['product'] ?? '';
$prefillPoNumber = $_GET['po_number'] ?? '';
$prefillPurchaseId = $_GET['purchase_id'] ?? '';

// Pre-fill subject if serial number is provided
$prefillSubject = '';
if (!empty($prefillSerial) && !empty($prefillProduct)) {
    $prefillSubject = "Service Request for Serial Number: {$prefillSerial} - {$prefillProduct}";
    if (!empty($prefillPoNumber)) {
        $prefillSubject .= " (PO: {$prefillPoNumber})";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support - Partners Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/partners-layout.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Support-specific styles */
        .support-card {
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
        
        .support-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
        }
        
        .support-card:hover {
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
        
        .form-group {
            margin-bottom: var(--spacing-6);
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: var(--spacing-2);
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .form-control, .form-select {
            width: 100%;
            padding: var(--spacing-4);
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-base);
            font-family: var(--font-family);
            transition: all var(--transition-fast);
            background: white;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px var(--primary-100);
            transform: translateY(-1px);
        }
        
        .form-text {
            font-size: var(--font-size-sm);
            color: var(--gray-500);
            margin-top: var(--spacing-2);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
        }
        .alert-success {
            background: #dcfce7;
            border-color: #bbf7d0;
            color: #166534;
        }
        .alert-danger {
            background: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-outline-secondary {
            background: transparent;
            color: #6b7280;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        .btn-outline-secondary:hover {
            background: #6b7280;
            color: white;
        }
        .form-actions {
            text-align: center;
            margin-top: 2rem;
        }
        .form-actions .btn {
            margin: 0 0.5rem;
        }
        
        /* Messages List Styles */
        .messages-list {
            margin-top: 1rem;
        }
        
        .message-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        
        .message-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .message-header {
            background: #f9fafb;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .message-subject {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 0.5rem 0;
        }
        
        .message-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }
        
        .message-purpose {
            background: #3b82f6;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        

        
        .message-date {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .message-status {
            flex-shrink: 0;
        }
        
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-replied {
            background: #d1fae5;
            color: #065f46;
        }
        
        .message-content {
            padding: 1.5rem;
        }
        
        .user-message, .admin-reply {
            margin-bottom: 1.5rem;
        }
        
        .admin-reply {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-radius: 0 8px 8px 0;
            margin-top: 1rem;
        }
        
        .message-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .message-text {
            color: #1f2937;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .admin-text {
            color: #1f2937;
            font-weight: 500;
        }
        
        .reply-date {
            margin-top: 0.75rem;
            font-size: 0.75rem;
            color: #6b7280;
            font-style: italic;
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
            .support-card {
                padding: 1.5rem;
            }
            .form-actions .btn {
                display: block;
                margin: 0.5rem 0;
                width: 100%;
            }
            
            /* Mobile Messages Styles */
            .message-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .message-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .message-status {
                align-self: flex-end;
            }
            
            .message-content {
                padding: 1rem;
            }
            
            .admin-reply {
                margin-top: 1rem;
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
                <a href="contact-support.php" class="sidebar-link active">
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
                    <h1 class="header-title">Support</h1>
                    <p class="text-muted mb-0">Contact us for assistance</p>
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
            <!-- New Message Form -->
            <div class="support-card mb-4">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-paper-plane me-2"></i>
                        Send New Message
                    </h2>
                    <p class="card-subtitle">Send us a message and we'll get back to you</p>
                </div>
                <?php if (!empty($prefillSerial)) : ?>
                    <div class="alert alert-info d-flex align-items-start" role="alert">
                        <i class="fas fa-barcode me-2 mt-1"></i>
                        <div>
                            <div><strong>Serial Number:</strong> <?php echo htmlspecialchars($prefillSerial); ?></div>
                            <?php if (!empty($prefillProduct)) : ?>
                                <div><strong>Product:</strong> <?php echo htmlspecialchars($prefillProduct); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($prefillPoNumber)) : ?>
                                <div><strong>PO Number:</strong> <?php echo htmlspecialchars($prefillPoNumber); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($prefillPurchaseId)) : ?>
                                <div><strong>Purchase ID:</strong> <?php echo htmlspecialchars($prefillPurchaseId); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Success!</strong> Your message has been sent successfully. We'll get back to you soon.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error!</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <?php if (!empty($prefillSerial)) : ?>
                            <input type="hidden" name="serial" value="<?php echo htmlspecialchars($prefillSerial); ?>">
                        <?php endif; ?>
                        <?php if (!empty($prefillProduct)) : ?>
                            <input type="hidden" name="product" value="<?php echo htmlspecialchars($prefillProduct); ?>">
                        <?php endif; ?>
                        <?php if (!empty($prefillPoNumber)) : ?>
                            <input type="hidden" name="po_number" value="<?php echo htmlspecialchars($prefillPoNumber); ?>">
                        <?php endif; ?>
                        <?php if (!empty($prefillPurchaseId)) : ?>
                            <input type="hidden" name="purchase_id" value="<?php echo htmlspecialchars($prefillPurchaseId); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="purpose" class="form-label">Purpose of Inquiry *</label>
                            <select class="form-select" id="purpose" name="purpose" required>
                                <option value="">Select Purpose</option>
                                <option value="INQUIRY ONLY" <?php echo ($_POST['purpose'] ?? $prefillPurpose) === 'INQUIRY ONLY' ? 'selected' : ''; ?>>INQUIRY ONLY</option>
                                <option value="REQUEST QUOTATION" <?php echo ($_POST['purpose'] ?? $prefillPurpose) === 'REQUEST QUOTATION' ? 'selected' : ''; ?>>REQUEST QUOTATION</option>
                                <option value="REQUEST SERVICE" <?php echo ($_POST['purpose'] ?? $prefillPurpose) === 'REQUEST SERVICE' ? 'selected' : ''; ?>>REQUEST SERVICE (Repair Service)</option>
                                <option value="RENTAL" <?php echo ($_POST['purpose'] ?? $prefillPurpose) === 'RENTAL' ? 'selected' : ''; ?>>RENTAL</option>
                                <option value="RENT TO OWN" <?php echo ($_POST['purpose'] ?? $prefillPurpose) === 'RENT TO OWN' ? 'selected' : ''; ?>>RENT TO OWN</option>
                                <option value="DISPOSAL" <?php echo ($_POST['purpose'] ?? $prefillPurpose) === 'DISPOSAL' ? 'selected' : ''; ?>>DISPOSAL</option>
                                <option value="CRUSHING" <?php echo ($_POST['purpose'] ?? $prefillPurpose) === 'CRUSHING' ? 'selected' : ''; ?>>CRUSHING</option>
                            </select>
                        </div>
                        

                        
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   placeholder="Enter subject" required
                                   value="<?php echo htmlspecialchars($_POST['subject'] ?? $prefillSubject); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      placeholder="Enter your message" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Message
                            </button>
                            <a href="partners-dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Support Messages List -->
            <div class="support-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-envelope me-2"></i>
                        My Support Messages
                    </h2>
                    <p class="card-subtitle">View your support messages and responses</p>
                </div>
                
                <div class="messages-list">
                    <?php if (empty($userMessages)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Messages Found</h5>
                            <p class="text-muted">You haven't sent any support messages yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($userMessages as $message): ?>
                            <div class="message-item">
                                <div class="message-header">
                                    <div class="message-info">
                                        <h5 class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></h5>
                                        <div class="message-meta">
                                            <span class="message-purpose"><?php echo htmlspecialchars($message['purpose']); ?></span>
                                            <span class="message-date">
                                                <?php 
                                                if (isset($message['created_at'])) {
                                                    $date = $message['created_at'];
                                                    if (is_object($date) && method_exists($date, 'toDateTime')) {
                                                        echo $date->toDateTime()->format('M d, Y g:i A');
                                                    } else {
                                                        echo date('M d, Y g:i A', strtotime((string)$date));
                                                    }
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="message-status">
                                        <span class="status-badge status-<?php echo $message['status']; ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="message-content">
                                    <div class="user-message">
                                        <div class="message-label">Your Message:</div>
                                        <div class="message-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                    </div>
                                    
                                    <?php if (!empty($message['admin_reply'])): ?>
                                        <div class="admin-reply">
                                            <div class="message-label">
                                                <i class="fas fa-reply me-1"></i>
                                                Admin Response:
                                            </div>
                                            <div class="message-text admin-text">
                                                <?php echo nl2br(htmlspecialchars($message['admin_reply'])); ?>
                                            </div>
                                            <div class="reply-date">
                                                <?php 
                                                if (isset($message['admin_reply_date'])) {
                                                    $replyDate = $message['admin_reply_date'];
                                                    if (is_object($replyDate) && method_exists($replyDate, 'toDateTime')) {
                                                        echo $replyDate->toDateTime()->format('M d, Y g:i A');
                                                    } else {
                                                        echo date('M d, Y g:i A', strtotime((string)$replyDate));
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/unified-layout.js?v=<?php echo time(); ?>"></script>
    <script>

    </script>
</body>
</html> 