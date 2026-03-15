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
    function saveSettings() {
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
      
      // Show local toast (if notification function exists)
      if (window.alert) alert('Settings saved successfully!');
    }

    document.querySelectorAll('input').forEach(input => {
      input.addEventListener('change', saveSettings);
    });

    window.onload = loadSettings;