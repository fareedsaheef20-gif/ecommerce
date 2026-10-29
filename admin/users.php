<?php
/**
 * Admin - Users Management
 */

$page_title = 'Manage Users';
require_once 'header.php';

$conn = getDB();

// Toggle user active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    $conn->query("UPDATE users SET is_active = 1 - is_active WHERE id=$uid");
    setFlash('success', 'User status updated.');
    redirect(SITE_URL . '/admin/users.php');
}

$search = sanitize($_GET['search'] ?? '');
$where  = "1";
if ($search) $where .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%' OR username LIKE '%$search%')";

$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 15;
$offset = ($page - 1) * $per;

$total = $conn->query("SELECT COUNT(*) as c FROM users WHERE $where")->fetch_assoc()['c'];
$total_pages = ceil($total / $per);

$result = $conn->query("
    SELECT u.*,
        (SELECT COUNT(*) FROM orders WHERE user_id=u.id) as order_count,
        (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id=u.id AND status != 'cancelled') as total_spent
    FROM users u
    WHERE $where
    ORDER BY u.created_at DESC
    LIMIT $per OFFSET $offset
");
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$total_users  = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$active_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_active=1")->fetch_assoc()['c'];
$new_today    = $conn->query("SELECT COUNT(*) as c FROM users WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
?>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="admin-card" style="display:flex;align-items:center;gap:16px;">
            <div style="width:48px;height:48px;background:#EFF6FF;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#0066FF;font-size:1.1rem;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;"><?php echo $total_users; ?></div>
                <div style="font-size:0.78rem;color:#6B7280;">Total Users</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card" style="display:flex;align-items:center;gap:16px;">
            <div style="width:48px;height:48px;background:#F0FDF4;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#10B981;font-size:1.1rem;">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;"><?php echo $active_users; ?></div>
                <div style="font-size:0.78rem;color:#6B7280;">Active Users</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card" style="display:flex;align-items:center;gap:16px;">
            <div style="width:48px;height:48px;background:#FFFBEB;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#F59E0B;font-size:1.1rem;">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;"><?php echo $new_today; ?></div>
                <div style="font-size:0.78rem;color:#6B7280;">New Today</div>
            </div>
        </div>
    </div>
</div>

<!-- Search Filter -->
<div class="admin-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-8">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#6B7280;">Search Users</label>
            <input type="text" name="search" class="form-control" placeholder="Name, email or username..."
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
        <div class="col-md-2">
            <a href="users.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <span style="font-size:0.88rem;color:#6B7280;">
            Showing <strong><?php echo count($users); ?></strong> of <strong><?php echo $total; ?></strong> users
        </span>
    </div>

    <?php if (empty($users)): ?>
    <div style="text-align:center;padding:60px;color:#9CA3AF;">
        <i class="fas fa-users" style="font-size:3rem;margin-bottom:12px;display:block;"></i>
        No users found.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Username</th>
                <th>Phone</th>
                <th style="text-align:center;">Orders</th>
                <th style="text-align:right;">Total Spent</th>
                <th style="text-align:center;">Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:0.9rem;flex-shrink:0;">
                            <?php echo strtoupper(substr($u['full_name'],0,1)); ?>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:0.88rem;"><?php echo htmlspecialchars($u['full_name']); ?></div>
                            <div style="font-size:0.75rem;color:#9CA3AF;"><?php echo htmlspecialchars($u['email']); ?></div>
                        </div>
                    </div>
                </td>
                <td style="font-size:0.85rem;color:#6B7280;">@<?php echo htmlspecialchars($u['username']); ?></td>
                <td style="font-size:0.85rem;color:#6B7280;"><?php echo htmlspecialchars($u['phone'] ?: '—'); ?></td>
                <td style="text-align:center;">
                    <a href="orders.php?search=<?php echo urlencode($u['email']); ?>"
                       style="font-weight:700;color:#0066FF;text-decoration:none;font-size:0.88rem;">
                        <?php echo $u['order_count']; ?>
                    </a>
                </td>
                <td style="text-align:right;font-weight:700;color:#0066FF;font-size:0.88rem;">
                    <?php echo "Rs.".($u['total_spent']); ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($u['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Suspended</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:0.82rem;color:#6B7280;"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="orders.php?search=<?php echo urlencode($u['email']); ?>"
                           class="btn btn-sm btn-outline-primary" title="View Orders">
                            <i class="fas fa-receipt"></i>
                        </a>
                        <a href="?toggle=<?php echo $u['id']; ?>"
                           class="btn btn-sm <?php echo $u['is_active']?'btn-outline-warning':'btn-outline-success'; ?>"
                           title="<?php echo $u['is_active']?'Suspend':'Activate'; ?>"
                           onclick="return confirm('<?php echo $u['is_active']?'Suspend':'Activate'; ?> this user?')">
                            <i class="fas fa-<?php echo $u['is_active']?'ban':'check'; ?>"></i>
                        </a>
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
        <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
        <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border:1px solid <?php echo $i==$page?'#0066FF':'#E5E7EB'; ?>;background:<?php echo $i==$page?'#0066FF':'white'; ?>;color:<?php echo $i==$page?'white':'#374151'; ?>;border-radius:8px;font-size:0.85rem;font-weight:600;text-decoration:none;">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
