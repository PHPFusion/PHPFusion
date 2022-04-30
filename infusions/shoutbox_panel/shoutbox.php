<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: shoutbox.php
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

class Shoutbox {
    protected static $sb_settings = [];
    private static $instance = NULL;
    private static $locale = [];
    private static $limit = 4;
    private static $arch_limit = 10;
    private $postLink;
    private $token_limit = 3600;
    private $data = [
        'shout_id'        => 0,
        'shout_name'      => '',
        'shout_message'   => '',
        'shout_datestamp' => '',
        'shout_ip'        => '',
        'shout_ip_type'   => '4',
        'shout_language'  => LANGUAGE
    ];

    public function __construct() {
        require_once INCLUDES."infusions_include.php";
        $site_path = fusion_get_settings("sitepath");

        $current_path = pathinfo(FUSION_REQUEST);
        $current_path = $site_path.str_replace("/", DIRECTORY_SEPARATOR, $current_path["dirname"].DIRECTORY_SEPARATOR.$current_path["basename"]);

        if (!session_get("shout_token_hash") || (session_get("shout_page") !== $current_path) && (FUSION_SELF !== "shoutbox_archive.php")) {
            session_add("shout_token_hash", "shoutbox_".random_string());
            session_add("shout_page", $current_path);
        }

        self::$locale = fusion_get_locale("", SHOUTBOX_LOCALE);

        self::$sb_settings = get_settings("shoutbox_panel");

        self::$limit = self::$sb_settings['visible_shouts'];
        $_GET['s_action'] = isset($_GET['s_action']) ? $_GET['s_action'] : '';

        // Just use this. You do not want "s_action" and "shoutbox_id"
        $this->postLink = clean_request("", ["s_action", "shout_id", "aid"], defined('ADMIN_PANEL'));

        switch (get('s_action')) {
            case 'delete':
                $id = defined('ADMIN_PANEL') ? get("shout_id") : $this->getSecureShoutId(get("shout_id"));
                self::deleteShout($id);
                break;
            case 'delete_select':
                if (empty(post(['shoutid']))) {
                    fusion_stop(self::$locale["SB_noentries"]);
                    redirect(clean_request("", ["section=shoutbox", "aid"]));
                }

                if (check_post(['shoutid'])) {
                    self::deleteShout(post(['shoutid']));
                }
                break;
            case 'edit':
                $id = defined('ADMIN_PANEL') ? get('shout_id') : $this->getSecureShoutId(get('shout_id'));

                if (self::verifyShout($id)) {
                    $result = dbquery("SELECT shout_id, shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type, shout_language
                        FROM ".DB_SHOUTBOX."
                        WHERE shout_id = :shoutid".(multilang_table("SB") ? " AND ".in_group('shout_language', LANGUAGE) : ""), [':shoutid' => $id]
                    );

                    if (dbrows($result) > 0) {
                        $this->data = dbarray($result);
                    }
                }
                break;
            case 'reply':
                $id = defined('ADMIN_PANEL') ? get('shout_id') : $this->getSecureShoutId(get('shout_id'));
                $this->data['shout_message'] = self::$locale['SB_reply_to'].' [shoutid-'.$id.']';
                break;
            default:
                break;
        }
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->setShoutboxDb();
        }

