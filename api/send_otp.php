<?php
/**
 * api/send_otp.php
 * POST { email }  → Calls Supabase Magic-Link/OTP endpoint.
 * Rate-limited: max 3 OTP sends per 5 minutes per IP.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ─── Rate limiting (session-based) ───────────────────────────────────────────
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
if (session_status() === PHP_SESSION_NONE) session_start();

$ip  = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$key = 'otp_rate_' . md5($ip);

if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = ['count' => 0, 'window_start' => time()];
}

$window = 5 * 60; // 5-minute window
if (time() - $_SESSION[$key]['window_start'] > $window) {
    $_SESSION[$key] = ['count' => 0, 'window_start' => time()];
}

if ($_SESSION[$key]['count'] >= 3) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many OTP requests. Please wait a few minutes.']);
    exit;
}
$_SESSION[$key]['count']++;

// ─── Input validation ─────────────────────────────────────────────────────────
$body  = json_decode(file_get_contents('php://input'), true);
$email = trim($body['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid email address is required.']);
    exit;
}

// ─── Forward request to Supabase ─────────────────────────────────────────────
$payload = json_encode([
    'email'              => $email,
    'create_user'        => false,   // Only existing users can log in
]);

$ch = curl_init(SUPABASE_URL . '/auth/v1/otp');
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

if ($status >= 400) {
    http_response_code(400);
    $errMsg = $data['msg'] ?? $data['error_description'] ?? $data['message'] ?? 'Failed to send OTP.';
    echo json_encode([
        'error' => $errMsg,
        'debug' => [
            'status' => $status,
            'raw' => $response
        ]
    ]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'OTP sent to ' . $email]);
