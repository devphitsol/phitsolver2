<?php
// Get current action to highlight active menu item
$currentAction = $_GET['action'] ?? 'dashboard';
?>
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

        <a href="index.php?action=support-messages" class="nav-link <?php echo $currentAction === 'support-messages' ? 'active' : ''; ?>">
            <i class="fas fa-headset"></i>
            Support Messages
        </a>
        <a href="index.php?action=company" class="nav-link <?php echo $currentAction === 'company' ? 'active' : ''; ?>">
            <i class="fas fa-building"></i>
            Company Management
        </a>
        <a href="index.php?action=products" class="nav-link <?php echo $currentAction === 'products' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            Product Management
        </a>
    </div>
</nav>

<!-- Top Header -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">
            <?php
            switch($currentAction) {
                case 'blog':
                    echo 'Blog Management';
                    break;
                case 'users':
                    echo 'User Management';
                    break;

                case 'support-messages':
                    echo 'Support Messages';
                    break;
                case 'company':
                    echo 'Company Management';
                    break;
                case 'products':
                    echo 'Product Management';
                    break;
                default:
                    echo 'Dashboard';
                    break;
            }
            ?>
        </h1>
        <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'Admin'); ?></p>
    </div>
    <div class="user-info">
        <div class="user-avatar">
            <?php echo strtoupper(substr($_SESSION['admin_email'] ?? 'A', 0, 1)); ?>
        </div>
        <a href="index.php?logout=1" class="btn-logout">
            <i class="fas fa-sign-out-alt me-2"></i>
            Logout
        </a>
    </div>
</div> 