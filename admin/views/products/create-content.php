<?php
// Product Create Form
$currentAction = 'products';
$categories = $categories ?? [];

// Helper function to get correct image path
function getImagePath($imagePath) {
    // Debug: Log original image path
    error_log("Original image path: " . $imagePath);
    
    // If it's already a full URL, return as is
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
        error_log("Using full URL: " . $imagePath);
        return $imagePath;
    }
    
    // If it already starts with public/, return as is
    if (strpos($imagePath, 'public/') === 0) {
        error_log("Using existing public path: " . $imagePath);
        return $imagePath;
    }
    
    // Get the correct relative path from admin/views/products/ to admin/public/uploads/products/
    // admin/views/products/create-content.php -> admin/public/uploads/products/
    $relativePath = '../../public/uploads/products/' . $imagePath;
    error_log("Using relative path: " . $relativePath);
    
    // Check if file exists
    $absolutePath = __DIR__ . '/../../public/uploads/products/' . $imagePath;
    error_log("Checking absolute path: " . $absolutePath);
    
    if (file_exists($absolutePath)) {
        error_log("File exists at: " . $absolutePath);
        return $relativePath;
    } else {
        error_log("File does not exist at: " . $absolutePath);
        // Try alternative paths
        $alternativePaths = [
            '../public/uploads/products/' . $imagePath,
            'public/uploads/products/' . $imagePath,
            $imagePath
        ];
        
        foreach ($alternativePaths as $altPath) {
            $altAbsolutePath = __DIR__ . '/../../' . $altPath;
            error_log("Trying alternative path: " . $altAbsolutePath);
            if (file_exists($altAbsolutePath)) {
                error_log("File found at alternative path: " . $altAbsolutePath);
                return $altPath;
            }
        }
        
        error_log("No file found, returning default path: " . $relativePath);
        return $relativePath;
    }
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
                                Add New Product
                            </h5>
                            <p class="text-muted mb-0">Create a new product for your catalog</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <a href="index.php?action=products" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Product Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?action=products&method=store" enctype="multipart/form-data" id="productForm">
                        <?php if (isset($_GET['company_id'])): ?>
                            <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($_GET['company_id']); ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">
                                            Product Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               placeholder="Enter product name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="category" class="form-label">
                                            Category <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="category" 
                                               name="category" 
                                               placeholder="Enter product category"
                                               list="categoryList">
                                        <datalist id="categoryList">
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="short_description" class="form-label">
                                            Short Description <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" 
                                                  id="short_description" 
                                                  name="short_description" 
                                                  rows="3" 
                                                  placeholder="Enter a brief description of the product (max 200 characters)"></textarea>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Brief summary of the product. Maximum 200 characters. This will be used for product previews and summaries.
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="sku" class="form-label">SKU</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="sku" 
                                               name="sku" 
                                               placeholder="Enter SKU (Stock Keeping Unit)">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="price" 
                                                   name="price" 
                                                   step="0.01" 
                                                   min="0" 
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="stock_quantity" 
                                               name="stock_quantity" 
                                               min="0" 
                                               value="0" 
                                               placeholder="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        Description <span class="text-danger">*</span>
                                    </label>
                                    <div class="description-editor-container">
                                        <textarea class="form-control" 
                                                  id="description" 
                                                  name="description" 
                                                  rows="10" 
                                                  placeholder="Enter detailed product description...&#10;&#10;You can use basic HTML tags:&#10;- &lt;strong&gt; for bold text&#10;- &lt;em&gt; for italic text&#10;- &lt;ul&gt; and &lt;li&gt; for lists&#10;- &lt;a href=&quot;url&quot;&gt; for links&#10;- &lt;br&gt; for line breaks"></textarea>
                                        <div class="description-toolbar mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('strong', 'Bold')">
                                                <i class="fas fa-bold"></i> Bold
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('em', 'Italic')">
                                                <i class="fas fa-italic"></i> Italic
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertList()">
                                                <i class="fas fa-list"></i> List
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink()">
                                                <i class="fas fa-link"></i> Link
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLineBreak()">
                                                <i class="fas fa-level-down-alt"></i> Line Break
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Description Guidelines:</strong>
                                        <ul class="mt-1 mb-0">
                                            <li>Provide detailed information about the product features, specifications, and benefits</li>
                                            <li>Use formatting tools above to make your content more readable</li>
                                            <li>Include key selling points and unique features</li>
                                            <li>Minimum 50 characters recommended for better product presentation</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Legacy Image URL field for backward compatibility -->
                                <div class="mb-3">
                                    <label for="image_url" class="form-label">Image URL (Optional)</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="image_url" 
                                           name="image_url" 
                                           placeholder="https://example.com/image.jpg">
                                    <div class="form-text">Enter a valid image URL for the product (alternative to file upload)</div>
                                </div>
                            </div>


                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-3">
                                    <a href="index.php?action=products" class="btn btn-outline-secondary btn-md">
                                        <i class="fas fa-times me-2"></i>
                                        Cancel
                                    </a>
                                    <button type="button" class="btn btn-primary btn-md" id="createProductBtn">
                                        <i class="fas fa-save me-2"></i>
                                        Create Product
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Description editor styles */
.description-editor-container {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
}

