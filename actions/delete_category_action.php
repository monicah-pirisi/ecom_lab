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
    // Check if category ID is provided via GET or POST
    $cat_id = null;
    
    if (isset($_GET['cat_id'])) {
        $cat_id = (int)$_GET['cat_id'];
    } elseif (isset($_POST['cat_id'])) {
        $cat_id = (int)$_POST['cat_id'];
    }
    
    if ($cat_id && $cat_id > 0) {
        // Delete category using the category controller
        $result = delete_category_ctr($cat_id);
        
        if ($result['success']) {
            // Return success response
            echo json_encode([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            // Return error response
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
        }
    } else {
        // Return error response for missing or invalid ID
        echo json_encode([
            'success' => false,
            'message' => 'Category ID is required and must be a valid positive integer'
        ]);
    }
} catch (Exception $e) {
    // Handle any unexpected errors
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting category: ' . $e->getMessage()
    ]);
}
?>
