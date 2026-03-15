<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Incident Management | Barangay Emergency System</title>
  <meta name="description"
    content="Live management of emergency incidents. Track, dispatch, and resolve reports in real-time.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="incident.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  <!-- Supabase JS -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>



  <a href="#main-content" class="skip-link"
    style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;">Skip to
    content</a>

  <header role="banner">
    <h1>Incidents</h1>
    <p>Live incident tracking and response management system</p>
  </header>



  <main id="main-content">

    <section class="controls" aria-label="Incident Management Controls">
      <div class="left">
        <div style="position: relative; flex: 1; max-width: 400px;">
          <i class="fas fa-search" style="position: absolute; left: 16px; top: 14px; color: #94a3b8;"
            aria-hidden="true"></i>
          <input id="searchInput" type="text" placeholder="Search by ID, reporter, type, or location..."
            style="padding-left: 44px; width: 100%;" aria-label="Search incidents" />
        </div>
        <select id="sortSelect" title="Sort incidents" aria-label="Sort incidents by">
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
        </select>
        <nav class="status-filters" role="navigation" aria-label="Filter incidents by status">
          <button class="filter-btn active" data-status="all" aria-pressed="true">All</button>
          <button class="filter-btn" data-status="Pending" aria-pressed="false">Pending</button>
          <button class="filter-btn" data-status="Enroute" aria-pressed="false">Enroute</button>
          <button class="filter-btn" data-status="Resolved" aria-pressed="false">Resolved</button>
        </nav>
      </div>
      <div class="right">
        <button id="openReportBtn" class="btn create-btn" aria-haspopup="dialog"><i class="fas fa-plus-circle"
            aria-hidden="true"></i> File New Report</button>
      </div>
    </section>

    <div class="table-wrap" aria-live="polite">
      <table class="incident-table" role="table" aria-label="Incident table">
        <thead>
          <tr>
            <th>Type</th>
            <th>ID</th>
            <th>Location</th>
            <th>Time</th>
            <th>Reporter</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="incidentTableBody"></tbody>
      </table>
    </div>

    <!-- Modal: View Incident -->
    <div id="incidentModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
      <div class="modal-content">
        <span class="close" title="Close" id="closeViewModal">&times;</span>
        <div id="modalDetails"></div>
        <div id="incidentMap"></div>
        <div class="modal-actions">
          <div class="modal-actions-left">
            <button class="btn resolve-btn">Mark as Resolved</button>
            <button class="btn archive-btn">Archive Incident</button>
          </div>
          <button id="openAgencyModalBtn" class="btn notify-btn"><i class="fas fa-bullhorn"></i> Notify
            Responders</button>
        </div>
      </div>
    </div>

    <!-- Modal: Create Incident (Manual Log) -->
    <div id="reportModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
      <div class="modal-content">
        <span class="close" title="Close" id="closeReportModal">&times;</span>
        <h2><i class="fas fa-plus-circle"></i> Manual Incident Report</h2>
        <p style="color:var(--muted); font-size: 0.9rem; margin-bottom: 15px;">Use this to log reports from phone calls
          or
          walk-ins.</p>
        <form id="reportForm">
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-top:12px;">
            <div>
              <label style="display:block; font-weight:600; margin-bottom:4px;">Incident Type</label>
              <select id="newType" required
                style="width:100%; padding:8px; border-radius:6px; border:1px solid #d1d5db;">
                <option value="Fire">Fire</option>
                <option value="Medical">Medical</option>
                <option value="Police">Police</option>
                <option value="Rescue">Rescue</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label style="display:block; font-weight:600; margin-bottom:4px;">Reporter Name</label>
              <input type="text" id="newReporter" placeholder="Walk-in / Phone Call"
                style="width:100%; padding:8px; border-radius:6px; border:1px solid #d1d5db;">
            </div>
          </div>
          <div style="margin-top:12px;">
            <label style="display:block; font-weight:600; margin-bottom:4px;">Location / Address</label>
            <input type="text" id="newLocation" required placeholder="Street name, Barangay..."
              style="width:100%; padding:8px; border-radius:6px; border:1px solid #d1d5db;">
          </div>
          <div style="margin-top:12px;">
            <label style="display:block; font-weight:600; margin-bottom:4px;">Description</label>
            <textarea id="newDescription" rows="3" placeholder="Additional details..."
              style="width:100%; padding:8px; border-radius:6px; border:1px solid #d1d5db; resize:none;"></textarea>
          </div>
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-top:12px;">
            <div>
              <label style="display:block; font-weight:600; margin-bottom:4px;">Latitude</label>
              <input type="number" step="any" id="newLat" placeholder="14.5995"
                style="width:100%; padding:8px; border-radius:6px; border:1px solid #d1d5db;">
            </div>
            <div>
              <label style="display:block; font-weight:600; margin-bottom:4px;">Longitude</label>
              <input type="number" step="any" id="newLng" placeholder="120.9842"
                style="width:100%; padding:8px; border-radius:6px; border:1px solid #d1d5db;">
            </div>
          </div>
          <div style="margin-top:18px; text-align:right;">
            <button type="submit" class="btn resolve-btn" id="submitReportBtn">Submit Report</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Notify Responders -->
    <div id="agencyModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
      <div class="modal-content agency-selector">
        <span class="close" title="Close" id="closeAgencyModal">&times;</span>
        <h2><i class="fas fa-bullhorn"></i> Notify & Dispatch Responders</h2>
        <p style="color: #64748b; margin-bottom: 20px;">Choose who to notify for this emergency.</p>

        <div class="agency-grid">
          <div class="agency-card" data-agency="PNP">
            <i class="fas fa-shield-halved"></i>
            <span>PNP (Police)</span>
          </div>
          <div class="agency-card" data-agency="BFP">
            <i class="fas fa-fire-flame-curved"></i>
            <span>BFP (Fire)</span>
          </div>
          <div class="agency-card" data-agency="Medical">
            <i class="fas fa-ambulance"></i>
            <span>Ambulance/911</span>
          </div>
          <div class="agency-card" data-agency="Responders">
            <i class="fas fa-users-rectangle"></i>
            <span>Bgy Responders</span>
            <small style="font-size: 0.7rem; color: var(--accent); font-weight: 700;">(Alert App)</small>
          </div>
        </div>

        <div id="dispatchLog"
          style="margin-top:20px; font-size: 0.85rem; max-height: 120px; overflow-y: auto; background: #f8fafc; padding: 10px; border-radius: 8px;">
          <h3 style="font-size: 0.9rem; margin-bottom: 8px; color: #1e293b;"><i class="fas fa-history"></i> Dispatch
            History
          </h3>
          <div id="historyList" style="color: #64748b;">No agencies notified yet.</div>
        </div>

        <div style="margin-top: 24px; text-align: right;">
          <button class="btn resolve-btn" id="confirmDispatchBtn" disabled>Send Alerts</button>
        </div>
      </div>
    </div>


    <!-- Custom Confirmation Modal -->
    <div id="confirmModal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
      <div class="card-modal">
        <div id="confirmIconCircle" class="modal-icon-circle"><i id="confirmIcon" class="fas fa-question"
            aria-hidden="true"></i></div>
        <h2 id="confirmTitle" style="margin:0 0 8px; font-size:1.5rem;">Confirm Action</h2>
        <p id="confirmText" style="color:#64748b; margin:0; line-height:1.5;">Are you sure you want to proceed?</p>
        <div class="modal-actions">
          <button class="btn-cancel" id="confirmCancelBtn">Cancel</button>
          <button class="btn-confirm" id="confirmOkBtn">Confirm</button>
        </div>
      </div>
    </div>

    <!-- Custom Alert Modal -->
    <div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
      <div class="card-modal">
        <div id="alertIconCircle" class="modal-icon-circle"><i id="alertIcon" class="fas fa-info-circle"
            aria-hidden="true"></i></div>
        <h2 id="alertTitle" style="margin:0 0 8px; font-size:1.5rem;">Notification</h2>
        <p id="alertText" style="color:#64748b; margin:0; line-height:1.5;">Message content goes here.</p>
        <div class="modal-actions">
          <button class="btn-confirm" onclick="closeModals()">Understood</button>
        </div>
      </div>
    </div>


    <script src="incident.js?v=<?php echo time(); ?>"></script>
</body>

</html>