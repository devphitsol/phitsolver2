<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - PHITSOL</title>
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Unified Admin Design System -->
    <link href="assets/css/unified-admin-design.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/theme-system.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../public/assets/css/responsive-enhancements.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body class="theme-aware">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-brand">
                <img 
                    src="assets/img/logo_white.png?v=<?php echo uniqid(); ?>"
                    alt="PHITSOL Logo"
                    class="phitsol-logo"
                    id="phitsol-logo"
                >
            </a>
        </div>
        
        <div class="sidebar-nav">
            <a href="index.php" class="nav-link <?php echo $currentAction === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="index.php?action=blog" class="nav-link <?php echo $currentAction === 'blog' ? 'active' : ''; ?>">
                <i class="fas fa-blog"></i>
                Blog Management
            </a>
            <a href="index.php?action=users" class="nav-link <?php echo $currentAction === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                User Management
            </a>
            
            <a href="index.php?action=company" class="nav-link <?php echo $currentAction === 'company' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                Company Management
            </a>
            
            <a href="index.php?action=products" class="nav-link <?php echo $currentAction === 'products' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                Product Management
            </a>
            
            <a href="index.php?action=support-messages" class="nav-link <?php echo $currentAction === 'support-messages' ? 'active' : ''; ?>">
                <i class="fas fa-headset"></i>
                Support Messages
            </a>
        </div>
    </nav>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Admin Header -->
        <div class="admin-header">
            <div class="header-left">
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle mobile menu">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="header-content">
                    <h1 class="admin-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                    <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'Admin'); ?></p>
                </div>
            </div>
            
            <div class="header-right">
                <!-- Theme Toggle -->
                <div class="user-avatar" id="userAvatarToggle">
                    <?php echo strtoupper(substr($_SESSION['admin_email'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <div class="dropdown-user-info">
                            <div class="dropdown-user-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
                            <div class="dropdown-user-email"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'admin@phitsol.com'); ?></div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" id="profileLink">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="#" class="dropdown-item" id="settingsLink">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="index.php?logout=1" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php 
            // Debug: Log the success message display
            error_log('Success message displayed: ' . $_SESSION['success']);
            unset($_SESSION['success']); 
            ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Page Content -->
        <?php echo $pageContent ?? ''; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Enhanced Layout JavaScript -->
    <script>
        // Theme System
        class ThemeManager {
            constructor() {
                this.themeToggle = document.getElementById('themeToggle');
                this.html = document.documentElement;
                this.currentTheme = localStorage.getItem('theme') || 'light';
                
                this.init();
            }
            
            init() {
                this.setTheme(this.currentTheme);
                this.themeToggle.addEventListener('click', () => this.toggleTheme());
                
                // Listen for system theme changes
                if (window.matchMedia) {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                        if (!localStorage.getItem('theme')) {
                            this.setTheme(e.matches ? 'dark' : 'light');
                        }
                    });
                }
            }
            
            setTheme(theme) {
                this.html.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
                this.currentTheme = theme;
            }
            
            toggleTheme() {
                const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
                this.setTheme(newTheme);
            }
        }
        
        // Mobile Navigation
        class MobileNavigation {
            constructor() {
                this.mobileMenuToggle = document.getElementById('mobileMenuToggle');
                this.sidebar = document.querySelector('.sidebar');
                this.sidebarOverlay = document.getElementById('sidebarOverlay');
                
                this.init();
            }
            
            init() {
                this.mobileMenuToggle.addEventListener('click', () => this.toggleSidebar());
                this.sidebarOverlay.addEventListener('click', () => this.closeSidebar());
                
                // Close sidebar on window resize
                window.addEventListener('resize', () => {
                    if (window.innerWidth > 1024) {
                        this.closeSidebar();
                        // Ensure sidebar is always visible on desktop
                        this.sidebar.style.transform = 'translateX(0)';
                        this.sidebar.style.visibility = 'visible';
                        this.sidebar.style.opacity = '1';
                        this.sidebar.style.position = 'fixed';
                        this.sidebar.style.left = '0';
                        this.sidebar.style.top = '0';
                        this.sidebar.style.height = '100vh';
                        this.sidebar.style.zIndex = '1000';
                    }
                });
                
                // Close sidebar on escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeSidebar();
                    }
                });
                
                // Initialize sidebar state based on screen size
                this.initializeSidebarState();
            }
            
            initializeSidebarState() {
                // On desktop (1024px and above), always show sidebar
                if (window.innerWidth > 1024) {
                    this.sidebar.classList.remove('show');
                    this.sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                    // Ensure sidebar is always visible on desktop
                    this.sidebar.style.transform = 'translateX(0)';
                    this.sidebar.style.visibility = 'visible';
                    this.sidebar.style.opacity = '1';
                    this.sidebar.style.position = 'fixed';
                    this.sidebar.style.left = '0';
                    this.sidebar.style.top = '0';
                    this.sidebar.style.height = '100vh';
                    this.sidebar.style.zIndex = '1000';
                }
            }
            
            toggleSidebar() {
                // Only toggle on mobile devices (1024px and below)
                if (window.innerWidth <= 1024) {
                    this.sidebar.classList.toggle('show');
                    this.sidebarOverlay.classList.toggle('show');
                    document.body.classList.toggle('sidebar-open');
                }
                // On desktop, ensure sidebar is always visible
                if (window.innerWidth > 1024) {
                    this.sidebar.style.transform = 'translateX(0)';
                    this.sidebar.style.visibility = 'visible';
                    this.sidebar.style.opacity = '1';
                    this.sidebar.style.position = 'fixed';
                    this.sidebar.style.left = '0';
                    this.sidebar.style.top = '0';
                    this.sidebar.style.height = '100vh';
                    this.sidebar.style.zIndex = '1000';
                }
            }
            
            closeSidebar() {
                this.sidebar.classList.remove('show');
                this.sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        }
        
        // Accessibility Enhancements
        class AccessibilityManager {
            constructor() {
                this.init();
            }
            
            init() {
                // Add focus indicators
                this.addFocusIndicators();
                
                // Add keyboard navigation
                this.addKeyboardNavigation();
                
                // Add skip links
                this.addSkipLinks();
            }
            
            addFocusIndicators() {
                const focusableElements = document.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
                
                focusableElements.forEach(element => {
                    element.addEventListener('focus', () => {
                        element.classList.add('focus-visible');
                    });
                    
                    element.addEventListener('blur', () => {
                        element.classList.remove('focus-visible');
                    });
                });
            }
            
            addKeyboardNavigation() {
                // Tab navigation
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Tab') {
                        document.body.classList.add('keyboard-navigation');
                    }
                });
                
                // Mouse navigation
                document.addEventListener('mousedown', () => {
                    document.body.classList.remove('keyboard-navigation');
                });
            }
            
            addSkipLinks() {
                const skipLink = document.createElement('a');
                skipLink.href = '#main-content';
                skipLink.textContent = 'Skip to main content';
                skipLink.className = 'skip-link sr-only';
                skipLink.style.cssText = `
                    position: absolute;
                    top: -40px;
                    left: 6px;
                    background: var(--primary);
                    color: white;
                    padding: 8px;
                    text-decoration: none;
                    border-radius: 4px;
                    z-index: 10000;
                `;
                
                skipLink.addEventListener('focus', () => {
                    skipLink.style.top = '6px';
                });
                
                skipLink.addEventListener('blur', () => {
                    skipLink.style.top = '-40px';
                });
                
                document.body.insertBefore(skipLink, document.body.firstChild);
            }
        }
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new ThemeManager();
            new MobileNavigation();
            new AccessibilityManager();
            
            // Additional sidebar persistence for desktop
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && window.innerWidth > 1024) {
                // Ensure sidebar is always visible on page load
                sidebar.style.transform = 'translateX(0)';
                sidebar.style.visibility = 'visible';
                sidebar.style.opacity = '1';
                sidebar.style.position = 'fixed';
                sidebar.style.left = '0';
                sidebar.style.top = '0';
                sidebar.style.height = '100vh';
                sidebar.style.zIndex = '1000';
                
                // Monitor for any changes that might hide the sidebar
                const observer = new MutationObserver(() => {
                    if (window.innerWidth > 1024) {
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
                
                observer.observe(sidebar, {
                    attributes: true,
                    attributeFilter: ['style', 'class']
                });
                
                // Ensure sidebar stays visible during scroll
                window.addEventListener('scroll', () => {
                    if (window.innerWidth > 1024) {
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
            }
        });

        // User Avatar Dropdown
        class UserAvatarDropdown {
            constructor() {
                this.avatar = document.getElementById('userAvatarToggle');
                this.dropdown = document.getElementById('userDropdownMenu');
                this.isOpen = false;
                
                this.init();
            }
            
            init() {
                if (this.avatar && this.dropdown) {
                    this.avatar.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleDropdown();
                    });
                    
                    // Close dropdown when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!this.avatar.contains(e.target) && !this.dropdown.contains(e.target)) {
                            this.closeDropdown();
                        }
                    });
                    
                    // Close dropdown on escape key
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.isOpen) {
                            this.closeDropdown();
                        }
                    });
                }
            }
            
            toggleDropdown() {
                if (this.isOpen) {
                    this.closeDropdown();
                } else {
                    this.openDropdown();
                }
            }
            
            openDropdown() {
                this.dropdown.classList.add('show');
                this.avatar.classList.add('active');
                this.isOpen = true;
                
                // Add animation
                this.dropdown.style.opacity = '0';
                this.dropdown.style.transform = 'translateY(-10px)';
                
                requestAnimationFrame(() => {
                    this.dropdown.style.transition = 'all 0.2s ease';
                    this.dropdown.style.opacity = '1';
                    this.dropdown.style.transform = 'translateY(0)';
                });
            }
            
            closeDropdown() {
                this.dropdown.classList.remove('show');
                this.avatar.classList.remove('active');
                this.isOpen = false;
                
                // Add animation
                this.dropdown.style.transition = 'all 0.2s ease';
                this.dropdown.style.opacity = '0';
                this.dropdown.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    this.dropdown.style.transition = '';
                }, 200);
            }
        }
        
        // Initialize user avatar dropdown when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new UserAvatarDropdown();
        });

    </script>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">
                        <i class="fas fa-user me-2"></i>
                        User Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="profile-avatar-large mb-3">
                                <div class="user-avatar-large">
                                    <?php echo strtoupper(substr($_SESSION['admin_email'] ?? 'A', 0, 1)); ?>
                                </div>
                            </div>
                            <h6 class="text-muted">Profile Picture</h6>
                        </div>
                        <div class="col-md-8">
                            <div class="profile-info">
                                <div class="info-group mb-3">
                                    <label class="form-label fw-semibold">Full Name</label>
                                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'admin@phitsol.com'); ?></div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="form-label fw-semibold">Role</label>
                                    <div class="info-value">
                                        <span class="badge bg-primary">Administrator</span>
                                    </div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="form-label fw-semibold">Last Login</label>
                                    <div class="info-value"><?php echo date('F j, Y \a\t g:i A'); ?></div>
                                </div>
                                <div class="info-group">
                                    <label class="form-label fw-semibold">Account Status</label>
                                    <div class="info-value">
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">
                        <i class="fas fa-cog me-2"></i>
                        Account Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="passwordChangeForm">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required minlength="8">
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Password Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>At least 8 characters long</li>
                                <li>Contains uppercase and lowercase letters</li>
                                <li>Contains at least one number</li>
                                <li>Contains at least one special character</li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="changePasswordBtn">Change Password</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Profile and Settings functionality
        document.addEventListener('DOMContentLoaded', function() {
            const profileLink = document.getElementById('profileLink');
            const settingsLink = document.getElementById('settingsLink');
            const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
            const settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            const passwordForm = document.getElementById('passwordChangeForm');

            // Profile link handler
            if (profileLink) {
                profileLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    profileModal.show();
                });
            }

            // Settings link handler
            if (settingsLink) {
                settingsLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    settingsModal.show();
                });
            }

            // Password change functionality
            if (changePasswordBtn) {
                changePasswordBtn.addEventListener('click', function() {
                    const currentPassword = document.getElementById('currentPassword').value;
                    const newPassword = document.getElementById('newPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;

                    // Basic validation
                    if (!currentPassword || !newPassword || !confirmPassword) {
                        alert('Please fill in all password fields.');
                        return;
                    }

                    if (newPassword !== confirmPassword) {
                        alert('New password and confirmation do not match.');
                        return;
                    }

                    if (newPassword.length < 8) {
                        alert('New password must be at least 8 characters long.');
                        return;
                    }

                    // Disable button to prevent multiple submissions
                    changePasswordBtn.disabled = true;
                    changePasswordBtn.textContent = 'Changing Password...';

                    // Send data to server
                    const formData = new FormData();
                    formData.append('currentPassword', currentPassword);
                    formData.append('newPassword', newPassword);
                    formData.append('confirmPassword', confirmPassword);

                    fetch('index.php?action=users&method=change-password', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Password changed successfully!');
                            passwordForm.reset();
                            settingsModal.hide();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while changing password. Please try again.');
                    })
                    .finally(() => {
                        // Re-enable button
                        changePasswordBtn.disabled = false;
                        changePasswordBtn.textContent = 'Change Password';
                    });
                });
            }

            // Real-time password confirmation validation
            const confirmPasswordField = document.getElementById('confirmPassword');
            if (confirmPasswordField) {
                confirmPasswordField.addEventListener('input', function() {
                    const newPassword = document.getElementById('newPassword').value;
                    const confirmPassword = this.value;

                    if (confirmPassword && newPassword !== confirmPassword) {
                        this.setCustomValidity('Passwords do not match');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
    </script>
</body>
</html> 