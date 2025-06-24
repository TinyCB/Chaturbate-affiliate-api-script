<?php
session_start();
$CONFIG_FILE = "config.php";
$config = include($CONFIG_FILE);
$genders = ['f'=>'Female','m'=>'Male','t'=>'Trans','c'=>'Couple'];
if (empty($config['slugs']) || !is_array($config['slugs'])) {
    $config['slugs'] = [
        'f' => 'girls',
        'm' => 'guys',
        't' => 'trans',
        'c' => 'couples',
        'model' => 'model'
    ];
    file_put_contents($CONFIG_FILE, "<?php\nreturn ".var_export($config,true).";\n");
}
if (!isset($config['privacy_email'])) $config['privacy_email'] = 'youremail@example.com';
if (!isset($config['google_analytics_id'])) $config['google_analytics_id'] = '';
if (!isset($config['google_site_verification'])) $config['google_site_verification'] = '';
if (!isset($config['bing_site_verification'])) $config['bing_site_verification'] = '';
if (empty($config['admin_password_hash'])) {
    $config['admin_password_hash'] = password_hash('changeme', PASSWORD_DEFAULT);
    file_put_contents($CONFIG_FILE, "<?php\nreturn ".var_export($config,true).";\n");
}
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === "POST"
        && isset($_POST['admin_password'])
        && password_verify($_POST['admin_password'], $config['admin_password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: /admin');
        exit;
    } else {
        ?>
        <form method="POST" style="margin:120px auto;max-width:350px;">
            <h2>Admin Login</h2>
            <input type="password" name="admin_password" placeholder="Admin password" style="width:100%;padding:8px;margin-bottom:10px;">
            <button style="width:100%;padding:7px;">Login</button>
            <?php if ($_SERVER['REQUEST_METHOD'] === "POST") echo "<div style='color:#c22;text-align:center;'>Invalid or missing password</div>"; ?>
        </form>
        <?php exit;
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /admin");
    exit;
}
if($_SERVER['REQUEST_METHOD']==="POST" && isset($_POST['site_name'])) {
    $config['site_name'] = $_POST['site_name'];
    $config['affiliate_id'] = $_POST['affiliate_id'];
    $config['primary_color'] = $_POST['primary_color'];
    $config['footer_text'] = $_POST['footer_text'];
    $config['cams_per_page'] = (int)($_POST['cams_per_page'] ?? 20);
    $config['whitelabel_domain'] = trim($_POST['whitelabel_domain'] ?? 'chaturbate.com');
    $config['login_url'] = trim($_POST['login_url'] ?? '');
    $config['signup_url'] = trim($_POST['signup_url'] ?? '');
    $config['broadcast_url'] = trim($_POST['broadcast_url'] ?? '');
    $config['google_analytics_id'] = trim($_POST['google_analytics_id'] ?? '');
    $config['privacy_email'] = trim($_POST['privacy_email'] ?? '');
    $config['google_site_verification'] = trim($_POST['google_site_verification'] ?? '');
    $config['bing_site_verification'] = trim($_POST['bing_site_verification'] ?? '');
    $config['meta_home_title'] = $_POST['meta_home_title'];
    $config['meta_home_desc'] = $_POST['meta_home_desc'];
    $config['meta_gender_titles'] = $_POST['meta_gender_titles'] ?? [];
    $config['meta_gender_descs'] = $_POST['meta_gender_descs'] ?? [];
    if (isset($_POST['slugs']) && is_array($_POST['slugs'])) {
        foreach (['f','m','t','c','model'] as $k) {
            if (isset($_POST['slugs'][$k]) && $_POST['slugs'][$k] !== '') {
                $config['slugs'][$k] = trim($_POST['slugs'][$k]);
            }
        }
    }
    if(!empty($_FILES['logo_file']['tmp_name'])) {
        $fn = 'assets/logo.png';
        move_uploaded_file($_FILES['logo_file']['tmp_name'], $fn);
        $config['logo_path']=$fn;
    }
    if (!empty($_POST['current_admin_password']) && !empty($_POST['new_admin_password'])) {
        if (password_verify($_POST['current_admin_password'], $config['admin_password_hash'])) {
            $config['admin_password_hash'] = password_hash($_POST['new_admin_password'], PASSWORD_DEFAULT);
            $success = "Admin password updated.";
        } else {
            $error = "Current admin password was incorrect. Password not changed.";
        }
    }
    file_put_contents($CONFIG_FILE, "<?php\nreturn ".var_export($config,true).";\n");
    if (empty($error)) $success = ($success ?? '') . " Settings saved.";
}
?><!DOCTYPE html>
<html><head>
<title>Admin</title>
<link rel="stylesheet" href="assets/admin.css">
<style>
:root{--primary-color:<?=$config['primary_color']?>;}
body{background:#fafafd;}
form{background:#fff;margin:40px auto;max-width:460px;padding:30px 28px;border-radius:10px;box-shadow:0 3px 20px rgba(60,90,150,.10);}
label{display:block;margin-bottom:4px;}
input,button{padding:6px;width:98%;margin-bottom:13px;}
button{background:var(--primary-color);color:#fff;border:none;width:100%;border-radius:5px;}
@media (max-width:600px) {form{max-width:98vw;padding:1vw 2vw;}}
</style>
</head>
<body>
<h2 style="text-align:center;">Site Admin</h2>
<?php
if(!empty($error))   echo "<div style='color:red;text-align:center;'>$error</div>";
if(!empty($success)) echo "<div style='color:green;text-align:center;'>$success</div>";
?>
<form method="POST" enctype="multipart/form-data" autocomplete="off">
    <label>Site Name</label>
    <input name="site_name" value="<?=htmlspecialchars($config['site_name'])?>">
    <label>Affiliate ID</label>
    <input name="affiliate_id" value="<?=htmlspecialchars($config['affiliate_id'])?>">
    <label>Login URL</label>
    <input name="login_url" value="<?=htmlspecialchars($config['login_url'] ?? '')?>">
    <label>Sign Up URL</label>
    <input name="signup_url" value="<?=htmlspecialchars($config['signup_url'] ?? '')?>">
    <label>Broadcast Yourself URL</label>
    <input name="broadcast_url" value="<?=htmlspecialchars($config['broadcast_url'] ?? '')?>">
    <label>Google Analytics Tag (Measurement ID)</label>
    <input name="google_analytics_id" placeholder="G-XXXXXXXXXX" value="<?=htmlspecialchars($config['google_analytics_id'] ?? '')?>">
    <label>Privacy Policy Contact Email</label>
    <input name="privacy_email" type="email" value="<?=htmlspecialchars($config['privacy_email'] ?? '')?>">
    <label>Google Site Verification Tag <span style="font-weight: normal">(content only)</span></label>
    <input name="google_site_verification" value="<?=htmlspecialchars($config['google_site_verification'] ?? '')?>">
    <label>Bing Site Verification Tag <span style="font-weight: normal">(content only)</span></label>
    <input name="bing_site_verification" value="<?=htmlspecialchars($config['bing_site_verification'] ?? '')?>">
    <label>Primary Color Website</label>
    <input type="color" name="primary_color" value="<?=htmlspecialchars($config['primary_color'])?>">
    <label>Footer Text</label>
    <input name="footer_text" value="<?=htmlspecialchars($config['footer_text'])?>">
    <label>Cams Per Page</label>
    <input name="cams_per_page" type="number" min="1" max="500" value="<?=htmlspecialchars($config['cams_per_page']??20)?>">
    <label>Whitelabel Domain (e.g. cam.mysite.com, no http://)</label>
    <input name="whitelabel_domain" value="<?=htmlspecialchars($config['whitelabel_domain'] ?? 'chaturbate.com')?>">
    <h3>URL Slugs</h3>
    <label>Slug for Female</label>
    <input name="slugs[f]" value="<?=htmlspecialchars($config['slugs']['f'] ?? 'girls')?>">
    <label>Slug for Male</label>
    <input name="slugs[m]" value="<?=htmlspecialchars($config['slugs']['m'] ?? 'guys')?>">
    <label>Slug for Trans</label>
    <input name="slugs[t]" value="<?=htmlspecialchars($config['slugs']['t'] ?? 'trans')?>">
    <label>Slug for Couple</label>
    <input name="slugs[c]" value="<?=htmlspecialchars($config['slugs']['c'] ?? 'couples')?>">
    <label>Slug for Model Profiles</label>
    <input name="slugs[model]" value="<?=htmlspecialchars($config['slugs']['model'] ?? 'model')?>">
    <h3>SEO Meta Tags</h3>
    <label>Meta Title (Homepage)</label>
    <input name="meta_home_title" value="<?=htmlspecialchars($config['meta_home_title'] ?? '')?>">
    <label>Meta Description (Homepage)</label>
    <input name="meta_home_desc" value="<?=htmlspecialchars($config['meta_home_desc'] ?? '')?>">
    <h3>Gender Page SEO</h3>
    <?php foreach($genders as $g=>$label): ?>
      <div style="background:#f7fbef;padding:7px 13px;margin-bottom:5px;border-radius:7px;">
        <b><?=$label?>:</b>
        <label>Meta Title</label>
        <input name="meta_gender_titles[<?=$g?>]" value="<?=htmlspecialchars($config['meta_gender_titles'][$g] ?? '')?>">
        <label>Meta Description</label>
        <input name="meta_gender_descs[<?=$g?>]" value="<?=htmlspecialchars($config['meta_gender_descs'][$g] ?? '')?>">
      </div>
    <?php endforeach; ?>
    <label>Logo</label>
    <input type="file" name="logo_file"><br>
    <img src="<?=$config['logo_path']?>" style="max-width:100px;"><br>
    <h3>Change Admin Password</h3>
    <label>Current Password</label>
    <input type="password" name="current_admin_password" autocomplete="current-password">
    <label>New Password</label>
    <input type="password" name="new_admin_password" autocomplete="new-password">
    <button type="submit">Save Settings</button>
</form>
<p style="text-align:center; margin-top:35px;">
    <a href="index.php">Back to site</a>
    &nbsp;|&nbsp;
    <a href="/admin?logout=1" onclick="return confirm('Log out?')">Log out</a>
</p>
<?php
if ($config['admin_password_hash'] && password_verify('changeme', $config['admin_password_hash'])) {
    echo "<div style='color:#db2727;max-width:480px;margin:20px auto;background:#ffeeee;border-radius:7px;padding:13px;text-align:center;font-weight:bold;font-size:1.03em;'>For security, <b>please change the default admin password</b> now.</div>";
}
?>
</body></html>