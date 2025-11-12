<?php
// Start session and include core files
session_start();
require_once '../../settings/core.php';
require_once '../../controllers/restaurant_controller.php';

// Check if user is logged in and is a restaurant owner
if (!isLoggedIn()) {
    header('Location: ../../login/login.php');
    exit();
}

$user_role = $_SESSION['user_role'] ?? null;
if ($user_role != 2) {
    header('Location: ../all_product.php');
    exit();
}

$owner_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'] ?? 'Restaurant Owner';

// Get all reviews for owner's restaurants
$reviews = get_owner_reviews_ctr($owner_id);
$total_reviews = is_array($reviews) ? count($reviews) : 0;

// Calculate average rating
$total_rating = 0;
$rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

if ($reviews && is_array($reviews)) {
    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
        $rating_counts[$review['rating']]++;
    }
}

$average_rating = $total_reviews > 0 ? $total_rating / $total_reviews : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - Taste of Africa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-color: #D19C97;
            --text-dark: #333;
            --text-light: #6c757d;
            --border-color: #e9ecef;
            --hover-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
            min-height: 100vh;
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 60px 0 40px;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 10px;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 15px;
        }

        .btn-back {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .rating-overview {
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .rating-summary {
            text-align: center;
        }

        .rating-number {
            font-size: 4em;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 10px;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .rating-count {
            color: var(--text-light);
            font-size: 1.1em;
        }

        .rating-breakdown {
            flex: 1;
            min-width: 300px;
        }

        .rating-bar-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .rating-label {
            min-width: 60px;
            color: var(--text-dark);
            font-weight: 600;
        }

        .rating-bar {
            flex: 1;
            height: 10px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffc107 0%, #ff9800 100%);
            transition: width 0.3s ease;
        }

        .rating-bar-count {
            min-width: 40px;
            text-align: right;
            color: var(--text-light);
        }

        .reviews-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .reviews-header h3 {
            color: var(--text-dark);
            font-weight: 600;
            margin: 0;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            background: white;
            color: var(--text-dark);
            border: 2px solid var(--border-color);
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9em;
        }

        .filter-btn:hover, .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .review-card {
            padding: 25px;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s ease;
        }

        .review-card:hover {
            background: #f8f9fa;
        }

        .review-card:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
        }

        .reviewer-details {
            flex: 1;
        }

        .reviewer-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .review-rating {
            color: #ffc107;
            font-size: 0.9em;
        }

        .review-date {
            color: var(--text-light);
            font-size: 0.85em;
        }

        .restaurant-tag {
            background: #e7f3ff;
            color: #0066cc;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .review-comment {
            color: var(--text-dark);
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 100px;
            color: #e9ecef;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .rating-overview {
                flex-direction: column;
                gap: 30px;
            }

            .rating-number {
                font-size: 3em;
            }
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-comments"></i> Customer Reviews</h1>
            <p>See what customers are saying about your restaurants</p>
        </div>
    </div>

    <!-- Content -->
    <div class="content-container">
        <a href="../../dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <?php if ($total_reviews > 0): ?>
            <!-- Rating Overview -->
            <div class="stats-card">
                <div class="rating-overview">
                    <div class="rating-summary">
                        <div class="rating-number"><?php echo number_format($average_rating, 1); ?></div>
                        <div class="rating-stars">
                            <?php
                            $full_stars = floor($average_rating);
                            $half_star = ($average_rating - $full_stars) >= 0.5 ? 1 : 0;

                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '<i class="fas fa-star"></i>';
                            }
                            if ($half_star) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                                $full_stars++;
                            }
                            for ($i = $full_stars; $i < 5; $i++) {
                                echo '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        <div class="rating-count"><?php echo $total_reviews; ?> Reviews</div>
                    </div>

                    <div class="rating-breakdown">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <?php
                            $count = $rating_counts[$i];
                            $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                            ?>
                            <div class="rating-bar-item">
                                <span class="rating-label"><?php echo $i; ?> stars</span>
                                <div class="rating-bar">
                                    <div class="rating-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="rating-bar-count"><?php echo $count; ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="reviews-container">
                <div class="reviews-header">
                    <h3>All Reviews (<?php echo $total_reviews; ?>)</h3>
                    <div class="filter-buttons">
                        <button class="filter-btn active" onclick="filterReviews('all')">All</button>
                        <button class="filter-btn" onclick="filterReviews('5')">5 ⭐</button>
                        <button class="filter-btn" onclick="filterReviews('4')">4 ⭐</button>
                        <button class="filter-btn" onclick="filterReviews('3')">3 ⭐</button>
                        <button class="filter-btn" onclick="filterReviews('2')">2 ⭐</button>
                        <button class="filter-btn" onclick="filterReviews('1')">1 ⭐</button>
                    </div>
                </div>

                <div id="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card" data-rating="<?php echo $review['rating']; ?>">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <?php
                                        $name = $review['customer_name'] ?? 'A';
                                        echo strtoupper(substr($name, 0, 1));
                                        ?>
                                    </div>
                                    <div class="reviewer-details">
                                        <div class="reviewer-name">
                                            <?php echo htmlspecialchars($review['customer_name'] ?? 'Anonymous'); ?>
                                        </div>
                                        <div class="review-rating">
                                            <?php
                                            for ($i = 0; $i < $review['rating']; $i++) {
                                                echo '<i class="fas fa-star"></i>';
                                            }
                                            for ($i = $review['rating']; $i < 5; $i++) {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="restaurant-tag">
                                    <i class="fas fa-store"></i>
                                    <?php echo htmlspecialchars($review['restaurant_name'] ?? 'Restaurant'); ?>
                                </div>
                            </div>

                            <?php if (!empty($review['comment'])): ?>
                                <div class="review-comment">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="review-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('F d, Y', strtotime($review['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="stats-card">
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>No Reviews Yet</h3>
                    <p>You haven't received any reviews for your restaurants yet.</p>
                    <p>Keep providing excellent service and reviews will start coming in!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter reviews by rating
        function filterReviews(rating) {
            const reviews = document.querySelectorAll('.review-card');
            const buttons = document.querySelectorAll('.filter-btn');

            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Show/hide reviews
            reviews.forEach(review => {
                if (rating === 'all' || review.dataset.rating === rating) {
                    review.style.display = 'block';
                } else {
                    review.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
