/* account_requests.js */

const SUPABASE_URL = "https://tukkkwtxuaxrbihyammp.supabase.co";
const SUPABASE_ANON_KEY = "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P";
const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

let allRequestsData = [];

// ---------- GUI Modal Helpers ----------
function showConfirm({ title, text, icon, type = 'info', confirmText = 'Confirm' }) {
  return new Promise((resolve) => {
    const modal = document.getElementById('confirmModal');
    const iconEl = document.getElementById('confirmIcon');
    const circle = document.getElementById('confirmIconCircle');
    const okBtn = document.getElementById('confirmOkBtn');
    const cancelBtn = document.getElementById('confirmCancelBtn');

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

function showToast(msg, type = 'info') {
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
  type = String(msg).toLowerCase().includes('failed') ? 'danger' : type;
  toast.className = `toast ${type}`;
  const iconInfo = type === 'danger' ? 'times-circle' : type === 'success' ? 'check-circle' : 'info-circle';
  toast.innerHTML = `<div class="icon"><i class="fas fa-${iconInfo}"></i></div><div class="content"><strong>Notification</strong><br/>${msg}</div><button class="close">&times;</button>`;
  container.appendChild(toast);
  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
  const hide = () => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 200); };
  toast.querySelector('.close').onclick = hide;
  setTimeout(hide, 5000);
}

function closeModals() {
  document.querySelectorAll('.custom-modal').forEach(m => m.style.display = 'none');
}

async function fetchRequests() {
  const container = document.getElementById('requestsList');
  container.innerHTML = `
    <div style="text-align:center; padding:100px;">
      <i class="fas fa-spinner fa-spin" style="font-size:2rem; color:#64748b;"></i>
      <p>Fetching pending requests...</p>
    </div>
  `;
  
  try {
    const { data, error } = await supabaseClient
      .from('profiles')
      .select('*')
      .eq('status', 'Pending');

    if (error) throw error;

    allRequestsData = data || [];
    renderRequestsList();

  } catch (err) {
    console.error('Error fetching requests:', err);
    container.innerHTML = `<p style="color:red; text-align:center;">Failed to load requests: ${err.message}</p>`;
  }
}

function renderRequestsList() {
  const container = document.getElementById('requestsList');
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const roleFilterElement = document.getElementById('roleFilter');
  const roleFilter = roleFilterElement ? roleFilterElement.value : 'All';

  if (allRequestsData.length === 0) {
    container.innerHTML = `
      <div class="empty-state" style="text-align:center; padding:60px; background:white; border-radius:16px; box-shadow:var(--card-shadow);">
        <i class="fas fa-user-check" style="font-size:3rem; color:#10b981; margin-bottom:16px;"></i>
        <h2 style="margin:0 0 8px;">All caught up!</h2>
        <p style="color:#64748b; margin:0;">There are no pending account requests at the moment.</p>
      </div>
    `;
    return;
  }

  // Filter Data
  let filteredData = allRequestsData.filter(req => {
    const nameMatch = (req.full_name || req.name || '').toLowerCase().includes(searchTerm);
    const emailMatch = (req.email || '').toLowerCase().includes(searchTerm);
    const searchMatch = nameMatch || emailMatch;
    const roleMatch = roleFilter === 'All' || (req.role && req.role.toLowerCase() === roleFilter.toLowerCase());
    return searchMatch && roleMatch;
  });

  // Sort by created_at descending (newest first)
  filteredData.sort((a, b) => {
    const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
    const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
    return dateB - dateA;
  });

  if (filteredData.length === 0) {
    container.innerHTML = `
      <div style="text-align:center; padding:40px; color:#64748b;">
        <i class="fas fa-search" style="font-size:2rem; margin-bottom:12px; opacity:0.5;"></i>
        <p>No pending requests match your search criteria.</p>
      </div>
    `;
    return;
  }

  container.innerHTML = '';
  filteredData.forEach(req => {
    const card = document.createElement('div');
    card.className = 'request-card';
    
    const roleClass = `role-${(req.role || 'citizen').toLowerCase()}`;
    const initials = (req.full_name || req.name || 'U').charAt(0).toUpperCase();

    let timeString = 'Unknown date';
    if (req.created_at) {
      const d = new Date(req.created_at);
      timeString = d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) + ' at ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    card.innerHTML = `
      <div class="user-avatar">${initials}</div>
      <div class="user-info">
        <h3>${req.full_name || req.name || 'Unnamed User'}</h3>
        <p><i class="fas fa-envelope"></i> ${req.email}</p>
        <div class="request-time">
          <i class="fas fa-clock"></i> Requested: ${timeString}
        </div>
        <span class="role-badge ${roleClass}">${req.role || 'Citizen'}</span>
      </div>
      <div style="display:flex; align-items:center; gap:24px;">
        <div class="id-preview" onclick="viewID('${req.id_url}')">
          ${req.id_url ? `<img src="${req.id_url}" alt="ID" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-exclamation-triangle\' style=\'color:orange\'></i><div class=\'id-label\'>ERR: PRIVATE</div>'">` : `<i class="fas fa-image"></i>`}
          <div class="id-label">VIEW ID</div>
        </div>
        <div class="actions">
          <button id="approve-${req.id}" class="btn-approve" onclick="handleAction('${req.id}', 'Active')">Approve</button>
          <button id="reject-${req.id}" class="btn-reject" onclick="handleAction('${req.id}', 'Rejected')">Reject</button>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
}

function viewID(url) {
  if (!url || url === 'null') {
    showToast('No ID was uploaded for this account.', 'warning');
    return;
  }
  document.getElementById('idLargeImage').src = url;
  document.getElementById('idViewer').style.display = 'flex';
}

async function handleAction(userId, newStatus) {
  const isApprove = newStatus === 'Active';
  const confirmed = await showConfirm({
    title: isApprove ? 'Approve Account?' : 'Reject Account?',
    text: isApprove 
        ? 'This will allow the user to log in to the apps and access their role features.' 
        : 'This will reject the registration. The user will be notified that their request was denied.',
    icon: isApprove ? 'user-check' : 'user-times',
    type: isApprove ? 'info' : 'danger',
    confirmText: isApprove ? 'Approve Now' : 'Reject Now'
  });
  
  if (!confirmed) return;

  const approveBtn = document.getElementById(`approve-${userId}`);
  const rejectBtn = document.getElementById(`reject-${userId}`);
  if (approveBtn) approveBtn.disabled = true;
  if (rejectBtn) rejectBtn.disabled = true;

  try {
    const { error } = await supabaseClient
      .from('profiles')
      .update({ status: newStatus })
      .eq('id', userId);

    if (error) throw error;

    showToast(isApprove ? 'Account approved successfully!' : 'Account request rejected.', isApprove ? 'success' : 'info');
    fetchRequests();
  } catch (err) {
    showToast('Action failed: ' + err.message, 'danger');
    if (approveBtn) approveBtn.disabled = false;
    if (rejectBtn) rejectBtn.disabled = false;
  }
}

// Refresh automatically if status changes somewhere else
supabaseClient
  .channel('requests-realtime')
  .on('postgres_changes', { event: '*', schema: 'public', table: 'profiles' }, () => {
    fetchRequests();
  })
  .subscribe();

document.addEventListener('DOMContentLoaded', fetchRequests);