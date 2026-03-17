<?php
/**
 * config.php — Server-side only. Never exposed to the browser.
 * All Supabase credentials live here, away from the front-end JS.
 *
 * Security features configured here:
 *  - Supabase credentials (BaaS backend connectivity)
 *  - Session hardening (HttpOnly, Secure, SameSite cookies)
 *  - CSRF secret for token-based request validation
 *  - Security response headers (Clickjack / Code-injection defence)
 *  - RBAC role whitelist
 */

// ─── Supabase ────────────────────────────────────────────────────────────────
define('SUPABASE_URL',         getenv('SUPABASE_URL')         ?: 'https://tukkkwtxuaxrbihyammp.supabase.co');
define('SUPABASE_ANON_KEY',    getenv('SUPABASE_ANON_KEY')    ?: 'sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P');
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY') ?: '');

// ─── Session ─────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME',       (int)(getenv('SESSION_LIFETIME') ?: 28800)); // 8 hours
define('SESSION_COOKIE_SECURE',  getenv('SESSION_COOKIE_SECURE') === 'true' || !!getenv('VERCEL'));
define('SESSION_COOKIE_SAMESITE', getenv('SESSION_COOKIE_SAMESITE') ?: 'Lax');
define('AUTH_COOKIE_NAME',       'sb_portal_token');

// ─── CSRF ─────────────────────────────────────────────────────────────────────
// Used by api/csrf.php to sign session-bound CSRF tokens.
// Override via environment variable in production.
define('CSRF_SECRET', getenv('CSRF_SECRET') ?: 'bbers_csrf_fallback_secret_change_me');

// ─── Allowed admin roles ──────────────────────────────────────────────────────
define('ALLOWED_ROLES', ['admin', 'super admin', 'staff']);

// ─── Security Response Headers ────────────────────────────────────────────────
/**
 * apply_security_headers()
 * Call at the top of any PHP page or API endpoint to apply
 * anti-clickjack, XSS-sniff, and code-injection defence headers.
 *
 * Note: .htaccess also applies these globally, but calling this function
 * ensures headers are set even in environments where mod_headers is absent
 * (e.g., PHP built-in server, Vercel serverless).
 */
function apply_security_headers(): void {
    // Clickjacking defence
    header('X-Frame-Options: DENY');

    // Prevent MIME-type sniffing (XSS vector)
    header('X-Content-Type-Options: nosniff');

    // Enable browser XSS filter (legacy, harmless)
    header('X-XSS-Protection: 1; mode=block');

    // Referrer policy — limit information leakage
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Remove server fingerprint
    header_remove('X-Powered-By');
    header_remove('Server');
}
