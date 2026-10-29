<?php
/**
 * Shopping Cart Page
 */

require_once 'includes/functions.php';
$page_title = 'Shopping Cart';

$cart_items = [];
$subtotal = 0;

if (isLoggedIn()) {
    $cart_items = getCartItems($_SESSION['user_id']);
    $subtotal = getCartTotal($_SESSION['user_id']);
}

$shipping = ($subtotal > 0 && $subtotal < FREE_SHIPPING_THRESHOLD) ? SHIPPING_COST : 0;
$total = $subtotal + $shipping;
?>
<?php include 'includes/header.php'; ?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">

<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item active">Shopping Cart</li>
        </ol>
    </div>
</div>

<section class="section-padding-sm" style="background:#F8FAFC;">
<div class="container">
    <h2 style="font-size:1.8rem;font-weight:800;margin-bottom:28px;">
        <i class="fas fa-shopping-cart me-2 text-primary"></i>Your Cart
        <?php if (!empty($cart_items)): ?>
            <span style="font-size:1rem;font-weight:500;color:#6B7280;">(<?php echo count($cart_items); ?> items)</span>
        <?php endif; ?>
    </h2>

    <?php if (!isLoggedIn()): ?>
    <!-- Not Logged In -->
    <div class="empty-state">
        <i class="fas fa-lock"></i>
        <h4>Login to view your cart</h4>
        <p>Please login or register to add items to your cart.</p>
        <a href="login.php" class="btn btn-primary mt-3 me-2">Login</a>
        <a href="register.php" class="btn btn-outline-primary mt-3">Register</a>
    </div>

    <?php elseif (empty($cart_items)): ?>
    <!-- Empty Cart -->
    <div class="empty-state">
        <i class="fas fa-shopping-cart"></i>
        <h4>Your cart is empty</h4>
        <p>Looks like you haven't added any products yet.</p>
        <a href="products.php" class="btn btn-primary mt-3">
            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
        </a>
    </div>

    <?php else: ?>
    <!-- Cart with Items -->
    <div class="row g-4">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;overflow:hidden;">
                <table class="cart-table w-100">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="padding:16px 20px;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.06em;color:#6B7280;">Product</th>
                            <th style="padding:16px;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.06em;color:#6B7280;text-align:center;">Qty</th>
                            <th style="padding:16px;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.06em;color:#6B7280;text-align:right;">Total</th>
                            <th style="padding:16px;width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item):
                            $item_price = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                            $item_total = $item_price * $item['quantity'];
                        ?>
                        <tr class="cart-row" data-product-id="<?php echo $item['product_id']; ?>">
                            <td style="padding:16px 20px;">
                                <div class="d-flex align-items-center gap-3">
                                    <a href="product.php?slug=<?php echo htmlspecialchars($item['slug']); ?>">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($item['image']); ?>"
                                             onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                                             class="cart-product-img" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </a>
                                    <div>
                                        <a href="product.php?slug=<?php echo htmlspecialchars($item['slug']); ?>"
                                           style="font-weight:700;font-size:0.9rem;color:#1F2937;text-decoration:none;">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                        <div style="font-size:0.8rem;color:#6B7280;margin-top:3px;">
                                            <?php echo formatPrice($item_price); ?> each
                                        </div>
                                        <?php if ($item['stock'] < $item['quantity']): ?>
                                        <div style="font-size:0.78rem;color:#EF4444;margin-top:3px;">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Only <?php echo $item['stock']; ?> available
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:16px;text-align:center;">
                                <div class="qty-control mx-auto">
                                    <button type="button" class="qty-btn qty-decrease" data-product-id="<?php echo $item['product_id']; ?>">−</button>
                                    <input type="number" class="qty-input qty-field" value="<?php echo $item['quantity']; ?>"
                                           min="1" max="<?php echo $item['stock']; ?>"
                                           data-product-id="<?php echo $item['product_id']; ?>"
                                           style="width:52px;">
                                    <button type="button" class="qty-btn qty-increase" data-product-id="<?php echo $item['product_id']; ?>">+</button>
                                </div>
                            </td>
                            <td style="padding:16px;text-align:right;">
                                <span class="item-total" data-product-id="<?php echo $item['product_id']; ?>"
                                      style="font-weight:800;font-size:1rem;color:#0066FF;">
                                    <?php echo formatPrice($item_total); ?>
                                </span>
                            </td>
                            <td style="padding:16px;">
                                <button class="remove-cart-item" data-product-id="<?php echo $item['product_id']; ?>"
                                        style="background:none;border:none;color:#9CA3AF;cursor:pointer;padding:4px;border-radius:6px;transition:all 0.2s;"
                                        onmouseover="this.style.color='#EF4444'" onmouseout="this.style.color='#9CA3AF'"
                                        title="Remove item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="products.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="cart-summary-card">
                <h5 style="font-weight:800;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #E5E7EB;">
                    Order Summary
                </h5>

                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#6B7280;font-size:0.9rem;">Subtotal (<?php echo count($cart_items); ?> items)</span>
                    <span style="font-weight:600;"><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span style="color:#6B7280;font-size:0.9rem;">Shipping</span>
                    <?php if ($shipping == 0): ?>
                        <span style="color:#10B981;font-weight:600;font-size:0.9rem;"><i class="fas fa-check me-1"></i>FREE</span>
                    <?php else: ?>
                        <span style="font-weight:600;"><?php echo formatPrice($shipping); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
                <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:10px;padding:12px;margin-bottom:16px;font-size:0.82rem;color:#92400E;">
                    <i class="fas fa-shipping-fast me-1"></i>
                    Add <strong><?php echo formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal); ?></strong> more for FREE shipping!
                </div>
                <?php endif; ?>

                <hr style="border-color:#E5E7EB;">

                <div class="d-flex justify-content-between mb-4">
                    <span style="font-weight:800;font-size:1rem;">Total</span>
                    <span class="cart-total-amount" style="font-weight:800;font-size:1.3rem;color:#0066FF;">
                        <?php echo formatPrice($total); ?>
                    </span>
                </div>

                <a href="checkout.php" class="btn btn-primary w-100 py-3" style="font-size:1rem;font-weight:700;">
                    <i class="fas fa-lock me-2"></i>Proceed to Checkout
                </a>

                <!-- Trust Badges -->
                <div class="text-center mt-3">
                    <div style="font-size:0.75rem;color:#9CA3AF;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
                        <span><i class="fas fa-shield-alt me-1 text-success"></i>Secure Checkout</span>
                        <span><i class="fas fa-lock me-1 text-success"></i>SSL Encrypted</span>
                    </div>
                    <div class="mt-2">
                        <i class="fab fa-cc-visa fa-lg text-muted me-1"></i>
                        <i class="fab fa-cc-mastercard fa-lg text-muted me-1"></i>
                        <i class="fab fa-cc-paypal fa-lg text-muted me-1"></i>
                        <i class="fas fa-money-bill-wave fa-lg text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
</section>

<?php include 'includes/footer.php'; ?>
