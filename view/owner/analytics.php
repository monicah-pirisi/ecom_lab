<?php
// Start session and include core files
session_start();
require_once '../../settings/core.php';
require_once '../../controllers/restaurant_controller.php';
require_once '../../controllers/product_controller.php';
require_once '../../controllers/order_controller.php';

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

// Get analytics data
$analytics = get_owner_analytics_ctr($owner_id);
$products_result = get_products_by_user_ctr($owner_id);
$products = $products_result['products'] ?? [];
$orders = get_customer_orders_ctr($owner_id);
$reviews = get_owner_reviews_ctr($owner_id);

// Calculate statistics
$total_products = count($products);
$total_orders = is_array($orders) ? count($orders) : 0;
$total_reviews = is_array($reviews) ? count($reviews) : 0;

// Calculate total revenue
$total_revenue = 0;
if ($orders && is_array($orders)) {
    foreach ($orders as $order) {
        $order_details = get_order_details_ctr($order['order_id']);
        if ($order_details && is_array($order_details)) {
            foreach ($order_details as $detail) {
                $total_revenue += ($detail['product_price'] ?? 0) * ($detail['qty'] ?? 0);
            }
        }
    }
}

// Get recent orders (last 5)
$recent_orders = is_array($orders) ? array_slice($orders, 0, 5) : [];

// Calculate product inventory value
$inventory_value = 0;
foreach ($products as $product) {
    $inventory_value += $product['product_price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Taste of Africa</title>
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
            max-width: 1400px;
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
            margin-bottom: 30px;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary-gradient);
        }

        .stat-card.revenue::before {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .stat-card.orders::before {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        }

        .stat-card.reviews::before {
            background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .stat-card .stat-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card.revenue .stat-icon {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .stat-card.orders .stat-icon {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        }

        .stat-card.reviews .stat-icon {
            background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 1.1em;
            font-weight: 500;
        }

        .section-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .section-card h3 {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .order-item {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
        }

        .order-item:hover {
            background: #f8f9fa;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-info {
            flex: 1;
        }

        .order-id {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .order-date {
            color: var(--text-light);
            font-size: 0.9em;
        }

        .order-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 60px;
            color: #e9ecef;
            margin-bottom: 15px;
        }

        .review-item {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .reviewer-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .review-rating {
            color: #ffc107;
        }

        .review-restaurant {
            color: var(--text-light);
            font-size: 0.9em;
            margin-bottom: 8px;
        }

        .review-comment {
            color: var(--text-dark);
            line-height: 1.6;
        }

        .review-date {
            color: var(--text-light);
            font-size: 0.85em;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-chart-line"></i> Business Analytics</h1>
            <p>Track your performance and growth</p>
        </div>
    </div>

    <!-- Content -->
    <div class="content-container">
        <a href="../../dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="stat-value"><?php echo $analytics['total_restaurants']; ?></div>
                <div class="stat-label">Total Restaurants</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-value"><?php echo $total_products; ?></div>
                <div class="stat-label">Total Products</div>
            </div>

            <div class="stat-card revenue">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card orders">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="stat-card reviews">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-value"><?php echo number_format($analytics['average_rating'], 1); ?></div>
                <div class="stat-label">Average Rating</div>
            </div>

            <div class="stat-card reviews">
                <div class="stat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-value"><?php echo $total_reviews; ?></div>
                <div class="stat-label">Total Reviews</div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Orders -->
            <div class="col-lg-6">
                <div class="section-card">
                    <h3><i class="fas fa-shopping-bag"></i> Recent Orders</h3>
                    <?php if (!empty($recent_orders)): ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                                    <div class="order-date">
                                        <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                                    </div>
                                </div>
                                <div class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <p>No orders yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="col-lg-6">
                <div class="section-card">
                    <h3><i class="fas fa-comments"></i> Recent Reviews</h3>
                    <?php if (!empty($reviews)): ?>
                        <?php
                        $recent_reviews = array_slice($reviews, 0, 5);
                        foreach ($recent_reviews as $review):
                        ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="reviewer-name">
                                        <?php echo htmlspecialchars($review['customer_name'] ?? 'Anonymous'); ?>
                                    </span>
                                    <span class="review-rating">
                                        <?php
                                        for ($i = 0; $i < $review['rating']; $i++) {
                                            echo '<i class="fas fa-star"></i>';
                                        }
                                        for ($i = $review['rating']; $i < 5; $i++) {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="review-restaurant">
                                    <i class="fas fa-store"></i>
                                    <?php echo htmlspecialchars($review['restaurant_name'] ?? 'Restaurant'); ?>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <div class="review-comment">
                                        <?php echo htmlspecialchars($review['comment']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="review-date">
                                    <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <p>No reviews yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Additional Metrics -->
        <div class="section-card">
            <h3><i class="fas fa-chart-bar"></i> Additional Metrics</h3>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="width: 50px; height: 50px; font-size: 20px;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="stat-value" style="font-size: 1.8em;">
                                <?php echo $analytics['active_restaurants']; ?>
                            </div>
                            <div class="stat-label">Active Restaurants</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="width: 50px; height: 50px; font-size: 20px;">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div>
                            <div class="stat-value" style="font-size: 1.8em;">
                                $<?php echo number_format($inventory_value, 2); ?>
                            </div>
                            <div class="stat-label">Inventory Value</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="width: 50px; height: 50px; font-size: 20px;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <div class="stat-value" style="font-size: 1.8em;">
                                $<?php
                                $avg_order = $total_orders > 0 ? $total_revenue / $total_orders : 0;
                                echo number_format($avg_order, 2);
                                ?>
                            </div>
                            <div class="stat-label">Average Order Value</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
