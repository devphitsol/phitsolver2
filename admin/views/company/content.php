<?php
// Purchase Management - Main Content
$currentAction = 'purchases';

// Ensure all variables are defined with defaults
$purchases = $purchases ?? [];
$totalPurchases = $totalPurchases ?? 0;
$pendingPurchases = $pendingPurchases ?? 0;
$completedPurchases = $completedPurchases ?? 0;
$cancelledPurchases = $cancelledPurchases ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$search = $search ?? '';
$companyId = $companyId ?? null;
$productId = $productId ?? null;
$paymentMethods = $paymentMethods ?? [];
$purchaseStatuses = $purchaseStatuses ?? [];

// Get company information if companyId is provided
$companyInfo = null;
if ($companyId) {
    $userController = new \App\Controllers\UserController();
    $companyInfo = $userController->getUserById($companyId);
}

// Get product information if productId is provided
$productInfo = null;
if ($productId) {
    $productModel = new \App\Models\Product();
    $productInfo = $productModel->getById($productId);
}
?>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="card-title mb-1">Purchase Management</h2>
                            <p class="text-muted mb-0">Manage product purchases and orders for companies</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="index.php?action=purchases&method=create<?php echo $companyId ? '&company_id=' . $companyId : ''; ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add New Purchase
                            </a>
                        </div>
                    </div>

                    <!-- Connection Information -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-link me-2"></i>
                            <div>
                                <strong>Partners Portal Connection:</strong> Purchases created here are automatically visible to partners in their 
                                <strong>Partners Portal > Purchased Products</strong> section. Partners can view their purchase history, 
                                order details, and product information through their portal.
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form method="GET" action="" class="d-flex gap-2">
                                <input type="hidden" name="action" value="purchases">
                                <input type="text" class="form-control" name="search" placeholder="Search by order ID, company name, or product..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if (!empty($search) || !empty($companyId) || !empty($productId)): ?>
                                    <a href="index.php?action=purchases" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-outline-primary" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-1"></i>Export
                                </button>
                                <button class="btn btn-outline-success" onclick="printPurchaseList()">
                                    <i class="fas fa-print me-1"></i>Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $totalPurchases; ?></div>
                        <div class="stat-label">Total Orders</div>
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
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $cancelledPurchases; ?></div>
                        <div class="stat-label">Cancelled Orders</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-3">
                        <input type="hidden" name="action" value="purchases">
                        <?php if ($companyId): ?>
                            <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($companyId); ?>">
                        <?php endif; ?>
                        <?php if ($productId): ?>
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">
                        <?php endif; ?>
                        
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search orders, reference numbers..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>
                                Search
                            </button>
                        </div>
                        
                        <div class="col-md-2">
                            <a href="index.php?action=purchases<?php echo $companyId ? '&company_id=' . $companyId : ''; ?><?php echo $productId ? '&product_id=' . $productId : ''; ?>" 
                               class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i>
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($purchases)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No purchase orders found</h5>
                            <p class="text-muted">Start by adding a new purchase order</p>
                            <a href="index.php?action=purchases&method=create<?php echo $companyId ? '&company_id=' . $companyId : ''; ?><?php echo $productId ? '&product_id=' . $productId : ''; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Add Purchase Order
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-stack-mobile">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-box me-1"></i>Product</th>
                                        <th><i class="fas fa-exchange-alt me-1"></i>Type</th>
                                        <th><i class="fas fa-hashtag me-1"></i>Qty</th>
                                        <th><i class="fas fa-calendar me-1"></i>Order Date</th>
                                        <th><i class="fas fa-credit-card me-1"></i>Payment</th>
                                        <th><i class="fas fa-clock me-1"></i>Terms</th>
                                        <th><i class="fas fa-bell me-1"></i>Reminder</th>
                                        <th><i class="fas fa-money-bill-wave me-1"></i>Total</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($purchases as $purchase): ?>
                                        <tr>
                                            <td data-label="Product">
                                                <div class="d-flex align-items-center">
                                                     <div class="product-thumb-placeholder me-3">
                                                         <i class="fas fa-box"></i>
                                                     </div>
                                                     <div>
                                                         <?php if (isset($purchase['purchase_items']) && is_array($purchase['purchase_items']) && count($purchase['purchase_items']) > 0): ?>
                                                             <h6 class="mb-0"><?php echo count($purchase['purchase_items']); ?> Product(s)</h6>
                                                             <small class="text-muted">
                                                                 <?php 
                                                                 $productNames = [];
                                                                 foreach ($purchase['purchase_items'] as $item) {
                                                                     $productNames[] = htmlspecialchars($item['product_name'] ?? 'Unknown Product');
                                                                 }
                                                                 echo implode(', ', array_slice($productNames, 0, 2));
                                                                 if (count($productNames) > 2) {
                                                                     echo ' +' . (count($productNames) - 2) . ' more';
                                                                 }
                                                                 ?>
                                                             </small>
                                                         <?php else: ?>
                                                             <h6 class="mb-0"><?php echo htmlspecialchars($purchase['product_name'] ?? 'N/A'); ?></h6>
                                                             <small class="text-muted">
                                                                 PO #<?php echo htmlspecialchars($purchase['po_number'] ?: 'N/A'); ?>
                                                             </small>
                                                         <?php endif; ?>
                                                     </div>
                                                 </div>
                                             </td>
                                             <td data-label="Transaction Type">
                                                 <div>
                                                       <span class="badge bg-info">
                                                           <?php 
                                                           $transactionType = $purchase['transaction_type'] ?? 'Purchase';
                                                           echo htmlspecialchars(ucfirst($transactionType)); 
                                                           ?>
                                                       </span>
                                                   </div>
                                               </td>
                                               <td data-label="Total Quantity">
                                                   <div class="fw-medium">
                                                        <?php 
                                                        if (isset($purchase['purchase_items']) && is_array($purchase['purchase_items']) && count($purchase['purchase_items']) > 0) {
                                                            $totalQuantity = 0;
                                                            foreach ($purchase['purchase_items'] as $item) {
                                                                $totalQuantity += (int)($item['quantity'] ?? 0);
                                                            }
                                                            echo $totalQuantity;
                                                        } else {
                                                            echo $purchase['quantity'] ?? 0;
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                                <td data-label="Order Date">
                                                    <div class="fw-medium">
                                                        <?php 
                                                        if (isset($purchase['order_date'])) {
                                                            $date = $purchase['order_date'];
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
                                                </td>
                                                <td data-label="Payment Method">
                                                    <div>
                                                        <span class="badge bg-secondary">
                                                            <?php 
                                                            $paymentMethod = $purchase['payment_method'] ?? 'N/A';
                                                            echo htmlspecialchars(ucfirst($paymentMethod)); 
                                                            ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td data-label="Payment Terms">
                                                    <div class="fw-medium">
                                                        <?php 
                                                        $paymentTerms = $purchase['payment_terms'] ?? 'N/A';
                                                        echo htmlspecialchars($paymentTerms); 
                                                        ?>
                                                    </div>
                                                </td>
                                                <td data-label="Reminder for Payment">
                                                    <div>
                                                      <?php 
                                                      $reminderDate = $purchase['reminder_date'] ?? null;
                                                      $paymentStatus = $purchase['payment_status'] ?? 'pending';
                                                      
                                                      if ($reminderDate) {
                                                          $reminder = $reminderDate;
                                                          if ($reminder instanceof \MongoDB\BSON\UTCDateTime) {
                                                              $reminderFormatted = $reminder->toDateTime()->format('M d, Y');
                                                          } else {
                                                              $reminderFormatted = date('M d, Y', strtotime($reminder));
                                                          }
                                                          
                                                          $today = new DateTime();
                                                          $reminderDateTime = $reminder instanceof \MongoDB\BSON\UTCDateTime ? 
                                                              $reminder->toDateTime() : new DateTime($reminder);
                                                          
                                                          if ($reminderDateTime < $today && $paymentStatus !== 'paid') {
                                                              echo '<span class="badge bg-danger">Overdue</span>';
                                                              echo '<br><small class="text-danger">' . $reminderFormatted . '</small>';
                                                          } elseif ($reminderDateTime->format('Y-m-d') === $today->format('Y-m-d')) {
                                                              echo '<span class="badge bg-warning">Due Today</span>';
                                                              echo '<br><small class="text-warning">' . $reminderFormatted . '</small>';
                                                          } else {
                                                              echo '<span class="badge bg-info">Due: ' . $reminderFormatted . '</span>';
                                                          }
                                                      } else {
                                                          echo '<span class="badge bg-secondary">No Reminder</span>';
                                                      }
                                                      ?>
                                                  </div>
                                              </td>
                                              
                                              <td data-label="Grand Total">
                                                  <div class="text-success fw-bold">
                                                      <?php 
                                                      // Calculate Grand Total dynamically using the same logic as view page
                                                      $calculatedGrandTotal = 0;
                                                      if (isset($purchase['purchase_items']) && is_array($purchase['purchase_items'])) {
                                                          foreach ($purchase['purchase_items'] as $item) {
                                                              $quantity = $item['quantity'] ?? 1;
                                                              $unitPrice = $item['unit_price'] ?? 0;
                                                              $discount = $item['discount'] ?? 0;
                                                              $vatIncluded = $item['vat_included'] ?? false;
                                                              $ratePerMonth = $item['rate_per_month'] ?? 0;
                                                              $vatAmount = $item['vat_amount'] ?? 0;
                                                              
                                                              // Check if this is a rental item (has rate_per_month)
                                                              if (!empty($ratePerMonth)) {
                                                                  // Rental calculation: (Rate per Month + VAT Amount) × Quantity
                                                                  $monthlyTotal = $ratePerMonth + $vatAmount;
                                                                  $total = $monthlyTotal * $quantity;
                                                                  $total = $total - $discount;
                                                              } else {
                                                                  // Purchase calculation: (Unit Price × Quantity) - Discount + VAT
                                                                  $subtotal = $quantity * $unitPrice;
                                                                  $total = $subtotal - $discount;
                                                                  
                                                                  if ($vatIncluded) {
                                                                      $vat = $total * 0.12; // 12% VAT
                                                                      $total += $vat;
                                                                  }
                                                              }
                                                              
                                                              $calculatedGrandTotal += $total;
                                                          }
                                                      }
                                                      ?>
                                                      ₱<?php echo number_format($calculatedGrandTotal, 2); ?>
                                                  </div>
                                              </td>
                                                                                         <td data-label="Actions">
                                                 <div class="btn-group" role="group">
                                                    <a href="index.php?action=purchases&method=view&id=<?php echo $purchase['_id']; ?>" 
                                                       class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="index.php?action=purchases&method=edit&id=<?php echo $purchase['_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
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
                            <nav aria-label="Purchase orders pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="index.php?action=purchases&page=<?php echo $currentPage - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $companyId ? '&company_id=' . $companyId : ''; ?><?php echo $productId ? '&product_id=' . $productId : ''; ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="index.php?action=purchases&page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $companyId ? '&company_id=' . $companyId : ''; ?><?php echo $productId ? '&product_id=' . $productId : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="index.php?action=purchases&page=<?php echo $currentPage + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $companyId ? '&company_id=' . $companyId : ''; ?><?php echo $productId ? '&product_id=' . $productId : ''; ?>">
                                                Next
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete purchase function
    window.deletePurchase = function(purchaseId) {
        if (confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')) {
            // Add loading state
            const button = event.target.closest('.btn');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            button.classList.add('loading');
            
            window.location.href = `index.php?action=purchases&method=delete&id=${purchaseId}`;
        }
    };
    
    // Enhanced table interactions
    const tableRows = document.querySelectorAll('.table-hover tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons or links
            if (e.target.closest('.btn') || e.target.closest('a')) {
                return;
            }
            
            // Add visual feedback
            this.style.transform = 'scale(1.02)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
    });
    
    // Enhanced search functionality
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Add loading state to search button
                const searchBtn = document.querySelector('button[type="submit"]');
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                    searchBtn.disabled = true;
                }
            }, 500);
        });
    }
    
    // Enhanced pagination
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const linkElement = this;
            linkElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            linkElement.style.pointerEvents = 'none';
        });
    });
    
    // Mobile menu enhancements
    if (window.innerWidth <= 768) {
        const table = document.querySelector('.table-stack-mobile');
        if (table) {
            table.classList.add('mobile-optimized');
        }
    }
    
    // Add smooth scrolling for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>

