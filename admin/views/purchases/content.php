<?php
// Purchase Management - Main Content
$currentAction = 'purchases';

// Ensure all variables are defined with defaults
$purchases = $purchases ?? [];
$totalPurchases = $totalPurchases ?? 0;
$pendingPurchases = $pendingPurchases ?? 0;
$completedPurchases = $completedPurchases ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$search = $search ?? '';
$companyId = $companyId ?? null;

// Get company information if companyId is provided
$companyInfo = null;
if ($companyId) {
    try {
        $userController = new \App\Controllers\UserController();
        $companyInfo = $userController->getUserById($companyId);
    } catch (Exception $e) {
        error_log("Error getting company info: " . $e->getMessage());
        $companyInfo = null;
    }
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
            <i class="fas fa-shopping-cart"></i>
            Purchase Management
            <?php if ($companyInfo): ?>
                <span class="text-muted">- <?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></span>
            <?php endif; ?>
        </h1>
        <div class="stats-info">
            <?php if ($companyInfo): ?>
                <span class="stat-item">Manage purchases for <?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></span>
            <?php else: ?>
                <span class="stat-item">Manage purchase orders and transactions</span>
            <?php endif; ?>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $totalPurchases; ?> total purchases</span>
        </div>
    </div>
    <div class="header-actions">
        <?php if ($companyInfo): ?>
            <a href="index.php?action=company" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Companies
            </a>
        <?php endif; ?>
        <a href="index.php?action=purchases&method=create<?php echo $companyId ? '&company_id=' . $companyId : ''; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add New Purchase
        </a>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">
    <!-- Statistics Grid -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $totalPurchases; ?></div>
                <div class="stat-label">Total Purchases</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $pendingPurchases; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $completedPurchases; ?></div>
                <div class="stat-label">Completed Orders</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $totalPages; ?></div>
                <div class="stat-label">Total Pages</div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-search"></i>
                Search & Filter
            </h3>
        </div>
        <div class="admin-card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="purchases">
                <?php if ($companyId): ?>
                    <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">
                <?php endif; ?>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Search Purchases</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Search by order number, product name, or company..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-outline-secondary">
                                Search
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control form-select" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo ($_GET['status'] ?? '') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-list"></i>
                Purchase Orders
            </h3>
        </div>
        <div class="admin-card-body">
            <?php if (empty($purchases)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No purchase orders found</h5>
                    <p class="text-muted">Start by creating your first purchase order.</p>
                    <a href="index.php?action=purchases&method=create<?php echo $companyId ? '&company_id=' . $companyId : ''; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add First Purchase
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Transaction Type</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Payment Method</th>
                                <th>Reminder for Payment</th>
                                <th>Grand Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $purchase): ?>
                                <?php 
                                // Get the first product from purchase_items for display
                                $purchaseItems = $purchase['purchase_items'] ?? [];
                                $firstItem = !empty($purchaseItems) ? $purchaseItems[0] : null;
                                $totalQuantity = 0;
                                $totalAmount = 0;
                                
                                // Calculate totals from all items
                                foreach ($purchaseItems as $item) {
                                    $totalQuantity += (int)($item['quantity'] ?? 0);
                                    $totalAmount += (float)($item['total_price'] ?? 0);
                                }
                                
                                // Format reminder payment date
                                $reminderDate = '';
                                if (isset($purchase['reminder_payment'])) {
                                    if ($purchase['reminder_payment'] instanceof MongoDB\BSON\UTCDateTime) {
                                        $reminderDate = $purchase['reminder_payment']->toDateTime()->format('M j, Y');
                                    } else {
                                        $reminderDate = date('M j, Y', strtotime($purchase['reminder_payment']));
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold">
                                            <?php echo htmlspecialchars($purchase['company_name'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $transactionType = $purchase['transaction_type'] ?? 'purchase';
                                        $typeIcons = [
                                            'purchase' => '🛒',
                                            'rental' => '📅',
                                            'rent_to_own' => '🏠'
                                        ];
                                        $typeLabels = [
                                            'purchase' => 'Purchase',
                                            'rental' => 'Rental',
                                            'rent_to_own' => 'Rent to Own'
                                        ];
                                        ?>
                                        <span class="badge badge-info">
                                            <?php echo $typeIcons[$transactionType] ?? '🛒'; ?> 
                                            <?php echo $typeLabels[$transactionType] ?? 'Purchase'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($firstItem): ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($firstItem['product_name'] ?? 'N/A'); ?></strong>
                                                <?php if (count($purchaseItems) > 1): ?>
                                                    <br><small class="text-muted">+<?php echo count($purchaseItems) - 1; ?> more items</small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No products</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-light">
                                            <?php echo $totalQuantity; ?> units
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $paymentMethod = $purchase['payment_method'] ?? '';
                                        if ($paymentMethod): ?>
                                            <span class="badge badge-secondary">
                                                <?php echo htmlspecialchars($paymentMethod); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($reminderDate): ?>
                                            <small class="text-info">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?php echo $reminderDate; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            ₱<?php echo number_format($purchase['grand_total'] ?? $totalAmount, 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="index.php?action=purchases&method=view&id=<?php echo $purchase['_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?action=purchases&method=edit&id=<?php echo $purchase['_id']; ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePurchase('<?php echo $purchase['_id']; ?>')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Purchase pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?action=purchases&page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?><?php echo $companyId ? '&company_id=' . $companyId : ''; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?action=purchases&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?><?php echo $companyId ? '&company_id=' . $companyId : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?action=purchases&page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?><?php echo $companyId ? '&company_id=' . $companyId : ''; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function deletePurchase(purchaseId) {
        if (confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')) {
            window.location.href = `index.php?action=purchases&method=delete&id=${purchaseId}`;
        }
    }
</script>

<style>
.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.admin-table th,
.admin-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.admin-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.admin-table tbody tr:hover {
    background-color: #f8f9fa;
}

.admin-table tbody tr:last-child td {
    border-bottom: none;
}

.admin-table td {
    vertical-align: middle;
}

.admin-table .fw-bold {
    font-weight: 600;
}

.admin-table .text-success {
    color: #28a745 !important;
}

.admin-table .text-info {
    color: #17a2b8 !important;
}

.admin-table .text-muted {
    color: #6c757d !important;
}

.badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 0.25rem;
}

.badge-success {
    background-color: #d4edda;
    color: #155724;
}

.badge-warning {
    background-color: #fff3cd;
    color: #856404;
}

.badge-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

.badge-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.badge-light {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Responsive table */
@media (max-width: 768px) {
    .admin-table {
        font-size: 0.875rem;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 0.5rem;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
}
</style> 