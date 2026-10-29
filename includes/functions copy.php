<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

require_once __DIR__ . '/config.php';

// ─────────────────────────────────────────────
// AUTH HELPERS
// ─────────────────────────────────────────────

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function isMainAdmin() {
    return isAdminLoggedIn() && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'main_admin';
}

function requireMainAdmin() {
    if (!isMainAdmin()) {
        header('Location: ' . SITE_URL . '/admin/dashboard.php');
        exit;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $conn = getDB();
    $id = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $id AND is_active = 1 LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

// ─────────────────────────────────────────────
// PRODUCT HELPERS
// ─────────────────────────────────────────────

function getProducts($limit = 20, $offset = 0, $where = '1') {
    $conn = getDB();
    $result = $conn->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND ($where)
        ORDER BY p.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getProductBySlug($slug) {
    $conn = getDB();
    $slug = sanitize($slug);
    $result = $conn->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = '$slug' AND p.is_active = 1 LIMIT 1
    ");
    return $result ? $result->fetch_assoc() : null;
}

function getProductById($id) {
    $conn = getDB();
    $id = (int)$id;
    $result = $conn->query("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = $id LIMIT 1
    ");
    return $result ? $result->fetch_assoc() : null;
}

function getFeaturedProducts($limit = 8) {
    $conn = getDB();
    $result = $conn->query("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_featured = 1 AND p.is_active = 1
        ORDER BY p.rating DESC LIMIT $limit
    ");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getRelatedProducts($product_id, $category_id, $limit = 4) {
    $conn = getDB();
    $product_id = (int)$product_id;
    $category_id = (int)$category_id;
    $result = $conn->query("
        SELECT * FROM products
        WHERE category_id = $category_id AND id != $product_id AND is_active = 1
        ORDER BY RAND() LIMIT $limit
    ");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getProductPrice($product) {
    return ($product['sale_price'] && $product['sale_price'] > 0)
        ? (float)$product['sale_price']
        : (float)$product['price'];
}

function formatPrice($amount) {
    return CURRENCY . number_format((float)$amount, 2);
}

// ─────────────────────────────────────────────
// CATEGORY HELPERS
// ─────────────────────────────────────────────

function getCategories($active_only = true) {
    $conn = getDB();
    $where = $active_only ? 'WHERE is_active = 1' : '';
    $result = $conn->query("SELECT * FROM categories $where ORDER BY name ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getCategoryBySlug($slug) {
    $conn = getDB();
    $slug = sanitize($slug);
    $result = $conn->query("SELECT * FROM categories WHERE slug = '$slug' AND is_active = 1 LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

// ─────────────────────────────────────────────
// CART HELPERS
// ─────────────────────────────────────────────

function getCartItems($user_id) {
    $conn = getDB();
    $user_id = (int)$user_id;
    $result = $conn->query("
        SELECT c.*, p.name, p.slug, p.image, p.price, p.sale_price, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = $user_id
        ORDER BY c.added_at DESC
    ");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getCartCount($user_id) {
    $conn = getDB();
    $user_id = (int)$user_id;
    $result = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
    $row = $result ? $result->fetch_assoc() : null;
    return $row ? (int)$row['total'] : 0;
}

function getCartTotal($user_id) {
    $items = getCartItems($user_id);
    $total = 0;
    foreach ($items as $item) {
        $price = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
        $total += $price * $item['quantity'];
    }
    return $total;
}

function addToCart($user_id, $product_id, $quantity = 1) {
    $conn = getDB();
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    $quantity = max(1, (int)$quantity);

    // Check stock
    $product = getProductById($product_id);
    if (!$product || $product['stock'] < $quantity) return false;

    $result = $conn->query("
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES ($user_id, $product_id, $quantity)
        ON DUPLICATE KEY UPDATE quantity = quantity + $quantity
    ");
    return $result;
}

function updateCartQuantity($user_id, $product_id, $quantity) {
    $conn = getDB();
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        return $conn->query("DELETE FROM cart WHERE user_id = $user_id AND product_id = $product_id");
    }
    return $conn->query("UPDATE cart SET quantity = $quantity WHERE user_id = $user_id AND product_id = $product_id");
}

function removeFromCart($user_id, $product_id) {
    $conn = getDB();
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    return $conn->query("DELETE FROM cart WHERE user_id = $user_id AND product_id = $product_id");
}

function clearCart($user_id) {
    $conn = getDB();
    $user_id = (int)$user_id;
    return $conn->query("DELETE FROM cart WHERE user_id = $user_id");
}

// ─────────────────────────────────────────────
// ORDER HELPERS
// ─────────────────────────────────────────────

function generateOrderNumber() {
    return 'EH-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . date('Y');
}

function createOrder($user_id, $shipping, $payment_method, $notes = '') {
    $conn = getDB();
    $user_id = (int)$user_id;
    $cart_items = getCartItems($user_id);
    if (empty($cart_items)) return false;

    $subtotal = getCartTotal($user_id);
    $shipping_cost = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
    $total = $subtotal + $shipping_cost;
    $order_number = generateOrderNumber();

    $sn = sanitize($shipping['name']);
    $se = sanitize($shipping['email']);
    $sp = sanitize($shipping['phone']);
    $sa = sanitize($shipping['address']);
    $sc = sanitize($shipping['city']);
    $sco = sanitize($shipping['country']);
    $pm = sanitize($payment_method);
    $nt = sanitize($notes);

    $conn->begin_transaction();
    try {
        $conn->query("
            INSERT INTO orders (user_id, order_number, total_amount, shipping_amount, payment_method,
                shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_country, notes)
            VALUES ($user_id, '$order_number', $total, $shipping_cost, '$pm',
                '$sn', '$se', '$sp', '$sa', '$sc', '$sco', '$nt')
        ");
        $order_id = $conn->insert_id;

        foreach ($cart_items as $item) {
            $price = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
            $item_total = $price * $item['quantity'];
            $pname = sanitize($item['name']);
            $pimg = sanitize($item['image']);
            $conn->query("
                INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, unit_price, total_price)
                VALUES ($order_id, {$item['product_id']}, '$pname', '$pimg', {$item['quantity']}, $price, $item_total)
            ");
            // Reduce stock
            $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['product_id']}");
        }

        // Create payment record
        $conn->query("INSERT INTO payments (order_id, payment_method, amount) VALUES ($order_id, '$pm', $total)");

        clearCart($user_id);
        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function getUserOrders($user_id) {
    $conn = getDB();
    $user_id = (int)$user_id;
    $result = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getOrderById($order_id, $user_id = null) {
    $conn = getDB();
    $order_id = (int)$order_id;
    $where = $user_id ? "AND user_id = " . (int)$user_id : '';
    $result = $conn->query("SELECT * FROM orders WHERE id = $order_id $where LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

function getOrderItems($order_id) {
    $conn = getDB();
    $order_id = (int)$order_id;
    $result = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getOrderStatusBadge($status) {
    $classes = [
        'pending'    => 'warning',
        'confirmed'  => 'info',
        'processing' => 'primary',
        'shipped'    => 'secondary',
        'delivered'  => 'success',
        'cancelled'  => 'danger',
        'refunded'   => 'dark'
    ];
    $class = $classes[$status] ?? 'secondary';
    return "<span class='badge bg-{$class}'>" . ucfirst($status) . "</span>";
}

// ─────────────────────────────────────────────
// FLASH MESSAGES
// ─────────────────────────────────────────────

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash() {
    $flash = getFlash();
    if ($flash) {
        echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert'>
            {$flash['message']}
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }
}

// ─────────────────────────────────────────────
// MISCELLANEOUS
// ─────────────────────────────────────────────

function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function renderStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) $html .= '<i class="fas fa-star text-warning"></i>';
        elseif ($rating >= $i - 0.5) $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        else $html .= '<i class="far fa-star text-warning"></i>';
    }
    return $html;
}

function getProductImageUrl($image) {
    $path = SITE_URL . '/assets/images/products/' . $image;
    return $path;
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hrs ago';
    return date('M d, Y', strtotime($datetime));
}

function truncate($text, $length = 100) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>
