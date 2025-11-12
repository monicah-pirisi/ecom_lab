<?php
// Error reporting for debugging (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering FIRST to catch any unwanted output
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

try {
    // Require files
    require_once '../settings/core.php';
    require_once '../controllers/cart_controller.php';

    // Check if form was submitted via POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method.'
        ]);
        exit();
    }

    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Invalid security token. Please refresh the page and try again.'
        ]);
        exit();
    }

    // Get product ID from POST
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    // Validate product ID
    if ($product_id <= 0) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Invalid product ID.'
        ]);
        exit();
    }

    // Get user information
    // Support both logged-in users and guests
    $customer_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    // Get user's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Handle proxy/forwarded IP
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip_address = trim($ip_list[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    }

    // Validate IP address
    if (empty($ip_address) || $ip_address === '0.0.0.0') {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Unable to identify your session. Please enable cookies and try again.'
        ]);
        exit();
    }

    // Remove from cart
    $result = remove_from_cart_ctr($product_id, $customer_id, $ip_address);

    if ($result) {
        // Get updated cart count and total
        $cart_count = get_cart_count_ctr($customer_id, $ip_address);
        $cart_total = get_cart_total_ctr($customer_id, $ip_address);

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Product removed from cart successfully!',
            'cart_count' => $cart_count,
            'cart_total' => number_format($cart_total, 2)
        ]);
    } else {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove product from cart. Please try again.'
        ]);
    }

} catch (Exception $e) {
    // Log the error
    error_log("Error in remove_from_cart_action.php: " . $e->getMessage());

    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}

// End output buffering and flush
ob_end_flush();
?>
