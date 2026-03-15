<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Account Management | Admin</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="account_management.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
      <input type="text" id="search" placeholder="Search accounts..."
        style="padding: 10px 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; width: 220px; background:rgba(255,255,255,0.08); color:#fff; outline:none;">

      <select id="roleFilter"
        style="padding: 10px 14px; border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; background:rgba(255,255,255,0.08); color:#fff; cursor: pointer; outline:none;">
        <option value="all" style="color:#0f172a;">All Roles</option>
        <option value="admin" style="color:#0f172a;">Admin</option>
        <option value="staff" style="color:#0f172a;">Staff</option>
        <option value="responder" style="color:#0f172a;">Responder</option>
        <option value="citizen" style="color:#0f172a;">Citizen</option>
      </select>

      <select id="statusFilter"
        style="padding: 10px 14px; border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; background:rgba(255,255,255,0.08); color:#fff; cursor: pointer; outline:none;">
        <option value="all" style="color:#0f172a;">All Status</option>
        <option value="Active" style="color:#0f172a;">Active</option>
        <option value="Suspended" style="color:#0f172a;">Suspended</option>
        <option value="Archived" style="color:#0f172a;">Archived</option>
      </select>
    </div>
  </header>

  <div class="stats-grid" id="statsGrid">
    <div class="stat-card">
      <div style="color: var(--secondary); font-size: 14px;">Total Users</div>
      <div style="font-size: 28px; font-weight: 800;" id="totalUsers">0</div>
    </div>
    <div class="stat-card">
      <div style="color: var(--secondary); font-size: 14px;">Active Employees</div>
      <div style="font-size: 28px; font-weight: 800; color: var(--success);" id="staffCount">0</div>
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
        </tbody>
      </table>
    </div>
  </div>

  <!-- History Modal -->
  <div id="historyModal" class="history-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 style="margin:0; font-size: 1.25rem;"><i class="fas fa-history" style="color: var(--primary);"></i>
          Reporting History</h2>
        <button class="btn-icon" onclick="closeHistory()"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body" id="historyContainer">
        <!-- History items injected here -->
      </div>
    </div>
  </div>

  <!-- Role Change Modal -->
  <div id="roleModal" class="custom-modal">
    <div class="card-modal">
      <div class="modal-icon-circle icon-info"><i class="fas fa-user-tag"></i></div>
      <h2 style="margin:0 0 8px; font-size: 1.5rem;">Change Role</h2>
      <p style="color: var(--secondary); margin:0;">Select a new permission level for <strong
          id="roleUserName">User</strong>.</p>

      <select id="newRoleSelect" class="custom-select">
        <option value="super admin">Super Admin (Full Control)</option>
        <option value="admin">Admin (View Only)</option>
        <option value="staff">Staff (Operational)</option>
        <option value="responder">Responder (App Access)</option>
        <option value="citizen">Citizen (Reporter)</option>
      </select>

      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeModals()">Cancel</button>
        <button class="btn-confirm" id="confirmRoleBtn">Update Role</button>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div id="confirmModal" class="custom-modal">
    <div class="card-modal">
      <div id="confirmIcon" class="modal-icon-circle icon-warning"><i class="fas fa-exclamation-triangle"></i></div>
      <h2 id="confirmTitle" style="margin:0 0 8px; font-size: 1.5rem;">Are you sure?</h2>
      <p id="confirmText" style="color: var(--secondary); margin:0;">This action cannot be undone.</p>

      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeModals()">Cancel</button>
        <button id="confirmActionButton" class="btn-confirm">Proceed</button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script src="account_management.js" defer></script>
</body>

</html>