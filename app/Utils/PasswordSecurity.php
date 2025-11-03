<?php

namespace App\Utils;

/**
 * Password Security Utility Class
 * 
 * Handles secure password operations including hashing, validation,
 * and security checks for admin accounts.
 */
class PasswordSecurity
{
    /**
     * Hash a password using PHP's password_hash with PASSWORD_DEFAULT
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify a password against its hash
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Validate password strength according to security requirements
     * 
     * @param string $password Password to validate
     * @param array $requirements Security requirements
     * @return array Validation result with success flag and errors
     */
    public static function validatePasswordStrength($password, $requirements = [])
    {
        $errors = [];
        $minLength = $requirements['min_length'] ?? 8;
        $requireUppercase = $requirements['require_uppercase'] ?? true;
        $requireLowercase = $requirements['require_lowercase'] ?? true;
        $requireNumbers = $requirements['require_numbers'] ?? true;
        $requireSpecialChars = $requirements['require_special_chars'] ?? true;
        
        // Check minimum length
        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long";
        }
        
        // Check for uppercase letters
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        // Check for lowercase letters
        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        // Check for numbers
        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        // Check for special characters
        if ($requireSpecialChars && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        // Check for common weak passwords
        $weakPasswords = [
            'password', '123456', 'admin', 'administrator', 'root',
            'user', 'guest', 'test', 'demo', 'sample', 'default'
        ];
        
        if (in_array(strtolower($password), $weakPasswords)) {
            $errors[] = "Password is too common and easily guessable";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Generate a secure random password
     * 
     * @param int $length Password length
     * @param bool $includeSpecialChars Include special characters
     * @return string Generated password
     */
    public static function generateSecurePassword($length = 12, $includeSpecialChars = true)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        if ($includeSpecialChars) {
            $chars .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
        }
        
        $password = '';
        $charsLength = strlen($chars);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $charsLength - 1)];
        }
        
        return $password;
    }
    
    /**
     * Check if password is in history (to prevent reuse)
     * 
     * @param string $newPassword New password
     * @param array $passwordHistory Array of previous password hashes
     * @return bool True if password was used before
     */
    public static function isPasswordInHistory($newPassword, $passwordHistory)
    {
        foreach ($passwordHistory as $oldHash) {
            if (self::verifyPassword($newPassword, $oldHash)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get password strength score (0-100)
     * 
     * @param string $password Password to score
     * @return int Strength score
     */
    public static function getPasswordStrength($password)
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
    public static function getPasswordStrengthDescription($score)
    {
        if ($score < 30) return 'Very Weak';
        if ($score < 50) return 'Weak';
        if ($score < 70) return 'Fair';
        if ($score < 90) return 'Good';
        return 'Strong';
    }
}
