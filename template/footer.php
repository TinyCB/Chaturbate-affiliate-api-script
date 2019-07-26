<?php
$core = new axl_Core();
$core->setHeaderFunc('tpl_header');
$core->setFooterFunc('tpl_footer');
$core->addCommand('home', 'tpl_home', '' . SERPTITLEHOME . ' - ' . SITENAME . '', '' . SERPDESCHOME . '', '' . KWSHOME . '');
$core->addCommand('' . GENDERSLUG . '', 'tpl_cams', ' ' . $_GET['arg1'] . ' '.GENDERTITLE.' - ' . SITENAME . '', '', '' . $_GET['arg1'] . ' cams');
$core->addCommand('' . MODELSLUG . '', 'tpl_view_cams', '' . $_GET['arg1'] . '\'s '.MODELTITLE.' - ' . SITENAME . '', '', '' . $_GET['arg1'] . '');
$core->addCommand('404', 'tpl_404', ' 404 error Page Not Found' . SITENAME, ' - The requested page was not found on ' . SITENAME, '');
$core->start();
echo '<div></div>';

function paginate($page, $total_pages, $limit, $targetpage)
{
    $adjacents = 5;
    if ($page == 0)
        $page = 1;
    $prev       = $page - 1;
    $next       = $page + 1;
    $lastpage   = ceil($total_pages / $limit);
    $lpm1       = $lastpage - 1;
    $targetpage = BASEHREF . $targetpage;
    $pagination = "";
    if ($lastpage > 1) {
        $pagination .= '<div class="cb_pager" style="z-index:9999; float:left;">';
        if ($page > 1)
            $pagination .= '<a rel="nofollow" href="' . $targetpage . $prev . '" class="prev">previous</a>';
        else
            $pagination .= '<span class="disabled">previous</span>';
        if ($lastpage < 7 + ($adjacents * 2)) {
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page)
                    $pagination .= '<span class="current">' . $counter . '</span>';
                else
                    $pagination .= '<a rel="nofollow" href="' . $targetpage . $counter . '">' . $counter . '</a>';
            }
        } elseif ($lastpage > 5 + ($adjacents * 2)) {
            if ($page < 1 + ($adjacents * 2)) {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                    
                    
                }
                
            }
            
            
        }
        if ($page <= $counter - 1)
            $pagination .= '<a rel="nofollow" href="' . $targetpage . $next . '" class="next">next</a>';
        else
            $pagination .= '<span class="disabled">next</span>';
        $pagination .= '</div>';
    }
    echo '<div class="pagination-wrapper">' . $pagination . '</div>';
}
?>
               </div>
                
            </div>
            
        </div>
        <div style="height:50px; width:100%; float:left;"></div>
    <footer>
        <div class="content-wrapper">
        <div class="footerLeft;">
        <ul>
        <li><span style="float:left;">&copy; 2019 <a href="/"><?php echo ''.SITENAME.''; ?></a>, <a href="/"><?php echo ''.FOOTERTXT.''; ?></span></li>
        </ul>
        </div>
        </div>
    </footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script>
function myFunction() {
  var x = document.getElementById("myLinks");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}</script>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo ''.ANALYTICS.''; ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', <?php echo json_encode(''.ANALYTICS.''); ?> );
</script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5550b8fc3ee9489a"></script>
<script>
  var VUUKLE_CONFIG = {
      apiKey: <?php echo json_encode(''.VUUKLE.''); ?>,
      articleId: <?php
echo json_encode($_GET['arg1']);
?>,
comments: {
        hideRecommendedArticles: false,
        hideCommentInputBox: false,
        enabled: true,
        commentingClosed: false,
        maxChars: '3000',
        countToLoad: '5',
        toxicityLimit: '150',
    },
  };
  // ⛔️ DON'T EDIT BELOW THIS LINE
  (function() {
      var d = document,
          s = d.createElement('script');
     s.src = 'https://cdn.vuukle.com/platform.js';
    (d.head || d.body).appendChild(s);
  })();
 </script>
