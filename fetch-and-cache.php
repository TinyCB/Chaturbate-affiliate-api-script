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
    if (!is_array($modelProfiles)) {
        $modelProfiles = [];
    }
}

// Map usernames to profile index for fast update
$profileMap = [];
foreach ($modelProfiles as $idx => $entry) {
    if (isset($entry['username'])) {
        $profileMap[strtolower($entry['username'])] = $idx;
    }
}

$now = time();
$seen_in_this_fetch = []; // usernames seen this run

// For each region, fetch and cache
foreach ($regions as $region) {
    echo "Fetching region: $region ... ";

    $api = "https://chaturbate.com/api/public/affiliates/onlinerooms/?wm=" .
          urlencode($config['affiliate_id']) .
          "&client_ip=1.2.3.4" .    // adjust to 'request_ip' or actual IP if desired
          "&region=$region&format=json&limit=500";

    $data = @file_get_contents($api);
    if ($data) {
        $json = json_decode($data, true);
        if ($json && isset($json['results'])) {
            // Save full JSON (with current_show included) to region cache file
            $json['cached_ts'] = $now;
            $fn = $cache_dir . "cams_{$region}.json";
            file_put_contents($fn, json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            echo "Saved to $fn (" . count($json['results']) . " rooms)\n";

            sleep(1); // polite delay for API

            // Archive / Update model profiles
            foreach ($json['results'] as $result) {
                $uname = strtolower($result['username']);
                $seen_in_this_fetch[$uname] = true;

                // Copy full profile data as is (do NOT unset current_show)
                $profileData = $result;

                // Remove fields that are truly ephemeral / live-only, if you want to keep any,
                // you can remove these unset calls or comment out:
                unset(
                    $profileData['iframe_embed'],          // embed code - keep or not as desired
                    $profileData['iframe_embed_revshare'],// embed code revshare - keep or not
                    // num_users and seconds_online are live counts, you may keep or unset:
                    // $profileData['num_users'],
                    // $profileData['seconds_online'],
                    // DO NOT unset current_show here, keep it
                );

                // Mark last time seen online
                $profileData['last_online'] = $now;

                if (isset($profileMap[$uname])) {
                    // Merge into existing profile (overwrites with latest fields)
                    $modelProfiles[$profileMap[$uname]] = array_merge($modelProfiles[$profileMap[$uname]], $profileData);
                } else {
                    // New profile
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

// PRUNE: Remove profiles not seen in last 120 days (adjust as needed)
$cutoff = $now - 120 * 86400;
$modelProfiles = array_values(array_filter($modelProfiles, function($m) use ($cutoff) {
    return !empty($m['last_online']) && $m['last_online'] >= $cutoff;
}));

// Save back the model_profiles archive (includes current_show now)
file_put_contents($profile_file, json_encode($modelProfiles, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

echo "All regions fetched, live caches & profile archive updated.\n";