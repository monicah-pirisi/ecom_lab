<?php
// Start session and include core files
session_start();
require_once '../../settings/core.php';
require_once '../../settings/db_class.php';

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

$user_id = $_SESSION['user_id'];

// Get user information
$db = new db_connection();
$sql = "SELECT * FROM customer WHERE customer_id = '$user_id'";
$user = $db->db_fetch_one($sql);

if (!$user) {
    die("User not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Taste of Africa</title>
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
            max-width: 800px;
            margin: 0 auto 50px;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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

        .profile-image-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .profile-image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .profile-image-default {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            margin: 0 auto 20px;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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

        .btn-change-password {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 15px;
        }

        .btn-change-password:hover {
            background: #667eea;
            color: white;
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

        .change-password-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }

        .change-password-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-user-edit"></i> Edit Profile</h1>
            <p>Update your account information</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="container">
        <a href="../../dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="form-container">
            <!-- Profile Image Section -->
            <div class="profile-image-section">
                <?php if (!empty($user['customer_image'])): ?>
                    <img src="../../<?php echo htmlspecialchars($user['customer_image']); ?>"
                         alt="Profile"
                         class="profile-image-preview"
                         id="image-preview"
                         onerror="this.style.display='none'; document.getElementById('default-avatar').style.display='flex'">
                    <div class="profile-image-default" id="default-avatar" style="display: none;">
                        <i class="fas fa-user"></i>
                    </div>
                <?php else: ?>
                    <div class="profile-image-default" id="default-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <img src="" alt="Profile" class="profile-image-preview" id="image-preview" style="display: none;">
                <?php endif; ?>
                <input type="file" class="form-control" id="profile_image" name="profile_image"
                       accept="image/*" onchange="previewImage(event)">
                <small class="text-muted">Max 5MB. JPG, PNG, GIF</small>
            </div>

            <form id="profile-form">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">
                            Full Name <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="customer_name"
                               name="customer_name" required
                               value="<?php echo htmlspecialchars($user['customer_name']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="customer_email" class="form-label">
                            Email Address <span class="required">*</span>
                        </label>
                        <input type="email" class="form-control" id="customer_email"
                               name="customer_email" required
                               value="<?php echo htmlspecialchars($user['customer_email']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="customer_contact" class="form-label">
                            Phone Number <span class="required">*</span>
                        </label>
                        <input type="tel" class="form-control" id="customer_contact"
                               name="customer_contact" required
                               value="<?php echo htmlspecialchars($user['customer_contact']); ?>">
                    </div>
                </div>

                <!-- Location Information -->
                <div class="form-section">
                    <h3>Location</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_country" class="form-label">
                                Country <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="customer_country"
                                   name="customer_country" required
                                   value="<?php echo htmlspecialchars($user['customer_country']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customer_city" class="form-label">
                                City <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="customer_city"
                                   name="customer_city" required
                                   value="<?php echo htmlspecialchars($user['customer_city']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="submit-btn">
                    <i class="fas fa-save"></i> Update Profile
                </button>

                <button type="button" class="btn-change-password" onclick="toggleChangePassword()">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>

            <!-- Change Password Section -->
            <div class="change-password-section" id="change-password-section">
                <h4 class="mb-3">Change Password</h4>
                <form id="password-form">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password"
                               name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password"
                               name="new_password" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password"
                               name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
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
            const defaultAvatar = document.getElementById('default-avatar');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    defaultAvatar.style.display = 'none';
                }
                reader.readAsDataURL(file);
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

        // Toggle change password section
        function toggleChangePassword() {
            const section = document.getElementById('change-password-section');
            section.classList.toggle('active');
        }

        // Handle profile form submission
        document.getElementById('profile-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            try {
                const formData = new FormData();
                formData.append('customer_name', document.getElementById('customer_name').value);
                formData.append('customer_email', document.getElementById('customer_email').value);
                formData.append('customer_contact', document.getElementById('customer_contact').value);
                formData.append('customer_country', document.getElementById('customer_country').value);
                formData.append('customer_city', document.getElementById('customer_city').value);
                formData.append('csrf_token', getCSRFToken());

                // Add profile image if selected
                const imageFile = document.getElementById('profile_image').files[0];
                if (imageFile) {
                    formData.append('profile_image', imageFile);
                }

                const response = await fetch('../../actions/update_profile_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');

                    // Update session name if changed
                    if (data.name) {
                        // Could update displayed name in header if exists
                    }
                } else {
                    showNotification(data.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Handle password form submission
        document.getElementById('password-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                showNotification('Passwords do not match', 'error');
                return;
            }

            try {
                const formData = new URLSearchParams();
                formData.append('current_password', document.getElementById('current_password').value);
                formData.append('new_password', newPassword);
                formData.append('csrf_token', getCSRFToken());

                const response = await fetch('../../actions/change_password_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');
                    document.getElementById('password-form').reset();
                    toggleChangePassword();
                } else {
                    showNotification(data.message || 'Failed to change password', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    </script>
</body>
</html>
