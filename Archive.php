<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Archived Incidents</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="incident.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
  <header>
    <h1>Archived Incidents</h1>
    <p>Past reports stored for record-keeping</p>
  </header>

  <div class="controls">
    <div class="left">
      <input id="searchInput" type="text" placeholder="Search by ID, reporter, type, or location" />
      <select id="sortSelect" title="Sort">
        <option value="newest">Newest First</option>
        <option value="oldest">Oldest First</option>
      </select>
    </div>
  </div>

  <div class="table-wrap" aria-live="polite">
    <table class="incident-table" role="table" aria-label="Archived incidents table">
      <thead>
        <tr>
          <th>Type</th>
          <th>ID</th>
          <th>Location</th>
          <th>Time</th>
          <th>Reporter</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="archiveTableBody"></tbody>
    </table>
  </div>

  <!-- Supabase JS -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

  <script>
    // ---------- Supabase config ----------
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

    const archiveTableBody = document.getElementById("archiveTableBody");
    const searchInput = document.getElementById("searchInput");
    const sortSelect = document.getElementById("sortSelect");

    // Local cache
    let archivedCache = [];

    window.alert = function (msg, type = 'info') {
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
      const iconInfo = type === 'danger' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle';
      toast.innerHTML = `<div class="icon"><i class="fas fa-${iconInfo}"></i></div><div class="content"><strong>Notification</strong><br/>${msg}</div><button class="close">&times;</button>`;
      container.appendChild(toast);
      requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
      const hide = () => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 200); };
      toast.querySelector('.close').onclick = hide;
      setTimeout(hide, 5000);
    };

    // Helper: escape HTML
    function escapeHtml(s) {
      if (s === null || s === undefined) return "";
      return s.toString().replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    }

    function formatTime(raw) {
      if (!raw) return "N/A";
      const d = new Date(raw);
      return isNaN(d) ? raw : d.toLocaleString();
    }

    function getStatusClass(status) {
      if (!status) return "unknown";
      const s = status.toLowerCase();
      if (s.includes("pend")) return "pending";
      if (s.includes("enroute") || s.includes("ongoing")) return "enroute";
      if (s.includes("resolv")) return "resolved";
      if (s.includes("archiv")) return "archived";
      return "unknown";
    }

    // Render archived incidents
    function renderArchive() {
      archiveTableBody.innerHTML = "";

      const query = (searchInput.value || "").toLowerCase().trim();
      const sortBy = sortSelect.value;

      const keywords = query.split(/\s+/).filter(k => k.length > 0);
      let list = archivedCache.filter(row => {
        const incident = row.data || row;
        const searchStr = `${incident.incidentId} ${incident.incident_id} ${incident.type} ${incident.location} ${incident.reporter} ${incident.description || ''}`.toLowerCase();
        return keywords.every(k => searchStr.includes(k));
      });

      // Sort
      list.sort((a, b) => {
        const incidentA = a.data || a;
        const incidentB = b.data || b;
        const ta = Date.parse(incidentA.reportedAt || incidentA.reported_at || incidentA.time) || 0;
        const tb = Date.parse(incidentB.reportedAt || incidentB.reported_at || incidentB.time) || 0;
        return sortBy === "newest" ? tb - ta : ta - tb;
      });

      if (list.length === 0) {
        archiveTableBody.innerHTML = '<tr><td colspan="7" style="padding:18px;text-align:center;color:var(--muted)">No archived incidents found matching your search.</td></tr>';
        return;
      }

      list.forEach(row => {
        const incident = row.data || row;
        const id = row.id ?? incident.id ?? incident.incident_id ?? incident.incidentId ?? "";
        const visibleId = incident.incidentId || incident.incident_id || id;

        let icon = "";
        switch ((incident.type || "").toLowerCase()) {
          case "fire": icon = '<i class="fas fa-fire"></i>'; break;
          case "medical": icon = '<i class="fas fa-ambulance"></i>'; break;
          case "police": icon = '<i class="fas fa-user-shield"></i>'; break;
          case "rescue": icon = '<i class="fas fa-life-ring"></i>'; break;
          default: icon = '<i class="fas fa-exclamation-circle"></i>';
        }

        const isCoords = !incident.location && (incident.coords?.lat && incident.coords?.lng);
        const locationText = incident.location || (isCoords ? `${Number(incident.coords.lat).toFixed(4)}, ${Number(incident.coords.lng).toFixed(4)}` : "N/A");
        const timeText = formatTime(incident.reportedAt || incident.reported_at || incident.time);

        // Unique ID for the location cell
        const locCellId = `arc-loc-${id}-${Math.random().toString(36).substr(2, 5)}`;

        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td class="type-cell">${icon} ${escapeHtml(incident.type || "Unknown")}</td>
          <td>${escapeHtml(visibleId)}</td>
          <td id="${locCellId}">${escapeHtml(locationText)}</td>
          <td>${escapeHtml(timeText)}</td>
          <td>${escapeHtml((incident.reporter || "Anonymous").split('(')[0].trim())}</td>
          <td><span class="status ${getStatusClass(incident.status || "Archived")}">${escapeHtml(incident.status || "Archived")}</span></td>
          <td>
            <button class="btn restore-btn" data-id="${escapeHtml(id)}" style="padding: 4px 8px; font-size: 0.8rem;">
              <i class="fas fa-undo"></i> Restore
            </button>
          </td>
        `;
        archiveTableBody.appendChild(tr);

        // Resolve coordinates to street name automatically
        if (isCoords) {
          reverseGeocode(Number(incident.coords.lat), Number(incident.coords.lng)).then(address => {
            if (address) {
              const cell = document.getElementById(locCellId);
              if (cell) cell.textContent = address;
            }
          });
        }
      });

      // attach restore handlers
      archiveTableBody.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
          e.stopPropagation();
          const id = btn.dataset.id;
          if (!id) return alert('Invalid incident id.');

          if (!confirm('Restore this incident to active incidents?')) return;

          try {
            // Fetch archived row to ensure we have latest data
            const { data: archivedRow, error: fetchErr } = await supabaseClient
              .from('archived_incidents')
              .select('*')
              .eq('id', id)
              .limit(1)
              .single();

            if (fetchErr) {
              alert('Incident not found in archive.');
              return;
            }

            const { id: archivedId, archivedAt, ...incidentData } = archivedRow;

            // Insert into incidents table with correct case-sensitive names
            const { error: insertErr } = await supabaseClient
              .from('incidents')
              .insert([{
                ...incidentData,
                status: incidentData.status || 'Pending'
                // reportedAt is already inside incidentData
              }]);

            if (insertErr) throw insertErr;

            // Delete from archived_incidents
            const { error: deleteErr } = await supabaseClient
              .from('archived_incidents')
              .delete()
              .eq('id', archivedId);

            if (deleteErr) throw deleteErr;

            alert('Incident restored successfully!');
            fetchArchived(); // Refresh the table
          } catch (err) {
            alert('Restore failed: ' + (err.message || err));
          }
        });
      });
    }

    // Initial fetch of archived incidents
    async function fetchArchived() {
      try {
        const { data, error } = await supabaseClient
          .from('archived_incidents')
          .select('*');

        if (error) {
          console.error('Supabase fetch error:', error);
          archiveList.innerHTML = '<div style="padding:18px;color:var(--muted)">Error loading archived incidents.</div>';
          return;
        }

        archivedCache = (data || []).map(d => ({ id: d.id ?? null, data: d }));
        renderArchive();
      } catch (err) {
        console.error('Fetch archived failed:', err);
        archiveList.innerHTML = '<div style="padding:18px;color:var(--muted)">Error loading archived incidents.</div>';
      }
    }

    // Realtime subscription to archived_incidents table
    function subscribeArchivedRealtime() {
      try {
        const channel = supabaseClient.channel('public:archived_incidents')
          .on('postgres_changes', { event: '*', schema: 'public', table: 'archived_incidents' }, payload => {
            const ev = payload.eventType; // INSERT, UPDATE, DELETE
            const newRow = payload.new;
            const oldRow = payload.old;

            if (ev === 'INSERT') {
              archivedCache.unshift({ id: newRow.id ?? null, data: newRow });
            } else if (ev === 'UPDATE') {
              const id = newRow.id ?? null;
              const idx = archivedCache.findIndex(r => r.id === id);
              if (idx !== -1) archivedCache[idx] = { id, data: newRow };
              else archivedCache.unshift({ id, data: newRow });
            } else if (ev === 'DELETE') {
              const id = oldRow.id ?? null;
              archivedCache = archivedCache.filter(r => r.id !== id);
            }

            renderArchive();
          })
          .subscribe((status) => {
            // optional: handle status updates
          });

        window._archivedRealtimeChannel = channel;
      } catch (err) {
        console.warn('Realtime subscription failed (check Supabase Realtime setup):', err);
      }
    }

    searchInput.addEventListener("input", renderArchive);
    sortSelect.addEventListener("change", renderArchive);

    // Init
    (async function init() {
      await fetchArchived();
      subscribeArchivedRealtime();
    })();
  </script>
</body>

</html>