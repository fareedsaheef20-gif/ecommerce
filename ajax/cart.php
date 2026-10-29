<?php
/**
 * AJAX Cart Handler
 * Handles add/update/remove cart actions via AJAX
 */

require_once '../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage cart.', 'login_required' => true]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

switch ($action) {
    case 'add':
        $product = getProductById($product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            break;
        }
        if ($product['stock'] < 1) {
            echo json_encode(['success' => false, 'message' => 'Product is out of stock.']);
            break;
        }
        $result = addToCart($user_id, $product_id, $quantity);
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => htmlspecialchars($product['name']) . ' added to cart!',
                'cart_count' => getCartCount($user_id)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not add to cart.']);
        }
        break;

    case 'update':
        $result = updateCartQuantity($user_id, $product_id, $quantity);
        $cart_total = getCartTotal($user_id);
        $shipping = $cart_total >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
        $total_with_shipping = $cart_total + $shipping;
        echo json_encode([
            'success' => $result,
            'cart_count' => getCartCount($user_id),
            'cart_total' => formatPrice($total_with_shipping),
            'subtotal' => formatPrice($cart_total),
            'message' => $result ? 'Cart updated.' : 'Update failed.'
        ]);
        break;

    case 'remove':
        $result = removeFromCart($user_id, $product_id);
        echo json_encode([
            'success' => $result,
            'cart_count' => getCartCount($user_id),
            'message' => $result ? 'Item removed.' : 'Remove failed.'
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
