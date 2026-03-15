console.log('%c [Module] Auth Login v8.0 Active ', 'color: #3b82f6; font-weight: bold;');
/* login.js — Login page logic. Calls PHP API endpoints, never Supabase directly. */
document.addEventListener('DOMContentLoaded', () => {
  const emailEl  = document.getElementById('email');
  const otpEl    = document.getElementById('otpCode');
  const loginBtn = document.getElementById('loginBtn');
  const verifyBtn = document.getElementById('verifyBtn');
  const toSignup  = document.getElementById('toSignup');
  const msgBox    = document.getElementById('msg');

  function showMsg(text, isError = false) {
    if (msgBox) {
      msgBox.textContent = text;
      msgBox.style.color = isError ? '#b00020' : '#0a7a0a';
    }
  }

  if (toSignup) toSignup.addEventListener('click', () => window.location.href = 'signup.php');

  // ── Step 1: Send OTP via PHP endpoint ──────────────────────────────────────
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

  // ── Step 2: Verify OTP via PHP endpoint (creates server session) ────────────
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
