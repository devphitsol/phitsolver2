<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?action=login');
    exit;
}

use App\Models\SupportMessage;

// Get message ID from URL
$messageId = $_GET['id'] ?? null;
if (!$messageId) {
    header('Location: index.php');
    exit;
}

$supportMessageModel = new SupportMessage();
$message = $supportMessageModel->getById($messageId);

if (!$message) {
    header('Location: index.php');
    exit;
}

// Handle deletion
try {
    $supportMessageModel->delete($messageId);
    header('Location: index.php?deleted=1');
    exit;
} catch (\Exception $e) {
    error_log('Delete support message error: ' . $e->getMessage());
    header('Location: index.php?error=1');
    exit;
}
?> 