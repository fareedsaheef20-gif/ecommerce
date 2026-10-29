<?php
/**
 * Admin Management - Only Main Admin can access
 * Create, activate/deactivate, delete sub-admins
 * Default password for new admins: Admin@1234
 */

$page_title = 'Manage Admins';
require_once 'header.php';
requireMainAdmin(); // Only main_admin can access this page

$conn = getDB();

// ── Handle Actions ────────────────────────────────────────────────
$action  = $_GET['action']  ?? '';
$target  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current = (int)$_SESSION['admin_id'];

// Toggle active/inactive
if ($action === 'toggle' && $target && $target !== $current) {
    $row = $conn->query("SELECT role FROM admins WHERE id=$target LIMIT 1")->fetch_assoc();
    if ($row && $row['role'] !== 'main_admin') {
        $conn->query("UPDATE admins SET is_active = 1 - is_active WHERE id=$target");
        setFlash('success', 'Admin status updated.');
    } else {
        setFlash('error', 'Cannot deactivate the Main Admin.');
    }
    redirect(SITE_URL . '/admin/admins.php');
}

// Delete sub-admin
if ($action === 'delete' && $target && $target !== $current) {
    $row = $conn->query("SELECT role FROM admins WHERE id=$target LIMIT 1")->fetch_assoc();
    if ($row && $row['role'] !== 'main_admin') {
        $conn->query("DELETE FROM admins WHERE id=$target");
        setFlash('success', 'Admin deleted successfully.');
    } else {
        setFlash('error', 'Cannot delete the Main Admin.');
    }
    redirect(SITE_URL . '/admin/admins.php');
}

// ── Add New Sub-Admin ─────────────────────────────────────────────
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $full_name = sanitize(trim($_POST['full_name'] ?? ''));
    $username  = sanitize(trim($_POST['username'] ?? ''));
    $email     = sanitize(trim($_POST['email'] ?? ''));

    if (empty($full_name) || empty($username) || empty($email)) {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } else {
        // Check duplicate
        $check = $conn->query("SELECT id FROM admins WHERE email='$email' OR username='$username' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $errors[] = 'Email or username already exists.';
        } else {
            // Default password: Admin@1234
            $default_password = password_hash('Admin@1234', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (full_name, username, email, password, role, is_active) VALUES (?, ?, ?, ?, 'sub_admin', 1)");
            $stmt->bind_param('ssss', $full_name, $username, $email, $default_password);
            if ($stmt->execute()) {
                setFlash('success', "Admin '$full_name' created! Default password: Admin@1234");
            } else {
                $errors[] = 'Failed to create admin. Please try again.';
            }
            $stmt->close();
            redirect(SITE_URL . '/admin/admins.php');
        }
    }
}

