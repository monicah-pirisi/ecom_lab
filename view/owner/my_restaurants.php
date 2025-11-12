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

// Get owner's restaurants
$restaurants = get_restaurants_by_owner_ctr($owner_id);
$restaurant_count = is_array($restaurants) ? count($restaurants) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Restaurants - Taste of Africa</title>
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
            margin-bottom: 20px;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .btn-add {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-add:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: white;
        }

        .restaurant-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .restaurant-card:hover {
            box-shadow: var(--hover-shadow);
        }

        .restaurant-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }

        .restaurant-title {
            font-size: 1.4em;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .restaurant-info {
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .restaurant-info i {
            width: 20px;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .btn-edit {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-edit:hover {
            background: #5568d3;
            transform: translateY(-2px);
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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

        .stats-row {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9em;
        }

        .stat-item i {
            color: #667eea;
            margin-right: 5px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 9999;
            min-width: 300px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification-success {
            border-left: 5px solid #28a745;
        }

        .notification-error {
            border-left: 5px solid #dc3545;
        }
    </style>
</head>
<body>
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-store"></i> My Restaurants</h1>
            <p>Manage your restaurant listings</p>
        </div>
    </div>

    <!-- Content -->
    <div class="content-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="../../dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="add_restaurant.php" class="btn-add">
                <i class="fas fa-plus"></i> Add New Restaurant
            </a>
        </div>

        <?php if ($restaurants && $restaurant_count > 0): ?>
            <div class="mb-4">
                <h4>Total Restaurants: <?php echo $restaurant_count; ?></h4>
            </div>

            <?php foreach ($restaurants as $restaurant): ?>
                <div class="restaurant-card" data-restaurant-id="<?php echo $restaurant['restaurant_id']; ?>">
                    <div class="row align-items-center">
                        <!-- Restaurant Image -->
                        <div class="col-md-2 col-3">
                            <?php
                            $image_path = !empty($restaurant['restaurant_image']) ? '../../uploads/' . $restaurant['restaurant_image'] : '../../images/default-restaurant.png';
                            ?>
                            <img src="<?php echo htmlspecialchars($image_path); ?>"
                                 alt="<?php echo htmlspecialchars($restaurant['restaurant_name']); ?>"
                                 class="restaurant-image"
                                 onerror="this.onerror=null; this.src='../../images/default-restaurant.png'">
                        </div>

                        <!-- Restaurant Details -->
                        <div class="col-md-6 col-9">
                            <h3 class="restaurant-title">
                                <?php echo htmlspecialchars($restaurant['restaurant_name']); ?>
                            </h3>
                            <?php if (!empty($restaurant['cuisine_type'])): ?>
                                <p class="restaurant-info">
                                    <i class="fas fa-utensils"></i>
                                    <?php echo htmlspecialchars($restaurant['cuisine_type']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="restaurant-info">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($restaurant['city'] . ', ' . $restaurant['country']); ?>
                            </p>
                            <p class="restaurant-info">
                                <i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($restaurant['phone']); ?>
                            </p>
                            <?php if (!empty($restaurant['opening_hours'])): ?>
                                <p class="restaurant-info">
                                    <i class="fas fa-clock"></i>
                                    <?php echo htmlspecialchars($restaurant['opening_hours']); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Stats -->
                            <div class="stats-row">
                                <span class="stat-item">
                                    <i class="fas fa-calendar"></i>
                                    Added: <?php echo date('M d, Y', strtotime($restaurant['created_at'])); ?>
                                </span>
                                <span class="status-badge status-<?php echo strtolower($restaurant['status']); ?>">
                                    <?php echo htmlspecialchars($restaurant['status']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-md-4 col-12 mt-3 mt-md-0 text-md-end">
                            <a href="edit_restaurant.php?id=<?php echo $restaurant['restaurant_id']; ?>"
                               class="btn-edit me-2">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn-delete"
                                    onclick="deleteRestaurant(<?php echo $restaurant['restaurant_id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($restaurant['description'])): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p class="restaurant-info mb-0">
                                    <?php echo htmlspecialchars($restaurant['description']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-store"></i>
                <h3>No Restaurants Yet</h3>
                <p>You haven't added any restaurants yet. Start by adding your first restaurant!</p>
                <a href="add_restaurant.php" class="btn-add">
                    <i class="fas fa-plus"></i> Add Your First Restaurant
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get CSRF token
        function getCSRFToken() {
            const tokenInput = document.querySelector('input[name="csrf_token"]');
            return tokenInput ? tokenInput.value : '';
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 24px;">${type === 'success' ? '✓' : '✗'}</span>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Delete restaurant
        async function deleteRestaurant(restaurantId) {
            if (!confirm('Are you sure you want to delete this restaurant? This action cannot be undone.')) {
                return;
            }

            try {
                const formData = new URLSearchParams();
                formData.append('restaurant_id', restaurantId);
                formData.append('csrf_token', getCSRFToken());

                const response = await fetch('../../actions/delete_restaurant_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');

                    // Remove the card from DOM
                    const card = document.querySelector(`[data-restaurant-id="${restaurantId}"]`);
                    if (card) {
                        card.remove();
                    }

                    // Reload page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification(data.message || 'Failed to delete restaurant', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        }
    </script>
</body>
</html>
