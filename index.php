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
<aside id="filter-sidebar" class="">
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
    </div>
    <div id="age-validation-error" style="display: none; color: #d32f2f; font-size: 11px; padding: 6px 8px; background: #ffebee; border-radius: 4px; border: 1px solid #ffcdd2; margin-top: 4px; width: 100%; box-sizing: border-box;">
      ⚠️ Min age can't be higher than max age
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
    <div class="filter-label">Spotlights</div>
    <div class="filter-spotlights">
      <span class="filter-chip" data-spotlight="super-star">🌟 Superstar</span>
      <span class="filter-chip" data-spotlight="trending">🔥 Trending</span>
      <span class="filter-chip" data-spotlight="top-performer">⭐ Top Performer</span>
      <span class="filter-chip" data-spotlight="just-live">⚡ Just Live</span>
      <span class="filter-chip" data-spotlight="marathon">🎯 Marathon</span>
      <span class="filter-chip" data-spotlight="rising-star">🚀 Rising Star</span>
      <span class="filter-chip" data-spotlight="hd-quality">✨ HD Stream</span>
      <span class="filter-chip" data-spotlight="interactive">🎪 Interactive</span>
      <span class="filter-chip" data-spotlight="multilingual">🗣️ Multilingual</span>
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
urlFilters.spotlight = sessionFilters.spotlight || [];
const FILTERS = {
  gender: urlFilters.gender,
  region: urlFilters.region,
  tag: urlFilters.tag,
  minAge: urlFilters.minAge,
  maxAge: urlFilters.maxAge,
  size: urlFilters.size,
  current_show: urlFilters.current_show,
  is_new: urlFilters.is_new,
  spotlight: urlFilters.spotlight,
  page: urlFilters.page,
};
let currentPage = FILTERS.page || 1;
function toggleSidebar() {
  var sidebar = document.getElementById('filter-sidebar');
  var backdrop = document.getElementById('sidebar-backdrop');
  var isMobile = window.innerWidth <= 768;
  
  if (!sidebar) return;
  
  if (sidebar.classList.contains('open')) {
    sidebar.classList.remove('open');
    if (backdrop && isMobile) backdrop.classList.remove('active');
    document.body.style.overflow = '';
  } else {
    sidebar.classList.add('open');
    if (backdrop && isMobile) backdrop.classList.add('active');
    // Prevent body scroll on mobile when sidebar is open
    if (isMobile) {
      document.body.style.overflow = 'hidden';
    }
  }
}
document.addEventListener('DOMContentLoaded', function() {
    // Create and add backdrop element
    var backdrop = document.createElement('div');
    backdrop.id = 'sidebar-backdrop';
    backdrop.className = 'sidebar-backdrop';
    backdrop.onclick = toggleSidebar;
    document.body.appendChild(backdrop);
    
    // Set sidebar state based on screen size
    var sidebar = document.getElementById('filter-sidebar');
    if (sidebar) {
        if (window.innerWidth > 768) {
            // Desktop: open sidebar by default
            sidebar.classList.add('open');
        } else {
            // Mobile: closed by default
            sidebar.classList.remove('open');
        }
    }
    
    var filterBtn = document.getElementById('filter-toggle');
    if (filterBtn) filterBtn.onclick = toggleSidebar;
    var closeBtn = document.querySelector('#filter-sidebar .close-btn');
    if (closeBtn) closeBtn.onclick = toggleSidebar;
    
    // Handle window resize to ensure proper behavior
    window.addEventListener('resize', function() {
        var sidebar = document.getElementById('filter-sidebar');
        var backdrop = document.getElementById('sidebar-backdrop');
        var isMobile = window.innerWidth <= 768;
        
        if (sidebar && backdrop) {
            if (!isMobile) {
                // On desktop, hide backdrop
                backdrop.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                // On mobile, ensure sidebar is closed
                if (sidebar.classList.contains('open')) {
                    backdrop.classList.add('active');
                } else {
                    backdrop.classList.remove('active');
                }
            }
        }
    });
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
    // Only set input values if they're not already set by user
    const minAgeInput = document.getElementById('min-age');
    const maxAgeInput = document.getElementById('max-age');
    console.log('DOMContentLoaded - current values:', minAgeInput.value, maxAgeInput.value);
    console.log('FILTERS values:', FILTERS.minAge, FILTERS.maxAge);
    
    if (minAgeInput.value == '18' && FILTERS.minAge && FILTERS.minAge != 18) {
        console.log('Setting minAge input to:', FILTERS.minAge);
        minAgeInput.value = FILTERS.minAge;
    }
    if (maxAgeInput.value == '99' && FILTERS.maxAge && FILTERS.maxAge != 99) {
        console.log('Setting maxAge input to:', FILTERS.maxAge);
        maxAgeInput.value = FILTERS.maxAge;
    }
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
      el.addEventListener('click', function(e) {
        e.preventDefault();
        const cs = el.dataset.current_show;
        if (FILTERS.current_show.includes(cs)) {
          FILTERS.current_show = FILTERS.current_show.filter(x => x !== cs);
          el.classList.remove('selected');
        } else {
          FILTERS.current_show.push(cs);
          el.classList.add('selected');
        }
        onFilterChange();
      });
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
    
    // Update URL without page reload (for browser history/bookmarking)
    const newPath = getCurrentPath();
    if (window.history && window.history.pushState) {
        window.history.pushState({}, '', newPath);
    }
    
    // Fetch new results without page reload to keep sidebar open
    fetchModels();
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
    
    // Calculate global stats for spotlight detection
    const globalStats = {
      avgViewers: d.results.reduce((sum, m) => sum + parseInt(m.num_users || 0), 0) / d.results.length,
      maxViewers: Math.max(...d.results.map(m => parseInt(m.num_users || 0))),
      avgOnlineTime: d.results.reduce((sum, m) => sum + parseInt(m.seconds_online || 0), 0) / d.results.length,
      maxOnlineTime: Math.max(...d.results.map(m => parseInt(m.seconds_online || 0)))
    };
    
    // First, apply basic server-side compatible filters only
    let filteredModels = d.results.filter(m=>
      m.age >= (FILTERS.minAge||18)
      && m.age <= (FILTERS.maxAge||99)
      && (!FILTERS.size || (m.num_users>=ROOMSIZE[FILTERS.size][0] && m.num_users<=ROOMSIZE[FILTERS.size][1]))
    );
    
    // Apply client-side spotlight filtering if needed
    let hasSpotlightFilter = FILTERS.spotlight && FILTERS.spotlight.length > 0;
    if (hasSpotlightFilter) {
      console.log('Applying spotlight filter for:', FILTERS.spotlight);
      console.log('Global stats (consistent):', globalStats);
      console.log('Sample model data:', d.results[0]); // Show structure of first model
      filteredModels = filteredModels.filter(m => {
        // IMPORTANT: Use d.results (all models) for consistent global stats
        const allModelSpotlights = detectAllModelSpotlights(m, d.results, globalStats);
        const hasMatchingSpotlight = allModelSpotlights.some(spotlight => 
          FILTERS.spotlight.includes(spotlight.type)
        );
        if (allModelSpotlights.length > 0) {
          console.log(`Model ${m.username}: spotlights=${JSON.stringify(allModelSpotlights.map(s => s.type))}, matches=${hasMatchingSpotlight}`);
        }
        return hasMatchingSpotlight;
      });
      console.log(`Filtered ${d.results.length} models down to ${filteredModels.length} with spotlight filters`);
    }
    
    // Sort models by spotlight priority, then by viewers
    filteredModels = sortModelsBySpotlight(filteredModels);
    
    // Handle pagination for client-side filtering
    if (hasSpotlightFilter) {
      // Client-side pagination when spotlight filters are active
      const totalFiltered = filteredModels.length;
      const startIndex = ((FILTERS.page || 1) - 1) * camsPerPage;
      const endIndex = startIndex + camsPerPage;
      allModels = filteredModels.slice(startIndex, endIndex);
      
      // Update totalCount for pagination
      const originalTotalCount = totalCount;
      totalCount = totalFiltered;
      renderModels(allModels);
      renderPagination();
      // Restore original totalCount for next API call
      totalCount = originalTotalCount;
    } else {
      // Server-side pagination for normal filtering
      allModels = filteredModels;
      renderModels(allModels);
      renderPagination();
    }
    loadTags();
    updateSelected();
    saveGridFilters();
    updateResetFiltersLink();
  });
}

