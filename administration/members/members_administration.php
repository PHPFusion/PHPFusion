<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: members_administration.php
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
namespace Administration\Members;

use Administration\Members\Sub_Controllers\Members_Action;
use Administration\Members\Sub_Controllers\Members_Display;
use Administration\Members\Sub_Controllers\Members_Profile;

class Members_Admin {

    const USER_MEMBER = 0;
    const USER_BAN = 1;
    const USER_REINSTATE = 2;
    const USER_SUSPEND = 3;
    const USER_SECURITY_BAN = 4;
    const USER_CANCEL = 5;
    const USER_ANON = 6;
    const USER_DEACTIVATE = 7;
    const USER_UNACTIVATED = 2;

    /*
     * Status filter links
     */
    protected static $locale = [];
    protected static $settings = [];
    protected static $rowstart = 0;
    protected static $sortby = 'all';
    protected static $status = 0;
    protected static $usr_mysql_status = 0;
    protected static $user_id = 0;
    protected static $user_data = [];
    protected static $status_uri = [];
    protected static $exit_link = '';
    protected static $is_admin = FALSE;
    protected static $time_overdue = 0;
    protected static $response_required = 0;
    protected static $deactivation_period = 0;
    private static $instance = NULL;

    public function __construct() {

        self::$settings = fusion_get_settings();
        self::$time_overdue = time() - (86400 * self::$settings['deactivation_period']);
        self::$response_required = time() + (86400 * self::$settings['deactivation_response']);
        self::$deactivation_period = self::$settings['deactivation_period'];

        /*
         * LOCALE
         */
        self::$locale = fusion_get_locale('', [
            LOCALE.LOCALESET."admin/members.php",
            LOCALE.LOCALESET.'admin/members_include.php',
            LOCALE.LOCALESET.'admin/members_email.php',
            LOCALE.LOCALESET."user_fields.php"
        ]);

        self::$rowstart = get('rowstart', FILTER_SANITIZE_NUMBER_INT);
        self::$sortby = check_get('sortby') ? stripinput(get('sortby')) : "all";
        self::$status = check_get('status') && get('status', FILTER_SANITIZE_NUMBER_INT) < 9 ? get('status') : 0;
        self::$usr_mysql_status = check_get('usr_mysql_status') && get('usr_mysql_status', FILTER_SANITIZE_NUMBER_INT) < 9 ? get('usr_mysql_status') : 0;

        if (self::$status == 0 && fusion_get_settings('enable_deactivation') == 1) {
            self::$usr_mysql_status = "0' AND user_lastvisit>'".self::$time_overdue."' AND user_actiontime='0";
        } else if (self::$status == 8 && fusion_get_settings('enable_deactivation') == 1) {
            self::$usr_mysql_status = "0' AND user_lastvisit<'".self::$time_overdue."' AND user_actiontime='0";
        }

        self::$exit_link = FUSION_SELF.fusion_get_aidlink()."&sortby=".self::$sortby."&status=".self::$status."&rowstart=".self::$rowstart;

        $base_url = FUSION_SELF.fusion_get_aidlink();
        self::$status_uri = [
            self::USER_MEMBER       => $base_url."&amp;status=".self::USER_MEMBER,
            self::USER_UNACTIVATED  => $base_url."&amp;status=".self::USER_UNACTIVATED,
            self::USER_BAN          => $base_url."&amp;status=".self::USER_BAN,
            self::USER_SUSPEND      => $base_url."&amp;status=".self::USER_SUSPEND,
            self::USER_SECURITY_BAN => $base_url."&amp;status=".self::USER_SECURITY_BAN,
            self::USER_CANCEL       => $base_url."&amp;status=".self::USER_CANCEL,
            self::USER_ANON         => $base_url."&amp;status=".self::USER_ANON,
            self::USER_DEACTIVATE   => $base_url."&amp;status=".self::USER_DEACTIVATE,
            'add_user'              => $base_url.'&amp;ref=add',
            'view'                  => $base_url.'&amp;ref=view&amp;lookup=',
            'edit'                  => $base_url.'&amp;ref=edit&amp;lookup=',
            'delete'                => $base_url.'&amp;ref=delete&amp;lookup=',
            'login_as'              => $base_url."&amp;ref=login&amp;lookup=",
            'inactive'              => $base_url.'&amp;ref=inactive',
            'resend'                => $base_url.'&amp;ref=resend&amp;lookup=',
            'activate'              => $base_url.'&amp;ref=activate&amp;lookup=',
            'reactivate'            => $base_url.'&amp;ref=reactivate&amp;lookup=',
        ];

        self::$user_id = (check_get('lookup') && dbcount('(user_id)', DB_USERS, 'user_id=:user_id', [':user_id' => get('lookup')]) ? get('lookup') : 0);

        self::$is_admin = FALSE;

        if (dbcount("(user_id)", DB_USERS, "user_id=:user_id AND user_level<:user_level", [
                ':user_id'    => self::$user_id,
                ':user_level' => USER_LEVEL_MEMBER,
            ]) > 0
        ) {
            self::$is_admin = TRUE;
        }

    }

