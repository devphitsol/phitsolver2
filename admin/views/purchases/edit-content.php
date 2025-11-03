<?php
// Purchase Edit Form
$currentAction = 'purchases';

// Ensure all variables are defined with defaults
$purchase = $purchase ?? [];
$products = $products ?? [];
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

// Extract purchase data for form population
$purchaseId = $purchase['_id'] ?? '';
$transactionType = $purchase['transaction_type'] ?? 'purchase';
$purchaseItems = $purchase['purchase_items'] ?? [];
$orderDate = $purchase['order_date'] ?? '';
$purchaseOrderDate = $purchase['purchase_order_date'] ?? '';
$deliveryDate = $purchase['delivery_date'] ?? '';
$poNumber = $purchase['po_number'] ?? '';
$invoiceNumber = $purchase['invoice'] ?? '';
$warrantyPeriod = $purchase['warranty_period'] ?? '';
$paymentMethod = $purchase['payment_method'] ?? '';
$paymentTerms = $purchase['payment_terms'] ?? '';
$reminderPayment = $purchase['reminder_payment'] ?? '';
$notes = $purchase['notes'] ?? '';
$subtotal = $purchase['subtotal'] ?? 0;
$totalVat = $purchase['total_vat'] ?? 0;
$totalDiscount = $purchase['total_discount'] ?? 0;
$grandTotal = $purchase['grand_total'] ?? 0;
?>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="fas fa-edit me-2"></i>
                                Edit Purchase Order
                                <?php if ($companyInfo): ?>
                                    <span class="text-muted">- <?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></span>
                                <?php endif; ?>
                            </h5>
                            <p class="text-muted mb-0">Update purchase order information and details</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <a href="index.php?action=purchases<?php echo $companyId ? '&company_id=' . $companyId : ''; ?><?php echo $productId ? '&product_id=' . $productId : ''; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Purchases
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Purchase Order Information
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="index.php?action=purchases&method=update&id=<?php echo $purchaseId; ?>" id="purchaseForm">
                        <?php if ($companyId): ?>
                            <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">
                        <?php endif; ?>
                        
                        <!-- 🔹 1. Header Section -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-building me-1"></i>Company
                                </label>
                                <div class="form-control-plaintext">
                                    <?php if ($companyInfo): ?>
                                        <strong><?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">No company selected</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="transaction_type" class="form-label">
                                    <i class="fas fa-exchange-alt me-1"></i>Transaction Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="">Select transaction type</option>
                                    <option value="purchase" <?php echo $transactionType === 'purchase' ? 'selected' : ''; ?>>🛒 Purchase</option>
                                    <option value="rental" <?php echo $transactionType === 'rental' ? 'selected' : ''; ?>>📅 Rental</option>
                                    <option value="rent_to_own" <?php echo $transactionType === 'rent_to_own' ? 'selected' : ''; ?>>🏠 Rent to Own</option>
                                </select>
                                <div class="form-text">Select the type of transaction for this order</div>
                            </div>
                        </div>
                        
                        <!-- 🔹 2. Product Details 영역 (거래 유형에 따라 다르게 표시) -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-boxes me-2"></i>
                                    Products
                                    <span class="text-danger">*</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="productRows">
                                    <!-- Product rows will be dynamically generated here -->
                                </div>
                                
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary" id="addProductBtn">
                                        <i class="fas fa-plus me-1"></i>
                                        Add Product
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 🔹 3. Additional Order Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Additional Order Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Row 1: Order Date, Purchase Order Date, Delivery Date -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="order_date" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Order Date
                                        </label>
                                        <input type="date" class="form-control" id="order_date" name="order_date" 
                                               value="<?php echo $orderDate ? date('Y-m-d', strtotime($orderDate)) : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="purchase_order_date" class="form-label">
                                            <i class="fas fa-calendar-check me-1"></i>Purchase Order Date
                                        </label>
                                        <input type="date" class="form-control" id="purchase_order_date" name="purchase_order_date" 
                                               value="<?php echo $purchaseOrderDate ? date('Y-m-d', strtotime($purchaseOrderDate)) : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="delivery_date" class="form-label">
                                            <i class="fas fa-truck me-1"></i>Delivery Date
                                        </label>
                                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                                               value="<?php echo $deliveryDate ? date('Y-m-d', strtotime($deliveryDate)) : ''; ?>">
                                    </div>
                                </div>
                                
                                <!-- Row 2: Purchase Order Number, Invoice Number, Warranty Period -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="po_number" class="form-label">
                                            <i class="fas fa-hashtag me-1"></i>Purchase Order Number
                                        </label>
                                        <input type="text" class="form-control" id="po_number" name="po_number" 
                                               value="<?php echo htmlspecialchars($poNumber); ?>" placeholder="PO-2024-001">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="invoice" class="form-label">
                                            <i class="fas fa-file-invoice me-1"></i>Invoice Number
                                        </label>
                                        <input type="text" class="form-control" id="invoice" name="invoice" 
                                               value="<?php echo htmlspecialchars($invoiceNumber); ?>" placeholder="INV-2024-001">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="warranty_period" class="form-label">
                                            <i class="fas fa-shield-alt me-1"></i>Warranty Period
                                        </label>
                                        <input type="text" class="form-control" id="warranty_period" name="warranty_period" 
                                               value="<?php echo htmlspecialchars($warrantyPeriod); ?>" placeholder="12 months">
                                    </div>
                                </div>
                                
                                <!-- Row 3: Payment Method, Payment Terms, Reminder for Payment -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="payment_method" class="form-label">
                                            <i class="fas fa-credit-card me-1"></i>Payment Method
                                        </label>
                                        <select class="form-select" id="payment_method" name="payment_method">
                                            <option value="">Select payment method</option>
                                            <option value="Cash" <?php echo $paymentMethod === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                                            <option value="Bank Transfer" <?php echo $paymentMethod === 'Bank Transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                            <option value="Check" <?php echo $paymentMethod === 'Check' ? 'selected' : ''; ?>>Check</option>
                                            <option value="Credit Card" <?php echo $paymentMethod === 'Credit Card' ? 'selected' : ''; ?>>Credit Card</option>
                                            <option value="PayPal" <?php echo $paymentMethod === 'PayPal' ? 'selected' : ''; ?>>PayPal</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="payment_terms" class="form-label">
                                            <i class="fas fa-clock me-1"></i>Payment Terms
                                        </label>
                                        <input type="text" class="form-control" id="payment_terms" name="payment_terms" 
                                               value="<?php echo htmlspecialchars($paymentTerms); ?>" placeholder="Net 30 days">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="reminder_payment" class="form-label">
                                            <i class="fas fa-bell me-1"></i>Reminder for Payment
                                        </label>
                                        <input type="date" class="form-control" id="reminder_payment" name="reminder_payment" 
                                               value="<?php echo $reminderPayment ? date('Y-m-d', strtotime($reminderPayment)) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 🔹 4. Order Summary -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-calculator me-2"></i>
                                    Order Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 offset-md-6">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td class="text-end"><strong>Subtotal:</strong></td>
                                                    <td class="text-end">₱<span id="subtotal"><?php echo number_format($subtotal, 2); ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end"><strong>Total VAT:</strong></td>
                                                    <td class="text-end">₱<span id="totalVat"><?php echo number_format($totalVat, 2); ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end"><strong>Total Discount:</strong></td>
                                                    <td class="text-end">₱<span id="totalDiscount"><?php echo number_format($totalDiscount, 2); ?></span></td>
                                                </tr>
                                                <tr class="border-top">
                                                    <td class="text-end"><strong>Grand Total:</strong></td>
                                                    <td class="text-end"><strong>₱<span id="grandTotal"><?php echo number_format($grandTotal, 2); ?></span></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 🔹 5. Notes -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    Notes
                                </h6>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" id="notes" name="notes" rows="4" 
                                          placeholder="Add any additional notes or comments about this purchase order..."><?php echo htmlspecialchars($notes); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- 🔹 6. Form Actions -->
                        <div class="d-flex justify-content-end gap-3">
                            <a href="index.php?action=purchases<?php echo $companyId ? '&company_id=' . $companyId : ''; ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Update Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Store products data for dropdown population
    const productsData = <?php echo json_encode($products); ?>;
    let productRowCounter = 0;
    let selectedProducts = new Set(); // Track selected products for duplicate prevention

    
    
    if (productsData.length === 0) {
        
    }

    // Initialize with existing purchase items
    const existingPurchaseItems = <?php echo json_encode($purchaseItems); ?>;
    
    
    if (existingPurchaseItems && existingPurchaseItems.length > 0) {
        existingPurchaseItems.forEach((item, index) => {
            addProductRow(item);
        });
    } else {
        // Initialize with one empty product row if no existing items
        addProductRow();
    }

    // Add Product Row Function
    function addProductRow(existingItem = null) {
        productRowCounter++;
        const rowId = `product-row-${productRowCounter}`;
        const transactionType = document.getElementById('transaction_type').value;
        
        const productRow = document.createElement('div');
        productRow.className = 'product-row border rounded p-3 mb-3';
        productRow.id = rowId;
        
        if (transactionType === 'purchase') {
            // 🔸 If Transaction Type = Purchase
            productRow.innerHTML = `
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select product-select" name="purchaseItems[${productRowCounter}][product_id]" required>
                            <option value="">Select a product</option>
                            ${generateProductOptions(existingItem?.product_id)}
                        </select>
                        <input type="hidden" class="product-name-input" name="purchaseItems[${productRowCounter}][product_name]" value="${existingItem?.product_name || ''}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control quantity-input" name="purchaseItems[${productRowCounter}][quantity]" 
                               value="${existingItem?.quantity || ''}" min="1" step="1" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Unit Price (₱) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control unit-price-input" name="purchaseItems[${productRowCounter}][unit_price]" 
                               value="${existingItem?.unit_price || ''}" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Net Amount (₱)</label>
                        <input type="number" class="form-control net-amount-input" name="purchaseItems[${productRowCounter}][net_amount]" 
                               value="${existingItem?.net_amount || ''}" readonly>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger remove-product-btn" onclick="removeProductRow('${rowId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        } else {
            // 🔸 If Transaction Type = Rental or Rent to Own
            productRow.innerHTML = `
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select product-select" name="purchaseItems[${productRowCounter}][product_id]" required>
                            <option value="">Select a product</option>
                            ${generateProductOptions(existingItem?.product_id)}
                        </select>
                        <input type="hidden" class="product-name-input" name="purchaseItems[${productRowCounter}][product_name]" value="${existingItem?.product_name || ''}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control quantity-input" name="purchaseItems[${productRowCounter}][quantity]" 
                               value="${existingItem?.quantity || ''}" min="1" step="1" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Rental Period (Months) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rental-period-input" name="purchaseItems[${productRowCounter}][rental_period]" 
                               value="${existingItem?.rental_period || '1'}" min="1" max="36" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Per Unit (₱) <i class="fas fa-edit" title="Editable"></i></label>
                        <input type="number" class="form-control per-unit-price-input" name="purchaseItems[${productRowCounter}][per_unit_price]" 
                               value="${existingItem?.per_unit_price || ''}" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Monthly Total (₱) <i class="fas fa-edit" title="Editable"></i></label>
                        <input type="number" class="form-control monthly-total-input" name="purchaseItems[${productRowCounter}][monthly_total]" 
                               value="${existingItem?.monthly_total || ''}" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger remove-product-btn" onclick="removeProductRow('${rowId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Net Amount (₱) <i class="fas fa-edit" title="Editable"></i></label>
                        <input type="number" class="form-control net-amount-input" name="purchaseItems[${productRowCounter}][net_amount]" 
                               value="${existingItem?.net_amount || ''}" min="0" step="0.01" required>
                    </div>
                </div>
            `;
        }
        
        document.getElementById('productRows').appendChild(productRow);
        
        // Add event listeners to the new product row
        addProductRowEventListeners(productRow);
        
        // Update selected products set
        if (existingItem?.product_id) {
            selectedProducts.add(existingItem.product_id);
        }
        
        // Update remove buttons visibility
        updateRemoveButtons();
    }

    // Generate product options for dropdown
    function generateProductOptions(selectedProductId = null) {
        let options = '';
        productsData.forEach(product => {
            const isSelected = selectedProductId === product._id ? 'selected' : '';
            const isDisabled = selectedProducts.has(product._id) && selectedProductId !== product._id ? 'disabled' : '';
            options += `<option value="${product._id}" ${isSelected} ${isDisabled}>${product.name}</option>`;
        });
        return options;
    }

    // Add event listeners to product row
    function addProductRowEventListeners(productRow) {
        const transactionType = document.getElementById('transaction_type').value;
        
        if (transactionType === 'purchase') {
            // Purchase event listeners
            const productSelect = productRow.querySelector('.product-select');
            const quantityInput = productRow.querySelector('.quantity-input');
            const unitPriceInput = productRow.querySelector('.unit-price-input');
            const netAmountInput = productRow.querySelector('.net-amount-input');
            const productNameInput = productRow.querySelector('.product-name-input');
            
            // Product selection event
            productSelect.addEventListener('change', function() {
                handleProductSelection(this, productRow, 'purchase');
            });
            
            // Quantity and price calculation events
            quantityInput.addEventListener('input', function() {
                calculatePurchaseRowTotal(productRow);
            });
            
            unitPriceInput.addEventListener('input', function() {
                calculatePurchaseRowTotal(productRow);
            });
            
        } else if (transactionType === 'rental' || transactionType === 'rent_to_own') {
            // Rental event listeners
            const productSelect = productRow.querySelector('.product-select');
            const quantityInput = productRow.querySelector('.quantity-input');
            const rentalPeriodInput = productRow.querySelector('.rental-period-input');
            const perUnitPriceInput = productRow.querySelector('.per-unit-price-input');
            const monthlyTotalInput = productRow.querySelector('.monthly-total-input');
            const netAmountInput = productRow.querySelector('.net-amount-input');
            const productNameInput = productRow.querySelector('.product-name-input');
            
            // Product selection event
            productSelect.addEventListener('change', function() {
                handleProductSelection(this, productRow, 'rental');
            });
            
            // Rental calculation events
            quantityInput.addEventListener('input', function() {
                calculateRentalRowTotal(productRow);
            });
            
            rentalPeriodInput.addEventListener('input', function() {
                calculateRentalRowTotal(productRow);
            });
            
            perUnitPriceInput.addEventListener('input', function() {
                calculateRentalRowTotal(productRow);
            });
            
            // Monthly total manual edit handling
            monthlyTotalInput.addEventListener('input', function() {
                this.setAttribute('data-manually-edited', 'true');
                setTimeout(() => {
                    this.removeAttribute('data-manually-edited');
                }, 2000);
            });
            
            // Net amount manual edit handling
            netAmountInput.addEventListener('input', function() {
                this.setAttribute('data-manually-edited', 'true');
                setTimeout(() => {
                    this.removeAttribute('data-manually-edited');
                }, 2000);
            });
        }
    }

    // Handle product selection
    function handleProductSelection(selectElement, productRow, type) {
        const selectedProductId = selectElement.value;
        const productNameInput = productRow.querySelector('.product-name-input');
        
        if (selectedProductId) {
            const selectedProduct = productsData.find(p => p._id === selectedProductId);
            if (selectedProduct) {
                productNameInput.value = selectedProduct.name;
                
                // Auto-fill price if available
                if (type === 'purchase') {
                    const unitPriceInput = productRow.querySelector('.unit-price-input');
                    if (unitPriceInput && !unitPriceInput.value) {
                        unitPriceInput.value = selectedProduct.price || '';
                    }
                } else if (type === 'rental' || type === 'rent_to_own') {
                    const perUnitPriceInput = productRow.querySelector('.per-unit-price-input');
                    if (perUnitPriceInput && !perUnitPriceInput.value) {
                        perUnitPriceInput.value = selectedProduct.rental_price || selectedProduct.price || '';
                    }
                }
                
                // Update calculations
                if (type === 'purchase') {
                    calculatePurchaseRowTotal(productRow);
                } else {
                    calculateRentalRowTotal(productRow);
                }
            }
        } else {
            productNameInput.value = '';
        }
    }

    // Calculate total for purchase row
    function calculatePurchaseRowTotal(productRow) {
        const quantity = parseFloat(productRow.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(productRow.querySelector('.unit-price-input').value) || 0;
        const discount = parseFloat(productRow.querySelector('.discount-input')?.value) || 0;
        const vat = parseFloat(productRow.querySelector('.vat-input')?.value) || 0;
        
        const netAmount = quantity * unitPrice;
        
        productRow.querySelector('.net-amount-input').value = netAmount.toFixed(2);
        calculateGrandTotal();
    }

    // Calculate total for rental row
    function calculateRentalRowTotal(productRow) {
        const quantity = parseFloat(productRow.querySelector('.quantity-input').value) || 0;
        const perUnitPrice = parseFloat(productRow.querySelector('.per-unit-price-input').value) || 0;
        const rentalPeriod = parseFloat(productRow.querySelector('.rental-period-input').value) || 1;
        
        // Calculate monthly total based on per unit price and quantity
        const monthlyTotal = perUnitPrice * quantity;
        
        // Calculate net amount based on monthly total and rental period
        const netAmount = monthlyTotal * rentalPeriod;
        
        // Update the fields (only if they haven't been manually edited)
        const monthlyTotalInput = productRow.querySelector('.monthly-total-input');
        const netAmountInput = productRow.querySelector('.net-amount-input');
        
        if (!monthlyTotalInput.hasAttribute('data-manually-edited')) {
            monthlyTotalInput.value = monthlyTotal.toFixed(2);
        }
        
        if (!netAmountInput.hasAttribute('data-manually-edited')) {
            netAmountInput.value = netAmount.toFixed(2);
        }
        
        calculateGrandTotal();
    }

    // Calculate grand total
    function calculateGrandTotal() {
        let subtotal = 0;
        let totalVat = 0;
        let totalDiscount = 0;
        
        const transactionType = document.getElementById('transaction_type').value;
        const productRows = document.querySelectorAll('.product-row');
        
        productRows.forEach(row => {
            if (transactionType === 'purchase') {
                const netAmount = parseFloat(row.querySelector('.net-amount-input').value) || 0;
                subtotal += netAmount;
            } else if (transactionType === 'rental' || transactionType === 'rent_to_own') {
                const netAmount = parseFloat(row.querySelector('.net-amount-input').value) || 0;
                subtotal += netAmount;
            }
        });
        
        const grandTotal = subtotal + totalVat - totalDiscount;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalVat').textContent = totalVat.toFixed(2);
        document.getElementById('totalDiscount').textContent = totalDiscount.toFixed(2);
        document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    }

    // Remove product row
    function removeProductRow(rowId) {
        const productRow = document.getElementById(rowId);
        if (productRow) {
            const productSelect = productRow.querySelector('.product-select');
            if (productSelect && productSelect.value) {
                selectedProducts.delete(productSelect.value);
            }
            productRow.remove();
            updateRemoveButtons();
            calculateGrandTotal();
        }
    }

    // Update remove buttons visibility
    function updateRemoveButtons() {
        const productRows = document.querySelectorAll('.product-row');
        const removeButtons = document.querySelectorAll('.remove-product-btn');
        
        if (productRows.length === 1) {
            removeButtons.forEach(btn => {
                btn.style.display = 'none';
            });
        } else {
            removeButtons.forEach(btn => {
                btn.style.display = 'block';
            });
        }
    }

    // Add product button event listener
    document.getElementById('addProductBtn').addEventListener('click', function() {
        addProductRow();
    });

    // Transaction type change event listener
    document.getElementById('transaction_type').addEventListener('change', function() {
        const transactionType = this.value;
        const productRows = document.querySelectorAll('.product-row');
        
        // Recreate all product rows with new structure
        productRows.forEach(row => {
            row.remove();
        });
        
        // Reset counter and selected products
        productRowCounter = 0;
        selectedProducts.clear();
        
        // Re-add existing items with new structure
        if (existingPurchaseItems && existingPurchaseItems.length > 0) {
            existingPurchaseItems.forEach((item, index) => {
                addProductRow(item);
            });
        } else {
            addProductRow();
        }
    });

    // Form submission validation
    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        const transactionType = document.getElementById('transaction_type').value;
        
        if (!transactionType) {
            e.preventDefault();
            alert('Please select a transaction type.');
            return;
        }
        
        const productRows = document.querySelectorAll('.product-row');
        if (productRows.length === 0) {
            e.preventDefault();
            alert('Please add at least one product.');
            return;
        }
        
        // Validate each product row
        let isValid = true;
        productRows.forEach(row => {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            
            if (!productSelect.value || !quantityInput.value) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields for each product.');
            return;
        }
        
        
    });

    // Initialize calculations on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateGrandTotal();
        updateRemoveButtons();
    });
</script>
