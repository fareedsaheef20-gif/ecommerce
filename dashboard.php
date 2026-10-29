<?php
/**
 * User Dashboard
 */

require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();
$orders = getUserOrders($_SESSION['user_id']);
$cart_count = getCartCount($_SESSION['user_id']);

// Stats
$total_orders  = count($orders);
$delivered     = array_filter($orders, fn($o) => $o['status'] === 'delivered');
$pending_orders= array_filter($orders, fn($o) => in_array($o['status'], ['pending','confirmed','processing','shipped']));
$total_spent   = array_sum(array_column($delivered, 'total_amount'));

$recent_orders = array_slice($orders, 0, 5);
$page_title = 'My Dashboard';
?>
<?php include 'includes/header.php'; ?>

<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>
</div>

<section class="section-padding-sm" style="background:#F8FAFC;">
<div class="container">
    <!-- Welcome Banner -->
    <div style="background:linear-gradient(135deg,#0A0E1A,#0D1B40);border-radius:20px;padding:32px;color:white;margin-bottom:32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;">
        <div>
            <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;">Welcome back</div>
            <h2 style="font-size:1.7rem;font-weight:800;margin:0;"><?php echo htmlspecialchars($user['full_name']); ?> 👋</h2>
            <div style="font-size:0.88rem;color:rgba(255,255,255,0.6);margin-top:4px;">
                <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($user['email']); ?>
            </div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="profile.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-user-edit me-1"></i>Edit Profile
            </a>
            <a href="products.php" class="btn btn-primary btn-sm">
                <i class="fas fa-shopping-bag me-1"></i>Shop Now
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#EFF6FF,#DBEAFE);">
                    <i class="fas fa-box" style="color:#0066FF;"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#F0FDF4,#DCFCE7);">
                    <i class="fas fa-check-circle" style="color:#10B981;"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo count($delivered); ?></div>
                    <div class="stat-label">Delivered</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#FFFBEB,#FEF3C7);">
                    <i class="fas fa-clock" style="color:#F59E0B;"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo count($pending_orders); ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#F5F3FF,#EDE9FE);">
            <i class="fas fa-money-bill-wave" style="color:#7C3AED;"></i>
        </div>
        <div class="stat-info">
            <div class="stat-number" style="font-size:1.3rem;">
                Rs. <?php echo number_format($total_spent, 2); ?>
            </div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>
