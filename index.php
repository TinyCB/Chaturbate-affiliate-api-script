<?php

$feed = 'https://www.chaturbate.com/affiliates/api/onlinerooms/?wm='.AFFID.'&format=xml';

$copy = 1;

if (time() - @filemtime("chaturbate.xml") < 120) {

        $feed = "chaturbate.xml";

        $copy = 0;

        }



$fh = fopen($feed,"r");

if ($copy) $saver = fopen("chaturbate.xml","w");

while ($xmlcontent = fread($fh,1024)) {

        if ($copy) fputs($saver,$xmlcontent);

        xml_parse($parser,$xmlcontent,feof($fh));

}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "https://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" version="XHTML+RDFa 1.0" lang="en" xml:lang="en" dir="ltr">
<head>
<style>
#header,#menu,#nav,.bio-info,.camwrap{padding:0}body,html,li,ul{padding:0;margin:0}body,html{height:100%;with:100%;background:#e2e2e2}.age,.gender,.username{z-index:100;background:rgba(255,255,255,.5);width:180px;position:absolute}li,ul{list-style-type:none}.gender{margin-top:115px}.subject{min-height:25px;max-height:25px;background:#eee;background:-moz-linear-gradient(top,#eee 0,#ccc 100%);background:-webkit-linear-gradient(top,#eee 0,#ccc 100%);background:linear-gradient(to bottom,#eee 0,#ccc 100%);filter:progid: DXImageTransform.Microsoft.gradient(startColorstr='#eeeeee', endColorstr='#cccccc', GradientType=0);width:180px;position:absolute;margin-top:146px}#header,#nav,#nav li,.logo,.logo-zone{position:relative}.camwrap{background:#f0f1f1;border:1px solid grey;border-radius:3px;width:180px;height:211px;font-family:arial;font-size:10px;margin:3px;float:left}#menu-template,#nav,.cam-info,.logo-zone,footer{font-family:anton}#header{min-width:960px;height:162px;width:100%;margin:0;background-color:orange}.section{height:80px;width:100%;background-color:#000}.nav-bar{height:44px;width:100%;background-color:#292929}#nav,.logo{width:96.7%;min-width:960px}#nav{text-shadow:1px 1px 0 #000;margin:0 auto;height:36px}#nav li{color:#ff0;font-size:16px;height:30px;top:10px;float:left;margin:0 20px 0 0}#nav a{color:#ff0;text-decoration:none}.logo-zone{height:65px;color:#fff;font-size:38px;top:10px}.logo{margin:0 auto}.video-spacer,.video-wrapper{position:relative;width:100%;border-left:2px solid #acacac;border-right:2px solid #acacac}.video-wrapper{margin:25px auto 0;height:auto;background-color:#e0e0e0;border-top:2px solid #acacac;border-top-left-radius:6px;border-top-right-radius:6px;float:left}.video{position:absolute;top:0;left:0;width:99.81%;height:100%;border:none}.bio-info,.cam-info,.cam-reviews{width:100%;float:left;position:relative}.video-spacer{margin:0 auto;height:25px;background-color:#acacac;float:left}.cam-info,.cam-reviews{border-left:2px solid #acacac;border-right:2px solid #acacac;height:auto}.cam-info{margin:0 auto;background-color:#7f7f7f}.bio-info{margin:0 auto;background-color:#fff}.cam-reviews{margin:0 auto 25px;background-color:#e0e0e0;border-bottom:2px solid #acacac;border-bottom-left-radius:6px;border-bottom-right-radius:6px}.search{float:right;width:235px;position:relative;top:7px}.search input{height:20px}.broadcaster-title{margin:0 auto;position:absolute;color:#DC143C;top:10px;left:10px}#menu-template,.bio-section-1,.bio-section-2,.content-wrapper,.iframe-container,.nav{position:relative}#menu nav a,#menu nav span,#menu-template nav a,#menu-template nav span{display:inline-block;color:#000;padding:5px 15px;border-top-left-radius:5px;border-top-right-radius:5px;margin-top:10px;position:relative;text-decoration:none}#menu{float:right;margin:0 auto}#menu nav a,#menu nav span{background:#fff;font-size:12px}#menu nav a:hover{background:#e0e0e0}#menu-template{margin:0 auto;min-width:960px;width:96.7%;padding:0;font-size:14px;z-index:9999}#menu-template nav a,#menu-template nav span{background:#e2e2e2}#menu-template nav a:hover{background-color:#7f7f7f}.bio-section-1{margin:0auto;width:100%;float:left}.bio-section-2{margin:0 auto;width:100%;float:left}.bio-info-list{margin:55px 55px 0;padding:0;float:left;width:100%;font-size:19px}#content-wrapper{min-width:960px;width:100%;height:100%}.content-wrapper{width:97%;height:100%;margin:0 auto}#content-fill{width:100%;height:100%;background-color:#e2e2e2}.bio-section-wrapper1,.bio-section-wrapper2{width:auto;height:auto;float:left}#top-menu-wrapper{min-width:960px;width:100%;float:left;background-color:#fff}.push,footer{height:110px}footer{display:table;font-size:14px;background-color:#000;min-width:960px;width:100%}footer a{color:#fff;text-decoration:none;display:block}footer a:hover{text-decoration:underline}footer ul{list-style:none;padding-left:0}footer li{padding:5px}.footerLeft{float:left;padding:30px 20px 10px}.footerLeft li{float:left}.footerRight{float:right;padding:30px 20px 10px}.footerRight li{float:left}.pagination-wrapper{width:100%;height:55px;font-family:anton;float:left}.cb_pager{width:97%;text-align:center}.disabled{margin-right:25px}.pagination-wrapper a{color:orange;padding:22px}#cmtx_rating,.cmtx_rating{background-color:#fff}.iframe-container{overflow:hidden;padding-top:56.25%}.iframe-container iframe{border:0;height:100%;left:0;position:absolute;top:0;width:100%}.iframe-container-4x3{padding-top:75%}@font-face{font-family:Anton;font-style:normal;font-weight:400;src:local('Anton Regular'),local('Anton-Regular'),url(https://fonts.gstatic.com/s/anton/v9/1Ptgg87LROyAm3K8-C8QSw.woff2) format('woff2');unicode-range:U+0102-0103,U+0110-0111,U+1EA0-1EF9,U+20AB}@font-face{font-family:Anton;font-style:normal;font-weight:400;src:local('Anton Regular'),local('Anton-Regular'),url(https://fonts.gstatic.com/s/anton/v9/1Ptgg87LROyAm3K9-C8QSw.woff2) format('woff2');unicode-range:U+0100-024F,U+0259,U+1E00-1EFF,U+2020,U+20A0-20AB,U+20AD-20CF,U+2113,U+2C60-2C7F,U+A720-A7FF}@font-face{font-family:Anton;font-style:normal;font-weight:400;src:local('Anton Regular'),local('Anton-Regular'),url(https://fonts.gstatic.com/s/anton/v9/1Ptgg87LROyAm3Kz-C8.woff2) format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD}.cam-thumbnails{width:180px;height:148px;float:left;border-top-left-radius:2px;border-top-right-radius:2px;}
</style>
<?php
function tpl_header($cmd, $title, $des, $kws) {
echo'
<title>' . $title . '</title>
<meta name="google-site-verification" content="'.googleverification.'" />
<meta name="description" content="'.$des.'" />
<meta name="keywords" content="" />
<meta name="Rating" content="mature">
<meta name="RATING" content="RTA-5042-1996-1400-1577-RTA"/>
<link rel="canonical" href="https://' . $_SERVER['HTTP_HOST'].'' . $_SERVER['REQUEST_URI'] .'"/>
</head>
<body>
<div id="header">

	<div class="nav-bar">
	
	<p style="color:white; margin-left:22px; padding:8px; font-family:anton; margin-top:0px; position:relative;min-width:960px;">
	
	'.headertxt.'
	
	</p>

	</div>
	
	<div class="section">
		
		<div class="logo-zone">
			
			<div class="logo">
				<a style="text-decoration:none;" href="/"><span style="color:white;">'.title.'</span></a>
				<div class="addthis_inline_share_toolbox" style="float:right; margin-top:25px;"></div>
			</div>
			
		</div>
	
	</div>
	
	<div class="nav-bar">
		<ul id="nav">
		<li class="menu-item"><a href="/">CHAT ROOMS</a></li>
		<li class="menu-item"><a href="'.whitelabel.'/accounts/model_register/" rel="nofollow">BROADCAST YOURSELF</a></li>
		<li class="menu-item"><a href="'.whitelabel.'/tags/" rel="nofollow">TAGS</a></li>		
		<li class="menu-item"><a href="'.whitelabel.'/auth/login/" rel="nofollow">LOGIN</a></li>
		<li class="menu-item"><a href="'.whitelabel.'/accounts/register/" rel="nofollow">SIGN UP</a></li>
		</ul>		
	</div>
	
</div>

		<div id="content-wrapper">
						
						<div id="top-menu-wrapper">

							<div id="menu-template">
								<div class="menu">
									<div class="nav">
										<nav class="top-menu">
										<a href="/">FEATURED</a>
										<a href="/cams/female">FEMALE</a>
										<a href="/cams/male">MALE</a>
										<a href="/cams/couple">COUPLE</a>
										<a href="/cams/shemale">TRANSSEXUAL</a>
										<span style="background-color:white; font-family:arial; font-style:italic; font-size:11px;">
										'.tagline.'
										</span>
										</nav>
									</div>
								</div>
							</div>

						</div>
			
			<div id="content-fill">
				
					<div class="content-wrapper">
';}
include ('includes/functions.php'); 
?>
				</div>
				
			</div>
			
		</div>
		
<div style="height:50px; width:100%; float:left;"></div>

	<footer>
	
		    <div class="wrapper">
		    	
					<ul class="footerLeft">
				        <li><a href="/">FEATURED</a></li>
				        <li><a href="/cams/female">FEMALE</a></li>
				        <li><a href="/cams/male">MALE</a></li>
						<li><a href="/cams/couple">COUPLE</a></li>
				        <li><a href="/cams/shemale">TRANSSEXUAL</a></li>
			        </ul>
					
			        <ul class="footerRight">
						<li style="color:white;"><a href="/">&copy; 2018 | <?php echo ''.SITENAME.'' ?></a></li>
			        </ul>
					
	        </div>
			
	</footer>
		
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5550b8fc3ee9489a"></script>
</body>
</html>
<?php
class axl_Core

	{
		
		var $commands;
		var $headerFunc;
		var $footerFunc;
		function axl_Core()
		
	{
		
		$this->commands = array();
		
	}
	
		function setHeaderFunc($func)

	{
		
		$this->headerFunc = $func;
		return true;
		
	}
	
		function setFooterFunc($func)
		
	{
		
		$this->footerFunc = $func;
		return true;
		
	}
	
function start()	{
	
	$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'home';
	$args = array();
	if (isset($_GET['arg1']))
	$args[0] = $_GET['arg1'];
	if (isset($_GET['arg2']))
	$args[1] = $_GET['arg2'];
	if (!isset($this->commands[$cmd]))
		
	{
		
		header('HTTP/1.0 404 Not Found');
		$this->outputHeader('404', '404 - File Not Found', 'You have reached our error page', '404 error');
		echo '<div class="box">';
		echo '<div class="title"><h2>File not found</h2></div>';
		echo '<div class="content">';
		echo '<p>The requested URL <strong>' . strip_tags($_SERVER['REQUEST_URI']) . '</strong> was not found.</p>';
		echo '</div>';
		echo '</div>';
		$this->outputFooter();
		return false;
	}
	
		$c = $this->commands[$cmd];
		if ($c[3])
		
	{
		
		$this->outputHeader($cmd, $c[1], $c[2], $c[3]);
		call_user_func($c[0], $args);
		$this->outputFooter();
		
	}
	
		else
		call_user_func($c[0], $args);
		return true;
		
	}
	
function outputHeader($cmd, $title, $des, $kw)	{
	
	if (isset($this->headerFunc))
	return call_user_func($this->headerFunc, $cmd, $title, $des, $kw);
	return false;
	
	}
	
function outputFooter()	{
	
	if (isset($this->footerFunc))
	return call_user_func($this->footerFunc);
	return false;
	
	}
	
function addCommand($cmd, $function, $title=NULL, $des=NULL, $kw=NULL)	{
	
		$this->commands[$cmd] = array($function, $title, $des, $kw);
		return true;

	}

	}
	
function get_cams( $affid, $track, $gender, $limit ){
	
		if ( $_GET['arg1'] ) {
		if ( is_numeric( $_GET['arg1'] ) ) {
		$page = $_GET['arg1'];
		$targetpage = 'cams/';

	} 
		else 
	{
		
		$targetpage = 'cams/' . $_GET['arg1'] . '/';
		if ( $_GET['arg2']  ) {
		if ( is_numeric( $_GET['arg2'] ) ) {
		$page = $_GET['arg2'];
	
	}

	}
		else 
	{
		
		$page = 1;
	
	}
	
	}
	
	}
	
		else 
			
	{
		
		$targetpage = 'cams/';
		$page = 1;
	
	}
	
		$end 	= $page * $limit;
		$start	= $end - $limit;
		$dom = new DomDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		$xml = 'chaturbate.xml';
		$dom->Load($xml);
		$cams = new SimpleXMLElement($xml, 0, true);

if ( $gender != $gender ) {
	
		$totalCams = 0;
		foreach( $dom->getElementsByTagName('gender') as $tag ) 
	
	{
		
foreach( $tag->childNodes as $child ) {
	
		$i = $dom->saveXML($child);
		if ($i == $gender )
		$totalCams++;
	
	}

	}
	
	} 
		else 
	{
		
		$totalCams = count($cams);

	}
	
		echo '<ul>';
		$count = '';
		
foreach( $cams as $cam ){ 

if ( $cam->gender == $gender ) {
	
if ( $count >= $start && $count < $end ) {
	
		print_cams($cam);
		
	}

		$count++;
		
	}

if ( $gender == '' ) {
	
if ( $count >= $start && $count < $end ) {
	
		print_cams($cam);
		
	}
		$count++;
	}

	}
		echo '</ul>';
		echo '<div style="clear: both;">&nbsp;</div>';
		paginate($page, $totalCams, $limit, $targetpage);
	}

function solo_cams( $affid, $track, $user, $cam ) {
			$cams = new SimpleXMLElement('chaturbate.xml', null, true);
				foreach ($cams as $cam);
					echo '<div class="video-wrapper">';
					echo '<div class="biolink" style="font-family:arial; margin-top:5px; margin-bottom:5px; margin-left:5px; font-weight:bold;"> Visit <a rel="nofollow" href="'.whitelabel.'/'. $_GET['arg1'] . '">'. $_GET['arg1'] . '\'s</a> main profile page for more info and options. </div>';
					echo '<iframe src="'.whitelabel.'/in/?tour=Jrvi&campaign='.AFFID.'&track='.TRACK.'&room='. $_GET['arg1'] . '&bgcolor=transparent&disable_sound=1&embed_video_only=0&target=_parent" style="height:491px; width:100%; border: none;" allowfullscreen></iframe>';
					echo '</div>';
				foreach( $cams as $cam ){ 
			if ( $cam->username == $user ) {
		if ( MODE == 'revshare' ) {
	echo $cam->iframe_embed;

	} else {

		$subject = ''.$cam->room_subject.'';
		$name	 = ''.$cam->display_name.'';
		$image   = ''.$cam->image_url.'';
		$gender  = ''.$cam->gender.'';
		$age	 = ''.$cam->age.'';
		$language= ''.$cam->spoken_languages.'';
		$location= ''.$cam->location.'';
		$Birth	 = ''.$cam->birthday.'';
		$time	 = ''.$cam->seconds_online.'';
		$time_online = ago( ''.$cam->seconds_online.'' );

		if ($gender=="f") $gender="Female";
		if ($gender=="m") $gender="Male";
		if ($gender=="c") $gender="Couple";
		if ($gender=="s") $gender="Transsexual";	
		
echo '
<div class="video-spacer">
</div>

<div class="cam-info">

<div id="menu">
	<div class="menu">
		<div class="nav">
			<nav class="top-menu">
			<span>Room Subject: '.$subject.'</span>
			</nav>
		</div>
	</div>
</div>


<div class="bio-info">
	
	<div class="bio-section-wrapper1">
		
		<div class="bio-section-1">
				<h2 class="broadcaster-title">' .$name.'</h2> 
				<img style="float:left; width:100%;"src="'.$image.'"/>
		</div>

	</div>
	
	<div class="bio-section-wrapper2">
		
		<div class="bio-section-2">
			
			<ul class="bio-info-list">
				<li> Gender: ' .$gender. ' </li>
				<li> Age: ' .$age.' </li>
				<li> Language: ' .$language. ' </li>
				<li> Location: ' .$location. ' </li>
				<li> Birth Date: ' .$Birth. ' </li>
				<li> Time Online: ' .$time_online. ' </li> 
			</ul>
		
		</div>
	
	</div>';
	
			echo '<div style="border-top:1px solid grey;width:100%;float:left;">';
			echo '<div style="margin-top:3px; margin-bottom:3px; margin-left:3px; with:100%; height:100%; margin:0auto; float:left; position:relative;">';
			random_text($cam->username, $cam->age, $cam->location, $cam->num_users, $time_online  );
			echo '</div>';
			echo '</div>';	
	
echo '</div>

</div>

';

}

}

}
echo '<div class="video-spacer"></div>';
}
?>