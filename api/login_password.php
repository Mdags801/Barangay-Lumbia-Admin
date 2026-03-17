<?php
/**
 * api/login_password.php
 * POST { email, password }  → RESTful login endpoint.
 *
 * Security:
 *  - Server-side input validation (email format, non-empty password)
 *  - Credentials verified by Supabase Auth (bcrypt hashing handled by Supabase)
 *  - Role-based access control (ALLOWED_ROLES whitelist)
 *  - Account status check (active only)
 *  - session_regenerate_id() prevents session fixation
 *  - Anti-clickjack / code-attack headers via apply_security_headers()
 */

require_once __DIR__ . '/../config.php';
apply_security_headers();
header('Content-Type: application/json');

// config.php already loaded above

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => SESSION_COOKIE_SECURE,
    'httponly' => true,
    'samesite' => SESSION_COOKIE_SAMESITE,
]);
if (session_status() === PHP_SESSION_NONE) session_start();

// ─── Input validation ─────────────────────────────────────────────────────────
$body     = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email'] ?? '');
$password = $body['password'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid email address is required.']);
    exit;
}
if (empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Password is required.']);
    exit;
}

// ─── Sign in with Supabase ────────────────────────────────────────────────────
$payload = json_encode([
    'email'    => $email,
    'password' => $password
]);

$ch = curl_init(SUPABASE_URL . '/auth/v1/token?grant_type=password');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'apikey: '        . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . SUPABASE_ANON_KEY,
    ],
]);

$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($status >= 400 || empty($data['user'])) {
    http_response_code(401);
    $errMsg = $data['error_description'] ?? $data['msg'] ?? 'Invalid email or password.';
    echo json_encode(['error' => $errMsg]);
    exit;
}

$userId      = $data['user']['id'];
$accessToken = $data['access_token'] ?? '';

// ─── Fetch profile from Supabase ─────────────────────────────────────────────
$ch = curl_init(SUPABASE_URL . '/rest/v1/profiles?id=eq.' . urlencode($userId) . '&select=role,status,full_name');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'apikey: '        . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json',
    ],
]);

$profileResp = curl_exec($ch);
$profileCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$profiles = json_decode($profileResp, true);
$profile  = $profiles[0] ?? null;

if (!$profile) {
    http_response_code(403);
    echo json_encode(['error' => 'Profile not found. Please contact support.']);
    exit;
}

$role   = strtolower($profile['role']   ?? '');
$status = strtolower($profile['status'] ?? 'pending');

// ─── Role check ───────────────────────────────────────────────────────────────
if (!in_array($role, array_map('strtolower', ALLOWED_ROLES))) {
    http_response_code(403);
    echo json_encode(['error' => 'Access Denied: Administrative personnel only.']);
    exit;
}

// ─── Status check ─────────────────────────────────────────────────────────────
if ($status !== 'active') {
    http_response_code(403);
    echo json_encode(['error' => 'Account is not active (Status: ' . ucfirst($status) . ').']);
    exit;
}

// ─── Create PHP Session ───────────────────────────────────────────────────────
session_regenerate_id(true);
$_SESSION['user_id']      = $userId;
$_SESSION['email']        = $email;
$_SESSION['role']         = $profile['role'];
$_SESSION['full_name']    = $profile['full_name'] ?? '';
$_SESSION['access_token'] = $accessToken;
$_SESSION['last_activity']= time();
// Store a hash of the client IP for session-hijack detection
$_SESSION['ip_hash']      = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') . CSRF_SECRET);

// Set a stateless auth cookie for Vercel/Serverless support
setcookie(AUTH_COOKIE_NAME, $accessToken, [
    'expires' => time() + SESSION_LIFETIME,
    'path' => '/',
    'secure' => SESSION_COOKIE_SECURE,
    'httponly' => true,
    'samesite' => 'Lax'
]);

echo json_encode([
    'success'   => true,
    'role'      => $profile['role'],
    'full_name' => $profile['full_name'] ?? '',
    'redirect'  => 'index.php',
]);
