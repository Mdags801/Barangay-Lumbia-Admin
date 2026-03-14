// ---------- CONFIG: make sure these match your project (Project Settings â†’ API) ----------
const SUPABASE_URL = "https://tukkkwtxuaxrbihyammp.supabase.co";
const SUPABASE_ANON_KEY = "sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P";
const MAPTILER_KEY = "31um5bDFFFAugzBg82HC";
// -------------------------------------------------------------------

if (typeof supabase === "undefined") {
  console.error("Supabase library did not load. Check the <script> src for @supabase/supabase-js.");
}

console.log("Using Supabase URL:", SUPABASE_URL);

// Create a named client to avoid colliding with the global 'supabase' library object
const supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

// ---------- Optional Auth Guard (disabled for debugging) ----------
(async function guard() {
  const redirectIfNoSession = false; // set true to re-enable redirect to login.html
  try {
    const { data, error } = await supabaseClient.auth.getSession();
    if (error) {
      console.warn("Auth getSession error:", error);
    }
    const session = data?.session;
    console.log("Auth session:", session);
    if (!session || !session.user) {
      console.log("No session found.");
      if (redirectIfNoSession) {
        window.location.href = 'login.html';
        return;
      }
    } else {
      try {
        parent.postMessage({ type: 'auth-status', signedIn: true, email: session.user.email }, '*');
      } catch (e) { }
    }
  } catch (err) {
    console.warn("Auth guard error:", err);
    if (redirectIfNoSession) window.location.href = 'login.html';
  }
})();

// ---------- Elements ----------
const modal = document.getElementById("incidentModal");
const modalDetails = document.getElementById("modalDetails");
const closeViewBtn = document.getElementById("closeViewModal");
const resolveBtn = document.querySelector(".resolve-btn");
const archiveBtn = document.querySelector(".archive-btn");
const notifyBtn = document.querySelector(".notify-btn");

const reportModal = document.getElementById("reportModal");
const openReportBtn = document.getElementById("openReportBtn");
const closeReportBtn = document.getElementById("closeReportModal");
const reportForm = document.getElementById("reportForm");

const agencyModal = document.getElementById("agencyModal");
const openAgencyBtn = document.getElementById("openAgencyModalBtn");
const closeAgencyBtn = document.getElementById("closeAgencyModal");
const agencyCards = document.querySelectorAll(".agency-card");
const confirmDispatchBtn = document.getElementById("confirmDispatchBtn");
const historyList = document.getElementById("historyList");

const searchInput = document.getElementById("searchInput");
const sortSelect = document.getElementById("sortSelect");
const tableBody = document.getElementById("incidentTableBody");
const filterButtons = document.querySelectorAll(".filter-btn");

let currentDocId = null;
let mapInstance = null;
let allIncidents = [];
let emergencyTypes = []; // For dynamic icons
const newTypeSelect = document.getElementById("newType");

// --- Live responder tracking ---
let responderMarker = null;      // Leaflet marker for responder dot
let responderChannel = null;     // Supabase realtime channel for location


// ---------- Utility ----------
function formatTime(raw) {
  if (!raw) return "N/A";
  const d = new Date(raw);
  if (!isNaN(d)) return d.toLocaleString();
  return raw;
}

function getStatusClass(status) {
  if (!status) return "unknown";
  const s = status.toLowerCase();
  if (s.includes("pend")) return "pending";
  if (s.includes("enroute") || s.includes("on-going") || s.includes("ongoing") || s.includes("en route")) return "enroute";
  if (s.includes("resolv")) return "resolved";
  return "unknown";
}

