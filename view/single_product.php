<?php
// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: all_product.php');
    exit();
}

// Fetch product details
$product = null;
try {
    $result = view_single_product_ctr($product_id);
    if ($result['success']) {
        $product = $result['product'];
    }
} catch (Exception $e) {
    error_log("Error loading product: " . $e->getMessage());
}

if (!$product) {
    header('Location: all_product.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?> - Taste of Africa</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['product_desc'] ?? '', 0, 160), ENT_QUOTES, 'UTF-8'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-color: #D19C97;
            --success-color: #28a745;
            --text-dark: #333;
            --text-light: #6c757d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
        }

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
        }

        .product-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .product-image-section {
            position: relative;
        }

        .product-main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .product-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--accent-color);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
        }

        .product-details-section {
            padding-left: 30px;
        }

        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }

        .breadcrumb-custom a {
            color: var(--text-light);
            text-decoration: none;
        }

        .breadcrumb-custom a:hover {
            color: var(--accent-color);
        }

        .product-category-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .product-title {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .product-brand {
            color: var(--text-light);
            font-size: 1.1em;
            margin-bottom: 20px;
        }

        .product-brand i {
            color: var(--accent-color);
        }

        .product-price {
            font-size: 3em;
            font-weight: 700;
            color: var(--success-color);
            margin-bottom: 25px;
        }

        .product-meta {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .meta-item {
            margin-bottom: 12px;
        }

        .meta-label {
            color: var(--text-light);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-value {
            color: var(--text-dark);
            font-weight: 500;
            font-size: 16px;
        }

        .product-description {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.3em;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }

        .description-text {
            color: var(--text-dark);
            font-size: 1.1em;
            line-height: 1.8;
        }

        .product-keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }

        .keyword-tag {
            background: var(--primary-gradient);
            color: white;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-add-cart {
            flex: 1;
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 12px;
            font-size: 1.2em;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-add-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-back {
            background: white;
            color: var(--text-dark);
            border: 2px solid var(--text-light);
            padding: 18px 40px;
            border-radius: 12px;
            font-size: 1.2em;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-back:hover {
            border-color: var(--accent-color);
            color: var(--accent-color);
            transform: translateY(-3px);
        }

        .product-id-badge {
            background: #e9ecef;
            color: var(--text-light);
            padding: 6px 15px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .product-container {
                padding: 20px;
            }

            .product-details-section {
                padding-left: 0;
                margin-top: 30px;
            }

            .product-title {
                font-size: 2em;
            }

            .product-price {
                font-size: 2.5em;
            }

            .product-main-image {
                height: 350px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Hidden CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">

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
                        <a class="nav-link-custom" href="all_product.php">
                            <i class="fas fa-shopping-bag me-1"></i>All Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom" href="cart.php" style="position: relative;">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                            <span class="cart-badge" id="cart-count" style="display: none; position: absolute; top: -5px; right: -10px; background: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; display: flex; align-items: center; justify-content: center; font-weight: bold;">0</span>
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

    <!-- Main Content -->
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-custom mt-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="breadcrumb-item"><a href="all_product.php">Products</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?></li>
            </ol>
        </nav>

        <!-- Product Details -->
        <div class="product-container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6">
                    <div class="product-image-section">
                        <?php
                        $image_url = !empty($product['product_image'])
                            ? '../' . htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8')
                            : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="500" height="500" viewBox="0 0 500 500"%3E%3Crect fill="%23f8f9fa" width="500" height="500"/%3E%3Ctext fill="%236c757d" font-family="sans-serif" font-size="24" dy="250" font-weight="bold" x="50%25" y="50%25" text-anchor="middle"%3ENo Image Available%3C/text%3E%3C/svg%3E';
                        ?>
                        <img src="<?php echo $image_url; ?>"
                             alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                             class="product-main-image"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'500\' height=\'500\' viewBox=\'0 0 500 500\'%3E%3Crect fill=\'%23f8f9fa\' width=\'500\' height=\'500\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'sans-serif\' font-size=\'24\' dy=\'250\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ENo Image Available%3C/text%3E%3C/svg%3E'">
                        <span class="product-badge">
                            <i class="fas fa-star me-1"></i>Featured
                        </span>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="col-lg-6">
                    <div class="product-details-section">
                        <!-- Category Badge -->
                        <span class="product-category-badge">
                            <i class="fas fa-folder me-1"></i><?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?>
                        </span>

                        <!-- Product Title -->
                        <h1 class="product-title"><?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?></h1>

                        <!-- Brand -->
                        <p class="product-brand">
                            <i class="fas fa-tag me-2"></i>Brand: <strong><?php echo htmlspecialchars($product['brand_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </p>

                        <!-- Price -->
                        <div class="product-price">
                            GHS <?php echo number_format($product['product_price'], 2); ?>
                        </div>

                        <!-- Product Meta Info -->
                        <div class="product-meta">
                            <div class="meta-item">
                                <span class="meta-label">Product ID:</span>
                                <span class="meta-value">#<?php echo $product['product_id']; ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Category:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($product['cat_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Brand:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($product['brand_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if (!empty($product['product_desc'])): ?>
                            <div class="product-description">
                                <h3 class="section-title"><i class="fas fa-info-circle me-2"></i>Description</h3>
                                <p class="description-text"><?php echo nl2br(htmlspecialchars($product['product_desc'], ENT_QUOTES, 'UTF-8')); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Keywords -->
                        <?php if (!empty($product['product_keywords'])): ?>
                            <div class="mb-4">
                                <h3 class="section-title"><i class="fas fa-tags me-2"></i>Tags</h3>
                                <div class="product-keywords">
                                    <?php
                                    $keywords = explode(',', $product['product_keywords']);
                                    foreach ($keywords as $keyword):
                                        $keyword = trim($keyword);
                                        if (!empty($keyword)):
                                    ?>
                                        <span class="keyword-tag"><?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                                <label style="font-weight: 600; color: var(--text-dark);">Quantity:</label>
                                <input type="number" id="product-quantity" value="1" min="1" max="100"
                                       style="width: 80px; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 16px; font-weight: 600;">
                            </div>
                            <button class="btn-add-cart" onclick="addToCart(<?php echo $product['product_id']; ?>, document.getElementById('product-quantity').value)">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                            <a href="all_product.php" class="btn-back">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Spacing -->
    <div style="height: 60px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
    <script>
        // Load cart count on page load
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                // Get cart count from server
                const response = await fetch('../actions/get_cart_count_action.php');
                if (response.ok) {
                    const data = await response.json();
                    if (data.count !== undefined) {
                        updateCartBadge(data.count);
                    }
                }
            } catch (error) {
                console.log('Error loading cart count:', error);
            }
        });
    </script>
</body>
</html>
