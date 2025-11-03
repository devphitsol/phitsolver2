<?php
/**
 * Authentication Helper Functions for PHITSOL Partners Portal
 * Handles user authentication, session management, and API communication
 */

require_once 'config/database.php';

class AuthHelper {
    private $apiBaseUrl;
    private $timeout;
    
    public function __construct() {
        $this->apiBaseUrl = API_BASE_URL;
        $this->timeout = API_TIMEOUT;
    }
    
    /**
     * Make authenticated API request
     */
    public function makeAPIRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->apiBaseUrl . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        // Add authorization header if user is logged in
        if ($this->isLoggedIn()) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['phitsol_token'];
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            logMessage('ERROR', 'cURL Error: ' . $error);
            return ['success' => false, 'message' => 'Connection error'];
        }
        
        $decodedResponse = json_decode($response, true);
        
        // Handle authentication errors
        if ($httpCode === 401) {
            $this->logout();
            return ['success' => false, 'message' => 'Authentication required'];
        }
        
        return $decodedResponse ?: ['success' => false, 'message' => 'Invalid response'];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['phitsol_token']) && 
               isset($_SESSION['phitsol_user']) && 
               !empty($_SESSION['phitsol_token']);
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $_SESSION['phitsol_user'];
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        $data = [
            'email' => $email,
            'password' => $password
        ];
        
        $response = $this->makeAPIRequest('/auth/login', 'POST', $data);
        
        if ($response && $response['success']) {
            $_SESSION['phitsol_token'] = $response['data']['token'];
            $_SESSION['phitsol_refresh_token'] = $response['data']['refreshToken'];
            $_SESSION['phitsol_user'] = $response['data']['user'];
            $_SESSION['login_time'] = time();
            
            logMessage('INFO', 'User logged in: ' . $email);
            return true;
        }
        
        logMessage('WARNING', 'Failed login attempt: ' . $email);
        return false;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            // Call logout API
            $this->makeAPIRequest('/auth/logout', 'POST');
            
            // Clear session data
            unset($_SESSION['phitsol_token']);
            unset($_SESSION['phitsol_refresh_token']);
            unset($_SESSION['phitsol_user']);
            unset($_SESSION['login_time']);
            
            logMessage('INFO', 'User logged out');
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Refresh authentication token
     */
    public function refreshToken() {
        if (!isset($_SESSION['phitsol_refresh_token'])) {
            return false;
        }
        
        $data = [
            'refreshToken' => $_SESSION['phitsol_refresh_token']
        ];
        
        $response = $this->makeAPIRequest('/auth/refresh-token', 'POST', $data);
        
        if ($response && $response['success']) {
            $_SESSION['phitsol_token'] = $response['data']['token'];
            $_SESSION['phitsol_refresh_token'] = $response['data']['refreshToken'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        // Admin and super_admin have all permissions
        if (in_array($user['role'], ['admin', 'super_admin'])) {
            return true;
        }
        
        return in_array($permission, $user['permissions'] ?? []);
    }
    
    /**
     * Require authentication
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    /**
     * Require specific role
     */
    public function requireRole($role) {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            header('HTTP/1.1 403 Forbidden');
            die('Access denied. Insufficient permissions.');
        }
    }
    
    /**
     * Require specific permission
     */
    public function requirePermission($permission) {
        $this->requireAuth();
        
        if (!$this->hasPermission($permission)) {
            header('HTTP/1.1 403 Forbidden');
            die('Access denied. Insufficient permissions.');
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($profileData) {
        $response = $this->makeAPIRequest('/auth/profile', 'PUT', $profileData);
        
        if ($response && $response['success']) {
            $_SESSION['phitsol_user'] = $response['data']['user'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Change password
     */
    public function changePassword($currentPassword, $newPassword) {
        $data = [
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword
        ];
        
        $response = $this->makeAPIRequest('/auth/change-password', 'PUT', $data);
        return $response && $response['success'];
    }
    
    /**
     * Get user profile from API
     */
    public function getUserProfile() {
        $response = $this->makeAPIRequest('/auth/profile');
        
        if ($response && $response['success']) {
            $_SESSION['phitsol_user'] = $response['data']['user'];
            return $response['data']['user'];
        }
        
        return null;
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCSRF($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && 
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRF() {
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Check if session is expired
     */
    public function isSessionExpired() {
        if (!isset($_SESSION['login_time'])) {
            return true;
        }
        
        return (time() - $_SESSION['login_time']) > SESSION_LIFETIME;
    }
    
    /**
     * Auto-refresh session if needed
     */
    public function autoRefreshSession() {
        if ($this->isSessionExpired()) {
            $this->logout();
            return false;
        }
        
        // Refresh token if it's close to expiring
        if (isset($_SESSION['login_time']) && 
            (time() - $_SESSION['login_time']) > (SESSION_LIFETIME * 0.8)) {
            return $this->refreshToken();
        }
        
        return true;
    }
}

// Global helper functions
function isLoggedIn() {
    global $auth;
    return $auth->isLoggedIn();
}

function getCurrentUser() {
    global $auth;
    return $auth->getCurrentUser();
}

function requireAuth() {
    global $auth;
    $auth->requireAuth();
}

function requireRole($role) {
    global $auth;
    $auth->requireRole($role);
}

function requirePermission($permission) {
    global $auth;
    $auth->requirePermission($permission);
}

function hasRole($role) {
    global $auth;
    return $auth->hasRole($role);
}

function hasPermission($permission) {
    global $auth;
    return $auth->hasPermission($permission);
}

function generateCSRF() {
    global $auth;
    return $auth->generateCSRF();
}

function validateCSRF($token) {
    global $auth;
    return $auth->validateCSRF($token);
}

// Initialize auth helper
$auth = new AuthHelper();

// Auto-refresh session
$auth->autoRefreshSession();
?>
