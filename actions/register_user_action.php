<?php
// Set headers FIRST - before any output
header('Content-Type: application/json; charset=utf-8');

require_once '../settings/core.php';
require_once '../controllers/customer_controller.php';

$response = array();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
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

try {
    // Validate required fields
    $requiredFields = [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'phone_number' => 'Phone number',
        'country' => 'Country',
        'city' => 'City',
        'role' => 'Role'
    ];

    foreach ($requiredFields as $field => $label) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $response['status'] = 'error';
            $response['message'] = $label . ' is required';
            echo json_encode($response);
            exit();
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $response['status'] = 'error';
        $response['message'] = 'Please enter a valid email address';
        echo json_encode($response);
        exit();
    }

    // Sanitize inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone_number = trim($_POST['phone_number']);
    $country = trim($_POST['country']);
    $city = trim($_POST['city']);
    $role = intval($_POST['role']);

    // Validate role value
    if (!in_array($role, [1, 2])) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid role selected';
        echo json_encode($response);
        exit();
    }

    // Check if email already exists
    $existing_customer = get_customer_by_email_ctr($email);
    if ($existing_customer) {
        $response['status'] = 'error';
        $response['message'] = 'Email already exists. Please use a different email.';
        echo json_encode($response);
        exit();
    }

    // Register user
    $user_id = register_customer_ctr($name, $email, $password, $phone_number, $country, $city, $role);

    if ($user_id) {
        $response['status'] = 'success';
        $response['message'] = 'Registration successful! Redirecting to login...';
        $response['user_id'] = $user_id;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to register. Please try again.';
    }

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'An error occurred during registration. Please try again.';
}

// Ensure clean JSON output
echo json_encode($response);
exit();
?>