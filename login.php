<?php
/**
 * login.php  — Protected login page.
 * Redirects already-authenticated users straight to the dashboard.
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

// Already logged in → skip login page
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
  <style>
    input, button { transition: box-shadow .12s ease, transform .08s ease; }
    button:active { transform: translateY(1px); }
  </style>
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
        <div id="msg" aria-live="polite" style="margin-bottom:12px;color:#b00020;font-size:0.9rem;">
          <?= $prefilledError ?>
        </div>
        <?php endif; ?>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required />
        </div>

        <div class="field" id="otpField" style="display:none;">
          <label for="otpCode">6-Digit Verification Code</label>
          <input id="otpCode" name="otpCode" type="text" inputmode="numeric" maxlength="6"
                 autocomplete="one-time-code" placeholder="123456" />
        </div>

        <div class="actions">
          <button id="loginBtn" class="btn-primary">Send OTP Code</button>
          <button id="verifyBtn" class="btn-primary" style="display:none;background:#10b981;">Verify &amp; Log In</button>
          <button id="toSignup" class="btn-ghost" type="button">Create an account</button>
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

  <script>
    /* ── Login logic — all Supabase calls go through PHP API endpoints ── */
    document.addEventListener('DOMContentLoaded', () => {
      const emailEl  = document.getElementById('email');
      const otpEl    = document.getElementById('otpCode');
      const loginBtn = document.getElementById('loginBtn');
      const verifyBtn= document.getElementById('verifyBtn');
      const toSignup = document.getElementById('toSignup');
      const msgBox   = document.getElementById('msg');

      function showMsg(text, isError = false) {
        if (msgBox) {
          msgBox.textContent = text;
          msgBox.style.color = isError ? '#b00020' : '#0a7a0a';
        }
      }

      if (toSignup) toSignup.addEventListener('click', () => window.location.href = 'signup.php');

      // ── Step 1: Send OTP via PHP endpoint ────────────────────────────────
      loginBtn.addEventListener('click', async (ev) => {
        ev.preventDefault();
        const email = emailEl.value.trim();

        if (!email) { showMsg('Please enter your email address.', true); emailEl.focus(); return; }

        loginBtn.disabled = true;
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Code...';
        showMsg('Requesting secure login code...');

        try {
          const res  = await fetch('api/send_otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email }),
          });
          const data = await res.json();

          if (!res.ok || data.error) {
            showMsg(data.error || 'Failed to send OTP code. Is your email registered?', true);
            loginBtn.disabled = false;
            loginBtn.textContent = 'Send OTP Code';
            return;
          }

          showMsg('A 6-digit code has been sent to your email.');
          document.getElementById('otpField').style.display = 'block';
          verifyBtn.style.display = 'block';
          loginBtn.style.display  = 'none';
          emailEl.disabled = true;
          otpEl.focus();

        } catch (err) {
          showMsg('An unexpected error occurred.', true);
          loginBtn.disabled = false;
          loginBtn.textContent = 'Send OTP Code';
        }
      });

      // ── Step 2: Verify OTP via PHP endpoint (creates server session) ──────
      verifyBtn.addEventListener('click', async (ev) => {
        ev.preventDefault();
        const email = emailEl.value.trim();
        const token = otpEl.value.trim();

        if (!token) { showMsg('Please enter the 6-digit verification code.', true); otpEl.focus(); return; }

        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        showMsg('Verifying code...');

        try {
          const res  = await fetch('api/verify_otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, token }),
          });
          const data = await res.json();

          if (!res.ok || data.error) {
            showMsg(data.error || 'Invalid code. Please try again.', true);
            verifyBtn.disabled = false;
            verifyBtn.textContent = 'Verify & Log In';
            return;
          }

          showMsg('Login successful! Redirecting to Dashboard...');
          setTimeout(() => window.location.href = data.redirect || 'index.php', 800);

        } catch (err) {
          showMsg('An unexpected error occurred during verification.', true);
          verifyBtn.disabled = false;
          verifyBtn.textContent = 'Verify & Log In';
        }
      });
    });
  </script>
