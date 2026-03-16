<?php
/**
 * reset.php — Forgot password / reset password page.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Reset Password — Barangay Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="auth.css">
  <link rel="stylesheet" href="reset.css">

  <!-- Supabase SDK (needed for resetPasswordForEmail) -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

</head>
<body>
  <main class="auth-shell">
    <div class="auth-card">
      <section class="auth-form">
        <a href="login.php" class="back-link">
          <i class="fas fa-arrow-left"></i> Back to Login
        </a>

        <!-- REQUEST RESET STEP -->
        <div id="requestStep">
          <h1>Forgot Password?</h1>
          <p class="lead">Enter your email and we'll send you a link to reset your password.</p>
          <div class="field">
            <label for="email">Email Address</label>
            <input id="email" type="email" placeholder="you@example.com" required />
          </div>
          <div class="actions">
            <button id="sendBtn" class="btn-primary" style="width:100%">Send Reset Link</button>
          </div>
        </div>

        <!-- NEW PASSWORD STEP -->
        <div id="resetStep" class="hidden">
          <h1>Create New Password</h1>
          <p class="lead">Please enter your new secure password below.</p>
          <div class="field">
            <label for="newPassword">New Password</label>
            <input id="newPassword" type="password" placeholder="••••••••" required />
          </div>
          <div class="field">
            <label for="confirmPassword">Confirm New Password</label>
            <input id="confirmPassword" type="password" placeholder="••••••••" required />
          </div>
          <div class="actions">
            <button id="resetBtn" class="btn-primary" style="width:100%">Update Password</button>
          </div>
        </div>

        <!-- SUCCESS -->
        <div id="successStep" class="hidden status-card">
          <div class="status-icon status-success">
            <i class="fas fa-check-circle"></i>
          </div>
          <h2 id="successTitle">Check your email</h2>
          <p id="successMessage" style="color:#64748b;margin-top:8px;">
            We've sent a password reset link to your email address.
          </p>
          <div class="actions" style="margin-top:24px;">
            <button onclick="window.location.href='login.php'" class="btn-ghost" style="width:100%">
              Return to Login
            </button>
          </div>
        </div>

        <div id="msg" style="margin-top:16px;font-size:.9rem;text-align:center;"></div>
      </section>

      <aside class="auth-visual">
        <div>
          <div style="font-weight:800;font-size:1.2rem;margin-bottom:12px;">Security First</div>
          <p style="opacity:.9;font-size:.95rem;">
            Resetting your password helps keep your barangay data safe and secure.
          </p>
        </div>
        <div class="small" style="color:rgba(255,255,255,.7)">Barangay Based Emergency Response System</div>
      </aside>
    </div>
  </main>

  <!-- Custom Alert Modal (Standardized) -->
  <div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
    <div class="card-modal">
      <div id="alertIconCircle" class="modal-icon-circle icon-info">
        <i id="alertIcon" class="fas fa-info-circle"></i>
      </div>
      <h2 id="alertTitle" style="margin:0 0 8px; font-size:1.5rem; font-weight: 800;">Notification</h2>
      <p id="alertText" style="color:#64748b; margin:0 0 24px; line-height:1.5;">Message content goes here.</p>
      <div class="modal-actions" style="justify-content:center;">
        <button class="btn-confirm" onclick="closeAlertModal()">Understood</button>
      </div>
    </div>
  </div>

  <script>
    function closeAlertModal() {
      // Direct hide as reset.js expects this function occasionally
      document.getElementById('alertModal').style.display = 'none';
    }
  </script>
  <script src="reset.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>