function matchesSearch(data, id, q) {
  if (!q) return true;
  q = q.toLowerCase();
  const visibleId = (data.incidentId || id || "").toString().toLowerCase();
  const time = (data.reportedAt || data.time || "").toString().toLowerCase();
  const reporter = (data.reporter || "").toString().toLowerCase();
  const type = (data.type || "").toString().toLowerCase();
  const location = (data.location || (data.coords?.lat && data.coords?.lng ? `${data.coords.lat},${data.coords.lng}` : "")).toString().toLowerCase();
  return (
    visibleId.includes(q) ||
    time.includes(q) ||
    reporter.includes(q) ||
    type.includes(q) ||
    location.includes(q)
  );
}

function escapeHtml(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// ---------- Reverse Geocoding ----------
// Cache so same coords never hit the API twice
const _geocodeCache = new Map();

async function reverseGeocode(lat, lng) {
  const key = `${lat.toFixed(5)},${lng.toFixed(5)}`;
  if (_geocodeCache.has(key)) return _geocodeCache.get(key);

  try {
    // MapTiler Geocoding API – reverse lookup (note: lng comes first)
    const url = `https://api.maptiler.com/geocoding/${lng},${lat}.json?key=${MAPTILER_KEY}&language=en`;
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();

    // Try ALL features, not only the first — prefer the most specific one
    const features = json.features || [];
    let address = null;

    for (const feature of features) {
      const p = feature.properties || {};
      const ctx = feature.context || [];
      const fromCtx = (prefix) => ctx.find(c => c.id?.startsWith(prefix))?.text || '';

      // ── Build street-first address ──────────────────────────────────────
      // p.address  = "12 Rizal St"  (MapTiler: house number + road)
      // p.name     = POI or street name when address is absent
      // context:   neighborhood → locality → place (city) → region (province)
      const street = p.address || p.name || '';
      const barangay = fromCtx('neighborhood') || fromCtx('locality');
      const city = fromCtx('place');       // municipality / city
      const province = fromCtx('region');      // province / state

      // Only use this feature if it has at least a street or barangay
      if (street || barangay) {
        const parts = [street, barangay, city, province].filter(Boolean);
        address = parts.join(', ');
        break;
      }

      // Fallback: MapTiler's pre-formatted place_name (strip country at end)
      if (feature.place_name) {
        const segments = feature.place_name.split(', ');
        // Drop last segment if it looks like a country (all caps or "Philippines")
        if (segments.length > 1) segments.pop();
        address = segments.join(', ');
        break;
      }
    }

    // Last resort: use Nominatim for a second opinion
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

// Build a street-level address string from a Nominatim address object
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


function renderTable() {
  if (!tableBody) return;
  const q = (searchInput?.value || "").trim().toLowerCase();
  const sortOrder = sortSelect?.value || "newest";
  const activeFilter = document.querySelector(".filter-btn.active")?.dataset?.status || "all";

  let list = allIncidents.slice();
  list.sort((a, b) => {
    const ta = Date.parse(a.reportedAt || a.time) || 0;
    const tb = Date.parse(b.reportedAt || b.time) || 0;
    return sortOrder === "newest" ? tb - ta : ta - tb;
  });

  if (activeFilter && activeFilter !== "all") {
    list = list.filter(item => (item.status || "").toLowerCase().includes(activeFilter.toLowerCase()));
  }

  list = list.filter(item => matchesSearch(item, item.id, q));

  tableBody.innerHTML = "";
  if (list.length === 0) {
    const tr = document.createElement("tr");
    tr.innerHTML = `
          <td colspan="6" style="padding:28px; text-align:center;">
            <div style="color:var(--muted); margin-bottom:12px;">No incidents found in the database.</div>
            <div style="font-size:0.85rem; color:#666; background:#f9fafb; padding:12px; border-radius:6px; display:inline-block; border:1px solid #eee;">
              <i class="fas fa-info-circle"></i> <strong>Note:</strong> If you're sure there is data, check your <b>Supabase RLS Policies</b>.<br>
              Make sure the <code>incidents</code> table allows <code>SELECT</code> for authenticated users.
            </div>
          </td>`;
    tableBody.appendChild(tr);
    return;
  }

  list.forEach((data) => {
    const id = data.id ?? data.incidentId ?? "";

    // Dynamic Icon Logic
    const foundType = emergencyTypes.find(t => (t.label || "").toLowerCase() === (data.type || "").toLowerCase());
    const iconName = foundType ? foundType.icon : null;
    const iconColor = foundType ? foundType.color : null;

    const colorHex = (() => {
      if (!iconColor) return "#64748b";
      // Map standard names to hex if needed, or just use as is
      const map = { 'red': '#ef4444', 'orange': '#f97316', 'blue': '#3b82f6', 'green': '#22c55e', 'purple': '#a855f7', 'pink': '#ec4899', 'teal': '#14b8a6', 'amber': '#f59e0b', 'black': '#000000', 'grey': '#64748b' };
      return map[iconColor.toLowerCase()] || iconColor;
    })();

    const icon = (() => {
      if (iconName) {
        return `<i class="fas fa-${iconName.replace(/_/g, '-')}" style="color: ${colorHex};" aria-hidden="true"></i>`;
      }
      // Fallback hardcoded defaults
      switch ((data.type || "").toLowerCase()) {
        case "fire": return '<i class="fas fa-fire-flame-curved" style="color: #e74c3c;" aria-hidden="true"></i>';
        case "medical": return '<i class="fas fa-ambulance" style="color: #3498db;" aria-hidden="true"></i>';
        case "police": return '<i class="fas fa-shield-halved" style="color: #1e3a8a;" aria-hidden="true"></i>';
        case "rescue": return '<i class="fas fa-life-ring" style="color: #f39c12;" aria-hidden="true"></i>';
        default: return '<i class="fas fa-circle-exclamation" style="color: #64748b;" aria-hidden="true"></i>';
      }
    })();

    const visibleId = data.incidentId || id;
    const locationText = data.location || (data.coords?.lat && data.coords?.lng ? `${data.coords.lat}, ${data.coords.lng}` : "N/A");
    const timeText = formatTime(data.reportedAt || data.time);

    const tr = document.createElement("tr");
    tr.setAttribute("role", "button");
    tr.setAttribute("tabindex", "0");
    tr.innerHTML = `
        <td class="type-cell">${icon} ${escapeHtml(data.type || "Unknown")}</td>
        <td>${escapeHtml(visibleId)}</td>
        <td>${escapeHtml(locationText)}</td>
        <td>${escapeHtml(timeText)}</td>
        <td>${escapeHtml(data.reporter || "Anonymous")}</td>
        <td><span class="status ${getStatusClass(data.status)}">${escapeHtml(data.status || "Pending")}</span></td>
      `;

    tr.addEventListener("click", () => openIncidentModal(data, id, icon));
    tr.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") openIncidentModal(data, id, icon);
    });

    tableBody.appendChild(tr);
  });
}

// ---------- Modal open / map ----------
function openIncidentModal(data, id, iconHtml) {
  currentDocId = id;
  const visibleId = data.incidentId || id;
  const locationText = data.location || (data.coords?.lat && data.coords?.lng ? `${data.coords.lat}, ${data.coords.lng}` : "N/A");
  const timeText = formatTime(data.reportedAt || data.time);

  // --- Responder info banner (shown when status is Enroute) ---
  const isEnroute = (data.status || "").toLowerCase().includes('enroute');
  const responderBanner = isEnroute && data.responderName ? `
    <div id="responderInfoBanner" style="
      background:linear-gradient(90deg,#1e3a8a,#2563eb);
      color:#fff;border-radius:12px;padding:14px 18px;
      margin-bottom:16px;display:flex;align-items:center;gap:14px;
    ">
      <span style="font-size:1.6rem;">&#x1F692;</span>
      <div style="flex:1">
        <div style="font-size:.75rem;font-weight:900;letter-spacing:.08em;opacity:.75">RESPONDER EN ROUTE</div>
        <div style="font-weight:700;font-size:1rem;">${escapeHtml(data.responderName)}</div>
      </div>
      <span id="responderLiveLabel" style="
        font-size:.78rem;font-weight:700;color:#4ade80;
        background:rgba(0,0,0,.2);padding:3px 9px;border-radius:99px;
      ">LOCATING...</span>
    </div>` : '';

  modalDetails.innerHTML = `
    ${responderBanner}
    <h2>${iconHtml} ${escapeHtml(data.type || "Unknown")} Incident</h2>
    <p><strong>ID:</strong> ${escapeHtml(visibleId)}</p>
    <p><strong>Location:</strong>
      <span id="modalLocationText" style="display:inline;">
        ${escapeHtml(locationText)}
      </span>
      ${data.coords?.lat && data.coords?.lng ? `<span id="geocodeSpinner" style="font-size:.8rem;color:#94a3b8;margin-left:6px;">
          <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> resolving address...
        </span>` : ''}
    </p>
    <p><strong>Time:</strong> ${escapeHtml(timeText)}</p>
    <p><strong>Reporter:</strong> ${escapeHtml(data.reporter || "Anonymous")}</p>
    <p><strong>Description:</strong> ${escapeHtml(data.description || "No details")}</p>
    ${data.coords?.lat && data.coords?.lng ? `<p style="font-size:.85rem;color:#94a3b8;"><strong>Coords:</strong> ${escapeHtml(data.coords.lat)}, ${escapeHtml(data.coords.lng)}</p>` : ""}
  `;

  // Kick off reverse geocoding immediately after rendering
  if (data.coords?.lat && data.coords?.lng) {
    reverseGeocode(Number(data.coords.lat), Number(data.coords.lng)).then(address => {
      const locSpan = document.getElementById('modalLocationText');
      const spinner = document.getElementById('geocodeSpinner');
      if (locSpan && address) locSpan.textContent = address;
      if (spinner) spinner.remove();
    });
  }


  modal.style.display = "flex";
  modal.setAttribute("aria-hidden", "false");

  const mapContainer = document.getElementById("incidentMap");
  if (!mapContainer) return;

  if (data.coords && data.coords.lat && data.coords.lng) {
    const lat = Number(data.coords.lat);
    const lng = Number(data.coords.lng);
    if (!isFinite(lat) || !isFinite(lng)) {
      mapContainer.innerHTML = "<div style='padding:18px;color:var(--muted)'>Invalid coordinates.</div>";
      return;
    }

    if (mapInstance) { try { mapInstance.remove(); } catch (e) { } mapInstance = null; }
    responderMarker = null;
    mapContainer.innerHTML = "";
    mapInstance = L.map("incidentMap", { attributionControl: false }).setView([lat, lng], 15);

    L.tileLayer(`https://api.maptiler.com/maps/streets/{z}/{x}/{y}.png?key=${MAPTILER_KEY}`, {
      attribution: "© MapTiler © OpenStreetMap contributors"
    }).addTo(mapInstance);

    // Red incident pin
    L.marker([lat, lng]).addTo(mapInstance)
      .bindPopup(`${escapeHtml(data.type || "Incident")} reported here`)
      .openPopup();

    // Inject responder dot CSS once
    if (!document.getElementById('_responderDotStyle')) {
      const s = document.createElement('style');
      s.id = '_responderDotStyle';
      s.textContent = `
        .r-dot-outer{width:20px;height:20px;background:rgba(37,99,235,.22);border:2.5px solid #2563eb;border-radius:50%;display:flex;align-items:center;justify-content:center;animation:rPing 1.4s ease-in-out infinite;}
        .r-dot-inner{width:9px;height:9px;background:#2563eb;border-radius:50%;}
        @keyframes rPing{0%,100%{box-shadow:0 0 0 0 rgba(37,99,235,.4);}60%{box-shadow:0 0 0 12px rgba(37,99,235,0);}}
      `;
      document.head.appendChild(s);
    }

    // Clean up old channel then subscribe to live responder location
    _cleanupResponderChannel();
    if (isEnroute) _subscribeResponderLocation(id);

  } else {
    if (mapInstance) { try { mapInstance.remove(); } catch (e) { } mapInstance = null; }
    mapContainer.innerHTML = "<div style='padding:18px;color:var(--muted)'>No coordinates available for this incident.</div>";
  }
}

// ---------- Responder live location helpers ----------
function _cleanupResponderChannel() {
  if (responderChannel) {
    try { supabaseClient.removeChannel(responderChannel); } catch (_) { }
    responderChannel = null;
  }
  responderMarker = null;
}

function _placeOrMoveResponderDot(lat, lng) {
  if (!mapInstance) return;
  const icon = L.divIcon({
    className: '',
    html: '<div class="r-dot-outer"><div class="r-dot-inner"></div></div>',
    iconSize: [20, 20],
    iconAnchor: [10, 10],
  });
  if (responderMarker) {
    responderMarker.setLatLng([lat, lng]);
  } else {
    responderMarker = L.marker([lat, lng], { icon }).addTo(mapInstance)
      .bindPopup('&#x1F692; Responder (Live)');
  }
  const lbl = document.getElementById('responderLiveLabel');
  if (lbl) lbl.textContent = 'LIVE';
}

async function _subscribeResponderLocation(incidentId) {
  // 1. Fetch last known position immediately
  try {
    const { data } = await supabaseClient
      .from('responder_locations')
      .select('lat,lng,updatedAt')
      .eq('incidentId', incidentId)
      .order('updatedAt', { ascending: false })
      .limit(1)
      .maybeSingle();
    if (data && data.lat && data.lng) {
      _placeOrMoveResponderDot(Number(data.lat), Number(data.lng));
    }
  } catch (_) { }

  // 2. Subscribe to real-time updates
  responderChannel = supabaseClient
    .channel(`rloc_${incidentId}`)
    .on('postgres_changes', {
      event: '*',
      schema: 'public',
      table: 'responder_locations',
      filter: `incidentId=eq.${incidentId}`
    }, payload => {
      const row = payload.new;
      if (row && row.lat && row.lng) {
        _placeOrMoveResponderDot(Number(row.lat), Number(row.lng));
      }
    })
    .subscribe();
}
// ---------- Fetch initial data ----------
async function fetchIncidents() {
  try {
    console.log("Calling Supabase incidents queryâ€¦");
    if (!tableBody) {
      console.warn("No table body element found.");
      return;
    }

    tableBody.innerHTML = `<tr><td colspan="6" style="padding:18px;color:var(--muted)">Loading incidentsâ€¦</td></tr>`;

    const { data, error } = await supabaseClient.from('incidents').select('*');

    console.log("Incidents data:", data);
    console.log("Incidents error:", error);

    if (error) {
      console.error("Supabase fetch error:", error);
      tableBody.innerHTML = `<tr><td colspan="6" style="padding:18px;color:#b91c1c">Query error: ${escapeHtml(error.message || error)}</td></tr>`;
      allIncidents = [];
      return;
    }

    if (!data || data.length === 0) {
      console.warn("Supabase returned no rows. This could be an empty table or an RLS Policy issue.");
      console.log("Check Dashboard -> Data Editor -> incidents table to see if data exists.");
      console.log("Check Dashboard -> Authentication -> Policies for the 'incidents' table.");
      allIncidents = [];
      renderTable();
      return;
    }

    // normalize rows
    allIncidents = (data || []).map(row => ({
      id: row.id ?? row.incidentId ?? null,
      ...row
    }));
    renderTable();
  } catch (err) {
    console.error("Fetch incidents failed:", err);
    tableBody.innerHTML = `<tr><td colspan="6" style="padding:18px;color:#b91c1c">Fetch failed: ${escapeHtml(err.message || err)}</td></tr>`;
  }
}

// ---------- Realtime listener ----------
function subscribeRealtime() {
  try {
    const channel = supabaseClient
      .channel('public:incidents')
      .on('postgres_changes', { event: '*', schema: 'public', table: 'incidents' }, payload => {
        const ev = payload.eventType;
        const newRow = payload.new;
        const oldRow = payload.old;

        console.log("Realtime incidents payload:", payload);

        if (ev === 'INSERT') {
          const row = { id: newRow.id ?? newRow.incidentId ?? null, ...newRow };
          allIncidents.unshift(row);
          // Alarm is now handled globally by index.html
        } else if (ev === 'UPDATE') {
          const id = newRow.id ?? newRow.incidentId ?? null;
          const idx = allIncidents.findIndex(r => (r.id ?? r.incidentId) === id);
          if (idx !== -1) {
            allIncidents[idx] = { id, ...newRow };
          } else {
            allIncidents.unshift({ id, ...newRow });
          }
        } else if (ev === 'DELETE') {
          const id = oldRow.id ?? oldRow.incidentId ?? null;
          allIncidents = allIncidents.filter(r => (r.id ?? r.incidentId) !== id);
        }
        renderTable();
      })
      .subscribe((status) => {
        console.log("Realtime channel status:", status);
      });

    window._incidentsRealtimeChannel = channel;
  } catch (err) {
    console.warn("Realtime subscription failed (check Supabase Realtime setup):", err);
  }
}


// ---------- Controls ----------
function debounce(fn, wait = 200) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), wait);
  };
}

