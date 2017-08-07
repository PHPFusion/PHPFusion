<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members/members_administration.php
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
namespace Administration\Members;

use Administration\Members\Sub_Controllers\Display_Members;
use Administration\Members\Sub_Controllers\Members_Action;
use Administration\Members\Sub_Controllers\Members_Display;
use Administration\Members\Sub_Controllers\Members_Profile;
use PHPFusion\BreadCrumbs;

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
    protected static $deactivation_period = 0;

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

        self::$rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0);
        self::$sortby = (isset($_GET['sortby']) ? stripinput($_GET['sortby']) : "all");
        self::$status = (isset($_GET['status']) && isnum($_GET['status'] && $_GET['status'] < 9) ? $_GET['status'] : 0);

        self::$usr_mysql_status = (isset($_GET['usr_mysql_status']) && isnum($_GET['usr_mysql_status'] && $_GET['usr_mysql_status'] < 9) ? $_GET['usr_mysql_status'] : 0);
        if (self::$status == 0 && fusion_get_settings('enable_deactivation') == 1) {
            self::$usr_mysql_status = "0' AND user_lastvisit>'".self::$time_overdue."' AND user_actiontime='0";
        } elseif (self::$status == 8 && fusion_get_settings('enable_deactivation') == 1) {
            self::$usr_mysql_status = "0' AND user_lastvisit<'".self::$time_overdue."' AND user_actiontime='0";
        }

        self::$exit_link = FUSION_SELF.fusion_get_aidlink()."&sortby=".self::$sortby."&status=".self::$status."&rowstart=".self::$rowstart;

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
            'add_user'              => $base_url.'&amp;ref=add',
            'view'                  => $base_url.'&amp;ref=view&amp;lookup=',
            'edit'                  => $base_url.'&amp;ref=edit&amp;lookup=',
            'delete'                => $base_url.'&amp;ref=delete&amp;lookup=',
            'inactive'              => $base_url.'&amp;ref=inactive',
        );

        self::$user_id = (isset($_GET['lookup']) && dbcount('(user_id)', DB_USERS, 'user_id=:user_id', [':user_id' => isnum($_GET['lookup']) ? $_GET['lookup'] : 0]) ? $_GET['lookup'] : 0);

        if (dbcount("(user_id)", DB_USERS, "user_id=:user_id AND user_level<:user_level", array(
                ':user_id'    => self::$user_id,
                ':user_level' => USER_LEVEL_MEMBER,
            )) > 0
        ) {
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
            redirect(self::$exit_link);
        }

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'members.php'.fusion_get_aidlink(), 'title' => self::$locale['ME_400']]);

        if (isset($_GET['ref'])) {
            switch ($_GET['ref']) {
                case 'log': // Show Logs
                    if (!self::$is_admin) {
                        display_suspend_log(self::$user_id, "all", self::$rowstart);
                    }
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
                            addNotice('success', self::$locale['ME_460']);
                            redirect(FUSION_SELF.fusion_get_aidlink());
                        }

                        if (isset($_POST['deactivate_users']) && defender::safe()) {
                            require_once INCLUDES."sendmail_include.php";
                            $result = dbquery("SELECT user_id, user_name, user_email, user_password FROM ".DB_USERS."
                                        WHERE user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'".self::$time_overdue."' AND user_actiontime='0' AND user_status='0'
                                        LIMIT 0,50
                                        ");
                            $rows = dbrows($result);
                            if ($rows != '0') {
                                while ($data = dbarray($result)) {
                                    $message = strtr(self::$locale['email_deactivate_message'], array(
                                            '[CODE]'         => md5(self::$response_required.$data['user_password']),
                                            '[SITENAME]'     => self::$settings['sitename'],
                                            '[SITEUSERNAME]' => self::$settings['siteusername'],
                                            '[USER_NAME]'    => $data['user_name'],
                                            '[USER_ID]'      => $data['user_id'],
                                        )
                                    );
                                    if (sendemail($data['user_name'], $data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], self::$locale['email_deactivate_subject'], $message)) {
                                        dbquery("UPDATE ".DB_USERS." SET user_status='7', user_actiontime='".self::$response_required."' WHERE user_id='".$data['user_id']."'");
                                        suspend_log($data['user_id'], self::USER_DEACTIVATE, self::$locale['ME_468']);
                                    }
                                }
                                addNotice('success', sprintf(self::$locale['ME_461'], format_word($rows, self::$locale['fmt_user'])));
                                redirect(FUSION_SELF.fusion_get_aidlink());
                            }
                        }

                        // Put this into view.
                        BreadCrumbs::getInstance()->addBreadCrumb(['link' => self::$status_uri['inactive'], 'title' => self::$locale['ME_462']]);
                        opentable(self::$locale['ME_462']);
                        if ($inactive > 50) {
                            addNotice('info', sprintf(self::$locale['ME_463'], floor($inactive / 50)));
                        }
                        echo "<div>";
                        $action = fusion_get_settings('deactivation_action') == 0 ? self::$locale['ME_556'] : self::$locale['ME_557'];
                        $text = sprintf(self::$locale['ME_464'], $inactive, self::$settings['deactivation_period'], self::$settings['deactivation_response'], $action);
                        echo str_replace(array("[strong]", "[/strong]"), array("<strong>", "</strong>"),
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
                        echo form_button('deactivate_users', $button, $button, array('class' => 'btn-primary m-r-10'));
                        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
                        echo closeform();
                        echo "</div>\n";
                        closetable();
                    }
                    break;
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
                    if (!empty(self::$user_id)) {
                        self::$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:user_id", [':user_id' => self::$user_id]));
                        if (empty(self::$user_data) || self::$user_data['user_level'] <= USER_LEVEL_SUPER_ADMIN) {
                            redirect(FUSION_SELF.fusion_get_aidlink());
                        }
                        $title = sprintf(self::$locale['ME_452'], self::$user_data['user_name']);
                        BreadCrumbs::getInstance()->addBreadCrumb(['link' => self::$status_uri['view'].$_GET['lookup'], 'title' => $title]);
                        opentable($title);
                        Members_Profile::edit_user_profile();
                        closetable();
                    } else {
                        redirect(FUSION_SELF.fusion_get_aidlink());
                    }
                    break;
                case 'delete':
                    if (!empty($_GET['newuser'])) {
                        opentable(sprintf(self::$locale['ME_453'], $_GET['lookup']));
                        Members_Profile::delete_unactivated_user();
                        closetable();

                    }
                    elseif (!empty(self::$user_id)) {
                        self::$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:user_id", [':user_id' => self::$user_id]));
                        if (empty(self::$user_data) || self::$user_data['user_level'] <= USER_LEVEL_SUPER_ADMIN) {
                            redirect(FUSION_SELF.fusion_get_aidlink());
                        }
                        opentable(sprintf(self::$locale['ME_453'], self::$user_data['user_name']));
                        Members_Profile::delete_user();
                        closetable();
                    } else {
                        redirect(FUSION_SELF.fusion_get_aidlink());
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
require_once(INCLUDES.'suspend_include.php');
