<?php
/**
 * Admin Login
 */

require_once '../includes/functions.php';

if (isAdminLoggedIn()) redirect(SITE_URL . '/admin/dashboard.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Both fields are required.';
    } else {
        $conn = getDB();
        $email_safe = sanitize($email);
        $result = $conn->query("SELECT * FROM admins WHERE email='$email_safe' LIMIT 1");
        $admin = $result ? $result->fetch_assoc() : null;

        if ($admin && password_verify($password, $admin['password'])) {
            if (!$admin['is_active']) {
                $errors[] = 'Your admin account has been deactivated. Contact the Main Admin.';
            } else {
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                redirect(SITE_URL . '/admin/dashboard.php');
            }
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | ElectroHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #0A0E1A 0%, #0D1B40 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
<div style="width:100%;max-width:420px;padding:20px;">
    <div style="background:white;border-radius:24px;padding:40px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="text-align:center;margin-bottom:28px;">
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.4rem;color:white;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2 style="font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;">Admin Portal</h2>
            <p style="color:#6B7280;font-size:0.88rem;margin:4px 0 0;">ElectroHub Management System</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($errors[0]); ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Admin Email</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#F8FAFC;border-right:none;"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control" style="border-left:none;" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="admin@electrohub.com">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#F8FAFC;border-right:none;"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="pwd" class="form-control" style="border-left:none;border-right:none;" required placeholder="Admin password">
                    <button type="button" class="input-group-text" style="background:#F8FAFC;cursor:pointer;" onclick="const i=document.getElementById('pwd');i.type=i.type==='password'?'text':'password';">
                        <i class="fas fa-eye text-muted"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-3" style="font-weight:700;">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In to Admin
            </button>
        </form>

        <div style="margin-top:20px;background:#F0F7FF;border:1px dashed #93C5FD;border-radius:10px;padding:12px;font-size:0.8rem;">
            <strong style="color:#1E40AF;">Default Login:</strong><br>
            Email: <code>admin@electrohub.com</code><br>
            Password: <code>Admin@1234</code>
        </div>

        <div style="text-align:center;margin-top:20px;">
            <a href="<?php echo SITE_URL; ?>" style="font-size:0.83rem;color:#9CA3AF;text-decoration:none;">
                <i class="fas fa-arrow-left me-1"></i>Back to Store
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