if (searchInput) searchInput.addEventListener("input", debounce(() => renderTable(), 200));
if (sortSelect) sortSelect.addEventListener("change", () => renderTable());

filterButtons.forEach(btn => {
  btn.addEventListener("click", () => {
    filterButtons.forEach(b => {
      b.classList.remove("active");
      b.setAttribute("aria-pressed", "false");
    });
    btn.classList.add("active");
    btn.setAttribute("aria-pressed", "true");
    renderTable();
  });
});

// ---------- Modal actions ----------
if (openReportBtn) {
  openReportBtn.onclick = () => {
    reportModal.style.display = "flex";
    reportModal.setAttribute("aria-hidden", "false");
  };
}

if (closeReportBtn) {
  closeReportBtn.onclick = () => {
    reportModal.style.display = "none";
    reportModal.setAttribute("aria-hidden", "true");
    reportForm.reset();
  };
}

if (reportForm) {
  reportForm.onsubmit = async (e) => {
    e.preventDefault();
    const submitBtn = document.getElementById("submitReportBtn");
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerText = "Submitting...";
    }

    const { data: { session } } = await supabaseClient.auth.getSession();

    const newIncident = {
      type: document.getElementById("newType").value,
      reporter: document.getElementById("newReporter").value || "Anonymous",
      location: document.getElementById("newLocation").value,
      description: document.getElementById("newDescription").value,
      status: "Pending",
      reportedAt: new Date().toISOString()
    };

    // If your table has a user_id column, we include it here
    if (session?.user?.id) {
      newIncident.user_id = session.user.id;
    }

    const latVal = document.getElementById("newLat").value;
    const lngVal = document.getElementById("newLng").value;
    if (latVal && lngVal) {
      const flatLat = parseFloat(latVal);
      const flatLng = parseFloat(lngVal);
      newIncident.lat = flatLat;
      newIncident.lng = flatLng;
      newIncident.coords = { lat: flatLat, lng: flatLng };
    }

    try {
      const { error } = await supabaseClient
        .from('incidents')
        .insert([newIncident]);

      if (error) throw error;

      showCustomAlert({ title: 'Success', text: 'Incident reported successfully!', icon: 'check-circle', type: 'info' });
      if (closeReportBtn) closeReportBtn.click();
    } catch (err) {
      console.error("Failed to report incident:", err);
      showCustomAlert({ title: 'Report Failed', text: (err.message || err), icon: 'times-circle', type: 'danger' });
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerText = "Submit Report";
      }
    }
  };
}

