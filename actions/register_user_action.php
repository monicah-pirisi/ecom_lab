<?php

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/customer_controller.php';

$response = array();

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid security token. Please refresh the page and try again.';
    echo json_encode($response);
    exit();
}

try {

    // Validate required fields
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        $response['status'] = 'error';
        $response['message'] = 'Name is required';
        echo json_encode($response);
        exit();
    }

    if (!isset($_POST['email']) || empty($_POST['email'])) {
        $response['status'] = 'error';
        $response['message'] = 'Email is required';
        echo json_encode($response);
        exit();
    }

    if (!isset($_POST['password']) || empty($_POST['password'])) {
        $response['status'] = 'error';
        $response['message'] = 'Password is required';
        echo json_encode($response);
        exit();
    }

    if (!isset($_POST['phone_number']) || empty($_POST['phone_number'])) {
        $response['status'] = 'error';
        $response['message'] = 'Phone number is required';
        echo json_encode($response);
        exit();
    }

    if (!isset($_POST['country']) || empty($_POST['country'])) {
        $response['status'] = 'error';
        $response['message'] = 'Country is required';
        echo json_encode($response);
        exit();
    }

    if (!isset($_POST['city']) || empty($_POST['city'])) {
        $response['status'] = 'error';
        $response['message'] = 'City is required';
        echo json_encode($response);
        exit();
    }

    if (!isset($_POST['role']) || empty($_POST['role'])) {
        $response['status'] = 'error';
        $response['message'] = 'Role is required';
        echo json_encode($response);
        exit();
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $response['status'] = 'error';
        $response['message'] = 'Please enter a valid email address';
        echo json_encode($response);
        exit();
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone_number = trim($_POST['phone_number']);
    $country = trim($_POST['country']);
    $city = trim($_POST['city']);
    $role = $_POST['role'];

    // Check if email already exists
    $existing_customer = get_customer_by_email_ctr($email);
    if ($existing_customer) {
        $response['status'] = 'error';
        $response['message'] = 'Email already exists. Please use a different email.';
        echo json_encode($response);
        exit();
    }

    $user_id = register_customer_ctr($name, $email, $password, $phone_number, $country, $city, $role);

    if ($user_id) {
        $response['status'] = 'success';
        $response['message'] = 'Registered successfully';
        $response['user_id'] = $user_id;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to register. Please try again.';
    }

} catch (Exception $e) {
    @error_log('Registration error: ' . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'An error occurred during registration. Please try again.';
}

echo json_encode($response);