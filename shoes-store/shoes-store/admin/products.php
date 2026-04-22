<?php
/**
 * Admin Products Management – Mobile Responsive
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

requireAdmin();

$page_title = 'Products - ' . SITE_NAME;
$db = new Database();

// Handle delete
if (isset($_GET['delete'])) {
    checkCsrf();
    $id = intval($_GET['delete']);
    try {
        $product = $db->fetchOne("SELECT image FROM products WHERE id = ?", [$id]);
        $db->query("DELETE FROM products WHERE id = ?", [$id]);
        if ($product && $product['image']) {
            $image_path = UPLOAD_PATH . 'products/' . $product['image'];
            if (file_exists($image_path)) unlink($image_path);
        }
        setFlash('success', 'Product deleted successfully');
        redirect(BASE_URL . 'admin/products.php');
    } catch (Exception $e) {
        setFlash('error', 'Error deleting product');
    }
}

require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';

// Search
$search = clean($_GET['search'] ?? '');
$params = [];
$whereClause = '';
if ($search) {
    $whereClause = "WHERE p.name LIKE ? OR c.name LIKE ?";
    $s = "%$search%";
    $params = [$s, $s];
}

$products = $db->fetchAll(
    "SELECT p.*, c.name as category_name FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     $whereClause
     ORDER BY p.created_at DESC",
    $params
);

$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
?>

<!-- ─── Page-level Mobile Responsive Styles ─────────────────────────── -->
<style>
/* ── Page Header ── */
.products-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.875rem;
    margin-bottom: 1.5rem;
}
.products-header-left { min-width: 0; }
.products-header-right {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    flex-wrap: wrap;
}
.search-form {
    position: relative;
    flex: 1 1 220px;
    min-width: 180px;
    max-width: 320px;
}
.search-form .search-icon {
    position: absolute;
    left: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.8rem;
    pointer-events: none;
}
.search-form input {
    padding-left: 2.4rem;
    height: 40px;
    font-size: 0.85rem;
}
.btn-add-product {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 0 1.25rem;
    height: 40px;
    font-size: 0.855rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.18s;
    text-decoration: none;
}
.btn-add-product:hover {
    background: #4f46e5;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(99,102,241,0.35);
}

/* ── Desktop Table ── */
.products-table-wrap { display: block; }

