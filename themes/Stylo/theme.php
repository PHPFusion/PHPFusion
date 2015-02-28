<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Stylo v2 Theme for PHP-Fusion V7.02.xx
| Author: Ion "Falcon" Cladico
| Web: www.agxthemes.com
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
define("THEME_BULLET", "<img src='".THEME."images/bullet.gif' class='bullet' alt='&raquo;' border='0' />");
define("THEME_WIDTH", "100%");

$enable_colour_switcher = true;

require_once THEME."functions.php";
require_once THEMES."templates/switcher.php";
require_once INCLUDES."theme_functions_include.php";

$colour_switcher = new Switcher("select", "colour", "png", "blue", "switcherbutton");
if(!$enable_colour_switcher){
	$colour_switcher->disable();
}

function get_head_tags(){
	global $colour_switcher;
	echo $colour_switcher->makeHeadTag();
	echo "<!--[if lte IE 7]><style type='text/css'>.clearfix {display:inline-block;} * html .clearfix{height: 1px;}</style><![endif]-->";
}

function render_page($license = false) {

	add_handler("theme_output");
	global $settings, $main_style, $locale, $colour_switcher, $mysql_queries_time;

	//Wrapper
	echo "<div class='wrapper' style='width:".THEME_WIDTH.";'>\n";

	//Header
	echo "<div class='main-header'>\n".showbanners()."</div>\n";
	echo "<div class='sub-header clearfix floatfix'>".showsublinks("","")."</div>\n";

	// Content
	echo "<div class='main-bg'>\n";
	if (LEFT) { echo "<div id='side-left'>".LEFT."</div>\n"; }
	if (RIGHT) { echo "<div id='side-right'>".RIGHT."</div>\n"; }
	echo "<div id='side-center' class='".$main_style."'>";
	echo "<div class='upper'>".U_CENTER."</div>\n";
	echo "<div class='content'>".CONTENT."</div>\n";
	echo "<div class='lower'>".L_CENTER."</div>\n";
	echo "</div>\n";
	echo "<div class='clear'></div>\n";
	echo "</div>\n";

	//Footer
	echo "<div class='sub-footer-top'></div>\n";
	echo "<div class='sub-footer clearfix'>\n";
if ($settings['rendertime_enabled'] == 1 || ($settings['rendertime_enabled'] == 2 && iADMIN)) {
	echo "<div class='flleft' style='padding-top: 8px;'>".showrendertime()."<br />".showcounter()."</div>\n";
  } else { echo "<div class='flleft' style='padding: 15px 0 6px 0;'>".showcounter()."</div>\n"; }
    //echo "<div class='flright' style='padding: 15px 0 6px 0;'>".showsubdate()."</div>\n";
	echo "<div style='padding: 15px 0 6px 0;'>".$colour_switcher->makeForm("flright")."</div>\n";
	echo "</div>\n";

	echo "<div class='main-footer clearfix'>\n";
	echo "<div class='flleft'>\n";
	if (!$license) { echo showcopyright(); }
	echo "<br />Theme designed by <a href='http://www.agxthemes.com'>Falcon</a></div>\n";
	echo "<div class='flright' style='width: 50%; text-align: right;'>".stripslashes($settings['footer'])."</div>\n";
	echo "</div>\n";

	echo "</div>\n";

}

function render_comments($c_data, $c_info){
	global $locale, $settings;

	if (!empty($c_data)){
		echo "<div class='comments floatfix'>\n";	
	    echo "<div style='margin-bottom: 15px;' class='floatfix'>\n";
		$c_makepagenav = '';
    if ($c_info['c_makepagenav'] !== false) { echo $c_makepagenav = "<div class='flleft'>".$c_info['c_makepagenav']."</div>\n"; }
	if ($c_info['admin_link'] !== false) { echo "<div class='flright'>".$c_info['admin_link']."</div>\n"; }
		echo "</div>\n";
		
		echo "<div class='comment-main'>\n";
	foreach($c_data as $data) {
			$comm_count = "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a>";
	if ($settings['comments_avatar'] == "1") { 
	    echo "<div class='comment-avatar-wrap'>".$data['user_avatar']."</div>\n";
	}
        echo "<div class='comment'>\n";
		echo "<div class='flright'>".$comm_count."\n</div>\n";
		echo "<div class='user'>".$data['comment_name']."\n";
		echo "<span class='date small'>".$data['comment_datestamp']."</span>\n";
		echo "</div>\n";
		echo "<div class='comment-body'><p>".$data['comment_message']."</p></div>\n";
	if ($data['edit_dell'] !== false) { echo "<span class='comment_actions'>".$data['edit_dell']."\n</span>\n"; }
		echo "</div>\n";
	}
		echo "</div>\n";
		
		echo $c_makepagenav;
		echo "</div>\n";

	} else {
	    echo "<div class='nocomments-message spacer'>".$locale['c101']."</div>\n";
	}

}

