/**
 * Image Gallery Viewer
 * 
 * This component provides a modal gallery view for product images
 * - Full-screen image viewing
 * - Image navigation (previous/next)
 * - Zoom functionality
 * - Keyboard navigation
 * - Touch gestures for mobile
 */

class ImageGallery {
    constructor(options = {}) {
        this.options = {
            container: 'body',
            modalId: 'imageGalleryModal',
            ...options
        };
        
        this.currentIndex = 0;
        this.images = [];
        this.isOpen = false;
        this.zoomLevel = 1;
        this.isDragging = false;
        this.dragStart = { x: 0, y: 0 };
        this.dragOffset = { x: 0, y: 0 };
        
        this.init();
    }

    init() {
        this.createModal();
        this.bindEvents();
    }

    createModal() {
        const modalHTML = `
            <div id="${this.options.modalId}" class="image-gallery-modal" style="display: none;">
                <div class="image-gallery-overlay"></div>
                <div class="image-gallery-container">
                    <div class="image-gallery-header">
                        <div class="image-gallery-info">
                            <span class="image-counter">1 / 1</span>
                            <span class="image-name"></span>
                        </div>
                        <div class="image-gallery-controls">
                            <button class="btn btn-sm btn-outline-light zoom-in" title="Zoom In">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-light zoom-out" title="Zoom Out">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-light reset-zoom" title="Reset Zoom">
                                <i class="fas fa-expand"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-light close-gallery" title="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="image-gallery-content">
                        <button class="nav-btn prev-btn" title="Previous Image">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="image-viewer">
                            <img class="gallery-image" src="" alt="Gallery Image">
                        </div>
                        <button class="nav-btn next-btn" title="Next Image">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="image-gallery-footer">
                        <div class="image-thumbnails"></div>
                    </div>
                </div>
            </div>
        `;

        const container = document.querySelector(this.options.container);
        container.insertAdjacentHTML('beforeend', modalHTML);
        
        this.modal = document.getElementById(this.options.modalId);
        this.imageElement = this.modal.querySelector('.gallery-image');
        this.counterElement = this.modal.querySelector('.image-counter');
        this.nameElement = this.modal.querySelector('.image-name');
        this.thumbnailsContainer = this.modal.querySelector('.image-thumbnails');
        this.imageViewer = this.modal.querySelector('.image-viewer');
    }

    bindEvents() {
        // Navigation buttons
        this.modal.querySelector('.prev-btn').addEventListener('click', () => this.previous());
        this.modal.querySelector('.next-btn').addEventListener('click', () => this.next());
        
        // Control buttons
        this.modal.querySelector('.zoom-in').addEventListener('click', () => this.zoomIn());
        this.modal.querySelector('.zoom-out').addEventListener('click', () => this.zoomOut());
        this.modal.querySelector('.reset-zoom').addEventListener('click', () => this.resetZoom());
        this.modal.querySelector('.close-gallery').addEventListener('click', () => this.close());
        
        // Overlay click to close
        this.modal.querySelector('.image-gallery-overlay').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                this.close();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!this.isOpen) return;
            
