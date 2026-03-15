<?php
/**
 * api/verify_otp.php
 * POST { email, token }
 * Verifies OTP with Supabase, checks role + status,
 * then creates a server-side PHP session for the user.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

require_once __DIR__ . '/../config.php';

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
$body  = json_decode(file_get_contents('php://input'), true);
$email = trim($body['email'] ?? '');
$token = trim($body['token'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid email required.']);
    exit;
}
if (empty($token) || !preg_match('/^\d{6}$/', $token)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid 6-digit verification code is required.']);
    exit;
}

// ─── Verify OTP with Supabase ─────────────────────────────────────────────────
$payload = json_encode([
    'email' => $email,
    'token' => $token,
    'type'  => 'magiclink',
]);

$ch = curl_init(SUPABASE_URL . '/auth/v1/verify');
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
    $errMsg = $data['msg'] ?? $data['error_description'] ?? 'Invalid or expired OTP code.';
    echo json_encode(['error' => $errMsg]);
    exit;
}

$userId      = $data['user']['id'];
$accessToken = $data['access_token'] ?? '';

// ─── Fetch profile from Supabase using service role ───────────────────────────
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
    echo json_encode(['error' => 'Your account profile could not be found. Please contact support.']);
    exit;
}

$role   = strtolower($profile['role']   ?? '');
$status = strtolower($profile['status'] ?? 'pending');

// ─── Role check ───────────────────────────────────────────────────────────────
if (in_array($role, ['citizen', 'responder'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access Denied: This portal is for administrative personnel only.']);
    exit;
}

// ─── Status whitelist (ONLY "active" may proceed) ─────────────────────────────
if ($status !== 'active') {
    $messages = [
        'pending'  => 'Your account is still PENDING approval. Please wait for the Admin to verify your ID.',
        'suspended'=> 'Your account has been SUSPENDED. Please contact the Barangay Admin.',
        'rejected' => 'Your account registration was REJECTED. Please contact support.',
        'archived' => 'This account is no longer active.',
    ];
    http_response_code(403);
    echo json_encode(['error' => $messages[$status] ?? 'Your account is not active.']);
    exit;
}

// ─── All checks passed → create server session ────────────────────────────────
session_regenerate_id(true);    // Prevent session fixation attacks

$_SESSION['user_id']      = $userId;
$_SESSION['email']        = $email;
$_SESSION['role']         = $profile['role'];
$_SESSION['full_name']    = $profile['full_name'] ?? '';
$_SESSION['access_token'] = $accessToken;         // kept server-side only
$_SESSION['last_activity']= time();

echo json_encode([
    'success'   => true,
    'role'      => $profile['role'],
    'full_name' => $profile['full_name'] ?? '',
    'redirect'  => 'index.php',
]);
