<?php
$no_filters_button = true;

// --- Retrieve username from rewritten URL ---
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

// --- Build 'Back to all cams' (PRETTY URL, NO QUERY) ---
$back_link = '/';
$pretty_gender = '';
if (!empty($_GET['gender']) && !is_array($_GET['gender'])) {
    switch ($_GET['gender']) {
        case 'f': $back_link = '/girls'; break;
        case 'm': $back_link = '/guys'; break;
        case 't': $back_link = '/trans'; break;
        case 'c': $back_link = '/couples'; break;
        default:  $back_link = '/';
    }
}

// --- Read model info from cache, not live api ---
$cache_dir = __DIR__."/cache/";
$regions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
$model = null;
foreach ($regions as $region) {
    $file = $cache_dir . "cams_{$region}.json";
    if (!file_exists($file)) continue;
    $json = json_decode(file_get_contents($file), true);
    if (!$json || !isset($json['results'])) continue;
    foreach ($json['results'] as $m) {
        if(strtolower($m['username']) === strtolower($username)) {
            $model = $m;
            break 2; // found, break both loops
        }
    }
}

// --- Fallback: not found or not online ---
if(!$model || empty($model['iframe_embed'])) {
    $meta_title = "Model offline | ".$config['site_name'];
    $meta_desc = "";
    include('templates/header.php');
    echo "<h2>Model not online.</h2>";
    include('templates/footer.php');
    exit;
}

// --- SEO meta: Name, gender, tags
$genders = array('f'=>'Female','m'=>'Male','c'=>'Couple','t'=>'Trans');
$gender_label = isset($genders[$model['gender']]) ? $genders[$model['gender']] : ucfirst($model['gender']);
$title_tags = '';
if (!empty($model['tags'])) {
    $title_tags = ' – #' . implode(' #', array_slice($model['tags'], 0, 3));
}
$meta_title = $model['username'] . " - $gender_label Live Cam$title_tags | " . ($config['site_name'] ?? 'Live Cams');
$meta_desc = !empty($model['room_subject'])
    ? $model['room_subject']
    : ("Watch {$model['username']} streaming live now.");

// --- Helper: Replace Chaturbate domain for whitelabel ---
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

// --- Helper: fullscreen for iframe ---
function ensure_iframe_fullscreen($iframe_html) {
    $iframe_html = preg_replace('/<iframe(?![^>]+allowfullscreen)/i', '<iframe allowfullscreen', $iframe_html);
    $iframe_html = preg_replace('/<iframe(?![^>]+allow="[^"]*fullscreen[^"]*")/i', '<iframe allow="autoplay; fullscreen"', $iframe_html);
    $iframe_html = preg_replace('/width\s*=\s*["\']?\d+["\']?/i', 'width="100%"', $iframe_html);
    $iframe_html = preg_replace('/height\s*=\s*["\']?\d+["\']?/i', 'height="660px"', $iframe_html);
    $iframe_html = preg_replace('/style=(["\']).*?\1/i', '', $iframe_html); // strip inline style
    $iframe_html = preg_replace('/<iframe/i', '<iframe class="cb-cam-iframe"', $iframe_html);
    return $iframe_html;
}

