<?php
// Start output buffering to prevent any unwanted output
ob_start();

// Error reporting - disable in production
error_reporting(0);
ini_set('display_errors', 0);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include core file with error handling
$core_file = 'settings/core.php';
if (file_exists($core_file)) {
    require_once $core_file;
} else {
    // Define fallback functions if core.php doesn't exist
    if (!function_exists('isLoggedIn')) {
        function isLoggedIn() {
            return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        }
    }
    
    if (!function_exists('isAdmin')) {
        function isAdmin() {
            return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
        }
    }
}

// Check if user is logged in and redirect if needed
if (function_exists('isLoggedIn') && isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Clean any output buffer content
ob_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taste of Africa - Discover Authentic African Cuisine</title>
    <meta name="description" content="Discover authentic African cuisine and connect with local restaurants. Join our community of food lovers and restaurant owners.">
    <meta name="keywords" content="African food, restaurants, cuisine, authentic, local dining">
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-gradient: linear-gradient(45deg, #D19C97, #b77a7a);
            --text-light: rgba(255,255,255,0.9);
            --text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            --card-backdrop: rgba(255,255,255,0.1);
            --border-glass: 1px solid rgba(255,255,255,0.2);
            --shadow-soft: 0 8px 32px rgba(0,0,0,0.1);
            --shadow-hover: 0 12px 40px rgba(0,0,0,0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: white;
        }

        /* Navigation Styles */
        .navbar-custom {
            background: var(--card-backdrop);
            backdrop-filter: blur(15px);
            border-bottom: var(--border-glass);
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
        }

        .navbar-custom.scrolled {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(20px);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.6rem;
            text-shadow: var(--text-shadow);
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 16px !important;
            border-radius: 20px;
            margin: 0 5px;
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .navbar-toggler {
            border: none;
            color: white;
            font-size: 1.2rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }

        .hero-content {
            z-index: 3;
            position: relative;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 800;
            color: white;
            text-shadow: var(--text-shadow);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: clamp(1.1rem, 2.5vw, 1.6rem);
            color: var(--text-light);
            margin-bottom: 2.5rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            max-width: 600px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        /* Button Styles */
        .btn-custom {
            background: var(--accent-gradient);
            border: none;
            color: white;
            padding: 18px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(209, 156, 151, 0.4);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(209, 156, 151, 0.6);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid rgba(255,255,255,0.8);
            color: white;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 18px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-outline-custom:hover {
            background: rgba(255,255,255,0.2);
            border-color: white;
            color: white;
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        /* Floating Elements Animation */
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
            pointer-events: none;
        }

        .floating-element {
            position: absolute;
            background: var(--card-backdrop);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            backdrop-filter: blur(5px);
        }

        .floating-element:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 8%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 55%;
            right: 8%;
            animation-delay: 2.5s;
        }

        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 75%;
            left: 15%;
            animation-delay: 5s;
        }

        .floating-element:nth-child(4) {
            width: 60px;
            height: 60px;
            top: 25%;
            right: 25%;
            animation-delay: 7s;
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg) scale(1); 
                opacity: 0.6;
            }
            33% { 
                transform: translateY(-25px) rotate(120deg) scale(1.1); 
                opacity: 0.8;
            }
            66% { 
                transform: translateY(-10px) rotate(240deg) scale(0.9); 
                opacity: 0.7;
            }
        }

        /* Hero Icon */
        .hero-icon {
            font-size: clamp(8rem, 15vw, 18rem);
            color: rgba(255,255,255,0.15);
            animation: pulse-glow 4s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { 
                opacity: 0.15; 
                transform: scale(1);
            }
            50% { 
                opacity: 0.25; 
                transform: scale(1.05);
            }
        }

        /* Features Section */
        .features-section {
            background: var(--card-backdrop);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            padding: 4rem 3rem;
            margin-top: 4rem;
            border: var(--border-glass);
            box-shadow: var(--shadow-soft);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
            text-shadow: var(--text-shadow);
        }

        .feature-item {
            text-align: center;
            padding: 2rem 1rem;
            transition: all 0.3s ease;
            border-radius: 15px;
        }

        .feature-item:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.05);
        }

        .feature-icon {
            font-size: 4rem;
            color: #D19C97;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(209, 156, 151, 0.3));
        }

        .feature-item:hover .feature-icon {
            transform: scale(1.1);
            filter: drop-shadow(0 6px 12px rgba(209, 156, 151, 0.5));
        }

        .feature-title {
            color: white;
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-shadow: var(--text-shadow);
        }

        .feature-text {
            color: var(--text-light);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                padding-top: 60px;
                text-align: center;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .btn-custom,
            .btn-outline-custom {
                padding: 15px 25px;
                font-size: 1rem;
                width: 100%;
                max-width: 280px;
                justify-content: center;
            }
            
            .features-section {
                padding: 3rem 2rem;
                margin-top: 2rem;
            }
            
            .floating-element {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .features-section {
                padding: 2rem 1.5rem;
            }
            
            .feature-item {
                padding: 1.5rem 0.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }

        /* Loading States */
        .btn-custom:active,
        .btn-outline-custom:active {
            transform: translateY(-1px);
        }

        /* Accessibility Improvements */
        .btn-custom:focus,
        .btn-outline-custom:focus,
        .nav-link:focus {
            outline: 2px solid rgba(255,255,255,0.8);
            outline-offset: 2px;
        }

        /* Smooth transitions */
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="#home" aria-label="Taste of Africa Home">
                <i class="fas fa-utensils me-2"></i>Taste of Africa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">
                            <i class="fas fa-star me-1"></i>Features
                        </a>
                    </li>
                    <?php if (!function_exists('isLoggedIn') || !isLoggedIn()): ?>
                        <!-- Show Register and Login for non-logged in users -->
                        <li class="nav-item">
                            <a class="nav-link" href="login/register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Show user-specific menu for logged in users -->
                        <?php if (function_exists('isAdmin') && isAdmin()): ?>
                            <!-- Admin menu -->
                            <li class="nav-item">
                                <a class="nav-link" href="admin/category.php">
                                    <i class="fas fa-cogs me-1"></i>Categories
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-7">
                    <div class="hero-content animate__animated animate__fadeInLeft">
                        <h1 class="hero-title">Welcome to Taste of Africa</h1>
                        <p class="hero-subtitle">
                            Discover authentic African cuisine and connect with local restaurants in your area. 
                            Experience the rich flavors and vibrant culture of Africa through our curated dining platform.
                        </p>
                        <div class="hero-buttons">
                            <?php if (!function_exists('isLoggedIn') || !isLoggedIn()): ?>
                                <a href="login/register.php" class="btn btn-custom animate__animated animate__pulse animate__infinite">
                                    <i class="fas fa-user-plus me-2"></i>Get Started
                                </a>
                                <a href="login/login.php" class="btn btn-outline-custom">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </a>
                            <?php else: ?>
                                <a href="dashboard.php" class="btn btn-custom animate__animated animate__pulse animate__infinite">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                                <?php if (function_exists('isAdmin') && isAdmin()): ?>
                                    <a href="admin/category.php" class="btn btn-outline-custom">
                                        <i class="fas fa-cogs me-2"></i>Manage Categories
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="text-center animate__animated animate__fadeInRight">
                        <i class="fas fa-utensils hero-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="features-section animate__animated animate__fadeInUp">
                <h2 class="section-title">Why Choose Taste of Africa?</h2>
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="feature-item">
                            <i class="fas fa-store feature-icon"></i>
                            <h3 class="feature-title">Restaurant Discovery</h3>
                            <p class="feature-text">
                                Find amazing African restaurants in your city and explore authentic flavors 
                                from across the continent.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="feature-item">
                            <i class="fas fa-users feature-icon"></i>
                            <h3 class="feature-title">Vibrant Community</h3>
                            <p class="feature-text">
                                Join a passionate community of food lovers and restaurant owners sharing 
                                their culinary experiences.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="feature-item">
                            <i class="fas fa-mobile-alt feature-icon"></i>
                            <h3 class="feature-title">Seamless Experience</h3>
                            <p class="feature-text">
                                Enjoy our user-friendly platform with simple registration and intuitive 
                                navigation to get you started quickly.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Smooth scrolling for navigation links
            $('a[href^="#"]').on('click', function(event) {
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 80
                    }, 1000, 'easeInOutCubic');
                }
            });

            // Navbar background change on scroll
            $(window).on('scroll', function() {
                if ($(window).scrollTop() > 100) {
                    $('#mainNavbar').addClass('scrolled');
                } else {
                    $('#mainNavbar').removeClass('scrolled');
                }
            });

            // Add loading states to buttons
            $('.btn-custom, .btn-outline-custom').on('click', function(e) {
                const $btn = $(this);
                const originalText = $btn.html();
                
                // Don't add loading state for anchor links
                if ($btn.attr('href').startsWith('#')) {
                    return;
                }
                
                $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
                $btn.addClass('disabled');
                
                // Reset after 2 seconds if still on page
                setTimeout(() => {
                    $btn.html(originalText);
                    $btn.removeClass('disabled');
                }, 2000);
            });

            // Add intersection observer for animations
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                        }
                    });
                }, { threshold: 0.1 });

                document.querySelectorAll('.feature-item').forEach(item => {
                    observer.observe(item);
                });
            }

            // Mobile menu auto-close
            $('.navbar-nav .nav-link').on('click', function() {
                if (window.innerWidth < 992) {
                    $('.navbar-collapse').collapse('hide');
                }
            });
        });

        // Add custom easing function
        $.easing.easeInOutCubic = function (x, t, b, c, d) {
            if ((t/=d/2) < 1) return c/2*t*t*t + b;
            return c/2*((t-=2)*t*t + 2) + b;
        };
    </script>
</body>
</html>