/**
 * Partners Portal - Modern UI JavaScript
 * 현대적인 Header & Sidebar 관리 시스템
 */

class ModernUnifiedLayout {
    constructor() {
        this.sidebar = document.querySelector('.partners-sidebar');
        this.mobileToggle = document.getElementById('mobileMenuToggle');
        this.overlay = document.getElementById('mobileOverlay');
        this.isMobileMenuOpen = false;
        this.lastScrollTop = 0;
        this.scrollThreshold = 100;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupResponsiveBehavior();
        this.setupScrollEffects();
        this.setupActiveNavigation();
        this.setupEnhancedAnimations();
        this.setupPerformanceOptimizations();
    }
    
    setupEventListeners() {
        // Mobile menu toggle with enhanced UX
        if (this.mobileToggle) {
            this.mobileToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMobileMenu();
            });
        }
        
        // Overlay click to close with smooth animation
        if (this.overlay) {
            this.overlay.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeMobileMenu();
            });
        }
        
        // Window resize handling with debounce
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => this.handleResize(), 250);
        });
        
        // Enhanced keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMobileMenuOpen) {
                this.closeMobileMenu();
            }
            
            // Ctrl/Cmd + K for quick navigation
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.showQuickNavigation();
            }
        });
        
        // Enhanced sidebar interactions
        this.setupSidebarHoverEffects();
        this.setupSmoothScrolling();
    }
    
    setupResponsiveBehavior() {
        // Check initial screen size with enhanced detection
        this.handleResize();
        
        // Add responsive classes with smooth transitions
        if (window.innerWidth <= 768) {
            document.body.classList.add('mobile-view');
            document.body.classList.remove('desktop-view');
        } else {
            document.body.classList.add('desktop-view');
            document.body.classList.remove('mobile-view');
        }
    }
    
    setupScrollEffects() {
        // Enhanced header scroll effects with smooth transitions
        const header = document.querySelector('.partners-header');
        if (header) {
            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const isScrollingDown = scrollTop > this.lastScrollTop;
                const hasScrolledEnough = scrollTop > this.scrollThreshold;
                
                if (isScrollingDown && hasScrolledEnough) {
                    // Scrolling down - hide header
                    header.style.transform = 'translateY(-100%)';
                    header.style.opacity = '0.8';
                } else {
                    // Scrolling up - show header
                    header.style.transform = 'translateY(0)';
                    header.style.opacity = '1';
                }
                
                this.lastScrollTop = scrollTop;
            }, { passive: true });
        }
        
        // Enhanced sidebar scroll effects
        if (this.sidebar) {
            this.sidebar.addEventListener('scroll', (e) => {
                const scrollTop = e.target.scrollTop;
                const sidebarHeader = this.sidebar.querySelector('.sidebar-header');
                
                if (sidebarHeader) {
                    if (scrollTop > 10) {
                        sidebarHeader.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
                        sidebarHeader.style.transform = 'translateY(-2px)';
                    } else {
                        sidebarHeader.style.boxShadow = 'none';
                        sidebarHeader.style.transform = 'translateY(0)';
                    }
                }
            }, { passive: true });
        }
        
        // Parallax effect for main content
        this.setupParallaxEffect();
    }
    
    setupParallaxEffect() {
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                mainContent.style.transform = `translateY(${rate}px)`;
            }, { passive: true });
        }
    }
    
    setupActiveNavigation() {
        // Enhanced active navigation with smooth transitions
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        
        sidebarLinks.forEach((link, index) => {
            const href = link.getAttribute('href');
            
            // Add staggered animation delay
            link.style.animationDelay = `${index * 0.1}s`;
            
            if (href && currentPath.includes(href.replace('.php', ''))) {
                link.classList.add('active');
                this.highlightActiveLink(link);
            } else {
                link.classList.remove('active');
            }
        });
    }
    
    highlightActiveLink(link) {
        // Add pulsing effect to active link
        link.style.animation = 'pulse 2s infinite';
        
        // Remove animation after 3 seconds
        setTimeout(() => {
            link.style.animation = '';
        }, 3000);
    }
    
    setupSidebarHoverEffects() {
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        
        sidebarLinks.forEach(link => {
            link.addEventListener('mouseenter', (e) => {
                const icon = link.querySelector('i');
                if (icon) {
                    icon.style.transform = 'scale(1.2) rotate(8deg)';
                    icon.style.color = 'var(--primary-200)';
                }
                
                // Add ripple effect
                this.createRippleEffect(e, link);
            });
            
            link.addEventListener('mouseleave', (e) => {
                const icon = link.querySelector('i');
                if (icon) {
                    icon.style.transform = 'scale(1) rotate(0deg)';
                    icon.style.color = '';
                }
            });
        });
        
        // Enhanced user-info interactions
        this.setupUserInfoInteractions();
    }
    
    setupUserInfoInteractions() {
        const newUserInfo = document.querySelector('.new-user-info');
        const userTrigger = document.querySelector('.user-trigger');
        
        if (newUserInfo && userTrigger) {
            // Add click handler for all devices
            userTrigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleNewUserDropdown();
                
                // Add haptic feedback for mobile
                if (navigator.vibrate) {
                    navigator.vibrate(30);
                }
                
                // Add visual feedback
                userTrigger.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    userTrigger.style.transform = '';
                }, 150);
            });
            
            // Add keyboard support
            userTrigger.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleNewUserDropdown();
                } else if (e.key === 'Escape') {
                    this.closeNewUserDropdown();
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!newUserInfo.contains(e.target)) {
                    this.closeNewUserDropdown();
                }
            });
            
            // Make user-trigger focusable
            userTrigger.setAttribute('tabindex', '0');
            userTrigger.setAttribute('role', 'button');
            userTrigger.setAttribute('aria-label', 'User menu');
            userTrigger.setAttribute('aria-expanded', 'false');
        }
        
        // Setup Home link interactions
        this.setupHomeLinkInteractions();
    }
    
    setupHomeLinkInteractions() {
        const homeLinks = document.querySelectorAll('a[href*="index.php"]');
        
        homeLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Add haptic feedback for mobile
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
                
                // Add visual feedback
                link.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    link.style.transform = '';
                }, 150);
            });
            
            // Add tooltip for better UX
            link.setAttribute('title', 'Go to Home page');
            link.setAttribute('aria-label', 'Go to Home page');
        });
        
        // Enhanced header home link interactions
        
        // Setup dropdown item interactions
        this.setupDropdownItemInteractions();
    }
    
    setupDropdownItemInteractions() {
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        
        dropdownItems.forEach(item => {
            item.addEventListener('click', (e) => {
                // Add click feedback
                item.style.transform = 'translateX(3px) scale(0.98)';
                setTimeout(() => {
                    item.style.transform = '';
                }, 150);
                
                // Add haptic feedback
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
                
                // Special handling for logout
                if (item.href && item.href.includes('logout.php')) {
                    e.preventDefault();
                    this.handleLogout(item.href);
                }
                
                // Special handling for home - removed notification
                if (item.href && item.href.includes('index.php')) {
                    // No notification needed for current tab navigation
                }
                
                // Close dropdown after a short delay
                setTimeout(() => {
                    this.closeUserDropdown();
                }, 200);
            });
            
            // Add keyboard support
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    item.click();
                }
            });
        });
    }
    
    handleLogout(logoutUrl) {
        // Show confirmation dialog
        if (confirm('Are you sure you want to logout?')) {
            this.showNotification('👋 Logging out...', 'info', 2000);
            
            // Add logout animation
            const userInfo = document.querySelector('.user-info');
            if (userInfo) {
                userInfo.style.animation = 'fadeOut 0.5s ease-out';
            }
            
            // Redirect after delay
            setTimeout(() => {
                window.location.href = logoutUrl;
            }, 1000);
        }
    }
    
    
    toggleNewUserDropdown() {
        const newUserInfo = document.querySelector('.new-user-info');
        const userTrigger = document.querySelector('.user-trigger');
        const dropdown = document.querySelector('.new-user-dropdown');
        
        if (newUserInfo && userTrigger && dropdown) {
            const isExpanded = userTrigger.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                this.closeNewUserDropdown();
            } else {
                this.openNewUserDropdown();
            }
        }
    }
    
    openNewUserDropdown() {
        const newUserInfo = document.querySelector('.new-user-info');
        const userTrigger = document.querySelector('.user-trigger');
        const dropdown = document.querySelector('.new-user-dropdown');
        
        if (newUserInfo && userTrigger && dropdown) {
            // Add active class for styling
            newUserInfo.classList.add('active');
            userTrigger.classList.add('active');
            
            // Show dropdown with animation
            dropdown.style.opacity = '1';
            dropdown.style.visibility = 'visible';
            dropdown.style.transform = 'translateY(0) scale(1)';
            
            // Update ARIA attributes
            userTrigger.setAttribute('aria-expanded', 'true');
            
            // Add entrance animation
            dropdown.style.animation = 'fadeInUp 0.3s ease-out';
            setTimeout(() => {
                dropdown.style.animation = '';
            }, 300);
        }
    }
    
    closeNewUserDropdown() {
        const newUserInfo = document.querySelector('.new-user-info');
        const userTrigger = document.querySelector('.user-trigger');
        const dropdown = document.querySelector('.new-user-dropdown');
        
        if (newUserInfo && userTrigger && dropdown) {
            // Remove active class
            newUserInfo.classList.remove('active');
            userTrigger.classList.remove('active');
            
            // Hide dropdown with animation
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
            dropdown.style.transform = 'translateY(-8px) scale(0.95)';
            
            // Update ARIA attributes
            userTrigger.setAttribute('aria-expanded', 'false');
        }
    }
    
    toggleUserDropdown() {
        const userInfo = document.querySelector('.user-info');
        const dropdown = document.querySelector('.user-dropdown');
        
        if (userInfo && dropdown) {
            const isExpanded = userInfo.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                this.closeUserDropdown();
            } else {
                this.openUserDropdown();
            }
        }
    }
    
    openUserDropdown() {
        const userInfo = document.querySelector('.user-info');
        const dropdown = document.querySelector('.user-dropdown');
        
        if (userInfo && dropdown) {
            // Add active class for styling
            userInfo.classList.add('active');
            
            // Show dropdown with animation
            dropdown.style.opacity = '1';
            dropdown.style.visibility = 'visible';
            dropdown.style.transform = 'translateY(0) scale(1)';
            
            // Update ARIA attributes
            userInfo.setAttribute('aria-expanded', 'true');
            
            // Add entrance animation
            dropdown.style.animation = 'fadeInUp 0.3s ease-out';
            setTimeout(() => {
                dropdown.style.animation = '';
            }, 300);
            
            // Show notification
            this.showNotification('👤 User menu opened', 'info', 1500);
        }
    }
    
    closeUserDropdown() {
        const userInfo = document.querySelector('.user-info');
        const dropdown = document.querySelector('.user-dropdown');
        
        if (userInfo && dropdown) {
            // Remove active class
            userInfo.classList.remove('active');
            
            // Hide dropdown with animation
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
            dropdown.style.transform = 'translateY(-8px) scale(0.95)';
            
            // Update ARIA attributes
            userInfo.setAttribute('aria-expanded', 'false');
        }
    }
    
    createRippleEffect(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        `;
        
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }
    
    setupSmoothScrolling() {
        // Smooth scroll for internal links
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
    }
    
    setupEnhancedAnimations() {
        // Add intersection observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);
        
        // Observe elements for animation
        document.querySelectorAll('.modern-card, .form-group, .alert').forEach(el => {
            observer.observe(el);
        });
    }
    
    setupPerformanceOptimizations() {
        // Throttle scroll events
        let ticking = false;
        
        const updateScroll = () => {
            // Update scroll-based animations
            ticking = false;
        };
        
        const requestTick = () => {
            if (!ticking) {
                requestAnimationFrame(updateScroll);
                ticking = true;
            }
        };
        
        window.addEventListener('scroll', requestTick, { passive: true });
    }
    
    toggleMobileMenu() {
        if (this.isMobileMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }
    
    openMobileMenu() {
        if (!this.sidebar || !this.overlay) return;
        
        // Enhanced mobile menu opening
        this.sidebar.classList.add('mobile-open');
        this.overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
        this.isMobileMenuOpen = true;
        
        // Smooth overlay animation
        requestAnimationFrame(() => {
            this.overlay.style.opacity = '1';
        });
        
        // Smooth sidebar animation
        requestAnimationFrame(() => {
            this.sidebar.style.transform = 'translateX(0)';
        });
        
        // Add haptic feedback for mobile devices
        if ('vibrate' in navigator) {
            navigator.vibrate(50);
        }
        

    }
    
    closeMobileMenu() {
        if (!this.sidebar || !this.overlay) return;
        
        // Enhanced mobile menu closing
        this.sidebar.style.transform = 'translateX(-100%)';
        this.overlay.style.opacity = '0';
        
        setTimeout(() => {
            this.sidebar.classList.remove('mobile-open');
            this.overlay.style.display = 'none';
            document.body.style.overflow = '';
            this.isMobileMenuOpen = false;
        }, 300);
        

    }
    
    handleResize() {
        const width = window.innerWidth;
        
        if (width > 768) {
            // Desktop view
            this.closeMobileMenu();
            document.body.classList.remove('mobile-view');
            document.body.classList.add('desktop-view');
        } else {
            // Mobile view
            document.body.classList.remove('desktop-view');
            document.body.classList.add('mobile-view');
        }
    }
    
    showQuickNavigation() {
        // Quick navigation modal (future enhancement)
        this.showNotification('Quick navigation coming soon!', 'info');
    }
    
    // Enhanced utility methods
    showNotification(message, type = 'info', duration = 4000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Smooth entrance animation
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });
        
        // Auto remove with smooth exit
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, duration);
    }
    
    getNotificationIcon(type) {
        const icons = {
            info: 'info-circle',
            success: 'check-circle',
            warning: 'exclamation-triangle',
            error: 'times-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    updateUserInfo(userData) {
        const userNameElement = document.querySelector('.user-name');
        const userRoleElement = document.querySelector('.user-role');
        
        if (userNameElement && userData.name) {
            userNameElement.textContent = userData.name;
            userNameElement.style.animation = 'pulse 0.6s ease-out';
        }
        
        if (userRoleElement && userData.role) {
            userRoleElement.textContent = userData.role;
            userRoleElement.style.animation = 'pulse 0.6s ease-out';
        }
    }
    

}

// Initialize modern unified layout when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.modernUnifiedLayout = new ModernUnifiedLayout();
});

// Add CSS for ripple effect
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .notification-content i {
        font-size: 1.25rem;
    }
`;
document.head.appendChild(style);

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernUnifiedLayout;
}