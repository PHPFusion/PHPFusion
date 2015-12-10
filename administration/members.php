<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members.php
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
require_once "../maincore.php";
pageAccess('M');
require_once THEMES."templates/admin_header.php";
require_once INCLUDES."suspend_include.php";
include LOCALE.LOCALESET."admin/members.php";
include LOCALE.LOCALESET."user_fields.php";
include THEMES."templates/global/profile.php";

$settings = fusion_get_settings();

$rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0);
$sortby   = (isset($_GET['sortby']) ? stripinput($_GET['sortby']) : "all");
$status   = (isset($_GET['status']) && isnum($_GET['status'] && $_GET['status'] < 9) ? $_GET['status'] : 0);
$usr_mysql_status = (isset($_GET['usr_mysql_status']) && isnum($_GET['usr_mysql_status'] && $_GET['usr_mysql_status'] < 9) ? $_GET['usr_mysql_status'] : 0);
$user_id  = (isset($_GET['user_id']) && isnum($_GET['user_id']) ? $_GET['user_id'] : FALSE);
$action   = (isset($_GET['action']) && isnum($_GET['action']) ? $_GET['action'] : "");

add_breadcrumb(array('link' => ADMIN.'members.php'.$aidlink, 'title' => $locale['400']));

define("USER_MANAGEMENT_SELF", FUSION_SELF.$aidlink."&sortby=$sortby&status=$status&rowstart=$rowstart");

$checkRights = dbcount("(user_id)", DB_USERS, "user_id='".$user_id."' AND user_level>101");
if ($checkRights > 0) {
    $isAdmin = TRUE;
} else {
    $isAdmin = FALSE;
}

