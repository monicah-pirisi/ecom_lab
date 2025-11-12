<?php
/**
 * Action Scripts Test Page
 * Tests all action scripts through AJAX calls
 *
 * HOW TO USE:
 * 1. Make sure you're logged in first
 * 2. Open: http://localhost/ecom_lab/test_actions.php
 * 3. Click the test buttons and check console for responses
 */

session_start();
require_once 'settings/core.php';

// Check if user is logged in
$is_logged_in = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Scripts Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin-bottom: 10px;
        }

        .content {
            padding: 30px;
        }

        .login-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-warning a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .test-section {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .test-section h2 {
            color: #495057;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .test-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #495057;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
        }

        .input-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        #console {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
        }

        .console-entry {
            margin-bottom: 10px;
            padding: 8px;
            border-left: 3px solid #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .console-success {
            border-left-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .console-error {
            border-left-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .console-info {
            border-left-color: #17a2b8;
            background: rgba(23, 162, 184, 0.1);
        }

        .timestamp {
            color: #6c757d;
            font-size: 11px;
        }

        .clear-console {
            background: #dc3545;
            margin-bottom: 10px;
        }

        .user-info {
            background: #d4edda;
            border: 2px solid #28a745;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Action Scripts Test Suite</h1>
            <p>Test all cart and checkout action scripts via AJAX</p>
        </div>

        <div class="content">
            <?php if (!$is_logged_in): ?>
                <div class="login-warning">
                    <h3>⚠️ Not Logged In</h3>
                    <p>Some tests require you to be logged in. <a href="login/login.php">Click here to login</a></p>
                    <p><small>You can still test some functions as a guest user</small></p>
                </div>
            <?php else: ?>
                <div class="user-info">
                    <strong>✅ Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                    (ID: <?php echo $_SESSION['user_id']; ?>)
                </div>
            <?php endif; ?>

            <!-- Test Configuration -->
            <div class="test-section">
                <h2>⚙️ Test Configuration</h2>
                <div class="input-group">
                    <label for="test_product_id">Product ID:</label>
                    <input type="number" id="test_product_id" value="1" min="1">
                    <small style="color: #6c757d;">Enter a valid product ID from your database</small>
                </div>
                <div class="input-group">
                    <label for="test_quantity">Quantity:</label>
                    <input type="number" id="test_quantity" value="2" min="1">
                </div>
            </div>

            <!-- Cart Actions Tests -->
            <div class="test-section">
                <h2>🛒 Cart Actions</h2>
                <div class="test-group">
                    <button onclick="testAddToCart()">➕ Test Add to Cart</button>
                    <button onclick="testUpdateQuantity()">✏️ Test Update Quantity</button>
                    <button onclick="testRemoveFromCart()">🗑️ Test Remove from Cart</button>
                    <button onclick="testEmptyCart()">🧹 Test Empty Cart</button>
                </div>
            </div>

            <!-- Checkout Actions Tests -->
            <div class="test-section">
                <h2>💳 Checkout Actions</h2>
                <div class="test-group">
                    <button onclick="testProcessCheckout()" <?php echo !$is_logged_in ? 'disabled' : ''; ?>>
                         Test Process Checkout
                    </button>
                </div>
                <?php if (!$is_logged_in): ?>
                    <small style="color: #dc3545;">⚠️ Checkout requires login</small>
                <?php endif; ?>
            </div>

            <!-- Batch Tests -->
            <div class="test-section">
                <h2>🔄 Batch Tests</h2>
                <div class="test-group">
                    <button onclick="runFullWorkflowTest()">🎯 Run Full Workflow Test</button>
                    <button onclick="runAllTests()">🚀 Run All Tests</button>
                </div>
                <small style="color: #6c757d;">Full workflow: Add → Update → Checkout → Verify</small>
            </div>

            <!-- Console -->
            <div class="test-section">
                <h2>📟 Console Output</h2>
                <button class="clear-console" onclick="clearConsole()">Clear Console</button>
                <div id="console">
                    <div class="console-info">
                        Console ready. Click test buttons above to see results...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token
        const csrfToken = '<?php echo getCSRFToken(); ?>';

        // Console functions
        function logToConsole(message, type = 'info') {
            const console = document.getElementById('console');
            const entry = document.createElement('div');
            entry.className = `console-entry console-${type}`;

            const timestamp = new Date().toLocaleTimeString();
            entry.innerHTML = `
                <span class="timestamp">[${timestamp}]</span>
                <div>${message}</div>
            `;

            console.appendChild(entry);
            console.scrollTop = console.scrollHeight;
        }

        function clearConsole() {
            document.getElementById('console').innerHTML = '<div class="console-info">Console cleared.</div>';
        }

        // Get test values
        function getProductId() {
            return document.getElementById('test_product_id').value;
        }

        function getQuantity() {
            return document.getElementById('test_quantity').value;
        }

        // Test Add to Cart
        async function testAddToCart() {
            logToConsole('🧪 Testing: Add to Cart', 'info');

            const formData = new URLSearchParams();
            formData.append('product_id', getProductId());
            formData.append('quantity', getQuantity());
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('actions/add_to_cart_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                logToConsole(`Response: ${JSON.stringify(data, null, 2)}`, data.success ? 'success' : 'error');

                if (data.success) {
                    logToConsole(`✅ Cart Count: ${data.cart_count}, Total: $${data.cart_total}`, 'success');
                }
            } catch (error) {
                logToConsole(`❌ Error: ${error.message}`, 'error');
            }
        }

        // Test Update Quantity
        async function testUpdateQuantity() {
            logToConsole('🧪 Testing: Update Quantity', 'info');

            const formData = new URLSearchParams();
            formData.append('product_id', getProductId());
            formData.append('quantity', 5); // Update to 5
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('actions/update_quantity_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                logToConsole(`Response: ${JSON.stringify(data, null, 2)}`, data.success ? 'success' : 'error');
            } catch (error) {
                logToConsole(`❌ Error: ${error.message}`, 'error');
            }
        }

        // Test Remove from Cart
        async function testRemoveFromCart() {
            logToConsole('🧪 Testing: Remove from Cart', 'info');

            const formData = new URLSearchParams();
            formData.append('product_id', getProductId());
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('actions/remove_from_cart_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                logToConsole(`Response: ${JSON.stringify(data, null, 2)}`, data.success ? 'success' : 'error');
            } catch (error) {
                logToConsole(`❌ Error: ${error.message}`, 'error');
            }
        }

        // Test Empty Cart
        async function testEmptyCart() {
            logToConsole('🧪 Testing: Empty Cart', 'info');

            const formData = new URLSearchParams();
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('actions/empty_cart_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                logToConsole(`Response: ${JSON.stringify(data, null, 2)}`, data.success ? 'success' : 'error');
            } catch (error) {
                logToConsole(`❌ Error: ${error.message}`, 'error');
            }
        }

        // Test Process Checkout
        async function testProcessCheckout() {
            logToConsole('🧪 Testing: Process Checkout', 'info');

            const formData = new URLSearchParams();
            formData.append('currency', 'USD');
            formData.append('payment_method', 'Test Card');
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('actions/process_checkout_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                logToConsole(`Response: ${JSON.stringify(data, null, 2)}`, data.success ? 'success' : 'error');

                if (data.success) {
                    logToConsole(`✅ Order Created! ID: ${data.order_id}, Ref: ${data.order_reference}`, 'success');
                }
            } catch (error) {
                logToConsole(`❌ Error: ${error.message}`, 'error');
            }
        }

        // Run Full Workflow Test
        async function runFullWorkflowTest() {
            logToConsole('======================================', 'info');
            logToConsole('🎯 STARTING FULL WORKFLOW TEST', 'info');
            logToConsole('======================================', 'info');

            // Step 1: Add to cart
            logToConsole('Step 1: Adding product to cart...', 'info');
            await testAddToCart();
            await sleep(1000);

            // Step 2: Update quantity
            logToConsole('Step 2: Updating quantity...', 'info');
            await testUpdateQuantity();
            await sleep(1000);

            // Step 3: Checkout (if logged in)
            <?php if ($is_logged_in): ?>
            logToConsole('Step 3: Processing checkout...', 'info');
            await testProcessCheckout();
            await sleep(1000);
            <?php else: ?>
            logToConsole('Step 3: Skipped (not logged in)', 'info');
            <?php endif; ?>

            logToConsole('======================================', 'success');
            logToConsole('✅ WORKFLOW TEST COMPLETED', 'success');
            logToConsole('======================================', 'success');
        }

        // Run All Tests
        async function runAllTests() {
            logToConsole('======================================', 'info');
            logToConsole('🚀 RUNNING ALL TESTS', 'info');
            logToConsole('======================================', 'info');

            await testAddToCart();
            await sleep(1000);

            await testUpdateQuantity();
            await sleep(1000);

            await testRemoveFromCart();
            await sleep(1000);

            await testAddToCart(); // Add again
            await sleep(1000);

            <?php if ($is_logged_in): ?>
            await testProcessCheckout();
            await sleep(1000);
            <?php endif; ?>

            logToConsole('======================================', 'success');
            logToConsole('✅ ALL TESTS COMPLETED', 'success');
            logToConsole('======================================', 'success');
        }

        // Helper function
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
    </script>
</body>
</html>
