<?php
// Purchase Create Form
$currentAction = 'purchases';

// Ensure all variables are defined with defaults
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
                                <i class="fas fa-plus me-2"></i>
                                Create Purchase Order
                                <?php if ($companyInfo): ?>
                                    <span class="text-muted">- <?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></span>
                                <?php endif; ?>
                            </h5>
                            <p class="text-muted mb-0">Add a new purchase order with detailed information</p>
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
                    <form method="POST" action="index.php?action=purchases&method=store" id="purchaseForm">
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
                                    <option value="purchase">🛒 Purchase</option>
                                    <option value="rental">📅 Rental</option>
                                    <option value="rent_to_own">🏠 Rent to Own</option>
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
                                <div id="productsContainer">
                                    <!-- Product rows will be added here -->
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-outline-primary" id="addProductRow">
                                            <i class="fas fa-plus me-1"></i>
                                            Add Product
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Grand Total -->
                                <div class="row mt-4">
                                    <div class="col-md-6 offset-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">Subtotal:</h6>
                                                    <span>₱<span id="subtotal">0.00</span></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">Total VAT:</h6>
                                                    <span>₱<span id="totalVat">0.00</span></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">Total Discount:</h6>
                                                    <span>₱<span id="totalDiscount">0.00</span></span>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0">Grand Total:</h5>
                                                    <h4 class="mb-0 text-success">
                                                        ₱<span id="grandTotal">0.00</span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Order Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Additional Order Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- 1행: Order Date, Purchase Order Date, Delivery Date -->
                                <div class="row mb-3">
                                    <div class="col-md-4 mb-2">
                                        <label for="order_date" class="form-label">Order Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="order_date" name="order_date" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                        <div class="form-text">Actual order date</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="purchase_order_date" class="form-label">Purchase Order Date</label>
                                        <input type="date" class="form-control" id="purchase_order_date" name="purchase_order_date">
                                        <div class="form-text">Date on PO document</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="delivery_date" class="form-label">Delivery Date</label>
                                        <input type="date" class="form-control" id="delivery_date" name="delivery_date">
                                        <div class="form-text">Expected or actual delivery date</div>
                                    </div>
                                </div>

                                <!-- 2행: Purchase Order Number, Invoice Number, Warranty Period -->
                                <div class="row mb-3">
                                    <div class="col-md-4 mb-2">
                                        <label for="po_number" class="form-label">Purchase Order Number</label>
                                        <input type="text" class="form-control" id="po_number" name="po_number" 
                                               placeholder="e.g., po-2024-001">
                                        <div class="form-text">Internal order reference</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="invoice" class="form-label">Invoice Number</label>
                                        <input type="text" class="form-control" id="invoice" name="invoice" 
                                               placeholder="e.g., INV-2024-001">
                                        <div class="form-text">Invoice reference number</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="warranty_period" class="form-label">Warranty Period</label>
                                        <input type="text" class="form-control" id="warranty_period" name="warranty_period" 
                                               placeholder="e.g., 1 year, 2 years">
                                        <div class="form-text">Warranty duration</div>
                                    </div>
                                </div>

                                <!-- 3행: Payment Method, Payment Terms, Reminder for Payment -->
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label for="payment_method" class="form-label">Payment Method</label>
                                        <select class="form-select" id="payment_method" name="payment_method">
                                            <option value="">Select payment method</option>
                                            <option value="Credit Card">Credit Card</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Check">Check</option>
                                            <option value="Cash">Cash</option>
                                            <option value="PayPal">PayPal</option>
                                            <option value="Wire Transfer">Wire Transfer</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="form-text">Method of payment used</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="payment_terms" class="form-label">Payment Terms</label>
                                        <input type="text" class="form-control" id="payment_terms" name="payment_terms" 
                                               placeholder="e.g., 30 days, Net 60, etc.">
                                        <div class="form-text">Payment terms and conditions</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="reminder_payment" class="form-label">Reminder for Payment</label>
                                        <input type="date" class="form-control" id="reminder_payment" name="reminder_payment">
                                        <div class="form-text">Payment reminder date</div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Notes -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Additional notes or comments..."></textarea>
                                <div class="form-text">Any additional information about this purchase</div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Create Purchase Order
                                    </button>
                                    <a href="index.php?action=purchases<?php echo $companyId ? '&company_id=' . $companyId : ''; ?><?php echo $productId ? '&product_id=' . $productId : ''; ?>" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Serial Number Modal -->
