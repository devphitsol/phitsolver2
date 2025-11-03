<style>
/* Statistics Grid Layout Improvements */
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

/* Table Header Styles */
.table-header {
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.table-info {
    flex: 1;
}

.table-title {
    margin: 0 0 0.5rem 0;
    color: #495057;
    font-weight: 600;
    font-size: 1.1rem;
}

.table-description {
    margin: 0;
    color: #6c757d;
    font-size: 0.875rem;
    line-height: 1.4;
}

.table-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
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

<!-- Content Header -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-blog"></i>
            Blog Management
        </h1>
        <div class="stats-info">
            <span class="stat-item"><?php echo count($posts); ?> posts</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo count(array_filter($posts, function($post) { return $post['status'] === 'published'; })); ?> published</span>
            <span class="stat-divider">•</span>
            <span class="stat-item"><?php echo count(array_filter($posts, function($post) { return $post['status'] === 'draft'; })); ?> drafts</span>
        </div>
    </div>
    <div class="header-actions">
        <a href="index.php?action=blog&method=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Post
        </a>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">
    <!-- Statistics Grid -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-blog"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo count($posts); ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo count(array_filter($posts, function($post) { return $post['status'] === 'published'; })); ?></div>
                <div class="stat-label">Published Posts</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo count(array_filter($posts, function($post) { return $post['status'] === 'draft'; })); ?></div>
                <div class="stat-label">Draft Posts</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo count(array_filter($posts, function($post) { 
                    $postDate = strtotime($post['created_at']);
                    $currentDate = time();
                    $daysDiff = floor(($currentDate - $postDate) / (60 * 60 * 24));
                    return $daysDiff <= 7; // Posts created in last 7 days
                })); ?></div>
                <div class="stat-label">Recent Posts (7 days)</div>
            </div>
        </div>
    </div>

    <!-- Blog Posts -->
    <div class="table-container">
        <div class="table-header">
            <div class="table-info">
                <h5 class="table-title">
                    <i class="fas fa-list me-2"></i>
                    Blog Posts
                </h5>
                <p class="table-description">
                    Manage your blog posts. Use the action buttons to edit, view, or delete posts. Published posts are visible to visitors.
                </p>
            </div>
            <div class="table-actions">
                <!-- Additional actions can be added here if needed -->
            </div>
        </div>
        
        <div class="card-body">
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <i class="fas fa-blog"></i>
                    <h4>No blog posts yet</h4>
                    <p>Create your first blog post to get started</p>
                    <a href="index.php?action=blog&method=create" class="btn-add">
                        <i class="fas fa-plus"></i>
                        Add First Post
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="blog-card">
                                <?php if (!empty($post['featured_image'])): ?>
                                    <img src="public/uploads/blog/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                         class="blog-image">
                                <?php else: ?>
                                    <div class="blog-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="blog-content">
                                    <h6 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h6>
                                    <p class="blog-excerpt"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                                    
                                    <div class="blog-meta">
                                        <span class="blog-date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                        <span class="post-status status-<?php echo $post['status']; ?>">
                                            <?php echo ucfirst($post['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="post-actions">
                                        <a href="index.php?action=blog&method=edit&id=<?php echo $post['_id']; ?>" 
                                           class="btn-action btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../public/blog.php?id=<?php echo $post['_id']; ?>" 
                                           class="btn-action btn-view" title="View" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?action=blog&method=delete&id=<?php echo $post['_id']; ?>" 
                                           class="btn-action btn-delete" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this post?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 