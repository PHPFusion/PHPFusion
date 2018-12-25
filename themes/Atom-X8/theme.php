<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 PHP-Fusion International
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.php
| Author: Hien (Frederick MC Chan)
| Author: Falk (Joakim Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

ob_end_clean();
ob_start();

require_once THEME."atom.micro.php";
require_once THEME."functions.php";
require_once THEME."opentable.switch.php";

define('BOOTSTRAP', TRUE);
define('ENTYPO', TRUE);

// Uncomment to enable/disable styles

// Disable Load Default CCS
// define('NO_DEFAULT_CSS', TRUE);

// Disable Load Global CCS
// define('NO_GLOBAL_CSS', TRUE);

// Enable Fontawesome
// define('FONTAWESOME', TRUE);

define("THEME_BULLET", "<span class='bullet'>&middot;</span>");
define("THEME_WIDTH", "1000px;");
define("TEMPLATE", THEME."tpl/");
define("THEME_IMG", THEME."images/");
define("ASSETS", THEME."assets/");
define("SUBNAV", true);

// Last seen users activate DB_ONLINE
function cache_users() {
	global $userdata;
	$cache_users = dbquery("SELECT * FROM ".DB_ONLINE." WHERE online_user=".($userdata['user_level'] != 0 ? "'".$userdata['user_id']."'" : "'0' AND online_ip='".USER_IP."'"));
	if (dbrows($cache_users)) {
		$cache_users = dbquery("UPDATE ".DB_ONLINE." SET online_lastactive='".time()."' WHERE online_user=".($userdata['user_level'] != 0 ? "'".$userdata['user_id']."'" : "'0' AND online_ip='".USER_IP."'")."");
	} else {
		$cache_users = dbquery("INSERT INTO ".DB_ONLINE." (online_user, online_ip, online_lastactive) VALUES ('".($userdata['user_level'] != 0 ? $userdata['user_id'] : "0")."', '".USER_IP."', '".time()."')");
	}
	$cache_users = dbquery("DELETE FROM ".DB_ONLINE." WHERE online_lastactive<".(time()-60)."");
	$count_users = dbcount("(online_user)", DB_ONLINE);
}

function render_page($license = false) {
	global $settings, $main_style, $locale, $userdata;
	
	cache_users();
	add_to_head("<script src='".THEME."pace.min.js'></script>");
	add_to_head("<link href='".THEME."pace-theme-minimal.css' rel='stylesheet' />");
	add_to_head("<link href='".ASSETS."google-code-prettify/prettify.css' type='text/css' rel='stylesheet' >");
	add_to_head("<script type='text/javascript' src='".ASSETS."google-code-prettify/prettify.js'></script>");
	add_to_head("<link rel='stylesheet' type='text/css' href='".THEME."colorbox.css'>");
    add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
	add_to_footer('<script type="text/javascript">
	$(document).ready(function() {
		prettyPrint();

		$(".downloadolay").colorbox({iframe:true,height:"100%",width:"100%",maxWidth:"560px",maxHeight:"170px",scrolling:false,overlayClose:false,transition:"elastic"});			

		$(".newsoverlay").colorbox({
			transition: "elasic",
			height:"100%",
			width:"100%",
			maxWidth:"98%",
			maxHeight:"98%",
			scrolling:false,
			overlayClose:true,
			close:false,
			photo:true,
			onComplete: function(result) {
				$("#colorbox").live("click", function(){
				$(this).unbind("click");
				$.fn.colorbox.close();
				});
			},
			onLoad: function () {
			}
	   });
	});
	</script>');

// Start Theme
echo "<section id='topnav' style='margin-top:13px;'>\n";
echo "<div class='container'>\n";
echo "<nav class='nav atom-x-nav'>\n";
echo user_login();
echo ($settings['sitebanner']) ? "<p class='logo'><a href='".BASEDIR."news.php'><img src='".BASEDIR.$settings['sitebanner']."'></a></p> \n" : $settings['sitename'];
echo "</nav>\n";
echo "<nav class='nav atom-x-subnav' style='z-index: 999;'>";
echo horizontalnav();
echo "</nav>\n";
echo "</div>\n";
echo "</section>\n";
if (iMEMBER) {
echo "<section class='p-0'>\n<div class='container'>\n";
echo user_info_bar($userdata);
echo "</div>\n</section>\n";
}
echo "<section style='z-index: -1'><div class='container'>\n";
$iLeft = ''; $iRight = '';

// Load Atom engine
echo atom_micro($iLeft, $iRight);

echo "</div></section>\n";

echo "<section class='p-0'><div class='container'>\n";
echo "<footer id='footer' class='m-b-50' role='footer'>\n";
echo "<div class='row'>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
echo "Atom-X8 for PHP-Fusion Version 8 - ".date('Y')." All rights reserved <br />
<a href='".BASEDIR."legal/privacy.php'>Privacy Policy</a> ·
<a href='".BASEDIR."legal/tos.php'>Terms of Service</a> ·
<a href='".BASEDIR."legal/coc.php'>Code of Conduct</a> \n";


echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6 text-right'>".license()."
".(($settings['rendertime_enabled'] == 1) || ($settings['rendertime_enabled'] == 2 && iADMIN) ? showrendertime()." - ".showMemoryUsage() : '' )."
".showcounter()."
</div>\n";

echo "</footer>\n";
echo "</div></section>\n";

}

function license() {
	if (function_exists('showcopyright') && preg_match("@".copyright()."@si", copyright())) {
		return copyright();
	} else {
		print_p('Illegal Copyright Infringements');
	}
}

function copyright() {
	if (!defined('LICENSED')) { define('LICENSED', true); }
	$res = "Powered by <a href='https://www.php-fusion.co.uk'>PHP-Fusion</a> Copyright &copy; ".date("Y")." PHP-Fusion Inc<br >";
	$res .= "Released as free software without warranties under <a href='http://www.fsf.org/licensing/licenses/agpl-3.0.html'>GNU Affero GPL</a> v3.<br>\n";
	return $res;
}

/* Basic News Section */
function render_news($subject, $news, $info) {
global $locale, $settings;
opentable($subject);
	echo "<ul class='news-info'>\n";
	//Author
	echo "<li class='author'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</li>\n";
	//Date
	echo "<li class='dated'>".showdate("%d %b %Y", $info['news_date'])."</li>\n";
	//Category
	echo "<li class='cat'>\n";
		if ($info['cat_id']) { echo "<a href='".BASEDIR."news_cats.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>\n";
	} else { echo "<a href='".BASEDIR."news_cats.php?cat_id=0'>".$locale['global_080']."</a>"; }
	echo "</li>\n";
	//Reads
	if ($info['news_ext'] == "y" || ($info['news_allow_comments'] && $settings['comments_enabled'] == "1")) {
	echo "<li class='reads'>\n";
		echo $info['news_reads'].$locale['global_074']; 
	echo "</li>\n";}
	//Comments
	if ($info['news_allow_comments'] && $settings['comments_enabled'] == "1") { echo "<li class='comments'><a ".(isset($_GET['readmore']) ? "class='scroll'" : "")." href='".BASEDIR."news.php?readmore=".$info['news_id']."#comments'>".$info['news_comments']."".($info['news_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a></li>\n"; }
	echo "</ul>\n";
	//The message
	echo $info['cat_image'].$news;

	//Read more button
	if (!isset($_GET['readmore']) && $info['news_ext'] == "y") {
		echo "<div class='flright'><a href='".BASEDIR."news.php?readmore=".$info['news_id']."' class='button'><img alt='".$locale['global_072']."' class='rightarrow icon' src='".THEME."images/blank.gif' />".$locale['global_072']."</a></div>\n";
	}
closetable();
}

function render_blog($subject, $blog, $info) {
global $locale, $settings;
opentable($subject);
	echo "<ul class='blog-info'>\n";
	//Author
	echo "<li class='author'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</li>\n";
	//Date
	echo "<li class='dated'>".showdate("%d %b %Y", $info['blog_date'])."</li>\n";
	//Category
	echo "<li class='cat'>\n";
		if ($info['cat_id']) { echo "<a href='".BASEDIR."blog_cats.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>\n";
	} else { echo "<a href='".BASEDIR."blog_cats.php?cat_id=0'>".$locale['global_080']."</a>"; }
	echo "</li>\n";
	//Reads
	if ($info['blog_ext'] == "y" || ($info['blog_allow_comments'] && $settings['comments_enabled'] == "1")) {
	echo "<li class='reads'>\n";
		echo $info['blog_reads'].$locale['global_074']; 
	echo "</li>\n";}
	//Comments
	if ($info['blog_allow_comments'] && $settings['comments_enabled'] == "1") { echo "<li class='comments'><a ".(isset($_GET['readmore']) ? "class='scroll'" : "")." href='".BASEDIR."blog.php?readmore=".$info['blog_id']."#comments'>".$info['blog_comments']."".($info['blog_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a></li>\n"; }
	echo "</ul>\n";
	//The message
	echo $info['cat_image'].$blog;

	//Read more button
	if (!isset($_GET['readmore']) && $info['blog_ext'] == "y") {
		echo "<div class='flright'><a href='".BASEDIR."blog.php?readmore=".$info['blog_id']."' class='button'><img alt='".$locale['global_072']."' class='rightarrow icon' src='".THEME."images/blank.gif' />".$locale['global_072']."</a></div>\n";
	}
closetable();
}

/* Basic Articles Section */
function render_article($subject, $article, $info) {
global $locale, $settings;
opentable($subject);
	echo "<ul class='article-info'>\n";
	//Author
	echo "<li class='author'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</li>\n";
	//Date
	echo "<li class='dated'>".showdate("%d %b %Y", $info['article_date'])."</li>\n";
	//Category
	echo "<li class='cat'>\n";
		if ($info['cat_id']) { echo "<a href='".BASEDIR."wiki.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>\n";
	} else { echo "<a href='".BASEDIR."wiki.php?cat_id=0'>".$locale['global_080']."</a>"; }
	echo "</li>\n";
	//Reads
	echo "<li class='reads'>".$info['article_reads'].$locale['global_074']."</li>\n";
	//Comments
	if ($info['article_allow_comments'] && $settings['comments_enabled'] == "1") { echo "<li class='comments'><a class='scroll' href='".BASEDIR."wiki.php?article_id=".$info['article_id']."#comments'>".$info['article_comments'].($info['article_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a></li>\n"; }
	echo "</ul>\n";
	//The message
	echo ($info['article_breaks'] == "y" ? nl2br($article) : $article)."\n";
closetable();
}

// Render comments
function render_comments($c_data, $c_info){
		global $locale, $settings;
		if ($c_info['admin_link'] !== FALSE) {
				echo "<div class='comment_admin floatfix' style='margin-bottom: 15px'><div class='flright'>".$c_info['admin_link']."</div></div>\n";
			}
		if (!empty($c_data)){
			echo "<div class='user-comments floatfix'>\n";
 			$c_makepagenav = '';
 			if ($c_info['c_makepagenav'] !== FALSE) { 
				echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n"; 
			}
 			foreach($c_data as $data) {
				echo "<div id='c".$data['comment_id']."' class='comment'>\n";
					//User avatar
					if ($settings['comments_avatar'] == "1") { echo "<span class='user_avatar'>".$data['user_avatar']."</span>\n"; $noav = ""; } else { $noav = "noavatar"; }
					echo "<div class='tbl1 comment_wrap $noav'>";
					//Pointer tip
					if ($settings['comments_avatar'] == "1") { echo "<div class='pointer'><span></span></div>\n"; }
					//Options
					echo "<div class='comment-info'>";
					if ($data['edit_dell'] !== FALSE) { 
						echo "<div class='actions flright'>".$data['edit_dell']."\n</div>\n";
					}
					//Info
					echo "<a class='scroll' href='".PERMALINK_CURRENT_PATH."#c".$data['comment_id']."'>#".$data['i']."</a> |\n";
					echo "<span class='comment-name'>".$data['comment_name']."</span>\n";
					echo "<span class='small'>".$data['comment_datestamp']."</span></div>\n";
					//The message
					echo "<div class='comment-msg'>".$data['comment_message']."</div></div></div>\n";
			}

			echo $c_makepagenav;
			
			echo "</div>\n";
		} else {
			echo "<div class='nocomments-message spacer'>".$locale['c101']."</div>\n";
		} 
}

function openside($title=false, $collapse = false, $state = "on") {
	global $panel_collapse; $panel_collapse = $collapse;
	echo "<aside class='panel-atom panel-default'>";
	echo "<div class='panel-heading'>\n";
	echo $title;
	echo "</div>\n";
	echo "<div class='panel-body m-b-15'>\n";
}

function closeside() {
	echo "</div>\n</aside>\n";
}

function closetable() {
	echo "</div>\n</div>\n";
}