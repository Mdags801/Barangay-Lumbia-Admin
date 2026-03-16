console.log('%c [Module] Account Management v8.0 Active ', 'color: #10b981; font-weight: bold;');
/* account_management.js */

const SUPABASE_URL = "https://tukkkwtxuaxrbihyammp.supabase.co";
    const SUPABASE_ANON_KEY = "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P";
    const supabaseAccountManager = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

    let allProfiles = [];
    let allIncidents = [];
    let currentUserRole = 'staff';

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

      // Fetch more details for history
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

      // Sort alpha
      filtered.sort((a, b) => (a.full_name || a.name || '').localeCompare(b.full_name || b.name || ''));

      filtered.forEach(user => {
        const historyCount = allIncidents.filter(i => i.user_id === user.id).length;
        const displayName = user.full_name || user.name || 'Anonymous User';
        const initial = displayName.charAt(0).toUpperCase();
        const status = user.status || 'Active';

        const tr = document.createElement('tr');

        // Logic: Role change is ONLY for Super Admin
        const canManageRoles = currentUserRole === 'super admin';

        tr.innerHTML = `
          <td>
            <div class="user-info">
              <div class="avatar">${initial}</div>
              <div>
                <div style="font-weight:600;">${displayName}</div>
                <div style="font-size:12px; color:var(--secondary);">${user.email || 'No email registered'}</div>
              </div>
            </div>
          </td>
          <td><span class="role-badge role-${(user.role || 'citizen').toLowerCase()}">${user.role || 'citizen'}</span></td>
          <td>
            <span style="color: ${status === 'Active' ? 'var(--success)' : status === 'Suspended' ? 'var(--danger)' : status === 'Pending' ? '#f59e0b' : 'var(--secondary)'}; font-size:13px;">
              <i class="fas fa-circle" style="font-size:8px;"></i> ${status}
            </span>
          </td>
          <td><span class="incident-count">${historyCount} Reports</span></td>
          <td>
            <div class="actions">
              ${canManageRoles ? `
                <button class="btn-icon" title="Change Role" onclick="changeRole('${user.id}', '${user.role}')"><i class="fas fa-user-tag"></i></button>
                <button class="btn-icon" style="color:${status === 'Suspended' ? 'var(--success)' : 'var(--danger)'};" 
                  title="${status === 'Suspended' ? 'Unsuspend' : 'Suspend'}" 
                  onclick="toggleStatus('${user.id}', '${status}')">
                  <i class="fas ${status === 'Suspended' ? 'fa-check-circle' : 'fa-ban'}"></i>
                </button>
              ` : ''}
              <button class="btn-icon" title="View History" onclick="viewHistory('${user.id}')"><i class="fas fa-history"></i></button>
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
        else loadData();
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

      showConfirm({
        title: `${action} User`,
        text: `Are you sure you want to ${isSuspending ? 'suspend' : 'reinstate'} the account for ${user.full_name}?`,
        icon: isSuspending ? 'danger' : 'info',
        confirmText: isSuspending ? 'Suspend Account' : 'Activate Account',
        isDanger: isSuspending,
        onConfirm: async () => {
          const newStatus = isSuspending ? 'Suspended' : 'Active';
          const { error } = await supabaseAccountManager.from('profiles').update({ status: newStatus }).eq('id', userId);
          if (error) showCustomAlert("Error", error.message, "danger");
          else loadData();
        }
      });
    }

    /* GUI Helpers */
    function showConfirm({ title, text, icon, confirmText, isDanger, onConfirm }) {
      const modal = document.getElementById('confirmModal');
      const iconEl = document.getElementById('confirmIcon');

      document.getElementById('confirmTitle').textContent = title;
      document.getElementById('confirmText').textContent = text;

      const confirmBtn = document.getElementById('confirmActionButton');
      confirmBtn.textContent = confirmText || 'Proceed';
      confirmBtn.className = isDanger ? 'btn-danger' : 'btn-confirm';

      iconEl.className = `modal-icon-circle icon-${icon || 'warning'}`;
      iconEl.innerHTML = icon === 'danger' ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-exclamation-triangle"></i>';

      modal.style.display = 'flex';
      confirmBtn.onclick = () => {
        onConfirm();
        closeModals();
      };
    }

    function showCustomAlert(title, text, type) {
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
      toast.className = `toast ${type || 'info'}`;

      const iconValue = type === 'danger' ? 'ban' : type === 'warning' ? 'exclamation-triangle' : 'info-circle';
      const iconHtml = `<div class="icon"><i class="fas fa-${iconValue}"></i></div>`;
      const contentHtml = `<div class="content"><strong>${title}</strong><br/>${text}</div>`;
      const closeHtml = `<button class="close">&times;</button>`;

      toast.innerHTML = iconHtml + contentHtml + closeHtml;
      container.appendChild(toast);

      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          toast.classList.add('show');
        });
      });

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

    function viewHistory(userId) {
      const user = allProfiles.find(p => p.id === userId);
      const userIncidents = allIncidents.filter(i => i.user_id === userId);
      const container = document.getElementById('historyContainer');

      container.innerHTML = '';

      if (userIncidents.length === 0) {
        container.innerHTML = `<div style="text-align:center; padding: 40px; color: var(--secondary);">No incident reports found for this user.</div>`;
      } else {
        userIncidents.sort((a, b) => new Date(b.reportedAt) - new Date(a.reportedAt));
        userIncidents.forEach(inc => {
          const date = new Date(inc.reportedAt).toLocaleString();
          const item = document.createElement('div');
          item.className = 'history-item';
          item.innerHTML = `
            <div>
              <div style="font-weight:600; color: #0f172a;">${inc.type || 'Unknown Type'}</div>
              <div style="font-size:12px; color: var(--secondary); margin-top:2px;">${inc.location || 'Unknown Location'}</div>
              <div style="font-size:11px; color: var(--secondary); margin-top:4px;">${date}</div>
            </div>
            <span class="role-badge" style="background: #f1f5f9; color: #475569; font-size:10px;">${(inc.status || 'Pending').toUpperCase()}</span>
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