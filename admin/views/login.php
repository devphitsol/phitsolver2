<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Admin Login - PHITSOL Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <style>
        /* Complete admin login styles */
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
            margin: 0;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header .phitsol-logo {
            height: 80px;
            width: auto;
            margin-bottom: 2rem;
            display: block;
            margin-left: auto;
            margin-right: auto;
            max-width: 100%;
            object-fit: contain;
            filter: brightness(1.2) contrast(1.1);
        }
        
        .login-header .phitsol-logo:hover {
            filter: brightness(1.3) contrast(1.2);
        }
        
        .login-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .input-group {
            margin-bottom: 1rem;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            color: #6c757d;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-left: none;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .alert i {
            margin-right: 0.5rem;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .mt-4 {
            margin-top: 1.5rem !important;
        }
        
        small {
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .login-card {
                margin: 1rem;
            }
            
            .login-header {
                padding: 1.5rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
        

        
        /* Admin Setup Section Styles */
        .admin-setup-section {
            border-top: 1px solid #e9ecef;
            padding-top: 1.5rem;
            margin-top: 1rem;
        }
        
        .admin-setup-section p {
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }
        
        .admin-setup-btn {
            border: 2px solid #ffc107;
            color: #856404;
            background: transparent;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .admin-setup-btn:hover {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
            text-decoration: none;
        }
        
        .admin-setup-btn i {
            font-size: 0.8rem;
        }
        
        
        /* Home Link Styles */
        .home-link {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            background: #f8f9fa;
        }
        
        .home-link:hover {
            color: #667eea;
            background: #e3f2fd;
            border-color: #667eea;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }
        
        .home-link i {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img 
                src="assets/img/logo_white.png?v=<?php echo uniqid(); ?>"
                alt="PHITSOL Logo"
                class="phitsol-logo"
                id="phitsol-logo"
                style="height: 80px;"
            >
        </div>
        
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_GET['message']) && $_GET['message'] === 'admin_exists'): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Admin account already exists!</strong><br>
                    Please log in with your existing admin credentials.
                </div>
            <?php endif; ?>

            <form method="POST" action="?action=login">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                </div>
                
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                
                <button type="submit" name="login" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Login
                </button>
            </form>
            
            

            
            <?php
            // Check if admin user exists
            try {
                require_once __DIR__ . '/../../vendor/autoload.php';
                require_once __DIR__ . '/../../config/database.php';
                $userModel = new App\Models\User();
                $adminUsers = $userModel->getAdminCount();
                
                if ($adminUsers == 0) {
                    echo '<div class="text-center mt-3">';
                    echo '<div class="admin-setup-section">';
                    echo '<p class="text-muted mb-2">First time setup?</p>';
                    echo '<a href="setup_admin.php" class="btn btn-outline-warning btn-sm admin-setup-btn">';
                    echo '<i class="fas fa-user-shield me-1"></i>';
                    echo 'Setup Admin Account';
                    echo '</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                // Silently handle errors
            }
            ?>
        </div>
        
        <!-- Home Link Section -->
        <div class="text-center mt-3 mb-3">
            <a href="index.php" class="home-link">
                <i class="fas fa-home me-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Force cache refresh for this page
        if (performance.navigation.type === 1) {
            // Page was refreshed, clear any cached data
            localStorage.removeItem('loginCache');
        }
        
        // Logo is set to white for admin pages
        // No switching needed as admin pages use dark backgrounds
        
        // Force reload logo if not displaying correctly
        document.addEventListener('DOMContentLoaded', function() {
            const logo = document.querySelector('.phitsol-logo');
            if (logo) {
                // Force reload the logo image
                const currentSrc = logo.src;
                logo.src = '';
                logo.src = currentSrc;
                
                // Ensure logo is visible
                logo.style.visibility = 'visible';
                logo.style.opacity = '1';
                logo.style.display = 'block';
            }
            
            // Add timestamp to prevent caching
            const currentTime = new Date().getTime();
        });
    </script>
</body>
</html> 