<?php

require_once '../settings/core.php';
require_once '../controllers/category_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Check if user is admin
if (!isAdmin()) {
    header('Location: ../login/login.php');
    exit();
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid security token. Please try again.';
        header('Location: ../admin/category.php');
        exit();
    }

    try {
        // Get form data
        $cat_name = trim($_POST['cat_name'] ?? '');
        $cat_type = $_POST['cat_type'] ?? '';

        // Validate data
        $validation = validate_category_data_ctr([
            'cat_name' => $cat_name,
            'cat_type' => $cat_type
        ]);

        if ($validation['valid']) {
            // Create category using the category controller
            $result = create_category_ctr($cat_name, $cat_type);

            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        } else {
            $_SESSION['error_message'] = 'Validation failed: ' . implode(', ', $validation['errors']);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'An error occurred while creating category. Please try again.';
    }

    header('Location: ../admin/category.php');
    exit();
} else {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: ../admin/category.php');
    exit();
}
?>