<div class="modal fade" id="serialNumberModal" tabindex="-1" aria-labelledby="serialNumberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serialNumberModalLabel">
                    <i class="fas fa-barcode me-2"></i>
                    Manage Serial Numbers
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Product:</strong> <span id="modalProductName">-</span>
                            <br>
                            <strong>Quantity:</strong> <span id="modalProductQuantity">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="serialNumberInput" class="form-label">Add Serial Number</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="serialNumberInput" 
                                   placeholder="Enter serial number (e.g., SN123456, ABC-789-XYZ)">
                            <button class="btn btn-outline-primary" type="button" id="addSerialNumberBtn">
                                <i class="fas fa-plus me-1"></i>
                                Add
                            </button>
                        </div>
                        <div class="form-text">
                            Enter one serial number at a time. You can also paste multiple serial numbers separated by commas, newlines, or spaces.
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <label class="form-label">Current Serial Numbers</label>
                        <div id="serialNumbersList" class="border rounded p-3" style="min-height: 100px; max-height: 200px; overflow-y: auto;">
                            <div class="text-muted text-center py-4">
                                <i class="fas fa-barcode fa-2x mb-2"></i>
                                <br>
                                No serial numbers added yet
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSerialNumbersBtn">
                    <i class="fas fa-save me-1"></i>
                    Save Serial Numbers
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    
    const productsContainer = document.getElementById('productsContainer');
    const addProductButton = document.getElementById('addProductRow');
    const grandTotalElement = document.getElementById('grandTotal');
    const subtotalElement = document.getElementById('subtotal');
    const totalVatElement = document.getElementById('totalVat');
    const totalDiscountElement = document.getElementById('totalDiscount');
    
    // Transaction Type Change Handler
    const transactionTypeSelect = document.getElementById('transaction_type');
    
    
    
    // Store products data for dropdown population
    const productsData = <?php echo json_encode($products); ?>;
    let productRowCounter = 0;
    let selectedProducts = new Set(); // Track selected products for duplicate prevention
    
    // Serial Number Modal variables
    let currentProductRowId = null;
    let currentSerialNumbers = [];

    
    
    if (productsData.length === 0) {
        
    }

    // Initialize with one product row
    addProductRow();
    
    transactionTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        
        
        
        // Update all product rows to show/hide rental fields
        updateAllProductRows();
        
        // Update calculation method for all rows
        updateAllRowCalculations();
    });

    // Add Product Row Function
    function addProductRow() {
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
                            ${generateProductOptions()}
                        </select>
                        <input type="hidden" class="product-name-input" name="purchaseItems[${productRowCounter}][product_name]">
                    </div>
                    
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control quantity-input" 
                               name="purchaseItems[${productRowCounter}][quantity]" 
                               value="1" min="1" required>
                    </div>
                    
                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            Unit Price <span class="text-danger">*</span>
                            <i class="fas fa-edit text-info ms-1" title="Price can be modified for this purchase"></i>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control unit-price-input" 
                                   name="purchaseItems[${productRowCounter}][unit_price]" 
                                   value="0.00" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Net Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control net-amount-input" 
                                   name="purchaseItems[${productRowCounter}][net_amount]" 
                                   value="0.00" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="col-md-2 mb-2 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-outline-info serial-number-btn" 
                                onclick="openSerialNumberModal('${rowId}')" 
                                title="Manage Serial Numbers">
                            <i class="fas fa-barcode me-1"></i>
                            Serial Numbers
                        </button>
                        <button type="button" class="btn btn-outline-danger remove-product-btn" 
                                onclick="removeProductRow('${rowId}')" 
                                ${productRowCounter === 1 ? 'style="display: none;"' : ''}>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        } else if (transactionType === 'rental' || transactionType === 'rent_to_own') {
            // 🔸 If Transaction Type = Rental or Rent to Own
            productRow.innerHTML = `
                <!-- 1행 – 제품 정보 및 계약 기간 -->
                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select product-select" name="purchaseItems[${productRowCounter}][product_id]" required>
                            <option value="">Select a product</option>
                            ${generateProductOptions()}
                        </select>
                        <input type="hidden" class="product-name-input" name="purchaseItems[${productRowCounter}][product_name]">
                    </div>
                    
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control quantity-input" 
                               name="purchaseItems[${productRowCounter}][quantity]" 
                               value="1" min="1" required>
                    </div>
                    
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Rental Period (Months) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rental-period-input" 
                               name="purchaseItems[${productRowCounter}][rental_period]" 
                               value="1" min="1" max="36" required>
                    </div>
                    
                    <div class="col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger remove-product-btn" 
                                onclick="removeProductRow('${rowId}')" 
                                ${productRowCounter === 1 ? 'style="display: none;"' : ''}>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <!-- 2행 – 가격 관련 필드 -->
                <div class="row">
                                         <div class="col-md-3 mb-2">
                         <label class="form-label">
                             Per Unit (Monthly)
                             <i class="fas fa-edit text-info ms-1" title="Price can be modified for this rental"></i>
                         </label>
                         <div class="input-group">
                             <span class="input-group-text">₱</span>
                             <input type="number" class="form-control per-unit-price-input" 
                                    name="purchaseItems[${productRowCounter}][per_unit_price]" 
                                    value="0.00" min="0" step="0.01">
                         </div>
                     </div>
                     
                     <div class="col-md-3 mb-2">
                         <label class="form-label">
                             Monthly Total
                             <i class="fas fa-edit text-info ms-1" title="Monthly total can be modified"></i>
                         </label>
                         <div class="input-group">
                             <span class="input-group-text">₱</span>
                             <input type="number" class="form-control monthly-total-input" 
                                    name="purchaseItems[${productRowCounter}][monthly_total]" 
                                    value="0.00" min="0" step="0.01">
                         </div>
                     </div>
                     
                     <div class="col-md-3 mb-2">
                         <label class="form-label">
                             Net Amount
                             <i class="fas fa-edit text-info ms-1" title="Net amount can be modified"></i>
                         </label>
                         <div class="input-group">
                             <span class="input-group-text">₱</span>
                             <input type="number" class="form-control net-amount-input" 
                                    name="purchaseItems[${productRowCounter}][net_amount]" 
                                    value="0.00" min="0" step="0.01">
                         </div>
                     </div>
                     
                     <div class="col-md-3 mb-2 d-flex align-items-end">
                         <button type="button" class="btn btn-outline-info serial-number-btn" 
                                 onclick="openSerialNumberModal('${rowId}')" 
                                 title="Manage Serial Numbers">
                             <i class="fas fa-barcode me-1"></i>
                             Serial Numbers
                         </button>
                     </div>
                </div>
            `;
        }
        
        productsContainer.appendChild(productRow);
        
        // Add event listeners to the new row
        addProductRowEventListeners(productRow);
        
        updateRemoveButtons();
    }
    
    // Generate product options for dropdown
    function generateProductOptions() {
        let options = '';
        productsData.forEach(product => {
            // Ensure product ID is a string
            const productId = typeof product._id === 'object' ? product._id.toString() : product._id;
            options += `<option value="${productId}" 
                                data-name="${product.name}" 
                                data-price="${product.price || 0}">
                            ${product.name}
                        </option>`;
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
            
            // Quantity and unit price change events
            quantityInput.addEventListener('input', () => calculatePurchaseRowTotal(productRow));
            unitPriceInput.addEventListener('input', () => calculatePurchaseRowTotal(productRow));
            
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
            
            // Quantity, rental period change events
            quantityInput.addEventListener('input', () => calculateRentalRowTotal(productRow));
            rentalPeriodInput.addEventListener('input', () => calculateRentalRowTotal(productRow));
            
                         // Make per unit price, monthly total, and net amount editable
             perUnitPriceInput.addEventListener('input', function() {
                 calculateRentalRowTotal(productRow);
             });
             
             monthlyTotalInput.addEventListener('input', function() {
                 // Mark as manually edited
                 this.setAttribute('data-manually-edited', 'true');
                 // Clear the flag after a short delay to allow for auto-calculation
                 setTimeout(() => {
                     this.removeAttribute('data-manually-edited');
                 }, 1000);
                 calculateGrandTotal();
             });
             
             netAmountInput.addEventListener('input', function() {
                 // Mark as manually edited
                 this.setAttribute('data-manually-edited', 'true');
                 // Clear the flag after a short delay to allow for auto-calculation
                 setTimeout(() => {
                     this.removeAttribute('data-manually-edited');
                 }, 1000);
                 calculateGrandTotal();
             });
        }
    }
    
    // Handle product selection
    function handleProductSelection(selectElement, productRow, type) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const productNameInput = productRow.querySelector('.product-name-input');
        
        if (selectedOption.value) {
            const productId = selectedOption.value;
            const productName = selectedOption.dataset.name;
            const basePrice = parseFloat(selectedOption.dataset.price) || 0;
            
            // Check for duplicate product selection
            const currentRowProductId = selectElement.getAttribute('data-current-product-id');
            const isDuplicate = selectedProducts.has(productId) && productId !== currentRowProductId;
            
            if (isDuplicate) {
                alert('This product is already selected in another row. Please choose a different product.');
                selectElement.value = currentRowProductId || '';
                return;
            }
            
            // Remove the previous product ID from selectedProducts if it exists
            if (currentRowProductId && currentRowProductId !== productId) {
                selectedProducts.delete(currentRowProductId);
            }
            
            // Update selected products set
            selectedProducts.add(productId);
            selectElement.setAttribute('data-current-product-id', productId);
            
            // Set product name
            productNameInput.value = productName;
            
            if (type === 'purchase') {
                // Set unit price for purchase
                const unitPriceInput = productRow.querySelector('.unit-price-input');
                unitPriceInput.value = basePrice.toFixed(2);
                calculatePurchaseRowTotal(productRow);
            } else if (type === 'rental') {
                // Set per unit price for rental
                const perUnitPriceInput = productRow.querySelector('.per-unit-price-input');
                perUnitPriceInput.value = basePrice.toFixed(2);
                calculateRentalRowTotal(productRow);
            }
        } else {
            productNameInput.value = '';
            if (type === 'purchase') {
                const unitPriceInput = productRow.querySelector('.unit-price-input');
                const netAmountInput = productRow.querySelector('.net-amount-input');
                unitPriceInput.value = '0.00';
                netAmountInput.value = '0.00';
            } else if (type === 'rental') {
                const perUnitPriceInput = productRow.querySelector('.per-unit-price-input');
                const monthlyTotalInput = productRow.querySelector('.monthly-total-input');
                const netAmountInput = productRow.querySelector('.net-amount-input');
                perUnitPriceInput.value = '0.00';
                monthlyTotalInput.value = '0.00';
                netAmountInput.value = '0.00';
            }
        }
    }
    
    // Calculate total for purchase row
    function calculatePurchaseRowTotal(productRow) {
        const quantity = parseFloat(productRow.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(productRow.querySelector('.unit-price-input').value) || 0;
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
         
         // Only auto-calculate if the field hasn't been manually edited recently
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
        
        const productRows = document.querySelectorAll('.product-row');
        productRows.forEach(row => {
            const netAmount = parseFloat(row.querySelector('.net-amount-input').value) || 0;
            subtotal += netAmount;
        });
        
        const grandTotal = subtotal;
        
        subtotalElement.textContent = subtotal.toFixed(2);
        totalVatElement.textContent = totalVat.toFixed(2);
        totalDiscountElement.textContent = totalDiscount.toFixed(2);
        grandTotalElement.textContent = grandTotal.toFixed(2);
    }
    
    // Update all product rows based on transaction type
    function updateAllProductRows() {
        const productRows = document.querySelectorAll('.product-row');
        productRows.forEach(row => {
            row.remove();
        });
        
        // Reset counter and selected products
        productRowCounter = 0;
        selectedProducts.clear();
        
        // Add new product rows with updated structure
        addProductRow();
    }
    
    // Remove product row
    window.removeProductRow = function(rowId) {
        const productRow = document.getElementById(rowId);
        const productSelect = productRow.querySelector('.product-select');
        
        // Remove from selected products set
        if (productSelect.value) {
            selectedProducts.delete(productSelect.value);
        }
        
        productRow.remove();
        calculateGrandTotal();
        updateRemoveButtons();
    }
    
    // Update remove buttons visibility
    function updateRemoveButtons() {
        const productRows = document.querySelectorAll('.product-row');
        const removeButtons = document.querySelectorAll('.remove-product-btn');
        
        removeButtons.forEach((button, index) => {
            if (productRows.length > 1) {
                button.style.display = 'block';
            } else {
                button.style.display = 'none';
            }
        });
    }
    
    // Add product button event
    addProductButton.addEventListener('click', addProductRow);
    
    // Update all row calculations
    function updateAllRowCalculations() {
        const productRows = document.querySelectorAll('.product-row');
        productRows.forEach(row => {
            const transactionType = document.getElementById('transaction_type').value;
            if (transactionType === 'purchase') {
                calculatePurchaseRowTotal(row);
            } else if (transactionType === 'rental' || transactionType === 'rent_to_own') {
                calculateRentalRowTotal(row);
            }
        });
    }

    // Serial Number Modal Functions
    window.openSerialNumberModal = function(rowId) {
        currentProductRowId = rowId;
        const productRow = document.getElementById(rowId);
        
        // Get product information
        const productSelect = productRow.querySelector('.product-select');
        const quantityInput = productRow.querySelector('.quantity-input');
        const productName = productSelect.options[productSelect.selectedIndex]?.text || 'Unknown Product';
        const quantity = quantityInput.value || '0';
        
        // Update modal information
        document.getElementById('modalProductName').textContent = productName;
        document.getElementById('modalProductQuantity').textContent = quantity;
        
        // Load existing serial numbers
        const serialNumbersInput = productRow.querySelector('.serial-numbers-input');
        if (serialNumbersInput && serialNumbersInput.value) {
            currentSerialNumbers = serialNumbersInput.value.split(',').map(sn => sn.trim()).filter(sn => sn);
        } else {
            currentSerialNumbers = [];
        }
        
        // Update modal display
        updateSerialNumbersDisplay();
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('serialNumberModal'));
        modal.show();
    };
    
    // Add serial number to the list
    document.getElementById('addSerialNumberBtn').addEventListener('click', function() {
        const input = document.getElementById('serialNumberInput');
        const serialNumber = input.value.trim();
        
        if (serialNumber) {
            // Check if it's a comma-separated list
            const serialNumbers = serialNumber.split(/[,\n\s]+/).map(sn => sn.trim()).filter(sn => sn);
            
            serialNumbers.forEach(sn => {
                if (sn && !currentSerialNumbers.includes(sn)) {
                    currentSerialNumbers.push(sn);
                }
            });
            
            input.value = '';
            updateSerialNumbersDisplay();
        }
    });
    
    // Handle Enter key in serial number input
    document.getElementById('serialNumberInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('addSerialNumberBtn').click();
        }
    });
    
    // Save serial numbers
    document.getElementById('saveSerialNumbersBtn').addEventListener('click', function() {
        if (currentProductRowId) {
            const productRow = document.getElementById(currentProductRowId);
            
            // Create or update hidden input for serial numbers
            let serialNumbersInput = productRow.querySelector('.serial-numbers-input');
            if (!serialNumbersInput) {
                serialNumbersInput = document.createElement('input');
                serialNumbersInput.type = 'hidden';
                serialNumbersInput.className = 'serial-numbers-input';
                serialNumbersInput.name = `purchaseItems[${currentProductRowId.split('-')[2]}][serial_numbers]`;
                productRow.appendChild(serialNumbersInput);
            }
            
            serialNumbersInput.value = currentSerialNumbers.join(',');
            
            // Update button text to show count
            const serialNumberBtn = productRow.querySelector('.serial-number-btn');
            if (currentSerialNumbers.length > 0) {
                serialNumberBtn.innerHTML = `<i class="fas fa-barcode me-1"></i>Serial Numbers (${currentSerialNumbers.length})`;
                serialNumberBtn.classList.remove('btn-outline-info');
                serialNumberBtn.classList.add('btn-info');
            } else {
                serialNumberBtn.innerHTML = `<i class="fas fa-barcode me-1"></i>Serial Numbers`;
                serialNumberBtn.classList.remove('btn-info');
                serialNumberBtn.classList.add('btn-outline-info');
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('serialNumberModal'));
            modal.hide();
        }
    });
    
    // Update serial numbers display in modal
    function updateSerialNumbersDisplay() {
        const container = document.getElementById('serialNumbersList');
        
        if (currentSerialNumbers.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-4">
                    <i class="fas fa-barcode fa-2x mb-2"></i>
                    <br>
                    No serial numbers added yet
                </div>
            `;
        } else {
            container.innerHTML = currentSerialNumbers.map((sn, index) => `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <span class="badge bg-primary me-2">${index + 1}</span>
                    <span class="flex-grow-1 font-monospace">${sn}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSerialNumber(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');
        }
    }
    
    // Remove serial number from list
    window.removeSerialNumber = function(index) {
        currentSerialNumbers.splice(index, 1);
        updateSerialNumbersDisplay();
    };

    // Form validation
    const form = document.getElementById('purchaseForm');
    form.addEventListener('submit', function(e) {
        
        
        // Check transaction type
        const transactionType = document.getElementById('transaction_type').value;
        if (!transactionType) {
            e.preventDefault();
            alert('Please select a transaction type');
            document.getElementById('transaction_type').focus();
            return;
        }
        
        const productRows = document.querySelectorAll('.product-row');
        let hasValidProduct = false;
        
        productRows.forEach(row => {
            const productSelect = row.querySelector('.product-select');
            const quantity = parseFloat(row.querySelector('.quantity-input').value);
            
            if (productSelect.value) {
                hasValidProduct = true;
                
                if (quantity <= 0) {
                    e.preventDefault();
                    alert('Quantity must be greater than 0');
                    row.querySelector('.quantity-input').focus();
                    return;
                }
            }
        });
        
        if (!hasValidProduct) {
            e.preventDefault();
            alert('Please select at least one product');
            return;
        }
        
        
        
        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        }
    });

    // Auto-fill current date for order date if empty
    if (!document.getElementById('order_date').value) {
        document.getElementById('order_date').value = new Date().toISOString().split('T')[0];
    }


});
</script>