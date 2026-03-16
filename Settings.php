<?php require_once __DIR__ . '/session_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Settings | Barangay Emergency System</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="settings.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body>
  <header>
    <div style="display: flex; flex-direction: column;">
      <h1 style="font-weight: 900; letter-spacing: -0.04em; font-size: 2.4rem; margin: 0; background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">System Configuration</h1>
      <p style="font-weight: 500; font-size: 1.1rem; margin-top: 8px; color: #94a3b8;">Fine-tune operational parameters and administrative security controls</p>
    </div>
  </header>

  <div class="settings-panel">
    <!-- 1️⃣ Account & User Management -->
    <section class="settings-section">
      <h2 style="font-weight: 900; letter-spacing: -0.01em;"><i class="fas fa-users-cog" style="color: var(--primary);"></i> Identity & Roles</h2>
      <div class="settings-content">
        <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 24px; line-height: 1.6; font-weight: 500;">Manage administrative permissions, responder credentials, and verify citizen account requests.</p>
        <button class="btn-primary" style="width: 100%; border-radius: 16px; padding: 16px; font-weight: 800; font-size: 0.95rem; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);" onclick="window.parent.postMessage({type:'redirect', page:'account'}, '*')">Launch Member Management</button>
      </div>
    </section>

    <!-- 2️⃣ Emergency Category Settings -->
    <section class="settings-section">
      <h2 style="font-weight: 900; letter-spacing: -0.01em;"><i class="fas fa-layer-group" style="color: #f59e0b;"></i> Service Categories</h2>
      <div class="settings-content">
        <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 24px; line-height: 1.6; font-weight: 500;">Configure emergency types, thematic icons, and color coding for the live mobile app ecosystem.</p>
        <button class="btn-primary" style="width: 100%; border-radius: 16px; padding: 16px; font-weight: 800; font-size: 0.95rem; background: linear-gradient(135deg, #1e293b, #0f172a); border:none;" onclick="window.parent.postMessage({type:'redirect', page:'app_manager'}, '*')">Open Design Workspace</button>
      </div>
    </section>

    <!-- 3️⃣ Notification & Alert Settings -->
    <section class="settings-section">
      <h2 style="font-weight: 900; letter-spacing: -0.01em;"><i class="fas fa-satellite-dish" style="color: #ef4444;"></i> Transmission & Alerts</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 20px;">
        <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; font-weight: 700; font-size: 1rem; color: #334155; padding: 12px; background: #f8fafc; border-radius: 14px; border: 1px solid #f1f5f9;">
          <span>Auditory Feedback</span>
          <input type="checkbox" id="soundToggle" class="custom-checkbox">
        </label>
        <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; font-weight: 700; font-size: 1rem; color: #334155; padding: 12px; background: #f8fafc; border-radius: 14px; border: 1px solid #f1f5f9;">
          <span>Persistence Mode</span>
          <input type="checkbox" id="repeatToggle" class="custom-checkbox">
        </label>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; padding: 0 8px;">
           <span style="font-size: 0.95rem; font-weight: 800; color: #475569;">Broadcast Interval (min)</span>
           <input type="number" id="intervalInput" style="width: 70px; padding: 10px; border-radius: 12px; border: 2px solid #e2e8f0; font-weight: 900; background: white; text-align: center; color: var(--primary);">
        </div>
      </div>
    </section>

    <!-- 4️⃣ Location & Map Settings -->
    <section class="settings-section">
      <h2 style="font-weight: 900; letter-spacing: -0.01em;"><i class="fas fa-map-marked-alt" style="color: #10b981;"></i> Geospatial Controls</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 20px;">
        <div style="flex-direction: column; display: flex; gap: 10px;">
          <span style="font-size: 0.75rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em;">Operations Base Name</span>
          <input type="text" id="defaultLocation" placeholder="e.g. Barangay Lumbia Complex" style="width: 100%; padding: 14px; border-radius: 14px; border: 2px solid #e2e8f0; background: #fff; font-weight: 600; font-family: inherit;">
        </div>
        <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; font-weight: 700; font-size: 1rem; color: #334155; padding: 12px; background: #f8fafc; border-radius: 14px; border: 1px solid #f1f5f9;">
          <span>Precision GPS Fence</span>
          <input type="checkbox" id="gpsToggle" class="custom-checkbox">
        </label>
      </div>
    </section>

    <!-- 6️⃣ Data & Records Management -->
    <section class="settings-section">
      <h2 style="font-weight: 900; letter-spacing: -0.01em;"><i class="fas fa-vault" style="color: #6366f1;"></i> Data Governance</h2>
      <div class="settings-content" style="display: flex; flex-direction: column; gap: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 8px;">
           <span style="font-size: 0.95rem; font-weight: 800; color: #475569;">Retention Strategy (mo)</span>
           <input type="number" id="retentionInput" style="width: 70px; padding: 10px; border-radius: 12px; border: 2px solid #e2e8f0; font-weight: 900; background: white; text-align: center; color: var(--primary);">
        </div>
        <div style="display: flex; gap: 12px;">
          <button class="btn-primary" style="flex: 1; border-radius: 16px; padding: 14px; font-weight: 800; font-size: 0.85rem; background: white; border: 2px solid #e2e8f0; color: #64748b;" onclick="window.parent.postMessage({type:'redirect', page:'reports'}, '*')"><i class="fas fa-file-chart-column" style="margin-right:8px;"></i> View Reports</button>
          <button class="btn-primary" style="flex: 1; border-radius: 16px; padding: 14px; font-weight: 800; font-size: 0.85rem; background: white; border: 2px solid #e2e8f0; color: #64748b;" onclick="window.parent.postMessage({type:'redirect', page:'archive'}, '*')"><i class="fas fa-archive" style="margin-right:8px;"></i> Archives</button>
        </div>
      </div>
    </section>
  </div>

  <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>

  <!-- Standardized Alert Modal -->
  <div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
    <div class="card-modal" style="border-radius:32px; box-shadow: 0 30px 70px rgba(0,0,0,0.25);">
      <div id="alertIconCircle" class="modal-icon-circle icon-info"><i id="alertIcon" class="fas fa-check-circle" aria-hidden="true"></i></div>
      <h2 id="alertTitle" style="margin:0 0 12px; font-size:1.7rem; font-weight: 900; letter-spacing: -0.03em;">Config Synchronized</h2>
      <p id="alertText" style="color:#64748b; margin:0 0 32px; line-height:1.6; font-weight: 500;">System preferences have been propagated across all terminals successfully.</p>
      <div class="modal-actions">
        <button class="btn-confirm" onclick="closeModals()" style="width:100%; padding:18px; border-radius:18px; font-weight:900;">Acknowledge</button>
      </div>
    </div>
  </div>

  <script>
    function closeModals() {
      const modal = document.getElementById('alertModal');
      modal.style.opacity = '0';
      setTimeout(() => {
        modal.style.display = 'none';
        modal.style.opacity = '1';
      }, 200);
    }
  </script>
  <script src="settings.js?v=<?php echo time(); ?>" defer></script>
</body>

</html>
