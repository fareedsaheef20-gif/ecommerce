<?php
/**
 * Admin Dashboard
 */

$page_title = 'Dashboard';
require_once 'header.php';

$conn = getDB();

// Key metrics
$total_users    = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_active=1")->fetch_assoc()['c'];
$total_products = $conn->query("SELECT COUNT(*) as c FROM products WHERE is_active=1")->fetch_assoc()['c'];
$total_orders   = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$total_revenue  = $conn->query("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE status != 'cancelled'")->fetch_assoc()['r'];
$pending_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$low_stock      = $conn->query("SELECT COUNT(*) as c FROM products WHERE stock <= 5 AND is_active=1")->fetch_assoc()['c'];
$today_orders   = $conn->query("SELECT COUNT(*) as c FROM orders WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$today_revenue  = $conn->query("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'")->fetch_assoc()['r'];

// Recent orders
$recent = $conn->query("
    SELECT o.*, u.full_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC LIMIT 8
");
$recent_orders = $recent ? $recent->fetch_all(MYSQLI_ASSOC) : [];

// Top products
$top_products = $conn->query("
    SELECT oi.product_name as name, oi.product_image as image, 
           SUM(oi.quantity) as sold, SUM(oi.total_price) as revenue
    FROM order_items oi
    GROUP BY oi.product_name, oi.product_image ORDER BY sold DESC LIMIT 5
");
$top_products = $top_products ? $top_products->fetch_all(MYSQLI_ASSOC) : [];

// Monthly revenue for chart (last 6 months)
$monthly = $conn->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') as month, SUM(total_amount) as revenue, COUNT(*) as orders
    FROM orders WHERE status != 'cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY created_at ASC
");
$monthly_data = $monthly ? $monthly->fetch_all(MYSQLI_ASSOC) : [];
?>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <?php
    $stats = [
        ['Total Revenue','Rs.'.number_format($total_revenue),'fas fa-money-bill','#0066FF','#EFF6FF'],
        ['Total Orders',$total_orders,'fas fa-receipt','#10B981','#F0FDF4'],
        ['Total Users',$total_users,'fas fa-users','#7C3AED','#F5F3FF'],
        ['Products',$total_products,'fas fa-box','#F59E0B','#FFFBEB'],
        ['Pending Orders',$pending_orders,'fas fa-clock','#EF4444','#FEF2F2'],
        ['Low Stock',$low_stock,'fas fa-exclamation-triangle','#F97316','#FFF7ED'],
        ["Today's Orders",$today_orders,'fas fa-calendar-day','#0EA5E9','#F0F9FF'],
        ["Today's Revenue",'Rs. ' . number_format($today_revenue, 2),'fas fa-chart-line','#06B6D4','#ECFEFF'],
    ];
    foreach ($stats as $s):
    ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="admin-card" style="display:flex;align-items:center;gap:16px;padding:20px;">
            <div style="width:48px;height:48px;background:<?php echo $s[4]; ?>;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:<?php echo $s[3]; ?>;flex-shrink:0;">
                <i class="<?php echo $s[2]; ?>"></i>
            </div>
            <div>
                <div style="font-family:'Syne',sans-serif;font-size:1.35rem;font-weight:800;line-height:1;color:#1F2937;"><?php echo $s[1]; ?></div>
                <div style="font-size:0.78rem;color:#6B7280;margin-top:3px;font-weight:500;"><?php echo $s[0]; ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-7">
        <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h6 style="font-weight:800;margin:0;">Revenue Overview (Last 6 Months)</h6>
            </div>
            <?php if (!empty($monthly_data)): ?>
            <canvas id="revenueChart" height="120"></canvas>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('revenueChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
                        datasets: [{
                            label: 'Revenue (Rs.)',
                            data: <?php echo json_encode(array_map(fn($m) => round($m['revenue'], 2), $monthly_data)); ?>,
                            backgroundColor: 'rgba(0,102,255,0.15)',
                            borderColor: '#0066FF',
                            borderWidth: 2,
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#F3F4F6' }, ticks: { callback: v => 'Rs.' + v.toLocaleString() } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
            </script>
            <?php else: ?>
            <div style="text-align:center;padding:40px;color:#9CA3AF;">No revenue data yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-5">
        <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h6 style="font-weight:800;margin:0;">Top Selling Products</h6>
                <a href="products.php" style="font-size:0.82rem;color:#0066FF;font-weight:600;text-decoration:none;">View All →</a>
            </div>
            <?php if (empty($top_products)): ?>
            <p style="color:#9CA3AF;text-align:center;padding:20px 0;font-size:0.88rem;">No sales data yet.</p>
            <?php else: ?>
            <?php foreach ($top_products as $p): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #F3F4F6;">
                <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($p['image']); ?>"
                     onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                     style="width:44px;height:44px;object-fit:contain;background:#F8FAFC;border-radius:8px;border:1px solid #E5E7EB;padding:4px;">
                <div style="flex-grow:1;">
                    <div style="font-weight:700;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">
                        <?php echo htmlspecialchars($p['name']); ?>
                    </div>
                    <div style="font-size:0.75rem;color:#6B7280;"><?php echo $p['sold']; ?> sold</div>
                </div>
                <div style="font-weight:800;font-size:0.9rem;color:#0066FF;"><?php echo 'Rs.'.($p['revenue']); ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h6 style="font-weight:800;margin:0;"><i class="fas fa-receipt me-2 text-primary"></i>Recent Orders</h6>
        <a href="orders.php" style="font-size:0.82rem;color:#0066FF;font-weight:600;text-decoration:none;">View All →</a>
    </div>
    <?php if (empty($recent_orders)): ?>
    <p style="text-align:center;color:#9CA3AF;padding:20px;">No orders yet.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_orders as $order): ?>
            <tr>
                <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                <td>
                    <div style="font-weight:600;font-size:0.85rem;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                    <div style="font-size:0.75rem;color:#9CA3AF;"><?php echo htmlspecialchars($order['email']); ?></div>
                </td>
                <td style="font-weight:800;color:#0066FF;"><?php echo 'Rs.'.($order['total_amount']); ?></td>
                <td><span style="font-size:0.8rem;text-transform:uppercase;font-weight:600;color:#6B7280;"><?php echo htmlspecialchars($order['payment_method']); ?></span></td>
                <td><?php echo getOrderStatusBadge($order['status']); ?></td>
                <td style="font-size:0.82rem;color:#6B7280;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                <td>
                    <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                        View
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php require_once 'footer.php'; ?>
