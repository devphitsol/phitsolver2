<?php
/**
 * Unified Blog Controller
 * Integrates both admin and public blog functionality
 * Works with MySQL as primary database
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/cache.php';

use App\Config\Database;

class UnifiedBlogController {
    private $collection;
    private $error = null;

    public function __construct() {
        try {
            // Use the modern database abstraction layer
            $db = Database::getInstance();
            $this->collection = $db->getCollection('blog_posts');
            
            // Test connection
            if ($db->isUsingFileStorage()) {
                throw new Exception('Database connection failed. Using file storage fallback.');
            }
            
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            error_log("UnifiedBlogController Error: " . $e->getMessage());
        }
    }

    /**
     * Get all blog posts with pagination and filtering
     */
    public function index($page = 1, $limit = 10, $status = null) {
        if ($this->error) {
            return [
                'posts' => [],
                'totalPosts' => 0,
                'publishedPosts' => 0,
                'draftPosts' => 0,
                'currentPage' => $page,
                'totalPages' => 1,
                'error' => $this->error
            ];
        }

        try {
            // Build filter
            $filter = [];
            if ($status) {
                $filter['status'] = $status;
            }

            // Count total posts
            $totalPosts = $this->collection->countDocuments($filter);

            // Count by status
            $publishedCount = $this->getCount('published');
            $draftCount = $this->getCount('draft');

            // Get posts with pagination
            $options = [
                'sort' => ['created_at' => -1],
                'skip' => ($page - 1) * $limit,
                'limit' => $limit
            ];

            $cursor = $this->collection->find($filter, $options);
            $posts = $cursor->toArray();

            // Convert to arrays if needed
            $postsArray = [];
            foreach ($posts as $post) {
                $postsArray[] = $this->convertDocument($post);
            }

            return [
                'posts' => $postsArray,
                'totalPosts' => $totalPosts,
                'publishedPosts' => $publishedCount,
                'draftPosts' => $draftCount,
                'currentPage' => $page,
                'totalPages' => ceil($totalPosts / $limit),
                'error' => null
            ];

        } catch (Exception $e) {
            error_log("UnifiedBlogController::index Error: " . $e->getMessage());
            return [
                'posts' => [],
                'totalPosts' => 0,
                'publishedPosts' => 0,
                'draftPosts' => 0,
                'currentPage' => $page,
                'totalPages' => 1,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get blog post by ID
     */
    public function getById($id) {
        if ($this->error) {
            return null;
        }

        try {
            // Use MySQL integer ID
            $filter = ['id' => (int)$id];
            
            $post = $this->collection->findOne($filter);

            if (!$post) {
                error_log("UnifiedBlogController::getById Error: No post found with ID: " . $id);
                return null;
            }

            return $this->convertDocument($post);

        } catch (Exception $e) {
            error_log("UnifiedBlogController::getById Error: " . $e->getMessage() . " for ID: " . $id);
            return null;
        }
    }

    /**
     * Create new blog post
     */
    public function create($data) {
        if ($this->error) {
            return ['success' => false, 'message' => $this->error];
        }

        try {
            $document = [
                'title' => $data['title'],
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 200),
                'status' => $data['status'] ?? 'draft',
                'category' => $data['category'] ?? 'Uncategorized',
                'tags' => $data['tags'] ?? [],
                'featured_image' => $data['featured_image'] ?? null,
                'author' => $_SESSION['admin_username'] ?? 'Admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null
            ];

            $result = $this->collection->insertOne($document);

            // Invalidate cache when new post is created
            $cache = getBlogCache();
            $cache->invalidateAll();

            return ['success' => true, 'message' => 'Blog post created successfully', 'id' => $result->getInsertedId()];

        } catch (Exception $e) {
            error_log("UnifiedBlogController::create Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update blog post
     */
    public function update($id, $data) {
        if ($this->error) {
            return ['success' => false, 'message' => $this->error];
        }

        try {
            // Use MySQL database
            $filter = ['id' => (int)$id];
            
            $update = [
                '$set' => [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'excerpt' => $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 200),
                    'status' => $data['status'],
                    'category' => $data['category'] ?? 'Uncategorized',
                    'tags' => $data['tags'] ?? [],
                    'featured_image' => $data['featured_image'] ?? null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];

            // Set published_at if status changed to published
            if ($data['status'] === 'published') {
                $update['$set']['published_at'] = date('Y-m-d H:i:s');
            }

            $result = $this->collection->updateOne($filter, $update);

            // Invalidate cache when post is updated
            $cache = getBlogCache();
            $cache->invalidatePost($id);

            return ['success' => true, 'message' => 'Blog post updated successfully'];

        } catch (Exception $e) {
            error_log("UnifiedBlogController::update Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete blog post
     */
    public function delete($id) {
        if ($this->error) {
            return ['success' => false, 'message' => $this->error];
        }

        try {
            // Use MySQL database
            $filter = ['id' => (int)$id];
            $result = $this->collection->deleteOne($filter);

            // Invalidate cache when post is deleted
            $cache = getBlogCache();
            $cache->invalidatePost($id);

            return ['success' => true, 'message' => 'Blog post deleted successfully'];

        } catch (Exception $e) {
            error_log("UnifiedBlogController::delete Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Toggle post status
     */
    public function toggleStatus($id) {
        $post = $this->getById($id);
        if (!$post) {
            return ['success' => false, 'message' => 'Post not found'];
        }

        $newStatus = $post['status'] === 'published' ? 'draft' : 'published';
        return $this->update($id, ['status' => $newStatus]);
    }

    /**
     * Get post count by status
     */
    public function getCount($status = null) {
        if ($this->error) {
            return 0;
        }

        try {
            $filter = [];
            if ($status) {
                $filter['status'] = $status;
            }

            return $this->collection->countDocuments($filter);

        } catch (Exception $e) {
            error_log("UnifiedBlogController::getCount Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get categories
     */
    public function getCategories() {
        if ($this->error) {
            return [];
        }

        try {
            $categories = $this->collection->distinct('category');
            return array_merge(['Uncategorized'], $categories);

        } catch (Exception $e) {
            error_log("UnifiedBlogController::getCategories Error: " . $e->getMessage());
            return ['Uncategorized'];
        }
    }

    /**
     * Convert document to array (works with MySQL)
     */
    private function convertDocument($document) {
        return $this->documentToArray($document);
    }
    
    /**
     * Public function to convert document to array
     * This can be used by public blog files
     */
    public function documentToArray($document) {
        // If it's already an array, return it
        if (is_array($document)) {
            return $document;
        }
        
        // Handle MySQL results
        if (is_object($document) && method_exists($document, 'toArray')) {
            $array = $document->toArray();
        } else {
            $array = (array)$document;
        }
        
        // MySQL uses integer IDs, no conversion needed
        // Dates are already in string format from MySQL

        return $array;
    }
}
?>
