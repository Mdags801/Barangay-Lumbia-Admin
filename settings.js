/* settings.js — Settings page logic */

// Load settings from localStorage
function loadSettings() {
  document.getElementById('soundToggle').checked = localStorage.getItem('soundEnabled') !== 'false';
  document.getElementById('repeatToggle').checked = localStorage.getItem('repeatAlerts') === 'true';
  document.getElementById('intervalInput').value = localStorage.getItem('alertInterval') || '5';
  document.getElementById('defaultLocation').value = localStorage.getItem('defaultLocation') || 'Barangay Center';
  document.getElementById('gpsToggle').checked = localStorage.getItem('gpsEnabled') !== 'false';
  document.getElementById('retentionInput').value = localStorage.getItem('retentionPeriod') || '12';
}

// Save settings to localStorage
function saveSettings(silent = false) {
  localStorage.setItem('soundEnabled', document.getElementById('soundToggle').checked);
  localStorage.setItem('repeatAlerts', document.getElementById('repeatToggle').checked);
  localStorage.setItem('alertInterval', document.getElementById('intervalInput').value);
  localStorage.setItem('defaultLocation', document.getElementById('defaultLocation').value);
  localStorage.setItem('gpsEnabled', document.getElementById('gpsToggle').checked);
  localStorage.setItem('retentionPeriod', document.getElementById('retentionInput').value);

  // Notify parent if needed
  window.parent.postMessage({ 
    type: 'settings-updated', 
    settings: {
      soundEnabled: document.getElementById('soundToggle').checked,
      alertInterval: document.getElementById('intervalInput').value
    }
  }, '*');
  
  if (!silent) {
    showToast("Preferences Updated", "Your changes have been saved to local storage.", "success");
  }
}

// ---------- GUI Helpers ----------
function showToast(title, text, type = 'info') {
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

  const iconValue = type === 'success' ? 'check-circle' : type === 'danger' ? 'times-circle' : 'info-circle';
  toast.innerHTML = `
    <div class="icon"><i class="fas fa-${iconValue}"></i></div>
    <div class="content"><strong>${title}</strong><br/>${text}</div>
    <button class="close">&times;</button>
  `;
  container.appendChild(toast);

  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));

  const hide = () => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 200);
  };

  toast.querySelector('.close').onclick = hide;
  setTimeout(hide, 4000);
}

function showCustomAlert(title, text, type = 'info') {
  const modal = document.getElementById('alertModal');
  const iconEl = document.getElementById('alertIcon');
  const circle = document.getElementById('alertIconCircle');
  
  document.getElementById('alertTitle').textContent = title;
  document.getElementById('alertText').textContent = text;
  
  iconEl.className = type === 'danger' ? 'fas fa-ban' : 'fas fa-info-circle';
  circle.className = 'modal-icon-circle ' + (type === 'danger' ? 'icon-danger' : 'icon-info');
  
  modal.style.display = 'flex';
}

document.querySelectorAll('input').forEach(input => {
  input.addEventListener('change', () => saveSettings());
});

window.onload = loadSettings;