<?php
/**
 * Delete Restaurant Action
 * Handles restaurant deletion with ownership verification
 */

// Start output buffering to prevent any output before JSON
ob_start();

// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/restaurant_controller.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'Please log in to delete a restaurant'
        ]);
        exit();
    }

    // Check if user is a restaurant owner
    $user_role = $_SESSION['user_role'] ?? null;
    if ($user_role != 2) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit();
    }

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid security token'
        ]);
        exit();
    }

    // Validate restaurant ID
    if (empty($_POST['restaurant_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Restaurant ID is required'
        ]);
        exit();
    }

    $restaurant_id = (int)$_POST['restaurant_id'];
    $owner_id = $_SESSION['user_id'];

    // Verify that the user owns this restaurant
    if (!user_owns_restaurant_ctr($restaurant_id, $owner_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to delete this restaurant'
        ]);
        exit();
    }

    // Get restaurant details to delete image
    $restaurant = get_restaurant_by_id_ctr($restaurant_id);
    if ($restaurant && !empty($restaurant['restaurant_image'])) {
        $image_path = '../' . $restaurant['restaurant_image'];
        if (file_exists($image_path)) {
            @unlink($image_path);
        }
    }

    // Delete restaurant
    $result = delete_restaurant_ctr($restaurant_id);

    if ($result) {
        // Clear output buffer and send success response
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Restaurant deleted successfully!'
        ]);
    } else {
        // Clear output buffer and send error response
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete restaurant. Please try again.'
        ]);
    }

} catch (Exception $e) {
    // Clear output buffer and send error response
    ob_end_clean();
    error_log("Error in delete_restaurant_action: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>
