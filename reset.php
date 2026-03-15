<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Reset Password — Barangay Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Supabase SDK -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        window.supabaseClient = supabase.createClient(
            "https://tukkkwtxuaxrbihyammp.supabase.co",
            "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P",
            { auth: { persistSession: true, autoRefreshToken: true, detectSessionInUrl: true } }
        );
    </script>

    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 24px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .hidden {
            display: none !important;
        }

        .status-card {
            text-align: center;
            padding: 40px 20px;
        }

        .status-icon {
            font-size: 3rem;
            margin-bottom: 16px;
        }

        .status-success {
            color: #10b981;
        }
    </style>
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

                <!-- NEW PASSWORD STEP (Hidden by default, shown if recovery token present) -->
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

                <!-- SUCCESS MESSAGE -->
                <div id="successStep" class="hidden status-card">
                    <div class="status-icon status-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 id="successTitle">Check your email</h2>
                    <p id="successMessage" style="color:#64748b; margin-top:8px;">We've sent a password reset link to
                        your email address.</p>
                    <div class="actions" style="margin-top:24px;">
                        <button onclick="window.location.href='login.php'" class="btn-ghost" style="width:100%">Return
                            to Login</button>
                    </div>
                </div>

                <div id="msg" style="margin-top:16px; font-size:0.9rem; text-align:center;"></div>
            </section>

            <aside class="auth-visual">
                <div>
                    <div style="font-weight:800; font-size:1.2rem; margin-bottom:12px;">Security First</div>
                    <p style="opacity:0.9; font-size:0.95rem;">Resetting your password helps keep your barangay data
                        safe and secure.</p>
                </div>
                <div class="small" style="color:rgba(255,255,255,0.7)">Barangay Based Emergency Response System</div>
            </aside>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const supabase = window.supabaseClient;
            const requestStep = document.getElementById('requestStep');
            const resetStep = document.getElementById('resetStep');
            const successStep = document.getElementById('successStep');
            const msgBox = document.getElementById('msg');

            function showMsg(text, isError = false) {
                msgBox.textContent = text;
                msgBox.className = isError ? 'error' : 'success';
            }

            // 0. DETECT RECOVERY EVENT (Official Supabase Method)
            supabase.auth.onAuthStateChange(async (event, session) => {
                if (event === "PASSWORD_RECOVERY") {
                    console.log("[Auth] Password recovery event detected");
                    requestStep.classList.add('hidden');
                    resetStep.classList.remove('hidden');
                    successStep.classList.add('hidden');
                    msgBox.textContent = ""; // Clear any old messages
                }
            });

            // Fallback for immediate hash detection
            if (window.location.hash && window.location.hash.includes('type=recovery')) {
                requestStep.classList.add('hidden');
                resetStep.classList.remove('hidden');
            }

            // 1. SEND RESET LINK
            document.getElementById('sendBtn').onclick = async () => {
                const email = document.getElementById('email').value.trim();
                if (!email) return showMsg('Please enter your email', true);

                const btn = document.getElementById('sendBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

                const resetPageUrl = window.location.href.split('?')[0].split('#')[0];
                console.log("[Auth] Sending reset link with redirectTo:", resetPageUrl);

                // Show a small diagnostic alert before sending (Temporary)
                alert("Requesting reset link. Supabase will be asked to return to: " + resetPageUrl);

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

            // 2. UPDATE PASSWORD
            document.getElementById('resetBtn').onclick = async () => {
                const newPass = document.getElementById('newPassword').value;
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
                    document.getElementById('successMessage').textContent = 'Your password has been successfully changed. You can now log in.';
                }
            };
        });
    </script>
</body>

</html>