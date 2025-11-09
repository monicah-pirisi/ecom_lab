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
    // Get form data
    $product_title = trim($_POST['product_title'] ?? '');
    $product_cat = (int)($_POST['product_cat'] ?? 0);
    $product_brand = (int)($_POST['product_brand'] ?? 0);
    $product_price = floatval($_POST['product_price'] ?? 0);
    $product_desc = trim($_POST['product_desc'] ?? '');
    $product_keywords = trim($_POST['product_keywords'] ?? '');
    $user_id = $_SESSION['user_id'];

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

    // Create product first (without image)
    $result = create_product_ctr([
        'product_title' => $product_title,
        'product_cat' => $product_cat,
        'product_brand' => $product_brand,
        'product_price' => $product_price,
        'product_desc' => $product_desc,
        'product_image' => '', // Will be updated after upload
        'product_keywords' => $product_keywords,
        'user_id' => $user_id
    ]);

    if (!$result['success']) {
        echo json_encode($result);
        exit();
    }

    $product_id = $result['product_id'];

    // Handle image uploads if provided
    $uploaded_images = [];
    if (isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
        // Handle bulk upload
        $upload_result = handle_bulk_product_image_upload_ctr($_FILES['product_images'], $user_id, $product_id);

        if ($upload_result['success']) {
            $uploaded_images = $upload_result['files'];

            // Update product with first image path
            if (!empty($uploaded_images)) {
                update_product_ctr($product_id, [
                    'product_image' => $uploaded_images[0]
                ]);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Product created successfully',
        'product_id' => $product_id,
        'images_uploaded' => count($uploaded_images),
        'image_paths' => $uploaded_images
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while creating product. Please try again.'
    ]);
}
?>
