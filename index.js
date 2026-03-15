/* index.js — Main portal script extracted from index.php */

// --- Supabase Initialization ---
    const SUPABASE_URL = 'https://tukkkwtxuaxrbihyammp.supabase.co';
    const SUPABASE_KEY = 'sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P';
    const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

    // --- Navigation and Page Metadata ---
    const NAV_LINKS = [
      {
        id: 'nav-dashboard',
        page: 'dashboard',
        file: 'Dashboard.php',
        title: 'Dashboard',
        subtitle: 'Overview & quick stats'
      },
      {
        id: 'nav-incident',
        page: 'incident',
        file: 'Incident.php',
        title: 'Incident',
        subtitle: 'Incident reporting & management'
      },
      {
        id: 'nav-reports',
        page: 'reports',
        file: 'Reports.php',
        title: 'Reports',
        subtitle: 'View and generate reports'
      },
      {
        id: 'nav-account',
        page: 'account',
        file: 'Account_Management.php',
        title: 'Account Management',
        subtitle: 'Manage user accounts and roles'
      },
      {
        id: 'nav-app_manager',
        page: 'app_manager',
        file: 'App_Manager.php',
        title: 'App Manager',
        subtitle: 'Manage barangay applications'
      },
      {
        id: 'nav-requests',
        page: 'requests',
        file: 'Account_Requests.php',
        title: 'Account Requests',
        subtitle: 'Approve or verify new registrations'
      },
      {
        id: 'nav-archive',
        page: 'archive',
        file: 'Archive.php',
        title: 'Archive',
        subtitle: 'Access archived records'
      }
    ];

    // --- Utility Functions ---
    function getUsernameFromEmail(email) {
      if (!email || typeof email !== 'string') return '';
      const atIdx = email.indexOf('@');
      return atIdx > 0 ? email.slice(0, atIdx) : email;
    }

    function setSidebarYear() {
      const yearSpan = document.getElementById('sidebar-year');
      if (yearSpan) yearSpan.textContent = new Date().getFullYear();
    }

    // --- Live Clock ---
    function startLiveClock() {
      const clockEl = document.getElementById('live-clock');
      function updateClock() {
        const now = new Date();
        // Format: Monday, 02 Mar 2026, 17:41:05
        const options = { weekday: 'short', year: 'numeric', month: 'short', day: '2-digit' };
        const dateStr = now.toLocaleDateString(undefined, options);
        const timeStr = now.toLocaleTimeString(undefined, { hour12: false });
        clockEl.textContent = `${dateStr}, ${timeStr}`;
      }
      updateClock();
      setInterval(updateClock, 1000);
    }

    // --- Sidebar Navigation ---
    function setActiveNav(page) {
      NAV_LINKS.forEach(link => {
        const el = document.getElementById(link.id);
        if (el) {
          if (link.page === page) {
            el.classList.add('active');
            el.setAttribute('aria-current', 'page');
          } else {
            el.classList.remove('active');
            el.removeAttribute('aria-current');
          }
        }
      });
    }

    function updateTopbar(page) {
      const meta = NAV_LINKS.find(l => l.page === page) || NAV_LINKS[0];
      document.getElementById('topbar-title').textContent = meta.title;
      document.getElementById('topbar-subtitle').textContent = meta.subtitle;
    }

    function loadPage(page, pushState = true) {
      const meta = NAV_LINKS.find(l => l.page === page) || NAV_LINKS[0];
      const iframe = document.getElementById('main-iframe');
      if (iframe) {
        // Force refresh for App Manager to avoid stale cache of UI updates
        const url = meta.page === 'app_manager' ? `${meta.file}?t=${Date.now()}` : meta.file;
        iframe.src = url;
      }
      setActiveNav(meta.page);
      updateTopbar(meta.page);
      if (pushState) {
        window.history.pushState({ page: meta.page }, meta.title, `#${meta.page}`);
      }
      // Persist active page
      localStorage.setItem('activePage', meta.page);
    }

    function handleNavClick(e) {
      e.preventDefault();
      const page = e.currentTarget.getAttribute('data-page');
      if (page) {
        loadPage(page);
      }
    }

    function setupSidebarNav() {
      NAV_LINKS.forEach(link => {
        const el = document.getElementById(link.id);
        if (el) {
          el.addEventListener('click', handleNavClick);
        }
      });
    }

    // --- Iframe Error Handling ---
    function setupIframeErrorHandling() {
      const iframe = document.getElementById('main-iframe');
      if (!iframe) return;
      iframe.addEventListener('error', function () {
        iframe.contentDocument.body.innerHTML = '<div style="padding:2em;text-align:center;color:#b71c1c;font-size:1.2em;">Failed to load content. Please check your connection or contact support.</div>';
      });
    }

    // --- Iframe Communication (postMessage) ---
    window.addEventListener('message', function (event) {
      if (event.data.type === 'redirect') {
        loadPage(event.data.page || 'dashboard');
      }
      if (event.data.type === 'request-greeting') {
        syncGreetingToIframe();
      }
      if (event.data.type === 'settings-updated') {
        console.log('[Settings] Updated:', event.data.settings);
        // Persist globally if needed or trigger effects
        if (event.data.settings.soundEnabled === false) {
           // Any global state for audioctx can be muted here
        }
      }
    });

    // --- Authentication State Management ---
    let currentUserProfile = null;
    let presenceChannel = null;
    let presenceUIInitialized = false;
    let currentAuthSession = null;

    async function updateAuthUI(user) {
      const userInfo = document.getElementById('user-info');
      const userEmail = document.getElementById('user-email');
      const signInBtn = document.getElementById('sign-in-btn');
      const signOutBtn = document.getElementById('sign-out-btn');
      const presenceFab = document.getElementById('activeUsersBtn');

      if (user) {
        // Fetch Profile for Role
        const { data, error } = await supabase.from('profiles').select('*').eq('id', user.id).single();
        currentUserProfile = data || { role: 'staff', full_name: user.email.split('@')[0] };

        const role = (currentUserProfile.role || 'staff').toLowerCase();

        // Security Gate: Citizens and Responders cannot access the website
        if (role === 'citizen' || role === 'responder') {
          await supabase.auth.signOut();
          window.location.href = 'login.php?error=access_denied';
          return;
        }

        // Handle Role-based Visibility
        applyRolePermissions(currentUserProfile.role);

        userInfo.style.display = 'flex';
        userInfo.style.alignItems = 'center';
        userInfo.style.gap = '8px';

        // Enhanced user info display
        userEmail.innerHTML = `
          <div style="display:flex; flex-direction:column; line-height:1.2;">
            <span style="font-weight:600;">${currentUserProfile.full_name || user.email}</span>
            <span style="font-size:10px; text-transform:uppercase; color:var(--primary); font-weight:700;">${currentUserProfile.role}</span>
          </div>
        `;

        signInBtn.style.display = 'none';
        signOutBtn.style.display = '';
        if (presenceFab) presenceFab.style.display = 'flex';
        initPresenceSystem();

        // Sync greeting to iframe if it's already loaded
        syncGreetingToIframe();
      } else {
        userInfo.style.display = 'none';
        signInBtn.style.display = '';
        signOutBtn.style.display = 'none';
        if (presenceFab) presenceFab.style.display = 'none';
      }
    }

    function applyRolePermissions(role) {
      const r = (role || 'staff').toLowerCase();
      const isAdmin = (r === 'admin' || r === 'super admin');

      const navAccount = document.getElementById('nav-account');
      const navAppManager = document.getElementById('nav-app_manager');

      if (!isAdmin) {
        if (navAccount) navAccount.style.display = 'none';
        if (navAppManager) navAppManager.style.display = 'none';

        // If on protected page, redirect to dashboard
        const activePage = getActivePage();
        if (activePage === 'account' || activePage === 'app_manager') {
          loadPage('dashboard');
        }
      } else {
        if (navAccount) navAccount.style.display = 'flex';
        if (navAppManager) navAppManager.style.display = 'flex';
      }
    }

    async function checkAuth() {
      const { data: { session } } = await supabase.auth.getSession();
      updateAuthUI(session?.user || null);
    }

    function syncGreetingToIframe() {
      const iframe = document.getElementById('main-iframe');
      if (iframe && currentUserProfile) {
        const username = currentUserProfile.full_name || getUsernameFromEmail(currentUserProfile.email);
        const role = currentUserProfile.role || 'Staff';
        iframe.contentWindow.postMessage({
          type: 'greeting',
          username,
          role: role.charAt(0).toUpperCase() + role.slice(1)
        }, '*');
      }
    }

    // --- Sign In / Sign Out Flows ---
    document.getElementById('sign-in-btn').addEventListener('click', async function () {
        window.location.href = 'login.php';
    });

    const logoutModal = document.getElementById('logoutModal');

    document.getElementById('sign-out-btn').addEventListener('click', () => {
      logoutModal.style.display = 'flex';
    });

    document.getElementById('cancelLogout').addEventListener('click', () => {
      logoutModal.style.display = 'none';
    });

    document.getElementById('confirmLogout').addEventListener('click', async function () {
      try {
        // Destroy the server-side PHP session
        const res = await fetch('api/logout.php', { method: 'POST' });
        const data = await res.json();
        window.location.href = data.redirect || 'login.php';
      } catch (err) {
        showToast('Sign out failed. Please try again.', 'danger');
      }
    });

    // --- Toast Notifications ---
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
      toast.className = `toast ${type}`;
      const iconClass = (type === 'error' || type === 'danger') ? 'times-circle' : (type === 'success' ? 'check-circle' : 'info-circle');
      toast.innerHTML = `
        <div class="icon"><i class="fas fa-${iconClass}"></i></div>
        <div class="content"><strong>Notification</strong><br/>${msg}</div>
        <button class="close" aria-label="Close">&times;</button>
      `;
      container.appendChild(toast);
      requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
      const hide = () => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 200); };
      toast.querySelector('.close').onclick = hide;
      setTimeout(hide, 5000);
    }

    // --- Global Incident Alarm System ---
    let alarmQueue = [];
    let isShowingAlarm = false;
    let alarmTimer = null;
    let audioCtx = null;
    let currentAlarmIncident = null;
    let isAlarmSounding = false;

    function initGlobalAlarm() {
      console.log('[Alarm] Initializing banner controls...');
      const banner = document.getElementById('alarmBanner');
      const dismissBtn = document.getElementById('alarmDismissBtn');
      const stopSirenBtn = document.getElementById('alarmStopSirenBtn');
      
      if (!banner || !dismissBtn || !stopSirenBtn) {
        console.warn('[Alarm] Banner elements not found in DOM!');
        return;
      }

      banner.onclick = (e) => {
        if (e.target.closest('#alarmDismissBtn') || e.target.closest('#alarmStopSirenBtn')) return;
        if (currentAlarmIncident) {
          openGlobalIncidentModal(currentAlarmIncident.id);
        }
      };

      stopSirenBtn.onclick = (e) => {
        e.stopPropagation();
        stopAlarmSound();
        if (alarmQueue.length > 0) showNextAlarm();
        else hideAlarm();
      };

      dismissBtn.onclick = (e) => {
        e.stopPropagation();
        stopAlarmSound();
        if (alarmQueue.length > 0) showNextAlarm();
        else hideAlarm();
      };
    }

    function stopAlarmSound() {
      console.log('[Alarm] Stopping siren sound.');
      isAlarmSounding = false;
      if (audioCtx) {
        audioCtx.close();
        audioCtx = null;
      }
    }

    function playAlarmSound() {
      if (isAlarmSounding) return;
      isAlarmSounding = true;
      
      function runSirenCycle() {
        if (!isAlarmSounding) return;
        
        try {
          if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
          if (audioCtx.state === 'suspended') audioCtx.resume();
          
          const now = audioCtx.currentTime;
          const dur = 1.0; // Slightly longer cycles for "non-stop" feel
          
          const osc = audioCtx.createOscillator();
          const gain = audioCtx.createGain();
          
          osc.connect(gain);
          gain.connect(audioCtx.destination);
          
          osc.type = 'sawtooth';
          // Higher frequencies + sawtooth = "Loud/Pierce" feel
          osc.frequency.setValueAtTime(900, now);
          osc.frequency.exponentialRampToValueAtTime(400, now + dur * 0.8);
          
          gain.gain.setValueAtTime(0, now);
          gain.gain.linearRampToValueAtTime(0.3, now + 0.1); // Higher gain for "Loud"
          gain.gain.linearRampToValueAtTime(0, now + dur);
          
          osc.start(now);
          osc.stop(now + dur);
          
          // Loop the cycle
          setTimeout(runSirenCycle, dur * 1000);
        } catch (e) {
          console.warn('[Alarm] Siren failed:', e);
          isAlarmSounding = false;
        }
      }
      
      runSirenCycle();
    }
    function showNextAlarm() {
      const banner = document.getElementById('alarmBanner');
      const msgEl = document.getElementById('alarmMessage');
      const badge = document.getElementById('alarmQueueBadge');

      if (alarmQueue.length === 0 && !currentAlarmIncident) { hideAlarm(); return; }

      // If already showing an alert, just update the badge for any new ones in queue
      if (isShowingAlarm && currentAlarmIncident) {
        if (alarmQueue.length > 0) {
          badge.textContent = `+${alarmQueue.length} more`;
          badge.style.display = 'inline-block';
        }
        return;
      }

      const incident = alarmQueue.shift();
      console.log('[Alarm] Showing incident alert:', incident);
      currentAlarmIncident = incident;
      isShowingAlarm = true;
      const type = incident.type || 'Emergency';
      const reporter = incident.reporter || 'Anonymous';
      msgEl.textContent = `${type} reported by ${reporter}`;

      if (alarmQueue.length > 0) {
        badge.textContent = `+${alarmQueue.length} more`;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }

      banner.setAttribute('aria-hidden', 'false');
      console.log('[Alarm] Banner is now PERSISTENT until dismissed/stopped.');

      playAlarmSound();
      clearTimeout(alarmTimer);
    }

    function hideAlarm() {
      document.getElementById('alarmBanner').setAttribute('aria-hidden', 'true');
      isShowingAlarm = false;
      currentAlarmIncident = null;
      clearTimeout(alarmTimer);
    }

    function subscribeGlobalIncidents() {
      console.log('[Alarm] Initializing Realtime listener for incidents...');
      supabase.channel('global_incidents')
        .on('postgres_changes', { event: 'INSERT', schema: 'public', table: 'incidents' }, payload => {
          const incident = payload.new;
          const reportedAt = incident.reportedAt || incident.time || incident.created_at;

          // --- Alert Security Guard ---
          // Only trigger the loud siren alarm if the incident was reported in the last 60 seconds.
          // This prevents the alarm from going off when restoring old incidents from the archive.
          if (reportedAt) {
            const timeParsed = new Date(reportedAt).getTime();
            if (!isNaN(timeParsed)) {
              const diffMs = Date.now() - timeParsed;
              if (diffMs > 60000) {
                console.log('[Alarm] Skipping siren for old/restored incident:', incident.id);
                return;
              }
            }
          }

          console.log('[Alarm] SIGNAL RECEIVED:', incident);
          alarmQueue.push(incident);
          if (!isShowingAlarm) showNextAlarm();
        })
        .subscribe(status => {
          console.log('[Alarm] Subscription status:', status);
          if (status === 'CHANNEL_ERROR') {
            console.error('[Alarm] Realtime subscription failed. Check Supabase Dashboard for Realtime settings.');
          }
        });
    }

    // --- Supabase Auth State Change Listener ---
    supabase.auth.onAuthStateChange((_event, session) => {
      updateAuthUI(session?.user || null);
      if (!session || !session.user) {
        window.location.href = 'login.php';
      }
    });

    // --- Greeting on Dashboard via postMessage ---
    function setupDashboardGreeting() {
      const iframe = document.getElementById('main-iframe');
      if (!iframe) return;
      iframe.addEventListener('load', function () {
        syncGreetingToIframe();
      });
    }

    // --- Active Page Persistence ---
    function getActivePage() {
      // Priority: hash > localStorage > default
      const hash = window.location.hash.replace('#', '');
      if (hash && NAV_LINKS.some(l => l.page === hash)) {
        return hash;
      }
      const stored = localStorage.getItem('activePage');
      if (stored && NAV_LINKS.some(l => l.page === stored)) {
        return stored;
      }
      return 'dashboard';
    }

    function restoreActivePage() {
      const page = getActivePage();
      loadPage(page, false);
    }

    // --- Popstate for Browser Navigation ---
    window.addEventListener('popstate', function (event) {
      const page = (event.state && event.state.page) || getActivePage();
      loadPage(page, false);
    });

    // --- Accessibility: Keyboard Navigation for Sidebar ---
    function setupSidebarAccessibility() {
      const sidebar = document.getElementById('sidebar');
      if (!sidebar) return;
      sidebar.addEventListener('keydown', function (e) {
        const links = Array.from(sidebar.querySelectorAll('.sidebar-link'));
        const current = document.activeElement;
        let idx = links.indexOf(current);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          const next = links[(idx + 1) % links.length];
          next.focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prev = links[(idx - 1 + links.length) % links.length];
          prev.focus();
        }
      });
    }


    // =============================================================
    // ACTIVE USERS PRESENCE SYSTEM
    // =============================================================


    function renderActiveUsers(state) {
      console.log('[Presence] Rendering state:', state);
      const badge = document.getElementById('activeUsersBadge');
      const countEl = document.getElementById('activeUsersCount');
      const usersList = document.getElementById('activeUsersList');
      const searchInput = document.getElementById('presenceSearch');
      if (!badge || !countEl || !usersList) return;

      let rawUsers = [];
      try {
        const currentState = (presenceChannel && typeof presenceChannel.presenceState === 'function') 
                       ? presenceChannel.presenceState() 
                       : {};
        
        // Safely extract users without relying entirely on .flat() which might fail in older webviews
        const valuesNode = Object.values(currentState);
        valuesNode.forEach(arr => {
          if (Array.isArray(arr)) {
            arr.forEach(u => {
              if (u && (u.user_id || u.email || u.name)) {
                rawUsers.push(u);
              }
            });
          }
        });
      } catch (err) {
        console.warn('[Presence] State parsing warning:', err);
      }
      
      // Deduplicate and merge with local data
      const uniqueUsersMap = new Map();
      rawUsers.forEach(u => {
        const id = u.user_id || u.email || u.name;
        if (!id) return;
        if (!uniqueUsersMap.has(id)) {
          uniqueUsersMap.set(id, u);
        } else {
          const existing = uniqueUsersMap.get(id);
          const uTime = new Date(u.online_at || 0).getTime();
          const eTime = new Date(existing.online_at || 0).getTime();
          if (uTime > eTime) uniqueUsersMap.set(id, u);
        }
      });

      // FORCED FALLBACK: If I'm logged in but not in the presence state (sync lag), add myself
      if (currentAuthSession && currentAuthSession.user) {
        const myId = currentAuthSession.user.id;
        const myEmail = currentAuthSession.user.email;
        if (!uniqueUsersMap.has(myId) && !uniqueUsersMap.has(myEmail)) {
           console.log('[Presence] Patching local user into display list');
           uniqueUsersMap.set(myId, {
             user_id: myId,
             email: myEmail,
             name: currentUserProfile?.full_name || myEmail.split('@')[0],
             role: currentUserProfile?.role || 'Admin',
             status: 'Online',
             online_at: new Date().toISOString(),
             is_local_patch: true
           });
        }
      }

      const users = Array.from(uniqueUsersMap.values());
      const count = users.length;

      // Update UI counts
      badge.textContent = count;
      badge.style.display = count > 0 ? 'flex' : 'none';
      countEl.textContent = count;

      // Broadcast stats to iframe
      const iframe = document.getElementById('main-iframe');
      if (iframe && iframe.contentWindow) {
        const responders = users.filter(u => (u.role || '').toLowerCase().includes('responder')).length;
        iframe.contentWindow.postMessage({ type: 'presence-update', total: count, responders: responders }, '*');
      }

      if (count === 0) {
        usersList.innerHTML = `
          <div class="active-empty-state">
            <div class="pulse-ring"></div>
            <i class="fas fa-satellite-dish" style="font-size:2.5rem; color:var(--primary); margin-bottom:15px; opacity:0.6;"></i>
            <p style="font-weight:600;">Searching for active units...</p>
            <span style="font-size:0.8rem; color:var(--text-muted);">Real-time presence is active</span>
            <button onclick="startPresenceTracking()" style="margin-top:20px; font-size:0.75rem; color:var(--primary); background:none; border:none; text-decoration:underline; cursor:pointer;">
               <i class="fas fa-sync"></i> Reconnect
            </button>
          </div>
        `;
        return;
      }

      // Filter by search query if present
      let filteredUsers = users;
      if (searchInput && searchInput.value) {
        const query = searchInput.value.toLowerCase();
        filteredUsers = users.filter(u => 
          (u.name || '').toLowerCase().includes(query) || 
          (u.email || '').toLowerCase().includes(query) ||
          (u.role || '').toLowerCase().includes(query)
        );
      }

      const ROLE_CONFIG = {
        'Super Admin': { color: '#6d28d9', icon: 'shield-check', bg: '#f5f3ff' },
        'Admin': { color: '#2563eb', icon: 'shield-alt', bg: '#eff6ff' },
        'Responder': { color: '#f59e0b', icon: 'truck-medical', bg: '#fffbeb' },
        'Citizen': { color: '#10b981', icon: 'user', bg: '#ecfdf5' },
        'Default': { color: '#64748b', icon: 'user', bg: '#f8fafc' }
      };

      const getInitials = (n) => {
        if (!n) return '?';
        const parts = n.trim().split(/\s+/);
        if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
        return n.slice(0, 2).toUpperCase();
      };

      const timeAgo = (iso) => {
        if (!iso) return 'Online';
        const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
        if (diff < 30) return 'Just joined';
        if (diff < 60) return diff + 's ago';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        return Math.floor(diff / 3600) + 'h ago';
      };

      // Sort order config
      const ROLE_ORDER = ['super admin', 'admin', 'responder', 'citizen', 'staff'];
      filteredUsers.sort((a, b) => {
        const roleA = (a.role || 'citizen').toLowerCase();
        const roleB = (b.role || 'citizen').toLowerCase();
        let idxA = ROLE_ORDER.indexOf(roleA);
        let idxB = ROLE_ORDER.indexOf(roleB);
        if (idxA === -1) idxA = 99;
        if (idxB === -1) idxB = 99;
        return idxA - idxB;
      });

      usersList.innerHTML = filteredUsers.map(u => {
        const role = u.role || 'Admin';
        const config = ROLE_CONFIG[role] || ROLE_CONFIG['Default'];
        const roleClass = role.toLowerCase().replace(/\s+/g, '-');
        const name = u.name || u.email || 'Anonymous';
        const isMe = currentAuthSession && (u.user_id === currentAuthSession.user.id || u.email === currentAuthSession.user.email);

        const isActive = isMe || (Date.now() - new Date(u.online_at || 0).getTime()) < 300000; // 5 mins

        return `
          <div class="active-user-card ${isMe ? 'is-me' : ''}" style="border-left: 4px solid ${config.color}; opacity: ${isActive ? 1 : 0.6};">
            <div class="active-user-avatar" style="background:${config.color};">
              ${getInitials(name)}
              <span class="status-dot ${isActive ? 'online' : 'away'}"></span>
            </div>
            <div class="active-user-info">
              <div class="active-user-name" style="display:flex; align-items:center; gap:6px;">
                ${name} ${isMe ? '<span class="me-pill">YOU</span>' : ''}
                ${u.platform ? `<i class="fas fa-${u.platform.includes('Mobile') ? 'mobile-alt' : 'desktop'}" style="font-size:0.6rem; opacity:0.5;" title="${u.platform}"></i>` : ''}
              </div>
              <div class="active-user-role" style="font-size: 0.7rem; color: ${config.color}; font-weight: 700; text-transform: uppercase;">
                ${role}
              </div>
              <div class="active-user-time">
                <i class="fas fa-clock" style="font-size:0.7rem; margin-right:4px;"></i>${timeAgo(u.online_at)}
              </div>
            </div>
            <div class="active-role-group">
               <i class="fas fa-${config.icon}" style="color:${config.color}; font-size: 1.1rem; opacity: 0.4;"></i>
            </div>
          </div>
        `;
      }).join('');
    }

    let _presenceJoinPending = false;
    async function startPresenceTracking() {
      if (_presenceJoinPending) return;
      _presenceJoinPending = true;

      try {
        const { data: { session } } = await supabase.auth.getSession();
        if (!session || !session.user) {
          console.log('[Presence] No session found, skipping tracking');
          _presenceJoinPending = false;
          return;
        }

        if (presenceChannel) {
          console.log('[Presence] Re-joining channel...');
          await presenceChannel.unsubscribe();
        }
        
        currentAuthSession = session;
        console.log('[Presence] Creating channel for:', session.user.id);

        presenceChannel = supabase.channel('app_presence', {
          config: { presence: { key: session.user.id } }
        });

        presenceChannel
          .on('presence', { event: 'sync' }, () => {
            const newState = presenceChannel.presenceState();
            console.log('Synced state:', newState);
            renderActiveUsers();
          })
          .on('presence', { event: 'join' }, ({ newPresences }) => {
            console.log('User joined:', newPresences);
            renderActiveUsers();
          })
          .on('presence', { event: 'leave' }, ({ leftPresences }) => {
            console.log('User left:', leftPresences);
            renderActiveUsers();
          })
          .subscribe(async (status) => {
            console.log('[Presence] Channel status:', status);
            if (status === 'SUBSCRIBED') {
              try {
                // Fetch latest profile for tracking - use maybeSingle so it doesn't throw if not found
                const { data: profile } = await supabase.from('profiles').select('*').eq('id', session.user.id).maybeSingle();
                if (profile) currentUserProfile = profile;

                const presenceData = {
                  user_id: session.user.id,
                  email: session.user.email,
                  name: currentUserProfile?.full_name || session.user.email.split('@')[0],
                  role: currentUserProfile?.role || 'Admin',
                  status: 'Online',
                  online_at: new Date().toISOString(),
                  platform: 'Web Portal'
                };

                console.log('[Presence] Tracking:', presenceData);
                await presenceChannel.track(presenceData);
              } catch (trackErr) {
                console.error('[Presence] Error during tracking setup:', trackErr);
              }
            }
            if (status === 'CHANNEL_ERROR' || status === 'CLOSED') {
               _presenceJoinPending = false;
            }
          });
      } catch (err) {
        console.error('[Presence] Join failed:', err);
      } finally {
        setTimeout(() => { _presenceJoinPending = false; }, 2000);
      }
    }

    function initPresenceSystem() {
      if (!presenceUIInitialized) {
        const fab = document.getElementById('activeUsersBtn');
        const drawer = document.getElementById('activeUsersDrawer');
        const overlay = document.getElementById('activeDrawerOverlay');
        const closeBtn = document.getElementById('closeActiveDrawer');
        const searchInput = document.getElementById('presenceSearch');

        if (fab && drawer) {
          fab.onclick = () => { 
            drawer.classList.add('open'); 
            overlay.classList.add('open'); 
            // Force a render when opening to ensure local user shows up
            renderActiveUsers(); 
          };
          closeBtn.onclick = overlay.onclick = () => { drawer.classList.remove('open'); overlay.classList.remove('open'); };
          
          if (searchInput) {
            searchInput.oninput = () => {
              renderActiveUsers();
            };
          }

          document.addEventListener('keydown', e => { if (e.key === 'Escape') { drawer.classList.remove('open'); overlay.classList.remove('open'); } });
          presenceUIInitialized = true;
        }
      }
      startPresenceTracking();
    }

    // =============================================================
    // GLOBAL INCIDENT MODAL LOGIC
    // =============================================================
    let globalIncidentMap = null;
    const MAPTILER_GLOBAL_KEY = "31um5bDFFFAugzBg82HC";

    async function openGlobalIncidentModal(id) {
      const modal = document.getElementById('globalIncidentModal');
      const details = document.getElementById('globalModalDetails');
      const mapContainer = document.getElementById('globalIncidentMap');

      if (!modal || !details) return;

      // Reset UI
      details.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#64748b;"></i><p>Loading incident details...</p></div>';
      modal.style.display = 'flex';
      modal.setAttribute('aria-hidden', 'false');

      try {
        const { data, error } = await supabase.from('incidents').select('*').eq('id', id).single();
        if (error || !data) throw error || new Error('Not found');

        const type = data.type || 'Unknown';
        const location = data.location || (data.coords?.lat ? `${data.coords.lat}, ${data.coords.lng}` : 'N/A');
        const time = data.reportedAt ? new Date(data.reportedAt).toLocaleString() : 'N/A';
        const statusClass = (data.status || 'pending').toLowerCase().replace(/\s+/g, '');

        details.innerHTML = `
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
            <div>
              <h2 style="margin:0 0 4px; font-size:1.5rem;">${type} Incident</h2>
              <p style="margin:0; font-size:0.85rem; color:#64748b;">Report ID: ${data.incidentId || data.id}</p>
            </div>
            <span class="status-pill ${statusClass}">${data.status || 'Pending'}</span>
          </div>
          
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:20px;">
            <div class="detail-box" style="background:#f8fafc; padding:12px; border-radius:12px; border:1px solid #e2e8f0;">
              <small style="text-transform:uppercase; font-weight:800; color:#94a3b8; font-size:0.65rem; display:block; margin-bottom:4px;">Reported At</small>
              <div style="font-weight:600; font-size:0.88rem;">${time}</div>
            </div>
            <div class="detail-box" style="background:#f8fafc; padding:12px; border-radius:12px; border:1px solid #e2e8f0;">
              <small style="text-transform:uppercase; font-weight:800; color:#94a3b8; font-size:0.65rem; display:block; margin-bottom:4px;">Reporter</small>
              <div style="font-weight:600; font-size:0.88rem;">${(data.reporter || 'Anonymous').split('(')[0].trim()}</div>
            </div>
          </div>

          <div style="margin-bottom:20px;">
            <small style="text-transform:uppercase; font-weight:800; color:#94a3b8; font-size:0.65rem; display:block; margin-bottom:4px;">Location</small>
            <div style="font-weight:600; font-size:0.95rem;">${location}</div>
          </div>

          <div style="margin-bottom:20px;">
            <small style="text-transform:uppercase; font-weight:800; color:#94a3b8; font-size:0.65rem; display:block; margin-bottom:4px;">Description</small>
            <div style="line-height:1.5; color:#475569;">${data.description || 'No additional details provided.'}</div>
          </div>
        `;

        // Render Map if coordinates exist
        if (data.coords && data.coords.lat && data.coords.lng) {
          mapContainer.style.display = 'block';
          const lat = parseFloat(data.coords.lat);
          const lng = parseFloat(data.coords.lng);

          if (globalIncidentMap) {
            globalIncidentMap.remove();
            globalIncidentMap = null;
          }

          globalIncidentMap = L.map('globalIncidentMap', { zoomControl: false, attributionControl: false }).setView([lat, lng], 15);
          L.tileLayer(`https://api.maptiler.com/maps/streets/{z}/{x}/{y}.png?key=${MAPTILER_GLOBAL_KEY}`, {
            attribution: "© MapTiler © OpenStreetMap contributors"
          }).addTo(globalIncidentMap);
          L.marker([lat, lng]).addTo(globalIncidentMap);

          // Add zoom control manually to a better spot
          L.control.zoom({ position: 'topright' }).addTo(globalIncidentMap);

          // Force resize after modal is visible
          setTimeout(() => globalIncidentMap.invalidateSize(), 100);
        } else {
          mapContainer.style.display = 'none';
        }

      } catch (err) {
        console.error('Error fetching global incident:', err);
        details.innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><p>Could not load incident details. It may have been archived or deleted.</p></div>';
      }
    }

    function closeGlobalIncidentModal() {
      const modal = document.getElementById('globalIncidentModal');
      if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
      }
      if (globalIncidentMap) {
        globalIncidentMap.remove();
        globalIncidentMap = null;
      }
    }

    // Expose to window for iframe access
    window.openGlobalIncidentModal = openGlobalIncidentModal;

    // Listen for messages from iframes
    window.addEventListener('message', (event) => {
      if (event.data.type === 'open-incident') {
        openGlobalIncidentModal(event.data.id);
      }
    });

    // --- Account Requests Monitor ---
    async function monitorAccountRequests() {
      const badge = document.getElementById('requests-badge');
      if (!badge) return;

      const updateBadge = async () => {
        const { count, error } = await supabase
          .from('profiles')
          .select('*', { count: 'exact', head: true })
          .eq('status', 'Pending');

        if (!error && count > 0) {
          badge.textContent = count > 99 ? '99+' : count;
          badge.style.display = 'block';
        } else {
          badge.style.display = 'none';
        }
      };

      // Initial check
      await updateBadge();

      // Real-time subscription
      supabase
        .channel('profiles-pending')
        .on('postgres_changes', { event: '*', schema: 'public', table: 'profiles' }, updateBadge)
        .subscribe();
    }

    // --- Initialization ---

    document.addEventListener('DOMContentLoaded', function () {
      setSidebarYear();
      startLiveClock();
      setupSidebarNav();
      setupSidebarAccessibility();
      setupIframeErrorHandling();
      setupDashboardGreeting();
      restoreActivePage();
      checkAuth();
      initGlobalAlarm();
      subscribeGlobalIncidents();
      monitorAccountRequests();
      initPresenceSystem();
    });