<?php
/**
 * Checkout Page
 */

require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();
$cart_items = getCartItems($_SESSION['user_id']);

if (empty($cart_items)) {
    setFlash('warning', 'Your cart is empty. Please add products before checking out.');
    redirect(SITE_URL . '/products.php');
}

$subtotal = getCartTotal($_SESSION['user_id']);
$shipping = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
$total = $subtotal + $shipping;

$errors = [];

// ── Handle POST ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['shipping_name'] ?? '');
    $email   = trim($_POST['shipping_email'] ?? '');
    $phone   = trim($_POST['shipping_phone'] ?? '');
    $address = trim($_POST['shipping_address'] ?? '');
    $city    = trim($_POST['shipping_city'] ?? '');
    $country = trim($_POST['shipping_country'] ?? '');
    $payment = $_POST['payment_method'] ?? 'cod';
    $notes   = trim($_POST['notes'] ?? '');

    if (empty($name))    $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($phone))   $errors[] = 'Phone number is required.';
    if (empty($address)) $errors[] = 'Shipping address is required.';
    if (empty($city))    $errors[] = 'City is required.';
    if (empty($country)) $errors[] = 'Country is required.';

    if (empty($errors)) {
        $shipping_data = compact('name','email','phone','address','city','country');
        $order_id = createOrder($_SESSION['user_id'], $shipping_data, $payment, $notes);
        if ($order_id) {
            setFlash('success', 'Your order has been placed successfully! Thank you for shopping with ElectroHub.');
            redirect(SITE_URL . '/order-confirmation.php?id=' . $order_id);
        } else {
            $errors[] = 'Failed to place order. Please try again.';
        }
    }
}

$page_title = 'Checkout';
?>
<?php include 'includes/header.php'; ?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">

<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/cart.php">Cart</a></li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol>
    </div>
</div>

