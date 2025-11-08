<?php

require_once '../settings/db_class.php';

/**
 * Customer Class for handling customer operations
 */
class Customer extends db_connection
{
    private $customer_id;
    private $customer_name;
    private $customer_email;
    private $customer_password;
    private $customer_contact;
    private $customer_country;
    private $customer_city;
    private $customer_image;
    private $user_role;
    private $date_created;

    public function __construct($customer_id = null)
    {
        if (!parent::db_connect()) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }
        if ($customer_id) {
            $this->customer_id = $customer_id;
            $this->loadCustomer();
        }
    }

    private function loadCustomer($customer_id = null)
    {
        if ($customer_id) {
            $this->customer_id = $customer_id;
        }
        if (!$this->customer_id) {
            return false;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $this->customer_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $this->customer_name = $result['customer_name'];
            $this->customer_email = $result['customer_email'];
            $this->customer_password = $result['customer_pass'];
            $this->customer_contact = $result['customer_contact'];
            $this->customer_country = $result['customer_country'];
            $this->customer_city = $result['customer_city'];
            $this->customer_image = $result['customer_image'];
            $this->user_role = $result['user_role'];
            $this->date_created = isset($result['date_created']) ? $result['date_created'] : null;
        }
    }

    public function createCustomer($name, $email, $password, $phone_number, $country, $city, $role)
    {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, customer_country, customer_city, user_role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Database prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("ssssssi", $name, $email, $hashed_password, $phone_number, $country, $city, $role);
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            } else {
                throw new Exception("Database execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Customer creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getCustomerByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function loginCustomer($email, $password)
    {
        // Get customer by email
        $customer = $this->getCustomerByEmail($email);
        
        if (!$customer) {
            return array(
                'status' => 'error',
                'message' => 'Invalid email or password'
            );
        }

        // Verify password
        if (password_verify($password, $customer['customer_pass'])) {
            return array(
                'status' => 'success',
                'user_id' => $customer['customer_id'],
                'user_name' => $customer['customer_name'],
                'user_email' => $customer['customer_email'],
                'user_role' => $customer['user_role'],
                'user_country' => $customer['customer_country'],
                'user_city' => $customer['customer_city'],
                'user_phone' => $customer['customer_contact']
            );
        } else {
            return array(
                'status' => 'error',
                'message' => 'Invalid email or password'
            );
        }
    }

    // Getter methods
    public function getCustomerId() { return $this->customer_id; }
    public function getCustomerName() { return $this->customer_name; }
    public function getCustomerEmail() { return $this->customer_email; }
    public function getCustomerContact() { return $this->customer_contact; }
    public function getCustomerCountry() { return $this->customer_country; }
    public function getCustomerCity() { return $this->customer_city; }
    public function getUserRole() { return $this->user_role; }
    public function getDateCreated() { return $this->date_created; }
}
?>
