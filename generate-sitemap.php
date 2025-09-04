<?php
// generate-sitemap.php: Run via CLI or browser to create sitemap.xml

$config    = include('config.php');

// Robust base URL detection for CLI and web
if (!empty($config['site_base_url'])) {
    $base = rtrim($config['site_base_url'], '/');
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = $scheme . '://' . $_SERVER['HTTP_HOST'];
} else {
    // LAST RESORT: Hardcode for CLI as fallback
    $base = 'https://yourdomain.com';
}

$cache_dir = __DIR__ . '/cache/';
$regions   = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
$slugs     = $config['slugs'] ?? ['f'=>'girls','m'=>'guys','t'=>'trans','c'=>'couples','model'=>'model'];
$urls = [];

// Homepage & main category pages
$urls[] = ['loc' => $base . '/', 'priority' => '1.0'];
foreach (['f','m','t','c'] as $g) {
    $urls[] = ['loc' => $base . '/' . $slugs[$g], 'priority' => '0.8'];
}

// Live model profiles
$model_usernames = [];
foreach ($regions as $region) {
    $fn = $cache_dir . "cams_{$region}.json";
    if (!file_exists($fn)) continue;
    $json = json_decode(file_get_contents($fn), true);
    if (!$json || !isset($json['results'])) continue;
    foreach ($json['results'] as $m) {
        $uname = $m['username'];
        if (!isset($model_usernames[$uname])) {
            $urls[] = [
                'loc' => $base . '/' . ($slugs['model'] ?? 'model') . '/' . urlencode($uname),
                'priority' => '0.5'
            ];
            $model_usernames[$uname] = true;
        }
    }
}

// Offline model profiles (archived)
$offline = $cache_dir . "model_profiles.json";
if (file_exists($offline)) {
    $profiles = json_decode(file_get_contents($offline), true);
    if (is_array($profiles)) {
        foreach ($profiles as $m) {
            $uname = $m['username'];
            if (!isset($model_usernames[$uname])) {
                $urls[] = [
                    'loc' => $base . '/' . ($slugs['model'] ?? 'model') . '/' . urlencode($uname),
                    'priority' => '0.2'
                ];
                $model_usernames[$uname] = true;
            }
        }
    }
}

// Add static legal/privacy page if present
foreach (['/privacy'] as $static) {
    if (file_exists(__DIR__ . $static . ".php")) {
        $urls[] = ['loc' => $base . $static, 'priority' => '0.1'];
    }
}

// Create XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach($urls as $u) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
    $xml .= "    <priority>{$u['priority']}</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= "</urlset>\n";

// Save as sitemap.xml
$sitemap_path = __DIR__ . "/sitemap.xml";
file_put_contents($sitemap_path, $xml);

echo "Sitemap saved to $sitemap_path (" . count($urls) . " URLs)\n";
?>