// =============================================
// MODEL SPOTLIGHT SYSTEM
// =============================================

function detectAllModelSpotlights(model, allModels, globalStats) {
  const spotlights = [];
  const now = Date.now() / 1000;
  const currentHour = new Date().getHours();
  const isWeekend = [0, 6].includes(new Date().getDay());
  
  // Get model stats
  const viewerCount = parseInt(model.num_users || 0);
  const secondsOnline = parseInt(model.seconds_online || 0);
  const age = parseInt(model.age || 18);
  const followerCount = parseInt(model.num_followers || 0);
  const isNew = !!model.is_new;
  const isHD = !!model.is_hd;
  const hoursOnline = secondsOnline / 3600;
  const tags = model.tags || [];
  const languages = (model.spoken_languages || '').split(',').filter(l => l.trim());
  
  // Debug logging for first few models
  if (Math.random() < 0.1) { // Log ~10% of models to avoid spam
    console.log(`\n=== SPOTLIGHT DEBUG: ${model.username} ===`);
    console.log(`Viewers: ${viewerCount}, Online: ${hoursOnline.toFixed(1)}h, HD: ${isHD}, New: ${isNew}`);
    console.log(`Languages: [${languages.join(', ')}], Subject length: ${model.room_subject?.length || 0}`);
    console.log(`Global stats - Avg: ${globalStats.avgViewers.toFixed(1)}, Max: ${globalStats.maxViewers}`);
  }
  
  // Calculate global stats if not provided
  if (!globalStats) {
    const allViewers = allModels.map(m => parseInt(m.num_users || 0));
    const allOnlineTimes = allModels.map(m => parseInt(m.seconds_online || 0));
    globalStats = {
      avgViewers: allViewers.reduce((a, b) => a + b, 0) / allViewers.length,
      maxViewers: Math.max(...allViewers),
      avgOnlineTime: allOnlineTimes.reduce((a, b) => a + b, 0) / allOnlineTimes.length,
      maxOnlineTime: Math.max(...allOnlineTimes)
    };
  }
  
  // SUPERSTAR DETECTION (most exclusive)
  const superSpotlightScore = (isHD ? 2 : 0) + 
                            (viewerCount >= globalStats.avgViewers * 1.5 ? 3 : 0) + 
                            (hoursOnline >= 2 ? 2 : 0) + 
                            (isNew ? 2 : 0) + 
                            (languages.length >= 2 ? 1 : 0);
  
  if (superSpotlightScore >= 7) {
    spotlights.push({ type: 'super-star', label: 'SUPERSTAR', icon: '🌟', priority: 11 });
  }
  
  // Check ALL applicable spotlights (not mutually exclusive for filtering)
  const debugThis = Math.random() < 0.1;
  
  const trendingThreshold = Math.max(20, globalStats.maxViewers * 0.7);
  if (viewerCount >= trendingThreshold) {
    spotlights.push({ type: 'trending', label: 'TRENDING', icon: '🔥', priority: 10 });
    if (debugThis) console.log(`✓ TRENDING: ${viewerCount} >= ${trendingThreshold.toFixed(1)}`);
  } else if (debugThis) console.log(`✗ trending: ${viewerCount} < ${trendingThreshold.toFixed(1)}`);
  
  const performerThreshold = Math.max(10, globalStats.avgViewers * 2);
  if (viewerCount >= performerThreshold) {
    spotlights.push({ type: 'top-performer', label: 'TOP PERFORMER', icon: '⭐', priority: 9 });
    if (debugThis) console.log(`✓ TOP PERFORMER: ${viewerCount} >= ${performerThreshold.toFixed(1)}`);
  } else if (debugThis) console.log(`✗ top-performer: ${viewerCount} < ${performerThreshold.toFixed(1)}`);
  
  const justLiveThreshold = Math.max(1, globalStats.avgViewers * 0.2);
  if (secondsOnline <= 3600 && viewerCount >= justLiveThreshold) {
    spotlights.push({ type: 'just-live', label: 'JUST LIVE', icon: '⚡', priority: 8 });
    if (debugThis) console.log(`✓ JUST LIVE: ${secondsOnline}s <= 3600 && ${viewerCount} >= ${justLiveThreshold.toFixed(1)}`);
  } else if (debugThis) console.log(`✗ just-live: ${secondsOnline}s > 3600 || ${viewerCount} < ${justLiveThreshold.toFixed(1)}`);
  
  if (hoursOnline >= 3) {
    spotlights.push({ type: 'marathon', label: 'MARATHON', icon: '🎯', priority: 7 });
    if (debugThis) console.log(`✓ MARATHON: ${hoursOnline.toFixed(1)}h >= 3`);
  } else if (debugThis) console.log(`✗ marathon: ${hoursOnline.toFixed(1)}h < 3`);
  
  const risingStarThreshold = Math.max(5, globalStats.avgViewers * 0.5);
  if (isNew && viewerCount >= risingStarThreshold) {
    spotlights.push({ type: 'rising-star', label: 'RISING STAR', icon: '🚀', priority: 6 });
    if (debugThis) console.log(`✓ RISING STAR: isNew=${isNew} && ${viewerCount} >= ${risingStarThreshold.toFixed(1)}`);
  } else if (debugThis) console.log(`✗ rising-star: isNew=${isNew} || ${viewerCount} < ${risingStarThreshold.toFixed(1)}`);
  
  const hdThreshold = Math.max(1, globalStats.avgViewers * 0.3);
  if (isHD && viewerCount >= hdThreshold) {
    spotlights.push({ type: 'hd-quality', label: 'HD STREAM', icon: '✨', priority: 5 });
    if (debugThis) console.log(`✓ HD QUALITY: isHD=${isHD} && ${viewerCount} >= ${hdThreshold.toFixed(1)}`);
  } else if (debugThis) console.log(`✗ hd-quality: isHD=${isHD} || ${viewerCount} < ${hdThreshold.toFixed(1)}`);
  
  if (model.room_subject && model.room_subject.length > 20) {
    spotlights.push({ type: 'interactive', label: 'INTERACTIVE', icon: '🎪', priority: 4 });
    if (debugThis) console.log(`✓ INTERACTIVE: subject length ${model.room_subject.length} > 20`);
  } else if (debugThis) console.log(`✗ interactive: subject length ${model.room_subject?.length || 0} <= 20`);
  
  if (languages.length >= 2) {
    spotlights.push({ type: 'multilingual', label: 'MULTILINGUAL', icon: '🗣️', priority: 3 });
    if (debugThis) console.log(`✓ MULTILINGUAL: ${languages.length} languages >= 2`);
  } else if (debugThis) console.log(`✗ multilingual: ${languages.length} languages < 2`);
  
  if (debugThis && spotlights.length > 0) {
    console.log(`Final spotlights: ${spotlights.map(s => s.type).join(', ')}`);
  }
  
  // Return ALL applicable spotlights for filtering
  return spotlights.sort((a, b) => b.priority - a.priority);
}

