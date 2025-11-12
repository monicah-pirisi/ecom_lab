<?php
/**
 * Cart Class
 * Handles all database operations for shopping cart functionality
 * Supports both logged-in users (via customer_id) and guest users (via IP address)
 */

// Include the database connection class
require_once(__DIR__ . '/../settings/db_class.php');

class Cart extends db_connection
{
    /**
     * Add product to cart or update quantity if already exists
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID (null for guest users)
     * @param string $ip_address User's IP address
     * @param int $quantity Quantity to add
     * @return bool True on success, false on failure
     */
    public function addToCart($product_id, $customer_id, $ip_address, $quantity)
    {
        try {
            // Check if product already exists in cart
            $existing = $this->checkProductInCart($product_id, $customer_id, $ip_address);

            if ($existing) {
                // Product exists - update quantity by adding to existing
                $new_quantity = $existing['qty'] + $quantity;
                return $this->updateCartQuantity($product_id, $customer_id, $ip_address, $new_quantity);
            } else {
                // Product doesn't exist - insert new record
                $customer_id_value = ($customer_id !== null) ? $customer_id : 'NULL';

                $sql = "INSERT INTO cart (p_id, c_id, ip_add, qty)
                        VALUES ('$product_id', $customer_id_value, '$ip_address', '$quantity')";

                return $this->db_write_query($sql);
            }
        } catch (Exception $e) {
            error_log("Error in addToCart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a product already exists in the cart
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID
     * @param string $ip_address IP address
     * @return array|false Cart item if exists, false otherwise
     */
    public function checkProductInCart($product_id, $customer_id, $ip_address)
    {
        try {
            if ($customer_id !== null) {
                // For logged-in users, check by customer_id
                $sql = "SELECT * FROM cart WHERE p_id = '$product_id' AND c_id = '$customer_id'";
            } else {
                // For guest users, check by IP address
                $sql = "SELECT * FROM cart WHERE p_id = '$product_id' AND ip_add = '$ip_address' AND c_id IS NULL";
            }

            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error in checkProductInCart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all cart items for a user
     * @param int|null $customer_id Customer ID
     * @param string $ip_address IP address
     * @return array|false Array of cart items with product details, false on failure
     */
    public function getCartItems($customer_id, $ip_address)
    {
        try {
            if ($customer_id !== null) {
                // For logged-in users
                $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image,
                        p.product_desc, (c.qty * p.product_price) as subtotal
                        FROM cart c
                        INNER JOIN products p ON c.p_id = p.product_id
                        WHERE c.c_id = '$customer_id'
                        ORDER BY p.product_title";
            } else {
                // For guest users
                $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image,
                        p.product_desc, (c.qty * p.product_price) as subtotal
                        FROM cart c
                        INNER JOIN products p ON c.p_id = p.product_id
                        WHERE c.ip_add = '$ip_address' AND c.c_id IS NULL
                        ORDER BY p.product_title";
            }

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error in getCartItems: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update quantity of a cart item
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID
     * @param string $ip_address IP address
     * @param int $quantity New quantity
     * @return bool True on success, false on failure
     */
    public function updateCartQuantity($product_id, $customer_id, $ip_address, $quantity)
    {
        try {
            // If quantity is 0 or negative, remove the item
            if ($quantity <= 0) {
                return $this->removeFromCart($product_id, $customer_id, $ip_address);
            }

            if ($customer_id !== null) {
                // For logged-in users
                $sql = "UPDATE cart SET qty = '$quantity'
                        WHERE p_id = '$product_id' AND c_id = '$customer_id'";
            } else {
                // For guest users
                $sql = "UPDATE cart SET qty = '$quantity'
                        WHERE p_id = '$product_id' AND ip_add = '$ip_address' AND c_id IS NULL";
            }

            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error in updateCartQuantity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a specific product from cart
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID
     * @param string $ip_address IP address
     * @return bool True on success, false on failure
     */
    public function removeFromCart($product_id, $customer_id, $ip_address)
    {
        try {
            if ($customer_id !== null) {
                // For logged-in users
                $sql = "DELETE FROM cart WHERE p_id = '$product_id' AND c_id = '$customer_id'";
            } else {
                // For guest users
                $sql = "DELETE FROM cart WHERE p_id = '$product_id' AND ip_add = '$ip_address' AND c_id IS NULL";
            }

            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error in removeFromCart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all items from cart
     * @param int|null $customer_id Customer ID
     * @param string $ip_address IP address
     * @return bool True on success, false on failure
     */
    public function clearCart($customer_id, $ip_address)
    {
        try {
            if ($customer_id !== null) {
                // For logged-in users
                $sql = "DELETE FROM cart WHERE c_id = '$customer_id'";
            } else {
                // For guest users
                $sql = "DELETE FROM cart WHERE ip_add = '$ip_address' AND c_id IS NULL";
            }

            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error in clearCart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cart count (number of items)
     * @param int|null $customer_id Customer ID
     * @param string $ip_address IP address
     * @return int Number of items in cart
     */
    public function getCartCount($customer_id, $ip_address)
    {
        try {
            if ($customer_id !== null) {
                // For logged-in users
                $sql = "SELECT COUNT(*) as count FROM cart WHERE c_id = '$customer_id'";
            } else {
                // For guest users
                $sql = "SELECT COUNT(*) as count FROM cart WHERE ip_add = '$ip_address' AND c_id IS NULL";
            }

            $result = $this->db_fetch_one($sql);
            return $result ? (int)$result['count'] : 0;
        } catch (Exception $e) {
            error_log("Error in getCartCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get cart total amount
     * @param int|null $customer_id Customer ID
     * @param string $ip_address IP address
     * @return float Total cart amount
     */
    public function getCartTotal($customer_id, $ip_address)
    {
        try {
            if ($customer_id !== null) {
                // For logged-in users
                $sql = "SELECT SUM(c.qty * p.product_price) as total
                        FROM cart c
                        INNER JOIN products p ON c.p_id = p.product_id
                        WHERE c.c_id = '$customer_id'";
            } else {
                // For guest users
                $sql = "SELECT SUM(c.qty * p.product_price) as total
                        FROM cart c
                        INNER JOIN products p ON c.p_id = p.product_id
                        WHERE c.ip_add = '$ip_address' AND c.c_id IS NULL";
            }

            $result = $this->db_fetch_one($sql);
            return $result && $result['total'] ? (float)$result['total'] : 0.0;
        } catch (Exception $e) {
            error_log("Error in getCartTotal: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Transfer guest cart to logged-in user cart
     * Used when a guest user logs in
     * @param int $customer_id Customer ID
     * @param string $ip_address IP address
     * @return bool True on success, false on failure
     */
    public function mergeGuestCart($customer_id, $ip_address)
    {
        try {
            // Get all guest cart items
            $guest_items = $this->getCartItems(null, $ip_address);

            if (!$guest_items || count($guest_items) == 0) {
                return true; // No items to merge
            }

            // For each guest item, add to user's cart (which handles duplicates)
            foreach ($guest_items as $item) {
                $this->addToCart($item['p_id'], $customer_id, $ip_address, $item['qty']);
            }

            // Clear guest cart
            $sql = "DELETE FROM cart WHERE ip_add = '$ip_address' AND c_id IS NULL";
            return $this->db_write_query($sql);

        } catch (Exception $e) {
            error_log("Error in mergeGuestCart: " . $e->getMessage());
            return false;
        }
    }
}
?>
