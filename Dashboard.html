<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Dashboard | Barangay Emergency System</title>
  <meta name="description"
    content="Admin Dashboard for tracking live incidents, responder status, and response metrics for the Barangay Based Emergency Response System.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  <style>
    .pulse-red {
      animation: pulse-red 2s infinite;
      border-radius: 50%;
    }

    @keyframes pulse-red {
      0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
      }

      70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
      }

      100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
      }
    }

    .pulse-indigo {
      animation: pulse-indigo 2s infinite;
      border-radius: 50%;
    }

    @keyframes pulse-indigo {
      0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7);
      }

      70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
      }

      100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
      }
    }

    /* Table Toolbar & Filters */
    .table-controls {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 24px;
      align-items: center;
      justify-content: flex-start;
      /* Better for natural wrapping */
    }

    .search-input-wrap {
      flex: 1 1 300px;
      /* Allow growth, base 300px */
      min-width: 0;
      /* Essential flexbox fix for children width */
      max-width: 450px;
      position: relative;
    }

    .search-input-wrap i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 0.9rem;
      pointer-events: none;
      z-index: 1;
      /* Keep icon above input background */
    }

    .search-input-wrap input {
      width: 100%;
      padding: 11px 16px 11px 40px;
      border-radius: 12px;
      border: 1px solid var(--border);
      background: #f8fafc;
      font-size: 0.92rem;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: var(--shadow-sm);
      display: block;
      /* Removes inline spacing issues */
      box-sizing: border-box;
    }

    .search-input-wrap input:focus {
      outline: none;
      border-color: var(--primary);
      background: #fff;
      box-shadow: 0 0 0 4px var(--primary-glow), var(--shadow-md);
    }

    .filter-group {
      display: flex;
      gap: 12px;
      flex-shrink: 0;
      /* Prevent dropdowns from shrinking */
      flex-wrap: wrap;
    }

    .filter-select {
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: #f8fafc;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-main);
      cursor: pointer;
      transition: all 0.2s;
    }

    .filter-select:focus {
      outline: none;
      border-color: var(--primary);
    }

    th.sortable {
      cursor: pointer;
      user-select: none;
      transition: background 0.2s;
    }

    th.sortable:hover {
      background: #f1f5f9;
    }

    th.sortable i {
      margin-left: 6px;
      font-size: 0.7rem;
      color: var(--text-muted);
      opacity: 0.5;
    }

    th.sortable.active i {
      opacity: 1;
      color: var(--primary);
    }
  </style>

  <!-- Skip to content for accessibility -->
  <a href="#main-content" class="skip-link"
    style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;">Skip to
    content</a>

  <header role="banner">
    <div class="header-content">
      <h1>Dashboard</h1>
      <p id="welcome-message">Welcome to your dashboard</p>
    </div>
    <div id="header-date" class="header-date" aria-label="Current Date"></div>
  </header>

  <main id="main-content">

    <!-- Metrics Section -->
    <section class="metrics" aria-label="Dashboard Statistics">
      <div class="card" onclick="window.parent.postMessage({type:'redirect', page:'incident'}, '*')" style="cursor:pointer;">
        <div class="metric-label"><i class="fas fa-fire pulse-red" style="color:#ef4444;margin-right:6px"></i> ACTIVE
          INCIDENTS
        </div>
        <span class="metric-value" id="activeCount">0</span>
      </div>
      <div class="card">
        <div class="metric-label"><i class="fas fa-stopwatch" style="color:#f59e0b;margin-right:6px"></i> AVG RESPONSE
        </div>
        <span class="metric-value" id="avgResponse">--</span>
      </div>
      <div class="card" onclick="window.parent.postMessage({type:'redirect', page:'account'}, '*')" style="cursor:pointer;">
        <div class="metric-label"><i class="fas fa-users pulse-indigo" style="color:#6366f1;margin-right:6px"></i>
          ACTIVE RESPONDERS</div>
        <span class="metric-value" id="responderCount">0</span>
      </div>
      <div class="card" onclick="window.parent.postMessage({type:'redirect', page:'reports'}, '*')" style="cursor:pointer;">
        <div class="metric-label"><i class="fas fa-check-circle" style="color:#22c55e;margin-right:6px"></i> RESOLVED
          TODAY</div>
        <span class="metric-value" id="resolvedCount">0</span>
      </div>
    </section>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
      <!-- Recent Incidents Section -->
      <section class="incidents" aria-labelledby="incidents-heading">
        <h2 id="incidents-heading"><i class="fas fa-list-ul" style="margin-right: 8px; color: #3b82f6;"></i> Recent
          Incidents</h2>

        <!-- Table Toolbar -->
        <div class="table-controls">
          <div class="search-input-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search incidents (ID, type, location)...">
          </div>
          <div class="filter-group">
            <select id="typeFilter" class="filter-select">
              <option value="">All Types</option>
              <option value="Fire">Fire</option>
              <option value="Medical">Medical</option>
              <option value="Rescue">Rescue</option>
              <option value="Police">Police</option>
              <option value="Others">Others</option>
            </select>
            <select id="statusFilter" class="filter-select">
              <option value="">All Status</option>
              <option value="Pending">Pending</option>
              <option value="EnRoute">En Route</option>
              <option value="Resolved">Resolved</option>
              <option value="Ongoing">On-going</option>
            </select>
          </div>
        </div>

        <div class="table-wrap">
          <table aria-describedby="incidents-heading">
            <thead>
              <tr>
                <th scope="col" class="sortable active" data-sort="time" id="sort-time">Time <i
                    class="fas fa-sort-down"></i></th>
                <th scope="col" class="sortable" data-sort="type" id="sort-type">Type <i class="fas fa-sort"></i></th>
                <th scope="col" class="sortable" data-sort="location" id="sort-location">Location <i
                    class="fas fa-sort"></i></th>
                <th scope="col" class="sortable" data-sort="status" id="sort-status">Status <i class="fas fa-sort"></i>
                </th>
              </tr>
            </thead>
            <tbody id="incidentTableBody">
              <!-- rows injected here -->
            </tbody>
          </table>
        </div>
      </section>

      <!-- Summary / Overview Section -->
      <section class="summary" aria-labelledby="summary-heading">
        <h2 id="summary-heading"><i class="fas fa-chart-pie" style="margin-right: 8px; color: #8b5cf6;"></i> Overview
        </h2>

        <div class="chart-container">
          <canvas id="incidentChart" aria-label="Incident distribution chart" role="img"></canvas>
        </div>

        <!-- Incident Type Breakdown -->
        <div class="breakdown-list" aria-label="Incident type breakdown">
          <div class="breakdown-item">
            <span class="breakdown-dot" style="background:#e74c3c"></span>
            <span class="breakdown-label">Fire</span>
            <span class="breakdown-count" id="bd-fire">0</span>
          </div>
          <div class="breakdown-item">
            <span class="breakdown-dot" style="background:#3498db"></span>
            <span class="breakdown-label">Medical</span>
            <span class="breakdown-count" id="bd-medical">0</span>
          </div>
          <div class="breakdown-item">
            <span class="breakdown-dot" style="background:#2ecc71"></span>
            <span class="breakdown-label">Rescue</span>
            <span class="breakdown-count" id="bd-rescue">0</span>
          </div>
          <div class="breakdown-item">
            <span class="breakdown-dot" style="background:#9b59b6"></span>
            <span class="breakdown-label">Police</span>
            <span class="breakdown-count" id="bd-police">0</span>
          </div>
          <div class="breakdown-item">
            <span class="breakdown-dot" style="background:#f1c40f"></span>
            <span class="breakdown-label">Others</span>
            <span class="breakdown-count" id="bd-others">0</span>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="summary-stats">
          <p><span><i class="fas fa-database" style="margin-right:6px;color:#6366f1"></i>Total Reports</span>
            <strong id="totalReports">0</strong>
          </p>
          <p><span><i class="fas fa-check" style="margin-right:6px;color:#22c55e"></i>Total Resolved</span>
            <strong id="totalResolved">0</strong>
          </p>
          <p><span><i class="fas fa-percent" style="margin-right:6px;color:#f59e0b"></i>Resolution Rate</span>
            <strong id="resolutionRate">—</strong>
          </p>
        </div>
      </section>
    </div>
  </main>

  <!-- Supabase JS -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
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
          .eq('role', 'responder');

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
  </script>
  </body>

</html>