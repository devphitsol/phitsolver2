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

// Company Profile Data - matching Admin Panel structure
$companyData = [
                'company_name' => $user['company_name'] ?? $user['name'] ?? $user['company'] ?? 'N/A',
    'company_address' => $user['company_address'] ?? $user['address'] ?? 'N/A',
    'date_of_incorporation' => $user['date_of_incorporation'] ?? 'N/A',
    'tin_number' => $user['tin_number'] ?? 'N/A',
    'business_permit' => $user['business_permit'] ?? 'N/A',
    'email_address' => $user['email'],
    'contact_number' => $user['contact_number'] ?? $user['phone'] ?? 'N/A',
    'website_url' => $user['website_url'] ?? 'N/A'
];

// Contact Persons Data - matching Admin Panel structure
$contactData = [
                'authorized_representative' => $user['name'] ?? $user['first_name'] . ' ' . $user['last_name'] ?? $contact,
    'position_title' => $user['position'] ?? 'N/A',
    'representative_contact' => $user['contact_number'] ?? $user['phone'] ?? 'N/A',
    'representative_email' => $user['email'],
    'secondary_contact_name' => $user['secondary_contact_name'] ?? 'N/A',
    'secondary_contact_position' => $user['secondary_contact_position'] ?? 'N/A',
    'secondary_contact_number' => $user['secondary_contact_number'] ?? 'N/A',
    'secondary_contact_email' => $user['secondary_contact_email'] ?? 'N/A'
];

// Documents Data - matching Admin Panel structure
$userDocuments = $user['documents'] ?? [];
if ($userDocuments instanceof \MongoDB\Model\BSONDocument) {
    $userDocuments = $userDocuments->getArrayCopy();
} elseif (!is_array($userDocuments)) {
    $userDocuments = [];
}

