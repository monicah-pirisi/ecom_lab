<?php
// Start session and include core files
session_start();
require_once '../../settings/core.php';
require_once '../../controllers/product_controller.php';

// Check if user is logged in and is a restaurant owner
if (!isLoggedIn()) {
    header('Location: ../../login/login.php');
    exit();
}

$user_role = $_SESSION['user_role'] ?? null;
if ($user_role != 2) {
    header('Location: ../all_product.php');
    exit();
}

$owner_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'] ?? 'Restaurant Owner';

// Get owner's products
$result = get_products_by_user_ctr($owner_id);
$products = $result['products'] ?? [];
$product_count = count($products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Taste of Africa</title>
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

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 60px 0 40px;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 10px;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 15px;
        }

        .btn-back {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .btn-add {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-add:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: white;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px);
        }

        .product-image-container {
            width: 100%;
            height: 250px;
            overflow: hidden;
            background: #f8f9fa;
            position: relative;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        .product-details {
            padding: 20px;
        }

        .product-title {
            font-size: 1.2em;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-category {
            color: var(--text-light);
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .product-price {
            color: #28a745;
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-edit {
            flex: 1;
            background: #667eea;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn-edit:hover {
            background: #5568d3;
            color: white;
        }

        .btn-delete {
            flex: 1;
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 100px;
            color: #e9ecef;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .stats-summary {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .stats-summary h4 {
            color: var(--text-dark);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-box h3 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-box p {
            margin: 0;
            opacity: 0.9;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 9999;
            min-width: 300px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification-success {
            border-left: 5px solid #28a745;
        }

        .notification-error {
            border-left: 5px solid #dc3545;
        }
    </style>
</head>
<body>
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-box-open"></i> My Products</h1>
            <p>Manage your product catalog</p>
        </div>
    </div>

    <!-- Content -->
    <div class="content-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="../../dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="../../admin/product.php" class="btn-add">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <?php if ($product_count > 0): ?>
            <!-- Stats Summary -->
            <div class="stats-summary">
                <h4>Product Overview</h4>
                <div class="stats-grid">
                    <div class="stat-box">
                        <h3><?php echo $product_count; ?></h3>
                        <p>Total Products</p>
                    </div>
                    <div class="stat-box">
                        <h3>$<?php
                            $total_value = 0;
                            foreach ($products as $product) {
                                $total_value += $product['product_price'];
                            }
                            echo number_format($total_value, 2);
                        ?></h3>
                        <p>Total Inventory Value</p>
                    </div>
                    <div class="stat-box">
                        <h3>$<?php
                            $avg_price = $total_value / $product_count;
                            echo number_format($avg_price, 2);
                        ?></h3>
                        <p>Average Price</p>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-product-id="<?php echo $product['product_id']; ?>"
                         onclick="window.location.href='../single_product.php?id=<?php echo $product['product_id']; ?>'">
                        <div class="product-image-container">
                            <?php
                            $image_path = !empty($product['product_image'])
                                ? '../../' . $product['product_image']
                                : '../../images/default-product.png';
                            ?>
                            <img src="<?php echo htmlspecialchars($image_path); ?>"
                                 alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                 class="product-image"
                                 onerror="this.src='../../images/default-product.png'">
                        </div>
                        <div class="product-details">
                            <h3 class="product-title">
                                <?php echo htmlspecialchars($product['product_title']); ?>
                            </h3>
                            <p class="product-category">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?>
                            </p>
                            <div class="product-price">
                                $<?php echo number_format($product['product_price'], 2); ?>
                            </div>
                            <div class="product-actions" onclick="event.stopPropagation()">
                                <a href="../../admin/product.php#product-<?php echo $product['product_id']; ?>"
                                   class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn-delete"
                                        onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Products Yet</h3>
                <p>You haven't added any products yet. Start by adding your first product!</p>
                <a href="../../admin/add_product.php" class="btn-add">
                    <i class="fas fa-plus"></i> Add Your First Product
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get CSRF token
        function getCSRFToken() {
            const tokenInput = document.querySelector('input[name="csrf_token"]');
            return tokenInput ? tokenInput.value : '';
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 24px;">${type === 'success' ? '✓' : '✗'}</span>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Delete product
        async function deleteProduct(productId) {
            if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                return;
            }

            try {
                const formData = new URLSearchParams();
                formData.append('product_id', productId);
                formData.append('csrf_token', getCSRFToken());

                const response = await fetch('../../actions/delete_product_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');

                    // Remove the card from DOM
                    const card = document.querySelector(`[data-product-id="${productId}"]`);
                    if (card) {
                        card.remove();
                    }

                    // Reload page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification(data.message || 'Failed to delete product', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        }
    </script>
</body>
</html>
