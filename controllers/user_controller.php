<?php

require_once '../classes/customer_class.php';


function register_user_ctr($name, $email, $password, $phone_number, $country, $city, $role)
{
    $customer = new Customer();
    $user_id = $customer->createCustomer($name, $email, $password, $phone_number, $country, $city, $role);
    if ($user_id) {
        return $user_id;
    }
    return false;
}

function get_user_by_email_ctr($email)
{
    $customer = new Customer();
    return $customer->getCustomerByEmail($email);
}