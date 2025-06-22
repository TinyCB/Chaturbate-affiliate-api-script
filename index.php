<?php
$config = include('config.php');
$camsPerPage = isset($config['cams_per_page']) ? intval($config['cams_per_page']) : 20;
$meta_title = $config['meta_home_title'];
$meta_desc  = $config['meta_home_desc'];
if (isset($_GET['gender']) && count((array)$_GET['gender']) == 1) {
    $g = is_array($_GET['gender']) ? $_GET['gender'][0] : $_GET['gender'];
    if (isset($config['meta_gender_titles'][$g]))   $meta_title = $config['meta_gender_titles'][$g];
    if (isset($config['meta_gender_descs'][$g]))    $meta_desc  = $config['meta_gender_descs'][$g];
}
include('templates/header.php');
?>
<style>
/* SIDEBAR: Minimal, perfectly visually integrated */
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

/* If you use .filter-chip:hover accent color, you can also base it on primary-color */
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
</style>
<div id="main-flex-wrap">
<aside id="filter-sidebar" class="open">
  <button class="close-btn" onclick="toggleSidebar()" title="Hide Filters">&#10006; Hide Sidebar</button>
  <div class="filter-section">
    <div class="filter-label">Gender</div>
    <div class="filter-gender">
      <span class="filter-chip" data-gender="f">&#9792; Female</span>
      <span class="filter-chip" data-gender="m">&#9794; Male</span>
      <span class="filter-chip" data-gender="t">&#9895; Trans</span>
      <span class="filter-chip" data-gender="c">&#9792;&#9794; Couple</span>
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
</aside>
<div class="main-content">
  <div class="model-grid" id="model-grid"></div>
  <div class="pagination-bar" id="pagination-bar"></div>
</div>
</div>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<script>
function saveGridFilters() {
    try { sessionStorage.setItem("livecams_filters", JSON.stringify(FILTERS)); } catch(e) {}
}
function loadGridFilters() {
    let s = null;
    try { s = sessionStorage.getItem("livecams_filters"); } catch(e) {}
    if (s) try { return JSON.parse(s); } catch(e) {}
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

// Parse path for gender & page
function parsePrettyPath() {
    const path = window.location.pathname.replace(/\/+/g, '/');
    let gender = '';
    let page = 1;
    let match;
    if ((match = /^\/(girls|guys|trans|couples)\/page\/([0-9]+)\/?$/.exec(path))) {
        return { gender: {girls: 'f', guys: 'm', trans: 't', couples: 'c'}[match[1]], page: parseInt(match[2],10) };
    }
    if ((match = /^\/page\/([0-9]+)\/?$/.exec(path))) {
        return { gender: '', page: parseInt(match[1], 10) };
    }
    if ((match = /^\/(girls|guys|trans|couples)\/?$/.exec(path))) {
        return { gender: {girls: 'f', guys: 'm', trans: 't', couples: 'c'}[match[1]], page: 1 };
    }
    return { gender: '', page: 1 };
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

const FILTERS = {
  gender: urlFilters.gender,
  region: urlFilters.region,
  tag: urlFilters.tag,
  minAge: urlFilters.minAge,
  maxAge: urlFilters.maxAge,
  size: urlFilters.size,
  page: urlFilters.page,
};

let currentPage = FILTERS.page || 1;

// Sidebar toggle logic
function toggleSidebar() {
  var sidebar = document.getElementById('filter-sidebar');
  if (!sidebar) return;
  if (sidebar.classList.contains('open')) {
    sidebar.classList.remove('open');
  } else {
    sidebar.classList.add('open');
  }
}
document.addEventListener('DOMContentLoaded', function() {
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
    document.getElementById('min-age').value = FILTERS.minAge;
    document.getElementById('max-age').value = FILTERS.maxAge;
    var resetEl = document.getElementById('reset-filters-link');
    if (resetEl) resetEl.onclick = resetFilters;
    updateResetFiltersLink();
});

function genderToPath() {
    if (FILTERS.gender && FILTERS.gender.length) {
        if (FILTERS.gender[0] === 'f') return '/girls';
        if (FILTERS.gender[0] === 'm') return '/guys';
        if (FILTERS.gender[0] === 't') return '/trans';
        if (FILTERS.gender[0] === 'c') return '/couples';
    }
    return '/';
}

function getCurrentPath() {
    let path = '';
    if (FILTERS.gender && FILTERS.gender.length) {
        if (FILTERS.gender[0] === 'f') path = '/girls';
        else if (FILTERS.gender[0] === 'm') path = '/guys';
        else if (FILTERS.gender[0] === 't') path = '/trans';
        else if (FILTERS.gender[0] === 'c') path = '/couples';
    } else {
        path = '/';
    }
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
  if(FILTERS.region.length)
      params.push('region='+FILTERS.region.map(encodeURIComponent).join(','));
  FILTERS.tag.forEach(t=>params.push('tag='+encodeURIComponent(t)));
  FILTERS.gender.forEach(g=>params.push('gender='+encodeURIComponent(g)));
  if(FILTERS.size) params.push('size='+encodeURIComponent(FILTERS.size));
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
    let subjectWithTags = rawSubject.replace(/#(\w+)/g, '<span class="tag-cb">#$1</span>');
    let tmpDiv = document.createElement('div');
    tmpDiv.innerHTML = subjectWithTags;
    let nodes = Array.from(tmpDiv.childNodes);
    let displaySubject = '';
    let charCount = 0;
    for (let node of nodes) {
      let text = node.nodeType === 3 ? node.textContent : node.outerHTML;
      let c = node.nodeType === 3 ? text.length : node.textContent.length;
      if (charCount + c > 63) {
        if (node.nodeType === 3) {
          displaySubject += text.slice(0, 63 - charCount) + '...';
        } else {
          break;
        }
        break;
      }
      displaySubject += text;
      charCount += c;
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
      <div class="model-img-wrap-cb">
        <a href="${href}">
          <img src="${m.image_url_360x270||m.image_url}" class="model-img-cb" alt="${m.username}">
        </a>
      </div>
      <div class="model-info-cb">
        <div class="row-top-cb">
          <span class="username-cb">${m.username}</span>
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
      (FILTERS.maxAge && FILTERS.maxAge !== 99));
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
  saveGridFilters();
  window.location.href = "/";
}
fetchModels();
updateSelected();
updateResetFiltersLink();
</script>
<?php include('templates/footer.php'); ?>