function detectModelSpotlights(model, allModels, globalStats) {
  // Get all applicable spotlights but return only the top one for display
  const allSpotlights = detectAllModelSpotlights(model, allModels, globalStats);
  return allSpotlights.slice(0, 1);
}

function renderSophisticatedSpotlight(spotlights) {
  if (!spotlights || spotlights.length === 0) return { cornerHTML: '', overlayHTML: '', cardClass: '' };
  
  // Get the highest priority spotlight for the corner indicator
  const topSpotlight = spotlights[0];
  
  // Determine priority class
  let priorityClass = 'priority-low';
  if (topSpotlight.priority >= 9) priorityClass = 'priority-high';
  else if (topSpotlight.priority >= 6) priorityClass = 'priority-medium';
  
  // Create subtle corner indicator with spotlight-specific styling
  const cornerHTML = `
    <div class="spotlight-corner ${priorityClass} corner-${topSpotlight.type}">
      <span>${topSpotlight.icon}</span>
      <div class="spotlight-tooltip">${topSpotlight.label}</div>
    </div>`;
  
  // Create hover overlay with spotlight info (max 1 spotlight for elegance)
  const overlayHTML = `
    <div class="spotlight-overlay">
      <div class="spotlight-info">
        <span class="spotlight-icon-small">${topSpotlight.icon}</span>
        <span class="spotlight-text-small">${topSpotlight.label}</span>
      </div>
    </div>`;
  
  return {
    cornerHTML,
    overlayHTML,
    cardClass: `spotlighted spotlight-${topSpotlight.type}`
  };
}

