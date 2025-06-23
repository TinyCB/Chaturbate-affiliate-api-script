<?php
$config = include('config.php');

$cache_dir = __DIR__ . "/cache/";
if (!is_dir($cache_dir)) mkdir($cache_dir);

// Supported regions (as in api-proxy.php)
$regions = [
    'northamerica',
    'europe_russia',
    'southamerica',
    'asia',
    'other'
];

// You can also support per-gender fetches by expanding this array and the loops, if desired
//$genders = ['f','m','t','c'];

// Fetch and cache for each region
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
            file_put_contents($fn, json_encode($json));
            echo "Saved to $fn (" . count($json['results']) . " rooms)\n";
            sleep(1); // polite to API
        } else {
            echo "Failed (bad data?)\n";
        }
    } else {
        echo "Failed (no response!)\n";
    }
}

echo "All done!\n";
