<?php
$username = 'mary_lepson';
$cache_dir = __DIR__ . '/cache/';
$regions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
$found = false;

foreach ($regions as $region) {
    $file = $cache_dir . "cams_{$region}.json";
    if (!file_exists($file)) continue;
    $json = json_decode(file_get_contents($file), true);
    if (!$json || !isset($json['results'])) continue;
    foreach ($json['results'] as $m) {
        if (strtolower($m['username']) === strtolower($username)) {
            echo "Found online: " . $m['username'] . "\n";
            $found = true;
            break 2;
        }
    }
}

if (!$found) {
    echo "Model not currently online\n";
    
    // Check if exists in analytics cache
    require_once 'analytics-cache-manager.php';
    $cache = new AnalyticsCacheManager();
    $data = $cache->getModelAnalytics($username);
    if ($data) {
        echo "But has analytics data (offline model)\n";
    }
}