<?php
// Add custom PHP include path
ini_set("include_path", '/home/qiimy7odbu3s/php:' . ini_get("include_path"));

require_once '../config/session.php';

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['role'] === 'business') {
        header('Location: partners-dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

// Load Composer autoloader
require_once '../vendor/autoload.php';

use App\Models\User;

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // PHP-based login (partners-login.php logic)
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $errors[] = 'Invalid request. Please try again.';
        } else {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($username)) {
                $errors[] = 'Username or email is required';
            }

            if (empty($password)) {
                $errors[] = 'Password is required';
            }

            // If no validation errors, attempt login
            if (empty($errors)) {
                try {
                    $userModel = new User();
                    $user = $userModel->authenticate($username, $password);
                    
                    if ($user) {
                        // Check if user is a business customer
                        if ($user['role'] !== 'business') {
                            $errors[] = 'Access denied. This portal is for business customers only.';
                        } elseif ($user['status'] !== 'active') {
                            if ($user['status'] === 'pending') {
                                $errors[] = 'Your account is pending approval. Please wait for admin approval before logging in.';
                            } else {
                                $errors[] = 'Your account has been deactivated. Please contact support.';
                            }
                        } else {
                            // Set session variables
                            $_SESSION['user_id'] = (string) $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['first_name'] = $user['first_name'];
                            $_SESSION['last_name'] = $user['last_name'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['logged_in'] = true;
                            $_SESSION['login_time'] = time();
                            
                            // Set admin session variables for dashboard access
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_user_id'] = (string) $user['id'];
                            $_SESSION['admin_username'] = $user['username'];
                            $_SESSION['admin_email'] = $user['email'];
                            $_SESSION['admin_role'] = $user['role'];
                            
                            // Regenerate session ID for security
                            session_regenerate_id(true);
                            
                            // Redirect to admin dashboard
                            header('Location: ../admin/index.php');
                            exit;
                        }
                    } else {
                        $errors[] = 'Invalid username/email or password';
                    }
                } catch (\Exception $e) {
                    error_log('Partners Portal Login error: ' . $e->getMessage());
                    $errors[] = 'An error occurred during login. Please try again.';
                }
            }
        }
    }

