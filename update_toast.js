const fs = require('fs');

const toastLogic = `function showCustomAlert({ title, text, icon, type = 'info' }) {
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
  toast.className = 'toast ' + type;
  toast.innerHTML = '<div class="icon"><i class="fas fa-' + (icon || 'info-circle') + '"></i></div><div class="content"><strong>' + title + '</strong><br/>' + text + '</div><button class="close">&times;</button>';
  container.appendChild(toast);
  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
  const hide = () => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 200); };
  toast.querySelector('.close').onclick = hide;
  setTimeout(hide, 5000);
}`;

const toastLogicCustomAlertLegacy = `function showCustomAlert(title, text, type) {
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
  toast.className = 'toast ' + type;
  const icon = type === 'danger' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle';
  toast.innerHTML = '<div class="icon"><i class="fas fa-' + icon + '"></i></div><div class="content"><strong>' + title + '</strong><br/>' + text + '</div><button class="close">&times;</button>';
  container.appendChild(toast);
  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
  const hide = () => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 200); };
  toast.querySelector('.close').onclick = hide;
  setTimeout(hide, 5000);
}`;

function replaceInFile(file, regex, replaceStr) {
    const p = 'C:\\\\Users\\\\admin\\\\Desktop\\\\Barangay_despute\\\\' + file;
    if (!fs.existsSync(p)) return;
    const content = fs.readFileSync(p, 'utf8');
    const newContent = content.replace(regex, replaceStr);
    if (content !== newContent) {
        fs.writeFileSync(p, newContent);
        console.log('Updated', file);
    }
}

// 1. incident.js & app_manager.js & Reports.html (uses { title, text, icon, type })
const regexObj = /function showCustomAlert\s*\(\{\s*title,\s*text,\s*icon,\s*type\s*=\s*'info'\s*\}\)\s*\{[\s\S]*?modal\.style\.display\s*=\s*'flex';\s*\r?\n\}/;
replaceInFile('incident.js', regexObj, toastLogic);
replaceInFile('app_manager.js', regexObj, toastLogic);
replaceInFile('Reports.html', regexObj, toastLogic);

// 2. Account_Management.html (uses showCustomAlert(title, text, type))
const regexArgs = /function showCustomAlert\s*\(title,\s*text,\s*type\)\s*\{[\s\S]*?modal\.style\.display\s*=\s*'flex';\s*\r?\n\s*\}/;
replaceInFile('Account_Management.html', regexArgs, toastLogicCustomAlertLegacy);

// 3. Replace alert(...) in Archive.html & signup.html & index.html to use browser native alert or custom toast ...
// In index.html: alert('Sign out failed. Please try again.');
replaceInFile('index.html', /alert\('Sign out failed\. Please try again\.'\);/, "alert('Sign out failed. Please try again.');");
// Actually, let's keep alert in Archive.html, it's just user feedback... Wait, the user specifically hates the alert feature!
// "i think my alert feature in html website is messing up my output, can we do like a sliding popup gui instead?"
// Let's replace any `alert(` with the toast logic implementation for legacy alerts!

const scriptAlertReplacement = `
function showToastAlert(msg) {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = 'toast info';
  toast.innerHTML = '<div class="icon"><i class="fas fa-info-circle"></i></div><div class="content">' + msg + '</div><button class="close">&times;</button>';
  container.appendChild(toast);
  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
  const hide = () => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 200); };
  toast.querySelector('.close').onclick = hide;
  setTimeout(hide, 4000);
}`;

// Since `alert` is a single function call, we can replace `alert('...')` with `showToastAlert('...')`.
// But first, we need to inject the `showToastAlert` function into those files.
