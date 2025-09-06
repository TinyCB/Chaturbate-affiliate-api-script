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

// Get all enhanced data including historical analytics
require_once 'model-analytics-enhanced.php';
$online_activity = getOnlineActivity($username, $cache_dir);
$model_insights = getModelInsights($username, $cache_dir);
$enhanced_analytics = getEnhancedModelAnalytics($username, 30); // Last 30 days
$similar_models = getSimilarModels($username, $cache_dir, $model);
$model_badges = getModelBadges($model, $model_insights);

// Generate chart data and insights
$viewer_chart_data = generateViewerTrendChart($enhanced_analytics['historical'], 30);
$performance_insights = getPerformanceInsights($enhanced_analytics);
$time_patterns = getTimeBasedPatterns($enhanced_analytics);

// Get current time and day for highlighting
$current_hour = intval(date('G')); // 0-23
$current_day = intval(date('w')); // 0 = Sunday, 1 = Monday, etc.

include('templates/header.php');
// Add Chart.js for analytics charts
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

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
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  overflow: hidden;
  scrollbar-width: none !important;
  display: block;
  margin: 8px 0;
  transition: box-shadow 0.3s ease;
}

.cb-cam-iframe:hover {
  box-shadow: 0 12px 40px rgba(0,0,0,0.2);
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

/* Enhanced Analytics Styles */
.metrics-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.metric-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  border-left: 4px solid var(--primary-color);
}

.metric-trend {
  font-size: 0.85em;
  color: #666;
  margin-top: 4px;
  font-weight: 500;
}

.insights-section {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.insights-title {
  margin: 0 0 16px 0;
  color: #333;
  font-size: 1.1em;
  font-weight: 600;
}

.insights-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 12px;
}

.insight-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border-radius: 8px;
  padding: 14px;
  border-left: 3px solid #ddd;
}

.insight-card.insight-positive {
  border-left-color: #10b981;
}

.insight-card.insight-opportunity {
  border-left-color: #f59e0b;
}

.insight-card.insight-negative {
  border-left-color: #ef4444;
}

.insight-card.insight-info {
  border-left-color: #3b82f6;
}

.insight-icon {
  font-size: 1.2em;
  flex-shrink: 0;
}

.insight-content .insight-title {
  font-weight: 600;
  color: #333;
  font-size: 0.9em;
  margin-bottom: 2px;
}

.insight-content .insight-message {
  color: #666;
  font-size: 0.85em;
  line-height: 1.3;
}

.chart-container {
  position: relative;
  height: 200px;
  margin: 16px 0;
}

.chart-legend {
  display: flex;
  gap: 20px;
  justify-content: center;
  margin-top: 12px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9em;
  color: #666;
}

.legend-color {
  width: 16px;
  height: 3px;
  border-radius: 2px;
}

.analytics-dashboard {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  margin: 20px 0;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.dashboard-header {
  text-align: center;
  margin-bottom: 24px;
}

.dashboard-title {
  margin: 0 0 8px 0;
  color: #333;
  font-size: 1.5em;
  font-weight: 700;
}

.dashboard-icon {
  margin-right: 8px;
  font-size: 1.2em;
}

.dashboard-subtitle {
  color: #666;
  margin: 0;
  font-size: 0.95em;
}

.analytics-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-top: 24px;
}

