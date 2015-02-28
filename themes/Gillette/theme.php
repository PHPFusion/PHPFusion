<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.php
| Author: Nick Jones (Digitanium)
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

define("THEME_BULLET", "<span class='bullet'>&middot;</span>");

require_once INCLUDES."theme_functions_include.php";

function render_page($license = false) {
	
	global $settings, $main_style, $locale, $mysql_queries_time;

	//Header
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='full-header'>\n".showbanners()."</td>\n";
	echo "</tr>\n</table>\n";
	
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='sub-header-left'></td>\n";
	echo "<td class='sub-header'>".showsublinks(" ".THEME_BULLET." ", "white")."</td>\n";
	echo "<td align='right' class='sub-header'>".showsubdate()."</td>\n";
	echo "<td class='sub-header-right'></td>\n";
	echo "</tr>\n</table>\n";
	
	//Content
	echo "<table cellpadding='0' cellspacing='0' width='100%' class='$main_style'>\n<tr>\n";
	if (LEFT) { echo "<td class='side-border-left' valign='top'>".LEFT."</td>"; }
	echo "<td class='main-bg' valign='top'>".U_CENTER.CONTENT.L_CENTER."</td>";
	if (RIGHT) { echo "<td class='side-border-right' valign='top'>".RIGHT."</td>"; }
	echo "</tr>\n</table>\n";
	
	//Footer
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='sub-header-left'></td>\n";
	echo "<td align='left' class='sub-header'>".showrendertime()."</td>\n";
	echo "<td align='right' class='sub-header'>".showcounter()."</td>\n";
	echo "<td class='sub-header-right'></td>\n";
	echo "</tr>\n</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td align='center' class='main-footer'>".stripslashes($settings['footer']);
	if (!$license) { echo "<br /><br />\n".showcopyright(); }
	echo "</td>\n";
	echo "</tr>\n</table>\n";
	
	/*foreach ($mysql_queries_time as $query) {
		echo $query[0]." QUERY: ".$query[1]."<br />";
	}*/

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

function render_news($subject, $news, $info) {

	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='capmain-left'></td>\n";
	echo "<td class='capmain'>".$subject."</td>\n";
	echo "<td class='capmain-right'></td>\n";
	echo "</tr>\n</table>\n";
	echo "<table width='100%' cellpadding='0' cellspacing='0' class='spacer'>\n<tr>\n";
	echo "<td class='main-body middle-border'>".$info['cat_image'].$news."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' class='news-footer middle-border'>\n";
	echo newsposter($info," &middot;").newscat($info," &middot;").newsopts($info,"&middot;").itemoptions("N",$info['news_id']);
	echo "</td>\n";
	echo "</tr><tr>\n";
	echo "<td style='height:5px;background-color:#f6a504;'></td>\n";
	echo "</tr>\n</table>\n";

}

function render_article($subject, $article, $info) {
	
	echo "<table width='100%' cellpadding='0' cellspacing='0'>\n<tr>\n";
	echo "<td class='capmain-left'></td>\n";
	echo "<td class='capmain'>".$subject."</td>\n";
	echo "<td class='capmain-right'></td>\n";
	echo "</tr>\n</table>\n";
	echo "<table width='100%' cellpadding='0' cellspacing='0' class='spacer'>\n<tr>\n";
	echo "<td class='main-body middle-border'>".($info['article_breaks'] == "y" ? nl2br($article) : $article)."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' class='news-footer'>\n";
	echo articleposter($info," &middot;").articlecat($info," &middot;").articleopts($info,"&middot;").itemoptions("A",$info['article_id']);
	echo "</td>\n</tr>\n</table>\n";

}

function opentable($title) {

	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='capmain-left'></td>\n";
	echo "<td class='capmain'>".$title."</td>\n";
	echo "<td class='capmain-right'></td>\n";
	echo "</tr>\n</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%' class='spacer'>\n<tr>\n";
	echo "<td class='main-body'>\n";

}

function closetable() {

	echo "</td>\n";
	echo "</tr><tr>\n";
	echo "<td style='height:5px;background-color:#f6a504;'></td>\n";
	echo "</tr>\n</table>\n";

}

function openside($title, $collapse = false, $state = "on") {

	global $panel_collapse; $panel_collapse = $collapse;
	
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
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
	if ($collapse == true) { echo panelstate($state, $boxname); }

}

function closeside() {
	
	global $panel_collapse;

	if ($panel_collapse == true) { echo "</div>\n"; }	
	echo "</td>\n</tr>\n</table>\n";

}
?>
