<?php

namespace App\Models;

use App\Config\Database;
// MongoDB dependencies removed - using MySQL as primary database

class Blog
{
    private $collection;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->collection = $db->getCollection('blog_posts');
    }

    /**
     * Get all blog posts
     */
    public function getAll($limit = null, $offset = 0)
    {
        try {
            $options = [
                'sort' => ['created_at' => -1] // Newest first
            ];
            
            if ($limit) {
                $options['limit'] = $limit;
                $options['skip'] = $offset;
            }
            
            $cursor = $this->collection->find([], $options);
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching blog posts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get published blog posts only
     */
    public function getPublished($limit = null, $offset = 0)
    {
        try {
            $options = [
                'sort' => ['created_at' => -1]
            ];
            
            if ($limit) {
                $options['limit'] = $limit;
                $options['skip'] = $offset;
            }
            
            $cursor = $this->collection->find(['status' => 'published'], $options);
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching published blog posts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get blog post by ID
     */
    public function getById($id)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            $post = $this->collection->findOne(['_id' => $id]);
            return $post ? (array) $post : null;
        } catch (\Exception $e) {
            error_log("Error fetching blog post by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get blog post by slug
     */
    public function getBySlug($slug)
    {
        try {
            $post = $this->collection->findOne(['slug' => $slug, 'status' => 'published']);
            return $post ? (array) $post : null;
        } catch (\Exception $e) {
            error_log("Error fetching blog post by slug: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new blog post
     */
    public function create($data)
    {
        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['title']);
            }

            // Set default values
            $data['created_at'] = $this->getCurrentDateTime();
            $data['updated_at'] = $this->getCurrentDateTime();
            $data['status'] = $data['status'] ?? 'draft';
            $data['type'] = $data['type'] ?? 'post'; // post or video
            $data['views'] = 0;

            $result = $this->collection->insertOne($data);
            return $result->getInsertedId();
        } catch (\Exception $e) {
            error_log("Error creating blog post: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update blog post
     */
    public function update($id, $data)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            // Generate slug if title changed and slug is empty
            if (!empty($data['title']) && empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['title']);
            }
            
            $data['updated_at'] = $this->getCurrentDateTime();
            
            $result = $this->collection->updateOne(
                ['_id' => $id],
                ['$set' => $data]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error updating blog post: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete blog post
     */
    public function delete($id)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            $result = $this->collection->deleteOne(['_id' => $id]);
            return $result->getDeletedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error deleting blog post: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle blog post status
     */
    public function toggleStatus($id)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            $post = $this->getById($id);
            if (!$post) {
                return false;
            }
            
            $newStatus = $post['status'] === 'published' ? 'draft' : 'published';
            
            $result = $this->collection->updateOne(
                ['_id' => $id],
                ['$set' => ['status' => $newStatus, 'updated_at' => $this->getCurrentDateTime()]]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error toggling blog post status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment view count
     */
    public function incrementViews($id)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            $result = $this->collection->updateOne(
                ['_id' => $id],
                ['$inc' => ['views' => 1]]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error incrementing blog post views: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search blog posts
     */
    public function search($query, $limit = 10)
    {
        try {
            $filter = [
                '$or' => [
                    ['title' => ['$regex' => $query, '$options' => 'i']],
                    ['content' => ['$regex' => $query, '$options' => 'i']],
                    ['excerpt' => ['$regex' => $query, '$options' => 'i']]
                ],
                'status' => 'published'
            ];
            
            $cursor = $this->collection->find($filter, [
                'sort' => ['created_at' => -1],
                'limit' => $limit
            ]);
            
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error searching blog posts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get blog posts by category
     */
    public function getByCategory($category, $limit = 10)
    {
        try {
            $cursor = $this->collection->find(
                ['category' => $category, 'status' => 'published'],
                [
                    'sort' => ['created_at' => -1],
                    'limit' => $limit
                ]
            );
            
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching blog posts by category: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get blog posts by type (post or video)
     */
    public function getByType($type, $limit = 10)
    {
        try {
            $cursor = $this->collection->find(
                ['type' => $type, 'status' => 'published'],
                [
                    'sort' => ['created_at' => -1],
                    'limit' => $limit
                ]
            );
            
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching blog posts by type: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get blog post count
     */
    public function getCount($status = null)
    {
        try {
            $filter = [];
            if ($status) {
                $filter['status'] = $status;
            }
            
            return $this->collection->countDocuments($filter);
        } catch (\Exception $e) {
            error_log("Error counting blog posts: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get featured blog posts
     */
    public function getFeatured($limit = 10)
    {
        try {
            $cursor = $this->collection->find(
                ['featured' => true, 'status' => 'published'],
                [
                    'sort' => ['created_at' => -1],
                    'limit' => $limit
                ]
            );
            
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching featured blog posts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get categories
     */
    public function getCategories()
    {
        try {
            $categories = $this->collection->distinct('category', ['status' => 'published']);
            return array_filter($categories); // Remove empty categories
        } catch (\Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug($title)
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists($slug)
    {
        try {
            $count = $this->collection->countDocuments(['slug' => $slug]);
            return $count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate blog post data
     */
    public function validate($data)
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }

        if (empty($data['content'])) {
            $errors[] = 'Content is required';
        }

        if (!empty($data['type']) && !in_array($data['type'], ['post', 'video'])) {
            $errors[] = 'Type must be either "post" or "video"';
        }

        if (!empty($data['status']) && !in_array($data['status'], ['draft', 'published'])) {
            $errors[] = 'Status must be either "draft" or "published"';
        }

        return $errors;
    }

    /**
     * Get current datetime as UTCDateTime
     */
    private function getCurrentDateTime()
    {
        return new UTCDateTime();
    }
} 