function sortModelsBySpotlight(models) {
  // First, calculate global stats for all models
  const globalStats = {
    avgViewers: models.reduce((sum, m) => sum + parseInt(m.num_users || 0), 0) / models.length,
    maxViewers: Math.max(...models.map(m => parseInt(m.num_users || 0))),
    avgOnlineTime: models.reduce((sum, m) => sum + parseInt(m.seconds_online || 0), 0) / models.length,
    maxOnlineTime: Math.max(...models.map(m => parseInt(m.seconds_online || 0)))
  };
  
  // Add spotlight data to each model
  const modelsWithSpotlights = models.map(model => {
    const spotlights = detectModelSpotlights(model, models, globalStats);
    const topSpotlight = spotlights.length > 0 ? spotlights[0] : null;
    
    return {
      ...model,
      _spotlightPriority: topSpotlight ? topSpotlight.priority : 0,
      _spotlightType: topSpotlight ? topSpotlight.type : null,
      _viewers: parseInt(model.num_users || 0),
      _onlineTime: parseInt(model.seconds_online || 0)
    };
  });
  
  // Sort by: 
  // 1. Spotlight priority (highest first)
  // 2. Viewer count (highest first) 
  // 3. Online time (longest first)
  return modelsWithSpotlights.sort((a, b) => {
    // Primary sort: Spotlight priority
    if (a._spotlightPriority !== b._spotlightPriority) {
      return b._spotlightPriority - a._spotlightPriority;
    }
    
    // Secondary sort: Viewer count
    if (a._viewers !== b._viewers) {
      return b._viewers - a._viewers;
    }
    
    // Tertiary sort: Online time
    return b._onlineTime - a._onlineTime;
  });
}