        return self::$instance;
    }

    private function deleteShout($id) {
        if (!empty($id)) {
            $i = 0;
            if (is_array($id)) {
                foreach ($id as $key => $right) {
                    if (self::verifyShout($key)) {
                        dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id = :shoutid", [':shoutid' => $key]);
                        $i++;
                    }
                }
            } else {
                if (self::verifyShout($id)) {
                    dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id = :shoutid", [':shoutid' => $id]);
                }
            }

            addnotice('success', self::$locale['SB_shout_deleted']);
        }

        redirect(defined('ADMIN_PANEL') ? clean_request("section=shoutbox", ["", "aid"]) : $this->postLink);
    }

    static function verifyShout($id) {
        if (isnum($id)) {
            if ((iADMIN && checkrights("S")) ||
                (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id = :shoutid AND shout_name = :shoutname".(multilang_table("SB") ? " AND ".in_group('shout_language', LANGUAGE) : ""), [':shoutid' => $id, ':shoutname' => fusion_get_userdata('user_id')]))
            ) {
                return TRUE;
            }

            return FALSE;
        }

        return FALSE;
    }

    private function getSecureShoutId($shout_hash) {
        $userdata = fusion_get_userdata();
        $hash = session_get("shout_token_hash");
        $decrypted_hash = fusion_decrypt($shout_hash, $hash);
        if (!empty($decrypted_hash)) {
            $hash = explode(".", $decrypted_hash);
            if (count($hash) === 3) {
                list($shout_token_id, $user_token_id, $time_token) = $hash;
                if (($userdata["user_lastvisit"] + $this->token_limit) >= $time_token && (int)$userdata["user_id"] === (int)$user_token_id) {
                    return (int)$shout_token_id;
                }
            }
        }
        return 0;
    }

    private function setShoutboxDb() {
        if (check_post('shout_box')) {
            if (iGUEST && self::$sb_settings['guest_shouts']) {
                // Process Captchas
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES."captchas/".fusion_get_settings('captcha')."/captcha_check.php";
                $sb_name = sanitizer('shout_name', '', 'shout_name');
                if (!$_CAPTCHA_IS_VALID) {
                    fusion_stop(self::$locale['SB_warning_validation_code']);
                    redirect(clean_request("section=shoutbox", ["", "aid"]));
                }
            }

            $this->data = [
                'shout_id'       => sanitizer('shout_id', 0, "shout_id"),
                'shout_name'     => (!empty($sb_name) ? $sb_name : (!empty(post('shout_name')) ? sanitizer('shout_name', '', 'shout_name') : fusion_get_userdata("user_id"))),
                'shout_message'  => sanitizer('shout_message', '', 'shout_message'),
                'shout_language' => sanitizer(['shout_language'], LANGUAGE, "shout_language")
            ];

            if (empty($this->data['shout_id'])) {
                $this->data += [
                    'shout_datestamp' => time(),
                    'shout_ip'        => USER_IP,
                    'shout_ip_type'   => USER_IP_TYPE
                ];
            }

            require_once INCLUDES."flood_include.php";
            if (!flood_control("shout_datestamp", DB_SHOUTBOX, "shout_name='".$this->data['shout_name']."'")) {
                if (fusion_safe()) {
                    dbquery_insert(DB_SHOUTBOX, $this->data, empty($this->data['shout_id']) ? "save" : "update");
                    addnotice("success", empty($this->data['shout_id']) ? self::$locale['SB_shout_added'] : self::$locale['SB_shout_updated']);
                }
            } else {
                fusion_stop(sprintf(self::$locale['SB_flood'], fusion_get_settings("flood_interval")));
            }
            defined('ADMIN_PANEL') ?
                redirect(clean_request("section=shoutbox", ["", "aid"])) :
                redirect($this->postLink);
        }

        if (check_post('sb_settings')) {
            $inputArray = [
                'visible_shouts' => sanitizer('visible_shouts', 5, 'visible_shouts'),
                'guest_shouts'   => check_post('guest_shouts') ? 1 : 0,
                'user_access'    => sanitizer(['user_access'], '', 'user_access')
            ];

            if (fusion_safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "shoutbox_panel",
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", ["primary_key" => "settings_name"]);
                }
                addnotice("success", self::$locale['SB_update_ok']);
                redirect(clean_request("section=shoutbox_settings", ["", "aid"]));
            }
        }

        if (check_post('sb_delete_old') && check_post('num_days') && isnum(post('num_days'))) {
            $deletetime = time() - (intval(post('num_days')) * 86400);
            $numrows = dbcount("(shout_id)", DB_SHOUTBOX, "shout_datestamp < :shoutdatestamp", [':shoutdatestamp' => $deletetime]);
            dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_datestamp < :shoutdatestamp", [':shoutdatestamp' => $deletetime]);
            addnotice("warning", number_format($numrows)." / ".post('num_days').self::$locale['SB_delete_old']);
            defined('ADMIN_PANEL') ?
                redirect(clean_request("section=shoutbox", ["", "aid"])) :
                redirect($this->postLink);
        }
    }

    public function displayAdmin() {
        $allowed_section = ["shoutbox", "shoutbox_form", "shoutbox_settings"];
        $section = check_get('section') && in_array(get('section'), $allowed_section) ? get('section') : $allowed_section[0];
        $edit = (check_get('s_action') && get('s_action') == 'edit') && check_get('shout_id');
        add_breadcrumb(['link' => INFUSIONS.'shoutbox_panel/shoutbox_admin.php'.fusion_get_aidlink(), "title" => self::$locale['SB_title']]);

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

        echo opentab($master_tab_title, $section, "shoutbox", TRUE, 'nav-tabs', "section", ['rowstart', 's_action', 'shout_id']);
        switch ($section) {
            case "shoutbox_form":
                add_to_title($edit ? self::$locale['edit'] : self::$locale['SB_add']);
                echo $this->sbForm();
                break;
            case "shoutbox_settings":
                add_to_title(self::$locale['SB_settings']);
                $this->settingsForm();
                break;
            default:
                add_to_title(self::$locale['SB_title']);
                $this->shoutsAdminListing();
                break;
        }
        echo closetab();
        closetable();
    }

    public function sbForm($form_name = 'sbform') {
        if (defined('ADMIN_PANEL')) {
            fusion_confirm_exit();
        }
        $html = '';

        if (iGUEST && !self::$sb_settings['guest_shouts']) {
            $html .= "<div class='text-center'>".self::$locale['SB_login_req']."</div>\n";
        } else {
            $html .= openform($form_name, 'post', $this->postLink);
            $html .= form_hidden('shout_name', '', $this->data['shout_name']);
            $html .= form_hidden('shout_id', '', $this->data['shout_id']);
            $html .= form_textarea('shout_message', self::$locale['SB_message'], $this->data['shout_message'], [
                'required'     => TRUE,
                'autosize'     => TRUE,
                'form_name'    => $form_name,
                'wordcount'    => TRUE,
                'maxlength'    => '200',
                'type'         => 'bbcode',
                'input_bbcode' => 'smiley|b|u|url|color'
            ]);

            if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (!$_CAPTCHA_HIDE_INPUT))) {
                $_CAPTCHA_HIDE_INPUT = FALSE;

                $html .= form_text('shout_name', self::$locale['SB_name'], '', ["required" => TRUE, 'max_length' => 30]);

                include INCLUDES.'captchas/'.fusion_get_settings('captcha').'/captcha_display.php';

                $html .= display_captcha([
                    'captcha_id' => 'captcha_shoutbox',
                    'input_id'   => 'captcha_code_shoutbox',
                    'image_id'   => 'captcha_image_shoutbox'
                ]);

                if (!$_CAPTCHA_HIDE_INPUT) {
                    $html .= form_text('captcha_code', self::$locale['global_151'], '', ['required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => 'captcha_code_shoutbox']);
                }
            }

            if (iADMIN && (count(fusion_get_enabled_languages()) > 1) && multilang_table("SB")) {
                $html .= form_select('shout_language[]', self::$locale['global_ML100'], $this->data['shout_language'], [
                    "inner_width" => "100%",
                    'required'    => TRUE,
                    'options'     => fusion_get_enabled_languages(),
                    'multiple'    => TRUE
                ]);
            } else {
                $html .= form_hidden('shout_language', '', $this->data['shout_language']);
            }

            $html .= form_button('shout_box', (empty(get('shout_id')) ? self::$locale['SB_save_shout'] : (get('s_action') == 'reply' ? self::$locale['SB_reply'] : self::$locale['SB_update_shout'])), (empty(get('shout_id')) ? self::$locale['send_message'] : (get('s_action') == 'reply' ? self::$locale['SB_reply'] : self::$locale['SB_update_shout'])), ['class' => 'btn-primary btn-sm btn-block']);

            $html .= closeform();
        }

        return $html;
    }

    public function settingsForm() {
        add_to_jquery("$('#sb_delete_old').bind('click', function() { return confirm('".self::$locale['SB_warning_shouts']."'); });");
        echo openform('shoutbox', 'post', $this->postLink);
        echo '<div class="row">';
        echo '<div class="col-xs-12 col-sm-6">';
        openside('');
        echo form_checkbox('guest_shouts', self::$locale['SB_guest_shouts'], self::$sb_settings['guest_shouts'], ['toggle' => TRUE]);
        echo form_text('visible_shouts', self::$locale['SB_visible_shouts'], self::$sb_settings['visible_shouts'], ['required' => TRUE, 'inline' => TRUE, 'inner_width' => '100px', "type" => "number"]);
        echo form_select('user_access[]', self::$locale['SB_visbility'], self::$sb_settings['user_access'], [
            'options'     => fusion_get_groups(),
            'placeholder' => self::$locale['choose'],
            'multiple'    => TRUE,
            'inline'      => TRUE
        ]);
        echo form_button('sb_settings', self::$locale['save'], self::$locale['save'], ['class' => 'btn-success']);
        closeside();
        echo '</div>';
        echo '<div class="col-xs-12 col-sm-6">';
        openside('');
        echo form_select('num_days', self::$locale['SB_delete_old'], '', [
            'inner_width' => '200px',
            'options'     => [
                '90' => "90 ".self::$locale['SB_days'],
                '60' => "60 ".self::$locale['SB_days'],
                '30' => "30 ".self::$locale['SB_days'],
                '20' => "20 ".self::$locale['SB_days'],
                '10' => "10 ".self::$locale['SB_days']
            ]
        ]);
        echo form_button('sb_delete_old', self::$locale['delete'], self::$locale['delete'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash']);
        closeside();
        echo '</div>';

        echo '</div>';
    }

    private function shoutsAdminListing() {
        $total_rows = dbcount("(shout_id)", DB_SHOUTBOX, (multilang_table("SB") ? in_group('shout_language', LANGUAGE) : ''));
        $rowstart = get_rowstart("rowstart", $total_rows);
        $result = $this->selectDb($rowstart, self::$limit, TRUE);
        $rows = dbrows($result);
        echo '<div class="m-t-10 m-b-10">';
        echo "<div class='display-inline'><span class='pull-right m-t-10'>".sprintf(self::$locale['SB_entries'], $rows, $total_rows)."</span></div>\n";
        echo ($total_rows > $rows) ? '<div>'.makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", ["aid", "section"])."&").'</div>' : "";
        echo '</div>';

        if ($rows > 0) {
            echo openform('sb_form', 'post', $this->postLink."&section=shoutbox&s_action=delete_select");
            echo "<div class='list-group'>\n";

            if (!defined("SHOUTBOXJS")) {
                add_to_jquery("$('.shoutbox-delete-btn').on('click', function(evt) {
                return confirm('".self::$locale['SB_warning_shout']."');
                });");
                define("SHOUTBOXJS", TRUE);
            }

            while ($data = dbarray($result)) {
                $data['user_name'] = !empty($data['user_id']) ? $data['user_name'] : $data['shout_name'];
                $online = !empty($data['user_lastvisit']) ? "<span style='color:#5CB85C'> <i class='".($data['user_lastvisit'] >= time() - 300 ? "fa fa-circle" : "fa fa-circle-thin")."'></i></span>" : '';
                echo "<div class='list-group-item clearfix'>\n";
                echo '<div class="row">';
                echo '<div class="col-sm-3">';
                echo display_avatar($data, '30px', '', !empty($item['user_id']), 'img-rounded pull-left m-r-10');
                echo "<div class='overflow-hide m-r-20'>";
                echo !empty($data['user_id']) ? profile_link($data['user_id'], $data['user_name'], $data['user_status']) : $data['shout_name'];
                echo $online;
                echo '<br/>'.self::$locale['SB_userip'].' '.$data['shout_ip'].'<br/>';
                echo "<small>".showdate("longdate", $data['shout_datestamp'])."</small><br/>";
                echo self::$locale['SB_lang'].': '.translate_lang_names($data['shout_language']).'<br/>';
                echo "</div>\n";
                echo '</div>';
                echo '<div class="col-sm-6">';
                echo parse_text($data['shout_message'], [
                    'decode'               => FALSE,
                    'default_image_folder' => NULL,
                    'add_line_breaks'      => TRUE
                ]);
                echo '</div>';
                echo '<div class="col-sm-3">';
                echo '<div class="btn-group btn-group-sm pull-left m-r-20">';
                echo "<a class='btn btn-default' href='".$this->postLink."&section=shoutbox_form&s_action=edit&shout_id=".$data['shout_id']."'>";
                echo "<i class='fa fa-edit fa-fw'></i> ".self::$locale['edit'];
                echo "</a>";
                echo "<a class='btn btn-danger shoutbox-delete-btn' href='".$this->postLink."&section=shoutbox&s_action=delete&shout_id=".$data['shout_id']."'>";
                echo "<i class='fa fa-trash fa-fw'></i> ".self::$locale['delete'];
                echo "</a>";
                echo '</div>';
                echo form_checkbox("shoutid[".$data['shout_id']."]", '', '');
                echo '</div>';
                echo '</div>';
                echo "</div>\n";
            }
            echo "</div>\n";
            echo form_button('sb_admins', self::$locale['SB_selected_shout'], self::$locale['SB_selected_shout'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash']);
            echo closeform();

            echo ($total_rows > $rows) ? '<div class="text-center">'.makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", ["aid", "section"])."&").'</div>' : "";
        } else {
            echo "<div class='text-center m-t-10'>".self::$locale['SB_no_msgs']."</div>\n";
        }
    }

    public function selectDb($rows, $min, $admin = FALSE) {
        return dbquery("SELECT s.shout_id, s.shout_name, s.shout_message, s.shout_datestamp, s.shout_language, s.shout_ip,
            u.user_id, u.user_name, u.user_avatar, u.user_status, u.user_lastvisit
            FROM ".DB_SHOUTBOX." AS s
            LEFT JOIN ".DB_USERS." AS u ON s.shout_name=u.user_id
            WHERE ".(multilang_table("SB") ? in_group('shout_language', LANGUAGE) : '')."
            ".($admin == FALSE ? (!empty(blacklist('u.user_id')) ? ' AND '.blacklist('u.user_id') : '') : '')."
            ORDER BY shout_datestamp DESC
            LIMIT ".(int)$rows.", ".$min
        );
    }

    public function displayShouts($archive = FALSE) {

        if (checkgroup(self::$sb_settings['user_access'])) {
            $sdata = $this->getShoutboxData($archive);
            $info = [
                'form'       => self::sbForm($archive ? 'sbarchive' : 'sbform'),
                'items'      => $sdata['items'],
                'archive'    => $sdata['archive'],
                'title'      => $archive ? self::$locale['SB_archive'] : self::$locale['SB_title'],
                'is_archive' => $archive,
                'pagenav'    => $sdata['pagenav']
            ];

            if ($archive) {
                add_to_title(self::$locale['SB_archive']);
            }

            if (!defined("SHOUTBOXJS")) {
                add_to_jquery("$('.shoutbox-delete-btn').on('click', function(evt) {
                    return confirm('".self::$locale['SB_warning_shout']."');
                });");
                define("SHOUTBOXJS", TRUE);
            }
            render_shoutbox($info);
        }

    }

    public function getShoutboxData($archive = FALSE) {
        $limit = $archive ? self::$arch_limit : self::$limit;

        $total_rows = dbcount("(shout_id)", DB_SHOUTBOX, (multilang_table("SB") ? in_group('shout_language', LANGUAGE) : ''));
        $rows = check_get('rows') && get('rows') <= $total_rows ? get('rows') : 0;
        $result = $this->selectDb($archive ? $rows : 0, $limit);
        $db_rows = dbrows($result);

        $sdata = [
            'items'   => [],
            'archive' => [],
            'pagenav' => ''
        ];

        if ($db_rows > 0) {
            while ($data = dbarray($result)) {
                $data['user_name'] = !empty($data['user_id']) ? $data['user_name'] : $data['shout_name'];

                $shout_id = $this->doSecureShoutId($data['shout_id']);

                if (iMEMBER || self::$sb_settings['guest_shouts']) {
                    $data['reply_link'] = INFUSIONS.'shoutbox_panel/shoutbox_archive.php?s_action=reply&shout_id='.$shout_id;
                    $data['reply_title'] = self::$locale['SB_reply'];
                }

                if ((iADMIN && checkrights("S")) || (iMEMBER && $data['shout_name'] == fusion_get_userdata('user_id') && isset($data['user_name']))) {
                    $data['edit_link'] = INFUSIONS.'shoutbox_panel/shoutbox_archive.php?s_action=edit&shout_id='.$shout_id;
                    $data['edit_title'] = self::$locale['edit'];
                    $data['delete_link'] = INFUSIONS.'shoutbox_panel/shoutbox_archive.php?s_action=delete&shout_id='.$shout_id;
                    $data['delete_title'] = self::$locale['delete'];
                }

                $data['profile_link'] = profile_link($data['shout_name'], $data['user_name'], $data['user_status']);
                $data['message'] = parse_text($data['shout_message'], [
                    'decode'               => FALSE,
                    'default_image_folder' => NULL,
                    'add_line_breaks'      => TRUE
                ]);

                $data['message'] = preg_replace('#\[shoutid-(.*?)\]#i', '<a href="#shout\1">#\1</a>', $data['message']);

                $sdata['items'][] = $data;
            }
        }

        if ($archive == TRUE) {
            $sdata['pagenav'] = $total_rows > $db_rows ? makepagenav($rows, self::$arch_limit, $total_rows, self::$arch_limit, INFUSIONS.'shoutbox_panel/shoutbox_archive.php?', 'rows') : '';
        }

        if ($total_rows > self::$sb_settings['visible_shouts']) {
            $sdata['archive'] = [
                'link'  => INFUSIONS.'shoutbox_panel/shoutbox_archive.php',
                'title' => self::$locale['SB_archive']
            ];
        }

        return $sdata;
    }

    private function doSecureShoutId($shout_id) {
        $userdata = fusion_get_userdata();
        $value = $shout_id.".".$userdata["user_id"].".".time();
        $hash = session_get("shout_token_hash");

        return urlencode(fusion_encrypt($value, $hash));
    }
}
