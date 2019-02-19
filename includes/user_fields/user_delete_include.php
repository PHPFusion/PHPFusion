<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_delete_include.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

// Display user field input
if ($profile_method == "input") {

    $user_fields = '';
    if (defined('ADMIN_PANEL')) {
        $user_fields = "<div class='well m-t-5 text-center'>".$locale['uf_delete']."</div>";
    }

    // Display in profile
} else if ($profile_method == "display") {
    if (!defined('ADMIN_PANEL')) {

        if (iMEMBER && isset($_POST['delete_me']) && fusion_get_userdata('user_id') == $_GET['lookup'] && !iSUPERADMIN) {
            $data = fusion_get_userdata('user_id');

            if (defined('ARTICLES_EXIST')) {
                dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_name='".$data."'");
            }
            dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_name='".$data."'");
            dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_to='".$data."' OR message_from='".$data."'");
            if (defined('NEWS_EXIST')) {
                dbquery("DELETE FROM ".DB_NEWS." WHERE news_name='".$data."'");
            }
            if (db_exists(DB_POLL_VOTES)) {
                dbquery("DELETE FROM ".DB_POLL_VOTES." WHERE vote_user='".$data."'");
            }
            dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_user='".$data."'");
            dbquery("DELETE FROM ".DB_SUSPENDS." WHERE suspended_user='".$data."'");
            if (db_exists(DB_FORUM_THREADS)) {
                dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author='".$data."'");
            }
            if (db_exists(DB_FORUM_POSTS)) {
                dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_author='".$data."'");
            }
            if (db_exists(DB_FORUM_THREAD_NOTIFY)) {
                dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user='".$data."'");
            }
            dbquery("DELETE FROM ".DB_USERS." WHERE user_id='".$data."'");

            addNotice('success', $locale['uf_delete_exit']);
            redirect('index.php');
        }

        if (iMEMBER && fusion_get_userdata('user_id') == $_GET['lookup'] && !iSUPERADMIN) {
            $action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
            $ab = openform('delete_me', 'post', $action_url);
            $ab .= form_button('delete_me', $locale['uf_delete_del'], "delete_me");
            $ab .= closeform();
            $user_fields = [
                'title' => $locale['uf_delete'],
                'value' => $ab
            ];
        }
    }
}
