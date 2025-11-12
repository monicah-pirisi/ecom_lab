<?php
/**
 * Cart & Order Functions Test Script
 * This script tests all cart and order functions directly
 *
 * HOW TO USE:
 * 1. Make sure XAMPP is running
 * 2. Open in browser: http://localhost/ecom_lab/test_cart_functions.php
 * 3. Check the output for any errors
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include required files
require_once 'settings/db_class.php';
require_once 'classes/cart_class.php';
require_once 'classes/order_class.php';
require_once 'controllers/cart_controller.php';
require_once 'controllers/order_controller.php';

// Set test user (you can change these)
$test_customer_id = 1; // Change to a valid customer ID from your database
$test_ip_address = '127.0.0.1';
$test_product_id = 1; // Change to a valid product ID from your database

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart & Order Functions Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #007bff;
            margin-top: 0;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border-left: 4px solid #007bff;
        }
        .test-result {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .test-pass {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .test-fail {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .config-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>🧪 Cart & Order Functions Test Suite</h1>

    <div class="config-info">
        <strong>⚙️ Test Configuration:</strong><br>
        Customer ID: <?php echo $test_customer_id; ?><br>
        IP Address: <?php echo $test_ip_address; ?><br>
        Product ID: <?php echo $test_product_id; ?><br>
        <br>
        <em>Note: Make sure these IDs exist in your database before testing!</em>
    </div>

    <?php
    // Test counter
    $total_tests = 0;
    $passed_tests = 0;
    $failed_tests = 0;

    function test_result($test_name, $result, $details = '') {
        global $total_tests, $passed_tests, $failed_tests;
        $total_tests++;

        if ($result) {
            $passed_tests++;
            echo "<div class='test-result test-pass'>";
            echo "✅ <strong>PASS:</strong> $test_name";
        } else {
            $failed_tests++;
            echo "<div class='test-result test-fail'>";
            echo "❌ <strong>FAIL:</strong> $test_name";
        }

        if ($details) {
            echo "<br><em>$details</em>";
        }

        echo "</div>";
    }
    ?>

    <!-- Test 1: Database Connection -->
    <div class="test-section">
        <h2>1️⃣ Database Connection Test</h2>
        <?php
        try {
            $db = new db_connection();
            $conn = $db->db_connect();
            test_result("Database Connection", $conn !== false, "Successfully connected to database");
        } catch (Exception $e) {
            test_result("Database Connection", false, "Error: " . $e->getMessage());
        }
        ?>
    </div>

    <!-- Test 2: Cart Class Tests -->
    <div class="test-section">
        <h2>2️⃣ Cart Class Tests</h2>
        <?php
        try {
            $cart = new Cart();
            test_result("Cart Class Instantiation", true, "Cart class created successfully");

            // Test: Add to Cart
            $add_result = $cart->addToCart($test_product_id, $test_customer_id, $test_ip_address, 2);
            test_result("Add to Cart", $add_result !== false, "Added product ID $test_product_id with quantity 2");

            // Test: Get Cart Items
            $items = $cart->getCartItems($test_customer_id, $test_ip_address);
            test_result("Get Cart Items", is_array($items), "Retrieved " . (is_array($items) ? count($items) : 0) . " items");

            if (is_array($items) && count($items) > 0) {
                echo "<pre><strong>Cart Items:</strong>\n";
                print_r($items);
                echo "</pre>";
            }

            // Test: Get Cart Count
            $count = $cart->getCartCount($test_customer_id, $test_ip_address);
            test_result("Get Cart Count", is_numeric($count), "Cart has $count item(s)");

            // Test: Get Cart Total
            $total = $cart->getCartTotal($test_customer_id, $test_ip_address);
            test_result("Get Cart Total", is_numeric($total), "Cart total: $" . number_format($total, 2));

            // Test: Update Cart Quantity
            $update_result = $cart->updateCartQuantity($test_product_id, $test_customer_id, $test_ip_address, 3);
            test_result("Update Cart Quantity", $update_result !== false, "Updated quantity to 3");

            // Test: Check Product in Cart
            $check = $cart->checkProductInCart($test_product_id, $test_customer_id, $test_ip_address);
            test_result("Check Product in Cart", $check !== false, "Product found in cart");

        } catch (Exception $e) {
            test_result("Cart Class Tests", false, "Error: " . $e->getMessage());
        }
        ?>
    </div>

    <!-- Test 3: Cart Controller Tests -->
    <div class="test-section">
        <h2>3️⃣ Cart Controller Tests</h2>
        <?php
        try {
            // Test: Add to Cart Controller
            $result = add_to_cart_ctr($test_product_id, $test_customer_id, $test_ip_address, 1);
            test_result("add_to_cart_ctr()", $result !== false, "Controller successfully added to cart");

            // Test: Get Cart Items Controller
            $items = get_cart_items_ctr($test_customer_id, $test_ip_address);
            test_result("get_cart_items_ctr()", is_array($items), "Controller retrieved cart items");

            // Test: Get Cart Count Controller
            $count = get_cart_count_ctr($test_customer_id, $test_ip_address);
            test_result("get_cart_count_ctr()", is_numeric($count), "Count: $count");

            // Test: Get Cart Total Controller
            $total = get_cart_total_ctr($test_customer_id, $test_ip_address);
            test_result("get_cart_total_ctr()", is_numeric($total), "Total: $" . number_format($total, 2));

            // Test: Validate Cart Controller
            $validation = validate_cart_ctr($test_customer_id, $test_ip_address);
            test_result("validate_cart_ctr()", is_array($validation), "Validation result: " . ($validation['valid'] ? 'Valid' : 'Invalid'));

        } catch (Exception $e) {
            test_result("Cart Controller Tests", false, "Error: " . $e->getMessage());
        }
        ?>
    </div>

    <!-- Test 4: Order Class Tests -->
    <div class="test-section">
        <h2>4️⃣ Order Class Tests</h2>
        <?php
        try {
            $order = new Order();
            test_result("Order Class Instantiation", true, "Order class created successfully");

            // Test: Generate Invoice Number
            $invoice_no = $order->generateInvoiceNumber();
            test_result("Generate Invoice Number", is_numeric($invoice_no), "Generated invoice: $invoice_no");

            // Test: Create Order
            $order_id = $order->createOrder($test_customer_id, $invoice_no, date('Y-m-d'), 'Pending');
            test_result("Create Order", $order_id !== false, "Created order ID: $order_id");

            if ($order_id) {
                // Test: Add Order Details
                $detail_result = $order->addOrderDetails($order_id, $test_product_id, 2);
                test_result("Add Order Details", $detail_result !== false, "Added order details");

                // Test: Create Payment
                $payment_id = $order->createPayment(99.99, $test_customer_id, $order_id, 'USD', date('Y-m-d'));
                test_result("Create Payment", $payment_id !== false, "Created payment ID: $payment_id");

                // Test: Get Order by ID
                $order_data = $order->getOrderById($order_id);
                test_result("Get Order by ID", is_array($order_data), "Retrieved order data");

                if (is_array($order_data)) {
                    echo "<pre><strong>Order Data:</strong>\n";
                    print_r($order_data);
                    echo "</pre>";
                }

                // Test: Get Order Details
                $order_details = $order->getOrderDetails($order_id);
                test_result("Get Order Details", is_array($order_details), "Retrieved order details");

                // Test: Get Payment by Order
                $payment_data = $order->getPaymentByOrder($order_id);
                test_result("Get Payment by Order", is_array($payment_data), "Retrieved payment data");

                // Test: Update Order Status
                $update_status = $order->updateOrderStatus($order_id, 'Processing');
                test_result("Update Order Status", $update_status !== false, "Updated status to 'Processing'");

                // Cleanup: Delete test order
                echo "<div class='info'><strong>🧹 Cleaning up test data...</strong></div>";
                $order->deleteOrder($order_id);
            }

        } catch (Exception $e) {
            test_result("Order Class Tests", false, "Error: " . $e->getMessage());
        }
        ?>
    </div>

    <!-- Test 5: Order Controller Tests -->
    <div class="test-section">
        <h2>5️⃣ Order Controller Tests</h2>
        <?php
        try {
            // Test: Generate Invoice Number Controller
            $invoice = generate_invoice_number_ctr();
            test_result("generate_invoice_number_ctr()", is_numeric($invoice), "Generated: $invoice");

            // Test: Generate Order Reference Controller
            $reference = generate_order_reference_ctr();
            test_result("generate_order_reference_ctr()", !empty($reference), "Generated: $reference");

            // Test: Process Checkout Controller
            $cart_items = get_cart_items_ctr($test_customer_id, $test_ip_address);
            if ($cart_items && count($cart_items) > 0) {
                $total = get_cart_total_ctr($test_customer_id, $test_ip_address);
                $checkout_result = process_checkout_ctr($test_customer_id, $cart_items, $total, 'USD');
                test_result("process_checkout_ctr()", is_array($checkout_result) && $checkout_result['success'],
                    "Checkout processed: Order #" . ($checkout_result['order_id'] ?? 'N/A'));

                if (is_array($checkout_result)) {
                    echo "<pre><strong>Checkout Result:</strong>\n";
                    print_r($checkout_result);
                    echo "</pre>";
                }

                // Check if cart was cleared
                $count_after = get_cart_count_ctr($test_customer_id, $test_ip_address);
                test_result("Cart Cleared After Checkout", $count_after == 0, "Cart count: $count_after");
            } else {
                echo "<div class='info'><em>Cart is empty - skipping checkout test</em></div>";
            }

        } catch (Exception $e) {
            test_result("Order Controller Tests", false, "Error: " . $e->getMessage());
        }
        ?>
    </div>

    <!-- Test Summary -->
    <div class="test-section" style="background: #e9ecef;">
        <h2>📊 Test Summary</h2>
        <div style="font-size: 18px;">
            <strong>Total Tests:</strong> <?php echo $total_tests; ?><br>
            <span class="success">✅ Passed: <?php echo $passed_tests; ?></span><br>
            <span class="error">❌ Failed: <?php echo $failed_tests; ?></span><br>
            <br>
            <strong>Success Rate:</strong>
            <?php
            $success_rate = $total_tests > 0 ? ($passed_tests / $total_tests) * 100 : 0;
            echo number_format($success_rate, 1) . "%";
            ?>
        </div>

        <?php if ($failed_tests == 0): ?>
            <div style="background: #d4edda; padding: 15px; margin-top: 15px; border-radius: 4px; border: 2px solid #28a745;">
                <strong style="color: #28a745; font-size: 20px;">🎉 ALL TESTS PASSED!</strong><br>
                <em>Your cart and order system is working perfectly!</em>
            </div>
        <?php else: ?>
            <div style="background: #f8d7da; padding: 15px; margin-top: 15px; border-radius: 4px; border: 2px solid #dc3545;">
                <strong style="color: #dc3545; font-size: 20px;">⚠️ SOME TESTS FAILED</strong><br>
                <em>Please check the errors above and fix them before proceeding.</em>
            </div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>🔧 Troubleshooting</h2>
        <p>If tests are failing, check:</p>
        <ul>
            <li>Make sure XAMPP MySQL is running</li>
            <li>Verify database credentials in <code>settings/db_cred.php</code></li>
            <li>Ensure the customer ID (<?php echo $test_customer_id; ?>) exists in the <code>customer</code> table</li>
            <li>Ensure the product ID (<?php echo $test_product_id; ?>) exists in the <code>products</code> table</li>
            <li>Check that all database tables exist: <code>cart</code>, <code>orders</code>, <code>orderdetails</code>, <code>payment</code></li>
            <li>Check PHP error logs for detailed error messages</li>
        </ul>
    </div>

</body>
</html>