if (closeViewBtn) {
  closeViewBtn.onclick = () => {
    modal.style.display = "none";
    modal.setAttribute("aria-hidden", "true");
    if (mapInstance) {
      try { mapInstance.remove(); } catch (e) { }
      mapInstance = null;
    }
    const mapContainer = document.getElementById("incidentMap");
    if (mapContainer) mapContainer.innerHTML = "";
    // Clean up live responder location subscription
    _cleanupResponderChannel();
  };
}


window.onclick = (event) => {
  if (event.target === modal) {
    closeViewBtn?.click();
  } else if (event.target === reportModal) {
    closeReportBtn?.click();
  }
};

if (resolveBtn) {
  resolveBtn.onclick = async () => {
    if (!currentDocId) return;
    const confirmed = await showConfirm({
      title: 'Mark as Resolved?',
      text: 'Are you sure you want to mark this incident as resolved?',
      icon: 'check-circle',
      type: 'info'
    });
    if (!confirmed) return;

    try {
      const { error } = await supabaseClient
        .from('incidents')
        .update({ status: "Resolved" })
        .eq('id', currentDocId);
      if (error) throw error;
      modal.style.display = "none";
      showCustomAlert({ title: 'Resolved', text: 'Incident has been marked as resolved.', icon: 'check-circle', type: 'info' });
    } catch (err) {
      showCustomAlert({ title: 'Error', text: (err.message || err), icon: 'times-circle', type: 'danger' });
    }
  };
}

