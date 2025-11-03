<?php
// Load Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// 공유 설정 로드
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Blog;
use App\Models\User;

use App\Models\SupportMessage;

// Set current action for sidebar highlighting
$currentAction = 'dashboard';
$pageTitle = 'Dashboard';

// Get statistics
$blogModel = new Blog();
$userModel = new User();

$supportMessageModel = new SupportMessage();
$totalUsers = $userModel->getActiveCount();
$totalPosts = $blogModel->getCount();
$publishedPosts = $blogModel->getCount('published');
$draftPosts = $blogModel->getCount('draft');
$videoPosts = count($blogModel->getByType('video'));
$adminUsers = $userModel->getAdminCount();
$employeeUsers = $userModel->getEmployeeCount();
$businessUsers = $userModel->getBusinessCount();
$totalDocuments = 0;
$pendingSupport = $supportMessageModel->getPendingCount();

// 1. 전체 사용자 및 서류 상태 통계 계산
$allUsers = $userModel->getAll();
$docList = [
    'company_profile', 'mayors_permit', 'bir_2303', 'gis', 'audited_fs',
    'proof_address', 'signatory_id', 'sec_cert', 'auth_representative', 'credit_form', 'peza_cert'
];
$statusCount = ['pending'=>0, 'approved'=>0, 'rejected'=>0];
$recentChanges = [];
$partnerRows = [];
foreach ($allUsers as $u) {
    if (($u['role'] ?? '') !== 'business') continue;
    $userDocs = $u['document_status'] ?? [];
    $pending = 0; $approved = 0; $rejected = 0;
    foreach ($docList as $docKey) {
        $s = $userDocs[$docKey]['status'] ?? 'pending';
        if ($s === 'pending') $pending++;
        if ($s === 'approved') $approved++;
        if ($s === 'rejected') $rejected++;
        $statusCount[$s]++;
        // 최근 변경 내역 수집
        if (!empty($userDocs[$docKey]['updated_at'])) {
            $recentChanges[] = [
                'user' => $u['name'] ?? ($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''),
                'email' => $u['email'] ?? '',
                'doc' => $docKey,
                'status' => $s,
                'updated_at' => is_object($userDocs[$docKey]['updated_at']) ? $userDocs[$docKey]['updated_at']->toDateTime()->format('Y-m-d H:i:s') : $userDocs[$docKey]['updated_at']
            ];
        }
    }
    $partnerRows[] = [
                        'name' => $u['name'] ?? ($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''),
        'email' => $u['email'] ?? '',
        'pending' => $pending,
        'approved' => $approved,
        'rejected' => $rejected
    ];
}
// 최근 변경 내림차순 정렬
usort($recentChanges, function($a, $b) {
    return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
});
$recentChanges = array_slice($recentChanges, 0, 5);
$statusLabel = [
    'pending' => '<span class="badge bg-warning">Pending</span>',
    'approved' => '<span class="badge bg-success">Approved</span>',
    'rejected' => '<span class="badge bg-danger">Rejected</span>'
];

ob_start();
?>

<style>
/* Statistics Grid Layout */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.stat-card:nth-child(1) .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card:nth-child(2) .stat-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.stat-card:nth-child(3) .stat-icon {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}

.stat-card:nth-child(4) .stat-icon {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.stat-card:nth-child(5) .stat-icon {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
}

.stat-card:nth-child(6) .stat-icon {
    background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
}

.stat-card:nth-child(7) .stat-icon {
    background: linear-gradient(135deg, #fd7e14 0%, #ff6b6b 100%);
}

.stat-card:nth-child(8) .stat-icon {
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #343a40;
    margin-bottom: 0.25rem;
    line-height: 1;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Dashboard Overview -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard Overview
        </h1>
        <div class="stats-info">
            <span class="stat-item"><?php echo $totalPosts; ?> posts</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo $totalUsers; ?> users</span>
        </div>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">
    <!-- Statistics Grid by Menu Categories -->
    
    <!-- Content Management Section -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-file-alt"></i>
                Content Management
            </h3>
        </div>
        <div class="admin-card-body">
            <div class="stats-cards">
                
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
            </div>
        </div>
    </div>
    
    <!-- User Management Section -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-users"></i>
                User Management
            </h3>
        </div>
        <div class="admin-card-body">
            <div class="stats-cards">
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
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $employeeUsers; ?></div>
                        <div class="stat-label">Employee Users</div>
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
            </div>
        </div>
    </div>
    
    <!-- Document Management Section -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-file-contract"></i>
                Document Management
            </h3>
        </div>
        <div class="admin-card-body">
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $statusCount['pending']; ?></div>
                        <div class="stat-label">Pending Documents</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $statusCount['approved']; ?></div>
                        <div class="stat-label">Approved Documents</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $statusCount['rejected']; ?></div>
                        <div class="stat-label">Rejected Documents</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Support & System Section -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-headset"></i>
                Support & System
            </h3>
        </div>
        <div class="admin-card-body">
            <div class="stats-cards">
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
        </div>
    </div>



    <!-- Recent Document Status Changes -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-history"></i>
                Recent Document Status Changes
            </h3>
        </div>
        <div class="admin-card-body">
            <?php if (empty($recentChanges)): ?>
                <div class="text-muted">No recent changes.</div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th><th>Email</th><th>Document</th><th>Status</th><th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentChanges as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['doc']); ?></td>
                        <td><?php echo $statusLabel[$row['status']] ?? $row['status']; ?></td>
                        <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Partner Document Status Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-users"></i>
                Partner Document Status Overview
            </h3>
        </div>
        <div class="admin-card-body">
            <?php if (empty($partnerRows)): ?>
                <div class="text-muted">No business partners found.</div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th><th>Email</th><th>Pending</th><th>Approved</th><th>Rejected</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partnerRows as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo $row['pending']; ?></td>
                        <td><?php echo $row['approved']; ?></td>
                        <td><?php echo $row['rejected']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions and System Info -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="fas fa-rocket"></i>
                        Quick Actions
                    </h3>
                    <p class="admin-card-description">
                        Quick access to main management functions. Create new content or navigate to management sections.
                    </p>
                </div>
                <div class="admin-card-body">
                    <div class="d-grid gap-2">
                        <a href="index.php?action=blog&method=create" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add New Blog Post
                        </a>
                        <a href="index.php?action=blog" class="btn btn-outline-primary">
                            <i class="fas fa-blog"></i>
                            Manage Blog
                        </a>
                        <a href="/partners.php" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt"></i>
                            Document Management
                        </a>
                        <a href="index.php?action=support-messages" class="btn btn-outline-secondary">
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
                        <i class="fas fa-info-circle"></i>
                        System Information
                    </h3>
                    <p class="admin-card-description">
                        Current system configuration and technical details for administrators.
                    </p>
                </div>
                <div class="admin-card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
                        </li>
                        <li class="mb-2">
                            <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                        </li>
                        <li class="mb-2">
                            <strong>Database:</strong> MongoDB
                        </li>
                        <li class="mb-0">
                            <strong>Last Login:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
include 'views/layout.php';
?> 