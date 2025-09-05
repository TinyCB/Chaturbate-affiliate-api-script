<?php
$no_filters_button = true;
$username = isset($_GET['username']) ? preg_replace('/[^\w\-]/','',explode('?', $_GET['username'])[0]) : '';
if(!$username) {
    $config = include('config.php');
    $meta_title = "Model not found | ".$config['site_name'];
    $meta_desc  = "";
    include('templates/header.php');
    echo "<h2>User not found.</h2>";
    include('templates/footer.php');
    exit;
}
$config = include('config.php');
$back_link = '/';
if (!empty($_GET['gender']) && !is_array($_GET['gender'])) {
    switch ($_GET['gender']) {
        case 'f': $back_link = '/female'; break;
        case 'm': $back_link = '/male'; break;
        case 't': $back_link = '/trans'; break;
        case 'c': $back_link = '/couples'; break;
    }
}
$cache_dir = __DIR__."/cache/";
$regions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
$model = null;
$model_online = false;
$now = time();
$last_online = null;

// 1. Try to find model in online caches
foreach ($regions as $region) {
    $file = $cache_dir . "cams_{$region}.json";
    if (!file_exists($file)) continue;
    $json = json_decode(file_get_contents($file), true);
    if (!$json || !isset($json['results'])) continue;
    foreach ($json['results'] as $m) {
        if(strtolower($m['username']) === strtolower($username)) {
            $model = $m;
            $model_online = true;
            $last_online = $now;
            break 2;
        }
    }
}
// If online, merge in ai_bio from archive if present
if ($model_online && !isset($model['ai_bio'])) {
    $profile_file = $cache_dir . "model_profiles.json";
    if (file_exists($profile_file)) {
        $profiles = json_decode(file_get_contents($profile_file), true);
        if (is_array($profiles)) {
            foreach ($profiles as $arch) {
                if (strtolower($arch['username']) === strtolower($model['username'])) {
                    if (!empty($arch['ai_bio'])) {
                        $model['ai_bio'] = $arch['ai_bio'];
                    }
                    break;
                }
            }
        }
    }
}
// 2. Fallback: Try to load from model_profiles.json if not online
if(!$model) {
    $profile_file = $cache_dir . "model_profiles.json";
    if (file_exists($profile_file)) {
        $profiles = json_decode(file_get_contents($profile_file), true);
        if (is_array($profiles)) {
            foreach ($profiles as $m) {
                if(strtolower($m['username']) === strtolower($username)) {
                    $model = $m;
                    $model_online = false;
                    $last_online = isset($m['last_online']) ? (int)$m['last_online'] : 0;
                    break;
                }
            }
        }
    }
}
if(!$model) {
    header("HTTP/1.0 404 Not Found");
    $meta_title = "Model not found | ".$config['site_name'];
    $meta_desc  = "";
    include('templates/header.php');
    echo "<h2>User not found.</h2>";
    include('templates/footer.php');
    exit;
}

// --- Staged status logic with activity awareness
$days_offline = 9999;
if ($last_online) $days_offline = floor(($now - $last_online) / 86400);

// Get activity metrics
$times_seen = (int)($model['times_seen'] ?? 1);
$days_active = (int)($model['days_active'] ?? 0);

// Check if model should be considered permanently removed
$is_truly_inactive = ($days_offline > 120 && $times_seen < 5 && $days_active < 30);
$is_active_model = ($times_seen >= 10 || $days_active >= 60);

if ($is_truly_inactive) {
    // Only mark as permanently gone if truly inactive
    header("HTTP/1.1 410 Gone");
    $meta_title = "Model permanently removed | " . $config['site_name'];
    $meta_desc = "";
    include('templates/header.php');
    echo "<h2>User permanently removed.</h2>";
    include('templates/footer.php');
    exit;
} elseif ($days_offline > 90 && !$is_active_model) {
    // Show soft error for moderately active models offline > 90 days
    $soft_error = true;
} else {
    $soft_error = false;
}

// Treat as offline if no embed
if(empty($model['iframe_embed_revshare'])) $model_online = false;
if (!$model_online) $model['current_show'] = 'offline';

