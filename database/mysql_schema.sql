-- MySQL Database Schema for PHITSOL Partners Portal
-- This schema creates all necessary tables to replace MongoDB collections

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS phitsol_partners CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phitsol_partners;

-- Users table (replaces MongoDB users collection)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role ENUM('admin', 'super_admin', 'employee', 'business') DEFAULT 'employee',
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    phone VARCHAR(20),
    company VARCHAR(100),
    position VARCHAR(100),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(20),
    website VARCHAR(255),
    bio TEXT,
    avatar VARCHAR(255),
    last_login DATETIME NULL,
    login_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Security fields for admin users
    is_default_password BOOLEAN DEFAULT FALSE,
    password_change_required BOOLEAN DEFAULT FALSE,
    password_history JSON,
    is_first_login BOOLEAN DEFAULT TRUE,
    
    -- Document status for business users
    document_status JSON,
    documents JSON,
    
    -- Indexes
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Blog posts table (replaces MongoDB blog_posts collection)
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    type ENUM('post', 'video') DEFAULT 'post',
    category VARCHAR(100),
    tags JSON,
    featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    author_id INT,
    featured_image VARCHAR(255),
    video_url VARCHAR(500),
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_category (category),
    INDEX idx_featured (featured),
    INDEX idx_created_at (created_at),
    INDEX idx_published_at (published_at),
    FULLTEXT idx_content (title, content, excerpt)
);

-- Sliders table (replaces MongoDB sliders collection)
CREATE TABLE IF NOT EXISTS sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    link VARCHAR(500),
    button_text VARCHAR(100),
    order_index INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_status (status),
    INDEX idx_order (order_index),
    INDEX idx_created_at (created_at)
);

-- Support messages table (replaces MongoDB support_messages collection)
CREATE TABLE IF NOT EXISTS support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(100),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    assigned_to INT NULL,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    
    -- Foreign key
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_created_at (created_at)
);

-- Products table (for product catalog)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    price DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'USD',
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    images JSON,
    specifications JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_sku (sku),
    INDEX idx_created_at (created_at)
);

-- Purchase orders table
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    
    -- Foreign key
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_order_number (order_number),
    INDEX idx_created_at (created_at)
);

-- Purchase order items table
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- Partners portal audit log table
CREATE TABLE IF NOT EXISTS partners_portal_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp)
);

-- Newsletter subscriptions table
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Create views for common queries
CREATE VIEW active_users AS
SELECT * FROM users WHERE status = 'active';

CREATE VIEW published_blog_posts AS
SELECT * FROM blog_posts WHERE status = 'published';

CREATE VIEW pending_support_messages AS
SELECT * FROM support_messages WHERE status = 'new';

-- Insert default admin user (password: admin123)
INSERT INTO users (
    username, email, password, name, role, status, 
    is_default_password, password_change_required, is_first_login
) VALUES (
    'admin', 'admin@phitsol.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'System Administrator', 'super_admin', 'active', 
    TRUE, TRUE, TRUE
) ON DUPLICATE KEY UPDATE username = username;

-- Insert sample blog post
INSERT INTO blog_posts (
    title, slug, content, excerpt, status, type, category, featured
) VALUES (
    'Welcome to PHITSOL Partners Portal', 
    'welcome-to-phitsol-partners-portal',
    '<p>Welcome to the PHITSOL Partners Portal. This is your gateway to managing your partnership with PHITSOL.</p>',
    'Welcome to the PHITSOL Partners Portal.',
    'published',
    'post',
    'General',
    TRUE
) ON DUPLICATE KEY UPDATE title = title;

-- Insert sample slider
INSERT INTO sliders (
    title, description, image, link, button_text, status, order_index
) VALUES (
    'Welcome to PHITSOL',
    'Your trusted partner in technology solutions',
    '/images/slider1.jpg',
    '/about',
    'Learn More',
    'active',
    1
) ON DUPLICATE KEY UPDATE title = title;
