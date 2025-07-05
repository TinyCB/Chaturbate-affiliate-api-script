<?php
$cache_dir = __DIR__ . "/cache/";

$validRegions = [
   'northamerica',
   'europe_russia',
   'southamerica',
   'asia',
   'other'
];

// Accept region as comma-separated (single param) or multiple params
$requestedRegions = [];
if (isset($_GET['region'])) {
    if (is_array($_GET['region'])) {
        foreach ($_GET['region'] as $r) {
            foreach (explode(',', $r) as $rr)
                $requestedRegions[] = trim($rr);
        }
    } else {
        foreach (explode(',', $_GET['region']) as $rr)
            $requestedRegions[] = trim($rr);
    }
}
$requestedRegions = array_values(array_intersect($requestedRegions, $validRegions));
if (empty($requestedRegions)) $requestedRegions = $validRegions;

// Load & merge models from selected regions, dedup by username
$models = [];
error_log("Loading regions: " . implode(", ", $requestedRegions));
foreach ($requestedRegions as $reg) {
    $file = $cache_dir . "cams_$reg.json";
    if (file_exists($file)) {
        $json = json_decode(file_get_contents($file), true);
        error_log("Loaded $reg: $file (" . count($json['results']) . ")");
        if (isset($json['results'])) {
            foreach($json['results'] as $m) {
                $models[$m['username']] = $m; // dedupe by username
            }
        }
    } else {
        error_log("Cache missing for $reg: $file");
    }
}

$results = array_values($models);

// Sort by viewers descending
usort($results, function($a,$b){ return $b['num_users'] <=> $a['num_users']; });

// --- Existing filters ---

if (isset($_GET['gender'])) {
  $filterVal = is_array($_GET['gender']) ? $_GET['gender'] : [$_GET['gender']];
  $results = array_filter($results, function($m) use ($filterVal){
    return isset($m['gender']) && in_array($m['gender'], $filterVal);
  });
}

if (isset($_GET['tag'])) {
  $filterVal = is_array($_GET['tag']) ? $_GET['tag'] : [$_GET['tag']];
  $results = array_filter($results, function($m) use ($filterVal){
    if (empty($m['tags'])) return false;
    foreach ($filterVal as $tag)
      if (in_array($tag, $m['tags'])) return true;
    return false;
  });
}

if (isset($_GET['hd'])) {
  $val = ($_GET['hd'] === 'true' || $_GET['hd'] === 1);
  $results = array_filter($results, function($m) use ($val) {
    return isset($m['is_hd']) && ($m['is_hd'] == $val);
  });
}

if (isset($_GET['minAge']) || isset($_GET['maxAge'])) {
  $min = isset($_GET['minAge']) ? intval($_GET['minAge']) : 18;
  $max = isset($_GET['maxAge']) ? intval($_GET['maxAge']) : 99;
  $results = array_filter($results, function($m) use ($min, $max) {
    return isset($m['age']) && $m['age'] >= $min && $m['age'] <= $max;
  });
}

// ROOM SIZE
if (isset($_GET['size'])) {
    $sizes = [
        'intimate' => [0, 40],
        'mid'      => [41, 120],
        'high'     => [121, 999999]
    ];
    $sizeParam = $_GET['size'];
    if (isset($sizes[$sizeParam])) {
        $min = $sizes[$sizeParam][0];
        $max = $sizes[$sizeParam][1];
        $results = array_filter($results, function($m) use ($min, $max) {
            return isset($m['num_users']) && $m['num_users'] >= $min && $m['num_users'] <= $max;
        });
    }
}

// -- NEW FILTER: current_show
if (isset($_GET['current_show'])) {
    $val = $_GET['current_show'];
    if (!is_array($val)) {
        $val = explode(',', $val);
    }
    $val = array_map('strtolower', array_filter(array_map('trim', $val)));
    if (count($val) > 0) {
        $results = array_filter($results, function($m) use ($val) {
            return isset($m['current_show']) && in_array(strtolower($m['current_show']), $val);
        });
    }
}

// -- NEW FILTER: is_new (boolean)
if (isset($_GET['is_new'])) {
    $isNewVal = $_GET['is_new'];
    $boolVal = ($isNewVal === 'true' || $isNewVal === '1' || $isNewVal === 1);
    $results = array_filter($results, function($m) use ($boolVal) {
        return isset($m['is_new']) && ($m['is_new'] == $boolVal);
    });
}

$results = array_values($results);

$total = count($results);
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$paged = array_slice($results, $offset, $limit);

header('Content-Type: application/json');
echo json_encode([
  'count' => $total,
  'results' => $paged
]);