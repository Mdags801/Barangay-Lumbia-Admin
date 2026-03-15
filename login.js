console.log('%c [Module] Auth Login v9.1 Active ', 'color: #3b82f6; font-weight: bold;');
/* login.js — Handles both OTP and Password login methods. */
document.addEventListener('DOMContentLoaded', () => {
  const emailEl       = document.getElementById('email');
  const passwordEl    = document.getElementById('password');
  const otpEl         = document.getElementById('otpCode');
  
  const sendOtpBtn      = document.getElementById('sendOtpBtn');
  const verifyOtpBtn    = document.getElementById('verifyOtpBtn');
  const passwordLoginBtn = document.getElementById('passwordLoginBtn');
  const toggleModeBtn    = document.getElementById('toggleLoginMode');
  
  const passwordField = document.getElementById('passwordField');
  const otpField      = document.getElementById('otpField');
  const toSignup      = document.getElementById('toSignup');
  const msgBox        = document.getElementById('msg');

  let currentMode = 'otp'; // 'otp' or 'password'

  function showMsg(text, isError = false) {
    if (msgBox) {
      msgBox.textContent = text;
      msgBox.style.color = isError ? '#b00020' : '#0a7a0a';
    }
  }

  if (toSignup) toSignup.addEventListener('click', () => window.location.href = 'signup.php');

  // Toggle between OTP and Password modes
  toggleModeBtn.addEventListener('click', () => {
    if (currentMode === 'otp') {
      currentMode = 'password';
      toggleModeBtn.textContent = 'Use OTP code instead';
      
      passwordField.style.display = 'block';
      passwordLoginBtn.style.display = 'block';
      
      otpField.style.display = 'none';
      sendOtpBtn.style.display = 'none';
      verifyOtpBtn.style.display = 'none';
    } else {
      currentMode = 'otp';
      toggleModeBtn.textContent = 'Use password instead';
      
      passwordField.style.display = 'none';
      passwordLoginBtn.style.display = 'none';
      
      sendOtpBtn.style.display = 'block';
    }
    showMsg('');
  });

  // ── OTP Method: Step 1 (Send Code) ──────────────────────────────────────────
  sendOtpBtn.addEventListener('click', async (ev) => {
    ev.preventDefault();
    const email = emailEl.value.trim();
    if (!email) { showMsg('Please enter your email.', true); emailEl.focus(); return; }

    sendOtpBtn.disabled = true;
    sendOtpBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    try {
      const res = await fetch('api/send_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      });
      const data = await res.json();
      if (!res.ok || data.error) throw new Error(data.error || 'Failed to send OTP');

      showMsg('A 6-digit code has been sent to your email.');
      otpField.style.display = 'block';
      verifyOtpBtn.style.display = 'block';
      sendOtpBtn.style.display = 'none';
      toggleModeBtn.style.display = 'none'; // Lock mode during verification
      emailEl.disabled = true;
      otpEl.focus();
    } catch (err) {
      showMsg(err.message, true);
      sendOtpBtn.disabled = false;
      sendOtpBtn.textContent = 'Send OTP Code';
    }
  });

  // ── OTP Method: Step 2 (Verify Code) ────────────────────────────────────────
  verifyOtpBtn.addEventListener('click', async (ev) => {
    ev.preventDefault();
    const email = emailEl.value.trim();
    const token = otpEl.value.trim();
    if (!token) { showMsg('Please enter the 6-digit code.', true); otpEl.focus(); return; }

    verifyOtpBtn.disabled = true;
    verifyOtpBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

    try {
      const res = await fetch('api/verify_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, token }),
      });
      const data = await res.json();
      if (!res.ok || data.error) throw new Error(data.error || 'Invalid code');

      showMsg('Login successful!');
      setTimeout(() => window.location.href = data.redirect || 'index.php', 800);
    } catch (err) {
      showMsg(err.message, true);
      verifyOtpBtn.disabled = false;
      verifyOtpBtn.textContent = 'Verify & Log In';
    }
  });

  // ── Password Method ─────────────────────────────────────────────────────────
  passwordLoginBtn.addEventListener('click', async (ev) => {
    ev.preventDefault();
    const email = emailEl.value.trim();
    const password = passwordEl.value;

    if (!email) { showMsg('Please enter your email.', true); emailEl.focus(); return; }
    if (!password) { showMsg('Please enter your password.', true); passwordEl.focus(); return; }

    passwordLoginBtn.disabled = true;
    passwordLoginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';

    try {
      const res = await fetch('api/login_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const data = await res.json();
      if (!res.ok || data.error) throw new Error(data.error || 'Invalid credentials');

      showMsg('Login successful!');
      setTimeout(() => window.location.href = data.redirect || 'index.php', 800);
    } catch (err) {
      showMsg(err.message, true);
      passwordLoginBtn.disabled = false;
      passwordLoginBtn.textContent = 'Log In with Password';
    }
  });
});