$documents = [
    'company_profile' => $userDocuments['company_profile'] ?? false,
    'business_permit' => $userDocuments['business_permit'] ?? false,
    'bir_2303' => $userDocuments['bir_2303'] ?? false,
    'gis' => $userDocuments['gis'] ?? false,
    'audited_financial' => $userDocuments['audited_financial'] ?? false,
    'proof_of_payment' => $userDocuments['proof_of_payment'] ?? false,
    'valid_id' => $userDocuments['valid_id'] ?? false,
    'corporate_secretary' => $userDocuments['corporate_secretary'] ?? false,
    'credit_investigation' => $userDocuments['credit_investigation'] ?? false,
    'peza_certification' => $userDocuments['peza_certification'] ?? false
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile - Partners Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/partners-layout.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Company Profile-specific styles */
        .company-profile-card {
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
        
        .company-profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .company-profile-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        /* Form styles matching Admin Panel */
        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: var(--spacing-2);
            font-size: var(--text-sm);
        }
        
        .form-control {
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-3);
            font-size: var(--text-sm);
            transition: all var(--transition-normal);
            background-color: var(--gray-50);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
            background-color: white;
        }
        
        .form-control[readonly] {
            background-color: var(--gray-100);
            color: var(--gray-600);
            cursor: not-allowed;
        }
        
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.25em;
            margin-right: var(--spacing-2);
            border: 2px solid var(--gray-400);
            border-radius: var(--border-radius-sm);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-input:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .form-check-label {
            font-weight: 500;
            color: var(--gray-700);
            cursor: pointer;
            font-size: var(--text-sm);
        }
        
        .form-check-label:has(+ .form-check-input:disabled) {
            cursor: not-allowed;
            opacity: 0.8;
        }
        
        /* Section headers */
        .card-title {
            font-size: var(--text-xl);
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: var(--spacing-6);
        }
        
        h6.text-primary, h6.text-secondary {
            font-weight: 600;
            font-size: var(--text-base);
            margin-bottom: var(--spacing-4);
        }
        
        /* Alert styling */
        .alert-info {
            background-color: rgba(13, 202, 240, 0.1);
            border: 1px solid rgba(13, 202, 240, 0.2);
            color: var(--gray-700);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-4);
        }
        
        /* Summary Card Styles (matching Product Catalogue) */
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
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .company-profile-card {
                padding: var(--spacing-6);
                margin-bottom: var(--spacing-4);
            }
            
            .card-title {
                font-size: var(--text-lg);
                margin-bottom: var(--spacing-4);
            }
            
            .form-control {
                padding: var(--spacing-2);
                font-size: var(--text-xs);
            }
            
            .summary-card {
                padding: var(--spacing-4);
                margin-bottom: var(--spacing-3);
            }
            
            .summary-icon {
                width: 50px;
                height: 50px;
                font-size: var(--font-size-lg);
                margin-bottom: var(--spacing-3);
            }
            
            .summary-content h3 {
                font-size: var(--font-size-xl);
            }
            
            .summary-content p {
                font-size: var(--font-size-sm);
            }
        }
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
                <a href="company-profile.php" class="sidebar-link active">
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
                    <h1 class="header-title">Company Profile</h1>
                    <p class="text-muted mb-0">View your company information and document status</p>
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
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo $status; ?></h3>
                            <p>Account Status</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-icon bg-success">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo count(array_filter($documents, function($doc) { return $doc; })); ?></h3>
                            <p>Documents Uploaded</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-icon bg-info">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo $lastLogin; ?></h3>
                            <p>Last Login</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-icon bg-warning">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo isset($user['created_at']) ? (is_object($user['created_at']) ? $user['created_at']->toDateTime()->format('M Y') : date('M Y', strtotime($user['created_at']))) : 'N/A'; ?></h3>
                            <p>Member Since</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Profile Card -->
            <div class="row">
                <div class="col-12">
                    <div class="company-profile-card">
                        <div class="card-body position-relative" style="z-index: 2;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="card-title text-dark mb-0">
                                    <i class="fas fa-building me-2"></i>
                                    Company Profile
                                </h2>
                            </div>

                            <!-- Information Alert -->
                            <div class="alert alert-info mb-4" style="background-color: rgba(13, 202, 240, 0.1); border: 1px solid rgba(13, 202, 240, 0.2); color: #0c5460;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div>
                                        <strong>Company Profile:</strong> View your company information and document submission status. 
                                        Keep your profile updated to ensure smooth business operations.
                                    </div>
                                </div>
                            </div>

                            <!-- Company Profile Form -->
                            <form id="companyProfileForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" 
                                               value="<?php echo htmlspecialchars($companyData['company_name']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="date_of_incorporation" class="form-label">Date of Incorporation <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="date_of_incorporation" name="date_of_incorporation" 
                                               value="<?php echo $companyData['date_of_incorporation']; ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="company_address" class="form-label">Company Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="company_address" name="company_address" rows="3" readonly><?php echo htmlspecialchars($companyData['company_address']); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tin_number" class="form-label">TIN / Tax ID Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="tin_number" name="tin_number" 
                                               value="<?php echo htmlspecialchars($companyData['tin_number']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="business_permit" class="form-label">Business Permit Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="business_permit" name="business_permit" 
                                               value="<?php echo htmlspecialchars($companyData['business_permit']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email_address" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email_address" name="email_address" 
                                               value="<?php echo htmlspecialchars($companyData['email_address']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                               value="<?php echo htmlspecialchars($companyData['contact_number']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="website_url" class="form-label">Website URL</label>
                                    <input type="url" class="form-control" id="website_url" name="website_url" 
                                           value="<?php echo htmlspecialchars($companyData['website_url']); ?>" readonly>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Authorized Contact Persons -->
            <div class="row">
                <div class="col-12">
                    <div class="company-profile-card">
                        <div class="card-body position-relative" style="z-index: 2;">
                            <h3 class="card-title text-dark mb-4">
                                <i class="fas fa-users me-2"></i>
                                Authorized Contact Persons
                            </h3>
                            
                            <!-- Contact Persons Form -->
                            <form id="contactPersonsForm">
                                <!-- Primary Contact -->
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user-tie me-1"></i>
                                    Primary Contact Person
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="authorized_representative" class="form-label">Authorized Representative <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="authorized_representative" name="authorized_representative" 
                                               value="<?php echo htmlspecialchars($contactData['authorized_representative']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="position_title" class="form-label">Position/Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="position_title" name="position_title" 
                                               value="<?php echo htmlspecialchars($contactData['position_title']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="representative_contact" class="form-label">Representative Contact No. <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="representative_contact" name="representative_contact" 
                                               value="<?php echo htmlspecialchars($contactData['representative_contact']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="representative_email" class="form-label">Representative Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="representative_email" name="representative_email" 
                                               value="<?php echo htmlspecialchars($contactData['representative_email']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <!-- Secondary Contact -->
                                <h6 class="text-secondary mb-3">
                                    <i class="fas fa-user me-1"></i>
                                    Secondary Contact Person
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="secondary_contact_name" class="form-label">Secondary Contact Person Name</label>
                                        <input type="text" class="form-control" id="secondary_contact_name" name="secondary_contact_name" 
                                               value="<?php echo htmlspecialchars($contactData['secondary_contact_name']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="secondary_contact_position" class="form-label">Secondary Contact Position</label>
                                        <input type="text" class="form-control" id="secondary_contact_position" name="secondary_contact_position" 
                                               value="<?php echo htmlspecialchars($contactData['secondary_contact_position']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="secondary_contact_number" class="form-label">Secondary Contact Number</label>
                                        <input type="tel" class="form-control" id="secondary_contact_number" name="secondary_contact_number" 
                                               value="<?php echo htmlspecialchars($contactData['secondary_contact_number']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="secondary_contact_email" class="form-label">Secondary Contact Email</label>
                                        <input type="email" class="form-control" id="secondary_contact_email" name="secondary_contact_email" 
                                               value="<?php echo htmlspecialchars($contactData['secondary_contact_email']); ?>" readonly>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Status -->
            <div class="row">
                <div class="col-12">
                    <div class="company-profile-card">
                        <div class="card-body position-relative" style="z-index: 2;">
                            <h3 class="card-title text-dark mb-4">
                                <i class="fas fa-file-alt me-2"></i>
                                Required Documents List
                            </h3>
                            
                            <!-- Documents Form -->
                            <form id="documentsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-3">Required Documents</h6>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="company_profile" name="company_profile" 
                                                   <?php echo $documents['company_profile'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="company_profile">
                                                Company Profile <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="business_permit" name="business_permit" 
                                                   <?php echo $documents['business_permit'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="business_permit">
                                                Mayor or Business Permit <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="bir_2303" name="bir_2303" 
                                                   <?php echo $documents['bir_2303'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="bir_2303">
                                                BIR 2303 <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="gis" name="gis" 
                                                   <?php echo $documents['gis'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="gis">
                                                GIS <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="audited_financial" name="audited_financial" 
                                                   <?php echo $documents['audited_financial'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="audited_financial">
                                                Audited Financial Statement <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="proof_of_payment" name="proof_of_payment" 
                                                   <?php echo $documents['proof_of_payment'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="proof_of_payment">
                                                Proof of Payment (3 Months Office Address) <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="valid_id" name="valid_id" 
                                                   <?php echo $documents['valid_id'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="valid_id">
                                                Valid ID of Authorized Person (Government/Business ID) <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="corporate_secretary" name="corporate_secretary" 
                                                   <?php echo $documents['corporate_secretary'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="corporate_secretary">
                                                Corporate Secretary Certificate (Notarized) <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h6 class="text-secondary mb-3">Optional Documents</h6>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="credit_investigation" name="credit_investigation" 
                                                   <?php echo $documents['credit_investigation'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="credit_investigation">
                                                Credit Investigation Form
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="peza_certification" name="peza_certification" 
                                                   <?php echo $documents['peza_certification'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label" for="peza_certification">
                                                PEZA Certification (if Zero Rated Tax)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/unified-layout.js?v=<?php echo time(); ?>"></script>
</body>
</html> 