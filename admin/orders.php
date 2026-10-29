<?php
/**
 * Admin - Orders Management
 */

$page_title = 'Manage Orders';
require_once 'header.php';

$conn = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id  = (int)($_POST['order_id'] ?? 0);
    $status    = sanitize($_POST['status'] ?? '');
    $pay_status = sanitize($_POST['payment_status'] ?? '');
    $valid_statuses = ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'];
    $valid_pay      = ['pending','paid','failed','refunded'];

    if ($order_id && in_array($status, $valid_statuses) && in_array($pay_status, $valid_pay)) {
        $conn->query("UPDATE orders SET status='$status', payment_status='$pay_status' WHERE id=$order_id");
        $conn->query("UPDATE payments SET status='$pay_status' WHERE order_id=$order_id");
        setFlash('success', 'Order #' . $order_id . ' updated successfully.');
    }
    redirect(SITE_URL . '/admin/orders.php?' . http_build_query(array_filter($_GET)));
}

// Filters
$status_filter  = sanitize($_GET['status'] ?? '');
$search         = sanitize($_GET['search'] ?? '');
$date_from      = sanitize($_GET['date_from'] ?? '');
$date_to        = sanitize($_GET['date_to'] ?? '');
$where_parts    = ['1'];

if ($status_filter) $where_parts[] = "o.status = '$status_filter'";
if ($search)        $where_parts[] = "(o.order_number LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
if ($date_from)     $where_parts[] = "DATE(o.created_at) >= '$date_from'";
if ($date_to)       $where_parts[] = "DATE(o.created_at) <= '$date_to'";
$where = implode(' AND ', $where_parts);

$page     = max(1, (int)($_GET['page'] ?? 1));
$per      = 15;
$offset   = ($page - 1) * $per;

$total = $conn->query("SELECT COUNT(*) as c FROM orders o JOIN users u ON o.user_id=u.id WHERE $where")->fetch_assoc()['c'];
$total_pages = ceil($total / $per);

$result = $conn->query("
    SELECT o.*, u.full_name, u.email, u.phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE $where
    ORDER BY o.created_at DESC
    LIMIT $per OFFSET $offset
");
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Status counts
$counts = [];
foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s) {
    $counts[$s] = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='$s'")->fetch_assoc()['c'];
}
$counts['all'] = array_sum($counts);
?>

