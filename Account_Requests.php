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

    <!-- Stats Summary Section -->
    <section style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px;">
      <div style="background:white; padding:24px; border-radius:20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02); display:flex; flex-direction:column; gap:8px;">
        <span style="font-size:0.75rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">PENDING VERIFICATION</span>
        <span id="pendingCount" style="font-size:2rem; font-weight:900; color:#2563eb;">0</span>
      </div>
      <div style="background:white; padding:24px; border-radius:20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02); display:flex; flex-direction:column; gap:8px;">
        <span style="font-size:0.75rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">TOTAL SUBMISSIONS</span>
        <span id="totalCount" style="font-size:2rem; font-weight:900; color:#0f172a;">0</span>
      </div>
    </section>

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
    <div style="margin-top: 60px; padding: 32px; background: #eef2ff; border-radius: 20px; border: 1px solid #e0e7ff; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.05);">
      <h3 style="margin-top:0; color: #1e40af; font-size: 1.1rem; font-weight: 800; display:flex; align-items:center; gap:10px;">
        <i class="fas fa-lightbulb"></i> SYSTEM GUIDELINES
      </h3>
      <ul style="font-size: 0.9rem; color: #475569; line-height: 1.8; margin-bottom:0; padding-left:20px;">
        <li>Review registration details carefully before approving access.</li>
        <li>Ensure the uploaded ID image is clear and matches the registrant's name.</li>
        <li>Approved personnel will receive immediate access to their respective portal/apps.</li>
      </ul>
    </div>
  </div>

  <div id="idViewer" class="id-viewer" onclick="this.style.display='none'">
    <span class="id-viewer-close">&times;</span>
    <img id="idLargeImage" src="" alt="ID Full View">
  </div>

  <script src="account_requests.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>
