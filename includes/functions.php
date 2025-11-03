<?php
/**
 * Utility Functions for PHITSOL Partners Portal
 * Common helper functions used throughout the application
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    
    // Check for at least one uppercase, lowercase, and number
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'USD') {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M j, Y') {
    if (!$date) return '';
    
    // Handle array input (from MongoDB)
    if (is_array($date)) {
        // If it's an array, try to extract a date value
        if (isset($date['date'])) {
            $date = $date['date'];
        } elseif (isset($date['$date'])) {
            $date = $date['$date'];
        } elseif (isset($date['timestamp'])) {
            $date = $date['timestamp'];
        } elseif (isset($date['sec'])) {
            // Handle MongoDB timestamp format
            $date = date('Y-m-d H:i:s', $date['sec']);
        } elseif (count($date) > 0) {
            // Try to get the first value if it's a simple array
            $firstValue = reset($date);
            if (is_string($firstValue) || is_numeric($firstValue)) {
                $date = $firstValue;
            } else {
                // If we can't find a valid date in the array, return empty
                return '';
            }
        } else {
            // If we can't find a date in the array, return empty
            return '';
        }
    }
    
    // Handle MongoDB BSON UTCDateTime objects
    if ($date instanceof MongoDB\BSON\UTCDateTime) {
        $date = $date->toDateTime()->format('Y-m-d H:i:s');
    }
    
    // If it's still not a string, handle it appropriately
    if (!is_string($date)) {
        if (is_array($date)) {
            // If it's still an array, return empty
            return '';
        } elseif (is_numeric($date)) {
            // If it's a numeric timestamp, use it directly
            return date($format, $date);
        } else {
            // For other types, try to convert to string
            $date = (string) $date;
        }
    }
    
    if (is_string($date)) {
        $date = strtotime($date);
    }
    
    return $date ? date($format, $date) : '';
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    if (!$datetime) return '';
    
    // Handle array input (from MongoDB)
    if (is_array($datetime)) {
        // If it's an array, try to extract a date value
        if (isset($datetime['date'])) {
            $datetime = $datetime['date'];
        } elseif (isset($datetime['$date'])) {
            $datetime = $datetime['$date'];
        } else {
            // If we can't find a date in the array, return empty
            return '';
        }
    }
    
    // Handle MongoDB BSON UTCDateTime objects
    if ($datetime instanceof MongoDB\BSON\UTCDateTime) {
        $datetime = $datetime->toDateTime()->format('Y-m-d H:i:s');
    }
    
    if (is_string($datetime)) {
        $datetime = strtotime($datetime);
    }
    
    return $datetime ? date($format, $datetime) : '';
}

/**
 * Time ago function
 */
function timeAgo($datetime) {
    if (is_string($datetime)) {
        $datetime = strtotime($datetime);
    }
    
    $time = time() - $datetime;
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' minutes ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 2592000) return floor($time / 86400) . ' days ago';
    if ($time < 31536000) return floor($time / 2592000) . ' months ago';
    
    return floor($time / 31536000) . ' years ago';
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status, $type = 'status') {
    $status = strtolower($status);
    $badgeClass = '';
    
    switch ($type) {
        case 'status':
            switch ($status) {
                case 'completed':
                case 'active':
                case 'approved':
                    $badgeClass = 'status-completed';
                    break;
                case 'processing':
                case 'in progress':
                case 'pending':
                    $badgeClass = 'status-processing';
                    break;
                case 'cancelled':
                case 'rejected':
                case 'inactive':
                    $badgeClass = 'status-pending';
                    break;
                default:
                    $badgeClass = 'status-processing';
            }
            break;
            
        case 'priority':
            switch ($status) {
                case 'high':
                case 'urgent':
                    $badgeClass = 'priority-high';
                    break;
                case 'medium':
                case 'normal':
                    $badgeClass = 'priority-medium';
                    break;
                case 'low':
                    $badgeClass = 'priority-low';
                    break;
                default:
                    $badgeClass = 'priority-medium';
            }
            break;
    }
    
    return '<span class="status-badge ' . $badgeClass . '">' . 
           ucfirst($status) . '</span>';
}

