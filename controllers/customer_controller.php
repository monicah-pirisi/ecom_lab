<?php

require_once '../classes/customer_class.php';

function login_customer_ctr($email, $password)
{
    $customer = new Customer();
    $result = $customer->loginCustomer($email, $password);
    
    if ($result['status'] === 'success') {
        return array(
            'status' => 'success',
            'user_id' => $result['user_id'],
            'user_name' => $result['user_name'],
            'user_email' => $result['user_email'],
            'user_role' => $result['user_role'],
            'user_country' => $result['user_country'],
            'user_city' => $result['user_city'],
            'user_phone' => $result['user_phone']
        );
    } else {
        return array(
            'status' => 'error',
            'message' => $result['message']
        );
    }
}

function get_customer_by_email_ctr($email)
{
    $customer = new Customer();
    return $customer->getCustomerByEmail($email);
}

function register_customer_ctr($name, $email, $password, $phone_number, $country, $city, $role)
{
    $customer = new Customer();
    $customer_id = $customer->createCustomer($name, $email, $password, $phone_number, $country, $city, $role);
    if ($customer_id) {
        return $customer_id;
    }
    return false;
}
?>
