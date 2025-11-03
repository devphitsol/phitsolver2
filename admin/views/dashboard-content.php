<?php
// Load Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// 공유 설정 로드
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Slider;
use App\Models\Blog;
use App\Models\User;

use App\Models\SupportMessage;

// Get statistics
$sliderModel = new Slider();
$blogModel = new Blog();
$userModel = new User();

$supportMessageModel = new SupportMessage();

$totalSlides = $sliderModel->getCount();
$activeSlides = count($sliderModel->getActive());
$totalUsers = $userModel->getActiveCount();
$totalPosts = $blogModel->getCount();
$publishedPosts = $blogModel->getCount('published');
$draftPosts = $blogModel->getCount('draft');
$videoPosts = count($blogModel->getByType('video'));
$adminUsers = $userModel->getAdminCount();
$totalDocuments = 0;
$pendingSupport = $supportMessageModel->getPendingCount();
$businessUsers = $userModel->getBusinessUserCount();
$pendingUsers = $userModel->getPendingCount();
$pendingDocuments = 0;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard Overview
        </h1>
        <div class="stats-info">
            <span class="stat-item"><?php echo $totalSlides; ?> slides</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $totalPosts; ?> posts</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $totalUsers; ?> users</span>
        </div>
    </div>
    <div class="header-actions">
        <a href="index.php?action=slider&method=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Slide
        </a>
        <a href="index.php?action=blog&method=create" class="btn btn-outline-primary">
            <i class="fas fa-plus"></i>
            Add Post
        </a>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">
    <!-- Statistics Grid -->
    <div class="stats-cards">
        <!-- Content Management -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-images"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $totalSlides; ?></div>
                <div class="stat-label">Total Slides</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $activeSlides; ?></div>
                <div class="stat-label">Active Slides</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-blog"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $totalPosts; ?></div>
                <div class="stat-label">Blog Posts</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $publishedPosts; ?></div>
                <div class="stat-label">Published Posts</div>
            </div>
        </div>
        
        <!-- User Management -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $adminUsers; ?></div>
                <div class="stat-label">Admin Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $businessUsers; ?></div>
                <div class="stat-label">Business Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $pendingUsers; ?></div>
                <div class="stat-label">Pending Users</div>
            </div>
        </div>
        
        <!-- Content Status -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $draftPosts; ?></div>
                <div class="stat-label">Draft Posts</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-video"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $videoPosts; ?></div>
                <div class="stat-label">Video Posts</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-headset"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $pendingSupport; ?></div>
                <div class="stat-label">Pending Support</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h3>
                    <p class="admin-card-description">
                        Quick access to main management sections. Click on any action to navigate to the respective management page.
                    </p>
                </div>
                <div class="admin-card-body">
                    <div class="d-grid gap-2">
                        <a href="index.php?action=slider" class="btn btn-outline-primary">
                            <i class="fas fa-images"></i>
                            Manage Slider
                        </a>
                        <a href="index.php?action=blog" class="btn btn-outline-primary">
                            <i class="fas fa-blog"></i>
                            Manage Blog Posts
                        </a>
                        <a href="index.php?action=users" class="btn btn-outline-primary">
                            <i class="fas fa-users"></i>
                            Manage Users
                        </a>
                        <a href="index.php?action=support-messages" class="btn btn-outline-primary">
                            <i class="fas fa-headset"></i>
                            Support Messages
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="fas fa-chart-line"></i>
                        System Status
                    </h3>
                    <p class="admin-card-description">
                        Current system overview showing active content, users, and pending items that require attention.
                    </p>
                </div>
                <div class="admin-card-body">
                    <div class="list-unstyled">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Slider Status</span>
                            <span class="badge badge-light"><?php echo $activeSlides; ?>/<?php echo $totalSlides; ?> Active</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Blog Status</span>
                            <span class="badge badge-light"><?php echo $publishedPosts; ?>/<?php echo $totalPosts; ?> Published</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>User Status</span>
                            <span class="badge badge-light"><?php echo $totalUsers; ?> Active</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Support</span>
                            <span class="badge badge-light"><?php echo $pendingSupport; ?> Pending</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 