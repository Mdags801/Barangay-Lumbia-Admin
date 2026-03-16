<?php require_once __DIR__ . '/session_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Requests — Barangay Admin</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="account_requests.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
</head>
<body style="background: #f8fafc;">

  <div class="requests-container">
    <header class="page-header">
      <div>
        <h1 style="margin:0; font-size:1.8rem;">Account Requests</h1>
        <p style="margin:4px 0 0; opacity: 0.8; font-size: 0.95rem;">Review and verify new registrations from the apps and website.</p>
      </div>
      <button onclick="fetchRequests()" class="btn-refresh">
        <i class="fas fa-sync-alt"></i> <span>Refresh List</span>
      </button>
    </header>

    <div class="controls-container">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search by name or email..." onkeyup="renderRequestsList()">
      </div>
      <div class="filter-box">
        <select id="roleFilter" onchange="renderRequestsList()">
          <option value="All">All Roles</option>
          <option value="Citizen">Citizen</option>
          <option value="Responder">Responder</option>
          <option value="Admin">Admin</option>
        </select>
      </div>
    </div>

    <div id="requestsList">
      <div style="text-align:center; padding:100px;">
        <i class="fas fa-spinner fa-spin" style="font-size:2rem; color:#64748b;"></i>
        <p>Fetching pending requests...</p>
      </div>
    </div>

    <!-- Troubleshooting Section -->
    <div style="margin-top: 60px; padding: 24px; background: #eef2ff; border-radius: 16px; border: 1px solid #e0e7ff;">
      <h3 style="margin-top:0; color: #1e40af; font-size: 1rem;"><i class="fas fa-lightbulb"></i> Troubleshooting</h3>
      <ul style="font-size: 0.85rem; color: #475569; line-height: 1.6; margin-bottom:0;">
        <li><strong>Not seeing any requests?</strong> Ensure users are signing up with the correct app version.</li>
        <li><strong>ID Images not loading?</strong> Make sure your <code>identities</code> storage bucket in Supabase is set to <strong>Public</strong>.</li>
        <li><strong>RLS Policy:</strong> Ensure your <code>profiles</code> table has a policy allowing Admins to <code>SELECT</code> and <code>UPDATE</code> all rows.</li>
      </ul>
    </div>
  </div>

  <!-- ID Viewer Modal (Standardized) -->
  <div id="idViewer" class="custom-modal" onclick="this.style.display='none'">
    <div style="position: relative; max-width: 90%; max-height: 90%; display: flex; align-items: center; justify-content: center; animation: modalSpringIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);">
      <span class="id-viewer-close" style="position: absolute; top: -50px; right: -10px; font-size: 2rem; color: white; cursor: pointer; text-shadow: 0 4px 12px rgba(0,0,0,0.5);">&times;</span>
      <img id="idLargeImage" src="" alt="ID Full View" style="max-width: 100%; max-height: 85vh; border-radius: 24px; box-shadow: 0 25px 60px rgba(0,0,0,0.6); border: 2px solid rgba(255,255,255,0.2);">
    </div>
  </div>

  <!-- Custom Confirmation Modal -->
  <div id="confirmModal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="card-modal">
      <div id="confirmIconCircle" class="modal-icon-circle icon-info"><i id="confirmIcon" class="fas fa-question" aria-hidden="true"></i></div>
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
        <button class="btn-confirm" onclick="closeModals()" style="max-width:200px;">Understood</button>
      </div>
    </div>
  </div>

  <script src="account_requests.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>
