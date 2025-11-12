<?php
// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/order_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Get order details from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order_reference = isset($_GET['reference']) ? htmlspecialchars($_GET['reference']) : '';

// Validate order ID
if ($order_id <= 0) {
    header('Location: all_product.php');
    exit();
}

// Get order details
$order = get_order_by_id_ctr($order_id);
$order_items = get_order_details_ctr($order_id);
$payment = get_payment_by_order_ctr($order_id);

if (!$order) {
    header('Location: all_product.php');
    exit();
}

// Calculate total
$total = 0;
if ($order_items) {
    foreach ($order_items as $item) {
        $total += $item['subtotal'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Taste of Africa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --text-dark: #333;
            --text-light: #6c757d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 50px 0;
        }

        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .confirmation-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .success-icon i {
            color: white;
            font-size: 50px;
        }

        .confirmation-card h1 {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 15px;
        }

        .confirmation-card p {
            color: var(--text-light);
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--text-light);
            font-weight: 600;
        }

        .detail-value {
            color: var(--text-dark);
            font-weight: 700;
        }

        .order-reference {
            background: var(--primary-gradient);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 1.3em;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .order-items {
            margin: 30px 0;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .order-item-details {
            flex: 1;
            text-align: left;
        }

        .order-item-title {
            font-weight: 600;
            color: var(--text-dark);
        }

        .order-item-qty {
            color: var(--text-light);
            font-size: 0.9em;
        }

        .order-item-price {
            font-weight: 700;
            color: #28a745;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-secondary {
            background: white;
            border: 2px solid #667eea;
            padding: 15px 40px;
            border-radius: 10px;
            font-weight: 600;
            color: #667eea;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        @media (max-width: 768px) {
            .confirmation-card {
                padding: 30px 20px;
            }

            .order-item {
                flex-direction: column;
                text-align: center;
            }

            .order-item-image {
                margin: 0 0 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <!-- Success Icon -->
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>

            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your order. We've received your payment and your order is being processed.</p>

            <!-- Order Reference -->
            <div class="order-reference">
                <?php echo $order_reference; ?>
            </div>

            <!-- Order Details -->
            <div class="order-details">
                <h4 style="margin-bottom: 20px;"><i class="fas fa-info-circle"></i> Order Details</h4>
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value">#<?php echo $order_id; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['order_status']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value" style="color: #28a745; font-size: 1.3em;">
                        $<?php echo number_format($total, 2); ?>
                    </span>
                </div>
            </div>

            <!-- Order Items -->
            <?php if ($order_items && count($order_items) > 0): ?>
            <div class="order-items">
                <h4 style="margin-bottom: 20px; text-align: left;">
                    <i class="fas fa-box"></i> Items Ordered (<?php echo count($order_items); ?>)
                </h4>
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <?php
                        $image_path = !empty($item['product_image']) ? '../uploads/' . $item['product_image'] : '../images/default-product.png';
                        ?>
                        <img src="<?php echo htmlspecialchars($image_path); ?>"
                             alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                             class="order-item-image"
                             onerror="this.src='../images/default-product.png'">
                        <div class="order-item-details">
                            <div class="order-item-title"><?php echo htmlspecialchars($item['product_title']); ?></div>
                            <div class="order-item-qty">Quantity: <?php echo $item['qty']; ?></div>
                        </div>
                        <div class="order-item-price">
                            $<?php echo number_format($item['subtotal'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Next Steps -->
            <div style="margin-top: 40px;">
                <p style="color: var(--text-light); font-size: 0.95em;">
                    <i class="fas fa-envelope"></i> A confirmation email has been sent to <strong><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></strong>
                </p>
            </div>

            <!-- Action Buttons -->
            <div style="margin-top: 30px;">
                <a href="all_product.php" class="btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="../dashboard.php" class="btn-secondary">
                    <i class="fas fa-home"></i> Go to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
