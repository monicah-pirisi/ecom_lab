<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

try {
    // Require files
    session_start();
    require_once '../controllers/cart_controller.php';

    // Get user information
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

    // Get cart count
    $count = get_cart_count_ctr($customer_id, $ip_address);

    ob_clean();
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);

} catch (Exception $e) {
    error_log("Error in get_cart_count_action.php: " . $e->getMessage());

    ob_clean();
    echo json_encode([
        'success' => false,
        'count' => 0
    ]);
}

ob_end_flush();
?>
