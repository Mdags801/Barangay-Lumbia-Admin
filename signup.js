/* signup.js — Signup page logic */

const SUPABASE_URL     = 'https://tukkkwtxuaxrbihyammp.supabase.co';
const SUPABASE_ANON_KEY= 'sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P';

function showMsg(msg, type) {
  const msgBox = document.getElementById('suMsg');
  if (msgBox) {
    msgBox.textContent = msg;
    msgBox.className = type || '';
    msgBox.style.display = 'block';
  } else {
    showToast(msg, type === 'success' ? 'success' : 'info');
  }
}

function showToast(msg, type = 'info') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'true');
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  const iconClass = type === 'error' || type === 'danger'
    ? 'times-circle' : type === 'success' ? 'check-circle' : 'info-circle';
    
  toast.innerHTML = `
    <div class="icon"><i class="fas fa-${iconClass}"></i></div>
    <div class="content"><strong>System Notification</strong><br/>${msg}</div>
    <button class="close">&times;</button>
  `;
  container.appendChild(toast);
  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
  const hide = () => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 200); };
  toast.querySelector('.close').onclick = hide;
  setTimeout(hide, 5000);
}

function withButtonLoading(button, fn) {
  if (!button) return fn();
  const originalText = button.textContent;
  button.disabled = true;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
  return Promise.resolve(fn()).finally(() => {
    button.disabled = false;
    button.textContent = originalText;
  });
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showAlreadyRegisteredModal(email, role) {
  const modal = document.getElementById('alertModal');
  const title = document.getElementById('alertTitle');
  const text  = document.getElementById('alertText');
  const icon  = document.getElementById('alertIcon');
  const circle = document.getElementById('alertIconCircle');
  
  if (!modal) return;
  title.textContent = 'Account Found';
  text.innerHTML = `The email <strong>${email}</strong> is already registered as <strong>${role}</strong>. Please log in instead.`;
  
  icon.className = 'fas fa-user-check';
  circle.className = 'modal-icon-circle icon-info';
  
  modal.style.display = 'flex';
}

function closeModals() {
  document.querySelectorAll('.custom-modal').forEach(m => m.style.display = 'none');
}

// ── Wait for Supabase SDK ─────────────────────────────────────────────────────
function waitForSupabaseSDK(callback, maxAttempts = 50, interval = 100) {
  let attempts = 0;
  (function check() {
    if (window.supabase && typeof window.supabase.createClient === 'function') {
      callback();
    } else if (++attempts < maxAttempts) {
      setTimeout(check, interval);
    } else {
      console.error('Supabase SDK failed to load after waiting.');
      showMsg('Supabase SDK failed to load. Please refresh the page.', 'error');
    }
  })();
}

document.addEventListener('DOMContentLoaded', () => {
  waitForSupabaseSDK(initializeSignup);
});

function initializeSignup() {
  if (!window.supabaseClient) {
    window.supabaseClient = window.supabase.createClient(
      SUPABASE_URL, SUPABASE_ANON_KEY,
      { auth: { autoRefreshToken: true, persistSession: true, detectSessionInUrl: true } }
    );
  }
  const supabase = window.supabaseClient;

  // Redirect if already logged in
  async function checkActiveSession() {
    const { data: { session } } = await supabase.auth.getSession();
    if (session && session.user) window.location.href = 'index.php';
  }
  checkActiveSession();

  // Back to login button
  const backLogin = document.getElementById('backLogin');
  if (backLogin) {
    backLogin.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = 'login.php';
    });
  }

  // Signup form
  const form = document.getElementById('signupForm');
  if (!form) { console.error('Signup form with id="signupForm" not found.'); return; }

  let isCodeSent = false;
  form.addEventListener('submit', handleSignup);

  async function handleSignup(event) {
    event.preventDefault();
    showMsg('', '');

    const fullName = form.elements['fullName']?.value?.trim();
    const email    = form.elements['email']?.value?.trim();
    const idFile   = form.elements['id_file']?.files[0];
    const otpCode  = form.elements['otpCode']?.value?.trim();
    const submitBtn= form.querySelector('[type="submit"]');

    if (!fullName)             { showMsg('Please enter your full name.',          'error'); form.elements['fullName']?.focus(); return; }
    if (!email)                { showMsg('Please enter your email address.',       'error'); form.elements['email']?.focus();    return; }
    if (!isValidEmail(email))  { showMsg('Please enter a valid email address.',   'error'); form.elements['email']?.focus();    return; }
    if (!idFile)               { showMsg('Please upload a copy of your ID.',       'error'); return; }

    if (!isCodeSent) {
      // STEP 1: Send OTP
      await withButtonLoading(submitBtn, async () => {
        const { error } = await supabase.auth.signInWithOtp({
          email,
          options: { shouldCreateUser: true }
        });
        if (error) { showMsg(error.message || 'Failed to send verification code.', 'error'); return; }

        showMsg('A 6-digit verification code has been sent to your email.', 'success');
        isCodeSent = true;

        form.elements['fullName'].disabled = true;
        form.elements['email'].disabled    = true;
        document.getElementById('idUploadContainer').style.display = 'none';

        const otpInput = form.elements['otpCode'];
        const otpLabel = document.getElementById('otpLabel');
        otpInput.style.display = 'block';
        otpLabel.style.display = 'block';
        otpInput.required = true;
        submitBtn.textContent = 'Verify Code & Complete Registration';
        otpInput.focus();
      });
      return;
    }

    // STEP 2: Verify OTP + create profile
    if (!otpCode) { showMsg('Please enter the 6-digit verification code.', 'error'); form.elements['otpCode']?.focus(); return; }

    await withButtonLoading(submitBtn, async () => {
      showMsg('Verifying your code...', 'info');
      try {
        const { data, error } = await supabase.auth.verifyOtp({ email, token: otpCode, type: 'email' });
        if (error) { showMsg(error.message || 'Invalid code. Please try again.', 'error'); return; }

        if (data?.user) {
          showMsg('Code verified! Processing your account details...', 'info');

          // Upload ID image
          let idUrl = null;
          try {
            const fileExt = idFile.name.split('.').pop();
            const fileName= `${data.user.id}-${Math.random().toString(36).substring(2)}.${fileExt}`;
            const filePath= `identities/${fileName}`;
            const { error: uploadError } = await supabase.storage.from('identities').upload(filePath, idFile);
            if (uploadError) throw uploadError;
            const { data: { publicUrl } } = supabase.storage.from('identities').getPublicUrl(filePath);
            idUrl = publicUrl;
          } catch (upErr) {
            console.error('[Supabase] ID Upload failed:', upErr);
          }

          // Upsert profile with Pending status
          const { error: profileError } = await supabase.from('profiles').upsert({
            id: data.user.id,
            full_name: fullName,
            email,
            role: 'staff',
            status: 'Pending',
            id_url: idUrl
          });
          if (profileError) console.error('[Supabase] Profile creation failed:', profileError);

          // Sign out — they are pending approval
          await supabase.auth.signOut();

          showMsg('Registering successful! Your account request has been submitted.', 'success');
          showToast('Request submitted! Please wait for administrative approval.', 'success');
          setTimeout(() => window.location.href = 'login.php', 2500);
        } else {
          showMsg('Signup request completed. Please check your email.', 'info');
        }
      } catch (err) {
        console.error('[Supabase] signUp exception:', err);
        showMsg('An unexpected error occurred. Please try again.', 'error');
      }
    });
  }
}