function renderModels(models) {
  let el = document.getElementById('model-grid');
  if(models.length===0) { el.innerHTML = "<b>No results.</b>"; return; }
  // Calculate global stats for spotlight detection
  const globalStats = {
    avgViewers: models.reduce((sum, m) => sum + parseInt(m.num_users || 0), 0) / models.length,
    maxViewers: Math.max(...models.map(m => parseInt(m.num_users || 0))),
    avgOnlineTime: models.reduce((sum, m) => sum + parseInt(m.seconds_online || 0), 0) / models.length,
    maxOnlineTime: Math.max(...models.map(m => parseInt(m.seconds_online || 0)))
  };
  
  el.innerHTML = models.map(m=>{
    // Detect spotlights for this model
    const modelSpotlights = detectModelSpotlights(m, models, globalStats);
    const spotlightElements = renderSophisticatedSpotlight(modelSpotlights);
    
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
      <div class="model-card-cb ${spotlightElements.cardClass}">
        <div class="model-img-wrap-cb" style="position:relative;">
          <a href="${href}">
            <img src="${m.image_url_360x270||m.image_url}" class="model-img-cb" alt="${m.username}">
          </a>
          ${chipHTML}
          ${spotlightElements.cornerHTML}
          ${spotlightElements.overlayHTML}
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
    function addClickAndTouchListeners(element, handler) {
      element.addEventListener('click', handler);
    }
    
    addClickAndTouchListeners(el, function(e) {
      e.preventDefault();
      let tag = el.dataset.tag;
      if(FILTERS.tag.includes(tag)) {
        FILTERS.tag = FILTERS.tag.filter(x=>x!==tag);
        el.classList.remove('selected');
      } else {
        if(FILTERS.tag.length>=5) FILTERS.tag.shift();
        FILTERS.tag.push(tag);
        el.classList.add('selected');
      }
      onFilterChange();
    });
    if(FILTERS.tag.includes(el.dataset.tag)) el.classList.add('selected');
    else el.classList.remove('selected');
  });
}
function attachFilterListeners() {
  function addClickAndTouchListeners(element, handler) {
    element.addEventListener('click', handler);
  }
  
  document.querySelectorAll('.filter-chip[data-region]').forEach(el=>{
    addClickAndTouchListeners(el, function(e) {
      e.preventDefault();
      let region = el.dataset.region;
      if(FILTERS.region.includes(region)) {
        FILTERS.region = FILTERS.region.filter(r => r !== region);
        el.classList.remove('selected');
      } else {
        FILTERS.region.push(region);
        el.classList.add('selected');
      }
      onFilterChange();
    });
  });
  document.querySelectorAll('.filter-chip[data-size]').forEach(el=>{
    addClickAndTouchListeners(el, function(e) {
      e.preventDefault();
      // Update visual state immediately
      document.querySelectorAll('.filter-chip[data-size]').forEach(sizeEl => sizeEl.classList.remove('selected'));
      
      if(FILTERS.size === el.dataset.size) {
        FILTERS.size = null;
      } else {
        FILTERS.size = el.dataset.size;
        el.classList.add('selected');
      }
      onFilterChange();
    });
  });
  document.querySelectorAll('.filter-chip[data-gender]').forEach(el=>{
    addClickAndTouchListeners(el, function(e) {
      e.preventDefault();
      let g = el.dataset.gender;
      
      // Update visual state immediately
      document.querySelectorAll('.filter-chip[data-gender]').forEach(genderEl => genderEl.classList.remove('selected'));
      
      if(FILTERS.gender.includes(g)) {
        FILTERS.gender = FILTERS.gender.filter(x=>x!==g);
      } else {
        FILTERS.gender = [g]; // Only one gender (for clean path)
        el.classList.add('selected');
      }
      onFilterChange();
    });
  });
  
  // Spotlight filters
  document.querySelectorAll('.filter-chip[data-spotlight]').forEach(el=>{
    addClickAndTouchListeners(el, function(e) {
      e.preventDefault();
      let spotlight = el.dataset.spotlight;
      if(FILTERS.spotlight.includes(spotlight)) {
        FILTERS.spotlight = FILTERS.spotlight.filter(s => s !== spotlight);
        el.classList.remove('selected');
      } else {
        FILTERS.spotlight.push(spotlight);
        el.classList.add('selected');
      }
      onFilterChange();
    });
  });
}

