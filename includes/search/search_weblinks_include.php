<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_weblinks_include.php
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

include LOCALE.LOCALESET."search/weblinks.php";

if ($_GET['stype']  ==  "weblinks" || $_GET['stype'] == "all") {
	if ($_GET['sort'] == "datestamp") {
		$sortby = "weblink_datestamp";
	} else if ($_GET['sort'] == "subject") {
		$sortby = "weblink_name";
	} else {
		$sortby = "weblink_datestamp";
	}
	$ssubject = search_querylike("weblink_name");
	$smessage = search_querylike("weblink_description");
	$surllink = search_querylike("weblink_url");
	if ($_GET['fields'] == 0) {
		$fieldsvar = search_fieldsvar($ssubject, $surllink);
	} else if ($_GET['fields'] == 1) {
		$fieldsvar = search_fieldsvar($smessage, $surllink);
	} else if ($_GET['fields'] == 2) {
		$fieldsvar = search_fieldsvar($ssubject, $smessage, $surllink);
	} else {
		$fieldsvar = "";
	}
	if ($fieldsvar) {
		$result = dbquery(
			"SELECT tw.*,twc.* FROM ".DB_WEBLINKS." tw
			INNER JOIN ".DB_WEBLINK_CATS." twc ON tw.weblink_cat=twc.weblink_cat_id
			WHERE ".groupaccess('weblink_cat_access')." AND ".$fieldsvar."
			".($_GET['datelimit'] != 0 ?" AND weblink_datestamp>=".(time()-$_GET['datelimit']):"")
		);
		$rows = dbrows($result);
	} else {
		$rows = 0;
	}
	if ($rows != 0) {
		$items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=weblinks&amp;stext=".$_GET['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['w401'] : $locale['w402'])." ".$locale['522']."</a><br />\n";
		$result = dbquery(
			"SELECT tw.*,twc.* FROM ".DB_WEBLINKS." tw
			INNER JOIN ".DB_WEBLINK_CATS." twc ON tw.weblink_cat=twc.weblink_cat_id
			WHERE ".groupaccess('weblink_cat_access')." AND ".$fieldsvar."
			".($_GET['datelimit'] != 0 ? " AND weblink_datestamp>=".(time() - $_GET['datelimit']) : "")."
			ORDER BY ".$sortby." ".($_GET['order'] == 1 ? "ASC" : "DESC").($_GET['stype']!="all"?" LIMIT ".$_GET['rowstart'].",10" : "")
		);
		while ($data = dbarray($result)) {
			$search_result = "";
			if ($data['weblink_datestamp']+604800 > time()+($settings['timeoffset']*3600)) {
				$new = " <span class='small'>".$locale['w403']."</span>";
			} else {
				$new = "";
			}
			$text_all = $data['weblink_description'];
			$text_all = search_striphtmlbbcodes($text_all);
			$text_frag = search_textfrag($text_all);
			// $text_frag = highlight_words($swords, $text_frag);
			$subj_c = search_stringscount($data['weblink_name']) + search_stringscount($data['weblink_url']);
			$text_c = search_stringscount($data['weblink_description']);

			$search_result .= "<a href='weblinks.php?cat_id=".$data['weblink_cat']."&amp;weblink_id=".$data['weblink_id']."' target='_blank'>".$data['weblink_name']."</a>".$new."<br /><br />\n";

			// $search_result .= "<a href='weblinks.php?cat_id=".$data['weblink_cat']."&amp;weblink_id=".$data['weblink_id']."' target='_blank'>".highlight_words($swords, $data['weblink_name'])."</a>".$new."<br /><br />\n";
			if ($text_frag != "") { $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />"; }
			$search_result .= "<span class='small'><font class='alt'>".$locale['w404']."</font> ".showdate("%d.%m.%y", $data['weblink_datestamp'])." | <span class='alt'>".$locale['w405']."</span> ".$data['weblink_count']."</span><br /><br />\n";
			search_globalarray($search_result);
		}
	} else {
		$items_count .= THEME_BULLET."&nbsp;0 ".$locale['w402']." ".$locale['522']."<br />\n";
	}
	$navigation_result = search_navigation($rows);
}
?>