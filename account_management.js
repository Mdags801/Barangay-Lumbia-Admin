/* account_management.js */

console.log('%c [Module] Account Management v9.0 Active ', 'color: #10b981; font-weight: bold;');

const SUPABASE_URL = "https://tukkkwtxuaxrbihyammp.supabase.co";
const SUPABASE_ANON_KEY = "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P";
const supabaseAccountManager = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

let allProfiles = [];
let allIncidents = [];
let currentUserRole = 'staff';

// ---------- GUI Modal Helpers ----------
function showConfirm({ title, text, icon, type = 'info', confirmText = 'Confirm' }) {
  return new Promise((resolve) => {
    const modal = document.getElementById('confirmModal');
    const iconEl = document.getElementById('confirmIcon');
    const circle = document.getElementById('confirmIconCircle');
    const okBtn = document.getElementById('confirmActionButton');
    const cancelBtn = modal.querySelector('.btn-cancel');

    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmText').textContent = text;
    iconEl.className = `fas fa-${icon || 'question'}`;
    okBtn.textContent = confirmText;

    // Reset classes
    circle.className = 'modal-icon-circle ' + (type === 'danger' ? 'icon-danger' : type === 'warning' ? 'icon-warning' : 'icon-info');
    okBtn.className = type === 'danger' ? 'btn-danger' : 'btn-confirm';

    modal.style.display = 'flex';

    const handleConfirm = () => {
      modal.style.display = 'none';
      cleanup();
      resolve(true);
    };
    const handleCancel = () => {
      modal.style.display = 'none';
      cleanup();
      resolve(false);
    };

    function cleanup() {
      okBtn.removeEventListener('click', handleConfirm);
      cancelBtn.removeEventListener('click', handleCancel);
    }

    okBtn.addEventListener('click', handleConfirm);
    cancelBtn.addEventListener('click', handleCancel);
  });
}

function showCustomAlert(title, text, type = 'info') {
  const modal = document.getElementById('alertModal');
  const iconEl = document.getElementById('alertIcon');
  const circle = document.getElementById('alertIconCircle');
  
  document.getElementById('alertTitle').textContent = title;
  document.getElementById('alertText').textContent = text;
  
  iconEl.className = type === 'danger' ? 'fas fa-ban' : 'fas fa-info-circle';
  circle.className = 'modal-icon-circle ' + (type === 'danger' ? 'icon-danger' : 'icon-info');
  
  modal.style.display = 'flex';
}

function showToast(title, text, type = 'info') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'true');
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;

  const iconValue = type === 'danger' ? 'times-circle' : type === 'success' ? 'check-circle' : 'info-circle';
  toast.innerHTML = `
    <div class="icon"><i class="fas fa-${iconValue}"></i></div>
    <div class="content"><strong>${title}</strong><br/>${text}</div>
    <button class="close">&times;</button>
  `;
  container.appendChild(toast);

  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));

  const hide = () => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 200);
  };

  toast.querySelector('.close').onclick = hide;
  setTimeout(hide, 5000);
}

function closeModals() {
  document.querySelectorAll('.custom-modal').forEach(m => m.style.display = 'none');
}

// ---------- Data Management ----------
async function checkCurrentRole() {
  const { data: { user } } = await supabaseAccountManager.auth.getUser();
  if (user) {
    const { data: profile } = await supabaseAccountManager.from('profiles').select('role').eq('id', user.id).single();
    currentUserRole = (profile?.role || 'staff').toLowerCase();

    // Security check
    const isAdmin = (currentUserRole === 'admin' || currentUserRole === 'super admin');
    if (!isAdmin) {
      window.parent.postMessage({ type: 'redirect', page: 'dashboard' }, '*');
      return;
    }
  }
}

async function loadData() {
  await checkCurrentRole();

  const { data: profiles, error: pError } = await supabaseAccountManager.from('profiles').select('*');
  if (pError) console.error("Profiles error:", pError);

  const { data: incidents, error: iError } = await supabaseAccountManager.from('incidents').select('id, user_id, type, status, reportedAt, location');
  if (iError) console.error("Incidents error:", iError);

  allProfiles = profiles || [];
  allIncidents = incidents || [];

  renderTable();
}

