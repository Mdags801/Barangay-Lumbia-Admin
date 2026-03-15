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

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="logout-modal" style="display:none;">
    <div class="logout-card">
      <div class="logout-icon"><i class="fas fa-sign-out-alt"></i></div>
      <h2>Sign Out?</h2>
      <p>Are you sure you want to end your session?</p>
      <div class="logout-actions">
        <button class="btn-cancel" id="cancelLogout">Stay</button>
        <button class="btn-confirm" id="confirmLogout">Sign Out</button>
      </div>
    </div>
  </div>



  <!-- System Core (v9.1 - Final Stability) -->
  <script>
    // Bridge the PHP session into the JS layer (full user info)
    window.PHP_SESSION = {
      access_token: <?php echo json_encode($_SESSION['access_token'] ?? ''); ?>,
      user_id:      <?php echo json_encode($_SESSION['user_id']     ?? ''); ?>,
      email:        <?php echo json_encode($_SESSION['email']       ?? ''); ?>,
      role:         <?php echo json_encode($_SESSION['role']        ?? ''); ?>,
      full_name:    <?php echo json_encode($_SESSION['full_name']   ?? ''); ?>
    };
    console.log('[System] PHP Session:', window.PHP_SESSION.email || 'NO SESSION');
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
        <h3>Real-time Presence</h3>
        <span id="activeUsersCount" class="active-drawer-count">0</span>
      </div>
      <div id="presenceStatus" style="font-size: 0.65rem; color: #64748b; font-weight: 600; background: #f1f5f9; padding: 2px 8px; border-radius: 12px; display: flex; align-items:center; gap:4px; margin-left:12px;">
         <i id="presenceStatusIcon" class="fas fa-circle" style="font-size:0.5rem; color:#f59e0b;"></i>
         <span id="presenceStatusText">Connecting...</span>
      </div>
      <button id="closeActiveDrawer" class="active-drawer-close" aria-label="Close panel">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; background: #fafafa;">
      <p style="margin:0 0 12px; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Connected Personnel</p>
      <div class="search-wrap" style="position:relative;">
        <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:#94a3b8;"></i>
        <input type="text" id="presenceSearch" placeholder="Find user..." 
          style="width:100%; padding:8px 12px 8px 34px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.85rem; outline:none;" />
      </div>
    </div>

    <div id="activeUsersList" class="active-users-list">
      <div class="active-empty-state">
        <i class="fas fa-wifi" style="font-size:2rem;opacity:.3;"></i>
        <p>No active users yet...</p>
      </div>
    </div>
  </div>
  <div id="activeDrawerOverlay" class="active-drawer-overlay"></div>
  <!-- ===== END ACTIVE USERS ===== -->

  <!-- ===== GLOBAL INCIDENT DETAILS MODAL ===== -->
  <div id="globalIncidentModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true"
    style="z-index: 20000;">
    <div class="modal-content" style="max-width: 600px;">
      <span class="close" title="Close" onclick="closeGlobalIncidentModal()">&times;</span>
      <div id="globalModalDetails"></div>
      <div id="globalIncidentMap" style="height: 250px; border-radius: 12px; margin-top: 20px;"></div>
      <div class="modal-actions" style="margin-top: 30px;">
        <button class="btn-confirm" style="flex:1;" onclick="closeGlobalIncidentModal()">Close Details</button>
        <button class="btn-notify" style="background:#64748b;"
          onclick="loadPage('incident'); closeGlobalIncidentModal();">Manage in
          Portal</button>
      </div>
    </div>
  </div>

</body>

</html>