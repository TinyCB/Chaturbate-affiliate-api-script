<?php
$c = include(__DIR__.'/../config.php');
$site_url = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '0') ? "https" : "http"
) . "://" . $_SERVER['HTTP_HOST'] . "/";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?=isset($meta_title) ? htmlspecialchars($meta_title) : htmlspecialchars($c['site_name'])?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?=isset($meta_desc) ? htmlspecialchars($meta_desc) : htmlspecialchars($c['meta_home_desc'] ?? '')?>">
  <?php if (!empty($c['google_site_verification'])): ?>
    <meta name="google-site-verification" content="<?=htmlspecialchars($c['google_site_verification'])?>">
  <?php endif; ?>
  <?php if (!empty($c['bing_site_verification'])): ?>
    <meta name="msvalidate.01" content="<?=htmlspecialchars($c['bing_site_verification'])?>">
  <?php endif; ?>
  <meta property="og:title" content="<?=isset($meta_title)?htmlspecialchars($meta_title):htmlspecialchars($c['site_name']??'')?>" />
  <meta property="og:description" content="<?=isset($meta_desc)?htmlspecialchars($meta_desc):htmlspecialchars($c['meta_home_desc']??'')?>" />
  <?php if (isset($model) && isset($model['image_url'])): ?>
    <meta property="og:image" content="<?=htmlspecialchars($model['image_url'])?>" />
  <?php else: ?>
    <meta property="og:image" content="/<?=htmlspecialchars($c['logo_path'])?>" />
  <?php endif; ?>
  <?php if (!empty($c['favicon_path'])): ?>
    <link rel="icon" href="/<?=htmlspecialchars($c['favicon_path'])?>" sizes="any">
    <?php if (preg_match('/\.png$/i', $c['favicon_path'])): ?>
      <link rel="icon" type="image/png" href="/<?=htmlspecialchars($c['favicon_path'])?>">
    <?php endif; ?>
  <?php endif; ?>
  <meta property="og:type" content="website" />
  <link rel="stylesheet" href="/assets/style.css">
  <!-- Font Awesome for info icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    :root { --primary-color: <?=htmlspecialchars($c['primary_color'])?>; }
    a.site-home-link { text-decoration: none; color: inherit; display: flex; align-items: center; }
    a.site-home-link:focus, a.site-home-link:hover { text-decoration: underline; }
    #logo { margin-right: 9px; border-radius: 5px; }
    header h1 { margin: 0; font-size: 1.22em; font-weight: 700; letter-spacing: 0.04em; }
    /* Auto-refresh label matching header link style */
    #auto-refresh-bar label {
      font-family: inherit;
      font-size: 13px;
      font-weight: normal;
      color: #235ec6;
      cursor: pointer;
      margin: 0;
      display:inline-block;
      vertical-align: middle;
    }
    #auto-refresh-bar {
      font-family: inherit;
      font-size: 13px;
      font-weight: normal;
    }
    .auto-refresh-help {
      display: inline-block;
      vertical-align: middle;
      margin-left: 3px;
      cursor: pointer;
      color: #5473ab;
      background: none;
      border: none;
      box-shadow: none;
      width: auto;
      height: auto;
      padding: 0;
      position: relative;
      transition: color .13s;
    }
    .auto-refresh-help i.fa-circle-info {
      font-size: 13px;
      line-height: 1;
      vertical-align: -1px;
      pointer-events: none;
      margin-left: 0;
    }
    .auto-refresh-help:focus, .auto-refresh-help:hover {
      color: #235ec6;
    }
    .auto-refresh-help:focus {
      outline: 1px dotted #235ec6;
      outline-offset: 1px;
    }
    .auto-refresh-tooltip {
      display:none;
      position:absolute;
      left: 105%;
      top: 50%;
      transform: translateY(-50%);
      min-width: 210px;
      max-width: 280px;
      background: #222e39;
      color: #f5fafc;
      font-size: 13.2px;
      border-radius:8px;
      padding: 12px 14px;
      z-index:19;
      box-shadow: 0 8px 32px #1119, 0 2px 4px #0005;
      white-space: normal;
      line-height: 1.45;
      font-weight:400;
      letter-spacing:0.009em;
    }
    .auto-refresh-help:hover .auto-refresh-tooltip,
    .auto-refresh-help:focus .auto-refresh-tooltip {
      display:block;
    }
    .auto-refresh-tooltip:before {
      content: "";
      position:absolute;
      left:-10px;
      top:50%;
      transform:translateY(-50%);
      border-width:7px;
      border-style:solid;
      border-color:transparent #222e39 transparent transparent;
    }
    @media (max-width: 700px) {
      .auto-refresh-help { display:none !important; }
      #auto-refresh-bar { display: none !important; }
    }
  </style>
</head>
<body>
<header>
  <a href="<?=htmlspecialchars($site_url)?>" class="site-home-link">
    <img src="/<?=htmlspecialchars($c['logo_path'])?>" alt="<?=htmlspecialchars($c['site_name'])?> Logo" id="logo" />
    <h1><?=htmlspecialchars($c['site_name'])?></h1>
  </a>
</header>
<nav class="main-navbar">
  <div class="nav-left">
    <?php if(empty($no_filters_button)): ?>
      <button id="filter-toggle" class="filter-btn">&#9776; Filters</button>
      <a id="reset-filters-link" href="#" class="nav-simple-link" style="display:none;">Reset filters</a>
      <!-- Auto-refresh: hidden by default; shown by JS if desktop -->
      <span id="auto-refresh-bar" style="margin-left:12px;display:none;">
        <label style="vertical-align:middle;">
          <input type="checkbox" id="toggle-auto-refresh"> Auto-refresh
        </label>
        <span class="auto-refresh-help" tabindex="0">
          <i class="fa-solid fa-circle-info"></i>
          <span class="auto-refresh-tooltip">
            <b>Auto-refresh</b> keeps the grid up to date every minute.
            <b>New models</b> will appear, <b>offline rooms</b> disappear, and details like thumbnails and subjects will refresh automatically.
          </span>
        </span>
      </span>
    <?php elseif(!empty($back_link)): ?>
      <a href="<?=htmlspecialchars($back_link)?>" class="back-btn">&laquo; Back to all cams</a>
    <?php endif; ?>
  </div>
  <div class="nav-right">
    <?php if(!empty($c['broadcast_url'])): ?>
      <a href="<?=htmlspecialchars($c['broadcast_url'])?>" target="_blank" rel="noopener" class="nav-simple-link">Broadcast Yourself</a>
    <?php endif; ?>
    <?php if(!empty($c['login_url'])): ?>
      <a href="<?=htmlspecialchars($c['login_url'])?>" target="_blank" rel="noopener" class="nav-simple-link">Login</a>
    <?php endif; ?>
  </div>
</nav>
<main>