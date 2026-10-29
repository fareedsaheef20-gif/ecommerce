<?php
/**
 * Product Listing / Shop Page
 */

require_once 'includes/functions.php';

$conn = getDB();
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build filter conditions
$where_parts = ['p.is_active = 1'];
$title_parts = [];

// Category filter
$category = null;
if (!empty($_GET['category'])) {
    $category = getCategoryBySlug($_GET['category']);
    if ($category) {
        $where_parts[] = "p.category_id = " . (int)$category['id'];
        $title_parts[] = $category['name'];
    }
}

// Featured filter
if (!empty($_GET['featured'])) {
    $where_parts[] = "p.is_featured = 1";
    $title_parts[] = "Featured";
}

// Search filter
$search = '';
if (!empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $where_parts[] = "(p.name LIKE '%$search%' OR p.brand LIKE '%$search%' OR p.short_description LIKE '%$search%')";
    $title_parts[] = "Search: \"$search\"";
}

// Brand filter
$brand_filter = '';
if (!empty($_GET['brand'])) {
    $brand_filter = sanitize($_GET['brand']);
    $where_parts[] = "p.brand = '$brand_filter'";
}

// Price filter
$max_price = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
if ($max_price > 0) {
    $where_parts[] = "COALESCE(p.sale_price, p.price) <= $max_price";
}

// Sort
$sort_options = [
    'newest'     => 'p.created_at DESC',
    'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
    'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
    'rating'     => 'p.rating DESC',
    'popular'    => 'p.review_count DESC',
];
$sort = $_GET['sort'] ?? 'newest';
$order_by = $sort_options[$sort] ?? $sort_options['newest'];

$where = implode(' AND ', $where_parts);

// Count total
$count_result = $conn->query("SELECT COUNT(*) as total FROM products p WHERE $where");
$total = $count_result ? (int)$count_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total / $per_page);

// Get products
$products = [];
$result = $conn->query("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $where
    ORDER BY $order_by
    LIMIT $per_page OFFSET $offset
");
if ($result) $products = $result->fetch_all(MYSQLI_ASSOC);

// Get available brands for filter
$brands_result = $conn->query("SELECT DISTINCT brand FROM products WHERE is_active=1 AND brand IS NOT NULL ORDER BY brand");
$brands = $brands_result ? $brands_result->fetch_all(MYSQLI_ASSOC) : [];

$page_title = !empty($title_parts) ? implode(' | ', $title_parts) . ' - Shop' : 'Shop All Electronics';

// Build query string for pagination
$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);
?>
<?php include 'includes/header.php'; ?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <?php if ($category): ?>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/products.php">Shop</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($category['name']); ?></li>
            <?php else: ?>
                <li class="breadcrumb-item active">Shop</li>
            <?php endif; ?>
        </ol>
    </div>
</div>

