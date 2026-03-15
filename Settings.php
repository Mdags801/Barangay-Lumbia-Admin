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
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 12px;">Managed via the <strong>Account Management</strong> tab.</p>
        <button class="btn" onclick="window.parent.postMessage({type:'redirect', page:'account'}, '*')">Go to Account Management</button>
      </div>
    </section>

    <!-- 2️⃣ Emergency Category Settings -->
    <section class="settings-section">
      <h2><i class="fas fa-list-alt"></i> Emergency Categories</h2>
      <div class="settings-content">
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 12px;">Managed via the <strong>App Manager</strong> tab.</p>
        <button class="btn" onclick="window.parent.postMessage({type:'redirect', page:'app_manager'}, '*')">Go to App Manager</button>
      </div>
    </section>

    <!-- 3️⃣ Notification & Alert Settings -->
    <section class="settings-section">
      <h2><i class="fas fa-bell"></i> Notifications & Alerts</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 12px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" id="soundToggle"> Enable Sound Alerts
        </label>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" id="repeatToggle"> Repeat Alerts for Unresolved Incidents
        </label>
        <div style="display: flex; align-items: center; gap: 12px;">
           <span style="font-size: 0.9rem;">Alert Interval (minutes):</span>
           <input type="number" id="intervalInput" style="width: 60px; padding: 4px 8px; border-radius: 4px; border: 1px solid #d1d5db;">
        </div>
      </div>
    </section>

    <!-- 4️⃣ Location & Map Settings -->
    <section class="settings-section">
      <h2><i class="fas fa-map-marker-alt"></i> Location & Map</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 12px;">
        <input type="text" id="defaultLocation" placeholder="Default Barangay Location" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #d1d5db;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" id="gpsToggle"> Enable GPS Validation
        </label>
      </div>
    </section>

    <!-- 6️⃣ Data & Records Management -->
    <section class="settings-section">
      <h2><i class="fas fa-database"></i> Data & Records</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
           <span style="font-size: 0.9rem;">Retention Period (months):</span>
           <input type="number" id="retentionInput" style="width: 60px; padding: 4px 8px; border-radius: 4px; border: 1px solid #d1d5db;">
        </div>
        <div style="display: flex; gap: 12px;">
          <button class="btn export" onclick="window.parent.postMessage({type:'redirect', page:'reports'}, '*')">View All Reports</button>
          <button class="btn save" onclick="window.parent.postMessage({type:'redirect', page:'archive'}, '*')">View Archives</button>
        </div>
      </div>
    </section>

  </div>

  <script src="settings.js?v=<?php echo time(); ?>" defer></script>
</body>

</html>