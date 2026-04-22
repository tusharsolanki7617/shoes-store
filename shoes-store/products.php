<?php
/**
 * Products Listing Page
 * With filtering, sorting, and pagination
 */

require_once 'config/config.php';
$page_title = 'Shop All Products - Kicks & Comfort';
require_once 'includes/header.php';

// Get filter parameters
$category_filter = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 100000;
$sort = $_GET['sort'] ?? 'newest';
$search = $_GET['q'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_active = 1";
$params = [];

// Apply filters
if ($category_filter) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " AND (p.price BETWEEN ? AND ? OR p.discount_price BETWEEN ? AND ?)";
$params[] = $min_price;
$params[] = $max_price;
$params[] = $min_price;
$params[] = $max_price;

// Apply sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY COALESCE(p.discount_price, p.price) ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY COALESCE(p.discount_price, p.price) DESC";
        break;
    case 'name':
        $query .= " ORDER BY p.name ASC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
}

// Get products
try {
    $db = new Database();
    
    // Get total count
    $count_query = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*) as total", $query);
    $count_query = preg_replace('/ORDER BY.*$/', '', $count_query);
    $total_products = $db->fetchOne($count_query, $params)['total'];
    
    // Pagination
    $per_page = PRODUCTS_PER_PAGE;
    $total_pages = ceil($total_products / $per_page);
    $offset = ($page - 1) * $per_page;
    
    $query .= " LIMIT $per_page OFFSET $offset";
    $products = $db->fetchAll($query, $params);
    
    // Get all categories for filter
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
} catch (Exception $e) {
    $products = [];
    $categories = [];
    $total_products = 0;
    $total_pages = 0;
}
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="p-0 border-0">
                <h5 class="mb-4 fw-bold text-uppercase ls-1">Filters</h5>
                
                <form method="GET" action="">
                    <!-- Category Filter -->
                    <div class="mb-5 border-bottom pb-4">
                        <label class="form-label fw-bold mb-3">Category</label>
                        <?php foreach ($categories as $cat): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="category" value="<?= $cat['id'] ?>" 
                                       id="cat<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cat<?= $cat['id'] ?>">
                                    <?= e($cat['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" value="" 
                                   id="catAll" <?= !$category_filter ? 'checked' : '' ?>>
                            <label class="form-check-label" for="catAll">All Categories</label>
                        </div>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="mb-5 border-bottom pb-4">
                        <label class="form-label fw-bold mb-3">Price Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control" name="min_price" 
                                       placeholder="Min" value="<?= $min_price ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" name="max_price" 
                                       placeholder="Max" value="<?= $max_price ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search -->
                    <?php if ($search): ?>
                        <input type="hidden" name="q" value="<?= e($search) ?>">
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        Apply Filters
                    </button>
                    <a href="products.php" class="btn btn-link text-secondary w-100 mt-2 text-decoration-none">
                        Clear All
                    </a>
                </form>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><?= $search ? 'Search Results for "' . e($search) . '"' : 'All Products' ?></h2>
                    <p class="text-muted">Showing <?= count($products) ?> of <?= $total_products ?> products</p>
                </div>
                <div>
                    <select class="form-select" onchange="window.location.href=this.value">
                        <option value="">Sort By</option>
                        <option value="?sort=newest&<?= http_build_query(array_diff_key($_GET, ['sort' => ''])) ?>" 
                                <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="?sort=price_low&<?= http_build_query(array_diff_key($_GET, ['sort' => ''])) ?>" 
                                <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="?sort=price_high&<?= http_build_query(array_diff_key($_GET, ['sort' => ''])) ?>" 
                                <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="?sort=name&<?= http_build_query(array_diff_key($_GET, ['sort' => ''])) ?>" 
                                <?= $sort == 'name' ? 'selected' : '' ?>>Name</option>
                    </select>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="row g-4">
                <?php if (empty($products)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center py-5">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            <h4>No products found</h4>
                            <p>Try adjusting your filters or search terms</p>
                            <a href="products.php" class="btn btn-primary">Browse All Products</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-sm-6 col-lg-4">
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if ($product['discount_price']): 
                                        $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                                    ?>
                                        <span class="product-badge">-<?= $discount_percent ?>%</span>
                                    <?php endif; ?>
                                    
                                    <a href="<?= BASE_URL ?>product-detail.php?id=<?= $product['id'] ?>">
                                        <img src="<?= PRODUCT_IMAGES_URL . ($product['image'] ?? 'placeholder.png') ?>" 
                                             alt="<?= e($product['name']) ?>" 
                                             loading="lazy"
                                             onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                                    </a>
                                </div>
                                
                                <div class="product-body">
                                    <div class="text-muted small mb-1"><?= e($product['category_name']) ?></div>
                                    <h5 class="product-title">
                                        <a href="<?= BASE_URL ?>product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none">
                                            <?= e($product['name']) ?>
                                        </a>
                                    </h5>
                                    
                                    <div class="product-price">
                                        <?= formatPrice($product['discount_price'] ?? $product['price']) ?>
                                        <?php if ($product['discount_price']): ?>
                                            <span class="product-price-old"><?= formatPrice($product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button class="btn btn-link position-absolute top-0 end-0 p-3 text-dark wishlist-btn" 
                                            data-product-id="<?= $product['id'] ?>" 
                                            style="z-index: 5;">
                                        <i class="<?= isInWishlist($product['id']) ? 'fas text-danger' : 'far' ?> fa-heart fa-lg"></i>
                                    </button>
                                    
                                    <?php if ($product['stock'] > 0): ?>
                                        <div class="d-grid mt-3">
                                            <a href="<?= BASE_URL ?>product-detail.php?id=<?= $product['id'] ?>"
                                               class="btn btn-primary">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-danger small mt-3 mb-0">Out of Stock</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <style>
                .pagination .page-link {
                    color: #000;
                    border-color: #dee2e6;
                }
                .pagination .page-link:hover {
                    color: #fff;
                    background-color: #000;
                    border-color: #000;
                }
                .pagination .page-item.active .page-link {
                    background-color: #000 !important;
                    border-color: #000 !important;
                    color: #fff !important;
                }
            </style>
            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
