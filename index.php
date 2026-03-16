<?php 
// Portal Version 9.0.1 - Vercel Fix
require_once __DIR__ . '/session_guard.php'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="google-site-verification" content="1mr7y1ebeS_hMMdy0HnzeZaQR2RQA_xGs28q91IXtPE" />
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Barangay Admin Portal</title>
  <!-- Google Fonts for modern typography -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Supabase JS SDK -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2.39.7/dist/umd/supabase.min.js"
    crossorigin="anonymous"></script>
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- Favicon (Data URI to prevent 404) -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
</head>

<body>
  <!-- ===== GLOBAL INCIDENT ALARM BANNER ===== -->
  <div id="alarmBanner" class="alarm-banner" role="alert" aria-live="assertive" aria-atomic="true" aria-hidden="true">
    <div class="alarm-banner-inner">
      <div class="alarm-icon-wrap">
        <span class="alarm-siren-icon" aria-hidden="true">🚨</span>
      </div>
      <div class="alarm-body">
        <strong class="alarm-title">NEW INCIDENT REPORTED!</strong>
        <span id="alarmMessage" class="alarm-msg">A citizen has submitted an emergency report.</span>
      </div>
      <div class="alarm-actions">
        <span id="alarmQueueBadge" class="alarm-queue-badge" style="display:none;"></span>
        <button id="alarmStopSirenBtn" class="alarm-stop-btn" title="Stop Sound" aria-label="Stop audio alert">
          <i class="fas fa-volume-mute" aria-hidden="true"></i> STOP SIREN
        </button>
        <button id="alarmDismissBtn" class="alarm-dismiss-btn" title="Dismiss alert" aria-label="Dismiss alert">
          <i class="fas fa-times" aria-hidden="true"></i>
        </button>
      </div>
    </div>
  </div>
  <!-- ===== END ALARM BANNER ===== -->
  <div class="container">
    <!-- Sidebar Navigation -->
    <nav aria-label="Sidebar" id="sidebar">
      <div class="sidebar-header" aria-label="Barangay Admin Portal">
        <i class="fas fa-shield-alt" aria-hidden="true"></i>
        <span style="margin-left:8px;">Barangay Lumbia</span>
      </div>
      <div class="sidebar-nav" role="navigation" aria-label="Main Navigation">
        <a href="#" class="sidebar-link" data-page="dashboard" id="nav-dashboard" aria-current="page">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
        </a>
        <a href="#" class="sidebar-link" data-page="incident" id="nav-incident">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Incident</span>
        </a>
        <a href="#" class="sidebar-link" data-page="reports" id="nav-reports">
          <i class="fas fa-file-alt"></i>
          <span>Reports</span>
        </a>
        <a href="#" class="sidebar-link" data-page="account" id="nav-account">
          <i class="fas fa-users-cog"></i>
          <span>Account Management</span>
        </a>
        <a href="#" class="sidebar-link" data-page="app_manager" id="nav-app_manager">
          <i class="fas fa-cogs"></i>
          <span>App Manager</span>
        </a>
        <a href="#" class="sidebar-link" data-page="requests" id="nav-requests">
          <i class="fas fa-user-plus"></i>
          <span>Account Requests</span>
          <span id="requests-badge" class="requests-badge" style="display:none;">0</span>
        </a>
        <a href="#" class="sidebar-link" data-page="archive" id="nav-archive">
          <i class="fas fa-archive"></i>
          <span>Archive</span>
        </a>

      </div>
      <div class="sidebar-footer" aria-label="Sidebar Footer">
        &copy; <span id="sidebar-year"></span> Barangay Portal
      </div>
    </nav>

    <!-- Main Content Area -->
    <div style="flex:1 1 auto; display:flex; flex-direction:column;">
      <!-- Topbar -->
      <header role="banner" id="topbar">
        <div class="topbar-left">
          <span class="topbar-title" id="topbar-title">Dashboard</span>
          <span class="topbar-subtitle" id="topbar-subtitle">Overview &amp; quick stats</span>
        </div>
        <div class="topbar-right">
          <span class="live-clock" id="live-clock" aria-live="polite" aria-label="Current time"></span>
          <span class="user-info" id="user-info" style="display:none;">
            <i class="fas fa-user-circle" aria-hidden="true"></i>
            <span class="user-email" id="user-email"></span>
          </span>
          <div class="auth-buttons" id="auth-buttons">
            <button class="btn" id="sign-in-btn" style="display:none;">
              <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
            <button class="btn secondary" id="sign-out-btn" style="display:none;">
              <i class="fas fa-sign-out-alt"></i> Sign Out
            </button>
          </div>
        </div>
      </header>
      <!-- Iframe Content -->
      <main>
        <iframe id="main-iframe" class="main-iframe" title="Main Content" src="Dashboard.php" tabindex="0"
          aria-label="Main Content Area"></iframe>
      </main>
    </div>
  </div>

  <!-- Standardized Logout Confirmation Modal -->
  <div id="logoutModal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="logoutTitle">
    <div class="card-modal">
      <div class="modal-icon-circle icon-danger"><i class="fas fa-sign-out-alt"></i></div>
      <h2 id="logoutTitle" style="margin:0 0 12px; font-size:1.6rem; font-weight:900; letter-spacing:-0.01em;">Sign Out?</h2>
      <p style="color:#64748b; margin:0 0 28px; line-height:1.5;">Are you sure you want to end your active session and disconnect from the portal?</p>
      <div class="modal-actions">
        <button class="btn-cancel" id="cancelLogout">Stay Connected</button>
        <button class="btn-confirm" id="confirmLogout">Sign Out</button>
      </div>
    </div>
  </div>

  <!-- Standardized bridge for PHP session into the JS layer -->
  <script>
    window.PHP_SESSION = {
      access_token: <?php echo json_encode($_SESSION['access_token'] ?? ''); ?>,
      user_id:      <?php echo json_encode($_SESSION['user_id']     ?? ''); ?>,
      email:        <?php echo json_encode($_SESSION['email']       ?? ''); ?>,
      role:         <?php echo json_encode($_SESSION['role']        ?? ''); ?>,
      full_name:    <?php echo json_encode($_SESSION['full_name']   ?? ''); ?>
    };
  </script>
  <script src="portal_v9.js?v=<?php echo time(); ?>"></script>

  <!-- ===== ACTIVE USERS FLOATING BUTTON ===== -->
  <button id="activeUsersBtn" class="active-users-fab" aria-label="Show active users" title="Active Users">
    <i class="fas fa-users" aria-hidden="true"></i>
    <span id="activeUsersBadge" class="active-users-badge">0</span>
  </button>

  <!-- ===== ACTIVE USERS DRAWER ===== -->
  <div id="activeUsersDrawer" class="active-drawer" role="dialog" aria-label="Active users" style="visibility: hidden;">
    <div class="active-drawer-header">
      <div class="active-drawer-title">
        <span class="live-dot-pulse"></span>
        <h3 style="font-weight: 800;">Real-time Presence</h3>
        <span id="activeUsersCount" class="active-drawer-count">0</span>
      </div>
      <div id="presenceStatus" style="font-size: 0.65rem; color: #64748b; font-weight: 700; background: #f1f5f9; padding: 4px 10px; border-radius: 12px; display: flex; align-items:center; gap:6px; margin-left:12px; text-transform:uppercase; letter-spacing:0.02em;">
         <i id="presenceStatusIcon" class="fas fa-circle" style="font-size:0.55rem; color:#f59e0b; display: inline-block; flex-shrink: 0;"></i>
         <span id="presenceStatusText">Connecting...</span>
      </div>
      <button id="closeActiveDrawer" class="active-drawer-close" aria-label="Close panel">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; background: #fafafa;">
      <p style="margin:0 0 12px; font-size: 0.75rem; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em;">Connected Personnel</p>
      <div class="search-wrap" style="position:relative;">
        <i class="fas fa-search" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:0.85rem; color:#94a3b8;"></i>
        <input type="text" id="presenceSearch" placeholder="Find user..." 
          style="width:100%; padding:10px 14px 10px 38px; border-radius:12px; border:1px solid #e2e8f0; font-size:0.9rem; outline:none; font-family:inherit; font-weight:500;" />
      </div>
    </div>

    <div id="activeUsersList" class="active-users-list">
      <div class="active-empty-state">
        <i class="fas fa-wifi" style="font-size:2.5rem; opacity:.15; margin-bottom:12px;"></i>
        <p style="font-weight:600; color:#64748b;">No active users yet...</p>
      </div>
    </div>
  </div>
  <div id="activeDrawerOverlay" class="active-drawer-overlay"></div>

  <!-- ===== GLOBAL INCIDENT DETAILS MODAL (Premium Landscape) ===== -->
  <div id="globalIncidentModal" class="custom-modal" role="dialog" aria-modal="true" aria-hidden="true" style="z-index: 20000;">
    <div class="card-modal" style="width: 850px; max-width: 95vw; padding: 0; overflow: hidden; display: flex; flex-direction: row; border-radius: 28px;">
      <!-- Left Column: Primary Details -->
      <div style="flex: 1; padding: 40px; text-align: left; border-right: 1px solid #f1f5f9;">
         <div id="globalModalHeader"></div>
         <div id="globalModalMeta" style="margin-bottom: 32px;"></div>
         <div id="globalModalDescription" style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0; min-height: 100px;"></div>
      </div>

      <!-- Right Column: Visual & Actions -->
      <div style="width: 360px; background: #f8fafc; padding: 40px; display: flex; flex-direction: column;">
         <div id="globalIncidentMap" style="height: 220px; border-radius: 20px; border: 2px solid #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-bottom: 32px;"></div>
         <div id="globalModalFooter" style="margin-top: auto; display: flex; flex-direction: column; gap: 14px;">
            <button class="btn-confirm" style="width: 100%; padding: 18px; font-weight: 800;" onclick="closeGlobalIncidentModal()">Dismiss Window</button>
            <button class="btn-cancel" style="width: 100%; padding: 16px; background: white; font-weight: 700;" onclick="loadPage('incident'); closeGlobalIncidentModal();">
              <i class="fas fa-external-link-alt" style="margin-right:8px;"></i> Open Full Panel
            </button>
         </div>
      </div>
    </div>
  </div>

  <div id="toastContainer" class="toast-container"></div>
</body>

</html>

</body>

</html>