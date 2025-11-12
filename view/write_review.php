<?php
// Start session and include core files
session_start();
require_once '../settings/core.php';
require_once '../controllers/restaurant_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Get restaurant ID from URL
$restaurant_id = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : 0;

if ($restaurant_id <= 0) {
    header('Location: all_product.php');
    exit();
}

// Get restaurant details
$restaurant = get_restaurant_by_id_ctr($restaurant_id);

if (!$restaurant) {
    header('Location: all_product.php');
    exit();
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'] ?? 'Customer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Review - <?php echo htmlspecialchars($restaurant['restaurant_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-color: #D19C97;
            --text-dark: #333;
            --text-light: #6c757d;
            --border-color: #e9ecef;
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

        .review-container {
            max-width: 800px;
            margin: 0 auto 50px;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .restaurant-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .restaurant-image {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
        }

        .restaurant-details h3 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .restaurant-details p {
            color: var(--text-light);
            margin: 0;
        }

        .rating-select {
            margin: 30px 0;
        }

        .rating-select h4 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .star-rating {
            display: flex;
            gap: 10px;
            font-size: 2.5em;
        }

        .star-rating i {
            cursor: pointer;
            color: #ddd;
            transition: all 0.3s ease;
        }

        .star-rating i:hover,
        .star-rating i.active {
            color: #ffc107;
            transform: scale(1.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        textarea.form-control {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            min-height: 150px;
            resize: vertical;
        }

        textarea.form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-submit {
            background: linear-gradient(45deg, #D19C97, #b77a7a);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(209, 156, 151, 0.4);
            color: white;
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
    </style>
</head>
<body>
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-star"></i> Write a Review</h1>
            <p>Share your experience with others</p>
        </div>
    </div>

    <!-- Review Form -->
    <div class="container">
        <a href="javascript:history.back()" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="review-container">
            <!-- Restaurant Info -->
            <div class="restaurant-info">
                <?php
                $image_path = !empty($restaurant['restaurant_image'])
                    ? '../' . $restaurant['restaurant_image']
                    : '../images/default-restaurant.png';
                ?>
                <img src="<?php echo htmlspecialchars($image_path); ?>"
                     alt="<?php echo htmlspecialchars($restaurant['restaurant_name']); ?>"
                     class="restaurant-image"
                     onerror="this.onerror=null; this.src='../images/default-restaurant.png'">
                <div class="restaurant-details">
                    <h3><?php echo htmlspecialchars($restaurant['restaurant_name']); ?></h3>
                    <p>
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($restaurant['city'] . ', ' . $restaurant['country']); ?>
                    </p>
                    <?php if (!empty($restaurant['cuisine_type'])): ?>
                        <p><i class="fas fa-utensils"></i> <?php echo htmlspecialchars($restaurant['cuisine_type']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Review Form -->
            <form id="review-form">
                <input type="hidden" name="restaurant_id" value="<?php echo $restaurant_id; ?>">

                <!-- Star Rating -->
                <div class="rating-select">
                    <h4>How would you rate this restaurant?</h4>
                    <div class="star-rating" id="star-rating">
                        <i class="far fa-star" data-rating="1"></i>
                        <i class="far fa-star" data-rating="2"></i>
                        <i class="far fa-star" data-rating="3"></i>
                        <i class="far fa-star" data-rating="4"></i>
                        <i class="far fa-star" data-rating="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="0">
                    <small class="text-muted" id="rating-text">Click on the stars to rate</small>
                </div>

                <!-- Review Comment -->
                <div class="mb-4">
                    <label for="comment" class="form-label">Your Review (Optional)</label>
                    <textarea class="form-control" id="comment" name="comment"
                              placeholder="Share your thoughts about your experience..."></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Get CSRF token
        function getCSRFToken() {
            const tokenInput = document.querySelector('input[name="csrf_token"]');
            return tokenInput ? tokenInput.value : '';
        }

        // Star rating functionality
        const stars = document.querySelectorAll('.star-rating i');
        const ratingInput = document.getElementById('rating');
        const ratingText = document.getElementById('rating-text');

        const ratingDescriptions = {
            1: 'Poor',
            2: 'Fair',
            3: 'Good',
            4: 'Very Good',
            5: 'Excellent'
        };

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                ratingInput.value = rating;

                // Update stars
                stars.forEach(s => {
                    const starRating = parseInt(s.getAttribute('data-rating'));
                    if (starRating <= rating) {
                        s.classList.remove('far');
                        s.classList.add('fas', 'active');
                    } else {
                        s.classList.remove('fas', 'active');
                        s.classList.add('far');
                    }
                });

                // Update text
                ratingText.textContent = ratingDescriptions[rating];
                ratingText.style.color = '#ffc107';
                ratingText.style.fontWeight = '600';
            });

            // Hover effect
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                stars.forEach(s => {
                    const starRating = parseInt(s.getAttribute('data-rating'));
                    if (starRating <= rating) {
                        s.style.color = '#ffc107';
                    }
                });
            });

            star.addEventListener('mouseleave', function() {
                const currentRating = parseInt(ratingInput.value);
                stars.forEach(s => {
                    const starRating = parseInt(s.getAttribute('data-rating'));
                    if (starRating > currentRating) {
                        s.style.color = '#ddd';
                    } else {
                        s.style.color = '#ffc107';
                    }
                });
            });
        });

        // Form submission
        document.getElementById('review-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const rating = parseInt(ratingInput.value);
            if (rating === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Rating Required',
                    text: 'Please select a star rating before submitting your review.'
                });
                return;
            }

            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            try {
                const formData = new FormData(this);
                formData.append('csrf_token', getCSRFToken());

                const response = await fetch('../actions/add_review_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Review Submitted!',
                        text: data.message,
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        window.history.back();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Submission Failed',
                        text: data.message || 'Failed to submit review. Please try again.'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again later.'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>
