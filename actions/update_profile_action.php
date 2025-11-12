<?php
/**
 * Update Profile Action
 * Handles user profile updates including image upload
 */

// Start output buffering to prevent any output before JSON
ob_start();

// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'Please log in to update your profile'
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
    $required_fields = ['customer_name', 'customer_email', 'customer_contact', 'customer_country', 'customer_city'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode([
                'success' => false,
                'message' => "Missing required field: " . str_replace('_', ' ', $field)
            ]);
            exit();
        }
    }

    $user_id = $_SESSION['user_id'];
    $customer_name = htmlspecialchars(trim($_POST['customer_name']), ENT_QUOTES, 'UTF-8');
    $customer_email = filter_var(trim($_POST['customer_email']), FILTER_VALIDATE_EMAIL);
    $customer_contact = htmlspecialchars(trim($_POST['customer_contact']), ENT_QUOTES, 'UTF-8');
    $customer_country = htmlspecialchars(trim($_POST['customer_country']), ENT_QUOTES, 'UTF-8');
    $customer_city = htmlspecialchars(trim($_POST['customer_city']), ENT_QUOTES, 'UTF-8');

    // Validate email
    if (!$customer_email) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email address'
        ]);
        exit();
    }

    $db = new db_connection();

    // Check if email is already taken by another user
    $check_email = "SELECT customer_id FROM customer WHERE customer_email = '$customer_email' AND customer_id != '$user_id'";
    $existing_user = $db->db_fetch_one($check_email);
    if ($existing_user) {
        echo json_encode([
            'success' => false,
            'message' => 'Email address is already in use'
        ]);
        exit();
    }

    // Handle profile image upload
    $image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];

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
        $upload_dir = '../uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = 'profile_' . $user_id . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        $relative_path = 'uploads/profiles/' . $new_filename;

        // Get old image to delete it
        $old_image_sql = "SELECT customer_image FROM customer WHERE customer_id = '$user_id'";
        $old_data = $db->db_fetch_one($old_image_sql);
        $old_image = $old_data['customer_image'] ?? null;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $image_path = $relative_path;

            // Delete old image if exists
            if ($old_image && file_exists('../' . $old_image)) {
                @unlink('../' . $old_image);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image'
            ]);
            exit();
        }
    }

    // Build update query
    $sql = "UPDATE customer SET
            customer_name = '$customer_name',
            customer_email = '$customer_email',
            customer_contact = '$customer_contact',
            customer_country = '$customer_country',
            customer_city = '$customer_city'";

    // Add image path if uploaded
    if ($image_path) {
        $sql .= ", customer_image = '$image_path'";
    }

    $sql .= " WHERE customer_id = '$user_id'";

    // Execute update
    $result = $db->db_write_query($sql);

    if ($result) {
        // Update session name
        $_SESSION['user_name'] = $customer_name;

        // Clear output buffer and send success response
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'name' => $customer_name
        ]);
    } else {
        // Clear output buffer and send error response
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update profile. Please try again.'
        ]);
    }

} catch (Exception $e) {
    // Clear output buffer and send error response
    ob_end_clean();
    error_log("Error in update_profile_action: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>
