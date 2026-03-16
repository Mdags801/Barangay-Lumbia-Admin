<?php require_once __DIR__ . '/session_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Settings</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="settings.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
  <header>
    <h1>Admin Settings Panel</h1>
    <p>Configure system preferences and controls</p>
  </header>

  <div class="settings-panel">

    <!-- 1️⃣ Account & User Management -->
    <section class="settings-section">
      <h2><i class="fas fa-users-cog"></i> Account & User Management</h2>
      <div class="settings-content">
        <p style="font-size: 0.95rem; color: #64748b; margin-bottom: 20px;">Control access levels and verify new user accounts within the portal.</p>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <button class="btn primary" onclick="window.parent.postMessage({type:'redirect', page:'account'}, '*')">
            <i class="fas fa-users"></i> Account Management
          </button>
          <button class="btn" style="background: #f1f5f9; color: #475569;" onclick="window.parent.postMessage({type:'redirect', page:'requests'}, '*')">
            <i class="fas fa-user-plus"></i> Verification Requests
          </button>
        </div>
      </div>
    </section>

    <!-- 2️⃣ Emergency Category Settings -->
    <section class="settings-section">
      <h2><i class="fas fa-list-alt"></i> Emergency Categories</h2>
      <div class="settings-content">
        <p style="font-size: 0.95rem; color: #64748b; margin-bottom: 20px;">Manage types of emergencies, responders, and reporting options.</p>
        <button class="btn primary" onclick="window.parent.postMessage({type:'redirect', page:'app_manager'}, '*')">
          <i class="fas fa-sliders-h"></i> Go to App Manager
        </button>
      </div>
    </section>

    <!-- 3️⃣ Notification & Alert Settings -->
    <section class="settings-section">
      <h2><i class="fas fa-bell"></i> Notifications & Alerts</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 16px;">
        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-weight: 600;">
          <input type="checkbox" id="soundToggle" style="width: 20px; height: 20px;"> Enable Sound Alerts
        </label>
        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-weight: 600;">
          <input type="checkbox" id="repeatToggle" style="width: 20px; height: 20px;"> Repeat Alerts for Unresolved Incidents
        </label>
        <div style="display: flex; align-items: center; gap: 16px;">
           <span style="font-size: 0.95rem; font-weight: 500;">Alert Interval (minutes):</span>
           <input type="number" id="intervalInput" style="width: 80px; padding: 10px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.1); background: #f8fafc; font-weight: 600; text-align: center;">
        </div>
      </div>
    </section>

    <!-- 4️⃣ Location & Map Settings -->
    <section class="settings-section">
      <h2><i class="fas fa-map-marker-alt"></i> Location & Map</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <span style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Default Barangay Location</span>
          <input type="text" id="defaultLocation" placeholder="Enter coordinates or address" style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.1); background: #f8fafc; outline: none; font-size: 0.95rem;">
        </div>
        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-weight: 600;">
          <input type="checkbox" id="gpsToggle" style="width: 20px; height: 20px;"> Enable GPS Validation
        </label>
      </div>
    </section>

    <!-- 5️⃣ Data & Records Management -->
    <section class="settings-section">
      <h2><i class="fas fa-database"></i> Data & Records</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 16px;">
           <span style="font-size: 0.95rem; font-weight: 500;">Retention Period (months):</span>
           <input type="number" id="retentionInput" style="width: 80px; padding: 10px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.1); background: #f8fafc; font-weight: 600; text-align: center;">
        </div>
        <div style="display: flex; gap: 12px; margin-top: 8px;">
          <button class="btn export" onclick="window.parent.postMessage({type:'redirect', page:'reports'}, '*')">
            <i class="fas fa-history"></i> Reporting History
          </button>
          <button class="btn save" onclick="window.parent.postMessage({type:'redirect', page:'archive'}, '*')">
            <i class="fas fa-archive"></i> Incident Archives
          </button>
        </div>
      </div>
    </section>

    <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; margin-top: 20px; padding-bottom: 40px;">
      <button class="btn primary" style="padding: 16px 40px; border-radius: 16px; font-size: 1rem; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);" onclick="alert('Settings feature optimized for next update.')">
        <i class="fas fa-check-circle"></i> Save All Changes
      </button>
    </div>

  </div>

  <script src="settings.js?v=<?php echo time(); ?>" defer></script>
</body>

</html>
