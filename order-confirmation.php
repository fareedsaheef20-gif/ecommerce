<?php
/**
 * Order Confirmation Page
 */

require_once 'includes/functions.php';
requireLogin();

$order_id = (int)($_GET['id'] ?? 0);
$order = getOrderById($order_id, $_SESSION['user_id']);

if (!$order) {
    setFlash('danger', 'Order not found.');
    redirect(SITE_URL . '/dashboard.php');
}

$order_items = getOrderItems($order_id);
$page_title = 'Order Confirmed - #' . $order['order_number'];

// Determine tracking steps
$statuses = ['pending','confirmed','processing','shipped','delivered'];
$current_step = array_search($order['status'], $statuses);
?>
<?php include 'includes/header.php'; ?>

<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/orders.php">My Orders</a></li>
            <li class="breadcrumb-item active">Order #<?php echo $order['order_number']; ?></li>
        </ol>
    </div>
</div>

<section class="section-padding-sm" style="background:#F8FAFC;">
<div class="container">

    <!-- Success Banner -->
    <div style="background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:20px;padding:40px;text-align:center;color:white;margin-bottom:32px;">
        <div style="width:70px;height:70px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.8rem;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 style="font-size:1.8rem;font-weight:800;margin-bottom:8px;">Order Confirmed!</h2>
        <p style="opacity:0.85;margin-bottom:4px;">Thank you for shopping with ElectroHub</p>
        <div style="font-size:1.1rem;font-weight:700;opacity:0.9;">Order #<?php echo $order['order_number']; ?></div>
        <div style="font-size:0.85rem;opacity:0.7;margin-top:6px;">
            Placed on <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Order Details -->
        <div class="col-lg-8">

            <!-- Tracking -->
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:28px;margin-bottom:24px;">
                <h5 style="font-weight:800;margin-bottom:24px;"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Order Tracking</h5>
                <div class="order-tracking-steps">
                    <?php
                    $steps = [
                        ['icon'=>'fas fa-clipboard-check','label'=>'Order Placed'],
                        ['icon'=>'fas fa-check-circle','label'=>'Confirmed'],
                        ['icon'=>'fas fa-cog','label'=>'Processing'],
                        ['icon'=>'fas fa-truck','label'=>'Shipped'],
                        ['icon'=>'fas fa-home','label'=>'Delivered'],
                    ];
                    foreach ($steps as $i => $step):
                        $cls = $i < $current_step ? 'done' : ($i == $current_step ? 'active' : '');
                    ?>
                    <div class="tracking-step <?php echo $cls; ?>">
                        <div class="tracking-step-icon">
                            <?php if ($i < $current_step): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                <i class="<?php echo $step['icon']; ?>"></i>
                            <?php endif; ?>
                        </div>
                        <div class="tracking-step-label"><?php echo $step['label']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-2">
                    <span class="badge" style="background:#F0F7FF;color:#0066FF;padding:6px 16px;border-radius:20px;font-size:0.85rem;font-weight:600;">
                        Current Status: <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>

            <!-- Order Items -->
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:28px;">
                <h5 style="font-weight:800;margin-bottom:20px;"><i class="fas fa-box me-2 text-primary"></i>Order Items</h5>
                <?php foreach ($order_items as $item): ?>
                <div class="d-flex align-items-center gap-3 py-3" style="border-bottom:1px solid #F3F4F6;">
                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>"
                         onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                         style="width:60px;height:60px;object-fit:contain;background:#F8FAFC;border-radius:10px;border:1px solid #E5E7EB;padding:4px;"
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <div style="flex-grow:1;">
                        <div style="font-weight:700;font-size:0.92rem;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div style="font-size:0.82rem;color:#6B7280;">
                            <?php echo formatPrice($item['unit_price']); ?> × <?php echo $item['quantity']; ?>
                        </div>
                    </div>
                    <div style="font-weight:800;color:#0066FF;"><?php echo formatPrice($item['total_price']); ?></div>
                </div>
                <?php endforeach; ?>

                <!-- Totals -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:#6B7280;">Subtotal</span>
                        <span style="font-weight:600;"><?php echo formatPrice($order['total_amount'] - $order['shipping_amount']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span style="color:#6B7280;">Shipping</span>
                        <?php if ($order['shipping_amount'] == 0): ?>
                            <span style="color:#10B981;font-weight:600;">FREE</span>
                        <?php else: ?>
                            <span style="font-weight:600;"><?php echo formatPrice($order['shipping_amount']); ?></span>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span style="font-weight:800;font-size:1rem;">Total</span>
                        <span style="font-weight:800;font-size:1.3rem;color:#0066FF;"><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Shipping Address -->
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:24px;margin-bottom:20px;">
                <h6 style="font-weight:800;margin-bottom:16px;"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Shipping To</h6>
                <p style="margin:0;font-size:0.9rem;line-height:1.7;color:#374151;">
                    <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong><br>
                    <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                    <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_country']); ?><br>
                    <i class="fas fa-phone me-1 text-muted"></i><?php echo htmlspecialchars($order['shipping_phone']); ?><br>
                    <i class="fas fa-envelope me-1 text-muted"></i><?php echo htmlspecialchars($order['shipping_email']); ?>
                </p>
            </div>

            <!-- Payment Info -->
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:24px;margin-bottom:20px;">
                <h6 style="font-weight:800;margin-bottom:16px;"><i class="fas fa-credit-card me-2 text-primary"></i>Payment</h6>
                <div style="font-size:0.9rem;color:#374151;">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="color:#6B7280;">Method</span>
                        <span style="font-weight:600;text-transform:uppercase;"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color:#6B7280;">Status</span>
                        <?php echo getOrderStatusBadge($order['payment_status']); ?>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-box me-2"></i>View All Orders
                </a>
                <a href="products.php" class="btn btn-outline-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
