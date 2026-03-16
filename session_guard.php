<?php
/**
 * session_guard.php
 * Include this at the TOP of every protected page.
 * Starts and validates the PHP session. Kicks unauthenticated users to login.php.
 */

require_once __DIR__ . '/config.php';

// Harden session cookie settings
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => SESSION_COOKIE_SECURE,
    'httponly' => true,                     // JS cannot read the cookie
    'samesite' => SESSION_COOKIE_SAMESITE,
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session expiry check
if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive > SESSION_LIFETIME) {
        session_unset();
        session_destroy();
        header('Location: login.php?error=session_expired');
        exit;
    }
}
$_SESSION['last_activity'] = time();

// Not logged in → kick to login
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: login.php?error=not_authenticated');
    exit;
}

// Wrong role → kick with access denied
$role = strtolower($_SESSION['role']);
if (!in_array($role, array_map('strtolower', ALLOWED_ROLES))) {
    session_unset();
    session_destroy();
    header('Location: login.php?error=access_denied');
    exit;
}
