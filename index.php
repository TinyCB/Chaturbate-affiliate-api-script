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
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.4; }
  100% { opacity: 1; }
}
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
    <div class="filter-label">Age Range</div>
    <div class="filter-ages-enhanced">
      <div class="age-display" style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #374151; font-weight: 500;">
        <span>Min: <strong id="min-age-display" style="color: var(--primary-color);">18</strong></span>
        <span>Max: <strong id="max-age-display" style="color: var(--primary-color);">99</strong></span>
      </div>
      <div class="dual-range-slider" style="position: relative; height: 20px; background: #e2e8f0; border-radius: 10px; margin: 12px 0;">
        <input type="range" min="18" max="99" value="18" id="min-age-slider" style="position: absolute; width: 100%; height: 20px; background: transparent; -webkit-appearance: none; -moz-appearance: none; appearance: none; cursor: pointer; z-index: 1;">
        <input type="range" min="18" max="99" value="99" id="max-age-slider" style="position: absolute; width: 100%; height: 20px; background: transparent; -webkit-appearance: none; -moz-appearance: none; appearance: none; cursor: pointer; z-index: 2;">
        <div class="slider-track" style="position: absolute; height: 6px; top: 7px; background: var(--primary-color); border-radius: 3px; z-index: 0;"></div>
      </div>
      <div class="age-inputs-fallback" style="display: flex; gap: 8px; align-items: center; margin-top: 8px; font-size: 12px;">
        <input type="number" min="18" max="99" id="min-age" value="18" style="width: 50px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
        <span style="color: #666;">to</span>
        <input type="number" min="18" max="99" id="max-age" value="99" style="width: 50px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
      </div>
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
  
  <!-- Enhanced Discovery Section -->
  <div class="discovery-controls" style="display: flex; justify-content: space-between; align-items: center; margin: 16px 8px; padding: 12px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <div class="sort-controls" style="display: none;">
    </div>
    <div class="view-controls">
      <button id="show-stats" style="padding: 8px 16px; background: var(--primary-color); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">🔍 Discovery Hub</button>
    </div>
  </div>

  <!-- Discovery Highlights -->
  <div id="discovery-highlights" style="margin: 16px 8px; display: none; width: calc(100% - 16px); max-width: calc(100% - 16px); box-sizing: border-box; overflow-x: hidden;">
    <!-- Stats Dashboard Header -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <h2 style="margin: 0; font-size: 24px; font-weight: 700;">🔍 Discovery Hub</h2>
        <span id="auto-refresh-indicator" style="font-size: 11px; opacity: 0.7; display: flex; align-items: center; gap: 4px;">
          <span style="display: inline-block; width: 6px; height: 6px; background: #4caf50; border-radius: 50%; animation: pulse 2s infinite;"></span>
          Auto-updating
        </span>
      </div>
      <p style="margin: 0; opacity: 0.9; font-size: 14px;">Discover amazing models you might have missed</p>
    </div>

    <!-- Key Metrics Overview -->
    <div id="stats-overview" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
      <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
        <div style="font-size: 28px; font-weight: 700; color: #2196F3; margin-bottom: 8px;" id="total-models">0</div>
        <div style="color: #666; font-weight: 500;">Total Models Online</div>
      </div>
      <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
        <div style="font-size: 28px; font-weight: 700; color: #4CAF50; margin-bottom: 8px;" id="total-viewers">0</div>
        <div style="color: #666; font-weight: 500;">Total Viewers</div>
      </div>
      <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
        <div style="font-size: 28px; font-weight: 700; color: #FF9800; margin-bottom: 8px;" id="avg-viewers">0</div>
        <div style="color: #666; font-weight: 500;">Avg Viewers/Model</div>
      </div>
      <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
        <div style="font-size: 28px; font-weight: 700; color: #9C27B0; margin-bottom: 8px;" id="hd-percentage">0%</div>
        <div style="color: #666; font-weight: 500;">HD Streams</div>
      </div>
    </div>

    <!-- Performance Insights -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 24px;">
      <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 16px 0; color: #333; font-size: 18px;">🏆 Top Performers</h3>
        <div id="top-performers" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto;">
          <!-- Top performers will be populated here -->
        </div>
      </div>
      
      <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 16px 0; color: #ff6b6b; font-size: 18px;">🎯 Almost There</h3>
        <div id="almost-there-widget" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto;">
          <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
            <div style="width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #ccc; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 8px;"></div>
            Loading models...
          </div>
        </div>
      </div>
      
      <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 16px 0; color: #54a0ff; font-size: 18px;">🔥 Close to Goal</h3>
        <div id="close-to-goal-widget" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto;">
          <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
            <div style="width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #ccc; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 8px;"></div>
            Loading models...
          </div>
        </div>
      </div>
      
      <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 16px 0; color: #5f27cd; font-size: 18px;">💎 Big Goals</h3>
        <div id="big-goals-widget" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto;">
          <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
            <div style="width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #ccc; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 8px;"></div>
            Loading models...
          </div>
        </div>
      </div>
      
      <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 16px 0; color: #4ecdc4; font-size: 18px;">🌟 New Goals</h3>
        <div id="new-goals-widget" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto;">
          <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
            <div style="width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #ccc; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 8px;"></div>
            Loading models...
          </div>
        </div>
      </div>
      
      <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 16px 0; color: #ff9ff3; font-size: 18px;">⚡ Fast Progress</h3>
        <div id="fast-progress-widget" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto;">
          <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
            <div style="width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #ccc; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 8px;"></div>
            Loading models...
          </div>
        </div>
      </div>
      
      <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 16px 0; color: #10b981; font-size: 18px;">🏆 Goal Completed</h3>
        <div id="goal-completed-widget" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto;">
          <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
            <div style="width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #ccc; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 8px;"></div>
            Loading models...
          </div>
        </div>
      </div>
      
      <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 16px 0; color: #333; font-size: 18px;">📈 Activity Trends</h3>
        <div id="activity-trends">
          <!-- Activity trends will be populated here -->
        </div>
      </div>
    </div>

    <!-- Category Filters -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px;">
      <h3 style="margin: 0 0 8px 0; color: #333; font-size: 18px;">🎯 Quick Discovery Filters</h3>
      <p style="margin: 0 0 16px 0; color: #666; font-size: 13px;">Click any category to filter discovery sections</p>
      <div id="category-breakdown" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 16px;">
        <!-- Category breakdown will be populated here -->
      </div>
    </div>

    <!-- Age Filters Section -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px;">
      <h3 style="margin: 0 0 8px 0; color: #333; font-size: 18px;">🎂 Filter by Age</h3>
      <p style="margin: 0 0 16px 0; color: #666; font-size: 13px;">Click an age range to filter discovery sections</p>
      <div id="age-filter-breakdown" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 16px;">
        <!-- Age filters will be populated here -->
      </div>
    </div>

    <!-- Popular Tags Section -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; color: #333; font-size: 18px;">🏷️ Popular Tags</h3>
        <span id="tags-status" style="font-size: 11px; color: #666;">Live updating...</span>
      </div>
      <p style="margin: 0 0 16px 0; color: #666; font-size: 13px;">Click any tag to discover models with similar interests</p>
      <div id="popular-tags-carousel" style="position: relative;">
        <!-- Navigation buttons -->
        <button class="tag-carousel-nav tag-carousel-prev" 
                onclick="scrollTagCarousel('prev')"
                style="position: absolute; 
                       left: -12px; 
                       top: 50%; 
                       transform: translateY(-50%); 
                       z-index: 10; 
                       background: rgba(255, 255, 255, 0.9); 
                       border: 1px solid #ddd; 
                       border-radius: 50%; 
                       width: 32px; 
                       height: 32px; 
                       cursor: pointer; 
                       font-size: 12px; 
                       display: none;
                       align-items: center; 
                       justify-content: center; 
                       box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                       transition: all 0.3s ease;"
                onmouseover="this.style.background='rgba(102, 126, 234, 0.9)'; this.style.color='white';"
                onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.color='black';">
          ◀
        </button>
        
        <div class="tag-carousel-container" style="overflow: hidden; border-radius: 8px;">
          <div class="tag-carousel-track" id="popular-tags" style="display: flex; transition: transform 0.3s ease; gap: 8px;">
            <!-- Popular tags will be populated here -->
          </div>
        </div>
        
        <button class="tag-carousel-nav tag-carousel-next" 
                onclick="scrollTagCarousel('next')"
                style="position: absolute; 
                       right: -12px; 
                       top: 50%; 
                       transform: translateY(-50%); 
                       z-index: 10; 
                       background: rgba(255, 255, 255, 0.9); 
                       border: 1px solid #ddd; 
                       border-radius: 50%; 
                       width: 32px; 
                       height: 32px; 
                       cursor: pointer; 
                       font-size: 12px; 
                       display: none;
                       align-items: center; 
                       justify-content: center; 
                       box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                       transition: all 0.3s ease;"
                onmouseover="this.style.background='rgba(102, 126, 234, 0.9)'; this.style.color='white';"
                onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.color='black';">
          ▶
        </button>
      </div>
    </div>

    <!-- Discovery Sections -->
    <div class="discovery-sections" style="display: grid; gap: 16px; margin-bottom: 20px; width: 100%; max-width: 100%; overflow-x: hidden;">
      <div class="discovery-section" id="trending-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #ff4757; font-size: 16px;">🔥 Trending Now</h3>
          <span id="trending-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="trending-grid"></div>
      </div>
      
      <div class="discovery-section" id="new-models-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #2ed573; font-size: 16px;">🌟 New Models</h3>
          <span id="new-models-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="new-models-grid"></div>
      </div>
      
      <div class="discovery-section" id="just-live-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #00d4ff; font-size: 16px;">⚡ Just Went Live</h3>
          <span id="just-live-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="just-live-grid"></div>
      </div>
      
      <div class="discovery-section" id="hidden-gems-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #9b59b6; font-size: 16px;">💎 Hidden Gems</h3>
          <span id="hidden-gems-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="hidden-gems-grid"></div>
      </div>
      
      <div class="discovery-section" id="high-energy-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #ff6b35; font-size: 16px;">⚡ High Energy</h3>
          <span id="high-energy-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="high-energy-grid"></div>
      </div>
      
      <div class="discovery-section" id="marathon-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #e74c3c; font-size: 16px;">🎯 Marathon Streamers (5+ hours)</h3>
          <span id="marathon-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="marathon-grid"></div>
      </div>
      
      <div class="discovery-section" id="international-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #6c5ce7; font-size: 16px;">🌍 International Models</h3>
          <span id="international-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="international-grid"></div>
      </div>
      
      <div class="discovery-section" id="interactive-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #e84393; font-size: 16px;">🎪 Interactive Shows</h3>
          <span id="interactive-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="interactive-grid"></div>
      </div>
      
      <div class="discovery-section" id="couples-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #fd79a8; font-size: 16px;">👫 Couples & Groups</h3>
          <span id="couples-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="couples-grid"></div>
      </div>
      
      <div class="discovery-section" id="mature-section" style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="margin: 0; color: #a29bfe; font-size: 16px;">🍷 Mature & Experienced</h3>
          <span id="mature-status" style="font-size: 11px; color: #666;">Live updating...</span>
        </div>
        <div class="discovery-grid" id="mature-grid"></div>
      </div>

    </div>
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
  
  if (!sidebar) return;
  
  if (sidebar.classList.contains('open')) {
    sidebar.classList.remove('open');
  } else {
    sidebar.classList.add('open');
  }
}