/* ── Mobile Cards ── */
.products-cards-wrap { display: none; }
.product-card-mobile {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    padding: 1rem 1.125rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.product-card-mobile:last-child { border-bottom: none; }
.product-card-mobile:active { background: #fafbff; }
.product-card-img {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 11px;
    border: 1px solid var(--border);
    flex-shrink: 0;
}
.product-card-body { flex: 1; min-width: 0; }
.product-card-name {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.25rem;
}
.product-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    align-items: center;
    margin-bottom: 0.4rem;
}
.product-card-price {
    font-weight: 700;
    font-size: 0.92rem;
    color: var(--text-primary);
}
.product-card-old-price {
    font-size: 0.75rem;
    text-decoration: line-through;
    color: var(--text-muted);
    margin-left: 0.2rem;
}
.product-card-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
    align-items: center;
}
.btn-card-edit, .btn-card-delete {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    font-size: 0.82rem;
    border: none;
    cursor: pointer;
    transition: all 0.18s;
    text-decoration: none;
}
.btn-card-edit {
    background: var(--accent-light);
    color: var(--accent);
}
.btn-card-edit:hover { background: #c7d2fe; color: var(--accent); }
.btn-card-delete {
    background: #fee2e2;
    color: var(--danger);
}
.btn-card-delete:hover { background: #fecaca; color: var(--danger); }

/* ── Floating Action Button (mobile) ── */
.fab-add-product {
    display: none;
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    color: white;
    border: none;
    border-radius: 50%;
    box-shadow: 0 6px 20px rgba(99,102,241,0.45);
    font-size: 1.25rem;
    cursor: pointer;
    z-index: 900;
    align-items: center;
    justify-content: center;
    transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
.fab-add-product:hover {
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 8px 24px rgba(99,102,241,0.55);
}
.fab-add-product:active { transform: scale(0.95); }

/* ── Modal — Full-screen on mobile ── */
@media (max-width: 575.98px) {
    .modal-dialog { margin: 0; max-width: 100%; height: 100%; }
    .modal-content {
        border-radius: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .modal-body { flex: 1; overflow-y: auto; }
    .modal-header { border-radius: 0; }
    .modal-footer { border-radius: 0; }
    .modal-footer .btn-ghost,
    .modal-footer .btn-accent { flex: 1; justify-content: center; }
}

/* ── Responsive breakpoints ── */
@media (max-width: 767.98px) {
    /* Switch to card layout */
    .products-table-wrap { display: none !important; }
    .products-cards-wrap { display: block; }

    /* Show FAB, hide header Add Product button */
    .fab-add-product { display: flex; }
    .btn-add-product { display: none; }

    /* Search takes full width */
    .products-header { flex-direction: column; align-items: stretch; }
    .products-header-right { flex-direction: column-reverse; gap: 0.5rem; }
    .search-form { max-width: 100%; flex: 1 1 auto; }
    .search-form input { width: 100%; }
}

@media (min-width: 768px) and (max-width: 991.98px) {
    /* Tablet — keep table but hide some columns, show add button */
    .search-form { max-width: 260px; }
}

/* ── Form touch targets ── */
@media (max-width: 767.98px) {
    .modal-body .form-control,
    .modal-body .form-select {
        height: 48px;
        font-size: 1rem;
        border-radius: 10px;
    }
    .modal-body textarea.form-control { height: auto; }
    .modal-body .form-label { font-size: 0.875rem; }
    .modal-body .row { --bs-gutter-x: 0.75rem; }
    .modal-footer { padding: 1rem; gap: 0.625rem; }
    .modal-footer .btn-ghost,
    .modal-footer .btn-accent {
        height: 48px;
        font-size: 0.95rem;
        border-radius: 10px;
    }
}

/* ── Products count pill ── */
.products-count-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: var(--accent-light);
    color: var(--accent);
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 0.72rem;
    font-weight: 700;
    margin-left: 0.5rem;
    vertical-align: middle;
}
</style>

<!-- ─── Page Header ─────────────────────────────────────────────────── -->
<div class="products-header">
    <div class="products-header-left">
        <h1 class="page-title">
            Products
            <span class="products-count-pill">
                <i class="fas fa-box"></i> <?= count($products) ?>
            </span>
        </h1>
        <p class="page-subtitle">Manage your product catalog</p>
    </div>
    <div class="products-header-right">
        <!-- Search -->
        <form method="GET" class="search-form">
            <i class="fas fa-search search-icon"></i>
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search products..."
                   value="<?= htmlspecialchars($search) ?>"
                   autocomplete="off">
        </form>
        <?php if ($search): ?>
            <a href="products.php" class="btn-ghost" style="height:40px;padding:0 1rem;">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
        <!-- Desktop Add Product button -->
        <button type="button"
                id="addProductBtnDesktop"
                class="btn-add-product"
                data-bs-toggle="modal"
                data-bs-target="#addProductModal">
            <i class="fas fa-plus"></i> Add Product
        </button>
    </div>
</div>

<!-- ─── Desktop Table ───────────────────────────────────────────────── -->
<div class="admin-card products-table-wrap">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="d-none d-sm-table-cell">ID</th>
                    <th>Product</th>
                    <th class="d-none d-md-table-cell">Category</th>
                    <th>Price</th>
                    <th class="d-none d-sm-table-cell">Stock</th>
                    <th class="d-none d-lg-table-cell">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="fas fa-box-open fa-2x d-block mb-2 opacity-25"></i>
                            No products found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="d-none d-sm-table-cell" style="color:var(--text-muted);font-size:0.8rem;">#<?= $product['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative flex-shrink-0">
                                        <img src="<?= PRODUCT_IMAGES_URL . ($product['image'] ?? 'placeholder.png') ?>"
                                             alt="<?= htmlspecialchars($product['name']) ?>"
                                             style="width:46px;height:46px;object-fit:cover;border-radius:10px;border:1px solid var(--border);"
                                             onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                                        <?php if ($product['is_featured']): ?>
                                            <span style="position:absolute;top:-4px;right:-4px;width:14px;height:14px;background:#f59e0b;border-radius:50%;border:2px solid white;" title="Featured"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="min-width:0;">
                                        <div style="font-weight:600;font-size:0.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;" title="<?= htmlspecialchars($product['name']) ?>">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </div>
                                        <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                            <span class="status-badge badge-danger" style="font-size:0.65rem;margin-top:2px;">SALE</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span class="status-badge badge-secondary"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></span>
                            </td>
                            <td>
                                <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                    <div style="font-weight:700;color:var(--text-primary);"><?= formatPrice($product['discount_price']) ?></div>
                                    <div style="font-size:0.75rem;text-decoration:line-through;color:var(--text-muted);"><?= formatPrice($product['price']) ?></div>
                                <?php else: ?>
                                    <div style="font-weight:700;"><?= formatPrice($product['price']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-sm-table-cell">
                                <?php if ($product['stock'] == 0): ?>
                                    <span class="status-badge badge-danger">Out of Stock</span>
                                <?php elseif ($product['stock'] <= 10): ?>
                                    <span class="status-badge badge-warning"><?= $product['stock'] ?> left</span>
                                <?php else: ?>
                                    <span class="status-badge badge-success"><?= $product['stock'] ?> units</span>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <span class="status-badge <?= $product['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="edit-product.php?id=<?= $product['id'] ?>"
                                       class="btn-ghost" style="padding:0.35rem 0.75rem;font-size:0.78rem;" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn-danger-ghost delete-product-btn"
                                            style="padding:0.35rem 0.75rem;font-size:0.78rem;"
                                            data-id="<?= $product['id'] ?>"
                                            data-url="?delete=<?= $product['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ─── Mobile Card Layout ──────────────────────────────────────────── -->
<div class="admin-card products-cards-wrap">
    <?php if (empty($products)): ?>
        <div class="text-center py-5" style="color:var(--text-muted);">
            <i class="fas fa-box-open fa-2x d-block mb-2 opacity-25"></i>
            <div style="font-size:0.9rem;">No products found</div>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card-mobile">
                <!-- Thumbnail -->
                <div class="position-relative flex-shrink-0">
                    <img class="product-card-img"
                         src="<?= PRODUCT_IMAGES_URL . ($product['image'] ?? 'placeholder.png') ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                    <?php if ($product['is_featured']): ?>
                        <span style="position:absolute;top:-3px;right:-3px;width:13px;height:13px;background:#f59e0b;border-radius:50%;border:2px solid white;" title="Featured"></span>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="product-card-body">
                    <div class="product-card-name"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="product-card-meta">
                        <!-- Price -->
                        <span class="product-card-price">
                            <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                <?= formatPrice($product['discount_price']) ?>
                                <span class="product-card-old-price"><?= formatPrice($product['price']) ?></span>
                            <?php else: ?>
                                <?= formatPrice($product['price']) ?>
                            <?php endif; ?>
                        </span>
                        <!-- Badges -->
                        <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                            <span class="status-badge badge-danger" style="font-size:0.65rem;">SALE</span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:0.3rem;">
                        <!-- Category -->
                        <span class="status-badge badge-secondary" style="font-size:0.68rem;">
                            <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                        </span>
                        <!-- Stock -->
                        <?php if ($product['stock'] == 0): ?>
                            <span class="status-badge badge-danger" style="font-size:0.68rem;">Out of Stock</span>
                        <?php elseif ($product['stock'] <= 10): ?>
                            <span class="status-badge badge-warning" style="font-size:0.68rem;"><?= $product['stock'] ?> left</span>
                        <?php else: ?>
                            <span class="status-badge badge-success" style="font-size:0.68rem;"><?= $product['stock'] ?> units</span>
                        <?php endif; ?>
                        <!-- Active -->
                        <span class="status-badge <?= $product['is_active'] ? 'badge-success' : 'badge-secondary' ?>" style="font-size:0.68rem;">
                            <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="product-card-actions">
                    <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn-card-edit" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button"
                            class="btn-card-delete delete-product-btn"
                            data-id="<?= $product['id'] ?>"
                            data-url="?delete=<?= $product['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ─── Floating Action Button (mobile only) ───────────────────────── -->
<button type="button"
        id="fabAddProduct"
        class="fab-add-product"
        title="Add Product"
        aria-label="Add Product">
    <i class="fas fa-plus"></i>
</button>

<!-- ─── Add Product Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">
                    <i class="fas fa-plus-circle me-2 text-indigo"></i>Add New Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- form wraps ONLY the body so footer is a direct flex child of modal-content -->
            <form id="addProductForm" method="POST" action="add-product.php" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="modal-body" id="addProductModalBody">
                    <!-- Section label -->
                    <p class="modal-section-label">Basic Information</p>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name"
                                   placeholder="e.g. Nike Air Max 2024" required autocomplete="off">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3"
                                  required placeholder="Describe this product..."></textarea>
                    </div>

                    <!-- Pricing & Stock -->
                    <p class="modal-section-label" style="margin-top:1.25rem;">Pricing &amp; Stock</p>
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price"
                                   min="0" step="0.01" required placeholder="0.00"
                                   inputmode="decimal">
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label">Discount Price (₹)</label>
                            <input type="number" class="form-control" name="discount_price"
                                   min="0" step="0.01" placeholder="Optional"
                                   inputmode="decimal">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stock"
                                   min="0" required placeholder="0"
                                   inputmode="numeric">
                        </div>
                    </div>

                    <!-- Images -->
                    <p class="modal-section-label" style="margin-top:1.25rem;">Product Images</p>
                    <div class="row g-3 mb-2" id="imageInputsWrap">

                        <!-- Main Image (required) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="imgMain">
                                Main Image <span class="text-danger">*</span>
                            </label>
                            <div class="img-upload-box" id="boxMain">
                                <div class="img-preview-wrap d-none" id="prevWrapMain">
                                    <img id="prevMain" src="" alt="preview" class="img-preview-thumb">
                                    <button type="button" class="img-clear-btn" data-target="imgMain" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="file" class="form-control img-input" id="imgMain"
                                       name="images[]" accept="image/*" required
                                       data-preview="prevMain" data-prevwrap="prevWrapMain">
                            </div>
                        </div>

                        <!-- Image 2 (optional) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="imgTwo">
                                Image 2 <span style="color:var(--text-muted);">(optional)</span>
                            </label>
                            <div class="img-upload-box" id="boxTwo">
                                <div class="img-preview-wrap d-none" id="prevWrapTwo">
                                    <img id="prevTwo" src="" alt="preview" class="img-preview-thumb">
                                    <button type="button" class="img-clear-btn" data-target="imgTwo" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="file" class="form-control img-input" id="imgTwo"
                                       name="images[]" accept="image/*"
                                       data-preview="prevTwo" data-prevwrap="prevWrapTwo">
                            </div>
                        </div>

                        <!-- Image 3 (optional) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="imgThree">
                                Image 3 <span style="color:var(--text-muted);">(optional)</span>
                            </label>
                            <div class="img-upload-box" id="boxThree">
                                <div class="img-preview-wrap d-none" id="prevWrapThree">
                                    <img id="prevThree" src="" alt="preview" class="img-preview-thumb">
                                    <button type="button" class="img-clear-btn" data-target="imgThree" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="file" class="form-control img-input" id="imgThree"
                                       name="images[]" accept="image/*"
                                       data-preview="prevThree" data-prevwrap="prevWrapThree">
                            </div>
                        </div>

                        <!-- Image 4 (optional) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="imgFour">
                                Image 4 <span style="color:var(--text-muted);">(optional)</span>
                            </label>
                            <div class="img-upload-box" id="boxFour">
                                <div class="img-preview-wrap d-none" id="prevWrapFour">
                                    <img id="prevFour" src="" alt="preview" class="img-preview-thumb">
                                    <button type="button" class="img-clear-btn" data-target="imgFour" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="file" class="form-control img-input" id="imgFour"
                                       name="images[]" accept="image/*"
                                       data-preview="prevFour" data-prevwrap="prevWrapFour">
                            </div>
                        </div>

                    </div><!-- /row -->
                    <p style="font-size:0.75rem;color:var(--text-muted);">
                        <i class="fas fa-info-circle me-1"></i>
                        Only the main image is required. Max 2 MB per image. Formats: JPG, PNG, WEBP, GIF.
                    </p>

                    <!-- Options -->
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured">
                        <label class="form-check-label" for="isFeatured" style="font-size:0.875rem;font-weight:500;">
                            <i class="fas fa-star text-amber me-1"></i> Mark as Featured Product
                        </label>
                    </div>
                </div><!-- /modal-body -->
            </form><!-- form ends here, footer is outside it -->

            <!-- Footer is a direct child of modal-content so flex works correctly -->
            <div class="modal-footer d-flex gap-2">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <!-- form="addProductForm" links this button to the form above -->
                <button type="submit" form="addProductForm" class="btn-accent">
                    <i class="fas fa-plus me-1"></i> Add Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Modal scrollbar + section label styles ───────────────────────── -->
<style>
/*
  THE SCROLLABLE MODAL APPROACH
  ─────────────────────────────
  modal-content is a flex column:
    [modal-header]  flex-shrink:0  → pinned to top
    [form]          flex:1 min-h:0 → takes all remaining space
      [modal-body]  height:100% overflow-y:auto → scrolls
    [modal-footer]  flex-shrink:0  → pinned to bottom
*/
#addProductModal .modal-dialog {
    max-height: calc(100vh - 3.5rem);
}
#addProductModal .modal-content {
    max-height: calc(100vh - 3.5rem);
    display: flex;
    flex-direction: column;
}
/* Header — fixed top */
#addProductModal .modal-header {
    flex-shrink: 0;
}
/* Form fills space between header and footer */
#addProductModal #addProductForm {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
/* Body scrolls */
#addProductModal .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #a5b4fc #f1f5f9;
}
/* Footer — fixed bottom (direct child of modal-content now) */
#addProductModal .modal-footer {
    flex-shrink: 0;
}

