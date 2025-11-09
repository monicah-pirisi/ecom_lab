<?php
// Set JSON header FIRST - before any output
header('Content-Type: application/json; charset=utf-8');

require_once '../settings/core.php';
require_once '../controllers/customer_controller.php';

$response = [];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit();
}

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid security token. Please refresh the page and try again.';
    echo json_encode($response);
    exit();
}

// Get POST data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    $response['status'] = 'error';
    $response['message'] = 'Please fill in all fields';
    echo json_encode($response);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['status'] = 'error';
    $response['message'] = 'Please enter a valid email address';
    echo json_encode($response);
    exit();
}

try {
    $login_result = login_customer_ctr($email, $password);

    if ($login_result['status'] === 'success') {
        // Set session variables
        $_SESSION['user_id'] = $login_result['user_id'];
        $_SESSION['user_name'] = $login_result['user_name'];
        $_SESSION['user_email'] = $login_result['user_email'];
        $_SESSION['user_role'] = $login_result['user_role'];
        $_SESSION['user_country'] = $login_result['user_country'] ?? '';
        $_SESSION['user_city'] = $login_result['user_city'] ?? '';
        $_SESSION['user_phone'] = $login_result['user_phone'] ?? '';

        // Regenerate session ID to prevent session fixation attacks
        regenerateSession();

        $response['status'] = 'success';
        $response['message'] = 'Login successful';
        
        // Redirect to main dashboard (same for all users)
        $response['redirect'] = '../dashboard.php';
        
        echo json_encode($response);
    } else {
        $response['status'] = 'error';
        $response['message'] = $login_result['message'] ?? 'Invalid email or password';
        echo json_encode($response);
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'An error occurred during login. Please try again.';
    echo json_encode($response);
}

exit();
?>