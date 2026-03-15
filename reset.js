/* reset.js — Reset/forgot password page logic */
const supabase = window.supabase.createClient(
  "https://tukkkwtxuaxrbihyammp.supabase.co",
  "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P",
  { auth: { persistSession: true, autoRefreshToken: true, detectSessionInUrl: true } }
);

document.addEventListener('DOMContentLoaded', async () => {
  const requestStep = document.getElementById('requestStep');
  const resetStep   = document.getElementById('resetStep');
  const successStep = document.getElementById('successStep');
  const msgBox      = document.getElementById('msg');

  function showMsg(text, isError = false) {
    msgBox.textContent = text;
    msgBox.style.color = isError ? '#ef4444' : '#10b981';
    msgBox.className = isError ? 'error' : 'success';
  }

  // 1. Initial State Check
  // Supabase takes a moment to process hashes and trigger events.
  // We check for the recovery code hash immediately.
  const hash = window.location.hash;
  if (hash && (hash.includes('type=recovery') || hash.includes('access_token='))) {
    requestStep.classList.add('hidden');
    resetStep.classList.remove('hidden');
    showMsg('Recovery link detected. Please enter your new password.');
  }

  // 2. Listen for recovery events (Standard Supabase Flow)
  supabase.auth.onAuthStateChange((event, session) => {
    if (event === 'PASSWORD_RECOVERY') {
      console.log('User is in recovery mode');
      requestStep.classList.add('hidden');
      resetStep.classList.remove('hidden');
      successStep.classList.add('hidden');
      showMsg('Session active. You can now reset your password.');
    }
  });

  // 3. ── Send reset link ──────────────────────────────────────────────────────────
  document.getElementById('sendBtn').onclick = async () => {
    const email = document.getElementById('email').value.trim();
    if (!email) return showMsg('Please enter your email', true);

    const btn = document.getElementById('sendBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    // The live URL where users will be sent back to
    const resetPageUrl = window.location.origin + window.location.pathname;

    const { error } = await supabase.auth.resetPasswordForEmail(email, {
      redirectTo: resetPageUrl,
    });

    if (error) {
      showMsg(error.message, true);
      btn.disabled = false;
      btn.textContent = 'Send Reset Link';
    } else {
      requestStep.classList.add('hidden');
      successStep.classList.remove('hidden');
      showMsg(''); // Clear any previous errors
    }
  };

  // 4. ── Update password ──────────────────────────────────────────────────────────
  document.getElementById('resetBtn').onclick = async () => {
    const newPass     = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;

    if (newPass.length < 6) return showMsg('Password must be at least 6 characters', true);
    if (newPass !== confirmPass) return showMsg('Passwords do not match', true);

    const btn = document.getElementById('resetBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    // This updates the password for the current recovery session user
    const { error } = await supabase.auth.updateUser({ password: newPass });

    if (error) {
      showMsg(error.message, true);
      btn.disabled = false;
      btn.textContent = 'Update Password';
    } else {
      resetStep.classList.add('hidden');
      successStep.classList.remove('hidden');
      document.getElementById('successTitle').textContent = 'Password Updated';
      document.getElementById('successMessage').textContent =
        'Your password has been successfully changed. You can now log in securely.';
      showMsg('');
    }
  };
});
