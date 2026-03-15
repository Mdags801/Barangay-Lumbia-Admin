/* account_requests.js */

const SUPABASE_URL = "https://tukkkwtxuaxrbihyammp.supabase.co";
    const SUPABASE_ANON_KEY = "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P";
    const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

    let allRequestsData = [];

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

        console.log('Fetched raw profiles data:', data, 'error:', error);

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

        // Format Date mapping
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
        alert('No ID was uploaded for this account.');
        return;
      }
      document.getElementById('idLargeImage').src = url;
      document.getElementById('idViewer').style.display = 'flex';
    }

    async function handleAction(userId, newStatus) {
      const confirmMsg = newStatus === 'Active' ? 'Approve this account?' : 'Reject and disable this account?';
      if (!confirm(confirmMsg)) return;

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

        // Note: index.html has a monitor that will see this change via realtime
        fetchRequests();
      } catch (err) {
        alert('Action failed: ' + err.message);
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