document.addEventListener('DOMContentLoaded', function() {
    
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
        var isMobile = window.innerWidth <= 768;
        
        if (sidebar && isMobile) {
            // Close sidebar on mobile when switching from desktop
            sidebar.classList.remove('open');
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
  
  const justLiveThreshold = Math.max(10, globalStats.avgViewers * 0.6);
  if (secondsOnline <= 1800 && viewerCount >= justLiveThreshold) {
    spotlights.push({ type: 'just-live', label: 'JUST LIVE', icon: '⚡', priority: 8 });
    if (debugThis) console.log(`✓ JUST LIVE: ${secondsOnline}s <= 1800 && ${viewerCount} >= ${justLiveThreshold.toFixed(1)}`);
  } else if (debugThis) console.log(`✗ just-live: ${secondsOnline}s > 1800 || ${viewerCount} < ${justLiveThreshold.toFixed(1)}`);
  
  if (hoursOnline >= 5) {
    spotlights.push({ type: 'marathon', label: 'MARATHON', icon: '🎯', priority: 7 });
    if (debugThis) console.log(`✓ MARATHON: ${hoursOnline.toFixed(1)}h >= 5`);
  } else if (debugThis) console.log(`✗ marathon: ${hoursOnline.toFixed(1)}h < 5`);
  
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
      
      // Check if we're in discovery mode
      const discoveryHighlights = document.getElementById('discovery-highlights');
      if (discoveryHighlights && discoveryHighlights.style.display !== 'none') {
        // In discovery mode - use discovery tag filtering
        filterDiscoveryByTag(tag);
      } else {
        // In normal mode - use regular filtering
        if(!FILTERS.tag.includes(tag)) {
          if(FILTERS.tag.length>=5) FILTERS.tag.shift();
          FILTERS.tag.push(tag);
          onFilterChange();
        }
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
          
          // Check if we're in discovery mode
          const discoveryHighlights = document.getElementById('discovery-highlights');
          if (discoveryHighlights && discoveryHighlights.style.display !== 'none') {
            // In discovery mode - use discovery tag filtering
            filterDiscoveryByTag(tag);
          } else {
            // In normal mode - use regular filtering
            if (!FILTERS.tag.includes(tag)) {
              if (FILTERS.tag.length >= 5) FILTERS.tag.shift();
              FILTERS.tag.push(tag);
              onFilterChange();
            }
          }
        });
      });
    });
}
// Enhanced Discovery Features
function sortModels(models, sortBy) {
  const sorted = [...models];
  
  switch(sortBy) {
    case 'viewers':
      return sorted.sort((a, b) => (b.num_users || 0) - (a.num_users || 0));
      
    case 'newest':
      return sorted.filter(m => (m.seconds_online || 0) <= 1800).sort((a, b) => (a.seconds_online || 0) - (b.seconds_online || 0));
      
    case 'marathon':
      return sorted.filter(m => (m.seconds_online || 0) >= 18000).sort((a, b) => (b.seconds_online || 0) - (a.seconds_online || 0));
      
    case 'engagement':
      return sorted.sort((a, b) => {
        const ratioA = (a.num_users || 0) / Math.max(1, a.num_followers || 1);
        const ratioB = (b.num_users || 0) / Math.max(1, b.num_followers || 1);
        return ratioB - ratioA;
      });
      
    case 'hidden-gems':
      return sorted.filter(m => {
        const followers = m.num_followers || 0;
        const viewers = m.num_users || 0;
        return followers > 10000 && viewers < (followers * 0.01);
      }).sort((a, b) => (b.num_followers || 0) - (a.num_followers || 0));
      
    case 'hd-first':
      return sorted.sort((a, b) => {
        if (a.is_hd && !b.is_hd) return -1;
        if (!a.is_hd && b.is_hd) return 1;
        return (b.num_users || 0) - (a.num_users || 0);
      });
      
    case 'new-models':
      return sorted.filter(m => m.is_new).sort((a, b) => (b.num_users || 0) - (a.num_users || 0));
      
    case 'favorites':
      const favorites = window.getFavorites ? window.getFavorites() : [];
      return sorted.filter(m => favorites.includes(m.username.toLowerCase())).sort((a, b) => (b.num_users || 0) - (a.num_users || 0));
      
    default:
      return sorted;
  }
}

function renderCarouselItems(models, globalStats) {
  return models.map(m => {
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
      <div class="carousel-item model-card-cb ${spotlightElements.cardClass}" style="flex: 0 0 170px; min-width: 170px; max-width: 170px;">
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
}

function renderDiscoverySection(models, sectionId, limit = 24) {
  const container = document.getElementById(sectionId);
  if (!container) return;
  
  if (models.length === 0) {
    container.innerHTML = '<p style="color: #666; text-align: center; padding: 20px;">No models found for this category.</p>';
    return;
  }
  
  // Store all models for carousel
  if (!window.discoveryReelData) {
    window.discoveryReelData = {};
  }
  // Calculate how many items can fit in the viewport (for display purposes)
  const viewportWidth = window.innerWidth;
  const availableWidth = Math.max(300, viewportWidth - 100); // Simple calculation with buffer
  const itemWidth = 170; // Standard model card width
  const gap = 12; // Standard gap
  const itemsPerView = Math.max(1, Math.floor(availableWidth / (itemWidth + gap)));
  
  window.discoveryReelData[sectionId] = {
    allModels: models,
    currentIndex: 0,
    itemsPerView: itemsPerView // Use calculated itemsPerView directly
  };
  
  // Calculate global stats for spotlight detection (same as main page)
  const globalStats = {
    avgViewers: models.reduce((sum, m) => sum + parseInt(m.num_users || 0), 0) / models.length,
    maxViewers: Math.max(...models.map(m => parseInt(m.num_users || 0))),
    avgOnlineTime: models.reduce((sum, m) => sum + parseInt(m.seconds_online || 0), 0) / models.length,
    maxOnlineTime: Math.max(...models.map(m => parseInt(m.seconds_online || 0)))
  };

  // Create carousel structure
  const reelData = window.discoveryReelData[sectionId];
  const totalModels = models.length;
  const canScrollLeft = reelData.currentIndex > 0;
  const canScrollRight = reelData.currentIndex + reelData.itemsPerView < totalModels;
  
  // Calculate exact carousel width to prevent overflow
  const maxCarouselWidth = (itemWidth * itemsPerView) + (gap * (itemsPerView - 1));
  
  container.innerHTML = `
    <div class="discovery-carousel" style="position: relative; width: 100%; max-width: 100%; box-sizing: border-box; padding: 0 20px;">
      <!-- Navigation buttons positioned inside padded container -->
      <button class="carousel-nav carousel-prev" 
              onclick="scrollDiscoveryCarousel('${sectionId}', 'prev')"
              style="position: absolute; 
                     left: 0; 
                     top: 50%; 
                     transform: translateY(-50%); 
                     z-index: 10; 
                     background: rgba(255, 255, 255, 0.9); 
                     border: 1px solid #ddd; 
                     border-radius: 50%; 
                     width: 32px; 
                     height: 32px; 
                     cursor: pointer; 
                     font-size: 12px; 
                     display: ${canScrollLeft ? 'flex' : 'none'};
                     align-items: center; 
                     justify-content: center; 
                     box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                     transition: all 0.3s ease;"
              onmouseover="this.style.background='rgba(255, 71, 87, 0.9)'; this.style.color='white';"
              onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.color='black';">
        ◀
      </button>
      
      <button class="carousel-nav carousel-next" 
              onclick="scrollDiscoveryCarousel('${sectionId}', 'next')"
              style="position: absolute; 
                     right: 0; 
                     top: 50%; 
                     transform: translateY(-50%); 
                     z-index: 10; 
                     background: rgba(255, 255, 255, 0.9); 
                     border: 1px solid #ddd; 
                     border-radius: 50%; 
                     width: 32px; 
                     height: 32px; 
                     cursor: pointer; 
                     font-size: 12px; 
                     display: ${canScrollRight ? 'flex' : 'none'};
                     align-items: center; 
                     justify-content: center; 
                     box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                     transition: all 0.3s ease;"
              onmouseover="this.style.background='rgba(255, 71, 87, 0.9)'; this.style.color='white';"
              onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.color='black';">
        ▶
      </button>
      
      
      <div class="carousel-container" style="overflow: hidden; border-radius: 8px; width: 100%; box-sizing: border-box;">
        <div class="carousel-track" style="display: flex; transition: transform 0.3s ease; transform: translateX(0px); gap: 12px;">
          ${renderCarouselItems(models, globalStats)}
        </div>
      </div>
    </div>
  `;
  
  // Add tag event listeners for the newly rendered tags
  container.querySelectorAll('.tag-cb.subject-tag').forEach(el => {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      let tag = el.dataset.tag;
      
      // Check if we're in discovery mode
      const discoveryHighlights = document.getElementById('discovery-highlights');
      if (discoveryHighlights && discoveryHighlights.style.display !== 'none') {
        // In discovery mode - use discovery tag filtering
        filterDiscoveryByTag(tag);
      } else {
        // In normal mode - use regular filtering
        if (!FILTERS.tag.includes(tag)) {
          if (FILTERS.tag.length >= 5) FILTERS.tag.shift();
          FILTERS.tag.push(tag);
          onFilterChange();
        }
      }
    });
  });
}

// No caching for analytics - always fetch fresh data for live goal updates

function showDiscoveryHighlights() {
  const highlights = document.getElementById('discovery-highlights');
  const showBtn = document.getElementById('show-stats');
  
  if (highlights.style.display === 'none') {
    highlights.style.display = 'block';
    showBtn.textContent = '🔍 Hide Discovery';
    
    // Store discovery state in sessionStorage
    sessionStorage.setItem('discoveryHubActive', 'true');
    
    // Start auto-refresh for live updates
    startDiscoveryAutoRefresh();
    
    // Hide main model grid, sidebar filters, and header elements when in discovery mode
    document.getElementById('model-grid').style.display = 'none';
    document.getElementById('pagination-bar').style.display = 'none';
    document.getElementById('filter-sidebar').style.display = 'none';
    
    // Hide header filter elements
    const resetFiltersLink = document.getElementById('reset-filters-link');
    if (resetFiltersLink) resetFiltersLink.style.display = 'none';
    
    const autoRefreshBar = document.getElementById('auto-refresh-bar');
    if (autoRefreshBar) autoRefreshBar.style.display = 'none';
    
    const filterToggleBtn = document.getElementById('filter-toggle');
    if (filterToggleBtn) filterToggleBtn.style.display = 'none';
    
    // Load ALL cached data for accurate stats (not just filtered models)
    loadAllCachedDataForStats().then(allCachedModels => {
      if (allCachedModels.length > 0) {
        // Calculate and populate stats overview using ALL cached data
        populateStatsOverview(allCachedModels);
        
        // Populate performance insights using ALL cached data
        populateTopPerformers(allCachedModels);
        populateGoalWidgets(allCachedModels);
        populateActivityTrends(allCachedModels);
        
        // Populate category breakdown using ALL cached data
        populateCategoryBreakdown(allCachedModels);
        
        // For discovery sections, use a curated subset of interesting models
        window.cachedModelsForDiscovery = allCachedModels; // Store for refresh functionality
        
        // Populate age filters
        populateAgeFilters(allCachedModels);
        
        // Populate popular tags
        populatePopularTags(allCachedModels);
        
        populateDiscoverySections(allCachedModels);
      }
    }).catch(error => {
      console.error('Error loading stats data:', error);
      // Fallback to existing filtered models if API call fails
      if (allModels.length > 0) {
        populateStatsOverview(allModels);
        populateTopPerformers(allModels);
        populateGoalWidgets(allModels);
        populateActivityTrends(allModels);
        populateCategoryBreakdown(allModels);
        
        window.cachedModelsForDiscovery = allModels; // Store for refresh functionality
        populateAgeFilters(allModels);
        populatePopularTags(allModels);
        populateDiscoverySections(allModels);
      }
    });
  } else {
    highlights.style.display = 'none';
    showBtn.textContent = '🔍 Discovery Hub';
    
    // Remove discovery state from sessionStorage
    sessionStorage.removeItem('discoveryHubActive');
    
    // Stop auto-refresh when hiding discovery hub
    stopDiscoveryAutoRefresh();
    
    // Show main model grid, sidebar filters, and header elements when discovery is hidden
    document.getElementById('model-grid').style.display = 'grid';
    document.getElementById('pagination-bar').style.display = 'flex';
    document.getElementById('filter-sidebar').style.display = 'block';
    
    // Show header filter elements
    const resetFiltersLink = document.getElementById('reset-filters-link');
    if (resetFiltersLink && (FILTERS.gender.length || FILTERS.tag.length || FILTERS.minAge !== 18 || FILTERS.maxAge !== 99 || FILTERS.hd || FILTERS.size || FILTERS.current_show.length || FILTERS.is_new)) {
      resetFiltersLink.style.display = 'inline';
    }
    
    const autoRefreshBar = document.getElementById('auto-refresh-bar');
    if (autoRefreshBar && isDesktop()) autoRefreshBar.style.display = 'inline';
    
    const filterToggleBtn = document.getElementById('filter-toggle');
    if (filterToggleBtn) filterToggleBtn.style.display = 'inline';
  }
}

async function loadAllCachedDataForStats() {
  const allRegions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
  
  // Load all regions without any filters
  const response = await fetch(`${API}?region=${allRegions.join(',')}&limit=10000`);
  const data = await response.json();
  
  return data.results || [];
}

function populateStatsOverview(models) {
  const totalModels = models.length;
  const totalViewers = models.reduce((sum, m) => sum + (parseInt(m.num_users) || 0), 0);
  const avgViewers = totalModels > 0 ? Math.round(totalViewers / totalModels) : 0;
  const hdModels = models.filter(m => m.is_hd).length;
  const hdPercentage = totalModels > 0 ? Math.round((hdModels / totalModels) * 100) : 0;
  
  document.getElementById('total-models').textContent = totalModels.toLocaleString();
  document.getElementById('total-viewers').textContent = totalViewers.toLocaleString();
  document.getElementById('avg-viewers').textContent = avgViewers.toLocaleString();
  document.getElementById('hd-percentage').textContent = hdPercentage + '%';
}

