<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }
//$settings['bootstrap'] = '0';
//define("THEME_BULLET", "<span class='bullet'>&middot;</span>");
define("THEME_BULLET", "");

require_once INCLUDES."theme_functions_include.php";

function render_page($license = FALSE) {
	global $settings, $main_style, $locale, $mysql_queries_time;
	//Header
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='full-header'>\n".showbanners()."</td>\n";
	echo "</tr>\n</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='sub-header-left'></td>\n";
	echo "<td class='sub-header'>".showsublinks(" ".THEME_BULLET." ", "")."</td>\n";
	echo "<td align='right' class='sub-header'><div class='hidden-xs'>".showsubdate()."</div>\n</td>\n";
	echo "<td class='sub-header-right'></td>\n";
	echo "</tr>\n</table>\n";
	if ($main_style == "") {
		$colspan = "";
	} elseif ($main_style == "side-both") {
		$colspan = "colspan='3'";
	} else {
		$colspan = "colspan='2'";
	}
	//Content
	echo renderNotices(getNotices(array('all', FUSION_SELF)));
	echo "<table cellpadding='0' cellspacing='0' width='100%' class='$main_style'>\n";
	echo AU_CENTER ? "<tr><td class='main-bg' ".$colspan." valign='top'>".AU_CENTER."</td>\n</tr>\n<tr>\n" : "<tr>\n";
	if (LEFT) {
		echo "<td class='side-border-left' valign='top'>".LEFT."</td>";
	}
	echo "<td class='main-bg' valign='top'>".U_CENTER.CONTENT.L_CENTER."</td>";
	if (RIGHT) {
		echo "<td class='side-border-right' valign='top'>".RIGHT."</td>";
	}
	echo BL_CENTER ? "</tr>\n<tr><td class='main-bg' ".$colspan." valign='top'>".BL_CENTER."</td>\n</tr>\n<tr>\n" : "";
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
	if (!$license) {
		echo "<br /><br />\n".showcopyright();
	}
	echo "</td>\n";
	echo "</tr>\n</table>\n";
	/*foreach ($mysql_queries_time as $query) {
		echo $query[0]." QUERY: ".$query[1]."<br />";
	}*/
}

/* New in v7.02 - render comments */
function render_comments($c_data, $c_info) {
	global $locale, $settings;
	opentable($locale['c100']);
	if (!empty($c_data)) {
		echo "<div class='comments floatfix'>\n";
		$c_makepagenav = '';
		if ($c_info['c_makepagenav'] !== FALSE) {
			echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n";
		}
		foreach ($c_data as $data) {
			$comm_count = "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a>";
			echo "<div class='tbl2 clearfix floatfix'>\n";
			if ($settings['comments_avatar'] == "1") {
				echo "<span class='comment-avatar'>".$data['user_avatar']."</span>\n";
			}
			echo "<span style='float:right' class='comment_actions'>".$comm_count."\n</span>\n";
			echo "<span class='comment-name'>".$data['comment_name']."</span>\n<br />\n";
			echo "<span class='small'>".$data['comment_datestamp']."</span>\n";
			if ($data['edit_dell'] !== FALSE) {
				echo "<br />\n<span class='comment_actions'>".$data['edit_dell']."\n</span>\n";
			}
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
	echo "<td class='capmain'>".trimlink($subject,30)."</td>\n";
	echo "<td class='capmain-right'></td>\n";
	echo "</tr>\n</table>\n";
	echo "<table width='100%' cellpadding='0' cellspacing='0' class='spacer'>\n<tr>\n";
	echo "<td class='main-body middle-border'>".$info['cat_image'].$news."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' class='news-footer middle-border'>\n";
	echo newsposter($info, " &middot;").newscat($info, " &middot;").newsopts($info, "&middot;").itemoptions("N", $info['news_id']);
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
	echo articleposter($info, " &middot;").articlecat($info, " &middot;").articleopts($info, "&middot;").itemoptions("A", $info['article_id']);
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

function openside($title, $collapse = FALSE, $state = "on") {
	global $panel_collapse;
	$panel_collapse = $collapse;
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='scapmain-left'></td>\n";
	echo "<td class='scapmain'>".$title."</td>\n";
	if ($collapse == TRUE) {
		$boxname = str_replace(" ", "", $title);
		echo "<td class='scapmain' align='right'>".panelbutton($state, $boxname)."</td>\n";
	}
	echo "<td class='scapmain-right'></td>\n";
	echo "</tr>\n</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%' class='spacer'>\n<tr>\n";
	echo "<td class='side-body'>\n";
	if ($collapse == TRUE) {
		echo panelstate($state, $boxname);
	}
}

function closeside() {
	global $panel_collapse;
	if ($panel_collapse == TRUE) {
		echo "</div>\n";
	}
	echo "</td>\n</tr>\n</table>\n";
}


