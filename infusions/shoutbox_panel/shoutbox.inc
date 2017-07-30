<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shoutbox.inc
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

class Shoutbox {
    protected static $sb_settings = array();
    private static $instance = NULL;
    private static $locale = array();
    private static $limit = 4;
    private static $arch_limit = 20;
    private $day_data = array();
    private $postLink = '';
    private $data = array(
        'shout_id'        => 0,
        'shout_name'      => '',
        'shout_message'   => '',
        'shout_datestamp' => '',
        'shout_ip'        => '',
        'shout_ip_type'   => '4',
        'shout_hidden'    => '',
        'shout_language'  => LANGUAGE,
    );
    private static $default_params = array(
        'sbform_name' => '',
        'sb_db'       => '',
        'sb_limit'    => ''
    );

    public function __construct() {
        require_once INCLUDES."infusions_include.php";

        self::$locale = fusion_get_locale("", SHOUTBOX_LOCALE);

        $sb_settings = self::get_sb_settings();
        self::$limit = $sb_settings['visible_shouts'];
        $_GET['s_action'] = isset($_GET['s_action']) ? $_GET['s_action'] : '';
        $this->postLink = FORM_REQUEST;
        $this->postLink = preg_replace("^(&amp;|\?)s_action=(edit|delete)&amp;shout_id=\d*^", "", $this->postLink);
        $this->sep = stristr($this->postLink, "?") ? "&amp;" : "?";

        switch ($_GET['s_action']) {
            case 'delete':
                self::delete_select($_GET['shout_id']);
                break;
            case 'delete_select':
                if (empty($_POST['rights'])) {
                    \defender::stop();
                    addNotice('danger', self::$locale['SB_noentries']);
                    redirect(clean_request("", array("section=shoutbox", "aid"), TRUE));
                }
                self::delete_select($_POST['rights']);
                break;
            case 'edit':
                $this->data = self::_selectedSB($_GET['shout_id']);
                break;
            default:
                break;
        }
    }

    public static function getInstance($key = TRUE) {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->setEmptyDay();
            self::$instance->set_shoutboxdb();
        }

