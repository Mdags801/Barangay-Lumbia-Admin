<?php require_once __DIR__ . '/session_guard.php'; ?>
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

    <!-- ===== Technical Capabilities Section ===== -->
    <section class="tech-capabilities" aria-labelledby="capabilities-heading">
      <div class="capabilities-header">
        <div class="capabilities-title-wrap">
          <h2 id="capabilities-heading">
            <i class="fas fa-layer-group" style="margin-right:10px;color:#6366f1;"></i>
            Technical Capabilities
          </h2>
          <p class="capabilities-subtitle">Core engineering competencies powering this system</p>
        </div>
        <span class="capabilities-badge">BBERS v9</span>
      </div>

      <div class="capabilities-grid">

        <!-- 1. Full-Stack Integration -->
        <div class="cap-card" style="--accent:#3b82f6; --accent-light:rgba(59,130,246,0.1);" aria-label="Full-Stack Integration">
          <div class="cap-icon-wrap">
            <i class="fas fa-layer-group"></i>
          </div>
          <div class="cap-body">
            <div class="cap-tags">
              <span class="cap-tag">Full-Stack</span>
            </div>
            <h3 class="cap-title">Full-Stack Integration</h3>
            <p class="cap-desc">Seamlessly integrates a React front-end with a robust back-end (Node/Express) and full database connectivity for a cohesive, end-to-end system.</p>
          </div>
          <div class="cap-footer">
            <span class="cap-tech"><i class="fab fa-react"></i> React</span>
            <span class="cap-tech"><i class="fab fa-node-js"></i> Node/Express</span>
            <span class="cap-tech"><i class="fas fa-database"></i> Database</span>
          </div>
        </div>

        <!-- 2. API & Real-Time Comm -->
        <div class="cap-card" style="--accent:#8b5cf6; --accent-light:rgba(139,92,246,0.1);" aria-label="API and Real-Time Communication">
          <div class="cap-icon-wrap">
            <i class="fas fa-bolt"></i>
          </div>
          <div class="cap-body">
            <div class="cap-tags">
              <span class="cap-tag">CLO2</span>
              <span class="cap-tag">CLO3</span>
            </div>
            <h3 class="cap-title">API &amp; Real-Time Comm</h3>
            <p class="cap-desc">Implements clean RESTful APIs and utilizes WebSockets for efficient real-time communication across all connected clients.</p>
          </div>
          <div class="cap-footer">
            <span class="cap-tech"><i class="fas fa-server"></i> REST API</span>
            <span class="cap-tech"><i class="fas fa-wifi"></i> WebSockets</span>
          </div>
        </div>

        <!-- 3. Security & Defense -->
        <div class="cap-card" style="--accent:#ef4444; --accent-light:rgba(239,68,68,0.1);" aria-label="Security and Defense">
          <div class="cap-icon-wrap">
            <i class="fas fa-shield-alt"></i>
          </div>
          <div class="cap-body">
            <div class="cap-tags">
              <span class="cap-tag">CLO3</span>
            </div>
            <h3 class="cap-title">Security &amp; Defense</h3>
            <p class="cap-desc">Robust Authentication &amp; Authorization with Input Validation, Password Hashing, and defense mechanisms against Clickjacking and Code Injection attacks.</p>
          </div>
          <div class="cap-footer">
            <span class="cap-tech"><i class="fas fa-lock"></i> Auth/Authz</span>
            <span class="cap-tech"><i class="fas fa-key"></i> Hashing</span>
            <span class="cap-tech"><i class="fas fa-bug"></i> XSS Defense</span>
          </div>
        </div>

        <!-- 4. Data & Account Mgmt -->
        <div class="cap-card" style="--accent:#f59e0b; --accent-light:rgba(245,158,11,0.1);" aria-label="Data and Account Management">
          <div class="cap-icon-wrap">
            <i class="fas fa-users-cog"></i>
          </div>
          <div class="cap-body">
            <div class="cap-tags">
              <span class="cap-tag">CLO3</span>
            </div>
            <h3 class="cap-title">Data &amp; Account Mgmt</h3>
            <p class="cap-desc">Full CRUD functionality with complex Role-Based Access Control (RBAC) and secure session handling to protect sensitive barangay data.</p>
          </div>
          <div class="cap-footer">
            <span class="cap-tech"><i class="fas fa-edit"></i> CRUD</span>
            <span class="cap-tech"><i class="fas fa-user-shield"></i> RBAC</span>
            <span class="cap-tech"><i class="fas fa-cookie-bite"></i> Sessions</span>
          </div>
        </div>

        <!-- 5. Project Planning & Ethics -->
        <div class="cap-card" style="--accent:#22c55e; --accent-light:rgba(34,197,94,0.1);" aria-label="Project Planning and Ethics">
          <div class="cap-icon-wrap">
            <i class="fas fa-drafting-compass"></i>
          </div>
          <div class="cap-body">
            <div class="cap-tags">
              <span class="cap-tag">CLO4</span>
            </div>
            <h3 class="cap-title">Project Planning &amp; Ethics</h3>
            <p class="cap-desc">Demonstrates high ethical standards and privacy considerations; project is well-documented using design thinking methodologies and planning tools.</p>
          </div>
          <div class="cap-footer">
            <span class="cap-tech"><i class="fas fa-pencil-ruler"></i> Design Thinking</span>
            <span class="cap-tech"><i class="fas fa-user-secret"></i> Privacy</span>
          </div>
        </div>

      </div>
    </section>
    <!-- ===== End Technical Capabilities ===== -->

  </main>

  <!-- Supabase JS -->
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="dashboard.js?v=<?php echo time(); ?>" defer></script>
  </body>

</html>
