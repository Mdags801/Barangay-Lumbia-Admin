<?php require_once __DIR__ . '/session_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Incident Archives</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="incident.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body style="background: #f8fafc; padding: 16px 24px 24px;">
  <header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; padding: 40px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 24px; color: white; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); position: relative; overflow: hidden;">
    <div style="z-index: 1;">
      <h1 style="font-size: 2.8rem; font-weight: 900; margin: 0; letter-spacing: -0.04em;">Incident Archives</h1>
      <p style="color: #94a3b8; font-weight: 500; font-size: 1.15rem; margin-top: 8px;">Secure storage for past emergency records</p>
    </div>
    <div style="position: absolute; top: -50%; right: -10%; width: 60%; height: 200%; background: radial-gradient(circle, rgba(37, 99, 235, 0.15), transparent 70%); pointer-events: none; z-index: 0;"></div>
  </header>

  <div class="controls">
    <div class="left">
      <input id="searchInput" type="text" placeholder="Search archived incidents..." />
      <select id="sortSelect" title="Sort Order">
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
