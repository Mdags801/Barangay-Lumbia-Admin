<?php
/**
 * api/logout.php
 * POST → destroys the PHP session and redirects to login.php
 */
require_once __DIR__ . '/../config.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
if (session_status() === PHP_SESSION_NONE) session_start();

session_unset();
session_destroy();

// Clear the session cookies
setcookie(session_name(), '', time() - 3600, '/');
setcookie(AUTH_COOKIE_NAME, '', time() - 3600, '/');

header('Content-Type: application/json');
echo json_encode(['success' => true, 'redirect' => 'login.php']);
