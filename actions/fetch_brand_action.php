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

try {
    // Get current user ID
    $user_id = $_SESSION['user_id'];

    // Fetch all brands created by the current user
    $result = get_brands_by_user_ctr($user_id);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'brands' => $result['brands']
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
        'message' => 'An error occurred while fetching brands. Please try again.'
    ]);
}
?>