<section class="section-padding-sm" style="background:#F8FAFC;">
<div class="container">
    <div class="row g-4">
        <!-- ── Sidebar Filter ── -->
        <div class="col-lg-3">
            <div class="filter-card">
                <h5 style="font-weight:800;font-size:1rem;margin-bottom:20px;">
                    <i class="fas fa-sliders-h me-2 text-primary"></i>Filter Products
                </h5>

                <form method="GET" action="products.php" id="filterForm">
                    <?php if (!empty($_GET['category'])): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']); ?>">
                    <?php endif; ?>
                    <?php if (!empty($search)): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <?php endif; ?>

                    <!-- Categories -->
                    <div class="filter-section">
                        <div class="filter-title">Category</div>
                        <?php foreach ($categories as $cat): ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="category" id="cat_<?php echo $cat['id']; ?>"
                                   value="<?php echo $cat['slug']; ?>"
                                   <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['slug']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="cat_<?php echo $cat['id']; ?>" style="font-size:0.87rem;">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Brands -->
                    <?php if (!empty($brands)): ?>
                    <div class="filter-section">
                        <div class="filter-title">Brand</div>
                        <?php foreach (array_slice($brands, 0, 12) as $b): ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="brand" id="brand_<?php echo md5($b['brand']); ?>"
                                   value="<?php echo htmlspecialchars($b['brand']); ?>"
                                   <?php echo ($brand_filter == $b['brand']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="brand_<?php echo md5($b['brand']); ?>" style="font-size:0.87rem;">
                                <?php echo htmlspecialchars($b['brand']); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Price Range -->
                    <div class="filter-section">
                        <div class="filter-title">Max Price</div>
                        <input type="range" class="price-range w-100" id="priceRange" name="max_price"
                               min="0" max="4000" step="50"
                               value="<?php echo $max_price ?: 4000; ?>">
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Rs.0</small>
                            <small class="text-muted fw-bold" id="priceDisplay">
                                Rs.0 – Rs.<?php echo $max_price ?: 4000; ?>
                            </small>
                            <small class="text-muted">Rs.4000</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Apply Filters</button>
                        <a href="products.php" class="btn btn-outline-secondary" title="Clear filters">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Product Grid ── -->
        <div class="col-lg-9">
            <!-- Sort Bar -->
            <div class="sort-bar">
                <div class="results-count">
                    Showing <strong><?php echo count($products); ?></strong> of
                    <strong><?php echo $total; ?></strong> products
                    <?php if (!empty($search)): ?> for "<strong><?php echo htmlspecialchars($search); ?></strong>"<?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0" style="font-size:0.82rem;white-space:nowrap;">Sort by:</label>
                    <select class="form-select form-select-sm" style="width:auto;"
                            onchange="const url = new URL(window.location); url.searchParams.set('sort', this.value); window.location = url;">
                        <option value="newest" <?php echo $sort=='newest'?'selected':''; ?>>Newest</option>
                        <option value="price_asc" <?php echo $sort=='price_asc'?'selected':''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort=='price_desc'?'selected':''; ?>>Price: High to Low</option>
                        <option value="rating" <?php echo $sort=='rating'?'selected':''; ?>>Best Rating</option>
                        <option value="popular" <?php echo $sort=='popular'?'selected':''; ?>>Most Popular</option>
                    </select>
                </div>
            </div>

            <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h4>No products found</h4>
                <p>Try adjusting your filters or search terms.</p>
                <a href="products.php" class="btn btn-primary mt-3">Clear Filters</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $product):
                    $price = getProductPrice($product);
                    $on_sale = $product['sale_price'] && $product['sale_price'] > 0;
                    $discount = $on_sale ? round((1 - $product['sale_price']/$product['price'])*100) : 0;
                ?>
                <div class="col-6 col-md-4">
                    <div class="product-card">
                        <div class="product-image-wrap">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                 onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 loading="lazy">
                            <div class="product-badges">
                                <?php if ($on_sale): ?><span class="badge-sale">-<?php echo $discount; ?>%</span><?php endif; ?>
                                <?php if ($product['is_featured']): ?><span class="badge-hot">HOT</span><?php endif; ?>
                                <?php if ($product['stock'] == 0): ?><span class="badge-sale">Out of Stock</span><?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>" class="action-btn">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></div>
                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>" class="product-name">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                            <div class="product-rating">
                                <?php echo renderStars($product['rating']); ?>
                                <span>(<?php echo $product['review_count']; ?>)</span>
                            </div>
                            <div class="product-price-wrap">
                                <span class="price-current"><?php echo "Rs.".($price); ?></span>
                                <?php if ($on_sale): ?>
                                    <span class="price-original"><?php echo "Rs.".($product['price']); ?></span>
                                    <span class="price-discount">-<?php echo $discount; ?>%</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="product-footer">
                            <button class="btn-add-cart <?php echo $product['stock']==0?'out-of-stock':''; ?>"
                                    data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart me-1"></i>
                                <?php echo $product['stock']==0 ? 'Out of Stock' : 'Add to Cart'; ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-center mt-5">
                <div class="custom-pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo $query_string; ?>&page=<?php echo $page-1; ?>" class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                        <a href="?<?php echo $query_string; ?>&page=<?php echo $i; ?>"
                           class="page-btn <?php echo $i==$page?'active':''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo $query_string; ?>&page=<?php echo $page+1; ?>" class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
