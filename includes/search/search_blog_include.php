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
include LOCALE.LOCALESET."search/blog.php";
if ($_REQUEST['stype'] == "blog" || $_REQUEST['stype'] == "all") {
    if ($_REQUEST['sort'] == "datestamp") {
        $sortby = "blog_datestamp";
    } else if ($_REQUEST['sort'] == "subject") {
        $sortby = "blog_subject";
    } else if ($_REQUEST['sort'] == "author") {
        $sortby = "blog_name";
    }
    $ssubject = search_querylike("blog_subject");
    $smessage = search_querylike("blog_blog");
    $sextended = search_querylike("blog_extended");
    if ($_REQUEST['fields'] == 0) {
        $fieldsvar = search_fieldsvar($ssubject);
    } else if ($_REQUEST['fields'] == 1) {
        $fieldsvar = search_fieldsvar($smessage, $sextended);
    } else if ($_REQUEST['fields'] == 2) {
        $fieldsvar = search_fieldsvar($ssubject, $smessage, $sextended);
    } else {
        $fieldsvar = "";
    }
    if ($fieldsvar) {
        $rows = dbcount("(blog_id)", DB_BLOG, groupaccess('blog_visibility')." AND ".$fieldsvar." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") ".($_REQUEST['datelimit'] != 0 ? " AND blog_datestamp>=".(time() - $_REQUEST['datelimit']) : ""));
    } else {
        $rows = 0;
    }
    if ($rows != 0) {
        if (!$settings['site_seo']) {
            $items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=blog&amp;stext=".$_REQUEST['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['b401'] : $locale['b402'])." ".$locale['522']."</a><br />\n";
        } else {
            $items_count .= THEME_BULLET."&nbsp;".$rows." ".($rows == 1 ? $locale['b401'] : $locale['b402'])." ".$locale['522']."<br />\n";
        }
        $result = dbquery("SELECT tn.*, tu.user_id, tu.user_name, tu.user_status FROM ".DB_BLOG." tn
			LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
			WHERE ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().")
			AND (blog_end='0'||blog_end>=".time().") AND ".$fieldsvar."
			".($_REQUEST['datelimit'] != 0 ? " AND blog_datestamp>=".(time() - $_REQUEST['datelimit']) : "")."
			ORDER BY ".$sortby." ".($_REQUEST['order'] == 1 ? "ASC" : "DESC").($_REQUEST['stype'] != "all" ? " LIMIT ".$_REQUEST['rowstart'].",10" : ""));
        while ($data = dbarray($result)) {
            $search_result = "";
            $text_all = $data['blog_blog']." ".$data['blog_extended'];
            $text_all = search_striphtmlbbcodes($text_all);
            $text_frag = search_textfrag($text_all);
            $subj_c = search_stringscount($data['blog_subject']);
            $text_c = search_stringscount($data['blog_blog']);
            $text_c2 = search_stringscount($data['blog_extended']);
            $search_result .= "<a href='".BASEDIR."blog.php?readmore=".$data['blog_id']."'>".$data['blog_subject']."</a>"."<br /><br />\n";
            $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
            $search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
            $search_result .= $locale['global_071'].showdate("longdate", $data['blog_datestamp'])."</span><br />\n";
            $search_result .= "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['b403']." ".$locale['b404'].", ";
            $search_result .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['b403']." ".$locale['b405'].", ";
            $search_result .= $text_c2." ".($text_c2 == 1 ? $locale['520'] : $locale['521'])." ".$locale['b403']." ".$locale['b406']."</span><br /><br />\n";
            search_globalarray($search_result);
        }
    } else {
        $items_count .= THEME_BULLET."&nbsp;0 ".$locale['b402']." ".$locale['522']."<br />\n";
    }
    $navigation_result = search_navigation($rows);
}
