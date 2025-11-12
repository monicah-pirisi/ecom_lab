<?php
// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/cart_controller.php';

// Check if user is logged in (checkout requires login)
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'view/checkout.php';
    header('Location: ../login/login.php');
    exit();
}

// Get user information
$customer_id = (int)$_SESSION['user_id'];
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Handle forwarded IP
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip_address = trim($ip_list[0]);
} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
}

// Get cart items
$cart_items = get_cart_items_ctr($customer_id, $ip_address);
$cart_total = get_cart_total_ctr($customer_id, $ip_address);
$cart_count = is_array($cart_items) ? count($cart_items) : 0;

// Redirect to cart if empty
if (!$cart_items || count($cart_items) === 0) {
    header('Location: cart.php');
    exit();
}

// Get user details
$user_name = $_SESSION['user_name'] ?? 'Customer';
$user_email = $_SESSION['user_email'] ?? '';
$user_phone = $_SESSION['user_phone'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Taste of Africa</title>
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

        /* Header */
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
        }

        /* Checkout Container */
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 15px;
        }

        /* Order Summary Card */
        .order-summary-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .order-summary-card h3 {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        /* Order Item */
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .order-item-price {
            color: #28a745;
            font-weight: 600;
        }

        .order-item-quantity {
            color: var(--text-light);
            font-size: 0.9em;
        }

        .order-item-subtotal {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.2em;
        }

        /* Customer Info Card */
        .customer-info-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .customer-info-card h3 {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-light);
            font-weight: 600;
        }

        .info-value {
            color: var(--text-dark);
            font-weight: 600;
        }

        /* Payment Summary */
        .payment-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 1.1em;
        }

        .summary-total {
            font-size: 1.5em;
            font-weight: 700;
            color: #28a745;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
        }

        /* Buttons */
        .btn-checkout {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 18px;
            width: 100%;
            border-radius: 10px;
            font-size: 1.3em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-checkout:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
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
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            opacity: 1;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 25px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--text-dark);
            font-weight: 700;
        }

        .modal-close {
            font-size: 30px;
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: var(--text-dark);
        }

        .modal-body {
            padding: 30px;
        }

        .payment-icon {
            text-align: center;
            color: #667eea;
            margin-bottom: 20px;
        }

        .payment-info {
            text-align: center;
        }

        .payment-info h3 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .payment-info p {
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .order-summary h4 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .amount {
            color: #28a745;
            font-size: 1.5em;
            font-weight: 700;
        }

        .payment-form {
            margin: 20px 0;
        }

        .form-control {
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 2px solid var(--border-color);
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-loader {
            display: none;
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Success Modal */
        .success-modal-content {
            text-align: center;
            padding: 40px;
        }

        .success-icon {
            color: #28a745;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .order-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .redirect-message {
            color: var(--text-light);
            font-size: 0.9em;
            margin-top: 15px;
        }

        /* Notifications */
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

        @media (max-width: 768px) {
            .order-item {
                flex-direction: column;
                text-align: center;
            }

            .order-item-image {
                margin: 0 0 15px 0;
            }

            .modal-content {
                width: 95%;
            }

            .modal-footer {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Hidden CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-credit-card"></i> Checkout</h1>
            <p>Review your order and complete payment</p>
        </div>
    </div>

    <!-- Checkout Content -->
    <div class="checkout-container">
        <div class="container">
            <div class="row">
                <!-- Left Column - Order Details -->
                <div class="col-lg-8">
                    <!-- Customer Information -->
                    <div class="customer-info-card">
                        <h3><i class="fas fa-user"></i> Customer Information</h3>
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user_name); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user_email); ?></span>
                        </div>
                        <?php if (!empty($user_phone)): ?>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user_phone); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Order Summary -->
                    <div class="order-summary-card">
                        <h3><i class="fas fa-list"></i> Order Summary (<?php echo $cart_count; ?> items)</h3>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <?php
                                $image_path = !empty($item['product_image']) ? '../uploads/' . $item['product_image'] : '../images/default-product.png';
                                ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>"
                                     alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                     class="order-item-image"
                                     onerror="this.onerror=null; this.src='../images/default-product.png'">
                                <div class="order-item-details">
                                    <div class="order-item-title"><?php echo htmlspecialchars($item['product_title']); ?></div>
                                    <div class="order-item-price">$<?php echo number_format($item['product_price'], 2); ?></div>
                                    <div class="order-item-quantity">Quantity: <?php echo $item['qty']; ?></div>
                                </div>
                                <div class="order-item-subtotal">
                                    $<?php echo number_format($item['subtotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <a href="cart.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>

                <!-- Right Column - Payment Summary -->
                <div class="col-lg-4">
                    <div class="order-summary-card">
                        <h3><i class="fas fa-file-invoice-dollar"></i> Payment Summary</h3>
                        <div class="payment-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span class="text-success">FREE</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax:</span>
                                <span>$0.00</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Total:</span>
                                <span class="total-amount" id="cart-total">$<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                        </div>

                        <button class="btn-checkout" id="checkout-btn">
                            <i class="fas fa-lock"></i>
                            <span>Simulate Payment</span>
                        </button>

                        <p class="text-center mt-3 text-muted" style="font-size: 0.9em;">
                            <i class="fas fa-shield-alt"></i> This is a demo payment system
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
    <script src="../js/checkout.js"></script>
</body>
</html>
