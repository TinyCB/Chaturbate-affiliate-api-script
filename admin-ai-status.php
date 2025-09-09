<?php
require_once 'bio-cache-manager.php';

$cache_dir = __DIR__ . "/cache/";
$profile_file = $cache_dir . "model_profiles.json";
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: /admin.php");
    exit;
}
$perPage = 50;
$stale_days = 30;
$stale_secs = $stale_days * 86400;
$page = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all'; // all | with | missing | stale | fresh
$sort   = $_GET['sort'] ?? 'default'; // default | user | last | length
$total = 0; $with_bio = 0; $missing = 0; $stale = 0; $profiles = [];
$now = time();
$error_msg = null;

// Initialize bio cache manager
$bio_cache = new BioCacheManager();
$bio_stats = $bio_cache->getCacheStats();

if (!is_dir($cache_dir)) {
    if (!@mkdir($cache_dir, 0775, true)) {
        $error_msg = "Server error: Could not create cache directory '$cache_dir'!";
    }
}
if (!$error_msg) {
    // Try to load from bio cache first, fallback to model_profiles.json
    $allModels = [];
    
    // Load all models with bios from bio cache
    $models_with_bios = $bio_cache->getAllModelsWithBios();
    foreach ($models_with_bios as $username) {
        $bio_data = $bio_cache->getModelBio($username);
        if ($bio_data) {
            $allModels[$username] = [
                'username' => $username,
                'id' => $username,
                'display_name' => $bio_data['source_profile']['display_name'] ?? $username,
                'ai_bio' => $bio_data['ai_bio'],
                'ai_bio_last_generated' => $bio_data['ai_bio_last_generated'],
                'ai_bio_version' => $bio_data['ai_bio_version'],
                'location' => $bio_data['source_profile']['location'] ?? '',
                'gender' => $bio_data['source_profile']['gender'] ?? '',
                'tags' => $bio_data['source_profile']['tags'] ?? [],
                '_bio_source' => 'bio_cache'
            ];
        }
    }
    
    // Fallback: Load models from model_profiles.json for models not in bio cache
    if (file_exists($profile_file) && is_readable($profile_file)) {
        $json = file_get_contents($profile_file);
        $profile_models = json_decode($json, true);
        if (is_array($profile_models)) {
            foreach ($profile_models as $model) {
                $username = $model['username'] ?? '';
                if (!empty($username) && !isset($allModels[$username])) {
                    $allModels[$username] = [
                        'username' => $username,
                        'id' => $model['id'] ?? $username,
                        'display_name' => $model['display_name'] ?? $username,
                        'ai_bio' => $model['ai_bio'] ?? '',
                        'ai_bio_last_generated' => $model['ai_bio_last_generated'] ?? 0,
                        'ai_bio_version' => $model['ai_bio_version'] ?? 0,
                        'location' => $model['location'] ?? '',
                        'gender' => $model['gender'] ?? '',
                        'tags' => $model['tags'] ?? [],
                        '_bio_source' => 'model_profiles'
                    ];
                }
            }
        }
    }
    
    $allModels = array_values($allModels); // Convert to indexed array
    
    if (empty($allModels)) {
        $error_msg = "No model data found. Make sure to generate bios or check that bio cache files exist.";
    } else {
        if ($search) {
            $allModels = array_filter($allModels, function($m) use($search) {
                foreach (['id','username','display_name'] as $field)
                    if (!empty($m[$field]) && stripos($m[$field], $search) !== false) return true;
                return false;
            });
        }
        foreach ($allModels as &$m) {
            $total++;
            $hasBio = !empty($m['ai_bio']);
            $with_bio += $hasBio ? 1 : 0;
            $missing += $hasBio ? 0 : 1;
            $lastGen = $m['ai_bio_last_generated'] ?? 0;
            if ($hasBio && ($lastGen < $now - $stale_secs)) $stale++;
            $m['_filter_stale']  = $hasBio && ($lastGen < $now - $stale_secs);
            $m['_filter_fresh']  = $hasBio && ($lastGen >= $now - $stale_secs);
            $m['_has_bio'] = $hasBio;
        }
        unset($m);

            // FILTER
            switch ($filter) {
                case 'with':   $allModels = array_filter($allModels, fn($m)=>$m['_has_bio']); break;
                case 'missing':$allModels = array_filter($allModels, fn($m)=>!$m['_has_bio']); break;
                case 'stale':  $allModels = array_filter($allModels, fn($m)=>$m['_filter_stale']); break;
                case 'fresh':  $allModels = array_filter($allModels, fn($m)=>$m['_filter_fresh']); break;
                case 'all': default: /* no filter */ break;
            }
            // SORT
            if ($sort === 'user') {
                usort($allModels, fn($a,$b)=>strcasecmp($a['username'],$b['username']));
            } elseif ($sort === 'last') {
                usort($allModels, function($a,$b){
                    return (($b['ai_bio_last_generated'] ?? 0) <=> ($a['ai_bio_last_generated'] ?? 0));
                });
            } elseif ($sort === 'length') {
                usort($allModels, function($a,$b){
                    return (mb_strlen($b['ai_bio'] ?? '') <=> mb_strlen($a['ai_bio'] ?? ''));
                });
            } else {
                usort($allModels, function($a, $b) use($now, $stale_secs) {
                    $aStale = $a['_filter_stale'] ?? false;
                    $bStale = $b['_filter_stale'] ?? false;
                    if ($aStale !== $bStale) return $bStale <=> $aStale;
                    if (!empty($a['ai_bio']) !== !empty($b['ai_bio'])) return (!empty($b['ai_bio']) - !empty($a['ai_bio']));
                    return ($b['ai_bio_last_generated'] ?? 0) <=> ($a['ai_bio_last_generated'] ?? 0);
                });
            }
            $profiles = array_values($allModels);
    }
}
$numPages = max(1, ceil(count($profiles) / $perPage));
$profilesPage = array_slice($profiles, ($page-1)*$perPage, $perPage);
$percent = $total > 0 ? round(($with_bio / $total) * 100, 2) : 0;
function paginator($page, $numPages, $search, $filter = 'all', $sort = 'default') {
    $html = '';
    $url = function($i) use($search, $filter, $sort) {
        return '?page='.$i
            .($filter && $filter != 'all' ? '&filter='.urlencode($filter) : '')
            .($sort && $sort != 'default' ? '&sort='.urlencode($sort) : '')
            .($search ? '&search='.urlencode($search) : '');
    };
    if ($numPages <= 1) return '';
    $html .= '<div class="polished-paginator">';
    if ($page > 1)  $html .= '<a href="'.$url($page-1).'" class="nav">&laquo; Prev</a>';
    else            $html .= '<span class="nav disabled">&laquo; Prev</span>';
    $adj = 2;
    $start = max(1, $page-$adj); $end = min($numPages, $page+$adj);
    if($start > 1) { $html .= '<a href="'.$url(1).'">1</a>'; if ($start > 2) $html .= '<span>…</span>'; }
    for($i=$start; $i<=$end; $i++) $html .= ($i==$page ? '<span class="selected">'.$i.'</span>' : '<a href="'.$url($i).'">'.$i.'</a>');
    if($end < $numPages){
        if ($end < $numPages-1) $html .= '<span>…</span>';
        $html .= '<a href="'.$url($numPages).'">'.$numPages.'</a>';
    }
    if ($page < $numPages) $html .= '<a href="'.$url($page+1).'" class="nav">Next &raquo;</a>';
    else                   $html .= '<span class="nav disabled">Next &raquo;</span>';
    $html .= '</div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>AI Bio Status</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body {
    background:#f7f8fa;
    font-family: system-ui, Arial, sans-serif;
}
.admin-tabs {
    display: flex;
    justify-content: center;
    gap: 3px;
    margin: 33px 0 19px 0;
    font-size: 1.10em;
}
.admin-tabs a {
    background: #f4f5fc;
    color: #595bb8;
    text-decoration: none;
    padding: 10px 35px;
    border-top-left-radius: 7px;
    border-top-right-radius: 7px;
    margin-right:2px;
    border: 1px solid #d2d6f1;
    border-bottom: none;
    font-weight: 600;
    transition: background .15s, color .14s;
    display: inline-block;
}
.admin-tabs a.selected, .admin-tabs a:focus {
    background: #fff;
    color: #3a278d;
    outline: none;
    border-bottom: 1.5px solid #fff;
    cursor: default;
}
.admin-tabs a:hover:not(.selected), .admin-tabs a:active {
    background: #ecefff; color: #775ba7;
}
.ai-status-box {
    background: #fff;
    margin: 0 auto 23px auto;
    max-width: 820px;
    border-radius: 11px;
    padding: 26px 35px 19px 35px;
    box-shadow: 0 3px 19px #786dc213;
}
.ai-status-title {
    color: #3f3c93;
    font-size: 1.44em;
    margin-bottom: 19px;
    text-align: center;
    letter-spacing:0.01em;
}
.ai-status-progress {
    margin: 0 0 18px 0;
    background: #f5f7fa;
    border-radius: 6px;
    height: 24px;
    position: relative;
    overflow: hidden;
}
.ai-status-bar {
    background: #86a6fc;
    border-radius: 6px;
    height: 100%;
    width: <?= htmlspecialchars($percent) ?>%;
    position: absolute; left:0; top:0;
    transition: width 0.24s;
    min-width: 18px;
}
.ai-status-bar span {
    text-align:center;
    display: block;
    width: 100%;
    color: #393671;
    font-size:1.09em;
    font-weight:600;
    position: absolute; left:0; right:0; top:0; bottom:0;
    line-height:24px;
}
.ai-status-stats {
    font-size:1.10em;
    margin-bottom:14px;
    display:flex;
    flex-direction:column;
    gap:6px 0;
}
.ai-status-label {
    display: inline-block;
    font-weight:600; color:#39396c;
    min-width:120px;
}
.notice {
    background:#fbf8e3;border:1px solid #e3dfcc;color:#6a640f;
    margin:15px auto 12px auto;font-size:1.14em;padding:10px 16px;
    max-width:650px;text-align:center;border-radius:6px;
}
.polished-table {
    width:100%; max-width: 1260px; margin: 20px auto 15px auto;
    border-collapse: separate; border-spacing:0;font-size:1.03em;
    background: #fff; border-radius:11px; box-shadow:0 2px 14px #b8bbf15c;
}
.polished-table th,.polished-table td {
    padding: 10px 11px;
    border-bottom: 1px solid #e2e6f0;
    vertical-align:top;
}
.polished-table th {
    background: #f6f6fd; color:#3841a1;
    text-align:left;font-size:1.11em;font-weight:600;letter-spacing:.01em;
    border-top:1px solid #e2e6f0;
}
tr:nth-child(even) td { background: #fcfcfa;}
tr.stale td { background:#fff2f2 !important; }
tr.missing td { background:#f7f4e7 !important; color:#a15; }
.table-bio-full {
    white-space: pre-line; background:#f3f1fa;
    color:#232050;
    border-radius:7px;
    margin:8px 0 0 0;
    font-size:0.98em;
    padding:13px 13px 8px 13px;
    box-shadow:0 1px 3px #d8d7ea38;
    line-height:1.44;
    border-left:4px solid #6260d1;
    display:none;
}
tr.active td .table-bio-full { display:block;}
.table-bio-excerpt {
    cursor:pointer;color:#355be6;text-decoration:underline;max-width:440px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
    background:transparent;line-height:1.4;transition:.15s;
    font-weight:500;
}
.table-bio-excerpt:hover { color:#4f31af; background:#f2f2fe;}
.date-stale { color:#dc4545; font-weight:700;}
th.bio-th, td.bio-td {min-width:205px;}
@media (max-width:1110px) {
    .polished-table,th,td{ font-size:0.98em;}
    th.bio-th, td.bio-td{min-width:140px;}
    .table-bio-excerpt{max-width:150px}
}
@media (max-width:850px) {
    .ai-status-box {max-width:99vw;padding:12px 2vw 12px 2vw;}
    .polished-table,th,td {font-size:0.97em;}
    .table-bio-full,.table-bio-excerpt{font-size:0.97em;}
    th.bio-th, td.bio-td{min-width:80px;}
}
@media (max-width:660px) {
    table, th, td {font-size:0.91em;}
    .table-bio-full{padding:8px;}
    .table-bio-excerpt{padding:4px 0;}
    th.bio-th, td.bio-td{font-size:0.89em;}
}
.polished-paginator {
    display:flex;align-items:center;justify-content:center;
    gap:2px;
    margin: 23px 0 20px 0;
    flex-wrap:wrap
}
.polished-paginator a,
.polished-paginator span {
    padding:6px 14px;
    border-radius:7px;
    text-decoration:none;
    color:#3941c2;
    margin:0 2px 2px 0;
    background:#f1f2fc;
    border:1px solid #e9e9fa;
    font-weight:500;
    transition:.12s;
    position:relative;
    font-size:1.09em;
    min-width:35px;text-align:center;
}
.polished-paginator .selected {
    background:#382c8e;
    color:#fff;
    box-shadow:0 1px 4px #c8c3fa6f;
    border:1px solid #4439bf;
    font-weight:700;
    z-index:1;
}
.polished-paginator .disabled {
    color:#aaa;background:#f6f6f6;border:1px solid #ededed;cursor:not-allowed;
}
.polished-paginator .nav {font-size:1.07em;padding:6px 16px;}
.polished-paginator span { cursor:default;}
</style>
<script>
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('.table-bio-excerpt').forEach(function(el){
        el.addEventListener('click', function(e){
            var tr = this.closest('tr');
            if(tr.classList.contains('active')){
                tr.classList.remove('active');
            } else {
                document.querySelectorAll('tr.active').forEach(function(r){r.classList.remove('active');});
                tr.classList.add('active');
            }
        });
    });
});
</script>
</head>
<body>
<div class="admin-tabs">
    <a href="/admin">Site Settings</a>
    <a href="/admin-ai-status" class="selected">AI Bio Status</a>
</div>
<?php if ($error_msg): ?>
    <div style="background:#ffeced;color:#a22;border:1px solid #f7cbcb;margin:25px auto;max-width:630px;padding:22px 16px;font-size:1.09em;border-radius:8px;">
        <b>Error:</b> <?=htmlspecialchars($error_msg)?>
        <br>
        <span style="font-size:0.96em;color:#543;">
            (Check server permissions and that you have generated bios and the
            <code>cache/model_profiles.json</code> file exists and is readable.)
        </span>
    </div>
<?php endif; ?>
<div class="ai-status-box">
    <div class="ai-status-title">AI Bio Progress</div>
    <div class="ai-status-progress">
        <div class="ai-status-bar">
            <span><?= htmlspecialchars($percent) ?>%</span>
        </div>
    </div>
    <div class="ai-status-stats">
        <div><span class="ai-status-label">Total models:</span> <?=number_format($total)?></div>
        <div><span class="ai-status-label">With bio:</span> <?=number_format($with_bio)?></div>
        <div><span class="ai-status-label">Missing bio:</span> <?=number_format($missing)?></div>
        <div><span class="ai-status-label">Stale bios (&gt;<?=$stale_days?>d):</span> <span style="color:#d21919;"><?=number_format($stale)?></span></div>
    </div>
    <div class="ai-status-stats" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
        <div><span class="ai-status-label">Bio cache files:</span> <?=number_format($bio_stats['file_count'])?></div>
        <div><span class="ai-status-label">Cache size:</span> <?=$bio_stats['total_size_mb']?> MB</div>
        <div><span class="ai-status-label">Avg file size:</span> <?=round($bio_stats['avg_file_size'] / 1024, 1)?> KB</div>
    </div>
    <form method="get" style="text-align:center;margin-top:7px;margin-bottom:3px;">
        <select name="filter" style="margin-right:9px;padding:7px 9px;border-radius:6px;font-size:1.06em;">
            <option value="all"    <?= $filter=='all'?'selected':'' ?>>All</option>
            <option value="with"   <?= $filter=='with'?'selected':'' ?>>With Bio</option>
            <option value="missing"<?= $filter=='missing'?'selected':'' ?>>Missing Bio</option>
            <option value="stale"  <?= $filter=='stale'?'selected':'' ?>>Stale Bio (&gt;<?=$stale_days?>d)</option>
            <option value="fresh"  <?= $filter=='fresh'?'selected':'' ?>>Fresh Bio (&le;<?=$stale_days?>d)</option>
        </select>
        <select name="sort" style="margin-right:9px;padding:7px 9px;border-radius:6px;font-size:1.06em;">
            <option value="default"<?= $sort=='default'?'selected':'' ?>>Default</option>
            <option value="user"   <?= $sort=='user'?'selected':'' ?>>Username</option>
            <option value="last"   <?= $sort=='last'?'selected':'' ?>>Last Written</option>
            <option value="length" <?= $sort=='length'?'selected':'' ?>>Bio Length</option>
        </select>
        <input type="text" name="search" value="<?=htmlspecialchars($search)?>" placeholder="Search user/display/id" style="width:152px;padding:7px;border-radius:7px;border:1px solid #d7d2ed;">
        <button type="submit" style="padding:7px 18px;border-radius:6px;background:#cecde6;color:#38357d;font-weight:600;border:1px solid #ebeaf4;">Search</button>
        <?php if ($search || $filter!='all' || $sort!='default'): ?><a href="admin-ai-status.php" style="margin-left:7px;color:#999;text-decoration:none;font-size:0.98em;">Clear</a><?php endif; ?>
    </form>
    <div style="font-size:0.99em;margin:13px 0 4px 0;color:#5072bd;text-align:center;">
        Bios are (re)generated via the batch script.<br>
        Refresh this page for live stats.
    </div>
</div>
<?php if (!$error_msg && $profilesPage): ?>
<table class="polished-table">
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Display Name</th>
    <th>Last Written</th>
    <th class="bio-th">Model BIO <span style="font-weight:400;font-size:0.95em">(click excerpt)</span></th>
</tr>
<?php foreach ($profilesPage as $p): ?>
    <?php
        $is_missing = empty($p['ai_bio']);
        $is_stale = !$is_missing && (empty($p['ai_bio_last_generated']) || $p['ai_bio_last_generated'] < $now - $stale_secs);
        $row_class = $is_missing ? "missing" : ($is_stale ? "stale" : "");
        $last = !empty($p['ai_bio_last_generated']) ? date('Y-m-d H:i', $p['ai_bio_last_generated']) : '<span style="color:#ba0;">Never</span>';
        $version = !empty($p['ai_bio_version']) ? "v".intval($p['ai_bio_version']) : "";
        $bio_source = ($p['_bio_source'] ?? '') === 'bio_cache' ? 
            '<span style="color:#10b981;font-size:0.8em;margin-left:5px;" title="From bio cache">💚</span>' : 
            '<span style="color:#f59e0b;font-size:0.8em;margin-left:5px;" title="From model_profiles.json">⚠️</span>';
        $excerpt = !empty($p['ai_bio'])
            ? '<span class="table-bio-excerpt">'.htmlspecialchars(mb_substr($p['ai_bio'],0,80), ENT_QUOTES, 'UTF-8')
                .(mb_strlen($p['ai_bio'])>80?"…":"").'</span>'
            : '<span style="color:#db4;">(none)</span>';
        $fullbio = !empty($p['ai_bio'])
            ? '<div class="table-bio-full">'.htmlspecialchars($p['ai_bio'], ENT_QUOTES, 'UTF-8').'</div>'
            : '';
    ?>
    <tr class="<?=$row_class?>">
        <td><?=htmlspecialchars($p['id'] ?? $p['username'])?></td>
        <td><?=htmlspecialchars($p['username'])?><?=$bio_source?></td>
        <td><?=htmlspecialchars($p['display_name'] ?? '')?></td>
        <td><?=$last?><?php if ($version): ?><br><span style="font-size:85%;color:#888;"><?=$version?></span><?php endif; ?></td>
        <td class="bio-td"><?=$excerpt?><?=$fullbio?></td>
    </tr>
<?php endforeach; ?>
</table>
<?= paginator($page, $numPages, $search, $filter, $sort) ?>
<?php elseif (!$error_msg && $total): ?>
    <div style="text-align:center;color:#b33;padding:23px 0;font-size:1.15em;">No bios found on this page/filter.</div>
<?php elseif (!$error_msg): ?>
    <div style="text-align:center;color:#c33;padding:26px 0;font-size:1.22em;">No models in database!</div>
<?php endif; ?>
</body>
</html>