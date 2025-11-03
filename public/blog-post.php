<?php
/**
 * Single Blog Post Detail Page
 * Displays individual blog post from MongoDB
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/cache.php';
require_once __DIR__ . '/../includes/mongodb.php';
require_once __DIR__ . '/../admin/controllers/UnifiedBlogController.php';

// Get post ID from URL
$postId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$postId) {
    header('Location: blog.php');
    exit();
}

// Get blog post data using UnifiedBlogController
try {
    $controller = new UnifiedBlogController();
    
    // Validate post ID format
    if (!preg_match('/^[a-f\d]{24}$/i', $postId)) {
        header('HTTP/1.0 404 Not Found');
        $error = 'Invalid post ID format';
        $postArray = null;
        $relatedPosts = [];
        $recentPosts = [];
        $categories = [];
    } else {
        // Get the specific post
        $post = $controller->getById($postId);
        
        if (!$post) {
            header('HTTP/1.0 404 Not Found');
            $error = 'Post not found';
            $postArray = null;
            $relatedPosts = [];
            $recentPosts = [];
            $categories = [];
        } elseif ($post['status'] !== 'published') {
            header('HTTP/1.0 404 Not Found');
            $error = 'Post not published';
            $postArray = null;
            $relatedPosts = [];
            $recentPosts = [];
            $categories = [];
        } else {
            $postArray = $post;
            
            // Get related posts (same category, excluding current post)
            $mongo = getMongoDB();
            $relatedFilter = [
                'status' => 'published',
                'category' => $postArray['category'],
                '_id' => ['$ne' => new MongoDB\BSON\ObjectId($postId)]
            ];
            $relatedOptions = ['sort' => ['published_at' => -1], 'limit' => 3];
            $relatedPosts = $mongo->find('blog_posts', $relatedFilter, $relatedOptions);
            
            // Get recent posts for sidebar
            $recentFilter = ['status' => 'published'];
            $recentOptions = ['sort' => ['published_at' => -1], 'limit' => 5];
            $recentPosts = $mongo->find('blog_posts', $recentFilter, $recentOptions);
            
            // Get categories for sidebar
            $categories = $controller->getCategories();
        }
    }
    
} catch (Exception $e) {
    error_log("Blog post error: " . $e->getMessage());
    $error = 'An error occurred while loading the post';
    $postArray = null;
    $relatedPosts = [];
    $recentPosts = [];
    $categories = [];
}

// Helper functions
function getCategories($mongo) {
    try {
        $command = [
            'distinct' => 'blog_posts',
            'key' => 'category',
            'query' => ['status' => 'published']
        ];
        $result = $mongo->executeCommand($command);
        return $result[0]->values ?? [];
    } catch (Exception $e) {
        return [];
    }
}

function formatDate($dateString) {
    if (!$dateString) return '';
    
    // Handle array input (from MongoDB)
    if (is_array($dateString)) {
        // If it's an array, try to extract a date value
        if (isset($dateString['date'])) {
            $dateString = $dateString['date'];
        } elseif (isset($dateString['$date'])) {
            $dateString = $dateString['$date'];
        } elseif (isset($dateString['timestamp'])) {
            $dateString = $dateString['timestamp'];
        } elseif (isset($dateString['sec'])) {
            // Handle MongoDB timestamp format
            $dateString = date('Y-m-d H:i:s', $dateString['sec']);
        } elseif (count($dateString) > 0) {
            // Try to get the first value if it's a simple array
            $firstValue = reset($dateString);
            if (is_string($firstValue) || is_numeric($firstValue)) {
                $dateString = $firstValue;
            } else {
                // If we can't find a valid date in the array, return empty
                return '';
            }
        } else {
            // If we can't find a date in the array, return empty
            return '';
        }
    }
    
    // Handle MongoDB BSON UTCDateTime objects
    if ($dateString instanceof MongoDB\BSON\UTCDateTime) {
        $dateString = $dateString->toDateTime()->format('Y-m-d H:i:s');
    }
    
    // If it's still not a string, handle it appropriately
    if (!is_string($dateString)) {
        if (is_array($dateString)) {
            // If it's still an array, try to convert to JSON or return empty
            return '';
        } elseif (is_numeric($dateString)) {
            // If it's a numeric timestamp, convert it
            $dateString = date('Y-m-d H:i:s', $dateString);
        } else {
            // For other types, try to convert to string
            $dateString = (string) $dateString;
        }
    }
    
    try {
        $date = new DateTime($dateString);
        return $date->format('d M Y');
    } catch (Exception $e) {
        // If date parsing fails, return the original value or empty string
        return is_string($dateString) ? $dateString : '';
    }
}

function formatDateTime($dateString) {
    if (!$dateString) return '';
    
    // Handle array input (from MongoDB)
    if (is_array($dateString)) {
        // If it's an array, try to extract a date value
        if (isset($dateString['date'])) {
            $dateString = $dateString['date'];
        } elseif (isset($dateString['$date'])) {
            $dateString = $dateString['$date'];
        } elseif (isset($dateString['timestamp'])) {
            $dateString = $dateString['timestamp'];
        } elseif (isset($dateString['sec'])) {
            // Handle MongoDB timestamp format
            $dateString = date('Y-m-d H:i:s', $dateString['sec']);
        } elseif (count($dateString) > 0) {
            // Try to get the first value if it's a simple array
            $firstValue = reset($dateString);
            if (is_string($firstValue) || is_numeric($firstValue)) {
                $dateString = $firstValue;
            } else {
                // If we can't find a valid date in the array, return empty
                return '';
            }
        } else {
            // If we can't find a date in the array, return empty
            return '';
        }
    }
    
    // Handle MongoDB BSON UTCDateTime objects
    if ($dateString instanceof MongoDB\BSON\UTCDateTime) {
        $dateString = $dateString->toDateTime()->format('Y-m-d H:i:s');
    }
    
    // If it's still not a string, handle it appropriately
    if (!is_string($dateString)) {
        if (is_array($dateString)) {
            // If it's still an array, try to convert to JSON or return empty
            return '';
        } elseif (is_numeric($dateString)) {
            // If it's a numeric timestamp, convert it
            $dateString = date('Y-m-d H:i:s', $dateString);
        } else {
            // For other types, try to convert to string
            $dateString = (string) $dateString;
        }
    }
    
    try {
        $date = new DateTime($dateString);
        return $date->format('F j, Y \a\t g:i A');
    } catch (Exception $e) {
        // If date parsing fails, return the original value or empty string
        return is_string($dateString) ? $dateString : '';
    }
}

function sanitizeContent($content) {
    // Allow safe HTML tags
    $allowedTags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre><div><span>';
    return strip_tags($content, $allowedTags);
}

function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <title><?php echo $postArray ? htmlspecialchars($postArray['title']) . ' - PHITSOL INC.' : 'Post Not Found - PHITSOL INC.'; ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="PHITSOL">
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <meta name="description" content="<?php echo $postArray ? htmlspecialchars(substr(strip_tags($postArray['excerpt'] ?? $postArray['content']), 0, 160)) : 'Blog post not found.'; ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $postArray ? htmlspecialchars($postArray['title']) : 'Post Not Found'; ?>">
    <meta property="og:description" content="<?php echo $postArray ? htmlspecialchars(substr(strip_tags($postArray['excerpt'] ?? $postArray['content']), 0, 160)) : 'Blog post not found.'; ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <?php if ($postArray && !empty($postArray['featured_image'])): ?>
    <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/phitsol/admin/public/uploads/blog/<?php echo htmlspecialchars($postArray['featured_image']); ?>">
    <?php endif; ?>
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $postArray ? htmlspecialchars($postArray['title']) : 'Post Not Found'; ?>">
    <meta name="twitter:description" content="<?php echo $postArray ? htmlspecialchars(substr(strip_tags($postArray['excerpt'] ?? $postArray['content']), 0, 160)) : 'Blog post not found.'; ?>">
    <?php if ($postArray && !empty($postArray['featured_image'])): ?>
    <meta name="twitter:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/phitsol/admin/public/uploads/blog/<?php echo htmlspecialchars($postArray['featured_image']); ?>">
    <?php endif; ?>
    
    <!-- favicon icon -->
    <link rel="shortcut icon" href="../images/favicon.png">
    <link rel="apple-touch-icon" href="../images/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="72x72" href="../images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="../images/apple-touch-icon-114x114.png">
    
    <!-- google fonts preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- style sheets and font icons  -->
    <link rel="stylesheet" href="../css/vendors.min.css"/>
    <link rel="stylesheet" href="../css/icon.min.css"/>
    <link rel="stylesheet" href="../css/style.css"/>
    <link rel="stylesheet" href="../css/responsive.css"/>
    <link rel="stylesheet" href="../demos/web-agency/web-agency.css" />
</head>
<body data-mobile-nav-style="classic" class="background-position-center-top" style="background-image: url(../images/vertical-line-bg-small-medium-gray.svg)"> 
    <!-- start header -->
    <header>
        <!-- start navigation -->
        <nav class="navbar navbar-expand-lg header-light bg-white disable-fixed">
            <div class="container-fluid">
                <div class="col-auto col-xl-3 col-lg-2 me-lg-0 me-auto">
                    <a class="navbar-brand" href="index.html">
                        <img src="../images/phitsol-logo-black@2x.png" data-at2x="../images/phitsol-logo-black@2x.png" alt="Phitsol" class="default-logo" style="height: 50px; max-width: 300px; object-fit: contain;" width="174" height="46">
                        <img src="../images/phitsol-logo-black@2x.png" data-at2x="../images/phitsol-logo-black@2x.png" alt="Phitsol" class="alt-logo" style="height: 50px; max-width: 300px; object-fit: contain;" width="174" height="46">
                        <img src="../images/phitsol-logo-black.png" data-at2x="../images/phitsol-logo-black@2x.png" alt="Phitsol" class="mobile-logo" style="height: 40px; max-width: 250px; object-fit: contain;">
                    </a>
                </div>
                <div class="col-auto col-xl-6 col-lg-8 menu-order position-static">
                    <button class="navbar-toggler float-start" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-label="Toggle navigation">
                        <span class="navbar-toggler-line"></span>
                        <span class="navbar-toggler-line"></span>
                        <span class="navbar-toggler-line"></span>
                        <span class="navbar-toggler-line"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
                            <li class="nav-item"><a href="about.html" class="nav-link">About</a></li>
                            <li class="nav-item"><a href="services-purchase.html" class="nav-link">Services</a></li>
                            <li class="nav-item"><a href="https://store.phitsol.com/" class="nav-link" target="_blank">Shop</a></li>
                            <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                            <li class="nav-item"><a href="contact.html" class="nav-link">Contact</a></li>
                            <li class="nav-item d-xl-none"><a href="login.php" class="nav-link">Partners Portal</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-auto col-xl-3 col-lg-2 text-end md-pe-0">
                    <div class="header-icon">
                        <div class="header-button ms-20px d-none d-xl-inline-block">
                            <a href="login.php" class="btn btn-rounded btn-transparent-light-gray border-1 btn-medium btn-switch-text text-transform-none">
                                <span>
                                    <span class="btn-double-text fw-600" data-text="Partners Portal">Partners Portal</span>
                                    <span><i class="fa-solid fa-handshake"></i></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <!-- end navigation -->
    </header>
    <!-- end header -->

    <?php if (isset($error) || !$postArray): ?>
        <!-- start page title -->
        <section class="p-0 sm-pb-40px top-space-margin page-title-center-alignment">
            <div class="container">
                <div class="row align-items-center justify-content-center small-screen sm-h-auto">
                    <div class="col-lg-10 text-center">
                        <span class="fs-18 mb-30px d-inline-block sm-mb-20px">Error</span>
                        <h1 class="alt-font fw-600 text-dark-gray ls-minus-2px mb-0">Post Not Found</h1>
                    </div>
                </div>
            </div>
        </section>
        <!-- end page title -->

        <!-- start section -->
        <section class="p-0">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center">
                        <div class="py-5">
                            <i class="fas fa-exclamation-triangle fs-48 text-muted mb-3"></i>
                            <h4 class="text-muted">Post Not Found</h4>
                            <p class="text-muted">The blog post you're looking for doesn't exist or has been removed.</p>
                            <a href="blog.php" class="btn btn-dark-gray btn-round-edge">Back to Blog</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end section -->
    <?php else: ?>
        <!-- start page title -->
        <section class="p-0 sm-pb-40px top-space-margin page-title-center-alignment">
            <div class="container">
                <div class="row align-items-center justify-content-center small-screen sm-h-auto">
                    <div class="col-lg-10 text-center">
                        <span class="fs-18 mb-30px d-inline-block sm-mb-20px">
                            By <a href="blog.php?author=<?php echo urlencode($postArray['author']); ?>" class="text-dark-gray text-dark-gray-hover text-decoration-line-bottom"><?php echo htmlspecialchars($postArray['author']); ?></a> 
                            in <a href="blog.php?category=<?php echo urlencode($postArray['category']); ?>" class="text-dark-gray text-dark-gray-hover text-decoration-line-bottom"><?php echo htmlspecialchars($postArray['category']); ?></a> 
                            on <?php echo formatDate($postArray['published_at']); ?>
                        </span>
                        <h1 class="alt-font fw-600 text-dark-gray ls-minus-2px mb-0"><?php echo htmlspecialchars($postArray['title']); ?></h1>
                    </div>
                </div>
            </div>
        </section>
        <!-- end page title -->

        <!-- start section -->
        <section class="p-0">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- start blog post content -->
                        <div class="blog-post-content">
                            <?php if (!empty($postArray['featured_image'])): ?>
                                <div class="blog-featured-image mb-40px">
                                    <img src="../admin/public/uploads/blog/<?php echo htmlspecialchars($postArray['featured_image']); ?>" alt="<?php echo htmlspecialchars($postArray['title']); ?>" class="w-100 border-radius-4px">
                                </div>
                            <?php endif; ?>
                            
                            <div class="blog-content">
                                <?php echo sanitizeContent($postArray['content']); ?>
                            </div>
                            
                            <!-- start tags and social sharing -->
                            <div class="row mb-30px">
                                <div class="tag-cloud col-md-9 text-center text-md-start sm-mb-15px">
                                    <?php if (!empty($postArray['tags'])): ?>
                                        <?php foreach ($postArray['tags'] as $tag): ?>
                                            <a href="blog.php?tag=<?php echo urlencode($tag); ?>"><?php echo htmlspecialchars($tag); ?></a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div> 
                                <div class="tag-cloud col-md-3 text-uppercase text-center text-md-end">
                                    <a class="likes-count fw-500 mx-0" href="#" onclick="shareOnSocial('facebook')">
                                        <i class="fab fa-facebook-f text-blue me-10px"></i>
                                        <span class="text-dark-gray text-dark-gray-hover">Share</span>
                                    </a>
                                </div>
                            </div>
                            <!-- end tags and social sharing -->
                            
                            <!-- start author bio -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-block d-md-flex w-100 box-shadow-extra-large align-items-center bg-white border-radius-4px p-7 sm-p-35px">
                                        <div class="w-140px text-center me-50px sm-mx-auto">
                                            <img src="https://placehold.co/125x125/667eea/ffffff?text=<?php echo urlencode(substr($postArray['author'], 0, 1)); ?>" class="rounded-circle w-120px" alt="<?php echo htmlspecialchars($postArray['author']); ?>">
                                            <a href="blog.php?author=<?php echo urlencode($postArray['author']); ?>" class="text-dark-gray text-dark-gray-hover fw-500 mt-20px d-inline-block lh-20"><?php echo htmlspecialchars($postArray['author']); ?></a>
                                            <span class="fs-15 lh-20 d-block sm-mb-15px">Author</span>
                                        </div>
                                        <div class="w-75 sm-w-100 text-center text-md-start last-paragraph-no-margin">
                                            <p>Published on <?php echo formatDateTime($postArray['published_at']); ?>. This post was written by <?php echo htmlspecialchars($postArray['author']); ?>.</p>
                                            <a href="blog.php?author=<?php echo urlencode($postArray['author']); ?>" class="btn btn-link btn-large text-dark-gray mt-15px">All author posts</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end author bio -->
                        </div>
                        <!-- end blog post content -->
                    </div>
                </div>
            </div>
        </section>
        <!-- end section -->

        <!-- start related posts section -->
        <?php if (!empty($relatedPosts)): ?>
            <section class="p-0">
                <div class="container">
                    <div class="row justify-content-center mb-1">
                        <div class="col-lg-7 text-center">
                            <span class="alt-font fw-500 text-uppercase d-inline-block">You may also like</span>
                            <h5 class="alt-font text-dark-gray fw-500">Related posts</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <ul class="blog-classic blog-wrapper grid grid-4col xl-grid-4col lg-grid-3col md-grid-2col sm-grid-2col xs-grid-1col gutter-extra-large appear anime-child anime-complete" data-anime="{ &quot;el&quot;: &quot;childs&quot;, &quot;translateY&quot;: [50, 0], &quot;opacity&quot;: [0,1], &quot;duration&quot;: 600, &quot;delay&quot;:100, &quot;staggervalue&quot;: 150, &quot;easing&quot;: &quot;easeOutQuad&quot; }">
                                <li class="grid-sizer"></li>
                                
                                <?php foreach ($relatedPosts as $relatedPost): ?>
                                    <?php $relatedArray = $controller->mongoToArray($relatedPost); ?>
                                    <!-- start blog item -->
                                    <li class="grid-item">
                                        <div class="card bg-transparent border-0 h-100">
                                            <div class="blog-image position-relative overflow-hidden border-radius-4px">
                                                <a href="blog-post.php?id=<?php echo htmlspecialchars($relatedArray['_id']); ?>">
                                                    <?php if (!empty($relatedArray['featured_image'])): ?>
                                                        <img src="../admin/public/uploads/blog/<?php echo htmlspecialchars($relatedArray['featured_image']); ?>" alt="<?php echo htmlspecialchars($relatedArray['title']); ?>">
                                                    <?php else: ?>
                                                        <img src="https://placehold.co/550x395/667eea/ffffff?text=<?php echo urlencode($relatedArray['title']); ?>" alt="<?php echo htmlspecialchars($relatedArray['title']); ?>">
                                                    <?php endif; ?>
                                                </a>
                                            </div>
                                            <div class="card-body px-0 pt-30px pb-30px xs-pb-15px">
                                                <span class="fs-14 text-uppercase d-block mb-5px fw-500">
                                                    <a href="blog.php?category=<?php echo urlencode($relatedArray['category']); ?>" class="text-dark-gray text-dark-gray-hover fw-700 categories-text"><?php echo htmlspecialchars($relatedArray['category']); ?></a>
                                                    <a href="#" class="blog-date text-medium-gray-hover"><?php echo formatDate($relatedArray['published_at']); ?></a>
                                                </span>
                                                <a href="blog-post.php?id=<?php echo htmlspecialchars($relatedArray['_id']); ?>" class="card-title fw-600 fs-17 lh-28 text-dark-gray text-dark-gray-hover d-inline-block w-95 sm-w-100">
                                                    <?php echo htmlspecialchars($relatedArray['title']); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                    <!-- end blog item -->
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        <!-- end related posts section -->
    <?php endif; ?>

    <!-- start footer - Updated 2024 -->
    <footer class="p-0 fs-16 border-top border-color-extra-medium-gray">
        <div class="container"> 
            <div class="row justify-content-center pt-6 sm-pt-40px">
                <!-- start footer column -->
                <div class="col-6 col-xl-3 col-lg-12 col-sm-6 last-paragraph-no-margin text-xl-start text-lg-center order-sm-1 lg-mb-50px sm-mb-30px">
                    <a href="/" class="footer-logo mb-15px d-inline-block"><img src="../images/phitsol-logo-black.png" data-at2x="../images/phitsol-logo-black@2x.png" alt="Phitsol" style="height: 40px; max-width: 200px; object-fit: contain;"></a>
                    <p class="lh-30 w-90 xl-w-100 mx-lg-auto mx-xl-0">Leading your business forward through innovation.</p>
                    <div class="elements-social social-icon-style-02 mt-20px xs-mt-15px">
                        <ul class="medium-icon dark">
                            <li class="my-0"><a class="facebook" href="https://www.facebook.com/phitsol.inc" target="_blank"><i class="fa-brands fa-facebook-f"></i></a></li>
                            <li class="my-0"><a class="youtube" href="https://www.youtube.com/@phitsol" target="_blank"><i class="fa-brands fa-youtube"></i></a></li> 
                            <li class="my-0"><a class="linkedin" href="https://www.linkedin.com/in/hoibin-yoon-273275213" target="_blank"><i class="fa-brands fa-linkedin"></i></a></li> 
                            <li class="my-0"><a class="instagram" href="http://www.instagram.com" target="_blank"><i class="fa-brands fa-instagram"></i></a></li> 
                        </ul>
                    </div>
                </div>
                <!-- end footer column -->
                <!-- start footer column -->
                <div class="col-6 col-xl-2 col-lg-3 col-sm-4 xs-mb-30px order-sm-3 order-lg-2">
                    <span class="fs-17 fw-600 d-block text-dark-gray mb-5px">Company</span>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="services-purchase.html">Services</a></li>
                        <li><a href="https://store.phitsol.com/" target="_blank">Shop</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>
                <!-- end footer column -->
                <!-- start footer column -->
                <div class="col-6 col-xl-2 col-lg-3 col-sm-4 xs-mb-30px order-sm-4 order-lg-3">
                    <span class="fs-17 fw-600 d-block text-dark-gray mb-5px">Services</span>
                    <ul>
                        <li><a href="services-purchase.html">IT Equipment Purchase</a></li>
                        <li><a href="services-rental.html">Equipment Rental</a></li>
                        <li><a href="services-disposal.html">Disposal Services</a></li>
                        <li><a href="services-maintenance.html">Maintenance & Repair</a></li>
                        <li><a href="services-technical.html">Technical Support</a></li>
                    </ul>
                </div>
                <!-- end footer column -->
                <!-- start footer column -->
                <div class="col-6 col-xl-2 col-lg-3 col-sm-4 xs-mb-30px order-sm-5 order-lg-4">
                    <span class="fs-17 fw-600 d-block text-dark-gray mb-5px">Customer</span>
                    <ul>
                        <li><a href="contact.html">Contact Us</a></li>
                        <li><a href="services-partners.html">Partners Portal</a></li>
                    </ul>
                </div>
                <!-- end footer column -->
                <!-- start footer column -->
                <div class="col-xl-3 col-lg-3 col-sm-6 md-mb-50px sm-mb-30px xs-mb-0 order-sm-2 order-lg-5">
                    <span class="fs-17 fw-600 d-block text-dark-gray mb-5px">Subscribe newsletter</span>
                    <p class="lh-30 w-95 sm-w-100 mb-15px">Subscribe our newsletter to get the latest news and updates!</p>
                    <div class="d-inline-block w-100 newsletter-style-02 position-relative"> 
                        <form action="email-templates/subscribe-newsletter.php" method="post" class="position-relative">
                            <input class="border-color-extra-medium-gray bg-transparent border-radius-4px w-100 form-control input-small pe-50px required" type="email" name="email" placeholder="Enter your email" />
                            <input type="hidden" name="redirect" value="">
                            <button class="btn pe-20px submit lh-16 newsletter-submit-btn" aria-label="submit"><i class="feather icon-feather-mail icon-small text-dark-gray"></i></button>
                            
                            <!-- Success Message -->
                            <div class="newsletter-status newsletter-success d-none">
                                <i class="fa-solid fa-check-circle icon-small text-success me-5px"></i>
                                Subscription successful!
                            </div>
                            
                            <!-- Success Message -->
                            <div class="newsletter-status newsletter-error d-none">
                                <i class="fa-solid fa-exclamation-circle icon-small text-danger me-5px"></i>
                                There was a problem. Please try again.
                            </div>
                            
                            <div class="form-results border-radius-4px pt-5px pb-5px ps-15px pe-15px fs-14 lh-22 mt-10px w-100 text-center position-absolute d-none"></div>
                        </form>
                    </div>
                </div>
                <!-- end footer column -->                      
            </div>
            <div class="row justify-content-center align-items-center pt-2">
                <!-- start divider -->
                <div class="col-12">
                    <div class="divider-style-03 divider-style-03-01 border-color-transparent-white-light"></div>
                </div>
                <!-- end divider -->
                <!-- start copyright -->
                <div class="col-lg-5 pt-35px pb-35px md-pt-0 order-2 order-lg-1 text-center text-lg-start last-paragraph-no-margin"><p>&copy; Copyright @ 2024. PHITSOL INC. Philippine I.T. Solution All Rights Reserved</p></div>
                <!-- end copyright -->
                <!-- start footer menu -->
                <div class="col-lg-7 pt-35px pb-35px md-pt-25px md-pb-5px order-1 order-lg-2 text-center text-lg-end">
                    <ul class="footer-navbar sm-lh-normal"> 
                        <li><a href="privacy-policy.html" class="nav-link">Privacy policy</a></li>
                        <li><a href="terms-and-conditions.html" class="nav-link">Terms and conditions</a></li>
                        <li><a href="copyright.html" class="nav-link">Copyright</a></li>
                    </ul>
                </div>
                <!-- end footer menu -->
            </div>
        </div> 
    </footer>
    <!-- end footer -->

    <!-- start scroll to top -->
    <a class="scroll-top-arrow" href="javascript:void(0);"><i class="feather icon-feather-arrow-up"></i></a>
    <!-- end scroll to top -->

    <!-- javascript -->
    <script type="text/javascript" src="../js/jquery.min.js"></script>
    <script type="text/javascript" src="../js/vendors.min.js"></script>
    <script type="text/javascript" src="../js/main.js"></script>
    
    <script>
    function shareOnSocial(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        
        let shareUrl = '';
        switch(platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'linkedin':
                shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                break;
        }
        
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }
    </script>
</body>
</html>
