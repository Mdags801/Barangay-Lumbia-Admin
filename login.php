<?php
/**
 * login.php — Admin login page.
 * Redirects already-authenticated users to the dashboard.
 */
require_once __DIR__ . '/config.php';

session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => SESSION_COOKIE_SECURE,
    'httponly' => true,
    'samesite' => SESSION_COOKIE_SAMESITE,
]);
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

$errorParam = htmlspecialchars($_GET['error'] ?? '');
$errorMsgs  = [
    'session_expired'   => 'Your session has expired. Please log in again.',
    'not_authenticated' => 'Please log in to access the portal.',
    'access_denied'     => 'Access Denied: This portal is for administrative personnel only.',
];
$prefilledError = $errorMsgs[$errorParam] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Log in — Barangay Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="auth.css">
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <main class="auth-shell" role="main">
    <div class="auth-card" aria-labelledby="authTitle">
      <section class="auth-form" aria-label="Login form">
        <div class="brand">
          <div class="logo">BD</div>
          <div>
            <div style="font-weight:800">Barangay Based Emergency Response System</div>
            <div class="small">Admin Portal</div>
          </div>
        </div>

        <h1 id="authTitle">Log in to your Account</h1>
        <p class="lead">Welcome back!</p>

        <?php if ($prefilledError): ?>
        <div id="msg" aria-live="polite" style="margin-bottom:12px;color:#b00020;font-size:.9rem;">
          <?= $prefilledError ?>
        </div>
        <?php endif; ?>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required />
        </div>

        <div class="field" id="passwordField" style="display:none;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <label for="password">Password</label>
            <a href="reset.php" style="font-size:0.75rem;color:#3b82f6;text-decoration:none;">Forgot?</a>
          </div>
          <input id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" />
        </div>

        <div class="field" id="otpField" style="display:none;">
          <label for="otpCode">6-Digit Verification Code</label>
          <input id="otpCode" name="otpCode" type="text" inputmode="numeric" maxlength="6"
                 autocomplete="one-time-code" placeholder="123456" />
        </div>

        <div class="actions">
          <!-- OTP Buttons -->
          <button id="sendOtpBtn" class="btn-primary">Send OTP Code</button>
          <button id="verifyOtpBtn" class="btn-primary" style="display:none;background:#10b981;">Verify &amp; Log In</button>
          
          <!-- Password Button -->
          <button id="passwordLoginBtn" class="btn-primary" style="display:none;">Log In with Password</button>
          
          <button id="toggleLoginMode" class="btn-ghost" style="margin-top:8px;font-size:0.85rem;color:#6b7280;" type="button">
            Use password instead
          </button>
          
          <button id="toSignup"  class="btn-ghost" type="button" style="margin-top:0;">Create an account</button>
        </div>

        <div id="msg" aria-live="polite" style="margin-top:12px"></div>
      </section>

      <aside class="auth-visual" aria-hidden="true">
        <div>
          <div class="visual-top">
            <div style="font-weight:800;font-size:1.05rem">Rapid Emergency Response</div>
          </div>
          <div class="visual-icons">
            <div class="visual-icon">🛡️</div>
            <div class="visual-icon">📞</div>
            <div class="visual-icon">📍</div>
            <div class="visual-icon">📊</div>
          </div>
          <div class="visual-tagline">
            Everything you need in an easily customizable dashboard for your barangay.
          </div>
        </div>
        <div class="small">Secure access for authorized personnel only.</div>
      </aside>
    </div>
  </main>

  <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>

  <script src="login.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>