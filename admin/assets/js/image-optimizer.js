/**
 * Image Optimization Utility
 * 
 * This utility provides image optimization features for the Main Image Selection feature
 * - Image compression and resizing
 * - Lazy loading
 * - Progressive loading
 * - Error handling and fallbacks
 */

class ImageOptimizer {
    constructor() {
        this.supportedFormats = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        this.maxFileSize = 5 * 1024 * 1024; // 5MB
        this.thumbnailSize = 200; // 200px for thumbnails
        this.previewSize = 400; // 400px for preview
    }

    // Compress image before upload
    async compressImage(file, maxWidth = 1920, quality = 0.8) {
        return new Promise((resolve, reject) => {
            if (!this.supportedFormats.includes(file.type)) {
                reject(new Error('Unsupported file format'));
                return;
            }

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = () => {
                // Calculate new dimensions
                let { width, height } = img;
                
                if (width > maxWidth) {
                    height = (height * maxWidth) / width;
                    width = maxWidth;
                }

                // Set canvas dimensions
                canvas.width = width;
                canvas.height = height;

                // Draw and compress
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob(
                    (blob) => {
                        if (blob) {
                            // Create new file with compressed data
                            const compressedFile = new File([blob], file.name, {
                                type: file.type,
                                lastModified: Date.now()
                            });
                            resolve(compressedFile);
                        } else {
                            reject(new Error('Failed to compress image'));
                        }
                    },
                    file.type,
                    quality
                );
            };

            img.onerror = () => reject(new Error('Failed to load image'));
            img.src = URL.createObjectURL(file);
        });
    }

    // Create thumbnail from image
    async createThumbnail(file, size = this.thumbnailSize) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = () => {
                // Calculate thumbnail dimensions
                let { width, height } = img;
                const aspectRatio = width / height;

                if (width > height) {
                    width = size;
                    height = size / aspectRatio;
                } else {
                    height = size;
                    width = size * aspectRatio;
                }

                // Set canvas dimensions
                canvas.width = width;
                canvas.height = height;

                // Draw thumbnail
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob(
                    (blob) => {
                        if (blob) {
                            const thumbnailFile = new File([blob], `thumb_${file.name}`, {
                                type: file.type,
                                lastModified: Date.now()
                            });
                            resolve(thumbnailFile);
                        } else {
                            reject(new Error('Failed to create thumbnail'));
                        }
                    },
                    file.type,
                    0.7
                );
            };

