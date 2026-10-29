<?php
/**
 * My Orders Page
 */

require_once 'includes/functions.php';
requireLogin();

$orders = getUserOrders($_SESSION['user_id']);
$page_title = 'My Orders';
?>
<?php include 'includes/header.php'; ?>

<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">My Orders</li>
        </ol>
    </div>
</div>

<section class="section-padding-sm" style="background:#F8FAFC;">
<div class="container">
    <h2 style="font-size:1.8rem;font-weight:800;margin-bottom:28px;">
        <i class="fas fa-box me-2 text-primary"></i>My Orders
    </h2>

    <?php if (empty($orders)): ?>
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h4>No orders yet</h4>
        <p>You haven't placed any orders. Start shopping!</p>
        <a href="products.php" class="btn btn-primary mt-3">
            <i class="fas fa-shopping-bag me-2"></i>Shop Now
        </a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:16px;">
        <?php foreach ($orders as $order):
            $items = getOrderItems($order['id']);
            $first_item = $items[0] ?? null;
        ?>
        <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;overflow:hidden;">
            <!-- Order Header -->
            <div style="padding:16px 24px;background:#F8FAFC;border-bottom:1px solid #E5E7EB;display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;">
                <div style="display:flex;flex-wrap:wrap;gap:24px;">
                    <div>
                        <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;margin-bottom:2px;">Order Number</div>
                        <div style="font-weight:800;font-size:0.92rem;">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;margin-bottom:2px;">Date Placed</div>
                        <div style="font-weight:600;font-size:0.88rem;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;margin-bottom:2px;">Total</div>
                        <div style="font-weight:800;font-size:0.92rem;color:#0066FF;"><?php echo "Rs.".($order['total_amount']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;margin-bottom:2px;">Status</div>
                        <?php echo getOrderStatusBadge($order['status']); ?>
                    </div>
                </div>
                <a href="order-confirmation.php?id=<?php echo $order['id']; ?>"
                   class="btn btn-outline-primary btn-sm">
                    View Details <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <!-- Order Items Preview -->
            <div style="padding:16px 24px;">
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <?php foreach (array_slice($items, 0, 3) as $item): ?>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>"
                             onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                             style="width:48px;height:48px;object-fit:contain;background:#F8FAFC;border-radius:8px;border:1px solid #E5E7EB;padding:3px;"
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <div>
                            <div style="font-size:0.82rem;font-weight:600;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </div>
                            <div style="font-size:0.75rem;color:#6B7280;">Qty: <?php echo $item['quantity']; ?> × <?php echo "Rs.".($item['unit_price']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($items) > 3): ?>
                    <div style="font-size:0.82rem;color:#6B7280;font-weight:600;">
                        +<?php echo count($items) - 3; ?> more item(s)
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</section>

<?php include 'includes/footer.php'; ?>