if (archiveBtn) {
  archiveBtn.onclick = async () => {
    if (!currentDocId) return;
    const confirmed = await showConfirm({
      title: 'Archive Incident?',
      text: 'This will move the incident to permanent archives and remove it from the live list.',
      icon: 'archive',
      type: 'warning',
      confirmText: 'Archive Now'
    });
    if (!confirmed) return;

    try {
      const { data: rows, error: fetchErr } = await supabaseClient
        .from('incidents')
        .select('*')
        .eq('id', currentDocId)
        .limit(1)
        .single();
      if (fetchErr) {
        showCustomAlert({ title: 'Error', text: 'Incident not found.', icon: 'times-circle', type: 'danger' });
        console.error("Archive fetch error:", fetchErr);
        return;
      }

      const { id, acceptedAt, ...cleanedData } = rows;
      const { error: insertErr } = await supabaseClient
        .from('archived_incidents')
        .insert([{
          ...cleanedData,
          incidentId: cleanedData.incidentId || id,
          reportedAt: cleanedData.reportedAt || cleanedData.time,
          archivedAt: new Date().toISOString()
        }]);
      if (insertErr) throw insertErr;

      const { error: deleteErr } = await supabaseClient
        .from('incidents')
        .delete()
        .eq('id', currentDocId);
      if (deleteErr) throw deleteErr;

      modal.style.display = "none";
      showCustomAlert({ title: 'Archived', text: 'Incident archived successfully.', icon: 'check-circle', type: 'info' });
    } catch (err) {
      console.error("Archive failed:", err);
      showCustomAlert({ title: 'Archive Failed', text: (err.message || err), icon: 'times-circle', type: 'danger' });
    }
  };
}

