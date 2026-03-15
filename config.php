<?php
/**
 * config.php — Server-side only. Never exposed to the browser.
 * All Supabase credentials live here, away from the front-end JS.
 */

// ─── Supabase ────────────────────────────────────────────────────────────────
define('SUPABASE_URL',         'https://tukkkwtxuaxrbihyammp.supabase.co');
define('SUPABASE_ANON_KEY',    'sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P');
define('SUPABASE_SERVICE_KEY', '');   // <-- Fill in your service_role key for admin operations

// ─── Session ─────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME',  60 * 60 * 8);   // 8 hours
define('SESSION_COOKIE_SECURE',  false);     // Set TRUE when served over HTTPS
define('SESSION_COOKIE_SAMESITE', 'Strict');

// ─── Allowed admin roles ──────────────────────────────────────────────────────
define('ALLOWED_ROLES', ['admin', 'super admin', 'staff']);
