<?php
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
define("THEME_BULLET", "<span class='bullet'>&middot;</span>");
require_once INCLUDES."theme_functions_include.php";
include "functions.php";
function render_page($license = FALSE) {
	global $userdata, $settings, $locale, $data, $db_prefix, $lookup, $msg_count, $aidlink;
	add_to_head('
	<!--[if lt IE 7]>
	<script type="text/javascript" src="'.THEME.'js/ie-png.js"></script>
	<script type="text/javascript" src="'.THEME.'js/supersleight.js"></script>
	<link rel="stylesheet" type="text/css" href="'.THEME.'css/lt7.css" />
	<![endif]-->
	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="'.THEME.'css/ie.css" />
	<![endif]-->
	<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="'.THEME.'css/ie7.css" />
	<![endif]-->
	<script type="text/javascript" src="'.THEME.'js/jquery.cycle.all.min.js"></script>
	<script type="text/javascript" src="'.THEME.'js/cufon-yui.js"></script>
	<script type="text/javascript" src="'.THEME.'js/Debonair-Calibri.js"></script>
	<script type="text/javascript" src="'.THEME.'js/Cufon-Settings.js"></script>
	<script type="text/javascript" src="'.THEME.'js/slider-settings.js"></script>
	<script type="text/javascript" src="'.THEME.'js/subnavie6.js"></script>
	');
	add_to_head("<link rel='stylesheet' href='".THEME."css/bootstrap_rewrite.css' type='text/css'/>");

	echo "<div id='wrapper'>\n";
	echo "<div class='container'>\n";
	echo "<div class='body-wrap'>\n";
	echo "<div class='body-inner-wrap'>\n";
	// start header ----
	echo "<header class='clearfix m-t-10'>
		<a class='logo' href='".BASEDIR."index.php'><img src='".THEME."images/logo.gif' alt='Web 2.0 Business Template'/></a>
		<div class='tagline'>Super Clean Web 2.0 Business Template</div>\n";
	echo "<div class='call-tag'>\n";
	if (iADMIN) {
		echo "<span class='display-inline-block m-r-10'><a href='".ADMIN.$aidlink."'>".$locale['global_123']."</a></span>\n";
	}
	echo $locale['global_ML102']."\n";
	foreach (fusion_get_enabled_languages() as $lang => $lang_name) {
		echo "<a href='".clean_request("lang=".$lang, array(), FALSE)."'>".$lang_name."</a>\n";
	}
	echo "<i id='theme_search' class='fa fa-search fa-fw'></i>";
	echo "</div>\n</header>\n";
	// end header ----
	// start nav ---
	/**
	 * If you want to make an inner search bar, you have to mod showsublinks(),
	 * Say, add a parameter (i.e. showsublinks(array("search_bar"=>true)) ), and submit your work to us at Github!
	 * Version 9, Dev Team is a PRO-MOD Team, we'll review and accept. Just talk to any of us.
	 * We're very fun people!
	 */
	echo showsublinks();
	// end nav --
	echo "<aside class='banner'>\n";
	echo "<div class='page-header'>\n";
	echo "<a href='".BASEDIR."login.php' class='pull-right btn btn-sm btn-success pull-right'><span>Register/Login</span></a>";
	echo "<div class='holder overflow-hide p-r-10'>\n";
	// start breadcrumbs
	echo "<div class='clearfix'>\n";
	echo "<div class='pull-left m-r-5'><span class='fa fa-map-marker fa-fw'></i>\n</span></div>";
	echo "<div class='overflow-hide'>\n";
	echo render_breadcrumbs();
	echo "</div>\n</div>\n";
	// end breadcrumbs
	// Do you know that using the breadcrumb instance, you can get the title of the page?
	// Fact: v9 has a very big and robust API.
	$title_instance = \PHPFusion\BreadCrumbs::getInstance();
	$reference = $title_instance->toArray(); // this will give you the whole breadcrumb array
	$debonAirTitle = (!empty($reference)) ? end($reference) : array('title' => $locale['home']);
	echo "<h1>".$debonAirTitle['title']."</h1>\n";
	echo "</div>\n</div>\n";
	echo "</aside>\n";
	function display_slider_box() {
		echo "<aside class='banner'>\n";
		//echo "<div class='page-header'>\n";
		echo "<div id='slider-container'>\n";
		echo "<ul id='slider-box'>\n";
		/*
		 *
		 */
		echo "<!-- Slide 1 -->
		 <li>
		 <div class='welcome-banner'><div class='slider-corner'></div>
		 <h1>Welcome to The Debonair Template</h1>
		 <h2>Super Clean <span>Web 2.0</span> Business Template</h2>
		 <div class='button-position'>
		 <div class='btn-group'><a class='btn btn-success btn-sm' href='#'>Learn more</a></div>
		 </div></div>
		 </li>
		 <!-- End slide 1 -->
		 ";
		echo "
		 <!-- Slide 2 -->
		 <li>
		 <div class='inner-banner'>
		 <div class='slider-corner'></div>
		 <div class='screen'>
		 <img src='".THEME."images/slider/screenshot1.jpg' alt='Screenshot'/> </div>
		 <h2>PSD Tuts Tutorial Contribution</h2>
		 <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s.</p>
		 <div class='button-position'>
		 <div class='btn-group'><a class='btn btn-success btn-sm' href='#'>Learn more</a></div>
		 </div></div>
		 </li>
		 <!-- End slide 2 -->
		";
		echo "</ul>\n";
		echo "
		<!-- Start Slider Nav-->
        <div class='slide-pager-container'>
		<div id='slide-pager'></div>
        </div>
        <!-- End Slider Nav-->
        </div>
		";
		echo "</aside>\n";
	}

	function display_lower_slider() {
		echo "<div class='lower-banner'>
	  <div class='holder'>
		 <div class='col'>
			<h2 class='icon1'>What is Lorem Ipsum?</h2>
			<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever. </p>
			<div class='link-holder'> <a href='#' class='more'>Learn More</a> </div>
								   </div>
		 <div class='col'>
			<h2 class='icon2'>Where does it come from?</h2>
			<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC.</p>
			<div class='link-holder'> <a href='#' class='more'>Learn More</a> </div>
		 </div>
		 <div class='col'>
			<h2 class='icon3'>Why do we use it?</h2>
			<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.</p>
			<div class='link-holder'> <a href='#' class='more'>Learn More</a> </div>
		 </div>
	  </div>
   </div>";
	}

	display_slider_box();
	display_lower_slider();
	// end of banner
	// Start of Inner page structure for Bootstrap
	$side_grid_settings = array('desktop_size' => 2,
		'laptop_size' => 3,
		'tablet_size' => 3,
		'phone_size' => 4,);
	echo "<section class='main-content'>\n<div class='main-content-inner'>\n";
	// now have to do bootstrap calculation
	// row 1 - go for max width
	if (defined('AU_CENTER') && AU_CENTER) echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".AU_CENTER."</div>\n</div>";
	// row 2 - fluid setitngs depending on panel appearances
	echo "<div class='row'>\n";
	if (defined('LEFT') && LEFT) echo "<div class='".html_prefix($side_grid_settings)."'>\n".LEFT."</div>\n"; // column left
	echo "<div class='".html_prefix(center_grid_settings($side_grid_settings))."'>\n".U_CENTER.CONTENT.L_CENTER."</div>\n"; // column center
	if (defined('RIGHT') && RIGHT) echo "<div class='".html_prefix($side_grid_settings)."'>\n".RIGHT."</div>\n"; // column right
	echo "</div>\n";
	// row 3
	if (defined('BL_CENTER') && BL_CENTER) echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".BL_CENTER."</div>\n</div>";
	echo "</div>\n</section>\n";
	echo "</div>\n"; // end body-inner-wrap
	// Begin Footer
	echo "<section class='lower-section'>\n";
	echo "<div class='row col-holder'>\n";
	// give it a 4 column design
	// column 1
	echo "<div class='col-xs-12 col-sm-3'>\n
	<h3 class='icon1 margin'>Lorem Ipsum is not simply random text</h3>
    <p>If you are going to use a passage of Lorem Ipsum, you need to be sure there isnt anything embarrassing hidden in the middle of text.</p>
	<div class='link-holder'> <a href='#' class='more-dark'>Learn More</a> </div>
	</div>\n";
	// column 2
	echo "<div class='col-xs-12 col-sm-3'>\n
	<h3 class='icon2'>Lorem Ipsum Generators </h3>
                        <p>All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet.</p>
                        <div class='link-holder'> <a href='#' class='more-dark'>Learn More</a> </div>
</div>\n";
	// column 3
	echo "<div class='col-xs-12 col-sm-3'>\n
	<h3 class='icon2'>Lorem Ipsum Generators </h3>
                        <p>All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet.</p>
                        <div class='link-holder'> <a href='#' class='more-dark'>Learn More</a> </div>
</div>\n";
	// column 4
	echo "<div class='col-xs-12 col-sm-3'>\n
	<h3 class='icon3'>Debonair Newsletter</h3>
                        <!-- Start Newsletter Form -->
                        <form id='newsletter' method='get' action='' class='form-add'>
                           <fieldset>
                           <div class='row'>
                              <label for='name'>Your Name:</label>
                              <div class='txt'>
                                 <input id='name' name='name' type='text' />
                              </div>
                           </div>
                           <div class='row'>
                              <label for='email'>Email Address:</label>
                              <div class='txt'>
                                 <input id='email' name='email' type='text' />
                              </div>
                           </div>
                           <div class='btn-holder'>
                              <button type='submit' class='formbutton'><span>Sign Up</span></button>
                              <a href='#'>Unsubscribe</a> </div>
                           </fieldset>
                        </form>
</div>\n";
	echo "</div>\n";
	// start bottom
	echo "
	<div class='bottom'>
                  <!-- Start Follow Us Links -->
                  <div class='follow-box'> <strong>Follow us on:</strong>
                     <ul>
                        <li><a href='#' class='facebook'>facebook</a></li>
                        <li><a href='#' class='twitter'>twitter</a></li>
                        <li><a href='#' class='rss'>rss</a></li>
                     </ul>
                  </div>
                  <!-- End Follow Us Links -->
                  <div class='txt-holder'>
                     <p><small>Powered by PHP-Fusion copyright &copy; 2002 - 2012 by Nick Jones. Released as free software without warranties under GNU Affero GPL v3.</small></p>
                  </div>
               </div>
            </div>";
	echo "</section>\n";
	echo "</div>\n <!--End Wrapper Sub Elements -->";
	echo "
	<div id='footer'>
      <!--Start Footer Nav -->
      <div class='footer-nav'>
         <div class='w1'>
            <div class='w2'>
               <ul>
                  <li><a href='#'>Home</a></li>
				  <li><a href='#'>About Us</a></li>
                  <li><a href='#'>Our Services</a></li>
                  <li><a href='#'>Portfolio</a></li>
                  <li><a href='#'>Blog</a></li>
                  <li><a href='#'>Contact Us</a></li>
               </ul>
            </div>
         </div>
      </div>
      <!--End Footer Nav -->
   </div>
	";
	echo '<script type="text/javascript"> Cufon.now(); </script>';
}

function render_news($subject, $news, $info) {
	echo '<div class="post-holder">
    
    <div class="post">
                           <!-- Start date-box -->
                           <div class="date-box">
                              <div> '.showdate('<strong>%d</strong> <span<>%m</span> ', $info["news_date"]).'</div>
                           </div>
                           <!-- End date-box -->
                           <div class="heading">
                              <h2><a href="'.BASEDIR.'news.php?readmore='.$info["news_id"].'">'.$subject.'</a></h2>
                              <div class="info">
                                 <p>'.showdate('%d.%m.%y um %H.%M', $info["news_date"]).' Uhr von '.profile_link($info["user_id"], $info["user_name"], $info["user_status"]).'</p>
                                 <em><a href="'.BASEDIR.'news.php?readmore='.$info["news_id"].'">'.$info["news_comments"].' comments</a></em> </div>
                           </div>
                           <div class="txt-content">
                              <p>'.$news.'</p>
                           </div>
                           <div class="more-holder"> <a href="'.BASEDIR.'news.php?readmore='.$info["news_id"].'" class="more-dark">Read More</a> </div>
                        </div>
    
    </div>';
}

/** Artikel erstellen **/
function render_article($subject, $article, $info) {
	echo '<div class="post-holder">
    
    <div class="post">
                           <!-- Start date-box -->
                           <div class="date-box">
                              <div> '.showdate('<strong>%d</strong> <span<>%m</span> ', $info["article_date"]).'</div>
                           </div>
                           <!-- End date-box -->
                           <div class="heading">
                              <h2><a href="'.BASEDIR.'articles.php?article_id='.$info["article_id"].'">'.$subject.'</a></h2>
                              <div class="info">
                                 <p>'.showdate('%d.%m.%y um %H.%M', $info["article_date"]).' Uhr von '.profile_link($info["user_id"], $info["user_name"], $info["user_status"]).'</p>
                                 <em><a href="'.BASEDIR.'articles.php?article_id='.$info["article_id"].'">'.$info["article_comments"].' comments</a></em> </div>
                           </div>
                           <div class="txt-content">
                              <p>'.$article.'</p>
                           </div>
                           <div class="more-holder"> <a href="'.BASEDIR.'articles.php?article_id='.$info["article_id"].'" class="more-dark">Read More</a> </div>
                        </div>
    
    </div>';
}

/** Opentable **/
function opentable($title) {
	echo '<div class="txt-content">
                           <h3>'.$title.'</h3><p>';
}

/** Closetable **/
function closetable() {
	echo "</p>
          </div>";
}

/** Openside **/
function openside($title) {
	echo '<h3>'.$title.'</h3><p>';
}

/** Closeside **/
function closeside() {
	echo '</p>';
}

?>