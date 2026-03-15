console.log('%c [Module] Dashboard v8.0 Active ', 'color: #8b5cf6; font-weight: bold;');
/* dashboard.js — Dashboard page logic */

// ---------- Supabase config ----------
    const SUPABASE_URL = 'https://tukkkwtxuaxrbihyammp.supabase.co';
    const SUPABASE_KEY = 'sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P';
    // Use the global supabase object from the CDN
    const supabaseDashboard = supabase.createClient(SUPABASE_URL, SUPABASE_KEY);
    const MAPTILER_KEY = "31um5bDFFFAugzBg82HC";

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

    // ---------- Elements ----------
    const incidentTableBody = document.getElementById("incidentTableBody");
    const activeCountEl = document.getElementById("activeCount");
    const avgResponseEl = document.getElementById("avgResponse");
    const responderCountEl = document.getElementById("responderCount");
    const resolvedCountEl = document.getElementById("resolvedCount");
    const totalReportsEl = document.getElementById("totalReports");
    const totalResolvedEl = document.getElementById("totalResolved");
    const resolutionRateEl = document.getElementById("resolutionRate");

    // ---------- Chart setup ----------
    let chart;
    const ctx = document.getElementById('incidentChart').getContext('2d');
    chart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Fire', 'Medical', 'Rescue', 'Police', 'Others'],
        datasets: [{
          data: [0, 0, 0, 0, 0],
          backgroundColor: ['#e74c3c', '#3498db', '#2ecc71', '#9b59b6', '#f1c40f']
        }]
      },
      options: { plugins: { legend: { position: 'bottom' } } }
    });

    // ---------- Local cache ----------
    let allIncidents = [];
    let currentSort = { key: 'time', direction: 'desc' };
    let searchQuery = '';
    let typeFilter = '';
    let statusFilter = '';

    // ---------- Helpers ----------
    function formatTime(raw) {
      if (!raw) return "N/A";
      const d = new Date(raw);
      return isNaN(d) ? raw : d.toLocaleString();
    }

    function getStatusClass(status) {
      if (!status) return "unknown";
      const s = status.toLowerCase();
      if (s.includes("pend") || s.includes("await")) return "pending";
      if (s.includes("enroute") || s.includes("on-going") || s.includes("ongoing") || s.includes("en route") || s.includes("dispatch")) return "enroute";
      if (s.includes("resolv") || s.includes("closed") || s.includes("done")) return "resolved";
      return "unknown";
    }

    // ---------- Render ----------
    function renderUI() {
      if (!incidentTableBody) return;

      // Reset Table
      incidentTableBody.innerHTML = "";

      // 1. Filtering
      let filtered = allIncidents.filter(incident => {
        const typeMatch = !typeFilter || incident.type === typeFilter;
        let statusMatch = true;
        if (statusFilter) {
          const s = (incident.status || '').toLowerCase();
          const target = statusFilter.toLowerCase();
          statusMatch = s.includes(target);
        }

        const searchLower = searchQuery.toLowerCase();
        const keywords = searchLower.split(/\s+/).filter(k => k.length > 0);
        const combined = `${incident.incidentId || incident.id || ''} ${incident.type || ''} ${incident.location || ''} ${incident.description || ''} ${incident.reporter || ''}`.toLowerCase();
        const searchMatch = keywords.length === 0 || keywords.every(k => combined.includes(k));

        return typeMatch && statusMatch && searchMatch;
      });

      // 2. Sorting
      filtered.sort((a, b) => {
        let valA, valB;
        if (currentSort.key === 'time') {
          valA = Date.parse(a.reportedAt || a.time) || 0;
          valB = Date.parse(b.reportedAt || b.time) || 0;
        } else {
          valA = (a[currentSort.key] || '').toString().toLowerCase();
          valB = (b[currentSort.key] || '').toString().toLowerCase();
        }

        if (valA < valB) return currentSort.direction === 'asc' ? -1 : 1;
        if (valA > valB) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
      });

      // 3. Stats Calculation (From ALL incidents for global dashboard metric consistency)
      let fire = 0, medical = 0, rescue = 0, police = 0, others = 0;
      let active = 0, resolvedToday = 0;
      let totalResponseTime = 0;
      let resolvedCountForAvg = 0;
      const todayStr = new Date().toISOString().slice(0, 10);

      allIncidents.forEach(incident => {
        const typeText = incident.type || incident.incident_type || "Unknown";
        switch (typeText.toLowerCase()) {
          case "fire": fire++; break;
          case "medical": medical++; break;
          case "rescue": rescue++; break;
          case "police": police++; break;
          default: others++;
        }
        const s = (incident.status || "").toLowerCase();
        const isResolved = s.includes("resolv") || s.includes("closed") || s.includes("done");
        
        // Robust date extraction
        const rawDate = incident.reportedAt || incident.reported_at || incident.time || incident.created_at;
        const incidentDate = rawDate ? new Date(rawDate) : null;
        const incidentDateStr = incidentDate && !isNaN(incidentDate.getTime()) ? incidentDate.toISOString().slice(0, 10) : null;

        if (isResolved) {
          if (incidentDateStr === todayStr) resolvedToday++;
          
          // Calculate real response time if available
          const start = incidentDate;
          const endRaw = incident.acceptedAt || incident.accepted_at || incident.respondedAt || incident.responded_at;
          const end = endRaw ? new Date(endRaw) : null;
          
          if (start && end && !isNaN(start.getTime()) && !isNaN(end.getTime())) {
            const diffMin = (end.getTime() - start.getTime()) / (1000 * 60);
            if (diffMin > 0) {
              totalResponseTime += diffMin;
              resolvedCountForAvg++;
            }
          }
        } else {
          active++;
        }
      });

      // 4. Update Header Metrics
      activeCountEl.textContent = active;
      resolvedCountEl.textContent = resolvedToday;
      totalReportsEl.textContent = allIncidents.length;
      if (resolvedCountForAvg > 0) {
        avgResponseEl.textContent = `${(totalResponseTime / resolvedCountForAvg).toFixed(1)}m`;
      } else {
        avgResponseEl.textContent = "N/A";
      }

      // 5. Update Breakdown & Chart
      ['fire', 'medical', 'rescue', 'police', 'others'].forEach(t => {
        const el = document.getElementById(`bd-${t}`);
        if (el) {
          if (t === 'fire') el.textContent = fire;
          if (t === 'medical') el.textContent = medical;
          if (t === 'rescue') el.textContent = rescue;
          if (t === 'police') el.textContent = police;
          if (t === 'others') el.textContent = others;
        }
      });
      if (totalResolvedEl) totalResolvedEl.textContent = resolvedCountForAvg;
      if (resolutionRateEl && allIncidents.length > 0) {
        resolutionRateEl.textContent = `${((resolvedCountForAvg / allIncidents.length) * 100).toFixed(0)}%`;
      }
      chart.data.datasets[0].data = [fire, medical, rescue, police, others];
      chart.update();

      // 6. Display Filtered Table Rows
      const displayList = filtered.slice(0, 8); // Still limit to 8 for dashboard aesthetics

      if (displayList.length === 0) {
        incidentTableBody.innerHTML = `<tr><td colspan="4" style="padding:40px; text-align:center; color:var(--text-muted);"><i class="fas fa-search-minus" style="font-size:1.5rem; margin-bottom:10px; display:block;"></i>No matching incidents found.</td></tr>`;
      } else {
        displayList.forEach(incident => {
          const statusText = incident.status || "Pending";
          const isCoords = !incident.location && (incident.coords?.lat && incident.coords?.lng);
          const locationText = incident.location || (isCoords ? `${Number(incident.coords.lat).toFixed(4)}, ${Number(incident.coords.lng).toFixed(4)}` : "N/A");
          const displayTime = formatTime(incident.reportedAt || incident.reported_at || incident.time || incident.created_at);
          const displayType = incident.type || incident.incident_type || "Unknown";
          const docId = incident.id || incident.incidentId || "inc";
          
          const tr = document.createElement("tr");
          tr.style.cursor = "pointer";
          const locCellId = `d-loc-${docId}-${Math.random().toString(36).substr(2, 5)}`;
          
          tr.onclick = () => window.parent.openGlobalIncidentModal(incident.id || incident.incidentId);
          tr.innerHTML = `
            <td>${displayTime}</td>
            <td>${displayType}</td>
            <td id="${locCellId}">${locationText}</td>
            <td><span class="status ${getStatusClass(statusText)}">${statusText}</span></td>
          `;
          incidentTableBody.appendChild(tr);

          if (isCoords) {
            reverseGeocode(Number(incident.coords.lat), Number(incident.coords.lng)).then(address => {
              if (address) {
                const cell = document.getElementById(locCellId);
                if (cell) cell.textContent = address;
              }
            });
          }
        });
      }
    }

    async function fetchResponders() {
      try {
        const { count, error } = await supabaseDashboard
          .from('profiles')
          .select('*', { count: 'exact', head: true })
          .eq('role', 'responder')
          .eq('status', 'Active');

        if (!error && count !== null) {
          responderCountEl.textContent = count;
        } else {
          responderCountEl.textContent = "8"; // Fallback
        }
      } catch (err) {
        console.warn("Responder count fetch failed:", err);
        responderCountEl.textContent = "8";
      }
    }

    // ---------- Realtime Listener ----------
    function subscribeRealtime() {
      supabaseDashboard.channel('dashboard_incidents')
        .on('postgres_changes', { event: '*', schema: 'public', table: 'incidents' }, payload => {
          const ev = payload.eventType;
          const newRow = payload.new;
          const oldRow = payload.old;

          if (ev === 'INSERT') {
            allIncidents.push(newRow);
          } else if (ev === 'UPDATE') {
            const idx = allIncidents.findIndex(r => r.id === newRow.id);
            if (idx !== -1) allIncidents[idx] = newRow;
            else allIncidents.push(newRow);
          } else if (ev === 'DELETE') {
            allIncidents = allIncidents.filter(r => r.id !== oldRow.id);
          }
          renderUI();
        })
        .subscribe();

      // Update responder count if profiles change
      supabaseDashboard.channel('dashboard_responders')
        .on('postgres_changes', { event: '*', schema: 'public', table: 'profiles' }, () => {
          fetchResponders();
        })
        .subscribe();
    }

    // ---------- Fetch History ----------
    async function fetchIncidents() {
      try {
        console.log("Dashboard: Fetching incidents...");
        const { data, error } = await supabaseDashboard.from('incidents').select('*');
        if (error) {
          console.error("Dashboard Supabase error:", error);
          throw error;
        }
        console.log("Dashboard: Data received:", data?.length || 0, "rows");
        allIncidents = data || [];
        renderUI();
      } catch (err) {
        console.error("Dashboard fetchIncidents failed:", err);
      }
    }

    // ---------- Init ----------
    (async function init() {
      // 1. Listen for parent greeting
      window.addEventListener('message', (event) => {
        if (event.data.type === 'greeting') {
          const { username, role } = event.data;
          const greetingEl = document.getElementById('welcome-message');
          if (greetingEl) {
            greetingEl.textContent = `Welcome back, ${username} — ${role}`;
          }
        }
        if (event.data.type === 'presence-update') {
          const { responders } = event.data;
          if (responderCountEl) {
            responderCountEl.textContent = responders || 0;
          }
        }
      });

      // Update static date
      const dateEl = document.getElementById('header-date');
      if (dateEl) {
        dateEl.textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
      }

      // 2. Initial Data
      await fetchIncidents();
      await fetchResponders();

      // 3. Start Live Subscriptions
      subscribeRealtime();

      // 4. Handle Inputs
      document.getElementById('searchInput').addEventListener('input', e => {
        searchQuery = e.target.value;
        renderUI();
      });
      document.getElementById('typeFilter').addEventListener('change', e => {
        typeFilter = e.target.value;
        renderUI();
      });
      document.getElementById('statusFilter').addEventListener('change', e => {
        statusFilter = e.target.value;
        renderUI();
      });

      // 5. Sorting Controls
      document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
          const key = th.dataset.sort;
          if (currentSort.key === key) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
          } else {
            currentSort.key = key;
            currentSort.direction = 'asc';
          }

          // Update Icons
          document.querySelectorAll('th.sortable').forEach(header => {
            header.classList.remove('active');
            const icon = header.querySelector('i');
            icon.className = 'fas fa-sort';
          });
          th.classList.add('active');
          const finalIcon = th.querySelector('i');
          finalIcon.className = currentSort.direction === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';

          renderUI();
        });
      });

      // 6. Request greeting from parent
      window.parent.postMessage({ type: 'request-greeting' }, '*');
    })();