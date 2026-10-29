<?php
/**
 * User Profile Edit Page
 */

require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDB();
    $action = $_POST['action'] ?? 'profile';

    if ($action === 'profile') {
        $full_name = sanitize(trim($_POST['full_name'] ?? ''));
        $phone     = sanitize(trim($_POST['phone'] ?? ''));
        $address   = sanitize(trim($_POST['address'] ?? ''));
        $city      = sanitize(trim($_POST['city'] ?? ''));
        $country   = sanitize(trim($_POST['country'] ?? ''));

        if (strlen($full_name) < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        } else {
            $id = (int)$_SESSION['user_id'];
            $conn->query("UPDATE users SET full_name='$full_name', phone='$phone', address='$address', city='$city', country='$country' WHERE id=$id");
            $_SESSION['user_name'] = $full_name;
            $success = true;
            setFlash('success', 'Profile updated successfully!');
            $user = getCurrentUser();
        }
    } elseif ($action === 'password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        } else {
            $id = (int)$_SESSION['user_id'];
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE id=$id");
            setFlash('success', 'Password changed successfully!');
            $success = true;
        }
    }
}

$page_title = 'My Profile';
?>
<?php include 'includes/header.php'; ?>

<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </div>
</div>

<section class="section-padding-sm" style="background:#F8FAFC;">
<div class="container">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:24px;text-align:center;margin-bottom:16px;">
                <div style="width:80px;height:80px;background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:2rem;color:white;font-weight:800;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <div style="font-weight:800;font-size:1rem;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div style="font-size:0.82rem;color:#6B7280;">@<?php echo htmlspecialchars($user['username']); ?></div>
                <div style="font-size:0.78rem;color:#9CA3AF;margin-top:4px;">
                    Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                </div>
            </div>
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;overflow:hidden;">
                <a href="dashboard.php" style="display:flex;align-items:center;gap:10px;padding:13px 20px;font-size:0.88rem;font-weight:500;color:#374151;text-decoration:none;border-bottom:1px solid #F3F4F6;transition:all 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                    <i class="fas fa-tachometer-alt" style="color:#0066FF;width:16px;"></i> Dashboard
                </a>
                <a href="profile.php" style="display:flex;align-items:center;gap:10px;padding:13px 20px;font-size:0.88rem;font-weight:700;color:#0066FF;text-decoration:none;border-bottom:1px solid #F3F4F6;background:#F0F7FF;">
                    <i class="fas fa-user" style="color:#0066FF;width:16px;"></i> My Profile
                </a>
                <a href="orders.php" style="display:flex;align-items:center;gap:10px;padding:13px 20px;font-size:0.88rem;font-weight:500;color:#374151;text-decoration:none;border-bottom:1px solid #F3F4F6;transition:all 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                    <i class="fas fa-box" style="color:#6B7280;width:16px;"></i> My Orders
                </a>
                <a href="cart.php" style="display:flex;align-items:center;gap:10px;padding:13px 20px;font-size:0.88rem;font-weight:500;color:#374151;text-decoration:none;border-bottom:1px solid #F3F4F6;transition:all 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                    <i class="fas fa-shopping-cart" style="color:#6B7280;width:16px;"></i> My Cart
                </a>
                <a href="logout.php" style="display:flex;align-items:center;gap:10px;padding:13px 20px;font-size:0.88rem;font-weight:500;color:#EF4444;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='#FEF2F2'" onmouseout="this.style.background='white'">
                    <i class="fas fa-sign-out-alt" style="width:16px;"></i> Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-4">
                <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>
            <?php showFlash(); ?>

            <!-- Profile Info Form -->
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:28px;margin-bottom:24px;">
                <h5 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">
                    <i class="fas fa-user-edit me-2 text-primary"></i>Personal Information
                </h5>
                <form method="POST">
                    <input type="hidden" name="action" value="profile">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required minlength="3"
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="@<?php echo htmlspecialchars($user['username']); ?>" disabled style="background:#F8FAFC;cursor:not-allowed;">
                            <div style="font-size:0.77rem;color:#9CA3AF;margin-top:4px;">Username cannot be changed.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background:#F8FAFC;cursor:not-allowed;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?php echo htmlspecialchars($user['phone']); ?>"
                                   placeholder="+94 71 234 5678">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Street Address</label>
                            <input type="text" name="address" class="form-control"
                                   value="<?php echo htmlspecialchars($user['address']); ?>"
                                   placeholder="123 Main Street">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control"
                                   value="<?php echo htmlspecialchars($user['city']); ?>"
                                   placeholder="Colombo">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <select name="country" class="form-select">
                                <?php
                                $countries = ['Sri Lanka','India','Australia','United Kingdom','United States','Canada','Singapore','Germany','UAE'];
                                foreach ($countries as $c):
                                ?>
                                <option value="<?php echo $c; ?>" <?php echo ($user['country']==$c)?'selected':''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:28px;">
                <h5 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">
                    <i class="fas fa-lock me-2 text-primary"></i>Change Password
                </h5>
                <form method="POST">
                    <input type="hidden" name="action" value="password">
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required
                                   placeholder="Enter current password">
                        </div>
                        <div class="col-md-6 col-12"><!-- spacer --></div>
                        <div class="col-md-6">
                            <label class="form-label">New Password *</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6"
                                   placeholder="Min. 6 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required
                                   placeholder="Repeat new password">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger px-5">
                                <i class="fas fa-key me-2"></i>Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
