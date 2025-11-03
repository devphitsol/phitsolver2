<?php
/**
 * Blog API Endpoints
 * Provides JSON API for blog data to be consumed by frontend
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Get request method and parameters
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Remove 'api' from path parts
if ($pathParts[0] === 'api') {
    array_shift($pathParts);
}

// Remove 'blog' from path parts
if ($pathParts[0] === 'blog') {
    array_shift($pathParts);
}

try {
    $mongo = getMongoDB();
    
    switch ($method) {
        case 'GET':
            if (empty($pathParts)) {
                // GET /api/blog - Get all published posts
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $category = isset($_GET['category']) ? $_GET['category'] : null;
                $tag = isset($_GET['tag']) ? $_GET['tag'] : null;
                
                $posts = getPublishedPosts($mongo, $page, $limit, $category, $tag);
                echo json_encode($posts);
                
            } elseif ($pathParts[0] === 'post' && isset($pathParts[1])) {
                // GET /api/blog/post/{id} - Get single post
                $postId = $pathParts[1];
                $post = getPostById($mongo, $postId);
                
                if ($post) {
                    echo json_encode($post);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Post not found']);
                }
                
            } elseif ($pathParts[0] === 'categories') {
                // GET /api/blog/categories - Get all categories
                $categories = getCategories($mongo);
                echo json_encode($categories);
                
            } elseif ($pathParts[0] === 'tags') {
                // GET /api/blog/tags - Get all tags
                $tags = getTags($mongo);
                echo json_encode($tags);
                
            } elseif ($pathParts[0] === 'featured') {
                // GET /api/blog/featured - Get featured posts
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;
                $posts = getFeaturedPosts($mongo, $limit);
                echo json_encode($posts);
                
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

/**
 * Get published blog posts with pagination and filtering
 */
function getPublishedPosts($mongo, $page = 1, $limit = 10, $category = null, $tag = null) {
    try {
        $filter = ['status' => 'published'];
        
        if ($category) {
            $filter['category'] = $category;
        }
        
        if ($tag) {
            $filter['tags'] = ['$in' => [$tag]];
        }
        
        // Count total posts
        $totalPosts = $mongo->count('blog_posts', $filter);
        
        // Get posts with pagination
        $options = [
            'sort' => ['published_at' => -1],
            'skip' => ($page - 1) * $limit,
            'limit' => $limit
        ];
        
        $posts = $mongo->find('blog_posts', $filter, $options);
        
        // Format posts for API response
        $formattedPosts = [];
        foreach ($posts as $post) {
            $formattedPosts[] = formatPostForAPI($post);
        }
        
        return [
            'success' => true,
            'data' => $formattedPosts,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalPosts / $limit),
                'total_posts' => $totalPosts,
                'per_page' => $limit,
                'has_next' => $page < ceil($totalPosts / $limit),
                'has_prev' => $page > 1
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'data' => []
        ];
    }
}

/**
 * Get single blog post by ID
 */
function getPostById($mongo, $postId) {
    try {
        $filter = ['_id' => new MongoDB\BSON\ObjectId($postId), 'status' => 'published'];
        $post = $mongo->findOne('blog_posts', $filter);
        
        if ($post) {
            return [
                'success' => true,
                'data' => formatPostForAPI($post)
            ];
        }
        
        return null;
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get all categories
 */
function getCategories($mongo) {
    try {
        $command = [
            'distinct' => 'blog_posts',
            'key' => 'category',
            'query' => ['status' => 'published']
        ];
        
        $result = $mongo->executeCommand($command);
        $categories = $result[0]->values ?? [];
        
        return [
            'success' => true,
            'data' => $categories
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'data' => []
        ];
    }
}

/**
 * Get all tags
 */
function getTags($mongo) {
    try {
        $command = [
            'distinct' => 'blog_posts',
            'key' => 'tags',
            'query' => ['status' => 'published']
        ];
        
        $result = $mongo->executeCommand($command);
        $tags = $result[0]->values ?? [];
        
        // Flatten nested arrays and remove duplicates
        $flatTags = [];
        foreach ($tags as $tagArray) {
            if (is_array($tagArray)) {
                $flatTags = array_merge($flatTags, $tagArray);
            } else {
                $flatTags[] = $tagArray;
            }
        }
        
        $flatTags = array_unique($flatTags);
        sort($flatTags);
        
        return [
            'success' => true,
            'data' => array_values($flatTags)
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'data' => []
        ];
    }
}

/**
 * Get featured posts
 */
function getFeaturedPosts($mongo, $limit = 3) {
    try {
        $filter = ['status' => 'published'];
        $options = [
            'sort' => ['published_at' => -1],
            'limit' => $limit
        ];
        
        $posts = $mongo->find('blog_posts', $filter, $options);
        
        $formattedPosts = [];
        foreach ($posts as $post) {
            $formattedPosts[] = formatPostForAPI($post);
        }
        
        return [
            'success' => true,
            'data' => $formattedPosts
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'data' => []
        ];
    }
}

/**
 * Format post data for API response
 */
function formatPostForAPI($post) {
    $postArray = mongoToArray($post);
    
    return [
        'id' => $postArray['_id'],
        'title' => $postArray['title'],
        'content' => $postArray['content'],
        'excerpt' => $postArray['excerpt'] ?? substr(strip_tags($postArray['content']), 0, 200),
        'author' => $postArray['author'] ?? 'Admin',
        'category' => $postArray['category'] ?? 'Uncategorized',
        'tags' => $postArray['tags'] ?? [],
        'featured_image' => $postArray['featured_image'] ?? null,
        'status' => $postArray['status'],
        'created_at' => $postArray['created_at'],
        'updated_at' => $postArray['updated_at'],
        'published_at' => $postArray['published_at'],
        'url' => '/blog-post.php?id=' . $postArray['_id'],
        'slug' => generateSlug($postArray['title'])
    ];
}

/**
 * Generate URL slug from title
 */
function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}
?>
