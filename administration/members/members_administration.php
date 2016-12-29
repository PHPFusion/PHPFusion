<?php
namespace Administration\Members;

use Administration\Members\Sub_Controllers\Display_Members;
use Administration\Members\Sub_Controllers\Members_Action;
use Administration\Members\Sub_Controllers\Members_Display;
use Administration\Members\Sub_Controllers\Members_Profile;
use PHPFusion\BreadCrumbs;
use PHPFusion\Members;
use PHPFusion\UserFields;
use PHPFusion\UserFieldsInput;

class Members_Admin {

    private static $instance = NULL;
    protected static $locale = array();
    protected static $settings = array();
    protected static $rowstart = 0;
    protected static $sortby = 'all';
    protected static $status = 0;
    protected static $usr_mysql_status = 0;
    protected static $user_id = 0;
    protected static $user_data = array();

    /*
     * Status filter links
     */
    protected static $status_uri = array();
    protected static $exit_link = '';
    protected static $is_admin = FALSE;
    protected static $time_overdue = 0;
    protected static $response_required = 0;

    const USER_MEMBER = 0;
    const USER_BAN = 1;
    const USER_REINSTATE = 2;
    const USER_SUSPEND = 3;
    const USER_SECURITY_BAN = 4;
    const USER_CANCEL = 5;
    const USER_ANON = 6;
    const USER_DEACTIVATE = 7;
    const USER_UNACTIVATED = 2;

    public function __construct() {

        self::$settings = fusion_get_settings();
        self::$time_overdue = TIME - (86400 * self::$settings['deactivation_period']);
        self::$response_required = TIME + (86400 * self::$settings['deactivation_response']);
        /*
         * LOCALE
         */
        self::$locale = fusion_get_locale('', [
            LOCALE.LOCALESET."admin/members.php",
            LOCALE.LOCALESET.'admin/members_include.php',
            LOCALE.LOCALESET."user_fields.php"
        ]);

        self::$rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0);

        self::$sortby = (isset($_GET['sortby']) ? stripinput($_GET['sortby']) : "all");

        self::$status = (isset($_GET['status']) && isnum($_GET['status'] && $_GET['status'] < 9) ? $_GET['status'] : 0);
        self::$usr_mysql_status = (isset($_GET['usr_mysql_status']) && isnum($_GET['usr_mysql_status'] && $_GET['usr_mysql_status'] < 9) ? $_GET['usr_mysql_status'] : 0);
        if (self::$status == 0 && fusion_get_settings('enable_deactivation') == 1) {
            self::$usr_mysql_status = "0' AND user_lastvisit>'".self::$time_overdue."' AND user_actiontime='0";
        } elseif (self::$status == 8 && fusion_get_settings('enable_deactivation') == 1) {
            self::$usr_mysql_status = "0' AND user_lastvisit<'".self::$time_overdue."' AND user_actiontime='0";
        }

        //self::$exit_link = FUSION_SELF.fusion_get_aidlink()."&sortby=".self::$sortby."&status=".self::$status."&rowstart=".self::$rowstart;

        $base_url = FUSION_SELF.fusion_get_aidlink();
        self::$status_uri = array(
            self::USER_MEMBER       => $base_url."&amp;status=".self::USER_MEMBER,
            self::USER_UNACTIVATED  => $base_url."&amp;status=".self::USER_UNACTIVATED,
            self::USER_BAN          => $base_url."&amp;status=".self::USER_BAN,
            self::USER_SUSPEND      => $base_url."&amp;status=".self::USER_SUSPEND,
            self::USER_SECURITY_BAN => $base_url."&amp;status=".self::USER_SECURITY_BAN,
            self::USER_CANCEL       => $base_url."&amp;status=".self::USER_CANCEL,
            self::USER_ANON         => $base_url."&amp;status=".self::USER_ANON,
            self::USER_DEACTIVATE   => $base_url."&amp;status=".self::USER_DEACTIVATE,
            'add_user' =>  $base_url.'&amp;ref=add',
            'view' =>  $base_url.'&amp;ref=view&amp;lookup=',
        );

