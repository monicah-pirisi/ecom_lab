<?php
ob_start();

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$httpOnly = true;

if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => $httpOnly,
        'samesite' => 'Lax'
    ]);
} else {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 1;
}

function regenerateSession() {
    try {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Session regeneration failed: " . $e->getMessage());
        return false;
    }
}

function checkSessionTimeout($idleTimeout = 1800, $absoluteTimeout = 28800) {
    try {
        $currentTime = time();

        if (!isset($_SESSION['session_start_time'])) {
            $_SESSION['session_start_time'] = $currentTime;
        }

        if (!isset($_SESSION['last_activity_time'])) {
            $_SESSION['last_activity_time'] = $currentTime;
        }

        if (($currentTime - $_SESSION['last_activity_time']) > $idleTimeout) {
            session_unset();
            session_destroy();
            return false;
        }

        if (($currentTime - $_SESSION['session_start_time']) > $absoluteTimeout) {
            session_unset();
            session_destroy();
            return false;
        }

        $_SESSION['last_activity_time'] = $currentTime;

        if (!isset($_SESSION['last_regeneration']) ||
            ($currentTime - $_SESSION['last_regeneration']) > 1800) {
            regenerateSession();
        }

        return true;
    } catch (Exception $e) {
        error_log("Session timeout check failed: " . $e->getMessage());
        return false;
    }
}

function generateCSRFToken() {
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(32));
        } else {
            $token = bin2hex(openssl_random_pseudo_bytes(32));
        }

        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    } catch (Exception $e) {
        error_log("CSRF token generation failed: " . $e->getMessage());
        $token = md5(uniqid(mt_rand(), true));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        return $token;
    }
}

function validateCSRFToken($token, $tokenLifetime = 3600) {
    try {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        if ((time() - $_SESSION['csrf_token_time']) > $tokenLifetime) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }

        if (function_exists('hash_equals')) {
            return hash_equals($_SESSION['csrf_token'], $token);
        } else {
            return $_SESSION['csrf_token'] === $token;
        }
    } catch (Exception $e) {
        error_log("CSRF token validation failed: " . $e->getMessage());
        return false;
    }
}

function getCSRFToken() {
    if (!isset($_SESSION['csrf_token']) ||
        !isset($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time']) > 3600) {
        return generateCSRFToken();
    }
    return $_SESSION['csrf_token'];
}

function csrfTokenField() {
    $token = getCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

if (isLoggedIn()) {
    if (!checkSessionTimeout()) {
        header('Location: ../login/login.php?timeout=1');
        exit();
    }
}
?>