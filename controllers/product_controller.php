<?php

require_once __DIR__ . '/../classes/product_class.php';

/**
 * Product Controller - handles business logic for product operations
 */

/**
 * Create a new product
 * @param array $data - Product data
 * @return array
 */
function create_product_ctr($data)
{
    $product = new Product();
    $result = $product->createProduct($data);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Product creation failed. Please check all required fields.'
        ];
    }
}

/**
 * Get all products
 * @return array
 */
function get_all_products_ctr()
{
    $product = new Product();
    $products = $product->getAllProducts();

    if ($products !== false) {
        return [
            'success' => true,
            'products' => $products
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to retrieve products'
        ];
    }
}

/**
 * Get products by user ID
 * @param int $user_id
 * @return array
 */
function get_products_by_user_ctr($user_id)
{
    $product = new Product();
    $products = $product->getProductsByUserId($user_id);

    if ($products !== false) {
        return [
            'success' => true,
            'products' => $products
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to retrieve products'
        ];
    }
}

/**
 * Get a specific product by ID
 * @param int $product_id
 * @return array
 */
function get_product_by_id_ctr($product_id)
{
    $product = new Product();
    $result = $product->getProductById($product_id);

    if ($result) {
        return [
            'success' => true,
            'product' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Product not found'
        ];
    }
}

/**
 * Get products by category
 * @param int $cat_id
 * @return array
 */
function get_products_by_category_ctr($cat_id)
{
    $product = new Product();
    $products = $product->getProductsByCategory($cat_id);

    if ($products !== false) {
        return [
            'success' => true,
            'products' => $products
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to retrieve products'
        ];
    }
}

/**
 * Get products by brand
 * @param int $brand_id
 * @return array
 */
function get_products_by_brand_ctr($brand_id)
{
    $product = new Product();
    $products = $product->getProductsByBrand($brand_id);

    if ($products !== false) {
        return [
            'success' => true,
            'products' => $products
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to retrieve products'
        ];
    }
}

/**
 * Update a product
 * @param int $product_id
 * @param array $data - Product data
 * @return array
 */
function update_product_ctr($product_id, $data)
{
    $product = new Product();
    $result = $product->updateProduct($product_id, $data);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Product updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Product update failed.'
        ];
    }
}

/**
 * Delete a product
 * @param int $product_id
 * @return array
 */
function delete_product_ctr($product_id)
{
    $product = new Product();
    $result = $product->deleteProduct($product_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Product deleted successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Product deletion failed'
        ];
    }
}

/**
 * Search products by keyword
 * @param string $keyword
 * @return array
 */
function search_products_ctr($keyword)
{
    $product = new Product();
    $products = $product->searchProducts($keyword);

    if ($products !== false) {
        return [
            'success' => true,
            'products' => $products
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Search failed'
        ];
    }
}

/**
 * Check if user owns product
 * @param int $product_id
 * @param int $user_id
 * @return boolean
 */
function user_owns_product_ctr($product_id, $user_id)
{
    $product = new Product();
    return $product->userOwnsProduct($product_id, $user_id);
}

/**
 * Validate product data
 * @param array $data
 * @return array
 */
