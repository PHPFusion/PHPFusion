<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_custompages_include.php
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
$locale = fusion_get_locale('', LOCALE.LOCALESET."search/custompages.php");
if ($_GET['stype'] == "custompages" || $_GET['stype'] == "all") {
	$order_by = array(
		'0' => ' DESC',
		'1' => ' ASC',
		);
	$sortby = !empty($_POST['order']) ? "ORDER BY page_title".$order_by[$_POST['order']] : "";
	$limit = ($_GET['stype'] != "all" ? " LIMIT ".$_POST['rowstart'].",10" : "");

        if ($_POST['fields'] == 0) {
			$ssubject = search_querylike_safe("page_title", $swords_keys_for_query, $c_swords, $fields_count, 0);
            $fieldsvar = search_fieldsvar($ssubject);

        } elseif ($_POST['fields'] == 1) {
			$smessage = search_querylike_safe("page_content", $swords_keys_for_query, $c_swords, $fields_count, 0);
			$ssnippet = search_querylike_safe("page_title", $swords_keys_for_query, $c_swords, $fields_count, 1);
			$fieldsvar = search_fieldsvar($smessage, $ssnippet);

        } elseif ($_POST['fields'] == 2) {
        	$ssubject = search_querylike_safe("page_title", $swords_keys_for_query, $c_swords, $fields_count, 0);
        	$smessage = search_querylike_safe("page_content", $swords_keys_for_query, $c_swords, $fields_count, 1);
			$ssnippet = search_querylike_safe("page_title", $swords_keys_for_query, $c_swords, $fields_count, 2);
			$fieldsvar = search_fieldsvar($ssubject, $ssnippet, $smessage);

        } else{
			$fieldsvar = "";

        }
    if ($fieldsvar) {
		$rows = dbcount("(page_id)", DB_CUSTOM_PAGES,
                            (multilang_table("CP") ? "page_language='".LANGUAGE."' AND " : "").groupaccess('page_access')." AND ".$fieldsvar."", $swords_for_query);
    } else {
        $rows = 0;
    }
    if ($rows != 0) {
        $items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=custompages&amp;stext=".$_POST['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['c401'] : $locale['c402'])." ".$locale['522']."</a><br />\n";
        $result = dbquery("SELECT *
        	FROM ".DB_CUSTOM_PAGES."
			".(multilang_table("CP") ? "WHERE page_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('page_access')." AND ".$fieldsvar."
			".$sortby.$limit, $swords_for_query);
        while ($data = dbarray($result)) {
            $search_result = "";
            $text_all = stripslashes($data['page_content']);
            ob_start();
            eval ("?>".$text_all."<?php ");
            $text_all = ob_get_contents();
            ob_end_clean();
            $text_all = search_striphtmlbbcodes($text_all);
            $text_frag = search_textfrag($text_all);
            $subj_c = search_stringscount($data['page_title']);
            $text_c = search_stringscount($text_all);
            $search_result .= "<a href='viewpage.php?page_id=".$data['page_id']."'>".$data['page_title']."</a>"."<br /><br />\n";
            $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />\n";
            $search_result .= "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['c403']." ".$locale['c404'].", ";
            $search_result .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['c403']." ".$locale['c405']."</span><br /><br />\n";
            search_globalarray($search_result);
        }
    } else {
        $items_count .= THEME_BULLET."&nbsp;0 ".$locale['c402']." ".$locale['522']."<br />\n";
    }
    $navigation_result = search_navigation($rows);
}
