<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_news_include.php
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
if (db_exists(DB_NEWS)) {
$locale = fusion_get_locale('', LOCALE.LOCALESET."search/news.php");
    if ($_GET['stype'] == "news" || $_GET['stype'] == "all") {
        if ($_POST['sort'] == "datestamp") {
            $sortby = "news_datestamp";
        } else {
            if ($_POST['sort'] == "subject") {
                $sortby = "news_subject";
            } else {
                if ($_POST['sort'] == "author") {
                    $sortby = "news_name";
                }
            }
        }
        $ssubject = search_querylike("news_subject");
        $smessage = search_querylike("news_news");
        $sextended = search_querylike("news_extended");
        if ($_POST['fields'] == 0) {
            $fieldsvar = search_fieldsvar($ssubject);
        } else {
            if ($_POST['fields'] == 1) {
                $fieldsvar = search_fieldsvar($smessage, $sextended);
            } else {
                if ($_POST['fields'] == 2) {
                    $fieldsvar = search_fieldsvar($ssubject, $smessage, $sextended);
                } else {
                    $fieldsvar = "";
                }
            }
        }
        if ($fieldsvar) {
            $datestamp = (time() - $_POST['datelimit']);
            $rows = dbcount("(news_id)", DB_NEWS,
                            (multilang_table("NS") ? "news_language='".LANGUAGE."' AND " : "").groupaccess('news_visibility')." AND ".$fieldsvar." AND (news_start='0'||news_start<=NOW()) AND (news_end='0'||news_end>=NOW()) ".($_POST['datelimit'] != 0 ? " AND news_datestamp>=".$datestamp : ""));
        } else {
            $rows = 0;
        }
        if ($rows != 0) {
            $items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=news&amp;stext=".$_POST['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['n401'] : $locale['n402'])." ".$locale['522']."</a><br />\n";
            $datestamp = (time() - $_POST['datelimit']);
            $result = dbquery("SELECT tn.*, tu.user_id, tu.user_name, tu.user_status
            	FROM ".DB_NEWS." tn
				LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
				".(multilang_table("NS") ? "WHERE tn.news_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('news_visibility')." AND (news_start='0'||news_start<=NOW())
				AND (news_end='0'||news_end>=NOW()) AND ".$fieldsvar."
				".($_POST['datelimit'] != 0 ? " AND news_datestamp>=".$datestamp : "")."
				ORDER BY ".$sortby." ".($_POST['order'] == 1 ? "ASC" : "DESC").($_GET['stype'] != "all" ? " LIMIT ".$_POST['rowstart'].",10" : ""));
            while ($data = dbarray($result)) {
                $search_result = "";
                $text_all = $data['news_news']." ".$data['news_extended'];
                $text_all = search_striphtmlbbcodes($text_all);
                $text_frag = search_textfrag($text_all);
                $subj_c = search_stringscount($data['news_subject']);
                $text_c = search_stringscount($data['news_news']);
                $text_c2 = search_stringscount($data['news_extended']);
                $search_result .= "<a href='".INFUSIONS."news/news.php?readmore=".$data['news_id']."'>".$data['news_subject']."</a>"."<br /><br />\n";
                $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                $search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'],
                                                                                             $data['user_status'])."\n";
                $search_result .= $locale['global_071'].showdate("longdate", $data['news_datestamp'])."</span><br />\n";
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
