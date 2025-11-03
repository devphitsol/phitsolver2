<?php
/**
 * Dynamic Blog Listing Page
 * Displays published blog posts from MongoDB
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/cache.php';
require_once __DIR__ . '/../includes/mongodb.php';
require_once __DIR__ . '/../admin/controllers/UnifiedBlogController.php';

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 9; // 9 posts per page for grid layout
$category = isset($_GET['category']) ? $_GET['category'] : null;
$tag = isset($_GET['tag']) ? $_GET['tag'] : null;

// Get blog data using UnifiedBlogController
try {
    $controller = new UnifiedBlogController();
    
    // Get published posts with pagination
    $data = $controller->index($page, $limit, 'published');
    $posts = $data['posts'];
    $totalPosts = $data['totalPosts'];
    $totalPages = $data['totalPages'];
    
    // Filter by category if specified
    if ($category) {
        $posts = array_filter($posts, function($post) use ($category) {
            return $post['category'] === $category;
        });
    }
    
    // Filter by tag if specified
    if ($tag) {
        $posts = array_filter($posts, function($post) use ($tag) {
            return in_array($tag, $post['tags'] ?? []);
        });
    }
    
    // Get categories and tags
    $categories = $controller->getCategories();
    $tags = getTags(getMongoDB());
    
} catch (Exception $e) {
    $posts = [];
    $totalPosts = 0;
    $totalPages = 1;
    $categories = [];
    $tags = [];
    $error = $e->getMessage();
    
    // Log the error for debugging
    error_log("Blog system error: " . $e->getMessage());
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

function getTags($mongo) {
    try {
        $command = [
            'distinct' => 'blog_posts',
            'key' => 'tags',
            'query' => ['status' => 'published']
        ];
        $result = $mongo->executeCommand($command);
        $tags = $result[0]->values ?? [];
        
        // Flatten and deduplicate
        $flatTags = [];
        foreach ($tags as $tagArray) {
            if (is_array($tagArray)) {
                $flatTags = array_merge($flatTags, $tagArray);
            } else {
                $flatTags[] = $tagArray;
            }
        }
        return array_unique($flatTags);
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

function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function sanitizeContent($content) {
    // Allow safe HTML tags
    $allowedTags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre>';
    return strip_tags($content, $allowedTags);
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <title>PHITSOL INC. - Blog</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="PHITSOL">
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <meta name="description" content="Read the latest insights, tips, and updates from PHITSOL INC. - Your trusted partner in technology solutions.">
    <meta name="keywords" content="PHITSOL, blog, technology, business, insights, updates, news">
    <meta name="author" content="PHITSOL INC.">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="PHITSOL INC. - Blog">
    <meta property="og:description" content="Read the latest insights, tips, and updates from PHITSOL INC.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:site_name" content="PHITSOL INC.">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PHITSOL INC. - Blog">
    <meta name="twitter:description" content="Read the latest insights, tips, and updates from PHITSOL INC.">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
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
</head>
<body data-mobile-nav-style="classic"> 
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
                                <div class="header-search-icon icon">
                                    <a href="services-partners.html" class="search-form-icon header-search-form"><i class="feather icon-feather-search"></i></a>
                                    <!-- start search input -->
                                    <div class="search-form-wrapper">
                                        <button title="Close" type="button" class="search-close">×</button>
                                        <form id="search-form" role="search" method="get" class="search-form text-left" action="search-result.html">
                                            <div class="search-form-box">
                                                <h2 class="text-dark-gray text-center fw-600 mb-4 ls-minus-1px">What are you looking for?</h2>
                                                <input class="search-input" id="search-form-input5e219ef164995" placeholder="Enter your keywords..." name="s" value="" type="text" autocomplete="off">
                                                <button type="submit" class="search-button">
                                                    <i class="feather icon-feather-search" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- end search input -->
                                </div>
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

        <!-- start page title -->
        <section class="pb-0 ipad-top-space-margin md-pt-0">
            <div class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-12 col-xl-6 col-lg-8 text-center position-relative page-title-double-large">
                        <div class="d-flex flex-column justify-content-center extra-very-small-screen">
                            <h1 class="text-dark-gray alt-font ls-minus-1px fw-700">Blog</h1>
                            <h2 class="text-dark-gray d-inline-block fw-400 ls-0px w-80 xs-w-100 mx-auto">Phitsol delivers smart and cost-effective IT solutions in the Philippines?�covering IT equipment rental, purchase, maintenance, and disposal?�helping businesses stay efficient and future-ready.</h2> 
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end page title -->

        <!-- start section -->
        <section class="pt-0 ps-2 pe-2 xs-px-0">
            <div class="container-fluid">                
                <div class="row blog-metro">
                    <div class="col-12">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-warning" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                                    <div>
                                        <h5 class="alert-heading mb-2">Blog Temporarily Unavailable</h5>
                                        <p class="mb-2">We're experiencing technical difficulties loading our blog posts.</p>
                                        <p class="mb-0">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Please try refreshing the page or contact support if the issue persists.
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php elseif (empty($posts)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-blog fs-48 text-muted mb-3"></i>
                                <h4 class="text-muted">No blog posts available</h4>
                                <p class="text-muted">Check back soon for new content!</p>
                            </div>
                        <?php else: ?>
                            <ul class="blog-metro blog-wrapper grid grid-4col xl-grid-4col lg-grid-3col md-grid-2col sm-grid-2col xs-grid-1col gutter-large">
                                <li class="grid-sizer"></li>
                                
                                <?php 
                                $postIndex = 0;
                                foreach ($posts as $post): 
                                    $postArray = $controller->mongoToArray($post);
                                    $isDouble = ($postIndex % 4 == 0 || $postIndex % 4 == 3); // Make every 1st and 4th item double
                                ?>
                                    <!-- start blog item -->
                                    <li class="grid-item <?php echo $isDouble ? 'grid-item-double' : ''; ?>">
                                        <figure class="position-relative mb-0 overflow-hidden">
                                            <div class="blog-image bg-dark-slate-blue">
                                                <a href="blog-post.php?id=<?php echo htmlspecialchars($postArray['_id']); ?>">
                                                    <?php if (!empty($postArray['featured_image'])): ?>
                                                        <img src="../admin/public/uploads/blog/<?php echo htmlspecialchars($postArray['featured_image']); ?>" alt="<?php echo htmlspecialchars($postArray['title']); ?>" />
                                                    <?php else: ?>
                                                        <img src="https://picsum.photos/1000/1000?random=<?php echo $postIndex + 1; ?>" alt="<?php echo htmlspecialchars($postArray['title']); ?>" />
                                                    <?php endif; ?>
                                                </a>
                                                <div class="blog-overlay"></div>
                                            </div>
                                            <figcaption class="d-flex flex-column justify-content-end h-100 <?php echo $isDouble ? 'ps-7 pe-7 pt-6 pb-6' : 'ps-14 pe-14 pt-12 pb-12 sm-ps-7 sm-pe-7 sm-pt-6 sm-pb-6'; ?>">
                                                <div class="blog-categories mb-auto">
                                                    <a href="blog.php?category=<?php echo urlencode($postArray['category']); ?>" class="categories-btn bg-white text-dark-gray text-uppercase alt-font fw-700 ms-0 mb-auto align-self-start"><?php echo htmlspecialchars($postArray['category']); ?></a>
                                                </div>
                                                <a href="blog-post.php?id=<?php echo htmlspecialchars($postArray['_id']); ?>" class="fs-13 alt-font mb-5px text-white opacity-6 text-uppercase"><?php echo formatDate($postArray['published_at']); ?></a>
                                                <a href="blog-post.php?id=<?php echo htmlspecialchars($postArray['_id']); ?>" class="text-white card-title fs-20 lh-30 fw-500 <?php echo $isDouble ? 'alt-font' : 'w-85 sm-w-100 alt-font'; ?>"><?php echo htmlspecialchars($postArray['title']); ?></a>
                                            </figcaption>
                                        </figure>
                                    </li>
                                    <!-- end blog item -->
                                <?php 
                                $postIndex++;
                                endforeach; 
                                ?>
                            </ul>
                            
                            <!-- start pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div class="row mt-5">
                                    <div class="col-12 text-center">
                                        <nav aria-label="Blog pagination">
                                            <ul class="pagination justify-content-center">
                                                <?php if ($page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $tag ? '&tag=' . urlencode($tag) : ''; ?>">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $tag ? '&tag=' . urlencode($tag) : ''; ?>">
                                                            <?php echo $i; ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <?php if ($page < $totalPages): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $tag ? '&tag=' . urlencode($tag) : ''; ?>">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <!-- end pagination -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <!-- end section -->

        <!-- start footer - Updated 2024 -->
        <footer class="p-0 fs-16 border-top border-color-extra-medium-gray">
            <div class="container"> 
                <div class="row justify-content-center pt-6 sm-pt-40px">
                    <!-- start footer column -->
                    <div class="col-6 col-xl-3 col-lg-12 col-sm-6 last-paragraph-no-margin text-xl-start text-lg-center order-sm-1 lg-mb-50px sm-mb-30px">
                        <a href="index.html" class="footer-logo mb-15px d-inline-block"><img src="../images/phitsol-logo-black.png" data-at2x="../images/phitsol-logo-black@2x.png" alt="Phitsol" style="height: 40px; max-width: 200px; object-fit: contain;"></a>
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
                                
                                <!-- ?�공 메시지 -->
                                <div class="newsletter-status newsletter-success d-none">
                                    <i class="fa-solid fa-check-circle icon-small text-success me-5px"></i>
                                    Subscription successful!
                                </div>
                                
                                <!-- ?�패 메시지 -->
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

        <!-- javascript libraries -->
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/vendors.min.js"></script>
    <script type="text/javascript" src="../js/main.js"></script>
</body>
</html>
