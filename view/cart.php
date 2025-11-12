<?php
// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/cart_controller.php';

// Get user information
$customer_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Taste of Africa</title>
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
            padding: 60px 0 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .page-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        /* Cart Container */
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Cart Item Card */
        .cart-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            box-shadow: var(--hover-shadow);
        }

        .product-image-cart {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-product-title {
            font-size: 1.2em;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .cart-product-price {
            color: #28a745;
            font-size: 1.3em;
            font-weight: 700;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-input {
            width: 80px;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        .quantity-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-quantity {
            background: var(--primary-gradient);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-quantity:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 20px;
        }

        .cart-summary h3 {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .summary-total {
            font-size: 1.5em;
            font-weight: 700;
            color: #28a745;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--border-color);
        }

        .btn-checkout {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-checkout:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-continue {
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

        .btn-continue:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .btn-empty-cart {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-empty-cart:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        /* Empty Cart Message */
        .empty-cart-message {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .empty-cart-message h3 {
            color: var(--text-dark);
            font-size: 2em;
            margin: 20px 0;
        }

        .empty-cart-message p {
            color: var(--text-light);
            font-size: 1.1em;
            margin-bottom: 30px;
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

        .notification-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-icon {
            font-size: 24px;
            font-weight: bold;
        }

        .notification-success .notification-icon {
            color: #28a745;
        }

        .notification-error .notification-icon {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .product-image-cart {
                width: 80px;
                height: 80px;
            }

            .cart-product-title {
                font-size: 1em;
            }

            .cart-summary {
                position: static;
                margin-top: 30px;
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
            <h1><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
            <p>Review your items before checkout</p>
        </div>
    </div>

    <!-- Cart Content -->
    <div class="cart-container">
        <div class="container">
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-items-container" id="cart-items">
                        <?php if ($cart_items && count($cart_items) > 0): ?>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item" data-product-id="<?php echo htmlspecialchars($item['p_id']); ?>">
                                    <div class="row align-items-center">
                                        <!-- Product Image -->
                                        <div class="col-md-2 col-3">
                                            <?php
                                            $image_path = !empty($item['product_image']) ? '../uploads/' . $item['product_image'] : '../images/default-product.png';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($image_path); ?>"
                                                 alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                                 class="product-image-cart"
                                                 onerror="this.src='../images/default-product.png'">
                                        </div>

                                        <!-- Product Details -->
                                        <div class="col-md-4 col-9">
                                            <h5 class="cart-product-title">
                                                <?php echo htmlspecialchars($item['product_title']); ?>
                                            </h5>
                                            <p class="cart-product-price item-price" data-price="<?php echo $item['product_price']; ?>">
                                                $<?php echo number_format($item['product_price'], 2); ?>
                                            </p>
                                        </div>

                                        <!-- Quantity Control -->
                                        <div class="col-md-3 col-6">
                                            <div class="quantity-control">
                                                <button class="btn-quantity" onclick="decrementQuantity(<?php echo $item['p_id']; ?>)">-</button>
                                                <input type="number"
                                                       class="quantity-input"
                                                       name="quantity"
                                                       value="<?php echo $item['qty']; ?>"
                                                       min="1"
                                                       max="100">
                                                <button class="btn-quantity" onclick="incrementQuantity(<?php echo $item['p_id']; ?>)">+</button>
                                            </div>
                                        </div>

                                        <!-- Subtotal & Remove -->
                                        <div class="col-md-2 col-4">
                                            <p class="cart-product-price item-subtotal subtotal">
                                                $<?php echo number_format($item['subtotal'], 2); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-1 col-2">
                                            <button class="btn-remove" title="Remove Item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="all_product.php" class="btn-continue">
                                    <i class="fas fa-arrow-left"></i> Continue Shopping
                                </a>
                                <button class="btn-empty-cart" id="empty-cart-btn">
                                    <i class="fas fa-trash-alt"></i> Empty Cart
                                </button>
                            </div>

                        <?php else: ?>
                            <!-- Empty Cart Message -->
                            <div class="empty-cart-message">
                                <i class="fas fa-shopping-cart" style="font-size: 100px; color: #e9ecef;"></i>
                                <h3>Your cart is empty</h3>
                                <p>Add some delicious products to your cart to continue shopping!</p>
                                <a href="all_product.php" class="btn-checkout">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cart Summary -->
                <?php if ($cart_items && count($cart_items) > 0): ?>
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal (<?php echo $cart_count; ?> items):</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span class="text-success">FREE</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax:</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span class="cart-total-amount" id="cart-total">$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn-checkout" id="checkout-btn">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        <p class="text-center mt-3 text-muted" style="font-size: 0.9em;">
                            <i class="fas fa-shield-alt"></i> Secure Checkout
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
    <script>
        // Increment quantity
        function incrementQuantity(productId) {
            const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
            const input = cartItem.querySelector('.quantity-input');
            let quantity = parseInt(input.value) || 1;
            quantity++;
            input.value = quantity;
            updateCartQuantity(productId, quantity);
        }

        // Decrement quantity
        function decrementQuantity(productId) {
            const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
            const input = cartItem.querySelector('.quantity-input');
            let quantity = parseInt(input.value) || 1;
            if (quantity > 1) {
                quantity--;
                input.value = quantity;
                updateCartQuantity(productId, quantity);
            }
        }
    </script>
</body>
</html>
