<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php?action=login');
    exit;
}

use App\Models\SupportMessage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Messages - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        .message-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .message-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .message-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            border-radius: 10px 10px 0 0;
        }
        
        .message-body {
            padding: 20px;
        }
        
        .message-footer {
            background: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 10px 10px;
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
        
        .reply-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: #6c757d;
            font-weight: 500;
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
                    <h2><i class="fas fa-headset me-2"></i>Support Messages</h2>
                    <a href="../index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
                
                <!-- Success/Error Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> Reply sent successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> Support message deleted successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Error!</strong> An error occurred while processing your request.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-number"><?php echo $totalCount; ?></div>
                            <div class="stats-label">Total Messages</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-number text-warning"><?php echo $pendingCount; ?></div>
                            <div class="stats-label">Pending</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-number text-success"><?php echo $repliedCount; ?></div>
                            <div class="stats-label">Replied</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-number text-info"><?php echo $totalCount > 0 ? round(($repliedCount / $totalCount) * 100) : 0; ?>%</div>
                            <div class="stats-label">Response Rate</div>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($messages)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No support messages found.
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-card">
                            <div class="message-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($message['subject']); ?></h5>
                                        <p class="mb-0 text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($message['user_name']); ?> 
                                            (<?php echo htmlspecialchars($message['user_email']); ?>)
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
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
                            </div>
                            
                            <div class="message-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6>Message:</h6>
                                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                        
                                        <?php if ($message['status'] === 'replied' && !empty($message['admin_reply'])): ?>
                                            <div class="reply-section">
                                                <h6 class="text-success">
                                                    <i class="fas fa-reply me-1"></i>Admin Reply:
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
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted mb-3">
                                            <small>
                                                <i class="fas fa-clock me-1"></i>
                                                Received: <?php 
                                                    if (is_object($message['created_at']) && method_exists($message['created_at'], 'toDateTime')) {
                                                        echo $message['created_at']->toDateTime()->format('F d, Y \a\t g:i A');
                                                    } else {
                                                        echo date('F d, Y \a\t g:i A', strtotime($message['created_at']));
                                                    }
                                                ?>
                                            </small>
                                        </div>
                                        
                                        <?php if ($message['status'] === 'pending'): ?>
                                            <a href="reply.php?id=<?php echo $message['_id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-reply me-1"></i>Reply
                                            </a>
                                        <?php else: ?>
                                            <a href="reply.php?id=<?php echo $message['_id']; ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit me-1"></i>Edit Reply
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-outline-danger btn-sm ms-2" 
                                                onclick="deleteMessage('<?php echo $message['_id']; ?>')">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteMessage(messageId) {
            if (confirm('Are you sure you want to delete this support message? This action cannot be undone.')) {
                window.location.href = 'delete.php?id=' + messageId;
            }
        }
    </script>
</body>
</html> 