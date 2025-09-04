<?php
$config = include('config.php');
$camsPerPage = isset($config['cams_per_page']) ? intval($config['cams_per_page']) : 20;
$slugs = $config['slugs'] ?? [
    'f'=>'girls',
    'm'=>'guys',
    't'=>'trans',
    'c'=>'couples',
    'model'=>'model'
];
$meta_title = $config['meta_home_title'];
$meta_desc  = $config['meta_home_desc'];
$g = '';
$page_num = 1;
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $path);
$slug_to_gender = array_flip($slugs);
if (isset($parts[0]) && isset($slug_to_gender[$parts[0]])) {
    $g = $slug_to_gender[$parts[0]];
    if (isset($parts[1]) && strtolower($parts[1]) === 'page' && isset($parts[2]) && is_numeric($parts[2])) {
        $page_num = intval($parts[2]);
    }
}
if (isset($_GET['gender']) && count((array)$_GET['gender']) == 1) {
    $g = is_array($_GET['gender']) ? $_GET['gender'][0] : $_GET['gender'];
    if (isset($_GET['page']) && is_numeric($_GET['page'])) $page_num = intval($_GET['page']);
}
if ($g && isset($config['meta_gender_titles'][$g])) {
    $meta_title = $config['meta_gender_titles'][$g];
    if ($page_num > 1) $meta_title .= " - Page $page_num";
}
if ($g && isset($config['meta_gender_descs'][$g])) {
    $meta_desc = $config['meta_gender_descs'][$g];
}
include('templates/header.php');
?>
<script>
window.GENDER_SLUGS = <?=json_encode($slugs, JSON_UNESCAPED_SLASHES)?>;
window.SLUG_TO_GENDER = {};
for (const key in window.GENDER_SLUGS) window.SLUG_TO_GENDER[window.GENDER_SLUGS[key]] = key;
</script>
<style>
#main-flex-wrap {
  display: flex;
  align-items: flex-start;
  width: 100%;
  min-height: 60vh;
}
#filter-sidebar {
  background: #fafafd;
  border: none;
  box-shadow: none;
  border-radius: 0;
  padding: 22px 13px 13px 10px;
  margin: 0;
  min-width: 174px;
  width: 220px;
  max-width: 98vw;
  height: fit-content;
  min-height: 82vh;
  transition: width 0.07s, padding 0.07s;
  position: relative;
  z-index: 2;
}
#filter-sidebar:not(.open) {
  width: 0 !important;
  min-width: 0 !important;
  padding: 0 !important;
  overflow: hidden;
  border: none;
  box-shadow: none;
}
#filter-sidebar .close-btn {
  float: right;
  background: none;
  border: none;
  color: var(--primary-color);
  font-size: 1.09em;
  margin-bottom: -1px;
  margin-top: -3px;
  padding: 3px 11px 4px 5px;
  border-radius: 7px;
  cursor: pointer;
  transition: color .13s, background .12s;
}
#filter-sidebar .close-btn:hover {
  background: #ffeaca;
  color: #fff;
}
#filter-sidebar .filter-label {
  color: var(--primary-color);
  font-weight: 700;
  font-size: 13.5px;
  margin-bottom: 8px;
  margin-top: 18px;
  letter-spacing: 0.017em;
}
#filter-sidebar .filter-section {
  margin-bottom: 18px;
  background: none;
  border: none;
  box-shadow: none;
}
#filter-sidebar .filter-section:not(:last-child) {
  border-bottom: 1px solid #f0f1f3;
  padding-bottom: 10px;
}
#filter-sidebar .filter-chip.selected {
  background: var(--primary-color);
  color: #fff;
  border-color: var(--primary-color);
}
#filter-sidebar .filter-chip:hover:not(.selected) {
  background: #ffeecc;
  color: var(--primary-color);
}
body { background: #fafafd; }
.main-content {
  flex: 1 1 0;
  width: 100%;
  transition: margin .07s;
  margin-left: 0;
}
@media (max-width: 700px) {
  #filter-sidebar { width: 92vw; }
}
a.tag-cb.subject-tag {
  color: #487bb7;
  background: transparent;
  border-radius: 3px;
  text-decoration: none;
  transition: background .13s, color .13s;
  cursor: pointer;
  font-size: 13px;
}
a.tag-cb.subject-tag:hover,
a.tag-cb.subject-tag:focus {
  background: #d2e1fb;
  color: #174377;
  outline: none;
}
a.tag-cb.subject-tag:focus {
  outline: 1.5px dotted #3f63ad;
  outline-offset: 2px;
}
@media (max-width: 600px) {
  .guest-area-text-desktop { display: none !important; }
  .guest-area-text-mobile  { display: inline !important; }
}
@media (min-width: 601px) {
  .guest-area-text-desktop { display: inline !important; }
  .guest-area-text-mobile  { display: none !important; }
}
#guest-area-msg button:hover,
#guest-area-msg button:focus {
  background: #e9e9ee !important;
  color: #c32;
  outline: none;
}
.filter-showtype {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}
.filter-showtype .filter-chip {
  min-width: 0;
  width: auto;
  padding: 2.5px 9px;
  font-size: 13px;
  display: inline-flex;
  margin-bottom: 2px;
  margin-right: 0;
  box-sizing: border-box;
  text-align: center;
  justify-content: center;
  align-items: center;
}
.current-show-chip {
  position: absolute;
  top: 8px;
  left: 8px;
  font-weight: 700;
  font-size: 11px;
  padding: 2px 7px;
  border-radius: 11px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
  z-index: 10;
  pointer-events: none;
  user-select: none;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  font-family: sans-serif;
  color: #fff;
}
.current-show-chip.status-private { background-color: #d94242; }
.current-show-chip.status-group { background-color: #d17c1d; }
.current-show-chip.status-away { background-color: #2a88d5; }
.current-show-chip.status-hidden { background-color: #666; }
.current-show-chip.status-new { background-color: #4caf50; }
</style>
<div id="main-flex-wrap">
<aside id="filter-sidebar" class="open">
  <button class="close-btn" onclick="toggleSidebar()" title="Hide Filters">&#10006; Hide Sidebar</button>
  <div class="filter-section">
    <div class="filter-label">Gender</div>
    <div class="filter-gender">
      <span class="filter-chip" data-gender="f">&#9792; <?=ucfirst(htmlspecialchars($slugs['f']))?></span>
      <span class="filter-chip" data-gender="m">&#9794; <?=ucfirst(htmlspecialchars($slugs['m']))?></span>
      <span class="filter-chip" data-gender="t">&#9895; <?=ucfirst(htmlspecialchars($slugs['t']))?></span>
      <span class="filter-chip" data-gender="c">&#9792;&#9794; <?=ucfirst(htmlspecialchars($slugs['c']))?></span>
    </div>
  </div>
  <div class="filter-section">
    <div class="filter-label">Regions</div>
    <div class="filter-regions">
      <span class="filter-chip" data-region="northamerica">North America</span>
      <span class="filter-chip" data-region="southamerica">South America</span>
      <span class="filter-chip" data-region="europe_russia">Europe/Russia</span>
      <span class="filter-chip" data-region="asia">Asia</span>
      <span class="filter-chip" data-region="other">Other</span>
    </div>
  </div>
  <div class="filter-section">
    <div class="filter-label">Room Size</div>
    <div class="filter-roomsize">
      <span class="filter-chip" data-size="intimate">Intimate</span>
      <span class="filter-chip" data-size="mid">Mid-Sized</span>
      <span class="filter-chip" data-size="high">High-Traffic</span>
    </div>
  </div>
  <div class="filter-section">
    <div class="filter-label">Tags</div>
    <div class="filter-tags-group" style="padding:6px 6px 3px 6px;">
      <input type="text" id="tag-search" placeholder="Search #tags..." autocomplete="off">
      <div class="filter-tags"></div>
    </div>
  </div>
  <div class="filter-section">
    <div class="filter-label">Age</div>
    <div class="filter-ages">
      <input type="number" min="18" max="99" id="min-age" value="18">
      <span>to</span>
      <input type="number" min="18" max="99" id="max-age" value="99">
      <button onclick="applyAge()" class="filter-chip" type="button">Apply</button>
    </div>
  </div>
  <div class="filter-section">
  <div class="filter-label">Show Type</div>
  <div class="filter-showtype">
    <span class="filter-chip" data-current_show="public">Public</span>
    <span class="filter-chip" data-current_show="private">Private</span>
    <span class="filter-chip" data-current_show="group">Group</span>
    <span class="filter-chip" data-current_show="away">Away</span>
    <span class="filter-chip" data-current_show="hidden">Hidden</span>
  </div>
</div>
  <div class="filter-section">
    <div>
      <label style="font-weight: 600; cursor:pointer;">
        <input type="checkbox" id="filter-new-models" style="vertical-align: middle;"> New Models Only
      </label>
    </div>
  </div>
</aside>
<div class="main-content">
  <div id="guest-area-msg"
       style="font-size:13px;padding:0 8px;border-radius:4px;margin:5px 0 5px 0;vertical-align:middle;display:block;max-width:100%;">
    <span class="guest-area-text-desktop">
      You’re currently browsing the guest area. For the full experience,
      <a href="<?=htmlspecialchars($config['signup_url'] ?? $config['login_url'] ?? '#')?>"
         style="color:#2068b6;text-decoration:underline;font-weight:500;">sign up</a>
      and visit our <a href="https://<?=htmlspecialchars($config['whitelabel_domain'] ?? '')?>/"
         style="color:#2068b6;text-decoration:underline;font-weight:500;">members area</a>.
    </span>
    <span class="guest-area-text-mobile" style="display:none;">
      Browsing as Guest. Full experience?
      <a href="<?=htmlspecialchars($config['signup_url'] ?? $config['login_url'] ?? '#')?>"
         style="color:#2068b6;text-decoration:underline;font-weight:500;">Sign up</a>
      /
      <a href="https://<?=htmlspecialchars($config['whitelabel_domain'] ?? '')?>/"
         style="color:#2068b6;text-decoration:underline;font-weight:500;">Members</a>
    </span>
    <button type="button" aria-label="Close guest notice"
      onclick="this.parentNode.style.display='none'"
      onmouseover="this.style.background='#e9e9ee';this.style.color='#c32';"
      onmouseout="this.style.background='transparent';this.style.color='#777';"
      style="background:transparent;border:none;font-size:15px;color:#777;cursor:pointer;line-height:1;padding:0 2.5px;margin-left:7px;vertical-align:middle;display:inline;border-radius:3px;transition:background .14s;">
      &times;
    </button>
  </div>
  <div class="model-grid" id="model-grid"></div>
  <div class="pagination-bar" id="pagination-bar"></div>
</div>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<script>
let totalCount = 0;
function saveGridFilters() { try { sessionStorage.setItem("livecams_filters", JSON.stringify(FILTERS)); } catch(e){} }
function loadGridFilters() {
    let s = null;
    try { s = sessionStorage.getItem("livecams_filters"); } catch(e){}
    if (s) try { return JSON.parse(s); } catch(e){}
    return null;
}
function getGenderIcon(g) {
  if (!g) return '';
  let classes = "gender-cb " + g;
  if (g === "f") return `<span class="${classes}" title="Female">&#9792;</span>`;
  if (g === "m") return `<span class="${classes}" title="Male">&#9794;</span>`;
  if (g === "t") return `<span class="${classes}" title="Trans">&#9895;</span>`;
  if (g === "c") return `<span class="${classes}" title="Couple">&#9792;&#9794;</span>`;
  return '';
}
const camsPerPage = <?= $camsPerPage ?>;
const API = '/api-proxy.php';
function getFlag(country) {
  if (!country) return '';
  return `<span class="country-cb"><img class="flag-cb" src="https://flagcdn.com/16x12/${country.toLowerCase()}.png" alt="${country}"></span>`;
}
function parsePrettyPath() {
    const GSLUGS = window.GENDER_SLUGS || {};
    const SLUG_TO_GENDER = window.SLUG_TO_GENDER || {};
    const slugsForRegex = Object.values(GSLUGS).map((s)=>s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join("|");
    const path = window.location.pathname.replace(/\/+/g, '/');
    let gender = '', page = 1, match;
    if ((match = new RegExp(`^/(${slugsForRegex})/page/(\\d+)/?$`).exec(path))) {
        gender = SLUG_TO_GENDER[match[1]];
        page = parseInt(match[2],10);
    }
    else if ((match = /^\/page\/([0-9]+)\/?$/.exec(path))) {
        gender = '';
        page = parseInt(match[1], 10);
    }
    else if ((match = new RegExp(`^/(${slugsForRegex})/?$`).exec(path))) {
        gender = SLUG_TO_GENDER[match[1]];
        page = 1;
    } else {
        gender = '';
        page = 1;
    }
    return { gender, page };
}
let pretty = parsePrettyPath();
let sessionFilters = loadGridFilters() || {};
let urlFilters = {};
urlFilters.gender = pretty.gender ? [pretty.gender] : (sessionFilters.gender || []);
urlFilters.page = pretty.page || sessionFilters.page || 1;
urlFilters.region = sessionFilters.region || [];
urlFilters.tag = sessionFilters.tag || [];
urlFilters.minAge = sessionFilters.minAge === undefined ? 18 : sessionFilters.minAge;
urlFilters.maxAge = sessionFilters.maxAge === undefined ? 99 : sessionFilters.maxAge;
urlFilters.size   = sessionFilters.size || null;
urlFilters.current_show = sessionFilters.current_show || [];
urlFilters.is_new = sessionFilters.is_new || false;
const FILTERS = {
  gender: urlFilters.gender,
  region: urlFilters.region,
  tag: urlFilters.tag,
  minAge: urlFilters.minAge,
  maxAge: urlFilters.maxAge,
  size: urlFilters.size,
  current_show: urlFilters.current_show,
  is_new: urlFilters.is_new,
  page: urlFilters.page,
};
let currentPage = FILTERS.page || 1;
function toggleSidebar() {
  var sidebar = document.getElementById('filter-sidebar');
  if (!sidebar) return;
  if (sidebar.classList.contains('open')) sidebar.classList.remove('open');
  else sidebar.classList.add('open');
}
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth <= 700) {
        var sidebar = document.getElementById('filter-sidebar');
        if (sidebar) sidebar.classList.remove('open');
    }
    var filterBtn = document.getElementById('filter-toggle');
    if (filterBtn) filterBtn.onclick = toggleSidebar;
    var closeBtn = document.querySelector('#filter-sidebar .close-btn');
    if (closeBtn) closeBtn.onclick = toggleSidebar;
    document.querySelectorAll('.filter-chip[data-gender]').forEach(el=>{
        if(FILTERS.gender.includes(el.dataset.gender)) el.classList.add('selected');
    });
    document.querySelectorAll('.filter-chip[data-region]').forEach(el=>{
        if(FILTERS.region.includes(el.dataset.region)) el.classList.add('selected');
    });
    document.querySelectorAll('.filter-chip[data-size]').forEach(el=>{
        if(FILTERS.size===el.dataset.size) el.classList.add('selected');
    });
    document.querySelectorAll('.filter-chip[data-current_show]').forEach(el=>{
        if(FILTERS.current_show.includes(el.dataset.current_show)) el.classList.add('selected');
    });
    document.getElementById('min-age').value = FILTERS.minAge;
    document.getElementById('max-age').value = FILTERS.maxAge;
    var newModelsCheckbox = document.getElementById('filter-new-models');
    if (newModelsCheckbox) newModelsCheckbox.checked = FILTERS.is_new;
    var resetEl = document.getElementById('reset-filters-link');
    if (resetEl) resetEl.onclick = resetFilters;
    updateResetFiltersLink();
    if (newModelsCheckbox) {
      newModelsCheckbox.addEventListener('change', function() {
        FILTERS.is_new = this.checked;
        onFilterChange();
      });
    }
    document.querySelectorAll('.filter-chip[data-current_show]').forEach(el => {
      el.onclick = () => {
        const cs = el.dataset.current_show;
        if (FILTERS.current_show.includes(cs)) {
          FILTERS.current_show = FILTERS.current_show.filter(x => x !== cs);
          el.classList.remove('selected');
        } else {
          FILTERS.current_show.push(cs);
          el.classList.add('selected');
        }
        onFilterChange();
      };
    });
    const bar = document.getElementById('auto-refresh-bar');
    if (bar && isDesktop()) {
        bar.style.display = '';
        const stored = sessionStorage.getItem("livecams_auto_refresh") === "1";
        document.getElementById('toggle-auto-refresh').checked = stored;
        isAutoRefreshEnabled = stored;
        if (stored) startAutoRefresh();
    }
    if (bar) {
        document.getElementById('toggle-auto-refresh').addEventListener('change', function() {
            isAutoRefreshEnabled = this.checked;
            sessionStorage.setItem("livecams_auto_refresh", (isAutoRefreshEnabled ? "1" : "0"));
            if (isAutoRefreshEnabled) startAutoRefresh(); else stopAutoRefresh();
        });
    }
});
function genderToPath() {
    if (FILTERS.gender && FILTERS.gender.length) {
        let g = FILTERS.gender[0];
        if (window.GENDER_SLUGS && window.GENDER_SLUGS[g]) {
            return '/' + window.GENDER_SLUGS[g];
        }
    }
    return '/';
}
function getCurrentPath() {
    let path = genderToPath();
    let page = FILTERS.page || 1;
    if (page > 1) {
        if (path.endsWith('/')) path += `page/${page}`;
        else path += `/page/${page}`;
    }
    if (!path.startsWith('/')) path = '/' + path;
    return path;
}
function setPage(n) {
    FILTERS.page = (n < 1) ? 1 : n;
    saveGridFilters();
    navigateToCurrentPrettyPath();
}
function navigateToCurrentPrettyPath() {
    window.location.href = getCurrentPath();
}
window.gotoPage = function(p) {
    setPage(p);
}
function onFilterChange() {
    FILTERS.page = 1;
    saveGridFilters();
    window.location.href = getCurrentPath();
}
const ROOMSIZE = { intimate: [0,40], mid: [41,120], high: [121,9999] };
let allTags = [];
let allModels = [];
function fetchModels() {
  let params = [];
  if(FILTERS.region.length) params.push('region='+FILTERS.region.map(encodeURIComponent).join(','));
  FILTERS.tag.forEach(t=>params.push('tag='+encodeURIComponent(t)));
  FILTERS.gender.forEach(g=>params.push('gender='+encodeURIComponent(g)));
  if(FILTERS.size) params.push('size='+encodeURIComponent(FILTERS.size));
  FILTERS.current_show.forEach(cs=>params.push('current_show='+encodeURIComponent(cs)));
  if(FILTERS.is_new) params.push('is_new=true');
  params.push('limit='+camsPerPage);
  params.push('offset='+(camsPerPage*((FILTERS.page||1)-1)));
  if(FILTERS.minAge) params.push('minAge='+FILTERS.minAge);
  if(FILTERS.maxAge) params.push('maxAge='+FILTERS.maxAge);
  fetch(API+'?'+params.join('&'))
  .then(r=>r.json())
  .then(d=>{
    totalCount = d.count ? parseInt(d.count,10) : ((d.results||[]).length);
    allModels = d.results.filter(m=>
      m.age >= (FILTERS.minAge||18)
      && m.age <= (FILTERS.maxAge||99)
      && (!FILTERS.size || (m.num_users>=ROOMSIZE[FILTERS.size][0] && m.num_users<=ROOMSIZE[FILTERS.size][1]))
    );
    renderModels(allModels);
    renderPagination();
    loadTags();
    updateSelected();
    saveGridFilters();
    updateResetFiltersLink();
  });
}
function renderModels(models) {
  let el = document.getElementById('model-grid');
  if(models.length===0) { el.innerHTML = "<b>No results.</b>"; return; }
  el.innerHTML = models.map(m=>{
    let rawSubject = m.room_subject ? m.room_subject : '';
    let subjectWithTags = rawSubject.replace(
      /#(\w+)/g,
      '<a href="#" class="tag-cb subject-tag" data-tag="$1">#$1</a>'
    );
    let tmpDiv = document.createElement('div');
    tmpDiv.innerHTML = subjectWithTags;
    let nodes = Array.from(tmpDiv.childNodes);
    let displaySubject = '';
    let charCount = 0;
    for (let node of nodes) {
      let text = node.nodeType === 3 ? node.textContent : node.outerHTML;
      let c = node.nodeType === 3 ? text.length : node.textContent.length;
      if (charCount + c > 63) {
        if (node.nodeType === 3) displaySubject += text.slice(0, 63 - charCount) + '...';
        else break;
        break;
      }
      displaySubject += text;
      charCount += c;
    }

    // One chip per card (show type or new)
    let chipHTML = '';
    const showType = (m.current_show || '').toLowerCase();
    if (showType && showType !== 'public') {
      const showColors = { private: 'status-private', group: 'status-group', away: 'status-away', hidden: 'status-hidden' };
      const showLabels = { private: 'PRIVATE', group: 'GROUP', away: 'AWAY', hidden: 'HIDDEN' };
      let label = showLabels[showType] || m.current_show.toUpperCase();
      let colorClass = showColors[showType] || 'status-away';
      chipHTML = `<div class="current-show-chip ${colorClass}">${label}</div>`;
    } else if (m.is_new) {
      chipHTML = `<div class="current-show-chip status-new">NEW</div>`;
    }
    let arrMeta = [];
    arrMeta.push(`<span class="age-cb">${m.age}</span>`);
    if (m.gender) arrMeta.push(getGenderIcon(m.gender));
    if (m.country) arrMeta.push(`<span class="country-cb"><img class="flag-cb" src="https://flagcdn.com/16x12/${m.country.toLowerCase()}.png" alt="${m.country}"></span>`);
    let metaRow = `<div class="row-meta-cb">${arrMeta.join('')}</div>`;
    let href = "/model/" + encodeURIComponent(m.username);
    let timeString = (m.seconds_online >= 3600) ? ((m.seconds_online/3600).toFixed(1) + ' hrs') : (Math.floor((m.seconds_online%3600)/60) + ' mins');
    let viewers = (m.num_users ? `${m.num_users} viewers` : '');
    return `
      <div class="model-card-cb">
        <div class="model-img-wrap-cb" style="position:relative;">
          <a href="${href}">
            <img src="${m.image_url_360x270||m.image_url}" class="model-img-cb" alt="${m.username}">
          </a>
          ${chipHTML}
        </div>
        <div class="model-info-cb">
          <div class="row-top-cb">
            <a href="${href}" class="username-cb">${m.username}</a>
            ${metaRow}
          </div>
          <div class="subject-cb">${displaySubject}</div>
          <div class="meta-row-cb">
            <span class="meta-group-cb"><span class="icon-cb">&#128065;</span><span>${viewers}</span></span>
            <span class="meta-group-cb"><span class="icon-cb">&#9201;</span><span>${timeString}</span></span>
          </div>
        </div>
      </div>
    `;
  }).join('');
  document.querySelectorAll('.tag-cb.subject-tag').forEach(el => {
    el.addEventListener('click', function(e) {
      e.preventDefault();
      let tag = el.dataset.tag;
      if(!FILTERS.tag.includes(tag)) {
        if(FILTERS.tag.length>=5) FILTERS.tag.shift();
        FILTERS.tag.push(tag);
        onFilterChange();
      }
    });
  });
}
function renderPagination() {
  let totalPages = Math.ceil(totalCount / camsPerPage);
  let page = FILTERS.page || 1;
  if (totalPages <= 1) { document.getElementById('pagination-bar').innerHTML=''; return; }
  let html = '';
  html += `<button ${page==1?'disabled':''} onclick="gotoPage(${page-1})">&laquo; Prev</button>`;
  let start = Math.max(1,page-2), end = Math.min(totalPages,page+2);
  if(start > 1) html += `<span style="padding:0 3px;">...</span>`;
  for(let i=start;i<=end;i++) {
    if(i===page) html+=`<b>${i}</b>`;
    else html+=`<button onclick="gotoPage(${i})">${i}</button>`;
  }
  if(end < totalPages) html += `<span style="padding:0 3px;">...</span>`;
  html += `<button ${page==totalPages?'disabled':''} onclick="gotoPage(${page+1})">Next &raquo;</button>`;
  document.getElementById('pagination-bar').innerHTML = html;
}
function loadTags() {
  if(allTags.length) return renderTags();
  let found = new Set();
  allModels.forEach(m=>{
    (m.tags||[]).forEach(tag=>found.add(tag));
  });
  allTags = Array.from(found);
  renderTags();
}
function renderTags() {
  let w = document.querySelector('.filter-tags');
  let html = allTags.slice(0,46).map(t=>`<span class="filter-chip" data-tag="${t}">${t}</span>`).join('');
  w.innerHTML = html;
  document.querySelectorAll('.filter-chip[data-tag]').forEach(el=>{
    el.onclick = ()=>{
      let tag = el.dataset.tag;
      if(FILTERS.tag.includes(tag)) FILTERS.tag = FILTERS.tag.filter(x=>x!==tag);
      else {
        if(FILTERS.tag.length>=5) FILTERS.tag.shift();
        FILTERS.tag.push(tag);
      }
      onFilterChange();
    }
    if(FILTERS.tag.includes(el.dataset.tag)) el.classList.add('selected');
    else el.classList.remove('selected');
  });
}
function updateSelected() {
  document.querySelectorAll('.filter-chip[data-region]').forEach(el=>{
    if(FILTERS.region.includes(el.dataset.region)) el.classList.add('selected');
    else el.classList.remove('selected');
    el.onclick = ()=>{
      let region = el.dataset.region;
      if(FILTERS.region.includes(region))
        FILTERS.region = FILTERS.region.filter(r => r !== region);
      else
        FILTERS.region.push(region);
      onFilterChange();
    }
  });
  document.querySelectorAll('.filter-chip[data-size]').forEach(el=>{
    if(FILTERS.size===el.dataset.size) el.classList.add('selected');
    else el.classList.remove('selected');
    el.onclick = ()=>{
      FILTERS.size = (FILTERS.size===el.dataset.size)?null:el.dataset.size;
      onFilterChange();
    }
  });
  document.querySelectorAll('.filter-chip[data-gender]').forEach(el=>{
    if(FILTERS.gender.includes(el.dataset.gender)) el.classList.add('selected');
    else el.classList.remove('selected');
    el.onclick = ()=>{
      let g = el.dataset.gender;
      if(FILTERS.gender.includes(g))
        FILTERS.gender = FILTERS.gender.filter(x=>x!==g);
      else
        FILTERS.gender = [g]; // Only one gender (for clean path)
      onFilterChange();
    }
  });
  document.querySelectorAll('.filter-chip[data-current_show]').forEach(el=>{
    if(FILTERS.current_show.includes(el.dataset.current_show)) el.classList.add('selected');
    else el.classList.remove('selected');
  });
  updateResetFiltersLink();
}
function applyAge() {
  FILTERS.minAge = parseInt(document.getElementById('min-age').value)||18;
  FILTERS.maxAge = parseInt(document.getElementById('max-age').value)||99;
  onFilterChange();
}
document.getElementById('min-age').onchange = applyAge;
document.getElementById('max-age').onchange = applyAge;
document.getElementById('tag-search').oninput = function() {
  let val = this.value.toLowerCase();
  document.querySelectorAll('.filter-chip[data-tag]').forEach(e=>{
    e.style.display = e.textContent.toLowerCase().includes(val) ? '' : 'none';
  });
}
function isAnyFilterActive() {
  return (FILTERS.gender.length || FILTERS.region.length || FILTERS.tag.length || FILTERS.size ||
      (FILTERS.minAge && FILTERS.minAge !== 18) ||
      (FILTERS.maxAge && FILTERS.maxAge !== 99) ||
      FILTERS.current_show.length > 0 ||
      FILTERS.is_new
  );
}
function updateResetFiltersLink() {
  var el = document.getElementById('reset-filters-link');
  if (!el) return;
  if (isAnyFilterActive()) el.style.display = '';
  else el.style.display = 'none';
}
function resetFilters(ev) {
  if (ev) ev.preventDefault();
  FILTERS.gender = [];
  FILTERS.region = [];
  FILTERS.tag = [];
  FILTERS.size = null;
  FILTERS.minAge = 18;
  FILTERS.maxAge = 99;
  FILTERS.page = 1;
  FILTERS.current_show = [];
  FILTERS.is_new = false;
  saveGridFilters();
  window.location.href = "/";
}
// ========== AUTO-REFRESH FEATURE ==========
const AUTO_REFRESH_INTERVAL = 60000;
function isDesktop() {
  return window.matchMedia("(pointer: fine)").matches
    && !/android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini/i.test(navigator.userAgent);
}
let autoRefreshTimer = null;
let isAutoRefreshEnabled = false;
function getAutoRefreshUrl() {
  let params = [];
  if(FILTERS.region.length)
    params.push('region='+FILTERS.region.map(encodeURIComponent).join(','));
  FILTERS.tag.forEach(t=>params.push('tag='+encodeURIComponent(t)));
  FILTERS.gender.forEach(g=>params.push('gender='+encodeURIComponent(g)));
  if(FILTERS.size) params.push('size='+encodeURIComponent(FILTERS.size));
  FILTERS.current_show.forEach(cs=>params.push('current_show='+encodeURIComponent(cs)));
  if(FILTERS.is_new) params.push('is_new=true');
  params.push('limit='+camsPerPage);
  params.push('offset='+(camsPerPage*((FILTERS.page||1)-1)));
  if(FILTERS.minAge) params.push('minAge='+FILTERS.minAge);
  if(FILTERS.maxAge) params.push('maxAge='+FILTERS.maxAge);
  return API + '?' + params.join('&');
}
function startAutoRefresh() {
  stopAutoRefresh();
  if (!isDesktop() || !isAutoRefreshEnabled) return;
  autoRefreshTimer = setInterval(doAutoRefresh, AUTO_REFRESH_INTERVAL);
  doAutoRefresh();
}
function stopAutoRefresh() {
  if (autoRefreshTimer) clearInterval(autoRefreshTimer);
  autoRefreshTimer = null;
}
function attachAutoRefreshEventHandler() {
  const checkbox = document.getElementById('toggle-auto-refresh');
  if (!checkbox) return;
  checkbox.checked = isAutoRefreshEnabled;
  checkbox.onchange = function() {
    isAutoRefreshEnabled = this.checked;
    sessionStorage.setItem("livecams_auto_refresh", isAutoRefreshEnabled ? "1" : "0");
    if (isAutoRefreshEnabled) startAutoRefresh();
    else stopAutoRefresh();
  };
}
function setupAutoRefreshCheckboxBar() {
  const bar = document.getElementById('auto-refresh-bar');
  if (bar && isDesktop() && typeof FILTERS !== 'undefined') {
    bar.style.display = '';
    isAutoRefreshEnabled = sessionStorage.getItem("livecams_auto_refresh") === "1";
    attachAutoRefreshEventHandler();
    if (isAutoRefreshEnabled) startAutoRefresh();
  } else if (bar) {
    bar.style.display = 'none';
    stopAutoRefresh();
  }
}
function doAutoRefresh() {
  fetch(getAutoRefreshUrl())
    .then(r => r.json())
    .then(d => {
      if (!Array.isArray(d.results)) return;
      const newModels = d.results;
      let curModels = {};
      document.querySelectorAll('.model-card-cb').forEach(card => {
        const user = card.querySelector('.username-cb');
        if (user) curModels[user.textContent.trim().toLowerCase()] = card;
      });
      let newUsernames = new Set(newModels.map(m => (m.username || '').toLowerCase()));
      for (const uname in curModels) {
        if (!newUsernames.has(uname)) {
          curModels[uname].remove();
        }
      }
      const grid = document.getElementById('model-grid');
      if (!grid) return;
      let frag = document.createDocumentFragment();
      for (const m of newModels) {
        let chipHTML = '';
        const showTypeNormalized = (m.current_show || '').toLowerCase();
        if (showTypeNormalized && showTypeNormalized !== 'public') {
          const showColors = { private: 'status-private', group: 'status-group', away: 'status-away' };
          const showLabels = { private: 'PRIVATE', group: 'GROUP', away: 'AWAY' };
          let label = showLabels[showTypeNormalized] || m.current_show.toUpperCase();
          let colorClass = showColors[showTypeNormalized] || 'status-away';
          chipHTML = `<div class="current-show-chip ${colorClass}">${label}</div>`;
        } else if (m.is_new) {
          chipHTML = `<div class="current-show-chip status-new">NEW</div>`;
        }
        let arrMeta = [];
        arrMeta.push(`<span class="age-cb">${m.age}</span>`);
        if (m.gender) arrMeta.push(getGenderIcon(m.gender));
        if (m.country) arrMeta.push(`<span class="country-cb"><img class="flag-cb" src="https://flagcdn.com/16x12/${m.country.toLowerCase()}.png" alt="${m.country}"></span>`);
        let metaRow = `<div class="row-meta-cb">${arrMeta.join('')}</div>`;
        let href = "/model/" + encodeURIComponent(m.username);
        let timeString = (m.seconds_online >= 3600) ? ((m.seconds_online/3600).toFixed(1) + ' hrs') : (Math.floor((m.seconds_online%3600)/60) + ' mins');
        let viewers = (m.num_users ? `${m.num_users} viewers` : '');
        let rawSubject = m.room_subject ? m.room_subject : '';
        let subjectWithTags = rawSubject.replace(
          /#(\w+)/g,
          '<a href="#" class="tag-cb subject-tag" data-tag="$1">#$1</a>'
        );
        let tmpDiv = document.createElement('div'); tmpDiv.innerHTML = subjectWithTags;
        let nodes = Array.from(tmpDiv.childNodes); let displaySubject = ''; let charCount = 0;
        for (let node of nodes) {
          let text = node.nodeType === 3 ? node.textContent : node.outerHTML;
          let c = node.nodeType === 3 ? text.length : node.textContent.length;
          if (charCount + c > 63) {
            if (node.nodeType === 3) displaySubject += text.slice(0, 63 - charCount) + '...';
            else break;
            break;
          }
          displaySubject += text;
          charCount += c;
        }
        let imgUrl = (m.image_url_360x270 || m.image_url) + ((m.image_url_360x270 || m.image_url).indexOf('?') === -1 ? '?' : '&') + 'cb=' + Date.now();
        let cardHTML = `
          <div class="model-card-cb">
            <div class="model-img-wrap-cb" style="position:relative;">
              <a href="${href}">
                <img src="${imgUrl}" class="model-img-cb" alt="${m.username}">
              </a>
              ${chipHTML}
            </div>
            <div class="model-info-cb">
              <div class="row-top-cb">
                <a href="${href}" class="username-cb">${m.username}</a>
                ${metaRow}
              </div>
              <div class="subject-cb">${displaySubject}</div>
              <div class="meta-row-cb">
                <span class="meta-group-cb"><span class="icon-cb">&#128065;</span><span>${viewers}</span></span>
                <span class="meta-group-cb"><span class="icon-cb">&#9201;</span><span>${timeString}</span></span>
              </div>
            </div>
          </div>
        `;
        let outer = document.createElement('div');
        outer.innerHTML = cardHTML.trim();
        frag.appendChild(outer.firstChild);
      }
      grid.innerHTML = '';
      grid.appendChild(frag);
      document.querySelectorAll('.tag-cb.subject-tag').forEach(el => {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          let tag = el.dataset.tag;
          if (!FILTERS.tag.includes(tag)) {
            if (FILTERS.tag.length >= 5) FILTERS.tag.shift();
            FILTERS.tag.push(tag);
            onFilterChange();
          }
        });
      });
    });
}
document.addEventListener('DOMContentLoaded', setupAutoRefreshCheckboxBar);
fetchModels();
updateSelected();
updateResetFiltersLink();
</script>
<?php include('templates/footer.php'); ?>