</div>
    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid #E5E7EB;display:flex;justify-content:space-between;align-items:center;">
                    <h5 style="font-weight:800;margin:0;"><i class="fas fa-receipt me-2 text-primary"></i>Recent Orders</h5>
                    <a href="orders.php" style="font-size:0.85rem;color:#0066FF;font-weight:600;text-decoration:none;">View All →</a>
                </div>

                <?php if (empty($recent_orders)): ?>
                <div class="empty-state" style="padding:40px;">
                    <i class="fas fa-box-open"></i>
                    <h4>No orders yet</h4>
                    <p>Start shopping to see your orders here.</p>
                    <a href="products.php" class="btn btn-primary mt-2">Shop Now</a>
                </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="w-100" style="border-collapse:collapse;">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="padding:12px 16px;font-size:0.78rem;text-transform:uppercase;color:#6B7280;font-weight:600;letter-spacing:0.05em;">Order #</th>
                            <th style="padding:12px 16px;font-size:0.78rem;text-transform:uppercase;color:#6B7280;font-weight:600;letter-spacing:0.05em;">Date</th>
                            <th style="padding:12px 16px;font-size:0.78rem;text-transform:uppercase;color:#6B7280;font-weight:600;letter-spacing:0.05em;">Total</th>
                            <th style="padding:12px 16px;font-size:0.78rem;text-transform:uppercase;color:#6B7280;font-weight:600;letter-spacing:0.05em;">Status</th>
                            <th style="padding:12px 16px;font-size:0.78rem;text-transform:uppercase;color:#6B7280;font-weight:600;letter-spacing:0.05em;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr style="border-bottom:1px solid #F3F4F6;">
                            <td style="padding:14px 16px;font-weight:700;font-size:0.88rem;">
                                #<?php echo htmlspecialchars($order['order_number']); ?>
                            </td>
                            <td style="padding:14px 16px;font-size:0.85rem;color:#6B7280;">
                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </td>
                            <td style="padding:14px 16px;font-weight:700;color:#0066FF;">
                                <?php echo "Rs.".($order['total_amount']); ?>
                            </td>
                            <td style="padding:14px 16px;"><?php echo getOrderStatusBadge($order['status']); ?></td>
                            <td style="padding:14px 16px;">
                                <a href="order-confirmation.php?id=<?php echo $order['id']; ?>"
                                   style="font-size:0.82rem;color:#0066FF;font-weight:600;text-decoration:none;">
                                    View <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar: Quick Links & Account Info -->
        <div class="col-lg-4">
            <!-- Quick Links -->
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:24px;margin-bottom:20px;">
                <h6 style="font-weight:800;margin-bottom:16px;"><i class="fas fa-th me-2 text-primary"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="products.php" class="btn btn-outline-primary text-start">
                        <i class="fas fa-shopping-bag me-2"></i>Browse Products
                    </a>
                    <a href="cart.php" class="btn btn-outline-primary text-start position-relative">
                        <i class="fas fa-shopping-cart me-2"></i>My Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="badge bg-danger" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="orders.php" class="btn btn-outline-primary text-start">
                        <i class="fas fa-box me-2"></i>All Orders
                    </a>
                    <a href="profile.php" class="btn btn-outline-primary text-start">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </a>
                    <a href="logout.php" class="btn btn-outline-danger text-start">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>

            <!-- Account Info -->
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:24px;">
                <h6 style="font-weight:800;margin-bottom:16px;"><i class="fas fa-user me-2 text-primary"></i>Account Info</h6>
                <ul style="list-style:none;padding:0;margin:0;font-size:0.87rem;">
                    <li style="padding:8px 0;border-bottom:1px solid #F3F4F6;display:flex;gap:10px;">
                        <i class="fas fa-user text-muted" style="width:16px;margin-top:2px;"></i>
                        <span style="color:#6B7280;">Name:</span>
                        <span style="font-weight:600;margin-left:auto;"><?php echo htmlspecialchars($user['full_name']); ?></span>
                    </li>
                    <li style="padding:8px 0;border-bottom:1px solid #F3F4F6;display:flex;gap:10px;">
                        <i class="fas fa-at text-muted" style="width:16px;margin-top:2px;"></i>
                        <span style="color:#6B7280;">Username:</span>
                        <span style="font-weight:600;margin-left:auto;">@<?php echo htmlspecialchars($user['username']); ?></span>
                    </li>
                    <li style="padding:8px 0;border-bottom:1px solid #F3F4F6;display:flex;gap:10px;align-items:flex-start;">
                        <i class="fas fa-envelope text-muted" style="width:16px;margin-top:2px;"></i>
                        <span style="color:#6B7280;">Email:</span>
                        <span style="font-weight:600;margin-left:auto;word-break:break-all;text-align:right;"><?php echo htmlspecialchars($user['email']); ?></span>
                    </li>
                    <?php if ($user['phone']): ?>
                    <li style="padding:8px 0;border-bottom:1px solid #F3F4F6;display:flex;gap:10px;">
                        <i class="fas fa-phone text-muted" style="width:16px;margin-top:2px;"></i>
                        <span style="color:#6B7280;">Phone:</span>
                        <span style="font-weight:600;margin-left:auto;"><?php echo htmlspecialchars($user['phone']); ?></span>
                    </li>
                    <?php endif; ?>
                    <li style="padding:8px 0;display:flex;gap:10px;">
                        <i class="fas fa-calendar text-muted" style="width:16px;margin-top:2px;"></i>
                        <span style="color:#6B7280;">Member since:</span>
                        <span style="font-weight:600;margin-left:auto;"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
