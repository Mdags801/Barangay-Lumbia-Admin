// ---------- Config ----------
// Replace with your Supabase project URL and anon key
const SUPABASE_URL = "https://tukkkwtxuaxrbihyammp.supabase.co";
const SUPABASE_ANON_KEY = "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P";

const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

// Elements
const typesTbody = document.getElementById('typesTbody');
const filterInput = document.getElementById('filterInput');
const refreshBtn = document.getElementById('refreshBtn');

const labelInput = document.getElementById('labelInput');
const addBtn = document.getElementById('addBtn');
const clearBtn = document.getElementById('clearBtn');

// Preview Elements
const previewIcon = document.getElementById('previewIcon');
const previewLabel = document.getElementById('previewLabel');
const livePreview = document.getElementById('livePreview');

let selectedIcon = 'fire-flame-curved';
let selectedColor = 'red';
let editSelectedIcon = 'fire-flame-curved';
let editSelectedColor = 'red';
let liveTypes = []; // cached

const colorMap = {
  'red': '#ef4444',
  'orange': '#f97316',
  'blue': '#3b82f6',
  'green': '#22c55e',
  'purple': '#a855f7',
  'pink': '#ec4899',
  'teal': '#14b8a6',
  'amber': '#f59e0b',
  'black': '#000000',
  'grey': '#64748b'
};

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

    // Reset classes
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

function escapeHtml(s) {
  if (!s) return '';
  return s.toString().replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
}

let currentTab = 'live';