<style>
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

/* Statistics Grid Styles */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 1.5rem;
    color: white;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:nth-child(1) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card:nth-child(2) {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card:nth-child(4) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.stat-content {
    text-align: left;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1.25rem;
    }
    
    .stat-icon {
        font-size: 2rem;
    }
    
    .stat-number {
        font-size: 1.25rem;
    }
}

@media (max-width: 576px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.btn-group .btn {
    border-radius: 4px;
    margin-right: 2px;
}

 .btn-group .btn:last-child {
     margin-right: 0;
 }
 
 /* Enhanced Mobile Responsive Design */
 @media (max-width: 768px) {
     .table-stack-mobile {
         display: block;
     }
     
     .table-stack-mobile thead {
         display: none;
     }
     
     .table-stack-mobile tbody,
     .table-stack-mobile tr,
     .table-stack-mobile td {
         display: block;
         width: 100%;
     }
     
     .table-stack-mobile tr {
         border: 1px solid #dee2e6;
         border-radius: 8px;
         margin-bottom: 1rem;
         padding: 1rem;
         background: #f8f9fa;
     }
     
     .table-stack-mobile td {
         border: none;
         padding: 0.5rem 0;
         display: flex;
         justify-content: space-between;
         align-items: center;
     }
     
     .table-stack-mobile td::before {
         content: attr(data-label) ": ";
         font-weight: 600;
         color: #495057;
         min-width: 120px;
     }
     
     .table-stack-mobile .btn-group {
         flex-direction: column;
         gap: 0.25rem;
     }
     
     .table-stack-mobile .btn-group .btn {
         width: 100%;
         margin: 0;
     }
     
     .table-stack-mobile .d-flex {
         flex-direction: column;
         align-items: flex-start !important;
     }
     
     .table-stack-mobile .product-thumb-placeholder {
         margin-bottom: 0.5rem;
     }
 }
 
 /* Enhanced UI Improvements */
 .stat-card {
     transition: all 0.3s ease;
 }
 
 .stat-card:hover {
     transform: translateY(-3px);
     box-shadow: 0 8px 25px rgba(0,0,0,0.15);
 }
 
 .table-hover tbody tr:hover {
     background-color: rgba(0,123,255,0.05);
     transform: scale(1.01);
     transition: all 0.2s ease;
 }
 
 .badge {
     font-size: 0.75rem;
     padding: 0.375rem 0.75rem;
 }
 
 .btn-group .btn {
     transition: all 0.2s ease;
 }
 
 .btn-group .btn:hover {
     transform: translateY(-1px);
     box-shadow: 0 4px 8px rgba(0,0,0,0.15);
 }
 
 /* Loading States */
 .loading {
     opacity: 0.6;
     pointer-events: none;
 }
 
 .loading::after {
     content: '';
     position: absolute;
     top: 50%;
     left: 50%;
     width: 20px;
     height: 20px;
     margin: -10px 0 0 -10px;
     border: 2px solid #f3f3f3;
     border-top: 2px solid #007bff;
     border-radius: 50%;
     animation: spin 1s linear infinite;
 }
 
 @keyframes spin {
     0% { transform: rotate(0deg); }
     100% { transform: rotate(360deg); }
 }
 </style>

<script>
// Enhanced Purchase Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add loading states to buttons
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('btn-secondary')) {
                this.classList.add('loading');
                setTimeout(() => {
                    this.classList.remove('loading');
                }, 2000);
            }
        });
    });

    // Enhanced table row hover effects
    document.querySelectorAll('.table-hover tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Export to Excel functionality
function exportToExcel() {
    const table = document.querySelector('.table');
    const ws = XLSX.utils.table_to_sheet(table);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Purchases");
    XLSX.writeFile(wb, "purchases_export.xlsx");
}

// Print purchase list
function printPurchaseList() {
    window.print();
}

// Enhanced search functionality
function performSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchTerm = searchInput.value.trim();
    
    if (searchTerm.length > 0) {
        // Add loading state
        document.querySelector('.table-responsive').classList.add('loading');
        
        // Simulate search delay
        setTimeout(() => {
            document.querySelector('.table-responsive').classList.remove('loading');
        }, 1000);
    }
}

// Real-time search (if needed)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 500);
        });
    }
});

// Enhanced mobile responsiveness
function checkMobileView() {
    const isMobile = window.innerWidth <= 768;
    const table = document.querySelector('.table');
    
    if (isMobile) {
        table.classList.add('table-stack-mobile');
    } else {
        table.classList.remove('table-stack-mobile');
    }
}

// Check on load and resize
window.addEventListener('load', checkMobileView);
window.addEventListener('resize', checkMobileView);
</script>