            img.onerror = () => reject(new Error('Failed to load image for thumbnail'));
            img.src = URL.createObjectURL(file);
        });
    }

    // Lazy load images
    setupLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Progressive image loading
    setupProgressiveLoading() {
        const images = document.querySelectorAll('img[data-progressive]');
        
        images.forEach(img => {
            const lowResSrc = img.dataset.progressive;
            const highResSrc = img.src;
            
            // Load low resolution first
            img.src = lowResSrc;
            
            // Then load high resolution
            const highResImg = new Image();
            highResImg.onload = () => {
                img.src = highResSrc;
                img.classList.add('loaded');
            };
            highResImg.src = highResSrc;
        });
    }

    // Validate image file
    validateImage(file) {
        const errors = [];

        // Check file type
        if (!this.supportedFormats.includes(file.type)) {
            errors.push(`Unsupported file type: ${file.type}. Supported: ${this.supportedFormats.join(', ')}`);
        }

        // Check file size
        if (file.size > this.maxFileSize) {
            errors.push(`File too large: ${(file.size / 1024 / 1024).toFixed(2)}MB. Maximum: ${(this.maxFileSize / 1024 / 1024).toFixed(2)}MB`);
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    // Get image dimensions
    getImageDimensions(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                resolve({
                    width: img.naturalWidth,
                    height: img.naturalHeight,
                    aspectRatio: img.naturalWidth / img.naturalHeight
                });
            };
            img.onerror = () => reject(new Error('Failed to get image dimensions'));
            img.src = URL.createObjectURL(file);
        });
    }

    // Create image preview with loading states
    createImagePreview(file, container, options = {}) {
        const {
            showLoading = true,
            showError = true,
            thumbnail = false,
            size = thumbnail ? this.thumbnailSize : this.previewSize
        } = options;

        return new Promise((resolve, reject) => {
            // Validation
            const validation = this.validateImage(file);
            if (!validation.isValid) {
                if (showError) {
                    container.innerHTML = `
                        <div class="image-error">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <p class="text-warning">${validation.errors.join(', ')}</p>
                        </div>
                    `;
                }
                reject(new Error(validation.errors.join(', ')));
                return;
            }

            // Show loading state
            if (showLoading) {
                container.innerHTML = `
                    <div class="image-loading">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Processing image...</p>
                    </div>
                `;
            }

            // Create preview
            this.createThumbnail(file, size)
                .then(thumbnailFile => {
                    const img = new Image();
                    
                    img.onload = () => {
                        container.innerHTML = `
                            <img src="${img.src}" 
                                 alt="Image Preview" 
                                 class="img-fluid ${thumbnail ? 'thumbnail' : 'preview'}"
                                 style="max-width: 100%; height: auto;">
                        `;
                        resolve(img.src);
                    };
                    
                    img.onerror = () => {
                        if (showError) {
                            container.innerHTML = `
                                <div class="image-error">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    <p class="text-warning">Failed to load image</p>
                                </div>
                            `;
                        }
                        reject(new Error('Failed to load image preview'));
                    };
                    
                    img.src = URL.createObjectURL(thumbnailFile);
                })
                .catch(error => {
                    if (showError) {
                        container.innerHTML = `
                            <div class="image-error">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <p class="text-warning">${error.message}</p>
                            </div>
                        `;
                    }
                    reject(error);
                });
        });
    }

    // Batch process multiple images
    async processImageBatch(files, options = {}) {
        const {
            compress = true,
            createThumbnails = true,
            maxConcurrent = 3
        } = options;

        const results = [];
        const errors = [];

        // Process files in batches
        for (let i = 0; i < files.length; i += maxConcurrent) {
            const batch = files.slice(i, i + maxConcurrent);
            const batchPromises = batch.map(async (file, index) => {
                try {
                    let processedFile = file;

                    // Compress if requested
                    if (compress) {
                        processedFile = await this.compressImage(processedFile);
                    }

                    // Create thumbnail if requested
                    let thumbnail = null;
                    if (createThumbnails) {
                        thumbnail = await this.createThumbnail(processedFile);
                    }

                    return {
                        original: file,
                        processed: processedFile,
                        thumbnail,
                        index: i + index
                    };
                } catch (error) {
                    errors.push({
                        file: file.name,
                        error: error.message,
                        index: i + index
                    });
                    return null;
                }
            });

            const batchResults = await Promise.all(batchPromises);
            results.push(...batchResults.filter(result => result !== null));
        }

        return {
            processed: results,
            errors,
            total: files.length,
            successful: results.length
        };
    }

    // Generate unique filename
    generateFilename(originalName, prefix = '') {
        const timestamp = Date.now();
        const random = Math.random().toString(36).substring(2, 15);
        const extension = originalName.split('.').pop();
        return `${prefix}${timestamp}_${random}.${extension}`;
    }

    // Clean up object URLs to prevent memory leaks
    cleanupObjectURLs() {
        // This should be called when images are no longer needed
        // Implementation depends on how you're tracking object URLs
        // Intentionally left blank: add URL.revokeObjectURL housekeeping when tracking object URLs
    }
}

// Global instance
window.ImageOptimizer = new ImageOptimizer();

// Auto-setup lazy loading when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.ImageOptimizer) {
        window.ImageOptimizer.setupLazyLoading();
        window.ImageOptimizer.setupProgressiveLoading();
    }
});