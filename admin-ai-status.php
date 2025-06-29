<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: /admin.php"); exit;
}
$cache_dir = __DIR__ . "/cache/";
$profile_file = $cache_dir . "model_profiles.json";
$total = 0; $with_bio = 0;
if (file_exists($profile_file)) {
    $json = file_get_contents($profile_file);
    $profiles = json_decode($json, true);
    if (is_array($profiles)) {
        foreach ($profiles as $m) {
            $total++;
            if (!empty($m['ai_bio'])) $with_bio++;
        }
    }
}
$missing = $total - $with_bio;
$percent = $total > 0 ? round(($with_bio / $total) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Bio Status</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body {
            background:#fafafd;
            font-family: system-ui, Arial, sans-serif;
        }
        .admin-tabs {
            display: flex;
            justify-content: center;
            gap: 2px;
            margin: 30px 0 18px 0;
            font-size: 1.07em;
        }
        .admin-tabs a {
            background: #f4f5fc;
            color: #595bb8;
            text-decoration: none;
            padding: 10px 34px;
            border-top-left-radius: 7px;
            border-top-right-radius: 7px;
            margin-right:2px;
            border: 1px solid #d2d6f1;
            border-bottom: none;
            font-weight: 600;
            transition: background .15s, color .15s;
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
            margin: 0 auto 0 auto;
            max-width: 440px;
            border-radius: 10px;
            padding: 22px 30px 24px 30px;
            box-shadow: 0 3px 18px #746dc347;
        }
        .ai-status-title {
            color: #3f3c93;
            font-size: 1.36em;
            margin-bottom: 18px;
            text-align:center;
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
            width: <?= $percent ?>%;
            position: absolute; left:0; top:0;
            transition: width 0.28s;
            min-width: 20px;
        }
        .ai-status-bar span {
            text-align:center;
            display: block;
            width: 100%;
            color: #32396a;
            font-size:1.09em;
            font-weight:600;
            position: absolute; left:0; right:0; top:0; bottom:0;
            line-height:24px;
        }
        .ai-status-stats {
            font-size:1.13em;
            margin-bottom:14px;
            display:flex;
            flex-direction:column;
            gap:6px 0;
        }
        .ai-status-label {
            display: inline-block;
            font-weight:600; color:#40497d;
            min-width:120px;
        }
        @media (max-width:600px) {
            .ai-status-box {max-width:99vw;padding:14px 3vw 16px 3vw;}
            .admin-tabs a {padding:8px 1em;}
        }
    </style>
</head>
<body>
    <div class="admin-tabs">
        <a href="/admin">Site Settings</a>
        <a href="/admin-ai-status" class="selected">AI Bio Status</a>
    </div>
    <div class="ai-status-box">
        <div class="ai-status-title">AI Bio Progress</div>
        <div class="ai-status-progress">
            <div class="ai-status-bar">
                <span><?= $percent ?>%</span>
            </div>
        </div>
        <div class="ai-status-stats">
            <div><span class="ai-status-label">Total models:</span> <?=number_format($total)?></div>
            <div><span class="ai-status-label">With bio:</span> <?=number_format($with_bio)?></div>
            <div><span class="ai-status-label">Missing bio:</span> <?=number_format($missing)?></div>
        </div>
        <div style="font-size:0.99em;margin:13px 0 0 0;color:#5072bd;text-align:center;">
            Bios are (re)generated via the batch script.<br>
            Refresh this page for live stats.
        </div>
    </div>
</body>
</html>