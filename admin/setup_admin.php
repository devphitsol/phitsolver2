<?php
// Add custom PHP include path
ini_set("include_path", '/home/qiimy7odbu3s/php:' . ini_get("include_path"));

// Admin Dashboard First-Time Setup Script
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

use App\Models\User;

$errors = [];
$success = false;

// Check if admin users already exist
try {
    $userModel = new User();
    $adminCount = $userModel->getAdminCount();
    
    if ($adminCount > 0) {
        // Redirect to login if admin already exists
        // Add a small delay to show message if accessed directly
        if (!isset($_GET['redirect'])) {
            header('Location: index.php?action=login&message=admin_exists');
        } else {
            header('Location: index.php?action=login');
        }
        exit;
    }
} catch (Exception $e) {
    error_log('Admin setup database error: ' . $e->getMessage());
    $errors[] = 'Database connection error: ' . $e->getMessage();
}

// Load default admin configuration
$defaultConfig = require __DIR__ . '/../config/default_admin.php';
$defaultAdmin = $defaultConfig['default_admin'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    
    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $existingUser = $userModel->getByUsername($username);
            if ($existingUser) {
                $errors[] = 'Username already exists';
            }
            
            $existingEmail = $userModel->getByEmail($email);
            if ($existingEmail) {
                $errors[] = 'Email already exists';
            }
        } catch (Exception $e) {
            $errors[] = 'Error checking existing users: ' . $e->getMessage();
        }
    }
    
    // Create admin user
    if (empty($errors)) {
        try {
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $password, // Will be hashed in User model
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => 'admin',
                'status' => 'active',
                'is_default_password' => false, // Custom password, not default
                'password_change_required' => false, // No forced change for custom setup
                'password_history' => [],
                'login_count' => 0,
                'is_first_login' => true,
                'created_by' => 'manual_setup',
                'notes' => 'Admin account created through setup form'
            ];
            
            $result = $userModel->create($userData);
            
            if ($result) {
                $success = true;
                // Auto-login the created admin
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = (string) $result;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_role'] = 'admin';
                
                // Set welcome message for first-time admin
                $_SESSION['success'] = 'Welcome to PHITSOL Admin Dashboard! Your administrator account has been created successfully.';
                
                // Log the admin creation
                $auditLogger = new \App\Utils\AuditLogger();
                $auditLogger->logEvent('admin_created_manual', (string)$result, 
                    "Admin account created through setup form: {$username}", [
                        'username' => $username,
                        'email' => $email,
                        'created_by' => 'manual_setup'
                    ]);
                
                // Redirect to dashboard after 3 seconds
                header('refresh:3;url=index.php');
            } else {
                $errors[] = 'Failed to create admin user';
            }
        } catch (Exception $e) {
            $errors[] = 'Error creating admin user: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - PHITSOL Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
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
        
        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .setup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .setup-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .setup-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-setup {
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
        
        .btn-setup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
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
        
        .setup-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .setup-info h6 {
            color: #1976d2;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .setup-info ul {
            margin: 0;
            padding-left: 1.2rem;
        }
        
        .setup-info li {
            color: #424242;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="setup-header">
            <h2><i class="fas fa-user-shield"></i> Admin Setup</h2>
            <p>Create your first administrator account</p>
        </div>
        
        <div class="card-body">
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <strong>Admin account created successfully!</strong><br>
                    You will be redirected to the dashboard in a few seconds...
                </div>
            <?php else: ?>
                <div class="setup-info">
                    <h6><i class="fas fa-info-circle"></i> Setup Information</h6>
                    <ul>
                        <li>This will create the first administrator account</li>
                        <li>You'll have full access to all admin features</li>
                        <li>Make sure to use a strong password</li>
                        <li>You can create additional admin users later</li>
                    </ul>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-setup">
                        <i class="fas fa-user-shield me-2"></i>
                        Create Admin Account
                    </button>
                </form>
            <?php endif; ?>
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
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = getPasswordStrength(password);
            
            // You can add visual feedback here if needed
        });
        
        function getPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            return strength;
        }
    </script>
</body>
</html>
