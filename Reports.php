<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Incident Reports | Barangay Emergency System</title>
  <meta name="description"
    content="View and manage incident reports for the Barangay Based Emergency Response System. Search, filter, and export detailed emergency logs.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="reports.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

</head>

<!-- Skip to content for accessibility -->
<a href="#main-content" class="skip-link"
  style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;">Skip to content</a>

<header role="banner">
  <h1>Reports</h1>
  <p id="date" aria-label="Current Date"></p>
</header>

<main id="main-content">
  <!-- Summary Metrics -->
  <section class="metrics" aria-label="Incident Statistics">
    <div class="card" role="status">
      <i class="fas fa-chart-line" style="color: #6366f1; margin-bottom: 8px; font-size: 1.2rem;"
        aria-hidden="true"></i>
      TOTAL REPORTS
      <span id="total">0</span>
    </div>
    <div class="card" role="status">
      <i class="fas fa-check-circle" style="color: #10b981; margin-bottom: 8px; font-size: 1.2rem;"
        aria-hidden="true"></i>
      RESOLVED
      <span id="resolved">0</span>
    </div>
    <div class="card" role="status">
      <i class="fas fa-clock" style="color: #f43f5e; margin-bottom: 8px; font-size: 1.2rem;" aria-hidden="true"></i>
      PENDING
      <span id="pending">0</span>
    </div>
  </section>

  <!-- Controls -->
  <section class="controls" aria-label="Search and Filter Controls">
    <div class="left">
      <div style="position: relative; flex: 1; min-width: 180px; max-width: 360px;">
        <i class="fas fa-search"
          style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8;"
          aria-hidden="true"></i>
        <input id="searchInput" type="text" placeholder="Search reports..."
          style="padding-left: 44px; width: 100%; box-sizing: border-box;"
          aria-label="Search reports by ID, type, or location" />
      </div>
      <select id="sortSelect" title="Sort reports" aria-label="Sort reports by"
        style="flex-shrink:0; white-space:nowrap;">
        <option value="newest">Newest First</option>
        <option value="oldest">Oldest First</option>
        <option value="type">By Type</option>
      </select>
      <nav class="status-filters" role="navigation" aria-label="Filter incidents by status" style="flex-shrink:0;">
        <button class="filter-btn active" data-status="all" aria-pressed="true">All</button>
        <button class="filter-btn" data-status="Pending" aria-pressed="false">Pending</button>
        <button class="filter-btn" data-status="Resolved" aria-pressed="false">Resolved</button>
      </nav>
    </div>
    <div class="right">
       <button class="btn export" id="exportAllBtn" style="background: var(--primary); color: white; border: none;">
         <i class="fas fa-file-csv"></i> Export All to CSV
       </button>
    </div>
  </section>

  <!-- Reports List -->
  <section class="reports" aria-labelledby="reports-heading">
    <h2 id="reports-heading"><i class="fas fa-file-invoice" style="color: #3b82f6;" aria-hidden="true"></i> Generated
      Reports</h2>
    <ul id="reports-list" aria-live="polite"></ul>
  </section>
</main>


<!-- Supabase JS -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script src="reports.js?v=<?php echo time(); ?>" defer></script>

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
  <div class="card-modal">
    <div id="confirmIconCircle" class="modal-icon-circle"><i id="confirmIcon" class="fas fa-question"
        aria-hidden="true"></i></div>
    <h2 id="confirmTitle" style="margin:0 0 8px; font-size:1.5rem;">Confirm Action</h2>
    <p id="confirmText" style="color:#64748b; margin:0; line-height:1.5;">Are you sure you want to proceed?</p>
    <div class="modal-actions-custom">
      <button class="btn-cancel" id="confirmCancelBtn">Cancel</button>
      <button class="btn-confirm" id="confirmOkBtn">Confirm</button>
    </div>
  </div>
</div>

<!-- Custom Alert Modal -->
<div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
  <div class="card-modal">
    <div id="alertIconCircle" class="modal-icon-circle"><i id="alertIcon" class="fas fa-info-circle"
        aria-hidden="true"></i></div>
    <h2 id="alertTitle" style="margin:0 0 8px; font-size:1.5rem;">Notification</h2>
    <p id="alertText" style="color:#64748b; margin:0; line-height:1.5;">Message content goes here.</p>
    <div class="modal-actions-custom" style="justify-content:center;">
      <button class="btn-confirm" onclick="closeModals()" style="max-width:200px;">Understood</button>
    </div>
  </div>
</div>
</body>

</html>