<!-- Quick Status Tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">
    <?php
    $tabs = [''=>'All','pending'=>'Pending','confirmed'=>'Confirmed','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'];
    $tab_colors = [''=>'#6B7280','pending'=>'#F59E0B','confirmed'=>'#0EA5E9','processing'=>'#0066FF','shipped'=>'#7C3AED','delivered'=>'#10B981','cancelled'=>'#EF4444'];
    foreach ($tabs as $val => $label):
        $count = $val ? ($counts[$val] ?? 0) : $counts['all'];
        $active = $status_filter === $val;
    ?>
    <a href="?status=<?php echo $val; ?>&search=<?php echo urlencode($search); ?>"
       style="padding:7px 16px;border-radius:20px;font-size:0.82rem;font-weight:700;text-decoration:none;
              border:2px solid <?php echo $active ? $tab_colors[$val] : '#E5E7EB'; ?>;
              background:<?php echo $active ? $tab_colors[$val] . '18' : 'white'; ?>;
              color:<?php echo $active ? $tab_colors[$val] : '#6B7280'; ?>;">
        <?php echo $label; ?>
        <span style="margin-left:5px;background:<?php echo $active ? $tab_colors[$val] : '#E5E7EB'; ?>;color:<?php echo $active ? 'white' : '#374151'; ?>;border-radius:10px;padding:1px 7px;font-size:0.72rem;">
            <?php echo $count; ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="admin-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
        <div class="col-md-4">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#6B7280;">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Order #, customer name or email..."
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#6B7280;">From Date</label>
            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#6B7280;">To Date</label>
            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="orders.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <span style="font-size:0.88rem;color:#6B7280;">
            <strong><?php echo $total; ?></strong> orders found
        </span>
        <a href="reports.php" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-chart-bar me-1"></i>View Reports
        </a>
    </div>

    <?php if (empty($orders)): ?>
    <div style="text-align:center;padding:60px;color:#9CA3AF;">
        <i class="fas fa-receipt" style="font-size:3rem;margin-bottom:12px;display:block;"></i>
        No orders found.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order):
                $item_count = $conn->query("SELECT SUM(quantity) as c FROM order_items WHERE order_id={$order['id']}")->fetch_assoc()['c'];
            ?>
            <tr>
                <td>
                    <a href="view-order.php?id=<?php echo $order['id']; ?>"
                       style="font-weight:800;color:#0066FF;text-decoration:none;font-size:0.88rem;">
                        #<?php echo htmlspecialchars($order['order_number']); ?>
                    </a>
                </td>
                <td>
                    <div style="font-weight:700;font-size:0.85rem;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                    <div style="font-size:0.75rem;color:#9CA3AF;"><?php echo htmlspecialchars($order['email']); ?></div>
                </td>
                <td style="font-size:0.85rem;color:#6B7280;"><?php echo $item_count; ?> item(s)</td>
                <td style="font-weight:800;color:#0066FF;"><?php echo 'Rs.'.($order['total_amount']); ?></td>
                <td>
                    <div style="font-size:0.78rem;text-transform:uppercase;font-weight:600;color:#6B7280;">
                        <?php echo htmlspecialchars($order['payment_method']); ?>
                    </div>
                    <?php
                    $pay_colors = ['pending'=>'warning','paid'=>'success','failed'=>'danger','refunded'=>'secondary'];
                    echo "<span class='badge bg-{$pay_colors[$order['payment_status']]}'>" . ucfirst($order['payment_status']) . "</span>";
                    ?>
                </td>
                <td><?php echo getOrderStatusBadge($order['status']); ?></td>
                <td style="font-size:0.82rem;color:#6B7280;">
                    <?php echo date('M d, Y', strtotime($order['created_at'])); ?><br>
                    <span style="font-size:0.75rem;"><?php echo date('h:i A', strtotime($order['created_at'])); ?></span>
                </td>
                <td>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <a href="view-order.php?id=<?php echo $order['id']; ?>"
                           class="btn btn-sm btn-outline-primary" title="View & Update">
                            <i class="fas fa-eye"></i>
                        </a>
                        <!-- Quick status change -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="payment_status" value="<?php echo $order['payment_status']; ?>">
                            <select name="status" class="form-select form-select-sm"
                                    style="width:110px;font-size:0.78rem;"
                                    onchange="if(confirm('Update status?')) this.form.submit()">
                                <?php
                                $statuses = ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'];
                                foreach ($statuses as $s):
                                ?>
                                <option value="<?php echo $s; ?>" <?php echo $order['status']==$s?'selected':''; ?>>
                                    <?php echo ucfirst($s); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
        <?php if ($page > 1): ?>
        <a href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page-1; ?>"
           style="padding:6px 12px;border:1px solid #E5E7EB;border-radius:8px;text-decoration:none;color:#374151;font-size:0.85rem;">
            ← Prev
        </a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
        <a href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border:1px solid <?php echo $i==$page?'#0066FF':'#E5E7EB'; ?>;background:<?php echo $i==$page?'#0066FF':'white'; ?>;color:<?php echo $i==$page?'white':'#374151'; ?>;border-radius:8px;font-size:0.85rem;font-weight:600;text-decoration:none;">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
        <a href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page+1; ?>"
           style="padding:6px 12px;border:1px solid #E5E7EB;border-radius:8px;text-decoration:none;color:#374151;font-size:0.85rem;">
            Next →
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
