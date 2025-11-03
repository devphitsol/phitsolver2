<?php

namespace App\Controllers;

use App\Models\Purchase;
use App\Models\Product;

class PurchaseController
{
    private $purchaseModel;
    private $productModel;

    public function __construct()
    {
        $this->purchaseModel = new Purchase();
        $this->productModel = new Product();
    }

    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $companyId = $_GET['company_id'] ?? null;
        $productId = $_GET['product_id'] ?? null;
        
        $data = $this->purchaseModel->getAll($page, 10, $search, $companyId, $productId);
        
        return [
            'purchases' => $data['purchases'],
            'totalPurchases' => $data['total'],
            'pendingPurchases' => $this->purchaseModel->getStatusCount('pending', $companyId),
            'completedPurchases' => $this->purchaseModel->getStatusCount('completed', $companyId),
            'cancelledPurchases' => $this->purchaseModel->getStatusCount('cancelled', $companyId),
            'currentPage' => $data['page'],
            'totalPages' => $data['totalPages'],
            'search' => $search,
            'companyId' => $companyId,
            'productId' => $productId,
            'paymentMethods' => $this->purchaseModel->getPaymentMethods(),
            'purchaseStatuses' => $this->purchaseModel->getPurchaseStatuses()
        ];
    }

    public function create()
    {
        $companyId = $_GET['company_id'] ?? null;
        $productId = $_GET['product_id'] ?? null;
        
        $products = [];
        
        // Load products - if companyId is provided, try to filter by company, otherwise load all products
        if ($companyId) {
            // First try to get products for this specific company
            $productData = $this->productModel->getAll(1, 1000, '', $companyId);
            $products = $productData['products'];
            
            // If no products found for this company, load all products and show a warning
            if (empty($products)) {
                $productData = $this->productModel->getAll(1, 1000, '');
                $products = $productData['products'];
            }
        } else {
            // Load all products if no company is specified
            $productData = $this->productModel->getAll(1, 1000, '');
            $products = $productData['products'];
        }
        
        return [
            'products' => $products,
            'companyId' => $companyId,
            'productId' => $productId,
            'paymentMethods' => $this->purchaseModel->getPaymentMethods(),
            'purchaseStatuses' => $this->purchaseModel->getPurchaseStatuses()
        ];
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=purchases&method=create');
            exit;
        }

        // Handle multiple products
        $purchaseItems = $_POST['purchaseItems'] ?? [];
        
        // Convert purchase_items to array if it's an object
        if (is_object($purchaseItems) || (is_array($purchaseItems) && !empty($purchaseItems) && !is_numeric(key($purchaseItems)))) {
            $purchaseItems = array_values((array)$purchaseItems);
        }
        
        $companyId = $_POST['company_id'] ?? $_GET['company_id'] ?? '';
        
        // Validation for purchase items
        if (empty($purchaseItems)) {
            $_SESSION['error'] = 'At least one product is required.';
            header('Location: index.php?action=purchases&method=create');
            exit;
        }

        // Validate each purchase item
        foreach ($purchaseItems as $index => $item) {
            if (empty($item['product_id'])) {
                $_SESSION['error'] = 'Product selection is required for all items.';
                header('Location: index.php?action=purchases&method=create');
                exit;
            }
            
            if (empty($item['quantity']) || $item['quantity'] <= 0) {
                $_SESSION['error'] = 'Quantity must be greater than 0 for all items.';
                header('Location: index.php?action=purchases&method=create');
                exit;
            }
            
            // Check for different price fields based on transaction type
            $transactionType = $_POST['transaction_type'] ?? 'purchase';
            if ($transactionType === 'purchase') {
                if (empty($item['unit_price']) || $item['unit_price'] < 0) {
                    $_SESSION['error'] = 'Unit price cannot be negative for any item.';
                    header('Location: index.php?action=purchases&method=create');
                    exit;
                }
            } else if ($transactionType === 'rental' || $transactionType === 'rent_to_own') {
                if (empty($item['per_unit_price']) || $item['per_unit_price'] < 0) {
                    $_SESSION['error'] = 'Per unit price cannot be negative for any item.';
                    header('Location: index.php?action=purchases&method=create');
                    exit;
                }
                if (empty($item['rental_period']) || $item['rental_period'] <= 0) {
                    $_SESSION['error'] = 'Rental period must be greater than 0 for any item.';
                    header('Location: index.php?action=purchases&method=create');
                    exit;
                }
            }
            
            // Process serial numbers for each item
            if (!empty($item['serial_numbers'])) {
                $purchaseItems[$index]['serial_numbers'] = $this->parseSerialNumbers($item['serial_numbers']);
            } else {
                $purchaseItems[$index]['serial_numbers'] = [];
            }
        }

        // Calculate grand total with VAT and discount
        $grandTotal = 0;
        $subtotal = 0;
        $totalVat = 0;
        $totalDiscount = 0;
        $transactionType = $_POST['transaction_type'] ?? 'purchase';
        
        foreach ($purchaseItems as $item) {
            $quantity = (float)($item['quantity'] ?? 0);
            $discount = (float)($item['discount'] ?? 0);
            $vat = (float)($item['vat'] ?? 0);
            
            if ($transactionType === 'purchase') {
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $rowSubtotal = ($unitPrice - $discount) * $quantity;
                $rowTotal = $rowSubtotal + $vat;
            } else if ($transactionType === 'rental' || $transactionType === 'rent_to_own') {
                $perUnitPrice = (float)($item['per_unit_price'] ?? 0);
                $rentalPeriod = (float)($item['rental_period'] ?? 1);
                $monthlyTotal = $perUnitPrice * $quantity;
                $netAmount = (float)($item['net_amount'] ?? ($monthlyTotal * $rentalPeriod));
                
                $rowSubtotal = $netAmount - $discount;
                $rowTotal = $rowSubtotal + $vat;
            } else {
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $rowSubtotal = ($unitPrice - $discount) * $quantity;
                $rowTotal = $rowSubtotal + $vat;
            }
            
            $subtotal += $rowSubtotal;
            $totalVat += $vat;
            $totalDiscount += $discount * $quantity;
            $grandTotal += $rowTotal;
        }

        $data = [
            'transaction_type' => $_POST['transaction_type'] ?? 'purchase',
            'purchase_items' => $purchaseItems, // Store multiple products
            'company_id' => $companyId,
            'subtotal' => $subtotal,
            'total_vat' => $totalVat,
            'total_discount' => $totalDiscount,
            'grand_total' => $grandTotal,
            'order_date' => $_POST['order_date'] ?? date('Y-m-d'),
            'purchase_order_date' => $_POST['purchase_order_date'] ?? '',
            'delivery_date' => $_POST['delivery_date'] ?? '',
            'po_number' => $_POST['po_number'] ?? '',
            'reference_no' => $_POST['reference_no'] ?? '',
            'payment_method' => $_POST['payment_method'] ?? '',
            'payment_terms' => $_POST['payment_terms'] ?? '',
            'reminder_payment' => $_POST['reminder_payment'] ?? '',
            'purchase_status' => 'pending', // Default status
            'invoice' => $_POST['invoice'] ?? '',
            'warranty_period' => $_POST['warranty_period'] ?? '',
            'serial_numbers' => $_POST['serial_numbers'] ?? [], // Handle multiple serial numbers
            'asset_tags' => $_POST['asset_tags'] ?? [], // Handle multiple asset tags
            'notes' => $_POST['notes'] ?? ''
        ];

        // Debug: Log the data being saved
        error_log('Purchase Data to Save: ' . json_encode($data));
        error_log('Purchase Items Count: ' . count($purchaseItems));
        foreach ($purchaseItems as $index => $item) {
            error_log("Purchase Item {$index}: " . json_encode($item));
        }

        // Handle file upload for invoice
        if (isset($_FILES['invoice_file']) && $_FILES['invoice_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleInvoiceUpload($_FILES['invoice_file']);
            if ($uploadResult['success']) {
                $data['invoice_file'] = $uploadResult['filepath'];
            } else {
                $_SESSION['error'] = $uploadResult['message'];
                header('Location: index.php?action=purchases&method=create');
                exit;
            }
        }

        // Validation
        if (empty($data['company_id'])) {
            $_SESSION['error'] = 'Company is required.';
            header('Location: index.php?action=purchases&method=create');
            exit;
        }

        if (empty($data['transaction_type'])) {
            $_SESSION['error'] = 'Transaction type is required.';
            header('Location: index.php?action=purchases&method=create');
            exit;
        }

        try {
            $purchaseId = $this->purchaseModel->create($data);
            
            // Log the change for audit trail
            $this->logPurchaseChange($purchaseId, 'created', $data);
            
            $_SESSION['success'] = 'Purchase order created successfully with ' . count($purchaseItems) . ' product(s).';
            
            $redirectUrl = 'index.php?action=purchases';
            if ($data['company_id']) {
                $redirectUrl .= '&company_id=' . $data['company_id'];
            }
            header('Location: ' . $redirectUrl);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to create purchase order. Please try again.';
            header('Location: index.php?action=purchases&method=create');
            exit;
        }
    }

    public function edit($id)
    {
        $purchase = $this->purchaseModel->getById($id);
        
        if (!$purchase) {
            $_SESSION['error'] = 'Purchase order not found.';
            header('Location: index.php?action=purchases');
            exit;
        }

        // Debug: Log the loaded purchase data
        error_log('Loaded Purchase Data: ' . json_encode($purchase));
        error_log('Purchase Items: ' . json_encode($purchase['purchase_items'] ?? []));
        error_log('Purchase Items Count: ' . count($purchase['purchase_items'] ?? []));

        $companyId = $purchase['company_id'] ?? null;
        $products = [];
        
        // Load all products for editing - user should be able to select any product
        $productData = $this->productModel->getAll(1, 1000, '');
        $products = $productData['products'];
        
        // Debug: Log the loaded products
        error_log('Loaded Products Count: ' . count($products));
        
        // If no products found, try to load products for the specific company
        if (empty($products) && $companyId) {
            $productData = $this->productModel->getAll(1, 1000, '', $companyId);
            $products = $productData['products'];
        }
        
        return [
            'purchase' => $purchase,
            'products' => $products,
            'paymentMethods' => $this->purchaseModel->getPaymentMethods(),
            'purchaseStatuses' => $this->purchaseModel->getPurchaseStatuses()
        ];
    }

    public function view($id)
    {
        $purchase = $this->purchaseModel->getById($id);
        
        if (!$purchase) {
            $_SESSION['error'] = 'Purchase order not found.';
            header('Location: index.php?action=purchases');
            exit;
        }

        // Get company information if available
        $companyInfo = null;
        if ($purchase['company_id'] ?? null) {
            $userController = new \App\Controllers\UserController();
            $companyInfo = $userController->getUserById($purchase['company_id']);
        }

        return [
            'purchase' => $purchase,
            'companyInfo' => $companyInfo
        ];
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=purchases&method=edit&id=' . $id);
            exit;
        }

        // Debug: Log the received data
        error_log('PurchaseController::update - POST data: ' . json_encode($_POST));
        error_log('PurchaseController::update - Purchase items: ' . json_encode($_POST['purchaseItems'] ?? []));

        // Handle multiple products
        $purchaseItems = $_POST['purchaseItems'] ?? [];
        
        // Convert purchase_items to array if it's an object
        if (is_object($purchaseItems) || (is_array($purchaseItems) && !empty($purchaseItems) && !is_numeric(key($purchaseItems)))) {
            $purchaseItems = array_values((array)$purchaseItems);
        }
        
        $companyId = $_POST['company_id'] ?? $_GET['company_id'] ?? '';
        
        // Validation for purchase items
        if (empty($purchaseItems)) {
            $_SESSION['error'] = 'At least one product is required.';
            header('Location: index.php?action=purchases&method=edit&id=' . $id);
            exit;
        }

        // Validate each purchase item
        foreach ($purchaseItems as $index => $item) {
            if (empty($item['product_id'])) {
                $_SESSION['error'] = 'Product selection is required for all items.';
                header('Location: index.php?action=purchases&method=edit&id=' . $id);
                exit;
            }
            
            if (empty($item['quantity']) || $item['quantity'] <= 0) {
                $_SESSION['error'] = 'Quantity must be greater than 0 for all items.';
                header('Location: index.php?action=purchases&method=edit&id=' . $id);
                exit;
            }
            
            // Check for different price fields based on transaction type
            $transactionType = $_POST['transaction_type'] ?? 'purchase';
            if ($transactionType === 'purchase') {
                if (empty($item['unit_price']) || $item['unit_price'] < 0) {
                    $_SESSION['error'] = 'Unit price cannot be negative for any item.';
                    header('Location: index.php?action=purchases&method=edit&id=' . $id);
                    exit;
                }
            } else if ($transactionType === 'rental' || $transactionType === 'rent_to_own') {
                if (empty($item['per_unit_price']) || $item['per_unit_price'] < 0) {
                    $_SESSION['error'] = 'Per unit price cannot be negative for any item.';
                    header('Location: index.php?action=purchases&method=edit&id=' . $id);
                    exit;
                }
                if (empty($item['rental_period']) || $item['rental_period'] <= 0) {
                    $_SESSION['error'] = 'Rental period must be greater than 0 for any item.';
                    header('Location: index.php?action=purchases&method=edit&id=' . $id);
                    exit;
                }
            }
            
            // Process serial numbers for each item
            if (!empty($item['serial_numbers'])) {
                $purchaseItems[$index]['serial_numbers'] = $this->parseSerialNumbers($item['serial_numbers']);
            } else {
                $purchaseItems[$index]['serial_numbers'] = [];
            }
        }

        // Calculate grand total
        $grandTotal = 0;
        $subtotal = 0;
        $totalVat = 0;
        $totalDiscount = 0;
        $transactionType = $_POST['transaction_type'] ?? 'purchase';
        
        foreach ($purchaseItems as $item) {
            $quantity = (float)($item['quantity'] ?? 0);
            $discount = (float)($item['discount'] ?? 0);
            $vat = (float)($item['vat'] ?? 0);
            
            if ($transactionType === 'purchase') {
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $rowSubtotal = ($unitPrice - $discount) * $quantity;
                $rowTotal = $rowSubtotal + $vat;
            } else if ($transactionType === 'rental' || $transactionType === 'rent_to_own') {
                $perUnitPrice = (float)($item['per_unit_price'] ?? 0);
                $rentalPeriod = (float)($item['rental_period'] ?? 1);
                $monthlyTotal = $perUnitPrice * $quantity;
                $netAmount = (float)($item['net_amount'] ?? ($monthlyTotal * $rentalPeriod));
                
                $rowSubtotal = $netAmount - $discount;
                $rowTotal = $rowSubtotal + $vat;
            } else {
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $rowSubtotal = ($unitPrice - $discount) * $quantity;
                $rowTotal = $rowSubtotal + $vat;
            }
            
            $subtotal += $rowSubtotal;
            $totalVat += $vat;
            $totalDiscount += $discount;
            $grandTotal += $rowTotal;
        }

        $data = [
            'transaction_type' => $_POST['transaction_type'] ?? 'purchase',
            'purchase_items' => $purchaseItems, // Store multiple products
            'company_id' => $companyId,
            'subtotal' => $subtotal,
            'total_vat' => $totalVat,
            'total_discount' => $totalDiscount,
            'grand_total' => $grandTotal,
            'order_date' => $_POST['order_date'] ?? null,
            'purchase_order_date' => $_POST['purchase_order_date'] ?? null,
            'delivery_date' => $_POST['delivery_date'] ?? null,
            'po_number' => $_POST['po_number'] ?? '',
            'invoice' => $_POST['invoice'] ?? '',
            'warranty_period' => $_POST['warranty_period'] ?? '',
            'payment_method' => $_POST['payment_method'] ?? '',
            'payment_terms' => $_POST['payment_terms'] ?? '',
            'reminder_payment' => $_POST['reminder_payment'] ?? null,
            'notes' => $_POST['notes'] ?? ''
        ];

        // Validation
        if (empty($data['company_id'])) {
            $_SESSION['error'] = 'Company is required.';
            header('Location: index.php?action=purchases&method=edit&id=' . $id);
            exit;
        }

        if (empty($data['transaction_type'])) {
            $_SESSION['error'] = 'Transaction type is required.';
            header('Location: index.php?action=purchases&method=edit&id=' . $id);
            exit;
        }

        // Debug: Log the data being saved
        error_log('PurchaseController::update - Data being saved: ' . json_encode($data));
        
        try {
            $success = $this->purchaseModel->update($id, $data);
            
            if ($success) {
                $_SESSION['success'] = 'Purchase order updated successfully with ' . count($purchaseItems) . ' product(s).';
            } else {
                $_SESSION['error'] = 'No changes were made to the purchase order.';
            }
            
            $redirectUrl = 'index.php?action=purchases';
            if ($data['company_id']) {
                $redirectUrl .= '&company_id=' . $data['company_id'];
            }
            header('Location: ' . $redirectUrl);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to update purchase order. Please try again.';
            header('Location: index.php?action=purchases&method=edit&id=' . $id);
            exit;
        }
    }

    public function delete($id)
    {
        try {
            $success = $this->purchaseModel->delete($id);
            
            if ($success) {
                $_SESSION['success'] = 'Purchase order deleted successfully.';
            } else {
                $_SESSION['error'] = 'Purchase order not found or already deleted.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to delete purchase order. Please try again.';
        }
        
        header('Location: index.php?action=purchases');
        exit;
    }

    public function search()
    {
        $query = $_GET['q'] ?? '';
        $companyId = $_GET['company_id'] ?? null;
        
        if (empty($query)) {
            header('Location: index.php?action=purchases');
            exit;
        }

        $data = $this->purchaseModel->getAll(1, 50, $query, $companyId);
        
        return [
            'purchases' => $data['purchases'],
            'searchQuery' => $query,
            'totalResults' => $data['total'],
            'companyId' => $companyId
        ];
    }

    /**
     * Handle invoice file upload
     */
    private function handleInvoiceUpload($file)
    {
        $uploadDir = 'public/uploads/purchases/invoices/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Only PDF and image files are allowed.'
            ];
        }
        
        // Validate file size
        if ($file['size'] > $maxFileSize) {
            return [
                'success' => false,
                'message' => 'File size must be less than 5MB.'
            ];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filepath' => $filepath,
                'filename' => $filename
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to upload file. Please try again.'
            ];
        }
    }

    /**
     * Log purchase changes for audit trail
     */
    private function logPurchaseChange($purchaseId, $action, $data)
    {
        try {
            $changeLog = [
                'purchase_id' => $purchaseId,
                'action' => $action,
                'changed_by' => $_SESSION['admin_user_id'] ?? 'system',
                'changed_at' => new \MongoDB\BSON\UTCDateTime(),
                'data' => $data
            ];
            
            // Store in change log collection (you can create a separate collection for this)
            $db = new \MongoDB\Client($_ENV['MONGODB_URI']);
            $collection = $db->selectDatabase($_ENV['MONGODB_DATABASE'])->selectCollection('purchase_changes');
            $collection->insertOne($changeLog);
            
        } catch (\Exception $e) {
            error_log('Failed to log purchase change: ' . $e->getMessage());
        }
    }
    
    /**
     * Parse serial numbers from text input
     * Supports comma-separated, newline-separated, and space-separated formats
     */
    private function parseSerialNumbers($text)
    {
        if (empty($text)) {
            return [];
        }
        
        // Split by commas, newlines, and spaces, then trim and filter empty values
        $serialNumbers = preg_split('/[,\n\s]+/', $text);
        $serialNumbers = array_map('trim', $serialNumbers);
        $serialNumbers = array_filter($serialNumbers, function($sn) {
            return !empty($sn);
        });
        
        return array_values($serialNumbers);
    }

    /**
     * Extract months from rental period text
     * Supports formats like "12 Months", "1 Year", etc.
     */
    private function extractMonthsFromPeriod($periodText)
    {
        if (empty($periodText)) {
            return 0;
        }
        
        $text = strtolower(trim($periodText));
        
        // Match patterns like "12 months", "1 year", etc.
        if (preg_match('/(\d+)\s*month/', $text, $matches)) {
            return (int)$matches[1];
        } elseif (preg_match('/(\d+)\s*year/', $text, $matches)) {
            return (int)$matches[1] * 12;
        }
        
        return 0;
    }
}