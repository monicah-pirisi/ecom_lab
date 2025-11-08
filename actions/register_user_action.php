<?php

header('Content-Type: application/json');

session_start();

$response = array();

// Note: Removed the "already logged in" check to allow registration
// Users should be able to register new accounts even if logged in

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../controllers/customer_controller.php';

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
    $response['status'] = 'error';
    $response['message'] = 'Registration error: ' . $e->getMessage();
    error_log('Registration error: ' . $e->getMessage());
}

echo json_encode($response);