if (isset($_POST['cancel'])) {
    redirect(USER_MANAGEMENT_SELF);
}
// Show Logs
elseif (isset($_GET['step']) && $_GET['step'] == "log" && $user_id && (!$isAdmin || iSUPERADMIN)) {
    display_suspend_log($user_id, "all", $rowstart);
} // Deactivate Inactive Users
elseif (isset($_GET['step']) && $_GET['step'] == "inactive" && !$user_id && $settings['enable_deactivation'] == 1 && (!$isAdmin || iSUPERADMIN)) {

    $inactive = dbcount("(user_id)", DB_USERS, "user_status='0' AND user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'".intval($time_overdue)."' AND user_actiontime='0'");
    $action   = $settings['deactivation_action'] == 0 ? $locale['616'] : $locale['615'];
    $button   = $locale['614'].($inactive == 1 ? " 1 ".$locale['612'] : " 50 ".$locale['613']);
    if (!$inactive) {
        redirect(USER_MANAGEMENT_SELF);
    }
    opentable($locale['580']);
    if ($inactive > 50) {
        $run_times = round($inactive / 50);
        addNotice("info", sprintf($locale['581'], $run_times));
    }
    echo "<div class='tbl1'>";
    $text = sprintf($locale['610'], $inactive, $settings['deactivation_period'], $settings['deactivation_response'], $action);
    echo str_replace(array("[strong]", "[/strong]"),
        array("<strong>", "</strong>"),
        $text
    );
    if ($settings['deactivation_action'] == 1) {
        echo "<br />\n".$locale['611'];
        echo "</div>\n<div class='admin-message alert alert-warning m-t-10'><strong>".$locale['617']."</strong>\n".$locale['618']."\n";
        if (checkrights("S9")) {
            echo "<a href='".ADMIN."settings_users.php".$aidlink."'>".$locale['619']."</a>";
        }
    }
    echo "</div>\n<div class='tbl1 text-center'>\n";
    echo openform('member_form', 'post', FUSION_SELF.$aidlink."&amp;step=inactive");
    echo form_button('cancel', $locale['418'], $locale['418'], array('class' => 'btn-primary'));
    echo form_button('deactivate_users', $button, $button, array('class' => 'btn-primary'));
    echo closeform();
    echo "</div>\n";
    closetable();
    if (isset($_POST['deactivate_users']) && $defender->safe()) {
        require_once LOCALE.LOCALESET."admin/members_email.php";
        require_once INCLUDES."sendmail_include.php";
        $result = dbquery("SELECT user_id, user_name, user_email, user_password FROM ".DB_USERS."
		WHERE user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'".$time_overdue."' AND user_actiontime='0' AND user_status='0'
		LIMIT 0,50");
        while ($data = dbarray($result)) {
            $code    = md5($response_required.$data['user_password']);
            $message = str_replace("[CODE]", $code, $locale['email_deactivate_message']);
            $message = str_replace("[SITENAME]", $settings['sitename'], $message);
            $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
            $message = str_replace("[USER_NAME]", $data['user_name'], $message);
            $message = str_replace("[USER_ID]", $data['user_id'], $message);
            if (sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['email_deactivate_subject'], $message)) {
                $result2 = dbquery("UPDATE ".DB_USERS." SET user_status='7', user_actiontime='".$response_required."' WHERE user_id='".$data['user_id']."'");
                suspend_log($data['user_id'], 7, $locale['621']);
            }
        }
        redirect(FUSION_SELF.$aidlink);
    }
// Add new User
} elseif (isset($_GET['step']) && $_GET['step'] == "add" && (!$isAdmin || iSUPERADMIN)) {

    if (isset($_POST['add_user'])) {

        $userInput                    = new \PHPFusion\UserFieldsInput();
        $userInput->validation = FALSE;
        $userInput->emailVerification = FALSE;
        $userInput->adminActivation = FALSE;
        $userInput->registration      = TRUE;
        $userInput->skipCurrentPass   = TRUE;

        $userInput->saveInsert();

        $udata = $userInput->getData();
        unset($userInput);

        if ($defender->safe()) {
            redirect(FUSION_SELF.$aidlink);
        }

    }

    if (!isset($_POST['add_user']) || (isset($_POST['add_user']) && !$defender->safe())) {

        opentable($locale['480']);
        add_breadcrumb(array('link' => '', 'title' => $locale['480']));

        $userFields                       = new \PHPFusion\UserFields();
        $userFields->postName             = "add_user";
        $userFields->postValue            = $locale['480'];
        $userFields->displayValidation    = fusion_get_settings("display_validation");
        $userFields->plugin_folder        = INCLUDES."user_fields/";
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->showAdminPass        = FALSE;
        $userFields->skipCurrentPass      = TRUE;
        $userFields->registration         = TRUE;
        $userFields->method               = 'input';

        $info = $userFields->get_profile_input();
        render_userform($info);
        closetable();
    }
// View User Profile
} elseif (isset($_GET['step']) && $_GET['step'] == "view" && $user_id && (!$isAdmin || iSUPERADMIN)) {
    $result = dbquery("SELECT u.*, s.suspend_reason
		FROM ".DB_USERS." u
		LEFT JOIN ".DB_SUSPENDS." s ON u.user_id=s.suspended_user
		WHERE user_id='".$user_id."'
		ORDER BY suspend_date DESC
		LIMIT 1");
    if (dbrows($result)) {
        $user_data = dbarray($result);
    } else {
        redirect(FUSION_SELF.$aidlink);
    }
    opentable($locale['u104']." ".$user_data['user_name']);
    member_nav(member_url("view", $user_id)."|".$user_data['user_name']);
    $userFields                       = new \PHPFusion\UserFields();
    $userFields->postName             = "register";
    $userFields->postValue            = $locale['u101'];
    $userFields->displayValidation    = $settings['display_validation'];
    $userFields->displayTerms         = $settings['enable_terms'];
    $userFields->plugin_folder        = INCLUDES."user_fields/";
    $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
    $userFields->showAdminPass        = FALSE;
    $userFields->skipCurrentPass      = TRUE;
    $userFields->registration         = FALSE;
    $userFields->userData             = $user_data;
    $userFields->method               = 'display';

    $info = $userFields->get_profile_output();
    render_userprofile($info);
    closetable();

// Edit User Profile
} elseif (isset($_GET['step']) && $_GET['step'] == "edit" && $user_id && (!$isAdmin || iSUPERADMIN)) {
    $user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$user_id."'"));
    if (!$user_data || $user_data['user_level'] == -103) {
        redirect(FUSION_SELF.$aidlink);
    }
    $errors = array();
    if (isset($_POST['savechanges'])) {
        $userInput                    = new \PHPFusion\UserFieldsInput();
        $userInput->userData          = $user_data;
        $userInput->adminActivation   = 0;
        $userInput->registration      = FALSE;
        $userInput->emailVerification = 0;
        $userInput->isAdminPanel      = TRUE;
        $userInput->skipCurrentPass   = TRUE;
        $userInput->saveUpdate();
        $user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$user_id."'"));
        unset($userInput);
        if ($defender->safe()) {
            redirect(FUSION_SELF.$aidlink);
        }
    }
    opentable($locale['430']);
    add_breadcrumb(array('link' => '', 'title' => $locale['430']));
    $userFields                       = new UserFields();
    $userFields->postName             = "savechanges";
    $userFields->postValue            = $locale['430'];
    $userFields->displayValidation    = 0;
    $userFields->displayTerms         = FALSE;
    $userFields->plugin_folder        = INCLUDES."user_fields/";
    $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
    $userFields->showAdminPass        = FALSE;
    $userFields->skipCurrentPass      = TRUE;
    $userFields->userData             = $user_data;
    $userFields->method               = 'input';
    $userFields->admin_mode           = true;
    $info = $userFields->get_profile_input();
    render_userform($info);
    closetable();
