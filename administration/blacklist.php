<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blacklist.php
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
pageAccess('B');
require_once THEMES."templates/admin_header.php";

use \PHPFusion\BreadCrumbs;

class Blaclist {
    private static $instance = NULL;
    private static $locale = array();
    private static $limit = 20;

    private $data = array(
        'blacklist_id'        => 0,
        'blacklist_user_id'   => '',
        'blacklist_ip'        => '',
        'blacklist_ip_type'   => '4',
        'blacklist_email'     => '',
        'blacklist_reason'    => '',
        'blacklist_datestamp' => ''
    );

    public function __construct() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/blacklist.php");

        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($_GET['action']) {
            case 'delete':
                self::delete_blacklist($_GET['blacklist_id']);
                break;
            case 'edit':
                $this->data = self::_selectBlaclist($_GET['blacklist_id']);
                break;
            default:
                break;
        }

        BreadCrumbs::getInstance()->addBreadCrumb(['link'=> ADMIN.'blacklist.php'.fusion_get_aidlink(), "title"=> self::$locale['BLS_000']]);
        self::set_adminsdb();
    }

    public static function getInstance($key = TRUE) {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function set_adminsdb() {
        if (isset($_POST['blacklist_admins'])) {
            $blacklist_ip_type = 0;

            if (strpos($_POST['blacklist_ip'], ".")) {
                if (strpos($_POST['blacklist_ip'], ":") === FALSE) {
                    $blacklist_ip_type = 4;
                } else {
                    $blacklist_ip_type = 5;
                }
            } else {
                $blacklist_ip_type = 6;
            }

            $this->data = array(
                'blacklist_id'        => !empty($_POST['blacklist_id']) ? form_sanitizer($_POST['blacklist_id'], 0, "blacklist_id") : '',
                'blacklist_user_id'   => fusion_get_userdata('user_id'),
                'blacklist_ip'        => form_sanitizer($_POST['blacklist_ip'], '', 'blacklist_ip'),
                'blacklist_ip_type'   => $blacklist_ip_type,
                'blacklist_email'     => !empty($_POST['blacklist_email']) ? form_sanitizer($_POST['blacklist_email'], '', 'blacklist_email') : '',
                'blacklist_reason'    => form_sanitizer($_POST['blacklist_reason'], '', 'blacklist_reason'),
                'blacklist_datestamp' => empty($_POST['blacklist_datestamp']) ? time() : $_POST['blacklist_datestamp']
            );

            if (\defender::safe()) {
                if (empty($this->data['blacklist_ip']) && empty($this->data['blacklist_email'])) {
                    \defender::stop();
                    addNotice("danger", self::$locale['BLS_010']);
                } else {
                    dbquery_insert(DB_BLACKLIST, $this->data, empty($this->data['blacklist_id']) ? 'save' : 'update');
                    addNotice('success', empty($this->data['blacklist_id']) ? self::$locale['BLS_011'] : self::$locale['BLS_012']);
                    redirect(clean_request("", array("section=blacklist", "aid"), TRUE));
                }
            }
        }
    }

    static function verify_blacklist($id) {
        if (isnum($id)) {
            return dbcount("(blacklist_id)", DB_BLACKLIST, "blacklist_id='".intval($id)."'");
        }

        return FALSE;
    }

    public function _countBlist($opt) {
        $DBc = dbcount("(blacklist_id)", DB_BLACKLIST, $opt);
        return $DBc;
    }

    private static function delete_blacklist($id) {
        if (self::verify_blacklist($id)) {
            dbquery("DELETE FROM ".DB_BLACKLIST." WHERE blacklist_id='".intval($id)."'");
            addNotice('success', self::$locale['BLS_013']);
            redirect(clean_request("", array("section=blacklist", "aid"), TRUE));
        }
    }

    public function _selectBlaclist($ids) {
        if (self::verify_blacklist($ids)) {
            $result = dbquery("SELECT blacklist_id, blacklist_user_id, blacklist_ip, blacklist_ip_type, blacklist_email, blacklist_reason, blacklist_datestamp
                FROM ".DB_BLACKLIST."
                WHERE blacklist_id=".intval($ids)
            );

            if (dbrows($result) > 0) {
                return $data = dbarray($result);
            }
        }
    }

    public function _selectDB($rows) {
        $result = dbquery("SELECT b.blacklist_id, b.blacklist_ip, b.blacklist_email, b.blacklist_reason, b.blacklist_datestamp, u.user_id, u.user_name, u.user_status
            FROM ".DB_BLACKLIST." b
            LEFT JOIN ".DB_USERS." u ON u.user_id=b.blacklist_user_id
            ORDER BY blacklist_datestamp DESC
            LIMIT ".intval($rows).", ".self::$limit
        );
        return $result;
    }

    public function display_admin() {
        $aidlink = fusion_get_aidlink();

        $allowed_section = array("blacklist", "blacklist_form");
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'blacklist';
        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['blacklist_id']) ? TRUE : FALSE;
        $_GET['blacklist_id'] = isset($_GET['blacklist_id']) && isnum($_GET['blacklist_id']) ? $_GET['blacklist_id'] : 0;

        opentable(self::$locale['BLS_000']);
            $master_tab_title['title'][] = self::$locale['BLS_020'];
            $master_tab_title['id'][] = "blacklist";
            $master_tab_title['title'][] = $edit ? self::$locale['BLS_021'] : self::$locale['BLS_022'];
            $master_tab_title['id'][] = "blacklist_form";
            echo opentab($master_tab_title, $_GET['section'], "blacklist", TRUE);
            switch ($_GET['section']) {
               case "blacklist_form":
            BreadCrumbs::getInstance()->addBreadCrumb(['link'=> FUSION_REQUEST, "title"=> $master_tab_title['title'][1]]);
                $this->blacklistForm();
                break;
            default:
            BreadCrumbs::getInstance()->addBreadCrumb(['link'=> FUSION_REQUEST, "title"=> $master_tab_title['title'][0]]);
                $this->blacklist_listing();
                break;
            }
            echo closetab();
        closetable();
    }

    public function blacklist_listing() {
        $aidlink = fusion_get_aidlink();
        $total_rows = $this->_countBlist("");
        $rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
        $result = $this->_selectDB($rowstart);
        $rows = dbrows($result);

        echo "<div class='clearfix'>\n";
        echo "<span class='pull-right m-t-10'>".sprintf(self::$locale['BLS_023'], $rows, $total_rows)."</span>\n";
        echo "</div>\n";
        echo ($total_rows > $rows) ? makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", array("aid", "section"), TRUE)."&amp;") : "";

        if ($rows > 0) {
            echo "<div class='m-t-20'>\n";
            echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
            echo "<tr>\n";
            echo "<td class='col-xs-2'>".self::$locale['BLS_030']."</th>\n";
            echo "<td class='col-xs-2'>".self::$locale['BLS_031']."</th>\n";
            echo "<td class='col-xs-2'>".self::$locale['BLS_032']."</th>\n";
            echo "<td class='col-xs-2'>".self::$locale['BLS_033']."</th>\n";
            echo "</tr>\n";

            while ($data = dbarray($result)) {
                echo "<tr>\n";
                echo "<td class='col-xs-2'>".($data['blacklist_ip'] ? $data['blacklist_ip'] : $data['blacklist_email']);
                if ($data['blacklist_reason']) {
                    echo "<br /><span class='small2'>".$data['blacklist_reason']."</span>";
                }
                echo "</td>\n<td class='col-xs-2'>".(!empty($data['user_name']) ? profile_link($data['user_id'], $data['user_name'], $data['user_status']) : self::$locale['na'])."</td>\n";
                echo "<td class='col-xs-2'>".(!empty($data['blacklist_datestamp']) ? showdate("shortdate", $data['blacklist_datestamp']) : self::$locale['na'])."</td>\n";
                echo "<td class='col-xs-2'>
                <a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=blacklist_form&amp;action=edit&amp;blacklist_id=".$data['blacklist_id']."'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>
                <a class='btn btn-danger btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=blacklist&amp;action=delete&amp;blacklist_id=".$data['blacklist_id']."' onclick=\"return confirm('".self::$locale['BLS_014']."');\">".self::$locale['delete']."<i class='fa fa-trash m-l-10'></i></a>
                </td>\n";
                echo "</tr>\n";
            }

            echo "</table>\n</div>";
            echo "</div>\n";
        } else {
            echo "<div style='text-align:center'><br />\n".self::$locale['BLS_015']."<br /><br />\n</div>\n";
        }
    }

    public function blacklistForm() {
        fusion_confirm_exit();
        openside('', 'm-t-15');
        echo "<div class='well'>\n";
        echo self::$locale['BLS_MS'];
        echo "</div>\n";
        echo openform('blacklist_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=blacklist_form");
        echo form_hidden('blacklist_id', '', $this->data['blacklist_id']);
        echo form_hidden('blacklist_datestamp', '', $this->data['blacklist_datestamp']);

        echo form_text('blacklist_ip', str_replace(['[STRONG]', '[/STRONG]'], ['<strong>', '</strong>'], self::$locale['BLS_034']), $this->data['blacklist_ip'], array('inline' => TRUE));

        echo form_text('blacklist_email', self::$locale['BLS_035'], $this->data['blacklist_email'], array('inline' => TRUE, 'type' => 'text', 'error_text' => self::$locale['BLS_016']));

        echo form_textarea('blacklist_reason', self::$locale['BLS_036'], $this->data['blacklist_reason'], array('inline' => TRUE, 'autosize' => TRUE));

        echo form_button('blacklist_admins', empty($_GET['blacklist_id']) ? self::$locale['BLS_037'] : self::$locale['BLS_038'], empty($_GET['blacklist_id']) ? self::$locale['BLS_037'] : self::$locale['BLS_038'], array('class' => 'btn-primary'));
        echo closeform();

        closeside();
    }
}

Blaclist::getInstance(TRUE)->display_admin();
require_once THEMES."templates/footer.php";