// ----- SEO meta -----
$genders = array('f'=>'Female','m'=>'Male','c'=>'Couple','t'=>'Trans');
$gender_label = isset($genders[$model['gender'] ?? null]) ? $genders[$model['gender']] : ucfirst($model['gender'] ?? '');
$title_tags = '';
if (!empty($model['tags'])) {
    $title_tags = ' – #' . implode(' #', array_slice($model['tags'], 0, 3));
}
$meta_title = ($model['username'] ?? 'Model') . " - $gender_label Live Cam$title_tags | " . ($config['site_name'] ?? 'Live Cams');
$meta_desc = !empty($model['room_subject']) ? $model['room_subject'] : ("Watch ".($model['username'] ?? 'model')." streaming live now.");

function chaturbate_whitelabel_replace($html, $wldomain) {
    if (!$wldomain || $wldomain === "chaturbate.com") return $html;
    return preg_replace_callback(
        '#(https?:)?//(www\.)?chaturbate\.com#i',
        function($matches) use ($wldomain) {
            return ($matches[1] ? $matches[1] : 'https:') . '//' . $wldomain;
        }, $html
    );
}
function is_mobile_device() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua));
}
function ensure_iframe_fullscreen($iframe_html, $height) {
    $iframe_html = preg_replace('/<iframe(?![^>]+allowfullscreen)/i', '<iframe allowfullscreen', $iframe_html);
    $iframe_html = preg_replace('/<iframe(?![^>]+allow="[^"]*fullscreen[^"]*")/i', '<iframe allow="autoplay; fullscreen"', $iframe_html);
    $iframe_html = preg_replace('/width\s*=\s*["\']?\d+["\']?/i', 'width="100%"', $iframe_html);
    $iframe_html = preg_replace('/height\s*=\s*["\']?\d+["\']?/i', 'height="'.$height.'"', $iframe_html);
    $iframe_html = preg_replace('/style=(["\']).*?\1/i', '', $iframe_html);
    $iframe_html = preg_replace('/<iframe/i', '<iframe class="cb-cam-iframe" scrolling="no" style="overflow:hidden;"', $iframe_html);
    return $iframe_html;
}
function human_birthday($date) {
    $t = strtotime($date);
    if(!$t) return htmlspecialchars($date);
    return date("F j, Y", $t);
}
$pri = htmlspecialchars($config['primary_color'] ?? '#ffa927');
$gender_colors = [
    'f' => '#c94ac8',
    'm' => '#4283ec',
    't' => '#a46ef7',
    'c' => '#ed7a38',
];
$this_gender_color = $gender_colors[$model['gender'] ?? 'f'] ?? $pri;
// More responsive iframe height calculation
function get_responsive_iframe_height() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Small mobile devices
    if (preg_match('/iPhone|iPod/i', $ua)) {
        return '300px';
    }
    
    // Android phones
    if (preg_match('/Android.*Mobile/i', $ua)) {
        return '320px';
    }
    
    // Tablets
    if (preg_match('/iPad|Android(?!.*Mobile)/i', $ua)) {
        return '450px';
    }
    
    // General mobile fallback
    if (is_mobile_device()) {
        return '380px';
    }
    
    // Desktop
    return '600px';
}

$iframe_height = get_responsive_iframe_height();
include('templates/header.php');

function markdown_links_to_html($text) {
    return preg_replace_callback(
        '/\[(.*?)\]\((https?:\/\/[^\s\)]+)\)/i',
        function($m) {
            return '<a href="' . htmlspecialchars($m[2]) . '" target="_blank" rel="noopener">' . htmlspecialchars($m[1]) . '</a>';
        },
        $text
    );
}
?>
<style>
:root {
  --primary-color: <?=$pri?>;
  --gender-color: <?=$this_gender_color?>;
}
/* -- your existing CSS here, as in your code -- */
body { 
  background: #f7f8fa; 
  font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
}

.model-profile-main {
  position: relative;
  width: 100%;
  max-width: 1200px;
  margin: 24px auto 0 auto;
  padding: 0 12px 24px 12px;
  box-sizing: border-box;
}

.model-profile-panel {
  width: 100%;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 20px;
  box-sizing: border-box;
  overflow: hidden;
}
.model-header-flex {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  gap: 20px;
  width: 100%;
}

.model-pp-avatar {
  flex: 0 0 auto;
  display: flex; 
  flex-direction: column; 
  align-items: center;
}

