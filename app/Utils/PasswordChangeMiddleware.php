<?php

namespace App\Utils;

use App\Models\User;

/**
 * Password Change Enforcement Middleware
 * 
 * This middleware checks if admin users need to change their password
 * and redirects them to the password change page if required.
 */
class PasswordChangeMiddleware
{
    private $userModel;
    private $auditLogger;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->auditLogger = new AuditLogger();
    }
    
    /**
     * Check if user needs to change password and redirect if necessary
     * 
     * @param string $userId User ID
     * @param string $currentPage Current page being accessed
     * @return bool True if access is allowed, false if redirect needed
     */
    public function checkPasswordChangeRequired($userId, $currentPage = '')
    {
        try {
            // Skip check for password change page itself
            if (strpos($currentPage, 'change-password') !== false || 
                strpos($currentPage, 'password-change') !== false) {
                return true;
            }
            
            // Skip check for logout
            if (strpos($currentPage, 'logout') !== false) {
                return true;
            }
            
            // Check if user requires password change
            if (!$this->userModel->requiresPasswordChange($userId)) {
                return true;
            }
            
            // Get user security status
            $securityStatus = $this->userModel->getSecurityStatus($userId);
            if (!$securityStatus) {
                return true; // Allow access if we can't determine status
            }
            
            // Log the redirect attempt
            $this->auditLogger->logEvent('password_change_redirect', $userId, 
                "User redirected to password change page", [
                    'current_page' => $currentPage,
                    'is_default_password' => $securityStatus['is_default_password'],
                    'is_first_login' => $securityStatus['is_first_login']
                ]);
            
            // Redirect to password change page
            $this->redirectToPasswordChange($securityStatus);
            return false;
            
        } catch (\Exception $e) {
            error_log("Password change middleware error: " . $e->getMessage());
            return true; // Allow access on error
        }
    }
    
    /**
     * Redirect user to password change page
     */
    private function redirectToPasswordChange($securityStatus)
    {
        $message = '';
        
        if ($securityStatus['is_default_password']) {
            $message = 'You must change your default password before continuing.';
        } elseif ($securityStatus['is_first_login']) {
            $message = 'You must change your password on first login.';
        } elseif ($securityStatus['password_change_required']) {
            $message = 'You must change your password before continuing.';
        } else {
            $message = 'Password change required.';
        }
        
        // Store message in session
        $_SESSION['password_change_message'] = $message;
        $_SESSION['password_change_required'] = true;
        
        // Redirect to password change page
        $redirectUrl = 'change-password.php';
        if (isset($_GET['redirect'])) {
            $redirectUrl .= '?redirect=' . urlencode($_GET['redirect']);
        }
        
        header("Location: {$redirectUrl}");
        exit;
    }
    
    /**
     * Get password change requirements for display
     * 
     * @return array Password requirements
     */
    public function getPasswordRequirements()
    {
        return [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special_chars' => true,
            'prevent_reuse' => true,
            'history_count' => 5
        ];
    }
    
    /**
     * Validate password against requirements
     * 
     * @param string $password Password to validate
     * @param string $userId User ID (for history check)
     * @return array Validation result
     */
    public function validatePassword($password, $userId = null)
    {
        $requirements = $this->getPasswordRequirements();
        $errors = [];
        
        // Basic strength validation
        if (strlen($password) < $requirements['min_length']) {
            $errors[] = "Password must be at least {$requirements['min_length']} characters long";
        }
        
        if ($requirements['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if ($requirements['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if ($requirements['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if ($requirements['require_special_chars'] && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        // Check for weak passwords
        $weakPasswords = [
            'password', '123456', 'admin', 'administrator', 'root',
            'user', 'guest', 'test', 'demo', 'sample', 'default'
        ];
        
        if (in_array(strtolower($password), $weakPasswords)) {
            $errors[] = "Password is too common and easily guessable";
        }
        
        // Check password history if user ID provided
        if ($userId && $requirements['prevent_reuse']) {
            $user = $this->userModel->getById($userId);
            if ($user && isset($user['password_history'])) {
                foreach ($user['password_history'] as $oldHash) {
                    if (password_verify($password, $oldHash)) {
                        $errors[] = "Cannot reuse a previous password";
                        break;
                    }
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculatePasswordStrength($password)
        ];
    }
    
    /**
     * Calculate password strength score
     * 
     * @param string $password Password to score
     * @return int Strength score (0-100)
     */
    private function calculatePasswordStrength($password)
    {
        $score = 0;
        $length = strlen($password);
        
        // Length scoring
        if ($length >= 8) $score += 20;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;
        
        // Character variety scoring
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 10;
        if (preg_match('/[0-9]/', $password)) $score += 10;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 20;
        
        // Pattern penalties
        if (preg_match('/(.)\1{2,}/', $password)) $score -= 10; // Repeated characters
        if (preg_match('/123|abc|qwe/i', $password)) $score -= 15; // Sequential patterns
        
        return max(0, min(100, $score));
    }
    
    /**
     * Get password strength description
     * 
     * @param int $score Password strength score
     * @return string Strength description
     */
    public function getPasswordStrengthDescription($score)
    {
        if ($score < 30) return 'Very Weak';
        if ($score < 50) return 'Weak';
        if ($score < 70) return 'Fair';
        if ($score < 90) return 'Good';
        return 'Strong';
    }
    
    /**
     * Clear password change requirements from session
     */
    public function clearPasswordChangeRequirements()
    {
        unset($_SESSION['password_change_message']);
        unset($_SESSION['password_change_required']);
    }
    
    /**
     * Check if current session has password change requirements
     * 
     * @return bool True if password change is required
     */
    public function isPasswordChangeRequired()
    {
        return isset($_SESSION['password_change_required']) && $_SESSION['password_change_required'];
    }
    
    /**
     * Get password change message from session
     * 
     * @return string Message or empty string
     */
    public function getPasswordChangeMessage()
    {
        return $_SESSION['password_change_message'] ?? '';
    }
}
