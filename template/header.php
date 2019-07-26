<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "https://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" version="XHTML+RDFa 1.0" lang="en" xml:lang="en" dir="ltr">
<head>
<link rel="stylesheet" href="/template/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
function tpl_header($cmd, $title, $des, $kws)
{
    echo '
<title>' . $title . '</title>
<meta name="google-site-verification" content="'.GOOGLEVALIDATE.'" />
<meta name="msvalidate.01" content="'.MSVALIDATE.'" />
<meta name="description" content="' . $des . '" />
<meta name="keywords" content="" />
<meta name="Rating" content="mature">
<meta name="RATING" content="RTA-5042-1996-1400-1577-RTA"/>

</head>
<body>
<div id="header">

    <div class="section">
        
        <div class="logo-zone">
            
            <div class="logo">
              <a style="text-decoration:none;" href="/"><span style="color:white;">' . SITENAME . ' '.SITELOGO.'</span></a>
                <div class="addthis_inline_share_toolbox" style="float:right; margin-top:25px;"></div>
            </div>
            
        </div>
    
    </div>
    
<!-- Top Navigation Menu -->
<div class="topnav">
  <a href="/">FEATURED CAMS</a>
  <!-- Navigation links (hidden by default) -->
  <div id="myLinks">
    <a href="/' . GENDERSLUG . '/female">FEMALE</a>
    <a href="/' . GENDERSLUG . '/male">MALE</a>
    <a href="/' . GENDERSLUG . '/couple">COUPLES</a>
    <a href="/' . GENDERSLUG . '/shemale">TRANSSEXUALS</a>
    <a href="'.WHITELABEL.'/tags/" rel="nofollow">TAGS</a>
    <a href="'.WHITELABEL.'/accounts/model_register/" rel="nofollow">BROADCAST</a>
    <a href="'.WHITELABEL.'/auth/login/" rel="nofollow">LOGIN</a>
    <a href="'.WHITELABEL.'/accounts/register/" rel="nofollow">REGISTER</a>
  </div>
  <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
  <a href="javascript:void(0);" class="icon" onclick="myFunction()">
    <i class="fa fa-bars"></i>
  </a>
</div>

    
</div>

        <div id="content-wrapper">
                                    
            <div id="content-fill">
                
                    <div class="content-wrapper">
';
}