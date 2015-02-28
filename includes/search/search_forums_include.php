<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_forums_include.php
| Author: Robert Gaudyn (Wooya)
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

include LOCALE.LOCALESET."search/forums.php";

if ($_GET['stype'] == "forums" || $_GET['stype']=="all") {
	if ($_GET['sort'] == "datestamp") {
		$sortby = "post_datestamp";
	} else if ($_GET['sort'] == "subject") {
		$sortby = "thread_subject";
	} else if ($_GET['sort'] == "author") {
		$sortby = "post_author";
	}
	$ssubject = search_querylike("thread_subject");
	$smessage = search_querylike("post_message");
	if ($_GET['fields'] == 0) {
		$fieldsvar = search_fieldsvar($ssubject);
	} else if ($_GET['fields'] == 1) {
		$fieldsvar = search_fieldsvar($smessage);
	} else if ($_GET['fields'] == 2) {
		$fieldsvar = search_fieldsvar($ssubject, $smessage);
	} else {
		$fieldsvar = "";
	}
	if ($fieldsvar) {
		$result = dbquery(
			"SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_message, tt.thread_subject,
			tf.forum_access FROM ".DB_POSTS." tp
			LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = tp.forum_id
			LEFT JOIN ".DB_THREADS." tt ON tt.thread_id = tp.thread_id			
			WHERE ".groupaccess('forum_access').($_GET['forum_id'] != 0 ? " AND tf.forum_id=".$_GET['forum_id'] : "")."
			AND ".$fieldsvar.($_GET['datelimit'] != 0 ? " AND post_datestamp>=".(time() - $_GET['datelimit']) : "")
		);
		$rows = dbrows($result);
	} else {
		$rows = 0;
	}
	if ($rows) {
		$items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=forums&amp;stext=".$_GET['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['f402'] : $locale['f403'])." ".$locale['522']."</a><br  />\n";
		$result = dbquery(
			"SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_message, tp.post_datestamp, tt.thread_subject,
			tt.thread_sticky, tf.forum_access, tu.user_id, tu.user_name, tu.user_status FROM ".DB_POSTS." tp
			LEFT JOIN ".DB_THREADS." tt ON tp.thread_id = tt.thread_id
			LEFT JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
			LEFT JOIN ".DB_USERS." tu ON tp.post_author=tu.user_id
			WHERE ".groupaccess('forum_access').($_GET['forum_id'] != 0 ? " AND tf.forum_id=".$_GET['forum_id'] : "")."
			AND ".$fieldsvar.($_GET['datelimit'] != 0 ? " AND post_datestamp>=".(time() - $_GET['datelimit']) : "")."
			ORDER BY ".$sortby." ".($_GET['order'] == 1 ? "ASC" : "DESC").($_GET['stype'] != "all"?" LIMIT ".$_GET['rowstart'].",10" : "")
		);		
		while ($data = dbarray($result)) {
			$search_result = "";
			$text_all = search_striphtmlbbcodes($data['post_message']);
			$text_frag = search_textfrag($text_all);
			$subj_c = search_stringscount($data['thread_subject']);
			$text_c = search_stringscount($data['post_message']);;
			// $text_frag = highlight_words($swords, $text_frag);
			$search_result .= ($data['thread_sticky'] == 1 ? "<strong>".$locale['f404']."</strong> " : "")."<a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;highlight=".urlencode($_GET['stext'])."&amp;pid=".$data['post_id']."#post_".$data['post_id']."'>".$data['thread_subject']."</a>"."<br  /><br  />\n";
			// $search_result .= ($data['thread_sticky'] == 1 ? "<strong>".$locale['f404']."</strong> " : "")."<a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;highlight=".urlencode($_GET['stext'])."&amp;pid=".$data['post_id']."#post_".$data['post_id']."'>".highlight_words($swords, $data['thread_subject'])."</a>"."<br  /><br  />\n";
			$search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br  />";
			$search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
			$search_result .= $locale['global_071'].showdate("longdate", $data['post_datestamp'])."</span><br  />\n";
			$search_result .= "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['f406']." ".$locale['f407'].", ";
			$search_result .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['f406']." ".$locale['f408']."</span><br  /><br  />\n";
			search_globalarray($search_result);
		}
	} else {
		$items_count .= THEME_BULLET."&nbsp;0 ".$locale['f403']." ".$locale['522']."<br  />\n";
	}

	$navigation_result = search_navigation($rows);
}
?>
