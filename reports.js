console.log('%c [Module] Reports System v8.0 Active ', 'color: #3b82f6; font-weight: bold;');
/* reports.js — Reports page logic */

// Replace with your Supabase project URL and anon key
  const SUPABASE_URL = "https://tukkkwtxuaxrbihyammp.supabase.co";
  const SUPABASE_ANON_KEY = "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P";
  const MAPTILER_KEY = "31um5bDFFFAugzBg82HC";

  const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
  
  // ---------- Reverse Geocoding ----------
  const _geocodeCache = new Map();

  async function reverseGeocode(lat, lng) {
    const key = `${lat.toFixed(5)},${lng.toFixed(5)}`;
    if (_geocodeCache.has(key)) return _geocodeCache.get(key);

    try {
      const url = `https://api.maptiler.com/geocoding/${lng},${lat}.json?key=${MAPTILER_KEY}&language=en`;
      const res = await fetch(url);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = await res.json();
      const features = json.features || [];
      let address = null;

      for (const feature of features) {
        const p = feature.properties || {};
        const ctx = feature.context || [];
        const fromCtx = (prefix) => ctx.find(c => c.id?.startsWith(prefix))?.text || '';
        const street = p.address || p.name || '';
        const barangay = fromCtx('neighborhood') || fromCtx('locality');
        const city = fromCtx('place');
        const province = fromCtx('region');

        if (street || barangay) {
          const parts = [street, barangay, city, province].filter(Boolean);
          address = parts.join(', ');
          break;
        }
        if (feature.place_name) {
          const segments = feature.place_name.split(', ');
          if (segments.length > 1) segments.pop();
          address = segments.join(', ');
          break;
        }
      }

      if (!address) {
        const nomRes = await fetch(
          `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1&zoom=18`,
          { headers: { 'User-Agent': 'BarangayEmergencySystem/1.0', 'Accept-Language': 'en' } }
        );
        if (nomRes.ok) {
          const nomJson = await nomRes.json();
          address = _buildNominatimAddress(nomJson.address) || nomJson.display_name || null;
        }
      }

      _geocodeCache.set(key, address);
      return address;
    } catch (err) {
      console.warn('Reverse geocode failed:', err);
      return null;
    }
  }

  function _buildNominatimAddress(addr) {
    if (!addr) return null;
    const houseNum = addr.house_number || '';
    const road = addr.road || addr.pedestrian || addr.footway
      || addr.path || addr.cycleway || addr.service || '';
    const street = [houseNum, road].filter(Boolean).join(' ');
    const barangay = addr.neighbourhood || addr.suburb || addr.quarter
      || addr.hamlet || addr.village || '';
    const city = addr.city || addr.town || addr.city_district
      || addr.municipality || '';
    const province = addr.state_district || addr.province || addr.state || '';
    return [street, barangay, city, province].filter(Boolean).join(', ') || null;
  }

  // Show today's date
  document.getElementById("date").textContent =
    "Date: " + new Date().toLocaleDateString();

  const reportsList = document.getElementById("reports-list");
  const totalEl = document.getElementById("total");
  const resolvedEl = document.getElementById("resolved");
  const pendingEl = document.getElementById("pending");

  // Local cache
  let incidentsCache = [];

  // Icon mapping helper
  function incidentIcon(type) {
    switch ((type || "").toLowerCase()) {
      case "fire": return "🔥";
      case "medical": return "💉";
      case "police": return "🚔";
      case "rescue": return "🛟";
      default: return "⚠️";
    }
  }

  function formatTime(raw) {
    if (!raw) return "N/A";
    const d = new Date(raw);
    if (!isNaN(d)) return d.toLocaleString();
    return raw;
  }

  // Render reports list and metrics from cache
  function renderReports() {
    if (!reportsList) return;
    reportsList.innerHTML = "";

    const q = (document.getElementById("searchInput")?.value || "").trim().toLowerCase();
    const sortOrder = document.getElementById("sortSelect")?.value || "newest";
    const activeFilter = document.querySelector(".filter-btn.active")?.dataset?.status || "all";

    let list = incidentsCache.slice();

    // 1. Filtering by Status
    if (activeFilter !== "all") {
      list = list.filter(row => (row.status || "Pending").toLowerCase() === activeFilter.toLowerCase());
    }

    // 2. Searching
    if (q) {
      const keywords = q.split(/\s+/).filter(k => k.length > 0);
      list = list.filter(row => {
        const id = (row.incidentId || row.id || "").toString().toLowerCase();
        const type = (row.type || "").toString().toLowerCase();
        const location = (row.location || (row.coords?.lat && row.coords?.lng ? `${row.coords.lat}, ${row.coords.lng}` : "")).toString().toLowerCase();
        const reporter = (row.reporter || "").toString().toLowerCase();
        const description = (row.description || "").toString().toLowerCase();
        const combined = `${id} ${type} ${location} ${reporter} ${description}`;
        return keywords.every(k => combined.includes(k));
      });
    }

    // 3. Sorting
    list.sort((a, b) => {
      if (sortOrder === "type") {
        return (a.type || "").localeCompare(b.type || "");
      }
      const ta = Date.parse(a.reportedAt || a.time) || 0;
      const tb = Date.parse(b.reportedAt || b.time) || 0;
      return sortOrder === "newest" ? tb - ta : ta - tb;
    });

    let total = 0, resolved = 0, pending = 0;

    // Calculate totals based on ALL data, not just filtered list? 
    // Usually summary cards show the overall stats.
    incidentsCache.forEach(row => {
      total++;
      if ((row.status || "Pending").toLowerCase() === "resolved") resolved++;
      else pending++;
    });

    if (list.length === 0) {
      reportsList.innerHTML = '<li style="padding:18px;color:var(--muted);justify-content:center;">No matching reports found.</li>';
    }

    list.forEach(row => {
      const icon = incidentIcon(row.type);
      const id = row.incidentId || row.id || "";
      const reporterDisplay = (row.reporter || "Anonymous").split('(')[0].trim();
      const isCoords = !row.location && (row.coords?.lat && row.coords?.lng);
      const location = row.location || (isCoords ? `${Number(row.coords.lat).toFixed(4)}, ${Number(row.coords.lng).toFixed(4)}` : "N/A");
      const status = row.status || "Pending";
      const savedReports = JSON.parse(localStorage.getItem('savedReports') || '[]');
      const isSaved = savedReports.includes(row.id || row.incidentId);

      // Unique ID for the specific location span in this list item
      const locSpanId = `re-loc-${id}-${Math.random().toString(36).substr(2, 5)}`;

      const li = document.createElement("li");
      li.setAttribute("role", "article");
      li.innerHTML = `
          <div>
            <strong>Incident Report - ${escapeHtml(id || "(no id)")}</strong>
            <span class="details">
              <span aria-hidden="true">${icon}</span> ${escapeHtml(row.type || "Unknown")} — <span id="${locSpanId}">${escapeHtml(location)}</span> — 
              <span class="status-indicator ${status.toLowerCase() === 'resolved' ? 'resolved' : 'pending'}" aria-label="Status: ${status}">
                ${escapeHtml(status)}
              </span>
            </span>
            <div style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">Reporter: ${escapeHtml(reporterDisplay)}</div>
          </div>
          <div class="actions">
            <button class="btn view" data-id="${escapeHtml(row.id || id)}" aria-label="View details for report ${id}"><i class="fas fa-eye" aria-hidden="true"></i> View</button>
            <button class="btn save" data-id="${escapeHtml(row.id || id)}" aria-label="Save report ${id}">
              ${isSaved ? '<i class="fas fa-bookmark"></i> Pinned' : '<i class="fas fa-save"></i> Save'}
            </button>
            <button class="btn export" data-id="${escapeHtml(row.id || id)}" aria-label="Export report ${id} to PDF"><i class="fas fa-file-pdf" aria-hidden="true"></i> Export</button>
          </div>
        `;
      reportsList.appendChild(li);

      // Resolve coordinates to street name automatically
      if (isCoords) {
        reverseGeocode(Number(row.coords.lat), Number(row.coords.lng)).then(address => {
          if (address) {
            const span = document.getElementById(locSpanId);
            if (span) span.textContent = address;
          }
        });
      }
    });

    totalEl.textContent = total;
    resolvedEl.textContent = resolved;
    pendingEl.textContent = pending;

    // Attach handlers (delegated approach could be used; here we attach per-render)
    reportsList.querySelectorAll('.view').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = btn.dataset.id;
        viewIncident(id);
      });
    });
    reportsList.querySelectorAll('.save').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = btn.dataset.id;
        toggleSaveReport(id, btn);
      });
    });
    reportsList.querySelectorAll('.export').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = btn.dataset.id;
        exportReport(id);
      });
    });
  }

  // Escape helper
  function escapeHtml(s) {
    if (s === null || s === undefined) return "";
    return s.toString().replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  // ---------- Initial fetch ----------
  async function fetchIncidents() {
    try {
      const { data, error } = await supabaseClient
        .from('incidents')
        .select('*');

      if (error) {
        console.error('Supabase fetch error:', error);
        reportsList.innerHTML = '<li style="padding:18px;color:var(--muted)">Error loading reports.</li>';
        return;
      }

      incidentsCache = (data || []).map(r => ({ id: r.id ?? null, ...r }));
      renderReports();
    } catch (err) {
      console.error('Fetch failed:', err);
      reportsList.innerHTML = '<li style="padding:18px;color:var(--muted)">Error loading reports.</li>';
    }
  }

  // ---------- Realtime subscription ----------
  function subscribeRealtime() {
    try {
      const channel = supabaseClient.channel('public:incidents')
        .on('postgres_changes', { event: '*', schema: 'public', table: 'incidents' }, payload => {
          const ev = payload.eventType; // INSERT, UPDATE, DELETE
          const newRow = payload.new;
          const oldRow = payload.old;

          if (ev === 'INSERT') {
            incidentsCache.push({ id: newRow.id ?? null, ...newRow });
          } else if (ev === 'UPDATE') {
            const id = newRow.id ?? null;
            const idx = incidentsCache.findIndex(r => (r.id ?? r.incidentId) === id);
            if (idx !== -1) incidentsCache[idx] = { id, ...newRow };
            else incidentsCache.push({ id, ...newRow });
          } else if (ev === 'DELETE') {
            const id = oldRow.id ?? null;
            incidentsCache = incidentsCache.filter(r => (r.id ?? r.incidentId) !== id);
          }

          renderReports();
        })
        .subscribe((status) => {
          // optional: handle status updates
        });

      window._reportsRealtimeChannel = channel;
    } catch (err) {
      console.warn('Realtime subscription failed (check Supabase Realtime setup):', err);
    }
  }

  // ---------- GUI Modal Helpers ----------
  function showConfirm({ title, text, icon, type = 'info', confirmText = 'Confirm' }) {
    return new Promise((resolve) => {
      const modal = document.getElementById('confirmModal');
      const iconEl = document.getElementById('confirmIcon');
      const circle = document.getElementById('confirmIconCircle');
      const okBtn = document.getElementById('confirmOkBtn');

      document.getElementById('confirmTitle').textContent = title;
      document.getElementById('confirmText').textContent = text;
      iconEl.className = `fas fa-${icon || 'question'}`;
      okBtn.textContent = confirmText;

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
        document.getElementById('confirmCancelBtn').removeEventListener('click', handleCancel);
      }

      okBtn.addEventListener('click', handleConfirm);
      document.getElementById('confirmCancelBtn').addEventListener('click', handleCancel);
    });
  }

  function showCustomAlert({ title, text, icon, type = 'info' }) {
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

    const iconHtml = `<div class="icon"><i class="fas fa-${icon || 'info-circle'}"></i></div>`;
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

  // ---------- Actions ----------
  function toggleSaveReport(id, btn) {
    let saved = JSON.parse(localStorage.getItem('savedReports') || '[]');
    const index = saved.indexOf(id);
    
    if (index === -1) {
      saved.push(id);
      showCustomAlert({ title: 'Report Pinned', text: `Report ${id} has been saved to your local workspace.`, icon: 'bookmark', type: 'success' });
      if (btn) btn.innerHTML = '<i class="fas fa-bookmark"></i> Pinned';
    } else {
      saved.splice(index, 1);
      showCustomAlert({ title: 'Report Unpinned', text: `Report ${id} removed from your workspace.`, icon: 'trash-alt', type: 'info' });
      if (btn) btn.innerHTML = '<i class="fas fa-save"></i> Save';
    }
    
    localStorage.setItem('savedReports', JSON.stringify(saved));
  }
  function exportReport(id) {
    const report = incidentsCache.find(r => r.id === id);
    if (!report) return showCustomAlert({ title: 'Error', text: 'Report data not found.', type: 'danger' });

    const headers = ["Field", "Value"];
    const rows = [
      ["Incident ID", report.incidentId || report.id],
      ["Type", report.type],
      ["Location", report.location || (report.coords?.lat ? `${report.coords.lat}, ${report.coords.lng}` : 'N/A')],
      ["Reported At", report.reportedAt || report.time],
      ["Status", report.status || 'Pending'],
      ["Reporter", report.reporter || 'Anonymous'],
      ["Description", (report.description || 'No description').replace(/\n/g, ' ')]
    ];

    let csvContent = "data:text/csv;charset=utf-8," + headers.join(",") + "\n" + rows.map(e => e.join(",")).join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Incident_Report_${report.incidentId || report.id}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showCustomAlert({ title: 'Exported', text: `Report ${id} has been exported to CSV!`, icon: 'file-csv', type: 'success' });
  }
  function viewIncident(id) {
    if (window.parent && window.parent.openGlobalIncidentModal) {
      window.parent.openGlobalIncidentModal(id);
    } else {
      window.location.href = "incident.php?id=" + encodeURIComponent(id);
    }
  }

  function exportAllReports() {
    if (incidentsCache.length === 0) return showCustomAlert({ title: 'No Data', text: 'No reports available to export.', type: 'warning' });

    const headers = ["ID", "Type", "Location", "Reported At", "Status", "Reporter", "Description"];
    const rows = incidentsCache.map(r => [
      r.incidentId || r.id,
      r.type,
      r.location || (r.coords?.lat ? `${r.coords.lat}, ${r.coords.lng}` : 'N/A'),
      r.reportedAt || r.time,
      r.status || 'Pending',
      r.reporter || 'Anonymous',
      (r.description || '').replace(/\n/g, ' ').replace(/,/g, ';')
    ]);

    let csvContent = "data:text/csv;charset=utf-8," + headers.join(",") + "\n" + rows.map(e => e.join(",")).join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `All_Incident_Reports_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showCustomAlert({ title: 'Full Export', text: 'All report data has been exported successfully.', icon: 'file-csv', type: 'success' });
  }

  // ---------- Init ----------
  (async function init() {
    await fetchIncidents();
    subscribeRealtime();

    // UI Listeners
    const searchInput = document.getElementById("searchInput");
    const sortSelect = document.getElementById("sortSelect");
    const filterBtns = document.querySelectorAll(".filter-btn");
    const exportAllBtn = document.getElementById("exportAllBtn");

    if (exportAllBtn) {
      exportAllBtn.addEventListener('click', exportAllReports);
    }

    if (searchInput) {
      searchInput.addEventListener("input", () => renderReports());
    }
    if (sortSelect) {
      sortSelect.addEventListener("change", () => renderReports());
    }
    filterBtns.forEach(btn => {
      btn.addEventListener("click", () => {
        filterBtns.forEach(b => {
          b.classList.remove("active");
          b.setAttribute("aria-pressed", "false");
        });
        btn.classList.add("active");
        btn.setAttribute("aria-pressed", "true");
        renderReports();
      });
    });

    // Debounced search for HCI efficiency
    let searchTimeout;
    if (searchInput) {
      searchInput.addEventListener("input", () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => renderReports(), 300);
      });
    }
  })();