<?php
// Error reporting for debugging (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering FIRST to catch any unwanted output
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

try {
    // Require files
    require_once '../settings/core.php';
    require_once '../controllers/cart_controller.php';
    require_once '../controllers/order_controller.php';
    require_once '../controllers/product_controller.php';

    // Check if user is logged in
    // Note: Checkout requires login for proper order tracking
    if (!isLoggedIn()) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'status' => 'unauthorized',
            'message' => 'You must be logged in to complete checkout.'
        ]);
        exit();
    }

    // Check if form was submitted via POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        exit();
    }

    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Invalid security token. Please refresh the page and try again.'
        ]);
        exit();
    }

    // Get customer ID from session
    $customer_id = (int)$_SESSION['user_id'];

    // Get user's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Handle proxy/forwarded IP
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip_address = trim($ip_list[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    }

    // Get payment information from POST (simulated)
    $currency = isset($_POST['currency']) ? trim($_POST['currency']) : 'USD';
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Card';

    // Validate currency
    if (empty($currency)) {
        $currency = 'USD';
    }

    // STEP 1: Validate cart
    $validation = validate_cart_ctr($customer_id, $ip_address);
    if (!$validation['valid']) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'status' => 'validation_failed',
            'message' => $validation['message']
        ]);
        exit();
    }

    // STEP 2: Get cart items
    $cart_items = get_cart_items_ctr($customer_id, $ip_address);

    if (!$cart_items || count($cart_items) == 0) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'status' => 'empty_cart',
            'message' => 'Your cart is empty.'
        ]);
        exit();
    }

    // STEP 3: Calculate total amount
    $total_amount = get_cart_total_ctr($customer_id, $ip_address);

    if ($total_amount <= 0) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'status' => 'invalid_amount',
            'message' => 'Invalid order total.'
        ]);
        exit();
    }

    // STEP 4: Generate unique order reference
    $order_reference = generate_order_reference_ctr();

    // STEP 5: Generate invoice number
    $invoice_no = generate_invoice_number_ctr();

    // STEP 6: Create order
    $order_date = date('Y-m-d');
    $order_id = create_order_ctr($customer_id, $invoice_no, $order_date, 'Pending');

    if (!$order_id) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'status' => 'order_creation_failed',
            'message' => 'Failed to create order. Please try again.'
        ]);
        exit();
    }

    // STEP 7: Add order details for each cart item
    $order_details_success = true;
    foreach ($cart_items as $item) {
        $result = add_order_details_ctr($order_id, $item['p_id'], $item['qty']);
        if (!$result) {
            $order_details_success = false;
            error_log("Failed to add order details for product: " . $item['p_id']);
        }
    }

    if (!$order_details_success) {
        error_log("Some order details failed to save for order: $order_id");
    }

    // STEP 8: Create payment record
    $payment_date = date('Y-m-d');
    $payment_id = create_payment_ctr($total_amount, $customer_id, $order_id, $currency, $payment_date);

    if (!$payment_id) {
        error_log("Failed to create payment record for order: $order_id");
        // Continue anyway - order is created
    }

    // STEP 9: Clear customer's cart
    $clear_result = clear_cart_ctr($customer_id, $ip_address);

    if (!$clear_result) {
        error_log("Failed to clear cart for customer: $customer_id after order: $order_id");
        // Continue anyway - order is created
    }

    // STEP 10: Return success response with order details
    ob_clean();
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'invoice_no' => $invoice_no,
        'order_reference' => $order_reference,
        'total_amount' => number_format($total_amount, 2),
        'currency' => $currency,
        'payment_method' => $payment_method,
        'order_date' => $order_date,
        'items_count' => count($cart_items)
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("Error in process_checkout_action.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    ob_clean();
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'An unexpected error occurred while processing your order. Please contact support.'
    ]);
}

// End output buffering and flush
ob_end_flush();
?>
