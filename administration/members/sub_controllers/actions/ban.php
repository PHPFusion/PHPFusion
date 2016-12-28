<?php
require_once dirname(__FILE__).'/../../../../maincore.php';
if (\defender::safe()) {
    if (isset($_POST['ban_action']) && isset($_POST['user_id'])) {
        require_once INCLUDES."sendmail_include.php";
        $locale = fusion_get_locale(LOCALE.LOCALESET."admin/members_email.php");
        $settings = fusion_get_settings();
        if ($user = fusion_get_user($_POST['user_id'])) {
            if (!empty($user)) {

                $user_id = $user['user_id'];
                $user_status = $user['user_status'];
                $ban_reason = form_sanitizer($_POST['ban_reason'], '', 'ban_reason');

                if ($_POST['ban_action'] == 1) {
                    if ($user_status == 0) {
                        // to ban
                        $result = dbquery("UPDATE ".DB_USERS." SET user_status=:user_status, user_actiontime=:action_time WHERE user_id=:user_id", array(
                            ':user_status' => 1,
                            ':action_time' => 0,
                            ':user_id' => $user_id
                        ));
                        suspend_log($user_id, 1, $ban_reason);
                        $message = str_replace("[USER_NAME]", $user['user_name'], $locale['email_ban_message']);
                        $message = str_replace("[REASON]", $ban_reason, $message);
                        $message = str_replace("[SITENAME]", $settings['sitename'], $message);
                        $message = str_replace("[ADMIN_USERNAME]", $userdata['user_name'], $message);
                        $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
                        $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['email_ban_subject']);
                        sendemail($user['user_name'], $user['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);

                        $_response = array(
                            'code' => 'OK',
                            'message' => $user['user_name'].' is now banned',
                        );

                    } else {

                        // user has already been banned
                        $_response = array(
                            'code' => 'FAIL',
                            'message' => $user['user_name'].' has already been banned',
                        );
                    }

                } elseif ($_POST['ban_action'] == 0 && $user_status > 0 && $user_id) {
                    $result = dbquery("UPDATE ".DB_USERS." SET user_status=:user_status, user_actiontime=:action_time WHERE user_id=:user_id", array(
                        ':user_status' => 0,
                        ':action_time' => 0,
                        ':user_id' => $user_id,
                    ));
                    unsuspend_log($user_id, 1, $ban_reason);
                    $_response = array(
                        'code' => 'OK',
                        'message' => $user['user_name'].' is now reinstated as a member',
                    );
                }
            } else {
                // no user found
                $_response = array(
                    'code' => 'FAIL',
                    'message' => 'User ID is invalid',
                );
            }
        } else {
            // unknown response
            $_response = array(
                'code' => 'FAIL',
                'message' => 'Unknown Error',
            );
        }
    } else {
        // unknown API
        $_response = array(
            'code' => 'FAIL',
            'message' => 'Illegal Parameter',
        );
    }
} else {
    // failed token
    $_response = array(
        'code' => 'FAIL',
        'message' => 'Failed Post Authentication',
    );
}
echo json_encode($_response);