<?php

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/product_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to perform this action.'
    ]);
    exit();
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token. Please refresh the page and try again.'
    ]);
    exit();
}

try {
    // Get product ID
    $product_id = (int)($_POST['product_id'] ?? 0);

    if (empty($product_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required.'
        ]);
        exit();
    }

    // Check if user owns the product or is admin
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 0;

    if ($user_role != 1 && !user_owns_product_ctr($product_id, $user_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. You can only edit your own products.'
        ]);
        exit();
    }

    // Get current product data
    $current_product = get_product_by_id_ctr($product_id);
    $old_image = $current_product['product']['product_image'] ?? '';

    // Get form data
    $product_title = trim($_POST['product_title'] ?? '');
    $product_cat = (int)($_POST['product_cat'] ?? 0);
    $product_brand = (int)($_POST['product_brand'] ?? 0);
    $product_price = floatval($_POST['product_price'] ?? 0);
    $product_desc = trim($_POST['product_desc'] ?? '');
    $product_keywords = trim($_POST['product_keywords'] ?? '');

    // Validate data
    $validation = validate_product_data_ctr([
        'product_title' => $product_title,
        'product_cat' => $product_cat,
        'product_brand' => $product_brand,
        'product_price' => $product_price,
        'product_desc' => $product_desc,
        'product_keywords' => $product_keywords
    ]);

    if (!$validation['valid']) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed: ' . implode(', ', $validation['errors'])
        ]);
        exit();
    }

    // Prepare update data
    $update_data = [
        'product_title' => $product_title,
        'product_cat' => $product_cat,
        'product_brand' => $product_brand,
        'product_price' => $product_price,
        'product_desc' => $product_desc,
        'product_keywords' => $product_keywords
    ];

    // Handle new image uploads if provided
    $uploaded_images = [];
    if (isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
        // Get the user_id from the product (in case admin is editing someone else's product)
        $product_user_id = $current_product['product']['user_id'] ?? $user_id;

        $upload_result = handle_bulk_product_image_upload_ctr($_FILES['product_images'], $product_user_id, $product_id);

        if ($upload_result['success']) {
            $uploaded_images = $upload_result['files'];

            // Update product with first new image path
            if (!empty($uploaded_images)) {
                $update_data['product_image'] = $uploaded_images[0];
            }
        }
    }

    // Update product
    $result = update_product_ctr($product_id, $update_data);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully',
            'images_uploaded' => count($uploaded_images),
            'image_paths' => $uploaded_images
        ]);
    } else {
        echo json_encode($result);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating product. Please try again.'
    ]);
}
?>
