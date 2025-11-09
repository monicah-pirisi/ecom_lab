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
        // Get category ID from POST
        $cat_id = (int)($_POST['cat_id'] ?? 0);

        if ($cat_id > 0) {
            // Delete category using the category controller
            $result = delete_category_ctr($cat_id);

            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        } else {
            $_SESSION['error_message'] = 'Invalid category ID';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'An error occurred while deleting category. Please try again.';
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
