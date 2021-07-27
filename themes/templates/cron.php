<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: cron.php
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
$locale = fusion_get_locale();
$settings = fusion_get_settings();

/**
 * Cron Job - 6 minutes
 */
if ($settings['cronjob_hour'] < (time() - 360)) {
    dbquery("DELETE FROM ".DB_FLOOD_CONTROL." WHERE flood_timestamp < :crontime", [':crontime' => time() - 360]);

    fusion_apply_hook('cron_job6m');

    dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:time WHERE settings_name=:name", [':time' => time(), ':name' => 'cronjob_hour']);
}

/**
 * Cron Job - 24 hours
 */
if ($settings['cronjob_day'] < (time() - 86400)) {
    fusion_apply_hook('cron_job24h');

    $user_datestamp = [':user_datestamp' => time() - 86400];

    if ($settings['admin_activation'] == 0) {
        dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_datestamp <:user_datestamp", $user_datestamp);
    }

    dbquery("DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_datestamp <:user_datestamp", $user_datestamp);
    $usr_inactive = dbcount("(user_id)", DB_USERS, "user_status='3' AND user_actiontime!='0' AND user_actiontime < NOW()");

    if ($usr_inactive) {
        require_once INCLUDES."sendmail_include.php";
        $result = dbquery("SELECT user_id, user_name, user_email FROM ".DB_USERS."
            WHERE user_status=:status AND user_actiontime!=:action_time_start AND user_actiontime < :action_time_end
            LIMIT 10", [
            ':status'            => 3,
            ':action_time_start' => 0,
            ':action_time_end'   => time()
        ]);

        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_USERS." SET user_status=:status, user_actiontime=:actiontime WHERE user_id=:user_id",
                [':status' => 0, ':actiontime' => 0, ':user_id' => $data['user_id']]
            );

            $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['global_451']);
            $message = str_replace("USER_NAME", $data['user_name'], $locale['global_452']);
            $message = str_replace("LOST_PASSWORD", $settings['siteurl']."lostpassword.php", $message);
            $message = str_replace("[SITEURL]", $settings['siteurl'], $message);
            $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
            sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
        }
    }

    $usr_deactivate = dbcount("(user_id)", DB_USERS, "
        user_actiontime < :action_time_start AND
        user_actiontime!=:action_time_end AND
        user_status=:user_status", [
        ':action_time_start' => time(),
        ':action_time_end'   => 0,
        ':user_status'       => 7
    ]);

    if ($usr_deactivate) {
        $deactivate_param = [
            ':action_time_start' => time(),
            ':action_time_end'   => 0,
            ':status'            => 0,
        ];
        $result = dbquery("SELECT user_id FROM ".DB_USERS."
            WHERE user_actiontime < :action_time_start AND user_actiontime!=:action_time_end AND user_status=:status
            LIMIT 10
        ", $deactivate_param);

        if ($settings['deactivation_action'] == 0) {
            while ($data = dbarray($result)) {
                $deactivate_param[':user_id'] = $data['user_id'];
                $deactivate_param[':status_6'] = 6;
                dbquery("UPDATE ".DB_USERS." SET user_actiontime=:action_time_end, user_status=:status_6 WHERE user_id=:user_id", $deactivate_param);
            }
        } else {
            while ($data = dbarray($result)) {
                dbquery("DELETE FROM ".DB_USERS." WHERE user_id=:user_id", [':user_id' => $data['user_id']]);
                dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_name=:user_id", [':user_id' => $data['user_id']]);
                dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_to=:user_id OR message_from=:user_id_2", [
                    ':user_id' => $data['user_id'], ':user_id_2' => $data['user_id']
                ]);
                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_user=:user_id", [':user_id' => $data['user_id']]);
                dbquery("DELETE FROM ".DB_SUSPENDS." WHERE suspended_user=:user_id", [':user_id' => $data['user_id']]);

                fusion_apply_hook('cron_job24h_users_data', $data);
            }
        }
    }

    dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".time()."' WHERE settings_name='cronjob_day'");
}
