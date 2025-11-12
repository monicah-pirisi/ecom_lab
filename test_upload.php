<?php
/**
 * Image Upload Test Page
 * This page helps diagnose upload issues
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$upload_status = '';
$upload_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    $file = $_FILES['test_image'];

    // Log file details
    $details = [
        'name' => $file['name'],
        'type' => $file['type'],
        'size' => $file['size'],
        'tmp_name' => $file['tmp_name'],
        'error' => $file['error']
    ];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/test/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $new_filename = 'test_' . time() . '_' . basename($file['name']);
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $upload_status = "✓ Upload successful!<br>";
            $upload_status .= "File saved to: " . $upload_path . "<br>";
            $upload_status .= "File size: " . $file['size'] . " bytes<br>";
            $upload_status .= "File type: " . mime_content_type($upload_path);
        } else {
            $upload_error = "✗ Failed to move uploaded file";
        }
    } else {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $upload_error = "✗ Upload error: " . ($error_messages[$file['error']] ?? 'Unknown error');
    }
}

// Get PHP upload settings
$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$file_uploads = ini_get('file_uploads') ? 'Enabled' : 'Disabled';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Test - Diagnostic Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 50px;
            background: #f8f9fa;
        }
        .test-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            padding: 15px;
            background: #d4edda;
            border-radius: 5px;
            margin: 15px 0;
        }
        .error {
            color: #dc3545;
            padding: 15px;
            background: #f8d7da;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info-table {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h2>Image Upload Diagnostic Tool</h2>
        <p class="text-muted">Test if image uploads are working correctly</p>

        <?php if ($upload_status): ?>
            <div class="success"><?php echo $upload_status; ?></div>
        <?php endif; ?>

        <?php if ($upload_error): ?>
            <div class="error"><?php echo $upload_error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="test_image" class="form-label">Select an image to test</label>
                <input type="file" class="form-control" id="test_image" name="test_image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Test Upload</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>

        <table class="table table-sm info-table">
            <thead>
                <tr>
                    <th colspan="2" class="bg-light">PHP Upload Configuration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>File Uploads</td>
                    <td><strong><?php echo $file_uploads; ?></strong></td>
                </tr>
                <tr>
                    <td>Max Upload Size</td>
                    <td><strong><?php echo $upload_max; ?></strong></td>
                </tr>
                <tr>
                    <td>Max POST Size</td>
                    <td><strong><?php echo $post_max; ?></strong></td>
                </tr>
                <tr>
                    <td>Uploads Directory</td>
                    <td><strong><?php echo is_writable('uploads') ? '✓ Writable' : '✗ Not Writable'; ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="alert alert-info mt-3">
            <strong>Note:</strong> This is a diagnostic tool. After testing, you can safely delete files in uploads/test/.
        </div>
    </div>
</body>
</html>
