<?php
/**
 * Home Page
 * ElectroHub E-Commerce
 */

require_once 'includes/functions.php';
$page_title = 'Home - Premium Electronics Store';
$featured = getFeaturedProducts(8);
$categories = getCategories();
$new_arrivals = getProducts(4, 0, 'p.id > 15');
?>
<?php include 'includes/header.php'; ?>

<!-- Add site URL meta for JS -->
<meta name="site-url" content="<?php echo SITE_URL; ?>">

<!-- ── Hero Section ── -->
<section class="hero-section">
    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-center text-center">
            <div class="col-lg-12 col-md-12">
                <h1 class="hero-title">
                    Discover the Latest <br><span class="highlight">Tech</span>
                    Innovations
                </h1><br>
            
                <div class="hero-cta ">
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="<?php echo SITE_URL; ?>/products.php?featured=1" class="btn btn-outline-light btn-lg px-4">
                        View Deals
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-number">500+</div>
                        <div class="hero-stat-label">Products</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number">50K+</div>
                        <div class="hero-stat-label">Customers</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number">4.9★</div>
                        <div class="hero-stat-label">Rating</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Feature Strips ── -->
<div style="background:white;border-bottom:1px solid #E5E7EB;">
    <div class="container">
        <div class="row g-0 text-center">
            <?php
            $features = [
                ['fas fa-shipping-fast','Free Delivery','On orders over $100'],
                ['fas fa-undo','30-Day Returns','Hassle free returns'],
                ['fas fa-shield-alt','2-Year Warranty','On all products'],
                ['fas fa-headset','24/7 Support','Always here to help'],
            ];
            foreach ($features as $f): ?>
            <div class="col-6 col-md-3" style="padding:20px;border-right:1px solid #E5E7EB;">
                <div style="display:flex;align-items:center;justify-content:center;gap:12px;">
                    <i class="<?php echo $f[0]; ?>" style="font-size:1.4rem;color:#0066FF;"></i>
                    <div style="text-align:left;">
                        <div style="font-weight:700;font-size:0.88rem;"><?php echo $f[1]; ?></div>
                        <div style="font-size:0.75rem;color:#6B7280;"><?php echo $f[2]; ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Categories ── -->
<section class="section-padding-sm" style="background:#F8FAFC;">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">Browse By Type</div>
            <h2 class="section-title">Shop by Category</h2>
        </div>
        <div class="row g-3">
            <?php foreach ($categories as $cat):
                $conn = getDB();
                $cid = (int)$cat['id'];
                $cnt = $conn->query("SELECT COUNT(*) as c FROM products WHERE category_id=$cid AND is_active=1")->fetch_assoc()['c'];
            ?>
            <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
                <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $cat['slug']; ?>" class="category-card">
                    <div class="category-icon">
                        <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                    </div>
                    <div class="category-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                    <div class="category-count"><?php echo $cnt; ?> Products</div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Featured Products ── -->
<section class="section-padding" style="background:white;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <div class="section-tag">Handpicked for You</div>
                <h2 class="section-title mb-0">Featured Products</h2>
            </div>
            <a href="<?php echo SITE_URL; ?>/products.php?featured=1" class="btn btn-outline-primary d-none d-md-inline-flex">
                View All <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $product):
                $price = getProductPrice($product);
                $on_sale = $product['sale_price'] && $product['sale_price'] > 0;
                $discount = $on_sale ? round((1 - $product['sale_price']/$product['price'])*100) : 0;
            ?>
            <div class="col-6 col-md-4 col-lg-3">
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
                            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>" class="action-btn" title="View Details">
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
                            <span class="price-current"><?php echo formatPrice($price); ?></span>
                            <?php if ($on_sale): ?>
                                <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
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
        <div class="text-center mt-4 d-md-none">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">View All Products</a>
        </div>
    </div>
</section>

