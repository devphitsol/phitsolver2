<?php
/**
 * Admin Password Change Page
 * 
 * This page handles forced password changes for admin users,
 * including first login and default password changes.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

use App\Models\User;
use App\Utils\PasswordChangeMiddleware;
use App\Utils\AuditLogger;

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: index.php?action=login');
    exit;
}

$userId = $_SESSION['admin_user_id'] ?? null;
$username = $_SESSION['admin_username'] ?? 'Unknown';

if (!$userId) {
    header('Location: index.php?action=login');
    exit;
}

$userModel = new User();
$middleware = new PasswordChangeMiddleware();
$auditLogger = new AuditLogger();

$errors = [];
$success = false;
$passwordChangeMessage = $middleware->getPasswordChangeMessage();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Basic validation
    if (empty($newPassword)) {
        $errors[] = 'New password is required';
    }
    
    if (empty($confirmPassword)) {
        $errors[] = 'Password confirmation is required';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    // Validate password strength
    if (empty($errors)) {
        $validation = $middleware->validatePassword($newPassword, $userId);
        if (!$validation['valid']) {
            $errors = array_merge($errors, $validation['errors']);
        }
    }
    
    // Change password if validation passes
    if (empty($errors)) {
        $result = $userModel->changePassword($userId, '', $newPassword, true); // Forced change
        
        if ($result['success']) {
            $success = true;
            
            // Clear password change requirements from session
            $middleware->clearPasswordChangeRequirements();
            
            // Log successful password change
            $auditLogger->logEvent('forced_password_change_success', $userId, 
                "User {$username} successfully changed password", [
                    'username' => $username,
                    'change_type' => 'forced'
                ]);
            
            // Redirect after 3 seconds
            $redirectUrl = $_GET['redirect'] ?? 'index.php';
            header("refresh:3;url={$redirectUrl}");
            
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Get user security status
$securityStatus = $userModel->getSecurityStatus($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - PHITSOL Admin</title>
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
        
        .password-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .password-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .password-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .password-header p {
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
        
        .btn-change {
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
        
        .btn-change:hover {
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
        
        .password-requirements {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .password-requirements h6 {
            color: #1976d2;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 1.2rem;
        }
        
        .password-requirements li {
            color: #424242;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .strength-very-weak { background-color: #dc3545; width: 20%; }
        .strength-weak { background-color: #fd7e14; width: 40%; }
        .strength-fair { background-color: #ffc107; width: 60%; }
        .strength-good { background-color: #20c997; width: 80%; }
        .strength-strong { background-color: #28a745; width: 100%; }
        
        .security-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .security-warning h6 {
            color: #856404;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="password-card">
        <div class="password-header">
            <h2><i class="fas fa-key"></i> Change Password</h2>
            <p>Security requirement for user: <?php echo htmlspecialchars($username); ?></p>
        </div>
        
        <div class="card-body">
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <strong>Password changed successfully!</strong><br>
                    You will be redirected to the dashboard in a few seconds...
                </div>
            <?php else: ?>
                <?php if ($passwordChangeMessage): ?>
                    <div class="security-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Security Notice</h6>
                        <p><?php echo htmlspecialchars($passwordChangeMessage); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="password-requirements">
                    <h6><i class="fas fa-shield-alt"></i> Password Requirements</h6>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Contains uppercase and lowercase letters</li>
                        <li>Contains numbers and special characters</li>
                        <li>Cannot be a common password</li>
                        <li>Cannot reuse previous passwords</li>
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
                
                <form method="POST" id="passwordForm">
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                            <small class="text-muted" id="strengthText">Password strength will appear here</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-change">
                        <i class="fas fa-key me-2"></i>
                        Change Password
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let strengthClass = '';
            let strengthLabel = '';
            
            // Length check
            if (password.length >= 8) strength += 20;
            if (password.length >= 12) strength += 10;
            if (password.length >= 16) strength += 10;
            
            // Character variety
            if (/[a-z]/.test(password)) strength += 10;
            if (/[A-Z]/.test(password)) strength += 10;
            if (/[0-9]/.test(password)) strength += 10;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 20;
            
            // Pattern penalties
            if (/(.)\1{2,}/.test(password)) strength -= 10;
            if (/123|abc|qwe/i.test(password)) strength -= 15;
            
            strength = Math.max(0, Math.min(100, strength));
            
            if (strength < 30) {
                strengthClass = 'strength-very-weak';
                strengthLabel = 'Very Weak';
            } else if (strength < 50) {
                strengthClass = 'strength-weak';
                strengthLabel = 'Weak';
            } else if (strength < 70) {
                strengthClass = 'strength-fair';
                strengthLabel = 'Fair';
            } else if (strength < 90) {
                strengthClass = 'strength-good';
                strengthLabel = 'Good';
            } else {
                strengthClass = 'strength-strong';
                strengthLabel = 'Strong';
            }
            
            strengthBar.className = 'strength-bar ' + strengthClass;
            strengthText.textContent = 'Password strength: ' + strengthLabel;
        });
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
