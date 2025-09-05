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

// Function to analyze online patterns using seconds_online data for accurate duration tracking
function getOnlineActivity($username, $cache_dir) {
    $activity = [];
    // Initialize 7 days x 24 hours grid (0 = never seen online)
    for ($day = 0; $day < 7; $day++) {
        for ($hour = 0; $hour < 24; $hour++) {
            $activity[$day][$hour] = 0;
        }
    }
    
    $regions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
    $username_lower = strtolower($username);
    $sessions = [];
    
    foreach ($regions as $region) {
        $pattern = $cache_dir . "cams_{$region}*.json";
        $files = glob($pattern);
        
        // Also look for archived files (for better historical data)
        $archived_pattern = $cache_dir . "archived/cams_{$region}*.json";
        if (is_dir($cache_dir . "archived/")) {
            $archived_files = glob($archived_pattern);
            $files = array_merge($files, $archived_files);
        }
        
        foreach ($files as $file) {
            if (!file_exists($file)) continue;
            
            $json = json_decode(file_get_contents($file), true);
            if (!$json || !isset($json['results'])) continue;
            
            // Try to extract timestamp from filename first, fallback to file modification time
            $file_time = filemtime($file);
            if (preg_match('/(\d{10,13})/', basename($file), $matches)) {
                $timestamp = $matches[1];
                // Convert to seconds if it's milliseconds
                if (strlen($timestamp) > 10) {
                    $timestamp = intval($timestamp / 1000);
                }
                $file_time = $timestamp;
            }
            
            // Find the model and use seconds_online to calculate session
            foreach ($json['results'] as $model) {
                if (strtolower($model['username']) === $username_lower) {
                    $seconds_online = $model['seconds_online'] ?? 0;
                    
                    if ($seconds_online > 0) {
                        // Calculate when they went online
                        $session_start = $file_time - $seconds_online;
                        $session_end = $file_time; // Snapshot time
                        
                        $sessions[] = [
                            'start' => $session_start,
                            'end' => $session_end,
                            'duration' => $seconds_online
                        ];
                    }
                    break;
                }
            }
        }
    }
    
    // Remove duplicate sessions and merge overlapping ones
    $sessions = array_unique($sessions, SORT_REGULAR);
    
    // Sort sessions by start time
    usort($sessions, function($a, $b) {
        return $a['start'] - $b['start'];
    });
    
    // Fill activity grid based on calculated sessions
    foreach ($sessions as $session) {
        $start_time = $session['start'];
        $end_time = $session['end'];
        
        // Mark each hour in the session as active
        $current_time = $start_time;
        
        // Align to hour boundaries for better accuracy
        $start_hour = floor($start_time / 3600) * 3600;
        $end_hour = ceil($end_time / 3600) * 3600;
        
        for ($time = $start_hour; $time <= $end_hour; $time += 3600) {
            // Only count hours that actually overlap with the session
            if ($time + 3600 >= $start_time && $time <= $end_time) {
                $day_of_week = date('w', $time); // 0 = Sunday
                $hour_of_day = date('G', $time); // 0-23
                
                $activity[$day_of_week][$hour_of_day]++;
            }
        }
    }
    
    // Convert raw counts to activity levels (0-3 scale)
    $max_count = 1;
    for ($day = 0; $day < 7; $day++) {
        for ($hour = 0; $hour < 24; $hour++) {
            if ($activity[$day][$hour] > $max_count) {
                $max_count = $activity[$day][$hour];
            }
        }
    }
    
    // Normalize to 0-3 scale
    $normalized = [];
    for ($day = 0; $day < 7; $day++) {
        for ($hour = 0; $hour < 24; $hour++) {
            if ($max_count > 0) {
                $level = intval(($activity[$day][$hour] / $max_count) * 3);
                $normalized[$day][$hour] = min(3, $level);
            } else {
                $normalized[$day][$hour] = 0;
            }
        }
    }
    
    return $normalized;
}

