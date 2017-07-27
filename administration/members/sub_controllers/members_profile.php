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
        $userFields->plugin_folder = INCLUDES."user_fields/";
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
        $userFields->plugin_folder = INCLUDES."user_fields/";
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
        $userFields->plugin_folder = INCLUDES."user_fields/";
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
                array(
                    ':user_id'    => self::$user_id,
                    ':user_level' => USER_LEVEL_SUPER_ADMIN
                )
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
                if (infusion_exists('gallery')) {
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
                dbquery("DELETE FROM ".DB_USERS." WHERE user_id='".$user_id."'");
                dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_name='".$user_id."'");
                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_user='".$user_id."'");
                dbquery("DELETE FROM ".DB_SUSPENDS." WHERE suspended_user='".$user_id."'");
                dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_to='".$user_id."' OR message_from='".$user_id."'");

                if (db_exists(DB_ARTICLES)) {
                    dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_name='".$user_id."'");
                }
                if (db_exists(DB_NEWS)) {
                    dbquery("DELETE FROM ".DB_NEWS." WHERE news_name='".$user_id."'");
                }
                if (db_exists(DB_POLL_VOTES)) {
                    dbquery("DELETE FROM ".DB_POLL_VOTES." WHERE vote_user='".$user_id."'");
                }
                if (db_exists(DB_FORUMS)) {
                    dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author='".$user_id."'");
                    dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_author='".$user_id."'");
                    dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user='".$user_id."'");
                    dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id='".$user_id."'"); // Delete votes on forum threads
                    $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_lastuser='".$user_id."'");
                    if (dbrows($threads)) {
                        while ($thread = dbarray($threads)) {
                            // Update thread last post author, date and id
                            $last_thread_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread['thread_id']."' ORDER BY post_id DESC LIMIT 0,1"));
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET
                            thread_lastpost='".$last_thread_post['post_datestamp']."',
							thread_lastpostid='".$last_thread_post['post_id']."',
							thread_lastuser='".$last_thread_post['post_author']."'
							WHERE thread_id='".$thread['thread_id']."'"
                            );
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
        echo form_button('delete_user', self::$locale['ME_456'], self::$locale['ME_456'], array('class' => 'btn-danger m-r-10'));
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
    }

    public static function delete_unactivated_user() {        if (isset($_POST['delete_newuser'])) {        dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_name='".$_GET['lookup']."'");
        redirect(clean_request('', array('ref', 'lookup', 'newuser'), FALSE));
        }
        echo "<div class='well'>\n";
        echo "<h4>".self::$locale['ME_454']."</h4>";
        echo "<p>".nl2br(sprintf(self::$locale['ME_457'], "<strong>".$_GET['lookup']."</strong>"))."</p>\n";
        echo openform('mod_form', 'post', FUSION_REQUEST);
        echo "<div class='spacer-sm'>\n";
        echo form_button('delete_newuser', self::$locale['ME_456'], self::$locale['ME_456'], array('class' => 'btn-danger m-r-10'));
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
    }
}