function render_news($subject, $news, $info) {
global $locale, $settings, $aidlink;

set_image("edit", THEME."images/icons/news_edit.png");

	echo "<div class='capmain-top'></div>\n";
	echo "<div class='capmain-news floatfix'>\n";
	echo "<div class='flleft'>".$subject."</div>\n";
if (iADMIN && checkrights("N")) {
	echo "<div class='flright clearfix' style='padding-right: 13px;'>\n";
    echo "<a href='".ADMIN."news.php".$aidlink."&amp;action=edit&amp;news_id=".$info['news_id']."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' /></a>\n";
	echo "</div>\n"; }
	echo "</div>\n";
	echo "<div class='spacer'>\n";
	echo "<div class='news_info middle-border floatfix'>\n";
	echo "<ul>\n";
	echo "<li class='print'><a href='".BASEDIR."print.php?type=N&amp;item_id=".$info['news_id']."'><span>".$locale['global_075']."</span></a></li>\n";
	echo "<li class='date'>".showdate("%d %b %Y", $info['news_date'])."</li>\n";
	echo "<li class='author'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</li>\n";
if ($info['cat_id']) { echo "<li class='cat'><a href='".BASEDIR."news_cats.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a></li>\n";
	} else { echo "<li class='cat'><a href='".BASEDIR."news_cats.php?cat_id=0'>".$locale['global_080']."</a></li>\n"; }
if ($info['news_ext'] == "y" || ($info['news_allow_comments'] && $settings['comments_enabled'] == "1")) {
    echo "<li class='reads'>".$info['news_reads'].$locale['global_074']."</li>\n"; }
if ($info['news_allow_comments'] && $settings['comments_enabled'] == "1") {
    echo "<li class='comments'><a href='".BASEDIR."news.php?readmore=".$info['news_id']."#comments'>".$info['news_comments'].($info['news_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a></li>\n"; }
	echo "</ul>\n";
    echo "</div>\n";
	echo "<div class='main-body floatfix'>\n";
if ($info['news_sticky'] == "1") {
	echo "<div style='position:absolute; padding-top:3px;'><img src='".THEME."images/icons/sticky.png' alt='sticky' width='41px' border='0' height='41px' /></div>";
	}
	echo $info['cat_image'].$news."<br />\n";
if (!isset($_GET['readmore']) && $info['news_ext'] == "y") {
	echo "<div class='flright'>\n";
	echo "<a href='".BASEDIR."news.php?readmore=".$info['news_id']."' class='button'><span class='rightarrow icon'></span>".$locale['global_072']."</a>\n";
	echo "</div>\n";
}
	echo "</div>\n";
	echo "</div>\n";

}

function render_article($subject, $article, $info) {
global $locale, $settings, $aidlink;

set_image("edit", THEME."images/icons/article_edit.png");

	echo "<div class='capmain-top'></div>\n";
	echo "<div class='capmain-articles floatfix'>\n";
	echo "<div class='flleft'>".$subject."</div>\n";
if (iADMIN && checkrights("A")) {
	echo "<div class='flright clearfix' style='padding-right: 13px;'>\n";
    echo "<a href='".ADMIN."articles.php".$aidlink."&amp;action=edit&amp;article_id=".$info['article_id']."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' /></a>\n";
	echo "</div>\n"; }
	echo "</div>\n";
	echo "<div class='spacer'>\n";
	echo "<div class='news_info middle-border floatfix'>\n";
	echo "<ul>\n";
	echo "<li class='print'><a href='".BASEDIR."print.php?type=A&amp;item_id=".$info['article_id']."'><span>".$locale['global_075']."</span></a></li>\n";
	echo "<li class='date'>".showdate("%d %b %Y", $info['article_date'])."</li>\n";
	echo "<li class='author'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</li>\n";
if ($info['cat_id']) {
	echo "<li class='cat'><a href='".BASEDIR."articles.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a></li>\n";
	} else { echo "<li class='cat'><a href='".BASEDIR."articles.php?cat_id=0'>".$locale['global_080']."</a></li>\n"; }
	echo "<li class='reads'>".$info['article_reads'].$locale['global_074']."</li>\n";
if ($info['article_allow_comments'] && $settings['comments_enabled'] == "1") {
	echo "<li class='comments'><a href='".BASEDIR."articles.php?article_id=".$info['article_id']."#comments'>".$info['article_comments'].($info['article_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a></li>\n"; }
	echo "</ul>\n";
    echo "</div>\n";
	echo "<div class='main-body floatfix'>".($info['article_breaks'] == "y" ? nl2br($article) : $article)."</div>\n";
	echo "</div>\n";

}

function opentable($title) {

	echo "<div class='capmain-top'></div>\n";
	echo "<div class='capmain'>".$title."</div>\n";

	echo "<div class='main-body floatfix spacer'>\n";

}

function closetable() {

	echo "</div>\n";

}

function openside($title, $collapse = false, $state = "on") {

	global $panel_collapse; $panel_collapse = $collapse;

	echo "<div class='scapmain-top'></div>\n";
	echo "<div class='scapmain floatfix clearfix'>\n";
	echo "<div class='flleft'>".$title."</div>\n";
	if ($collapse == true) {
		$boxname = str_replace(" ", "", $title);
		echo "<div class='flright' style='padding-top: 2px;'>".panelbutton($state, $boxname)."</div>\n";
	}
	echo "</div>\n";

	echo "<div class='side-body floatfix spacer'>\n";
	if ($collapse == true) { echo panelstate($state, $boxname); }

}

function closeside() {

	global $panel_collapse;

	if ($panel_collapse == true) { echo "</div>\n"; }
	echo "</div>\n";

}

?>