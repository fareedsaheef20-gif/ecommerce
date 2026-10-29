<?php
/**
 * Product Detail Page
 */

require_once 'includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) { header('Location: products.php'); exit; }

$product = getProductBySlug($slug);
if (!$product) { header('Location: products.php'); exit; }

// Increment view counter
$conn = getDB();
$conn->query("UPDATE products SET views = views + 1 WHERE id = " . (int)$product['id']);

$price = getProductPrice($product);
$on_sale = $product['sale_price'] && $product['sale_price'] > 0;
$discount = $on_sale ? round((1 - $product['sale_price']/$product['price'])*100) : 0;

// Parse specs from JSON
$specs = [];
if (!empty($product['specifications'])) {
    $specs = json_decode($product['specifications'], true) ?? [];
}

$related = getRelatedProducts($product['id'], $product['category_id'], 4);

$page_title = htmlspecialchars($product['name']);
$meta_desc = htmlspecialchars($product['short_description'] ?? '');
?>
<?php include 'includes/header.php'; ?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/products.php">Shop</a></li>
            <?php if (!empty($product['category_slug'])): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_slug']; ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </div>
</div>

<section class="section-padding-sm" style="background:#F8FAFC;">
<div class="container">
    <div class="row g-5">
        <!-- ── Product Gallery ── -->
        <div class="col-lg-5">
            <div class="product-detail-gallery">
                <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;overflow:hidden;aspect-ratio:1;">
                    <img id="mainProductImg"
                         src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                         onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="width:100%;height:100%;object-fit:contain;padding:24px;">
                </div>
                <!-- Thumbnails (same image repeated as placeholder) -->
                <div class="product-gallery-thumbs mt-3">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                         onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                         class="gallery-thumb <?php echo $i==0?'active':''; ?>"
                         data-src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                         alt="View <?php echo $i+1; ?>">
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- ── Product Info ── -->
        <div class="col-lg-7">
            <div class="product-detail-info">
                <!-- Brand & Category -->
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span style="background:rgba(0,102,255,0.08);color:#0066FF;padding:3px 12px;border-radius:20px;font-size:0.75rem;font-weight:700;text-transform:uppercase;">
                        <?php echo htmlspecialchars($product['brand']); ?>
                    </span>
                    <span style="font-size:0.8rem;color:#6B7280;">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                </div>

                <h1 class="product-detail-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                <!-- Rating -->
                <div class="d-flex align-items-center gap-2 my-3">
                    <?php echo renderStars($product['rating']); ?>
                    <span style="font-size:0.88rem;color:#6B7280;"><?php echo $product['rating']; ?>/5.0
                        (<?php echo $product['review_count']; ?> reviews)</span>
                    <span class="ms-2" style="font-size:0.8rem;color:#6B7280;">|</span>
                    <span style="font-size:0.8rem;color:#6B7280;"><?php echo $product['views']; ?> views</span>
                </div>

                <!-- Price -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="product-detail-price"><?php echo formatPrice($price); ?></span>
                    <?php if ($on_sale): ?>
                        <span style="text-decoration:line-through;color:#9CA3AF;font-size:1.1rem;"><?php echo formatPrice($product['price']); ?></span>
                        <span style="background:#FEE2E2;color:#EF4444;padding:4px 10px;border-radius:8px;font-size:0.82rem;font-weight:700;">
                            Save <?php echo $discount; ?>%
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Short Description -->
                <?php if (!empty($product['short_description'])): ?>
                <p style="color:#6B7280;font-size:0.92rem;line-height:1.7;margin-bottom:20px;">
                    <?php echo htmlspecialchars($product['short_description']); ?>
                </p>
                <?php endif; ?>

                <!-- Stock Status -->
                <div class="mb-4">
                    <?php if ($product['stock'] > 0): ?>
                        <span style="color:#10B981;font-weight:600;font-size:0.88rem;">
                            <i class="fas fa-check-circle me-1"></i>In Stock
                            <?php if ($product['stock'] < 10): ?>
                                <span style="color:#F59E0B;"> — Only <?php echo $product['stock']; ?> left!</span>
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span style="color:#EF4444;font-weight:600;font-size:0.88rem;">
                            <i class="fas fa-times-circle me-1"></i>Out of Stock
                        </span>
                    <?php endif; ?>
                    <span class="ms-3" style="font-size:0.8rem;color:#9CA3AF;">SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
                </div>

                <!-- Add to Cart -->
                <?php if ($product['stock'] > 0): ?>
                <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
                    <div class="qty-control">
                        <button type="button" class="qty-btn qty-minus">−</button>
                        <input type="number" id="qty" class="qty-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>
                    <button id="addToCartBtn" class="btn btn-primary px-5 py-2"
                            data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                    <a href="<?php echo SITE_URL; ?>/cart.php" class="btn btn-outline-secondary py-2">
                        <i class="fas fa-shopping-bag"></i>
                    </a>
                </div>
                <?php else: ?>
                <button class="btn btn-secondary px-5 py-2 mb-4" disabled>Out of Stock</button>
                <?php endif; ?>

                <!-- Shipping Info -->
                <div style="background:#F0F7FF;border-radius:12px;padding:16px;margin-bottom:24px;">
                    <div class="d-flex gap-4 flex-wrap">
                        <div style="display:flex;align-items:center;gap:8px;font-size:0.85rem;">
                            <i class="fas fa-shipping-fast text-primary"></i>
                            <span>Free shipping over $100</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;font-size:0.85rem;">
                            <i class="fas fa-undo text-primary"></i>
                            <span>30-day returns</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;font-size:0.85rem;">
                            <i class="fas fa-shield-alt text-primary"></i>
                            <span>2-year warranty</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Tabs: Description & Specs ── -->
    <div class="row mt-5">
        <div class="col-12">
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;overflow:hidden;">
                <ul class="nav nav-tabs px-4 pt-3" id="productTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description">Description</button>
                    </li>
                    <?php if (!empty($specs)): ?>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs">Specifications</button>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content p-4">
                    <div class="tab-pane fade show active" id="description">
                        <p style="line-height:1.8;color:#374151;font-size:0.95rem;">
                            <?php echo nl2br(htmlspecialchars($product['description'] ?? $product['short_description'] ?? 'No description available.')); ?>
                        </p>
                    </div>
                    <?php if (!empty($specs)): ?>
                    <div class="tab-pane fade" id="specs">
                        <table class="specs-table">
                            <?php foreach ($specs as $key => $value): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($key); ?></td>
                                <td><?php echo htmlspecialchars($value); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Related Products ── -->
    <?php if (!empty($related)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 style="font-size:1.4rem;font-weight:800;margin-bottom:24px;">Related Products</h3>
            <div class="row g-4">
                <?php foreach ($related as $rp):
                    $rp_price = getProductPrice($rp);
                    $rp_sale = $rp['sale_price'] && $rp['sale_price'] > 0;
                    $rp_disc = $rp_sale ? round((1 - $rp['sale_price']/$rp['price'])*100) : 0;
                ?>
                <div class="col-6 col-md-3">
                    <div class="product-card">
                        <div class="product-image-wrap">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($rp['image']); ?>"
                                 onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                                 alt="<?php echo htmlspecialchars($rp['name']); ?>"
                                 loading="lazy">
                            <?php if ($rp_sale): ?>
                            <div class="product-badges"><span class="badge-sale">-<?php echo $rp_disc; ?>%</span></div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-brand"><?php echo htmlspecialchars($rp['brand']); ?></div>
                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $rp['slug']; ?>" class="product-name">
                                <?php echo htmlspecialchars($rp['name']); ?>
                            </a>
                            <div class="product-price-wrap">
                                <span class="price-current"><?php echo formatPrice($rp_price); ?></span>
                                <?php if ($rp_sale): ?>
                                    <span class="price-original"><?php echo formatPrice($rp['price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="product-footer">
                            <button class="btn-add-cart" data-product-id="<?php echo $rp['id']; ?>">
                                <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
</section>

<?php include 'includes/footer.php'; ?>
