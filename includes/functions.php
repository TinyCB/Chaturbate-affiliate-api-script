<?php
function print_cams($cam) {
	if ($cam->room_subject=="") $cam->room_subject="No room subject / goal set. ".$cam->username."'s sex cam";
	
		$subject_title = ''.$cam->room_subject.'';
		$username_title = ''.$cam->username.'';
		$subject = ''.$cam->room_subject.'';
		$username= ''.$cam->username.'';
		$image   = ''.$cam->image_url.'';
		$gender  = ''.$cam->gender.'';
		$age	 = ''.$cam->age.'';
		$awayimage = "/img/away.png";
		$privateimage = "/img/private.png";
		$hiddenimage = "/img/hidden.png";
		$groupimage = "/img/private.png";
		
		if ($cam->current_show=="public")  $currentshow=$image;
		if ($cam->current_show=="away")	   $currentshow=$awayimage;
		if ($cam->current_show=="private") $currentshow=$privateimage;
		if ($cam->current_show=="hidden") $currentshow=$hiddenimage;
		if ($cam->current_show=="group") $currentshow=$groupimage;

		$subject=substr($subject, 0, 52) . '';
		$username=substr($username, 0, 20) . '';

		if ($gender=="f") $gender="Female";
		if ($gender=="m") $gender="Male";
		if ($gender=="c") $gender="Couple";
		if ($gender=="s") $gender="Trans...";
	
		$time_online = ago( ''.$cam->seconds_online.'' );

echo '

			<li class="camwrap">
			
			<span class="thumbails">
			

			<a class="model" href="' . BASEHREF . 'cam/' . $cam->username . '" rel="nofollow" title="' . $cam->username . '" style="margin-left:0px;">
			
				<img class="cam-thumbnails" src="'.$currentshow.'" alt="' .$username. '" title="' .$username. '"/>
			
			</a>
			
			
			</span>
			<span class="" style="float:left;width:100%; height:100%;">
			
			<span class="" style="border-bottom:1px solid black; float:left; width:96%; height:17px; position:relative; margin-left:3px;">
			<a href="' . BASEHREF . 'cam/' . $username . '" rel="nofollow" title="' . $username . '">
			<p style="color:orange; font-weight:bold; float:left; padding:0px; margin:0px; margin-left:3px; margin-top:3px; margin-bottom:3px;" title="'.$username_title.'">'.$username.'</p>
			</a>
			<p style="float:right; margin-right:3px; padding:0px; margin:0px; margin-right:3px; margin-top:3px; margin-bottom:3px;" title="'.$gender.', '.$age.'">'.$gender.', '.$age.'</p>
			
			</span>
			
			<span class="" style="float:left; width:96%; height:32px; margin-left:3px; margin-right:3px;">
			<p style="float:left; padding:0px; margin:0px; margin-left:3px; margin-top:3px; margin-bottom:3px;" alt="'.$subject_title.'" title="'.$subject_title.'">'.$subject.'</p>
			</span>
			
			<span class="" style="float:left; width:96%; height:20px; margin-left:3px;">
			<p style="float:left; margin:0px; padding:0px; margin-left:3px;" title="">' .$time_online. ', '.$cam->num_users.' viewers</p>
			</span>
			
			</span>
			</li>
';
}

function tpl_home() {
	get_cams ( AFFID, TRACK, $gender='', 36 );
}