            switch (e.key) {
                case 'Escape':
                    this.close();
                    break;
                case 'ArrowLeft':
                    this.previous();
                    break;
                case 'ArrowRight':
                    this.next();
                    break;
                case '+':
                case '=':
                    e.preventDefault();
                    this.zoomIn();
                    break;
                case '-':
                    e.preventDefault();
                    this.zoomOut();
                    break;
                case '0':
                    this.resetZoom();
                    break;
            }
        });

        // Mouse wheel for zoom
        this.imageViewer.addEventListener('wheel', (e) => {
            e.preventDefault();
            if (e.deltaY < 0) {
                this.zoomIn();
            } else {
                this.zoomOut();
            }
        });

        // Touch events for mobile
        let touchStartX = 0;
        let touchStartY = 0;
        let touchStartTime = 0;

        this.imageViewer.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            touchStartTime = Date.now();
        });

        this.imageViewer.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const touchEndTime = Date.now();
            const touchDuration = touchEndTime - touchStartTime;
            const touchDistance = Math.sqrt(
                Math.pow(touchEndX - touchStartX, 2) + 
                Math.pow(touchEndY - touchStartY, 2)
            );

            // Swipe detection
            if (touchDuration < 300 && touchDistance > 50) {
                const deltaX = touchEndX - touchStartX;
                const deltaY = touchEndY - touchStartY;

                if (Math.abs(deltaX) > Math.abs(deltaY)) {
                    if (deltaX > 0) {
                        this.previous();
                    } else {
                        this.next();
                    }
                }
            }
        });

        // Mouse drag for panning
        this.imageViewer.addEventListener('mousedown', (e) => {
            if (this.zoomLevel > 1) {
                this.isDragging = true;
                this.dragStart = { x: e.clientX, y: e.clientY };
                this.imageViewer.style.cursor = 'grabbing';
            }
        });

        document.addEventListener('mousemove', (e) => {
            if (this.isDragging) {
                const deltaX = e.clientX - this.dragStart.x;
                const deltaY = e.clientY - this.dragStart.y;
                
                this.dragOffset.x += deltaX;
                this.dragOffset.y += deltaY;
                
                this.updateImageTransform();
                this.dragStart = { x: e.clientX, y: e.clientY };
            }
        });

        document.addEventListener('mouseup', () => {
            this.isDragging = false;
            this.imageViewer.style.cursor = 'grab';
        });
    }

    open(images, startIndex = 0) {
        this.images = images;
        this.currentIndex = startIndex;
        this.isOpen = true;
        this.zoomLevel = 1;
        this.dragOffset = { x: 0, y: 0 };

        this.modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        this.loadImage();
        this.updateThumbnails();
        this.updateCounter();
        
        // Focus for keyboard events
        this.modal.focus();
    }

    close() {
        this.isOpen = false;
        this.modal.style.display = 'none';
        document.body.style.overflow = '';
        this.images = [];
    }

    loadImage() {
        if (this.images.length === 0) return;

        const image = this.images[this.currentIndex];
        this.imageElement.src = image.src || image.url;
        this.imageElement.alt = image.alt || `Image ${this.currentIndex + 1}`;
        this.nameElement.textContent = image.name || `Image ${this.currentIndex + 1}`;
        
        this.resetZoom();
        this.updateCounter();
    }

    previous() {
        if (this.images.length <= 1) return;
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.loadImage();
        this.updateThumbnailSelection();
    }

    next() {
        if (this.images.length <= 1) return;
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this.loadImage();
        this.updateThumbnailSelection();
    }

    zoomIn() {
        this.zoomLevel = Math.min(this.zoomLevel * 1.2, 5);
        this.updateImageTransform();
    }

    zoomOut() {
        this.zoomLevel = Math.max(this.zoomLevel / 1.2, 0.1);
        this.updateImageTransform();
    }

    resetZoom() {
        this.zoomLevel = 1;
        this.dragOffset = { x: 0, y: 0 };
        this.updateImageTransform();
    }

    updateImageTransform() {
        this.imageElement.style.transform = `translate(${this.dragOffset.x}px, ${this.dragOffset.y}px) scale(${this.zoomLevel})`;
    }

    updateCounter() {
        this.counterElement.textContent = `${this.currentIndex + 1} / ${this.images.length}`;
    }

    updateThumbnails() {
        this.thumbnailsContainer.innerHTML = '';
        
        this.images.forEach((image, index) => {
            const thumbnail = document.createElement('div');
            thumbnail.className = 'image-thumbnail';
            thumbnail.innerHTML = `
                <img src="${image.src || image.url}" 
                     alt="${image.alt || `Thumbnail ${index + 1}`}"
                     class="${index === this.currentIndex ? 'active' : ''}">
            `;
            
            thumbnail.addEventListener('click', () => {
                this.currentIndex = index;
                this.loadImage();
                this.updateThumbnailSelection();
            });
            
            this.thumbnailsContainer.appendChild(thumbnail);
        });
    }

    updateThumbnailSelection() {
        const thumbnails = this.thumbnailsContainer.querySelectorAll('.image-thumbnail img');
        thumbnails.forEach((thumb, index) => {
            thumb.classList.toggle('active', index === this.currentIndex);
        });
    }

    // Static method to open gallery from image click
    static openFromImage(imageElement, images, startIndex = 0) {
        if (!window.imageGallery) {
            window.imageGallery = new ImageGallery();
        }
        
        // If images not provided, try to get from parent container
        if (!images) {
            const container = imageElement.closest('.existing-images-container, .product-images');
            if (container) {
                const imageElements = container.querySelectorAll('img');
                images = Array.from(imageElements).map((img, index) => ({
                    src: img.src,
                    alt: img.alt,
                    name: img.alt || `Image ${index + 1}`
                }));
                startIndex = Array.from(imageElements).indexOf(imageElement);
            }
        }
        
        if (images && images.length > 0) {
            window.imageGallery.open(images, startIndex);
        }
    }
}

// Auto-initialize gallery when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Add click handlers to existing images
    document.querySelectorAll('.existing-image-preview').forEach(img => {
        img.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const container = img.closest('.existing-images-container');
            if (container) {
                const images = Array.from(container.querySelectorAll('.existing-image-preview')).map((image, index) => ({
                    src: image.src,
                    alt: image.alt,
                    name: image.alt || `Product Image ${index + 1}`
                }));
                
                const startIndex = Array.from(container.querySelectorAll('.existing-image-preview')).indexOf(img);
                ImageGallery.openFromImage(img, images, startIndex);
            }
        });
    });
});