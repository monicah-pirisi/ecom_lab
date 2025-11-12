<?php
/**
 * Restaurant Class
 * Handles all database operations for restaurants and reviews
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Restaurant extends db_connection
{
    /**
     * Create a new restaurant
     */
    public function createRestaurant($data)
    {
        try {
            $sql = "INSERT INTO restaurants
                    (owner_id, restaurant_name, description, cuisine_type, address, city, country, phone, email, opening_hours, restaurant_image, status)
                    VALUES
                    ('{$data['owner_id']}', '{$data['restaurant_name']}', '{$data['description']}', '{$data['cuisine_type']}',
                     '{$data['address']}', '{$data['city']}', '{$data['country']}', '{$data['phone']}', '{$data['email']}',
                     '{$data['opening_hours']}', '{$data['restaurant_image']}', 'Active')";

            $result = $this->db_write_query($sql);
            if ($result) {
                return $this->last_insert_id();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error creating restaurant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get restaurants by owner ID
     */
    public function getRestaurantsByOwner($owner_id)
    {
        try {
            $sql = "SELECT * FROM restaurants WHERE owner_id = '$owner_id' ORDER BY created_at DESC";
            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error getting owner restaurants: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get restaurant by ID
     */
    public function getRestaurantById($restaurant_id)
    {
        try {
            $sql = "SELECT r.*, c.customer_name as owner_name, c.customer_email as owner_email
                    FROM restaurants r
                    LEFT JOIN customer c ON r.owner_id = c.customer_id
                    WHERE r.restaurant_id = '$restaurant_id'";
            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error getting restaurant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update restaurant
     */
    public function updateRestaurant($restaurant_id, $data)
    {
        try {
            $sql = "UPDATE restaurants SET
                    restaurant_name = '{$data['restaurant_name']}',
                    description = '{$data['description']}',
                    cuisine_type = '{$data['cuisine_type']}',
                    address = '{$data['address']}',
                    city = '{$data['city']}',
                    country = '{$data['country']}',
                    phone = '{$data['phone']}',
                    email = '{$data['email']}',
                    opening_hours = '{$data['opening_hours']}',
                    status = '{$data['status']}'";

            if (!empty($data['restaurant_image'])) {
                $sql .= ", restaurant_image = '{$data['restaurant_image']}'";
            }

            $sql .= " WHERE restaurant_id = '$restaurant_id'";

            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error updating restaurant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete restaurant
     */
    public function deleteRestaurant($restaurant_id)
    {
        try {
            $sql = "DELETE FROM restaurants WHERE restaurant_id = '$restaurant_id'";
            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error deleting restaurant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all restaurants (for public listing)
     */
    public function getAllRestaurants()
    {
        try {
            $sql = "SELECT r.*, c.customer_name as owner_name,
                    (SELECT AVG(rating) FROM reviews WHERE restaurant_id = r.restaurant_id) as avg_rating,
                    (SELECT COUNT(*) FROM reviews WHERE restaurant_id = r.restaurant_id) as review_count
                    FROM restaurants r
                    LEFT JOIN customer c ON r.owner_id = c.customer_id
                    WHERE r.status = 'Active'
                    ORDER BY r.created_at DESC";
            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error getting all restaurants: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add review for restaurant
     */
    public function addReview($restaurant_id, $customer_id, $rating, $comment)
    {
        try {
            $sql = "INSERT INTO reviews (restaurant_id, customer_id, rating, comment)
                    VALUES ('$restaurant_id', '$customer_id', '$rating', '$comment')";
            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error adding review: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get reviews for restaurant
     */
    public function getRestaurantReviews($restaurant_id)
    {
        try {
            $sql = "SELECT r.*, c.customer_name, c.customer_image
                    FROM reviews r
                    LEFT JOIN customer c ON r.customer_id = c.customer_id
                    WHERE r.restaurant_id = '$restaurant_id'
                    ORDER BY r.created_at DESC";
            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error getting reviews: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get reviews for owner's restaurants
     */
    public function getOwnerRestaurantReviews($owner_id)
    {
        try {
            $sql = "SELECT rv.*, rst.restaurant_name, c.customer_name
                    FROM reviews rv
                    INNER JOIN restaurants rst ON rv.restaurant_id = rst.restaurant_id
                    LEFT JOIN customer c ON rv.customer_id = c.customer_id
                    WHERE rst.owner_id = '$owner_id'
                    ORDER BY rv.created_at DESC";
            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error getting owner reviews: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get restaurant statistics
     */
    public function getRestaurantStats($restaurant_id)
    {
        try {
            $sql = "SELECT
                    COUNT(DISTINCT r.review_id) as total_reviews,
                    COALESCE(AVG(r.rating), 0) as avg_rating,
                    COUNT(DISTINCT p.product_id) as total_products
                    FROM restaurants rst
                    LEFT JOIN reviews r ON rst.restaurant_id = r.restaurant_id
                    LEFT JOIN products p ON rst.owner_id = p.product_id
                    WHERE rst.restaurant_id = '$restaurant_id'";
            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error getting restaurant stats: " . $e->getMessage());
            return false;
        }
    }
}
?>
