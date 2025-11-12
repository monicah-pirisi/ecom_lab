<?php
// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/category_controller.php';
require_once '../controllers/brand_controller.php';

// Get filter parameters
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$selected_brand = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12; // Products per page
$offset = ($current_page - 1) * $limit;

// Fetch categories and brands for filters
$all_categories = [];
$all_brands = [];

try {
    $categories_result = get_categories_ctr();
    $all_categories = $categories_result['categories'] ?? [];

    $brands_result = get_all_brands_ctr();
    $all_brands = $brands_result['brands'] ?? [];
} catch (Exception $e) {
    error_log("Error loading filters: " . $e->getMessage());
}

// Fetch products based on filters
$products_data = [];
try {
    if ($selected_category > 0) {
        $products_data = filter_products_by_category_ctr($selected_category, $limit, $offset);
    } elseif ($selected_brand > 0) {
        $products_data = filter_products_by_brand_ctr($selected_brand, $limit, $offset);
    } else {
        $products_data = view_all_products_ctr($limit, $offset);
    }
} catch (Exception $e) {
    error_log("Error loading products: " . $e->getMessage());
    $products_data = ['success' => false, 'products' => [], 'total' => 0];
}

$all_products = $products_data['products'] ?? [];
$total_products = $products_data['total'] ?? 0;
$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop All Products - Taste of Africa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-color: #D19C97;
            --text-dark: #333;
            --text-light: #6c757d;
            --border-color: #e9ecef;
            --hover-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
            min-height: 100vh;
        }

        /* Header Section */
        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 80px 0 60px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="rgba(255,255,255,0.1)"/></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }

        .page-header h1 {
            font-size: 3em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 15px;
        }

        .page-header p {
            font-size: 1.3em;
            opacity: 0.9;
        }

        /* Navigation */
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .nav-link-custom {
            color: var(--text-dark);
            font-weight: 500;
            padding: 8px 15px;
            margin: 0 5px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .nav-link-custom:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-2px);
        }

        /* Filters Section */
        .filters-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border-left: 5px solid var(--accent-color);
        }

        .filter-title {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .filter-select {
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(209, 156, 151, 0.2);
        }

        .filter-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Product Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--hover-shadow);
        }

        .product-image-wrapper {
            position: relative;
            overflow: hidden;
            height: 250px;
            background: #f8f9fa;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(209, 156, 151, 0.95);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .product-details {
            padding: 20px;
        }

        .product-category {
            color: var(--accent-color);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .product-title {
            color: var(--text-dark);
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 10px;
            line-height: 1.4;
            min-height: 50px;
        }

        .product-brand {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 12px;
        }

        .product-price {
            color: #28a745;
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .product-keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 15px;
            min-height: 30px;
        }

        .keyword-tag {
            background: #f8f9fa;
            color: var(--text-light);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
        }

        .btn-add-cart {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add-cart:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin: 40px 0;
        }

        .pagination {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .page-link-custom {
            padding: 10px 18px;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-link-custom:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        .page-link-custom.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        .page-link-custom.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 5em;
            color: var(--accent-color);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        /* Product Count */
        .product-count {
            color: var(--text-light);
            font-size: 1.1em;
            margin-bottom: 25px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2em;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php" style="color: var(--text-dark); font-weight: 700; font-size: 1.5em;">
                <i class="fas fa-utensils" style="color: var(--accent-color);"></i> Taste of Africa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link-custom" href="../index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom active" href="all_product.php">
                            <i class="fas fa-shopping-bag me-1"></i>All Products
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link-custom" href="../admin/product.php">
                                    <i class="fas fa-cog me-1"></i>Manage
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link-custom" href="../dashboard.php">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-custom" href="../login/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link-custom" href="../login/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-custom" href="../login/register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container text-center">
            <h1 class="animate__animated animate__fadeInDown">
                <i class="fas fa-store me-3"></i>Explore Our Products
            </h1>
            <p class="animate__animated animate__fadeInUp">Discover authentic African products handpicked for you</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Filters Section -->
        <div class="filters-section">
            <h3 class="filter-title"><i class="fas fa-filter me-2"></i>Filter Products</h3>
            <form method="GET" action="all_product.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($all_categories as $category): ?>
                                <option value="<?php echo $category['cat_id']; ?>"
                                        <?php echo $selected_category == $category['cat_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Brand</label>
                        <select name="brand" class="filter-select">
                            <option value="">All Brands</option>
                            <?php foreach ($all_brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>"
                                        <?php echo $selected_brand == $brand['brand_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        <a href="all_product.php" class="filter-btn" style="background: #6c757d; text-align: center; text-decoration: none;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Product Count -->
        <?php if (!empty($all_products)): ?>
            <div class="product-count">
                <strong><?php echo $total_products; ?></strong> product<?php echo $total_products != 1 ? 's' : ''; ?> found
                <?php if ($current_page > 1): ?>
                    - Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <?php if (empty($all_products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Products Found</h3>
                <p>Try adjusting your filters or check back later for new items!</p>
                <a href="all_product.php" class="btn filter-btn mt-3" style="width: auto;">View All Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($all_products as $product): ?>
                    <div class="product-card" onclick="window.location.href='single_product.php?id=<?php echo $product['product_id']; ?>'">
                        <div class="product-image-wrapper">
                            <?php
                            $image_url = !empty($product['product_image'])
                                ? '../' . htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8')
                                : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="250" viewBox="0 0 300 250"%3E%3Crect fill="%23f8f9fa" width="300" height="250"/%3E%3Ctext fill="%236c757d" font-family="sans-serif" font-size="16" dy="125" font-weight="bold" x="50%25" y="50%25" text-anchor="middle"%3ENo Image%3C/text%3E%3C/svg%3E';
                            ?>
                            <img src="<?php echo $image_url; ?>"
                                 alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                 class="product-image"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'250\' viewBox=\'0 0 300 250\'%3E%3Crect fill=\'%23f8f9fa\' width=\'300\' height=\'250\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'16\' dy=\'125\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            <span class="product-badge">New</span>
                        </div>
                        <div class="product-details">
                            <div class="product-category"><?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></div>
                            <h3 class="product-title"><?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="product-brand">
                                <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product['brand_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <div class="product-price">GHS <?php echo number_format($product['product_price'], 2); ?></div>

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

                            <button class="btn-add-cart" onclick="event.stopPropagation(); alert('Add to cart feature coming soon!');">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?><?php echo $selected_category ? '&category=' . $selected_category : ''; ?><?php echo $selected_brand ? '&brand=' . $selected_brand : ''; ?>"
                               class="page-link-custom">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?><?php echo $selected_category ? '&category=' . $selected_category : ''; ?><?php echo $selected_brand ? '&brand=' . $selected_brand : ''; ?>"
                               class="page-link-custom <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?><?php echo $selected_category ? '&category=' . $selected_category : ''; ?><?php echo $selected_brand ? '&brand=' . $selected_brand : ''; ?>"
                               class="page-link-custom">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Footer Spacing -->
    <div style="height: 60px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
