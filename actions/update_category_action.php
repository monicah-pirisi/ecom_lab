<?php

require_once '../settings/core.php';
require_once '../controllers/category_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    ob_clean();
    header('Location: ../login/login.php');
    exit();
}

// Check if user is admin
if (!isAdmin()) {
    ob_clean();
    header('Location: ../login/login.php');
    exit();
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid security token. Please try again.';
        ob_clean();
    header('Location: ../admin/category.php');
        exit();
    }

    try {
        // Get form data
        $cat_id = (int)($_POST['cat_id'] ?? 0);
        $cat_name = trim($_POST['cat_name'] ?? '');
        $cat_type = $_POST['cat_type'] ?? '';
        
        // Validate data
        $validation = validate_category_data_ctr([
            'cat_name' => $cat_name,
            'cat_type' => $cat_type
        ]);

        if ($cat_id <= 0) {
            $_SESSION['error_message'] = 'Invalid category ID';
        } elseif ($validation['valid']) {
            // Update category using the category controller
            $result = update_category_ctr($cat_id, $cat_name, $cat_type);

            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        } else {
            $_SESSION['error_message'] = 'Validation failed: ' . implode(', ', $validation['errors']);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'An error occurred while updating category. Please try again.';
    }

    ob_clean();
    header('Location: ../admin/category.php');
    exit();
} else {
    $_SESSION['error_message'] = 'Invalid request method.';
    ob_clean();
    header('Location: ../admin/category.php');
    exit();
}
?>