function updateSelected() {
  document.querySelectorAll('.filter-chip[data-region]').forEach(el=>{
    if(FILTERS.region.includes(el.dataset.region)) el.classList.add('selected');
    else el.classList.remove('selected');
  });
  document.querySelectorAll('.filter-chip[data-size]').forEach(el=>{
    if(FILTERS.size===el.dataset.size) el.classList.add('selected');
    else el.classList.remove('selected');
  });
  document.querySelectorAll('.filter-chip[data-gender]').forEach(el=>{
    if(FILTERS.gender.includes(el.dataset.gender)) el.classList.add('selected');
    else el.classList.remove('selected');
  });
  document.querySelectorAll('.filter-chip[data-current_show]').forEach(el=>{
    if(FILTERS.current_show.includes(el.dataset.current_show)) el.classList.add('selected');
    else el.classList.remove('selected');
  });
  
  // Spotlight filters
  document.querySelectorAll('.filter-chip[data-spotlight]').forEach(el=>{
    if(FILTERS.spotlight.includes(el.dataset.spotlight)) el.classList.add('selected');
    else el.classList.remove('selected');
  });
  updateResetFiltersLink();
}
function applyAge() {
  let minInput = document.getElementById('min-age').value.trim();
  let maxInput = document.getElementById('max-age').value.trim();
  let errorDiv = document.getElementById('age-validation-error');
  
  console.log('applyAge called - minInput:', minInput, 'maxInput:', maxInput);
  
  // Parse values with defaults
  let minAge = minInput === '' ? 18 : parseInt(minInput) || 18;
  let maxAge = maxInput === '' ? 99 : parseInt(maxInput) || 99;
  
  // Validation: Check if min age is higher than max age
  if (minInput !== '' && maxInput !== '' && minAge > maxAge) {
    // Show error and don't apply the filter
    errorDiv.style.display = 'block';
    console.log('Validation error: minAge (' + minAge + ') > maxAge (' + maxAge + ')');
    return; // Don't update filters
  } else {
    // Hide error if validation passes
    errorDiv.style.display = 'none';
  }
  
  // Apply basic validation to filtering values
  if (minAge < 18) minAge = 18;
  
  console.log('Final filtering values - minAge:', minAge, 'maxAge:', maxAge);
  FILTERS.minAge = minAge;
  FILTERS.maxAge = maxAge;
  onFilterChange();
}

