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
                    $existing = $modelProfiles[$profileMap[$uname]];
                    
                    // Track activity metrics for better cleanup logic
                    if (!isset($existing['first_seen'])) {
                        $profileData['first_seen'] = $now;
                    } else {
                        $profileData['first_seen'] = $existing['first_seen'];
                    }
                    
                    // Count total times seen online (activity indicator)
                    $profileData['times_seen'] = (int)($existing['times_seen'] ?? 0) + 1;
                    
                    // Track last activity period (days since first seen)
                    $profileData['days_active'] = floor(($now - $profileData['first_seen']) / 86400);
                    
                    $modelProfiles[$profileMap[$uname]] = array_merge($existing, $profileData);
                } else {
                    // New profile
                    $profileData['first_seen'] = $now;
                    $profileData['times_seen'] = 1;
                    $profileData['days_active'] = 0;
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

// PRUNE: Remove truly inactive profiles using smarter logic
$modelProfiles = array_values(array_filter($modelProfiles, function($m) use ($now) {
    // Always keep if no last_online timestamp (safety)
    if (empty($m['last_online'])) return true;
    
    $days_offline = floor(($now - $m['last_online']) / 86400);
    $times_seen = (int)($m['times_seen'] ?? 1);
    $days_active = (int)($m['days_active'] ?? 0);
    
    // Remove profiles that meet ALL of these criteria (truly inactive):
    // 1. Haven't been seen for over 120 days
    // 2. Have been seen fewer than 5 times total (low activity)
    // 3. Were active for less than 30 days total (not a regular broadcaster)
    if ($days_offline > 120 && $times_seen < 5 && $days_active < 30) {
        return false; // Remove inactive profile
    }
    
    // Keep active models even if temporarily offline for longer periods
    // Active models: seen 10+ times OR active for 60+ days
    if ($times_seen >= 10 || $days_active >= 60) {
        return true; // Keep active models regardless of offline time
    }
    
    // For moderately active models, use standard 120-day cutoff
    return $days_offline <= 120;
}));

// Save back the model_profiles archive (includes current_show now)
file_put_contents($profile_file, json_encode($modelProfiles, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

echo "All regions fetched, live caches & profile archive updated.\n";