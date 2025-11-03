<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php?action=login');
    exit;
}

// Check if user data is available
if (!isset($user) || !$user) {
    header('Location: index.php?action=users');
    exit;
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-edit"></i>
            Edit User
        </h1>
        <div class="stats-info">
            <span class="stat-item">Editing: <?php echo htmlspecialchars($user['username']); ?></span>
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
        <div class="form-title">
            <i class="fas fa-edit"></i>
            Edit User Information
        </div>
        
        <form method="POST" action="index.php?action=users&method=edit&id=<?php echo $user['_id']; ?>" id="userForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required minlength="3" placeholder="Enter username">
                        <div class="form-text">Minimum 3 characters, unique</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="Enter email address">
                        <div class="form-text">Must be a valid email address</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required placeholder="Enter full name">
                        <div class="form-text">User's complete name</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="Enter new password">
                        <div class="form-text">Leave blank to keep current password (minimum 6 characters)</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role">
                            <option value="employee" <?php echo ($user['role'] ?? '') === 'employee' ? 'selected' : ''; ?>>Employee</option>
                            <option value="business" <?php echo ($user['role'] ?? '') === 'business' ? 'selected' : ''; ?>>Business Customer</option>
                            <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <div class="form-text">User role and permissions</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" <?php echo ($user['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="pending" <?php echo ($user['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        </select>
                        <div class="form-text">Account status</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter phone number">
                        <div class="form-text">Optional contact number</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="company" class="form-label">Company</label>
                        <input type="text" class="form-control" id="company" name="company" value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>" placeholder="Enter company name">
                        <div class="form-text">For business users</div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                <div class="form-text">Optional address information</div>
            </div>
            
            <div class="form-actions">
                <a href="index.php?action=users" class="btn btn-cancel">
                    <i class="fas fa-times"></i>
                    Discard Changes
                </a>
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Enhanced Button Styles for Edit User Page */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-save {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-save:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.btn-cancel {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
}

.btn-cancel:hover {
    background: linear-gradient(135deg, #5a6268 0%, #343a40 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
}

/* Form Actions Styling */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
    margin-top: 2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userForm = document.getElementById('userForm');
    
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const fullName = document.getElementById('name').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters long');
                document.getElementById('username').focus();
                return;
            }
            
            if (!email || !email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address');
                document.getElementById('email').focus();
                return;
            }
            
            if (!fullName) {
                e.preventDefault();
                alert('Please enter the full name');
                document.getElementById('name').focus();
                return;
            }
            
            if (password && password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                document.getElementById('password').focus();
                return;
            }
        });
    }
});
</script> 