// Enhanced analytics functions
function getModelInsights($username, $cache_dir) {
    $regions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
    $username_lower = strtolower($username);
    $insights = [
        'peak_viewers' => 0,
        'avg_viewers' => 0,
        'total_snapshots' => 0,
        'consistency_score' => 0,
        'last_seen' => null,
        'session_lengths' => [],
        'popular_tags' => [],
        'room_subjects' => [],
        'peak_hours' => [], // Track viewer counts by hour
        'viewer_trends' => [], // Track viewer trends over time
        'activity_score' => 0, // Overall activity rating
        'engagement_rate' => 0 // Viewers per follower ratio
    ];
    
    $viewer_counts = [];
    $all_tags = [];
    $all_subjects = [];
    $hourly_data = []; // [hour => [viewer_counts...]]
    $trend_data = []; // [timestamp => viewer_count]
    
    foreach ($regions as $region) {
        $pattern = $cache_dir . "cams_{$region}*.json";
        $files = glob($pattern);
        
        foreach ($files as $file) {
            if (!file_exists($file)) continue;
            $json = json_decode(file_get_contents($file), true);
            if (!$json || !isset($json['results'])) continue;
            
            $file_time = filemtime($file);
            
            foreach ($json['results'] as $model) {
                if (strtolower($model['username']) === $username_lower) {
                    $insights['total_snapshots']++;
                    $insights['last_seen'] = max($insights['last_seen'], $file_time);
                    
                    $num_users = $model['num_users'] ?? 0;
                    $viewer_counts[] = $num_users;
                    $insights['peak_viewers'] = max($insights['peak_viewers'], $num_users);
                    
                    // Collect hourly data
                    $hour = date('H', $file_time);
                    if (!isset($hourly_data[$hour])) {
                        $hourly_data[$hour] = [];
                    }
                    $hourly_data[$hour][] = $num_users;
                    
                    // Collect trend data (last 7 days)
                    if ($file_time > (time() - 7 * 24 * 3600)) {
                        $trend_data[$file_time] = $num_users;
                    }
                    
                    $seconds_online = $model['seconds_online'] ?? 0;
                    if ($seconds_online > 0) {
                        $insights['session_lengths'][] = $seconds_online;
                    }
                    
                    if (!empty($model['tags'])) {
                        foreach ($model['tags'] as $tag) {
                            $all_tags[] = strtolower(trim($tag));
                        }
                    }
                    
                    if (!empty($model['room_subject'])) {
                        $all_subjects[] = $model['room_subject'];
                    }
                    
                    break;
                }
            }
        }
    }
    
    if (!empty($viewer_counts)) {
        $insights['avg_viewers'] = intval(array_sum($viewer_counts) / count($viewer_counts));
    }
    
    // Calculate peak hours
    $peak_hours_processed = [];
    foreach ($hourly_data as $hour => $counts) {
        if (!empty($counts)) {
            $avg_for_hour = array_sum($counts) / count($counts);
            $peak_hours_processed[$hour] = $avg_for_hour;
        }
    }
    arsort($peak_hours_processed);
    $insights['peak_hours'] = $peak_hours_processed;
    
    // Process viewer trends (last 7 days)
    ksort($trend_data);
    $insights['viewer_trends'] = $trend_data;
    
    // Calculate activity score (0-100)
    $activity_factors = [];
    $activity_factors[] = min(100, $insights['total_snapshots'] * 2); // Snapshot frequency
    $activity_factors[] = min(100, ($insights['avg_viewers'] / 10) * 100); // Viewer engagement
    if (!empty($insights['session_lengths'])) {
        $avg_session = array_sum($insights['session_lengths']) / count($insights['session_lengths']);
        $activity_factors[] = min(100, ($avg_session / 3600) * 25); // Session length score
    }
    $insights['activity_score'] = !empty($activity_factors) ? intval(array_sum($activity_factors) / count($activity_factors)) : 0;
    
    $insights['consistency_score'] = min(100, ($insights['total_snapshots'] / 10) * 100);
    $insights['popular_tags'] = array_slice(array_count_values($all_tags), 0, 5, true);
    $insights['room_subjects'] = array_slice(array_reverse($all_subjects), 0, 3);
    
    return $insights;
}

