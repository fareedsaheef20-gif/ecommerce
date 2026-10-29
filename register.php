<?php
/**
 * Registration Page
 */

require_once 'includes/functions.php';

if (isLoggedIn()) redirect(SITE_URL . '/dashboard.php');

$errors = [];
$data = ['full_name'=>'','username'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['full_name'] = trim($_POST['full_name'] ?? '');
    $data['username']  = trim($_POST['username'] ?? '');
    $data['email']     = trim($_POST['email'] ?? '');
    $data['phone']     = trim($_POST['phone'] ?? '');
    $password          = $_POST['password'] ?? '';
    $confirm           = $_POST['confirm_password'] ?? '';

    if (strlen($data['full_name']) < 3) $errors[] = 'Full name must be at least 3 characters.';
    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $data['username'])) $errors[] = 'Username must be 3-30 alphanumeric characters.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $conn = getDB();
        $email_safe    = sanitize($data['email']);
        $username_safe = sanitize($data['username']);

        // Check duplicates
        $check = $conn->query("SELECT id FROM users WHERE email='$email_safe' OR username='$username_safe' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $errors[] = 'Email or username is already registered.';
        } else {
            $hashed   = password_hash($password, PASSWORD_DEFAULT);
            $fn_safe  = sanitize($data['full_name']);
            $ph_safe  = sanitize($data['phone']);

            $conn->query("
                INSERT INTO users (full_name, username, email, password, phone)
                VALUES ('$fn_safe','$username_safe','$email_safe','$hashed','$ph_safe')
            ");

            if ($conn->insert_id) {
                $_SESSION['user_id']    = $conn->insert_id;
                $_SESSION['user_name']  = $data['full_name'];
                $_SESSION['user_email'] = $data['email'];
                setFlash('success', 'Account created successfully! Welcome to ElectroHub, ' . htmlspecialchars($data['full_name']) . '!');
                redirect(SITE_URL . '/dashboard.php');
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper" style="padding:32px 16px;">
    <div class="auth-card" style="max-width:520px;">
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

        <h2 class="auth-title">Create Account</h2>
        <p class="auth-subtitle">Join Ashrifmobile and start shopping today</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" data-validate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required
                           value="<?php echo htmlspecialchars($data['full_name']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username *</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#F8FAFC;border-right:none;">@</span>
                        <input type="text" name="username" class="form-control" style="border-left:none;" required
                               value="<?php echo htmlspecialchars($data['username']); ?>"
                               pattern="[a-zA-Z0-9_]{3,30}">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?php echo htmlspecialchars($data['email']); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($data['phone']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password *</label>
                    <div class="input-group">
                        <input type="password" name="password" id="pwd1" class="form-control"
                               minlength="6" style="border-right:none;">
                        <button type="button" class="input-group-text" style="background:#F8FAFC;cursor:pointer;"
                                onclick="togglePwd('pwd1','eye1')">
                            <i class="fas fa-eye text-muted" id="eye1"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password *</label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" id="pwd2" class="form-control"
                               style="border-right:none;">
                        <button type="button" class="input-group-text" style="background:#F8FAFC;cursor:pointer;"
                                onclick="togglePwd('pwd2','eye2')">
                            <i class="fas fa-eye text-muted" id="eye2"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms" style="font-size:0.87rem;">
                            I agree to the <a href="#" style="color:#0066FF;">Terms of Service</a> and
                            <a href="#" style="color:#0066FF;">Privacy Policy</a>
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100 py-3" style="font-size:1rem;font-weight:700;">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <p class="text-center mb-0" style="font-size:0.9rem;color:#6B7280;">
            Already have an account?
            <a href="login.php" style="color:#0066FF;font-weight:700;text-decoration:none;">Sign In</a>
        </p>
        <div class="text-center mt-3">
            <a href="<?php echo SITE_URL; ?>" style="font-size:0.83rem;color:#9CA3AF;text-decoration:none;">
                <i class="fas fa-arrow-left me-1"></i>Back to Home
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}
</script>
</body>
</html>
