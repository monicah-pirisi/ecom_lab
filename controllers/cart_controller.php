<?php
/**
 * Cart Controller
 * Business logic layer for cart operations
 * Wraps cart_class methods with validation and error handling
 */

// Include the cart class
require_once(__DIR__ . '/../classes/cart_class.php');

/**
 * Add product to cart
 * @param int $product_id Product ID
 * @param int|null $customer_id Customer ID (null for guest)
 * @param string $ip_address User's IP address
 * @param int $quantity Quantity to add
 * @return bool True on success, false on failure
 */
function add_to_cart_ctr($product_id, $customer_id, $ip_address, $quantity)
{
    try {
        // Validate inputs
        if (!is_numeric($product_id) || $product_id <= 0) {
            error_log("Invalid product ID: $product_id");
            return false;
        }

        if (!is_numeric($quantity) || $quantity <= 0) {
            error_log("Invalid quantity: $quantity");
            return false;
        }

        if (empty($ip_address)) {
            error_log("IP address is required");
            return false;
        }

        // Create cart instance and add item
        $cart = new Cart();
        return $cart->addToCart($product_id, $customer_id, $ip_address, $quantity);
    } catch (Exception $e) {
        error_log("Error in add_to_cart_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all cart items for a user
 * @param int|null $customer_id Customer ID
 * @param string $ip_address IP address
 * @return array|false Array of cart items, false on failure
 */
function get_cart_items_ctr($customer_id, $ip_address)
{
    try {
        if (empty($ip_address)) {
            error_log("IP address is required");
            return false;
        }

        $cart = new Cart();
        return $cart->getCartItems($customer_id, $ip_address);
    } catch (Exception $e) {
        error_log("Error in get_cart_items_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Update cart item quantity
 * @param int $product_id Product ID
 * @param int|null $customer_id Customer ID
 * @param string $ip_address IP address
 * @param int $quantity New quantity
 * @return bool True on success, false on failure
 */
function update_cart_quantity_ctr($product_id, $customer_id, $ip_address, $quantity)
{
    try {
        // Validate inputs
        if (!is_numeric($product_id) || $product_id <= 0) {
            error_log("Invalid product ID: $product_id");
            return false;
        }

        if (!is_numeric($quantity) || $quantity < 0) {
            error_log("Invalid quantity: $quantity");
            return false;
        }

        if (empty($ip_address)) {
            error_log("IP address is required");
            return false;
        }

        $cart = new Cart();
        return $cart->updateCartQuantity($product_id, $customer_id, $ip_address, $quantity);
    } catch (Exception $e) {
        error_log("Error in update_cart_quantity_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove item from cart
 * @param int $product_id Product ID
 * @param int|null $customer_id Customer ID
 * @param string $ip_address IP address
 * @return bool True on success, false on failure
 */
function remove_from_cart_ctr($product_id, $customer_id, $ip_address)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0) {
            error_log("Invalid product ID: $product_id");
            return false;
        }

        if (empty($ip_address)) {
            error_log("IP address is required");
            return false;
        }

        $cart = new Cart();
        return $cart->removeFromCart($product_id, $customer_id, $ip_address);
    } catch (Exception $e) {
        error_log("Error in remove_from_cart_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Clear all items from cart
 * @param int|null $customer_id Customer ID
 * @param string $ip_address IP address
 * @return bool True on success, false on failure
 */
function clear_cart_ctr($customer_id, $ip_address)
{
    try {
        if (empty($ip_address)) {
            error_log("IP address is required");
            return false;
        }

        $cart = new Cart();
        return $cart->clearCart($customer_id, $ip_address);
    } catch (Exception $e) {
        error_log("Error in clear_cart_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get cart count
 * @param int|null $customer_id Customer ID
 * @param string $ip_address IP address
 * @return int Number of items in cart
 */
function get_cart_count_ctr($customer_id, $ip_address)
{
    try {
        if (empty($ip_address)) {
            error_log("IP address is required");
            return 0;
        }

        $cart = new Cart();
        return $cart->getCartCount($customer_id, $ip_address);
    } catch (Exception $e) {
        error_log("Error in get_cart_count_ctr: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get cart total
 * @param int|null $customer_id Customer ID
 * @param string $ip_address IP address
 * @return float Total cart amount
 */
function get_cart_total_ctr($customer_id, $ip_address)
{
    try {
        if (empty($ip_address)) {
            error_log("IP address is required");
            return 0.0;
        }

        $cart = new Cart();
        return $cart->getCartTotal($customer_id, $ip_address);
    } catch (Exception $e) {
        error_log("Error in get_cart_total_ctr: " . $e->getMessage());
        return 0.0;
    }
}

/**
 * Merge guest cart with user cart after login
 * @param int $customer_id Customer ID
 * @param string $ip_address IP address
 * @return bool True on success, false on failure
 */
function merge_guest_cart_ctr($customer_id, $ip_address)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: $customer_id");
            return false;
        }

        if (empty($ip_address)) {
            error_log("IP address is required");
            return false;
        }

        $cart = new Cart();
        return $cart->mergeGuestCart($customer_id, $ip_address);
    } catch (Exception $e) {
        error_log("Error in merge_guest_cart_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if product exists in cart
 * @param int $product_id Product ID
 * @param int|null $customer_id Customer ID
 * @param string $ip_address IP address
 * @return array|false Cart item if exists, false otherwise
 */
function check_product_in_cart_ctr($product_id, $customer_id, $ip_address)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0) {
            error_log("Invalid product ID: $product_id");
            return false;
        }

        if (empty($ip_address)) {
            error_log("IP address is required");
            return false;
        }

        $cart = new Cart();
        return $cart->checkProductInCart($product_id, $customer_id, $ip_address);
    } catch (Exception $e) {
        error_log("Error in check_product_in_cart_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate cart before checkout
 * Checks if cart is not empty and all products are still available
 * @param int|null $customer_id Customer ID
 * @param string $ip_address IP address
 * @return array Validation result ['valid' => bool, 'message' => string]
 */
function validate_cart_ctr($customer_id, $ip_address)
{
    try {
        $cart = new Cart();
        $items = $cart->getCartItems($customer_id, $ip_address);

        if (!$items || count($items) == 0) {
            return [
                'valid' => false,
                'message' => 'Cart is empty'
            ];
        }

        // Check if all products still exist and are available
        foreach ($items as $item) {
            if (!$item['product_title']) {
                return [
                    'valid' => false,
                    'message' => 'Some products in your cart are no longer available'
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Cart is valid'
        ];
    } catch (Exception $e) {
        error_log("Error in validate_cart_ctr: " . $e->getMessage());
        return [
            'valid' => false,
            'message' => 'Error validating cart'
        ];
    }
}
?>
