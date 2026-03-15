<?php
/**
 * signup.php — Account request / registration page.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Sign Up — Barangay Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="auth.css">
  <link rel="stylesheet" href="signup.css">

  <!-- Supabase SDK (needed for storage & OTP on this page) -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2" crossorigin="anonymous"></script>
</head>
<body>
  <main class="auth-shell">
    <div class="auth-card">
      <section class="auth-form" aria-label="Sign up form">
        <div class="brand">
          <div class="logo">BD</div>
          <div>
            <div style="font-weight:800">Barangay Based Emergency Response System</div>
            <div class="small">Create an admin account</div>
          </div>
        </div>

        <h1>Create an account</h1>
        <p class="lead">Register with your official email.</p>

        <form id="signupForm" novalidate>
          <div class="field">
            <label for="suFullName">Full name</label>
            <input id="suFullName" name="fullName" type="text" autocomplete="name" placeholder="Juan Dela Cruz" />
          </div>

          <div class="field">
            <label for="suEmail">Email</label>
            <input id="suEmail" name="email" type="email" autocomplete="email" placeholder="you@example.com" required />
          </div>

          <div class="field">
            <label for="suConfirm" id="otpLabel" style="display:none;">6-Digit Verification Code</label>
            <input id="suConfirm" name="otpCode" type="text" autocomplete="one-time-code"
                   placeholder="Enter code sent to email" style="display:none;" />
          </div>

          <div class="field" id="idUploadContainer"
               style="margin-top:20px;border:1px dashed #cbd5e1;padding:16px;border-radius:12px;background:#f8fafc;">
            <label for="suID" style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--primary);font-weight:600;">
              <i class="fas fa-id-card"></i> Upload National ID / Residency Proof
            </label>
            <input id="suID" name="id_file" type="file" accept="image/*"
                   style="margin-top:8px;font-size:.8rem;width:100%;" required />
            <p style="font-size:11px;color:#64748b;margin-top:8px;">Required for account approval by Barangay Admin.</p>
          </div>

          <div class="actions">
            <button id="createBtn" class="btn-primary" type="submit">Verify Email &amp; Create Account</button>
            <button id="backLogin" class="btn-ghost" type="button">Back to login</button>
          </div>
        </form>

        <div id="suMsg" aria-live="polite"></div>
      </section>

      <aside class="auth-visual" aria-hidden="true">
        <div>
          <div style="font-weight:700">Secure access for barangay admins</div>
          <div class="visual-icons" aria-hidden="true" style="margin-top:18px">
            <div class="visual-icon">🛡️</div>
            <div class="visual-icon">📞</div>
            <div class="visual-icon">📍</div>
          </div>
        </div>
        <div class="small">After sign up, check your email for confirmation if enabled.</div>
      </aside>
    </div>
  </main>

  <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>

  <!-- Custom Alert Modal -->
  <div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
    <div class="card-modal">
      <div id="alertIconCircle" class="modal-icon-circle icon-info">
        <i id="alertIcon" class="fas fa-user-check" aria-hidden="true"></i>
      </div>
      <h2 id="alertTitle" style="margin:0 0 8px;font-size:1.5rem;">Notification</h2>
      <p id="alertText" style="color:#64748b;margin:0;line-height:1.5;">Message content goes here.</p>
      <div class="modal-actions-custom" style="justify-content:center;margin-top:24px;">
        <button class="btn-confirm" onclick="window.location.href='login.php'" style="max-width:200px;">
          Go to Login
        </button>
      </div>
    </div>
  </div>

  <script src="signup.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>