function validate_product_data_ctr($data)
{
    $errors = [];

    // Validate product title
    if (empty($data['product_title'])) {
        $errors[] = 'Product title is required';
    } elseif (strlen($data['product_title']) < 3) {
        $errors[] = 'Product title must be at least 3 characters long';
    } elseif (strlen($data['product_title']) > 200) {
        $errors[] = 'Product title must not exceed 200 characters';
    }

    // Validate category
    if (empty($data['product_cat'])) {
        $errors[] = 'Category is required';
    } elseif (!is_numeric($data['product_cat'])) {
        $errors[] = 'Invalid category';
    }

    // Validate brand
    if (empty($data['product_brand'])) {
        $errors[] = 'Brand is required';
    } elseif (!is_numeric($data['product_brand'])) {
        $errors[] = 'Invalid brand';
    }

    // Validate price
    if (empty($data['product_price']) && $data['product_price'] !== '0') {
        $errors[] = 'Price is required';
    } elseif (!is_numeric($data['product_price'])) {
        $errors[] = 'Price must be a valid number';
    } elseif ($data['product_price'] < 0) {
        $errors[] = 'Price cannot be negative';
    }

    // Validate description (optional but check length if provided)
    if (isset($data['product_desc']) && strlen($data['product_desc']) > 1000) {
        $errors[] = 'Description must not exceed 1000 characters';
    }

    // Validate keywords (optional but check length if provided)
    if (isset($data['product_keywords']) && strlen($data['product_keywords']) > 500) {
        $errors[] = 'Keywords must not exceed 500 characters';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Handle single image upload with new directory structure
 * @param array $file - $_FILES['image'] array
 * @param int $user_id - User ID
 * @param int $product_id - Product ID
 * @param string $old_image - Old image path (for updates)
 * @return array
 */
function handle_product_image_upload_ctr($file, $user_id, $product_id, $old_image = null)
{
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return [
            'success' => false,
            'message' => 'No file uploaded',
            'image_path' => $old_image
        ];
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'File upload error: ' . $file['error']
        ];
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.'
        ];
    }

    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $max_size) {
        return [
            'success' => false,
            'message' => 'File size exceeds 5MB limit.'
        ];
    }

    // Create directory structure: uploads/u{user_id}/p{product_id}/
    $upload_base = '../uploads/';
    $user_dir = "u{$user_id}";
    $product_dir = "p{$product_id}";
    $full_dir = $upload_base . $user_dir . '/' . $product_dir . '/';

    // Security check: Ensure path is within uploads directory
    $real_upload_base = realpath($upload_base);

    if (!is_dir($full_dir)) {
        if (!mkdir($full_dir, 0755, true)) {
            return [
                'success' => false,
                'message' => 'Failed to create upload directory'
            ];
        }
    }

    $real_full_dir = realpath($full_dir);

    // Verify the directory is within uploads/
    if ($real_full_dir === false || strpos($real_full_dir, $real_upload_base) !== 0) {
        return [
            'success' => false,
            'message' => 'Security violation: Upload outside authorized directory'
        ];
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'image_' . uniqid() . '.' . $file_extension;
    $upload_path = $full_dir . $new_filename;
    $relative_path = 'uploads/' . $user_dir . '/' . $product_dir . '/' . $new_filename;

    // Upload file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Delete old image if exists
        if ($old_image && file_exists('../' . $old_image)) {
            @unlink('../' . $old_image);
        }

        return [
            'success' => true,
            'message' => 'Image uploaded successfully',
            'image_path' => $relative_path
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to upload image'
        ];
    }
}

/**
 * Handle bulk image upload for products
 * @param array $files - $_FILES['images'] array (multiple files)
 * @param int $user_id - User ID
 * @param int $product_id - Product ID
 * @return array
 */
function handle_bulk_product_image_upload_ctr($files, $user_id, $product_id)
{
    $uploaded_files = [];
    $errors = [];

    if (!isset($files['name']) || empty($files['name'][0])) {
        return [
            'success' => false,
            'message' => 'No images uploaded',
            'files' => []
        ];
    }

    $file_count = count($files['name']);

    for ($i = 0; $i < $file_count; $i++) {
        // Skip if no file uploaded
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        // Check for upload errors
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = "File {$files['name'][$i]}: Upload error";
            continue;
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($files['tmp_name'][$i]);

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "File {$files['name'][$i]}: Invalid file type";
            continue;
        }

        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024;
        if ($files['size'][$i] > $max_size) {
            $errors[] = "File {$files['name'][$i]}: File too large";
            continue;
        }

        // Create directory structure
        $upload_base = '../uploads/';
        $user_dir = "u{$user_id}";
        $product_dir = "p{$product_id}";
        $full_dir = $upload_base . $user_dir . '/' . $product_dir . '/';

        if (!is_dir($full_dir)) {
            mkdir($full_dir, 0755, true);
        }

        // Security check
        $real_upload_base = realpath($upload_base);
        $real_full_dir = realpath($full_dir);

        if ($real_full_dir === false || strpos($real_full_dir, $real_upload_base) !== 0) {
            $errors[] = "Security violation";
            continue;
        }

        // Generate filename
        $file_extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        $new_filename = 'image_' . ($i + 1) . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $full_dir . $new_filename;
        $relative_path = 'uploads/' . $user_dir . '/' . $product_dir . '/' . $new_filename;

        // Upload file
        if (move_uploaded_file($files['tmp_name'][$i], $upload_path)) {
            $uploaded_files[] = $relative_path;
        } else {
            $errors[] = "Failed to upload {$files['name'][$i]}";
        }
    }

    return [
        'success' => !empty($uploaded_files),
        'message' => count($uploaded_files) . ' image(s) uploaded successfully',
        'files' => $uploaded_files,
        'errors' => $errors
    ];
}
