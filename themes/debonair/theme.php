<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: debonair/theme.php
| Author: HappyTunes (Russia)
| Co-Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

if (!defined("IN_FUSION")) {
	die("Access Denied");
}
define("THEME_BULLET", "<span class='bullet'>&middot;</span>");
require_once INCLUDES."theme_functions_include.php";
include "functions.php";
function render_page($license = FALSE) {
	global $userdata, $settings, $locale, $data, $db_prefix, $lookup, $msg_count, $aidlink;
	include THEME."/locale/".LOCALESET."locale.php";

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
	include THEME."theme_db.php";
	$theme_settings = get_theme_settings("debonair");

	echo "<div id='wrapper'>\n";
	echo "<div class='container'>\n";
	echo "<div class='body-wrap'>\n";
	echo "<div class='body-inner-wrap'>\n";
	// start header ----
	$banner_path = fusion_get_settings("sitebanner");
	echo "<header class='clearfix m-t-10'>
		<a class='logo' href='".BASEDIR."index.php'><img src='".($banner_path !== "" ? BASEDIR.$banner_path : IMAGES."php-fusion-logo.png")."' alt='".fusion_get_settings("sitename")."'/></a>
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
	echo showsublinks();
	// end nav --

	// Header Banner
	$banner_inclusion_url = explode(",", $theme_settings['main_banner_url']);
	if (in_array(START_PAGE, $banner_inclusion_url)) {
		// get the results of the banner
		$result = dbquery("SELECT * FROM ".DB_DEBONAIR." where banner_language='".LANGUAGE."' order by banner_order ASC");
		// show banner
		echo "<aside class='banner'>\n";
		echo "<div id='slider-container'>\n";
		echo "<ul id='slider-box'>\n";
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				echo "<!--Slide ".$data['banner_id']."-->\n";
				echo "<li>\n";
				echo "<div class='inner-banner'>\n";
				echo "<div class='slider-corner'></div>\n";
				if ($data['banner_image']) {
					echo "<div class='screen'><img src='".THEME."upload/".$data['banner_image']."' alt='".$data['banner_subject']."'/></div>\n";
				}
				if ($data['banner_description'] !== "") {
					echo "<h2>".$data['banner_subject']."</h2>\n";
					echo "<p>".parseubb(parsesmileys($data['banner_description']))."</p>\n";
				} else {
					echo "<h1>".$data['banner_subject']."</h1>\n";
				}
				if ($data['banner_link'] !=="") {
					echo "<div class='button-position'>\n";
					echo "<div class='btn-group'><a class='btn btn-success btn-sm' href='".BASEDIR.$data['banner_link']."'>Learn more</a></div>\n";
					echo "</div>\n";
				}
				echo "</div>\n</li>\n";
				echo "<!--End slide ".$data['banner_id']."-->\n";
			}
		} else {
			 echo "<!--Slide Welcome-->
			 <li>
			 <div class='welcome-banner'><div class='slider-corner'></div>
			 <h1>".$locale['debonair_0500']."</h1>
			 <h2>".$locale['debonair_0501']."</h2>
			 </div>
			 </li>
			 <!-- End Slide Welcome-->
		 	";
			echo "<!--Slide Customize-->
			 <li>
			 <div class='welcome-banner-2'><div class='slider-corner'></div>
			 <h1>".$locale['debonair_0502']."</h1>
			 <h2>".$locale['debonair_0502a']."</h2>
			 <div class='button-position'>
			 <p>".$locale['debonair_0502c']."</p>
			 </div></div>
			 </li>
			 <!-- End Slide Customize-->
		 	";
		}
		echo "</ul>\n";
		echo "<!-- Start Slider Nav-->\n<div class='slide-pager-container'>\n<div id='slide-pager'></div>\n</div>\n<!-- End Slider Nav-->\n</div>\n";
		echo "</aside>\n";
		// upperbanner
		echo "<div class='lower-banner'>\n<div class='row holder'>\n";
		// 3 columns
		for($i=1; $i<=3; $i++) {
			echo "<div class='col-xs-12 col-sm-4 col'>\n";
			if ($theme_settings['ubanner_col_'.$i] !=="") {
				$data = uncomposeSelection($theme_settings['ubanner_col_'.$i]);
				if (!empty($data['selected']) && multilang_table("NS") ? !empty($data['options'][LANGUAGE]) : "") {
					switch($data['selected']) {
						case "news":
							if (db_exists(DB_NEWS) && isset($data['options'][LANGUAGE])) {
								$result = dbquery("select * from ".DB_NEWS."
											".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('news_visibility')."
											AND (news_start='0'||news_start<=".time().")
											AND (news_end='0'||news_end>=".time().") AND news_draft='0'
											AND news_id='".$data['options'][LANGUAGE]."'
											");
								if (dbrows($result)>0) {
									$data = dbarray($result);
									echo "<h2 class='icon1'>".$data['news_subject']."</h2>\n";
									echo "<p>".fusion_first_words(html_entity_decode(stripslashes($data['news_news'])), 50)."</p>\n";
									echo "<div class='link-holder'><a href='".INFUSIONS."news/news.php?readmore=".$data['news_id']."' class='more'>".$locale['debonair_0504']."</a></div>\n";
								} else {
									echo "<p>".$locale['debonair_0600']."</p>\n";
								}
							} else {
								echo "<p>".$locale['debonair_0408']."</p>\n";
							}
							break;
						case "blog":
							if (db_exists(DB_BLOG) && isset($data['options'][LANGUAGE])) {
								$result = dbquery("select * from ".DB_BLOG."
											".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')."
											AND (blog_start='0'||blog_start<=".time().")
											AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
											AND blog_id='".$data['options'][LANGUAGE]."'
											");
								if (dbrows($result)>0) {
									$data = dbarray($result);
									echo "<h2 class='icon2'>".$data['blog_subject']."</h2>\n";
									echo "<p>".fusion_first_words(html_entity_decode(stripslashes($data['blog_blog'])), 50)."</p>\n";
									echo "<div class='link-holder'><a href='".INFUSIONS."blog/blog.php?readmore=".$data['blog_id']."' class='more'>".$locale['debonair_0504']."</a></div>\n";
								} else {
									echo "<p>".$locale['debonair_0600']."</p>\n";
								}
							} else {
								echo "<p>".$locale['debonair_0405']."</p>\n";
							}
							break;
						case "articles":
							if (db_exists(DB_ARTICLES) && isset($data['options'][LANGUAGE])) {
								$result = dbquery("SELECT ta.article_id, ta.article_subject, ta.article_snippet, ta.article_article, ta.article_keywords, ta.article_breaks,
								ta.article_datestamp, ta.article_reads, ta.article_allow_comments, ta.article_allow_ratings,
								tac.article_cat_id, tac.article_cat_name
								FROM ".DB_ARTICLES." ta
								INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
								".(multilang_table("AR") ? "WHERE tac.article_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('article_visibility')." AND article_id='".$data['options'][LANGUAGE]."' AND article_draft='0'");
						if (dbrows($result)>0) {
									$data = dbarray($result);
									echo "<h2 class='icon2'>".$data['article_subject']."</h2>\n";
									echo "<p>".fusion_first_words(html_entity_decode(stripslashes($data['article_subject'])), 50)."</p>\n";
									echo "<div class='link-holder'><a href='".INFUSIONS."articles/articles.php?article_id=".$data['article_id']."' class='more'>".$locale['debonair_0504']."</a></div>\n";
								} else {
									echo "<p>".$locale['debonair_0600']."</p>\n";
								}
							} else {
								echo "<p>".$locale['debonair_0415']."</p>\n";
							}
							break;
						case "cp":
							$result = dbquery("SELECT page_id, page_title, page_content
										from ".DB_CUSTOM_PAGES."
										WHERE ".groupaccess('page_access')."
										AND page_id='".$data['options'][LANGUAGE]."'");
							if (dbrows($result)>0) {
								$data = dbarray($result);
								echo "<h2 class='icon3'>".$data['page_title']."</h2>\n";
								echo "<p>".fusion_first_words(html_entity_decode(stripslashes($data['page_content'])), 50)."</p>\n";
								echo "<div class='link-holder'><a href='".BASEDIR."viewpage.php?page_id=".$data['page_id']."' class='more'>".$locale['debonair_0504']."</a></div>\n";
							} else {
								echo "<p>".$locale['debonair_0600']."</p>\n";
							}
							break;
					}
				}
			} else {
				echo "<h2 class='icon3'>".$locale['debonair_0601']."</h2>\n";
				echo "<p>".$locale['debonair_0602']."</p>\n";
			}
			echo "</div>\n";
		}
		echo "</div>\n</div>\n";
	} else {
		// show simple header
		echo "<aside class='banner m-b-15'>\n";
		echo "<div class='page-header'>\n";
		echo "<a href='".BASEDIR."login.php' class='pull-right btn btn-sm btn-success pull-right'><span>Register/Login</span></a>";
		echo "<div class='holder overflow-hide p-r-10'>\n";
		echo "<div class='clearfix'>\n";
		echo "<div class='pull-left m-r-5'><span class='fa fa-map-marker fa-fw'></i>\n</span></div>";
		echo "<div class='overflow-hide'>\n";
		echo render_breadcrumbs();
		echo "</div>\n</div>\n";
		$title_instance = \PHPFusion\BreadCrumbs::getInstance();
		$reference = $title_instance->toArray(); // this will give you the whole breadcrumb array
		$debonAirTitle = (!empty($reference)) ? end($reference) : array('title' => $locale['home']);
		echo "<h1>".$debonAirTitle['title']."</h1>\n";
		echo "</div>\n</div>\n";
		echo "</aside>\n";
	}
	// end of banner
	// Start of Inner page structure for Bootstrap
	$side_grid_settings = array(
		'desktop_size' => 2,
		'laptop_size' => 3,
		'tablet_size' => 3,
		'phone_size' => 4
	);

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
	// column 1
	echo "<div class='col-xs-12 col-sm-3'>\n";
	if ($theme_settings['lbanner_col_1'] !=="") {
		include "include/".$theme_settings['lbanner_col_1'];
	}
	// column 2
	echo "</div>\n<div class='col-xs-12 col-sm-3'>\n";
	if ($theme_settings['lbanner_col_2'] !=="") {
		include "include/".$theme_settings['lbanner_col_2'];
	}
	// column 3
	echo "</div>\n<div class='col-xs-12 col-sm-3'>\n";
	if ($theme_settings['lbanner_col_3'] !=="") {
		include "include/".$theme_settings['lbanner_col_3'];
	}
	// column 4
	echo "</div>\n<div class='col-xs-12 col-sm-3'>\n";
	if ($theme_settings['lbanner_col_4'] !=="") {
		include "include/".$theme_settings['lbanner_col_4'];
	}
	echo "</div>\n";
	echo "</div>\n";
	// start bottom
	echo "<div class='bottom'>\n";
	if ($theme_settings['facebook_url'] || $theme_settings['twitter_url']) {
		echo "<!-- Start Follow Us Links -->\n<div class='follow-box'> <strong>".$locale['debonair_0510']."</strong><ul>\n";
		if ($theme_settings['facebook_url']) echo "<li><a href='".$theme_settings['facebook_url']."' class='facebook'>".$locale['debonair_0511']."</a></li>\n";
		if ($theme_settings['twitter_url']) echo "<li><a href='".$theme_settings['twitter_url']."' class='twitter'>".$locale['debonair_0512']."</a></li>\n";
		echo "</ul></div><!-- End Follow Us Links -->\n";
	}
	echo "<div class='txt-holder'><p><small>".str_replace("<br />", "", showcopyright())."</small></p></div>
	</div>\n</div>";
	echo "</section>\n";
	echo "</div>\n <!--End Wrapper Sub Elements -->";
	echo "
	<div id='footer'>
      <!--Start Footer Nav -->
      <div class='footer-nav'>
         <div class='w1'>
            <div class='w2'>
               <ul>
                  <li><a href='".BASEDIR.fusion_get_settings("opening_page")."'>".$locale['debonair_0505']."</a></li>
				  <li><a href='".BASEDIR."contact.php'>".$locale['debonair_0506']."</a></li>\n";
					if (db_exists(DB_ARTICLES)) echo "<li><a href='".INFUSIONS."articles/articles.php'>".$locale['debonair_0507']."</a></li>\n";
					if (db_exists(DB_NEWS)) echo "<li><a href='".INFUSIONS."news/news.php'>".$locale['debonair_0508']."</a></li>\n";
					if (db_exists(DB_BLOG)) echo "<li><a href='".INFUSIONS."blog/blog.php'>".$locale['debonair_0509']."</a></li>\n";
               echo "</ul>
            </div>
         </div>
      </div>
      <!--End Footer Nav -->
   </div>
	";
	echo '<script type="text/javascript">Cufon.now();</script>';
}
?>