<section class="section-padding-sm" style="background:#F8FAFC;">
<div class="container">
    <h2 style="font-size:1.8rem;font-weight:800;margin-bottom:28px;">
        <i class="fas fa-lock me-2 text-primary"></i>Secure Checkout
    </h2>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" data-validate>
    <div class="row g-4">
        <!-- ── Left Column: Forms ── -->
        <div class="col-lg-7">

            <!-- Shipping Info -->
            <div class="checkout-section">
                <h5><i class="fas fa-map-marker-alt me-2 text-primary"></i>Shipping Information</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="shipping_name" class="form-control" required
                               value="<?php echo htmlspecialchars($_POST['shipping_name'] ?? $user['full_name']); ?>"
                               placeholder="John Doe">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="shipping_email" class="form-control" required
                               value="<?php echo htmlspecialchars($_POST['shipping_email'] ?? $user['email']); ?>"
                               placeholder="john@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="shipping_phone" class="form-control" required
                               value="<?php echo htmlspecialchars($_POST['shipping_phone'] ?? $user['phone']); ?>"
                               placeholder="+94 71 234 5678">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City *</label>
                        <input type="text" name="shipping_city" class="form-control" required
                               value="<?php echo htmlspecialchars($_POST['shipping_city'] ?? $user['city']); ?>"
                               placeholder="Colombo">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Street Address *</label>
                        <input type="text" name="shipping_address" class="form-control" required
                               value="<?php echo htmlspecialchars($_POST['shipping_address'] ?? $user['address']); ?>"
                               placeholder="123 Main Street, Apt 4B">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country *</label>
                        <select name="shipping_country" class="form-select" required>
                            <?php
                            $countries = ['Sri Lanka','India','Australia','United Kingdom','United States','Canada','Singapore','Germany','France','UAE'];
                            $selected_country = $_POST['shipping_country'] ?? $user['country'] ?? 'Sri Lanka';
                            foreach ($countries as $c):
                            ?>
                            <option value="<?php echo $c; ?>" <?php echo $selected_country==$c?'selected':''; ?>>
                                <?php echo $c; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Order Notes <span style="color:#9CA3AF;font-weight:400;">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Any special delivery instructions?"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="checkout-section">
                <h5><i class="fas fa-credit-card me-2 text-primary"></i>Payment Method</h5>
                <div class="row g-3">
                    <?php
                    $payment_methods = [
                        'cod'    => ['icon'=>'fas fa-money-bill-wave','label'=>'Cash on Delivery','desc'=>'Pay when your order arrives'],
                        'card'   => ['icon'=>'fas fa-credit-card','label'=>'Credit / Debit Card','desc'=>'Visa, Mastercard, Amex'],
                        'paypal' => ['icon'=>'fab fa-paypal','label'=>'PayPal','desc'=>'Pay securely with PayPal'],
                        'bank'   => ['icon'=>'fas fa-university','label'=>'Bank Transfer','desc'=>'Direct bank transfer'],
                    ];
                    foreach ($payment_methods as $key => $method):
                    ?>
                    <div class="col-md-6">
                        <label style="cursor:pointer;display:block;">
                            <input type="radio" name="payment_method" value="<?php echo $key; ?>"
                                   <?php echo (($_POST['payment_method'] ?? 'cod') == $key) ? 'checked' : ''; ?>
                                   style="display:none;" class="payment-radio">
                            <div class="payment-option" style="border:2px solid #E5E7EB;border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;transition:all 0.2s;">
                                <i class="<?php echo $method['icon']; ?> fa-lg" style="color:#0066FF;width:24px;text-align:center;"></i>
                                <div>
                                    <div style="font-weight:700;font-size:0.9rem;"><?php echo $method['label']; ?></div>
                                    <div style="font-size:0.78rem;color:#6B7280;"><?php echo $method['desc']; ?></div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ── Right Column: Order Summary ── -->
        <div class="col-lg-5">
            <div class="cart-summary-card" style="position:sticky;top:90px;">
                <h5 style="font-weight:800;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #E5E7EB;">
                    Order Summary
                </h5>

                <!-- Cart Items -->
                <?php foreach ($cart_items as $item):
                    $item_price = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                    $item_total = $item_price * $item['quantity'];
                ?>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="position:relative;flex-shrink:0;">
                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($item['image']); ?>"
                             onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                             style="width:52px;height:52px;object-fit:contain;background:#F8FAFC;border-radius:10px;border:1px solid #E5E7EB;padding:4px;"
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <span style="position:absolute;top:-6px;right:-6px;background:#0066FF;color:white;font-size:0.65rem;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <?php echo $item['quantity']; ?>
                        </span>
                    </div>
                    <div style="flex-grow:1;">
                        <div style="font-size:0.85rem;font-weight:600;color:#1F2937;">
                            <?php echo htmlspecialchars(truncate($item['name'], 45)); ?>
                        </div>
                        <div style="font-size:0.78rem;color:#6B7280;"><?php echo formatPrice($item_price); ?> × <?php echo $item['quantity']; ?></div>
                    </div>
                    <div style="font-weight:700;font-size:0.9rem;"><?php echo formatPrice($item_total); ?></div>
                </div>
                <?php endforeach; ?>

                <hr style="border-color:#E5E7EB;margin:16px 0;">

                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#6B7280;font-size:0.9rem;">Subtotal</span>
                    <span style="font-weight:600;"><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span style="color:#6B7280;font-size:0.9rem;">Shipping</span>
                    <?php if ($shipping == 0): ?>
                        <span style="color:#10B981;font-weight:600;">FREE</span>
                    <?php else: ?>
                        <span style="font-weight:600;"><?php echo formatPrice($shipping); ?></span>
                    <?php endif; ?>
                </div>

                <hr style="border-color:#E5E7EB;margin:16px 0;">

                <div class="d-flex justify-content-between mb-4">
                    <span style="font-weight:800;font-size:1rem;">Total</span>
                    <span style="font-weight:800;font-size:1.3rem;color:#0066FF;"><?php echo formatPrice($total); ?></span>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3" style="font-size:1rem;font-weight:700;">
                    <i class="fas fa-check-circle me-2"></i>Place Order (<?php echo formatPrice($total); ?>)
                </button>

                <div class="text-center mt-3">
                    <small style="color:#9CA3AF;">
                        <i class="fas fa-shield-alt me-1 text-success"></i>
                        Your data is protected with 256-bit SSL encryption
                    </small>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>
</section>

<script>
// Highlight selected payment option
document.querySelectorAll('.payment-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.style.borderColor = '#E5E7EB';
            opt.style.background = 'white';
        });
        if (this.checked) {
            const opt = this.nextElementSibling;
            opt.style.borderColor = '#0066FF';
            opt.style.background = '#F0F7FF';
        }
    });
    // Trigger on load for checked
    if (radio.checked) radio.dispatchEvent(new Event('change'));
});
</script>

<?php include 'includes/footer.php'; ?>
