<?php
/**
 * Login Page
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) redirect(SITE_URL . '/dashboard.php');

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (empty($password)) {
        $errors[] = 'Password is required.';
    } else {
        $conn = getDB();
        $email_safe = sanitize($email);

        // ── Check admins table first ──────────────────────────
        $admin_result = $conn->query("SELECT * FROM admins WHERE email = '$email_safe' LIMIT 1");
        $admin = $admin_result ? $admin_result->fetch_assoc() : null;

        if ($admin && password_verify($password, $admin['password'])) {
            if (!$admin['is_active']) {
                $errors[] = 'Your admin account has been deactivated. Contact the Main Admin.';
            } else {
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                setFlash('success', 'Welcome, ' . htmlspecialchars($admin['full_name']) . '!');
                redirect(SITE_URL . '/admin/dashboard.php');
            }
        } else {
            // ── Check users table ─────────────────────────────
            $result = $conn->query("SELECT * FROM users WHERE email = '$email_safe' AND is_active = 1 LIMIT 1");
            $user = $result ? $result->fetch_assoc() : null;

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email']= $user['email'];

                $redirect = $_SESSION['redirect_after_login'] ?? (SITE_URL . '/dashboard.php');
                unset($_SESSION['redirect_after_login']);
                setFlash('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');
                redirect($redirect);
            } else {
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}

$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <a href="<?php echo SITE_URL; ?>" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                <span style="width:36px;height:36px;background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;">
                    <i class="fas fa-bolt"></i>
                </span>
                <span style="font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;color:#0A0E1A;">
                    Ashrif<span style="color:#0066FF;">mobile</span>
                </span>
            </a>
        </div>

        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your account to continue</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" action="" data-validate>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#F8FAFC;border-right:none;">
                        <i class="fas fa-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" class="form-control" style="border-left:none;"
                           value="<?php echo htmlspecialchars($email); ?>"
                           placeholder="you@example.com" required >
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#F8FAFC;border-right:none;">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" id="passwordInput" class="form-control" style="border-left:none;border-right:none;"
                           placeholder="Your password" required>
                    <button type="button" class="input-group-text" style="background:#F8FAFC;cursor:pointer;"
                            onclick="togglePassword()">
                        <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:0.87rem;">Remember me</label>
                </div>
                <a href="#" style="font-size:0.87rem;color:#0066FF;text-decoration:none;">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3" style="font-size:1rem;font-weight:700;">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>

        <hr class="my-4">

        <p class="text-center mb-0" style="font-size:0.9rem;color:#6B7280;">
            Don't have an account?
            <a href="register.php" style="color:#0066FF;font-weight:700;text-decoration:none;">Create Account</a>
        </p>

        <div class="text-center mt-3">
            <a href="<?php echo SITE_URL; ?>" style="font-size:0.83rem;color:#9CA3AF;text-decoration:none;">
                <i class="fas fa-arrow-left me-1"></i>Back to Home
            </a>
        </div>

        <!-- Demo Credentials -->
        <!-- <div style="margin-top:20px;background:#F0F7FF;border:1px dashed #93C5FD;border-radius:10px;padding:14px;font-size:0.82rem;">
            <div style="font-weight:700;color:#1E40AF;margin-bottom:8px;"><i class="fas fa-info-circle me-1"></i>Demo Credentials</div>
            <div style="color:#374151;margin-bottom:4px;"><strong>User:</strong> <code>john@example.com</code> / <code>password</code></div>
            <div style="color:#374151;"><strong>Admin:</strong> <code>admin@electrohub.com</code> / <code>Admin@1234</code></div>
        </div> -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}
</script>
</body>
</html>