function renderTypes(filter = '') {
  const q = (filter || '').toLowerCase().trim();
  typesTbody.innerHTML = '';

  const list = liveTypes.filter(t => {
    const matchesTab = currentTab === 'archived' ? t.data.status === 'archived' : t.data.status !== 'archived';
    if (!matchesTab) return false;

    if (!q) return true;
    const v = `${t.data.label} ${t.data.icon} ${t.data.color}`.toLowerCase();
    return v.includes(q);
  });

  if (list.length === 0) {
    typesTbody.innerHTML = `<tr><td colspan="4" style="padding:18px;color:var(--muted)">No ${currentTab} types found.</td></tr>`;
    return;
  }

  list.forEach(({ id, data }) => {
    const label = escapeHtml(data.label || '');
    const icon = escapeHtml(data.icon || '');
    const color = data.color || 'grey';
    const hex = colorMap[color] || '#64748b';
    const status = data.status || 'active';
    const iconClass = icon.replace(/_/g, '-');

    const tr = document.createElement('tr');
    tr.innerHTML = `
          <td>
            <div style="display:flex;align-items:center">
              <div class="type-icon" style="background:#fff;border:1px solid #eef2f7">
                <i class="fas fa-${iconClass}" style="color:${hex}"></i>
              </div>
              <div style="margin-left:8px;font-weight:700">${label}</div>
            </div>
          </td>
          <td><span class="status-badge status-${status}">${status}</span></td>
          <td><div class="color-dot" style="background:${hex}"></div></td>
          <td class="row-actions">
            ${status !== 'archived' ? `
              <button class="edit-btn" data-id="${id}" title="Edit"><i class="fas fa-pen"></i></button>
              <button class="disable-btn" data-id="${id}" data-status="${status}" title="${status === 'active' ? 'Disable' : 'Enable'}">
                <i class="fas ${status === 'active' ? 'fa-eye-slash' : 'fa-eye'}"></i>
              </button>
              <button class="archive-btn" data-id="${id}" title="Archive"><i class="fas fa-archive"></i></button>
            ` : `
              <button class="restore-btn" data-id="${id}" title="Restore to Live"><i class="fas fa-undo"></i></button>
            `}
          </td>
        `;
    typesTbody.appendChild(tr);
  });

  // attach handlers
  typesTbody.querySelectorAll('.edit-btn').forEach(btn => {
    btn.onclick = (e) => {
      e.preventDefault();
      e.stopPropagation();
      const id = btn.getAttribute('data-id');
      const t = liveTypes.find(x => String(x.id) === String(id));
      
      if (!t) {
        return showCustomAlert({ 
          title: 'Error', 
          text: 'Category data not found.', 
          type: 'danger', 
          icon: 'exclamation-triangle' 
        });
      }

      const labelInput = document.getElementById('editLabelInput');
      const saveBtn = document.getElementById('saveEditBtn');

      labelInput.value = t.data.label || '';
      editSelectedIcon = t.data.icon || 'fire-flame-curved';
      editSelectedColor = t.data.color || 'red';

      // Set active states on Edit Pickers
      document.querySelectorAll('#editIconPicker .picker-item').forEach(p => {
        p.classList.toggle('active', p.dataset.value === editSelectedIcon);
      });
      document.querySelectorAll('#editColorPicker .picker-item').forEach(p => {
        p.classList.toggle('active', p.dataset.value === editSelectedColor);
      });

      const modal = document.getElementById('editModal');
      modal.style.display = 'flex';
      updateEditPreview();

      saveBtn.onclick = async () => {
        const newLabel = labelInput.value.trim();
        const newIcon = editSelectedIcon;
        const newColor = editSelectedColor;

        if (!newLabel) {
          return showCustomAlert({ title: 'Input Error', text: 'Label cannot be empty.', type: 'warning' });
        }

        // Duplication check (excluding the current item being edited)
        const isDup = liveTypes.some(x => 
          String(x.id) !== String(id) && 
          x.data.label.toLowerCase() === newLabel.toLowerCase()
        );

        if (isDup) {
          return showCustomAlert({ 
            title: 'Duplicate Name', 
            text: `A category named "${newLabel}" already exists.`, 
            type: 'warning', 
            icon: 'exclamation-circle' 
          });
        }

        try {
          saveBtn.disabled = true;
          saveBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Saving...';

          const { error } = await supabaseClient
            .from('emergency_types')
            .update({ label: newLabel, icon: newIcon, color: newColor })
            .eq('id', id);

          if (error) throw error;

          modal.style.display = 'none';
          showCustomAlert({ 
            title: 'Updated', 
            text: 'Emergency category updated successfully!', 
            type: 'info', 
            icon: 'check-circle' 
          });
          
          // Re-fetch to ensure sync (though Realtime should handle it)
          fetchTypes();
        } catch (err) {
          showCustomAlert({ 
            title: 'Update Failed', 
            text: err.message || 'An error occurred while saving.', 
            type: 'danger', 
            icon: 'times-circle' 
          });
        } finally {
          saveBtn.disabled = false;
          saveBtn.innerText = 'Save Changes';
        }
      };
    };
  });

  // Disable/Enable Action
  typesTbody.querySelectorAll('.disable-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const currentStatus = btn.dataset.status;
      const newStatus = currentStatus === 'active' ? 'disabled' : 'active';

      try {
        const { error } = await supabaseClient.from('emergency_types').update({ status: newStatus }).eq('id', id);
        if (error) throw error;
        showCustomAlert({
          title: 'Status Updated',
          text: `Category has been ${newStatus}.`,
          type: 'info',
          icon: 'check-circle'
        });
      } catch (err) {
        showCustomAlert({ title: 'Action Failed', text: err.message, type: 'danger', icon: 'times-circle' });
      }
    });
  });

  // Archive Action
  typesTbody.querySelectorAll('.archive-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const confirmed = await showConfirm({
        title: 'Archive Type?',
        text: 'This will hide it from the mobile app. You can restore it from the Archive tab later.',
        icon: 'archive',
        type: 'warning',
        confirmText: 'Archive Now'
      });
      if (!confirmed) return;

      try {
        const { error } = await supabaseClient.from('emergency_types').update({ status: 'archived' }).eq('id', id);
        if (error) throw error;
        showCustomAlert({
          title: 'Archived',
          text: 'Category moved to archives.',
          type: 'info',
          icon: 'check-circle'
        });
      } catch (err) {
        showCustomAlert({ title: 'Archive Failed', text: err.message, type: 'danger', icon: 'times-circle' });
      }
    });
  });

  // Restore Action
  typesTbody.querySelectorAll('.restore-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      try {
        const { error } = await supabaseClient.from('emergency_types').update({ status: 'active' }).eq('id', id);
        if (error) throw error;
        showCustomAlert({
          title: 'Restored',
          text: 'Category is now active on the mobile app.',
          type: 'info',
          icon: 'check-circle'
        });
      } catch (err) {
        showCustomAlert({ title: 'Restore Failed', text: err.message, type: 'danger', icon: 'times-circle' });
      }
    });
  });
}

