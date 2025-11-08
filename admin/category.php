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
$controller_file = '../controllers/category_controller.php';

if (!file_exists($core_file)) {
    die('Core configuration file not found. Please check your file paths.');
}

if (!file_exists($controller_file)) {
    die('Category controller file not found. Please check your file paths.');
}

require_once $core_file;
require_once $controller_file;

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
$categories = [];
$category_types = [];
$edit_category = null;

// Get all categories with error handling
try {
    if (function_exists('get_categories_grouped_ctr')) {
        $categories_result = get_categories_grouped_ctr();
        $categories = isset($categories_result['grouped_categories']) ? $categories_result['grouped_categories'] : [];
        $category_types = isset($categories_result['category_types']) ? $categories_result['category_types'] : [];
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error loading categories: ' . $e->getMessage();
}

// Handle edit form display with error handling
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    try {
        if (function_exists('get_category_by_id_ctr')) {
            $edit_result = get_category_by_id_ctr($edit_id);
            if (isset($edit_result['success']) && $edit_result['success']) {
                $edit_category = $edit_result['category'];
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error loading category for editing: ' . $e->getMessage();
    }
}

// Clean any output buffer content
ob_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - Admin Panel</title>
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

        .categories-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }

        .categories-section h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8em;
            font-weight: 400;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }

        .category-type {
            margin-bottom: 40px;
        }

        .category-type:last-child {
            margin-bottom: 0;
        }

        .category-type h3 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.4em;
            padding: 15px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }

        .category-card h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3em;
            font-weight: 600;
        }

        .category-card p {
            color: #6c757d;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .category-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .category-actions .btn {
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
            .categories-section {
                padding: 20px;
            }

            .category-grid {
                grid-template-columns: 1fr;
            }

            .category-actions {
                flex-direction: column;
            }

            .category-actions .btn {
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
            .categories-section h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Category Management</h1>
            <p>Manage your e-commerce platform categories</p>
        </div>

        <div class="nav-links">
            <a href="../dashboard.php">← Dashboard</a>
            <a href="../login/logout.php">Logout</a>
        </div>

        <?php if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Create/Update Category Form -->
        <div class="form-section">
            <h2><?php echo $edit_category ? 'Update Category' : 'Create New Category'; ?></h2>
            <form action="../actions/<?php echo $edit_category ? 'update_category_action.php' : 'create_category_action.php'; ?>" method="POST" novalidate>
                <?php if ($edit_category): ?>
                    <input type="hidden" name="cat_id" value="<?php echo htmlspecialchars($edit_category['cat_id'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="cat_name">Category Name:</label>
                    <input type="text" 
                           id="cat_name" 
                           name="cat_name" 
                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['cat_name'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                           required 
                           maxlength="100"
                           placeholder="Enter category name">
                </div>

                <div class="form-group">
                    <label for="cat_type">Category Type:</label>
                    <select id="cat_type" name="cat_type" required>
                        <option value="">Select Category Type</option>
                        <?php if (!empty($category_types)): ?>
                            <?php foreach ($category_types as $type_key => $type_label): ?>
                                <option value="<?php echo htmlspecialchars($type_key, ENT_QUOTES, 'UTF-8'); ?>" 
                                        <?php echo ($edit_category && isset($edit_category['cat_type']) && $edit_category['cat_type'] === $type_key) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn">
                        <?php echo $edit_category ? 'Update Category' : 'Create Category'; ?>
                    </button>
                    
                    <?php if ($edit_category): ?>
                        <a href="category.php" class="btn btn-secondary" style="margin-left: 15px;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Display Categories -->
        <div class="categories-section">
            <h2>Your Categories</h2>
            
            <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <h3>No categories found</h3>
                    <p>Create your first category using the form above.</p>
                </div>
            <?php else: ?>
                <?php foreach ($category_types as $type_key => $type_label): ?>
                    <?php if (isset($categories[$type_key]) && !empty($categories[$type_key])): ?>
                        <div class="category-type">
                            <h3><?php echo htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="category-grid">
                                <?php foreach ($categories[$type_key] as $category): ?>
                                    <div class="category-card">
                                        <h4><?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <p>ID: <?php echo htmlspecialchars($category['cat_id'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <div class="category-actions">
                                            <a href="?edit=<?php echo urlencode($category['cat_id']); ?>" class="btn btn-warning">Edit</a>
                                            <a href="../actions/delete_category_action.php?cat_id=<?php echo urlencode($category['cat_id']); ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">Delete</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const catName = document.getElementById('cat_name').value.trim();
                    const catType = document.getElementById('cat_type').value;
                    
                    if (!catName) {
                        alert('Please enter a category name.');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (!catType) {
                        alert('Please select a category type.');
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>