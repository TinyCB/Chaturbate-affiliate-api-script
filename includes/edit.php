<?php

/* Do not edit the parts between the first brackets. Only between the second brackets. */
/* Note that the PHP version being used for this script is PHP 5.3. and might not work with PHP 7. I might create a version for PHP 7 in the future. */

define ( 'SITENAME',	'Websitename(.tld)'); /* Title for search engines e.g. website name. */

define ( 'BASEHREF',	'/'); /* URL of your website. Usually you don't have to change this. */

define ( 'BASEPATH',	'/'); /* Folder of your website. If in root directory of your website / is sufficient. */

define ( 'whitelabel',  'https://sub.domain.tld'); /* URL of your chaturbate whitelabel. */

define ( 'tagline',		'Place a catchy tagline here!'); /* Tagline for your website shown next to page menu. */

define ( 'title',		'Yourwebsitename'); /* Website title for in your header. */

define ( 'AFFID',		'9EV1k'); /* Chaturbate affiliate ID https://www.chaturbate.com/affiliates/api/onlinerooms/?wm=2DLMP&format=xml where like in my case 2DLMP is my ID. */

define ( 'TRACK',		'' ); /* Tracking code to track your affiliate stats. You can put anything in here as you please. */

define ( 'googleverification',		'webmaster verification tag'); /* Google webmasters verification tag */

define ( 'headertxt',				'HEADER TEXT'); /* Text being displayed at the top of your webpage. Write something catchy here. */

define ( 'mainpagetitletag',		'title tagline for Google snippet'); /* Title tagline for your Google snippet which will be displayed together with your website name */ 

define ( 'subpagetitletag',		'' . $_GET['arg1'] . ' title subpage tagline for Google snippet'); /* $_GET['arg1'] grabs female,male,couple,transsexual and model names */

define ( 'modelpagetitletag',	'' . $_GET['arg1'] . '\'s title model page tagline for Google Snippet'); /* $_GET['arg1'] grabs female,male,couple,transsexual and model names */

?>