<!-- ── Promo Banners ── -->
<section class="section-padding-sm" style="background:#F8FAFC;">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="promo-banner" style="background:linear-gradient(135deg,#0A0E1A,#0D1B40);">
                    <div style="position:relative;z-index:1;">
                        <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:#00D4FF;font-weight:700;margin-bottom:8px;">Limited Offer</div>
                        <h3 style="font-size:1.6rem;font-weight:800;color:white;margin-bottom:8px;">Up to 30% Off<br>Smartphones</h3>
                        <p style="color:rgba(255,255,255,0.6);font-size:0.88rem;margin-bottom:20px;">Latest flagship phones from Apple, Samsung & Google</p>
                        <a href="<?php echo SITE_URL; ?>/products.php?category=mobile-phones" class="btn btn-primary">Shop Phones</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="promo-banner" style="background:linear-gradient(135deg,#003380,#0052CC);">
                    <div style="position:relative;z-index:1;">
                        <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:#00D4FF;font-weight:700;margin-bottom:8px;">Best Sellers</div>
                        <h3 style="font-size:1.6rem;font-weight:800;color:white;margin-bottom:8px;">Premium Laptops<br>For Work & Play</h3>
                        <p style="color:rgba(255,255,255,0.6);font-size:0.88rem;margin-bottom:20px;">MacBooks, XPS, ROG and more</p>
                        <a href="<?php echo SITE_URL; ?>/products.php?category=laptops" class="btn btn-outline-light">Shop Laptops</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── New Arrivals ── -->
<?php if (!empty($new_arrivals)): ?>
<section class="section-padding" style="background:white;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <div class="section-tag">Just In</div>
                <h2 class="section-title mb-0">New Arrivals</h2>
            </div>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-outline-primary d-none d-md-inline-flex">
                View All <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="row g-4">
            <?php foreach ($new_arrivals as $product):
                $price = getProductPrice($product);
                $on_sale = $product['sale_price'] && $product['sale_price'] > 0;
                $discount = $on_sale ? round((1 - $product['sale_price']/$product['price'])*100) : 0;
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card">
                    <div class="product-image-wrap">
                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                             onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             loading="lazy">
                        <div class="product-badges">
                            <span class="badge-new">NEW</span>
                            <?php if ($on_sale): ?><span class="badge-sale">-<?php echo $discount; ?>%</span><?php endif; ?>
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
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-footer">
                        <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Testimonials ── -->
<section class="section-padding-sm" style="background:#F8FAFC;">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">Customer Reviews</div>
            <h2 class="section-title">What Our Customers Say</h2>
        </div>
        <div class="row g-4">
            <?php
            $reviews = [
                ['name'=>'AGA.Nafa','rating'=>5,'text'=>'Amazing products and super fast delivery! My MacBook Pro arrived in perfect condition. Definitely shopping here again.','location'=>'Colombo'],
                ['name'=>'usman.k','rating'=>5,'text'=>'Best electronics store online! Got my Sony headphones at a great price. The quality is outstanding.','location'=>'Kandy'],
                ['name'=>'alakshan.r','rating'=>4,'text'=>'Great selection of products. Customer support was very helpful when I had questions about my order.','location'=>'Galle'],
            ];
            foreach ($reviews as $r): ?>
            <div class="col-md-4">
                <div style="background:white;border-radius:16px;padding:24px;border:1px solid #E5E7EB;height:100%;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <div style="width:44px;height:44px;background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1rem;">
                            <?php echo $r['name'][0]; ?>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:0.9rem;"><?php echo $r['name']; ?></div>
                            <div style="font-size:0.75rem;color:#6B7280;"><?php echo $r['location']; ?></div>
                        </div>
                        <div class="ms-auto"><?php echo renderStars($r['rating']); ?></div>
                    </div>
                    <p style="font-size:0.88rem;color:#374151;line-height:1.7;margin:0;">"<?php echo $r['text']; ?>"</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