// ---------- Real-time listener ----------
// Start a realtime channel for emergency_types table to keep UI in sync
function startRealtimeListener() {
  try {
    const channel = supabaseClient.channel('public:emergency_types')
      .on('postgres_changes', { event: '*', schema: 'public', table: 'emergency_types' }, payload => {
        const ev = payload.eventType; // INSERT, UPDATE, DELETE
        const newRow = payload.new;
        const oldRow = payload.old;

        if (ev === 'INSERT') {
          const row = { id: newRow.id ?? null, data: newRow };
          liveTypes.push(row);
        } else if (ev === 'UPDATE') {
          const id = newRow.id ?? null;
          const idx = liveTypes.findIndex(r => r.id === id);
          if (idx !== -1) {
            liveTypes[idx] = { id, data: newRow };
          } else {
            liveTypes.push({ id, data: newRow });
          }
        } else if (ev === 'DELETE') {
          const id = oldRow.id ?? null;
          liveTypes = liveTypes.filter(r => r.id !== id);
        }

        renderTypes(filterInput.value);
      })
      .subscribe((status) => {
        // optional: handle status updates
      });

    window._emergencyTypesRealtimeChannel = channel;
  } catch (err) {
    console.warn('Realtime subscription failed (check Supabase Realtime setup):', err);
  }
}

// ---------- Initial fetch ----------
async function fetchTypes() {
  try {
    const { data, error } = await supabaseClient
      .from('emergency_types')
      .select('*')
      .order('label', { ascending: true });

    if (error) {
      console.error('emergency_types fetch error:', error);
      typesTbody.innerHTML = '<tr><td colspan="4" style="padding:18px;color:var(--muted)">Error loading types.</td></tr>';
      return;
    }

    liveTypes = (data || []).map(d => ({ id: d.id ?? null, data: d }));
    renderTypes(filterInput.value);
  } catch (err) {
    console.error('Fetch failed:', err);
    typesTbody.innerHTML = '<tr><td colspan="4" style="padding:18px;color:var(--muted)">Error loading types.</td></tr>';
  }
}

// ---------- Picker Logic ----------
function updatePreview() {
  const label = labelInput.value || 'Label';
  const hex = colorMap[selectedColor] || '#000';

  previewLabel.textContent = label;
  previewLabel.style.color = hex;
  previewIcon.style.color = hex;
  previewIcon.className = `fas fa-${selectedIcon}`;
  livePreview.style.borderColor = hex;
}

function updateEditPreview() {
  const lblInput = document.getElementById('editLabelInput');
  const preLabel = document.getElementById('editPreviewLabel');
  const preIcon = document.getElementById('editPreviewIcon');
  const lPreview = document.getElementById('editLivePreview');

  if (!lblInput || !preLabel || !preIcon || !lPreview) return;

  const label = lblInput.value || 'Label';
  const hex = colorMap[editSelectedColor] || '#000';

  preLabel.textContent = label;
  preLabel.style.color = hex;
  preIcon.style.color = hex;
  preIcon.className = `fas fa-${editSelectedIcon}`;
  lPreview.style.borderColor = hex;
}

document.querySelectorAll('#iconPicker .picker-item').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('#iconPicker .picker-item').forEach(el => {
      el.classList.remove('active');
      el.setAttribute('aria-selected', 'false');
    });
    item.classList.add('active');
    item.setAttribute('aria-selected', 'true');
    selectedIcon = item.dataset.value;
    updatePreview();
  });
});

