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

  <div id="idViewer" class="id-viewer" onclick="this.style.display='none'">
    <span class="id-viewer-close">&times;</span>
    <img id="idLargeImage" src="" alt="ID Full View">
  </div>

  <script src="account_requests.js" defer></script>
</body>
</html>