/**
 * Pagination helper
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';
    
    $pagination = '<nav class="pagination">';
    $pagination .= '<ul class="pagination-list">';
    
    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-link">Previous</a></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $pagination .= '<li><a href="' . $baseUrl . '?page=1" class="pagination-link">1</a></li>';
        if ($start > 2) {
            $pagination .= '<li><span class="pagination-ellipsis">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $activeClass = ($i === $currentPage) ? ' active' : '';
        $pagination .= '<li><a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link' . $activeClass . '">' . $i . '</a></li>';
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $pagination .= '<li><span class="pagination-ellipsis">...</span></li>';
        }
        $pagination .= '<li><a href="' . $baseUrl . '?page=' . $totalPages . '" class="pagination-link">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-link">Next</a></li>';
    }
    
    $pagination .= '</ul>';
    $pagination .= '</nav>';
    
    return $pagination;
}

/**
 * File upload helper
 */
function handleFileUpload($file, $allowedTypes = null, $maxSize = null) {
    if ($allowedTypes === null) {
        $allowedTypes = UPLOAD_ALLOWED_TYPES;
    }
    
    if ($maxSize === null) {
        $maxSize = UPLOAD_MAX_SIZE;
    }
    
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'No file uploaded'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'File too large'];
        default:
            return ['success' => false, 'message' => 'Unknown upload error'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    $fileName = generateRandomString(16) . '.' . $fileExtension;
    $filePath = UPLOAD_PATH . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
    
    return [
        'success' => true,
        'filename' => $fileName,
        'path' => $filePath,
        'size' => $file['size']
    ];
}

/**
 * Send email notification
 */
function sendEmail($to, $subject, $message, $isHTML = true) {
    if (!NOTIFICATION_EMAIL) {
        return false;
    }
    
    $headers = [
        'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
        'Reply-To: ' . SMTP_FROM_EMAIL,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    if ($isHTML) {
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
    } else {
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    }
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Log user activity
 */
function logActivity($action, $details = '') {
    $user = getCurrentUser();
    $userId = $user ? $user['_id'] : 'anonymous';
    $userEmail = $user ? $user['email'] : 'anonymous';
    
    $logData = [
        'user_id' => $userId,
        'user_email' => $userEmail,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    logMessage('INFO', 'User Activity: ' . $action, $logData);
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    // This would typically fetch from your database
    // For now, returning mock data
    return [
        'total_orders' => 47,
        'pending_orders' => 3,
        'completed_orders' => 44,
        'total_revenue' => 125430.50,
        'active_products' => 12,
        'support_tickets' => 2,
        'new_messages' => 1,
        'pending_approvals' => 0
    ];
}

/**
 * Get recent orders
 */
function getRecentOrders($limit = 5) {
    // This would typically fetch from your database
    // For now, returning mock data
    return [
        ['id' => 'ORD-001', 'product' => 'Industrial Printer', 'status' => 'Completed', 'amount' => 2500.00, 'date' => '2024-01-15'],
        ['id' => 'ORD-002', 'product' => '3D Scanner', 'status' => 'Processing', 'amount' => 1800.00, 'date' => '2024-01-14'],
        ['id' => 'ORD-003', 'product' => 'CAD Software License', 'status' => 'Pending', 'amount' => 450.00, 'date' => '2024-01-13'],
        ['id' => 'ORD-004', 'product' => 'Technical Support', 'status' => 'Completed', 'amount' => 200.00, 'date' => '2024-01-12'],
        ['id' => 'ORD-005', 'product' => 'Maintenance Contract', 'status' => 'Completed', 'amount' => 1200.00, 'date' => '2024-01-11']
    ];
}

/**
 * Get support tickets
 */
function getSupportTickets($limit = 5) {
    // This would typically fetch from your database
    // For now, returning mock data
    return [
        ['id' => 'TKT-001', 'subject' => 'Printer Installation Issue', 'status' => 'Open', 'priority' => 'High', 'date' => '2024-01-15'],
        ['id' => 'TKT-002', 'subject' => 'Software License Renewal', 'status' => 'In Progress', 'priority' => 'Medium', 'date' => '2024-01-14']
    ];
}

/**
 * Check if feature is enabled
 */
function isFeatureEnabled($feature) {
    return featureEnabled($feature);
}

/**
 * Get user initials for avatar
 */
function getUserInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Generate breadcrumb navigation
 */
function generateBreadcrumb($items) {
    $breadcrumb = '<nav class="breadcrumb">';
    $breadcrumb .= '<ol class="breadcrumb-list">';
    
    foreach ($items as $index => $item) {
        $isLast = ($index === count($items) - 1);
        
        if ($isLast) {
            $breadcrumb .= '<li class="breadcrumb-item active">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $breadcrumb .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $breadcrumb .= '</ol>';
    $breadcrumb .= '</nav>';
    
    return $breadcrumb;
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit();
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    
    return null;
}
?>
