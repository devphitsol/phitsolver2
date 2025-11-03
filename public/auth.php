<?php
// Add custom PHP include path
ini_set("include_path", '/home/qiimy7odbu3s/php:' . ini_get("include_path"));

require_once '../config/session.php';

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Load Composer autoloader
require_once '../vendor/autoload.php';

use App\Models\User;

$errors = [];
$success = '';
$form = $_GET['form'] ?? 'login';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        if ($form === 'login') {
            // Handle login
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username)) {
                $errors[] = 'Username or email is required';
            }
            if (empty($password)) {
                $errors[] = 'Password is required';
            }

            if (empty($errors)) {
                try {
                    $userModel = new User();
                    $user = $userModel->authenticate($username, $password);
                    
                    if ($user) {
                        if ($user['status'] !== 'active') {
                            if ($user['status'] === 'pending') {
                                $errors[] = 'Your account is pending approval. Please wait for admin approval.';
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
                    error_log('Login error: ' . $e->getMessage());
                    $errors[] = 'An error occurred during login. Please try again.';
                }
            }
        } elseif ($form === 'register') {
            // Handle registration
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? 'employee';

            // Validation
            if (empty($first_name)) $errors[] = 'First name is required';
            if (empty($last_name)) $errors[] = 'Last name is required';
            if (empty($email)) $errors[] = 'Email is required';
            if (empty($username)) $errors[] = 'Username is required';
            if (empty($password)) $errors[] = 'Password is required';
            if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
            if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';

            if (empty($errors)) {
                try {
                    $userModel = new User();
                    
                    // Check if username or email already exists
                    if ($userModel->getByUsername($username)) {
                        $errors[] = 'Username already exists';
                    }
                    if ($userModel->getByEmail($email)) {
                        $errors[] = 'Email already exists';
                    }

                    if (empty($errors)) {
                        // Create user
                        $userData = [
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'username' => $username,
                            'password' => password_hash($password, PASSWORD_DEFAULT),
                            'role' => $role,
                            'status' => $role === 'business' ? 'pending' : 'active'
                        ];

                        $result = $userModel->create($userData);
                        
                        if ($result) {
                            $success = 'Registration successful! ' . 
                                     ($role === 'business' ? 'Your account is pending approval.' : 'You can now login.');
                            $form = 'login'; // Switch to login form
                        } else {
                            $errors[] = 'Registration failed. Please try again.';
                        }
                    }
                } catch (\Exception $e) {
                    error_log('Registration error: ' . $e->getMessage());
                    $errors[] = 'An error occurred during registration. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $form === 'login' ? 'Login' : 'Register'; ?> - PHITSOL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .auth-header {
            background: var(--primary-color);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .auth-header .phitsol-logo {
            width: 120px;
            height: auto;
            margin-bottom: 20px;
        }
        
        .auth-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .auth-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .auth-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .btn-auth {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-auth:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .auth-switch {
            text-align: center;
            margin-top: 20px;
        }
        
        .auth-switch a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-switch a:hover {
            text-decoration: underline;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-home a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-to-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="assets/img/logo_white.png" alt="PHITSOL Logo" class="phitsol-logo">
                <h2><?php echo $form === 'login' ? 'Login' : 'Register'; ?></h2>
                <p><?php echo $form === 'login' ? 'Welcome back! Please login to your account.' : 'Create your account to get started.'; ?></p>
            </div>
            
            <div class="auth-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Error!</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($form === 'login'): ?>
                    <!-- Login Form -->
                    <form method="POST" action="auth.php?form=login">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Username or Email" required 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            <label for="username">Username or Email</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        
                        <button type="submit" class="btn btn-auth">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login
                        </button>
                    </form>
                    
                    <div class="auth-switch">
                        <p>Don't have an account? <a href="auth.php?form=register">Register here</a></p>
                    </div>
                <?php else: ?>
                    <!-- Registration Form -->
                    <form method="POST" action="auth.php?form=register">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           placeholder="First Name" required 
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                                    <label for="first_name">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           placeholder="Last Name" required 
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                                    <label for="last_name">Last Name</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Email" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <label for="email">Email</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Username" required 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            <label for="username">Username</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm Password" required>
                            <label for="confirm_password">Confirm Password</label>
                        </div>
                        
                        <div class="form-floating">
                            <select class="form-control" id="role" name="role" required>
                                <option value="employee" <?php echo ($_POST['role'] ?? '') === 'employee' ? 'selected' : ''; ?>>Employee</option>
                                <option value="business" <?php echo ($_POST['role'] ?? '') === 'business' ? 'selected' : ''; ?>>Business Customer</option>
                            </select>
                            <label for="role">Account Type</label>
                        </div>
                        
                        <button type="submit" class="btn btn-auth">
                            <i class="fas fa-user-plus me-2"></i>
                            Register
                        </button>
                    </form>
                    
                    <div class="auth-switch">
                        <p>Already have an account? <a href="auth.php?form=login">Login here</a></p>
                    </div>
                <?php endif; ?>
                
                <div class="back-to-home">
                    <a href="/">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 