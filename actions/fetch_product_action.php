<?php
// Set JSON header FIRST
header('Content-Type: application/json; charset=utf-8');

// Start output buffering to catch any unwanted output
ob_start();

try {
    require_once '../settings/core.php';
    require_once '../controllers/product_controller.php';

    // Check if user is logged in
    if (!isLoggedIn()) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'You must be logged in to perform this action.'
        ]);
        exit();
    }

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
        ob_clean();
        echo json_encode([
            'success' => true,
            'products' => $result['products']
        ]);
    } else {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to retrieve products',
            'products' => []
        ]);
    }
} catch (Exception $e) {
    ob_clean();
    error_log('Fetch products error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching products.',
        'error' => $e->getMessage()
    ]);
}

exit();
?>