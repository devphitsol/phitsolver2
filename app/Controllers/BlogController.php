<?php

namespace App\Controllers;

// Note: Models are autoloaded by Composer

use App\Models\Blog;

class BlogController
{
    private $blogModel;

    public function __construct()
    {
        $this->blogModel = new Blog();
    }

    /**
     * Show blog posts list
     */
    public function index()
    {
        $posts = $this->blogModel->getAll();
        $totalPosts = $this->blogModel->getCount();
        $publishedPosts = $this->blogModel->getCount('published');
        $draftPosts = $this->blogModel->getCount('draft');
        
        return [
            'posts' => $posts,
            'totalPosts' => $totalPosts,
            'publishedPosts' => $publishedPosts,
            'draftPosts' => $draftPosts
        ];
    }

    /**
     * Handle create blog post form submission
     */
    public function create()
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateBlogData($_POST);
            
            if (empty($data['errors'])) {
                // Handle featured image upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleImageUpload($_FILES['featured_image']);
                    if ($uploadResult['success']) {
                        $data['data']['featured_image'] = $uploadResult['filename'];
                    } else {
                        $data['errors'][] = $uploadResult['error'];
                    }
                }

                // Handle video file upload for video posts
                if ($data['data']['type'] === 'video' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleVideoUpload($_FILES['video_file']);
                    if ($uploadResult['success']) {
                        $data['data']['video_file'] = $uploadResult['filename'];
                    } else {
                        $data['errors'][] = $uploadResult['error'];
                    }
                }