function populateTopPerformers(models) {
  const topModels = [...models]
    .sort((a, b) => (parseInt(b.num_users) || 0) - (parseInt(a.num_users) || 0))
    .slice(0, 10);
  
  const totalViewers = topModels.reduce((sum, model) => sum + (parseInt(model.num_users) || 0), 0);
  const avgViewers = totalViewers / Math.max(topModels.length, 1);
  
  const container = document.getElementById('top-performers');
  container.innerHTML = `
    <div style="margin-bottom: 12px; padding: 8px; background: #f8f9fa; border-radius: 6px; text-align: center;">
      <div style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 4px;">Top 10 Summary</div>
      <div style="font-size: 12px; color: #666;">Total: ${totalViewers.toLocaleString()} viewers | Avg: ${Math.round(avgViewers).toLocaleString()}</div>
    </div>
    ${topModels.map((model, index) => `
      <div style="display: flex; align-items: center; gap: 8px; padding: 6px; border-radius: 6px; background: ${index < 3 ? (index === 0 ? '#fff3cd' : index === 1 ? '#e8f5e8' : '#f0e8ff') : '#f8f9fa'}; cursor: pointer; transition: transform 0.2s ease;" 
           onclick="window.open('/model/${encodeURIComponent(model.username)}', '_blank');" 
           onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" 
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
        <span style="font-weight: 700; color: ${index < 3 ? (index === 0 ? '#b8860b' : index === 1 ? '#4CAF50' : '#9C27B0') : '#666'}; font-size: 12px; min-width: 18px;">#${index + 1}</span>
        <img src="${model.image_url}" alt="${model.username}" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 600; font-size: 12px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${model.username}</div>
          <div style="font-size: 11px; color: #666;">${(parseInt(model.num_users) || 0).toLocaleString()}</div>
        </div>
        ${model.is_hd ? '<span style="background: #4CAF50; color: white; padding: 1px 4px; border-radius: 3px; font-size: 9px;">HD</span>' : ''}
      </div>
    `).join('')}
  `;
}

function populateGoalWidgets(models) {
  console.log('populateGoalWidgets called with', models.length, 'models');
  
  // Get models with analytics data
  const enrichedModels = models.filter(m => m._analytics?.actual_goals?.current_goal);
  console.log('Found', enrichedModels.length, 'models with goal data');
  
  // Debug: Check what goal data looks like
  if (enrichedModels.length > 0) {
    const sampleGoal = enrichedModels[0]._analytics?.actual_goals?.current_goal;
    console.log('Sample goal data structure:', sampleGoal);
    console.log('Available goal fields:', Object.keys(sampleGoal || {}));
  }
  
  // Calculate goal categories
  const almostThere = enrichedModels.filter(m => {
    const goal = m._analytics?.actual_goals?.current_goal;
    if (!goal || !goal.tokens_remaining || goal.tokens_remaining <= 0) return false;
    const initialTokens = goal.initial_tokens || 0;
    if (initialTokens <= 0) return false;
    const progress = ((initialTokens - goal.tokens_remaining) / initialTokens) * 100;
    return progress > 80 && (m.num_users || 0) > 3;
  }).slice(0, 10);
  
  const closeToGoal = enrichedModels.filter(m => {
    const goal = m._analytics?.actual_goals?.current_goal;
    if (!goal || !goal.tokens_remaining || goal.tokens_remaining <= 0) return false;
    const initialTokens = goal.initial_tokens || 0;
    if (initialTokens <= 0) return false;
    const progress = ((initialTokens - goal.tokens_remaining) / initialTokens) * 100;
    return progress > 50 && progress <= 80 && (m.num_users || 0) > 5;
  }).slice(0, 10);
  
  const bigGoals = enrichedModels.filter(m => {
    const goal = m._analytics?.actual_goals?.current_goal;
    return goal && (goal.initial_tokens || 0) > 1000 && (m.num_users || 0) > 10;
  }).slice(0, 10);
  
  const newGoals = enrichedModels.filter(m => {
    const goal = m._analytics?.actual_goals?.current_goal;
    if (!goal || !goal.tokens_remaining || goal.tokens_remaining <= 0) return false;
    const initialTokens = goal.initial_tokens || 0;
    if (initialTokens <= 0) return false;
    const progress = ((initialTokens - goal.tokens_remaining) / initialTokens) * 100;
    return progress < 10 && (m.num_users || 0) > 3;
  }).slice(0, 10);
  
  const fastProgress = enrichedModels.filter(m => {
    const goal = m._analytics?.actual_goals?.current_goal;
    const velocity = goal?.velocity || goal?.token_velocity || 0;
    const viewers = (m.num_users || 0);
    
    return goal && velocity > 5 && viewers > 5;
  }).slice(0, 10);
  
  // Goal Completed - models that recently finished goals and are doing shows
  // Filter models with recent goal completions
  const filteredModels = models.filter(m => {
    const analytics = m._analytics?.actual_goals;
    if (!analytics) return false;
    
    // Debug logging for goal completed detection
    console.log(`Checking ${m.username} for goal completion:`, {
      hasCompletedGoals: !!(analytics.completed_goals && analytics.completed_goals.length > 0),
      completedGoalsLength: analytics.completed_goals?.length || 0,
      hasCurrentGoal: !!analytics.current_goal,
      viewers: m.num_users || 0,
      completedGoals: analytics.completed_goals
    });
    
    // Check if they have recent completed goals - removed all other criteria for debugging
    const hasRecentCompletion = analytics.completed_goals && analytics.completed_goals.length > 0;
    
    // For debugging - only check 5-minute window, no other criteria
    if (hasRecentCompletion) {
      // Check if the most recent goal was completed recently (within last 5 minutes)
      const recentGoals = analytics.completed_goals.filter(goal => {
        if (goal.completed_at) {
          const completedTime = goal.completed_at * 1000; // Convert to milliseconds
          const fiveMinutesAgo = Date.now() - (5 * 60 * 1000); // 5 minutes ago
          const isRecent = completedTime > fiveMinutesAgo;
          
          // Debug logging
          const minutesAgo = (Date.now() - completedTime) / 1000 / 60;
          console.log(`${m.username} goal completed ${minutesAgo.toFixed(1)} minutes ago - Recent: ${isRecent}`);
          
          return isRecent;
        }
        return false;
      });
      
      console.log(`${m.username} recent goals:`, recentGoals.length);
      return recentGoals.length > 0;
    }
    
    return false;
  });
  
  // Deduplicate by username - keep only the latest goal completion for each model
  const uniqueModelsMap = filteredModels.reduce((uniqueModels, model) => {
    const username = model.username;
    const analytics = model._analytics?.actual_goals;
    
    if (analytics?.completed_goals) {
      // Get the most recent completed goal for this model
      const mostRecentGoal = analytics.completed_goals
        .filter(g => g.completed_at)
        .sort((a, b) => (b.completed_at || 0) - (a.completed_at || 0))[0];
      
      // Check if we already have this model
      const existingModel = uniqueModels.get(username);
      
      if (!existingModel || !existingModel._mostRecentGoalTime || 
          (mostRecentGoal?.completed_at || 0) > existingModel._mostRecentGoalTime) {
        // Add timestamp for comparison and keep this model
        model._mostRecentGoalTime = mostRecentGoal?.completed_at || 0;
        uniqueModels.set(username, model);
        console.log(`Updated ${username} with goal completed at ${mostRecentGoal?.completed_at}`);
      } else {
        console.log(`Skipped duplicate ${username} - existing goal is more recent`);
      }
    }
    
    return uniqueModels;
  }, new Map());
  
  // Convert Map back to array and sort
  const goalCompleted = Array.from(uniqueModelsMap.values()).sort((a, b) => {
    // Sort by performance metrics - highest performing goal completers first
    const aViewers = a.num_users || 0;
    const bViewers = b.num_users || 0;
    const aHD = a.is_hd ? 1000 : 0; // HD bonus
    const bHD = b.is_hd ? 1000 : 0;
    const aOnlineTime = (a.seconds_online || 0) / 3600; // Hours online
    const bOnlineTime = (b.seconds_online || 0) / 3600;
    
    // Calculate performance score: viewers + HD bonus + time bonus
    const aScore = aViewers + aHD + (aOnlineTime * 50);
    const bScore = bViewers + bHD + (bOnlineTime * 50);
    
    return bScore - aScore; // Descending order
  }).slice(0, 5); // Only show top 5 goal completers
  
  console.log('Goal completed models found:', goalCompleted.length);
  
  // Populate individual widgets
  populateGoalWidget('almost-there-widget', almostThere, 'Almost There', '#ff6b6b');
  populateGoalWidget('close-to-goal-widget', closeToGoal, 'Close to Goal', '#54a0ff');
  populateGoalWidget('big-goals-widget', bigGoals, 'Big Goals', '#5f27cd');
  populateGoalWidget('new-goals-widget', newGoals, 'New Goals', '#4ecdc4');
  populateGoalWidget('fast-progress-widget', fastProgress, 'Fast Progress', '#ff9ff3');
  populateGoalWidget('goal-completed-widget', goalCompleted, 'Goal Completed', '#10b981');
}

function getTimeAgo(date) {
  const now = Date.now();
  const time = date.getTime();
  const diff = now - time;
  
  const minutes = Math.floor(diff / (1000 * 60));
  const hours = Math.floor(diff / (1000 * 60 * 60));
  
  if (minutes < 60) {
    return `${minutes}m ago`;
  } else if (hours < 24) {
    return `${hours}h ago`;
  } else {
    return `${Math.floor(hours / 24)}d ago`;
  }
}

function createGoalCompletedCard(model, recentGoal, timeAgo, color) {
  const age = model.age || '?';
  const genderInfo = {
    'f': { icon: 'F', color: '#e91e63', label: 'Female' },
    'm': { icon: 'M', color: '#2196f3', label: 'Male' },
    't': { icon: 'T', color: '#9c27b0', label: 'Trans' },
    'c': { icon: 'C', color: '#ff5722', label: 'Couple' }
  };
  const gender = genderInfo[model.gender] || { icon: '?', color: '#9e9e9e', label: 'Unknown' };
  
  let goalText = 'Show completed';
  if (recentGoal?.goal_text) {
    goalText = recentGoal.goal_text.length > 30 ? 
      recentGoal.goal_text.substring(0, 27) + '...' : 
      recentGoal.goal_text;
  }
  
  return `
    <div style="display: flex; align-items: flex-start; gap: 10px; padding: 10px; border-radius: 8px; background: linear-gradient(135deg, #f0fdf4, #ecfdf5); cursor: pointer; transition: all 0.2s ease; margin-bottom: 6px; border: 1px solid #10b981; box-shadow: 0 1px 3px rgba(16, 185, 129, 0.15);" 
         onclick="window.open('/model/${encodeURIComponent(model.username)}', '_blank');" 
         onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.25)'; this.style.borderColor='#059669';" 
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(16, 185, 129, 0.15)'; this.style.borderColor='#10b981';">
      <div style="position: relative;">
        <img src="${model.image_url}" alt="${model.username}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 0 0 2px #10b981;">
        <div style="position: absolute; bottom: -3px; right: -3px; background: ${gender.color}; color: white; width: 18px; height: 18px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); font-family: Arial, sans-serif;" title="${gender.label}">${gender.icon}</div>
        <div style="position: absolute; top: -3px; left: -3px; background: #10b981; color: white; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);" title="Goal Completed">✓</div>
      </div>
      <div style="flex: 1; min-width: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
          <div style="display: flex; flex-direction: column; min-width: 0; flex: 1;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <span style="font-weight: 600; color: #1f2937; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${model.username}</span>
              <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 600;">SHOW</span>
            </div>
            <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
              ${age} • ${model.num_users || 0} viewers
            </div>
          </div>
        </div>
        <div style="font-size: 11px; color: #059669; margin-bottom: 4px; font-style: italic;">
          "${goalText}" completed ${timeAgo}
        </div>
        <div style="font-size: 10px; color: #10b981; font-weight: 600;">
          🎉 Show in progress
        </div>
      </div>
    </div>
  `;
}

