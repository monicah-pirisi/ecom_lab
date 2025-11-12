<?php
/**
 * Change Password Action
 * Handles password changes with current password verification
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
            'message' => 'Please log in to change your password'
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
    if (empty($_POST['current_password']) || empty($_POST['new_password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Validate new password length
    if (strlen($new_password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'New password must be at least 6 characters long'
        ]);
        exit();
    }

    $db = new db_connection();

    // Get current password hash from database
    $sql = "SELECT customer_pass FROM customer WHERE customer_id = '$user_id'";
    $user = $db->db_fetch_one($sql);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit();
    }

    // Verify current password
    if (!password_verify($current_password, $user['customer_pass'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        exit();
    }

    // Check if new password is same as current
    if (password_verify($new_password, $user['customer_pass'])) {
        echo json_encode([
            'success' => false,
            'message' => 'New password must be different from current password'
        ]);
        exit();
    }

    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $update_sql = "UPDATE customer SET customer_pass = '$hashed_password' WHERE customer_id = '$user_id'";
    $result = $db->db_write_query($update_sql);

    if ($result) {
        // Clear output buffer and send success response
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully!'
        ]);
    } else {
        // Clear output buffer and send error response
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to change password. Please try again.'
        ]);
    }

} catch (Exception $e) {
    // Clear output buffer and send error response
    ob_end_clean();
    error_log("Error in change_password_action: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>