// ---------- Agency Modal Logic ----------
let selectedAgencies = [];

async function loadDispatchHistory(incidentId) {
  const { data, error } = await supabaseClient
    .from('notifications')
    .select('*')
    .eq('incidentId', incidentId)
    .order('timestamp', { ascending: false });

  if (data && data.length > 0) {
    historyList.innerHTML = data.map(n =>
      `<div style="margin-bottom:4px;">â€¢ [${new Date(n.timestamp).toLocaleTimeString()}] ${n.message}</div>`
    ).join('');
  } else {
    historyList.innerHTML = "No agencies notified yet.";
  }
}

if (openAgencyBtn) {
  openAgencyBtn.onclick = () => {
    selectedAgencies = [];
    agencyCards.forEach(c => c.classList.remove('selected'));
    confirmDispatchBtn.disabled = true;
    agencyModal.style.display = "flex";
    loadDispatchHistory(currentDocId);
  };
}

closeAgencyBtn.onclick = () => agencyModal.style.display = "none";

agencyCards.forEach(card => {
  card.onclick = () => {
    card.classList.toggle('selected');
    const agency = card.dataset.agency;
    if (card.classList.contains('selected')) {
      selectedAgencies.push(agency);
    } else {
      selectedAgencies = selectedAgencies.filter(a => a !== agency);
    }
    confirmDispatchBtn.disabled = selectedAgencies.length === 0;
  };
});