// include header.php, which uses $meta_title/$meta_desc/$back_link/$no_filters_button
include('templates/header.php');
?>
<style>
.profile-wide {
  max-width: 1200px;
  margin: 32px auto 24px auto;
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 2px 32px rgba(22,44,99,0.11);
  padding: 0 0 32px 0;
  width: 100%;
}
.model-player { margin-top: 0; }
.player-header {
  display: flex; align-items: center; gap: 28px; margin-bottom: 18px; flex-wrap: wrap; padding: 0 28px;
}
.profile-avatar img {
  width: 110px; height: 110px; border-radius: 50%;
  border: 2px solid #e2ebfb; box-shadow:0 2px 12px #0002; object-fit:cover; background:#f3f6fa;
}
.player-title { flex: 1; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.username-profile { font-size: 2.1em; color: #194fa0; font-weight: 600; margin-right: 6px; }
.age-badge { font-size: 1.08em; background: #ffaad1; color: #fff; font-weight: 700; border-radius: 13px; padding: 2px 15px; margin-right: 7px; }
.model-gender { font-size: 1.09em; color: #475570; background: #e7eaf3; padding: 2px 13px; border-radius: 12px; margin-right: 4px; }
.flag {
  margin: 0 5px; height: 18px; width:24px; display:inline-block; border-radius:2.5px; vertical-align: middle; border:1px solid #e1e7f1; background:#f5faff;
}
.profile-badge {padding:3px 10px; border-radius:12px; font-size:1em; font-weight:bold; margin:0 3px; color:#fff; letter-spacing:0.4px; vertical-align:middle;}
.profile-badge.hd-badge { background: #27b7e7; }
.profile-badge.new-badge { background: #afe150; color: #3d522b; }
.player-iframe-outer {
  width:100%;
  display: flex;
  justify-content: center;
  align-items: center;
}
.cb-cam-iframe, .player-iframe-outer iframe {
  width: 100% !important;
  max-width: 1150px;
  min-width: 320px;
  min-height: 480px;
  height: 660px;
  aspect-ratio: 16 / 9;
  background: #1a1422;
  border-radius: 16px;
  display: block;
  box-shadow: 0 3px 28px #0002;
  border: none;
}
.model-profile-section {
  width: 100%; max-width:1150px;
  margin: 32px auto 7px auto;
  background: #f7fbff;
  border-radius:14px;
  padding: 32px 44px 25px 44px;
  box-shadow: 0 .5px 2px #b0d2fa22;
  min-width: 0;
}
.mp-row { font-size:1.1em; color:#2b355a; margin-bottom: 12px; display:flex; flex-wrap:wrap; align-items:center; gap: 8px;}
.prof-label { color: #5a6e8c; font-weight:600; margin-right:3px;}
.prof-value { color: #223361; }
.mp-tag { background:#e6eef8; color:#2361aa; font-size:.98em; border-radius:13px; padding:2px 12px; margin-right:4px; margin-bottom:1.5px;}
.back-link { margin-top:18px; font-size: 1.04em; color: var(--primary-color); text-decoration: underline; }
@media (max-width: 1300px) {
  .profile-wide { max-width:98vw; }
  .model-profile-section { max-width:98vw; }
  .player-iframe-outer iframe, .cb-cam-iframe {max-width:99vw;}
}
@media (max-width: 900px) {
  .player-header { flex-direction:column; align-items:flex-start; gap:14px; }
  .profile-avatar img { width:70px; height:70px; }
  .model-profile-section { padding: 17px 2vw 9px 2vw;}
  .player-iframe-outer iframe, .cb-cam-iframe { height: 44vw; min-height:210px; }
}
@media (max-width: 650px) {
  .profile-wide { border-radius: 0; padding:0;}
  .model-profile-section { border-radius: 0;padding:8px 1vw 8px 1vw;}
  .player-iframe-outer iframe, .cb-cam-iframe {aspect-ratio:4/5;height: 220px;}
}
</style>
<div class="profile-wide">
  <div class="model-player">
    <div class="player-header">
      <div class="profile-avatar">
        <img src="<?=htmlspecialchars($model['image_url'])?>" alt="<?=htmlspecialchars($model['username'])?>">
      </div>
      <div class="player-title">
        <span class="username-profile"><?=htmlspecialchars($model['username'])?></span>
        <span class="age-badge"><?= isset($model['age']) ? intval($model['age']) . ' yrs' : '' ?></span>
        <?= (!empty($model['country']))
            ? '<img class="flag" src="https://flagcdn.com/24x18/'.strtolower($model['country']).'.png"
                 alt="'.htmlspecialchars($model['country']).'" style="margin-bottom:-2px;">'
            : '' ?>
        <span class="model-gender"><?= $gender_label ?></span>
        <?php if($model['is_hd']): ?>
          <span class="profile-badge hd-badge">HD</span>
        <?php endif; ?>
        <?php if($model['is_new']): ?>
          <span class="profile-badge new-badge">NEW</span>
        <?php endif; ?>
      </div>
    </div>
    <div class="player-iframe-outer">
      <?= ensure_iframe_fullscreen(
            chaturbate_whitelabel_replace($model['iframe_embed'], $config['whitelabel_domain'])
          )
      ?>
      <div id="cb-embed-fallback" style="display:none; color:#e44; margin:18px 0 8px 0; text-align:center; font-size:1.09em;">
        The cam video may be blocked by your browser, privacy, or adblocker settings. Try disabling shields or using a different browser if the cam does not display.
      </div>
      <script>
      setTimeout(function() {
        var frame = document.querySelector('.cb-cam-iframe');
        if (frame) {
          if (
            (!frame.contentWindow && frame.offsetHeight < 150) ||
            frame.offsetHeight === 0
          ) {
            document.getElementById('cb-embed-fallback').style.display = 'block';
          }
        }
      }, 3500);
      </script>
    </div>
  </div>
  <div class="model-profile-section">
    <div class="mp-row">
      <span class="prof-label">Viewers:</span> <?=intval($model['num_users'])?>
      <span class="prof-label" style="margin-left:18px;">Followers:</span> <?= isset($model['num_followers']) ? (int)$model['num_followers'] : '' ?>
      <span class="prof-label" style="margin-left:18px;">Show:</span>
      <?php
        $show_map = [
          'public' => 'Public Show',
          'private' => 'Private Show',
          'group' => 'Group Show',
          'away' => 'Away'
        ];
        $show_status = isset($model['current_show']) && isset($show_map[$model['current_show']])
            ? $show_map[$model['current_show']]
            : ucfirst($model['current_show']);
        echo $show_status;
      ?>
    </div>
    <?php if(!empty($model['room_subject'])): ?>
    <div class="mp-row" style="margin:12px 0 8px 0;">
      <span class="prof-label">Room topic:</span>
      <span class="prof-value"><?= htmlspecialchars($model['room_subject']) ?></span>
    </div>
    <?php endif; ?>
    <?php if(!empty($model['location'])): ?>
    <div class="mp-row">
      <span class="prof-label">Location:</span>
      <span class="prof-value"><?=htmlspecialchars($model['location'])?></span>
    </div>
    <?php endif; ?>
    <?php if(!empty($model['spoken_languages'])): ?>
    <div class="mp-row">
      <span class="prof-label">Languages:</span>
      <span class="prof-value"><?=htmlspecialchars($model['spoken_languages'])?></span>
    </div>
    <?php endif; ?>
    <?php if(!empty($model['birthday'])): ?>
    <div class="mp-row">
      <span class="prof-label">Birthday:</span>
      <span class="prof-value"><?=htmlspecialchars($model['birthday'])?></span>
    </div>
    <?php endif; ?>
    <?php if(!empty($model['tags'])): ?>
    <div class="mp-row">
      <span class="prof-label">Tags:</span>
      <?php foreach($model['tags'] as $t): ?>
        <span class="mp-tag">#<?=htmlspecialchars($t)?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="mp-row">
      <span class="prof-label">Online:</span>
      <?php
        $h = floor($model['seconds_online']/3600);
        $m = floor(($model['seconds_online']%3600)/60);
        echo $h>0 ? "$h hours " : "";
        echo "$m min";
      ?>
    </div>
  </div>
</div>
<?php include('templates/footer.php'); ?>