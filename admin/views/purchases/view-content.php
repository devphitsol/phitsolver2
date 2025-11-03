<?php
// View Purchase Order
$currentAction = 'purchases';

// Ensure all variables are defined with defaults
$purchase = $purchase ?? [];
$companyInfo = $companyInfo ?? [];

// Helper functions
function formatDateForDisplay($date) {
    if (!$date) return 'N/A';
    if ($date instanceof \MongoDB\BSON\UTCDateTime) {
        return $date->toDateTime()->format('Y-m-d');
    }
    return date('Y-m-d', strtotime($date));
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function calculateDisplayTotal($item, $transactionType) {
    $quantity = $item['quantity'] ?? 1;
    
    if ($transactionType === 'purchase') {
        $unitPrice = $item['unit_price'] ?? 0;
        $discount = $item['discount'] ?? 0;
        $vat = $item['vat'] ?? 0;
        
        $subtotal = $quantity * $unitPrice;
        $total = $subtotal - $discount + $vat;
    } else if ($transactionType === 'rental' || $transactionType === 'rent_to_own') {
        $perUnitPrice = $item['per_unit_price'] ?? 0;
        $rentalPeriod = $item['rental_period'] ?? 1;
        $monthlyTotal = $perUnitPrice * $quantity;
        $netAmount = $item['net_amount'] ?? ($monthlyTotal * $rentalPeriod);
        $discount = $item['discount'] ?? 0;
        $vat = $item['vat'] ?? 0;
        
        $total = $netAmount - $discount + $vat;
    } else {
        $unitPrice = $item['unit_price'] ?? 0;
        $discount = $item['discount'] ?? 0;
        $vat = $item['vat'] ?? 0;
        
        $subtotal = $quantity * $unitPrice;
        $total = $subtotal - $discount + $vat;
    }
    
    return $total;
}

function calculateFinancialSummary($purchase) {
    $subtotal = 0;
    $totalVat = 0;
    $totalDiscount = 0;
    $grandTotal = 0;
    $totalQuantity = 0;
    $transactionType = $purchase['transaction_type'] ?? 'purchase';
    
    if (isset($purchase['purchase_items']) && is_array($purchase['purchase_items'])) {
        foreach ($purchase['purchase_items'] as $item) {
            $itemTotal = calculateDisplayTotal($item, $transactionType);
            $discount = $item['discount'] ?? 0;
            $vat = $item['vat'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            
            if ($transactionType === 'purchase') {
                $unitPrice = $item['unit_price'] ?? 0;
                $itemSubtotal = $unitPrice * $quantity;
            } else if ($transactionType === 'rental' || $transactionType === 'rent_to_own') {
                $perUnitPrice = $item['per_unit_price'] ?? 0;
                $rentalPeriod = $item['rental_period'] ?? 1;
                $monthlyTotal = $perUnitPrice * $quantity;
                $itemSubtotal = $monthlyTotal * $rentalPeriod;
            } else {
                $unitPrice = $item['unit_price'] ?? 0;
                $itemSubtotal = $unitPrice * $quantity;
            }
            
            $subtotal += $itemSubtotal;
            $totalVat += $vat;
            $totalDiscount += $discount;
            $grandTotal += $itemTotal;
            $totalQuantity += $quantity;
        }
    }
    
    return [
        'subtotal' => $subtotal,
        'totalVat' => $totalVat,
        'totalDiscount' => $totalDiscount,
        'grandTotal' => $grandTotal,
        'totalQuantity' => $totalQuantity
    ];
}

// Get transaction type for display
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

<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="fas fa-eye me-2"></i>
                                View Purchase Order
                                <?php if ($companyInfo): ?>
                                    <span class="text-muted">- <?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></span>
                                <?php endif; ?>
                            </h5>
                            <p class="text-muted mb-0">Purchase order details and information</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="index.php?action=purchases&method=edit&id=<?php echo $purchase['_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                Edit
                            </a>
                            <button type="button" class="btn btn-success" onclick="printPurchaseOrder()">
                                <i class="fas fa-print me-1"></i>
                                Print / PDF
                            </button>
                            <a href="index.php?action=purchases<?php echo ($purchase['company_id'] ?? null) ? '&company_id=' . $purchase['company_id'] : ''; ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Close
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Details Container -->
    <div class="purchase-details-container" style="
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        box-sizing: border-box;
    ">
        
        <!-- Transaction Type Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Transaction Type: 
                        <strong><?php echo $typeIcons[$transactionType] ?? '🛒'; ?> <?php echo $typeLabels[$transactionType] ?? 'Purchase'; ?></strong>
                    </h6>
                </div>
            </div>
        </div>

        <!-- Section 1: Purchase Items Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-boxes me-2"></i>
                            Products
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <?php if ($transactionType === 'purchase'): ?>
                                            <th>Unit Price (₱)</th>
                                        <?php else: ?>
                                            <th>Per Unit (₱)</th>
                                            <th>Rental Period (Months)</th>
                                            <th>Monthly Total (₱)</th>
                                        <?php endif; ?>
                                        <th>Net Amount (₱)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($purchase['purchase_items']) && is_array($purchase['purchase_items'])): ?>
                                        <?php foreach ($purchase['purchase_items'] as $index => $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($item['quantity'] ?? '1'); ?></td>
                                            <?php if ($transactionType === 'purchase'): ?>
                                                <td><?php echo formatCurrency($item['unit_price'] ?? 0); ?></td>
                                            <?php else: ?>
                                                <td><?php echo formatCurrency($item['per_unit_price'] ?? 0); ?></td>
                                                <td><?php echo htmlspecialchars($item['rental_period'] ?? 'N/A'); ?></td>
                                                <td><?php echo formatCurrency($item['monthly_total'] ?? 0); ?></td>
                                            <?php endif; ?>
                                            <td><strong><?php echo formatCurrency($item['net_amount'] ?? 0); ?></strong></td>
                                            <td>
                                                <?php 
                                                $serialNumbers = $item['serial_numbers'] ?? [];
                                                $serialCount = count($serialNumbers);
                                                $buttonText = $serialCount > 0 ? "Serial ({$serialCount})" : "Serial";
                                                $buttonClass = $serialCount > 0 ? "btn-outline-success" : "btn-outline-secondary";
                                                ?>
                                                <button type="button" 
                                                        class="btn btn-sm <?php echo $buttonClass; ?> serial-view-btn" 
                                                        onclick="viewSerialNumbers(<?php echo $index; ?>, '<?php echo htmlspecialchars($item['product_name'] ?? 'N/A'); ?>', <?php echo htmlspecialchars(json_encode($serialNumbers)); ?>)"
                                                        title="View Serial Numbers">
                                                    <i class="fas fa-barcode me-1"></i><?php echo $buttonText; ?>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="<?php echo $transactionType === 'purchase' ? '5' : '7'; ?>" class="text-center text-muted">No products found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Order Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Order Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold" style="width: 40%;">Order Date:</td>
                                                <td><?php echo formatDateForDisplay($purchase['order_date'] ?? null); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Purchase Order Date:</td>
                                                <td><?php echo formatDateForDisplay($purchase['purchase_order_date'] ?? null); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Delivery Date:</td>
                                                <td><?php echo formatDateForDisplay($purchase['delivery_date'] ?? null); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Purchase Order Number:</td>
                                                <td><?php echo htmlspecialchars($purchase['po_number'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Invoice Number:</td>
                                                <td><?php echo htmlspecialchars($purchase['invoice'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Warranty Period:</td>
                                                <td><?php echo htmlspecialchars($purchase['warranty_period'] ?? 'N/A'); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold" style="width: 40%;">Payment Method:</td>
                                                <td><?php echo htmlspecialchars($purchase['payment_method'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Payment Terms:</td>
                                                <td><?php echo htmlspecialchars($purchase['payment_terms'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Reminder for Payment:</td>
                                                <td><?php echo formatDateForDisplay($purchase['reminder_payment'] ?? null); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Company:</td>
                                                <td><?php echo htmlspecialchars($companyInfo['company_name'] ?? $companyInfo['name'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Transaction Type:</td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo $typeIcons[$transactionType] ?? '🛒'; ?> 
                                                        <?php echo $typeLabels[$transactionType] ?? 'Purchase'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Total Quantity:</td>
                                                <td>
                                                    <?php 
                                                    $totalQuantity = 0;
                                                    if (isset($purchase['purchase_items']) && is_array($purchase['purchase_items'])) {
                                                        foreach ($purchase['purchase_items'] as $item) {
                                                            $totalQuantity += (int)($item['quantity'] ?? 0);
                                                        }
                                                    }
                                                    echo $totalQuantity . ' units';
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Financial Summary -->
        <?php 
        // Calculate financial summary dynamically
        $financialSummary = calculateFinancialSummary($purchase);
        ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calculator me-2"></i>
                            Financial Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">Subtotal:</td>
                                                <td class="text-end"><?php echo formatCurrency($financialSummary['subtotal']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">VAT:</td>
                                                <td class="text-end"><?php echo formatCurrency($financialSummary['totalVat']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Discount:</td>
                                                <td class="text-end"><?php echo formatCurrency($financialSummary['totalDiscount']); ?></td>
                                            </tr>
                                            <tr class="border-top">
                                                <td class="fw-bold fs-5">Grand Total:</td>
                                                <td class="text-end fs-5 fw-bold text-primary"><?php echo formatCurrency($financialSummary['grandTotal']); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Additional Information -->
        <?php if (!empty($purchase['notes'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            Notes
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($purchase['notes'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Serial Number View Modal -->
        <div class="modal fade" id="serialNumberViewModal" tabindex="-1" aria-labelledby="serialNumberViewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="serialNumberViewModalLabel">
                            <i class="fas fa-barcode me-2"></i>
                            Serial Numbers - <span id="modalProductName"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="serialNumbersList">
                            <!-- Serial numbers will be populated here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Print Header (Hidden by default) -->
    <div class="print-header" style="display: none;">
        <div class="text-center mb-4">
            <h3>Purchase Order Details</h3>
            <p class="text-muted">Order ID: <?php echo htmlspecialchars($purchase['_id'] ?? 'N/A'); ?></p>
        </div>
    </div>
</div>

<style>
/* Print styles for A4 paper */
@media print {
    /* A4 Paper Settings - 210mm x 297mm */
    @page {
        size: A4 portrait;
        margin: 15mm 15mm 15mm 15mm;
    }
    
    /* Reset all elements for print */
    * {
        box-sizing: border-box !important;
    }
    
    /* Hide all navigation and interactive elements */
    .btn, 
    .card-header .d-flex,
    .sidebar,
    .navbar,
    .main-header,
    .breadcrumb,
    .page-header,
    .action-buttons,
    .no-print {
        display: none !important;
    }
    
    /* Show print header */
    .print-header {
        display: block !important;
        margin-bottom: 15px !important;
        page-break-after: avoid;
    }
    
    /* Clean text-based layout */
    body {
        font-family: Arial, sans-serif !important;
        font-size: 10pt !important;
        line-height: 1.3 !important;
        color: #000 !important;
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
    }
    
    /* Container adjustments for A4 */
    .container-fluid {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
    }
    
    .purchase-details-container {
        box-shadow: none !important;
        border: none !important;
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: none !important;
    }
    
    /* Card styling for print - optimized for A4 */
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        background: white !important;
        margin-bottom: 10px !important;
        page-break-inside: avoid;
        width: 100% !important;
        max-width: none !important;
    }
    
    .card-header {
        background: #f8f9fa !important;
        border-bottom: 1px solid #000 !important;
        padding: 6px 8px !important;
        page-break-after: avoid;
    }
    
    .card-header h6 {
        margin: 0 !important;
        font-size: 12pt !important;
        font-weight: bold !important;
        color: #000 !important;
    }
    
    .card-body {
        padding: 8px !important;
    }
    
    /* Table styling for print - compact for A4 */
    .table {
        border-collapse: collapse !important;
        width: 100% !important;
        margin-bottom: 0 !important;
        font-size: 9pt !important;
    }
    
    .table th,
    .table td {
        border: 1px solid #000 !important;
        padding: 4px 6px !important;
        text-align: left !important;
        vertical-align: top !important;
        font-size: 9pt !important;
        word-wrap: break-word !important;
        max-width: 0 !important;
    }
    
    .table th {
        background: #f8f9fa !important;
        font-weight: bold !important;
        text-align: center !important;
        font-size: 9pt !important;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background: #f8f9fa !important;
    }
    
    /* Remove badges and icons */
    .badge,
    .fas,
    .fa,
    .icon {
        display: none !important;
    }
    
    /* Hide action buttons in print */
    .serial-view-btn,
    .table th:last-child,
    .table td:last-child {
        display: none !important;
    }
    
    /* Alert styling - compact */
    .alert {
        border: 1px solid #000 !important;
        background: #f8f9fa !important;
        padding: 6px 8px !important;
        margin-bottom: 10px !important;
        font-size: 10pt !important;
    }
    
    .alert-info {
        background: #e7f3ff !important;
        border-color: #000 !important;
    }
    
    .alert h6 {
        font-size: 11pt !important;
        margin: 0 !important;
    }
    
    /* Text formatting */
    .text-muted {
        color: #666 !important;
    }
    
    .text-primary {
        color: #000 !important;
    }
    
    .fw-bold {
        font-weight: bold !important;
    }
    
    .fs-5 {
        font-size: 12pt !important;
    }
    
    /* Remove hover effects */
    .table-hover tbody tr:hover {
        background: inherit !important;
    }
    
    /* Page breaks - prevent awkward breaks */
    .card {
        page-break-inside: avoid;
    }
    
    .table {
        page-break-inside: auto;
    }
    
    .table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    /* Ensure proper spacing for A4 */
    .row {
        margin: 0 !important;
        width: 100% !important;
    }
    
    .col-12,
    .col-md-6,
    .col-md-8,
    .col-md-4 {
        padding: 0 !important;
        width: 100% !important;
        float: none !important;
    }
    
    /* Hide responsive elements */
    .table-responsive {
        overflow: visible !important;
        width: 100% !important;
    }
    
    /* Print header styling - compact */
    .print-header h3 {
        font-size: 16pt !important;
        font-weight: bold !important;
        margin-bottom: 8px !important;
        text-align: center !important;
    }
    
    .print-header p {
        font-size: 10pt !important;
        text-align: center !important;
        margin-bottom: 15px !important;
    }
    
    /* Force table columns to fit A4 width */
    .table th:nth-child(1),
    .table td:nth-child(1) { width: 25% !important; } /* Product Name */
    .table th:nth-child(2),
    .table td:nth-child(2) { width: 8% !important; }  /* Quantity */
    .table th:nth-child(3),
    .table td:nth-child(3) { width: 18% !important; } /* Unit Price/Per Unit */
    .table th:nth-child(4),
    .table td:nth-child(4) { width: 12% !important; } /* Rental Period */
    .table th:nth-child(5),
    .table td:nth-child(5) { width: 12% !important; } /* Monthly Total */
    .table th:nth-child(6),
    .table td:nth-child(6) { width: 15% !important; } /* Net Amount */
    .table th:nth-child(7),
    .table td:nth-child(7) { width: 10% !important; } /* Actions */
    
    /* Ensure text doesn't overflow */
    .table td {
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    
    /* Compact spacing for financial summary */
    .table-borderless td {
        padding: 3px 6px !important;
        font-size: 10pt !important;
    }
    
    /* Remove any floating elements */
    .d-flex {
        display: block !important;
    }
    
    .flex-column {
        display: block !important;
    }
    
    /* Ensure proper text alignment */
    .text-end {
        text-align: right !important;
    }
    
    .text-center {
        text-align: center !important;
    }
}

/* Responsive adjustments for sidebar */
@media (max-width: 768px) {
    .purchase-details-container {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
}

@media (max-width: 480px) {
    .purchase-details-container {
        padding: 0.75rem;
    }
    
    .card-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
    
    .table-responsive {
        font-size: 0.8rem;
    }
}

@media (max-width: 360px) {
    .purchase-details-container {
        padding: 0.5rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>

<script>
function printPurchaseOrder() {
    // Hide action buttons during print
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => btn.style.display = 'none');
    
    // Show print header
    const printHeader = document.querySelector('.print-header');
    if (printHeader) {
        printHeader.style.display = 'block';
    }
    
    // Print the page
    window.print();
    
    // Restore buttons after print
    setTimeout(() => {
        buttons.forEach(btn => btn.style.display = '');
        if (printHeader) {
            printHeader.style.display = 'none';
        }
    }, 1000);
}

// Serial Number Viewing Functions
function viewSerialNumbers(itemIndex, productName, serialNumbers) {
    // Set the product name in the modal
    document.getElementById('modalProductName').textContent = productName;
    
    // Get the serial numbers list container
    const serialNumbersList = document.getElementById('serialNumbersList');
    
    if (serialNumbers && serialNumbers.length > 0) {
        // Create a table to display serial numbers
        let html = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Found <strong>${serialNumbers.length}</strong> serial number(s) for this product.
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Serial Number</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        serialNumbers.forEach((serialNumber, index) => {
            html += `
                <tr>
                    <td class="text-center fw-bold">${index + 1}</td>
                    <td><code class="bg-light px-2 py-1 rounded">${serialNumber}</code></td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        serialNumbersList.innerHTML = html;
    } else {
        // No serial numbers found
        serialNumbersList.innerHTML = `
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>No serial numbers found</strong><br>
                No serial numbers have been recorded for this product.
            </div>
        `;
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('serialNumberViewModal'));
    modal.show();
}

// Ensure sidebar persistence on this page
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar && window.innerWidth > 1024) {
        // Ensure sidebar is always visible on desktop
        sidebar.style.transform = 'translateX(0)';
        sidebar.style.visibility = 'visible';
        sidebar.style.opacity = '1';
        sidebar.style.position = 'fixed';
        sidebar.style.left = '0';
        sidebar.style.top = '0';
        sidebar.style.height = '100vh';
        sidebar.style.zIndex = '1000';
    }
});
</script> 