confirmDispatchBtn.onclick = async () => {
  confirmDispatchBtn.disabled = true;
  confirmDispatchBtn.innerText = "Dispatching...";

  try {
    const promises = selectedAgencies.map(agency =>
      supabaseClient.from('notifications').insert([{
        incidentId: currentDocId,
        timestamp: new Date().toISOString(),
        message: `${agency} Notified by Admin`
      }])
    );

    await Promise.all(promises);

    // If Responders are notified, we could also update the incident status to 'Dispatched' 
    // to trigger higher priority in the phone app
    if (selectedAgencies.includes('Responders')) {
      await supabaseClient.from('incidents').update({
        status: 'Dispatched' // Or another status the app can recognize
      }).eq('id', currentDocId);
    }

    showCustomAlert({ title: 'Dispatch Successful', text: 'Alerts have been sent to the selected responders and agencies.', icon: 'check-circle', type: 'info' });
    agencyModal.style.display = "none";
  } catch (err) {
    console.error("Dispatch Error:", err);
    showCustomAlert({ title: 'Dispatch Failed', text: err.message, icon: 'times-circle', type: 'danger' });
  } finally {
    confirmDispatchBtn.disabled = false;
    confirmDispatchBtn.innerText = "Send Alerts";
  }
};

// Accessibility: close modal with Escape
window.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    if (modal && modal.style.display === "flex") closeViewBtn?.click();
    if (reportModal && reportModal.style.display === "flex") closeReportBtn?.click();
  }
});

