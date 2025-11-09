<?php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../settings/core.php';
require_once '../controllers/product_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to perform this action.'
    ]);
    exit();
}

try {
    // Get current user ID and role
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 0;

    // Admins can see all products, others see only their own
    if ($user_role == 1) {
        $result = get_all_products_ctr();
    } else {
        $result = get_products_by_user_ctr($user_id);
    }

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'products' => $result['products']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine()
    ]);
}
?>