function populateGoalWidget(containerId, models, title, color) {
  const container = document.getElementById(containerId);
  if (!container) {
    console.error(containerId + ' container not found');
    return;
  }

  // Check if this is initial load (no analytics data processed yet)
  const hasAnalyticsData = window.cachedModelsForDiscovery && 
    window.cachedModelsForDiscovery.some(m => m._analytics);

  if (models.length === 0) {
    if (!hasAnalyticsData) {
      // Still loading analytics data
      container.innerHTML = `
        <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
          <div style="margin-bottom: 8px;">
            <div style="width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid ${color}; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 8px;"></div>
          </div>
          <div>Loading ${title.toLowerCase()}...</div>
          <div style="font-size: 11px; margin-top: 4px; color: #999;">Analyzing goal data</div>
        </div>
        <style>
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        </style>
      `;
    } else {
      // Analytics loaded but no models in this category
      container.innerHTML = `
        <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
          <div style="margin-bottom: 8px; font-size: 18px; opacity: 0.3;">💤</div>
          <div>No ${title.toLowerCase()} right now</div>
          <div style="font-size: 11px; margin-top: 4px; color: #999;">Check back later</div>
        </div>
      `;
    }
    return;
  }

  // Special handling for goal completed widget
  let summaryHtml;
  if (containerId === 'goal-completed-widget') {
    summaryHtml = `
      <div style="margin-bottom: 12px; padding: 8px; background: #f0fdf4; border: 1px solid #10b981; border-radius: 6px; text-align: center;">
        <div style="font-size: 14px; font-weight: 600; color: #10b981; margin-bottom: 4px;">🏆 ${title} (${models.length})</div>
        <div style="font-size: 12px; color: #059669;">Performers who completed goals</div>
      </div>
    `;
  } else {
    const totalTokens = models.reduce((sum, model) => {
      const goal = model._analytics?.actual_goals?.current_goal;
      return sum + (goal?.tokens_remaining || 0);
    }, 0);
    const avgTokens = Math.round(totalTokens / models.length);
    
    summaryHtml = `
      <div style="margin-bottom: 12px; padding: 8px; background: #f8f9fa; border-radius: 6px; text-align: center;">
        <div style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 4px;">${title} (${models.length})</div>
        <div style="font-size: 12px; color: #666;">Avg: ${avgTokens} tokens remaining</div>
      </div>
    `;
  }

  container.innerHTML = summaryHtml +
    models.map((model, index) => {
      // Handle goal completed models differently
      if (containerId === 'goal-completed-widget') {
        // For goal completed models, get the most recent completed goal
        const allCompletedGoals = model._analytics?.actual_goals?.completed_goals || [];
        const recentGoal = allCompletedGoals
          .filter(g => g.completed_at)
          .sort((a, b) => (b.completed_at || 0) - (a.completed_at || 0))[0];
        const completedTime = recentGoal?.completed_at ? new Date(recentGoal.completed_at * 1000) : null;
        const timeAgo = completedTime ? getTimeAgo(completedTime) : '';
        
        return createGoalCompletedCard(model, recentGoal, timeAgo, color);
      }
      
      // Regular goal widget handling
      const goal = model._analytics?.actual_goals?.current_goal;
      const initialTokens = goal?.initial_tokens || 0;
      const tokensRemaining = goal?.tokens_remaining || 0;
      const progress = initialTokens > 0 ? ((initialTokens - tokensRemaining) / initialTokens) * 100 : 0;
      const velocity = goal?.velocity || goal?.token_velocity || 0;
      
      const age = model.age || '?';
      const genderInfo = {
        'f': { icon: 'F', color: '#e91e63', label: 'Female' },
        'm': { icon: 'M', color: '#2196f3', label: 'Male' },
        't': { icon: 'T', color: '#9c27b0', label: 'Trans' },
        'c': { icon: 'C', color: '#ff5722', label: 'Couple' }
      };
      const gender = genderInfo[model.gender] || { icon: '?', color: '#9e9e9e', label: 'Unknown' };
      
      // Extract goal subject from raw_subject or room_subject
      let goalSubject = '';
      if (goal?.goal_text) {
        goalSubject = goal.goal_text;
      } else if (model.room_subject) {
        // Try to extract goal from room subject
        const goalMatch = model.room_subject.match(/goal[:\s]*([^#]+?)(?:\s*#|$)/i);
        if (goalMatch) {
          goalSubject = goalMatch[1].trim();
        } else {
          // Fallback to first part of room subject
          goalSubject = model.room_subject.split(/[#-]/)[0].trim();
        }
      }
      
      // Truncate goal subject if too long
      if (goalSubject.length > 35) {
        goalSubject = goalSubject.substring(0, 32) + '...';
      }
      
      return `
        <div style="display: flex; align-items: flex-start; gap: 10px; padding: 10px; border-radius: 8px; background: white; cursor: pointer; transition: all 0.2s ease; margin-bottom: 6px; border: 1px solid #e8e8e8; box-shadow: 0 1px 3px rgba(0,0,0,0.08);" 
             onclick="window.open('/model/${encodeURIComponent(model.username)}', '_blank');" 
             onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'; this.style.borderColor='${color}';" 
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.08)'; this.style.borderColor='#e8e8e8';">
          <div style="position: relative;">
            <img src="${model.image_url}" alt="${model.username}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 0 0 2px ${color};">
            <div style="position: absolute; bottom: -3px; right: -3px; background: ${gender.color}; color: white; width: 18px; height: 18px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); font-family: Arial, sans-serif;" title="${gender.label}">${gender.icon}</div>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
              <div style="display: flex; flex-direction: column; min-width: 0; flex: 1;">
                <div style="display: flex; align-items: center; gap: 6px;">
                  <div style="font-weight: 700; font-size: 13px; color: #2c3e50; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${model.username}</div>
                  <span style="font-size: 10px; color: #7f8c8d; background: #ecf0f1; padding: 2px 6px; border-radius: 10px; font-weight: 600;">${age}</span>
                </div>
                ${goalSubject ? `<div style="font-size: 10px; color: #34495e; line-height: 1.3; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-style: italic;" title="${goalSubject}">"${goalSubject}"</div>` : ''}
              </div>
              <div style="text-align: right; margin-left: 8px; min-width: 50px;">
                <div style="font-size: 12px; color: #2c3e50; font-weight: 700;">${(parseInt(model.num_users) || 0)}</div>
                <div style="font-size: 9px; color: #95a5a6; font-weight: 500;">viewers</div>
              </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <div style="font-size: 9px; color: #7f8c8d; font-weight: 600;">${Math.round(progress)}% complete</div>
              <div style="font-size: 9px; color: #7f8c8d; font-weight: 600;">${tokensRemaining} tokens left</div>
            </div>
            <div style="margin-bottom: 6px;">
              <div style="background: #ecf0f1; height: 6px; border-radius: 3px; overflow: hidden;">
                <div style="background: ${color}; height: 100%; width: ${Math.min(progress, 100)}%; transition: width 0.3s ease;"></div>
              </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 9px;">
              <div style="display: flex; gap: 6px;">
                ${velocity > 0 ? `<span style="background: #27ae60; color: white; padding: 2px 6px; border-radius: 3px; font-weight: 600;">${Math.round(velocity)} tok/min</span>` : '<span style="color: #95a5a6; font-size: 8px;">No velocity data</span>'}
              </div>
              <div style="display: flex; gap: 4px;">
                ${model.is_hd ? '<span style="background: #3498db; color: white; padding: 2px 6px; border-radius: 3px; font-weight: 600;">HD</span>' : ''}
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');
}

function filterDiscoveryByGoalType(goalType) {
  if (!window.cachedModelsForDiscovery) return;

  // Get filtered models based on goal type
  const filtered = window.cachedModelsForDiscovery.filter(m => {
    if (!m._analytics?.actual_goals?.current_goal) return false;
    
    const goal = m._analytics.actual_goals.current_goal;
    const initialTokens = goal.initial_tokens || 0;
    const tokensRemaining = goal.tokens_remaining || 0;
    const progress = initialTokens > 0 ? ((initialTokens - tokensRemaining) / initialTokens) * 100 : 0;
    const viewers = parseInt(m.num_users) || 0;
    const velocity = goal.velocity || 0;
    
    switch(goalType) {
      case 'almost-there':
        return tokensRemaining > 0 && progress > 80 && viewers > 3;
      case 'close-to-goal':
        return tokensRemaining > 0 && progress > 50 && progress <= 80 && viewers > 5;
      case 'big-goals':
        return initialTokens > 1000 && viewers > 10;
      case 'new-goals':
        return tokensRemaining > 0 && progress < 10 && viewers > 3;
      case 'fast-progress':
        return velocity > 5 && viewers > 5;
      default:
        return false;
    }
  });

  // Hide discovery sections and show filtered models in main grid
  const discoveryHighlights = document.getElementById('discovery-highlights');
  if (discoveryHighlights) {
    discoveryHighlights.style.display = 'none';
    sessionStorage.removeItem('discoveryHubActive');
    const showBtn = document.getElementById('show-stats');
    if (showBtn) showBtn.textContent = '🔍 Discovery Hub';
  }

  // Show main grid and populate with filtered models
  const modelGrid = document.getElementById('model-grid');
  const pagination = document.querySelector('.pagination-wrapper');
  const filterSidebar = document.getElementById('filter-sidebar');
  const searchSection = document.querySelector('.search-section');
  const headerControls = document.querySelector('.header-controls');
  
  if (modelGrid) modelGrid.style.display = 'block';
  if (pagination) pagination.style.display = 'block';
  if (filterSidebar) filterSidebar.classList.add('open');
  if (searchSection) searchSection.style.display = 'block';
  if (headerControls) headerControls.style.display = 'flex';

  // Add goal type indicator
  const goalTypeNames = {
    'almost-there': 'Almost There (80%+ complete)',
    'close-to-goal': 'Close to Goal (50-80% complete)',
    'big-goals': 'Big Goals (1000+ tokens)',
    'new-goals': 'New Goals (<10% complete)',
    'fast-progress': 'Fast Progress (high velocity)'
  };
  
  const indicatorHtml = `
    <div style="background: #e3f2fd; border: 1px solid #2196F3; border-radius: 8px; padding: 12px; margin-bottom: 16px; text-align: center;">
      <div style="color: #1976D2; font-weight: 600; margin-bottom: 4px;">🎯 Filtered by Goal Type</div>
      <div style="color: #424242; font-size: 14px;">${goalTypeNames[goalType]} (${filtered.length} models)</div>
      <button onclick="clearGoalTypeFilter()" style="margin-top: 8px; padding: 6px 12px; background: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">Clear Filter</button>
    </div>
  `;

  if (modelGrid) {
    modelGrid.innerHTML = indicatorHtml + renderModels(filtered.slice(0, window.camsPerPage || 20));
  }

  // Update displayed counts
  const totalResultsEl = document.getElementById('total-results');
  if (totalResultsEl) {
    totalResultsEl.textContent = filtered.length.toLocaleString();
  }
}

function clearGoalTypeFilter() {
  if (window.cachedModelsForDiscovery) {
    const modelGrid = document.getElementById('model-grid');
    const totalResultsEl = document.getElementById('total-results');
    
    if (modelGrid) {
      modelGrid.innerHTML = renderModels(window.cachedModelsForDiscovery.slice(0, window.camsPerPage || 20));
    }
    
    if (totalResultsEl) {
      totalResultsEl.textContent = window.cachedModelsForDiscovery.length.toLocaleString();
    }
  }
}

function populateActivityTrends(models) {
  const onlineTimeRanges = {
    'Under 1 hour': models.filter(m => (m.seconds_online || 0) < 3600).length,
    '1-3 hours': models.filter(m => (m.seconds_online || 0) >= 3600 && (m.seconds_online || 0) < 10800).length,
    '3-5 hours': models.filter(m => (m.seconds_online || 0) >= 10800 && (m.seconds_online || 0) < 18000).length,
    '5+ hours': models.filter(m => (m.seconds_online || 0) >= 18000).length
  };
  
  const viewerRanges = {
    '1-10 viewers': models.filter(m => (m.num_users || 0) >= 1 && (m.num_users || 0) <= 10).length,
    '11-50 viewers': models.filter(m => (m.num_users || 0) >= 11 && (m.num_users || 0) <= 50).length,
    '51-100 viewers': models.filter(m => (m.num_users || 0) >= 51 && (m.num_users || 0) <= 100).length,
    '100+ viewers': models.filter(m => (m.num_users || 0) > 100).length
  };
  
  const container = document.getElementById('activity-trends');
  container.innerHTML = `
    <div style="margin-bottom: 16px;">
      <h4 style="margin: 0 0 8px 0; font-size: 14px; color: #333;">Online Duration</h4>
      ${Object.entries(onlineTimeRanges).map(([range, count]) => `
        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
          <span style="font-size: 13px; color: #666;">${range}</span>
          <span style="font-size: 13px; font-weight: 600; color: #333;">${count}</span>
        </div>
      `).join('')}
    </div>
    <div>
      <h4 style="margin: 0 0 8px 0; font-size: 14px; color: #333;">Viewer Ranges</h4>
      ${Object.entries(viewerRanges).map(([range, count]) => `
        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
          <span style="font-size: 13px; color: #666;">${range}</span>
          <span style="font-size: 13px; font-weight: 600; color: #333;">${count}</span>
        </div>
      `).join('')}
    </div>
  `;
}

function populateCategoryBreakdown(models) {
  const genderCounts = {
    'Female': { count: models.filter(m => m.gender === 'f').length, filter: 'f' },
    'Male': { count: models.filter(m => m.gender === 'm').length, filter: 'm' },
    'Couple': { count: models.filter(m => m.gender === 'c').length, filter: 'c' },
    'Trans': { count: models.filter(m => m.gender === 't').length, filter: 't' }
  };
  
  const showTypeCounts = {
    'Public': { count: models.filter(m => m.current_show === 'public').length, filter: 'public' },
    'Private': { count: models.filter(m => m.current_show === 'private').length, filter: 'private' },
    'Group': { count: models.filter(m => m.current_show === 'group').length, filter: 'group' },
    'Away': { count: models.filter(m => m.current_show === 'away').length, filter: 'away' }
  };
  
  const newModels = models.filter(m => m.is_new).length;
  const hdModels = models.filter(m => m.is_hd).length;
  
  // Check current filter for selected state
  const currentFilter = window.currentDiscoveryFilter;
  
  // Helper function to get selected styles
  function getFilterStyles(filterType, filterValue, baseColor) {
    // Check if this specific filter is selected (support multiple filters)
    const currentFilters = window.currentDiscoveryFilters || {};
    const isSelected = currentFilters[filterType] === filterValue;
    if (isSelected) {
      return {
        background: baseColor,
        border: `2px solid ${baseColor}`,
        textColor: '#fff',
        numberColor: '#fff'
      };
    }
    return {
      background: '#f8f9fa',
      border: '1px solid transparent',
      textColor: '#666',
      numberColor: baseColor
    };
  }
  
  const container = document.getElementById('category-breakdown');
  container.innerHTML = `
    ${Object.entries(genderCounts).filter(([_, data]) => data.count > 0).map(([category, data]) => {
      const styles = getFilterStyles('gender', data.filter, '#e91e63');
      return `
        <div onclick="filterDiscoveryByCategory('gender', '${data.filter}')" 
             style="text-align: center; padding: 12px; background: ${styles.background}; border: ${styles.border}; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" 
             onmouseover="if(!this.style.background.includes('rgb(233, 30, 99)')) { this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'; }" 
             onmouseout="if(!this.style.background.includes('rgb(233, 30, 99)')) { this.style.transform='translateY(0)'; this.style.boxShadow='none'; }">
          <div style="font-size: 20px; font-weight: 700; color: ${styles.numberColor}; margin-bottom: 4px;">${data.count}</div>
          <div style="font-size: 12px; color: ${styles.textColor};">${category}</div>
        </div>
      `;
    }).join('')}
    ${Object.entries(showTypeCounts).filter(([_, data]) => data.count > 0).map(([category, data]) => {
      const styles = getFilterStyles('show_type', data.filter, '#2196F3');
      return `
        <div onclick="filterDiscoveryByCategory('show_type', '${data.filter}')" 
             style="text-align: center; padding: 12px; background: ${styles.background}; border: ${styles.border}; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" 
             onmouseover="if(!this.style.background.includes('rgb(33, 150, 243)')) { this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'; }" 
             onmouseout="if(!this.style.background.includes('rgb(33, 150, 243)')) { this.style.transform='translateY(0)'; this.style.boxShadow='none'; }">
          <div style="font-size: 20px; font-weight: 700; color: ${styles.numberColor}; margin-bottom: 4px;">${data.count}</div>
          <div style="font-size: 12px; color: ${styles.textColor};">${category}</div>
        </div>
      `;
    }).join('')}
    ${(() => {
      const styles = getFilterStyles('is_new', true, '#FF5722');
      return `
        <div onclick="filterDiscoveryByCategory('is_new', true)" 
             style="text-align: center; padding: 12px; background: ${styles.background}; border: ${styles.border}; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" 
             onmouseover="if(!this.style.background.includes('rgb(255, 87, 34)')) { this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'; }" 
             onmouseout="if(!this.style.background.includes('rgb(255, 87, 34)')) { this.style.transform='translateY(0)'; this.style.boxShadow='none'; }">
          <div style="font-size: 20px; font-weight: 700; color: ${styles.numberColor}; margin-bottom: 4px;">${newModels}</div>
          <div style="font-size: 12px; color: ${styles.textColor};">New Models</div>
        </div>
      `;
    })()}
    ${(() => {
      const styles = getFilterStyles('is_hd', true, '#4CAF50');
      return `
        <div onclick="filterDiscoveryByCategory('is_hd', true)" 
             style="text-align: center; padding: 12px; background: ${styles.background}; border: ${styles.border}; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" 
             onmouseover="if(!this.style.background.includes('rgb(76, 175, 80)')) { this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'; }" 
             onmouseout="if(!this.style.background.includes('rgb(76, 175, 80)')) { this.style.transform='translateY(0)'; this.style.boxShadow='none'; }">
          <div style="font-size: 20px; font-weight: 700; color: ${styles.numberColor}; margin-bottom: 4px;">${hdModels}</div>
          <div style="font-size: 12px; color: ${styles.textColor};">HD Streams</div>
        </div>
      `;
    })()}
    <div onclick="clearDiscoveryFilters()" style="text-align: center; padding: 12px; background: #e8f4fd; border: 1px solid #2196F3; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" 
         onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)';" 
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
      <div style="font-size: 16px; font-weight: 700; color: #2196F3; margin-bottom: 4px;">🔄</div>
      <div style="font-size: 12px; color: #2196F3;">Clear Filters</div>
    </div>
  `;
}

function populateAgeFilters(models) {
  const container = document.getElementById('age-filter-breakdown');
  if (!container) return;
  
  // Define age ranges
  const ageRanges = [
    { label: '18-21', min: 18, max: 21 },
    { label: '22-25', min: 22, max: 25 },
    { label: '26-30', min: 26, max: 30 },
    { label: '31-35', min: 31, max: 35 },
    { label: '36-40', min: 36, max: 40 },
    { label: '41-50', min: 41, max: 50 },
    { label: '50+', min: 50, max: 99 }
  ];
  
  // Count models in each age range
  const ageCounts = ageRanges.map(range => {
    const count = models.filter(m => {
      const age = parseInt(m.age);
      return age >= range.min && age <= range.max;
    }).length;
    return { ...range, count };
  }).filter(range => range.count > 0); // Only show ranges with models
  
  if (ageCounts.length === 0) {
    container.innerHTML = '<p style="color: #666; font-style: italic;">No age data available</p>';
    return;
  }
  
  container.innerHTML = ageCounts.map(range => {
    const isSelected = window.currentDiscoveryAgeFilter && 
      window.currentDiscoveryAgeFilter.min === range.min && 
      window.currentDiscoveryAgeFilter.max === range.max;
    
    return `
      <div class="filter-item age-filter-item" 
           onclick="filterDiscoveryByAge(${range.min}, ${range.max})"
           style="background: ${isSelected ? 'linear-gradient(135deg, #ff4757 0%, #ff6b7a 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'}; 
                  color: white; 
                  padding: 16px; 
                  border-radius: 8px; 
                  text-align: center; 
                  cursor: pointer; 
                  transition: all 0.3s ease;
                  box-shadow: 0 2px 8px rgba(${isSelected ? '255, 71, 87' : '102, 126, 234'}, 0.3);"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(${isSelected ? '255, 71, 87' : '102, 126, 234'}, 0.4)';"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(${isSelected ? '255, 71, 87' : '102, 126, 234'}, 0.3)';">
        <div style="font-size: 18px; font-weight: bold; margin-bottom: 4px;">${range.label}</div>
        <div style="font-size: 12px; opacity: 0.9;">${range.count} models</div>
      </div>
    `;
  }).join('');
}

function filterDiscoveryByAge(minAge, maxAge) {
  if (!window.cachedModelsForDiscovery) return;
  
  // Check if this age filter is already selected - if so, toggle it off
  if (window.currentDiscoveryAgeFilter && 
      window.currentDiscoveryAgeFilter.min === minAge && 
      window.currentDiscoveryAgeFilter.max === maxAge) {
    clearAgeFilter();
    return;
  }
  
  // Set the current age filter
  window.currentDiscoveryAgeFilter = { min: minAge, max: maxAge };
  
  // Filter models by age range
  const filteredModels = window.cachedModelsForDiscovery.filter(m => {
    const age = parseInt(m.age);
    return age >= minAge && age <= maxAge;
  });
  
  // Update age filters display to show selected state
  populateAgeFilters(window.cachedModelsForDiscovery);
  
  // Repopulate all discovery sections with filtered models
  populateDiscoverySections(filteredModels);
  
  // Add an "Age Filter Active" indicator
  addAgeFilterIndicator(minAge, maxAge, filteredModels.length);
}

function clearAgeFilter() {
  // Remove current age filter
  window.currentDiscoveryAgeFilter = null;
  
  // Remove indicator
  const indicator = document.getElementById('age-filter-indicator');
  if (indicator) {
    indicator.remove();
  }
  
  // Reset age filters display
  if (window.cachedModelsForDiscovery) {
    populateAgeFilters(window.cachedModelsForDiscovery);
    populateDiscoverySections(window.cachedModelsForDiscovery);
  }
}

function addAgeFilterIndicator(minAge, maxAge, count) {
  // Add indicator above discovery sections
  const sectionsContainer = document.querySelector('.discovery-sections');
  if (!sectionsContainer) return;
  
  // Remove existing indicator
  const existingIndicator = document.getElementById('age-filter-indicator');
  if (existingIndicator) {
    existingIndicator.remove();
  }
  
  // Create new indicator
  const indicator = document.createElement('div');
  indicator.id = 'age-filter-indicator';
  const ageLabel = maxAge >= 99 ? `${minAge}+` : `${minAge}-${maxAge}`;
  indicator.innerHTML = `
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                color: white; 
                padding: 12px 20px; 
                border-radius: 8px; 
                margin-bottom: 16px; 
                display: flex; 
                justify-content: space-between; 
                align-items: center;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
      <span>🎂 Showing ${count} models aged ${ageLabel}</span>
      <button onclick="clearAgeFilter()" 
              style="background: rgba(255,255,255,0.2); 
                     border: 1px solid rgba(255,255,255,0.3); 
                     color: white; 
                     padding: 4px 8px; 
                     border-radius: 4px; 
                     font-size: 12px; 
                     cursor: pointer;">
        Clear Filter
      </button>
    </div>
  `;
  
  sectionsContainer.parentNode.insertBefore(indicator, sectionsContainer);
}

function populatePopularTags(models) {
  const container = document.getElementById('popular-tags');
  if (!container) return;
  
  // Extract all tags from models and count occurrences
  const tagCounts = {};
  models.forEach(m => {
    if (m.tags && Array.isArray(m.tags)) {
      m.tags.forEach(tag => {
        const cleanTag = tag.toLowerCase().trim();
        if (cleanTag.length > 1 && cleanTag.length <= 15) { // Reasonable tag length
          tagCounts[cleanTag] = (tagCounts[cleanTag] || 0) + 1;
        }
      });
    }
    // Also extract hashtags from room subjects
    if (m.room_subject) {
      const hashtagMatches = m.room_subject.match(/#(\w+)/g);
      if (hashtagMatches) {
        hashtagMatches.forEach(hashtag => {
          const cleanTag = hashtag.slice(1).toLowerCase().trim();
          if (cleanTag.length > 1 && cleanTag.length <= 15) {
            tagCounts[cleanTag] = (tagCounts[cleanTag] || 0) + 1;
          }
        });
      }
    }
  });
  
  // Sort tags by popularity and get top 50 (more for carousel)
  const popularTags = Object.entries(tagCounts)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 50);
  
  if (popularTags.length === 0) {
    container.innerHTML = '<p style="color: #666; font-style: italic;">No tags found</p>';
    return;
  }
  
  // Initialize tag carousel data
  if (!window.tagCarouselData) {
    window.tagCarouselData = {
      allTags: popularTags,
      currentIndex: 0,
      itemsPerView: 8 // Show 8 tags at a time
    };
  }
  window.tagCarouselData.allTags = popularTags;
  window.tagCarouselData.currentIndex = 0;
  
  container.innerHTML = popularTags.map(([tag, count]) => {
    const fontSize = Math.min(16, Math.max(12, 12 + (count / 10)));
    const opacity = Math.min(1, Math.max(0.6, count / 50));
    return `
      <button onclick="filterDiscoveryByTag('${tag}')" 
              style="flex: 0 0 auto;
                     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                     border: none; 
                     color: white; 
                     padding: 6px 12px; 
                     border-radius: 16px; 
                     font-size: ${fontSize}px; 
                     cursor: pointer; 
                     opacity: ${opacity};
                     transition: all 0.3s ease;
                     box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
                     white-space: nowrap;"
              onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(102, 126, 234, 0.4)';"
              onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(102, 126, 234, 0.3)';">
        #${tag} (${count})
      </button>
    `;
  }).join('');
  
  // Update carousel navigation buttons visibility
  updateTagCarouselNavigation();
}

function scrollTagCarousel(direction) {
  const data = window.tagCarouselData;
  if (!data) return;
  
  const totalTags = data.allTags.length;
  const scrollAmount = 4; // Scroll 4 tags at a time
  
  if (direction === 'next' && data.currentIndex + data.itemsPerView < totalTags) {
    data.currentIndex = Math.min(data.currentIndex + scrollAmount, totalTags - data.itemsPerView);
  } else if (direction === 'prev' && data.currentIndex > 0) {
    data.currentIndex = Math.max(data.currentIndex - scrollAmount, 0);
  }
  
  // Update the carousel display
  const container = document.getElementById('popular-tags');
  if (!container) return;
  
  // Calculate translation based on the visible items width
  const tagButtons = container.querySelectorAll('button');
  let translateX = 0;
  for (let i = 0; i < data.currentIndex; i++) {
    if (tagButtons[i]) {
      translateX += tagButtons[i].offsetWidth + 8; // 8px gap
    }
  }
  
  container.style.transform = `translateX(-${translateX}px)`;
  
  // Update navigation buttons
  updateTagCarouselNavigation();
}

function updateTagCarouselNavigation() {
  const data = window.tagCarouselData;
  if (!data) return;
  
  const prevBtn = document.querySelector('.tag-carousel-prev');
  const nextBtn = document.querySelector('.tag-carousel-next');
  
  const totalTags = data.allTags.length;
  const canScrollLeft = data.currentIndex > 0;
  const canScrollRight = data.currentIndex + data.itemsPerView < totalTags;
  
  if (prevBtn) prevBtn.style.display = canScrollLeft ? 'flex' : 'none';
  if (nextBtn) nextBtn.style.display = canScrollRight ? 'flex' : 'none';
}

function refreshPopularTags() {
  if (!window.cachedModelsForDiscovery) return;
  populatePopularTags(window.cachedModelsForDiscovery);
}

// Auto-refresh system for discovery hub
let discoveryAutoRefreshInterval;
let lastAutoRefreshTime = 0;

function startDiscoveryAutoRefresh() {
  // Clear any existing interval
  if (discoveryAutoRefreshInterval) {
    clearInterval(discoveryAutoRefreshInterval);
  }
  
  console.log('🔄 Starting discovery hub auto-refresh (10s intervals for testing)');
  
  // Do an immediate refresh first
  setTimeout(async () => {
    console.log('🔄 Performing immediate auto-refresh...');
    await performAutoRefresh();
  }, 3000);
  
  discoveryAutoRefreshInterval = setInterval(async () => {
    console.log('🔄 Auto-refresh interval triggered at', new Date().toLocaleTimeString());
    await performAutoRefresh();
  }, AUTO_REFRESH_INTERVAL);
}

async function performAutoRefresh() {
  try {
    const now = Date.now();
    
    console.log('🔄 Starting auto-refresh cycle...');
    
    // Update status indicators to show refreshing
    updateRefreshStatus('refreshing');
    
    // Fetch fresh data
    const allModels = await loadAllCachedDataForStats();
    
    if (allModels && allModels.length > 0) {
      console.log(`🔄 Auto-refresh: Loaded ${allModels.length} models`);
      
      // Update cached data
      window.cachedModelsForDiscovery = allModels;
      
      // Refresh all sections (in correct order)
      populateStatsOverview(allModels);
      populateTopPerformers(allModels);
      populatePopularTags(allModels);
      
      // Apply any active discovery filters before repopulating sections
      let modelsToDisplay = allModels;
      if (window.currentDiscoveryFilters && Object.keys(window.currentDiscoveryFilters).length > 0) {
        console.log('🔄 Auto-refresh: Applying active filters:', window.currentDiscoveryFilters);
        modelsToDisplay = applyMultipleDiscoveryFilters(allModels, window.currentDiscoveryFilters);
        console.log(`🔄 Auto-refresh: Filtered from ${allModels.length} to ${modelsToDisplay.length} models`);
      }
      
      populateDiscoverySections(modelsToDisplay);
      
      // Load analytics for goal widgets (await to ensure proper loading)
      await loadAnalyticsForGoalWidgets(allModels);
      
      // Update last refresh time and status
      lastAutoRefreshTime = now;
      updateRefreshStatus('updated');
      
      console.log('✅ Discovery hub auto-refresh complete');
    } else {
      console.warn('⚠️ Auto-refresh returned no data');
      updateRefreshStatus('error');
    }
  } catch (error) {
    console.error('❌ Discovery hub auto-refresh error:', error);
    updateRefreshStatus('error');
  }
}

function stopDiscoveryAutoRefresh() {
  if (discoveryAutoRefreshInterval) {
    clearInterval(discoveryAutoRefreshInterval);
    discoveryAutoRefreshInterval = null;
    console.log('🛑 Discovery hub auto-refresh stopped');
  }
}

function updateRefreshStatus(status) {
  const statusElements = [
    'trending-status', 'new-models-status', 'just-live-status', 
    'hidden-gems-status', 'high-energy-status', 'marathon-status',
    'international-status', 'interactive-status', 'couples-status', 
    'mature-status', 'tags-status'
  ];
  
  let statusText = '';
  let statusColor = '#666';
  
  switch(status) {
    case 'refreshing':
      statusText = 'Updating...';
      statusColor = '#ff9800';
      break;
    case 'updated':
      const timeAgo = Math.floor((Date.now() - lastAutoRefreshTime) / 1000);
      statusText = timeAgo < 60 ? `Updated ${timeAgo}s ago` : `Updated ${Math.floor(timeAgo/60)}m ago`;
      statusColor = '#4caf50';
      break;
    case 'error':
      statusText = 'Update failed';
      statusColor = '#f44336';
      break;
    default:
      statusText = 'Live updating...';
      statusColor = '#666';
  }
  
  statusElements.forEach(id => {
    const element = document.getElementById(id);
    if (element) {
      element.textContent = statusText;
      element.style.color = statusColor;
    }
  });
}

function filterDiscoveryByTag(tag) {
  if (!window.cachedModelsForDiscovery) return;
  
  // Check if this tag is already selected - if so, toggle it off
  if (window.currentDiscoveryTagFilter === tag) {
    clearTagFilter();
    return;
  }
  
  // Set the current tag filter
  window.currentDiscoveryTagFilter = tag;
  
  // Filter models that have this tag in their tags array or room subject
  const filteredModels = window.cachedModelsForDiscovery.filter(m => {
    // Check if tag is in the tags array
    if (m.tags && m.tags.some(t => t.toLowerCase() === tag.toLowerCase())) {
      return true;
    }
    // Check if tag is in the room subject as hashtag
    if (m.room_subject && m.room_subject.toLowerCase().includes('#' + tag.toLowerCase())) {
      return true;
    }
    return false;
  });
  
  // Update popular tags to highlight selected tag
  highlightSelectedTag(tag);
  
  // Repopulate all discovery sections with filtered models
  populateDiscoverySections(filteredModels);
  
  // Add a "Clear Tag Filter" message
  addTagFilterIndicator(tag, filteredModels.length);
}

function highlightSelectedTag(selectedTag) {
  const container = document.getElementById('popular-tags');
  if (!container) return;
  
  // Update all tag buttons to show selected state
  container.querySelectorAll('button').forEach(btn => {
    const tagMatch = btn.textContent.match(/#(\w+)/);
    if (tagMatch && tagMatch[1].toLowerCase() === selectedTag.toLowerCase()) {
      btn.style.background = 'linear-gradient(135deg, #ff4757 0%, #ff6b7a 100%)';
      btn.style.boxShadow = '0 4px 8px rgba(255, 71, 87, 0.4)';
    } else {
      btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
      btn.style.boxShadow = '0 2px 4px rgba(102, 126, 234, 0.3)';
    }
  });
}

function addTagFilterIndicator(tag, count) {
  // Add indicator above discovery sections
  const sectionsContainer = document.querySelector('.discovery-sections');
  if (!sectionsContainer) return;
  
  // Remove existing indicator
  const existingIndicator = document.getElementById('tag-filter-indicator');
  if (existingIndicator) {
    existingIndicator.remove();
  }
  
  // Create new indicator
  const indicator = document.createElement('div');
  indicator.id = 'tag-filter-indicator';
  indicator.innerHTML = `
    <div style="background: linear-gradient(135deg, #ff4757 0%, #ff6b7a 100%); 
                color: white; 
                padding: 12px 20px; 
                border-radius: 8px; 
                margin-bottom: 16px; 
                display: flex; 
                justify-content: space-between; 
                align-items: center;
                box-shadow: 0 4px 12px rgba(255, 71, 87, 0.3);">
      <span>📋 Showing ${count} models with tag: <strong>#${tag}</strong></span>
      <button onclick="clearTagFilter()" 
              style="background: rgba(255,255,255,0.2); 
                     border: 1px solid rgba(255,255,255,0.3); 
                     color: white; 
                     padding: 4px 8px; 
                     border-radius: 4px; 
                     font-size: 12px; 
                     cursor: pointer;">
        Clear Filter
      </button>
    </div>
  `;
  
  sectionsContainer.parentNode.insertBefore(indicator, sectionsContainer);
}

function clearTagFilter() {
  // Remove current tag filter
  window.currentDiscoveryTagFilter = null;
  
  // Remove indicator
  const indicator = document.getElementById('tag-filter-indicator');
  if (indicator) {
    indicator.remove();
  }
  
  // Reset tag highlighting
  const container = document.getElementById('popular-tags');
  if (container) {
    container.querySelectorAll('button').forEach(btn => {
      btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
      btn.style.boxShadow = '0 2px 4px rgba(102, 126, 234, 0.3)';
    });
  }
  
  // Repopulate sections with all models
  if (window.cachedModelsForDiscovery) {
    populateDiscoverySections(window.cachedModelsForDiscovery);
  }
}

// No caching - always fetch fresh analytics for live goal updates

function enrichModelsWithAnalytics(models) {
  return models.map(model => {
    const enrichedModel = { ...model };
    const username = model.username.toLowerCase();
    
    // No caching - analytics data will be loaded asynchronously in background
    enrichedModel._analytics = null;
    
    return enrichedModel;
  });
}

async function loadAnalyticsForGoalWidgets(models) {
  // Load analytics data in background for goal stats widget
  console.log(`🎯 Loading analytics for ${models.length} models for goal widgets...`);
  const sampleModels = models.slice(0, 100);
  
  try {
    const enrichedModels = await Promise.all(sampleModels.map(async (model) => {
      const username = model.username.toLowerCase();
      
      // Always fetch fresh analytics data for live updates
      try {
        const response = await fetch(`/cache/analytics/${username}.json`);
        if (response.ok) {
          const analyticsData = await response.json();
          return { ...model, _analytics: analyticsData };
        } else {
          return { ...model, _analytics: null };
        }
      } catch (error) {
        console.error(`Failed to fetch analytics for ${username}:`, error);
        return { ...model, _analytics: null };
      }
    }));
    
    // Update goal stats widget with enriched data
    if (window.cachedModelsForDiscovery) {
      // Update cached models with analytics data
      const updatedModels = window.cachedModelsForDiscovery.map(model => {
        const enriched = enrichedModels.find(e => e.username === model.username);
        return enriched || model;
      });
      window.cachedModelsForDiscovery = updatedModels;
      
      console.log(`✅ Analytics loaded, re-populating goal widgets with ${updatedModels.length} models...`);
      // Re-populate goal widgets with enriched data
      populateGoalWidgets(updatedModels);
    }
  } catch (error) {
    console.error('❌ Error loading analytics for goal widgets:', error);
  }
}

function populateDiscoverySections(models) {
  const trending = getDiscoveryModels(models, 'trending');
  const newModels = getDiscoveryModels(models, 'new-models');
  const justLive = getDiscoveryModels(models, 'just-live');
  const hiddenGems = getDiscoveryModels(models, 'hidden-gems');
  const highEnergy = getDiscoveryModels(models, 'high-energy');
  const marathon = getDiscoveryModels(models, 'marathon');
  const international = getDiscoveryModels(models, 'international');
  const interactive = getDiscoveryModels(models, 'interactive');
  const couples = getDiscoveryModels(models, 'couples');
  const mature = getDiscoveryModels(models, 'mature');
  
  // Load analytics data for goal widgets
  loadAnalyticsForGoalWidgets(models);
  
  renderDiscoverySection(trending, 'trending-grid', 24);
  renderDiscoverySection(newModels, 'new-models-grid', 24);
  renderDiscoverySection(justLive, 'just-live-grid', 24);
  renderDiscoverySection(hiddenGems, 'hidden-gems-grid', 24);
  renderDiscoverySection(highEnergy, 'high-energy-grid', 24);
  renderDiscoverySection(marathon, 'marathon-grid', 24);
  renderDiscoverySection(international, 'international-grid', 24);
  renderDiscoverySection(interactive, 'interactive-grid', 24);
  renderDiscoverySection(couples, 'couples-grid', 24);
  renderDiscoverySection(mature, 'mature-grid', 24);
  
  // Show sections with data
  document.getElementById('trending-section').style.display = trending.length > 0 ? 'block' : 'none';
  document.getElementById('new-models-section').style.display = newModels.length > 0 ? 'block' : 'none';
  document.getElementById('just-live-section').style.display = justLive.length > 0 ? 'block' : 'none';
  document.getElementById('hidden-gems-section').style.display = hiddenGems.length > 0 ? 'block' : 'none';
  document.getElementById('high-energy-section').style.display = highEnergy.length > 0 ? 'block' : 'none';
  document.getElementById('marathon-section').style.display = marathon.length > 0 ? 'block' : 'none';
  document.getElementById('international-section').style.display = international.length > 0 ? 'block' : 'none';
  document.getElementById('interactive-section').style.display = interactive.length > 0 ? 'block' : 'none';
  document.getElementById('couples-section').style.display = couples.length > 0 ? 'block' : 'none';
  document.getElementById('mature-section').style.display = mature.length > 0 ? 'block' : 'none';
}

function getDiscoveryModels(models, category) {
  let filtered = [];
  const now = Date.now() / 1000;
  
  switch(category) {
    case 'trending':
      // High viewer count with good engagement
      filtered = [...models]
        .filter(m => (m.num_users || 0) > 50)
        .sort((a, b) => {
          const scoreA = (a.num_users || 0) * Math.log((a.num_followers || 1) + 1);
          const scoreB = (b.num_users || 0) * Math.log((b.num_followers || 1) + 1);
          return scoreB - scoreA;
        })
        .slice(0, 100);
      break;
      
    case 'new-models':
      filtered = [...models]
        .filter(m => m.is_new)
        .sort((a, b) => (b.num_users || 0) - (a.num_users || 0))
        .slice(0, 100);
      break;
      
    case 'just-live':
      // Recently came online (within 30 minutes) with strong initial viewership
      const avgViewers = models.reduce((sum, m) => sum + (parseInt(m.num_users) || 0), 0) / models.length;
      const justLiveViewerThreshold = Math.max(10, avgViewers * 0.6);
      filtered = [...models]
        .filter(m => (m.seconds_online || 0) <= 1800 && (m.num_users || 0) >= justLiveViewerThreshold)
        .sort((a, b) => (b.num_users || 0) - (a.num_users || 0))
        .slice(0, 100);
      break;
      
    case 'hidden-gems':
      filtered = [...models]
        .filter(m => {
          const followers = m.num_followers || 0;
          const viewers = m.num_users || 0;
          return followers > 10000 && viewers < 100 && viewers > 5;
        })
        .sort((a, b) => (b.num_followers || 0) - (a.num_followers || 0))
        .slice(0, 100);
      break;
      
    case 'high-energy':
      // Models with lots of tags (indicating activity) and decent viewer count
      filtered = [...models]
        .filter(m => (m.tags || []).length >= 3 && (m.num_users || 0) > 20)
        .sort((a, b) => {
          const scoreA = (a.tags || []).length * (a.num_users || 0);
          const scoreB = (b.tags || []).length * (b.num_users || 0);
          return scoreB - scoreA;
        })
        .slice(0, 100);
      break;
      
    case 'marathon':
      filtered = [...models]
        .filter(m => (m.seconds_online || 0) >= 18000) // 5+ hours
        .sort((a, b) => (b.seconds_online || 0) - (a.seconds_online || 0))
        .slice(0, 100);
      break;
      
    case 'international':
      // Models from diverse countries excluding US
      filtered = [...models]
        .filter(m => m.country && m.country.toLowerCase() !== 'us' && (m.num_users || 0) > 10)
        .sort((a, b) => (b.num_users || 0) - (a.num_users || 0))
        .slice(0, 100);
      break;
      
    case 'interactive':
      // Models likely doing interactive shows (lovense, interactive tags)
      const interactiveTags = ['lovense', 'lush', 'interactive', 'tip', 'goal', 'ohmibod', 'nora'];
      filtered = [...models]
        .filter(m => {
          const modelTags = (m.tags || []).map(t => t.toLowerCase());
          const roomSubject = (m.room_subject || '').toLowerCase();
          return interactiveTags.some(tag => 
            modelTags.includes(tag) || roomSubject.includes(tag)
          ) && (m.num_users || 0) > 15;
        })
        .sort((a, b) => (b.num_users || 0) - (a.num_users || 0))
        .slice(0, 100);
      break;
      
    case 'couples':
      // Couples and group shows
      const couplesTags = ['couple', 'couples', 'threesome', 'group', 'lesbian'];
      filtered = [...models]
        .filter(m => {
          const modelTags = (m.tags || []).map(t => t.toLowerCase());
          const roomSubject = (m.room_subject || '').toLowerCase();
          return couplesTags.some(tag => 
            modelTags.includes(tag) || roomSubject.includes(tag)
          ) && (m.num_users || 0) > 10;
        })
        .sort((a, b) => (b.num_users || 0) - (a.num_users || 0))
        .slice(0, 100);
      break;
      
    case 'mature':
      // Mature and experienced models
      filtered = [...models]
        .filter(m => (m.age || 0) >= 35 && (m.num_users || 0) > 8)
        .sort((a, b) => (b.num_users || 0) - (a.num_users || 0))
        .slice(0, 100);
      break;
      
  }
  
  // Shuffle for variety on refresh
  return shuffleArray(filtered);
}

function shuffleArray(array) {
  const shuffled = [...array];
  for (let i = shuffled.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
  }
  return shuffled;
}

function scrollDiscoveryCarousel(sectionId, direction) {
  const reelData = window.discoveryReelData[sectionId];
  if (!reelData) return;
  
  const totalModels = reelData.allModels.length;
  const scrollAmount = Math.min(3, reelData.itemsPerView); // Scroll by items per view or 3, whichever is smaller
  const itemWidth = 170; // Fixed item width (matches model card width)
  const gap = 12; // Gap between items
  const itemWithGap = itemWidth + gap;
  
  if (direction === 'next' && reelData.currentIndex + reelData.itemsPerView < totalModels) {
    reelData.currentIndex = Math.min(reelData.currentIndex + scrollAmount, totalModels - reelData.itemsPerView);
  } else if (direction === 'prev' && reelData.currentIndex > 0) {
    reelData.currentIndex = Math.max(reelData.currentIndex - scrollAmount, 0);
  }
  
  // Update the carousel display
  const container = document.getElementById(sectionId);
  if (!container) return;
  
  const track = container.querySelector('.carousel-track');
  const prevBtn = container.querySelector('.carousel-prev');
  const nextBtn = container.querySelector('.carousel-next');
  const infoDiv = container.querySelector('.carousel-info');
  
  if (track) {
    // Calculate the transform based on item width + gap, but subtract one gap since the first item doesn't need it
    const translateX = reelData.currentIndex > 0 
      ? (reelData.currentIndex * itemWithGap) - gap 
      : 0;
    track.style.transform = `translateX(-${translateX}px)`;
  }
  
  // Update button visibility
  const canScrollLeft = reelData.currentIndex > 0;
  const canScrollRight = reelData.currentIndex + reelData.itemsPerView < totalModels;
  
  if (prevBtn) prevBtn.style.display = canScrollLeft ? 'flex' : 'none';
  if (nextBtn) nextBtn.style.display = canScrollRight ? 'flex' : 'none';
  
  // Update info text
  if (infoDiv) {
    const visibleEnd = Math.min(reelData.currentIndex + reelData.itemsPerView, totalModels);
    infoDiv.textContent = `${reelData.currentIndex + 1}-${visibleEnd} of ${totalModels} models`;
  }
}

function showMoreDiscoveryModels(sectionId) {
  const reelData = window.discoveryReelData[sectionId];
  if (!reelData) return;
  
  // Increment page
  reelData.currentPage++;
  
  const startIndex = reelData.currentPage * reelData.modelsPerPage;
  const endIndex = startIndex + reelData.modelsPerPage;
  const nextModels = reelData.allModels.slice(startIndex, endIndex);
  
  if (nextModels.length === 0) return;
  
  const container = document.getElementById(sectionId);
  if (!container) return;
  
  // Calculate global stats for spotlight detection
  const globalStats = {
    avgViewers: reelData.allModels.reduce((sum, m) => sum + parseInt(m.num_users || 0), 0) / reelData.allModels.length,
    maxViewers: Math.max(...reelData.allModels.map(m => parseInt(m.num_users || 0))),
    avgOnlineTime: reelData.allModels.reduce((sum, m) => sum + parseInt(m.seconds_online || 0), 0) / reelData.allModels.length,
    maxOnlineTime: Math.max(...reelData.allModels.map(m => parseInt(m.seconds_online || 0)))
  };
  
  // Find and remove the existing "Show More" button
  const existingButton = container.querySelector('button[onclick*="showMoreDiscoveryModels"]');
  if (existingButton) {
    existingButton.parentElement.remove();
  }
  
  // Add new model cards
  const newCardsHTML = nextModels.map(m => {
    // Detect spotlights for this model
    let spotlightElements = detectSpotlight(m, globalStats);
    
    let chipHTML = '';
    if (m.current_show) {
      const showColors = {
        'public': 'status-public',
        'group': 'status-group', 
        'private': 'status-private',
        'away': 'status-away'
      };
      let showType = m.current_show.toLowerCase();
      let label = showType === 'group' ? 'GROUP' : showType.toUpperCase();
      let showTypeNormalized = showType === 'ticket' ? 'private' : showType;
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
    
    return `
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
  }).join('');
  
  container.innerHTML += newCardsHTML;
  
  // Add "Show More" button if there are still more models
  const totalModels = reelData.allModels.length;
  const currentlyShown = (reelData.currentPage + 1) * reelData.modelsPerPage;
  
  if (totalModels > currentlyShown) {
    const remainingModels = totalModels - currentlyShown;
    const showMoreText = remainingModels <= reelData.modelsPerPage 
      ? `Show ${remainingModels} More` 
      : `Show ${reelData.modelsPerPage} More`;
      
    container.innerHTML += `
      <div style="text-align: center; margin-top: 16px;">
        <button onclick="showMoreDiscoveryModels('${sectionId}')" 
                style="background: linear-gradient(45deg, #ff4757, #ff6b7a); 
                       border: none; 
                       color: white; 
                       padding: 10px 20px; 
                       border-radius: 20px; 
                       font-size: 14px; 
                       font-weight: 600; 
                       cursor: pointer; 
                       box-shadow: 0 2px 8px rgba(255, 71, 87, 0.3);
                       transition: all 0.3s ease;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(255, 71, 87, 0.4)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(255, 71, 87, 0.3)';">
          ${showMoreText} (${remainingModels} total remaining)
        </button>
      </div>
    `;
  }
}

function refreshDiscoverySection(category) {
  if (!window.cachedModelsForDiscovery) return;
  
  let models = window.cachedModelsForDiscovery;
  
  // Apply current discovery filter if any
  if (window.currentDiscoveryFilter) {
    models = applyDiscoveryFilter(models, window.currentDiscoveryFilter);
  }
  
  const discoveryModels = getDiscoveryModels(models, category);
  const gridId = category + '-grid';
  
  // Reset reel pagination on refresh
  if (window.discoveryReelData && window.discoveryReelData[gridId]) {
    window.discoveryReelData[gridId].currentPage = 0;
  }
  
  renderDiscoverySection(discoveryModels, gridId, 24);
}

function filterDiscoveryByCategory(filterType, filterValue) {
  if (!window.cachedModelsForDiscovery) return;
  
  // Initialize filters object if it doesn't exist
  if (!window.currentDiscoveryFilters) {
    window.currentDiscoveryFilters = {};
  }
  
  // Toggle behavior: if this filter is already active, remove it
  if (window.currentDiscoveryFilters[filterType] === filterValue) {
    delete window.currentDiscoveryFilters[filterType];
  } else {
    // Add or update this filter
    window.currentDiscoveryFilters[filterType] = filterValue;
  }
  
  // Apply all active filters and repopulate sections
  const filteredModels = applyMultipleDiscoveryFilters(window.cachedModelsForDiscovery, window.currentDiscoveryFilters);
  populateDiscoverySections(filteredModels);
  
  // Update category breakdown to show filtered counts
  populateCategoryBreakdown(filteredModels);
}

function applyMultipleDiscoveryFilters(models, filters) {
  // If no filters are active, return all models
  if (!filters || Object.keys(filters).length === 0) {
    return models;
  }
  
  // Apply all filters sequentially
  return models.filter(model => {
    // Check each active filter
    for (const [filterType, filterValue] of Object.entries(filters)) {
      switch(filterType) {
        case 'gender':
          if (model.gender !== filterValue) return false;
          break;
        case 'show_type':
          if (model.current_show !== filterValue) return false;
          break;
        case 'is_new':
          if (model.is_new !== filterValue) return false;
          break;
        case 'is_hd':
          if (model.is_hd !== filterValue) return false;
          break;
      }
    }
    return true; // Model passes all active filters
  });
}

// Keep old function for backward compatibility
function applyDiscoveryFilter(models, filter) {
  switch(filter.type) {
    case 'gender':
      return models.filter(m => m.gender === filter.value);
    case 'show_type':
      return models.filter(m => m.current_show === filter.value);
    case 'is_new':
      return models.filter(m => m.is_new === filter.value);
    case 'is_hd':
      return models.filter(m => m.is_hd === filter.value);
    default:
      return models;
  }
}

function clearDiscoveryFilters() {
  if (!window.cachedModelsForDiscovery) return;
  
  // Clear both old and new filter systems
  window.currentDiscoveryFilter = null;
  window.currentDiscoveryFilters = {};
  
  // Repopulate all sections with unfiltered data
  populateDiscoverySections(window.cachedModelsForDiscovery);
  populateCategoryBreakdown(window.cachedModelsForDiscovery);
}

function setupAgeSliders() {
  const minSlider = document.getElementById('min-age-slider');
  const maxSlider = document.getElementById('max-age-slider');
  const minInput = document.getElementById('min-age');
  const maxInput = document.getElementById('max-age');
  const minDisplay = document.getElementById('min-age-display');
  const maxDisplay = document.getElementById('max-age-display');
  const sliderTrack = document.querySelector('.slider-track');
  const container = document.querySelector('.dual-range-slider');
  
  if (!minSlider || !maxSlider || !container) return;
  
  function updateSliderTrack() {
    const min = parseInt(minSlider.min);
    const max = parseInt(minSlider.max);
    const minVal = parseInt(minSlider.value);
    const maxVal = parseInt(maxSlider.value);
    
    const minPercent = ((minVal - min) / (max - min)) * 100;
    const maxPercent = ((maxVal - min) / (max - min)) * 100;
    
    sliderTrack.style.left = minPercent + '%';
    sliderTrack.style.width = (maxPercent - minPercent) + '%';
  }
  
  function updateDisplays(minVal, maxVal) {
    minDisplay.textContent = minVal;
    maxDisplay.textContent = maxVal;
    minInput.value = minVal;
    maxInput.value = maxVal;
  }
  
  function handleSliderChange() {
    let minVal = parseInt(minSlider.value);
    let maxVal = parseInt(maxSlider.value);
    
    // Ensure min doesn't exceed max
    if (minVal > maxVal) {
      if (this === minSlider) {
        maxVal = minVal;
        maxSlider.value = maxVal;
      } else {
        minVal = maxVal;
        minSlider.value = minVal;
      }
    }
    
    updateDisplays(minVal, maxVal);
    updateSliderTrack();
    
    FILTERS.minAge = minVal;
    FILTERS.maxAge = maxVal;
    onFilterChange();
  }
  
  // Intelligent slider selection based on mouse position and values
  function getCloserSlider(event) {
    const rect = container.getBoundingClientRect();
    const clickX = event.clientX - rect.left;
    const containerWidth = rect.width;
    const clickPercent = (clickX / containerWidth) * 100;
    
    const min = parseInt(minSlider.min);
    const max = parseInt(minSlider.max);
    const minVal = parseInt(minSlider.value);
    const maxVal = parseInt(maxSlider.value);
    
    const minPercent = ((minVal - min) / (max - min)) * 100;
    const maxPercent = ((maxVal - min) / (max - min)) * 100;
    
    // Calculate distances from click to each slider position
    const distToMin = Math.abs(clickPercent - minPercent);
    const distToMax = Math.abs(clickPercent - maxPercent);
    
    // Return the closer slider, with preference for max if very close
    if (Math.abs(distToMin - distToMax) < 5) {
      return clickPercent > (minPercent + maxPercent) / 2 ? maxSlider : minSlider;
    }
    return distToMin < distToMax ? minSlider : maxSlider;
  }
  
  // Handle mouse interactions
  let activeSlider = null;
  let isDragging = false;
  
  function startDrag(event) {
    event.preventDefault();
    activeSlider = getCloserSlider(event);
    isDragging = true;
    
    // Bring active slider to front
    if (activeSlider === minSlider) {
      minSlider.style.zIndex = '3';
      maxSlider.style.zIndex = '2';
      minSlider.style.pointerEvents = 'all';
      maxSlider.style.pointerEvents = 'none';
    } else {
      maxSlider.style.zIndex = '3';
      minSlider.style.zIndex = '2';
      maxSlider.style.pointerEvents = 'all';
      minSlider.style.pointerEvents = 'none';
    }
    
    // Simulate mousedown on the active slider
    const rect = container.getBoundingClientRect();
    const percent = ((event.clientX - rect.left) / rect.width) * 100;
    const newValue = Math.round((percent / 100) * (99 - 18) + 18);
    const clampedValue = Math.max(18, Math.min(99, newValue));
    
    activeSlider.value = clampedValue;
    handleSliderChange.call(activeSlider);
    
    document.addEventListener('mousemove', handleDrag);
    document.addEventListener('mouseup', stopDrag);
  }
  
  function handleDrag(event) {
    if (!isDragging || !activeSlider) return;
    
    const rect = container.getBoundingClientRect();
    const percent = ((event.clientX - rect.left) / rect.width) * 100;
    const newValue = Math.round((percent / 100) * (99 - 18) + 18);
    const clampedValue = Math.max(18, Math.min(99, newValue));
    
    activeSlider.value = clampedValue;
    handleSliderChange.call(activeSlider);
  }
  
  function stopDrag() {
    isDragging = false;
    activeSlider = null;
    
    // Reset pointer events
    minSlider.style.pointerEvents = 'all';
    maxSlider.style.pointerEvents = 'all';
    
    document.removeEventListener('mousemove', handleDrag);
    document.removeEventListener('mouseup', stopDrag);
  }
  
  // Add container mouse events
  container.addEventListener('mousedown', startDrag);
  
  // Keep original slider events as backup
  minSlider.addEventListener('input', handleSliderChange);
  maxSlider.addEventListener('input', handleSliderChange);
  
  // Sync number inputs with sliders
  if (minInput) {
    minInput.addEventListener('change', function() {
      const val = Math.max(18, Math.min(99, parseInt(this.value) || 18));
      minSlider.value = val;
      handleSliderChange.call(minSlider);
    });
  }
  
  if (maxInput) {
    maxInput.addEventListener('change', function() {
      const val = Math.max(18, Math.min(99, parseInt(this.value) || 99));
      maxSlider.value = val;
      handleSliderChange.call(maxSlider);
    });
  }
  
  // Initialize
  updateSliderTrack();
  updateDisplays(parseInt(minSlider.value), parseInt(maxSlider.value));
}

// Favorites System
function setupFavoritesSystem() {
  const FAVORITES_KEY = 'camsite_favorites';
  
  function getFavorites() {
    try {
      return JSON.parse(localStorage.getItem(FAVORITES_KEY)) || [];
    } catch (e) {
      return [];
    }
  }
  
  function saveFavorites(favorites) {
    try {
      localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));
    } catch (e) {
      console.warn('Could not save favorites to localStorage');
    }
  }
  
  function isFavorite(username) {
    return getFavorites().includes(username.toLowerCase());
  }
  
  function toggleFavorite(username) {
    const favorites = getFavorites();
    const lowerUsername = username.toLowerCase();
    const index = favorites.indexOf(lowerUsername);
    
    if (index > -1) {
      favorites.splice(index, 1);
    } else {
      favorites.push(lowerUsername);
    }
    
    saveFavorites(favorites);
    updateFavoriteButtons();
    return favorites.includes(lowerUsername);
  }
  
  function updateFavoriteButtons() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
      const username = btn.dataset.username;
      const isFav = isFavorite(username);
      btn.classList.toggle('active', isFav);
      btn.innerHTML = isFav ? '❤️ Favorited' : '🤍 Add Favorite';
      btn.title = isFav ? 'Remove from favorites' : 'Add to favorites';
    });
  }
  
  // Add favorite buttons to model cards
  function addFavoriteButtons() {
    document.querySelectorAll('.model-card-cb').forEach(card => {
      const username = card.querySelector('[data-username]')?.dataset.username;
      if (!username || card.querySelector('.favorite-btn')) return;
      
      const favoriteBtn = document.createElement('button');
      favoriteBtn.className = 'favorite-btn';
      favoriteBtn.dataset.username = username;
      favoriteBtn.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleFavorite(username);
      };
      
      const cardTitle = card.querySelector('.model-username-cb, .card-title');
      if (cardTitle && cardTitle.parentNode) {
        cardTitle.parentNode.insertBefore(favoriteBtn, cardTitle.nextSibling);
      }
    });
    updateFavoriteButtons();
  }
  
  // Expose globally
  window.toggleFavorite = toggleFavorite;
  window.getFavorites = getFavorites;
  window.addFavoriteButtons = addFavoriteButtons;
  
  // Add favorites filter to sort dropdown
  const sortSelect = document.getElementById('sort-by');
  if (sortSelect && !document.querySelector('option[value="favorites"]')) {
    const option = document.createElement('option');
    option.value = 'favorites';
    option.textContent = 'My Favorites';
    sortSelect.appendChild(option);
  }
}