async function fetchEmergencyTypes() {
  try {
    const { data, error } = await supabaseClient.from('emergency_types').select('*');
    if (!error && data) {
      emergencyTypes = data;
      renderTable(); // Re-render to apply icons

      // Update Report Modal Dropdown - show only active/live types
      const dropdown = document.getElementById("newType");
      if (dropdown) {
        const currentVal = dropdown.value;
        const liveOptions = data.filter(t => (t.status || 'active') === 'active');
        dropdown.innerHTML = liveOptions.map(t =>
          `<option value="${escapeHtml(t.label)}">${escapeHtml(t.label)}</option>`
        ).join('') + `<option value="Other">Other</option>`;
        if (currentVal) {
          // Try to preserve selection if it still exists
          const holdsValue = Array.from(dropdown.options).some(o => o.value === currentVal);
          if (holdsValue) dropdown.value = currentVal;
        }
      }
    }
  } catch (e) { console.warn("Could not fetch emergency types:", e); }
}

// ---------- Init ----------
(async function init() {
  await fetchIncidents();
  fetchEmergencyTypes(); // Load dynamic icons in background
  subscribeRealtime();
})();



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
  toast.className = 'toast ' + type;
  toast.innerHTML = '<div class="icon"><i class="fas fa-' + (icon || 'info-circle') + '"></i></div><div class="content"><strong>' + title + '</strong><br/>' + text + '</div><button class="close">&times;</button>';
  container.appendChild(toast);
  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
  const hide = () => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 200); };
  toast.querySelector('.close').onclick = hide;
  setTimeout(hide, 5000);
}

function closeModals() {
  document.querySelectorAll('.custom-modal').forEach(m => m.style.display = 'none');
}



