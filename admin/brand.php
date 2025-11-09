<?php
// Start output buffering to prevent any unwanted output
ob_start();

// Error reporting - remove in production
error_reporting(0);
ini_set('display_errors', 0);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files with error handling
$core_file = '../settings/core.php';
$category_controller_file = '../controllers/category_controller.php';

if (!file_exists($core_file)) {
    die('Core configuration file not found. Please check your file paths.');
}

if (!file_exists($category_controller_file)) {
    die('Category controller file not found. Please check your file paths.');
}

require_once $core_file;
require_once $category_controller_file;

// Check if user is logged in
if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Check if user is admin
if (!function_exists('isAdmin') || !isAdmin()) {
    header('Location: ../login/login.php');
    exit();
}

// Initialize variables
$all_categories = [];

// Get all categories for dropdown
try {
    if (function_exists('get_categories_ctr')) {
        $categories_result = get_categories_ctr();
        $all_categories = isset($categories_result['categories']) ? $categories_result['categories'] : [];
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error loading categories: ' . $e->getMessage();
}

// Clean any output buffer content
ob_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Management - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .nav-links {
            margin-bottom: 20px;
            text-align: center;
        }

        .nav-links a {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 25px;
            margin: 0 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-links a:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        .form-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .form-section h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8em;
            font-weight: 400;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            background-color: #fff;
        }

        .btn {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            border: 2px solid transparent;
        }

        .btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }

        .btn-danger {
            background: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 4px 15px rgba(220,53,69,0.3);
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            background: #e0a800;
            box-shadow: 0 4px 15px rgba(255,193,7,0.3);
        }

        .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
            box-shadow: 0 4px 15px rgba(108,117,125,0.3);
        }

        .brands-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }

        .brands-section h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8em;
            font-weight: 400;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }

        .category-group {
            margin-bottom: 40px;
        }

        .category-group:last-child {
            margin-bottom: 0;
        }

        .category-group h3 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.4em;
            padding: 15px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .brand-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .brand-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .brand-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }

        .brand-card h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3em;
            font-weight: 600;
        }

        .brand-card p {
            color: #6c757d;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .brand-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .brand-actions .btn {
            padding: 10px 20px;
            font-size: 14px;
            flex: 1;
            min-width: 80px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state h3 {
            margin-bottom: 15px;
            color: #495057;
            font-size: 1.5em;
        }

        .empty-state p {
            font-size: 1.1em;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }

        .modal-close:hover,
        .modal-close:focus {
            color: #000;
        }

        .modal-icon {
            font-size: 60px;
            margin: 20px 0;
        }

        .modal-success .modal-icon {
            color: #28a745;
        }

        .modal-error .modal-icon {
            color: #dc3545;
        }

        .modal-message {
            font-size: 18px;
            margin: 20px 0;
            color: #333;
            white-space: pre-line;
        }

        .modal-btn {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .modal-btn:hover {
            background: #0056b3;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 2em;
            }

            .form-section,
            .brands-section {
                padding: 20px;
            }

            .brand-grid {
                grid-template-columns: 1fr;
            }

            .brand-actions {
                flex-direction: column;
            }

            .brand-actions .btn {
                flex: none;
            }

            .nav-links a {
                display: block;
                margin: 5px 0;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.8em;
            }

            .form-section h2,
            .brands-section h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Brand Management</h1>
            <p>Manage your brands organized by categories</p>
        </div>

        <div class="nav-links">
            <a href="../dashboard.php">← Dashboard</a>
            <a href="category.php">Manage Categories</a>
            <a href="../login/logout.php">Logout</a>
        </div>

        <!-- Create/Update Brand Form -->
        <div class="form-section">
            <h2>Create New Brand</h2>
            <?php if (empty($all_categories)): ?>
                <div class="alert alert-error">
                    You need to create categories first before adding brands.
                    <a href="category.php" style="color: #721c24; text-decoration: underline; font-weight: bold;">Go to Category Management</a>
                </div>
            <?php else: ?>
                <form id="brandForm">
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">

                    <div class="form-group">
                        <label for="brand_name">Brand Name:</label>
                        <input type="text"
                               id="brand_name"
                               name="brand_name"
                               required
                               maxlength="100"
                               placeholder="Enter brand name">
                    </div>

                    <div class="form-group">
                        <label for="brand_cat">Category:</label>
                        <select id="brand_cat" name="brand_cat" required>
                            <option value="">Select Category</option>
                            <?php if (!empty($all_categories)): ?>
                                <?php foreach ($all_categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['cat_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn">Create Brand</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- Display Brands Grouped by Category -->
        <div class="brands-section">
            <h2>Your Brands</h2>
            <div class="empty-state">
                <h3>Loading brands...</h3>
                <p>Please wait while we load your brands.</p>
            </div>
        </div>
    </div>

    <script src="../js/brand.js"></script>
</body>
</html>
