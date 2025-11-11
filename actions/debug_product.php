<?php
// Debug file to test product creation
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/plain; charset=utf-8');

echo "=== Product Creation Debug ===\n\n";

// Test 1: Include files
echo "Test 1: Loading files...\n";
try {
    require_once '../settings/core.php';
    echo "✓ Core loaded\n";

    require_once '../controllers/product_controller.php';
    echo "✓ Product controller loaded\n";
} catch (Exception $e) {
    echo "✗ Error loading files: " . $e->getMessage() . "\n";
    exit();
}

// Test 2: Check if logged in
echo "\nTest 2: Checking login status...\n";
if (!isLoggedIn()) {
    echo "✗ Not logged in\n";
    echo "Session vars: " . print_r($_SESSION, true) . "\n";
    exit();
}
echo "✓ User is logged in\n";
echo "User ID: " . $_SESSION['user_id'] . "\n";

// Test 3: Check database connection
echo "\nTest 3: Testing database connection...\n";
try {
    require_once '../classes/product_class.php';
    $product = new Product();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit();
}

// Test 4: Check categories and brands exist
echo "\nTest 4: Checking categories and brands...\n";
try {
    $test_result = create_product_ctr([
        'product_title' => 'Test Product',
        'product_cat' => 1,
        'product_brand' => 1,
        'product_price' => 10.00,
        'product_desc' => 'Test description',
        'product_image' => '',
        'product_keywords' => 'test',
        'user_id' => $_SESSION['user_id']
    ]);

    if ($test_result['success']) {
        echo "✓ Product created successfully!\n";
        echo "Product ID: " . $test_result['product_id'] . "\n";

        // Delete the test product
        require_once '../classes/product_class.php';
        $product = new Product();
        $product->deleteProduct($test_result['product_id']);
        echo "✓ Test product deleted\n";
    } else {
        echo "✗ Product creation failed: " . $test_result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
?>
