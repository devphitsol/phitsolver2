<?php
// Handle POST requests for reply/delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submissions here if needed
    // For now, we'll let the controller handle this
}
?>

<style>
/* Statistics Grid Layout */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.stat-card:nth-child(1) .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card:nth-child(2) .stat-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.stat-card:nth-child(3) .stat-icon {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #343a40;
    margin-bottom: 0.25rem;
    line-height: 1;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Table Header Styles */
.table-header {
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.table-info {
    flex: 1;
}

.table-title {
    margin: 0 0 0.5rem 0;
    color: #495057;
    font-weight: 600;
    font-size: 1.1rem;
}

.table-description {
    margin: 0;
    color: #6c757d;
    font-size: 0.875rem;
    line-height: 1.4;
}

.table-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .table-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .table-actions {
        justify-content: flex-start;
    }
}

@media (max-width: 480px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-headset"></i>
            Support Messages
        </h1>
        <div class="stats-info">
            <span class="stat-item"><?php echo $totalCount; ?> total messages</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $pendingCount; ?> pending</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $repliedCount; ?> replied</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $totalCount > 0 ? round(($repliedCount / $totalCount) * 100) : 0; ?>% response rate</span>
        </div>
    </div>
    <div class="header-actions">
        <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i>
            Refresh
        </button>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">
    <!-- Stat Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $pendingCount; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $repliedCount; ?></div>
                <div class="stat-label">Replied</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-inbox"></i></div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $totalCount; ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="table-container">
        <div class="table-header">
            <div class="table-info">
                <h5 class="table-title">
                    <i class="fas fa-list me-2"></i>
                    Support Messages
                </h5>
                <p class="table-description">
                    Manage support messages from partners. Use the action buttons to view, reply to, or delete messages. Pending messages require attention.
                </p>
            </div>
            <div class="table-actions">
                <!-- Additional actions can be added here if needed -->
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <i class="fas fa-headset"></i>
                    <h4>No support messages found</h4>
                    <p>Support messages from partners will appear here</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>User</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($message['subject']); ?></strong>
                                    <div class="text-muted small"><?php echo htmlspecialchars(substr($message['message'], 0, 60)) . '...'; ?></div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($message['user_name'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="user-name"><?php echo htmlspecialchars($message['user_name'] ?? ''); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($message['user_email'] ?? ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="purpose-badge"><?php echo htmlspecialchars($message['purpose']); ?></span>
                                    <?php if (!empty($message['subcategory'])): ?>
                                        <div class="text-muted small"><?php echo htmlspecialchars($message['subcategory']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $message['status']; ?>">
                                        <?php echo ucfirst($message['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted"><?php 
                                        if (is_object($message['created_at']) && method_exists($message['created_at'], 'toDateTime')) {
                                            echo $message['created_at']->toDateTime()->format('M d, Y');
                                        } else {
                                            echo date('M d, Y', strtotime($message['created_at']));
                                        }
                                    ?></span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="index.php?action=support-messages&method=show&id=<?php echo $message['_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($message['status'] === 'pending'): ?>
                                            <a href="index.php?action=support-messages&method=reply&id=<?php echo $message['_id']; ?>" 
                                               class="btn btn-sm btn-outline-success" title="Reply">
                                                <i class="fas fa-reply"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=support-messages&method=reply&id=<?php echo $message['_id']; ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit Reply">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="index.php?action=support-messages&method=delete&id=<?php echo $message['_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this message?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

 