function renderTable() {
  const userTableBody = document.getElementById('userTableBody');
  const searchTerm = document.getElementById('search').value.toLowerCase();
  const roleFilter = document.getElementById('roleFilter').value;
  const statusFilter = document.getElementById('statusFilter').value;

  userTableBody.innerHTML = '';

  let filtered = allProfiles.filter(user => {
    const role = (user.role || 'citizen').toLowerCase();
    const status = user.status || 'Active';

    const matchesRole = roleFilter === 'all' || role === roleFilter;
    const matchesStatus = statusFilter === 'all' || status === statusFilter;

    if (!matchesRole || !matchesStatus) return false;

    if (!searchTerm) return true;
    const keywords = searchTerm.split(/\s+/).filter(k => k.length > 0);
    const name = (user.full_name || user.name || '').toLowerCase();
    const email = (user.email || '').toLowerCase();
    const combined = `${name} ${email} ${role} ${status}`;

    return keywords.every(k => combined.includes(k));
  });

  filtered.sort((a, b) => (a.full_name || a.name || '').localeCompare(b.full_name || b.name || ''));

  if (filtered.length === 0) {
    userTableBody.innerHTML = `<tr><td colspan="5" style="padding:60px; text-align:center; color:var(--text-muted);">No matching personnel records found.</td></tr>`;
    return;
  }

  filtered.forEach(user => {
    const historyCount = allIncidents.filter(i => i.user_id === user.id).length;
    const displayName = user.full_name || user.name || 'Anonymous User';
    const initial = displayName.charAt(0).toUpperCase();
    const status = user.status || 'Active';

    const tr = document.createElement('tr');
    tr.style.cursor = 'default';

    const canManageRoles = currentUserRole === 'super admin';

    tr.innerHTML = `
      <td>
        <div class="user-info">
          <div class="avatar">${initial}</div>
          <div>
            <div style="font-weight:700; color: #0f172a;">${displayName}</div>
            <div style="font-size:12px; color:#64748b; font-weight:500;">${user.email || 'No email registered'}</div>
          </div>
        </div>
      </td>
      <td><span class="role-badge role-${(user.role || 'citizen').toLowerCase()}">${user.role || 'citizen'}</span></td>
      <td>
        <span class="status-indicator ${status.toLowerCase()}">
          <i class="fas fa-circle" style="font-size:8px;"></i> ${status}
        </span>
      </td>
      <td><span class="incident-count">${historyCount} Reports</span></td>
      <td>
        <div class="actions">
          ${canManageRoles ? `
            <button class="btn-icon" title="Change Role" onclick="changeRole('${user.id}', '${user.role}')"><i class="fas fa-user-tag"></i></button>
            <button class="btn-icon status-toggle ${status === 'Suspended' ? 'unsuspend' : 'suspend'}" 
              title="${status === 'Suspended' ? 'Unsuspend' : 'Suspend'}" 
              onclick="toggleStatus('${user.id}', '${status}')">
              <i class="fas ${status === 'Suspended' ? 'fa-check-circle' : 'fa-ban'}"></i>
            </button>
          ` : ''}
          <button class="btn-icon highlight" title="View History" onclick="viewHistory('${user.id}')"><i class="fas fa-history"></i></button>
        </div>
      </td>
    `;
    userTableBody.appendChild(tr);
  });

  document.getElementById('totalUsers').innerText = allProfiles.length;
  const countLabel = document.getElementById('staffCount');
  if (countLabel) {
    countLabel.innerText = allProfiles.filter(u => 
      ['super admin', 'admin', 'staff', 'responder'].includes(u.role?.toLowerCase()) && 
      u.status === 'Active'
    ).length;
  }
}

