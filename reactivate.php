<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: reactivate.php
| Author: Paul Beuk (muscapaul)
| Co Author: Hans Krisitan Flaatten (Starefossen)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__).'/maincore.php';
require_once THEMES."templates/header.php";
require_once INCLUDES."suspend_include.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET."reactivate.php");

if (iMEMBER) {
   redirect(BASEDIR."index.php");
}

if (isset($_GET['error']) && isnum($_GET['error'])) {
	$text = "";
    switch ($_GET['error']) {
        case 1:
        	$text = str_replace('[SITEEMAIL]', "<a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a>", $locale['501']);
            break;
        case 2:
        	$text = str_replace('[SITEEMAIL]', "<a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a>", $locale['502']);
            break;
        case 3:
        	$text = str_replace(['[LINK]', '[/LINK]', '[SITEEMAIL]'],
            	["<a href='".fusion_get_settings('siteurl')."login.php'>", "</a>", "<a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a>"],
            	$locale['503']
            );
            break;
        default:
        	redirect(BASEDIR."index.php");
    }
    opentable($locale['500']);
    echo "<div class='alert alert-danger text-center'>".$text."</div>\n";
    closetable();
}

if (isset($_GET['user_id']) && isnum($_GET['user_id']) && isset($_GET['code']) && preg_check("/^[0-9a-z]{32}$/", $_GET['code'])) {
    $result = dbquery("SELECT user_name, user_email, user_actiontime, user_password
                      FROM ".DB_USERS."
                      WHERE user_id='".$_GET['user_id']."' AND user_actiontime>'0' AND user_status='7'"
                      );
    if (dbrows($result)) {
        $data = dbarray($result);
        $code = md5($data['user_actiontime'].$data['user_password']);
        if ($_GET['code'] == $code) {
            if ($data['user_actiontime'] > TIME) {
                dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0', user_lastvisit='".TIME."' WHERE user_id='".$_GET['user_id']."'");
                unsuspend_log($_GET['user_id'], 7, $locale['506'], TRUE);
                $message = str_replace(
                    ["[USER_NAME]", '[SITENAME]', '[SITEUSERNAME]'],
                    [$data['user_name'], fusion_get_settings('sitename'), fusion_get_settings('siteusername')],
                    $locale['505']
                );
                require_once INCLUDES."sendmail_include.php";
                sendemail($data['user_name'], $data['user_email'], fusion_get_settings('siteusername'), fusion_get_settings('siteemail'), str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['504']), $message);
                redirect(BASEDIR."login.php");
            } else {
                redirect(FUSION_SELF."?error=1");
            }
        } else {
            redirect(FUSION_SELF."?error=2&user_id=".$data['user_id']."&code=".$_GET['code']);
        }
    } else {
        redirect(FUSION_SELF."?error=3");
    }
} else {
    redirect(BASEDIR."index.php");
}

require_once THEMES."templates/footer.php";
