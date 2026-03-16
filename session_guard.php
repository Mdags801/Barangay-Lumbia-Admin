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

// Not logged in → Try to reconstruct from stateless cookie (Vercel Support)
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    $cookieToken = $_COOKIE[AUTH_COOKIE_NAME] ?? '';
    if (!empty($cookieToken)) {
        // Verify token with Supabase
        $ch = curl_init(SUPABASE_URL . '/auth/v1/user');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . $cookieToken,
            ]
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200) {
            $userData = json_decode($resp, true);
            $userId = $userData['id'] ?? '';
            
            // Re-fetch roles/profile
            $ch = curl_init(SUPABASE_URL . '/rest/v1/profiles?id=eq.' . $userId . '&select=role,full_name,status');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'apikey: ' . SUPABASE_ANON_KEY,
                    'Authorization: Bearer ' . $cookieToken,
                ]
            ]);
            $profResp = curl_exec($ch);
            curl_close($ch);
            $profiles = json_decode($profResp, true);
            $profile = $profiles[0] ?? null;

            if ($profile && strtolower($profile['status'] ?? '') === 'active') {
                $_SESSION['user_id']      = $userId;
                $_SESSION['email']        = $userData['email'] ?? '';
                $_SESSION['role']         = $profile['role'];
                $_SESSION['full_name']    = $profile['full_name'] ?? '';
                $_SESSION['access_token'] = $cookieToken;
                $_SESSION['last_activity']= time();
            } else {
                header('Location: login.php?error=access_denied');
                exit;
            }
        }
    }
}

// Still not logged in after check? → kick to login
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
