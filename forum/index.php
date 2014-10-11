<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: index.php
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
require_once dirname(__FILE__)."../../maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum/main.php";

add_to_title($locale['global_200'].$locale['400']);
opentable($locale['400']);
$tab_title['title'][] = "Forum";
$tab_title['id'][] = "thread";
$tab_title['icon'][] = "";
$tab_title['title'][] = $locale['global_021'];
$tab_title['id'][] = "latest";
$tab_title['icon'][] = "";
$tab_title['title'][] = $locale['global_056'];
$tab_title['id'][] = "tracked";
$tab_title['icon'][] = "";
$tab_active = isset($_GET['section']) ? tab_active($tab_title, 0) : 'thread';
echo "<div class='panel tbl-border p-0'>\n";
echo "<div class='display-inline-block pull-right' style='max-width:250px;'>\n";
echo openform('searchform', 'searchform', 'post'," ".($settings['site_seo'] == "1" ? FUSION_ROOT : '').$settings['siteurl']."search.php?stype=forums", array('downtime' => 0));
echo form_hidden('stype', 'stype', 'stype', 'forums');
echo form_text('', 'stext', 'stext', '', array('placeholder' => $locale['550'], 'append_button' => 1));
echo closeform();
echo "</div>\n";

echo opentab($tab_title, $tab_active, 'forum_tabs', FORUM."index.php");
// == using ID as key
echo opentabbody($tab_title['title'], $tab_active, $tab_active, FORUM."index.php");
if (isset($_GET['section']) && $_GET['section'] == 'latest') {
	latest();
} elseif (isset($_GET['section']) && $_GET['section'] == 'tracked') {
	echo tracked();
} elseif (!isset($_GET['section']) || isset($_GET['section']) && $_GET['section'] == 'thread') {
	echo forum();
}
echo closetabbody();
closetable();
// Main Forum Category Function.
function forum() {
	global $locale, $userdata, $settings;
	$forum_list = "";
	$current_cat = "";
	$forumCollapsed = FALSE;
	$forumCollapse = TRUE;
	if (!isset($lastvisited) || !isnum($lastvisited)) {
		$lastvisited = time();
	}
	$catWhere = "";
	$catID = "";
	if (isset($_GET['cat']) && isnum($_GET['cat'])) {
		$check = dbcount("(forum_id)", DB_FORUMS, "forum_id='cat' AND forum_cat='0'");
		if ($check == 0) {
			$catID = $_GET['cat'];
			$catWhere = "f2.forum_id='".$catID."' AND";
		}
	}
	$result = dbquery("SELECT	f.forum_id, f.forum_cat, f.forum_name, f.forum_description, f.forum_moderators, f.forum_lastpost, f.forum_postcount,
        f.forum_threadcount, f.forum_lastuser, f.forum_access, f2.forum_id AS forum_cat_id, f2.forum_name AS forum_cat_name, f2.forum_description AS forum_cat_description,
        t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject,
        u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM ".DB_FORUMS." f
        LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat = f2.forum_id
        LEFT JOIN ".DB_THREADS." t ON f.forum_id = t.forum_id AND f.forum_lastpost=t.thread_lastpost
        LEFT JOIN ".DB_USERS." u ON f.forum_lastuser = u.user_id
        ".(multilang_table("FO") ? "WHERE f2.forum_language='".LANGUAGE."' AND" : "WHERE")." ".$catWhere." ".groupaccess('f.forum_access')." AND f.forum_cat!='0'
        GROUP BY forum_id ORDER BY f2.forum_order ASC, f.forum_order ASC, t.thread_lastpost DESC");
	$i = 0;
	if (dbrows($result) != 0) {
		echo "<div class='panel-body m-t-20 p-0'>\n";
		while ($data = dbarray($result)) {
			if ($catID != "") {
				add_to_title($locale['global_201'].$data['forum_cat_name']);
				set_meta("description", $data['forum_cat_name']);
			}
			if ($data['forum_cat_name'] != $current_cat) {
				if ($i > 0) {
					echo "</tbody></table>\n<!--sub_forum_idx_table-->\n";
				}
				$current_cat = $data['forum_cat_name'];
				$forumStatus = ($forumCollapsed ? "off" : "on");
				$boxname = "forum_".$data['forum_id'];
				$element = "tbody";
				if ($i == 0) {
					echo "<!--pre_forum_idx-->";
				}
				echo "<div class='forum-table-container panel-body'><strong><a href='".BASEDIR."forum/index.php?cat=".$data['forum_cat']."'>".$data['forum_cat_name']."</a></strong>";
				if ($data['forum_cat_description']) {
					echo " - <span class='forum-cat-description'>".nl2br(parseubb($data['forum_cat_description']))."</span>";
				}
				echo "</div>\n";
				echo "<table class='forum_idx_table table table-responsive' id='forum_cat_".$data['forum_cat']."' cellpadding='0' cellspacing='0' width='100%'>\n<thead>\n<tr class='forum-cat-head'>\n";
				echo "<th class='forum-caption forum_cat_name' colspan='2'><!--forum_cat_name-->";
				echo "Directory";
				echo "</th>\n";
				echo "<th class='forum-caption' width='1%' style='white-space:nowrap'>".$locale['402']."</th>\n";
				echo "<th class='forum-caption' width='1%' style='white-space:nowrap'>".$locale['403']."</th>\n";
				echo "<th class='forum-caption' style='width: 250px;'><span class='flleft'>".$locale['404']."</span>".($forumCollapse ? "<div class='flright'>".panelbutton($forumStatus, $boxname)."</div>\n" : "")."</th>\n";
				echo "</tr>\n</thead>\n";
				echo($forumCollapse ? "".panelstate($forumStatus, $boxname, "tbody")."\n" : "<tbody>");
			}
			$i++;
			$moderators = "";
			if ($data['forum_moderators']) {
				$mod_groups = explode(".", $data['forum_moderators']);
				foreach ($mod_groups as $mod_group) {
					if ($moderators) $moderators .= ", ";
					$moderators .= $mod_group < 101 ? "<a href='".BASEDIR."profile.php?group_id=".$mod_group."'>".getgroupname($mod_group)."</a>" : getgroupname($mod_group);
				}
			}
			$forum_match = "\|".$data['forum_lastpost']."\|".$data['forum_id'];
			$fclass = 'icon-old';
			if ($data['forum_lastpost'] > $lastvisited) {
				if (iMEMBER && ($data['forum_lastuser'] == $userdata['user_id'] || preg_match("({$forum_match}\.|{$forum_match}$)", $userdata['user_threads']))) {
					$fim = "<img src='".get_image("folder")."' title='".$locale['561']."' />";
				} else {
					$fim = "<img class='img-responsive' src='".get_image("foldernew")."' title='".$locale['560']."' />";
					$fclass = 'icon-new';
				}
			} else {
				$fim = "<img class='img-responsive' src='".get_image("folder")."' title='".$locale['561']."' />";
			}
			echo "<tr id='forum_".$data['forum_id']."' >\n";
			echo "<td class='tbl2 forum-icon ".$fclass."' width='1%'>".$fim."</td>\n";
			echo "<td class='tbl1 forum-name'><!--forum_name--><h3><a href='".FORUM."viewforum.php?forum_id=".$data['forum_id']."'>".$data['forum_name']."</a></h3>\n";
			if ($data['forum_description'] || $moderators) {
				echo "<span class='forum-description small'>".nl2br(parseubb($data['forum_description']))."</span>".($data['forum_description'] && $moderators ? "<br />\n" : "");
				echo ($moderators ? "<span class='forum-moderators'><strong>".$locale['411']."</strong>".$moderators."</span>\n" : "")."\n";
			}
			echo "</td>\n";
			echo "<td class='tbl forum-stats text-center'>\n";
			echo number_format($data['forum_threadcount']);
			echo "</td>\n";
			echo "<td class='tbl2 forum-stats text-center'>\n";
			echo number_format($data['forum_postcount']);
			echo "</td>\n";
			echo "<td class='tbl1 forum-lastpost'>";
			if ($data['forum_lastpost'] == 0) {
				echo $locale['405']."</td>\n</tr>\n";
			} else {
				echo "<div class='clearfix'>\n";
				if ($settings['forum_last_post_avatar'] == 1) {
					echo "<div class='pull-left lastpost-avatar m-r-10'>".display_avatar($data, '60px')."</div>";
				}
				echo "<a class='lastpost-title' href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 25)."</a> ";
				echo "<a class='lastpost-goto' href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid']."' title='".$data['thread_subject']."'>";
				if ($data['forum_lastpost'] > $lastvisited) {
					if (iMEMBER && preg_match("({$forum_match}\.|{$forum_match}$)", $userdata['user_threads'])) {
						$fim = "<img src='".get_image("lastpost")."' alt='".$locale['404']."' title='".$locale['404']."' />";
					} else {
						$fim = "<img src='".get_image("lastpostnew")."' alt='".$locale['404']."' title='".$locale['404']."' />";
					}
				} else {
					$fim = "<img src='".get_image("lastpost")."' alt='".$locale['404']."' title='".$locale['404']."' />";
				}
				//echo $fim;
				echo "</a>$fim <br />\n";
				echo "<span class='lastpost-user small'>by ".profile_link($data['forum_lastuser'], $data['user_name'], $data['user_status'])."</span><br />\n";
				echo "<span class='lastpost-date small'>".showdate("forumdate", $data['forum_lastpost'])."</span> \n";
				echo "</div>\n</td>\n";
				echo "</tr>\n";
			}
		}
		echo "</tbody></table>\n<!--sub_forum_idx_table-->\n";
		echo "</div>\n</div>\n";
	} else {
		echo $locale['407']."\n";
	}
}

function latest() {
	global $locale, $settings;
	$_GET['rowstart'] = 0;
	$result = dbquery("SELECT	f.forum_id, f.forum_cat, f.forum_name, f.forum_description, f.forum_moderators, f.forum_lastpost, f.forum_postcount,
            f.forum_threadcount, f.forum_lastuser, f.forum_access, f2.forum_id AS forum_cat_id, f2.forum_name AS forum_cat_name, f2.forum_description AS forum_cat_description,
            t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject, t.thread_postcount, t.thread_views, t.thread_lastuser, t.thread_poll, t.thread_lastpost,
            u.user_id, u.user_name, u.user_status, u.user_avatar,
            uc.user_id AS s_user_id, uc.user_name AS s_user_name, uc.user_status AS s_user_status, uc.user_avatar AS s_user_avatar
            FROM ".DB_FORUMS." f
            LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat = f2.forum_id
            LEFT JOIN ".DB_THREADS." t ON f.forum_id = t.forum_id AND f.forum_lastpost=t.thread_lastpost
            LEFT JOIN ".DB_USERS." u ON u.user_id=t.thread_lastuser
            LEFT JOIN ".DB_USERS." uc ON uc.user_id=t.thread_author
            ".(multilang_table("FO") ? "WHERE f2.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')." AND f.forum_cat!='0' AND t.thread_hidden='0' ".(isset($_POST['filter']) && $_POST['filter'] ? "AND t.thread_lastpost < '".(time()-($_POST['filter']*24*3600))."'" : '')."
            GROUP BY thread_id ORDER BY t.thread_lastpost LIMIT ".$_GET['rowstart'].", ".$settings['numofthreads']."");
	if (dbrows($result) > 0) {
		echo "<div class='forum-table-container panel-body'>\n";
		echo "<strong>".$locale['global_021']."</strong>\n";
		echo "</div>\n";
		echo "<table class='forum_idx_table table table-responsive' cellpadding='0' cellspacing='0' width='100%'>\n<thead>\n<tr class='forum-cat-head'>\n";
		echo "<th class='forum-caption forum_cat_name'><!--forum_cat_name-->";
		echo "Directory";
		echo "</th>\n";
		echo "<th class='forum-caption' width='1%' style='white-space:nowrap'>".$locale['402']."</th>\n";
		echo "<th class='forum-caption' width='1%' style='white-space:nowrap'>".$locale['403']."</th>\n";
		echo "<th class='forum-caption' style='width: 250px;'>".$locale['404']."</th>\n";
		echo "</tr>\n</thead>\n<tbody>\n";
		while ($data = dbarray($result)) {
			$itemsubject = trimlink($data['thread_subject'], 23);
			echo "<tr id='forum_".$data['forum_id']."' >\n";
			$sdata['user_avatar'] = $data['s_user_avatar'];
			echo "<td class='tbl1 forum-name'><!--forum_name--><h3><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid']."'>".$itemsubject."</a></h3>\n";
			echo "<div class='m-t-10'>\nby ".display_avatar($sdata, '25px')." ".profile_link($data['s_user_id'], $data['s_user_name'], $data['s_user_status'])." in <a href='".FORUM."index.php?cat_id=".$data['forum_id']."'>".$data['forum_name']."</a> of <a href='".FORUM."index.php?cat_id=".$data['forum_cat_id']."'>".$data['forum_cat_name']."</a>\n</div>\n";
			echo "</td>\n";
			echo "<td class='tbl forum-stats text-center'>\n";
			echo number_format($data['forum_threadcount']);
			echo "</td>\n";
			echo "<td class='tbl2 forum-stats text-center'>\n";
			echo number_format($data['forum_postcount']);
			echo "</td>\n";
			echo "<td class='tbl1 forum-lastpost'>";
			if ($data['forum_lastpost'] == 0) {
				echo $locale['405']."</td>\n</tr>\n";
			} else {
				echo "<div class='clearfix'>\n";
				if ($settings['forum_last_post_avatar'] == 1) {
					echo "<div class='pull-left lastpost-avatar m-r-10'>".display_avatar($data, '50px')."</div>";
				}
				echo "<span class='lastpost-user small'>by ".profile_link($data['forum_lastuser'], $data['user_name'], $data['user_status'])."</span><br />\n";
				echo "<span class='lastpost-date small'>".showdate("forumdate", $data['forum_lastpost'])."</span> \n";
				echo "</div>\n</td>\n";
				echo "</tr>\n";
			}
		}
		echo "</tbody>\n</table>\n";
	} else {
		echo "<div class='well text-center'>\n".$locale['global_023']."</div>\n";
	}
	echo "<div class='panel panel-default'>\n<div class='panel-body'>\n";
	$opts = array('0' => 'All Results', '1' => '1 Day', '7' => '7 Days', '14' => '2 Weeks', '30' => '1 Month',
				  '90' => '3 Months', '180' => '6 Months', '365' => '1 Year');
	echo openform('filter_form', 'filter_form', 'post', FORUM."index.php?section=latest", array('downtime' => 0));
	echo form_button('Go', 'go', 'go', 'Go', array('class' => 'btn-primary pull-right'));
	echo form_select('', 'filter', 'filter', $opts, isset($_POST['filter']) && $_POST['filter'] ? $_POST['filter'] : 0, array('width' => '200px',
																															  'class' => 'pull-right m-l-10 m-r-10'));
	echo "<label for='filter' class='pull-right'>Display Posts from Previous</label>\n";
	echo closeform();
	echo "</div>\n</div>\n";
}

function tracked() {
	global $userdata, $locale, $settings;
	if (!iMEMBER) {
		redirect("../../index.php");
	}
	if (isset($_GET['delete']) && isnum($_GET['delete']) && dbcount("(thread_id)", DB_THREAD_NOTIFY, "thread_id='".$_GET['delete']."' AND notify_user='".$userdata['user_id']."'")) {
		$result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id=".$_GET['delete']." AND notify_user=".$userdata['user_id']);
		redirect(FUSION_SELF);
	}
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
		$_GET['rowstart'] = 0;
	}
	$result = dbquery("SELECT tn.thread_id FROM ".DB_THREAD_NOTIFY." tn
            INNER JOIN ".DB_THREADS." tt ON tn.thread_id = tt.thread_id
            INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
            WHERE tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'");
	$rows = dbrows($result);
	if ($rows) {
		$result = dbquery("
                SELECT tf.forum_id, tf.forum_name, tf.forum_access, tn.thread_id, tn.notify_datestamp, tn.notify_user,
                ttc.forum_id AS forum_cat_id, ttc.forum_name AS forum_cat_name,
                tt.thread_subject, tt.forum_id, tt.thread_lastpost, tt.thread_lastpostid, tt.thread_lastuser, tt.thread_postcount,
                tu.user_id AS user_id1, tu.user_name AS user_name1, tu.user_status AS user_status1, tu.user_avatar AS user_avatar1,
                tu2.user_id AS user_id2, tu2.user_name AS user_name2, tu2.user_status AS user_status2, tu2.user_avatar AS user_avatar2
                FROM ".DB_THREAD_NOTIFY." tn
                INNER JOIN ".DB_THREADS." tt ON tn.thread_id = tt.thread_id
                INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
                LEFT JOIN ".DB_FORUMS." ttc ON ttc.forum_id = tf.forum_cat
                LEFT JOIN ".DB_USERS." tu ON tt.thread_author = tu.user_id
                LEFT JOIN ".DB_USERS." tu2 ON tt.thread_lastuser = tu2.user_id
                INNER JOIN ".DB_POSTS." tp ON tt.thread_id = tp.thread_id
                WHERE tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'
                GROUP BY tn.thread_id
                ORDER BY tn.notify_datestamp DESC
                LIMIT ".$_GET['rowstart'].",10
            ");
		echo "<div class='forum-table-container panel-body'>\n";
		echo "<strong>".$locale['global_056']."</strong>\n";
		echo "</div>\n";
		echo "<table class='forum_idx_table table table-responsive'>\n<thead>\n<tr class='forum-cat-head'>\n";
		echo "<th class='forum-caption forum_cat_name'><!--forum_cat_name-->";
		echo $locale['global_044'];
		echo "</th>\n";
		echo "<th class='forum-caption' width='1%' style='white-space:nowrap'>".$locale['403']."</th>\n";
		echo "<th class='forum-caption' style='width:250px' style='white-space:nowrap'>".$locale['404']."</th>\n";
		echo "<th class='forum-caption' width='1%''>".$locale['global_057']."</th>\n";
		echo "</tr>\n</thead>\n<tbody>\n";
		$i = 0;
		while ($data = dbarray($result)) {
			$sdata['user_avatar'] = $data['user_avatar1'];
			$sdata2['user_avatar'] = $data['user_avatar2'];
			$itemsubject = trimlink($data['thread_subject'], 23);
			echo "<tr>\n";
			echo "<td class='tbl1 forum-name'><!--forum_name--><h3><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid']."'>".$itemsubject."</a></h3>\n";
			echo "<div class='m-t-10'>\nby ".display_avatar($sdata, '25px')." ".profile_link($data['user_id1'], $data['user_name1'], $data['user_status1'])." in <a href='".FORUM."index.php?cat_id=".$data['forum_id']."'>".$data['forum_name']."</a> of <a href='".FORUM."index.php?cat_id=".$data['forum_cat_id']."'>".$data['forum_cat_name']."</a>\n</div>\n";
			echo "</td>\n";
			echo "<td class='tbl2' style='text-align:center;white-space:nowrap'>".($data['thread_postcount']-1)."</td>\n";
			echo "<td class='tbl1 forum-lastpost'>";
			if ($data['thread_lastpost'] == 0) {
				echo $locale['405']."</td>\n";
			} else {
				echo "<div class='clearfix'>\n";
				if ($settings['forum_last_post_avatar'] == 1) {
					echo "<div class='pull-left lastpost-avatar m-r-10'>".display_avatar($sdata2, '50px')."</div>";
				}
				echo "<span class='lastpost-user small'>by ".profile_link($data['user_id2'], $data['user_name2'], $data['user_status2'])."</span><br />\n";
				echo "<span class='lastpost-date small'>".showdate("forumdate", $data['thread_lastpost'])."</span> \n";
				echo "</div>\n</td>\n";
			}
			echo "<td class='tbl1 text-center'><a class='btn btn-default' href='".FUSION_SELF."?delete=".$data['thread_id']."' onclick=\"return confirm('".$locale['global_060']."');\">".$locale['global_058']."</a></td>\n";
			echo "</tr>\n";
			$i++;
		}
		echo "</table>\n";
		echo "<div align='center' style='margin-top:5px;'>".makepagenav($_GET['rowstart'], 10, $rows, 3, FUSION_SELF."?")."</div>\n";
	} else {
		echo "<div style='text-align:center;'>".$locale['global_059']."</div>\n";
	}
}

require_once THEMES."templates/footer.php";
?>