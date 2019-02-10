<?php

/* 

   For this script. It is highly recommended to create a Chaturbate whitlabel on a subdomain seperate from this script.
   The script does function without it, however some menu items are pointing towards pages which don't use a tracking code.
   
   If you haven't signed up with Chaturbate yet, 
   please consider using my affiliate link to sign up with Chaturbate,
   and keep me motivated to update this script further with more functionality.
   
   My affiliate link: https://chaturbate.com/in/?track=default&tour=9O7D&campaign=2DLMP
   
   Thank you in advance!
   
*/

/* Must be set */
define ( 'SITENAME',	'Localhost'); 		  /* Website name*/
define ( 'BASEHREF',	'http://localhost/'); /* Website URL */
define ( 'BASEPATH',	'http://localhost/'); /* Website URL */
define ( 'AFFID',		'2DLMP' );			  /* Affiliate ID */
define ( 'TRACK',		'TinyCB' );			  /* Tracking code */

/* Optional but recommended */
define ( 'GENDERSLUG',  'cams');			  /* Gender pages slug (url) */
define ( 'MODELSLUG',   'cam');				  /* Model page (single page) slug (url) */
define ( 'SERPTITLEHOME',   'My Title');      /* Title for Google (SERP) (Recommended)*/
define ( 'GENDERTITLE',		'');			  /* Title for Google gender page (male, female, couples, transsexuals are automatically set)*/
define ( 'MODELTITLE', 		'');			  /* Title for Google model page (single page) (Title starts with example name JaneDoe's...) */
define ( 'SERPDESCHOME',    ''); 			  /* Home page description for Google (optional)*/
define ( 'KWSHOME',			'Don\'t break my page');       		  /* Homepage keywords (obsolete, optional) (Set at least one keyword, else the page will break. Keyword won't be used on pages, it's just for tech.*/
define ( 'WHITELABEL',		'https://chaturbate.com');/* Whitebale URL, no whitelabel? Leave default */
define ( 'VUUKLE',			''); 			  /* If you want VUUKLE as a comment system, leave empty if not. Else get VUUKLE API key and paste it here.*/
define ( 'GOOGLEVALIDATE',  ''); 			  /* Google webmaster verification code */
define ( 'MSVALIDATE',		'');			  /* Bing webmasters verification code */
define ( 'ANALYTICS',		'XX-XXXXXXXXX-X');/* Analytics ID */
define ( 'FOOTERTXT',		'All rights reserved'); /* Footer text */
?>
