<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members/sub_controllers/members_profile.php
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
namespace Administration\Members\Sub_Controllers;

use Administration\Members\Members_Admin;
use PHPFusion\UserFields;
use PHPFusion\UserFieldsInput;

/**
 * Class Members_Profile
 * Controller for View, Add, Edit and Delete Users Account
 *
 * @package Administration\Members\Sub_Controllers
 */
class Members_Profile extends Members_Admin {

    /*
     * Displays new user form
     */
    public static function display_new_user_form() {
        if (isset($_POST['add_new_user'])) {
            $userInput = new UserFieldsInput();
            $userInput->validation = FALSE;
            $userInput->emailVerification = FALSE;
            $userInput->adminActivation = FALSE;
            $userInput->registration = TRUE;
            $userInput->skipCurrentPass = TRUE;
            $userInput->saveInsert();
            unset($userInput);
            if (\defender::safe()) {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }
        $userFields = new UserFields();
        $userFields->postName = "add_new_user";
        $userFields->postValue = self::$locale['ME_450'];
        $userFields->displayValidation = fusion_get_settings("display_validation");
        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->registration = TRUE;
        $userFields->method = 'input';
        $userFields->display_profile_input();
    }

    /*
     * Displays user profile
     */
    public static function display_user_profile() {
        $settings = fusion_get_settings();
        $userFields = new UserFields();
        $userFields->postName = "register";
        $userFields->postValue = self::$locale['u101'];
        $userFields->displayValidation = $settings['display_validation'];
        $userFields->displayTerms = $settings['enable_terms'];
        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->registration = FALSE;
        $userFields->userData = self::$user_data;
        $userFields->method = 'display';
        $userFields->display_profile_output();
    }

    public static function edit_user_profile() {
        if (isset($_POST['savechanges'])) {
            $userInput = new \UserFieldsInput();
            $userInput->userData = self::$user_data; // full user data
            $userInput->adminActivation = 0;
            $userInput->registration = FALSE;
            $userInput->emailVerification = 0;
            $userInput->isAdminPanel = TRUE;
            $userInput->skipCurrentPass = TRUE;
            $userInput->saveUpdate();
            self::$user_data = $userInput->getData(); // data overridden on error.
            unset($userInput);
            if (\defender::safe()) {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }
        $userFields = new UserFields();
        $userFields->postName = 'savechanges';
        $userFields->postValue = self::$locale['ME_437'];
        $userFields->displayValidation = 0;
        $userFields->displayTerms = FALSE;
        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->userData = self::$user_data;
        $userFields->method = 'input';
        $userFields->admin_mode = TRUE;
        $userFields->display_profile_input();
    }

    public static function delete_user() {

        if (isset($_POST['delete_user'])) {
            $result = dbquery("SELECT user_id, user_avatar FROM ".DB_USERS." WHERE user_id=:user_id AND user_level >:user_level",
                [
                    ':user_id'    => self::$user_id,
                    ':user_level' => USER_LEVEL_SUPER_ADMIN
                ]
            );
            $rows = dbrows($result);
            if ($rows != '0') {
                // Delete avatar
                $data = dbarray($result);
                $user_id = $data['user_id'];

                if ($data['user_avatar'] != "" && file_exists(IMAGES."avatars/".$data['user_avatar'])) {
                    @unlink(IMAGES."avatars/".$data['user_avatar']);
                }

                /**
                 * @todo: Need to store user content reference column in a table for each infusions
                 */
                if (defined('GALLERY_EXIST')) {
                    // Delete photos
                    $result = dbquery("SELECT album_id, photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE photo_user=:photo_user", [':photo_user' => $user_id]);
                    if (dbrows($result)) {
                        while ($data = dbarray($result)) {
                            $result = dbquery("DELETE FROM ".DB_PHOTOS." WHERE photo_user=:user_id", [':user_id' => $user_id]);
                            @unlink(IMAGES_G.$data['photo_filename']);
                            @unlink(IMAGES_G_T.$data['photo_thumb1']);
                            @unlink(IMAGES_G_T.$data['photo_thumb2']);
                        }
                    }
                }

                // Delete content
                dbquery("DELETE FROM ".DB_USERS." WHERE user_id=:user_id", [':user_id' => $user_id]);
                dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_name=:comment_name", [':comment_name' => $user_id]);
                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_user=:rating_user", [':rating_user' => $user_id]);
                dbquery("DELETE FROM ".DB_SUSPENDS." WHERE suspended_user=:suspended_user", [':suspended_user' => $user_id]);
                dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_to=:message_to OR message_from=:message_from", [':message_to' => $user_id, ':message_from' => $user_id]);

                if (defined('ARTICLES_EXIST')) {
                    dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_name=:article_name", [':article_name' => $user_id]);
                }
                if (defined('NEWS_EXIST')) {
                    dbquery("DELETE FROM ".DB_NEWS." WHERE news_name=:news_name", [':news_name' => $user_id]);
                }
                if (defined('MEMBER_POLL_PANEL_EXIST')) {
                    dbquery("DELETE FROM ".DB_POLL_VOTES." WHERE vote_user=:vote_user", [':vote_user' => $user_id]);
                }
                if (defined('FORUM_EXIST')) {
                    dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author=:thread_author", [':thread_author' => $user_id]);
                    dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_author=:post_author", [':post_author' => $user_id]);
                    dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user=:notify_user", [':notify_user' => $user_id]);
                    dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id=:forum_vote_user_id", [':forum_vote_user_id' => $user_id]);
                    $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_lastuser=:thread_lastuser", [':thread_lastuser' => $user_id]);
                    if (dbrows($threads)) {
                        while ($thread = dbarray($threads)) {
                            // Update thread last post author, date and id
                            $last_thread_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id=:thread_id ORDER BY post_id DESC LIMIT 0,1", [':thread_id' => $thread['thread_id']]));
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:thread_lastpost, thread_lastpostid=:thread_lastpostid, thread_lastuser=:thread_lastuser WHERE thread_id=:thread_id",
                                [
                                    ':thread_lastpost'   => $last_thread_post['post_datestamp'],
                                    ':thread_lastpostid' => $last_thread_post['post_id'],
                                    ':thread_lastuser'   => $last_thread_post['post_author'],
                                    ':thread_id'         => $thread['thread_id']
                                ]);
                            // Update thread posts count
                            $posts_count = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_postcount=:thread_postcount WHERE thread_id=:thread_id", [':thread_postcount' => $posts_count, ':thread_id' => $thread['thread_id']]);
                            // Update forum threads count and posts count
                            list($threadcount, $postcount) = dbarraynum(dbquery("SELECT COUNT(thread_id), SUM(thread_postcount) FROM ".DB_FORUM_THREADS." WHERE forum_id=:forum_id AND thread_lastuser=:thread_lastuser AND thread_hidden=:thread_hidden", [':forum_id' => $thread['forum_id'], ':thread_lastuser' => $user_id, ':thread_hidden' => '0']));
                            if (isnum($threadcount) && isnum($postcount)) {
                                dbquery("UPDATE ".DB_FORUMS." SET forum_postcount=:forum_postcount, forum_threadcount=:forum_threadcount WHERE forum_id=:forum_id AND forum_lastuser=:forum_lastuser",
                                    [
                                        ':forum_postcount'   => $postcount,
                                        ':forum_threadcount' => $threadcount,
                                        ':forum_id'          => $thread['forum_id'],
                                        ':forum_lastuser'    => $user_id
                                    ]);
                            }
                        }
                    }
                    $forums = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_lastuser=:forum_lastuser", [':forum_lastuser' => $user_id]);
                    if (dbrows($forums)) {
                        while ($forum = dbarray($forums)) {
                            // find the user one before the current user's post
                            $last_forum_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id=:forum_id ORDER BY post_id DESC LIMIT 0,1", [':forum_id' => $forum['forum_id']]));
                            dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost=:forum_lastpost, forum_lastuser=:forum_lastuser WHERE forum_id=:forum_id AND forum_lastuser=:forum_lastuser",
                                [
                                    ':forum_lastpost' => $last_forum_post['post_datestamp'],
                                    ':forum_id'       => $forum['forum_id'],
                                    ':forum_lastuser' => $user_id
                                ]);
                        }
                    }
                    // Delete all threads that has been started by the user.
                    $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_author=:thread_author", [':thread_author' => $user_id]);
                    if (dbrows($threads)) {
                        while ($thread = dbarray($threads)) {
                            // Delete the posts made by other users in threads started by deleted user
                            if ($thread['thread_postcount'] > 0) {
                                dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                            }
                            // Delete polls in threads and their associated poll options and votes cast by other users in threads started by deleted user
                            if ($thread['thread_poll'] == 1) {
                                dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                                dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                                dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                            }
                        }
                    }
                    $count_posts = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
                    if (dbrows($count_posts)) {
                        while ($data = dbarray($count_posts)) {
                            // Update the posts count for all users
                            dbquery("UPDATE ".DB_USERS." SET user_posts=:user_posts WHERE user_id=:user_id", [':user_posts' => $data['num_posts'], ':user_id' => $data['post_author']]);
                        }
                    }
                }
                redirect(FUSION_SELF.fusion_get_aidlink());
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }
        echo "<div class='well'>\n";
        echo "<h4>".self::$locale['ME_454']."</h4>";
        echo "<p>".nl2br(sprintf(self::$locale['ME_455'], "<strong>".self::$user_data['user_name']."</strong>"))."</p>\n";
        echo openform('mod_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;ref=delete&amp;lookup=".self::$user_id."");
        echo "<div class='spacer-sm'>\n";
        echo form_button('delete_user', self::$locale['ME_456'], self::$locale['ME_456'], ['class' => 'btn-danger m-r-10']);
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
    }

    public static function delete_unactivated_user() {
        if (isset($_POST['delete_newuser'])) {
            dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_name=:user_name", [':user_name' => $_GET['lookup']]);
            redirect(clean_request('', ['ref', 'lookup', 'newuser'], FALSE));
        }

        echo "<div class='well'>\n";
        echo "<h4>".self::$locale['ME_454']."</h4>";
        echo "<p>".nl2br(sprintf(self::$locale['ME_457'], "<strong>".$_GET['lookup']."</strong>"))."</p>\n";
        echo openform('mod_form', 'post', FUSION_REQUEST);
        echo "<div class='spacer-sm'>\n";
        echo form_button('delete_newuser', self::$locale['ME_456'], self::$locale['ME_456'], ['class' => 'btn-danger m-r-10']);
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
    }

    public static function activate_user() {
        $result = dbquery("SELECT * FROM ".DB_NEW_USERS." WHERE user_code=:code AND user_name=:name", [':code' => $_GET['code'], ':name' => $_GET['lookup']]);

        $data = dbarray($result);
        $user_info = unserialize(base64_decode($data['user_info']));
        dbquery_insert(DB_USERS, $user_info, 'save');
        dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_code=:code", [':code' => $_GET['code']]);

        addNotice('success', self::$locale['ME_469']);
        redirect(clean_request('', ['ref', 'lookup', 'code'], FALSE));
    }

    public static function resend_email() {
        if (isset($_GET['lookup']) && !isnum($_GET['lookup'])) {
            $dbquery = dbquery("SELECT * FROM ".DB_NEW_USERS."
                WHERE user_name=:username", [':username' => $_GET['lookup']]);
            if (dbrows($dbquery)) {
                require_once INCLUDES."sendmail_include.php";
                self::$user_data = dbarray($dbquery);
                $activationUrl = fusion_get_settings('siteurl')."register.php?email=".self::$user_data['user_email']."&code=".self::$user_data['user_code'];
                $message = str_replace("[USER_NAME]", self::$user_data['user_name'], self::$locale['email_resend_message']);
                $message = str_replace("[SITENAME]", self::$settings['sitename'], $message);
                $message = str_replace("[ACTIVATION_LINK]", $activationUrl, $message);
                $subject = str_replace("[SITENAME]", self::$settings['sitename'], self::$locale['email_resend_subject']);

                if (!sendemail(self::$user_data['user_name'], self::$user_data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], $subject, $message)) {
                    addNotice('warning', self::$locale['u153'], 'all');
                }

                if (\defender::safe()) {
                    dbquery("UPDATE ".DB_NEW_USERS." SET user_datestamp = '".time()."' WHERE user_name=:user_name", [':user_name' => $_GET['lookup']]);
                    addNotice('success', self::$locale['u165']);
                    redirect(clean_request('', ['ref', 'lookup'], FALSE));
                }

            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }

        }

    }
}
