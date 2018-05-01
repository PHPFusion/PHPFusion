<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PHPFusion/Feedback/Comments.ajax.php
| Author: Frederick MC Chan (Chan)
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

if (isset($_GET['action']) && iMEMBER) {
    if ($_GET['action'] == 'edit') {
        // Fetch Data Only
        $eresult = dbquery("SELECT tcm.*, tcu.user_name
                FROM ".DB_COMMENTS." tcm
                LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
                WHERE comment_id='".intval($_POST['comment_id'])."' AND comment_hidden='0'
                ");
        if (dbrows($eresult)) {
            $edata = dbarray($eresult);
            $edata['comment_options'] = \defender::unserialize($_POST['comment_options']);
            if ((iADMIN && checkrights("C"))
                || ($edata['comment_name'] == fusion_get_userdata('user_id') && isset($edata['user_name']))
            ) {
                header('Content-Type: application/json');
                echo json_encode($edata);
            }
            exit;
        }
    } else if ($_GET['action'] == 'delete' && isnum($_POST['comment_id'])) {

        $eresult = dbquery("SELECT tcm.*, tcu.user_name
                FROM ".DB_COMMENTS." tcm
                LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
                WHERE comment_id='".intval($_POST['comment_id'])."' AND comment_hidden='0'");
        if (dbrows($eresult) > 0) {
            $edata = dbarray($eresult);
            //$ajax_respond = \defender::unserialize($_POST['comment_options']);
            if ((iADMIN && checkrights("C"))
                || ($edata['comment_name'] == fusion_get_userdata('user_id') && isset($edata['user_name']))
            ) {
                $child_query = "SELECT comment_id FROM ".DB_COMMENTS." WHERE comment_cat='".intval($_POST['comment_id'])."'";
                $result = dbquery($child_query);
                if (dbrows($result)) {
                    while ($child = dbarray($result)) {
                        dbquery("UPDATE ".DB_COMMENTS." SET comment_cat='".$edata['comment_cat']."' WHERE comment_id='".$child['comment_id']."'");
                    }
                }
                dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id='".$_POST['comment_id']."'".(iADMIN ? "" : "AND comment_name='".fusion_get_userdata('user_id')."'"));
                // Refetch the query
                $ajax_respond = \defender::unserialize($_POST['comment_options']);
                $ajax_respond['comment_custom_script'] = TRUE;
                echo PHPFusion\Feedback\Comments::getInstance($ajax_respond, $ajax_respond['comment_key'])->showComments();
            }
            exit;
        }
    }

} else {
    $ajax_respond = \defender::unserialize($_POST['comment_options']);
    $ajax_respond['comment_custom_script'] = TRUE;
    echo PHPFusion\Feedback\Comments::getInstance($ajax_respond, $ajax_respond['comment_key'])->showComments();
}
