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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
if (db_exists(DB_DOWNLOADS)) {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."search/downloads.php");
    if ($_GET['stype'] == "downloads" || $_GET['stype'] == "all") {
	$sort_by = array(
		'datestamp' => "download_datestamp",
		'subject' => "download_title",
		'author' => "download_user",
		);
	$order_by = array(
		'0' => ' DESC',
		'1' => ' ASC',
		);
	$sortby = !empty($_POST['sort']) ? "ORDER BY ".$sort_by[$_POST['sort']].$order_by[$_POST['order']] : "";
	$limit = ($_GET['stype'] != "all" ? " LIMIT ".$_POST['rowstart'].",10" : "");

        if ($_POST['fields'] == 0) {
			$ssubject = search_querylike_safe("download_title", $swords_keys_for_query, $c_swords, $fields_count, 0);
            $fieldsvar = search_fieldsvar($ssubject);

        } elseif ($_POST['fields'] == 1) {
			$smessage = search_querylike_safe("download_description", $swords_keys_for_query, $c_swords, $fields_count, 0);
			$ssnippet = search_querylike_safe("download_title", $swords_keys_for_query, $c_swords, $fields_count, 1);
			$fieldsvar = search_fieldsvar($smessage, $ssnippet);

        } elseif ($_POST['fields'] == 2) {
        	$ssubject = search_querylike_safe("download_title", $swords_keys_for_query, $c_swords, $fields_count, 0);
        	$smessage = search_querylike_safe("download_description", $swords_keys_for_query, $c_swords, $fields_count, 1);
			$ssnippet = search_querylike_safe("download_title", $swords_keys_for_query, $c_swords, $fields_count, 2);
			$fieldsvar = search_fieldsvar($ssubject, $ssnippet, $smessage);

        } else{
			$fieldsvar = "";

        }
        if ($fieldsvar) {
            $datestamp = (time() - $_POST['datelimit']);
            $result = dbquery("SELECT td.*,tdc.*
            	FROM ".DB_DOWNLOADS." td
				INNER JOIN ".DB_DOWNLOAD_CATS." tdc ON td.download_cat=tdc.download_cat_id
				".(multilang_table("DL") ? "WHERE tdc.download_cat_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('download_visibility')." AND ".$fieldsvar."
				".($_POST['datelimit'] != 0 ? " AND download_datestamp>=".$datestamp : ""), $swords_for_query);
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }
        if ($rows != 0) {
            $items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=downloads&amp;stext=".$_POST['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['d401'] : $locale['d402'])." ".$locale['522']."</a><br />\n";
            $datestamp = (time() - $_POST['datelimit']);
            $result = dbquery("SELECT td.*,tdc.*
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
            	FROM ".DB_DOWNLOADS." td
				INNER JOIN ".DB_DOWNLOAD_CATS." tdc ON td.download_cat=tdc.download_cat_id
				LEFT JOIN ".DB_USERS." tu ON td.download_user=tu.user_id
				".(multilang_table("DL") ? "WHERE tdc.download_cat_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('download_cat_access')." AND ".$fieldsvar."
				".($_POST['datelimit'] != 0 ? " AND download_datestamp>=".$datestamp : "")."
				".$sortby.$limit, $swords_for_query);
            while ($data = dbarray($result)) {
                $search_result = "";
                if ($data['download_datestamp'] + 604800 > time() + ($settings['timeoffset'] * 3600)) {
                    $new = " <span class='small'>".$locale['d403']."</span>";
                } else {
                    $new = "";
                }
                $text_all = $data['download_description'];
                $text_all = search_striphtmlbbcodes($text_all);
                $text_frag = search_textfrag($text_all);
                $subj_c = search_stringscount($data['download_title']);
                $text_c = search_stringscount($data['download_description']);
                $search_result .= "<a href='".DOWNLOADS."downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']."' target='_blank'>".$data['download_title']."</a> - ".$data['download_filesize']." ".$new."<br /><br />\n";
                if ($text_frag != "") {
                    $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                }
                $search_result .= "<span class='small'><span class='alt'>".$locale['d404']."</span> ".$data['download_license']." |\n";
                $search_result .= "<span class='alt'>".$locale['d405']."</span> ".$data['download_os']." |\n";
                $search_result .= "<span class='alt'>".$locale['d406']."</span> ".$data['download_version']."<br />\n";
                $search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'],
                                                                                             $data['user_status'])."\n";
                $search_result .= "<span class='alt'>".$locale['d407']."</span> ".showdate("%d.%m.%y", $data['download_datestamp'])." |\n";
                $search_result .= "<span class='alt'>".$locale['d408']."</span> ".$data['download_count']."</span><br /><br />\n";
                search_globalarray($search_result);
            }
        } else {
            $items_count .= THEME_BULLET."&nbsp;0 ".$locale['d402']." ".$locale['522']."<br />\n";
        }
        $navigation_result = search_navigation($rows);
    }
}
