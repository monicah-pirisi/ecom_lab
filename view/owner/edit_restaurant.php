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

// Get restaurant ID from URL
$restaurant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($restaurant_id <= 0) {
    header('Location: ../../dashboard.php');
    exit();
}

// Fetch restaurant data
$restaurant = get_restaurant_by_id_ctr($restaurant_id);

if (!$restaurant) {
    header('Location: ../../dashboard.php');
    exit();
}

// Verify the restaurant belongs to the logged-in owner
$owner_id = $_SESSION['user_id'];
if ($restaurant['owner_id'] != $owner_id) {
    header('Location: ../../dashboard.php');
    exit();
}

$customer_name = $_SESSION['user_name'] ?? 'Restaurant Owner';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Restaurant - Taste of Africa</title>
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

        .form-container {
            max-width: 900px;
            margin: 0 auto 50px;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .image-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 15px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .image-preview i {
            font-size: 60px;
            color: var(--border-color);
        }

        .current-image-label {
            font-size: 0.9em;
            color: var(--text-light);
            margin-top: 10px;
        }

        .btn-submit {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
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

        .required {
            color: #dc3545;
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
            <h1><i class="fas fa-edit"></i> Edit Restaurant</h1>
            <p>Update your restaurant information</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="container">
        <a href="../../dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="form-container">
            <form id="restaurant-form" enctype="multipart/form-data">
                <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurant['restaurant_id']); ?>">

                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="restaurant_name" class="form-label">
                                Restaurant Name <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="restaurant_name"
                                   name="restaurant_name" required value="<?php echo htmlspecialchars($restaurant['restaurant_name']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cuisine_type" class="form-label">Cuisine Type</label>
                            <input type="text" class="form-control" id="cuisine_type"
                                   name="cuisine_type" placeholder="e.g., African, Italian, Asian" value="<?php echo htmlspecialchars($restaurant['cuisine_type'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"
                                  placeholder="Tell customers about your restaurant..."><?php echo htmlspecialchars($restaurant['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h3>Contact Information</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                Phone Number <span class="required">*</span>
                            </label>
                            <input type="tel" class="form-control" id="phone"
                                   name="phone" required placeholder="+1234567890" value="<?php echo htmlspecialchars($restaurant['phone']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email"
                                   name="email" placeholder="restaurant@example.com" value="<?php echo htmlspecialchars($restaurant['email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-section">
                    <h3>Location</h3>
                    <div class="mb-3">
                        <label for="address" class="form-label">
                            Street Address <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="address"
                               name="address" required placeholder="123 Main Street" value="<?php echo htmlspecialchars($restaurant['address']); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">
                                City <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="city"
                                   name="city" required value="<?php echo htmlspecialchars($restaurant['city']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">
                                Country <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="country"
                                   name="country" required value="<?php echo htmlspecialchars($restaurant['country']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="form-section">
                    <h3>Operating Hours</h3>
                    <div class="mb-3">
                        <label for="opening_hours" class="form-label">Opening Hours</label>
                        <input type="text" class="form-control" id="opening_hours"
                               name="opening_hours"
                               placeholder="e.g., Mon-Fri: 9AM-10PM, Sat-Sun: 10AM-11PM" value="<?php echo htmlspecialchars($restaurant['opening_hours'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Restaurant Status -->
                <div class="form-section">
                    <h3>Restaurant Status</h3>
                    <div class="mb-3">
                        <label for="status" class="form-label">
                            Status <span class="required">*</span>
                        </label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Active" <?php echo ($restaurant['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($restaurant['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="Pending" <?php echo ($restaurant['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>
                </div>

                <!-- Restaurant Image -->
                <div class="form-section">
                    <h3>Restaurant Image</h3>
                    <?php if (!empty($restaurant['restaurant_image'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div class="image-preview" id="current-image">
                                <img src="../../<?php echo htmlspecialchars($restaurant['restaurant_image']); ?>" alt="Current Restaurant Image">
                            </div>
                            <p class="current-image-label">Leave blank to keep current image</p>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="restaurant_image" class="form-label">Upload New Image (Optional)</label>
                        <input type="file" class="form-control" id="restaurant_image"
                               name="restaurant_image" accept="image/*" onchange="previewImage(event)">
                        <small class="text-muted">Recommended: 800x600px, Max 5MB</small>
                    </div>
                    <div class="image-preview" id="image-preview" style="<?php echo !empty($restaurant['restaurant_image']) ? 'display:none;' : ''; ?>">
                        <i class="fas fa-image"></i>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-section">
                    <button type="submit" class="btn-submit" id="submit-btn">
                        <i class="fas fa-save"></i> Update Restaurant
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get CSRF token
        function getCSRFToken() {
            const tokenInput = document.querySelector('input[name="csrf_token"]');
            return tokenInput ? tokenInput.value : '';
        }

        // Preview image
        function previewImage(event) {
            const preview = document.getElementById('image-preview');
            const currentImage = document.getElementById('current-image');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.display = 'flex';
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    if (currentImage) {
                        currentImage.style.opacity = '0.5';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                preview.innerHTML = '<i class="fas fa-image"></i>';
                if (currentImage) {
                    currentImage.style.opacity = '1';
                }
            }
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

        // Handle form submission
        document.getElementById('restaurant-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating Restaurant...';

            try {
                const formData = new FormData(this);
                formData.append('csrf_token', getCSRFToken());

                const response = await fetch('../../actions/update_restaurant_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');

                    // Redirect to dashboard after 2 seconds
                    setTimeout(() => {
                        window.location.href = '../../dashboard.php';
                    }, 2000);
                } else {
                    showNotification(data.message || 'Failed to update restaurant', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>
