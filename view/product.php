<?php
// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/category_controller.php';
require_once '../controllers/brand_controller.php';

// Get all products, categories, and brands
$all_products = [];
$all_categories = [];
$all_brands = [];
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$selected_brand = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch categories and brands for filters
try {
    $categories_result = get_categories_ctr();
    $all_categories = $categories_result['categories'] ?? [];

    $brands_result = get_all_brands_ctr();
    $all_brands = $brands_result['brands'] ?? [];
} catch (Exception $e) {
    error_log("Error loading filters: " . $e->getMessage());
}

// Fetch products based on filters
try {
    if (!empty($search_keyword)) {
        $products_result = search_products_ctr($search_keyword);
    } elseif ($selected_category > 0) {
        $products_result = get_products_by_category_ctr($selected_category);
    } elseif ($selected_brand > 0) {
        $products_result = get_products_by_brand_ctr($selected_brand);
    } else {
        $products_result = get_all_products_ctr();
    }

    $all_products = $products_result['products'] ?? [];
} catch (Exception $e) {
    error_log("Error loading products: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Taste of Africa</title>
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
            padding: 30px 20px;
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
            margin-bottom: 30px;
            text-align: center;
        }

        .nav-links a {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 25px;
            margin: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-links a:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .filters-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .filters-section h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.3em;
        }

        .filter-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group select,
        .filter-group input {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .btn {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .products-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .products-section h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8em;
            font-weight: 400;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }

        .product-image {
            width: 100%;
            height: 250px;
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
        }

        .product-keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }

        .keyword-tag {
            background: #e9ecef;
            color: #495057;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
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

        .category-badge {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 10px;
        }

        .brand-badge {
            background: #ffc107;
            color: #212529;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 10px;
            margin-left: 5px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header h1 {
                font-size: 2em;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .filter-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Our Products</h1>
            <p>Discover authentic African products</p>
        </div>

        <div class="nav-links">
            <a href="../index.php">Home</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="../admin/product.php">Manage Products</a>
                    <a href="../admin/category.php">Categories</a>
                    <a href="../admin/brand.php">Brands</a>
                <?php endif; ?>
                <a href="../dashboard.php">Dashboard</a>
                <a href="../login/logout.php">Logout</a>
            <?php else: ?>
                <a href="../login/login.php">Login</a>
                <a href="../login/register.php">Register</a>
            <?php endif; ?>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <h3>Filter Products</h3>
            <form method="GET" action="product.php">
                <div class="filter-group">
                    <select name="category" id="category">
                        <option value="">All Categories</option>
                        <?php foreach ($all_categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['cat_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php echo $selected_category == $category['cat_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="brand" id="brand">
                        <option value="">All Brands</option>
                        <?php foreach ($all_brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand['brand_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php echo $selected_brand == $brand['brand_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text"
                           name="search"
                           placeholder="Search products..."
                           value="<?php echo htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div>
                    <button type="submit" class="btn">Apply Filters</button>
                    <a href="product.php" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Products Section -->
        <div class="products-section">
            <h2>
                <?php
                if (!empty($search_keyword)) {
                    echo "Search Results for: " . htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8');
                } elseif ($selected_category > 0) {
                    $cat_name = array_filter($all_categories, function($c) use ($selected_category) {
                        return $c['cat_id'] == $selected_category;
                    });
                    $cat_name = !empty($cat_name) ? reset($cat_name)['cat_name'] : 'Category';
                    echo htmlspecialchars($cat_name, ENT_QUOTES, 'UTF-8') . " Products";
                } elseif ($selected_brand > 0) {
                    $brand_name = array_filter($all_brands, function($b) use ($selected_brand) {
                        return $b['brand_id'] == $selected_brand;
                    });
                    $brand_name = !empty($brand_name) ? reset($brand_name)['brand_name'] : 'Brand';
                    echo htmlspecialchars($brand_name, ENT_QUOTES, 'UTF-8') . " Products";
                } else {
                    echo "All Products";
                }
                ?>
                <span style="font-size: 0.6em; color: #6c757d;">(<?php echo count($all_products); ?> items)</span>
            </h2>

            <?php if (empty($all_products)): ?>
                <div class="empty-state">
                    <h3>No products found</h3>
                    <p>
                        <?php if (!empty($search_keyword) || $selected_category > 0 || $selected_brand > 0): ?>
                            Try adjusting your filters or <a href="product.php">view all products</a>
                        <?php else: ?>
                            Check back later for new products!
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($all_products as $product): ?>
                        <div class="product-card">
                            <?php
                            $image_url = !empty($product['product_image'])
                                ? '../' . htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8')
                                : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="250" viewBox="0 0 300 250"%3E%3Crect fill="%23f8f9fa" width="300" height="250"/%3E%3Ctext fill="%236c757d" font-family="sans-serif" font-size="16" dy="125" font-weight="bold" x="50%25" y="50%25" text-anchor="middle"%3ENo Image%3C/text%3E%3C/svg%3E';
                            ?>
                            <img src="<?php echo $image_url; ?>"
                                 alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                 class="product-image"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'250\' viewBox=\'0 0 300 250\'%3E%3Crect fill=\'%23f8f9fa\' width=\'300\' height=\'250\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'16\' dy=\'125\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ENo Image%3C/text%3E%3C/svg%3E'">

                            <div class="product-details">
                                <div>
                                    <span class="category-badge"><?php echo htmlspecialchars($product['cat_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="brand-badge"><?php echo htmlspecialchars($product['brand_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>

                                <h3 class="product-title"><?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?></h3>

                                <div class="product-price">GHS <?php echo number_format($product['product_price'], 2); ?></div>

                                <?php if (!empty($product['product_desc'])): ?>
                                    <p class="product-description">
                                        <?php
                                        $desc = htmlspecialchars($product['product_desc'], ENT_QUOTES, 'UTF-8');
                                        echo strlen($desc) > 120 ? substr($desc, 0, 120) . '...' : $desc;
                                        ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($product['product_keywords'])): ?>
                                    <div class="product-keywords">
                                        <?php
                                        $keywords = explode(',', $product['product_keywords']);
                                        foreach (array_slice($keywords, 0, 3) as $keyword):
                                            $keyword = trim($keyword);
                                            if (!empty($keyword)):
                                        ?>
                                            <span class="keyword-tag"><?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