</body>
</html>


  <style>
    /* Buttons */

    input,
    button {
      transition: box-shadow .12s ease, transform .08s ease;
    }

    button:active {
      transform: translateY(1px);
    }
  </style>
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

        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required />
        </div>

        <div class="field" id="passwordField" style="display: none;">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" />
        </div>

        <div class="field" id="otpField" style="display: none;">
          <label for="otpCode">6-Digit Verification Code</label>
          <input id="otpCode" name="otpCode" type="text" autocomplete="one-time-code" placeholder="123456" />
        </div>

        <div class="controls-row" id="controlsRow">
          <label class="checkbox"><input id="remember" type="checkbox" /> Remember me</label>
        </div>

        <div class="actions">
          <button id="loginBtn" class="btn-primary">Send OTP Code</button>
          <button id="verifyBtn" class="btn-primary" style="display: none; background: #10b981;">Verify & Log In</button>
          <button id="toSignup" class="btn-ghost" type="button">Create an account</button>
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

  <!-- Login logic -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const supabase = window.supabaseClient;
      const emailEl = document.getElementById('email');
      const passwordEl = document.getElementById('password');
      const loginBtn = document.getElementById('loginBtn');
      const toSignup = document.getElementById('toSignup');
      const forgotLink = document.getElementById('forgotLink');
      const msgBox = document.getElementById('msg');

      function showMsg(text, isError = false) {
        if (msgBox) {
          msgBox.textContent = text;
          msgBox.style.color = isError ? '#b00020' : '#0a7a0a';
        }
        console.log('[login]', text);
      }

      // Check if user is already logged in
      async function checkActiveSession() {
        const { data: { session } } = await supabase.auth.getSession();
        if (session && session.user) {
          console.log('[login] Active session found, redirecting to portal...');
          window.location.href = 'index.php';
        }
      }
      checkActiveSession();

      if (toSignup) {
        toSignup.addEventListener('click', () => window.location.href = 'signup.php');
      }
      if (forgotLink) {
        forgotLink.addEventListener('click', () => window.location.href = 'reset.php');
      }

      // Check for security error params
      const params = new URLSearchParams(window.location.search);
      if (params.get('error') === 'access_denied') {
        showMsg('Access Denied: This portal is for administrative personnel only.', true);
      }

      loginBtn.addEventListener('click', async (ev) => {
        ev.preventDefault();
        const email = (emailEl?.value || '').trim();

        if (!email) {
          showMsg('Please enter your email address', true);
          emailEl.focus();
          return;
        }

        if (!supabase) {
          showMsg('Authentication client not initialized.', true);
          return;
        }

        const originalBtnText = loginBtn.textContent;
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Code...';
        showMsg('Requesting secure login code...');

        try {
          const { data, error } = await supabase.auth.signInWithOtp({ 
            email, 
            options: { shouldCreateUser: false } 
          });

          if (error) {
            showMsg(error.message || 'Failed to send OTP code. Is your email registered?', true);
            loginBtn.disabled = false;
            loginBtn.textContent = originalBtnText;
            return;
          }

          showMsg('A 6-digit code has been sent to your email.');
          document.getElementById('otpField').style.display = 'block';
          document.getElementById('verifyBtn').style.display = 'block';
          loginBtn.style.display = 'none';
          document.getElementById('controlsRow').style.display = 'none';
          emailEl.disabled = true;
          document.getElementById('otpCode').focus();

        } catch (err) {
           showMsg('An unexpected error occurred.', true);
           loginBtn.disabled = false;
           loginBtn.textContent = originalBtnText;
        }
      });

      const verifyBtn = document.getElementById('verifyBtn');
      verifyBtn.addEventListener('click', async (ev) => {
        ev.preventDefault();
        const email = (emailEl?.value || '').trim();
        const token = (document.getElementById('otpCode')?.value || '').trim();

        if (!token) {
          showMsg('Please enter the 6-digit verification code.', true);
          document.getElementById('otpCode').focus();
          return;
        }

        const originalBtnText = verifyBtn.textContent;
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        showMsg('Verifying code...');

        try {
          const { data, error } = await supabase.auth.verifyOtp({ 
            email, 
            token, 
            type: 'email' 
          });

          if (error) {
            showMsg(error.message || 'Invalid code. Please try again.', true);
            verifyBtn.disabled = false;
            verifyBtn.textContent = originalBtnText;
            return;
          }

          if (data && data.user) {
            // Check Role and Status Restriction
            const { data: profile, error: profileError } = await supabase.from('profiles').select('role, status').eq('id', data.user.id).maybeSingle();

            if (profileError || !profile) {
              await supabase.auth.signOut();
              showMsg('Your account profile could not be found or is waiting for initial setup setup.', true);
              verifyBtn.disabled = false;
              verifyBtn.textContent = originalBtnText;
              return;
            }

            const role = (profile.role || 'staff').toLowerCase();
            const status = String(profile.status || 'Pending').toLowerCase();

            if (role === 'citizen' || role === 'responder') {
              await supabase.auth.signOut();
              showMsg('Access Denied: This portal is for administrative personnel only.', true);
              verifyBtn.disabled = false;
              verifyBtn.textContent = originalBtnText;
              return;
            }

            if (status !== 'active') {
              await supabase.auth.signOut();
              let msg = 'Your account is not active.';
              if (status === 'pending') {
                msg = 'Your account is still PENDING approval. Please wait for the Admin to verify your ID.';
              } else if (status === 'suspended') {
                msg = 'Your account has been SUSPENDED. Please contact support.';
              } else if (status === 'archived' || status === 'rejected') {
                msg = 'This account is no longer active or was rejected during verification.';
              }
              showMsg(msg, true);
              verifyBtn.disabled = false;
              verifyBtn.textContent = originalBtnText;
              return;
            }

            showMsg('Login successful. Taking you to Dashboard...');
            setTimeout(() => { window.location.href = 'index.php'; }, 1000);
          } else {
            showMsg('Unexpected error, please try again.', true);
            verifyBtn.disabled = false;
            verifyBtn.textContent = originalBtnText;
          }
        } catch (err) {
           showMsg('An unexpected error occurred during verification.', true);
           verifyBtn.disabled = false;
           verifyBtn.textContent = originalBtnText;
        }
      });

      // End of verifyBtn event listener

    });
  </script>
</body>

</html>