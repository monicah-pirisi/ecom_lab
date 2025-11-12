<?php
/**
 * Order Class
 * Handles all database operations for orders, order details, and payments
 */

// Include the database connection class
require_once(__DIR__ . '/../settings/db_class.php');

class Order extends db_connection
{
    /**
     * Create a new order
     * @param int $customer_id Customer ID
     * @param int $invoice_no Invoice number
     * @param string $order_date Order date (Y-m-d format)
     * @param string $order_status Order status
     * @return int|false Order ID on success, false on failure
     */
    public function createOrder($customer_id, $invoice_no, $order_date, $order_status = 'Pending')
    {
        try {
            $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status)
                    VALUES ('$customer_id', '$invoice_no', '$order_date', '$order_status')";

            $result = $this->db_write_query($sql);

            if ($result) {
                return $this->last_insert_id();
            }

            return false;
        } catch (Exception $e) {
            error_log("Error in createOrder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add order details (products in the order)
     * @param int $order_id Order ID
     * @param int $product_id Product ID
     * @param int $quantity Quantity ordered
     * @return bool True on success, false on failure
     */
    public function addOrderDetails($order_id, $product_id, $quantity)
    {
        try {
            $sql = "INSERT INTO orderdetails (order_id, product_id, qty)
                    VALUES ('$order_id', '$product_id', '$quantity')";

            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error in addOrderDetails: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create payment record
     * @param float $amount Payment amount
     * @param int $customer_id Customer ID
     * @param int $order_id Order ID
     * @param string $currency Currency code
     * @param string $payment_date Payment date (Y-m-d format)
     * @return int|false Payment ID on success, false on failure
     */
    public function createPayment($amount, $customer_id, $order_id, $currency, $payment_date)
    {
        try {
            $sql = "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date)
                    VALUES ('$amount', '$customer_id', '$order_id', '$currency', '$payment_date')";

            $result = $this->db_write_query($sql);

            if ($result) {
                return $this->last_insert_id();
            }

            return false;
        } catch (Exception $e) {
            error_log("Error in createPayment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order by ID
     * @param int $order_id Order ID
     * @return array|false Order data, false on failure
     */
    public function getOrderById($order_id)
    {
        try {
            $sql = "SELECT o.*, c.customer_name, c.customer_email, c.customer_contact
                    FROM orders o
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    WHERE o.order_id = '$order_id'";

            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error in getOrderById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order by invoice number
     * @param int $invoice_no Invoice number
     * @return array|false Order data, false on failure
     */
    public function getOrderByInvoice($invoice_no)
    {
        try {
            $sql = "SELECT o.*, c.customer_name, c.customer_email, c.customer_contact
                    FROM orders o
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    WHERE o.invoice_no = '$invoice_no'";

            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error in getOrderByInvoice: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all orders for a customer
     * @param int $customer_id Customer ID
     * @return array|false Array of orders, false on failure
     */
    public function getCustomerOrders($customer_id)
    {
        try {
            $sql = "SELECT o.*,
                    (SELECT SUM(od.qty * p.product_price)
                     FROM orderdetails od
                     INNER JOIN products p ON od.product_id = p.product_id
                     WHERE od.order_id = o.order_id) as total_amount
                    FROM orders o
                    WHERE o.customer_id = '$customer_id'
                    ORDER BY o.order_date DESC, o.order_id DESC";

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error in getCustomerOrders: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order details (products) for an order
     * @param int $order_id Order ID
     * @return array|false Array of order details, false on failure
     */
    public function getOrderDetails($order_id)
    {
        try {
            $sql = "SELECT od.*, p.product_title, p.product_price, p.product_image,
                    (od.qty * p.product_price) as subtotal
                    FROM orderdetails od
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE od.order_id = '$order_id'";

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error in getOrderDetails: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment information for an order
     * @param int $order_id Order ID
     * @return array|false Payment data, false on failure
     */
    public function getPaymentByOrder($order_id)
    {
        try {
            $sql = "SELECT * FROM payment WHERE order_id = '$order_id'";

            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error in getPaymentByOrder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order status
     * @param int $order_id Order ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateOrderStatus($order_id, $status)
    {
        try {
            $sql = "UPDATE orders SET order_status = '$status'
                    WHERE order_id = '$order_id'";

            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error in updateOrderStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all orders (for admin)
     * @return array|false Array of all orders, false on failure
     */
    public function getAllOrders()
    {
        try {
            $sql = "SELECT o.*, c.customer_name, c.customer_email,
                    (SELECT SUM(od.qty * p.product_price)
                     FROM orderdetails od
                     INNER JOIN products p ON od.product_id = p.product_id
                     WHERE od.order_id = o.order_id) as total_amount
                    FROM orders o
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    ORDER BY o.order_date DESC, o.order_id DESC";

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error in getAllOrders: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get orders by status
     * @param string $status Order status
     * @return array|false Array of orders, false on failure
     */
    public function getOrdersByStatus($status)
    {
        try {
            $sql = "SELECT o.*, c.customer_name, c.customer_email,
                    (SELECT SUM(od.qty * p.product_price)
                     FROM orderdetails od
                     INNER JOIN products p ON od.product_id = p.product_id
                     WHERE od.order_id = o.order_id) as total_amount
                    FROM orders o
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    WHERE o.order_status = '$status'
                    ORDER BY o.order_date DESC, o.order_id DESC";

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error in getOrdersByStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique invoice number
     * @return int Unique invoice number
     */
    public function generateInvoiceNumber()
    {
        try {
            // Get the maximum invoice number
            $sql = "SELECT MAX(invoice_no) as max_invoice FROM orders";
            $result = $this->db_fetch_one($sql);

            if ($result && $result['max_invoice']) {
                return (int)$result['max_invoice'] + 1;
            }

            // If no orders exist, start from 1000
            return 1000;
        } catch (Exception $e) {
            error_log("Error in generateInvoiceNumber: " . $e->getMessage());
            // Return timestamp-based number as fallback
            return (int)(time() - 1600000000);
        }
    }

    /**
     * Check if invoice number exists
     * @param int $invoice_no Invoice number
     * @return bool True if exists, false otherwise
     */
    public function invoiceExists($invoice_no)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM orders WHERE invoice_no = '$invoice_no'";
            $result = $this->db_fetch_one($sql);

            return $result && $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Error in invoiceExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete order and all related data
     * @param int $order_id Order ID
     * @return bool True on success, false on failure
     */
    public function deleteOrder($order_id)
    {
        try {
            // Delete payment first
            $sql1 = "DELETE FROM payment WHERE order_id = '$order_id'";
            $this->db_write_query($sql1);

            // Delete order details
            $sql2 = "DELETE FROM orderdetails WHERE order_id = '$order_id'";
            $this->db_write_query($sql2);

            // Delete order
            $sql3 = "DELETE FROM orders WHERE order_id = '$order_id'";
            return $this->db_write_query($sql3);
        } catch (Exception $e) {
            error_log("Error in deleteOrder: " . $e->getMessage());
            return false;
        }
    }
}
?>
