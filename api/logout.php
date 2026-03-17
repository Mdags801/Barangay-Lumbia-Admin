<?php
/**
 * api/logout.php
 * POST → Destroys the PHP session and clears auth cookies.
 *
 * Security:
 *  - Calls apply_security_headers() for Clickjack/XSS defence
 *  - Clears both the PHP session cookie and the sb_portal_token JWT cookie
 *  - Uses SameSite=Strict on the session cookie clear for CSRF safety
 */
require_once __DIR__ . '/../config.php';
apply_security_headers();
header('Content-Type: application/json');

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
if (session_status() === PHP_SESSION_NONE) session_start();

session_unset();
session_destroy();

// Clear the session cookies (expire in the past)
setcookie(session_name(), '', time() - 3600, '/');
setcookie(AUTH_COOKIE_NAME, '', time() - 3600, '/');

echo json_encode(['success' => true, 'redirect' => 'login.php']);

