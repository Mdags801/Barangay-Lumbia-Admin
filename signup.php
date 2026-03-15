<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Correct Supabase SDK include -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script>
    // Initialize client once SDK is parsed
    window.supabase = supabase.createClient(
      "https://tukkkwtxuaxrbihyammp.supabase.co",
      "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P",
      { auth: { persistSession: true, autoRefreshToken: true, detectSessionInUrl: false } }
    );
    console.log("[Supabase] Client initialized");
  </script>


  <meta charset="utf-8" />
  <title>Sign Up</title>
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/supabase.min.js"
    onload="(function(){ try { window.supabase = window.supabase || supabase.createClient('https://tukkkwtxuaxrbihyammp.supabase.co','sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P',{ auth:{ persistSession:true, autoRefreshToken:true, detectSessionInUrl:false } }); console.log('[signup] supabase onload init'); } catch(e){ console.error('[signup] onload init failed', e); } })();"
    crossorigin="anonymous"></script>

  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="auth.css">
  <style>
    /* Helpers */

    .btn-loading {
      position: relative;
    }

    .btn-loading .spinner {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 16px;
      height: 16px;
      border-radius: 50%;
      border: 2px solid rgba(0, 0, 0, 0.12);
      border-top-color: rgba(0, 0, 0, 0.6);
      animation: spin .9s linear infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    .btn-success {
      animation: successPulse .42s ease forwards;
      background: linear-gradient(90deg, #16a34a, #059669);
      color: #fff !important;
      border-color: transparent !important;
    }

    @keyframes successPulse {
      0% {
        transform: scale(1);
        box-shadow: none
      }

      50% {
        transform: scale(1.04);
        box-shadow: 0 8px 24px rgba(5, 150, 105, 0.18)
      }

      100% {
        transform: scale(1);
        box-shadow: none
      }
    }

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
            <input id="suConfirm" name="otpCode" type="text" autocomplete="one-time-code" placeholder="Enter code sent to email" style="display:none;" />
          </div>

          <div class="field" id="idUploadContainer" style="margin-top: 20px; border: 1px dashed #cbd5e1; padding: 16px; border-radius: 12px; background: #f8fafc;">
            <label for="suID" style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--primary); font-weight:600;">
              <i class="fas fa-id-card"></i> Upload National ID / Residency Proof
            </label>
            <input id="suID" name="id_file" type="file" accept="image/*" style="margin-top:8px; font-size: 0.8rem; width:100%;" required />
            <p style="font-size: 11px; color: #64748b; margin-top: 8px;">Required for account approval by Barangay Admin.</p>
          </div>

          <div class="actions">
            <button id="createBtn" class="btn-primary" type="submit">Verify Email & Create Account</button>
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

  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script>
    // === CONFIGURATION ===
    const SUPABASE_URL = 'https://tukkkwtxuaxrbihyammp.supabase.co';
    const SUPABASE_ANON_KEY = 'sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P';

    function showMsg(msg, type) {
      const msgBox = document.getElementById('msg-box');
      if (msgBox) {
        msgBox.textContent = msg;
        msgBox.className = type || '';
        msgBox.style.display = 'block';
      } else {
        showToast(msg, type || 'info');
      }
    }
    function showToast(msg, type) {
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
      const iconClass = type === 'error' || type === 'danger' ? 'times-circle' : type === 'success' ? 'check-circle' : 'info-circle';
      toast.innerHTML = `<div class="icon"><i class="fas fa-${iconClass}"></i></div><div class="content"><strong>Notification</strong><br/>${msg}</div><button class="close">&times;</button>`;
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
      button.textContent = 'Loading...';
      return Promise.resolve(fn()).finally(() => {
        button.disabled = false;
        button.textContent = originalText;
      });
    }
    const backLogin = document.getElementById('backLogin');
    if (backLogin) {
      backLogin.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = '/login.php'; // adjust path if needed
      });
    }

    // === Wait for SDK and DOM ===
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

    document.addEventListener('DOMContentLoaded', function () {
      waitForSupabaseSDK(initializeSignup);
    });

    function initializeSignup() {
      // === Create global Supabase client only once ===
      if (!window.supabaseClient) {
        window.supabaseClient = window.supabase.createClient(
          SUPABASE_URL,
          SUPABASE_ANON_KEY,
          {
            auth: {
              autoRefreshToken: true,
              persistSession: true,
              detectSessionInUrl: true
            }
          }
        );
        console.log('[Supabase] Client initialized');
      }
      const supabase = window.supabaseClient;
      
      // Check if user is already logged in
      async function checkActiveSession() {
        const { data: { session } } = await supabase.auth.getSession();
        if (session && session.user) {
          console.log('[signup] Active session found, redirecting to portal...');
          window.location.href = 'index.php';
        }
      }
      checkActiveSession();

      // === Attach signup handler to form ===
      const form = document.getElementById('signupForm'); // <-- FIXED ID
      if (!form) {
        console.error('Signup form with id="signupForm" not found.');
        return;
      }
      form.removeEventListener('submit', handleSignup);
      form.addEventListener('submit', handleSignup);
      let isCodeSent = false;

      async function handleSignup(event) {
        event.preventDefault();
        showMsg('', '');

        const fullName = form.elements['fullName']?.value?.trim();
        const email = form.elements['email']?.value?.trim();
        const idFile = form.elements['id_file']?.files[0];
        const otpCode = form.elements['otpCode']?.value?.trim();
        const submitBtn = form.querySelector('[type="submit"]');

        if (!fullName) {
          showMsg('Please enter your full name.', 'error');
          form.elements['fullName']?.focus();
          return;
        }
        if (!email) {
          showMsg('Please enter your email address.', 'error');
          form.elements['email']?.focus();
          return;
        }
        if (!isValidEmail(email)) {
          showMsg('Please enter a valid email address.', 'error');
          form.elements['email']?.focus();
          return;
        }
        if (!idFile) {
          showMsg('Please upload a copy of your ID for verification.', 'error');
          return;
        }

        if (!isCodeSent) {
          // STEP 1: Send OTP
          await withButtonLoading(submitBtn, async () => {
             console.log('[Supabase] Requesting OTP for signup:', email);
             const { error } = await supabase.auth.signInWithOtp({ 
                email, 
                options: { shouldCreateUser: true } 
             });

             if (error) {
               showMsg(error.message || 'Failed to send verification code. Please try again.', 'error');
               return;
             }

             // Successfully sent
             showMsg('A 6-digit verification code has been sent to your email.', 'success');
             isCodeSent = true;
             
             // Update UI to ask for code
             form.elements['fullName'].disabled = true;
             form.elements['email'].disabled = true;
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

        // STEP 2: Verify OTP and Complete Profile
        if (!otpCode) {
          showMsg('Please enter the 6-digit verification code.', 'error');
          form.elements['otpCode']?.focus();
          return;
        }

        await withButtonLoading(submitBtn, async () => {
          showMsg('Verifying your code...', 'info');
          
          try {
            const { data, error } = await supabase.auth.verifyOtp({
              email,
              token: otpCode,
              type: 'email'
            });

            if (error) {
              showMsg(error.message || 'Invalid code. Please try again.', 'error');
              return;
            }

            if (data?.user) {
              showMsg('Code verified! Processing your account details...', 'info');

              // 1. Upload ID Image (Since we are now verified config session allows upload)
              let idUrl = null;
              try {
                const fileExt = idFile.name.split('.').pop();
                const fileName = `${data.user.id}-${Math.random().toString(36).substring(2)}.${fileExt}`;
                const filePath = `identities/${fileName}`;

                const { error: uploadError } = await supabase.storage
                  .from('identities')
                  .upload(filePath, idFile);

                if (uploadError) throw uploadError;

                const { data: { publicUrl } } = supabase.storage
                  .from('identities')
                  .getPublicUrl(filePath);
                  
                idUrl = publicUrl;
              } catch (upErr) {
                console.error('[Supabase] ID Upload failed:', upErr);
              }

              // 2. INSERT INTO PROFILES TABLE
              const { error: profileError } = await supabase
                .from('profiles')
                .upsert({
                  id: data.user.id,
                  full_name: fullName,
                  email: email,
                  role: 'staff', // Default role for Admin internal requests
                  status: 'Pending',
                  id_url: idUrl
                });

              if (profileError) {
                console.error('[Supabase] Profile creation failed:', profileError);
              }

              // Since they are technically logged in now, but pending approval, we sign them out immediately
              await supabase.auth.signOut();

              showMsg('Verification successful! Your account request has been submitted to the Barangay Admin.', 'success');
              if (window.showToast) showToast('Request submitted! Please wait for approval.', 'success');
              
              setTimeout(() => {
                window.location.href = 'login.php';
              }, 2500);

            } else {
              showMsg('Signup request completed. Please check your email.', 'info');
            }

          } catch (err) {
            console.error('[Supabase] signUp exception:', err);
            showMsg('An unexpected error occurred. Please try again.', 'error');
            showToast && showToast('Unexpected error during signup.', 'error');
          }
        });
      }

      function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      }
      function isValidPassword(password) {
        return typeof password === 'string' && password.length >= 8;
      }

      console.log('[Supabase] SDK version:', window.supabase?.version || 'unknown');
      console.log('[Supabase] Client ready:', !!window.supabaseClient);
    }

    function showAlreadyRegisteredModal(email, role) {
      const modal = document.getElementById('alertModal');
      const title = document.getElementById('alertTitle');
      const text = document.getElementById('alertText');

      if (!modal) return;

      title.textContent = "Account Found";
      text.innerHTML = `The email <strong>${email}</strong> is already registered as <strong>${role}</strong>. Please log in instead.`;

      modal.style.display = 'flex';
    }

    function closeModals() {
      document.querySelectorAll('.custom-modal').forEach(m => m.style.display = 'none');
    }
  </script>

  <!-- Custom Alert Modal -->
  <div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
    <div class="card-modal">
      <div id="alertIconCircle" class="modal-icon-circle icon-info">
        <i id="alertIcon" class="fas fa-user-check" aria-hidden="true"></i>
      </div>
      <h2 id="alertTitle" style="margin:0 0 8px; font-size:1.5rem;">Notification</h2>
      <p id="alertText" style="color:#64748b; margin:0; line-height:1.5;">Message content goes here.</p>
      <div class="modal-actions-custom" style="justify-content:center; margin-top:24px;">
        <button class="btn-confirm" onclick="window.location.href='login.php'" style="max-width:200px;">Go to
          Login</button>
      </div>
    </div>
  </div>
</body>

</html>