<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_reset.php
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
pageAccess('APWR');
require_once THEMES."templates/admin_header.php";

class admin_reset_admin {
    private static $locale = array();

    private $data = array(
        'reset_id'        => 0,
        'reset_admin_id'  => '',
        'reset_timestamp' => '',
        'reset_sucess'    => '',
        'reset_failed'    => '',
        'reset_admins'    => '',
        'reset_reason'    => ''
    );

    public function __construct() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/admin_reset.php");
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($_GET['action']) {
            case 'delete':
                self::delete_admin_reset($_GET['reset_id']);
                break;
            default:
                break;
        }

       \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link'=> ADMIN.'admin_reset.php'.fusion_get_aidlink(), "title"=> self::$locale['apw_title']]);
        self::set_adminsdb();
    }

    private function set_adminsdb() {
        if (isset($_POST['reset_admins'])) {
            require_once INCLUDES."sendmail_include.php";
            $aidlink = fusion_get_aidlink();
            $userdata = fusion_get_userdata();
            $reset_message = form_sanitizer($_POST['reset_message'], '', 'reset_message');
            $reset_admin = form_sanitizer($_POST['reset_admin'], '', 'reset_admin');
            $reset_login = isset($_POST['reset_login']) ? $_POST['reset_login'] : FALSE;
            $reset_success = array();
            $reset_failed = array();

            if (\defender::safe()) {
                $user_sql = (isnum($reset_admin) ? "user_id='".$reset_admin."'" :
                    ($reset_admin == "all" ? "user_level=".USER_LEVEL_ADMIN." OR user_level=".USER_LEVEL_SUPER_ADMIN :
                    ($reset_admin == "sa" ? "user_level=".USER_LEVEL_SUPER_ADMIN :
                    ($reset_admin == "a" ? "user_level=".USER_LEVEL_ADMIN :
                    ""
                ))));

                $result = dbquery("SELECT user_id, user_name, user_email, user_language
                    FROM ".DB_USERS."
                    WHERE ".$user_sql."
                    ORDER BY user_level DESC, user_id"
                );

                while ($data = dbarray($result)) {
                    $loginPassIsReset = FALSE;
                    $adminPassIsReset = FALSE;
                    $adminPass = new PasswordAuth();
                    $newLoginPass = "";
                    $newAdminPass = $adminPass->getNewPassword(12);
                    $adminPass->inputNewPassword = $newAdminPass;
                    $adminPass->inputNewPassword2 = $newAdminPass;
                    $adminPassIsReset = ($adminPass->isValidNewPassword() === 0 ? TRUE : FALSE);

                    if (!empty($reset_login)) {
                        $loginPass = new PasswordAuth();
                        $newLoginPass = $loginPass->getNewPassword(12);
                        $loginPass->inputNewPassword = $newLoginPass;
                        $loginPass->inputNewPassword2 = $newLoginPass;
                        $message = str_replace(
                            array(
                                "[SITEURL]",
                                "[USER_NAME]",
                                "[NEW_PASS]",
                                "[NEW_ADMIN_PASS]",
                                "[ADMIN]",
                                "[RESET_MESSAGE]"
                            ),
                            array(
                                "<a href='".fusion_get_settings("siteurl")."'>".fusion_get_settings("sitename")."</a>",
                                $data['user_name'],
                                $newLoginPass,
                                $newAdminPass,
                                $userdata['user_name'],
                                $reset_message
                            ), fusion_get_locale('apw_409', LOCALE.$data['user_language']."/admin/admin_reset.php"));
                        $loginPassIsReset = ($loginPass->isValidNewPassword() === 0 ? TRUE : FALSE);
                    } else {
                        $message = str_replace(
                            array(
                                "[SITEURL]",
                                "[USER_NAME]",
                                "[NEW_ADMIN_PASS]",
                                "[ADMIN]",
                                "[RESET_MESSAGE]"
                            ),
                            array(
                                "<a href='".fusion_get_settings('siteurl')."'>".fusion_get_settings('sitename')."</a>",
                                $data['user_name'],
                                $newAdminPass,
                                $userdata['user_name'],
                                $reset_message
                            ), fusion_get_locale('apw_408', LOCALE.$data['user_language']."/admin/admin_reset.php")
                        );
                        $loginPassIsReset = TRUE;
                    }

                    if ($loginPassIsReset && $adminPassIsReset && sendemail($data['user_name'], $data['user_email'], $userdata['user_name'], $userdata['user_email'], self::$locale['apw_407'].fusion_get_settings('sitename'), $message)) {
                        $result2 = dbquery("UPDATE ".DB_USERS." SET
                            ".($newLoginPass ? "user_algo='".$loginPass->getNewAlgo()."', user_salt='".$loginPass->getNewSalt()."',
                            user_password='".$loginPass->getNewHash()."', " : "")."
                            user_admin_algo='".$adminPass->getNewAlgo()."', user_admin_salt='".$adminPass->getNewSalt()."',
                            user_admin_password='".$adminPass->getNewHash()."'
                            WHERE user_id='".$data['user_id']."'");
                        $reset_success[] = array('user_id' => $data['user_id'], 'user_name' => $data['user_name'], 'user_email' => $data['user_email']);
                    } else {
                        $reset_failed[] = array('user_id' => $data['user_id'], 'user_name' => $data['user_name'], 'user_email' => $data['user_email']);
                    }
                }

                $sucess_ids = "";
                $failed_ids = "";
                $text = "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
                    if (!empty($reset_success)) {
                        foreach ($reset_success as $key => $info) {
                            $sucess_ids .= $sucess_ids != "" ? ".".$info['user_id'] : $info['user_id'];
                            $text .= "<tr>\n";
                            $text .= "<td class='col-xs-2'><strong>".($key == 0 ? self::$locale['apw_424'] : "")."</strong></td>\n";
                            $text .= "<td class='col-xs-2'>".$info['user_name']." (".$info['user_email'].")</td>\n";
                            $text .= "</tr>\n";
                        }
                    }

                    if (!empty($reset_failed)) {
                        foreach ($reset_failed as $key => $info) {
                            $failed_ids .= $failed_ids != "" ? ".".$info['user_id'] : $info['user_id'];
                            $text .= "<tr>\n";
                            $text .= "<td class='col-xs-2'><strong>".($key == 0 ? self::$locale['apw_425'] : "")."</strong></td>\n";
                            $text .= "<td class='col-xs-2'>".$info['user_name']." (".$info['user_email'].")</td>\n";
                            $text .= "</tr>\n";
                        }
                    }

                $text .= "</table>\n</div>";
                $preview_html = openmodal('apw_preview', self::$locale['apw_410']);
                $preview_html .= "<p>".$text."</p>\n";
                $preview_html .= closemodal();
                add_to_footer($preview_html);

                $this->data = array(
                    'reset_id'        => 0,
                    'reset_admin_id'  => $userdata['user_id'],
                    'reset_timestamp' => time(),
                    'reset_sucess'    => $sucess_ids,
                    'reset_failed'    => $failed_ids,
                    'reset_admins'    => $reset_admin,
                    'reset_reason'    => $reset_message
                );

                dbquery_insert(DB_ADMIN_RESETLOG, $this->data, 'save');
                addNotice('success', self::$locale['apw_411']);
            }
        }
    }

    static function load_admins() {
        $list = array();
        $list = self::admin_title();

        $result = dbquery("SELECT user_id, user_name, user_level
            FROM ".DB_USERS."
            WHERE user_level<=".USER_LEVEL_ADMIN."
            ORDER BY user_level DESC, user_name"
        );

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $list[$data['user_id']] = $data['user_name'];
            }
        }

        return (array)$list;
    }

    static function load_all_admin_reset() {
        $list = array();
        $result = dbquery("SELECT arl.*, u1.user_status, u1.user_name, u1.user_id, u2.user_name as user_name_reset, u2.user_id as user_id_reset, u2.user_status as user_status_reset
            FROM ".DB_ADMIN_RESETLOG." arl
            LEFT JOIN ".DB_USERS." u1 ON arl.reset_admin_id=u1.user_id
            LEFT JOIN ".DB_USERS." u2 ON arl.reset_admins=u2.user_id
            ORDER BY arl.reset_timestamp DESC"
        );

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $list[] = $data;
            }
        }

        return (array)$list;
    }

    static function admin_title() {
        return array("all" => self::$locale['apw_401'], "sa" => self::$locale['apw_402'], "a" => self::$locale['apw_403']);
    }

    static function verify_admin_reset($id) {
        if (isnum($id)) {
            return dbcount("(reset_id)", DB_ADMIN_RESETLOG, "reset_id='".intval($id)."'");
        }

        return FALSE;
    }

    private static function delete_admin_reset($id) {
        if (self::verify_admin_reset($id)) {
            dbquery("DELETE FROM ".DB_ADMIN_RESETLOG." WHERE reset_id='".intval($id)."'");
            addNotice('warning', self::$locale['apw_429']);
            redirect(clean_request("", array("section=smiley_list", "aid"), TRUE));
        }
    }

    public function display_admin() {
        opentable(self::$locale['apw_title']);
            $allowed_section = array("adminreset_form", "adminreset_list");
            $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'adminreset_list';

            $tab_title['title'][] = self::$locale['apw_415'];
            $tab_title['id'][] = 'adminreset_list';
            $tab_title['icon'][] = "";

            $tab_title['title'][] = self::$locale['apw_title'];
            $tab_title['id'][] = 'adminreset_form';
            $tab_title['icon'][] = "";

            echo opentab($tab_title, $_GET['section'], 'adminreset_list', TRUE);
            switch ($_GET['section']) {
                case "adminreset_form":
                    $this->admin_reset_form();
                    break;
                default:
                    $this->admin_reset_listing();
                    break;
            }
            echo closetab();
        closetable();
    }

    public function admin_reset_listing() {
        $all_admin_reset = self::load_all_admin_reset();

        echo '<div class="m-t-15">';
            echo '<h2>'.self::$locale['apw_415'].'</h2>';
            if (!empty($all_admin_reset)) {
                echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
                echo "<tr>\n";
                echo "<td class='col-xs-2'><strong>".self::$locale['apw_417']."</strong></td>\n";
                echo "<td class='col-xs-2'><strong>".self::$locale['apw_418']."</strong></td>\n";
                echo "<td class='col-xs-2'><strong>".self::$locale['apw_419']."</strong></td>\n";
                echo "<td class='col-xs-2'><strong>".self::$locale['apw_420']."</strong></td>\n";
                echo "<td class='col-xs-2'><strong>".self::$locale['apw_421']."</strong></td>\n";
                echo "<td class='col-xs-2'><strong>".self::$locale['apw_427']."</strong></td>\n";
                echo "</tr>\n";

                foreach ($all_admin_reset as $info) {
                    $adm_title = self::admin_title();
                    $reset_passwords = (isnum($info['reset_admins']) ? profile_link($info['user_id_reset'], $info['user_name_reset'], $info['user_status_reset']) : $adm_title[$info['reset_admins']]) ;
                    $sucess = !empty($info['reset_sucess']) ? count(explode(".", $info['reset_sucess'])) : 0;
                    $failed = !empty($info['reset_failed']) ? count(explode(".", $info['reset_failed'])) : 0;
                    echo "<tr>\n";
                    echo "<td class='col-xs-2'>".showdate("shortdate", $info['reset_timestamp'])."</td>\n";
                    echo "<td class='col-xs-2'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</td>\n";
                    echo "<td class='col-xs-2'>".$reset_passwords."</td>\n";
                    echo "<td class='col-xs-2'>".$sucess." ".self::$locale['apw_422']." ".($sucess + $failed)."</td>\n";
                    echo "<td class='col-xs-2'>".($info['reset_reason'] ? $info['reset_reason'] : self::$locale['apw_423'])."</td>\n";
                    echo "<td class='col-xs-2'><a id='confirm' class='btn btn-default btn-sm' href='".FUSION_SELF.fusion_get_aidlink()."&amp;section=adminreset_list&amp;action=delete&amp;reset_id=".$info['reset_id']."' onclick=\"return confirm('".self::$locale['apw_428']."');\">".self::$locale['delete']."<i class='fa fa-trash m-l-10'></i></a></td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n</div>";
            } else {
                echo "<div class='well text-center'>".self::$locale['apw_426']."</div>\n";
            }
        echo '</div>';
    }

    public function admin_reset_form() {
        fusion_confirm_exit();
        openside('', 'm-t-15');
            echo openform('admin_reset', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=adminreset_form");
            echo form_select('reset_admin', self::$locale['apw_400'], '', array(
                'required' => TRUE,
                'options' => self::load_admins(),
                'placeholder' => self::$locale['choose'],
                'allowclear' => TRUE,
                'inline' => TRUE
            ));

            echo form_textarea('reset_message', self::$locale['apw_404'], '', array('required' => TRUE, 'autosize' => TRUE));
            echo form_checkbox("reset_login", self::$locale['apw_405'], '', array("inline" => TRUE));
            echo form_button('reset_admins', self::$locale['apw_406'], self::$locale['apw_406'], array('class' => 'btn-primary'));
            echo closeform();
        closeside();
    }
}

$admres = new admin_reset_admin();
$admres->display_admin();
require_once THEMES."templates/footer.php";