// Use oninput for real-time filtering as user types
document.addEventListener('DOMContentLoaded', function() {
    const minAgeEl = document.getElementById('min-age');
    const maxAgeEl = document.getElementById('max-age');
    console.log('Setting up age input handlers');
    
    if (minAgeEl) {
        minAgeEl.oninput = applyAge;
        console.log('Min age handler attached');
    }
    if (maxAgeEl) {
        maxAgeEl.oninput = applyAge;
        console.log('Max age handler attached');
    }
});
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
      
      // Calculate global stats for spotlight detection (same as renderModels)
      const globalStats = {
        avgViewers: newModels.reduce((sum, m) => sum + parseInt(m.num_users || 0), 0) / newModels.length,
        maxViewers: Math.max(...newModels.map(m => parseInt(m.num_users || 0))),
        avgOnlineTime: newModels.reduce((sum, m) => sum + parseInt(m.seconds_online || 0), 0) / newModels.length,
        maxOnlineTime: Math.max(...newModels.map(m => parseInt(m.seconds_online || 0)))
      };
      
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
        // Detect spotlights for this model (same as renderModels)
        const modelSpotlights = detectModelSpotlights(m, newModels, globalStats);
        const spotlightElements = renderSophisticatedSpotlight(modelSpotlights);
        
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
          <div class="model-card-cb ${spotlightElements.cardClass}">
            <div class="model-img-wrap-cb" style="position:relative;">
              <a href="${href}">
                <img src="${imgUrl}" class="model-img-cb" alt="${m.username}">
              </a>
              ${chipHTML}
              ${spotlightElements.cornerHTML}
              ${spotlightElements.overlayHTML}
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
document.addEventListener('DOMContentLoaded', function() {
  setupAutoRefreshCheckboxBar();
  attachFilterListeners();
  fetchModels();
  updateSelected();
  updateResetFiltersLink();
});
</script>