.analytics-panel {
  background: #f9fafb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.panel-title {
  margin: 0 0 16px 0;
  color: #374151;
  font-size: 1.1em;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.panel-icon {
  font-size: 1.1em;
}

@media (max-width: 768px) {
  .analytics-content {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .metrics-row {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
  }
  
  .insights-grid {
    grid-template-columns: 1fr;
  }
  
  .chart-container {
    height: 180px;
  }
}

/* Enhanced Legend Styles */
.legend-header {
  margin-bottom: 16px;
  text-align: center;
}

.legend-subtitle {
  color: #666;
  font-size: 0.9em;
  margin-top: 4px;
  line-height: 1.4;
}

/* Activity Pattern Summary */
.activity-pattern-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin: 20px 0;
  padding: 20px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.pattern-stat {
  text-align: center;
}

.pattern-value {
  font-size: 1.8rem;
  font-weight: 700;
  color: #3b82f6;
  margin-bottom: 4px;
}

.pattern-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.pattern-message {
  grid-column: 1 / -1;
  text-align: center;
  color: #64748b;
  font-style: italic;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

@media (max-width: 768px) {
  .activity-pattern-summary {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    padding: 15px;
  }
  
  .pattern-value {
    font-size: 1.5rem;
  }
}

/* Global overflow protection and constraints */
* {
  box-sizing: border-box;
}

html, body {
  max-width: 100vw;
  overflow-x: hidden;
}

main {
  overflow-x: hidden;
  max-width: 100vw;
  box-sizing: border-box;
}

/* Full-Width Modern Layout */
.model-page-container {
  width: 100%;
  margin: 0;
  padding: 0 clamp(15px, 2vw, 40px) 40px clamp(15px, 2vw, 40px);
  background: #f8fafc;
  min-height: calc(100vh - 80px);
  box-sizing: border-box;
  overflow-x: hidden;
}

.model-hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 40px 0;
  margin: 0 calc(-1 * 15px) 30px calc(-1 * 15px);
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  box-sizing: border-box;
  overflow-x: hidden;
}

.model-hero-content {
  width: 100%;
  margin: 0;
  padding: 0 clamp(15px, 2vw, 40px);
  display: grid;
  grid-template-columns: minmax(0, auto) 1fr minmax(0, auto);
  gap: clamp(20px, 3vw, 40px);
  align-items: center;
  box-sizing: border-box;
}

.model-hero-avatar {
  width: 120px;
  height: 120px;
  max-width: 120px;
  border-radius: 20px;
  border: 4px solid rgba(255,255,255,0.2);
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
  flex-shrink: 0;
}

.model-hero-info {
  min-width: 0;
  overflow: hidden;
}

.model-hero-info h1 {
  font-size: 2.8rem;
  font-weight: 700;
  margin: 0 0 15px 0;
  text-shadow: 0 2px 4px rgba(0,0,0,0.3);
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.model-hero-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 15px;
  min-width: 0;
}

.hero-badge {
  background: rgba(255,255,255,0.2);
  color: white;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
  backdrop-filter: blur(10px);
}

.model-hero-stats {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  text-align: center;
  min-width: 0;
  flex-shrink: 0;
}

/* Similar Models Click Fix */
.similar-model-row {
  transition: background-color 0.2s ease;
}

.similar-model-row:hover {
  background-color: rgba(59, 130, 246, 0.05);
  border-radius: 8px;
}

.similar-model-row a {
  cursor: pointer;
}

.similar-model-row a:hover .similar-username {
  color: #3b82f6 !important;
}

.hero-stat-large {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1;
}

.hero-stat-label {
  font-size: 0.9rem;
  opacity: 0.9;
}

/* Main Content Grid */
.model-content-grid {
  width: 100%;
  margin: 0;
  padding: 0 clamp(15px, 2vw, 40px);
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(280px, 380px);
  gap: clamp(20px, 3vw, 40px);
  box-sizing: border-box;
  overflow-x: hidden;
}

/* Medium screens - reduce sidebar width */
@media (max-width: 1300px) {
  .model-content-grid {
    grid-template-columns: minmax(0, 1fr) minmax(260px, 320px);
    gap: clamp(15px, 2.5vw, 30px);
  }
}

/* Auto-fit layout for ultra-wide screens */
@media (min-width: 1800px) {
  .model-content-grid {
    grid-template-columns: minmax(0, 1fr) minmax(320px, 450px);
    gap: clamp(30px, 4vw, 60px);
  }
}

.model-main-content {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.model-sidebar {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Model Info Row - responsive for details and similar models */
.model-info-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: clamp(15px, 2.5vw, 25px);
  margin-bottom: clamp(20px, 3vw, 30px);
}

/* Responsive Analytics Cards */
.analytics-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: clamp(15px, 2.5vw, 25px);
  margin-bottom: clamp(20px, 3vw, 30px);
}

.compact-analytics-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  border: 1px solid #e2e8f0;
  transition: transform 0.2s, box-shadow 0.2s;
}

.compact-analytics-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f1f5f9;
}

