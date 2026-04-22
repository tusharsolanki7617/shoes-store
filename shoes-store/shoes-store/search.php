<?php
/**
 * Search Results Page
 */

require_once 'config/config.php';
$page_title = 'Search Results - Kicks & Comfort';
require_once 'includes/header.php';

$search = clean($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));

if (empty($search)) {
    redirect(BASE_URL . 'products.php');
}

try {
    $db = new Database();
    
    // Search query
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ?)";
    $params = ["%$search%", "%$search%"];
    
    //Get total count
    $count_query = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*) as total", $query);
    $total_products = $db->fetchOne($count_query, $params)['total'];
    
    // Pagination
    $per_page = PRODUCTS_PER_PAGE;
    $total_pages = ceil($total_products / $per_page);
    $offset = ($page - 1) * $per_page;
    
    $query .= " ORDER BY p.name ASC LIMIT $per_page OFFSET $offset";
    $products = $db->fetchAll($query, $params);
} catch (Exception $e) {
    $products = [];
    $total_products = 0;
    $total_pages = 0;
}
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-search"></i> Search Results for "<?= e($search) ?>"
    </h2>
    <p class="text-muted mb-4">Found <?= $total_products ?> products</p>
    
    <?php if (empty($products)): ?>
        <div class="card-glass p-5 text-center">
            <i class="fas fa-search fa-5x text-muted mb-4"></i>
            <h3>No products found</h3>
            <p class="text-muted">Try using different keywords or browse all products</p>
            <a href="<?= BASE_URL ?>products.php" class="btn btn-primary mt-3">
                <i class="fas fa-shopping-bag"></i> Browse All Products
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
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
                            
                            <?php if ($product['stock'] > 0): ?>
                                <div class="d-grid mt-3">
                                    <button class="btn btn-primary add-to-cart" data-product-id="<?= $product['id'] ?>">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger small mt-3 mb-0">Out of Stock</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