document.querySelectorAll('#colorPicker .picker-item').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('#colorPicker .picker-item').forEach(el => {
      el.classList.remove('active');
      el.setAttribute('aria-selected', 'false');
    });
    item.classList.add('active');
    item.setAttribute('aria-selected', 'true');
    selectedColor = item.dataset.value;
    updatePreview();
  });
});

// Edit Modal Pickers
document.querySelectorAll('#editIconPicker .picker-item').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('#editIconPicker .picker-item').forEach(el => el.classList.remove('active'));
    item.classList.add('active');
    editSelectedIcon = item.dataset.value;
    updateEditPreview();
  });
});

document.querySelectorAll('#editColorPicker .picker-item').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('#editColorPicker .picker-item').forEach(el => el.classList.remove('active'));
    item.classList.add('active');
    editSelectedColor = item.dataset.value;
    updateEditPreview();
  });
});

labelInput.addEventListener('input', updatePreview);
document.getElementById('editLabelInput').addEventListener('input', updateEditPreview);

// ---------- Actions ----------
addBtn.addEventListener('click', async () => {
  const label = (labelInput.value || '').trim();
  const icon = selectedIcon;
  const color = selectedColor;

  if (!label) {
    showCustomAlert({ 
      title: 'Required', 
      text: 'Please enter a category label.', 
      type: 'warning', 
      icon: 'exclamation-circle' 
    });
    return;
  }

  // Duplicate Check (Case-insensitive)
  const isDup = liveTypes.some(t => 
    (t.data.label || '').toLowerCase() === label.toLowerCase()
  );

  if (isDup) {
    showCustomAlert({ 
      title: 'Already Exists', 
      text: `A category named "${label}" already exists in the system.`, 
      type: 'warning', 
      icon: 'exclamation-circle' 
    });
    return;
  }

  try {
    addBtn.disabled = true;
    addBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Deploying...';

    const { data, error } = await supabaseClient
      .from('emergency_types')
      .insert([{ label, icon, color, status: 'active' }])
      .select();

    if (error) throw error;

    labelInput.value = '';
    updatePreview();
    showCustomAlert({ 
      title: 'Deployed', 
      text: 'New emergency category is now live!', 
      type: 'info', 
      icon: 'check-circle' 
    });
    
    // Explicit refresh to be sure (though Realtime is active)
    fetchTypes();
  } catch (err) {
    showCustomAlert({ 
      title: 'Deployment Failed', 
      text: err.message || err, 
      type: 'danger', 
      icon: 'times-circle' 
    });
  } finally {
    addBtn.disabled = false;
    addBtn.innerHTML = '<i class="fas fa-plus"></i> Deploy to Mobile App';
  }
});

clearBtn.addEventListener('click', () => {
  labelInput.value = '';
  updatePreview();
});

refreshBtn.addEventListener('click', async () => {
  try {
    refreshBtn.disabled = true;
    const { data, error } = await supabaseClient
      .from('emergency_types')
      .select('*')
      .order('label', { ascending: true });

    if (error) throw error;
    liveTypes = (data || []).map(d => ({ id: d.id ?? null, data: d }));
    renderTypes(filterInput.value);
  } catch (err) {
    showCustomAlert({ title: 'Refresh Failed', text: err.message || err, type: 'danger', icon: 'times-circle' });
  } finally {
    refreshBtn.disabled = false;
  }
});

filterInput.addEventListener('input', () => renderTypes(filterInput.value));

document.querySelectorAll('.tab-item').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab-item').forEach(el => {
      el.classList.remove('active');
      el.setAttribute('aria-selected', 'false');
    });
    tab.classList.add('active');
    tab.setAttribute('aria-selected', 'true');
    currentTab = tab.dataset.tab;
    renderTypes(filterInput.value);
  });
});

// ---------- Start ----------
(function init() {
  fetchTypes();
  startRealtimeListener();
})();
