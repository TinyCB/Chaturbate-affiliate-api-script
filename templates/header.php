<?php $c = include(__DIR__.'/../config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?=isset($meta_title) ? htmlspecialchars($meta_title) : htmlspecialchars($c['site_name'])?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?=isset($meta_desc) ? htmlspecialchars($meta_desc) : htmlspecialchars($c['meta_home_desc'] ?? '')?>">
  <!-- SEO: Open Graph tags for richer sharing -->
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
  </style>
</head>
<body>
<header>
  <img src="/<?=htmlspecialchars($c['logo_path'])?>" alt="<?=htmlspecialchars($c['site_name'])?> Logo" id="logo" />
  <h1><?=htmlspecialchars($c['site_name'])?></h1>
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