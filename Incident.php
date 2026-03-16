<?php require_once __DIR__ . '/session_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Incident Management | Barangay Emergency System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="incident.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
</head>

<body>
  <header>
    <div class="header-left">
      <h1>Incident Manager</h1>
      <p>Monitor and respond to emergencies in real-time</p>
    </div>
    <div class="header-right">
      <button class="btn create-btn" id="openReportBtn">
        <i class="fas fa-plus"></i> Manual Report
      </button>
    </div>
  </header>

  <div class="controls">
    <div class="left">
      <div>
        <input id="searchInput" type="text" placeholder="Search by ID, reporter, type, or location" />
      </div>
      <select id="sortSelect" title="Sort">
        <option value="newest">Newest First</option>
        <option value="oldest">Oldest First</option>
      </select>
      <nav class="status-filters" role="navigation" aria-label="Filter incidents by status">
        <button class="filter-btn active" data-status="all">All</button>
        <button class="filter-btn" data-status="Pending">Pending</button>
        <button class="filter-btn" data-status="EnRoute">En-route</button>
        <button class="filter-btn" data-status="Resolved">Resolved</button>
      </nav>
    </div>
  </div>

  <div class="table-wrap" aria-live="polite">
    <table class="incident-table" role="table" aria-label="Incident reports table">
      <thead>
        <tr>
          <th>Type <i class="fas fa-sort" style="font-size: 0.7rem; opacity: 0.5;"></i></th>
          <th>ID</th>
          <th>Location</th>
          <th>Time</th>
          <th>Reporter</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="incidentTableBody">
        <tr>
          <td colspan="6" style="padding:40px; text-align:center;">
            <i class="fas fa-spinner fa-spin" style="font-size:2rem; color:var(--primary); margin-bottom:12px;"></i>
            <p style="color:var(--text-muted);">Synchronizing with Supabase...</p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div id="incidentModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content" style="max-width: 650px;">
      <span class="close" title="Close" id="closeViewModal"><i class="fas fa-times"></i></span>
      <div id="modalDetails"></div>
      <div id="incidentMap" style="height: 300px; border-radius: 18px; border: 1px solid #e2e8f0; margin-bottom: 24px;"></div>
      <div class="modal-actions">
        <button class="btn resolve-btn btn-confirm" style="flex:1;">Mark as Resolved</button>
        <button id="openAgencyModalBtn" class="btn notify-btn btn-cancel" style="flex:1;"><i class="fas fa-bullhorn"></i> Notify Responders</button>
      </div>
      <div style="margin-top: 12px;">
        <button class="btn archive-btn" style="width: 100%; background: transparent; color: #94a3b8; font-size: 0.8rem;">Archive Incident</button>
      </div>
    </div>
  </div>

  <!-- Modal: Create Incident (Manual Log) -->
  <div id="reportModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content" style="max-width: 550px;">
      <span class="close" title="Close" id="closeReportModal"><i class="fas fa-times"></i></span>
      <h2 style="font-size: 1.5rem; margin-bottom: 8px; font-weight: 800;"><i class="fas fa-plus-circle" style="color: var(--primary);"></i> Manual Incident Report</h2>
      <p style="color:var(--muted); font-size: 0.9rem; margin-bottom: 20px;">Use this to log reports from phone calls or walk-ins.</p>
      <form id="reportForm">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
          <div>
            <label style="display:block; font-weight:700; font-size: 0.8rem; text-transform: uppercase; color: #64748b; margin-bottom:6px;">Incident Type</label>
            <select id="newType" required
              style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0; background: #f8fafc; font-weight: 600;">
              <option value="Fire">Fire</option>
              <option value="Medical">Medical</option>
              <option value="Police">Police</option>
              <option value="Rescue">Rescue</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div>
            <label style="display:block; font-weight:700; font-size: 0.8rem; text-transform: uppercase; color: #64748b; margin-bottom:6px;">Reporter Name</label>
            <input type="text" id="newReporter" placeholder="Walk-in / Phone Call"
              style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0; background: #f8fafc; font-weight: 600;">
          </div>
        </div>
        <div style="margin-top:16px;">
          <label style="display:block; font-weight:700; font-size: 0.8rem; text-transform: uppercase; color: #64748b; margin-bottom:6px;">Location / Address</label>
          <input type="text" id="newLocation" required placeholder="Street name, Barangay..."
            style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0; background: #f8fafc; font-weight: 600;">
        </div>
        <div style="margin-top:16px;">
          <label style="display:block; font-weight:700; font-size: 0.8rem; text-transform: uppercase; color: #64748b; margin-bottom:6px;">Description</label>
          <textarea id="newDescription" rows="3" placeholder="Additional details..."
            style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0; background: #f8fafc; font-weight: 600; resize:none;"></textarea>
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:16px;">
          <div>
            <label style="display:block; font-weight:700; font-size: 0.8rem; text-transform: uppercase; color: #64748b; margin-bottom:6px;">Latitude (Optional)</label>
            <input type="number" step="any" id="newLat" placeholder="8.4833"
              style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0; background: #f8fafc; font-weight: 600;">
          </div>
          <div>
            <label style="display:block; font-weight:700; font-size: 0.8rem; text-transform: uppercase; color: #64748b; margin-bottom:6px;">Longitude (Optional)</label>
            <input type="number" step="any" id="newLng" placeholder="124.6500"
              style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0; background: #f8fafc; font-weight: 600;">
          </div>
        </div>
        <div style="margin-top:32px; display: flex; gap: 12px;">
          <button type="button" class="btn-cancel" style="flex:1;" onclick="document.getElementById('closeReportModal').click()">Cancel</button>
          <button type="submit" class="btn-confirm" id="submitReportBtn" style="flex:1;">Submit Report</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Notify Responders -->
  <div id="agencyModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content agency-selector" style="max-width: 450px;">
      <span class="close" title="Close" id="closeAgencyModal"><i class="fas fa-times"></i></span>
      <h2 style="font-size: 1.5rem; margin-bottom: 24px; font-weight: 800;"><i class="fas fa-bullhorn" style="color: var(--primary);"></i> Notify & Dispatch</h2>
      <div class="agency-grid">
        <div class="agency-card selected" data-agency="Responders">
          <i class="fas fa-users-rectangle"></i>
          <span>Bgy Responders</span>
          <small style="font-size: 0.75rem; color: #1e40af; font-weight: 800; opacity: 0.8;">(Personnel App)</small>
        </div>
      </div>

      <div style="margin-top: 24px; padding: 16px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
        <h3 style="font-size: 0.85rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; color: #64748b;">
          <i class="fas fa-history" style="margin-right: 6px;"></i> Dispatch History
        </h3>
        <div id="historyList" style="font-size: 0.9rem; color: #475569;">No alerts sent yet.</div>
      </div>

      <div class="modal-actions" style="margin-top: 32px;">
        <button class="btn-cancel" style="flex:1;" onclick="document.getElementById('closeAgencyModal').click()">Dismiss</button>
        <button class="btn-confirm" id="confirmDispatchBtn" style="flex:2;">Send Live Alerts</button>
      </div>
    </div>
  </div>


  <!-- Custom Confirmation Modal -->
  <div id="confirmModal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="card-modal">
      <div id="confirmIconCircle" class="modal-icon-circle icon-warning"><i id="confirmIcon" class="fas fa-question"
          aria-hidden="true"></i></div>
      <h2 id="confirmTitle" style="margin:0 0 12px; font-size:1.6rem; font-weight: 900; letter-spacing:-0.01em;">Confirm Action</h2>
      <p id="confirmText" style="color:#64748b; margin:0 0 28px; line-height:1.5;">Are you sure you want to proceed?</p>
      <div class="modal-actions">
        <button class="btn-cancel" id="confirmCancelBtn">Cancel</button>
        <button class="btn-confirm" id="confirmOkBtn">Confirm</button>
      </div>
    </div>
  </div>

  <!-- Custom Alert Modal -->
  <div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
    <div class="card-modal">
      <div id="alertIconCircle" class="modal-icon-circle icon-info"><i id="alertIcon" class="fas fa-info-circle"
          aria-hidden="true"></i></div>
      <h2 id="alertTitle" style="margin:0 0 12px; font-size:1.6rem; font-weight: 900; letter-spacing:-0.01em;">Notification</h2>
      <p id="alertText" style="color:#64748b; margin:0 0 28px; line-height:1.5;">Message content goes here.</p>
      <div class="modal-actions">
        <button class="btn-confirm" onclick="closeModals()">Understood</button>
      </div>
    </div>
  </div>


  <script src="incident.js?v=<?php echo time(); ?>"></script>
</body>

</html>