function getSimilarModels($username, $cache_dir, $current_model_data) {
    if (!$current_model_data) return [];
    
    $regions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
    $username_lower = strtolower($username);
    $similar_models = [];
    $current_tags = array_map('strtolower', $current_model_data['tags'] ?? []);
    $current_age = $current_model_data['age'] ?? null;
    $current_gender = $current_model_data['gender'] ?? '';
    
    foreach ($regions as $region) {
        $file = $cache_dir . "cams_{$region}.json";
        if (!file_exists($file)) continue;
        
        $json = json_decode(file_get_contents($file), true);
        if (!$json || !isset($json['results'])) continue;
        
        foreach ($json['results'] as $model) {
            if (strtolower($model['username']) === $username_lower) continue;
            
            $similarity_score = 0;
            
            // Gender match (high weight)
            if ($model['gender'] === $current_gender) {
                $similarity_score += 30;
            }
            
            // Age similarity
            if ($current_age && $model['age']) {
                $age_diff = abs($current_age - $model['age']);
                if ($age_diff <= 2) $similarity_score += 25;
                elseif ($age_diff <= 5) $similarity_score += 15;
                elseif ($age_diff <= 10) $similarity_score += 5;
            }
            
            // Tag overlap
            $model_tags = array_map('strtolower', $model['tags'] ?? []);
            $tag_overlap = count(array_intersect($current_tags, $model_tags));
            $similarity_score += $tag_overlap * 10;
            
            // Country match
            if ($model['country'] === $current_model_data['country']) {
                $similarity_score += 10;
            }
            
            if ($similarity_score > 30) {
                $similar_models[] = [
                    'model' => $model,
                    'similarity' => $similarity_score
                ];
            }
        }
    }
    
    usort($similar_models, function($a, $b) {
        return $b['similarity'] - $a['similarity'];
    });
    
    return array_slice($similar_models, 0, 6);
}

function getModelBadges($model_data, $insights) {
    $badges = [];
    
    if ($model_data['is_new'] ?? false) {
        $badges[] = ['type' => 'new', 'label' => 'New Model', 'color' => '#10b981'];
    }
    
    if ($model_data['is_hd'] ?? false) {
        $badges[] = ['type' => 'hd', 'label' => 'HD Quality', 'color' => '#3b82f6'];
    }
    
    $seconds_online = $model_data['seconds_online'] ?? 0;
    if ($seconds_online > 14400) { // 4+ hours
        $badges[] = ['type' => 'marathon', 'label' => 'Marathon Streamer', 'color' => '#f59e0b'];
    }
    
    if ($insights['peak_viewers'] > 1000) {
        $badges[] = ['type' => 'popular', 'label' => 'Popular', 'color' => '#ef4444'];
    }
    
    if ($insights['consistency_score'] > 80) {
        $badges[] = ['type' => 'consistent', 'label' => 'Regular Performer', 'color' => '#8b5cf6'];
    }
    
    return $badges;
}

// Get all enhanced data
$online_activity = getOnlineActivity($username, $cache_dir);
$model_insights = getModelInsights($username, $cache_dir);
$similar_models = getSimilarModels($username, $cache_dir, $model_data);
$model_badges = getModelBadges($model_data, $model_insights);

