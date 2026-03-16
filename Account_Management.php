<?php require_once __DIR__ . '/session_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Account Management | Admin</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="account_management.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body>
  <header class="page-header">
    <div>
      <h1 style="margin:0; font-size: 2.2rem; font-weight:900; letter-spacing:-0.03em; color:#fff;">Account Management
      </h1>
      <p style="margin:4px 0 0; color:#94a3b8; font-weight:500;">Manage user roles, permissions, and view reporting
        history.</p>
    </div>
    <div style="display: flex; gap: 12px; align-items: center; position:relative; z-index:1;">
      <div class="search-box">
        <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.85rem;"></i>
        <input type="text" id="search" placeholder="Search accounts..."
          style="padding: 10px 16px 10px 36px; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; width: 220px; background:rgba(255,255,255,0.08); color:#fff; outline:none; transition: border-color 0.2s;">
      </div>

      <select id="roleFilter"
        style="padding: 10px 14px; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; background:rgba(255,255,255,0.08); color:#fff; cursor: pointer; outline:none; font-weight: 600;">
        <option value="all" style="color:#0f172a;">All Roles</option>
        <option value="admin" style="color:#0f172a;">Admin</option>
        <option value="staff" style="color:#0f172a;">Staff</option>
        <option value="responder" style="color:#0f172a;">Responder</option>
        <option value="citizen" style="color:#0f172a;">Citizen</option>
      </select>

      <select id="statusFilter"
        style="padding: 10px 14px; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; background:rgba(255,255,255,0.08); color:#fff; cursor: pointer; outline:none; font-weight: 600;">
        <option value="all" style="color:#0f172a;">All Status</option>
        <option value="Active" style="color:#0f172a;">Active</option>
        <option value="Pending" style="color:#0f172a;">Pending</option>
        <option value="Suspended" style="color:#0f172a;">Suspended</option>
        <option value="Archived" style="color:#0f172a;">Archived</option>
      </select>
    </div>
  </header>

  <div class="stats-grid" id="statsGrid">
    <div class="stat-card">
      <div style="color: var(--secondary); font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Total Users</div>
      <div style="font-size: 32px; font-weight: 900;" id="totalUsers">0</div>
    </div>
    <div class="stat-card">
      <div style="color: var(--secondary); font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Active Personnel</div>
      <div style="font-size: 32px; font-weight: 900; color: #10b981;" id="staffCount">0</div>
    </div>
  </div>

  <div class="user-container">
    <div class="user-table-card">
      <table>
        <thead>
          <tr>
            <th>User Details</th>
            <th>Role</th>
            <th>Status</th>
            <th>Incidents Reported</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="userTableBody">
          <!-- Rows injected by JS -->
          <tr>
            <td colspan="5" style="padding:100px; text-align:center;">
              <i class="fas fa-spinner fa-spin" style="font-size:2rem; color:var(--primary); margin-bottom:12px;"></i>
              <p style="color:var(--text-muted); font-weight:500;">Loading user records...</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- History Modal (Glassmorphism + Premium Styling) -->
  <div id="historyModal" class="custom-modal">
    <div class="modal-content" style="max-width: 650px; text-align: left; padding: 40px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h2 style="margin:0; font-size: 1.8rem; font-weight: 900; letter-spacing: -0.02em;"><i class="fas fa-history" style="color: var(--primary); margin-right: 16px;"></i>Reporting History</h2>
        <span class="close" title="Close" onclick="closeHistory()"><i class="fas fa-times"></i></span>
      </div>
      <div id="historyContainer" style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
        <!-- History items injected here -->
      </div>
      <div class="modal-actions" style="margin-top: 32px;">
        <button class="btn-confirm" onclick="closeHistory()" style="width: 100%;">Close History</button>
      </div>
    </div>
  </div>

  <!-- Role Change Modal -->
  <div id="roleModal" class="custom-modal">
    <div class="card-modal">
      <div class="modal-icon-circle icon-info"><i class="fas fa-user-shield"></i></div>
      <h2 style="margin:0 0 12px; font-size: 1.6rem; font-weight: 900; letter-spacing: -0.01em;">Modify User Role</h2>
      <p style="color: #64748b; margin-bottom: 28px; line-height:1.5;">Escalate or restrict permissions for <strong id="roleUserName" style="color: #0f172a;">User</strong>.</p>

      <div style="margin-bottom: 32px; text-align: left;">
        <label style="display:block; font-weight:800; font-size: 0.75rem; text-transform: uppercase; color: #64748b; margin-bottom:8px; letter-spacing: 0.05rem;">Select Authorization Level</label>
        <select id="newRoleSelect" class="custom-select" style="width:100%; padding:14px; border-radius:12px; border:1px solid #e2e8f0; background:#f8fafc; font-weight:700; cursor:pointer;">
          <option value="super admin">Super Admin (Full System Control)</option>
          <option value="admin">Admin (Dashboard & Reports)</option>
          <option value="staff">Staff (Operational Access)</option>
          <option value="responder">Responder (App Integration)</option>
          <option value="citizen">Citizen (Public Reporter)</option>
        </select>
      </div>

      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeModals()">Discard</button>
        <button class="btn-confirm" id="confirmRoleBtn">Save Changes</button>
      </div>
    </div>
  </div>

  <!-- Standardized Confirmation Modal -->
  <div id="confirmModal" class="custom-modal">
    <div class="card-modal">
      <div id="confirmIconCircle" class="modal-icon-circle icon-warning"><i id="confirmIcon" class="fas fa-exclamation-triangle"></i></div>
      <h2 id="confirmTitle" style="margin:0 0 12px; font-size: 1.6rem; font-weight: 900; letter-spacing: -0.01em;">Confirm Action</h2>
      <p id="confirmText" style="color: #64748b; margin:0 0 28px; line-height:1.5;">This action may have significant effects. Are you sure you want to proceed?</p>

      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeModals()">Cancel</button>
        <button id="confirmActionButton" class="btn-confirm">Proceed Now</button>
      </div>
    </div>
  </div>

  <!-- Standardized Alert Modal -->
  <div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
    <div class="card-modal">
      <div id="alertIconCircle" class="modal-icon-circle icon-info"><i id="alertIcon" class="fas fa-info-circle"
          aria-hidden="true"></i></div>
      <h2 id="alertTitle" style="margin:0 0 12px; font-size:1.6rem; font-weight: 900; letter-spacing: -0.01em;">Notification</h2>
      <p id="alertText" style="color:#64748b; margin:0 0 28px; line-height:1.5;">Message content goes here.</p>
      <div class="modal-actions">
        <button class="btn-confirm" onclick="closeModals()" style="width:100%;">Understood</button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script src="account_management.js?v=<?php echo time(); ?>" defer></script>
</body>

</html>