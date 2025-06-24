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
            break 2;
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

// Treat as offline if no embed
if(empty($model['iframe_embed'])) $model_online = false;
// If offline, force current_show to "offline"
if (!$model_online) $model['current_show'] = 'offline';

// ----- SEO meta -----
$genders = array('f'=>'Female','m'=>'Male','c'=>'Couple','t'=>'Trans');
$gender_label = isset($genders[$model['gender']]) ? $genders[$model['gender']] : ucfirst($model['gender']);
$title_tags = '';
if (!empty($model['tags'])) {
    $title_tags = ' – #' . implode(' #', array_slice($model['tags'], 0, 3));
}
$meta_title = $model['username'] . " - $gender_label Live Cam$title_tags | " . ($config['site_name'] ?? 'Live Cams');
$meta_desc = !empty($model['room_subject']) ? $model['room_subject'] : ("Watch {$model['username']} streaming live now.");

function chaturbate_whitelabel_replace($html, $wldomain) {
    if (!$wldomain || $wldomain === "chaturbate.com") return $html;
    return preg_replace_callback(
        '#(https?:)?//(www\.)?chaturbate\.com#i',
        function($matches) use ($wldomain) {
            return ($matches[1] ? $matches[1] : 'https:') . '//' . $wldomain;
        },
        $html
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
$this_gender_color = $gender_colors[$model['gender']] ?? $pri;
$iframe_height = is_mobile_device() ? '425px' : '600px';

include('templates/header.php');
?>
<style>
:root {
  --primary-color: <?=$pri?>;
  --gender-color: <?=$this_gender_color?>;
}
/* -- your existing CSS here, as in your code -- */
body { background: #f7f8fa; }
.model-profile-main {
  position: relative;
  width: 98vw;
  max-width: 1300px;
  min-width: 320px;
  margin: 42px auto 0 auto;
  z-index: 10;
  padding-bottom: 34px;
}
.model-profile-panel {
  width: 100%;
  border-radius: 22px;
  background: #fff;
  box-shadow: 0 5px 36px #8b91ae21;
  padding: 28px 30px 18px 30px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1vw;
  box-sizing: border-box;
}
.model-header-flex {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 22px;
  margin-bottom: 5px;
}
.model-pp-avatar {
  flex: 0 0 auto;
  display: flex; flex-direction: column; align-items: center;
}
.model-pp-avatar img {
  width: 90px; height: 90px; border-radius: 11px;
  background: #f1f3f8;
  object-fit: cover;
  border: 3px solid #fff;
  box-shadow: 0 2px 14px #7b7a9c13;
}
.model-pp-summary {
  flex: 1 1 480px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  min-width: 0;
}
.model-pp-row {
  display: flex; align-items: center; gap: 11px 13px; flex-wrap: wrap; min-width: 0;
}
.model-pp-username {
  font-size: 1.7em;
  font-weight: 800;
  color: var(--primary-color, #ffa927);
  letter-spacing: .01em;
  margin-right: 8px;
  line-height: 1.13;
  max-width: 40vw;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.model-badge, .model-gender-badge, .model-age-badge {
  border-radius: 13px; padding: 4px 10px; font-weight: 700; font-size: 1em; margin-right: 5px;
  box-shadow:0 1px 5px #97979724; vertical-align: middle;
}
.model-age-badge { background: #ffd5dc; color: #e02c48;}
.model-gender-badge { background: var(--gender-color,#ded3ff); color: #fff;}
.model-badge.hd { background:#13addb !important;color:#fff;}
.model-badge.new { background:#a5e751 !important;color:#234002;}
.model-country-flag {
  width: 22px; height: 15px; border-radius: 3px; border:1px solid #f3f3f4;
  background: #f6f8fb; vertical-align:bottom;
  object-fit:cover;
}
.model-pp-stats {
  display: flex; align-items: center; gap: 14px 13px; font-size: .98em;
  margin: 6px 0 8px 0; flex-wrap: wrap;
}
.stat-pill {
  display: flex; align-items: center;
  background: #f8fafd;
  padding: 3px 11px;
  border-radius: 14px;
  font-weight: 600;
  color: #2b3552;
  box-shadow: 0 1px 5px #bfb3e820;
  font-size: 1em;
  gap: 6px;
}
.stat-pill .icon {
  font-size: 1.1em;
  opacity: .84;
}
.cb-cam-iframe {
  width: 100% !important;
  min-width: 0 !important;
  border-radius: 8px;
  outline: none;
  border: none !important;
  background: #151d29;
  box-shadow: none;
  overflow: hidden;
  scrollbar-width: none !important;
  display: block;
}
.cb-cam-iframe::-webkit-scrollbar {display:none;}
.model-fallback-msg {
  display:none; color:#e44; margin:18px 0 8px 0; text-align:center; font-size:1.08em;
}
.model-meta-wrap {
  width: 100%;
  background: #f7fafd;
  border-radius: 13px;
  box-shadow: 0 2px 8px #acc5e022;
  padding: 19px 16px 13px 16px;
  margin:0;
  display: flex; flex-direction: row; flex-wrap:wrap; gap: 0 35px;
  box-sizing: border-box;
  overflow: visible;
  align-items: flex-start;
}
.model-meta-col {
  flex: 1 1 220px;
  min-width:160px;
  max-width:400px;
  box-sizing: border-box;
}
.model-meta-item {
  margin-bottom: 8px;
  font-size: 1.09em;
  color: #273146;
  font-weight: 400;
}
.model-meta-item b {
  color: #4263a5;
  font-weight: 600;
  font-size: 1em;
  margin-right: 7px;
  letter-spacing: 0.01em;
  white-space: nowrap;
}
.room-topic-value {
  white-space: pre-line;
  word-break: break-word;
  display: inline;
}
.model-tags {
  display: inline;
}
.model-tag-chip {
  display: inline-block;
  background: #e7f1ff;
  color: #1866c2;
  font-size: .99em;
  border-radius: 11px;
  padding: 2px 9px;
  font-weight: 500;
  margin-right: 4px;
  margin-bottom: 1px;
}
@media (max-width: 900px) {
  .model-profile-main {max-width:99vw;}
  .model-profile-panel{padding:7px 1vw 7px 1vw;}
  .model-meta-wrap{gap:14px;}
  .model-meta-col{max-width:100%;}
}
@media(max-width:640px){
  .model-pp-username {font-size:1.1em;max-width:90vw;}
  .model-meta-wrap{flex-direction:column;gap:8px;}
  .model-meta-col{min-width:0;}
}
</style>
<div class="model-profile-main">
  <div class="model-profile-panel">
    <div class="model-header-flex">
      <div class="model-pp-avatar">
        <img src="<?=htmlspecialchars($model['image_url'])?>" alt="<?=htmlspecialchars($model['username'])?>">
      </div>
      <div class="model-pp-summary">
        <div class="model-pp-row">
          <span class="model-pp-username"><?=htmlspecialchars($model['username'])?></span>
          <span class="model-age-badge"><?= intval($model['age']) ?> yrs</span>
          <?php if(!empty($model['country'])): ?>
            <img class="model-country-flag"
                src="https://flagcdn.com/<?= strtolower(strlen($model['country'])===2 ? $model['country'] : substr($model['country'],0,2)) ?>.svg"
                onerror="this.style.display='none'"
                alt="<?=htmlspecialchars($model['country'])?>">
          <?php endif; ?>
          <span class="model-gender-badge"><?=ucfirst($gender_label)?></span>
          <?php if($model['is_hd']): ?>
            <span class="model-badge hd">HD</span>
          <?php endif; ?>
          <?php if($model['is_new']): ?>
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
              echo $show_map[$model['current_show']] ?? ucfirst($model['current_show']);
            ?>
          </span>
        </div>
      </div>
    </div>
    <?php if($model_online): ?>
    <?=ensure_iframe_fullscreen(chaturbate_whitelabel_replace($model['iframe_embed'], $config['whitelabel_domain']), $iframe_height)?>
    <div class="model-fallback-msg" id="cb-embed-fallback">
      The cam video may be blocked by your browser, privacy, or adblocker settings. Try disabling shields or using a different browser if the cam does not display.
    </div>
    <script>
    setTimeout(function() {
      var f=document.querySelector('.cb-cam-iframe');
      if(f) {
        if((!f.contentWindow&&f.offsetHeight<150)||f.offsetHeight===0)
          document.getElementById('cb-embed-fallback').style.display = 'block';
      }
    }, 2600);
    </script>
    <?php endif; ?>
    <div class="model-meta-wrap">
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