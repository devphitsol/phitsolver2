<?php
// Product Edit Form
$currentAction = 'products';

// Ensure all variables are defined with defaults
$product = $product ?? [];
$categories = $categories ?? [];
$existingImages = $product['images'] ?? [];

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
    // admin/views/products/edit-content.php -> admin/public/uploads/products/
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
                                <i class="fas fa-edit me-2"></i>
                                Edit Product
                            </h5>
                            <p class="text-muted mb-0">Update product information for <?php echo htmlspecialchars($product['name'] ?? 'Product'); ?></p>
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
                    <form method="POST" action="index.php?action=products&method=update&id=<?php echo $product['_id'] ?? ''; ?>" enctype="multipart/form-data" id="productForm">
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
                                               required 
                                               value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
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
                                               required 
                                               value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>"
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
                                                  required
                                                  placeholder="Enter a brief description of the product (max 200 characters)"><?php echo htmlspecialchars($product['short_description'] ?? ''); ?></textarea>
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
                                               value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>"
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
                                                   value="<?php echo $product['price'] ?? 0; ?>"
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
                                               value="<?php echo $product['stock_quantity'] ?? 0; ?>" 
                                               placeholder="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($product['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($product['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control rich-text-editor" 
                                              id="description" 
                                              name="description" 
                                              rows="8" 
                                              required
                                              placeholder="Enter product description... (Supports HTML formatting, links, and rich text)"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        You can use HTML formatting, add links, create lists, and include images. The content will be safely sanitized.
                                    </div>
                                </div>

                                <!-- Existing Product Images Section -->
                                <?php if (!empty($existingImages)): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        Current Product Images 
                                        <span class="image-counter" id="existingImageCounter"><?php echo count($existingImages); ?>/5</span>
                                    </label>
                                    <div class="existing-images-container">
                                        <div class="row">
                                            <?php foreach ($existingImages as $index => $imagePath): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="existing-image-box" data-image-index="<?php echo $index; ?>">
                                                    <?php 
                                                    // Use helper function to get correct image path
                                                    $imageSrc = getImagePath($imagePath);
                                                    error_log("Existing image {$index}: {$imagePath} -> {$imageSrc}");
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($imageSrc); ?>" 
                                                         alt="Product Image <?php echo $index + 1; ?>" 
                                                         class="existing-image-preview"
                                                         onclick="selectMainImage(this, '<?php echo htmlspecialchars($imageSrc); ?>', '<?php echo htmlspecialchars($imagePath); ?>')"
                                                         style="cursor: pointer;"
                                                         onerror="console.log('Existing image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                         onload="console.log('Existing image loaded successfully:', this.src);">
                                                    <div class="image-error-placeholder" style="display: none; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #f8f9fa;">
                                                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                                        <p class="text-warning mt-1 small mb-0">Image not found</p>
                                                        <small class="text-muted">File may be missing</small>
                                                    </div>
                                                    <button type="button" 
                                                            class="remove-existing-image-btn" 
                                                            onclick="removeExistingImage(this, '<?php echo htmlspecialchars($imagePath); ?>')"
                                                            title="Remove this image">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($imagePath); ?>">
                                                    <div class="image-overlay">
                                                        <span class="image-number"><?php echo $index + 1; ?></span>
                                                        <button type="button" 
                                                                class="select-main-image-btn" 
                                                                onclick="selectMainImage(this, '<?php echo htmlspecialchars($imageSrc); ?>', '<?php echo htmlspecialchars($imagePath); ?>')"
                                                                title="Set as main image">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    </div>
                                                    <?php if ($product['main_image'] === $imagePath): ?>
                                                    <div class="main-image-indicator">
                                                        <i class="fas fa-star"></i>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Hover over images to see remove button. Click to remove images from the product.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- New Product Images Section -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        Add New Images 
                                        <span class="text-muted">(Max 5 total images)</span>
                                        <span class="image-counter" id="newImageCounter">0/5</span>
                                    </label>
                                    <div class="image-upload-container">
                                        <div class="row" id="imageUploadRow">
                                            <div class="col-md-6 mb-2">
                                                <div class="image-upload-box">
                                                    <input type="file" 
                                                           class="form-control image-input" 
                                                           name="product_images[]" 
                                                           accept="image/*" 
                                                           data-index="0">
                                                    <div class="upload-placeholder">
                                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                                        <p class="text-muted mt-2 mb-0">Click to upload image</p>
                                                        <small class="text-muted">JPG, PNG, GIF, WebP (Max 5MB)</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="addImageBtn" style="display: none;">
                                                <i class="fas fa-plus me-1"></i>Add Another Image
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Supported formats: JPG, PNG, GIF, WebP. Max file size: 5MB per image. Images will be automatically optimized for web display.
                                    </div>
                                </div>

                                <!-- Main Image Selection -->
                                <div class="mb-3">
                                    <label for="main_image" class="form-label">Main Image (Selected from existing images)</label>
                                    <input type="hidden" 
                                           id="main_image" 
                                           name="main_image" 
                                           value="<?php echo htmlspecialchars($product['main_image'] ?? ''); ?>">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Click on any existing image or use the star button to set it as the main image for the product preview.
                                    </div>
                                </div>

                                <!-- Legacy Image URL field for backward compatibility -->
                                <div class="mb-3">
                                    <label for="image_url" class="form-label">Image URL (Optional)</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="image_url" 
                                           name="image_url" 
                                           value="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>"
                                           placeholder="https://example.com/image.jpg">
                                    <div class="form-text">Enter a valid image URL for the product (alternative to file upload)</div>
                                </div>
                            </div>


                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-3">
                                    <a href="index.php?action=products" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Update Product
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

