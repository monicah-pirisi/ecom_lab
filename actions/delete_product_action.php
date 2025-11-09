<?php

header('Content-Type: application/json');

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

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token. Please refresh the page and try again.'
    ]);
    exit();
}

try {
    // Get product ID
    $product_id = (int)($_POST['product_id'] ?? 0);

    if (empty($product_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required.'
        ]);
        exit();
    }

    // Check if user owns the product or is admin
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 0;

    if ($user_role != 1 && !user_owns_product_ctr($product_id, $user_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. You can only delete your own products.'
        ]);
        exit();
    }

    // Delete product
    $result = delete_product_ctr($product_id);

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting product. Please try again.'
    ]);
}
?>
