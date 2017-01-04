<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: footer.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

require_once INCLUDES . "footer_includes.php";
\PHPFusion\Panels::getInstance()->getSitePanel();
define("CONTENT", ob_get_clean()); //ob_start() called in header.php

/**
 * Cron Job (6 minutes)
 */
if (fusion_get_settings("cronjob_hour") < (TIME - 360)) {
    $crontime = (TIME - 360);
    dbquery("DELETE FROM " . DB_FLOOD_CONTROL . " WHERE flood_timestamp < $crontime");
    dbquery("DELETE FROM " . DB_CAPTCHA . " WHERE captcha_datestamp < $crontime");
    dbquery("DELETE FROM " . DB_USERS . " WHERE user_joined='0' AND user_ip='0.0.0.0' and user_level=" . USER_LEVEL_SUPER_ADMIN);
    dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=NOW() WHERE settings_name='cronjob_hour'");
}

/**
 * Cron Job (24 hours)
 */
if (fusion_get_settings("cronjob_day") < (TIME - 86400)) {
    $new_time = TIME;
    $user_datestamp = array(':user_datestamp' => TIME - 86400);
    $notify_datestamp = array(':notify_datestamp' => TIME - 1209600);
    if (db_exists(DB_FORUM_THREAD_NOTIFY)) {
        dbquery("DELETE FROM " . DB_FORUM_THREAD_NOTIFY . " WHERE notify_datestamp <:notify_datestamp", $notify_datestamp);
    }
    dbquery("DELETE FROM " . DB_NEW_USERS . " WHERE user_datestamp <:user_datestamp", $user_datestamp);
    dbquery("DELETE FROM " . DB_EMAIL_VERIFY . " WHERE user_datestamp <:user_datestamp", $user_datestamp);
    $usr_inactive = dbcount("(user_id)", DB_USERS, "user_status='3' AND user_actiontime!='0' AND user_actiontime < NOW()");
    if ($usr_inactive) {
        require_once INCLUDES . "sendmail_include.php";
        $result = dbquery("SELECT user_id, user_name, user_email FROM " . DB_USERS . "
			WHERE user_status=:status AND user_actiontime!=:action_time_start AND user_actiontime < :action_time_end
			LIMIT 10", array(
            ':status' => 3,
            ':action_time_start' => 0,
            ':action_time_end' => TIME
        ));

        while ($data = dbarray($result)) {
            dbquery("UPDATE " . DB_USERS . " SET user_status=:status, user_actiontime=:status WHERE user_id=:user_id",
                array(':status' => 0, ':user_id' => $data['user_id'])
            );

            $subject = $locale['global_451'];
            $message = str_replace("USER_NAME", $data['user_name'], $locale['global_452']);
            $message = str_replace("LOST_PASSWORD", fusion_get_settings("siteurl") . "lostpassword.php", $message);
            sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), $subject,
                $message);
        }

        if ($usr_inactive > 10) {
            $new_time = fusion_get_settings("cronjob_day");
        }
    }

    $usr_deactivate = dbcount("(user_id)", DB_USERS, "user_actiontime < :action_time_start AND user_actiontime!=:action_time_end AND user_status=:user_status",
        array(
            ':action_time_start' => TIME,
            ':action_time_end' => 0,
            ':user_status' => 7
        )
    );

    if ($usr_deactivate) {
        $deactivate_param = array(
            ':action_time_start' => TIME,
            ':action_time_end' => 0,
            ':status' => 0,
        );
        $result = dbquery("SELECT user_id FROM " . DB_USERS . "
			WHERE user_actiontime < :action_time_start AND user_actiontime!=:action_time_end AND user_status=:status
			LIMIT 10", $deactivate_param);

        if (fusion_get_settings("deactivation_action") == 0) {

            while ($data = dbarray($result)) {
                $deactivate_param[':user_id'] = $data['user_id'];
                $deactivate_param[':status_6'] = 6;
                dbquery("UPDATE " . DB_USERS . " SET user_actiontime=:action_time_end, user_status=:status_6 WHERE user_id=:user_id", $deactivate_param);
            }

        } else {

            while ($data = dbarray($result)) {

                $user_mysql = array(':user_id', $data['user_id'], ':user_id_2' => $data['user_id']);

                dbquery("DELETE FROM " . DB_USERS . " WHERE user_id=:user_id", $user_mysql);
                dbquery("DELETE FROM " . DB_COMMENTS . " WHERE comment_name=:user_id", $user_mysql);
                dbquery("DELETE FROM " . DB_MESSAGES . " WHERE message_to=:user_id OR message_from=:user_id_2", $user_mysql);
                dbquery("DELETE FROM " . DB_RATINGS . " WHERE rating_user=:user_id", $user_mysql);
                dbquery("DELETE FROM " . DB_SUSPENDS . " WHERE suspended_user=:user_id", $user_mysql);

                if (db_exists(DB_ARTICLES)) dbquery("DELETE FROM " . DB_ARTICLES . " WHERE article_name=:user_id", $user_mysql);
                if (db_exists(DB_NEWS)) dbquery("DELETE FROM " . DB_NEWS . " WHERE news_name=:user_id", $user_mysql);
                if (db_exists(DB_POLL_VOTES)) dbquery("DELETE FROM " . DB_POLL_VOTES . " WHERE vote_user=:user_id", $user_mysql);
                if (db_exists(DB_FORUM_THREADS)) dbquery("DELETE FROM " . DB_FORUM_THREADS . " WHERE thread_author=:user_id", $user_mysql);
                if (db_exists(DB_FORUM_POSTS)) dbquery("DELETE FROM " . DB_FORUM_POSTS . " WHERE post_author=:user_id", $user_mysql);
                if (db_exists(DB_FORUM_THREAD_NOTIFY)) dbquery("DELETE FROM " . DB_FORUM_THREAD_NOTIFY . " WHERE notify_user=:user_id", $user_mysql);

            }
        }
        if ($usr_deactivate > 10) {
            $new_time = fusion_get_settings("cronjob_day");
        }
    }

    dbquery("UPDATE " . DB_SETTINGS . " SET settings_value='" . $new_time . "' WHERE settings_name='cronjob_day'");
}

if (!isset($fusion_jquery_tags)) {
    $fusion_jquery_tags = '';
}

// Load layout
if (defined('ADMIN_PANEL')) {
    require_once __DIR__.'/admin_layout.php';
} else {
    require_once __DIR__.'/layout.php';
}

// Catch the output
$output = ob_get_contents(); //ob_start() called in maincore
if (ob_get_length() !== FALSE) {
    ob_end_clean();
}

// Do the final output manipulation
$output = handle_output($output);

// Search in output and replace normal links with SEF links
//if (!isset($_GET['aid']) && fusion_get_settings("site_seo") == 1) {
if (!isset($_GET['aid'])) {
    //if (isset($router) && $router->getFilePath() !== "error.php") {
    //  $output = \PHPFusion\Rewrite\Permalinks::getInstance()->getOutput($output);
    //}
    if (fusion_get_settings('site_seo')) {
        \PHPFusion\Rewrite\Permalinks::getPermalinkInstance()->handle_url_routing($output);
        $output = \PHPFusion\Rewrite\Permalinks::getPermalinkInstance()->getOutput($output);
    }
}

if (isset($permalink)) {
    unset($permalink);
}

// Output the final complete page content
echo $output;

remove_notice();

if ((ob_get_length() > 0)) { // length is a number
    ob_end_flush();
}