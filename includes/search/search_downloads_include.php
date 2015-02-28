<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_downloads_include.php
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

include LOCALE.LOCALESET."search/downloads.php";

if ($_GET['stype'] == "downloads" || $_GET['stype'] == "all") {
	if ($_GET['sort'] == "datestamp") {
		$sortby = "download_datestamp";
	} else if ($_GET['sort'] == "subject") {
		$sortby = "download_title";
	} else {
		$sortby = "download_datestamp";
	}
	$ssubject = search_querylike("download_title");
	$smessage = search_querylike("download_description");
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
			"SELECT td.*,tdc.* FROM ".DB_DOWNLOADS." td
			INNER JOIN ".DB_DOWNLOAD_CATS." tdc ON td.download_cat=tdc.download_cat_id
			WHERE ".groupaccess('download_cat_access')." AND ".$fieldsvar."
			".($_GET['datelimit'] != 0 ? " AND download_datestamp>=".(time() - $_GET['datelimit']):"")
		);
		$rows = dbrows($result);
	} else {
		$rows = 0;
	}
	if ($rows != 0) {
		$items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=downloads&amp;stext=".$_GET['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['d401'] : $locale['d402'])." ".$locale['522']."</a><br />\n";
		$result = dbquery(
			"SELECT td.*,tdc.* FROM ".DB_DOWNLOADS." td
			INNER JOIN ".DB_DOWNLOAD_CATS." tdc ON td.download_cat=tdc.download_cat_id
			WHERE ".groupaccess('download_cat_access')." AND ".$fieldsvar."
			".($_GET['datelimit'] != 0 ? " AND download_datestamp>=".(time() - $_GET['datelimit']):"")."
			ORDER BY ".$sortby." ".($_GET['order'] == 1 ? "ASC" : "DESC").($_GET['stype'] != "all" ? " LIMIT ".$_GET['rowstart'].",10" : "")
		);
		while ($data = dbarray($result)) {
			$search_result = "";
			if ($data['download_datestamp']+604800 > time()+($settings['timeoffset']*3600)) {
				$new = " <span class='small'>".$locale['d403']."</span>";
			} else {
				$new = "";
			}
			$text_all = $data['download_description'];
			$text_all = search_striphtmlbbcodes($text_all);
			$text_frag = search_textfrag($text_all);
			$subj_c = search_stringscount($data['download_title']);
			$text_c = search_stringscount($data['download_description']);
			// $text_frag = highlight_words($swords, $text_frag);
			$search_result .= "<a href='downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']."' target='_blank'>".$data['download_title']."</a> - ".$data['download_filesize']." ".$new."<br /><br />\n";
			// $search_result .= "<a href='downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']."' target='_blank'>".highlight_words($swords, $data['download_title'])."</a> - ".$data['download_filesize']." ".$new."<br /><br />\n";
			if ($text_frag != "") $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
			$search_result .= "<span class='small'><span class='alt'>".$locale['d404']."</span> ".$data['download_license']." |\n";
			$search_result .= "<span class='alt'>".$locale['d405']."</span> ".$data['download_os']." |\n";
			$search_result .= "<span class='alt'>".$locale['d406']."</span> ".$data['download_version']."<br />\n";
			$search_result .= "<span class='alt'>".$locale['d407']."</span> ".showdate("%d.%m.%y", $data['download_datestamp'])." |\n";
			$search_result .= "<span class='alt'>".$locale['d408']."</span> ".$data['download_count']."</span><br /><br />\n";
			search_globalarray($search_result);
		}
	} else {
		$items_count .= THEME_BULLET."&nbsp;0 ".$locale['d402']." ".$locale['522']."<br />\n";
	}
	$navigation_result = search_navigation($rows);
}
?>