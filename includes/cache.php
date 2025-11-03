<?php
/**
 * Simple File-based Caching System
 * Provides caching functionality for blog data to improve performance
 */

class BlogCache {
    private $cacheDir;
    private $defaultTTL = 3600; // 1 hour default TTL
    
    public function __construct($cacheDir = null) {
        $this->cacheDir = $cacheDir ?: __DIR__ . '/../cache/blog';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cached data
     */
    public function get($key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = file_get_contents($filename);
        $cacheData = json_decode($data, true);
        
        if (!$cacheData) {
            return null;
        }
        
        // Check if cache has expired
        if (time() > $cacheData['expires']) {
            $this->delete($key);
            return null;
        }
        
        return $cacheData['data'];
    }
    
    /**
     * Set cached data
     */
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?: $this->defaultTTL;
        $filename = $this->getFilename($key);
        
        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($filename, json_encode($cacheData)) !== false;
    }
    
    /**
     * Delete cached data
     */
    public function delete($key) {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear() {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    /**
     * Clear expired cache
     */
    public function clearExpired() {
        $files = glob($this->cacheDir . '/*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            $data = file_get_contents($file);
            $cacheData = json_decode($data, true);
            
            if ($cacheData && time() > $cacheData['expires']) {
                unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        $files = glob($this->cacheDir . '/*.cache');
        $totalFiles = count($files);
        $totalSize = 0;
        $expiredFiles = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = file_get_contents($file);
            $cacheData = json_decode($data, true);
            
            if ($cacheData && time() > $cacheData['expires']) {
                $expiredFiles++;
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'expired_files' => $expiredFiles,
            'active_files' => $totalFiles - $expiredFiles
        ];
    }
    
    /**
     * Get cache directory
     */
    public function getCacheDir() {
        return $this->cacheDir;
    }
    
    /**
     * Generate cache filename
     */
    private function getFilename($key) {
        $hash = md5($key);
        return $this->cacheDir . '/' . $hash . '.cache';
    }
}

/**
 * Blog-specific caching functions
 */
class BlogCacheManager {
    private $cache;
    
    public function __construct() {
        $this->cache = new BlogCache();
    }
    
    /**
     * Get cached blog posts
     */
    public function getPosts($page = 1, $limit = 10, $category = null, $tag = null) {
        $key = "posts_{$page}_{$limit}_" . ($category ?: 'all') . "_" . ($tag ?: 'all');
        return $this->cache->get($key);
    }
    
    /**
     * Cache blog posts
     */
    public function setPosts($data, $page = 1, $limit = 10, $category = null, $tag = null) {
        $key = "posts_{$page}_{$limit}_" . ($category ?: 'all') . "_" . ($tag ?: 'all');
        return $this->cache->set($key, $data, 1800); // 30 minutes TTL
    }
    
    /**
     * Get cached single post
     */
    public function getPost($postId) {
        $key = "post_{$postId}";
        return $this->cache->get($key);
    }
    
    /**
     * Cache single post
     */
    public function setPost($postId, $data) {
        $key = "post_{$postId}";
        return $this->cache->set($key, $data, 3600); // 1 hour TTL
    }
    
    /**
     * Get cached categories
     */
    public function getCategories() {
        return $this->cache->get('categories');
    }
    
    /**
     * Cache categories
     */
    public function setCategories($data) {
        return $this->cache->set('categories', $data, 7200); // 2 hours TTL
    }
    
    /**
     * Get cached tags
     */
    public function getTags() {
        return $this->cache->get('tags');
    }
    
    /**
     * Cache tags
     */
    public function setTags($data) {
        return $this->cache->set('tags', $data, 7200); // 2 hours TTL
    }
    
    /**
     * Get cached related posts
     */
    public function getRelatedPosts($postId, $category) {
        $key = "related_{$postId}_{$category}";
        return $this->cache->get($key);
    }
    
    /**
     * Cache related posts
     */
    public function setRelatedPosts($postId, $category, $data) {
        $key = "related_{$postId}_{$category}";
        return $this->cache->set($key, $data, 1800); // 30 minutes TTL
    }
    
    /**
     * Invalidate post cache when post is updated
     */
    public function invalidatePost($postId) {
        $this->cache->delete("post_{$postId}");
        
        // Clear related posts cache
        $cacheDir = $this->cache->getCacheDir();
        $files = glob($cacheDir . '/related_*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        
        // Clear posts listing cache
        $files = glob($cacheDir . '/posts_*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * Invalidate all blog cache
     */
    public function invalidateAll() {
        $this->cache->clear();
    }
}

// Global cache manager instance
function getBlogCache() {
    static $cacheManager = null;
    if ($cacheManager === null) {
        $cacheManager = new BlogCacheManager();
    }
    return $cacheManager;
}
?>
