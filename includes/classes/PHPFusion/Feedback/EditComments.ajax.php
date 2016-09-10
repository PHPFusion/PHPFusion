<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: /PHPFusion/Feedback/EditComments.ajax.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../../../../maincore.php';
require_once THEME."theme.php";
require_once THEMES."templates/render_functions.php";
require_once INCLUDES."comments_include.php";
$eresult = dbquery("SELECT tcm.*, tcu.user_name
				FROM ".DB_COMMENTS." tcm
				LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
				WHERE comment_id='".intval($_POST['comment_id'])."' AND comment_item_id='".stripinput($_POST['comment_item_id'])."'
				AND comment_type='".stripinput($_POST['comment_item_type'])."' AND comment_hidden='0'");
if (dbrows($eresult) > 0) {
    $edata = dbarray($eresult);
    echo json_encode($edata);
}