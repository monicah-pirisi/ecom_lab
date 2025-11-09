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
$brand_controller_file = '../controllers/brand_controller.php';

if (!file_exists($core_file)) {
    die('Core configuration file not found. Please check your file paths.');
}

if (!file_exists($category_controller_file)) {
    die('Category controller file not found. Please check your file paths.');
}

if (!file_exists($brand_controller_file)) {
    die('Brand controller file not found. Please check your file paths.');
}

require_once $core_file;
require_once $category_controller_file;
require_once $brand_controller_file;

// Check if user is logged in
if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Initialize variables
$all_categories = [];
$all_brands = [];
$user_role = $_SESSION['user_role'] ?? 0;

// Get all categories for dropdown
try {
    if (function_exists('get_categories_ctr')) {
        $categories_result = get_categories_ctr();
        $all_categories = isset($categories_result['categories']) ? $categories_result['categories'] : [];
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error loading categories: ' . $e->getMessage();
}

// Get all brands for dropdown
try {
    if (function_exists('get_all_brands_ctr')) {
        $brands_result = get_all_brands_ctr();
        $all_brands = isset($brands_result['brands']) ? $brands_result['brands'] : [];
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error loading brands: ' . $e->getMessage();
}

// Clean any output buffer content
ob_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Taste of Africa</title>
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
            max-width: 1400px;
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group-full {
            grid-column: 1 / -1;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #fff;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            background-color: #fff;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: inline-block;
            padding: 15px;
            cursor: pointer;
            background: #f8f9fa;
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
            width: 100%;
        }

        .file-input-label:hover {
            border-color: #007bff;
            background: #f0f7ff;
        }

        .file-name {
            margin-top: 10px;
            font-size: 14px;
            color: #6c757d;
        }

        .image-preview {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-preview-item {
            position: relative;
            width: 150px;
            height: 150px;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .image-preview-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
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

        .products-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }

        .products-section h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8em;
            font-weight: 400;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #f8f9fa;
        }

        .product-details {
            padding: 20px;
        }

        .product-title {
            color: #333;
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .product-price {
            color: #007bff;
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .product-info {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .product-description {
            color: #495057;
            font-size: 14px;
            margin: 15px 0;
            line-height: 1.5;
            max-height: 60px;
            overflow: hidden;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .product-actions .btn {
            padding: 10px 20px;
            font-size: 14px;
            flex: 1;
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
            .products-section {
                padding: 20px;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .nav-links a {
                display: block;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Product Management</h1>
            <p>Add and manage your products with ease</p>
        </div>

        <div class="nav-links">
            <a href="../dashboard.php">← Dashboard</a>
            <a href="category.php">Categories</a>
            <a href="brand.php">Brands</a>
            <a href="../login/logout.php">Logout</a>
        </div>

        <!-- Create/Update Product Form -->
        <div class="form-section">
            <h2 id="formTitle">Add New Product</h2>
            <?php if (empty($all_categories) || empty($all_brands)): ?>
                <div class="alert alert-error">
                    You need to create categories and brands before adding products.
                    <a href="category.php" style="color: #721c24; text-decoration: underline; font-weight: bold;">Go to Category Management</a> |
                    <a href="brand.php" style="color: #721c24; text-decoration: underline; font-weight: bold;">Go to Brand Management</a>
                </div>
            <?php else: ?>
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    <input type="hidden" id="product_id" name="product_id" value="">

                    <div class="form-grid">
                        <div class="form-group form-group-full">
                            <label for="product_title">Product Title *</label>
                            <input type="text"
                                   id="product_title"
                                   name="product_title"
                                   required
                                   maxlength="200"
                                   placeholder="Enter product title">
                        </div>

                        <div class="form-group">
                            <label for="product_cat">Category *</label>
                            <select id="product_cat" name="product_cat" required>
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

                        <div class="form-group">
                            <label for="product_brand">Brand *</label>
                            <select id="product_brand" name="product_brand" required>
                                <option value="">Select Brand</option>
                                <?php if (!empty($all_brands)): ?>
                                    <?php foreach ($all_brands as $brand): ?>
                                        <option value="<?php echo htmlspecialchars($brand['brand_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="product_price">Price (GHS) *</label>
                            <input type="number"
                                   id="product_price"
                                   name="product_price"
                                   required
                                   min="0"
                                   step="0.01"
                                   placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label for="product_keywords">Keywords (comma-separated)</label>
                            <input type="text"
                                   id="product_keywords"
                                   name="product_keywords"
                                   maxlength="500"
                                   placeholder="e.g., jollof, rice, spicy">
                        </div>

                        <div class="form-group form-group-full">
                            <label for="product_desc">Description</label>
                            <textarea id="product_desc"
                                      name="product_desc"
                                      maxlength="1000"
                                      placeholder="Describe your product..."></textarea>
                        </div>

                        <div class="form-group form-group-full">
                            <label for="product_images">Product Images (Multiple - JPG, PNG, GIF, WEBP - Max 5MB each)</label>
                            <div class="file-input-wrapper">
                                <input type="file"
                                       id="product_images"
                                       name="product_images[]"
                                       accept="image/*"
                                       multiple>
                                <label for="product_images" class="file-input-label">
                                    📷 Click to upload images (you can select multiple)
                                </label>
                            </div>
                            <div class="file-name" id="fileName"></div>
                            <div class="image-preview" id="imagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px;">
                                <!-- Multiple image previews will be shown here -->
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 30px; display: flex; gap: 15px;">
                        <button type="submit" class="btn">Add Product</button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn" style="display: none;">Cancel</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- Display Products -->
        <div class="products-section">
            <h2>Your Products</h2>
            <div id="productsContainer">
                <div class="empty-state">
                    <h3>Loading products...</h3>
                    <p>Please wait while we load your products.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/product.js"></script>
</body>
</html>
