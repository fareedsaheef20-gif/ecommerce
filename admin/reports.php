<?php
/**
 * Admin - Sales Report
 */

$page_title = 'Sales Report';
require_once 'header.php';

$conn = getDB();

// Date range filter
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to   = $_GET['date_to']   ?? date('Y-m-d');
$df = sanitize($date_from);
$dt = sanitize($date_to);

// Summary metrics for the range
$summary = $conn->query("
    SELECT
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount),0) as total_revenue,
        COALESCE(AVG(total_amount),0) as avg_order,
        COALESCE(SUM(shipping_amount),0) as total_shipping
    FROM orders
    WHERE status NOT IN ('cancelled','refunded')
    AND DATE(created_at) BETWEEN '$df' AND '$dt'
")->fetch_assoc();

$cancelled = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='cancelled' AND DATE(created_at) BETWEEN '$df' AND '$dt'")->fetch_assoc()['c'];

// Daily breakdown
$daily = $conn->query("
    SELECT DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total_amount) as revenue
    FROM orders
    WHERE status NOT IN ('cancelled','refunded')
    AND DATE(created_at) BETWEEN '$df' AND '$dt'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$daily_data = $daily ? $daily->fetch_all(MYSQLI_ASSOC) : [];

// Top categories
$top_cats = $conn->query("
    SELECT c.name, SUM(oi.quantity) as sold, SUM(oi.total_price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status NOT IN ('cancelled','refunded')
    AND DATE(o.created_at) BETWEEN '$df' AND '$dt'
    GROUP BY c.id ORDER BY revenue DESC LIMIT 6
");
$top_cats = $top_cats ? $top_cats->fetch_all(MYSQLI_ASSOC) : [];

// Top products
$top_products = $conn->query("
    SELECT p.name, p.brand, p.image, SUM(oi.quantity) as sold, SUM(oi.total_price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status NOT IN ('cancelled','refunded')
    AND DATE(o.created_at) BETWEEN '$df' AND '$dt'
    GROUP BY p.id ORDER BY revenue DESC LIMIT 10
");
$top_products = $top_products ? $top_products->fetch_all(MYSQLI_ASSOC) : [];

// Payment method breakdown
$payment_methods = $conn->query("
    SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total
    FROM orders
    WHERE status NOT IN ('cancelled','refunded')
    AND DATE(created_at) BETWEEN '$df' AND '$dt'
    GROUP BY payment_method
");
$payment_methods = $payment_methods ? $payment_methods->fetch_all(MYSQLI_ASSOC) : [];
?>

<!-- Date Range Filter -->
<div class="admin-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#6B7280;">From Date</label>
            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#6B7280;">To Date</label>
            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <a href="?date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary btn-sm">Today</a>
            <a href="?date_from=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary btn-sm">7 Days</a>
            <a href="?date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary btn-sm">This Month</a>
            <a href="?date_from=<?php echo date('Y-01-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary btn-sm">This Year</a>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="admin-card" style="text-align:center;padding:24px 16px;">
            <div style="font-size:1.7rem;font-weight:800;color:#0066FF;"><?php echo 'Rs. ' . number_format($summary['total_revenue'], 2); ?></div>
            <div style="font-size:0.78rem;color:#6B7280;margin-top:4px;">Total Revenue</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="admin-card" style="text-align:center;padding:24px 16px;">
            <div style="font-size:1.7rem;font-weight:800;color:#10B981;"><?php echo $summary['total_orders']; ?></div>
            <div style="font-size:0.78rem;color:#6B7280;margin-top:4px;">Total Orders</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="admin-card" style="text-align:center;padding:24px 16px;">
            <div style="font-size:1.7rem;font-weight:800;color:#7C3AED;"><?php echo 'Rs. ' . number_format($summary['avg_order'], 2); ?></div>
            <div style="font-size:0.78rem;color:#6B7280;margin-top:4px;">Average Order Value</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="admin-card" style="text-align:center;padding:24px 16px;">
            <div style="font-size:1.7rem;font-weight:800;color:#EF4444;"><?php echo $cancelled; ?></div>
            <div style="font-size:0.78rem;color:#6B7280;margin-top:4px;">Cancelled Orders</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="admin-card">
            <h6 style="font-weight:800;margin-bottom:20px;">Daily Revenue (<?php echo date('M d', strtotime($date_from)); ?> – <?php echo date('M d, Y', strtotime($date_to)); ?>)</h6>
            <?php if (!empty($daily_data)): ?>
            <canvas id="dailyChart" height="130"></canvas>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                new Chart(document.getElementById('dailyChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(fn($d) => date('M d', strtotime($d['date'])), $daily_data)); ?>,
                        datasets: [{
                            label: 'Revenue',
                            data: <?php echo json_encode(array_map(fn($d) => round($d['revenue'], 2), $daily_data)); ?>,
                            borderColor: '#0066FF',
                            backgroundColor: 'rgba(0,102,255,0.08)',
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#0066FF',
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#F3F4F6' }, ticks: { callback: v => 'Rs. ' + v.toLocaleString() } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
            </script>
            <?php else: ?>
            <div style="text-align:center;padding:60px;color:#9CA3AF;">No data for selected period.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="col-lg-4">
        <div class="admin-card">
            <h6 style="font-weight:800;margin-bottom:20px;">Payment Methods</h6>
            <?php if (!empty($payment_methods)): ?>
            <?php
            $method_total = array_sum(array_column($payment_methods, 'total'));
            $method_icons = ['cod'=>'fas fa-money-bill-wave','card'=>'fas fa-credit-card','paypal'=>'fab fa-paypal','bank'=>'fas fa-university'];
            $method_colors = ['cod'=>'#10B981','card'=>'#0066FF','paypal'=>'#0EA5E9','bank'=>'#7C3AED'];
            foreach ($payment_methods as $pm):
                $pct = $method_total > 0 ? round(($pm['total'] / $method_total) * 100) : 0;
                $color = $method_colors[$pm['payment_method']] ?? '#6B7280';
            ?>
            <div style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="<?php echo $method_icons[$pm['payment_method']] ?? 'fas fa-money-bill'; ?>" style="color:<?php echo $color; ?>;width:16px;text-align:center;"></i>
                        <span style="font-size:0.85rem;font-weight:600;text-transform:uppercase;"><?php echo htmlspecialchars($pm['payment_method']); ?></span>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:800;font-size:0.88rem;"><?php echo 'Rs. ' . number_format($pm['total'], 2); ?></div>
                        <div style="font-size:0.75rem;color:#9CA3AF;"><?php echo $pm['count']; ?> orders</div>
                    </div>
                </div>
                <div style="height:6px;background:#F3F4F6;border-radius:6px;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $pct; ?>%;background:<?php echo $color; ?>;border-radius:6px;transition:width 0.5s;"></div>
                </div>
                <div style="font-size:0.72rem;color:#9CA3AF;text-align:right;margin-top:2px;"><?php echo $pct; ?>%</div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style="text-align:center;color:#9CA3AF;padding:20px;">No payment data.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Top Products -->
    <div class="col-lg-7">
        <div class="admin-card">
            <h6 style="font-weight:800;margin-bottom:20px;">Top Selling Products</h6>
            <?php if (!empty($top_products)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th style="text-align:center;">Sold</th>
                        <th style="text-align:right;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_products as $i => $p): ?>
                    <tr>
                        <td style="font-weight:800;color:<?php echo $i==0?'#F59E0B':($i==1?'#9CA3AF':($i==2?'#92400E':'#374151')); ?>;">
                            #<?php echo $i+1; ?>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($p['image']); ?>"
                                     onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                                     style="width:38px;height:38px;object-fit:contain;background:#F8FAFC;border-radius:6px;border:1px solid #E5E7EB;padding:2px;">
                                <div>
                                    <div style="font-weight:700;font-size:0.84rem;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div style="font-size:0.75rem;color:#9CA3AF;"><?php echo htmlspecialchars($p['brand']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align:center;font-weight:700;"><?php echo $p['sold']; ?></td>
                        <td style="text-align:right;font-weight:800;color:#0066FF;"><?php echo 'Rs. ' . number_format($p['revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align:center;color:#9CA3AF;padding:20px;">No product sales in this period.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Categories -->
    <div class="col-lg-5">
        <div class="admin-card">
            <h6 style="font-weight:800;margin-bottom:20px;">Revenue by Category</h6>
            <?php if (!empty($top_cats)):
                $cat_total = array_sum(array_column($top_cats, 'revenue'));
                $cat_colors = ['#0066FF','#10B981','#7C3AED','#F59E0B','#EF4444','#0EA5E9'];
                foreach ($top_cats as $i => $cat):
                    $pct = $cat_total > 0 ? round(($cat['revenue'] / $cat_total) * 100) : 0;
                    $color = $cat_colors[$i % count($cat_colors)];
            ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                    <span style="font-size:0.85rem;font-weight:600;"><?php echo htmlspecialchars($cat['name']); ?></span>
                    <span style="font-weight:800;font-size:0.85rem;color:<?php echo $color; ?>;"><?php echo 'Rs. ' . number_format($cat['revenue'], 2  ); ?></span>
                </div>
                <div style="height:8px;background:#F3F4F6;border-radius:8px;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $pct; ?>%;background:<?php echo $color; ?>;border-radius:8px;"></div>
                </div>
                <div style="font-size:0.72rem;color:#9CA3AF;margin-top:2px;"><?php echo $cat['sold']; ?> units · <?php echo $pct; ?>%</div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style="text-align:center;color:#9CA3AF;padding:20px;">No category data.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php require_once 'footer.php'; ?>
