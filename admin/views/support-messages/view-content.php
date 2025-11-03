<?php
// Handle POST requests for reply operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submissions here if needed
    // For now, we'll let the controller handle this
}
?>

<!-- Message Details -->
<div class="content-card">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-eye me-2"></i>
            Support Message Details
        </h5>
    </div>
    
    <div class="card-body">
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
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons mt-4">
            <a href="index.php?action=support-messages" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Messages
            </a>
            <?php if ($message['status'] === 'pending'): ?>
                <a href="index.php?action=support-messages&method=reply&id=<?php echo $message['_id']; ?>" class="btn btn-primary">
                    <i class="fas fa-reply me-2"></i>Reply
                </a>
            <?php else: ?>
                <a href="index.php?action=support-messages&method=reply&id=<?php echo $message['_id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit Reply
                </a>
            <?php endif; ?>
            <a href="index.php?action=support-messages&method=delete&id=<?php echo $message['_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                <i class="fas fa-trash me-2"></i>Delete
            </a>
        </div>
    </div>
</div>

<style>
/* Essential support message view styles */
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
    background: #667eea;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    font-weight: 500;
}

.btn:hover {
    text-decoration: none;
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border: 1px solid #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.btn-primary {
    background: #007bff;
    border: 1px solid #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    color: white;
}

.btn-warning {
    background: #ffc107;
    border: 1px solid #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background: #e0a800;
    border-color: #e0a800;
    color: #212529;
}

.btn-danger {
    background: #dc3545;
    border: 1px solid #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    border-color: #c82333;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .message-details .row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .message-details .col-md-4.text-md-end {
        text-align: left !important;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn {
        justify-content: center;
    }
}
</style> 