<?php
// Product Management - Main Content
$currentAction = 'products';

// Ensure all variables are defined with defaults
$products = $products ?? [];
$totalProducts = $totalProducts ?? 0;
$activeProducts = $activeProducts ?? 0;
$inactiveProducts = $inactiveProducts ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$search = $search ?? '';
$categories = $categories ?? [];
$companyId = $companyId ?? null;

// Get company information if companyId is provided
$companyInfo = null;
if ($companyId) {
    try {
        $userController = new \App\Controllers\UserController();
        $companyInfo = $userController->getUserById($companyId);
        // Debug logging
        error_log("Products content - companyId: $companyId, companyInfo: " . ($companyInfo ? 'found' : 'not found'));
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

.product-thumb-placeholder {
    width: 40px;
    height: 40px;
    background-color: #f8f9fa;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

/* Product description text wrapping */
.product-description {
    white-space: normal !important;
    word-wrap: break-word;
    word-break: break-word;
    line-height: 1.4;
    max-width: 500px;
    display: block;
}

/* Table cell adjustments for better text display */
.admin-table td {
    vertical-align: top;
    padding: 12px 8px;
}

.admin-table td:first-child {
    min-width: 250px;
    max-width: 350px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-description {
        max-width: 200px;
        font-size: 0.75rem;
    }
    
    .admin-table td:first-child {
        min-width: 200px;
        max-width: 250px;
    }
}
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-box"></i>
            Product Management
            <?php if ($companyInfo): ?>
                <span class="text-muted">- <?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></span>
            <?php endif; ?>
        </h1>
        <div class="stats-info">
            <?php if ($companyInfo): ?>
                <span class="stat-item">Manage products for <?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></span>
            <?php else: ?>
                <span class="stat-item">Manage your product catalog</span>
            <?php endif; ?>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $totalProducts; ?> total products</span>
        </div>
    </div>
    <div class="header-actions">
        <?php if ($companyInfo): ?>
            <a href="index.php?action=company" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Companies
            </a>
        <?php endif; ?>
        <a href="index.php?action=products&method=create<?php echo $companyId ? '&company_id=' . $companyId : ''; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add New Product
        </a>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">

    <!-- Statistics Grid -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $totalProducts; ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $activeProducts; ?></div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $inactiveProducts; ?></div>
                <div class="stat-label">Inactive Products</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $totalPages; ?></div>
                <div class="stat-label">Total Pages</div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-card-title">
                <i class="fas fa-search"></i>
                Search & Filter
            </h5>
        </div>
        <div class="admin-card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="products">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Search products by name, description, or category..."
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-outline-secondary">
                            Search
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-card-title">
                <i class="fas fa-list"></i>
                Product List
            </h5>
        </div>
        <div class="admin-card-body">
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No products found</h5>
                    <p class="text-muted">Start by adding your first product.</p>
                    <a href="index.php?action=products&method=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Product
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php 
                                            $productImage = '';
                                            if (!empty($product['images']) && is_array($product['images']) && count($product['images']) > 0) {
                                                $productImage = $product['images'][0];
                                            } elseif (!empty($product['image_url'])) {
                                                $productImage = $product['image_url'];
                                            }
                                            
                                            if (!empty($productImage)): ?>
                                                <img src="<?php echo htmlspecialchars($productImage); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     class="product-thumb me-3" 
                                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                <small class="text-muted product-description">
                                                    <?php 
                                                    $shortDescription = $product['short_description'] ?? '';
                                                    if (!empty($shortDescription)) {
                                                        echo htmlspecialchars($shortDescription);
                                                    } else {
                                                        // Fallback to description if short_description is empty
                                                        $description = $product['description'] ?? '';
                                                        $plainDescription = strip_tags($description);
                                                        echo htmlspecialchars(substr($plainDescription, 0, 100));
                                                        echo strlen($plainDescription) > 100 ? '...' : '';
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light">
                                            <?php echo htmlspecialchars($product['category'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">
                                            ₱<?php echo number_format($product['price'] ?? 0, 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="<?php echo ($product['stock_quantity'] ?? 0) > 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $product['stock_quantity'] ?? 0; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product['status'] === 'active'): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php 
                                            $createdAt = $product['created_at'];
                                            if ($createdAt instanceof MongoDB\BSON\UTCDateTime) {
                                                echo $createdAt->toDateTime()->format('M j, Y');
                                            } else {
                                                echo date('M j, Y', strtotime($createdAt));
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="index.php?action=products&method=edit&id=<?php echo $product['_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?action=purchases&product_id=<?php echo $product['_id']; ?><?php echo $companyId ? '&company_id=' . $companyId : ''; ?>" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="toggleProductStatus('<?php echo $product['_id']; ?>')">
                                                <i class="fas fa-<?php echo $product['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteProduct('<?php echo $product['_id']; ?>')">
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
                    <nav aria-label="Product pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?action=products&page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?action=products&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?action=products&page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>">
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
    // Make functions globally available
    window.toggleProductStatus = function(productId) {
        if (confirm('Are you sure you want to change the status of this product?')) {
            window.location.href = `index.php?action=products&method=toggle-status&id=${productId}`;
        }
    };

    window.deleteProduct = function(productId) {
        if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
            window.location.href = `index.php?action=products&method=delete&id=${productId}`;
        }
    };
</script> 