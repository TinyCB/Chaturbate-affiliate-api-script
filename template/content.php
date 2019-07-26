<?php
function print_cams($cam)
{
    if ($cam->room_subject == "")
        $cam->room_subject = "No room subject / goal set. " . $cam->username . "'s sex cam";
    $subject_title  = '' . $cam->room_subject . '';
    $username_title = '' . $cam->username . '';
    $subject        = '' . $cam->room_subject . '';
    $username       = '' . $cam->username . '';
    $image          = '' . $cam->image_url . '';
    $gender         = '' . $cam->gender . '';
    $age            = '' . $cam->age . '';
    $awayimage      = "".BASEHREF."/img/away.png";
    $privateimage   = "".BASEHREF."/img/private.png";
    $hiddenimage    = "".BASEHREF."/img/hidden.png";
    $groupimage     = "".BASEHREF."/img/private.png";
    if ($cam->current_show == "public")
        $currentshow = $image;
    if ($cam->current_show == "away")
        $currentshow = $awayimage;
    if ($cam->current_show == "private")
        $currentshow = $privateimage;
    if ($cam->current_show == "hidden")
        $currentshow = $hiddenimage;
    if ($cam->current_show == "group")
        $currentshow = $groupimage;
    $subject  = substr($subject, 0, 52) . '';
    $username = substr($username, 0, 20) . '';
    
    if ($gender == "f")
        $gender = "Female";
    if ($gender == "m")
        $gender = "Male";
    if ($gender == "c")
        $gender = "Couple";
    if ($gender == "s")
        $gender = "Trans...";
    $time_online = ago('' . $cam->seconds_online . '');
    
    echo '

            <li class="camwrap">
            
            <span class="thumbails">
            

            <a class="model" href="' . BASEHREF . '' . MODELSLUG . '/' . $cam->username . '" title="' . $cam->username . '" style="margin-left:0px;">
            
                <img class="cam-thumbnails" src="' . $currentshow . '" alt="' . $username . '" title="' . $username . '"/>
            
            </a>
            
            
            </span>
            <span class="" style="float:left;width:100%;">
            
            <span class="" style="border-bottom:1px solid black; float:left; width:96%; height:17px; position:relative; margin-left:3px;">
            <a href="' . BASEHREF . '' . MODELSLUG . '/' . $username . '" title="' . $username . '">
            <p style="color:orange; font-weight:bold; float:left; padding:0px; margin:0px; margin-left:3px; margin-top:3px; margin-bottom:3px;" title="' . $username_title . '">' . $username . '</p>
            </a>
            <p style="float:right; margin-right:3px; padding:0px; margin:0px; margin-right:3px; margin-top:3px; margin-bottom:3px;" title="' . $gender . ', ' . $age . '">' . $gender . ', ' . $age . '</p>
            
            </span>
            
            <span class="" style="float:left; width:96%; height:32px; margin-left:3px; margin-right:3px;">
            <p style="float:left; padding:0px; margin:0px; margin-left:3px; margin-top:3px; margin-bottom:3px;" alt="' . $subject_title . '" title="' . $subject_title . '">' . $subject . '</p>
            </span>
            
            <span class="" style="float:left; width:96%; height:20px; margin-left:3px;">
            <p style="float:left; margin:0px; padding:0px; margin-left:3px;" title="">' . $time_online . ', ' . $cam->num_users . ' viewers</p>
            </span>
            
            </span>
            </li>
';
}

function tpl_home()
{
    get_cams(AFFID, TRACK, $gender = '', 36);
}
function tpl_cams()
{
    $gender = $_GET['arg1'];
    switch ($gender) {
        default:
            $gender = '';
            break;
        case 'female':
            $gender = 'f';
            break;
        case 'male':
            $gender = 'm';
            break;
        case 'couple':
            $gender = 'c';
            break;
        case 'shemale':
            $gender = 's';
    }
    get_cams(AFFID, TRACK, $gender, 36);
}

function ago($secs)
{
    
    $second = 1;
    $minute = 60;
    $hour   = 60 * 60;
    $day    = 60 * 60 * 24;
    $week   = 60 * 60 * 24 * 7;
    $month  = 60 * 60 * 24 * 7 * 30;
    $year   = 60 * 60 * 24 * 7 * 30 * 365;
    
    if ($secs <= 0) {
        $output = "now";
    } elseif ($secs > $second && $secs < $minute) {
        $output = round($secs / $second) . " second";
    } elseif ($secs >= $minute && $secs < $hour) {
        $output = round($secs / $minute) . " minute";
    } elseif ($secs >= $hour && $secs < $day) {
        $output = round($secs / $hour) . " hour";
    } elseif ($secs >= $day && $secs < $week) {
        $output = round($secs / $day) . " day";
    } elseif ($secs >= $week && $secs < $month) {
        $output = round($secs / $week) . " week";
    } elseif ($secs >= $month && $secs < $year) {
        $output = round($secs / $month) . " month";
    } elseif ($secs >= $year && $secs < $year * 10) {
        $output = round($secs / $year) . " year";
    } else {
        $output = "more than a decade ago";
    }
    
    if ($output <> "now") {
        $output = (substr($output, 0, 2) <> "1 ") ? $output . "s" : $output;
    }
    return $output;
    
}
function tpl_view_cams()
{
    solo_cams(AFFID, TRACK, $_GET['arg1'], $cam);
}

include('edit.php');

$feed = "".WHITELABEL."/affiliates/api/onlinerooms/?wm=".AFFID."&format=xml";

$copy = 1;

if (time() - @filemtime("chaturbate.xml") < 120) {
    
    $feed = "chaturbate.xml";
    
    $copy = 0;
    
}



$fh = fopen($feed, "r");

if ($copy)
    $saver = fopen("chaturbate.xml", "w");

while ($xmlcontent = fread($fh, 1024)) {
    
    if ($copy)
        fputs($saver, $xmlcontent);
    
    xml_parse($xmlcontent, feof($fh));
    
}