?>
<!doctype html>
<html class="no-js" lang="en">
    <head>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-MJSZT33R');</script>
        <!-- End Google Tag Manager -->
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-6DFTSQLP05"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'G-6DFTSQLP05');
        </script>
        <!-- End Google tag (gtag.js) -->
        <!-- Analytics Event Tracking -->
        <script src="../js/analytics-events.js"></script>
        <title>PHITSOL INC. - PARTNERS PORTAL LOGIN</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="author" content="ThemeZaa">
        <meta name="viewport" content="width=device-width,initial-scale=1.0" />
        <meta name="description" content="PHITSOL Partners Portal - Secure login for business partners to manage profiles, access products, track orders, and connect with support.">
        <!-- favicon icon -->
        <link rel="shortcut icon" href="../images/favicon.png">
        <link rel="p-icon" href="../images/p-icon-57x57.png">
        <link rel="p-icon" sizes="72x72" href="../images/p-icon-72x72.png">
        <link rel="p-icon" sizes="114x114" href="../images/p-icon-114x114.png">
        <!-- google fonts preconnect -->
        <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <!-- style sheets and font icons  -->
        <link rel="stylesheet" href="../css/vendors.min.css"/>
        <link rel="stylesheet" href="../css/icon.min.css"/>
        <link rel="stylesheet" href="../css/style.css"/>
        <link rel="stylesheet" href="../css/responsive.css"/>
        <link rel="stylesheet" href="../demos/corporate/corporate.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            /* Unified Login page specific styles */
            .login-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 20px;
                -webkit-overflow-scrolling: touch;
            }
            
            .login-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                padding: 40px;
                width: 100%;
                max-width: 450px;
                position: relative;
                overflow: hidden;
            }
            
            .login-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #667eea, #764ba2);
            }
            
            .login-header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .login-logo {
                max-width: 200px;
                height: auto;
                margin-bottom: 20px;
            }
            
            .login-title {
                font-size: 28px;
                font-weight: 700;
                color: #333;
                margin-bottom: 10px;
            }
            
            .login-subtitle {
                color: #666;
                font-size: 16px;
                margin-bottom: 0;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                display: block;
                font-weight: 600;
                color: #333;
                margin-bottom: 8px;
                font-size: 14px;
            }
            
            .form-control {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e1e5e9;
                border-radius: 8px;
                font-size: 16px;
                transition: all 0.3s ease;
                background: #fff;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .input-with-icon {
                position: relative;
            }
            
            .input-with-icon .form-control {
                padding-left: 45px;
            }
            
            .input-icon {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #999;
                font-size: 16px;
            }
            
            .btn-login {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 10px;
            }
            
            .btn-login:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            }
            
            .btn-login:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }
            
            .login-footer {
                text-align: center;
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #e1e5e9;
            }
            
            .back-to-site {
                display: inline-flex;
                align-items: center;
                color: #667eea;
                text-decoration: none;
                font-weight: 500;
                margin-top: 15px;
                transition: all 0.3s ease;
            }
            
            .back-to-site:hover {
                color: #764ba2;
                transform: translateX(-5px);
            }
            
            .back-to-site i {
                margin-right: 8px;
            }
            
            .error-message, .success-message {
                padding: 12px 16px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: none;
                font-weight: 500;
            }
            
            .error-message {
                background: #fee;
                color: #c33;
                border: 1px solid #fcc;
            }
            
            .success-message {
                background: #efe;
                color: #363;
                border: 1px solid #cfc;
            }
            
            .alert {
                padding: 12px 16px;
                border-radius: 8px;
                margin-bottom: 20px;
                border: 1px solid;
                display: flex;
                align-items: center;
                gap: 12px;
                font-weight: 500;
            }
            
            .alert-danger {
                background: #fee;
                border-color: #fcc;
                color: #c33;
            }
            
            .alert ul {
                margin: 8px 0 0 0;
                padding-left: 20px;
            }
            
            .alert li {
                margin-bottom: 4px;
            }
            
            .alert li:last-child {
                margin-bottom: 0;
            }
            
            
            @media (max-width: 480px) {
                .login-container {
                    padding: 10px;
                }
                
                .login-card {
                    padding: 30px 20px;
                }
                
                .login-title {
                    font-size: 24px;
                }
            }
        </style>
    </head>
    <body>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MJSZT33R"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <img src="../images/phitsol-logo-black@2x.png" data-at2x="../images/phitsol-logo-black@2x.png" alt="Phitsol" class="default-logo" style="height: 50px; max-width: 300px; object-fit: contain;" width="174" height="46">
                    <h1 class="login-title">Partners Portal</h1>
                    <p class="login-subtitle">Partner Dashboard - For Business Users Only</p>
                    <p class="login-subtitle">If you are a personal user, <a href="https://store.phitsol.com/" target="_blank" rel="noopener noreferrer"><strong>Please click here.</strong></a></p>
                </div>
                
                
                <!-- Error/Success Messages -->
                <div class="error-message" id="errorMessage"></div>
                <div class="success-message" id="successMessage"></div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Login Error!</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form id="phpLoginForm" method="POST" action="login.php" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username or Email</label>
                        <div class="input-with-icon">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Enter your username or email" required autocomplete="username"
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                        <div class="invalid-feedback">
                            Please enter your username or email.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-with-icon">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required autocomplete="current-password">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                        <div class="invalid-feedback">
                            Please enter your password.
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In to Partners Portal
                    </button>
                </form>
                
                
                <div class="login-footer">
                    <p>Don't have an account? <a href="services-partners.html">Contact us to become a partner</a></p>
                </div>
                
                <a href="index.html" class="back-to-site">
                    <i class="feather icon-feather-arrow-left"></i>
                    Back to Website
                </a>
            </div>
        </div>

        <!-- javascript libraries -->
        <script type="text/javascript" src="../js/jquery.js"></script>
        <script type="text/javascript" src="../js/vendors.min.js"></script>
        <script type="text/javascript" src="../js/main.js"></script>
        <script>
        // Login Configuration
        
        // Utility functions
        function showError(message) {
            const errorMsg = $('#errorMessage');
            errorMsg.text(message).show();
            $('#successMessage').hide();
        }
        
        function showSuccess(message) {
            const successMsg = $('#successMessage');
            successMsg.text(message).show();
            $('#errorMessage').hide();
        }
        
        function hideMessages() {
            $('#errorMessage').hide();
            $('#successMessage').hide();
        }
        
        function setLoadingState(button, isLoading, text) {
            button.prop('disabled', isLoading).text(text);
        }
        
        
        $(document).ready(function() {
            console.log('Partners Portal Login page loaded');
            
            // Form validation for PHP form
            (function() {
                'use strict';
                window.addEventListener('load', function() {
                    var forms = document.getElementsByClassName('needs-validation');
                    var validation = Array.prototype.filter.call(forms, function(form) {
                        form.addEventListener('submit', function(event) {
                            if (form.checkValidity() === false) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
                }, false);
            })();
        });
        </script>
    </body>
</html>

