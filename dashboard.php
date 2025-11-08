<?php
// Start output buffering to prevent any unwanted output
ob_start();

// Error reporting - remove in production
error_reporting(0);
ini_set('display_errors', 0);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login/login.php');
    exit();
}

// Get user information from session with defaults
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Not available';
$user_role = isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : 0;
$user_country = isset($_SESSION['user_country']) ? $_SESSION['user_country'] : 'Not specified';
$user_city = isset($_SESSION['user_city']) ? $_SESSION['user_city'] : 'Not specified';
$user_phone = isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : 'Not provided';

// Determine user type
$user_type = ($user_role == 1) ? 'Administrator' : 'Restaurant Owner';
$welcome_message = ($user_role == 1) ? 'Welcome to your admin dashboard!' : 'Welcome to your restaurant owner dashboard!';

// Clean any output buffer content
ob_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Taste of Africa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 20px;
            padding: 8px 16px !important;
            margin: 0 5px;
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .dashboard-container {
            padding-top: 100px;
            min-height: 100vh;
        }

        .welcome-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .welcome-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .welcome-subtitle {
            color: rgba(255,255,255,0.8);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .user-info-card {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-item:hover {
            background: rgba(209, 156, 151, 0.05);
            border-radius: 8px;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .info-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(45deg, #D19C97, #b77a7a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(209, 156, 151, 0.3);
        }

        .info-label {
            font-weight: 600;
            color: #333;
            margin-right: 0.5rem;
            min-width: 120px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #555;
            flex: 1;
            font-size: 16px;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-custom {
            background: linear-gradient(45deg, #D19C97, #b77a7a);
            border: none;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(209, 156, 151, 0.4);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(209, 156, 151, 0.6);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid rgba(255,255,255,0.8);
            color: white;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-outline-custom:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255,255,255,0.3);
        }

        .btn-danger-custom {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-danger-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.6);
            color: white;
        }

        .role-badge {
            background: linear-gradient(45deg, #D19C97, #b77a7a);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(209, 156, 151, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stats-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem 1.5rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            background: rgba(255,255,255,0.15);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .stats-label {
            color: rgba(255,255,255,0.9);
            font-size: 0.95rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            color: white;
            margin-bottom: 1.5rem;
            font-weight: 400;
            display: flex;
            align-items: center;
            font-size: 1.5rem;
        }

        .section-title i {
            margin-right: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .welcome-title {
                font-size: 2rem;
            }
            
            .dashboard-container {
                padding-top: 80px;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .btn-custom,
            .btn-outline-custom,
            .btn-danger-custom {
                flex: 1;
                min-width: 200px;
                justify-content: center;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .welcome-card,
            .user-info-card {
                padding: 1.5rem;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }
            
            .info-icon {
                margin-bottom: 0.5rem;
            }
            
            .info-label,
            .info-value {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-utensils me-2"></i>Taste of Africa
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="color: white;">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <?php if ($user_role == 1): ?>
                        <!-- Admin menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="admin/category.php">
                                <i class="fas fa-cogs me-1"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-users me-1"></i>Users
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login/logout.php" onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-card animate__animated animate__fadeInDown">
            <h1 class="welcome-title">
                <i class="fas fa-user-circle me-3"></i>Hello, <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>!
            </h1>
            <p class="welcome-subtitle"><?php echo htmlspecialchars($welcome_message, ENT_QUOTES, 'UTF-8'); ?></p>
            <span class="role-badge">
                <i class="fas fa-<?php echo ($user_role == 1) ? 'shield-alt' : 'store'; ?> me-2"></i>
                <?php echo htmlspecialchars($user_type, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>

        <div class="row">
            <!-- User Information -->
            <div class="col-lg-8">
                <div class="user-info-card animate__animated animate__fadeInLeft">
                    <h3 class="section-title" style="color: #333; margin-bottom: 2rem;">
                        <i class="fas fa-info-circle" style="color: #D19C97;"></i>
                        Your Information
                    </h3>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_phone, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <span class="info-label">Country:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_country, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span class="info-label">City:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_city, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-<?php echo ($user_role == 1) ? 'shield-alt' : 'store'; ?>"></i>
                        </div>
                        <span class="info-label">Role:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_type, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="col-lg-4">
                <div class="stats-card animate__animated animate__fadeInRight">
                    <div class="stats-number">1</div>
                    <div class="stats-label">Active Account</div>
                </div>

                <div class="stats-card animate__animated animate__fadeInRight" style="animation-delay: 0.2s;">
                    <div class="stats-number">0</div>
                    <div class="stats-label">Orders Placed</div>
                </div>

                <div class="stats-card animate__animated animate__fadeInRight" style="animation-delay: 0.4s;">
                    <div class="stats-number">0</div>
                    <div class="stats-label">Reviews Written</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="welcome-card animate__animated animate__fadeInUp">
                    <h3 class="section-title">
                        <i class="fas fa-rocket"></i>Quick Actions
                    </h3>
                    <div class="action-buttons">
                        <?php if ($user_role == 1): ?>
                            <!-- Admin Actions -->
                            <a href="admin/category.php" class="btn btn-custom">
                                <i class="fas fa-cogs me-2"></i>Manage Categories
                            </a>
                            <a href="#" class="btn btn-outline-custom" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                            <a href="#" class="btn btn-outline-custom" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-chart-bar me-2"></i>Analytics
                            </a>
                            <a href="#" class="btn btn-outline-custom" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        <?php else: ?>
                            <!-- Restaurant Owner Actions -->
                            <a href="#" class="btn btn-custom" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-plus me-2"></i>Add Restaurant
                            </a>
                            <a href="#" class="btn btn-outline-custom" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            <a href="#" class="btn btn-outline-custom" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-chart-bar me-2"></i>Analytics
                            </a>
                            <a href="#" class="btn btn-outline-custom" onclick="alert('Feature coming soon!')">
                                <i class="fas fa-comments me-2"></i>Reviews
                            </a>
                        <?php endif; ?>
                        
                        <a href="login/logout.php" class="btn btn-danger-custom" onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Add interactive animations and functionality
        $(document).ready(function() {
            // Animate stats numbers
            $('.stats-number').each(function() {
                const $this = $(this);
                const countTo = parseInt($this.text());
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });

            // Add loading states to buttons
            $('.btn').on('click', function(e) {
                if ($(this).attr('href') === '#') {
                    e.preventDefault();
                }
            });

            // Smooth scrolling for internal links
            $('a[href^="#"]').on('click', function(event) {
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                }
            });

            // Add hover effects to cards
            $('.stats-card, .info-item').on('mouseenter', function() {
                $(this).addClass('animate__pulse');
            }).on('mouseleave', function() {
                $(this).removeClass('animate__pulse');
            });
        });

        // Handle navigation collapse on mobile
        $(document).on('click', '.navbar-nav .nav-link', function() {
            $('.navbar-collapse').collapse('hide');
        });
    </script>
</body>
</html>