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
namespace Administration\Members\Users;

use Administration\Members\Members;
use PHPFusion\UserFields;
use PHPFusion\UserFieldsInput;

/**
 * Class Members_Profile
 * Controller for View, Add, Edit and Delete Users Account
 *
 * @package Administration\Members\Sub_Controllers
 */
class Profile {

    private $class = '';
    private $info = [];

    public function __construct($obj) {
        $this->class = $obj;
    }

    /**
     * Edit User Profile in Administration
     */
    public function editForm() {

        $locale = fusion_get_locale();
        $lookup = get('lookup', FILTER_VALIDATE_INT);
        if (fusion_get_user($lookup, 'user_id')) {
            $user = fusion_get_user($lookup);

            $userFields = new \UserFields();
            $userFields->post_name = 'update_user';
            $userFields->post_value = $locale['ME_437'];
            $userFields->display_validation = FALSE;
            $userFields->is_admin_panel = TRUE;
            $userFields->display_terms = FALSE;
            $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
            $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
            $userFields->show_admin_password = ($user['user_level'] <= USER_LEVEL_ADMIN ? TRUE : FALSE);
            $userFields->skip_password = TRUE;
            $userFields->inline_field = TRUE;
            $userFields->method = 'input';
            $userFields->user_data = $user;
            $userFields->user_name_change = TRUE;

            $userInput = new \UserFieldsInput();
            $userInput->admin_activation = FALSE;
            $userInput->registration = FALSE;
            $userInput->email_verification = FALSE;
            $userInput->is_admin_panel = TRUE;
            $userInput->skip_password = TRUE;
            $userInput->user_data = $user;
            $userInput->post_name = 'update_user';
            $userInput->saveUpdate();

            $this->info = $userFields->get_input_info();
            //display_profile_form($info);
            $this->display_register_form();
        } else {
            addNotice('danger', "There are no user by found by the user id");
            redirect(clean_request('', ['ref', 'lookup'], FALSE));
        }
    }

    /*
     * Displays new user form
     */
    public function display_new_user_form() {
        $locale = fusion_get_locale();
        $userFields = new UserFields();
        $userFields->post_name = "add_new_user";
        $userFields->post_value = $locale['ME_450'];
        $userFields->display_validation = FALSE;
        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->show_admin_password = FALSE;
        $userFields->skip_password = TRUE;
        $userFields->registration = TRUE;
        $userFields->method = 'input';
        $userFields->inline_field = FALSE;

        $userInput = new UserFieldsInput();
        $userInput->validation = FALSE;
        $userInput->email_verification = FALSE;
        $userInput->admin_activation = FALSE;
        $userInput->registration = TRUE;
        $userInput->skip_password = TRUE;
        $userInput->post_name = 'add_new_user';
        $userInput->redirect_uri = ADMIN.'members.php'.fusion_get_aidlink();
        $userInput->saveInsert();

        self::$info = $userFields->get_input_info();

        self::display_register_form();
    }

    public function display_register_form() {
        echo $this->info['openform'];
        echo "<div class='".grid_row()."'>";
        echo "<div class='".grid_column_size(100,100,100,80)."'>\n";
        if ($this->info['user_password_notice']) {
            echo "<div class='alert alert-warning'>".$this->info['user_password_notice']."</div>\n";
        }
        if (isset($this->info['user_admin_password_notice'])) {
            echo "<div class='alert alert-danger'>".$this->info['user_admin_password_notice']."</div>\n";
        }
        echo "<div class='spacer-sm'>\n";
        if ($this->info['user_avatar']) {
            echo $this->info['user_avatar'];
        }
        echo $this->info['user_name'];
        echo $this->info['user_email'];
        echo $this->info['user_password'];
        if ($this->info['user_admin_password']) {
            echo $this->info['user_admin_password'];
        }
        if ($this->info['user_reputation']) {
            echo $this->info['user_reputation'];
        }
        $opentab = '';
        $closetab = '';
        $tabpages = [];
        $tab_content = '';
        if (!empty($this->info['section'])) {
            $tab = new \FusionTabs();
            $tab->set_remember(TRUE);

            foreach ($this->info['section'] as $tab_id => $tabdata) {
                $tabpages['title'][$tab_id] = $tabdata['name'];
                $tabpages['id'][$tab_id] = $tabdata['id'];
            }
            reset($this->info['section']);
            $default_active = key($this->info['section']);
            $tab_active = $tab::tab_active($tabpages, $default_active);
            $tab_content = '';
            foreach ($this->info['section'] as $tab_id => $tabdata) {
                $user_field = '';
                if (isset($this->info['user_field'][$tab_id])) {
                    foreach ($this->info['user_field'][$tab_id] as $cat_id => $field_prop) {
                        $user_field .= "<h4>\n".$field_prop['title']."</h4>";
                        $user_field .= implode('', $field_prop['fields']);
                    }
                }
                $tab_content .= $tab->opentabbody($tabdata['id'], $tab_active).$user_field.$tab->closetabbody();
            }
            $opentab = $tab->opentab($tabpages, $tab_active, 'admin_registration', FALSE, 'nav-tabs nav-stacked');
            $closetab = $tab->closetab();
        }
        echo $opentab.$tab_content.$closetab;
        echo $this->info['button'];
        echo "</div>\n";
        echo "</div>\n<div class='".grid_column_size(100,100,100,20)."'>\n";
        echo "</div>\n</div>\n";

        echo $this->info['closeform'];
    }

    /*
     * Displays user profile
     */
    public function display_user_profile() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $userFields = new UserFields();
        $userFields->post_name = "register";
        $userFields->post_value = $locale['u101'];
        $userFields->display_validation = $settings['display_validation'];
        $userFields->display_terms = $settings['enable_terms'];
        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->show_admin_password = FALSE;
        $userFields->skip_password = TRUE;
        $userFields->registration = FALSE;
        $userFields->user_data = self::$user_data;
        $userFields->method = 'display';
        $userFields->display_profile_output();
    }

    public function delete_user() {
        $locale = fusion_get_locale();
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
        echo "<h4>".$locale['ME_454']."</h4>";
        echo "<p>".nl2br(sprintf($locale['ME_455'], "<strong>".self::$user_data['user_name']."</strong>"))."</p>\n";
        echo openform('mod_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;ref=delete&amp;lookup=".self::$user_id."");
        echo "<div class='spacer-sm'>\n";
        echo form_button('delete_user', $locale['ME_456'], $locale['ME_456'], ['class' => 'btn-danger m-r-10']);
        echo form_button('cancel', $locale['cancel'], $locale['cancel']);
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
    }

    public function delete_unactivated_user() {
        $locale = fusion_get_locale();
        if (isset($_POST['delete_newuser'])) {
            dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_name=:user_name", [':user_name' => $_GET['lookup']]);
            redirect(clean_request('', ['ref', 'lookup', 'newuser'], FALSE));

        }

        echo "<div class='well'>\n";
        echo "<h4>".$locale['ME_454']."</h4>";
        echo "<p>".nl2br(sprintf($locale['ME_457'], "<strong>".$_GET['lookup']."</strong>"))."</p>\n";
        echo openform('mod_form', 'post', FUSION_REQUEST);
        echo "<div class='spacer-sm'>\n";
        echo form_button('delete_newuser', $locale['ME_456'], $locale['ME_456'], ['class' => 'btn-danger m-r-10']);
        echo form_button('cancel', $locale['cancel'], $locale['cancel']);
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
    }
}