// ── Fetch All Admins ──────────────────────────────────────────────
$admins = $conn->query("SELECT * FROM admins ORDER BY role='main_admin' DESC, created_at ASC")->fetch_all(MYSQLI_ASSOC);
$total       = count($admins);
$active_cnt  = count(array_filter($admins, fn($a) => $a['is_active']));
$sub_cnt     = count(array_filter($admins, fn($a) => $a['role'] === 'sub_admin'));
?>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="admin-card" style="display:flex;align-items:center;gap:16px;">
            <div style="width:48px;height:48px;background:#EFF6FF;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#0066FF;font-size:1.1rem;">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;"><?php echo $total; ?></div>
                <div style="font-size:0.78rem;color:#6B7280;">Total Admins</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card" style="display:flex;align-items:center;gap:16px;">
            <div style="width:48px;height:48px;background:#F0FDF4;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#10B981;font-size:1.1rem;">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;"><?php echo $active_cnt; ?></div>
                <div style="font-size:0.78rem;color:#6B7280;">Active Admins</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card" style="display:flex;align-items:center;gap:16px;">
            <div style="width:48px;height:48px;background:#FFF7ED;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#F97316;font-size:1.1rem;">
                <i class="fas fa-users-cog"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;"><?php echo $sub_cnt; ?></div>
                <div style="font-size:0.78rem;color:#6B7280;">Sub Admins</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Add New Admin Form -->
    <div class="col-lg-4">
        <div class="admin-card">
            <h5 style="font-weight:800;margin-bottom:4px;font-size:1rem;">
                <i class="fas fa-user-plus me-2 text-primary"></i>Add New Sub-Admin
            </h5>
            <p style="font-size:0.8rem;color:#6B7280;margin-bottom:20px;">
                Default password will be set to <code>Admin@1234</code>
            </p>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="font-size:0.85rem;">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($errors[0]); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#374151;">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#F8FAFC;border-right:none;">
                            <i class="fas fa-user text-muted" style="font-size:0.85rem;"></i>
                        </span>
                        <input type="text" name="full_name" class="form-control" style="border-left:none;"
                               placeholder="e.g. John Doe"
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#374151;">Username</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#F8FAFC;border-right:none;">
                            <i class="fas fa-at text-muted" style="font-size:0.85rem;"></i>
                        </span>
                        <input type="text" name="username" class="form-control" style="border-left:none;"
                               placeholder="e.g. johndoe"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#374151;">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#F8FAFC;border-right:none;">
                            <i class="fas fa-envelope text-muted" style="font-size:0.85rem;"></i>
                        </span>
                        <input type="email" name="email" class="form-control" style="border-left:none;"
                               placeholder="john@electrohub.com"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div style="background:#FFF7ED;border:1px dashed #FDBA74;border-radius:10px;padding:12px;margin-bottom:16px;font-size:0.8rem;color:#92400E;">
                    <i class="fas fa-key me-1"></i>
                    New admin will use default password: <strong>Admin@1234</strong>
                </div>

                <button type="submit" name="add_admin" class="btn btn-primary w-100 py-2" style="font-weight:700;">
                    <i class="fas fa-user-plus me-2"></i>Create Sub-Admin
                </button>
            </form>
        </div>
    </div>

    <!-- Admins List -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h5 style="font-weight:800;margin:0;font-size:1rem;">
                    <i class="fas fa-list me-2 text-primary"></i>All Admins
                </h5>
                <span style="font-size:0.82rem;color:#6B7280;"><?php echo $total; ?> admin(s) total</span>
            </div>

            <?php if (empty($admins)): ?>
            <div style="text-align:center;padding:50px;color:#9CA3AF;">
                <i class="fas fa-user-shield" style="font-size:3rem;margin-bottom:12px;display:block;"></i>
                No admins found.
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Username</th>
                        <th style="text-align:center;">Role</th>
                        <th style="text-align:center;">Status</th>
                        <th>Joined</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $adm): ?>
                    <?php $is_me = ($adm['id'] == $current); ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:38px;height:38px;background:<?php echo $adm['role']==='main_admin'?'linear-gradient(135deg,#7C3AED,#A78BFA)':'linear-gradient(135deg,#0066FF,#00D4FF)'; ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:0.9rem;flex-shrink:0;">
                                    <?php echo strtoupper(substr($adm['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.88rem;">
                                        <?php echo htmlspecialchars($adm['full_name']); ?>
                                        <?php if ($is_me): ?>
                                        <span style="font-size:0.7rem;background:#DBEAFE;color:#1D4ED8;padding:2px 7px;border-radius:20px;margin-left:4px;font-weight:600;">You</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size:0.75rem;color:#9CA3AF;"><?php echo htmlspecialchars($adm['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:0.85rem;color:#6B7280;">@<?php echo htmlspecialchars($adm['username']); ?></td>
                        <td style="text-align:center;">
                            <?php if ($adm['role'] === 'main_admin'): ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#F3E8FF;color:#7C3AED;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:700;">
                                <i class="fas fa-crown" style="font-size:0.65rem;"></i> Main Admin
                            </span>
                            <?php else: ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#EFF6FF;color:#2563EB;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:700;">
                                <i class="fas fa-user-cog" style="font-size:0.65rem;"></i> Sub Admin
                            </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($adm['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.82rem;color:#6B7280;">
                            <?php echo date('M d, Y', strtotime($adm['created_at'])); ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($adm['role'] === 'main_admin'): ?>
                            <!-- Main Admin - no actions -->
                            <span style="font-size:0.75rem;color:#9CA3AF;">—</span>
                            <?php else: ?>
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <!-- Toggle Active / Inactive -->
                                <a href="?action=toggle&id=<?php echo $adm['id']; ?>"
                                   class="btn btn-sm <?php echo $adm['is_active']?'btn-outline-warning':'btn-outline-success'; ?>"
                                   title="<?php echo $adm['is_active']?'Deactivate':'Activate'; ?>"
                                   onclick="return confirm('<?php echo $adm['is_active']?'Deactivate':'Activate'; ?> admin <?php echo htmlspecialchars($adm['full_name']); ?>?')">
                                    <i class="fas fa-<?php echo $adm['is_active']?'ban':'check-circle'; ?>"></i>
                                </a>
                                <!-- Delete -->
                                <a href="?action=delete&id=<?php echo $adm['id']; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   title="Delete Admin"
                                   onclick="return confirm('Permanently delete admin <?php echo htmlspecialchars($adm['full_name']); ?>? This cannot be undone.')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info Box -->
        <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:12px;padding:16px;margin-top:16px;font-size:0.82rem;color:#166534;">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> New sub-admins will receive the default password <code>Admin@1234</code>.
            Share their email & this password securely. They can change it after logging in from their profile.
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
