<?php
/**
 * api/csrf.php
 * CSRF Token Generation and Validation Helper
 *
 * Usage (in any protected POST handler):
 *   require_once __DIR__ . '/../api/csrf.php';
 *   csrf_verify();   // throws 403 if token is invalid
 *
 * Usage (in PHP page to embed token into JS):
 *   require_once __DIR__ . '/api/csrf.php';
 *   $csrfToken = csrf_token();
 *   // Then: <script>window.CSRF_TOKEN = "<?= $csrfToken ?>";</script>
 *
 * Usage (in JavaScript fetch calls):
 *   headers: { 'X-CSRF-Token': window.CSRF_TOKEN, 'Content-Type': 'application/json' }
 */

require_once __DIR__ . '/../config.php';

/**
 * Ensure a session is started before calling any CSRF function.
 */
function csrf_session_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => SESSION_COOKIE_SECURE,
            'httponly' => true,
            'samesite' => SESSION_COOKIE_SAMESITE,
        ]);
        session_start();
    }
}

/**
 * Generate (or return existing) CSRF token bound to the current session.
 * Tokens rotate every 2 hours.
 */
function csrf_token(): string {
    csrf_session_start();

    $now = time();
    $rotation = 2 * 3600; // 2 hours

    // Rotate token if expired
    if (
        empty($_SESSION['_csrf_token']) ||
        empty($_SESSION['_csrf_issued']) ||
        ($now - $_SESSION['_csrf_issued']) > $rotation
    ) {
        $_SESSION['_csrf_token']  = bin2hex(random_bytes(32));
        $_SESSION['_csrf_issued'] = $now;
    }

    return $_SESSION['_csrf_token'];
}

/**
 * Validate CSRF token from request headers or body.
 * Sends HTTP 403 and exits if validation fails.
 */
function csrf_verify(): void {
    csrf_session_start();

    // Accept token from header (preferred) or JSON body
    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $bodyToken   = '';

    if (empty($headerToken)) {
        $body      = json_decode(file_get_contents('php://input'), true);
        $bodyToken = $body['_csrf'] ?? '';
    }

    $submittedToken = $headerToken ?: $bodyToken;
    $sessionToken   = $_SESSION['_csrf_token'] ?? '';

    if (
        empty($submittedToken) ||
        empty($sessionToken) ||
        !hash_equals($sessionToken, $submittedToken)
    ) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Invalid or missing security token. Please refresh and try again.']);
        exit;
    }
}
