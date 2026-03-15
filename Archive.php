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
  <script src="archive.js?v=<?php echo time(); ?>" defer></script>
</body>

</html>