function tpl_cams() {
	$gender = $_GET['arg1'];
		switch ( $gender ) {
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
get_cams ( AFFID, TRACK, $gender, 36 );
}

function random_text( $model, $age, $location, $num_users, $time_online  ) {

			$num 	= Rand (1,6);

			switch ($num) {

				// Description 1

					case 1:
					echo '
						
							Welcome to <strong>' . $model . '\'s live stream</strong> and chat room! Watching ' . $model . ' getting naked, fucking, sucking, etc... is <storng>completely FREE</strong>! However, to chat with ' . $model . ', view ' . $model . '\'s private profile photos and video clips, and many more member-only features... you\'ll need a <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">FREE account</a>. Right now, ' . $model . ' is responding live to viewers... <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">Create your free account</a> now to join in on the fun!
						
					';
					break;

				// Description 2	

					case 2:
					echo '
						
							Thanks for checking out <strong>' . $model . '\'s live stream</strong> and chat room! You can enjoy watching ' . $model . ' absolutely FREE! If you would like to chat with this <strong>Smoking HOT ' . $age . ' year old</strong>, or view ' . $model . '\'s private pics and video clips you\'ll have  to <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">register for a FREE account</a>.

						
					';
					break;

				// Description 3

					case 3:
					echo '
						
							Welcome to <strong>' . $model . '\'s live stream</strong> and chat room! Watching ' . $model . ' getting naked, fucking, sucking, etc... is <storng>completely FREE</strong>! However, to chat with ' . $model . ', view ' . $model . '\'s private profile photos and video clips, and many more member-only features... you\'ll need a <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">FREE account</a>. Right now, ' . $model . ' is responding live to viewers... <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">Create your free account</a> now to join in on the fun!
						
					';
					break;

				// Description 4

					case 4:
					echo '
						
							Thanks for checking out <strong>' . $model . '\'s live stream</strong> and chat room! You can enjoy watching ' . $model . ' absolutely FREE! If you would like to chat with this <strong>Smoking HOT ' . $age . ' year old</strong>, or view ' . $model . '\'s private pics and video clips you\'ll have  to <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">register for a FREE account</a>.

					';
					break;

				// Description 5

					case 5:
					echo '
						
							Welcome to <strong>' . $model . '\'s live stream</strong> and chat room! Watching ' . $model . ' getting naked, fucking, sucking, etc... is <storng>completely FREE</strong>! However, to chat with ' . $model . ', view ' . $model . '\'s private profile photos and video clips, and many more member-only features... you\'ll need a <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">FREE account</a>. Right now, ' . $model . ' is responding live to viewers... <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">Create your free account</a> now to join in on the fun!
						
					';
					break;

				// Description 6

					case 6:
					echo '
						
							Thanks for checking out <strong>' . $model . '\'s live stream</strong> and chat room! You can enjoy watching ' . $model . ' absolutely FREE! If you would like to chat with this <strong>Smoking HOT ' . $age . ' year old</strong>, or view ' . $model . '\'s private pics and video clips you\'ll have  to <a rel="nofollow" href="'.whitelabel.'/accounts/register/" class="external">register for a FREE account</a>.

					';
					break;																									

			}
			
		}
function ago( $secs ) {
			
			$second 	= 1;
			$minute 	= 60;
			$hour 		= 60*60;
			$day 		= 60*60*24;
			$week 		= 60*60*24*7;
			$month 		= 60*60*24*7*30;
			$year 		= 60*60*24*7*30*365;
				 
			if ( $secs <= 0 ) {
				$output = "now";
			} elseif ( $secs > $second && $secs < $minute ) {
				$output = round( $secs/$second )." second";
			} elseif ( $secs >= $minute && $secs < $hour ) {
				$output = round( $secs/$minute )." minute";
			} elseif ( $secs >= $hour && $secs < $day ) {
				$output = round( $secs/$hour )." hour";
			} elseif ( $secs >= $day && $secs < $week ) {
				$output = round( $secs/$day )." day";
			} elseif ( $secs >= $week && $secs < $month ) {
				$output = round( $secs/$week )." week";
			} elseif ( $secs >= $month && $secs < $year ) {
				$output = round( $secs/$month )." month";
			} elseif ( $secs >= $year && $secs < $year*10 ) {
				$output = round( $secs/$year )." year";
			} else { 
				$output = "more than a decade ago"; 
			}
				 
			if ( $output <> "now" ) {
				$output = ( substr( $output,0,2 )<>"1 " ) ? $output."s" : $output;
			}
			return $output;
				
		}
function tpl_view_cams() {
	solo_cams( AFFID, TRACK, $_GET['arg1'], $cam );
}

include 'includes/edit.php';

echo '<div></div>';

$core = new axl_Core();
$core->setHeaderFunc('tpl_header');
$core->setFooterFunc('tpl_footer');
$core->addCommand('home', 'tpl_home',''.mainpagetitletag.' - '. SITENAME . '', '', 'free cams, live sex');
$core->addCommand('cams', 'tpl_cams',' '.subpagetitletag.' - '.SITENAME.'', '','' . $_GET['arg1'] . ' cams');
$core->addCommand('cam', 'tpl_view_cams', ''.modelpagetitletag.' - '.SITENAME.'', '',''. $_GET['arg1'] .'');
$core->addCommand('404', 'tpl_404', ' 404 error Page Not Found' . SITENAME, ' - The requested page was not found on ' . SITENAME, '');	
$core->start();

function paginate($page, $total_pages, $limit, $targetpage) { 

		$adjacents = 5;
		if ($page == 0) $page = 1;
		$prev = $page - 1;
		$next = $page + 1;
		$lastpage = ceil($total_pages/$limit);
		$lpm1 = $lastpage - 1;
		$targetpage = BASEHREF . $targetpage;
		$pagination = "";
		
		if ($lastpage > 1)
	
	{
	
		$pagination .= '<div class="cb_pager">';
		if ($page > 1)
		$pagination.= '<a rel="nofollow" href="' . $targetpage . $prev . '" class="prev">previous</a>';
		else
		$pagination.= '<span class="disabled">previous</span>';	
		if ($lastpage < 7 + ($adjacents * 2))
		
	{

		for ($counter = 1; $counter <= $lastpage; $counter++)

	{
		
		if ($counter == $page)
		$pagination.= '<span class="current">' . $counter . '</span>';
		else
		$pagination.= '<a rel="nofollow" href="' . $targetpage . $counter . '">' . $counter . '</a>';	
				
	}

	}

		elseif($lastpage > 5 + ($adjacents * 2))

	{

		if($page < 1 + ($adjacents * 2))
	
	{
	
		for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
	
	{
	
	}

	}

	}

		if ($page <= $counter -1 ) 
		$pagination.= '<a rel="nofollow" href="' . $targetpage . $next . '" class="next">next</a>';
		else
		$pagination.= '<span class="disabled">next</span>';
		$pagination.= '</div>';
		
	}
	
		echo '<div class="pagination-wrapper">'.$pagination.'</div>';
		
	}
?>