        return self::$instance;
    }

    private function setEmptyDay() {
        $this->day_data = array(
            '90' => "90 ".self::$locale['SB_days'],
            '60' => "60 ".self::$locale['SB_days'],
            '30' => "30 ".self::$locale['SB_days'],
            '20' => "20 ".self::$locale['SB_days'],
            '10' => "10 ".self::$locale['SB_days']
        );
    }

    public static function get_sb_settings() {
        if (empty(self::$sb_settings)) {
            self::$sb_settings = get_settings("shoutbox_panel");
        }

        return self::$sb_settings;
    }

    private function set_shoutboxdb() {
        $shout_group = 0;
        $shout_group_n = "";
        foreach (fusion_get_groups() as $key => $grups) {
            if (!empty($_POST[$grups])) {
                $shout_group = $key;
                $shout_group_n = $grups;
            }
        }

        if (isset($_POST[$shout_group_n]) or isset($_POST['shout_box'])) {
            !empty($_POST['shout_box']) ? $shout_group = (isset($_POST['shout_hidden']) ? form_sanitizer($_POST['shout_hidden'], 0, "shout_hidden") : 0) : $shout_group;

            if (iGUEST && self::$sb_settings['guest_shouts']) {
                // Process Captchas
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES."captchas/".fusion_get_settings('captcha')."/captcha_check.php";
                $sb_name = form_sanitizer($_POST['shout_name'], '', 'shout_name');
                if (!isset($_POST['captcha_code']) || $_CAPTCHA_IS_VALID == TRUE) {
                    \defender::stop();
                    addNotice("danger", self::$locale['SB_shout_captha']);
                    redirect(clean_request("section=shoutbox", array("", "aid"), TRUE));
                }
            }

            $this->data = array(
                'shout_id'       => form_sanitizer($_POST['shout_id'], 0, "shout_id"),
                'shout_name'     => !empty($sb_name) ? $sb_name : fusion_get_userdata("user_id"),
                'shout_message'  => form_sanitizer($_POST['shout_message'], '', 'shout_message'),
                'shout_hidden'   => $shout_group,
                'shout_language' => form_sanitizer($_POST['shout_language'], LANGUAGE, "shout_language"),
            );

            if (empty($this->data['shout_id'])) {
                $this->data += array(
                    'shout_datestamp' => time(),
                    'shout_ip'        => USER_IP,
                    'shout_ip_type'   => USER_IP_TYPE,
                    'shout_hidden'    => $shout_group,
                );
            }

            require_once INCLUDES."flood_include.php";
            if (!flood_control("shout_datestamp", DB_SHOUTBOX, "shout_name='".$this->data['shout_name']."'")) {
                if (\defender::safe()) {
                    dbquery_insert(DB_SHOUTBOX, $this->data, empty($this->data['shout_id']) ? "save" : "update");
                    addNotice("success", empty($this->data['shout_id']) ? self::$locale['SB_shout_added'] : self::$locale['SB_shout_updated']);
                }

            } else {
                \defender::stop();
                addNotice("danger", sprintf(self::$locale['SB_flood'], fusion_get_settings("flood_interval")));
            }
            defined('ADMIN_PANEL') ?
                redirect(clean_request("section=shoutbox", array("", "aid"), TRUE)) :
                redirect($this->postLink);
        }

        if (isset($_POST['sb_settings'])) {
            $inputArray = array(
                'visible_shouts' => form_sanitizer($_POST['visible_shouts'], 0, "visible_shouts"),
                'guest_shouts'   => form_sanitizer($_POST['guest_shouts'], 0, "guest_shouts"),
                'hidden_shouts'  => form_sanitizer($_POST['hidden_shouts'], 0, "hidden_shouts"),
            );

            if (\defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = array(
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "shoutbox_panel",
                    );
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
                }
                addNotice("success", self::$locale['SB_update_ok']);
                redirect(clean_request("section=shoutbox_settings", array("", "aid"), TRUE));
            }
        }

        if (isset($_POST['sb_delete_old']) && isset($_POST['num_days']) && isnum($_POST['num_days'])) {
            $deletetime = time() - (intval($_POST['num_days']) * 86400);
            $numrows = dbcount("(shout_id)", DB_SHOUTBOX, "shout_datestamp < '".$deletetime."'");
            $result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_datestamp < '".$deletetime."'");
            addNotice("warning", number_format(intval($numrows))." / ".$_POST['num_days'].self::$locale['SB_delete_old']);
            defined('ADMIN_PANEL') ?
                redirect(clean_request("section=shoutbox", array("", "aid"), TRUE)) :
                redirect($this->postLink);
        }
    }

    private function delete_select($id) {
        if (!empty($id)) {
            $cnt = count($id);
            $i = 0;
            if (is_array($id)) {
                foreach ($id as $key => $right) {
                    if (self::verify_sbdb($key)) {
                        dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".intval($key)."'");
                        $i++;
                    }
                }
            } else {
                if (self::verify_sbdb($id)) {
                    dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".intval($id)."'");
                    $i++;
                }

            }

            addNotice('warning', $cnt." / ".$i.' '.self::$locale['SB_shout_deleted']);
        }

        defined('ADMIN_PANEL') ?
            redirect(clean_request("section=shoutbox", array("", "aid"), TRUE)) :
            redirect($this->postLink);
    }

    static function verify_sbdb($id) {
        if (isnum($id)) {
            if ((iADMIN && checkrights("S")) ||
                (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".$id."' AND shout_name='".fusion_get_userdata("user_id")."'".(multilang_table("SB") ? " AND shout_language='".LANGUAGE."'" : "").""))
            ) {
                return TRUE;
            }

            return FALSE;
        }

        return FALSE;
    }

    public function _countSB($opt) {
        $DBc = dbcount("(shout_id)", DB_SHOUTBOX, (multilang_table("SB") ? "shout_language='".LANGUAGE."' AND " : "").groupaccess('shout_hidden').$opt);

        return $DBc;
    }

    public function _selectDB($rows, $min) {
        $result = dbquery("SELECT s.shout_id, s.shout_name, s.shout_message, s.shout_datestamp, s.shout_language, s.shout_ip, s.shout_hidden,
        	u.user_id, u.user_name, u.user_avatar, u.user_status, u.user_lastvisit
            FROM ".DB_SHOUTBOX." s
            LEFT JOIN ".DB_USERS." u ON s.shout_name=u.user_id
            WHERE ".(multilang_table("SB") ? "shout_language='".LANGUAGE."' AND " : "")."
            ".groupaccess('s.shout_hidden')."
            ORDER BY shout_datestamp DESC
            LIMIT ".intval($rows).", ".$min
        );

        return $result;
    }

    public function _selectedSB($ids) {
        if (self::verify_sbdb($ids)) {
            $result = dbquery("SELECT shout_id, shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type, shout_hidden, shout_language
                FROM ".DB_SHOUTBOX."
                WHERE shout_id=".intval($ids).(multilang_table("SB") ? " AND shout_language='".LANGUAGE."'" : "")
            );

            if (dbrows($result) > 0) {
                return $this->data = dbarray($result);
            }
        }
    }

    public function display_admin() {
        $aidlink = fusion_get_aidlink();
        $allowed_section = array("shoutbox", "shoutbox_form", "shoutbox_settings");
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'shoutbox';
        $edit = (isset($_GET['s_action']) && $_GET['s_action'] == 'edit') && isset($_GET['shout_id']) ? TRUE : FALSE;
        $_GET['shout_id'] = isset($_GET['shout_id']) && isnum($_GET['shout_id']) ? $_GET['shout_id'] : 0;
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => INFUSIONS.'shoutbox_panel/shoutbox_admin.php'.fusion_get_aidlink(), "title" => self::$locale['SB_title']]);
        switch ($_GET['section']) {
            case "shoutbox_form":
                add_to_title(self::$locale['edit']);
                \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, "title" => $edit ? self::$locale['edit'] : self::$locale['SB_add']]);
                break;
            case "shoutbox_settings":
                add_to_title(self::$locale['SB_settings']);
                \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, "title" => self::$locale['SB_settings']]);
                break;
            default:
        }

        opentable(self::$locale['SB_admin1']);
        $master_tab_title['title'][] = self::$locale['SB_admin1'];
        $master_tab_title['id'][] = "shoutbox";
        $master_tab_title['icon'][] = "";

        $master_tab_title['title'][] = $edit ? self::$locale['edit'] : self::$locale['SB_add'];
        $master_tab_title['id'][] = "shoutbox_form";
        $master_tab_title['icon'][] = "";

        $master_tab_title['title'][] = self::$locale['SB_settings'];
        $master_tab_title['id'][] = "shoutbox_settings";
        $master_tab_title['icon'][] = "";

        echo opentab($master_tab_title, $_GET['section'], "shoutbox", TRUE, 'nav-tabs m-b-10');
        switch ($_GET['section']) {
            case "shoutbox_form":
                add_to_title(self::$locale['edit']);
                $this->sb_AdminForm();
                break;
            case "shoutbox_settings":
                add_to_title(self::$locale['SB_settings']);
                $this->settings_Form();
                break;
            default:
                add_to_title(self::$locale['SB_title']);
                $this->sb_listing();
                break;
        }
        echo closetab();
        closetable();
    }

    public function settings_Form() {
        $sb_settings = self::get_sb_settings();

        openside('');
        echo openform('shoutbox', 'post', $this->postLink);
        echo form_select('num_days', self::$locale['SB_delete_old'], '', array('inline' => TRUE, 'inner_width' => '300px', 'options' => $this->day_data));
        echo form_button('sb_delete_old', self::$locale['delete'], self::$locale['delete'], array('class' => 'btn-danger', 'icon' => 'fa fa-trash'));
        echo closeform();
        closeside();

        add_to_jquery("$('#sb_delete_old').bind('click', function() { confirm('".self::$locale['SB_warning_shouts']."'); });");

        openside('');
        echo openform('shoutbox2', 'post', $this->postLink);
        echo form_text('visible_shouts', self::$locale['SB_visible_shouts'], $sb_settings['visible_shouts'], array('required' => TRUE, 'inline' => TRUE, 'inner_width' => '100px', "type" => "number"));
        $opts = array('1' => self::$locale['yes'], '0' => self::$locale['no'],);
        echo form_select('guest_shouts', self::$locale['SB_guest_shouts'], $sb_settings['guest_shouts'], array('inline' => TRUE, 'inner_width' => '100px', 'options' => $opts));
        echo form_select('hidden_shouts', self::$locale['SB_hidden_shouts'], $sb_settings['hidden_shouts'], array('inline' => TRUE, 'inner_width' => '100px', 'options' => $opts));
        echo form_button('sb_settings', self::$locale['save'], self::$locale['save'], array('class' => 'btn-success'));
        echo closeform();
        closeside();
    }

    public function sb_captcha() {
        if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
            $_CAPTCHA_HIDE_INPUT = FALSE;
            echo form_text('shout_name', self::$locale['SB_name'], '',
                array("inline" => TRUE, "required" => TRUE, 'max_length' => 30));
            echo "<div class='m-t-10 m-b-10'>";
            echo "<label class='col-xs-12 col-sm-12'>".self::$locale['global_150']."</label>
            <div class='panel-heading'>\n";
            include INCLUDES."captchas/".fusion_get_settings('captcha')."/captcha_display.php";
            if (!$_CAPTCHA_HIDE_INPUT) {
                echo "<br />\n<label for='captcha_code'>".self::$locale['global_151']."</label>";
                echo "<br />\n<input type='text' id='captcha_code-".$this->data['shout_id']."' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
            }
            echo "</div>\n";
            echo "</div>\n";
        }
    }

    public function sbForm() {
        defined('ADMIN_PANEL') ? fusion_confirm_exit() : "";
        openside(self::$locale['SB_title']);
        if (iGUEST && !self::$sb_settings['guest_shouts'] && empty(self::$sb_settings['hidden_shouts'])) {
            echo "<div class='text-center'>".self::$locale['SB_login_req']."</div>\n";
        } else {
            echo openform(self::$default_params['sbform_name'], 'post', $this->postLink);
            echo form_hidden('shout_id', '', $this->data['shout_id']);
            echo form_hidden('shout_hidden', '', $this->data['shout_hidden']);
            echo form_textarea('shout_message', self::$locale['SB_message'], $this->data['shout_message'], array('required' => TRUE, "autosize" => TRUE, 'form_name' => self::$default_params['sbform_name'], 'wordcount' => TRUE, 'maxlength' => '200', 'type' => 'bbcode', 'input_bbcode' => 'smiley|b|u|url|color'));
            if (iGUEST) {
                echo self::sb_captcha();
            }
            if ((count(fusion_get_enabled_languages()) > 1) && multilang_table("SB")) {
                echo form_select('shout_language', self::$locale['global_ML100'], $this->data['shout_language'], array(
                    "inner_width" => "100%",
                    'required'    => TRUE,
                    'options'     => fusion_get_enabled_languages()
                ));
            } else {
                echo form_hidden('shout_language', '', $this->data['shout_language']);
            }
            if (iMEMBER && self::$sb_settings['hidden_shouts']) {
                echo "<div class='btn-group dropup'>
                ".form_button('shout_box', empty($_GET['shout_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], empty($_GET['blacklist_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], array('class' => 'btn-primary'))."
                <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>\n";
                echo "<span class='caret'></span>\n";
                echo "</button>\n";
                echo "<ul class='dropdown-menu'>\n";
                foreach (fusion_get_groups() as $key => $glink) {
                    if (!iADMIN && $key > 0) {
                        echo users_groupaccess($key) ? "<li>".form_button($glink, $glink, $glink, array('class' => 'btn-primary'))."</li>\n" : "";
                    } else {
                        echo "<li>".form_button($glink, $glink, $glink, array('class' => 'btn-primary'))."</li>\n";
                    }
                }
                echo "</ul>\n";
                echo "</div>\n";
            } else {
                echo form_button('shout_box', empty($_GET['shout_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], empty($_GET['blacklist_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], array('class' => 'btn-primary btn-block'));
            }

            echo closeform();
        }
    }

    public function sb_listing() {
        $total_rows = $this->_countSB("");
        $rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
        $result = $this->_selectDB($rowstart, self::$limit);
        $rows = dbrows($result);

        echo '<div class="m-t-10 m-b-10">';
        echo "<div class='display-inline'><span class='pull-right m-t-10'>".sprintf(self::$locale['SB_entries'], $rows, $total_rows)."</span></div>\n";
        echo ($total_rows > $rows) ? '<div>'.makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", array("aid", "section"), TRUE)."&amp;").'</div>' : "";
        echo '</div>';

        if ($rows > 0) {
            echo openform('sb_form', 'post', $this->postLink."&amp;section=shoutbox&amp;s_action=delete_select");
            echo "<div class='list-group'>\n";
            while ($data = dbarray($result)) {
                $online = "<span style='height:5px; width:10px; border-radius:50%; color:#5CB85C'><i class='".($data['user_lastvisit'] >= time() - 3600 ? "fa fa-circle" : "fa fa-circle-thin")."'></i></span>";
                echo "<div class='list-group-item clearfix'>\n";
                echo '<div class="row">';
                echo '<div class="col-sm-3">';
                echo display_avatar($data, '30px', '', TRUE, 'img-rounded pull-left m-r-10');
                echo "<div class='overflow-hide pull-left m-r-20'>";
                echo $data['user_name'] ? profile_link($data['user_id'], $data['user_name'], $data['user_status']) : $data['shout_name'];
                echo $online;
                echo ' '.self::$locale['SB_userip'].' '.$data['shout_ip'].'<br/>';;
                echo "<small>".showdate("longdate", $data['shout_datestamp'])."</small><br/>";
                echo self::$sb_settings['hidden_shouts'] ? self::$locale['SB_visbility'].': '.getgroupname($data['shout_hidden']).'<br/>' : '';
                echo self::$locale['SB_lang'].': '.translate_lang_names($data['shout_language']).'<br/>';
                echo "</div>\n";
                echo '</div>';
                echo '<div class="col-sm-6">';
                echo parse_textarea($data['shout_message'], TRUE, TRUE, FALSE);
                echo '</div>';
                echo '<div class="col-sm-3">';
                echo '<div class="btn-group btn-group-sm pull-left m-r-20">';
                echo "<a class='btn btn-default' href='".$this->postLink."&amp;section=shoutbox_form&amp;s_action=edit&amp;shout_id=".$data['shout_id']."'>";
                echo "<i class='fa fa-edit fa-fw'></i> ".self::$locale['edit'];
                echo "</a>";
                echo "<a class='btn btn-danger' href='".$this->postLink."&amp;section=shoutbox&amp;s_action=delete&amp;shout_id=".$data['shout_id']."' onclick=\"return confirm('".self::$locale['SB_warning_shout']."');\">";
                echo "<i class='fa fa-trash fa-fw'></i> ".self::$locale['delete'];
                echo "</a>";
                echo '</div>';
                echo form_checkbox("rights[".$data['shout_id']."]", '', '');
                echo '</div>';
                echo '</div>';
                echo "</div>\n";
            }
            echo "</div>\n";
            echo form_button('sb_admins', self::$locale['SB_selected_shout'], self::$locale['SB_selected_shout'], array('class' => 'btn-danger', 'icon' => 'fa fa-trash'));
            echo closeform();

            echo ($total_rows > $rows) ? '<div class="text-center">'.makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", array("aid", "section"), TRUE)."&amp;").'</div>' : "";
        } else {
            echo "<div class='text-center m-t-10'>".self::$locale['SB_no_msgs']."</div>\n";
        }
    }

    public function sb_AdminForm() {
        self::$default_params = array(
            'sbform_name' => 'sb_admin',
            'sb_db'       => '?rowstart',
            'sb_limit'    => self::$limit,
        );

        self::sbForm(self::$default_params);
    }

    public function get_shout() {
        self::$default_params = array(
            'sbform_name' => 'sbpanel',
            'sb_db'       => '?rowstart',
            'sb_limit'    => self::$limit,
        );

        self::shout_list(self::$default_params);
    }

    public function get_archiveshout() {
        self::$default_params = array(
            'sbform_name' => 'sarchiv',
            'sb_db'       => '?rows',
            'sb_limit'    => self::$arch_limit,
        );

        self::shout_list(self::$default_params);
    }

    public function shout_list($info) {
        echo self::sbForm();
        $total_rows = $this->_countSB("");
        $rows = isset($_GET['rows']) && ($_GET['rows'] <= $total_rows) ? $_GET['rows'] : 0;
        $result = $this->_selectDB($rows, $info['sb_limit']);
        $rows = dbrows($result);

        if ($rows > 0) {
            if ($info['sbform_name'] == 'sarchiv') {
                echo "<div class='clearfix'>\n";
                echo "<span class='pull-right m-t-10'>".sprintf(self::$locale['SB_entries'], $rows, $total_rows)."</span>\n";
                echo "</div>\n";
            }
            while ($data = dbarray($result)) {
                echo "<div class='display-block shoutboxwrapper clearfix'>\n";
                echo "<div class='shoutboxavatar pull-left m-r-10 m-t-5'>\n";
                echo display_avatar($data, '50px', '', TRUE, 'img-rounded');
                echo "</div>\n";
                if ((iADMIN && checkrights("S")) || (iMEMBER && $data['shout_name'] == fusion_get_userdata('user_id') && isset($data['user_name']))) {
                    echo "<div class='pull-right btn-group'>\n";
                    echo "<a class='btn btn-default btn-xs' title='".self::$locale['edit']."' href='".INFUSIONS."shoutbox_panel/shoutbox_archive.php?s_action=edit&shout_id=".$data['shout_id']."' class='side'><i class='fa fa-edit'></i></a>\n"; //
                    echo "<a class='btn btn-default btn-xs' title='".self::$locale['delete']."' href='".INFUSIONS."shoutbox_panel/shoutbox_archive.php?s_action=delete&shout_id=".$data['shout_id']."' onclick=\"return confirm('".self::$locale['SB_warning_shout']."');\" class='side'><i class='fa fa-trash'></i></a>\n"; //
                    echo "</div>\n";
                }
                $online = "<span style='height:5px; width:10px; border-radius:50%; color:#5CB85C'><i class='".($data['user_lastvisit'] >= time() - 3600 ? "fa fa-circle" : "fa fa-circle-thin")."'></i></span>";

                echo "<div class='shoutboxname'>\n";
                echo ($data['user_name']) ? "<span class='side'>".profile_link($data['shout_name'], $data['user_name'],
                        $data['user_status'])."</span>\n".$online : $data['shout_name']."\n";
                echo "</div>\n";
                echo "<div class='shoutboxdate'>".timer($data['shout_datestamp'])."</div>\n";
                $shout_message = parse_textarea($data['shout_message'], TRUE, TRUE, FALSE);
                echo "<div class='shoutbox'>".$shout_message."</div>\n";
                echo "</div>\n";
            }
            echo($info['sbform_name'] == 'sarchiv' ? ($total_rows > $rows ? makepagenav($rows, $info['sb_limit'], $total_rows, $info['sb_limit'], FUSION_SELF.$info['sb_db'], FALSE) : "") : (
            ($total_rows > self::$sb_settings['visible_shouts'] ? "<div class='text-center m-t-15'><a class='btn btn-default btn-xs' href='".INFUSIONS."shoutbox_panel/shoutbox_archive.php' class='side'>".self::$locale['SB_archive']."</a>\n</div>\n" : "")));
        } else {
            echo "<div class='text-center'>".self::$locale['SB_no_msgs']."</div>\n";
        }
        closeside();
    }
}
