<?php
/**
 * Add Review Action
 * Handles customer review submission
 */

// Start output buffering
ob_start();

// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/restaurant_controller.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'Please log in to submit a review'
        ]);
        exit();
    }

    // Check if form was submitted via POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
        exit();
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid security token'
        ]);
        exit();
    }

    $customer_id = $_SESSION['user_id'];

    // Validate required fields
    if (!isset($_POST['restaurant_id']) || empty($_POST['restaurant_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Restaurant ID is required'
        ]);
        exit();
    }

    if (!isset($_POST['rating']) || empty($_POST['rating'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please select a rating'
        ]);
        exit();
    }

    $restaurant_id = intval($_POST['restaurant_id']);
    $rating = intval($_POST['rating']);
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // Validate rating range
    if ($rating < 1 || $rating > 5) {
        echo json_encode([
            'success' => false,
            'message' => 'Rating must be between 1 and 5 stars'
        ]);
        exit();
    }

    // Validate restaurant exists
    $restaurant = get_restaurant_by_id_ctr($restaurant_id);
    if (!$restaurant) {
        echo json_encode([
            'success' => false,
            'message' => 'Restaurant not found'
        ]);
        exit();
    }

    // Check if user already reviewed this restaurant
    $existing_review = check_existing_review_ctr($restaurant_id, $customer_id);
    if ($existing_review) {
        echo json_encode([
            'success' => false,
            'message' => 'You have already reviewed this restaurant. You can only submit one review per restaurant.'
        ]);
        exit();
    }

    // Sanitize comment
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

    // Add review
    $result = add_review_ctr($restaurant_id, $customer_id, $rating, $comment);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your review! Your feedback has been submitted successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to submit review. Please try again.'
        ]);
    }

} catch (Exception $e) {
    error_log('Add review error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while submitting your review.'
    ]);
}

ob_end_flush();
exit();
?>
