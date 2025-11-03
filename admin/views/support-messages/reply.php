<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php?action=login');
    exit;
}

use App\Models\SupportMessage;

$errors = [];
$success = false;

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = trim($_POST['reply'] ?? '');
    
    // Validate input
    if (empty($reply)) {
        $errors[] = 'Reply message is required';
    }
    
    // If no validation errors, add reply
    if (empty($errors)) {
        try {
            $supportMessageModel->addAdminReply($messageId, $reply);
            $success = true;
            
            // Redirect after successful reply
            header('Location: index.php?success=1');
            exit;
        } catch (\Exception $e) {
            error_log('Admin reply error: ' . $e->getMessage());
            $errors[] = 'An error occurred while sending the reply. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Support Message - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        .message-details {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .reply-form {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 25px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-replied {
            background: #d4edda;
            color: #155724;
        }
        
        .purpose-badge {
            background: var(--primary-color);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'views/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-reply me-2"></i>Reply to Support Message</h2>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Messages
                    </a>
                </div>
                
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
                
                <!-- Message Details -->
                <div class="message-details">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><?php echo htmlspecialchars($message['subject']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($message['user_name']); ?> 
                                (<?php echo htmlspecialchars($message['user_email']); ?>)
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-clock me-1"></i>
                                Received: <?php 
                                    if (is_object($message['created_at']) && method_exists($message['created_at'], 'toDateTime')) {
                                        echo $message['created_at']->toDateTime()->format('F d, Y \a\t g:i A');
                                    } else {
                                        echo date('F d, Y \a\t g:i A', strtotime($message['created_at']));
                                    }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="status-badge status-<?php echo $message['status']; ?>">
                                <?php echo ucfirst($message['status']); ?>
                            </span>
                            <span class="purpose-badge ms-2">
                                <?php echo htmlspecialchars($message['purpose']); ?>
                            </span>
                            <?php if (!empty($message['subcategory'])): ?>
                                <span class="badge bg-secondary ms-1">
                                    <?php echo htmlspecialchars($message['subcategory']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Original Message:</h6>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                    
                    <?php if ($message['status'] === 'replied' && !empty($message['admin_reply'])): ?>
                        <hr>
                        <h6 class="text-success">
                            <i class="fas fa-reply me-1"></i>Previous Reply:
                        </h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($message['admin_reply'])); ?></p>
                        <small class="text-muted">
                            Replied on: <?php 
                                if (is_object($message['admin_reply_date']) && method_exists($message['admin_reply_date'], 'toDateTime')) {
                                    echo $message['admin_reply_date']->toDateTime()->format('F d, Y \a\t g:i A');
                                } else {
                                    echo date('F d, Y \a\t g:i A', strtotime($message['admin_reply_date']));
                                }
                            ?>
                        </small>
                    <?php endif; ?>
                </div>
                
                <!-- Reply Form -->
                <div class="reply-form">
                    <h5 class="mb-3">
                        <i class="fas fa-edit me-2"></i>
                        <?php echo $message['status'] === 'replied' ? 'Edit Reply' : 'Send Reply'; ?>
                    </h5>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="reply" class="form-label">Your Reply *</label>
                            <textarea class="form-control" id="reply" name="reply" rows="8" 
                                      placeholder="Type your reply here..." required><?php echo htmlspecialchars($_POST['reply'] ?? $message['admin_reply'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                <?php echo $message['status'] === 'replied' ? 'Update Reply' : 'Send Reply'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 