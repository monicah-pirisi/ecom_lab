<?php
/**
 * Restaurant Controller
 * Handles validation and business logic for restaurant operations
 */

require_once(__DIR__ . '/../classes/restaurant_class.php');

/**
 * Create a new restaurant
 */
function create_restaurant_ctr($data)
{
    // Validate required fields
    $required = ['owner_id', 'restaurant_name', 'address', 'city', 'country', 'phone'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            error_log("Missing required field: $field");
            return false;
        }
    }

    // Validate owner_id
    if (!is_numeric($data['owner_id']) || $data['owner_id'] <= 0) {
        error_log("Invalid owner ID: " . $data['owner_id']);
        return false;
    }

    // Validate phone format (basic)
    if (!preg_match('/^[0-9\s\+\-\(\)]+$/', $data['phone'])) {
        error_log("Invalid phone format: " . $data['phone']);
        return false;
    }

    // Validate email if provided
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: " . $data['email']);
        return false;
    }

    // Sanitize inputs
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $data[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }

    $restaurant = new Restaurant();
    return $restaurant->createRestaurant($data);
}

/**
 * Get restaurants by owner ID
 */
function get_restaurants_by_owner_ctr($owner_id)
{
    // Validate owner ID
    if (!is_numeric($owner_id) || $owner_id <= 0) {
        error_log("Invalid owner ID: $owner_id");
        return false;
    }

    $restaurant = new Restaurant();
    return $restaurant->getRestaurantsByOwner($owner_id);
}

/**
 * Get restaurant by ID
 */
function get_restaurant_by_id_ctr($restaurant_id)
{
    // Validate restaurant ID
    if (!is_numeric($restaurant_id) || $restaurant_id <= 0) {
        error_log("Invalid restaurant ID: $restaurant_id");
        return false;
    }

    $restaurant = new Restaurant();
    return $restaurant->getRestaurantById($restaurant_id);
}

/**
 * Update restaurant
 */
function update_restaurant_ctr($restaurant_id, $data)
{
    // Validate restaurant ID
    if (!is_numeric($restaurant_id) || $restaurant_id <= 0) {
        error_log("Invalid restaurant ID: $restaurant_id");
        return false;
    }

    // Validate required fields
    $required = ['restaurant_name', 'address', 'city', 'country', 'phone', 'status'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            error_log("Missing required field: $field");
            return false;
        }
    }

    // Validate phone format
    if (!preg_match('/^[0-9\s\+\-\(\)]+$/', $data['phone'])) {
        error_log("Invalid phone format: " . $data['phone']);
        return false;
    }

    // Validate email if provided
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: " . $data['email']);
        return false;
    }

    // Validate status
    $valid_statuses = ['Active', 'Inactive', 'Pending'];
    if (!in_array($data['status'], $valid_statuses)) {
        error_log("Invalid status: " . $data['status']);
        return false;
    }

    // Sanitize inputs
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $data[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }

    $restaurant = new Restaurant();
    return $restaurant->updateRestaurant($restaurant_id, $data);
}

/**
 * Delete restaurant
 */
function delete_restaurant_ctr($restaurant_id)
{
    // Validate restaurant ID
    if (!is_numeric($restaurant_id) || $restaurant_id <= 0) {
        error_log("Invalid restaurant ID: $restaurant_id");
        return false;
    }

    $restaurant = new Restaurant();
    return $restaurant->deleteRestaurant($restaurant_id);
}

/**
 * Get all restaurants (public listing)
 */
function get_all_restaurants_ctr()
{
    $restaurant = new Restaurant();
    return $restaurant->getAllRestaurants();
}

/**
 * Add review for restaurant
 */
function add_review_ctr($restaurant_id, $customer_id, $rating, $comment)
{
    // Validate restaurant ID
    if (!is_numeric($restaurant_id) || $restaurant_id <= 0) {
        error_log("Invalid restaurant ID: $restaurant_id");
        return false;
    }

    // Validate customer ID
    if (!is_numeric($customer_id) || $customer_id <= 0) {
        error_log("Invalid customer ID: $customer_id");
        return false;
    }

    // Validate rating (1-5)
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        error_log("Invalid rating: $rating. Must be between 1 and 5.");
        return false;
    }

    // Sanitize comment
    $comment = htmlspecialchars(trim($comment), ENT_QUOTES, 'UTF-8');

    $restaurant = new Restaurant();
    return $restaurant->addReview($restaurant_id, $customer_id, $rating, $comment);
}

/**
 * Get reviews for restaurant
 */
function get_restaurant_reviews_ctr($restaurant_id)
{
    // Validate restaurant ID
    if (!is_numeric($restaurant_id) || $restaurant_id <= 0) {
        error_log("Invalid restaurant ID: $restaurant_id");
        return false;
    }

    $restaurant = new Restaurant();
    return $restaurant->getRestaurantReviews($restaurant_id);
}

/**
 * Get all reviews for owner's restaurants
 */
function get_owner_reviews_ctr($owner_id)
{
    // Validate owner ID
    if (!is_numeric($owner_id) || $owner_id <= 0) {
        error_log("Invalid owner ID: $owner_id");
        return false;
    }

    $restaurant = new Restaurant();
    return $restaurant->getOwnerRestaurantReviews($owner_id);
}

/**
 * Get restaurant statistics
 */
function get_restaurant_stats_ctr($restaurant_id)
{
    // Validate restaurant ID
    if (!is_numeric($restaurant_id) || $restaurant_id <= 0) {
        error_log("Invalid restaurant ID: $restaurant_id");
        return false;
    }

    $restaurant = new Restaurant();
    return $restaurant->getRestaurantStats($restaurant_id);
}

/**
 * Get owner analytics (all restaurants combined)
 */
function get_owner_analytics_ctr($owner_id)
{
    // Validate owner ID
    if (!is_numeric($owner_id) || $owner_id <= 0) {
        error_log("Invalid owner ID: $owner_id");
        return false;
    }

    $restaurant = new Restaurant();
    $restaurants = $restaurant->getRestaurantsByOwner($owner_id);

    if (!$restaurants) {
        return [
            'total_restaurants' => 0,
            'total_reviews' => 0,
            'average_rating' => 0,
            'total_products' => 0,
            'active_restaurants' => 0
        ];
    }

    $total_reviews = 0;
    $total_rating = 0;
    $rating_count = 0;
    $active_count = 0;

    foreach ($restaurants as $rest) {
        $stats = $restaurant->getRestaurantStats($rest['restaurant_id']);
        if ($stats) {
            $total_reviews += $stats['total_reviews'];
            if ($stats['avg_rating'] > 0) {
                $total_rating += $stats['avg_rating'];
                $rating_count++;
            }
        }
        if ($rest['status'] === 'Active') {
            $active_count++;
        }
    }

    return [
        'total_restaurants' => count($restaurants),
        'total_reviews' => $total_reviews,
        'average_rating' => $rating_count > 0 ? round($total_rating / $rating_count, 2) : 0,
        'active_restaurants' => $active_count
    ];
}

/**
 * Check if user owns restaurant
 */
function user_owns_restaurant_ctr($restaurant_id, $owner_id)
{
    // Validate IDs
    if (!is_numeric($restaurant_id) || $restaurant_id <= 0) {
        return false;
    }
    if (!is_numeric($owner_id) || $owner_id <= 0) {
        return false;
    }

    $restaurant = new Restaurant();
    $rest = $restaurant->getRestaurantById($restaurant_id);

    if (!$rest) {
        return false;
    }

    return $rest['owner_id'] == $owner_id;
}
?>
