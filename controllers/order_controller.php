<?php
/**
 * Order Controller
 * Business logic layer for order operations
 * Wraps order_class methods with validation and error handling
 */

// Include the order class
require_once(__DIR__ . '/../classes/order_class.php');

/**
 * Create a new order
 * @param int $customer_id Customer ID
 * @param int $invoice_no Invoice number
 * @param string $order_date Order date
 * @param string $order_status Order status
 * @return int|false Order ID on success, false on failure
 */
function create_order_ctr($customer_id, $invoice_no, $order_date, $order_status = 'Pending')
{
    try {
        // Validate inputs
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: $customer_id");
            return false;
        }

        if (!is_numeric($invoice_no) || $invoice_no <= 0) {
            error_log("Invalid invoice number: $invoice_no");
            return false;
        }

        if (empty($order_date)) {
            error_log("Order date is required");
            return false;
        }

        $order = new Order();
        return $order->createOrder($customer_id, $invoice_no, $order_date, $order_status);
    } catch (Exception $e) {
        error_log("Error in create_order_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Add order details
 * @param int $order_id Order ID
 * @param int $product_id Product ID
 * @param int $quantity Quantity
 * @return bool True on success, false on failure
 */
function add_order_details_ctr($order_id, $product_id, $quantity)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: $order_id");
            return false;
        }

        if (!is_numeric($product_id) || $product_id <= 0) {
            error_log("Invalid product ID: $product_id");
            return false;
        }

        if (!is_numeric($quantity) || $quantity <= 0) {
            error_log("Invalid quantity: $quantity");
            return false;
        }

        $order = new Order();
        return $order->addOrderDetails($order_id, $product_id, $quantity);
    } catch (Exception $e) {
        error_log("Error in add_order_details_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Create payment record
 * @param float $amount Payment amount
 * @param int $customer_id Customer ID
 * @param int $order_id Order ID
 * @param string $currency Currency code
 * @param string $payment_date Payment date
 * @return int|false Payment ID on success, false on failure
 */
function create_payment_ctr($amount, $customer_id, $order_id, $currency, $payment_date)
{
    try {
        if (!is_numeric($amount) || $amount <= 0) {
            error_log("Invalid amount: $amount");
            return false;
        }

        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: $customer_id");
            return false;
        }

        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: $order_id");
            return false;
        }

        if (empty($currency)) {
            error_log("Currency is required");
            return false;
        }

        $order = new Order();
        return $order->createPayment($amount, $customer_id, $order_id, $currency, $payment_date);
    } catch (Exception $e) {
        error_log("Error in create_payment_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get order by ID
 * @param int $order_id Order ID
 * @return array|false Order data, false on failure
 */
function get_order_by_id_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: $order_id");
            return false;
        }

        $order = new Order();
        return $order->getOrderById($order_id);
    } catch (Exception $e) {
        error_log("Error in get_order_by_id_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get order by invoice number
 * @param int $invoice_no Invoice number
 * @return array|false Order data, false on failure
 */
function get_order_by_invoice_ctr($invoice_no)
{
    try {
        if (!is_numeric($invoice_no) || $invoice_no <= 0) {
            error_log("Invalid invoice number: $invoice_no");
            return false;
        }

        $order = new Order();
        return $order->getOrderByInvoice($invoice_no);
    } catch (Exception $e) {
        error_log("Error in get_order_by_invoice_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all orders for a customer
 * @param int $customer_id Customer ID
 * @return array|false Array of orders, false on failure
 */
function get_customer_orders_ctr($customer_id)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: $customer_id");
            return false;
        }

        $order = new Order();
        return $order->getCustomerOrders($customer_id);
    } catch (Exception $e) {
        error_log("Error in get_customer_orders_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get order details (products)
 * @param int $order_id Order ID
 * @return array|false Array of order details, false on failure
 */
function get_order_details_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: $order_id");
            return false;
        }

        $order = new Order();
        return $order->getOrderDetails($order_id);
    } catch (Exception $e) {
        error_log("Error in get_order_details_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get payment by order ID
 * @param int $order_id Order ID
 * @return array|false Payment data, false on failure
 */
function get_payment_by_order_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: $order_id");
            return false;
        }

        $order = new Order();
        return $order->getPaymentByOrder($order_id);
    } catch (Exception $e) {
        error_log("Error in get_payment_by_order_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Update order status
 * @param int $order_id Order ID
 * @param string $status New status
 * @return bool True on success, false on failure
 */
function update_order_status_ctr($order_id, $status)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: $order_id");
            return false;
        }

        if (empty($status)) {
            error_log("Status is required");
            return false;
        }

        $order = new Order();
        return $order->updateOrderStatus($order_id, $status);
    } catch (Exception $e) {
        error_log("Error in update_order_status_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all orders (admin)
 * @return array|false Array of orders, false on failure
 */
function get_all_orders_ctr()
{
    try {
        $order = new Order();
        return $order->getAllOrders();
    } catch (Exception $e) {
        error_log("Error in get_all_orders_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Get orders by status
 * @param string $status Order status
 * @return array|false Array of orders, false on failure
 */
function get_orders_by_status_ctr($status)
{
    try {
        if (empty($status)) {
            error_log("Status is required");
            return false;
        }

        $order = new Order();
        return $order->getOrdersByStatus($status);
    } catch (Exception $e) {
        error_log("Error in get_orders_by_status_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate unique invoice number
 * @return int Invoice number
 */
function generate_invoice_number_ctr()
{
    try {
        $order = new Order();
        return $order->generateInvoiceNumber();
    } catch (Exception $e) {
        error_log("Error in generate_invoice_number_ctr: " . $e->getMessage());
        return (int)(time() - 1600000000);
    }
}

/**
 * Check if invoice exists
 * @param int $invoice_no Invoice number
 * @return bool True if exists, false otherwise
 */
function invoice_exists_ctr($invoice_no)
{
    try {
        if (!is_numeric($invoice_no)) {
            return false;
        }

        $order = new Order();
        return $order->invoiceExists($invoice_no);
    } catch (Exception $e) {
        error_log("Error in invoice_exists_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete order
 * @param int $order_id Order ID
 * @return bool True on success, false on failure
 */
function delete_order_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: $order_id");
            return false;
        }

        $order = new Order();
        return $order->deleteOrder($order_id);
    } catch (Exception $e) {
        error_log("Error in delete_order_ctr: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate unique order reference
 * Format: ORD-YYYYMMDD-XXXXX
 * @return string Order reference
 */
function generate_order_reference_ctr()
{
    try {
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return "ORD-{$date}-{$random}";
    } catch (Exception $e) {
        error_log("Error in generate_order_reference_ctr: " . $e->getMessage());
        return "ORD-" . time();
    }
}

/**
 * Process complete checkout
 * Creates order, order details, and payment in a single transaction
 * @param int $customer_id Customer ID
 * @param array $cart_items Cart items
 * @param float $total_amount Total amount
 * @param string $currency Currency code
 * @return array Result ['success' => bool, 'order_id' => int, 'invoice_no' => int, 'reference' => string, 'message' => string]
 */
function process_checkout_ctr($customer_id, $cart_items, $total_amount, $currency = 'USD')
{
    try {
        // Validate customer
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid customer ID'
            ];
        }

        // Validate cart items
        if (empty($cart_items) || !is_array($cart_items)) {
            return [
                'success' => false,
                'message' => 'Cart is empty'
            ];
        }

        // Validate amount
        if (!is_numeric($total_amount) || $total_amount <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid order amount'
            ];
        }

        $order = new Order();

        // Generate invoice number
        $invoice_no = $order->generateInvoiceNumber();

        // Create order
        $order_date = date('Y-m-d');
        $order_id = $order->createOrder($customer_id, $invoice_no, $order_date, 'Pending');

        if (!$order_id) {
            return [
                'success' => false,
                'message' => 'Failed to create order'
            ];
        }

        // Add order details for each cart item
        foreach ($cart_items as $item) {
            $result = $order->addOrderDetails($order_id, $item['p_id'], $item['qty']);
            if (!$result) {
                error_log("Failed to add order details for product: " . $item['p_id']);
            }
        }

        // Create payment record
        $payment_date = date('Y-m-d');
        $payment_id = $order->createPayment($total_amount, $customer_id, $order_id, $currency, $payment_date);

        if (!$payment_id) {
            error_log("Failed to create payment record for order: $order_id");
        }

        // Generate order reference
        $reference = generate_order_reference_ctr();

        return [
            'success' => true,
            'order_id' => $order_id,
            'invoice_no' => $invoice_no,
            'reference' => $reference,
            'message' => 'Order placed successfully'
        ];

    } catch (Exception $e) {
        error_log("Error in process_checkout_ctr: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An error occurred while processing your order'
        ];
    }
}
?>
