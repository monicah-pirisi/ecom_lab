<?php

header('Content-Type: application/json');

require_once '../settings/core.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to upload images.'
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
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    // Validate product_id
    if (empty($product_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required.'
        ]);
        exit();
    }

    // Check if files were uploaded
    if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
        echo json_encode([
            'success' => false,
            'message' => 'No images uploaded.'
        ]);
        exit();
    }

    $uploaded_files = [];
    $errors = [];

    // Process multiple files
    $files = $_FILES['images'];
    $file_count = count($files['name']);

    for ($i = 0; $i < $file_count; $i++) {
        // Skip if no file uploaded
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        // Check for upload errors
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = "File {$files['name'][$i]}: Upload error code {$files['error'][$i]}";
            continue;
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($files['tmp_name'][$i]);

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "File {$files['name'][$i]}: Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.";
            continue;
        }

        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB in bytes
        if ($files['size'][$i] > $max_size) {
            $errors[] = "File {$files['name'][$i]}: File size exceeds 5MB limit.";
            continue;
        }

        // Create directory structure: uploads/u{user_id}/p{product_id}/
        $upload_base = '../uploads/';
        $user_dir = "u{$user_id}";
        $product_dir = "p{$product_id}";
        $full_dir = $upload_base . $user_dir . '/' . $product_dir . '/';

        // Security check: Ensure path is within uploads directory
        $real_upload_base = realpath($upload_base);
        $real_full_dir = realpath($full_dir);

        if (!is_dir($full_dir)) {
            if (!mkdir($full_dir, 0755, true)) {
                $errors[] = "Failed to create upload directory for file {$files['name'][$i]}";
                continue;
            }
            $real_full_dir = realpath($full_dir);
        }

        // Verify the directory is within uploads/
        if ($real_full_dir === false || strpos($real_full_dir, $real_upload_base) !== 0) {
            $errors[] = "Security violation: Attempted upload outside authorized directory";
            continue;
        }

        // Generate unique filename
        $file_extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        $new_filename = 'image_' . ($i + 1) . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $full_dir . $new_filename;
        $relative_path = 'uploads/' . $user_dir . '/' . $product_dir . '/' . $new_filename;

        // Upload file
        if (move_uploaded_file($files['tmp_name'][$i], $upload_path)) {
            $uploaded_files[] = [
                'filename' => $new_filename,
                'path' => $relative_path,
                'size' => $files['size'][$i],
                'original_name' => $files['name'][$i]
            ];
        } else {
            $errors[] = "Failed to upload file {$files['name'][$i]}";
        }
    }

    // Return results
    if (!empty($uploaded_files)) {
        echo json_encode([
            'success' => true,
            'message' => count($uploaded_files) . ' image(s) uploaded successfully',
            'files' => $uploaded_files,
            'errors' => $errors
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No images were uploaded successfully',
            'errors' => $errors
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while uploading images. Please try again.',
        'error' => $e->getMessage()
    ]);
}
?>