/* Webkit scrollbar (Chrome / Safari / Edge) */
#addProductModal .modal-body::-webkit-scrollbar { width: 6px; }
#addProductModal .modal-body::-webkit-scrollbar-track { background: #f1f5f9; }
#addProductModal .modal-body::-webkit-scrollbar-thumb {
    background: #a5b4fc;
    border-radius: 99px;
}
#addProductModal .modal-body::-webkit-scrollbar-thumb:hover { background: #6366f1; }

/* Mobile – full screen */
@media (max-width: 575.98px) {
    #addProductModal .modal-dialog { max-height: 100vh; }
    #addProductModal .modal-content { max-height: 100vh; border-radius: 0; }
    #addProductModal .modal-dialog { margin: 0; max-width: 100%; }
    #addProductModal .modal-footer .btn-ghost,
    #addProductModal .modal-footer .btn-accent { flex: 1; justify-content: center; }
}

/* ── Section labels with divider line ── */
.modal-section-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin-bottom: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.modal-section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}
</style>

<!-- ─── Image upload UI styles ───────────────────────────────────────────── -->
<style>
.img-upload-box { position: relative; }

/* Live preview strip */
.img-preview-wrap {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 0.5rem;
    padding: 0.5rem 0.625rem;
    background: #f8faff;
    border: 1.5px solid #c7d2fe;
    border-radius: 10px;
    animation: fadeInUp 0.25s ease;
}
.img-preview-thumb {
    width: 52px;
    height: 52px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e0e7ff;
    flex-shrink: 0;
}
.img-preview-name {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    min-width: 0;
}
.img-preview-size {
    font-size: 0.7rem;
    color: var(--text-muted);
    white-space: nowrap;
}
.img-clear-btn {
    width: 26px;
    height: 26px;
    background: #fee2e2;
    border: none;
    border-radius: 50%;
    color: #ef4444;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.15s;
    margin-left: auto;
}
.img-clear-btn:hover { background: #fecaca; }

/* Valid border on input */
.img-input.img-valid { border-color: #10b981 !important; }
.img-input.img-error { border-color: #ef4444 !important; }

/* Error message style */
.img-error-msg {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.775rem;
    color: #dc2626;
    font-weight: 500;
    margin-top: 0.35rem;
    padding: 0.3rem 0.6rem;
    background: #fff1f2;
    border-radius: 7px;
    border-left: 3px solid #ef4444;
    animation: fadeInUp 0.2s ease;
}
</style>

<!-- ─── Scripts ─────────────────────────────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Modal trigger helpers ── */
    function openAddModal() {
        var el = document.getElementById('addProductModal');
        if (el && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    // Desktop button
    var desktopBtn = document.getElementById('addProductBtnDesktop');
    if (desktopBtn) desktopBtn.addEventListener('click', openAddModal);

    // FAB (mobile)
    var fab = document.getElementById('fabAddProduct');
    if (fab) fab.addEventListener('click', openAddModal);

    /* ── Delete confirmation ── */
    document.querySelectorAll('.delete-product-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = this.getAttribute('data-url');
            Swal.fire({
                title: 'Delete Product?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
            }).then(function (result) {
                if (result.isConfirmed) window.location.href = url;
            });
        });
    });
});

