<?php
/**
 * config.php — Server-side only. Never exposed to the browser.
 * All Supabase credentials live here, away from the front-end JS.
 */

// ─── Supabase ────────────────────────────────────────────────────────────────
define('SUPABASE_URL',         getenv('SUPABASE_URL')         ?: 'https://tukkkwtxuaxrbihyammp.supabase.co');
define('SUPABASE_ANON_KEY',    getenv('SUPABASE_ANON_KEY')    ?: 'sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P');
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY') ?: ''); 

// ─── Session ─────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME',  (int)(getenv('SESSION_LIFETIME')  ?: 28800)); // 8 hours default
define('SESSION_COOKIE_SECURE',  getenv('SESSION_COOKIE_SECURE') === 'true' || !!getenv('VERCEL')); 
define('SESSION_COOKIE_SAMESITE', getenv('SESSION_COOKIE_SAMESITE') ?: 'Lax');
define('AUTH_COOKIE_NAME',        'sb_portal_token');

// ─── Allowed admin roles ──────────────────────────────────────────────────────
define('ALLOWED_ROLES', ['admin', 'super admin', 'staff']);