// Get current time and day for highlighting
$current_hour = intval(date('G')); // 0-23
$current_day = intval(date('w')); // 0 = Sunday, 1 = Monday, etc.

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
    
    <!-- Enhanced Model Badges -->
    <?php if (!empty($model_badges)): ?>
    <div class="model-badges-section">
      <?php foreach ($model_badges as $badge): ?>
        <span class="model-badge-enhanced" style="background: <?= $badge['color'] ?>;">
          <?php if ($badge['type'] === 'new'): ?>🌟<?php endif; ?>
          <?php if ($badge['type'] === 'hd'): ?>📺<?php endif; ?>
          <?php if ($badge['type'] === 'marathon'): ?>⏰<?php endif; ?>
          <?php if ($badge['type'] === 'popular'): ?>🔥<?php endif; ?>
          <?php if ($badge['type'] === 'consistent'): ?>✨<?php endif; ?>
          <?= $badge['label'] ?>
        </span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
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
    
    <!-- Model Info Section -->
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
    
    <!-- Analytics Dashboard Section -->
    <div class="analytics-dashboard">
      <div class="dashboard-header">
        <h2 class="dashboard-title">
          <span class="dashboard-icon">📊</span>
          Performance Analytics
        </h2>
        <p class="dashboard-subtitle">Complete insights based on <?= htmlspecialchars($model['username']) ?>'s activity data</p>
      </div>
      
      <!-- Key Metrics Cards -->
      <div class="metrics-row">
        <div class="metric-card">
          <div class="metric-icon-wrapper">
            <span class="metric-icon">👁️</span>
          </div>
          <div class="metric-content">
            <div class="metric-number"><?= number_format($model_insights['peak_viewers']) ?></div>
            <div class="metric-label">Peak Viewers</div>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-icon-wrapper">
            <span class="metric-icon">📊</span>
          </div>
          <div class="metric-content">
            <div class="metric-number"><?= number_format($model_insights['avg_viewers']) ?></div>
            <div class="metric-label">Avg Viewers</div>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-icon-wrapper">
            <span class="metric-icon">✨</span>
          </div>
          <div class="metric-content">
            <div class="metric-number"><?= $model_insights['consistency_score'] ?>%</div>
            <div class="metric-label">Consistency</div>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-icon-wrapper">
            <span class="metric-icon">📈</span>
          </div>
          <div class="metric-content">
            <div class="metric-number"><?= $model_insights['total_snapshots'] ?></div>
            <div class="metric-label">Total Snapshots</div>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-icon-wrapper">
            <span class="metric-icon">🎯</span>
          </div>
          <div class="metric-content">
            <div class="metric-number"><?= $model_insights['activity_score'] ?>%</div>
            <div class="metric-label">Activity Score</div>
          </div>
        </div>
      </div>
      
      <!-- Two Column Analytics Content -->
      <div class="analytics-content">
        <div class="analytics-left">
          <!-- Session Analytics -->
          <?php if (!empty($model_insights['session_lengths'])): ?>
          <div class="analytics-panel">
            <h3 class="panel-title">
              <span class="panel-icon">⏱️</span>
              Session Patterns
            </h3>
            <div class="session-stats">
              <div class="session-stat">
                <span class="stat-label">Average Session</span>
                <span class="stat-value">
                  <?php 
                  $avg_session = array_sum($model_insights['session_lengths']) / count($model_insights['session_lengths']);
                  $hours = floor($avg_session / 3600);
                  $minutes = floor(($avg_session % 3600) / 60);
                  echo ($hours > 0 ? "$hours hr " : '') . "$minutes min";
                  ?>
                </span>
              </div>
              <div class="session-stat">
                <span class="stat-label">Longest Session</span>
                <span class="stat-value">
                  <?php 
                  $max_session = max($model_insights['session_lengths']);
                  $hours = floor($max_session / 3600);
                  $minutes = floor(($max_session % 3600) / 60);
                  echo ($hours > 0 ? "$hours hr " : '') . "$minutes min";
                  ?>
                </span>
              </div>
            </div>
          </div>
          <?php endif; ?>
          
          <!-- Peak Hours Chart -->
          <?php if (!empty($model_insights['peak_hours'])): ?>
          <div class="analytics-panel">
            <h3 class="panel-title">
              <span class="panel-icon">🕒</span>
              Peak Hours Analysis
            </h3>
            <div class="peak-hours-chart">
              <div class="chart-explanation">Best times to catch <?= htmlspecialchars($model['username']) ?> with high viewer engagement</div>
              <div class="hours-grid">
                <?php 
                $top_hours = array_slice($model_insights['peak_hours'], 0, 6, true);
                foreach ($top_hours as $hour => $avg_viewers): 
                  $hour_24 = str_pad($hour, 2, '0', STR_PAD_LEFT);
                  $hour_12 = date('g A', strtotime($hour_24 . ':00'));
                  $intensity = min(100, ($avg_viewers / max($model_insights['peak_hours'])) * 100);
                ?>
                  <div class="hour-block" data-intensity="<?= $intensity ?>">
                    <div class="hour-time"><?= $hour_12 ?></div>
                    <div class="hour-bar" style="height: <?= $intensity ?>%; background: linear-gradient(to top, var(--primary-color), #ffb347);"></div>
                    <div class="hour-viewers"><?= intval($avg_viewers) ?> avg</div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
          
          <!-- Popular Tags -->
          <?php if (!empty($model_insights['popular_tags'])): ?>
          <div class="analytics-panel">
            <h3 class="panel-title">
              <span class="panel-icon">🏷️</span>
              Popular Tags
            </h3>
            <div class="tags-cloud">
              <?php foreach ($model_insights['popular_tags'] as $tag => $count): ?>
                <span class="tag-item" data-count="<?= $count ?>">
                  #<?= htmlspecialchars($tag) ?>
                  <span class="tag-count"><?= $count ?></span>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
        
        <div class="analytics-right">
          <!-- Enhanced Room Topics History -->
          <?php if (!empty($model_insights['room_subjects'])): ?>
          <div class="analytics-panel">
            <h3 class="panel-title">
              <span class="panel-icon">💬</span>
              Room Topic History
            </h3>
            <div class="topics-timeline">
              <div class="timeline-explanation">Recent room topics and goal progressions</div>
              <?php foreach (array_slice($model_insights['room_subjects'], 0, 5) as $index => $subject): 
                $topic_length = strlen($subject);
                $has_goal = preg_match('/goal|target|\[.*\]/i', $subject);
                $is_completed = preg_match('/completed?|reached?|done/i', $subject);
              ?>
                <div class="topic-timeline-item <?= $is_completed ? 'completed' : ($has_goal ? 'has-goal' : 'normal') ?>">
                  <div class="timeline-marker">
                    <span class="timeline-number"><?= $index + 1 ?></span>
                    <?php if ($is_completed): ?>
                      <span class="timeline-status completed">✅</span>
                    <?php elseif ($has_goal): ?>
                      <span class="timeline-status goal">🎯</span>
                    <?php endif; ?>
                  </div>
                  <div class="timeline-content">
                    <div class="topic-text"><?= htmlspecialchars(substr($subject, 0, 100)) ?><?= strlen($subject) > 100 ? '...' : '' ?></div>
                    <div class="topic-meta">
                      <span class="topic-length"><?= $topic_length ?> chars</span>
                      <?php if ($has_goal): ?>
                        <span class="topic-type goal">Goal-oriented</span>
                      <?php endif; ?>
                      <?php if ($is_completed): ?>
                        <span class="topic-type completed">Completed</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          
          <!-- Language Analysis -->
          <?php if (!empty($model_data['spoken_languages'])): ?>
          <div class="analytics-panel">
            <h3 class="panel-title">
              <span class="panel-icon">🗣️</span>
              Language Breakdown
            </h3>
            <div class="language-analysis">
              <?php 
              $languages = explode(',', $model_data['spoken_languages']);
              $language_count = count($languages);
              foreach ($languages as $index => $lang): 
                $lang = trim($lang);
                $is_primary = $index === 0;
              ?>
                <div class="language-item <?= $is_primary ? 'primary' : 'secondary' ?>">
                  <div class="language-name"><?= htmlspecialchars($lang) ?></div>
                  <div class="language-meta">
                    <?php if ($is_primary): ?>
                      <span class="language-badge primary">Primary</span>
                    <?php else: ?>
                      <span class="language-badge secondary">Secondary</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
              <div class="language-summary">
                <div class="multilingual-status">
                  <?php if ($language_count > 1): ?>
                    <span class="multilingual-badge">🌍 Multilingual</span>
                    <span class="language-count"><?= $language_count ?> languages</span>
                  <?php else: ?>
                    <span class="monolingual-badge">Single Language</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
          
          <!-- Similar Models -->
          <?php if (!empty($similar_models) && count($similar_models) > 0): ?>
          <div class="analytics-panel">
            <h3 class="panel-title">
              <span class="panel-icon">👥</span>
              Similar Models
            </h3>
            <div class="similar-models">
              <?php foreach (array_slice($similar_models, 0, 4) as $similar): ?>
                <a href="/<?= htmlspecialchars($similar['username']) ?>" class="similar-model">
                  <img src="<?= htmlspecialchars($similar['image_url']) ?>" alt="<?= htmlspecialchars($similar['username']) ?>" class="similar-avatar">
                  <div class="similar-details">
                    <div class="similar-name"><?= htmlspecialchars($similar['username']) ?></div>
                    <div class="similar-info"><?= $similar['age'] ?>yr • <?= ucfirst($similar['gender']) ?></div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    </div>
    
    <!-- Analytics and Heat Map Section -->
    <div class="model-full-width-section">
      <!-- Online Time Tracker Heat Map -->
      <div class="online-time-tracker">
        <div class="heatmap-header">
          <div class="heatmap-title">
            <span class="heatmap-icon">📊</span>
            <h3>Weekly Activity Pattern</h3>
            <p class="heatmap-subtitle">Typical online hours based on recent activity</p>
          </div>
        </div>
      
      <div class="heatmap-container">
        <!-- Full-width responsive SVG container -->
        <div class="heatmap-chart">
          <!-- Hour labels across the top -->
          <div class="heatmap-hours-row">
            <div class="heatmap-weekday-spacer"></div>
            <?php for ($h = 0; $h < 24; $h++): ?>
              <div class="heatmap-hour-cell">
                <?php if ($h % 2 == 0): ?>
                  <span class="heatmap-hour-label"><?= sprintf('%02d', $h) ?></span>
                <?php endif; ?>
              </div>
            <?php endfor; ?>
          </div>
          
          <!-- Heat map grid with day labels -->
          <?php 
          $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
          $day_full = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
          $colors = ['#ebedf0', '#9be9a8', '#40c463', '#30a14e'];
          $activity_labels = ['Never seen online', 'Rarely online', 'Sometimes online', 'Often online'];
          
          for ($day = 0; $day < 7; $day++): ?>
            <div class="heatmap-day-row">
              <div class="heatmap-weekday-label">
                <span><?= $days[$day] ?></span>
              </div>
              
              <?php for ($hour = 0; $hour < 24; $hour++): 
                $activity_level = $online_activity[$day][$hour];
                $color = $colors[$activity_level];
                $is_current = ($hour == $current_hour && $day == $current_day);
                $tooltip = $day_full[$day] . ' ' . sprintf('%02d:00', $hour) . ' - ' . $activity_labels[$activity_level];
                if ($is_current) $tooltip .= ' (Current time)';
                $current_class = $is_current ? ' current-time' : '';
              ?>
                <div class="heatmap-cell<?= $current_class ?>" 
                     style="background-color: <?= $color ?>;<?= $is_current ? ' border: 2px solid #fd8c73; animation: cell-pulse 2s infinite;' : '' ?>" 
                     data-tooltip="<?= htmlspecialchars($tooltip) ?>"
                     data-activity="<?= $activity_level ?>"
                     data-hour="<?= $hour ?>"
                     data-day="<?= $day ?>">
                </div>
              <?php endfor; ?>
            </div>
          <?php endfor; ?>
        </div>
        
        <!-- Tooltip -->
        <div id="heatmap-tooltip" class="heatmap-tooltip" style="display: none;"></div>
        
        <script>
        // Simple, reliable tooltip positioning
        document.addEventListener('DOMContentLoaded', function() {
          const tooltip = document.getElementById('heatmap-tooltip');
          const cells = document.querySelectorAll('.heatmap-cell');
          
          function positionTooltip(e) {
            // Get viewport dimensions
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            // Set content first to measure tooltip
            tooltip.style.visibility = 'hidden';
            tooltip.style.display = 'block';
            
            const tooltipRect = tooltip.getBoundingClientRect();
            const tooltipWidth = tooltipRect.width;
            const tooltipHeight = tooltipRect.height;
            
            // Calculate position relative to mouse
            let left = e.clientX + 15;
            let top = e.clientY - 35;
            
            // Check right boundary
            if (left + tooltipWidth > viewportWidth - 20) {
              left = e.clientX - tooltipWidth - 15;
            }
            
            // Check left boundary  
            if (left < 20) {
              left = 20;
            }
            
            // Check top boundary
            if (top < 20) {
              top = e.clientY + 20;
            }
            
            // Check bottom boundary
            if (top + tooltipHeight > viewportHeight - 20) {
              top = e.clientY - tooltipHeight - 15;
            }
            
            // Apply position
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.style.visibility = 'visible';
          }
          
          cells.forEach(cell => {
            cell.addEventListener('mouseenter', function(e) {
              tooltip.textContent = this.getAttribute('data-tooltip');
              tooltip.style.opacity = '0';
              positionTooltip(e);
              tooltip.style.opacity = '1';
            });
            
            cell.addEventListener('mousemove', function(e) {
              positionTooltip(e);
            });
            
            cell.addEventListener('mouseleave', function() {
              tooltip.style.display = 'none';
              tooltip.style.opacity = '0';
            });
          });
        });
        </script>
        
        <!-- Modern Legend -->
        <div class="heatmap-legend">
          <div class="legend-title">Activity Level</div>
          <div class="legend-items">
            <div class="legend-item">
              <div class="legend-color" style="background: #ebedf0;"></div>
              <span>Never online</span>
            </div>
            <div class="legend-item">
              <div class="legend-color" style="background: #9be9a8;"></div>
              <span>Rarely online</span>
            </div>
            <div class="legend-item">
              <div class="legend-color" style="background: #40c463;"></div>
              <span>Sometimes online</span>
            </div>
            <div class="legend-item">
              <div class="legend-color" style="background: #30a14e;"></div>
              <span>Often online</span>
            </div>
          </div>
        </div>
        
        <!-- Similar Models Section - Integrated with Heat Map -->
        <?php if (!empty($similar_models)): ?>
        <div class="similar-models-section">
          <h3>🎯 Models Like This</h3>
          <p class="similar-models-subtitle">Based on shared tags, age, and location</p>
          <div class="similar-models-grid">
            <?php foreach (array_slice($similar_models, 0, 6) as $similar): 
              $sim_model = $similar['model'];
              $sim_score = $similar['similarity'];
            ?>
              <div class="similar-model-card">
                <div class="similar-model-image">
                  <img src="<?= htmlspecialchars($sim_model['image_url'] ?? '/assets/offline.png') ?>" 
                       alt="<?= htmlspecialchars($sim_model['username']) ?>"
                       onerror="this.src='/assets/offline.png'">
                  <div class="similarity-badge"><?= $sim_score ?>% match</div>
                </div>
                <div class="similar-model-info">
                  <a href="/model/<?= htmlspecialchars($sim_model['username']) ?>" class="similar-model-name">
                    <?= htmlspecialchars($sim_model['username']) ?>
                  </a>
                  <div class="similar-model-stats">
                    <span class="similar-age"><?= intval($sim_model['age'] ?? 0) ?>y</span>
                    <span class="similar-viewers">👁 <?= number_format($sim_model['num_users'] ?? 0) ?></span>
                    <?php if ($sim_model['is_hd'] ?? false): ?><span class="similar-hd">HD</span><?php endif; ?>
                  </div>
                  <div class="similar-model-tags">
                    <?php foreach (array_slice($sim_model['tags'] ?? [], 0, 3) as $tag): ?>
                      <span class="similar-tag">#<?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include('templates/footer.php'); ?>