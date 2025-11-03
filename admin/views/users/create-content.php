<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php?action=login');
    exit;
}

// Get user count for the form
$userCount = isset($userCount) ? $userCount : 0;
$maxUsers = isset($maxUsers) ? $maxUsers : 100;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-user-plus"></i>
            Add New User
        </h1>
        <div class="stats-info">
            <span class="stat-item">Create new user account</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $userCount; ?> of <?php echo $maxUsers; ?> users</span>
        </div>
    </div>
    <div class="header-actions">
        <a href="index.php?action=users" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Users
        </a>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="form-title">
                    <h3>User Information</h3>
                    <p>Fill in the details to create a new user account</p>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="POST" action="index.php?action=users&method=create" id="userForm" class="needs-validation" novalidate>
                <!-- Basic Information -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Basic Information
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Username *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       required 
                                       minlength="3" 
                                       placeholder="Enter username"
                                       pattern="[a-zA-Z0-9_]+"
                                       title="Username can only contain letters, numbers, and underscores">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Minimum 3 characters, unique, letters/numbers/underscores only
                                </div>
                                <div class="invalid-feedback">
                                    Please provide a valid username (minimum 3 characters).
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address *
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       required 
                                       placeholder="Enter email address">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Must be a valid email address
                                </div>
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-id-card"></i>
                                    Full Name *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       required 
                                       placeholder="Enter full name">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    User's complete name
                                </div>
                                <div class="invalid-feedback">
                                    Please provide the full name.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Password *
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           minlength="6" 
                                           placeholder="Enter password">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Minimum 6 characters
                                </div>
                                <div class="invalid-feedback">
                                    Password must be at least 6 characters long.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Settings -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-cog"></i>
                        Account Settings
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role" class="form-label">
                                    <i class="fas fa-user-tag"></i>
                                    Role
                                </label>
                                <select class="form-control" id="role" name="role">
                                    <option value="employee">👤 Employee</option>
                                    <option value="business">🏢 Business Customer</option>
                                    <option value="admin">👑 Admin</option>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    User role and permissions
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label">
                                    <i class="fas fa-toggle-on"></i>
                                    Status
                                </label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active">✅ Active</option>
                                    <option value="inactive">❌ Inactive</option>
                                    <option value="pending">⏳ Pending</option>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Account status
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-address-book"></i>
                        Contact Information
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Phone Number
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="Enter phone number"
                                       pattern="[0-9+\-\s\(\)]+">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Optional contact number
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Company
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="company" 
                                       name="company" 
                                       placeholder="Enter company name">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    For business users
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Address
                        </label>
                        <textarea class="form-control" 
                                  id="address" 
                                  name="address" 
                                  rows="3" 
                                  placeholder="Enter address"></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Optional address information
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="index.php?action=users" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-container {
    max-width: 800px;
    margin: 0 auto;
}

.form-card {
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    border: 1px solid var(--border-color);
}

.form-header {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.form-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    font-size: 1.5rem;
}

.form-title h3 {
    margin: 0;
    color: var(--text-primary);
    font-weight: 600;
}

.form-title p {
    margin: 0.25rem 0 0 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.form-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: var(--bg-secondary);
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.section-title {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.1rem;
}

.section-title i {
    margin-right: 0.5rem;
    color: var(--primary-color);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.form-label i {
    margin-right: 0.5rem;
    color: var(--primary-color);
    width: 16px;
}

.form-control {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    background: var(--input-bg);
    color: var(--text-primary);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
    background: var(--input-bg);
    color: var(--text-primary);
}

.form-text {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.form-text i {
    margin-right: 0.25rem;
    color: var(--info-color);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--border-color);
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border: none;
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.4);
}

.btn-outline-secondary {
    border: 2px solid var(--border-color);
    color: var(--text-secondary);
    background: transparent;
}

.btn-outline-secondary:hover {
    background: var(--bg-secondary);
    border-color: var(--text-secondary);
    color: var(--text-primary);
}

.alert {
    border-radius: 8px;
    border: none;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: rgba(25, 135, 84, 0.1);
    color: #198754;
    border-left: 4px solid #198754;
}

.alert-danger {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border-left: 4px solid #dc3545;
}

.input-group .btn {
    border-radius: 0 8px 8px 0;
    border-left: none;
}

.input-group .form-control {
    border-radius: 8px 0 0 8px;
}

@media (max-width: 768px) {
    .form-card {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userForm = document.getElementById('userForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    // Password toggle functionality
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Form validation
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            if (!userForm.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            userForm.classList.add('was-validated');
            
            // Custom validation
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const fullName = document.getElementById('name').value.trim();
            const password = document.getElementById('password').value;
            
            let isValid = true;
            
            // Username validation
            if (!username || username.length < 3) {
                showFieldError('username', 'Username must be at least 3 characters long');
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                showFieldError('username', 'Username can only contain letters, numbers, and underscores');
                isValid = false;
            } else {
                clearFieldError('username');
            }
            
            // Email validation
            if (!email || !email.includes('@')) {
                showFieldError('email', 'Please enter a valid email address');
                isValid = false;
            } else {
                clearFieldError('email');
            }
            
            // Name validation
            if (!fullName) {
                showFieldError('name', 'Please enter the full name');
                isValid = false;
            } else {
                clearFieldError('name');
            }
            
            // Password validation
            if (!password || password.length < 6) {
                showFieldError('password', 'Password must be at least 6 characters long');
                isValid = false;
            } else {
                clearFieldError('password');
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = userForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating User...';
            submitBtn.disabled = true;
            
            // Re-enable after a delay (in case of validation failure)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    }
    
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        
        if (feedback) {
            feedback.textContent = message;
            feedback.style.display = 'block';
        }
        
        field.classList.add('is-invalid');
    }
    
    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        
        if (feedback) {
            feedback.style.display = 'none';
        }
        
        field.classList.remove('is-invalid');
    }
    
    // Real-time validation
    const inputs = userForm.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });
});
</script> 