<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Theme: Modern10
| Filename: theme.php
| Author: Hans Kristian Flaatten {Starefossen}
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

require_once INCLUDES."theme_functions_include.php";

// Required constant
define("THEME_BULLET", "");

// Set png images
set_image("up", THEME."images/up.png");
set_image("down", THEME."images/down.png");
set_image("left", THEME."images/left.png");
set_image("right", THEME."images/right.png");

set_image("newthread", THEME."forum/newthread.png");
set_image("reply", THEME."forum/reply.png");
set_image("forum_edit", THEME."forum/edit.png");
set_image("quote", THEME."forum/quote.png");
set_image("pm", THEME."forum/pm.png");
set_image("web", THEME."forum/web.png");
set_image("email", THEME."forum/email.png");
set_image("profile", THEME."forum/profile.png");

set_image("folder", THEME."forum/folder.png");
set_image("folderhot", THEME."forum/folderhot.png");
set_image("folderlock", THEME."forum/folderlock.png");
set_image("foldernew", THEME."forum/foldernew.png");
set_image("stickythread", THEME."forum/stickythread.png");

function render_page($license = false) {
	global $settings, $main_style, $locale, $mysql_queries_time;

	// Container Start
	echo "<div id='container'>\n";
	
	// Top Nav
	echo "<div id='top-nav'>".showsublinks("")."</div>\n";
	
	// Header
	echo "<div id='header'><a href='".BASEDIR."index.php'>".$settings['sitename']."</a></div>\n";
	
	// Search
	echo "<div id='search'>\n";
	echo "<form action='".BASEDIR."search.php' method='get'>\n";
	echo "<input type='text' class='search' name='stext' />";
	echo "<input type='submit' class='submit' name='search' value='' />";
	echo "</form>\n";
	echo "</div>\n";
	echo "<div class='clear'></div>\n";
	
	// Content
	echo "<div id='conent'>\n";
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
	echo "<div id='footer' class='".$main_style."'>\n";
	echo "<div id='copyright-site'>".stripslashes($settings['footer'])."</div>\n";
	if (!$license) { echo "<div id='copyright-fusion'>".showcopyright()."</div>\n"; }
	echo "</div>\n";

	echo "<!-- ".showrendertime()." -->";
	
	// Container End
	echo "</div>\n";	

}

function render_news($subject, $news, $info) {
	global $_GET; $image = "";
	
	$image = $info['cat_image'];
	
	echo "<a name='news_".$info['news_id']."' id='news_".$info['news_id']."'></a>\n";
	
	echo "<div class='news-item floatfix'>\n";
	
	echo "<div class='content'>\n";
		echo "<div class='image'>".$image."</div>\n";
		echo "<div class='info'>\n";
			echo "<span class='title'><a href='".BASEDIR."news.php?readmore=".$info['news_id']."'>".$info['news_subject']."</a></span>\n";
			echo "<span class='poster'>".newsposter($info, "")."</span>\n";
		echo "</div>\n";
		echo "<div class='subject'>".$news."</div>\n";
		echo "<div class='footer'>\n";
			echo "<span class='category'>".newscat($info, " - ")."</span>";
			echo "<span class='read-more'>".newsopts($info, " - ")."</span>";
		echo "</div>\n";
	echo "</div>\n";
	echo "<div class='clear'></div>\n";
	
	echo "</div>\n";
}

function render_article($subject, $article, $info) {
	
	echo "<div class='news-item'>\n";
	
	echo "<div class='content'>\n";
		echo "<div class='info'>\n";
			echo "<span class='title'>".$subject."</span>\n";
			echo "<span class='poster'>".articleposter($info, "")."</span>\n";
		echo "</div>\n";
		echo "<div class='subject'>".$article."</div>\n";
		echo "<div class='footer'>\n";
			echo "<span class='category'>".articlecat($info, " - ")."</span>";
			echo "<span class='read-more'>".articleopts($info, " - ")."</span>";
		echo "</div>\n";
	echo "</div>\n";
	
	echo "</div>\n";
}

/* New in v7.02 - render comments */
function render_comments($c_data, $c_info){
	global $locale, $settings;
	opentable($locale['c100']);
	if (!empty($c_data)){
		echo "<div class='comments floatfix'>\n";
			$c_makepagenav = '';
			if ($c_info['c_makepagenav'] !== FALSE) { 
			echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n"; 
		}
			foreach($c_data as $data) {
	        $comm_count = "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a>";
			echo "<div class='tbl2 clearfix floatfix'>\n";
			if ($settings['comments_avatar'] == "1") { echo "<span class='comment-avatar'>".$data['user_avatar']."</span>\n"; }
	        echo "<span style='float:right' class='comment_actions'>".$comm_count."\n</span>\n";
			echo "<span class='comment-name'>".$data['comment_name']."</span>\n<br />\n";
			echo "<span class='small'>".$data['comment_datestamp']."</span>\n";
	if ($data['edit_dell'] !== false) { echo "<br />\n<span class='comment_actions'>".$data['edit_dell']."\n</span>\n"; }
			echo "</div>\n<div class='tbl1 comment_message'>".$data['comment_message']."</div>\n";
		}
		echo $c_makepagenav;
		if ($c_info['admin_link'] !== FALSE) {
			echo "<div style='float:right' class='comment_admin'>".$c_info['admin_link']."</div>\n";
		}
		echo "</div>\n";
	} else {
		echo $locale['c101']."\n";
	}
	closetable();   
}

function opentable($title) {

	echo "<div class='side-panel'>\n";
	echo "<div class='title'>".$title."</div>\n";

}

function closetable() {

	echo "</div>";

}

function openside($title, $collapse = false, $state = "on") {

	global $panel_collapse; $panel_collapse = $collapse;
	
	echo "<div class='side-panel'>\n";
	echo "<div class='title'>".$title."</div>\n";
	
	/* echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='scapmain-left'></td>\n";
	echo "<td class='scapmain'>".$title."</td>\n";
	if ($collapse == true) {
		$boxname = str_replace(" ", "", $title);
		echo "<td class='scapmain' align='right'>".panelbutton($state, $boxname)."</td>\n";
	}
	echo "<td class='scapmain-right'></td>\n";
	echo "</tr>\n</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%' class='spacer'>\n<tr>\n";
	echo "<td class='side-body'>\n";	
	if ($collapse == true) { echo panelstate($state, $boxname); } */

}

function closeside() {
	
	global $panel_collapse;

	echo "</div>\n";
	
	/* if ($panel_collapse == true) { echo "</div>\n"; }	
	echo "</td>\n</tr>\n</table>\n"; */

}
?>
