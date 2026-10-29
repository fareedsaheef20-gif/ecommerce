<?php
require_once 'includes/functions.php';
$page_title = 'Contact Us';
$success_msg = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (strlen($name) < 2)                      $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if (strlen($message) < 10)                  $errors[] = 'Message must be at least 10 characters.';

    if (empty($errors)) {
        // In production, send an actual email here with mail()
        $success_msg = 'Thank you, ' . htmlspecialchars($name) . '! Your message has been received. We\'ll get back to you within 24 hours.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item active">Contact</li>
        </ol>
    </div>
</div>

<section class="section-padding" style="background:#F8FAFC;">
<div class="container">
    <div class="section-header">
        <div class="section-tag">Get in Touch</div>
        <h2 class="section-title">Contact Us</h2>
        <p style="color:#6B7280;max-width:500px;margin:0 auto;font-size:0.95rem;">
            Have a question about a product, order, or need tech advice? We're here to help.
        </p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Contact Info -->
        <div class="col-lg-4">
            <div style="display:flex;flex-direction:column;gap:16px;">
                <?php
                $contact_info = [
                    ['fas fa-map-marker-alt','Visit Us','282,Hjra 5th Road sammanthurai','#0066FF'],
                    ['fas fa-phone','Call Us','+94 769665410<br>+94 768796312','#10B981'],
                    ['fas fa-envelope','Email Us','info@electrohub.com<br>support@electrohub.com','#F59E0B'],
                    ['fas fa-clock','Business Hours','Mon - Sat: 9AM - 8PM<br>Sunday: 10AM - 6PM','#7C3AED'],
                ];
                foreach ($contact_info as $info):
                ?>
                <div style="background:white;border-radius:16px;border:1px solid #E5E7EB;padding:20px;display:flex;gap:16px;align-items:flex-start;">
                    <div style="width:44px;height:44px;background:<?php echo $info[3]; ?>18;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="<?php echo $info[0]; ?>" style="color:<?php echo $info[3]; ?>;font-size:1rem;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:0.9rem;margin-bottom:4px;"><?php echo $info[1]; ?></div>
                        <div style="font-size:0.84rem;color:#6B7280;line-height:1.6;"><?php echo $info[2]; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
            <div style="background:white;border-radius:20px;border:1px solid #E5E7EB;padding:36px;">
                <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?><div><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST" data-validate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? (isLoggedIn() ? $_SESSION['user_name'] : '')); ?>"
                                   placeholder="John Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? (isLoggedIn() ? $_SESSION['user_email'] : '')); ?>"
                                   placeholder="you@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select">
                                <option>Product Inquiry</option>
                                <option>Order Support</option>
                                <option>Technical Support</option>
                                <option>Returns & Refunds</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="5" required minlength="10"
                                      placeholder="How can we help you?"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-5 py-3" style="font-weight:700;">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
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
