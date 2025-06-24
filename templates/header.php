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
  <meta property="og:type" content="website" />
  <link rel="stylesheet" href="/assets/style.css">
  <style>
    :root { --primary-color: <?=htmlspecialchars($c['primary_color'])?>; }
    a.site-home-link { text-decoration: none; color: inherit; display: flex; align-items: center; }
    a.site-home-link:focus, a.site-home-link:hover { text-decoration: underline; }
    #logo { margin-right: 9px; border-radius: 5px; }
    header h1 { margin: 0; font-size: 1.22em; font-weight: 700; letter-spacing: 0.04em; }
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