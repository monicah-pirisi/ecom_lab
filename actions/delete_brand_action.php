<?php

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/brand_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to perform this action.'
    ]);
    exit();
}

// Check if user is admin
if (!isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Admin privileges required.'
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
    // Get brand ID from POST
    $brand_id = (int)($_POST['brand_id'] ?? 0);

    if ($brand_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid brand ID'
        ]);
        exit();
    }

    // Delete brand using the brand controller
    $result = delete_brand_ctr($brand_id);

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting brand. Please try again.'
    ]);
}
?>