.description-editor-container textarea {
    border: none;
    border-radius: 0;
    resize: vertical;
    min-height: 200px;
}

.description-editor-container textarea:focus {
    box-shadow: none;
    border-color: transparent;
}

.description-toolbar {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 8px;
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.description-toolbar .btn {
    font-size: 0.875rem;
    padding: 4px 8px;
}

.description-toolbar .btn:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

/* Form action buttons height consistency */
.d-flex.justify-content-end.gap-3 .btn {
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
    text-decoration: none;
}

.d-flex.justify-content-end.gap-3 .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.d-flex.justify-content-end.gap-3 .btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    
    const nameInput = document.getElementById('name');
    const categoryInput = document.getElementById('category');
    const priceInput = document.getElementById('price');
    const stockInput = document.getElementById('stock_quantity');
    const descriptionInput = document.getElementById('description');
    const imageUrlInput = document.getElementById('image_url');

    // Note: CKEditor removed for stability - using simple textarea with formatting toolbar instead
    

    // Add form submission handler to sync CKEditor content
    const form = document.getElementById('productForm');
    if (form) {
        // Disable the complex form submission handler to avoid conflicts
        // The individual buttons now handle form submission
        
    }

    // Create Product button - simplified version
    const createProductBtn = document.getElementById('createProductBtn');
    if (createProductBtn) {
        createProductBtn.addEventListener('click', function() {
            
            
            // Simple validation
            const nameField = document.querySelector('#name');
            const categoryField = document.querySelector('#category');
            const shortDescField = document.querySelector('#short_description');
            const descField = document.querySelector('#description');
            
            if (!nameField || !nameField.value.trim()) {
                alert('Please enter a product name');
                nameField?.focus();
                return;
            }
            
            if (!categoryField || !categoryField.value.trim()) {
                alert('Please enter a category');
                categoryField?.focus();
                return;
            }
            
            if (!shortDescField || !shortDescField.value.trim()) {
                alert('Please enter a short description');
                shortDescField?.focus();
                return;
            }
            
            if (!descField || !descField.value.trim()) {
                alert('Please enter a description');
                descField?.focus();
                return;
            }
            
            
            
            // Submit the form directly
            form.submit();
        });
    }

    // Description formatting functions
    window.insertTag = function(tag, label) {
        const textarea = document.getElementById('description');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        let replacement = '';
        if (tag === 'strong') {
            replacement = `<strong>${selectedText || 'Bold Text'}</strong>`;
        } else if (tag === 'em') {
            replacement = `<em>${selectedText || 'Italic Text'}</em>`;
        }
        
        textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + replacement.length, start + replacement.length);
    };

    window.insertList = function() {
        const textarea = document.getElementById('description');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        let replacement = '';
        if (selectedText) {
            // Convert selected text to list items
            const lines = selectedText.split('\n').filter(line => line.trim());
            replacement = '<ul>\n' + lines.map(line => `  <li>${line.trim()}</li>`).join('\n') + '\n</ul>';
        } else {
            replacement = '<ul>\n  <li>List item 1</li>\n  <li>List item 2</li>\n  <li>List item 3</li>\n</ul>';
        }
        
        textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + replacement.length, start + replacement.length);
    };

    window.insertLink = function() {
        const url = prompt('Enter URL:', 'https://');
        if (url) {
            const text = prompt('Enter link text:', 'Link Text');
            const textarea = document.getElementById('description');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            const replacement = `<a href="${url}" target="_blank">${selectedText || text}</a>`;
            textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + replacement.length, start + replacement.length);
        }
    };

    window.insertLineBreak = function() {
        const textarea = document.getElementById('description');
        const start = textarea.selectionStart;
        const replacement = '<br>';
        
        textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(start);
        textarea.focus();
        textarea.setSelectionRange(start + replacement.length, start + replacement.length);
    };
});
</script> 