        self::$user_id = (isset($_GET['lookup']) && dbcount('(user_id)', DB_USERS, 'user_id=:user_id', [':user_id'=>isnum($_GET['lookup']) ? $_GET['lookup'] : 0]) ? $_GET['lookup'] : 0);

        if (dbcount("(user_id)", DB_USERS, "user_id=:user_id AND user_level<:user_level", array(
                ':user_id'    => self::$user_id,
                ':user_level' => USER_LEVEL_MEMBER,
            )) > 0) {
            self::$is_admin = TRUE;
        } else {
            self::$is_admin = FALSE;
        }
    }

    public static function getInstance() {
        if (self::$instance == NULL) {
            pageAccess('M');
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function display_admin() {

        if (isset($_POST['cancel'])) {
            redirect(USER_MANAGEMENT_SELF);
        }

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'members.php'.fusion_get_aidlink(), 'title' => self::$locale['400']]);

        if (isset($_GET['ref'])) {

            switch ($_GET['ref']) {
                case 'log': // Show Logs
                    if (self::$is_admin) {
                        display_suspend_log(self::$user_id, "all", self::$rowstart);
                    }
                    break;
                case 'inactive':
                    if (!self::$user_id && fusion_get_settings('enable_deactivation') && self::$is_admin) {
                        $inactive = dbcount("(user_id)", DB_USERS,
                            "user_status='0' AND user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'".intval($time_overdue)."' AND user_actiontime='0'");
                        $action = $settings['deactivation_action'] == 0 ? $locale['616'] : $locale['615'];
                        $button = $locale['614'].($inactive == 1 ? " 1 ".$locale['612'] : " 50 ".$locale['613']);
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
                                echo "<a href='".ADMIN."settings_users.php".fusion_get_aidlink()."'>".$locale['619']."</a>";
                            }
                        }
                        echo "</div>\n<div class='tbl1 text-center'>\n";
                        echo openform('member_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;step=inactive");
                        echo form_button('cancel', $locale['418'], $locale['418'], array('class' => 'btn-primary'));
                        echo form_button('deactivate_users', $button, $button, array('class' => 'btn-primary'));
                        echo closeform();
                        echo "</div>\n";
                        closetable();
                        if (isset($_POST['deactivate_users']) && defender::safe()) {
                            require_once LOCALE.LOCALESET."admin/members_email.php";
                            require_once INCLUDES."sendmail_include.php";
                            $result = dbquery("SELECT user_id, user_name, user_email, user_password FROM ".DB_USERS."
		WHERE user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'".$time_overdue."' AND user_actiontime='0' AND user_status='0'
		LIMIT 0,50");
                            while ($data = dbarray($result)) {
                                $code = md5($response_required.$data['user_password']);
                                $message = str_replace("[CODE]", $code, $locale['email_deactivate_message']);
                                $message = str_replace("[SITENAME]", $settings['sitename'], $message);
                                $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
                                $message = str_replace("[USER_NAME]", $data['user_name'], $message);
                                $message = str_replace("[USER_ID]", $data['user_id'], $message);
                                if (sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'],
                                    $locale['email_deactivate_subject'], $message)) {
                                    $result2 = dbquery("UPDATE ".DB_USERS." SET user_status='7', user_actiontime='".$response_required."' WHERE user_id='".$data['user_id']."'");
                                    suspend_log($data['user_id'], 7, $locale['621']);
                                }
                            }
                            redirect(FUSION_SELF.fusion_get_aidlink());
                        }
                    }
                    // Deactivate Inactive Users
                    break;

                // OK
                case 'add':
                    BreadCrumbs::getInstance()->addBreadCrumb(['link' => self::$status_uri['add_user'], 'title' => self::$locale['ME_450']]);
                    opentable(self::$locale['ME_450']);
                    Members_Profile::display_new_user_form();
                    closetable();
                    break;
                case 'view':
                    if (!empty(self::$user_id)) {
                        $query = "SELECT u.*, s.suspend_reason
                                FROM ".DB_USERS." u
                                LEFT JOIN ".DB_SUSPENDS." s ON u.user_id=s.suspended_user
                                WHERE u.user_id=:user_id GROUP BY u.user_id 
                                ORDER BY s.suspend_date DESC                                
                                ";
                        $bind = array(
                            ':user_id' => self::$user_id
                        );
                        self::$user_data = dbarray(dbquery($query, $bind));
                        $title = sprintf(self::$locale['ME_451'], self::$user_data['user_name']);
                        BreadCrumbs::getInstance()->addBreadCrumb(['link' => self::$status_uri['view'].$_GET['lookup'], 'title' => $title]);
                        opentable($title);
                        Members_Profile::display_user_profile();
                        closetable();
                    } else {
                        redirect(FUSION_SELF.fusion_get_aidlink());
                    }
                    break;
                case 'edit': // Edit User Profile
                    $user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$user_id."'"));
                    if (!$user_data || $user_data['user_level'] == -103) {
                        redirect(FUSION_SELF.fusion_get_aidlink());
                    }
                    $errors = array();
                    if (isset($_POST['savechanges'])) {
                        $userInput = new \PHPFusion\UserFieldsInput();
                        $userInput->userData = $user_data;
                        $userInput->adminActivation = 0;
                        $userInput->registration = FALSE;
                        $userInput->emailVerification = 0;
                        $userInput->isAdminPanel = TRUE;
                        $userInput->skipCurrentPass = TRUE;
                        $userInput->saveUpdate();
                        $user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$user_id."'"));
                        unset($userInput);
                        if (defender::safe()) {
                            redirect(FUSION_SELF.fusion_get_aidlink());
                        }
                    }
                    opentable($locale['430']);
                    BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['430']]);
                    $userFields = new UserFields();
                    $userFields->postName = "savechanges";
                    $userFields->postValue = $locale['430'];
                    $userFields->displayValidation = 0;
                    $userFields->displayTerms = FALSE;
                    $userFields->plugin_folder = INCLUDES."user_fields/";
                    $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
                    $userFields->showAdminPass = FALSE;
                    $userFields->skipCurrentPass = TRUE;
                    $userFields->userData = $user_data;
                    $userFields->method = 'input';
                    $userFields->admin_mode = TRUE;
                    $userFields->display_profile_input();

                    closetable();
                    break;
                case 'delete':
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
                                $result = dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author='".$user_id."'");
                                $result = dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_author='".$user_id."'");
                                $result = dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user='".$user_id."'");
                                $result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id='".$user_id."'"); // Delete votes on forum threads
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
                        echo "<p>".sprintf($locale['425a'], "<strong>".$user_data['user_name']."</strong>")."</p>\n";
                        echo openform('mod_form', 'post', stripinput(USER_MANAGEMENT_SELF)."&amp;step=delete&amp;user_id=".$user_id);
                        echo form_button('delete_user', $locale['426'], $locale['426'], array('class' => 'btn-primary m-r-10'));
                        echo form_button('cancel', $locale['427'], $locale['427'], array('class' => 'btn-primary'));
                        echo closeform();
                        closetable();
                    }
                    break;
            }
        } else {
            if (isset($_POST['action']) && isset($_POST['user_id']) && is_array($_POST['user_id'])) {
                $user_action = new Members_Action();
                $user_action->set_userID((array)$_POST['user_id']);
                $user_action->set_action((string)$_POST['action']);
                $user_action->execute();
            }
            opentable(self::$locale['ME_400']);
            echo Members_Display::render_listing();
            closetable();
        }
    }

}

require_once(ADMIN.'members/members_view.php');
require_once(ADMIN.'members/sub_controllers/members_display.php');
require_once(ADMIN.'members/sub_controllers/members_action.php');
require_once(ADMIN.'members/sub_controllers/members_profile.php');

// require all the connected files here
require_once INCLUDES."suspend_include.php";