<?php require_once __DIR__ . '/session_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Archived Incidents</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="incident.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
  <header>
    <h1>Archived Incidents</h1>
    <p>Past reports stored for record-keeping</p>
  </header>

  <div class="controls">
    <div class="left">
      <input id="searchInput" type="text" placeholder="Search by ID, reporter, type, or location" />
      <select id="sortSelect" title="Sort">
        <option value="newest">Newest First</option>
        <option value="oldest">Oldest First</option>
      </select>
    </div>
  </div>

  <div class="table-wrap" aria-live="polite">
    <table class="incident-table" role="table" aria-label="Archived incidents table">
      <thead>
        <tr>
          <th>Type</th>
          <th>ID</th>
          <th>Location</th>
          <th>Time</th>
          <th>Reporter</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="archiveTableBody"></tbody>
    </table>
  </div>

  <!-- Custom Confirmation Modal -->
  <div id="confirmModal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="card-modal">
      <div id="confirmIconCircle" class="modal-icon-circle icon-warning"><i id="confirmIcon" class="fas fa-question" aria-hidden="true"></i></div>
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
      <div id="alertIconCircle" class="modal-icon-circle icon-info"><i id="alertIcon" class="fas fa-info-circle" aria-hidden="true"></i></div>
      <h2 id="alertTitle" style="margin:0 0 12px; font-size:1.6rem; font-weight: 900; letter-spacing:-0.01em;">Notification</h2>
      <p id="alertText" style="color:#64748b; margin:0 0 28px; line-height:1.5;">Message content goes here.</p>
      <div class="modal-actions">
        <button class="btn-confirm" onclick="closeModals()">Understood</button>
      </div>
    </div>
  </div>

  <!-- Supabase JS -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script src="archive.js?v=<?php echo time(); ?>" defer></script>
</body>

</html>