/* Main image selection styles */
.existing-image-box {
    position: relative;
    transition: all 0.3s ease;
}

.existing-image-box.main-image-selected {
    border: 3px solid #28a745;
    box-shadow: 0 0 15px rgba(40, 167, 69, 0.3);
}

.existing-image-preview {
    transition: all 0.3s ease;
}

.existing-image-box:hover .existing-image-preview {
    transform: scale(1.05);
}

.select-main-image-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid #007bff;
    color: #007bff;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
    opacity: 0;
}

.existing-image-box:hover .select-main-image-btn {
    opacity: 1;
}

.select-main-image-btn:hover {
    background: #007bff;
    color: white;
    transform: scale(1.1);
}

.select-main-image-btn.selected {
    background: #28a745;
    border-color: #28a745;
    color: white;
    opacity: 1;
}

.select-main-image-btn.selected:hover {
    background: #218838;
    border-color: #218838;
}

/* Main image indicator */
.main-image-indicator {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    z-index: 10;
}

.existing-image-box.main-image-selected .main-image-indicator {
    display: flex;
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
    


    const addImageBtn = document.getElementById('addImageBtn');
    const imageUploadRow = document.getElementById('imageUploadRow');
    const newImageCounter = document.getElementById('newImageCounter');
    const existingImageCounter = document.getElementById('existingImageCounter');
    const mainImageInput = document.getElementById('main_image');
    let imageCount = 1;
    
    // Count existing images
    const existingImageCount = <?php echo count($existingImages); ?>;
    const maxImages = 5;
    




    // Select main image from existing images
    window.selectMainImage = function(element, imageSrc, imagePath) {
        
        // Update the hidden input field
        mainImageInput.value = imagePath;
        
        // Update the main image selection
        
        // Update visual feedback on existing images
        updateMainImageSelection(imagePath);
        
        
    };
    

    
    // Update visual feedback for main image selection
    function updateMainImageSelection(selectedImagePath) {
        // Remove previous selection indicators
        document.querySelectorAll('.existing-image-box').forEach(box => {
            box.classList.remove('main-image-selected');
            const starBtn = box.querySelector('.select-main-image-btn');
            if (starBtn) {
                starBtn.classList.remove('selected');
            }
        });
        
        // Add selection indicator to the selected image
        const selectedBox = document.querySelector(`.existing-image-box input[value="${selectedImagePath}"]`).closest('.existing-image-box');
        if (selectedBox) {
            selectedBox.classList.add('main-image-selected');
            const starBtn = selectedBox.querySelector('.select-main-image-btn');
            if (starBtn) {
                starBtn.classList.add('selected');
            }
        }
    }
    


    // Handle image upload preview
    function handleImageUpload(input) {
        const file = input.files[0];
        const uploadBox = input.closest('.image-upload-box');
        const placeholder = uploadBox.querySelector('.upload-placeholder');
        
        if (file) {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, GIF, WebP)');
                input.value = '';
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                uploadBox.classList.add('has-image');
                placeholder.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="image-preview">
                    <button type="button" class="remove-image-btn" onclick="removeImage(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                updateImageCounters();
            };
            reader.readAsDataURL(file);
        }
    }

    // Remove image
    window.removeImage = function(btn) {
        const uploadBox = btn.closest('.image-upload-box');
        const input = uploadBox.querySelector('input[type="file"]');
        const placeholder = uploadBox.querySelector('.upload-placeholder');
        
        input.value = '';
        uploadBox.classList.remove('has-image');
        placeholder.innerHTML = `
            <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
            <p class="text-muted mt-2 mb-0">Click to upload image</p>
            <small class="text-muted">JPG, PNG, GIF, WebP (Max 5MB)</small>
        `;
        updateImageCounters();
    };

    // Remove existing image
    window.removeExistingImage = function(btn, imagePath) {
        if (confirm('Are you sure you want to remove this image?')) {
            const imageBox = btn.closest('.existing-image-box');
            const hiddenInput = imageBox.querySelector('input[name="existing_images[]"]');
            
            // Mark for removal by setting value to empty
            hiddenInput.value = '';
            imageBox.style.display = 'none';
            
            // Update the add image button visibility and counters
            updateAddImageButtonVisibility();
            updateImageCounters();
        }
    };

    // Update add image button visibility
    function updateAddImageButtonVisibility() {
        const visibleExistingImages = document.querySelectorAll('.existing-image-box:not([style*="display: none"])').length;
        const newImageInputs = document.querySelectorAll('.image-input');
        const totalImages = visibleExistingImages + newImageInputs.length;
        
        if (totalImages < maxImages) {
            addImageBtn.style.display = 'inline-block';
        } else {
            addImageBtn.style.display = 'none';
        }
    }

    // Update image counters
    function updateImageCounters() {
        const visibleExistingImages = document.querySelectorAll('.existing-image-box:not([style*="display: none"])').length;
        const uploadedNewImages = document.querySelectorAll('.image-upload-box.has-image').length;
        
        if (existingImageCounter) {
            existingImageCounter.textContent = `${visibleExistingImages}/5`;
            if (visibleExistingImages >= 5) {
                existingImageCounter.className = 'image-counter danger';
            } else if (visibleExistingImages >= 3) {
                existingImageCounter.className = 'image-counter warning';
            } else {
                existingImageCounter.className = 'image-counter';
            }
        }
        
        if (newImageCounter) {
            newImageCounter.textContent = `${uploadedNewImages}/5`;
            if (uploadedNewImages >= 5) {
                newImageCounter.className = 'image-counter danger';
            } else if (uploadedNewImages >= 3) {
                newImageCounter.className = 'image-counter warning';
            } else {
                newImageCounter.className = 'image-counter';
            }
        }
    }

    // Add new image upload field
    addImageBtn.addEventListener('click', function() {
        const visibleExistingImages = document.querySelectorAll('.existing-image-box:not([style*="display: none"])').length;
        const currentNewImages = document.querySelectorAll('.image-input').length;
        
        if (visibleExistingImages + currentNewImages >= maxImages) return;
        
        const newCol = document.createElement('div');
        newCol.className = 'col-md-6 mb-2';
        newCol.innerHTML = `
            <div class="image-upload-box">
                <input type="file" 
                       class="form-control image-input" 
                       name="product_images[]" 
                       accept="image/*" 
                       data-index="${imageCount}">
                <div class="upload-placeholder">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                    <p class="text-muted mt-2 mb-0">Click to upload image</p>
                    <small class="text-muted">JPG, PNG, GIF, WebP (Max 5MB)</small>
                </div>
            </div>
        `;
        
        imageUploadRow.appendChild(newCol);
        imageCount++;
        
        // Add event listener to new input
        const newInput = newCol.querySelector('input[type="file"]');
        newInput.addEventListener('change', function() {
            handleImageUpload(this);
            updateAddImageButtonVisibility();
        });
        
        updateAddImageButtonVisibility();
        updateImageCounters();
    });

    // Add event listeners for image uploads
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('image-input')) {
            handleImageUpload(e.target);
            updateAddImageButtonVisibility();
        }
    });


    updateAddImageButtonVisibility();
    updateImageCounters();
    
    // Initialize main image selection
    const currentMainImage = mainImageInput.value;
    if (currentMainImage) {
        updateMainImageSelection(currentMainImage);
    }

    // Initialize CKEditor Rich Text Editor
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#description'), {
                toolbar: {
                    items: [
                        'undo', 'redo',
                        '|', 'heading',
                        '|', 'bold', 'italic', 'underline',
                        '|', 'link', 'bulletedList', 'numberedList',
                        '|', 'alignment',
                        '|', 'removeFormat'
                    ]
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                    ]
                },
                link: {
                    addTargetToExternalLinks: true,
                    defaultProtocol: 'https://'
                },
                removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload'],
                height: '300px'
            })
            .then(editor => {
                // Store editor instance globally for form submission
                window.descriptionEditor = editor;
            })
            .catch(() => {});
    } else {
        // Load CKEditor from CDN if not already loaded
        const script = document.createElement('script');
        script.src = 'https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js';
        script.onload = function() {
            ClassicEditor
                .create(document.querySelector('#description'), {
                    toolbar: {
                        items: [
                            'undo', 'redo',
                            '|', 'heading',
                            '|', 'bold', 'italic', 'underline',
                            '|', 'link', 'bulletedList', 'numberedList',
                            '|', 'alignment',
                            '|', 'removeFormat'
                        ]
                    },
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                        ]
                    },
                    link: {
                        addTargetToExternalLinks: true,
                        defaultProtocol: 'https://'
                    },
                    removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload'],
                    height: '300px'
                })
                .then(editor => {
                    // Store editor instance globally for form submission
                    window.descriptionEditor = editor;
                })
                .catch(() => {});
        };
        document.head.appendChild(script);
    }

    // Add form submission handler to sync CKEditor content
    const form = document.getElementById('productForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            
            // Sync CKEditor content to textarea before form submission
            if (window.descriptionEditor) {
                const editorData = window.descriptionEditor.getData();
                const textarea = document.querySelector('#description');
                if (textarea) {
                    textarea.value = editorData;
                    console.log('CKEditor content synced to textarea');
                }
            }
            
            // Validate required fields
            const requiredFields = ['name', 'short_description', 'description', 'category'];
            let isValid = true;
            
            requiredFields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field && !field.value.trim()) {
                    
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            
        });
    }
});

// Main Image Selection test script loading removed in production

// Load image optimization utility
const optimizerScript = document.createElement('script');
optimizerScript.src = 'assets/js/image-optimizer.js?v=' + Date.now();
document.head.appendChild(optimizerScript);

// Load image gallery component
const galleryScript = document.createElement('script');
galleryScript.src = 'assets/js/image-gallery.js?v=' + Date.now();
document.head.appendChild(galleryScript);
</script> 