</body>
</html>
<?php
function tpl_footer()
{
    echo '';
}
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
    function start()
    {
        $cmd  = isset($_GET['cmd']) ? $_GET['cmd'] : 'home';
        $args = array();
        if (isset($_GET['arg1']))
            $args[0] = $_GET['arg1'];
        if (isset($_GET['arg2']))
            $args[1] = $_GET['arg2'];
        if (!isset($this->commands[$cmd])) {
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
        if ($c[3]) {
            $this->outputHeader($cmd, $c[1], $c[2], $c[3]);
            call_user_func($c[0], $args);
            $this->outputFooter();
        } else
            call_user_func($c[0], $args);
        return true;
    }
    function outputHeader($cmd, $title, $des, $kw)
    {
        if (isset($this->headerFunc))
            return call_user_func($this->headerFunc, $cmd, $title, $des, $kw);
        return false;
    }
    function outputFooter()
    {
        if (isset($this->footerFunc))
            return call_user_func($this->footerFunc);
        return false;
    }
    function addCommand($cmd, $function, $title = NULL, $des = NULL, $kw = NULL)
    {
        $this->commands[$cmd] = array(
            $function,
            $title,
            $des,
            $kw
        );
        return true;
    }
}
function get_cams($affid, $track, $gender, $limit)
{
    if ($_GET['arg1']) {
        if (is_numeric($_GET['arg1'])) {
            $page       = $_GET['arg1'];
            $targetpage = '' . GENDERSLUG . '/';
        } else {
            $targetpage = '' . GENDERSLUG . '/' . $_GET['arg1'] . '/';
            if ($_GET['arg2']) {
                if (is_numeric($_GET['arg2'])) {
                    $page = $_GET['arg2'];
                }
            } else {
                $page = 1;
            }
        }
    } else {
        $targetpage = '' . GENDERSLUG . '/';
        $page       = 1;
    }
    $end                     = $page * $limit;
    $start                   = $end - $limit;
    $dom                     = new DomDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $xml                     = 'chaturbate.xml';
    $dom->Load($xml);
    $cams = new SimpleXMLElement($xml, 0, true);
    if ($gender != $gender) {
        $totalCams = 0;
        foreach ($dom->getElementsByTagName('gender') as $tag) {
            foreach ($tag->childNodes as $child) {
                $i = $dom->saveXML($child);
                if ($i == $gender)
                    $totalCams++;
            }
        }
    } else {
        $totalCams = count($cams);
    }
    echo '<ul>';
    $count = '';
    foreach ($cams as $cam) {
        if ($cam->gender == $gender) {
            if ($count >= $start && $count < $end) {
                print_cams($cam);
            }
            $count++;
        }
        if ($gender == '') {
            if ($count >= $start && $count < $end) {
                print_cams($cam);
            }
            $count++;
        }
    }
    echo '</ul>';
    echo '<div style="clear: both;">&nbsp;</div>';
    paginate($page, $totalCams, $limit, $targetpage);
}

function solo_cams($affid, $track, $user, $cam)
{
    $cams = new SimpleXMLElement('chaturbate.xml', null, true);
    foreach ($cams as $cam);
    echo '<div class="video-wrapper">';
    echo '<div class="resp-container">
            <iframe class="resp-iframe" src="'.WHITELABEL.'/in/?track='.TRACK.'&tour=Limj&campaign='.AFFID.'&signup_notice=1&b=' . $_GET['arg1'] . '" allow="autoplay"  allow="encrypted-media" allowfullscreen></iframe>
    </div>';
    echo '</div>';
    foreach ($cams as $cam) {
        if ($cam->username == $user) {
            if (MODE == 'revshare') {
                echo $cam->iframe_embed;
            } else {
                
                $subject     = '' . $cam->room_subject . '';
                $name        = '' . $cam->display_name . '';
                $image       = '' . $cam->image_url . '';
                $gender      = '' . $cam->gender . '';
                $age         = '' . $cam->age . '';
                $language    = '' . $cam->spoken_languages . '';
                $location    = '' . $cam->location . '';
                $Birth       = '' . $cam->birthday . '';
                $time        = '' . $cam->seconds_online . '';
                $time_online = ago('' . $cam->seconds_online . '');
                
                if ($gender == "f")
                    $gender = "Female";
                if ($gender == "m")
                    $gender = "Male";
                if ($gender == "c")
                    $gender = "Couple";
                if ($gender == "s")
                    $gender = "Transsexual";
                
                
            }
            
        }
        
    }
    echo '<div class="video-spacer"></div>';
    echo '<div class="cam-reviews">';
    echo '<div style="height:auto; width:95%; position:relative; margin:0 auto; margin-bottom:100px; font-family:arial;">';
    echo '<h4>Using a phone? Tilt your screen!</h4>';
	echo '<div style="float:left; margin-bottom:5px; width:100%;">'.$subject.'</div>';
	echo '<div style="height:1px; float:left; border-top:1px solid grey; width:100%; margin-bottom:5px;"></div>';
	echo '<div style="float:left; margin-bottom:5px; width:100%;"> <strong>Name: </strong> <a href="'.WHITELABEL.'/'.$user.'"> '.$name.' </a></div> ';
	echo '<div style="float:left; margin-bottom:5px; width:100%;"><strong>Gender: </strong>'.$gender.'</div>';
	echo '<div style="float:left; margin-bottom:5px; width:100%;"><strong>Age: </strong>'.$age.'</div>';
	echo '<div style="float:left; margin-bottom:5px; width:100%;"><strong>Language: </strong>'.$language.'</div>';
	echo '<div style="float:left; margin-bottom:5px; width:100%;"><strong>Location: </strong>'.$location.'</div>';
	echo '<div style="float:left; margin-bottom:5px; width:100%;"><strong>Birth: </strong>'.$Birth.'</div>';
	echo '<div style="float:left; margin-bottom:5px; width:100%;"><strong>Time Online: </strong>'.$time_online.'</div>';
	echo '<div style="height:1px; float:left; border-top:1px solid grey; width:100%; margin-bottom:5px;"></div>';
    echo "
<div style='width:100%; float:left; margin-top:15px;'>
<div id='vuukle-emote'></div>

<div class='vuukle-powerbar'></div>

<div id='vuukle-comments'></div>

<div id='vuukle-newsfeed'></div>

<div id='vuukle-subscribe'></div>

</div>
";
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>