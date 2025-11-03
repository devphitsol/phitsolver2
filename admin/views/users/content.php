<?php
// Handle POST requests for create/edit/delete operations
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

.stat-card:nth-child(4) .stat-icon {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.stat-card:nth-child(5) .stat-icon {
    background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
}

.stat-card:nth-child(6) .stat-icon {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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
    
    /* Table Header Responsive */
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
            <i class="fas fa-users"></i>
            User Management
        </h1>
        <div class="stats-info">
            <span class="stat-item"><?php echo $userCount; ?> total users</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $businessCount; ?> business users</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $pendingCount; ?> pending approvals</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $adminUserCount ?? 0; ?> admins</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $employeeCount ?? 0; ?> employees</span>
        </div>
    </div>
    <div class="header-actions">
        <a href="index.php?action=users&method=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add New User
        </a>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">
    <!-- Statistics Grid -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $userCount; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo count(array_filter($users, function($user) { 
                    $userDate = strtotime($user['created_at']);
                    $currentDate = time();
                    $daysDiff = floor(($currentDate - $userDate) / (60 * 60 * 24));
                    return $daysDiff <= 7; // Users created in last 7 days
                })); ?></div>
                <div class="stat-label">Recent Users (7 days)</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $pendingCount; ?></div>
                <div class="stat-label">Pending Approvals</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $businessCount; ?></div>
                <div class="stat-label">Business Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $adminUserCount ?? 0; ?></div>
                <div class="stat-label">Admin Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $employeeCount ?? 0; ?></div>
                <div class="stat-label">Employee Users</div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="table-container">
        <div class="table-header">
            <div class="table-info">
                <h5 class="table-title">
                    <i class="fas fa-list me-2"></i>
                    Users List
                </h5>
                <p class="table-description">
                    Manage your users. Use the action buttons to edit, approve, or delete users. Business accounts require approval before activation.
                </p>
            </div>
            <div class="table-actions">
                <!-- Additional actions can be added here if needed -->
            </div>
        </div>
        
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h4>No users yet</h4>
                    <p>Create your first user to get started</p>
                    <a href="index.php?action=users&method=create" class="btn-add">
                        <i class="fas fa-plus"></i>
                        Add First User
                    </a>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="user-name"><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></div>
                                            <div class="user-username">@<?php echo htmlspecialchars($user['username']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="index.php?action=users&method=edit&id=<?php echo $user['_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['role'] === 'business' && $user['status'] === 'pending'): ?>
                                            <a href="index.php?action=users&method=approve&id=<?php echo $user['_id']; ?>" 
                                               class="btn btn-sm btn-outline-success" title="Approve" 
                                               onclick="return confirm('Are you sure you want to approve this business account?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['role'] === 'business' && $user['status'] === 'active'): ?>
                                            <a href="../public/partners-dashboard.php" target="_blank" 
                                               class="btn btn-sm btn-outline-info" title="View Partners Portal">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="index.php?action=users&method=delete&id=<?php echo $user['_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this user?')">
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

 