                if (empty($data['errors'])) {
                    $postId = $this->blogModel->create($data['data']);
                    
                    if ($postId) {
                        $_SESSION['success'] = 'Blog post created successfully!';
                        header('Location: index.php?action=blog');
                        exit;
                    } else {
                        $_SESSION['error'] = 'Failed to create blog post.';
                    }
                }
            }
            
            if (!empty($data['errors'])) {
                $_SESSION['error'] = implode(', ', $data['errors']);
            }
        }
        
        return [
            'categories' => $this->getCategories(),
            'postTypes' => ['post', 'video']
        ];
    }

    /**
     * Handle edit blog post form submission
     */
    public function edit($id)
    {
        $post = $this->blogModel->getById($id);
        
        if (!$post) {
            $_SESSION['error'] = 'Blog post not found.';
            header('Location: index.php?action=blog');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateBlogData($_POST);
            
            if (empty($data['errors'])) {
                // Handle featured image upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleImageUpload($_FILES['featured_image']);
                    if ($uploadResult['success']) {
                        $data['data']['featured_image'] = $uploadResult['filename'];
                        
                        // Delete old image if exists
                        if (!empty($post['featured_image'])) {
                            $this->deleteImage($post['featured_image']);
                        }
                    } else {
                        $data['errors'][] = $uploadResult['error'];
                    }
                }

                // Handle video file upload for video posts
                if ($data['data']['type'] === 'video' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleVideoUpload($_FILES['video_file']);
                    if ($uploadResult['success']) {
                        $data['data']['video_file'] = $uploadResult['filename'];
                        
                        // Delete old video if exists
                        if (!empty($post['video_file'])) {
                            $this->deleteVideo($post['video_file']);
                        }
                    } else {
                        $data['errors'][] = $uploadResult['error'];
                    }
                }

                if (empty($data['errors'])) {
                    $success = $this->blogModel->update($id, $data['data']);
                    
                    if ($success) {
                        $_SESSION['success'] = 'Blog post updated successfully!';
                        header('Location: index.php?action=blog');
                        exit;
                    } else {
                        $_SESSION['error'] = 'Failed to update blog post.';
                    }
                }
            }
            
            if (!empty($data['errors'])) {
                $_SESSION['error'] = implode(', ', $data['errors']);
            }
        }
        
        return [
            'post' => $post,
            'categories' => $this->getCategories(),
            'postTypes' => ['post', 'video']
        ];
    }

    /**
     * Delete blog post
     */
    public function delete($id)
    {
        $post = $this->blogModel->getById($id);
        
        if (!$post) {
            $_SESSION['error'] = 'Blog post not found.';
            header('Location: index.php?action=blog');
            exit;
        }

        // Delete associated files
        if (!empty($post['featured_image'])) {
            $this->deleteImage($post['featured_image']);
        }
        
        if (!empty($post['video_file'])) {
            $this->deleteVideo($post['video_file']);
        }

        $success = $this->blogModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Blog post deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete blog post.';
        }
        
        header('Location: index.php?action=blog');
        exit;
    }

    /**
     * Toggle blog post status
     */
    public function toggleStatus($id)
    {
        $success = $this->blogModel->toggleStatus($id);
        
        if ($success) {
            $_SESSION['success'] = 'Blog post status updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update blog post status.';
        }
        
        header('Location: index.php?action=blog');
        exit;
    }

    /**
     * Get all posts for layout
     */
    public function getPosts()
    {
        return $this->blogModel->getAll();
    }

    /**
     * Get post count for layout
     */
    public function getPostCount()
    {
        return $this->blogModel->getCount();
    }

    /**
     * Get post by ID for layout
     */
    public function getPostById($id)
    {
        return $this->blogModel->getById($id);
    }

    /**
     * Get total posts count
     */
    public function getTotalPosts()
    {
        return $this->blogModel->getCount();
    }

    /**
     * Get published posts count
     */
    public function getPublishedPosts()
    {
        return $this->blogModel->getCount('published');
    }

    /**
     * Get draft posts count
     */
    public function getDraftPosts()
    {
        return $this->blogModel->getCount('draft');
    }

    /**
     * Get categories for form
     */
    public function getCategories()
    {
        return ['Technology', 'Business', 'Marketing', 'Design', 'Development', 'General'];
    }

    /**
     * Validate blog data
     */
    private function validateBlogData($data)
    {
        $errors = [];
        $validatedData = [];

        // Required fields
        $validatedData['title'] = trim($data['title'] ?? '');
        $validatedData['content'] = trim($data['content'] ?? '');

        if (empty($validatedData['title'])) {
            $errors[] = 'Title is required';
        }

        if (empty($validatedData['content'])) {
            $errors[] = 'Content is required';
        }

        // Optional fields
        $validatedData['excerpt'] = trim($data['excerpt'] ?? '');
        $validatedData['category'] = trim($data['category'] ?? '');
        $validatedData['tags'] = trim($data['tags'] ?? '');
        $validatedData['type'] = $data['type'] ?? 'post';
        $validatedData['status'] = $data['status'] ?? 'draft';
        $validatedData['video_url'] = trim($data['video_url'] ?? ''); // For YouTube/Vimeo links
        $validatedData['meta_title'] = trim($data['meta_title'] ?? '');
        $validatedData['meta_description'] = trim($data['meta_description'] ?? '');

        // Validate type
        if (!in_array($validatedData['type'], ['post', 'video'])) {
            $errors[] = 'Invalid post type';
        }

        // Validate status
        if (!in_array($validatedData['status'], ['draft', 'published'])) {
            $errors[] = 'Invalid status';
        }

        return [
            'data' => $validatedData,
            'errors' => $errors
        ];
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload($file)
    {
        $uploadDir = __DIR__ . '/../../admin/public/uploads/blog/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Please upload JPG, PNG, GIF, or WebP images only.'];
        }

        // Validate file size (10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'File size must be less than 10MB.'];
        }

        $filename = uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'error' => 'Failed to upload image.'];
        }
    }

    /**
     * Handle video upload
     */
    private function handleVideoUpload($file)
    {
        $uploadDir = __DIR__ . '/../../admin/public/uploads/blog/videos/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        // Validate file type
        $allowedTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Please upload MP4, AVI, MOV, WMV, FLV, or WebM videos only.'];
        }

        // Validate file size (100MB)
        if ($file['size'] > 100 * 1024 * 1024) {
            return ['success' => false, 'error' => 'File size must be less than 100MB.'];
        }

        $filename = uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'error' => 'Failed to upload video.'];
        }
    }

    /**
     * Delete image file
     */
    private function deleteImage($filename)
    {
        $imagePath = __DIR__ . '/../../admin/public/uploads/blog/' . $filename;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    /**
     * Delete video file
     */
    private function deleteVideo($filename)
    {
        $videoPath = __DIR__ . '/../../admin/public/uploads/blog/videos/' . $filename;
        if (file_exists($videoPath)) {
            unlink($videoPath);
        }
    }
} 