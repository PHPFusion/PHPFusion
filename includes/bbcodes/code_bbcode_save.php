<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: code_bbcode_save.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require "../../maincore.php";
require INCLUDES."class.httpdownload.php";

if ((isset($_GET['thread_id']) && isnum($_GET['thread_id'])) && (isset($_GET['post_id']) && isnum($_GET['post_id'])) && (isset($_GET['code_id']) && isnum($_GET['code_id']))) {
    $result = dbquery("SELECT fp.*, ff.* FROM ".DB_FORUM_POSTS." AS fp
        INNER JOIN ".DB_FORUMS." AS ff ON ff.forum_id=fp.forum_id
        WHERE fp.thread_id='".$_GET['thread_id']."' AND fp.post_id='".$_GET['post_id']."'");
    if (dbrows($result)) {
        $data = dbarray($result);
        $text = $data['post_message'];
        preg_match_all("#\[code](.*?)\[/code]#si", $text, $matches, PREG_PATTERN_ORDER);
        if (isset($matches[1][$_GET['code_id']])) {
            $text = html_entity_decode(stripslashes($matches[1][$_GET['code_id']]), ENT_QUOTES, fusion_get_locale('charset'));
            $filename = "code_".$_GET['thread_id']."_".$_GET['post_id']."_".$_GET['code_id'].".txt";
            $object = new PHPFusion\httpdownload;
            $object->set_bydata($text);
            $object->use_resume = TRUE;
            $object->set_filename($filename);
            $object->download();
        }
    }
}