<!-- Spotlight Help Modal -->
<div id="spotlight-modal" class="spotlight-modal" style="display: none;">
  <div class="spotlight-modal-content">
    <div class="spotlight-modal-header">
      <h2>Spotlight System Guide</h2>
      <button id="close-spotlight-modal" class="modal-close-btn">&times;</button>
    </div>
    <div class="spotlight-modal-body">
      <p class="modal-intro">Spotlights highlight exceptional models based on real-time performance data. Only the most impressive models earn these recognition badges!</p>
      
      <div class="spotlight-categories">
        <div class="spotlight-category">
          <div class="category-header priority-high">
            <h3>🌟 Top Tier Spotlights</h3>
            <span class="priority-badge high">Highest Priority</span>
          </div>
          <div class="spotlight-items">
            <div class="spotlight-item">
              <span class="spotlight-icon-large">🌟</span>
              <div class="spotlight-details">
                <div class="spotlight-name">SUPERSTAR</div>
                <div class="spotlight-description">Models with multiple exceptional qualities: HD streams, high viewership, long sessions, and more</div>
              </div>
            </div>
            <div class="spotlight-item">
              <span class="spotlight-icon-large">🔥</span>
              <div class="spotlight-details">
                <div class="spotlight-name">TRENDING</div>
                <div class="spotlight-description">Currently attracting 80%+ of the maximum viewers on the platform</div>
              </div>
            </div>
            <div class="spotlight-item">
              <span class="spotlight-icon-large">⭐</span>
              <div class="spotlight-details">
                <div class="spotlight-name">TOP PERFORMER</div>
                <div class="spotlight-description">Drawing 2.5x more viewers than average - exceptional appeal</div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="spotlight-category">
          <div class="category-header priority-medium">
            <h3>⚡ Activity Spotlights</h3>
            <span class="priority-badge medium">High Priority</span>
          </div>
          <div class="spotlight-items">
            <div class="spotlight-item">
              <span class="spotlight-icon-large">⚡</span>
              <div class="spotlight-details">
                <div class="spotlight-name">JUST LIVE</div>
                <div class="spotlight-description">Recently came online (within 30 minutes) with strong initial viewership</div>
              </div>
            </div>
            <div class="spotlight-item">
              <span class="spotlight-icon-large">🎯</span>
              <div class="spotlight-details">
                <div class="spotlight-name">MARATHON</div>
                <div class="spotlight-description">Streaming for 4+ hours straight - impressive dedication!</div>
              </div>
            </div>
            <div class="spotlight-item">
              <span class="spotlight-icon-large">🚀</span>
              <div class="spotlight-details">
                <div class="spotlight-name">RISING STAR</div>
                <div class="spotlight-description">New models who are already performing exceptionally well</div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="spotlight-category">
          <div class="category-header priority-low">
            <h3>✨ Special Recognition</h3>
            <span class="priority-badge low">Notable Features</span>
          </div>
          <div class="spotlight-items">
            <div class="spotlight-item">
              <span class="spotlight-icon-large">✨</span>
              <div class="spotlight-details">
                <div class="spotlight-name">HD STREAM</div>
                <div class="spotlight-description">High-definition video quality with solid viewership</div>
              </div>
            </div>
            <div class="spotlight-item">
              <span class="spotlight-icon-large">🎪</span>
              <div class="spotlight-details">
                <div class="spotlight-name">INTERACTIVE</div>
                <div class="spotlight-description">Engaging room topics and active interaction with viewers</div>
              </div>
            </div>
            <div class="spotlight-item">
              <span class="spotlight-icon-large">🗣️</span>
              <div class="spotlight-details">
                <div class="spotlight-name">MULTILINGUAL</div>
                <div class="spotlight-description">Speaks 3+ languages - internationally accessible</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <p><strong>Pro Tip:</strong> Hover over any spotlight badge to see exactly what it means!</p>
      </div>
    </div>
  </div>
</div>

<script>
// Spotlight modal functionality
document.addEventListener('DOMContentLoaded', function() {
  const helpBtn = document.getElementById('spotlight-guide-btn');
  const modal = document.getElementById('spotlight-modal');
  const closeBtn = document.getElementById('close-spotlight-modal');
  
  if (helpBtn) {
    helpBtn.addEventListener('click', function() {
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    });
  }
  
  if (closeBtn) {
    closeBtn.addEventListener('click', function() {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
  }
  
  // Close modal when clicking outside
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  });
  
  // Close modal with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.style.display === 'flex') {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  });
});
</script>

<?php include('templates/footer.php'); ?>