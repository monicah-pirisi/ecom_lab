<?php
/**
 * Add Restaurant Action
 * Handles restaurant creation with image upload
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
            'message' => 'Please log in to add a restaurant'
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

    // Validate required fields
    $required_fields = ['restaurant_name', 'address', 'city', 'country', 'phone'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode([
                'success' => false,
                'message' => "Missing required field: " . str_replace('_', ' ', $field)
            ]);
            exit();
        }
    }

    $owner_id = $_SESSION['user_id'];

    // Prepare restaurant data
    $restaurant_data = [
        'owner_id' => $owner_id,
        'restaurant_name' => $_POST['restaurant_name'],
        'description' => $_POST['description'] ?? '',
        'cuisine_type' => $_POST['cuisine_type'] ?? '',
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'country' => $_POST['country'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'] ?? '',
        'opening_hours' => $_POST['opening_hours'] ?? '',
        'restaurant_image' => ''
    ];

    // Handle image upload
    if (isset($_FILES['restaurant_image']) && $_FILES['restaurant_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['restaurant_image'];

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.'
            ]);
            exit();
        }

        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            echo json_encode([
                'success' => false,
                'message' => 'File size exceeds 5MB limit.'
            ]);
            exit();
        }

        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/restaurants/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = 'restaurant_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        $relative_path = 'uploads/restaurants/' . $new_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $restaurant_data['restaurant_image'] = $relative_path;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image'
            ]);
            exit();
        }
    }

    // Create restaurant
    $restaurant_id = create_restaurant_ctr($restaurant_data);

    if ($restaurant_id) {
        // Clear output buffer and send success response
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Restaurant added successfully!',
            'restaurant_id' => $restaurant_id
        ]);
    } else {
        // Clear output buffer and send error response
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add restaurant. Please try again.'
        ]);
    }

} catch (Exception $e) {
    // Clear output buffer and send error response
    ob_end_clean();
    error_log("Error in add_restaurant_action: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>