// Delete User
} elseif (isset($_GET['step']) && $_GET['step'] == "delete" && $user_id && (!$isAdmin || iSUPERADMIN)) {
    if (isset($_POST['delete_user'])) {
        $result = dbquery("SELECT user_id, user_avatar FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level>".USER_LEVEL_SUPER_ADMIN);
        if (dbrows($result)) {
            // Delete avatar
            $data = dbarray($result);
            if ($data['user_avatar'] != "" && file_exists(IMAGES."avatars/".$data['user_avatar'])) {
                @unlink(IMAGES."avatars/".$data['user_avatar']);
            }
            if (db_exists(DB_PHOTOS)) {
                // Delete photos
                $result = dbquery("SELECT album_id, photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE photo_user='".$user_id."'");
                if (dbrows($result)) {
                    while ($data = dbarray($result)) {
                        $result = dbquery("DELETE FROM ".DB_PHOTOS." WHERE photo_user='".intval($user_id)."'");
                        @unlink(IMAGES_G.$data['photo_filename']);
                        @unlink(IMAGES_G_T.$data['photo_thumb1']);
                        @unlink(IMAGES_G_T.$data['photo_thumb2']);
                    }
                }
            }

            // Delete content
            $result = dbquery("DELETE FROM ".DB_USERS." WHERE user_id='".$user_id."'");
            $result = dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_name='".$user_id."'");
            $result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_user='".$user_id."'");
            $result = dbquery("DELETE FROM ".DB_SUSPENDS." WHERE suspended_user='".$user_id."'");
            $result = dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_to='".$user_id."' OR message_from='".$user_id."'");

            if (db_exists(DB_ARTICLES)) {
                $result = dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_name='".$user_id."'");
            }
            if (db_exists(DB_NEWS)) {
                $result = dbquery("DELETE FROM ".DB_NEWS." WHERE news_name='".$user_id."'");
            }
            if (db_exists(DB_POLL_VOTES)) {
                $result = dbquery("DELETE FROM ".DB_POLL_VOTES." WHERE vote_user='".$user_id."'");
            }
            if (db_exists(DB_FORUMS)) {
                $result  = dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author='".$user_id."'");
                $result  = dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_author='".$user_id."'");
                $result  = dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user='".$user_id."'");
                $result  = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id='".$user_id."'"); // Delete votes on forum threads
                $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_lastuser='".$user_id."'");
                if (dbrows($threads)) {
                    while ($thread = dbarray($threads)) {
                        // Update thread last post author, date and id
                        $last_thread_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread['thread_id']."' ORDER BY post_id DESC LIMIT 0,1"));
                        dbquery("UPDATE ".DB_FORUM_THREADS." SET	thread_lastpost='".$last_thread_post['post_datestamp']."',
																	thread_lastpostid='".$last_thread_post['post_id']."',
																	thread_lastuser='".$last_thread_post['post_author']."'
																	WHERE thread_id='".$thread['thread_id']."'");
                        // Update thread posts count
                        $posts_count = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".$thread['thread_id']."'");
                        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_postcount='".$posts_count."' WHERE thread_id='".$thread['thread_id']."'");
                        // Update forum threads count and posts count
                        list($threadcount, $postcount) = dbarraynum(dbquery("SELECT COUNT(thread_id), SUM(thread_postcount) FROM ".DB_FORUM_THREADS." WHERE forum_id='".$thread['forum_id']."' AND thread_lastuser='".$user_id."' AND thread_hidden='0'"));
                        if (isnum($threadcount) && isnum($postcount)) {
                            dbquery("UPDATE ".DB_FORUMS." SET forum_postcount='".$postcount."', forum_threadcount='".$threadcount."' WHERE forum_id='".$thread['forum_id']."' AND forum_lastuser='".$user_id."'");
                        }
                    }
                }
                $forums = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_lastuser='".$user_id."'");
                if (dbrows($forums)) {
                    while ($forum = dbarray($forums)) {
                        // find the user one before the current user's post
                        $last_forum_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id='".$forum['forum_id']."' ORDER BY post_id DESC LIMIT 0,1"));
                        dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$last_forum_post['post_datestamp']."', forum_lastuser='".$last_forum_post['post_author']."' WHERE forum_id='".$forum['forum_id']."' AND forum_lastuser='".$user_id."'");
                    }
                }
                // Delete all threads that has been started by the user.
                $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_author='".$user_id."'");
                if (dbrows($threads)) {
                    while ($thread = dbarray($threads)) {
                        // Delete the posts made by other users in threads started by deleted user
                        if ($thread['thread_postcount'] > 0) {
                            dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread['thread_id']."'");
                        }
                        // Delete polls in threads and their associated poll options and votes cast by other users in threads started by deleted user
                        if ($thread['thread_poll'] == 1) {
                            dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$thread['thread_id']."'");
                            dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$thread['thread_id']."'");
                            dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$thread['thread_id']."'");
                        }
                    }
                }
                $count_posts = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
                if (dbrows($count_posts)) {
                    while ($data = dbarray($count_posts)) {
                        // Update the posts count for all users
                        dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
                    }
                }
            }
            redirect(USER_MANAGEMENT_SELF."&status=dok");
        } else {
            redirect(USER_MANAGEMENT_SELF."&status=der");
        }
    } else {
        $user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$user_id."'"));
        opentable($locale['410']." ".$locale['612'].": ".$user_data['user_name']);
        echo "<h2>".$locale['425']."</h2>";
        echo "<p>".sprintf($locale['425a'], "<strong>".$user_data['user_name'])."</strong></p>\n";
        echo openform('mod_form', 'post', stripinput(USER_MANAGEMENT_SELF)."&amp;step=delete&amp;user_id=".$user_id, array('max_tokens' => 1));
        echo form_button('delete_user', $locale['426'], $locale['426'], array('class' => 'btn-primary m-r-10'));
        echo form_button('cancel', $locale['427'], $locale['427'], array('class' => 'btn-primary'));
        echo closeform();
        closetable();
    }
    // Ban User
} elseif (isset($_GET['action']) && $_GET['action'] == 1 && $user_id && (!$isAdmin || iSUPERADMIN)) {
    require_once LOCALE.LOCALESET."admin/members_email.php";
    require_once INCLUDES."sendmail_include.php";
    $result = dbquery("SELECT user_name, user_email, user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level>".USER_LEVEL_SUPER_ADMIN);
    if (dbrows($result)) {
        $udata = dbarray($result);
        if (isset($_POST['ban_user'])) {
            if ($udata['user_status'] == 1) {
                $result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
                unsuspend_log($user_id, 1, stripinput($_POST['ban_reason']));
                redirect(USER_MANAGEMENT_SELF."&status=bre");
            } else {
                $result = dbquery("UPDATE ".DB_USERS." SET user_status='1', user_actiontime='0' WHERE user_id='".$user_id."'");
                $ban_reason = form_sanitizer($_POST['ban_reason'], "", "ban_reason");
                if ($defender->safe()) {
                    suspend_log($user_id, 1, $ban_reason);
                    $message = str_replace("[USER_NAME]", $udata['user_name'], $locale['email_ban_message']);
                    $message = str_replace("[REASON]", $ban_reason, $message);
                    $message = str_replace("[SITENAME]", $settings['sitename'], $message);
                    $message = str_replace("[ADMIN_USERNAME]", $userdata['user_name'], $message);
                    $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
                    $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['email_ban_subject']);
                    sendemail($udata['user_name'], $udata['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
                    redirect(USER_MANAGEMENT_SELF."&status=bad");
                }

            }
        } else {
            if ($udata['user_status'] == 1) {
                $ban_title = $locale['408']." ".$udata['user_name'];
            } else {
                $ban_title = $locale['409']." ".$udata['user_name'];
            }
            opentable($ban_title);
            echo openform('ban_user', 'post', stripinput(USER_MANAGEMENT_SELF)."&amp;action=1&amp;user_id=".$user_id, array('max_tokens' => 1));
            echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tbody>\n<tr>\n";
            echo "<td colspan='2' class='tbl'><strong>".$locale['585a'].$udata['user_name'].".</strong></td>\n";
            echo "</tr>\n<tr>\n";
            echo "<td valign='top' width='80' class='tbl'>".$locale['515'].":</td>\n";
            echo "<td class='tbl'>\n";
            echo form_textarea('ban_reason', '', '');
            echo "</td>\n</tr>\n<tr>\n";
            echo "<td colspan='2' align='center'>\n";
            echo form_button('cancel', $locale['418'], $locale['418'], array('class' => 'btn-primary m-r-10'));
            echo form_button('ban_user', $ban_title, $ban_title, array('class' => 'btn-primary'));
            echo "</tbody>\n</tr>\n</table>\n";
            echo closeform();
            closetable();
            display_suspend_log($user_id, 1, $rowstart, 10);
        }
    } else {
        redirect(USER_MANAGEMENT_SELF."&status=ber");
    }
    // Activate User
} elseif (isset($_GET['action']) && $_GET['action'] == 2 && $user_id && (!$isAdmin || iSUPERADMIN)) {
    require_once LOCALE.LOCALESET."admin/members_email.php";
    require_once INCLUDES."sendmail_include.php";
    $result = dbquery("SELECT user_name, user_email FROM ".DB_USERS." WHERE user_id='".$user_id."' LIMIT 1");
    if (dbrows($result)) {
        $udata  = dbarray($result);
        $result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
        suspend_log($user_id, 2);
        $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['email_activate_subject']);
        $message = str_replace("[USER_NAME]", $udata['user_name'], $message);
        $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
        sendemail($udata['user_name'], $udata['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
        redirect(USER_MANAGEMENT_SELF."&status=aok");
    } else {
        redirect(USER_MANAGEMENT_SELF."&status=aer");
    }
    // Suspend User
} elseif (isset($_GET['action']) && $_GET['action'] == 3 && $user_id && (!$isAdmin || iSUPERADMIN)) {
    include LOCALE.LOCALESET."admin/members_email.php";
    require_once INCLUDES."sendmail_include.php";
    $result = dbquery("SELECT user_name, user_email, user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level>".USER_LEVEL_SUPER_ADMIN);
    if (dbrows($result)) {
        $udata = dbarray($result);
        if (isset($_POST['suspend_user'])) {
            if ($udata['user_status'] == 3) {
                $result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
                unsuspend_log($user_id, 3, stripinput($_POST['suspend_reason']));
                redirect(USER_MANAGEMENT_SELF."&status=sre");
            } else {
                $actiontime = (isset($_POST['suspend_duration']) && isnum($_POST['suspend_duration']) ? $_POST['suspend_duration'] * 86400 : 864000) + time();
                $result     = dbquery("UPDATE ".DB_USERS." SET user_status='3', user_actiontime='$actiontime' WHERE user_id='".$user_id."'");
                suspend_log($user_id, 3, stripinput($_POST['suspend_reason']));
                $message = str_replace("[USER_NAME]", $udata['user_name'], $locale['email_suspend_message']);
                $message = str_replace("[SITENAME]", $settings['sitename'], $message);
                $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
                $message = str_replace("[ADMIN_USERNAME]", $userdata['user_name'], $message);
                $message = str_replace("[DATE]", showdate('longdate', $actiontime), $message);
                $message = str_replace("[REASON]", stripinput($_POST['suspend_reason']), $message);

                $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['email_suspend_subject']);

                sendemail($udata['user_name'], $udata['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
                redirect(USER_MANAGEMENT_SELF."&status=sad");
            }
        } else {
            if ($udata['user_status'] == 3) {
                $suspend_title = $locale['591']." ".$udata['user_name'];
                $action        = $locale['593'];
            } else {
                $suspend_title = $locale['590']." ".$udata['user_name'];
                $action        = $locale['592'];
            }
            opentable($suspend_title);
            echo openform('ban_user', 'post', stripinput(USER_MANAGEMENT_SELF)."&amp;action=3&amp;user_id=".$user_id, array('max_tokens' => 1));
            echo "<table cellpadding='0' cellspacing='0' width='460' class='table table-responsive center'>\n<tbody>\n<tr>\n";
            echo "<td colspan='2' class='tbl'><strong>".$locale['594'].$action.$locale['595'].$udata['user_name'].".</strong></td>\n";
            if ($udata['user_status'] != 3) {
                echo "</tr>\n<tr>\n";
                echo "<td valign='top' width='80' class='tbl'>".$locale['596']."</td>\n";
                echo "<td class='tbl'><input type='text' name='suspend_duration' class='textbox' style='width:60px;' /> <span class='small'>(".$locale['551'].")</span></td>\n";
            }
            echo "</tr>\n<tr>\n";
            echo "<td valign='top' width='80' class='tbl'>".$locale['552']."</td>\n";
            echo "<td class='tbl'>\n";
            echo form_textarea('suspend_reason', '', '');
            echo "</td>\n</tr>\n<tr>\n";
            echo "<td colspan='2' align='center'>\n";
            echo form_button('cancel', $locale['418'], $locale['418'], array('class' => 'btn-primary m-r-10'));
            echo form_button('suspend_user', $suspend_title, $suspend_title, array('class' => 'btn-primary'));
            echo "</td>\n</tr>\n</tbody>\n</table>\n</form>\n";
            closetable();
            display_suspend_log($user_id, 3, 10, 10);
        }
    } else {
        redirect(USER_MANAGEMENT_SELF."&status=ser");
    }
    // Security Ban User
} elseif (isset($_GET['action']) && $_GET['action'] == 4 && $user_id && (!$isAdmin || iSUPERADMIN)) {
    require_once LOCALE.LOCALESET."admin/members_email.php";
    require_once INCLUDES."sendmail_include.php";
    $result = dbquery("SELECT user_name, user_email, user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level>".USER_LEVEL_SUPER_ADMIN);
    if (dbrows($result)) {
        $udata = dbarray($result);
        if (isset($_POST['sban_user'])) {
            if ($udata['user_status'] == 4) {
                $result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
                unsuspend_log($user_id, 4, stripinput($_POST['sban_reason']));
                redirect(USER_MANAGEMENT_SELF."&status=sbre");
            } else {
                $result = dbquery("UPDATE ".DB_USERS." SET user_status='4', user_actiontime='0' WHERE user_id='".$user_id."'");
                suspend_log($user_id, 4, stripinput($_POST['sban_reason']));
                $message = str_replace("[USER_NAME]", $udata['user_name'], $locale['email_secban_message']);
                $message = str_replace("[SITENAME]", $settings['sitename'], $message);
                $message = str_replace("[ADMIN_USERNAME]", $userdata['user_name'], $message);
                $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);

                $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['email_secban_subject']);
                sendemail($udata['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['email_secban_subject'], $message);
                redirect(USER_MANAGEMENT_SELF."&status=sbad");
            }
        } else {
            if ($udata['user_status'] == 4) {
                $ban_title = $locale['602'].$udata['user_name'];
                $action    = $locale['603'];
            } else {
                $ban_title = $locale['600']." ".$udata['user_name'];
                $action    = $locale['601'];
            }
            opentable($ban_title);
            echo openform('sban_users', 'post', stripinput(USER_MANAGEMENT_SELF)."&amp;action=4&amp;user_id=".$user_id, array('max_tokens' => 1));
            echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tbody>\n<tr>\n";
            echo "<td colspan='2' class='tbl'><strong>".$locale['594'].$action.$locale['595'].$udata['user_name'].".</strong></td>\n";
            echo "</tr>\n<tr>\n";
            echo "<td valign='top' width='80' class='tbl'>".$locale['604']."</td>\n";
            echo "<td class='tbl'>\n";
            echo form_textarea('sban_reason', '', '');
            echo "</td>\n</tr>\n<tr>\n";
            echo "<td colspan='2' align='center'>\n";
            echo form_button('cancel', $locale['418'], $locale['418'], array('class' => 'btn-primary m-r-10'));
            echo form_button('sban_user', $ban_title, $ban_title, array('class' => 'btn-primary'));
            echo "</td>\n</tr>\n</tbody>\n</table>\n</form>\n";
            closetable();
            display_suspend_log($user_id, 4, 10, 10);
        }
    } else {
        redirect(USER_MANAGEMENT_SELF."&status=sber");
    }
    // Cancel User
} elseif (isset($_GET['action']) && $_GET['action'] == 5 && $user_id && (!$isAdmin || iSUPERADMIN)) {
    $result = dbquery("SELECT user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level<".USER_LEVEL_SUPER_ADMIN);
    if (dbrows($result)) {
        $udata = dbarray($result);
        if ($udata['user_status'] == 5) {
            $result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
            unsuspend_log($user_id, 5);
        } else {
            $result = dbquery("UPDATE ".DB_USERS." SET user_status='5', user_actiontime='".$response_required."' WHERE user_id='".$user_id."'");
            suspend_log($user_id, 5);
        }
        redirect(USER_MANAGEMENT_SELF);
    } else {
        redirect(USER_MANAGEMENT_SELF);
    }
    // Annonymise User
} elseif (isset($_GET['action']) && $_GET['action'] == 6 && $user_id && (!$isAdmin || iSUPERADMIN)) {
    $result = dbquery("SELECT user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level>".USER_LEVEL_SUPER_ADMIN);
    if (dbrows($result)) {
        $udata = dbarray($result);
        if ($udata['user_status'] == 6) {
            $result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
            unsuspend_log($user_id, 6);
        } else {
            $result = dbquery("UPDATE ".DB_USERS." SET user_status='6', user_actiontime='0' WHERE user_id='".$user_id."'");
            suspend_log($user_id, 6);
        }
        redirect(USER_MANAGEMENT_SELF);
    } else {
        redirect(USER_MANAGEMENT_SELF);
    }
    // Deactivate User
} elseif (isset($_GET['action']) && $_GET['action'] == 7 && $user_id && (!$isAdmin || iSUPERADMIN)) {
    $result = dbquery("SELECT user_status FROM ".DB_USERS." WHERE user_id='".$user_id."' AND user_level>".USER_LEVEL_SUPER_ADMIN);
    if (dbrows($result)) {
        $udata = dbarray($result);
        if ($udata['user_status'] == 7) {
            $result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user_id."'");
            unsuspend_log($user_id, 7);
        } else {
            require_once LOCALE.LOCALESET."admin/members_email.php";
            require_once INCLUDES."sendmail_include.php";
            $code    = md5($response_required.$data['user_password']);

            $message = str_replace("[USER_NAME]", $udata['user_name'], $locale['email_deactivate_message']);
            $message = str_replace("[SITENAME]", $settings['sitename'], $message);
            $message = str_replace("[ADMIN_USERNAME]", $userdata['user_name'], $message);
            $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
            $message = str_replace("[REACTIVATION_LINK]", fusion_get_settings('siteurl')."reactivate.php?user_id=".$data['user_id']."&code=".$code, $message);

            $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['email_deactivate_subject']);

            if (sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message)) {
                $result = dbquery("UPDATE ".DB_USERS." SET user_status='7', user_actiontime='".$response_required."' WHERE user_id='".$user_id."'");
                suspend_log($user_id, 7);
            }
        }
        redirect(USER_MANAGEMENT_SELF);
    } else {
        redirect(USER_MANAGEMENT_SELF);
    }
} else {
    opentable($locale['400']);
    if (isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i", $_GET['search_text'])) {
        $user_name = " user_name LIKE '".stripinput($_GET['search_text'])."%' AND";
        $list_link = "search_text=".stripinput($_GET['search_text']);
    } elseif (isset($_GET['sortby']) && preg_check("/^[0-9A-Z]$/", $_GET['sortby'])) {
        $user_name = ($_GET['sortby'] == "all" ? "" : " user_name LIKE '".stripinput($_GET['sortby'])."%' AND");
        $list_link = "sortby=".stripinput($_GET['sortby']);
    } else {
        $user_name      = "";
        $list_link      = "sortby=all";
        $_GET['sortby'] = "all";
    }
    $usr_mysql_status = $status;
    if ($status == 0 && $settings['enable_deactivation'] == 1) {
        $usr_mysql_status = "0' AND user_lastvisit>'".$time_overdue."' AND user_actiontime='0";
    } elseif ($status == 8 && $settings['enable_deactivation'] == 1) {
        $usr_mysql_status = "0' AND user_lastvisit<'".$time_overdue."' AND user_actiontime='0";
    }
    $rows   = dbcount("(user_id)", DB_USERS, "$user_name user_status='$usr_mysql_status' AND user_level>".USER_LEVEL_SUPER_ADMIN);
    $result = dbquery("SELECT user_id, user_name, user_level, user_avatar, user_status FROM ".DB_USERS."
		WHERE $user_name user_status='$usr_mysql_status' AND user_level>".USER_LEVEL_SUPER_ADMIN."
		ORDER BY user_level DESC, user_name
		LIMIT $rowstart,20");
    echo openform('viewstatus', 'get', FUSION_SELF.$aidlink, array('max_tokens' => 1, 'class' => 'clearfix'));
    echo "<div class='btn-group'>\n";
    echo "<a class='button btn btn-sm btn-primary' href='".FUSION_SELF.$aidlink."&amp;step=add'>".$locale['402']."</a>\n";
    if ($settings['enable_deactivation'] == 1) {
        if (dbcount("(user_id)", DB_USERS, "user_status='0' AND user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'$time_overdue' AND user_actiontime='0'")) {
            echo "<a class='button btn btn-sm btn-default' href='".FUSION_SELF.$aidlink."&amp;step=inactive'>".$locale['580']."</a>\n";
        }
    }
    echo "</div>\n";
    echo form_hidden('aid', '', iAUTH);
    echo form_hidden('sortby', '', $sortby);
    echo form_hidden('rowstart', '', $rowstart);
    for ($i = 0; $i < 9; $i++) {
        if ($i < 8 || $settings['enable_deactivation'] == 1) {
            $opts[$i] = getsuspension($i);
        }
    }
    echo "<div class='display-inline-block pull-right'>\n";
    echo form_select('status', $locale['405'], isset($_GET['status']) && isnum($_GET['status']) ? $_GET['status'] : '', array(
        'options'     => $opts,
        'placeholder' => $locale['choose'],
        'class'       => 'col-sm-3 col-md-3 col-lg-3',
        'inline'      => 1,
        'allowclear'  => 1
    ));
    echo "</div>\n";
    add_to_jquery("$('#status').on('change', function() { this.form.submit(); });");
    echo form_hidden('rowstart', '', $rowstart);
    echo closeform();
    if ($rows) {
        $i = 0;
        echo "<div class='list-group clearfix'>\n";
        while ($data = dbarray($result)) {
            echo "<div class='list-group-item clearfix'>\n";
            echo "<div class='pull-left m-r-10'>\n".display_avatar($data, '50px', '', '', 'img-rounded')."</div>\n";
            echo "<div class='pull-right m-l-15'>\n";
            $ban_link     = FUSION_SELF.$aidlink."&amp;sortby=$sortby&amp;status=$status&amp;rowstart=$rowstart&amp;user_id=".$data['user_id']."&amp;action=1";
            $suspend_link = FUSION_SELF.$aidlink."&amp;sortby=$sortby&amp;status=$status&amp;rowstart=$rowstart&amp;user_id=".$data['user_id']."&amp;action=3";
            $cancel_link  = FUSION_SELF.$aidlink."&amp;sortby=$sortby&amp;status=$status&amp;rowstart=$rowstart&amp;user_id=".$data['user_id']."&amp;action=5";
            $anon_link    = FUSION_SELF.$aidlink."&amp;sortby=$sortby&amp;status=$status&amp;rowstart=$rowstart&amp;user_id=".$data['user_id']."&amp;action=6";
            $deac_link    = FUSION_SELF.$aidlink."&amp;sortby=$sortby&amp;status=$status&amp;rowstart=$rowstart&amp;user_id=".$data['user_id']."&amp;action=7";
            $inac_link    = FUSION_SELF.$aidlink."&amp;sortby=$sortby&amp;status=$status&amp;rowstart=$rowstart&amp;user_id=".$data['user_id']."&amp;action=8";
            echo "<div class='btn-group'>\n";
            if (iSUPERADMIN || $data['user_level'] < 102) {
                echo "<a class='btn button btn-sm btn-default ' href='".FUSION_SELF.$aidlink."&amp;step=edit&amp;user_id=".$data['user_id']."&amp;settings'>".$locale['406']."</a>\n";
                if ($status == 0) {
                    echo "<a class='btn button btn-sm btn-default ' href='".stripinput(USER_MANAGEMENT_SELF."&action=3&user_id=".$data['user_id'])."'>".$locale['553']."</a>\n";
                } elseif ($status == 2) {
                    $title = $locale['407'];
                } elseif ($status != 8) {
                    $title = $locale['419'];
                }
                if (isset($title)) {
                    echo "<a class='btn button btn-sm btn-default' href='".stripinput(USER_MANAGEMENT_SELF."&action=$status&user_id=".$data['user_id'])."'>$title</a>\n";
                }
                echo "<div class='btn-group'>\n";
                echo "<a class='btn button btn-sm btn-default' href='".stripinput(USER_MANAGEMENT_SELF."&step=delete&user_id=".$data['user_id'])."'>".$locale['410']."</a>\n";
                // more actions.
                echo "<a class='btn button btn-sm btn-default dropdown-toggle' data-toggle='dropdown'>\n<span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></a>\n";
                echo "<ul class='dropdown-menu text-left' role='action-menu'>\n";
                echo "<li><a href='$ban_link'>".getsuspension(1, TRUE)."</a></li>\n";
                echo "<li><a href='$suspend_link'>".getsuspension(3, TRUE)."</a></li>\n";
                echo "<li><a href='$cancel_link'>".getsuspension(5, TRUE)."</a></li>\n";
                echo "<li><a href='$anon_link'>".getsuspension(6, TRUE)."</a></li>\n";
                echo "<li><a href='$deac_link'>".getsuspension(7, TRUE)."</a></li>\n";
                echo "<li><a href='$inac_link'>".getsuspension(8, TRUE)."</a></li>\n";
                echo "</ul>\n";
                echo "</div>\n";
            }
            echo "</div>\n";
            echo "</div>\n";
            echo "<div class='overflow-hide'>\n";
            echo "<a class='strong display-inline-block' href='".FUSION_SELF.$aidlink."&amp;step=view&amp;user_id=".$data['user_id']."'>".$data['user_name']."</a>\n";
            echo "<br/><span class='text-smaller'>".getuserlevel($data['user_level'])."</span>\n";
            echo "</div>\n";
            echo "</div>\n";
            $i++;
        }
        echo "<div>\n";
    } else {
        if (isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i", $_GET['search_text'])) {
            echo "<div style='text-align:center'><br />".sprintf($locale['411'], ($status == 0 ? "" : getsuspension($status))).$locale['413']."'".stripinput($_GET['search_text'])."'<br /><br />\n</div>\n";
        } else {
            echo "<div style='text-align:center'><br />".sprintf($locale['411'], ($status == 0 ? "" : getsuspension($status))).($_GET['sortby'] == "all" ? "" : $locale['412'].$_GET['sortby']).".<br /><br />\n</div>\n";
        }
    }
    echo "<hr/>\n";
    $alphanum = array(
        "A",
        "B",
        "C",
        "D",
        "E",
        "F",
        "G",
        "H",
        "I",
        "J",
        "K",
        "L",
        "M",
        "N",
        "O",
        "P",
        "Q",
        "R",
        "S",
        "T",
        "U",
        "V",
        "W",
        "X",
        "Y",
        "Z",
        "0",
        "1",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "9"
    );
    echo "<table class='table table-responsive table-striped center'>\n<tr>\n";
    echo "<td rowspan='2' class='tbl2'><a class='strong' href='".FUSION_SELF.$aidlink."&amp;status=".$status."'>".$locale['414']."</a></td>";
    for ($i = 0; $i < 36; $i++) {
        echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF.$aidlink."&amp;sortby=".$alphanum[$i]."&amp;status=$status'>".$alphanum[$i]."</a></div></td>";
        echo($i == 17 ? "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;status=".$status."'>".$locale['414']."</a></td>\n</tr>\n<tr>\n" : "\n");
    }
    echo "</tr>\n</table>\n";
    echo "<hr />\n";
    echo openform('searchform', 'get', FUSION_SELF.$aidlink, array('max_tokens' => 1, 'notice' => 0));
    echo form_hidden('aid', '', iAUTH);
    echo form_hidden('status', '', $status);
    echo form_text('search_text', $locale['415'], '', array('inline' => 1));
    echo form_button('search', $locale['416'], $locale['416'], array('class' => 'col-sm-offset-3 btn-sm btn-primary'));
    echo closeform();
    closetable();
    if ($rows > 20) {
        echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($rowstart, 20, $rows, 3, FUSION_SELF.$aidlink."&amp;sortby=".$sortby."&amp;status=".$status."&amp;")."\n</div>\n";
    }
}
require_once THEMES."templates/footer.php";
