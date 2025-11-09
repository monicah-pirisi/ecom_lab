// Settings/core.php
<?php

// for header redirection
ob_start();

// Secure session configuration and single start
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
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
	// Best-effort for older PHP versions
	ini_set('session.cookie_samesite', 'Lax');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Check if the user is logged in
function isLoggedIn() {
	return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if the logged-in user has admin role
function isAdmin() {
	return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 1;
}

//  SESSION SECURITY FUNCTIONS 

/**
 * Regenerate session ID to prevent session fixation attacks
 * Call this after successful login
 *
 * @return bool True on success, false on failure
 */
function regenerateSession() {
	try {
		if (session_status() === PHP_SESSION_ACTIVE) {
			// Delete old session file but keep session data
			session_regenerate_id(true);

			// Update session timestamp
			$_SESSION['last_regeneration'] = time();

			return true;
		}
		return false;
	} catch (Exception $e) {
		return false;
	}
}

/**
 * Check session timeout
 * Validates both idle timeout and absolute timeout
 *
 * @param int $idleTimeout Idle timeout in seconds (default: 1800 = 30 minutes)
 * @param int $absoluteTimeout Absolute timeout in seconds (default: 28800 = 8 hours)
 * @return bool True if session is valid, false if timed out
 */
function checkSessionTimeout($idleTimeout = 1800, $absoluteTimeout = 28800) {
	try {
		$currentTime = time();

		// Check if session start time exists, if not set it
		if (!isset($_SESSION['session_start_time'])) {
			$_SESSION['session_start_time'] = $currentTime;
		}

		// Check if last activity time exists, if not set it
		if (!isset($_SESSION['last_activity_time'])) {
			$_SESSION['last_activity_time'] = $currentTime;
		}

		// Check idle timeout (no activity for X seconds)
		if (($currentTime - $_SESSION['last_activity_time']) > $idleTimeout) {
			session_unset();
			session_destroy();
			return false;
		}

		// Check absolute timeout (session too old regardless of activity)
		if (($currentTime - $_SESSION['session_start_time']) > $absoluteTimeout) {
			session_unset();
			session_destroy();
			return false;
		}

		// Update last activity time
		$_SESSION['last_activity_time'] = $currentTime;

		// Regenerate session ID periodically (every 30 minutes)
		if (!isset($_SESSION['last_regeneration']) ||
			($currentTime - $_SESSION['last_regeneration']) > 1800) {
			regenerateSession();
		}

		return true;
	} catch (Exception $e) {
		@error_log("Session timeout check failed: " . $e->getMessage());
		return false;
	}
}

/**
 * Generate CSRF token for form protection
 * Creates a unique token and stores it in session
 *
 * @return string The generated CSRF token
 */
function generateCSRFToken() {
	try {
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		// Generate cryptographically secure random token
		if (function_exists('random_bytes')) {
			$token = bin2hex(random_bytes(32));
		} else {
			// Fallback for older PHP versions
			$token = bin2hex(openssl_random_pseudo_bytes(32));
		}

		// Store token in session with timestamp
		$_SESSION['csrf_token'] = $token;
		$_SESSION['csrf_token_time'] = time();

		return $token;
	} catch (Exception $e) {
		@error_log("CSRF token generation failed: " . $e->getMessage());
		// Fallback to a less secure but still usable token
		$token = md5(uniqid(mt_rand(), true));
		$_SESSION['csrf_token'] = $token;
		$_SESSION['csrf_token_time'] = time();
		return $token;
	}
}

/**
 * Validate CSRF token from form submission
 * Compares submitted token with session token
 *
 * @param string $token The token to validate
 * @param int $tokenLifetime Token lifetime in seconds (default: 3600 = 1 hour)
 * @return bool True if token is valid, false otherwise
 */
function validateCSRFToken($token, $tokenLifetime = 3600) {
	try {
		// Check if token exists in session
		if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
			return false;
		}

		// Check token age
		if ((time() - $_SESSION['csrf_token_time']) > $tokenLifetime) {
			unset($_SESSION['csrf_token']);
			unset($_SESSION['csrf_token_time']);
			return false;
		}

		// Use timing-safe comparison to prevent timing attacks
		if (function_exists('hash_equals')) {
			return hash_equals($_SESSION['csrf_token'], $token);
		} else {
			// Fallback for older PHP versions
			return $_SESSION['csrf_token'] === $token;
		}
	} catch (Exception $e) {
		@error_log("CSRF token validation failed: " . $e->getMessage());
		return false;
	}
}

/**
 * Get or create CSRF token
 * Returns existing token or generates new one if doesn't exist
 *
 * @return string The CSRF token
 */
function getCSRFToken() {
	if (!isset($_SESSION['csrf_token']) ||
		!isset($_SESSION['csrf_token_time']) ||
		(time() - $_SESSION['csrf_token_time']) > 3600) {
		return generateCSRFToken();
	}
	return $_SESSION['csrf_token'];
}

/**
 * Output CSRF token as hidden input field
 * Convenience function for forms
 *
 * @return void
 */
function csrfTokenField() {
	$token = getCSRFToken();
	echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

//  AUTO-INITIALIZE SESSION SECURITY ====================

// Initialize session timeout check on every page load
if (isLoggedIn()) {
	if (!checkSessionTimeout()) {
		// Session timed out, redirect to login
		header('Location: ../login/login.php?timeout=1');
		exit();
	}
}

?>