/* ── jQuery Validation (polls until jQuery + plugin are ready) ── */
function initProductValidation() {
    if (typeof $ === 'undefined' || typeof $.fn.validate === 'undefined') {
        return setTimeout(initProductValidation, 100);
    }

    $.validator.addMethod('lessThanPrice', function (value, element) {
        var price = $("input[name='price']").val();
        if (!value || !price) return true;
        return parseFloat(value) < parseFloat(price);
    }, 'Discount price must be less than regular price');

    $.validator.addMethod('validImageExtension', function (value, element) {
        if (element.files.length === 0) return true;
        var ext = element.files[0].name.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext);
    }, 'Please upload a valid image (JPG, PNG, WEBP)');

    $.validator.addMethod('validImageSize', function (value, element) {
        if (element.files.length === 0) return true;
        return element.files[0].size / (1024 * 1024) <= 2;
    }, 'Image must be under 2MB');

    $('#addProductForm').validate({
        rules: {
            name:           { required: true, minlength: 3 },
            category_id:    { required: true },
            description:    { required: true, minlength: 10 },
            price:          { required: true, number: true, min: 0.01 },
            discount_price: { number: true, min: 0, lessThanPrice: true },
            stock:          { required: true, digits: true, min: 0 },
        },
        errorClass: 'is-invalid',
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.col-12, .col-6, .col-md-4, .col-md-6, .mb-3').append(error);
        },
        highlight: function (element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
    });

    $('input[name="images[]"]').each(function () {
        $(this).rules('add', {
            required: $(this).prop('required'),
            validImageExtension: true,
            validImageSize: true,
            messages: { required: 'Please select a main image' },
        });
    });
}
initProductValidation();
</script>

<?php require_once 'includes/admin-footer.php'; ?>
