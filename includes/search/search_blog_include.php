<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_blog_include.php
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
if (db_exists(DB_BLOG)) {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."search/blog.php");
    if ($_GET['stype'] == "blog" || $_GET['stype'] == "all") {

	$sort_by = array(
		'datestamp' => "blog_datestamp",
		'subject' => "blog_subject",
		'author' => "blog_name",
		);
	$order_by = array(
		'0' => ' DESC',
		'1' => ' ASC',
		);
	$sortby = !empty($_POST['sort']) ? "ORDER BY ".$sort_by[$_POST['sort']].$order_by[$_POST['order']] : "";

        if ($_POST['fields'] == 0) {
			$ssubject = search_querylike_safe("blog_subject", $swords_keys_for_query, $c_swords, $fields_count, 0);
            $fieldsvar = search_fieldsvar($ssubject);

        } elseif ($_POST['fields'] == 1) {
			$smessage = search_querylike_safe("blog_blog", $swords_keys_for_query, $c_swords, $fields_count, 0);
			$sextended = search_querylike_safe("blog_extended", $swords_keys_for_query, $c_swords, $fields_count, 1);
			$fieldsvar = search_fieldsvar($smessage, $sextended);

        } elseif ($_POST['fields'] == 2) {
        	$ssubject = search_querylike_safe("blog_subject", $swords_keys_for_query, $c_swords, $fields_count, 0);
        	$smessage = search_querylike_safe("blog_blog", $swords_keys_for_query, $c_swords, $fields_count, 1);
			$sextended = search_querylike_safe("blog_extended", $swords_keys_for_query, $c_swords, $fields_count, 2);
			$fieldsvar = search_fieldsvar($ssubject, $sextended, $smessage);

        } else{
			$fieldsvar = "";

        }

        if ($fieldsvar) {
            $datestamp = (time() - $_POST['datelimit']);
            $rows = dbcount("(blog_id)", DB_BLOG,
                            (multilang_table("BL") ? "blog_language='".LANGUAGE."' AND " : "").groupaccess('blog_visibility')." AND ".$fieldsvar." AND (blog_start='0'||blog_start<=NOW()) AND (blog_end='0'||blog_end>=NOW()) ".($_POST['datelimit'] != 0 ? " AND blog_datestamp>=".$datestamp : ""), $swords_for_query);
        } else {
            $rows = 0;
        }
        if ($rows != 0) {
            $items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=blog&amp;stext=".$_POST['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['n401'] : $locale['n402'])." ".$locale['522']."</a><br />\n";
            $datestamp = (time() - $_POST['datelimit']);
            $result = dbquery("SELECT tn.blog_id, tn.blog_name, tn.blog_visibility, tn.blog_start, tn.blog_end, tn.blog_datestamp, tn.blog_blog, tn.blog_extended, tn.blog_subject,
            	tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
            	FROM ".DB_BLOG." tn
				LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
				".(multilang_table("BL") ? "WHERE tn.blog_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=NOW())
				AND (blog_end='0'||blog_end>=NOW()) AND ".$fieldsvar."
				".($_POST['datelimit'] != 0 ? " AND blog_datestamp>=".$datestamp : "")."
				".$sortby.($_GET['stype'] != "all" ? " LIMIT ".$_POST['rowstart'].",10" : ""), $swords_for_query);
            while ($data = dbarray($result)) {
                $search_result = "";
                $text_all = $data['blog_blog']." ".$data['blog_extended'];
                $text_all = search_striphtmlbbcodes($text_all);
                $text_frag = search_textfrag($text_all);
                $subj_c = search_stringscount($data['blog_subject']);
                $text_c = search_stringscount($data['blog_blog']);
                $text_c2 = search_stringscount($data['blog_extended']);
                $search_result .= "<a href='".INFUSIONS."blog/blog.php?readmore=".$data['blog_id']."'>".$data['blog_subject']."</a>"."<br /><br />\n";
                $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                $search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'],
                                                                                             $data['user_status'])."\n";
                $search_result .= $locale['global_071'].showdate("longdate", $data['blog_datestamp'])."</span><br />\n";
                $search_result .= "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n404'].", ";
                $search_result .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n405'].", ";
                $search_result .= $text_c2." ".($text_c2 == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n406']."</span><br /><br />\n";
                search_globalarray($search_result);
            }
        } else {
            $items_count .= THEME_BULLET."&nbsp;0 ".$locale['n402']." ".$locale['522']."<br />\n";
        }
        $navigation_result = search_navigation($rows);
    }
}
