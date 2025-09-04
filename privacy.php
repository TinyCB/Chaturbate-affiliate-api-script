<?php
$config = include(__DIR__ . '/config.php');
$sitename = trim($config['site_name'] ?? '');
if ($sitename === '' || strtolower($sitename) === 'tinycb') {
    $sitename = preg_replace('~^www\.~', '', $_SERVER['HTTP_HOST']);
}
$theme_color = htmlspecialchars($config['primary_color'] ?? '#ffa927');
$privacyEmail = !empty($config['privacy_email']) ? $config['privacy_email'] : 'youremail@example.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Privacy Policy – <?=htmlspecialchars($sitename)?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
  :root { --primary-color: <?=$theme_color?>; }
  body {
    background: #fafafd;
    color: #28303d;
    font-family: 'Roboto', Arial, sans-serif;
    max-width: 700px;
    margin: 37px auto 30px auto;
    line-height: 1.6;
    padding: 2vw;
  }
  h1 {
    color: var(--primary-color);
    font-size: 2.1em;
    margin-bottom: 22px;
    margin-top: 13px;
    font-family: inherit;
    font-weight: 700;
  }
  h2 {
    color: var(--primary-color);
    font-size: 1.18em;
    font-weight: bold;
    margin-top: 1.7em;
    margin-bottom: .7em;
  }
  ul { margin-left: 1em; list-style-type: disc;}
  a { color: var(--primary-color); text-decoration: underline;}
  .pp-section { margin-bottom: 2.1em; }
  .key {color:var(--primary-color); font-weight:600;}
  .pp-card {
    border-radius: 16px;
    background: #f8fafc;
    box-shadow: 0 3px 17px #22344a13;
    padding:32px 32px 17px 32px;
    max-width: 675px;
    margin:0 auto 17px auto;
  }
  @media (max-width:700px) {
    .pp-card {padding:4vw 3vw;max-width:98vw;}
    body{padding:2vw;}
  }
  </style>
</head>
<body>
<div class="pp-card">
<h1>Privacy Policy</h1>
<div class="pp-section">
  <p>
    <b><?=htmlspecialchars($sitename)?></b> is a Chaturbate whitelabel/live cams frontend.  
    We display live content and features through Chaturbate’s official whitelabel system.   
    No personal or account data is processed or stored by this site.
  </p>
</div>
<div class="pp-section">
  <h2>1. Essential Cookies</h2>
  <ul>
    <li>This site uses <span class="key">essential cookies</span> to remember user filter/state preferences and cookie settings only.</li>
    <li>No personal or identifying information is stored in these cookies.</li>
    <li>These cookies cannot be turned off as they are required for the basic operation of the site.</li>
  </ul>
</div>
<div class="pp-section">
  <h2>2. Optional Tracking Cookies (Google Analytics)</h2>
  <ul>
    <li>If you consent, we use Google Analytics to anonymously understand page usage, improve site content, and monitor performance.</li>
    <li>You may disable these tracking cookies at any time in the cookie notice popup (bottom corner).</li>
    <li>No personally identifiable or sensitive data is ever collected by analytics on this site.</li>
    <li>See: <a href="https://policies.google.com/technologies/partner-sites" rel="noopener" target="_blank">How Google uses data when you use our partners’ sites or apps</a></li>
  </ul>
</div>
<div class="pp-section">
  <h2>3. Chaturbate Whitelabel Content & External Data</h2>
  <ul>
    <li>All live cam content, chat, and model profile data are embedded from Chaturbate’s whitelabel platform (via iframe/API).</li>
    <li>Any Chaturbate account creation, payments, or user login are handled <b>by Chaturbate</b> and are subject to <a href="https://chaturbate.com/international/privacy-policy/" rel="noopener" target="_blank">Chaturbate’s Privacy Policy</a>.</li>
    <li>This website does not process or store user credentials, payment information, or PII.</li>
  </ul>
</div>
<div class="pp-section">
  <h2>4. Contact & Data Rights</h2>
  <ul>
    <li>No personal data is processed or stored. If you have privacy concerns or questions, contact:
    <a href="mailto:<?=htmlspecialchars($privacyEmail)?>"><?=htmlspecialchars($privacyEmail)?></a></li>
  </ul>
</div>
<div class="pp-section" style="margin-bottom:7px;">
  <b>Last updated:</b> <?=date('Y-m-d')?>
</div>
<p>
  <a href="/" style="color:var(--primary-color);">← Back to Home</a>
</p>
</div>
</body>
</html>
