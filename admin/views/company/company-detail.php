<?php
// Company Management Page
$pageTitle = 'Company Management';
$currentAction = 'company';

// Get business users from database
$userController = new \App\Controllers\UserController();
$businessUsers = $userController->getBusinessUsers();
$businessCount = $userController->getBusinessCount();
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
            <i class="fas fa-building"></i>
            Business Companies Management
        </h1>
        <div class="stats-info">
            <span class="stat-item"><?php echo $businessCount; ?> total companies</span>
            <span class="stat-divider">•</span>
            <span class="stat-item">Manage registered business companies</span>
        </div>
    </div>
    <div class="header-actions">
        <!-- Additional actions can be added here if needed -->
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
                <div class="stat-number">7</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">4</div>
                <div class="stat-label">Business Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">1</div>
                <div class="stat-label">Pending Approvals</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">0</div>
                <div class="stat-label">Recent Users (7 days)</div>
            </div>
        </div>
    </div>

    <!-- Business Companies List -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-card-title">
                <i class="fas fa-list"></i>
                Registered Business Companies
            </h5>
        </div>
        <div class="admin-card-body">
            <?php if (empty($businessUsers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Business Companies Found</h5>
                    <p class="text-muted">No business companies have been registered yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($businessUsers as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="company-avatar me-3">
                                                <?php echo strtoupper(substr($user['company_name'] ?? $user['name'] ?? 'N', 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($user['company_name'] ?? $user['name'] ?? 'N/A'); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['role'] ?? 'N/A'); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><?php echo htmlspecialchars($user['email']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        $statusText = '';
                                        switch ($user['status']) {
                                            case 'active':
                                                $statusClass = 'status-active';
                                                $statusText = 'Active';
                                                break;
                                            case 'pending':
                                                $statusClass = 'status-pending';
                                                $statusText = 'Pending';
                                                break;
                                            case 'inactive':
                                                $statusClass = 'status-inactive';
                                                $statusText = 'Inactive';
                                                break;
                                            default:
                                                $statusClass = 'status-pending';
                                                $statusText = 'Unknown';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">
                                                <?php 
                                                if (isset($user['created_at'])) {
                                                    $date = $user['created_at'];
                                                    if ($date instanceof \MongoDB\BSON\UTCDateTime) {
                                                        echo $date->toDateTime()->format('M d, Y');
                                                    } else {
                                                        echo date('M d, Y', strtotime($date));
                                                    }
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php 
                                                if (isset($user['created_at'])) {
                                                    $date = $user['created_at'];
                                                    if ($date instanceof \MongoDB\BSON\UTCDateTime) {
                                                        echo $date->toDateTime()->format('g:i A');
                                                    } else {
                                                        echo date('g:i A', strtotime($date));
                                                    }
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-company-profile btn-sm" 
                                                    onclick="updateCompanyProfile('<?php echo $user['_id']; ?>')">
                                                <i class="fas fa-building"></i>
                                                Manage Company
                                            </button>
                                            <button type="button" class="btn btn-product-management btn-sm" 
                                                    onclick="updateProduct('<?php echo $user['_id']; ?>')">
                                                <i class="fas fa-shopping-cart"></i>
                                                Manage Products
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ✅ Successfully updated
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Success Toast Function
    function showSuccessToast(message = '✅ Successfully updated') {
        const toastElement = document.getElementById('successToast');
        if (toastElement) {
            const toastBody = toastElement.querySelector('.toast-body');
            if (toastBody) {
                toastBody.textContent = message;
            }
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    }

    // Make functions globally available
    window.updateCompanyProfile = function(userId) {
        // Redirect to company edit page
        window.location.href = `index.php?action=company&method=edit&id=${userId}`;
    };

    window.updateProduct = function(userId) {
        // Debug logging

        
        // Validate userId
        if (!userId) {
            alert('Error: Company ID is missing');
            return;
        }
        
        // Encode the userId to handle special characters
        const encodedUserId = encodeURIComponent(userId);
        
        // Redirect to purchase management page for this company (not product management)
        const url = `index.php?action=purchases&company_id=${encodedUserId}`;

        
        try {
            window.location.href = url;
        } catch (error) {
            console.error('Error redirecting:', error);
            alert('Error redirecting to purchase management. Please try again.');
        }
    };
});
</script>

<style>
/* Company Avatar Styles */
.company-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
}

/* Status Badge Styles */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-active {
    background-color: #d1e7dd;
    color: #0f5132;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-inactive {
    background-color: #f8d7da;
    color: #721c24;
}

.status-badge i {
    font-size: 0.5rem;
}

/* Toast Styles */
.toast {
    z-index: 1055;
}

/* Action Buttons Size Unification */
.d-flex.gap-2 .btn {
    min-width: 180px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
    white-space: nowrap;
}

.d-flex.gap-2 .btn i {
    margin-right: 0.5rem;
    font-size: 0.875rem;
}

/* New Button Styles */
.btn-company-profile {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-company-profile:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.btn-product-management {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.btn-product-management:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4c93 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
    .d-flex.gap-2 .btn {
        min-width: 140px;
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    .d-flex.gap-2 .btn i {
        font-size: 0.8rem;
        margin-right: 0.25rem;
    }
}

@media (max-width: 576px) {
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .d-flex.gap-2 .btn {
        min-width: 100%;
        width: 100%;
    }
}
</style> 