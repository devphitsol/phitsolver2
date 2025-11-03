<?php
// POST processing is now handled in admin/index.php
?>

<!-- Message Details -->
<div class="content-card">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-reply me-2"></i>
            Reply to Support Message
        </h5>
    </div>
    
    <div class="card-body">
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
            
            <form method="POST" action="index.php?action=support-messages&method=reply&id=<?php echo $message['_id']; ?>">
                <div class="mb-3">
                    <label for="reply" class="form-label">Your Reply *</label>
                    <textarea class="form-control" id="reply" name="reply" rows="8" 
                              placeholder="Type your reply here..." required><?php echo htmlspecialchars($message['admin_reply'] ?? ''); ?></textarea>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        The user will receive an email notification when you send this reply.
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php?action=support-messages" class="btn btn-outline-secondary">
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

<style>
/* Essential support message reply styles */
.content-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
    overflow: hidden;
}

.card-header {
    background: #f8f9fa;
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.card-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #343a40;
}

.card-body {
    padding: 2rem;
}

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
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.5rem;
}
</style> 