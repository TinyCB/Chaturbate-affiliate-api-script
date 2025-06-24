<?php
// fetch-and-cache.php
$config = include('config.php');
$cache_dir = __DIR__ . "/cache/";
if (!is_dir($cache_dir)) mkdir($cache_dir);

$regions = [
    'northamerica',
    'europe_russia',
    'southamerica',
    'asia',
    'other'
];

$profile_file = $cache_dir . "model_profiles.json";

// Load existing profiles if file exists
$modelProfiles = [];
if (file_exists($profile_file)) {
    $json = @file_get_contents($profile_file);
    $modelProfiles = json_decode($json, true);
    if (!is_array($modelProfiles)) $modelProfiles = [];
}

// Map usernames to profile index for fast update
$profileMap = [];
foreach ($modelProfiles as $idx => $entry) {
    if (isset($entry['username'])) {
        $profileMap[strtolower($entry['username'])] = $idx;
    }
}

// For each region, fetch and cache
foreach ($regions as $region) {
    echo "Fetching region: $region ... ";
    $api = "https://chaturbate.com/api/public/affiliates/onlinerooms/?wm=" .
          urlencode($config['affiliate_id']) .
          "&client_ip=1.2.3.4" .
          "&region=$region&format=json&limit=500";
    $data = @file_get_contents($api);
    if ($data) {
        $json = json_decode($data, true);
        if ($json && isset($json['results'])) {
            $json['cached_ts'] = time();
            $fn = $cache_dir . "cams_{$region}.json";
            file_put_contents($fn, json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            echo "Saved to $fn (" . count($json['results']) . " rooms)\n";
            sleep(1); // polite to API

            // Archive/Update model profiles
            foreach ($json['results'] as $result) {
                // Copy all result fields except volatile ones
                $profileData = $result;
                // Remove live-only/ephemeral fields
                unset(
                    $profileData['iframe_embed'],
                    $profileData['num_users'],
                    $profileData['seconds_online'],
                    $profileData['current_show'] // Optional, you may want to keep last known online state
                );
                // Mark last time seen online
                $profileData['last_online'] = time();

                $uname = strtolower($result['username']);
                if (isset($profileMap[$uname])) {
                    $modelProfiles[$profileMap[$uname]] = array_merge($modelProfiles[$profileMap[$uname]], $profileData);
                } else {
                    $modelProfiles[] = $profileData;
                    $profileMap[$uname] = count($modelProfiles) - 1;
                }
            }
        } else {
            echo "Failed (bad data?)\n";
        }
    } else {
        echo "Failed (no response!)\n";
    }
}

// Save back the model_profiles archive
file_put_contents($profile_file, json_encode($modelProfiles, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

echo "All regions fetched, live caches & profile archive updated.\n";