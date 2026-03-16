<?php require_once __DIR__ . '/session_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>App Manager | Barangay Emergency System</title>
  <meta name="description" content="Manage the emergency types and app configurations for the mobile response system.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="app_manager.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">
</head>

<body>


  <a href="#main-content" class="skip-link"
    style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;">Skip to
    content</a>

  <header role="banner">
    <h1>App Configurations Menu</h1>
    <p>Fine-tune incident categories and manage their availability on the mobile app.</p>
  </header>

  <main id="main-content" class="container">
    <!-- Left: Live list -->
    <section class="panel" aria-labelledby="live-types-heading">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 id="live-types-heading" style="font-size:1.1rem;margin:0;font-weight:800;">Active Emergency Categories</h2>
        <div style="color:var(--text-muted);font-size:0.85rem;font-weight:700;">
          <i class="fas fa-sync-alt fa-spin" style="margin-right:4px;color:#10b981;"></i> Live Sync Active
        </div>
      </div>

      <div class="controls">
        <div class="search-wrap">
          <i class="fas fa-search search-icon" aria-hidden="true"></i>
          <input id="filterInput" type="text" placeholder="Search categories..." aria-label="Filter category list" />
        </div>
        <button id="refreshBtn" class="btn btn-secondary" title="Refresh category list">
          <i class="fas fa-sync" aria-hidden="true"></i> Refresh
        </button>
      </div>

      <nav class="tab-nav" role="tablist" aria-label="Category Status">
        <div class="tab-item active" data-tab="live" role="tab" aria-selected="true">Active List</div>
        <div class="tab-item" data-tab="archived" role="tab" aria-selected="false">Archived</div>
      </nav>

      <div class="table-wrap" aria-live="polite">
        <table class="types-table" aria-describedby="live-types-heading">
          <thead>
            <tr>
              <th scope="col">Category</th>
              <th scope="col">Status</th>
              <th scope="col">Theme</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody id="typesTbody">
            <tr>
              <td colspan="4" style="padding:48px;text-align:center;color:var(--text-muted)">
                <i class="fas fa-circle-notch fa-spin fa-2x" style="margin-bottom:12px;display:block;color:var(--primary);"></i>
                Synchronizing with Supabase...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Right: Add / Editor -->
    <section class="panel" aria-labelledby="editor-heading">
      <h2 id="editor-heading" style="font-size:1.1rem;margin:0 0 16px;font-weight:800;">Category Designer</h2>

      <div class="form-group" style="position:relative;">
        <label for="labelInput" class="preview-label">Category Label</label>
        <div style="position:relative;">
          <i class="fas fa-tag" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8; z-index:1;"></i>
          <input id="labelInput" placeholder="e.g., Structural Fire" class="premium-input-field" />
        </div>

        <label class="preview-label">Visual Representation (Icon)</label>
        <div class="grid-picker" id="iconPicker" role="listbox" aria-label="Icon picker">
          <div class="picker-item active" data-value="fire-flame-curved" role="option" aria-selected="true"><i
              class="fas fa-fire-flame-curved"></i></div>
          <div class="picker-item" data-value="ambulance" role="option" aria-selected="false"><i
              class="fas fa-ambulance"></i></div>
          <div class="picker-item" data-value="shield-halved" role="option" aria-selected="false"><i
              class="fas fa-shield-halved"></i></div>
          <div class="picker-item" data-value="life-ring" role="option" aria-selected="false"><i
              class="fas fa-life-ring"></i></div>
          <div class="picker-item" data-value="users-rectangle" role="option" aria-selected="false"><i
              class="fas fa-users-rectangle"></i></div>
          <div class="picker-item" data-value="person-falling-burst" role="option" aria-selected="false"><i
              class="fas fa-person-falling-burst"></i></div>
          <div class="picker-item" data-value="triangle-exclamation" role="option" aria-selected="false"><i
              class="fas fa-triangle-exclamation"></i></div>
          <div class="picker-item" data-value="droplet" role="option" aria-selected="false"><i
              class="fas fa-droplet"></i></div>
          <div class="picker-item" data-value="car-burst" role="option" aria-selected="false"><i
              class="fas fa-car-burst"></i></div>
          <div class="picker-item" data-value="heart" role="option" aria-selected="false"><i class="fas fa-heart"></i>
          </div>
        </div>

        <label class="preview-label" style="display:block;margin-top:20px;">Color Palette</label>
        <div class="grid-picker" id="colorPicker" role="listbox" aria-label="Color picker">
          <div class="picker-item active" data-value="red" role="option" aria-selected="true">
            <div class="color-dot" style="background:#ef4444"></div>
          </div>
          <div class="picker-item" data-value="orange" role="option" aria-selected="false">
            <div class="color-dot" style="background:#f97316"></div>
          </div>
          <div class="picker-item" data-value="blue" role="option" aria-selected="false">
            <div class="color-dot" style="background:#3b82f6"></div>
          </div>
          <div class="picker-item" data-value="green" role="option" aria-selected="false">
            <div class="color-dot" style="background:#22c55e"></div>
          </div>
          <div class="picker-item" data-value="purple" role="option" aria-selected="false">
            <div class="color-dot" style="background:#a855f7"></div>
          </div>
          <div class="picker-item" data-value="pink" role="option" aria-selected="false">
            <div class="color-dot" style="background:#ec4899"></div>
          </div>
          <div class="picker-item" data-value="teal" role="option" aria-selected="false">
            <div class="color-dot" style="background:#14b8a6"></div>
          </div>
          <div class="picker-item" data-value="amber" role="option" aria-selected="false">
            <div class="color-dot" style="background:#f59e0b"></div>
          </div>
          <div class="picker-item" data-value="black" role="option" aria-selected="false">
            <div class="color-dot" style="background:#000"></div>
          </div>
          <div class="picker-item" data-value="grey" role="option" aria-selected="false">
            <div class="color-dot" style="background:#64748b"></div>
          </div>
        </div>

        <div class="preview-box">
          <span class="preview-label">Live App Preview</span>
          <div id="livePreview"
            style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:15px;border-radius:18px;background:#fff;border:2px solid #ef4444;width:80px;height:80px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <i id="previewIcon" class="fas fa-fire-flame-curved" style="font-size:24px;color:#ef4444;"></i>
            <span id="previewLabel"
              style="font-size:11px;font-weight:900;color:#ef4444;margin-top:6px;text-align:center;">Fire</span>
          </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;">
          <button id="addBtn" class="btn btn-primary" style="flex:1;">
            <i class="fas fa-plus"></i> Deploy to Mobile App
          </button>
          <button id="clearBtn" class="btn btn-secondary">Reset</button>
        </div>
      </div>

      <div style="margin-top:32px;padding:20px;background:#f1f5f9;border-radius:12px;">
        <h3 style="margin:0 0 8px;font-size:0.9rem;font-weight:800;color:#475569;"><i class="fas fa-lightbulb"
            style="color:#f59e0b;"></i> Pro Tip</h3>
        <p style="margin:0;font-size:0.85rem;color:#64748b;line-height:1.5;">
          These categories are pushed via <strong>Supabase Realtime</strong> directly to responder hand-sets. Deploying
          a new category makes it instantly selectable for new reports.
        </p>
      </div>
    </section>
    </div>

    <!-- Standardized Confirmation Modal -->
    <div id="confirmModal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
      <div class="card-modal">
        <div id="confirmIconCircle" class="modal-icon-circle icon-info"><i id="confirmIcon" class="fas fa-question" aria-hidden="true"></i></div>
        <h2 id="confirmTitle" style="margin:0 0 12px; font-size:1.6rem; font-weight:900; letter-spacing:-0.01em;">Confirm Action</h2>
        <p id="confirmText" style="color:#64748b; margin:0 0 28px; line-height:1.5;">Are you sure you want to proceed?</p>
        <div class="modal-actions">
          <button class="btn-cancel" id="confirmCancelBtn">Cancel</button>
          <button class="btn-confirm" id="confirmOkBtn">Confirm</button>
        </div>
      </div>
    </div>

    <!-- Standardized Alert Modal -->
    <div id="alertModal" class="custom-modal" role="alertdialog" aria-modal="true" aria-labelledby="alertTitle">
      <div class="card-modal">
        <div id="alertIconCircle" class="modal-icon-circle icon-info"><i id="alertIcon" class="fas fa-info-circle" aria-hidden="true"></i></div>
        <h2 id="alertTitle" style="margin:0 0 12px; font-size:1.6rem; font-weight:900; letter-spacing:-0.01em;">Notification</h2>
        <p id="alertText" style="color:#64748b; margin:0 0 28px; line-height:1.5;">Message content goes here.</p>
        <div class="modal-actions">
          <button class="btn-confirm" onclick="closeModals()" style="width:100%;">Understood</button>
        </div>
      </div>
    </div>

    <!-- Custom Edit Modal (Premium Landscape Layout) -->
    <div id="editModal" class="custom-modal" role="dialog" aria-modal="true" aria-hidden="true">
      <div class="card-modal" style="width: 880px; max-width: 95vw; padding: 0; overflow: hidden; display: flex; flex-direction: row; border-radius: 32px; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.3);">
        <!-- Left Section: Designer Tools -->
        <div style="flex: 1; padding: 48px; text-align: left; border-right: 1px solid #f1f5f9; background: white;">
          <h2 style="margin: 0 0 8px; font-size: 1.9rem; font-weight: 900; letter-spacing: -0.03em;">Modify Category</h2>
          <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 36px; font-weight: 500;">Fine-tune visual parameters for the live responder ecosystem.</p>
          
          <label style="font-weight: 900; color: #0f172a; margin-bottom: 12px; display: block; text-transform:uppercase; font-size:0.7rem; letter-spacing:0.1em;">Category Identity</label>
          <div style="position:relative; margin-bottom:28px;">
            <i class="fas fa-tag" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
            <input id="editLabelInput" placeholder="Enter Label..." class="premium-input-field" />
          </div>

          <label style="font-weight: 900; color: #0f172a; margin-bottom: 12px; display: block; text-transform:uppercase; font-size:0.7rem; letter-spacing:0.1em;">Visual Representation</label>
          <div class="grid-picker" id="editIconPicker" role="listbox" style="grid-template-columns: repeat(5, 1fr); gap: 14px;">
            <div class="picker-item" data-value="fire-flame-curved"><i class="fas fa-fire-flame-curved"></i></div>
            <div class="picker-item" data-value="ambulance"><i class="fas fa-ambulance"></i></div>
            <div class="picker-item" data-value="shield-halved"><i class="fas fa-shield-halved"></i></div>
            <div class="picker-item" data-value="life-ring"><i class="fas fa-life-ring"></i></div>
            <div class="picker-item" data-value="users-rectangle"><i class="fas fa-users-rectangle"></i></div>
            <div class="picker-item" data-value="person-falling-burst"><i class="fas fa-person-falling-burst"></i></div>
            <div class="picker-item" data-value="triangle-exclamation"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="picker-item" data-value="droplet"><i class="fas fa-droplet"></i></div>
            <div class="picker-item" data-value="car-burst"><i class="fas fa-car-burst"></i></div>
            <div class="picker-item" data-value="heart"><i class="fas fa-heart"></i></div>
          </div>

          <label style="display:block; margin-top:32px; font-weight: 900; color: #0f172a; margin-bottom: 12px; text-transform:uppercase; font-size:0.7rem; letter-spacing:0.1em;">Thematic Color Palette</label>
          <div class="grid-picker" id="editColorPicker" role="listbox" style="grid-template-columns: repeat(5, 1fr); gap: 14px;">
            <div class="picker-item" data-value="red"><div class="color-dot" style="background:#ef4444"></div></div>
            <div class="picker-item" data-value="orange"><div class="color-dot" style="background:#f97316"></div></div>
            <div class="picker-item" data-value="blue"><div class="color-dot" style="background:#3b82f6"></div></div>
            <div class="picker-item" data-value="green"><div class="color-dot" style="background:#22c55e"></div></div>
            <div class="picker-item" data-value="purple"><div class="color-dot" style="background:#a855f7"></div></div>
            <div class="picker-item" data-value="pink"><div class="color-dot" style="background:#ec4899"></div></div>
            <div class="picker-item" data-value="teal"><div class="color-dot" style="background:#14b8a6"></div></div>
            <div class="picker-item" data-value="amber"><div class="color-dot" style="background:#f59e0b"></div></div>
            <div class="picker-item" data-value="black"><div class="color-dot" style="background:#000"></div></div>
            <div class="picker-item" data-value="grey"><div class="color-dot" style="background:#64748b"></div></div>
          </div>
        </div>

        <!-- Right Section: Presentation & Actions -->
        <div style="width: 360px; padding: 48px; background: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center; position:relative; overflow:hidden;">
          <div style="position:absolute; top: -50px; left: -50px; width: 200px; height: 200px; background: rgba(37,99,235,0.03); filter: blur(40px); border-radius:50%;"></div>
          
          <div class="modal-icon-circle icon-info" style="margin-top: 0; width: 72px; height: 72px; font-size: 1.8rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); background: white; border: 1px solid #eef2f7; z-index:1;"><i class="fas fa-mobile-alt" style="color:#64748b;"></i></div>
          <span style="font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.15em; font-size: 0.65rem; margin-bottom: 32px; z-index:1;">Live Interface Preview</span>
          
          <div id="editLivePreview"
            style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:15px; border-radius:32px; background:#fff; border:4px solid #ef4444; width:140px; height:140px; box-shadow: 0 30px 60px -15px rgba(0,0,0,0.15); margin-bottom: 64px; z-index:1; transform: rotate(-1deg); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <i id="editPreviewIcon" class="fas fa-fire-flame-curved" style="font-size:52px; color:#ef4444;"></i>
            <span id="editPreviewLabel"
              style="font-size:15px; font-weight:900; color:#ef4444; margin-top:12px; text-align:center; letter-spacing:-0.01em;">Fire</span>
          </div>

          <div style="width: 100%; display: flex; flex-direction: column; gap: 14px; z-index:1;">
            <button class="btn-confirm" id="saveEditBtn" style="width: 100%; padding: 20px; font-size:1rem; border-radius:18px; font-weight:900;">Push Live Update</button>
            <button class="btn-cancel" onclick="closeModals()" style="width: 100%; padding: 18px; background: white; border-radius:18px; font-size:1rem; font-weight:800; color:#64748b; border:1px solid #e2e8f0;">Discard Changes</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Supabase JS -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>


    <script src="app_manager.js?v=<?php echo time(); ?>"></script>
</body>

</html>
