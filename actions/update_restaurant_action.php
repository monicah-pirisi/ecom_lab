<?php
/**
 * Update Restaurant Action
 * Handles restaurant updates with optional image upload
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
            'message' => 'Please log in to update a restaurant'
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

    // Get restaurant ID
    $restaurant_id = isset($_POST['restaurant_id']) ? intval($_POST['restaurant_id']) : 0;

    if ($restaurant_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid restaurant ID'
        ]);
        exit();
    }

    // Fetch existing restaurant data
    $existing_restaurant = get_restaurant_by_id_ctr($restaurant_id);

    if (!$existing_restaurant) {
        echo json_encode([
            'success' => false,
            'message' => 'Restaurant not found'
        ]);
        exit();
    }

    // Verify the restaurant belongs to the logged-in owner
    $owner_id = $_SESSION['user_id'];
    if ($existing_restaurant['owner_id'] != $owner_id) {
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to update this restaurant'
        ]);
        exit();
    }

    // Validate required fields
    $required_fields = ['restaurant_name', 'address', 'city', 'country', 'phone', 'status'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode([
                'success' => false,
                'message' => "Missing required field: " . str_replace('_', ' ', $field)
            ]);
            exit();
        }
    }

    // Prepare restaurant data - keep existing image by default
    $restaurant_data = [
        'restaurant_name' => $_POST['restaurant_name'],
        'description' => $_POST['description'] ?? '',
        'cuisine_type' => $_POST['cuisine_type'] ?? '',
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'country' => $_POST['country'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'] ?? '',
        'opening_hours' => $_POST['opening_hours'] ?? '',
        'status' => $_POST['status'],
        'restaurant_image' => $existing_restaurant['restaurant_image'] ?? ''
    ];

    // Handle image upload (optional - only if new image is uploaded)
    if (isset($_FILES['restaurant_image']) && $_FILES['restaurant_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['restaurant_image'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $error_msg = $error_messages[$file['error']] ?? 'Unknown upload error';
            error_log("Restaurant image upload error: " . $error_msg);
            echo json_encode([
                'success' => false,
                'message' => 'Image upload failed: ' . $error_msg
            ]);
            exit();
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            error_log("Restaurant image upload error: Invalid file type - " . $file_type);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.'
            ]);
            exit();
        }

        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            error_log("Restaurant image upload error: File too large - " . $file['size'] . " bytes");
            echo json_encode([
                'success' => false,
                'message' => 'File size exceeds 5MB limit.'
            ]);
            exit();
        }

        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/restaurants/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                error_log("Restaurant image upload error: Failed to create directory - " . $upload_dir);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create upload directory'
                ]);
                exit();
            }
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = 'restaurant_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        $relative_path = 'uploads/restaurants/' . $new_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Delete old image if it exists and is different
            $old_image = $existing_restaurant['restaurant_image'] ?? '';
            if (!empty($old_image) && file_exists('../' . $old_image)) {
                @unlink('../' . $old_image);
                error_log("Deleted old restaurant image: " . $old_image);
            }

            $restaurant_data['restaurant_image'] = $relative_path;
            error_log("Restaurant image uploaded successfully: " . $relative_path);
        } else {
            error_log("Restaurant image upload error: move_uploaded_file failed");
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save uploaded image. Please try again.'
            ]);
            exit();
        }
    }

    // Update restaurant
    $result = update_restaurant_ctr($restaurant_id, $restaurant_data);

    if ($result) {
        // Clear output buffer and send success response
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Restaurant updated successfully!',
            'restaurant_id' => $restaurant_id
        ]);
    } else {
        // Clear output buffer and send error response
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update restaurant. Please try again.'
        ]);
    }

} catch (Exception $e) {
    // Clear output buffer and send error response
    ob_end_clean();
    error_log("Error in update_restaurant_action: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>