// Comparison Tool
function setupComparisonTool() {
  const COMPARE_KEY = 'camsite_comparison';
  let comparisonList = [];
  
  function addToComparison(modelData) {
    if (comparisonList.length >= 3) {
      alert('Maximum 3 models can be compared at once');
      return false;
    }
    
    const exists = comparisonList.find(m => m.username === modelData.username);
    if (exists) {
      return false;
    }
    
    comparisonList.push(modelData);
    updateComparisonUI();
    return true;
  }
  
  function removeFromComparison(username) {
    comparisonList = comparisonList.filter(m => m.username !== username);
    updateComparisonUI();
  }
  
  function updateComparisonUI() {
    let compareBtn = document.getElementById('comparison-btn');
    if (!compareBtn && comparisonList.length > 0) {
      compareBtn = document.createElement('button');
      compareBtn.id = 'comparison-btn';
      compareBtn.className = 'comparison-floating-btn';
      compareBtn.innerHTML = `<span class="compare-icon">⚖️</span><span class="compare-count">${comparisonList.length}</span>`;
      compareBtn.onclick = showComparisonModal;
      document.body.appendChild(compareBtn);
    }
    
    if (compareBtn) {
      if (comparisonList.length === 0) {
        compareBtn.remove();
      } else {
        compareBtn.querySelector('.compare-count').textContent = comparisonList.length;
        compareBtn.style.display = 'block';
      }
    }
    
    // Update compare buttons on cards
    document.querySelectorAll('.compare-btn').forEach(btn => {
      const username = btn.dataset.username;
      const inComparison = comparisonList.find(m => m.username === username);
      btn.classList.toggle('active', !!inComparison);
      btn.textContent = inComparison ? '✓ In Comparison' : '⚖️ Compare';
    });
  }
  
  function showComparisonModal() {
    if (comparisonList.length < 2) {
      alert('Add at least 2 models to compare');
      return;
    }
    
    const modal = createComparisonModal();
    document.body.appendChild(modal);
  }
  
  function createComparisonModal() {
    const modal = document.createElement('div');
    modal.className = 'comparison-modal';
    modal.innerHTML = `
      <div class="comparison-modal-content">
        <div class="comparison-header">
          <h2>Model Comparison</h2>
          <button class="close-comparison" onclick="this.closest('.comparison-modal').remove()">×</button>
        </div>
        <div class="comparison-table">
          <table>
            <thead>
              <tr>
                <th>Model</th>
                ${comparisonList.map(m => `<th><img src="${m.image_url}" alt="${m.username}" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;"><br>${m.username}</th>`).join('')}
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Viewers</strong></td>
                ${comparisonList.map(m => `<td>${m.num_users || 0}</td>`).join('')}
              </tr>
              <tr>
                <td><strong>Followers</strong></td>
                ${comparisonList.map(m => `<td>${(m.num_followers || 0).toLocaleString()}</td>`).join('')}
              </tr>
              <tr>
                <td><strong>Age</strong></td>
                ${comparisonList.map(m => `<td>${m.age || 'N/A'}</td>`).join('')}
              </tr>
              <tr>
                <td><strong>HD Stream</strong></td>
                ${comparisonList.map(m => `<td>${m.is_hd ? '✅ Yes' : '❌ No'}</td>`).join('')}
              </tr>
              <tr>
                <td><strong>Languages</strong></td>
                ${comparisonList.map(m => `<td>${(m.spoken_languages || 'N/A').split(',').slice(0,2).join(', ')}</td>`).join('')}
              </tr>
              <tr>
                <td><strong>Online Time</strong></td>
                ${comparisonList.map(m => {
                  const hours = Math.floor((m.seconds_online || 0) / 3600);
                  const mins = Math.floor(((m.seconds_online || 0) % 3600) / 60);
                  return `<td>${hours}h ${mins}m</td>`;
                }).join('')}
              </tr>
            </tbody>
          </table>
        </div>
        <div class="comparison-actions">
          <button onclick="comparisonList = []; updateComparisonUI(); this.closest('.comparison-modal').remove();">Clear All</button>
        </div>
      </div>
    `;
    return modal;
  }
  
  // Add compare buttons to model cards
  function addCompareButtons() {
    document.querySelectorAll('.model-card-cb').forEach(card => {
      const usernameEl = card.querySelector('[data-username]');
      const username = usernameEl?.dataset.username;
      if (!username || card.querySelector('.compare-btn')) return;
      
      // Extract model data from card
      const modelData = {
        username: username,
        image_url: card.querySelector('img')?.src || '',
        num_users: parseInt(card.querySelector('.viewer-count')?.textContent?.replace(/\D/g, '')) || 0,
        num_followers: 0, // Would need to be passed from model data
        age: null,
        is_hd: card.querySelector('.hd-badge') !== null,
        spoken_languages: '',
        seconds_online: 0
      };
      
      const compareBtn = document.createElement('button');
      compareBtn.className = 'compare-btn';
      compareBtn.dataset.username = username;
      compareBtn.textContent = '⚖️ Compare';
      compareBtn.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        addToComparison(modelData);
      };
      
      const favoriteBtn = card.querySelector('.favorite-btn');
      if (favoriteBtn) {
        favoriteBtn.parentNode.insertBefore(compareBtn, favoriteBtn.nextSibling);
      }
    });
  }
  
  // Expose globally
  window.addToComparison = addToComparison;
  window.removeFromComparison = removeFromComparison;
  window.comparisonList = comparisonList;
  window.addCompareButtons = addCompareButtons;
}

document.addEventListener('DOMContentLoaded', function() {
  setupAutoRefreshCheckboxBar();
  attachFilterListeners();
  fetchModels();
  updateSelected();
  updateResetFiltersLink();
  
  // Setup discovery features
  const sortBy = document.getElementById('sort-by');
  const showStatsBtn = document.getElementById('show-stats');
  
  if (sortBy) {
    sortBy.addEventListener('change', function() {
      if (allModels.length > 0) {
        const sorted = sortModels(allModels, this.value);
        renderModels(sorted);
      }
    });
  }
  
  if (showStatsBtn) {
    showStatsBtn.addEventListener('click', showDiscoveryHighlights);
  }
  
  // Setup enhanced age sliders
  setupAgeSliders();
  
  // Setup favorites system
  setupFavoritesSystem();
  
  // Setup comparison tool
  setupComparisonTool();
  
  // Check for and restore discovery hub state
  if (sessionStorage.getItem('discoveryHubActive') === 'true') {
    // Small delay to ensure DOM is fully ready
    setTimeout(() => {
      const showStatsBtn = document.getElementById('show-stats');
      if (showStatsBtn) {
        showStatsBtn.click(); // This will trigger showDiscoveryHighlights
      }
    }, 100);
  }
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
                <div class="spotlight-description">Streaming for 5+ hours straight - impressive dedication!</div>
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
            <div class="spotlight-item">
              <span class="spotlight-icon-large">💎</span>
              <div class="spotlight-details">
                <div class="spotlight-name">HIDDEN GEMS</div>
                <div class="spotlight-description">Established models (10k+ followers) with lower current viewers - discovery opportunities</div>
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