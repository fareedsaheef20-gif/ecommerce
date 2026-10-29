<?php
/**
 * Database Configuration
 * E-Commerce Electronics Store
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce_electronics');

define('SITE_NAME', 'ElectroHub');
define('SITE_URL', 'http://localhost/ecommerce_updated/');
define('SITE_EMAIL', 'info@electrohub.com');
define('CURRENCY', '$');
define('SHIPPING_COST', 9.99);
define('FREE_SHIPPING_THRESHOLD', 100.00);

// Connect to database
function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="padding:20px;background:#fee;color:#c00;font-family:sans-serif;">
                <h3>Database Connection Error</h3>
                <p>Could not connect to MySQL. Please ensure XAMPP is running and the database is imported.</p>
                <p>Error: ' . $conn->connect_error . '</p>
            </div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Sanitize input to prevent SQL injection
function sanitize($data) {
    $conn = getDB();
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
