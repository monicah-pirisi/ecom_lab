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

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $cat_id = (int)($_POST['cat_id'] ?? 0);
        $cat_name = trim($_POST['cat_name'] ?? '');
        $cat_type = $_POST['cat_type'] ?? '';
        
        // Validate data
        $validation = validate_category_data_ctr([
            'cat_name' => $cat_name,
            'cat_type' => $cat_type
        ]);
        
        if ($cat_id <= 0) {
            // Return error response for invalid ID
            echo json_encode([
                'success' => false,
                'message' => 'Invalid category ID'
            ]);
        } elseif ($validation['valid']) {
            // Update category using the category controller
            $result = update_category_ctr($cat_id, $cat_name, $cat_type);
            
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
            // Return validation error response
            echo json_encode([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validation['errors'])
            ]);
        }
    } catch (Exception $e) {
        // Handle any unexpected errors
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while updating category: ' . $e->getMessage()
        ]);
    }
} else {
    // Return error for non-POST requests
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only POST requests are allowed.'
    ]);
}
?>