.card-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

/* Compact Metrics Grid */
.metrics-mini-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
  margin-bottom: 20px;
}

.metric-mini {
  text-align: center;
  padding: 15px;
  background: #f8fafc;
  border-radius: 12px;
}

.metric-mini-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #3b82f6;
  margin-bottom: 5px;
}

.metric-mini-label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
}

/* Stream Section */
.stream-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  border: 1px solid #e2e8f0;
}

.stream-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.live-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #ef4444;
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
}

.live-dot {
  width: 8px;
  height: 8px;
  background: white;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

/* Enhanced Heatmap with Proper Axes */
.heatmap-compact {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  border: 1px solid #e2e8f0;
}

.heatmap-container-with-labels {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Hour labels (x-axis) */
.heatmap-hour-labels {
  display: flex;
  align-items: center;
  font-size: 0.75rem;
  color: #64748b;
  margin-bottom: 5px;
}

.hour-label-spacer {
  width: 35px;
  flex-shrink: 0;
}

.hour-label {
  flex: 2;
  text-align: center;
  font-weight: 500;
}

/* Main heatmap with day labels */
.heatmap-with-days {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.heatmap-day-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.day-label {
  width: 35px;
  font-size: 0.75rem;
  font-weight: 500;
  color: #64748b;
  text-align: right;
  flex-shrink: 0;
}

.heatmap-hour-row {
  display: flex;
  gap: 2px;
  flex: 1;
}

.heatmap-cell {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid rgba(255,255,255,0.1);
}

.heatmap-cell:hover {
  transform: scale(1.4);
  border: 1px solid #374151;
  z-index: 10;
  position: relative;
}

/* Enhanced Legend */
.heatmap-legend-enhanced {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 15px;
  padding-top: 12px;
  border-top: 1px solid #f1f5f9;
}

.legend-left {
  flex: 1;
}

.legend-right {
  display: flex;
  align-items: center;
  gap: 3px;
}

.legend-square {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  border: 1px solid rgba(255,255,255,0.1);
}

/* Iframe scaling for larger screens */
@media (min-width: 1400px) {
  .cb-cam-iframe {
    min-height: 500px !important;
  }
}

@media (min-width: 1600px) {
  .cb-cam-iframe {
    min-height: 600px !important;
  }
}

@media (min-width: 1920px) {
  .cb-cam-iframe {
    min-height: 700px !important;
  }
}

/* Responsive Design */
@media (max-width: 1024px) {
  .model-content-grid {
    grid-template-columns: 1fr;
    gap: clamp(15px, 3vw, 25px);
  }
  
  .model-sidebar {
    display: none;
  }
}
  
  .model-hero-content {
    grid-template-columns: auto 1fr;
    gap: 20px;
    text-align: left;
  }
  
  .model-hero-stats {
    grid-column: 1 / -1;
    flex-direction: row;
    justify-content: space-around;
    margin-top: 20px;
  }
}

@media (max-width: 767px) {
  .model-hero-section {
    padding: 30px 0;
  }
  
  .model-hero-content {
    grid-template-columns: 1fr;
    text-align: center;
    gap: clamp(15px, 3vw, 25px);
  }
  
  .model-hero-info h1 {
    font-size: clamp(1.8rem, 5vw, 2.2rem);
  }
  
  .model-content-grid {
    grid-template-columns: 1fr;
  }
  
  .metrics-mini-grid {
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
  }
}
</style>

<!-- Full-Width Model Page -->
<div class="model-page-container">
  <?php if (!empty($soft_error)): ?>
    <div style='background:#fffbe2;color:#7b6800;padding:18px;font-size:1.13em;margin:26px 0 18px 0;border-radius:7px;text-align:center;'>
      This model has been inactive for a while.<br>
      This profile is temporarily unavailable, but may return soon.
    </div>
  <?php endif; ?>

  <!-- Hero Section -->
  <div class="model-hero-section">
    <div class="model-hero-content">
      <img src="<?= $model_online ? htmlspecialchars($model['image_url'] ?? '') : '/assets/offline.png' ?>" 
           alt="<?=htmlspecialchars($model['username'] ?? '')?>" 
           class="model-hero-avatar"
           onerror="this.src='/assets/offline.png'">
      
      <div class="model-hero-info">
        <h1><?=htmlspecialchars($model['username'] ?? '')?></h1>
        <div class="model-hero-badges">
          <div class="hero-badge"><?= intval($model['age'] ?? 0) ?> years</div>
          <div class="hero-badge"><?=ucfirst($gender_label)?></div>
          <?php if(!empty($model['location'])): ?>
            <div class="hero-badge">📍 <?=htmlspecialchars($model['location'])?></div>
          <?php endif; ?>
          <?php if(!empty($model['is_hd'])): ?>
            <div class="hero-badge">🎬 HD</div>
          <?php endif; ?>
          <?php if(!empty($model['is_new'])): ?>
            <div class="hero-badge">⭐ NEW</div>
          <?php endif; ?>
          <?php if(!$model_online): ?>
            <div class="hero-badge" style="background: rgba(239,68,68,0.2);">⚫ OFFLINE</div>
          <?php endif; ?>
        </div>
        <?php if (!empty($model['room_subject'])): ?>
          <p style="margin: 0; opacity: 0.9; font-size: 1.1rem;"><?= htmlspecialchars($model['room_subject']) ?></p>
        <?php endif; ?>
      </div>

      <div class="model-hero-stats">
        <?php if($model_online && isset($model['num_users'])): ?>
          <div>
            <div class="hero-stat-large"><?=number_format(intval($model['num_users']))?></div>
            <div class="hero-stat-label">Current Viewers</div>
          </div>
        <?php else: ?>
          <div>
            <div class="hero-stat-large"><?=number_format(intval($model['num_followers'] ?? 0))?></div>
            <div class="hero-stat-label">Total Followers</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Main Content Grid -->
  <div class="model-content-grid">
    <!-- Main Content -->
    <div class="model-main-content">
      
      <?php if($model_online): ?>
      <!-- Live Stream Card -->
      <div class="stream-card">
        <div class="stream-header">
          <h2 style="margin: 0; font-size: 1.4rem; font-weight: 600;">Live Stream</h2>
          <div class="live-indicator">
            <div class="live-dot"></div>
            LIVE
          </div>
        </div>
        <?=ensure_iframe_fullscreen(chaturbate_whitelabel_replace($model['iframe_embed_revshare'], $config['whitelabel_domain']), $iframe_height)?>
        <div class="model-fallback-msg" id="cb-embed-fallback" style="display: none; color: #e44; margin: 16px 0 8px 0; text-align: center; font-size: 1em; padding: 12px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
          The cam video may be blocked by your browser, privacy, or adblocker settings. Try disabling shields or using a different browser if the cam does not display.
        </div>
        <script>
        setTimeout(function() {
          var f=document.querySelector('.cb-cam-iframe');
          if(f && ((!f.contentWindow&&f.offsetHeight<150)||f.offsetHeight===0))
              document.getElementById('cb-embed-fallback').style.display = 'block';
        }, 2600);
        </script>
      </div>
      <?php endif; ?>

      <!-- Model Details and Similar Models Row -->
      <div class="model-info-row">
        <!-- Model Details -->
        <div class="compact-analytics-card">
          <div class="card-header">
            <div class="card-icon"><i class="fas fa-user"></i></div>
            <h3 class="card-title">Model Details</h3>
          </div>
          
          <?php if (!empty($model['tags'])): ?>
          <div style="margin-bottom: 20px;">
            <h4 style="margin-bottom: 10px; color: #64748b; font-size: 0.875rem; font-weight: 600;">TAGS</h4>
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
              <?php foreach (array_slice($model['tags'], 0, 12) as $tag): ?>
              <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                #<?= htmlspecialchars($tag) ?>
              </span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($model['spoken_languages'])): ?>
          <div style="margin-bottom: 15px;">
            <h4 style="margin-bottom: 8px; color: #64748b; font-size: 0.875rem; font-weight: 600;">LANGUAGES</h4>
            <p style="margin: 0; color: #1e293b;"><?= htmlspecialchars($model['spoken_languages']) ?></p>
          </div>
          <?php endif; ?>

          <?php if (!empty($model['location'])): ?>
          <div style="margin-bottom: 15px;">
            <h4 style="margin-bottom: 8px; color: #64748b; font-size: 0.875rem; font-weight: 600;">LOCATION</h4>
            <p style="margin: 0; color: #1e293b;"><?= htmlspecialchars($model['location']) ?></p>
          </div>
          <?php endif; ?>

          <?php if (!empty($model['ai_bio'])): ?>
          <div class="model-written-bio" style="margin-bottom: 15px;">
            <?= htmlspecialchars($model['ai_bio']) ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Similar Models -->
        <?php if (!empty($similar_models)): ?>
        <div class="compact-analytics-card">
          <div class="card-header">
            <div class="card-icon"><i class="fas fa-users"></i></div>
            <h3 class="card-title">Similar Models</h3>
          </div>
          <?php foreach (array_slice($similar_models, 0, 4) as $similar): 
            $sim_model = $similar['model'];
            $sim_score = $similar['similarity'];
          ?>
          <div class="similar-model-row" style="display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; position: relative;">
            <a href="/model/<?= htmlspecialchars($sim_model['username']) ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none; width: 100%; position: relative; z-index: 1;">
              <img src="<?= htmlspecialchars($sim_model['image_url'] ?? '/assets/offline.png') ?>" 
                   alt="<?= htmlspecialchars($sim_model['username']) ?>"
                   style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;"
                   onerror="this.src='/assets/offline.png'">
              <div style="flex: 1; min-width: 0;">
                <div class="similar-username" style="color: #1e293b; font-weight: 500; font-size: 0.9rem;">
                  <?= htmlspecialchars($sim_model['username']) ?>
                </div>
                <div style="color: #64748b; font-size: 0.8rem;">
                  <?= intval($sim_model['age'] ?? 0) ?>y • <?= number_format($sim_model['num_users'] ?? 0) ?> viewers
                </div>
              </div>
            </a>
            <div style="background: #e2e8f0; color: #475569; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
              <?= $sim_score ?>%
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Analytics Row -->
      <div class="analytics-row">
        <!-- Performance Analytics -->
        <div class="compact-analytics-card">
          <div class="card-header">
            <div class="card-icon"><i class="fas fa-chart-line"></i></div>
            <h3 class="card-title">Performance Metrics</h3>
          </div>
          <div class="metrics-mini-grid">
            <div class="metric-mini">
              <div class="metric-mini-value"><?= number_format($model_insights['peak_viewers']) ?></div>
              <div class="metric-mini-label">Peak Viewers</div>
            </div>
            <div class="metric-mini">
              <div class="metric-mini-value"><?= number_format($model_insights['avg_viewers']) ?></div>
              <div class="metric-mini-label">Avg Viewers</div>
            </div>
            <div class="metric-mini">
              <div class="metric-mini-value"><?= $enhanced_analytics['performance_score']['score'] ?>%</div>
              <div class="metric-mini-label">Performance</div>
            </div>
            <div class="metric-mini">
              <div class="metric-mini-value"><?= $model_insights['consistency_score'] ?>%</div>
              <div class="metric-mini-label">Consistency</div>
            </div>
          </div>
        </div>

        <!-- Activity Pattern -->
        <div class="compact-analytics-card">
          <div class="card-header">
            <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
            <h3 class="card-title">Activity Pattern</h3>
          </div>
          <?php if ($enhanced_analytics['historical'] && isset($enhanced_analytics['historical']['weekly_pattern'])): 
            $pattern = $enhanced_analytics['historical']['weekly_pattern'];
            $day_names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
          ?>
          <div class="metrics-mini-grid">
            <div class="metric-mini">
              <div class="metric-mini-value"><?= $pattern['activity_score'] ?>%</div>
              <div class="metric-mini-label">Weekly Activity</div>
            </div>
            <div class="metric-mini">
              <div class="metric-mini-value"><?= $day_names[$pattern['best_day']] ?? 'N/A' ?></div>
              <div class="metric-mini-label">Best Day</div>
            </div>
            <div class="metric-mini">
              <div class="metric-mini-value"><?= sprintf('%02d:00', $pattern['best_hour']) ?></div>
              <div class="metric-mini-label">Peak Hour</div>
            </div>
            <div class="metric-mini">
              <div class="metric-mini-value"><?= $pattern['total_sessions'] ?></div>
              <div class="metric-mini-label">Sessions (7d)</div>
            </div>
          </div>
          <?php else: ?>
          <div style="text-align: center; color: #64748b; font-style: italic; padding: 20px;">
            <i class="fas fa-info-circle"></i><br>
            Activity pattern data will appear after a few days of tracking
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Weekly Activity Heatmap with Proper Axes -->
      <div class="heatmap-compact">
        <div class="card-header">
          <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
          <h3 class="card-title">Weekly Activity Pattern</h3>
        </div>
        
        <div class="heatmap-container-with-labels">
          <!-- Hour labels (x-axis) -->
          <div class="heatmap-hour-labels">
            <div class="hour-label-spacer"></div>
            <?php for ($hour = 0; $hour < 24; $hour += 2): ?>
              <div class="hour-label"><?= sprintf('%02d', $hour) ?></div>
            <?php endfor; ?>
          </div>
          
          <!-- Main heatmap grid with day labels -->
          <div class="heatmap-with-days">
            <?php 
            $colors = ['#ebedf0', '#9be9a8', '#40c463', '#30a14e'];
            $day_names = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            $day_names_full = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            
            // Use analytics data if available, otherwise show empty heatmap
            if ($enhanced_analytics['historical'] && isset($enhanced_analytics['historical']['weekly_pattern'])) {
              $pattern = $enhanced_analytics['historical']['weekly_pattern'];
              $activity_by_day = $pattern['activity_by_day'] ?? array_fill(0, 7, 0);
              $activity_by_hour = $pattern['activity_by_hour'] ?? array_fill(0, 24, 0);
              
              for ($day = 0; $day < 7; $day++): ?>
                <div class="heatmap-day-row">
                  <div class="day-label"><?= $day_names[$day] ?></div>
                  <div class="heatmap-hour-row">
                    <?php for ($hour = 0; $hour < 24; $hour++): 
                      // Get actual sessions for this specific day+hour combination
                      $activity_level = 0;
                      $sessions_this_slot = 0;
                      
                      // Access the full analytics data to get viewer history
                      if (isset($enhanced_analytics['historical']) && 
                          is_array($enhanced_analytics['historical']) && 
                          isset($enhanced_analytics['historical']['viewer_history'])) {
                        
                        // Look through the last 7 days of viewer history
                        $viewer_history = $enhanced_analytics['historical']['viewer_history'];
                        $week_ago = time() - (7 * 24 * 3600);
                        
                        foreach ($viewer_history as $session) {
                          if ($session['timestamp'] > $week_ago) {
                            $session_day = isset($session['day_of_week']) ? 
                                          $session['day_of_week'] : 
                                          date('w', $session['timestamp']);
                            $session_hour = isset($session['hour_of_day']) ? 
                                           $session['hour_of_day'] : 
                                           date('G', $session['timestamp']);
                            
                            if ($session_day == $day && $session_hour == $hour) {
                              $sessions_this_slot++;
                            }
                          }
                        }
                      }
                      
                      // Set activity level based on actual sessions in this time slot
                      if ($sessions_this_slot == 0) {
                        $activity_level = 0; // Never online
                      } elseif ($sessions_this_slot == 1) {
                        $activity_level = 1; // Rarely online  
                      } elseif ($sessions_this_slot <= 3) {
                        $activity_level = 2; // Sometimes online
                      } else {
                        $activity_level = 3; // Often online
                      }
                      
                      $color = $colors[$activity_level];
                      $activity_labels = ['Never online', 'Rarely online', 'Sometimes online', 'Often online'];
                    ?>
                      <div class="heatmap-cell" 
                           style="background-color: <?= $color ?>;" 
                           title="<?= $day_names_full[$day] ?> <?= sprintf('%02d:00', $hour) ?> - <?= $activity_labels[$activity_level] ?>"
                           data-day="<?= $day ?>" 
                           data-hour="<?= $hour ?>"
                           data-activity="<?= $activity_level ?>"></div>
                    <?php endfor; ?>
                  </div>
                </div>
              <?php endfor;
            } else {
              // Show empty heatmap when no data available
              for ($day = 0; $day < 7; $day++): ?>
                <div class="heatmap-day-row">
                  <div class="day-label"><?= $day_names[$day] ?></div>
                  <div class="heatmap-hour-row">
                    <?php for ($hour = 0; $hour < 24; $hour++): ?>
                      <div class="heatmap-cell" 
                           style="background-color: <?= $colors[0] ?>;" 
                           title="<?= $day_names_full[$day] ?> <?= sprintf('%02d:00', $hour) ?> - No data available"
                           data-day="<?= $day ?>" 
                           data-hour="<?= $hour ?>"
                           data-activity="0"></div>
                    <?php endfor; ?>
                  </div>
                </div>
              <?php endfor;
            }
            ?>
          </div>
          
          <!-- Legend -->
          <div class="heatmap-legend-enhanced">
            <div class="legend-left">
              <span style="font-size: 0.8rem; color: #64748b;">
                <?php if ($enhanced_analytics['historical'] && isset($enhanced_analytics['historical']['weekly_pattern'])): ?>
                  <?= $enhanced_analytics['historical']['weekly_pattern']['total_sessions'] ?> sessions in last 7 days
                <?php else: ?>
                  No activity data yet
                <?php endif; ?>
              </span>
            </div>
            <div class="legend-right">
              <span style="font-size: 0.8rem; color: #64748b; margin-right: 8px;">Less</span>
              <?php foreach ($colors as $color): ?>
                <div class="legend-square" style="background: <?= $color ?>;"></div>
              <?php endforeach; ?>
              <span style="font-size: 0.8rem; color: #64748b; margin-left: 8px;">More</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="model-sidebar">
      <!-- Performance Insights -->
      <?php if (!empty($performance_insights)): ?>
      <div class="compact-analytics-card">
        <div class="card-header">
          <div class="card-icon"><i class="fas fa-lightbulb"></i></div>
          <h3 class="card-title">Performance Insights</h3>
        </div>
        <?php foreach (array_slice($performance_insights, 0, 3) as $insight): ?>
        <div style="background: <?= $insight['type'] === 'positive' ? '#f0fdf4' : ($insight['type'] === 'opportunity' ? '#fefce8' : '#f8fafc') ?>; border-left: 3px solid <?= $insight['type'] === 'positive' ? '#10b981' : ($insight['type'] === 'opportunity' ? '#f59e0b' : '#64748b') ?>; padding: 12px 15px; margin-bottom: 12px; border-radius: 8px;">
          <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;"><?= htmlspecialchars($insight['title']) ?></div>
          <div style="font-size: 0.875rem; color: #64748b; line-height: 1.4;"><?= htmlspecialchars($insight['message']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include("templates/footer.php"); ?>
