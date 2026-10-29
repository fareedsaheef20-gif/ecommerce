<?php
/**
 * Admin - View Single Order
 */

$page_title = 'Order Details';
require_once 'header.php';

$conn = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect(SITE_URL . '/admin/orders.php');

$order = getOrderById($id);
if (!$order) { setFlash('danger','Order not found.'); redirect(SITE_URL . '/admin/orders.php'); }

$order_items = getOrderItems($id);
$user_result = $conn->query("SELECT * FROM users WHERE id={$order['user_id']} LIMIT 1");
$user = $user_result ? $user_result->fetch_assoc() : null;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status     = sanitize($_POST['status'] ?? '');
    $pay_status = sanitize($_POST['payment_status'] ?? '');
    $valid_s    = ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'];
    $valid_p    = ['pending','paid','failed','refunded'];
    if (in_array($status, $valid_s) && in_array($pay_status, $valid_p)) {
        $conn->query("UPDATE orders SET status='$status', payment_status='$pay_status' WHERE id=$id");
        $conn->query("UPDATE payments SET status='$pay_status' WHERE order_id=$id");
        setFlash('success', 'Order status updated successfully.');
        redirect(SITE_URL . '/admin/view-order.php?id=' . $id);
    }
}

// Tracking steps
$statuses = ['pending','confirmed','processing','shipped','delivered'];
$current_step = array_search($order['status'], $statuses);
if ($current_step === false) $current_step = -1;
?>

<div style="margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <a href="orders.php" style="color:#6B7280;font-size:0.88rem;text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i>Back to Orders
    </a>
    <div style="display:flex;gap:8px;">
        <span style="font-size:0.88rem;color:#6B7280;">Order #<strong><?php echo $order['order_number']; ?></strong></span>
        <?php echo getOrderStatusBadge($order['status']); ?>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Order Details -->
    <div class="col-lg-8">

        <!-- Tracking -->
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:20px;"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Order Tracking</h6>
            <div class="order-tracking-steps">
                <?php
                $steps = [
                    ['fas fa-clipboard-check','Placed'],
                    ['fas fa-check-circle','Confirmed'],
                    ['fas fa-cog','Processing'],
                    ['fas fa-truck','Shipped'],
                    ['fas fa-home','Delivered'],
                ];
                foreach ($steps as $i => $step):
                    $cls = ($i < $current_step) ? 'done' : ($i == $current_step ? 'active' : '');
                ?>
                <div class="tracking-step <?php echo $cls; ?>">
                    <div class="tracking-step-icon">
                        <?php if ($i < $current_step): ?>
                            <i class="fas fa-check"></i>
                        <?php else: ?>
                            <i class="<?php echo $step[0]; ?>"></i>
                        <?php endif; ?>
                    </div>
                    <div class="tracking-step-label"><?php echo $step[1]; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Items -->
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:20px;"><i class="fas fa-box me-2 text-primary"></i>Order Items</h6>
            <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Unit Price</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>"
                                     onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                                     style="width:48px;height:48px;object-fit:contain;background:#F8FAFC;border-radius:8px;border:1px solid #E5E7EB;padding:3px;">
                                <span style="font-weight:700;font-size:0.88rem;"><?php echo htmlspecialchars($item['product_name']); ?></span>
                            </div>
                        </td>
                        <td style="text-align:center;font-weight:700;"><?php echo $item['quantity']; ?></td>
                        <td style="text-align:right;font-weight:600;"><?php echo formatPrice($item['unit_price']); ?></td>
                        <td style="text-align:right;font-weight:800;color:#0066FF;"><?php echo formatPrice($item['total_price']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="background:#F8FAFC;">
                    <tr>
                        <td colspan="3" style="text-align:right;font-weight:600;padding:12px 16px;color:#6B7280;">Subtotal</td>
                        <td style="text-align:right;font-weight:700;padding:12px 16px;"><?php echo formatPrice($order['total_amount'] - $order['shipping_amount']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:right;font-weight:600;padding:8px 16px;color:#6B7280;">Shipping</td>
                        <td style="text-align:right;font-weight:700;padding:8px 16px;">
                            <?php echo $order['shipping_amount'] > 0 ? formatPrice($order['shipping_amount']) : '<span style="color:#10B981;">FREE</span>'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:right;font-weight:800;padding:12px 16px;font-size:1rem;">Total</td>
                        <td style="text-align:right;font-weight:800;padding:12px 16px;font-size:1.1rem;color:#0066FF;"><?php echo formatPrice($order['total_amount']); ?></td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>

        <!-- Update Status Form -->
        <div class="admin-card">
            <h6 style="font-weight:800;margin-bottom:20px;"><i class="fas fa-edit me-2 text-primary"></i>Update Order Status</h6>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Order Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $order['status']==$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <?php foreach (['pending','paid','failed','refunded'] as $p): ?>
                            <option value="<?php echo $p; ?>" <?php echo $order['payment_status']==$p?'selected':''; ?>><?php echo ucfirst($p); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Customer & Shipping -->
    <div class="col-lg-4">

        <!-- Customer Info -->
        <?php if ($user): ?>
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:16px;"><i class="fas fa-user me-2 text-primary"></i>Customer</h6>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:44px;height:44px;background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;">
                    <?php echo strtoupper(substr($user['full_name'],0,1)); ?>
                </div>
                <div>
                    <div style="font-weight:700;font-size:0.92rem;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <div style="font-size:0.8rem;color:#6B7280;">@<?php echo htmlspecialchars($user['username']); ?></div>
                </div>
            </div>
            <div style="font-size:0.85rem;color:#374151;">
                <div style="margin-bottom:6px;"><i class="fas fa-envelope me-2 text-muted"></i><?php echo htmlspecialchars($user['email']); ?></div>
                <div><i class="fas fa-phone me-2 text-muted"></i><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></div>
            </div>
            <a href="users.php?search=<?php echo urlencode($user['email']); ?>" class="btn btn-outline-primary btn-sm w-100 mt-3">
                View Customer Profile
            </a>
        </div>
        <?php endif; ?>

        <!-- Shipping Address -->
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:16px;"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Shipping Address</h6>
            <div style="font-size:0.88rem;line-height:1.8;color:#374151;">
                <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong><br>
                <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_country']); ?><br>
                <i class="fas fa-phone me-1 text-muted"></i><?php echo htmlspecialchars($order['shipping_phone']); ?><br>
                <i class="fas fa-envelope me-1 text-muted"></i><?php echo htmlspecialchars($order['shipping_email']); ?>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:16px;"><i class="fas fa-credit-card me-2 text-primary"></i>Payment Details</h6>
            <div style="font-size:0.88rem;">
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#6B7280;">Method</span>
                    <span style="font-weight:700;text-transform:uppercase;"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#6B7280;">Payment Status</span>
                    <?php
                    $pay_colors = ['pending'=>'warning','paid'=>'success','failed'=>'danger','refunded'=>'secondary'];
                    echo "<span class='badge bg-{$pay_colors[$order['payment_status']]}'>" . ucfirst($order['payment_status']) . "</span>";
                    ?>
                </div>
                <div class="d-flex justify-content-between">
                    <span style="color:#6B7280;">Order Date</span>
                    <span style="font-weight:600;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <?php if ($order['notes']): ?>
        <div class="admin-card">
            <h6 style="font-weight:800;margin-bottom:12px;"><i class="fas fa-sticky-note me-2 text-primary"></i>Order Notes</h6>
            <p style="font-size:0.88rem;color:#374151;line-height:1.6;margin:0;background:#FFF7ED;border-radius:8px;padding:12px;">
                <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
