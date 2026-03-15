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
    msgBox.className = isError ? 'error' : 'success';
  }

  // Detect PASSWORD_RECOVERY event from Supabase
  supabase.auth.onAuthStateChange(async (event) => {
    if (event === 'PASSWORD_RECOVERY') {
      requestStep.classList.add('hidden');
      resetStep.classList.remove('hidden');
      successStep.classList.add('hidden');
      msgBox.textContent = '';
    }
  });

  // Fallback: hash detection
  if (window.location.hash && window.location.hash.includes('type=recovery')) {
    requestStep.classList.add('hidden');
    resetStep.classList.remove('hidden');
  }

  // ── Send reset link ──────────────────────────────────────────────────────────
  document.getElementById('sendBtn').onclick = async () => {
    const email = document.getElementById('email').value.trim();
    if (!email) return showMsg('Please enter your email', true);

    const btn = document.getElementById('sendBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    const resetPageUrl = window.location.href.split('?')[0].split('#')[0];

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
    }
  };

  // ── Update password ──────────────────────────────────────────────────────────
  document.getElementById('resetBtn').onclick = async () => {
    const newPass     = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;

    if (newPass.length < 6) return showMsg('Password must be at least 6 characters', true);
    if (newPass !== confirmPass) return showMsg('Passwords do not match', true);

    const btn = document.getElementById('resetBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

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
        'Your password has been successfully changed. You can now log in.';
    }
  };
});
