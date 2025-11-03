<?php

namespace App\Controllers;

use App\Models\Product;

class ProductController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $companyId = $_GET['company_id'] ?? null;
        
        $data = $this->productModel->getAll($page, 10, $search, $companyId);
        
        return [
            'products' => $data['products'],
            'totalProducts' => $data['total'],
            'activeProducts' => $this->productModel->getActiveCount($companyId),
            'inactiveProducts' => $this->productModel->getInactiveCount($companyId),
            'currentPage' => $data['page'],
            'totalPages' => $data['totalPages'],
            'search' => $search,
            'companyId' => $companyId,
            'categories' => $this->productModel->getCategories()
        ];
    }

    public function create()
    {
        $categories = $this->productModel->getCategories();
        
        return [
            'categories' => $categories
        ];
    }

    private function handleImageUploads($existingImages = [])
    {
        $uploadedImages = $existingImages;
        
        // Handle file uploads
        if (isset($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
            // Use absolute path for upload directory
            $uploadDir = __DIR__ . '/../../admin/public/uploads/products/';
            
            // Ensure upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            $maxImages = 5;
            
            // Count existing images
            $currentImageCount = count($existingImages);
            
            for ($i = 0; $i < count($_FILES['product_images']['name']); $i++) {
                if ($_FILES['product_images']['error'][$i] === UPLOAD_ERR_OK) {
                    // Check if we've reached the maximum number of images
                    if (count($uploadedImages) >= $maxImages) {
                        break;
                    }
                    
                    $file = [
                        'name' => $_FILES['product_images']['name'][$i],
                        'type' => $_FILES['product_images']['type'][$i],
                        'tmp_name' => $_FILES['product_images']['tmp_name'][$i],
                        'error' => $_FILES['product_images']['error'][$i],
                        'size' => $_FILES['product_images']['size'][$i]
                    ];
                    
                    // Validate file type
                    if (!in_array($file['type'], $allowedTypes)) {
                        continue;
                    }
                    
                    // Validate file size
                    if ($file['size'] > $maxFileSize) {
                        continue;
                    }
                    
                    // Generate unique filename
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '_' . time() . '.' . $extension;
                    $filepath = $uploadDir . $filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        // Store relative path for database
                        $uploadedImages[] = 'public/uploads/products/' . $filename;
                    }
                }
            }
        }
        
        return $uploadedImages;
    }

    public function store()
    {
        
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("ProductController::store() - Not POST request, redirecting");
            header('Location: index.php?action=products&method=create');
            exit;
        }

        

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => $_POST['description'] ?? '', // Remove trim() to preserve HTML formatting
            'category' => trim($_POST['category'] ?? ''),
            'price' => $_POST['price'] ?? 0,
            'sku' => trim($_POST['sku'] ?? ''),
            'stock_quantity' => $_POST['stock_quantity'] ?? 0,
            'status' => $_POST['status'] ?? 'active',
            'image_url' => $_POST['image_url'] ?? '',
            'main_image' => $_POST['main_image'] ?? '',
            'company_id' => $_POST['company_id'] ?? $_GET['company_id'] ?? null // Add company_id
        ];

        

        // Sanitize HTML content while preserving safe tags
        $data['description'] = $this->sanitizeHtml($data['description']);
        

        // Validation
        if (empty($data['name'])) {
            
            $_SESSION['error'] = 'Product name is required.';
            header('Location: index.php?action=products&method=create');
            exit;
        }

        if (empty($data['short_description'])) {
            
            $_SESSION['error'] = 'Short description is required.';
            header('Location: index.php?action=products&method=create');
            exit;
        }

        if (empty($data['description'])) {
            
            $_SESSION['error'] = 'Description is required.';
            header('Location: index.php?action=products&method=create');
            exit;
        }

        if (empty($data['category'])) {
            
            $_SESSION['error'] = 'Product category is required.';
            header('Location: index.php?action=products&method=create');
            exit;
        }

        if ($data['price'] < 0) {
            
            $_SESSION['error'] = 'Price cannot be negative.';
            header('Location: index.php?action=products&method=create');
            exit;
        }

        if ($data['stock_quantity'] < 0) {
            
            $_SESSION['error'] = 'Stock quantity cannot be negative.';
            header('Location: index.php?action=products&method=create');
            exit;
        }

        

        // Handle image uploads
        $data['images'] = $this->handleImageUploads();
        

        try {
            $result = $this->productModel->create($data);
            $_SESSION['success'] = 'Product created successfully.';
            header('Location: index.php?action=products');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to create product. Please try again. Error: ' . $e->getMessage();
            header('Location: index.php?action=products&method=create');
            exit;
        }
    }

    public function edit($id)
    {
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $_SESSION['error'] = 'Product not found.';
            header('Location: index.php?action=products');
            exit;
        }

        $categories = $this->productModel->getCategories();
        
        return [
            'product' => $product,
            'categories' => $categories
        ];
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=products&method=edit&id=' . $id);
            exit;
        }

        // Debug logging
        error_log("ProductController::update() - POST data received: " . print_r($_POST, true));

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => $_POST['description'] ?? '', // Remove trim() to preserve HTML formatting
            'category' => trim($_POST['category'] ?? ''),
            'price' => $_POST['price'] ?? 0,
            'sku' => trim($_POST['sku'] ?? ''),
            'stock_quantity' => $_POST['stock_quantity'] ?? 0,
            'status' => $_POST['status'] ?? 'active',
            'image_url' => $_POST['image_url'] ?? '',
            'main_image' => $_POST['main_image'] ?? ''
        ];

        // Sanitize HTML content while preserving safe tags
        $data['description'] = $this->sanitizeHtml($data['description']);

        // Validation
        if (empty($data['name'])) {
            $_SESSION['error'] = 'Product name is required.';
            header('Location: index.php?action=products&method=edit&id=' . $id);
            exit;
        }

        if (empty($data['short_description'])) {
            $_SESSION['error'] = 'Short description is required.';
            header('Location: index.php?action=products&method=edit&id=' . $id);
            exit;
        }

        if (empty($data['description'])) {
            $_SESSION['error'] = 'Description is required.';
            header('Location: index.php?action=products&method=edit&id=' . $id);
            exit;
        }

        if (empty($data['category'])) {
            $_SESSION['error'] = 'Product category is required.';
            header('Location: index.php?action=products&method=edit&id=' . $id);
            exit;
        }

        if ($data['price'] < 0) {
            $_SESSION['error'] = 'Price cannot be negative.';
            header('Location: index.php?action=products&method=edit&id=' . $id);
            exit;
        }

        if ($data['stock_quantity'] < 0) {
            $_SESSION['error'] = 'Stock quantity cannot be negative.';
            header('Location: index.php?action=products&method=edit&id=' . $id);
            exit;
        }

        // Get existing product to preserve existing images
        $existingProduct = $this->productModel->getById($id);
        $existingImages = $existingProduct['images'] ?? [];
        
        // Handle existing images (filter out removed ones)
        if (isset($_POST['existing_images']) && is_array($_POST['existing_images'])) {
            $existingImages = array_filter($_POST['existing_images'], function($image) {
                return !empty(trim($image));
            });
        }
        
        // Handle image uploads (preserve existing images)
        $data['images'] = $this->handleImageUploads($existingImages);

        try {
            $success = $this->productModel->update($id, $data);
            
            if ($success) {
                $_SESSION['success'] = 'Product updated successfully.';
            } else {
                $_SESSION['error'] = 'No changes were made to the product.';
            }
            
            header('Location: index.php?action=products');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to update product. Please try again.';
            header('Location: index.php?action=products&method=edit&id=' . $id);
            exit;
        }
    }

    public function delete($id)
    {
        try {
            $success = $this->productModel->delete($id);
            
            if ($success) {
                $_SESSION['success'] = 'Product deleted successfully.';
            } else {
                $_SESSION['error'] = 'Product not found or already deleted.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to delete product. Please try again.';
        }
        
        header('Location: index.php?action=products');
        exit;
    }

    public function toggleStatus($id)
    {
        try {
            $success = $this->productModel->toggleStatus($id);
            
            if ($success) {
                $_SESSION['success'] = 'Product status updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update product status.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to update product status. Please try again.';
        }
        
        header('Location: index.php?action=products');
        exit;
    }

    public function search()
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            header('Location: index.php?action=products');
            exit;
        }

        $products = $this->productModel->search($query);
        
        return [
            'products' => $products,
            'searchQuery' => $query,
            'totalResults' => count($products),
            'categories' => $this->productModel->getCategories()
        ];
    }

    /**
     * Sanitize HTML content while preserving safe tags
     * @param string $html
     * @return string
     */
    private function sanitizeHtml($html)
    {
        // Define allowed HTML tags
        $allowedTags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><div><span><blockquote><code><pre><table><thead><tbody><tr><th><td><img>';
        
        // Strip all tags except allowed ones
        $sanitized = strip_tags($html, $allowedTags);
        
        // Clean up any potentially dangerous attributes
        $sanitized = preg_replace('/<a[^>]*href\s*=\s*["\'](?!https?:\/\/|mailto:|tel:|\/|#)[^"\']*["\'][^>]*>/i', '<a>', $sanitized);
        $sanitized = preg_replace('/<img[^>]*src\s*=\s*["\'](?!https?:\/\/|\/)[^"\']*["\'][^>]*>/i', '<img>', $sanitized);
        
        // Remove any script tags or javascript: URLs that might have been missed
        $sanitized = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $sanitized);
        $sanitized = preg_replace('/javascript:/i', '', $sanitized);
        
        return $sanitized;
    }
} 