.model-pp-avatar img {
  width: 100px; 
  height: 100px; 
  border-radius: 12px;
  background: #f1f3f8;
  object-fit: cover;
  border: 3px solid #fff;
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.model-pp-summary {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  min-width: 0;
  width: 100%;
}

.model-pp-row {
  display: flex; 
  align-items: center; 
  gap: 8px 12px; 
  flex-wrap: wrap; 
  min-width: 0;
  width: 100%;
}

.model-pp-username {
  font-size: 1.8em;
  font-weight: 700;
  color: var(--primary-color, #ffa927);
  letter-spacing: 0;
  margin-right: 12px;
  line-height: 1.2;
  word-break: break-word;
  flex: 1 1 auto;
  min-width: 0;
}
.model-badge, .model-gender-badge, .model-age-badge {
  border-radius: 8px; 
  padding: 6px 12px; 
  font-weight: 600; 
  font-size: 0.9em; 
  margin: 2px 4px 2px 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
  vertical-align: middle;
  white-space: nowrap;
}

.model-age-badge { background: #ffd5dc; color: #e02c48;}
.model-gender-badge { background: var(--gender-color,#ded3ff); color: #fff;}
.model-badge.hd { background:#13addb !important;color:#fff;}
.model-badge.new { background:#a5e751 !important;color:#234002;}

.model-country-flag {
  width: 24px; 
  height: 16px; 
  border-radius: 4px; 
  border: 1px solid #e0e0e0;
  background: #f6f8fb; 
  vertical-align: middle;
  object-fit: cover;
  margin-left: 4px;
}

.model-pp-stats {
  display: flex; 
  align-items: center; 
  gap: 12px 16px; 
  font-size: 1em;
  margin: 12px 0 8px 0; 
  flex-wrap: wrap;
  width: 100%;
}
.stat-pill {
  display: flex; 
  align-items: center;
  background: #f8fafd;
  padding: 6px 12px;
  border-radius: 12px;
  font-weight: 500;
  color: #2b3552;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  font-size: 0.9em;
  gap: 6px;
  white-space: nowrap;
}

.stat-pill .icon {
  font-size: 1em;
  opacity: 0.8;
}

.cb-cam-iframe {
  width: 100% !important;
  min-width: 0 !important;
  border-radius: 12px;
  outline: none;
  border: none !important;
  background: #151d29;
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
  overflow: hidden;
  scrollbar-width: none !important;
  display: block;
  margin: 8px 0;
}

.cb-cam-iframe::-webkit-scrollbar {display:none;}

.model-fallback-msg {
  display: none; 
  color: #e44; 
  margin: 16px 0 8px 0; 
  text-align: center; 
  font-size: 1em;
  padding: 12px;
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 8px;
}
.model-meta-wrap {
  width: 100%;
  background: #f7fafd;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  padding: 20px;
  margin: 0;
  display: flex; 
  flex-direction: row; 
  flex-wrap: wrap; 
  gap: 24px;
  box-sizing: border-box;
  overflow: visible;
  align-items: flex-start;
}

.model-meta-col {
  flex: 1 1 250px;
  min-width: 200px;
  box-sizing: border-box;
}

.model-meta-item {
  margin-bottom: 12px;
  font-size: 1em;
  color: #273146;
  font-weight: 400;
  line-height: 1.5;
}

.model-meta-item b {
  color: #4263a5;
  font-weight: 600;
  font-size: 1em;
  margin-right: 8px;
  letter-spacing: 0;
  white-space: nowrap;
  display: inline-block;
}
.room-topic-value {
  white-space: pre-line;
  word-break: break-word;
  display: inline;
}

.model-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 4px;
}

.model-tag-chip {
  display: inline-block;
  background: #e7f1ff;
  color: #1866c2;
  font-size: 0.85em;
  border-radius: 8px;
  padding: 4px 8px;
  font-weight: 500;
  margin: 0;
  white-space: nowrap;
  transition: background 0.2s ease;
}

.model-tag-chip:hover {
  background: #d4e9ff;
}
/* Tablet Styles */
@media (max-width: 768px) {
  .model-profile-main {
    margin-top: 16px;
    padding: 0 8px 20px 8px;
  }
  
  .model-profile-panel {
    padding: 20px 16px;
    border-radius: 12px;
    gap: 16px;
  }
  
  .model-header-flex {
    gap: 16px;
  }
  
  .model-pp-avatar img {
    width: 80px;
    height: 80px;
  }
  
  .model-pp-username {
    font-size: 1.6em;
  }
  
  .model-meta-wrap {
    padding: 16px;
    gap: 16px;
  }
  
  .model-meta-col {
    flex: 1 1 100%;
    min-width: 0;
  }
}

/* Mobile Styles */
@media (max-width: 600px) {
  .model-profile-main {
    margin-top: 8px;
    padding: 0 4px 16px 4px;
  }
  
  .model-profile-panel {
    padding: 16px 12px;
    gap: 12px;
  }
  
  .model-header-flex {
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-align: center;
  }
  
  .model-pp-avatar {
    align-items: center;
  }
  
  .model-pp-avatar img {
    width: 90px;
    height: 90px;
  }
  
  .model-pp-summary {
    align-items: center;
    text-align: center;
    width: 100%;
  }
  
  .model-pp-username {
    font-size: 1.4em;
    text-align: center;
    width: 100%;
    max-width: none;
  }
  
  .model-pp-row {
    justify-content: center;
  }
  
  .model-pp-stats {
    justify-content: center;
    gap: 8px;
  }
  
  .stat-pill {
    font-size: 0.85em;
    padding: 4px 8px;
  }
  
  .model-meta-wrap {
    flex-direction: column;
    padding: 12px;
    gap: 12px;
  }
  
  .model-meta-col {
    min-width: 0;
  }
  
  .cb-cam-iframe {
    height: 320px !important;
    border-radius: 8px;
  }
}

/* Small Mobile Styles */
@media (max-width: 400px) {
  .model-profile-panel {
    padding: 12px 8px;
  }
  
  .model-pp-username {
    font-size: 1.2em;
  }
  
  .model-pp-avatar img {
    width: 70px;
    height: 70px;
  }
  
  .model-meta-wrap {
    padding: 8px;
  }
  
  .cb-cam-iframe {
    height: 280px !important;
  }
  
  .model-badge, .model-gender-badge, .model-age-badge {
    font-size: 0.8em;
    padding: 4px 8px;
  }
}
.model-written-bio {
  font-style: italic;
  color: #594f6b;
  background: #f6f7fc;
  padding: 12px 16px;
  margin: 0 0 16px 0;
  border-radius: 10px;
  width: 100%;
  text-align: left;
  box-sizing: border-box;
  border-left: 4px solid #d4e9ff;
  line-height: 1.6;
}

@media (max-width: 600px) {
  .model-written-bio {
    padding: 10px 12px;
    margin: 0 0 12px 0;
    text-align: left;
  }
}
</style>
<div class="model-profile-main">
  <div class="model-profile-panel">
    <?php if (!empty($soft_error)): ?>
      <div style='background:#fffbe2;color:#7b6800;padding:18px;font-size:1.13em;margin:26px 0 18px 0;border-radius:7px;text-align:center;'>
        This model has been inactive for a while.<br>
        This profile is temporarily unavailable, but may return soon.
      </div>
    <?php endif; ?>
    <div class="model-header-flex">
    <div class="model-pp-avatar">
      <?php if (!$model_online): ?>
        <img src="/assets/offline.png" alt="Offline Model Avatar">
      <?php else: ?>
        <img src="<?=htmlspecialchars($model['image_url'] ?? '')?>" alt="<?=htmlspecialchars($model['username'] ?? '')?>">
      <?php endif; ?>
    </div>
      <div class="model-pp-summary">
        <div class="model-pp-row">
          <span class="model-pp-username"><?=htmlspecialchars($model['username'] ?? '')?></span>
          <span class="model-age-badge"><?= intval($model['age'] ?? 0) ?> yrs</span>
          <?php if(!empty($model['country'])): ?>
            <img class="model-country-flag"
                src="https://flagcdn.com/<?= strtolower(strlen($model['country'])===2 ? $model['country'] : substr($model['country'],0,2)) ?>.svg"
                onerror="this.style.display='none'"
                alt="<?=htmlspecialchars($model['country'])?>">
          <?php endif; ?>
          <span class="model-gender-badge"><?=ucfirst($gender_label)?></span>
          <?php if(!empty($model['is_hd'])): ?>
            <span class="model-badge hd">HD</span>
          <?php endif; ?>
          <?php if(!empty($model['is_new'])): ?>
            <span class="model-badge new">NEW</span>
          <?php endif; ?>
          <?php if(!$model_online): ?>
            <span class="model-badge" style="background:#e5e5e5; color:#6d6d6d;">OFFLINE</span>
          <?php endif; ?>
        </div>
        <div class="model-pp-stats">
          <?php if($model_online && isset($model['num_users'])): ?>
            <span class="stat-pill"><span class="icon">&#128065;</span> <?=intval($model['num_users'])?> Viewers</span>
          <?php endif; ?>
          <?php if(isset($model['num_followers'])): ?>
            <span class="stat-pill"><span class="icon">&#128100;</span><?=intval($model['num_followers'])?> Followers</span>
          <?php endif; ?>
          <?php if($model_online && isset($model['seconds_online'])): ?>
            <span class="stat-pill"><span class="icon">&#9201;</span>
            <?php $h = floor($model['seconds_online']/3600); $m = floor(($model['seconds_online']%3600)/60);
              echo ($h>0?"$h hr ":'')."$m min";
            ?> Online</span>
          <?php endif; ?>
          <span class="stat-pill"><b>Show:</b>
            <?php $show_map=['public'=>'Public','private'=>'Private','group'=>'Group','away'=>'Away','offline'=>'Offline'];
              echo $show_map[$model['current_show'] ?? "offline"] ?? ucfirst($model['current_show'] ?? "offline");
            ?>
          </span>
        </div>
      </div>
    </div>
    <?php if($model_online): ?>
    <?=ensure_iframe_fullscreen(chaturbate_whitelabel_replace($model['iframe_embed_revshare'], $config['whitelabel_domain']), $iframe_height)?>
    <div class="model-fallback-msg" id="cb-embed-fallback">
      The cam video may be blocked by your browser, privacy, or adblocker settings. Try disabling shields or using a different browser if the cam does not display.
    </div>
    <script>
    setTimeout(function() {
      var f=document.querySelector('.cb-cam-iframe');
      if(f && ((!f.contentWindow&&f.offsetHeight<150)||f.offsetHeight===0))
          document.getElementById('cb-embed-fallback').style.display = 'block';
    }, 2600);
    </script>
    <?php endif; ?>
	   <div class="model-meta-wrap">
		<?php if (!empty($model['ai_bio'])): ?>
		  <div class="model-written-bio" style="
			font-style:italic;
			color:#594f6b;
			background:#f6f7fc;
			padding:8px 0 7px 0;              /* no side padding */
			margin:0 0 12px 0;
			border-radius:8px;
			width:100%;
			text-align:left;
			box-sizing:border-box;
		  ">
			<?=markdown_links_to_html($model['ai_bio'])?>
		  </div>
		<?php endif; ?>
      <div class="model-meta-col">
        <?php if(!empty($model['location'])): ?>
          <div class="model-meta-item">
            <b>Location:</b> <?=htmlspecialchars($model['location'])?>
          </div>
        <?php endif; ?>
        <?php if(!empty($model['spoken_languages'])): ?>
          <div class="model-meta-item">
            <b>Language:</b> <?=htmlspecialchars($model['spoken_languages'])?>
          </div>
        <?php endif; ?>
        <?php if(!empty($model['birthday'])): ?>
          <div class="model-meta-item">
            <b>Birthday:</b> <?=human_birthday($model['birthday'])?>
          </div>
        <?php endif; ?>
      </div>
      <div class="model-meta-col">
        <?php if(!empty($model['room_subject'])): ?>
          <div class="model-meta-item">
            <b>Room Topic:</b>
            <span class="room-topic-value"><?=preg_replace('/<br\s*\/?>/i', ' ', $model['room_subject'])?></span>
          </div>
        <?php endif; ?>
        <?php if(!empty($model['tags'])): ?>
          <div class="model-meta-item">
            <b>Tags:</b>
            <?php foreach(array_slice($model['tags'],0,18) as $t): ?>
              <span class="model-tag-chip">#<?=htmlspecialchars($t)?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include('templates/footer.php'); ?>