async function changeRole(userId, currentRole) {
  if (currentUserRole !== 'super admin') {
    showCustomAlert("Access Denied", "Only Super Admin can update roles.", "danger");
    return;
  }

  const user = allProfiles.find(p => p.id === userId);
  document.getElementById('roleUserName').textContent = user.full_name || user.email;
  document.getElementById('newRoleSelect').value = currentRole.toLowerCase();

  document.getElementById('roleModal').style.display = 'flex';

  document.getElementById('confirmRoleBtn').onclick = async () => {
    const newRole = document.getElementById('newRoleSelect').value;
    const { error } = await supabaseAccountManager.from('profiles').update({ role: newRole }).eq('id', userId);
    closeModals();
    if (error) showCustomAlert("Error", error.message, "danger");
    else {
      showToast("Role Updated", `Account for ${user.full_name || user.email} is now ${newRole}.`, "success");
      loadData();
    }
  };
}

async function toggleStatus(userId, currentStatus) {
  if (currentUserRole !== 'super admin') {
    showCustomAlert("Access Denied", "Only Super Admin can suspend/activate accounts.", "danger");
    return;
  }

  const isSuspending = currentStatus === 'Active';
  const action = isSuspending ? 'SUSPEND' : 'ACTIVATE';
  const user = allProfiles.find(p => p.id === userId);

  const confirmed = await showConfirm({
    title: `${action} User Access`,
    text: `Are you sure you want to ${isSuspending ? 'disable' : 'restore'} the account for ${user.full_name || user.email}?`,
    icon: isSuspending ? 'ban' : 'user-check',
    type: isSuspending ? 'danger' : 'info',
    confirmText: isSuspending ? 'Confirm Suspension' : 'Confirm Activation'
  });

  if (!confirmed) return;

  const newStatus = isSuspending ? 'Suspended' : 'Active';
  const { error } = await supabaseAccountManager.from('profiles').update({ status: newStatus }).eq('id', userId);
  
  if (error) {
    showCustomAlert("Error", error.message, "danger");
  } else {
    showToast("Status Updated", `User ${user.full_name || user.email} is now ${newStatus.toUpperCase()}.`, isSuspending ? "info" : "success");
    loadData();
  }
}

function viewHistory(userId) {
  const user = allProfiles.find(p => p.id === userId);
  const userIncidents = allIncidents.filter(i => i.user_id === userId);
  const container = document.getElementById('historyContainer');

  container.innerHTML = '';

  if (userIncidents.length === 0) {
    container.innerHTML = `
      <div style="text-align:center; padding: 60px; color: #94a3b8;">
        <i class="fas fa-folder-open" style="font-size:3rem; margin-bottom:16px; opacity:0.3;"></i>
        <p style="margin:0;">No incident reports found for this user.</p>
      </div>`;
  } else {
    userIncidents.sort((a, b) => new Date(b.reportedAt) - new Date(a.reportedAt));
    userIncidents.forEach(inc => {
      const date = new Date(inc.reportedAt).toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
      const item = document.createElement('div');
      item.className = 'history-item';
      item.style.padding = '16px';
      item.style.borderBottom = '1px solid #f1f5f9';
      item.style.display = 'flex';
      item.style.justifyContent = 'space-between';
      item.style.alignItems = 'center';
      
      item.innerHTML = `
        <div style="flex:1;">
          <div style="font-weight:700; color: #0f172a; margin-bottom:4px;">${inc.type || 'Unknown Type'}</div>
          <div style="font-size:13px; color: #64748b; margin-bottom:4px;"><i class="fas fa-map-marker-alt" style="margin-right:6px; font-size:11px;"></i> ${inc.location || 'Unknown Location'}</div>
          <div style="font-size:11px; color: #94a3b8; font-weight:600;"><i class="fas fa-clock" style="margin-right:6px;"></i> ${date}</div>
        </div>
        <span class="status-pill ${(inc.status || 'pending').toLowerCase()}">${inc.status || 'PENDING'}</span>
      `;
      container.appendChild(item);
    });
  }

  document.getElementById('historyModal').style.display = 'flex';
}

function closeHistory() {
  document.getElementById('historyModal').style.display = 'none';
}

// Listeners
document.getElementById('search').addEventListener('input', renderTable);
document.getElementById('roleFilter').addEventListener('change', renderTable);
document.getElementById('statusFilter').addEventListener('change', renderTable);

loadData();
supabaseAccountManager.channel('profile-realtime').on('postgres_changes', { event: '*', schema: 'public', table: 'profiles' }, loadData).subscribe();