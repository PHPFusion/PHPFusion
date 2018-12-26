<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_photos_include.php
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

include LOCALE.LOCALESET."search/photos.php";

if (!defined("SAFEMODE")) {
    define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
}

if ($_REQUEST['stype'] == "photos" || $_REQUEST['stype'] == "all") {
    if ($_REQUEST['sort'] == "datestamp") {
        $sortby = "photo_datestamp";
    } else if ($_REQUEST['sort'] == "subject") {
        $sortby = "photo_title";
    } else if ($_REQUEST['sort'] == "author") {
        $sortby = "photo_user";
    }
    $swords = '';
    $ssubject1 = search_querylike("photo_title");
    $smessage1 = search_querylike("photo_description");
    $ssubject2 = search_querylike("album_title");
    $smessage2 = search_querylike("album_description");
    if ($_REQUEST['fields'] == 0) {
        $fieldsvar = search_fieldsvar($ssubject1, $ssubject2);
    } else if ($_REQUEST['fields'] == 1) {
        $fieldsvar = search_fieldsvar($smessage1, $smessage2);
    } else if ($_REQUEST['fields'] == 2) {
        $fieldsvar = search_fieldsvar($ssubject1, $ssubject2, $smessage1, $smessage2);
    } else {
        $fieldsvar = "";
    }
    if ($fieldsvar) {
        $datestamp = (time() - $_REQUEST['datelimit']);
        $result = dbquery("SELECT tp.*,ta.* FROM ".DB_PHOTOS." tp
			INNER JOIN ".DB_PHOTO_ALBUMS." ta ON tp.album_id=ta.album_id
			WHERE ".groupaccess('album_access')." AND ".$fieldsvar."
			".($_REQUEST['datelimit'] != 0 ? " AND (photo_datestamp>=".$datestamp." OR album_datestamp>=".$datestamp.")" : ""));
        $rows = dbrows($result);
    } else {
        $rows = 0;
    }
    if ($rows != 0) {
        if (!$settings['site_seo']) {
            $items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=photos&amp;stext=".$_REQUEST['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['p401'] : $locale['p402'])." ".$locale['522']."</a><br />\n";
        } else {
            $items_count .= THEME_BULLET."&nbsp;".$rows." ".($rows == 1 ? $locale['p401'] : $locale['p402'])." ".$locale['522']."<br />\n";
        }
        $datestamp = (time() - $_REQUEST['datelimit']);
        $result = dbquery("SELECT tp.*,ta.* FROM ".DB_PHOTOS." tp
			INNER JOIN ".DB_PHOTO_ALBUMS." ta ON tp.album_id=ta.album_id
			WHERE ".groupaccess('album_access')." AND ".$fieldsvar."
			".($_REQUEST['datelimit'] != 0 ? " AND (photo_datestamp>=".$datestamp." OR album_datestamp>=".$datestamp.")" : "")."
			ORDER BY ".$sortby." ".($_REQUEST['order'] == 1 ? "ASC" : "DESC").($_REQUEST['stype'] != "all" ? " LIMIT ".$_REQUEST['rowstart'].",20" : ""));
        while ($data = dbarray($result)) {
            $search_result = "";
            if ($data['photo_datestamp'] + 604800 > time() + ($settings['timeoffset'] * 3600)) {
                $new = " <span class='small'>".$locale['p403']."</span>";
            } else {
                $new = "";
            }
            $text_all = $data['photo_description'];
            $text_all = search_striphtmlbbcodes($text_all);
            $text_frag = search_textfrag($text_all);
            $subj_c = search_stringscount($data['photo_title']) + search_stringscount($data['album_title']);
            $text_c = search_stringscount($data['photo_description']) + search_stringscount($data['album_description']);
            $search_result .= "<table width='100%'>";
            $search_result .= "<tr><td width='".$settings['thumb_w']."'>";
            $photodir = PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : "");
            if ($data['photo_thumb1'] != "" && file_exists($photodir.$data['photo_thumb1'])) {
                $search_result .= "<a href='".BASEDIR."photogallery.php?photo_id=".$data['photo_id']."'><img src='".$photodir.$data['photo_thumb1']."' style='border:none' alt='".$data['photo_title']."' /></a>";
            } else if ($data['photo_thumb2'] != "" && file_exists($photodir.$data['photo_thumb2'])) {
                $search_result .= "<a href='".BASEDIR."photogallery.php?photo_id=".$data['photo_id']."'><img src='".$photodir.$data['photo_thumb2']."' style='border:none' alt='".$data['photo_title']."' /></a>";
            } else {
                $search_result .= "<a href='".BASEDIR."photogallery.php?photo_id=".$data['photo_id']."'><img src='".get_image("imagenotfound")."' style='border:none' alt='".$data['photo_title']."' /></a>";
            }
            $search_result .= "</td><td>";
            $search_result .= "<a href='".BASEDIR."photogallery.php?photo_id=".$data['photo_id']."'>".highlight_words($swords, $data['photo_title'])."</a>".$new." (".$locale['p404']." <a href='photogallery.php?album_id=".$data['album_id']."'>".highlight_words($swords, $data['album_title'])."</a>)"."<br /><br />\n";
            if ($text_frag != "")
                $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />\n";
            $search_result .= "<span class='small'><span class='alt'>".$locale['p405']."</span> ".showdate("%d.%m.%y", $data['photo_datestamp'])." | <span class='alt'>".$locale['p406']."</span> ".$data['photo_views']."</span>";
            $search_result .= "</td></tr></table><br /><br />\n";
            search_globalarray($search_result);
        }
    } else {
        $items_count .= THEME_BULLET."&nbsp;0 ".$locale['p402']." ".$locale['522']."<br />\n";
    }
    $navigation_result = search_navigation($rows);
}