    public static function getInstance() {
        if (self::$instance == NULL) {
            pageaccess('M');
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function display_admin() {

        $settings = fusion_get_settings();

        $aidlink = fusion_get_aidlink();

        if (post("cancel")) {
            redirect(self::$exit_link);
        }

        add_breadcrumb(['link' => ADMIN.'members.php'.fusion_get_aidlink(), 'title' => self::$locale['ME_400']]);

        if (check_get("ref")) {

            switch (get("ref")) {

                case "log": // Show Logs
                    if (!self::$is_admin) {
                        display_suspend_log(self::$user_id, "all", self::$rowstart);
                    }
                    break;
                case "login":
                    if ($user = fusion_get_user(self::$user_id)) {
                        if ($user["user_id"]) {
                            if (fusion_get_userdata("user_level") <= $user["user_level"] && fusion_get_userdata("user_id") != $user["user_id"]) {
                                session_add("login_as", $user["user_id"]);
                                redirect(BASEDIR.$settings["opening_page"]);
                            }
                        }
                    }
                    redirect(FUSION_REQUEST);
                    break;
                case 'inactive':

                    if (!self::$user_id && fusion_get_settings('enable_deactivation') && self::$is_admin) {
                        $inactive = dbcount("(user_id)", DB_USERS,
                            "user_status='0' AND user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit <:last_visited AND user_actiontime=:action_time",
                            [
                                ':last_visited' => self::$time_overdue,
                                ':action_time'  => 0,
                            ]
                        );
                        $button = self::$locale['ME_502'].format_word($inactive, self::$locale['fmt_user']);
                        if (!$inactive) {
                            addnotice('success', self::$locale['ME_460']);
                            redirect(FUSION_SELF.fusion_get_aidlink());
                        }

                        if (check_post('deactivate_users') && fusion_safe()) {
                            require_once INCLUDES."sendmail_include.php";
                            $result = dbquery("SELECT user_id, user_name, user_email, user_password FROM ".DB_USERS."
                                        WHERE user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'".self::$time_overdue."' AND user_actiontime='0' AND user_status='0'
                                        LIMIT 0,50
                                        ");
                            $rows = dbrows($result);
                            if ($rows != '0') {
                                while ($data = dbarray($result)) {
                                    $message = strtr(self::$locale['email_deactivate_message'], [
                                            '[CODE]'         => md5(self::$response_required.$data['user_password']),
                                            '[SITENAME]'     => self::$settings['sitename'],
                                            '[SITEUSERNAME]' => self::$settings['siteusername'],
                                            '[USER_NAME]'    => $data['user_name'],
                                            '[USER_ID]'      => $data['user_id'],
                                        ]
                                    );
                                    if (sendemail($data['user_name'], $data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], self::$locale['email_deactivate_subject'], $message)) {
                                        dbquery("UPDATE ".DB_USERS." SET user_status='7', user_actiontime='".self::$response_required."' WHERE user_id='".$data['user_id']."'");
                                        suspend_log($data['user_id'], self::USER_DEACTIVATE, self::$locale['ME_468']);
                                    }
                                }
                                addnotice('success', sprintf(self::$locale['ME_461'], format_word($rows, self::$locale['fmt_user'])));
                                redirect(FUSION_SELF.fusion_get_aidlink());
                            }
                        }

                        // Put this into view.
                        add_breadcrumb(['link' => self::$status_uri['inactive'], 'title' => self::$locale['ME_462']]);
                        opentable(self::$locale['ME_462']);
                        if ($inactive > 50) {
                            addnotice('info', sprintf(self::$locale['ME_463'], floor($inactive / 50)));
                        }
                        echo "<div>";
                        $action = fusion_get_settings('deactivation_action') == 0 ? self::$locale['ME_556'] : self::$locale['ME_557'];
                        $text = sprintf(self::$locale['ME_464'], $inactive, self::$settings['deactivation_period'], self::$settings['deactivation_response'], $action);
                        echo str_replace(["[strong]", "[/strong]"], ["<strong>", "</strong>"],
                            $text
                        );
                        if (self::$settings['deactivation_action'] == 1) {
                            echo "<br />\n".self::$locale['ME_465'];
                            echo "</div>\n<div class='admin-message alert alert-warning m-t-10'><strong>".self::$locale['ME_454']."</strong>\n".self::$locale['ME_466']."\n";
                            if (checkrights('S9')) {
                                echo "<a href='".ADMIN."settings_users.php".fusion_get_aidlink()."'>".self::$locale['ME_467']."</a>";
                            }
                        }
                        echo "</div>\n<div class='text-center'>\n";
                        echo openform('member_form', 'post', self::$status_uri['inactive']);
                        echo form_button('deactivate_users', $button, $button, ['class' => 'btn-primary m-r-10']);
                        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
                        echo closeform();
                        echo "</div>\n";
                        closetable();
                    }
                    break;
                case 'add':
                    add_breadcrumb(['link' => self::$status_uri['add_user'], 'title' => self::$locale['ME_450']]);
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
                        $bind = [
                            ':user_id' => self::$user_id
                        ];
                        self::$user_data = dbarray(dbquery($query, $bind));
                        $title = sprintf(self::$locale['ME_451'], self::$user_data['user_name']);
                        add_breadcrumb(['link' => self::$status_uri['view'].self::$user_id, 'title' => $title]);
                        opentable($title);
                        Members_Profile::display_user_profile();
                        closetable();
                    } else {
                        redirect(FUSION_SELF.$aidlink);
                    }
                    break;
                case 'edit': // Edit User Profile
                    if (!empty(self::$user_id)) {
                        self::$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:user_id", [':user_id' => self::$user_id]));
                        if (empty(self::$user_data) || self::$user_data['user_level'] <= USER_LEVEL_SUPER_ADMIN) {
                            redirect(FUSION_SELF.fusion_get_aidlink());
                        }
                        $title = sprintf(self::$locale['ME_452'], self::$user_data['user_name']);
                        add_breadcrumb(['link' => self::$status_uri['view'].self::$user_id, 'title' => $title]);
                        opentable($title);
                        Members_Profile::edit_user_profile();
                        closetable();
                    } else {
                        redirect(FUSION_SELF.$aidlink);
                    }
                    break;
                case 'delete':
                    if (!empty(get('newuser'))) {
                        opentable(sprintf(self::$locale['ME_453'], get('lookup')));
                        Members_Profile::delete_unactivated_user();
                        closetable();

                    } else if (!empty(self::$user_id)) {
                        self::$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:user_id", [':user_id' => self::$user_id]));
                        if (empty(self::$user_data) || self::$user_data['user_level'] <= USER_LEVEL_SUPER_ADMIN) {
                            redirect(FUSION_SELF.$aidlink);
                        }
                        opentable(sprintf(self::$locale['ME_453'], self::$user_data['user_name']));
                        Members_Profile::delete_user();
                        closetable();
                    } else {
                        redirect(FUSION_SELF.fusion_get_aidlink());
                    }
                    break;
                case 'resend':
                    if (get("lookup")) {
                        Members_Profile::resend_email();
                    } else {
                        redirect(FUSION_SELF.$aidlink);
                    }
                    break;
                case 'activate':
                    if (get("lookup") && get("code")) {
                        Members_Profile::activate_user();
                    } else {
                        redirect(FUSION_SELF.$aidlink);
                    }
                case 'reactivate':
                    if (get("lookup")) {
                        Members_Profile::reactivate_user();
                    } else {
                        redirect(FUSION_SELF.$aidlink);
                    }
            }
        } else {

            if (isset($_REQUEST['action']) && isset($_REQUEST['user_id']) || isset($_REQUEST['lookup'])) {
                $user_action = new Members_Action();
                if (isset($_REQUEST['lookup']) && !is_array($_REQUEST['lookup'])) {
                    $_REQUEST['lookup'] = [$_REQUEST['lookup']];
                }
                $user_action->set_userID((array)(isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $_REQUEST['lookup']));
                if (isset($_REQUEST['action'])) {
                    $user_action->set_action((string)$_REQUEST['action']);
                }
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
require_once(INCLUDES.'suspend_include.php');
