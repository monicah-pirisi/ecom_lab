<?php

header('Content-Type: application/json');

session_start();

$response = array();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

require_once '../controllers/customer_controller.php';

// Get POST data
$email = $_POST['email'];
$password = $_POST['password'];

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

// Attempt to login the customer
$login_result = login_customer_ctr($email, $password);

if ($login_result['status'] === 'success') {
    // Set session variables
    $_SESSION['user_id'] = $login_result['user_id'];
    $_SESSION['user_name'] = $login_result['user_name'];
    $_SESSION['user_email'] = $login_result['user_email'];
    $_SESSION['user_role'] = $login_result['user_role'];
    $_SESSION['user_country'] = $login_result['user_country'];
    $_SESSION['user_city'] = $login_result['user_city'];
    $_SESSION['user_phone'] = $login_result['user_phone'];
    
    $response['status'] = 'success';
    $response['message'] = 'Login successful';
    $response['redirect'] = '../dashboard.php'; // Redirect to dashboard after login
} else {
    $response['status'] = 'error';
    $response['message'] = $login_result['message'];
}

echo json_encode($response);
?>
