<?php

require_once '../settings/core.php';
require_once '../controllers/category_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Check if user is admin
if (!isAdmin()) {
    header('Location: ../login/login.php');
    exit();
}

// Set content type to JSON for API response
header('Content-Type: application/json');

try {
    // Fetch all categories using the category controller
    $result = get_categories_ctr();
    
    if ($result['success']) {
        // Return success response with categories data
        echo json_encode([
            'success' => true,
            'message' => 'Categories fetched successfully',
            'data' => $result['categories'],
            'count' => count($result['categories'])
        ]);
    } else {
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => $result['message'],
            'data' => null,
            'count' => 0
        ]);
    }
} catch (Exception $e) {
    // Handle any unexpected errors
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching categories: ' . $e->getMessage(),
        'data' => null,
        'count' => 0
    ]);
}
?>


