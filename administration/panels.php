<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: panels.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('P');

class PanelsAdministration {
    private static $locale = [];

    private $data = [
        'panel_id'          => 0,
        'panel_name'        => '',
        'panel_filename'    => '',
        'panel_content'     => '',
        'panel_type'        => 'php',
        'panel_php_exe'     => 0,
        'panel_side'        => 1,
        'panel_order'       => 0,
        'panel_access'      => 0,
        'panel_display'     => 0,
        'panel_status'      => 0,
        'panel_url_list'    => '',
        'panel_restriction' => 3,
        'panel_languages'   => LANGUAGE
    ];

    /**
     * @var string
     */
    private $formaction = '';
    /**
     * @var array
     */
    private $panel_data;

    /**
     * Sanitization Globals Vars
     */
    public function __construct() {

        $aidlink = fusion_get_aidlink();

        self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/panels.php');

        $this->data['panel_content'] = stripslashes($this->data['panel_content']);

        $result = dbquery("SELECT * FROM ".DB_PANELS." ORDER BY panel_side ASC, panel_order ASC");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->panel_data[$data['panel_side']][] = $data;
            }
        }

        switch (get('action')) {
            case 'edit':
                if (check_get('panel_id') && get('panel_id', FILTER_SANITIZE_NUMBER_INT)) {
                    $result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_id='".get('panel_id')."'");
                    if (dbrows($result) > 0) {
                        $this->data = dbarray($result);
                    }
                    $this->formaction = FUSION_SELF.$aidlink."&section=panelform&action=edit&panel_id=".get('panel_id');
                } else {
                    redirect(FUSION_SELF.$aidlink);
                }
                break;
            case 'setstatus':
                if (self::verifyPanel(get('panel_id'))) {
                    dbquery("UPDATE ".DB_PANELS." SET panel_status='".get('panel_status')."' WHERE panel_id='".get('panel_id')."'");
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
                break;
            case 'delete':
                if (self::verifyPanel(get('panel_id'))) {
                    $data = dbarray(dbquery("SELECT panel_side, panel_order FROM ".DB_PANELS." WHERE panel_id='".get('panel_id')."'"));
                    dbquery("DELETE FROM ".DB_PANELS." WHERE panel_id='".get('panel_id')."'");
                    dbquery("UPDATE ".DB_PANELS." SET panel_order=panel_order-1 WHERE panel_side='".intval($data['panel_side'])."' AND panel_order>='".intval($data['panel_order'])."'");
                    addnotice('success', self::$locale['PANEL_489']);
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
                break;
            default:
                $this->formaction = FUSION_SELF.$aidlink."&section=panelform";
        }
        add_breadcrumb(['link' => ADMIN.'panels.php'.$aidlink, 'title' => self::$locale['PANEL_600']]);
    }

    /**
     * Return panel positions array
     *
     * @return array
     */
    private static function getPanelGrid() {
        return [
            1  => self::$locale['PANEL_420'],
            2  => self::$locale['PANEL_421'],
            3  => self::$locale['PANEL_425'],
            4  => self::$locale['PANEL_422'],
            5  => self::$locale['PANEL_426'],
            6  => self::$locale['PANEL_427'],
            7  => self::$locale['PANEL_428a'],
            8  => self::$locale['PANEL_428b'],
            9  => self::$locale['PANEL_428c'],
            10 => self::$locale['PANEL_428d']
        ];
    }

    /**
     * Checks if a panel id is valid
     *
     * @param $id
     *
     * @return bool|int
     */
    static function verifyPanel($id) {
        if (isnum($id)) {
            return dbcount("(panel_id)", DB_PANELS, "panel_id='".intval($id)."'");
        }

        return FALSE;
    }

    /**
     * MYSQL save/update panels
     */
    private function setPanelDb() {
        $aidlink = fusion_get_aidlink();
        $locale = fusion_get_locale();

        if (check_post('panel_save')) {
            $this->data['panel_id'] = sanitizer('panel_id', '0', 'panel_id');
            $this->data['panel_name'] = sanitizer('panel_name', '', 'panel_name');
            $this->data['panel_side'] = sanitizer('panel_side', 1, 'panel_side');
            $this->data['panel_access'] = sanitizer('panel_access', '0', 'panel_access');
            // panel name is unique
            $result = dbcount("(panel_id)", DB_PANELS, "panel_name='".$this->data['panel_name']."' AND panel_id !='".$this->data['panel_id']."'");
            if ($result) {
                fusion_stop();
                addnotice('danger', self::$locale['PANEL_471']);
            }
            $this->data['panel_filename'] = sanitizer('panel_filename', '', 'panel_filename');
            // panel content formatting
            if ($this->data['panel_filename'] == 'none') {
                $this->data['panel_php_exe'] = sanitizer('panel_php_exe', 0, 'panel_php_exe');
                $this->data['panel_type'] = "php";
                $this->data['panel_content'] = addslashes(post('panel_content'));
                if (!$this->data['panel_content']) {
                    $this->data['panel_content'] = "opentable(\"name\");\n"."echo \"".$locale['PANEL_469a']."\";\n"."closetable();";
                    if ($this->data['panel_side'] == 1 || $this->data['panel_side'] == 4) {
                        $this->data['panel_content'] = "openside(\"name\");\n"."echo \"".$locale['PANEL_469a']."\";\n"."closeside();";
                    }
                }
            } else {
                $this->data['panel_content'] = '';
                $this->data['panel_type'] = "file";
            }
            // need to add fourth option. only show in front page.
            $this->data['panel_restriction'] = sanitizer('panel_restriction', 0, 'panel_restriction');
            // 3, show on all, 2 = show on home page. 1 = exclude , 0 = include
            //  post 0 to include all , 1 to exclude all, show all.
            if ($this->data['panel_restriction'] == '3') { // show on all
                $this->data['panel_display'] = ($this->data['panel_side'] != 1 && $this->data['panel_side'] != 4) ? 1 : 0;
                $this->data['panel_url_list'] = '';
            } else if ($this->data['panel_restriction'] == '2') {
                // show on homepage only
                $this->data['panel_display'] = 0;
                $this->data['panel_url_list'] = '';
            } else {
                // require panel_url_list in this case
                $this->data['panel_url_list'] = sanitizer('panel_url_list', '', 'panel_url_list');
                if ($this->data['panel_url_list']) {
                    $this->data['panel_url_list'] = str_replace(",", "\r\n", $this->data['panel_url_list']);
                    $this->data['panel_display'] = ($this->data['panel_side'] != 1 && $this->data['panel_side'] != 4) ? 1 : 0;
                } else {
                    fusion_stop();
                    addnotice('danger', self::$locale['PANEL_475']);
                }
            }

            $panel_languages = !empty(post(['panel_languages'])) ? \Defender::sanitize_array(post(['panel_languages'])) : [];
            if (!empty($panel_languages)) {
                $this->data['panel_languages'] = implode('.', $panel_languages);
            }

            if (fusion_safe()) {
                if ($this->data['panel_id'] && self::verifyPanel($this->data['panel_id'])) {
                    // Panel Update
                    dbquery_insert(DB_PANELS, $this->data, 'update');
                    addnotice('success', self::$locale['PANEL_482']);
                } else {
                    // Panel Save
                    $result = dbquery("SELECT panel_order FROM ".DB_PANELS." WHERE panel_side='".intval($this->data['panel_side'])."' ORDER BY panel_order DESC LIMIT 1");
                    if (dbrows($result) != 0) {
                        $data = dbarray($result);
                        $this->data['panel_order'] = $data['panel_order'] + 1;
                    } else {
                        $this->data['panel_order'] = 1;
                    }
                    dbquery_insert(DB_PANELS, $this->data, 'save');
                    addnotice('success', self::$locale['PANEL_485']);
                }
            }

            // Regulate Panel Ordering
            $result = dbquery("SELECT panel_id, panel_side FROM ".DB_PANELS." ORDER BY panel_side ASC, panel_order ASC");
            if (dbrows($result)) {
                $current_side = 0;
                $order = '';
                while ($data = dbarray($result)) {
                    $panel_id = $data['panel_id'];
                    $panel_side = $data['panel_side'];
                    if ($panel_side !== $current_side) {
                        $order = 0;
                    }
                    $order = $order + 1;
                    dbquery("UPDATE ".DB_PANELS." SET panel_order=:order WHERE panel_id=:panel_id", [':order' => $order, ':panel_id' => $panel_id]);
                    $current_side = $panel_side;
                }
            }
            if (fusion_safe()) {
                redirect(FUSION_SELF.$aidlink."&section=listpanel");
            }
        }
    }

    public function displayAdmin() {
        opentable(self::$locale['PANEL_600']);
        $edit = check_get('action') && get('action') == 'edit' ? $this->verifyPanel(get('panel_id')) : 0;

        $tabs['title'][] = self::$locale['PANEL_407'];
        $tabs['id'][] = 'listpanel';
        $tabs['icon'][] = '';
        $tabs['title'][] = $edit ? self::$locale['PANEL_409'] : self::$locale['PANEL_408'];
        $tabs['id'][] = 'panelform';
        $tabs['icon'][] = '';
        $tabs['title'][] = fusion_get_locale('admins_448', LOCALE.LOCALESET.'admin/settings.php');
        $tabs['id'][] = 'settings';
        $tabs['icon'][] = '';

        $allowed_sections = ['listpanel', 'panelform', 'settings'];
        $sections = in_array(get('section'), $allowed_sections) ? get('section') : 'listpanel';
        echo opentab($tabs, $sections, 'id', TRUE);
        switch ($sections) {
            case 'panelform':
                $this->addPanelForm();
                break;
            case 'settings':
                $this->settings();
                break;
            default:
                $this->panelListing();
                break;
        }

        echo closetab();
        closetable();
    }

    private function settings() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        if (check_post('savesettings')) {
            $inputData = [
                'exclude_left'   => sanitizer('exclude_left', '', 'exclude_left'),
                'exclude_upper'  => sanitizer('exclude_upper', '', 'exclude_upper'),
                'exclude_aupper' => sanitizer('exclude_aupper', '', 'exclude_aupper'),
                'exclude_lower'  => sanitizer('exclude_lower', '', 'exclude_lower'),
                'exclude_blower' => sanitizer('exclude_blower', '', 'exclude_blower'),
                'exclude_right'  => sanitizer('exclude_right', '', 'exclude_right'),
                'exclude_user1'  => sanitizer('exclude_user1', '', 'exclude_user1'),
                'exclude_user2'  => sanitizer('exclude_user2', '', 'exclude_user2'),
                'exclude_user3'  => sanitizer('exclude_user3', '', 'exclude_user3'),
                'exclude_user4'  => sanitizer('exclude_user4', '', 'exclude_user4')
            ];

            if (fusion_safe()) {
                foreach ($inputData as $settings_name => $settings_value) {
                    dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                        ':settings_value' => $settings_value,
                        ':settings_name'  => $settings_name
                    ]);
                }

                addnotice('success', $locale['settings_updated']);
                redirect(FUSION_REQUEST);
            }
        }

        echo "<div class='m-b-20'>".$locale['PANEL_463']."<br>
        /index.php<br>
        /infusions/news*<br>
        /infusions/news/news.php<br>
        /infusions/forum*<br>
        /infusions/forum/index.php<br>
        </div>\n";

        echo openform('settingsform', 'post', FUSION_REQUEST);
        echo form_textarea('exclude_aupper', $locale['PANEL_426'], $settings['exclude_aupper'], ['autosize' => TRUE]);
        echo '<div class="row">';
        echo '<div class="col-xs-12 col-sm-3">';
        echo form_textarea('exclude_left', $locale['PANEL_420'], $settings['exclude_left'], ['autosize' => TRUE]);
        echo '</div>';
        echo '<div class="col-xs-12 col-sm-6">';
        echo form_textarea('exclude_upper', $locale['PANEL_421'], $settings['exclude_upper'], ['autosize' => TRUE]);
        echo '<div class="hidden-xs hidden-sm well text-center">'.fusion_get_locale('page_0441', LOCALE.LOCALESET.'admin/custom_pages.php').'</div>';
        echo form_textarea('exclude_lower', $locale['PANEL_425'], $settings['exclude_lower'], ['autosize' => TRUE]);
        echo '</div>';
        echo '<div class="col-xs-12 col-sm-3">';
        echo form_textarea('exclude_right', $locale['PANEL_422'], $settings['exclude_right'], ['autosize' => TRUE]);
        echo '</div>';
        echo '</div>';

        echo form_textarea('exclude_blower', $locale['PANEL_427'], $settings['exclude_blower'], ['autosize' => TRUE]);

        echo '<div class="row">';
        echo '<div class="col-xs-12 col-sm-3">';
        echo form_textarea('exclude_user1', $locale['PANEL_428a'], $settings['exclude_user1'], ['autosize' => TRUE]);
        echo '</div>';
        echo '<div class="col-xs-12 col-sm-3">';
        echo form_textarea('exclude_user2', $locale['PANEL_428b'], $settings['exclude_user2'], ['autosize' => TRUE]);
        echo '</div>';
        echo '<div class="col-xs-12 col-sm-3">';
        echo form_textarea('exclude_user3', $locale['PANEL_428c'], $settings['exclude_user3'], ['autosize' => TRUE]);
        echo '</div>';
        echo '<div class="col-xs-12 col-sm-3">';
        echo form_textarea('exclude_user4', $locale['PANEL_428d'], $settings['exclude_user4'], ['autosize' => TRUE]);
        echo '</div>';
        echo '</div>';
        echo form_button('savesettings', $locale['save_settings'], $locale['save_settings'], ['class' => 'btn-primary']);
        echo closeform();
    }

    /**
     * Current Panel Template
     */
    public function panelListing() {
        $aidlink = fusion_get_aidlink();

        add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui/jquery-ui.min.js'></script>");
        add_to_jquery("
        $('.panels-list, .cards-list').sortable({
                handle : '.handle',
                placeholder: 'state-highlight',
                connectWith: '.connected',
                scroll: true,
                axis: 'auto',
                update: function () {
                    var ul = $(this),
                        order = ul.sortable('serialize'),
                        i = 0;
                    $('#info').load('panels_updater.php".$aidlink."&'+order);
                    ul.find('.num').each(function(i) {
                        $(this).text(i+1);
                    });
                    ul.find('li').removeClass('tbl2').removeClass('tbl1');
                    ul.find('li:odd').addClass('tbl2');
                    ul.find('li:even').addClass('tbl1');
                    window.setTimeout('closeDiv();',2500);
                },
                receive: function () {
                    var ul = $(this),
                        order = ul.sortable('serialize'),
                        pdata = ul.attr('data-side');
                        if (pdata == 1) { var psidetext = '".self::$locale['PANEL_420']."'; }
                        if (pdata == 2) { var psidetext = '".self::$locale['PANEL_421']."'; }
                        if (pdata == 3) { var psidetext = '".self::$locale['PANEL_425']."'; }
                        if (pdata == 4) { var psidetext = '".self::$locale['PANEL_422']."'; }
                    ul.find('.pside').each(function() {
                        $(this).text(psidetext);
                    });
                    $('#info').load('panels_updater.php".$aidlink."&panel_side='+pdata+'&'+order);
                }
            });
        ");
        echo "<div id='info'></div>\n";
        echo "<div class='well text-center'>".self::$locale['PANEL_410']."</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
        echo self::panelReactor(5);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panelReactor(1);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-6'>\n";
        echo self::panelReactor(2);
        echo "<div class='well text-center strong text-dark'>".self::$locale['PANEL_606']."</div>\n";
        echo self::panelReactor(3);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panelReactor(4);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
        echo self::panelReactor(6);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panelReactor(7);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panelReactor(8);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panelReactor(9);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panelReactor(10);
        echo "</div>\n</div>\n";

        //Unused Panels in the directory
        $panel_list = self::panelsList();
        $string = format_word(count($panel_list), self::$locale['PANEL_604']);
        $title = self::$locale['PANEL_602'].": ".$string;
        echo "<div class='panel panel-default'>\n";
        echo "<div class='panel-heading'>".$title."</div>\n";
        echo "<div class='panel-body text-dark'>\n";

        foreach ($panel_list as $panel) {
            echo "<div style='float:left;'>".$panel."</div>\n";
            echo "<div style='float:right; width:250px;'>";
            echo "</div>\n";
            echo "<div style='float:right; width:10%;'>".self::$locale['PANEL_607']."</div>\n";
            echo "<div style='clear:both;'></div>\n";
        }
        echo "</div>\n</div>\n";
    }

    /**
     * The container for each grid positions
     *
     * @param $side
     *
     * @return string
     */
    private function panelReactor($side) {
        $aidlink = fusion_get_aidlink();

        $grid_opts = self::getPanelGrid();
        $type = $grid_opts[$side];
        $k = 0;
        $count = dbcount("('panel_id')", DB_PANELS, "panel_side='".$side."'");
        $title = $type." <span id='side-".$side."' class='badge num pull-right'>".$count."</span>";
        $html = "<div class='panel panel-default' style='border-style: dashed'>\n<div class='panel-body clearfix'>\n";
        $html .= "<i class='fa fa-desktop m-r-10'></i> $title ";
        $html .= "</div>\n";
        $html .= "<ul id='panel-side".$side."' data-side='".$side."' style='list-style: none;' class='panels-list connected list-group p-10'>\n";
        if (isset($this->panel_data[$side])) {
            foreach ($this->panel_data[$side] as $data) {
                $row_color = ($k % 2 == 0 ? "tbl1" : "tbl2");
                $type = $data['panel_type'] == "file" ? self::$locale['PANEL_423'] : self::$locale['PANEL_424'];
                $html .= "<li id='listItem_".$data['panel_id']."' style='border:1px solid #ddd;' class='pointer list-group-item ".$row_color.($data['panel_status'] == 0 ? " pdisabled" : '')."'>\n";
                $html .= "<div class='handle'>\n";
                $html .= "<i class='pull-right display-inline-block m-t-5 m-r-10 fa fa-arrows-alt' title='move'></i>\n";
                $html .= "<div class='overflow-hide'>\n";
                $html .= "<a id='dd".$data['panel_id']."' class='dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>\n";
                $html .= "<strong>".$data['panel_name']."</strong> <span class='caret'></span>\n\n";
                $html .= "</a>\n";
                $html .= "<ul class='dropdown-menu' aria-labelledby='dd".$data['panel_id']."'>\n";
                $html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-bullseye m-r-10 m-t-5'></i> ".getgroupname($data['panel_access'])."</li>\n";
                $html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-file-o m-r-10 m-t-5'></i><span class='badge'>".$type."</span></li>\n";
                $html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-arrows-v m-r-10'></i> ".$data['panel_order']."</li>\n";
                $html .= "<li class='divider'></li>\n";
                $html .= "<li class='dropdown-item'><a href='".FUSION_SELF.$aidlink."&section=panelform&action=edit&panel_id=".$data['panel_id']."'><i class='fa fa-pencil m-r-10 m-t-5'></i>".self::$locale['edit']."</a>\n</li>\n";
                if ($data['panel_status'] == 0) {
                    $html .= "<li class='dropdown-item'><a href='".FUSION_SELF.$aidlink."&action=setstatus&panel_status=1&panel_id=".$data['panel_id']."'><i class='fa fa-check m-r-10 m-t-5'></i>".self::$locale['PANEL_435']."</a>\n</li>\n";
                } else {
                    $html .= "<li class='dropdown-item'><a href='".FUSION_SELF.$aidlink."&action=setstatus&panel_status=0&panel_id=".$data['panel_id']."'><i class='fa fa-close m-r-10 m-t-5'></i>".self::$locale['PANEL_436']."</a>\n</li>\n";
                }
                $html .= "<li class='dropdown-item'><a href='".FUSION_SELF.$aidlink."&action=delete&panel_id=".$data['panel_id']."' onclick=\"return confirm('".self::$locale['PANEL_440']."');\"><i class='fa fa-trash m-r-10 m-t-5'></i>".self::$locale['delete']."</a>\n</li>\n";
                $html .= "</ul>\n";
                $html .= "</div>\n";
                $html .= "</div>\n";
                $html .= "</li>\n";
                $k++;
            }
        }
        $html .= "</ul>\n";
        $html .= "</div>\n";

        return $html;
    }

    /**
     * Panel array
     *
     * @return array
     */
    private function panelsList() {
        $panel_list = [];
        $panels = [];
        $result = dbquery("SELECT panel_id, panel_filename FROM ".DB_PANELS." ORDER BY panel_id");
        while ($data = dbarray($result)) {
            $panels[] = $data['panel_filename'];
        }

        if (!empty($panels)) {
            $temp = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
            foreach ($temp as $folder) {
                if (strstr($folder, "_panel")) {
                    if (!in_array($folder, $panels)) {
                        $panel_list[] = ucwords(str_replace('_', ' ', $folder));
                    }
                }
            }
        }
        sort($panel_list);

        return $panel_list;
    }

    /**
     * The Panel Editor Form
     */
    public function addPanelForm() {
        fusion_confirm_exit();

        if (check_post('cancel')) {
            redirect(clean_request('section=listpanel', ['action', 'panel_id', 'section'], FALSE));
        }

        self::setPanelDb();

        if (check_post('panel_preview')) {
            $panel_title = sanitizer('panel_name', "", "panel_name");
            if (fusion_safe()) {
                ob_start();
                echo openmodal('cp_preview', $panel_title);
                if (post('panel_php_exe')) {
                    ob_start();
                    eval(stripslashes(post('panel_content')));
                    $eval = ob_get_contents();
                    ob_end_clean();
                    echo $eval;
                } else {
                    echo parse_text(post('panel_content'), ['parse_smileys' => FALSE, 'parse_bbcode' => FALSE, 'add_line_breaks' => TRUE]);
                }
                echo closemodal();
                add_to_footer(ob_get_contents());
                ob_end_clean();
            }
            $this->data = [
                "panel_id"          => sanitizer('panel_id', 0, "panel_id"),
                "panel_name"        => sanitizer('panel_name', "", "panel_name"),
                "panel_filename"    => sanitizer('panel_filename', "", "panel_filename"),
                "panel_side"        => sanitizer('panel_side', "", "panel_side"),
                "panel_php_exe"     => sanitizer('panel_php_exe', "", "panel_php_exe"),
                "panel_content"     => sanitizer('panel_content', "", "panel_content"),
                "panel_restriction" => sanitizer('panel_restriction', "", "panel_restriction"),
                "panel_url_list"    => sanitizer('panel_url_list', "", "panel_url_list"),
                "panel_display"     => sanitizer('panel_display', "", "panel_display"),
                "panel_access"      => sanitizer('panel_access', iGUEST, "panel_access"),
                "panel_languages"   => sanitizer('panel_languages', LANGUAGE, "panel_languages")
            ];
        }

        echo openform('panel_form', 'post', $this->formaction);

        echo "<div class='m-b-10'>\n";
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default m-r-5', 'input_id' => 'btn1']);
        echo form_button('panel_save', self::$locale['PANEL_461'], self::$locale['PANEL_460'], ['class' => 'm-r-5 btn-success', 'input_id' => 'btn3']);
        echo form_button('panel_preview', self::$locale['preview'], self::$locale['preview'], ['class' => 'btn-default', 'input_id' => 'btn2']);
        echo "</div>\n";


        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-8'>\n";
        openside('');
        echo form_hidden('panel_id', '', $this->data['panel_id']);
        echo form_text('panel_name', self::$locale['PANEL_452'], $this->data['panel_name'], [
            'inline'   => TRUE,
            'required' => TRUE
        ]);
        echo form_select('panel_filename', self::$locale['PANEL_453'], $this->data['panel_filename'], [
            'options' => self::getPanelOpts(),
            'inline'  => TRUE
        ]);
        $grid_opts = self::getPanelGrid();
        echo form_select('panel_side', self::$locale['PANEL_457'], $this->data['panel_side'], [
            'options' => $grid_opts,
            'inline'  => TRUE
        ]);
        closeside();
        openside('');
        add_to_jquery("
        ".(($this->data['panel_restriction'] == 3 || $this->data['panel_restriction'] == 2) ? "$('#panel_url_list-grp').hide();" : '')."
        $('#panel_restriction').bind('change', function(e) {
            if ($(this).val() == '3' || $(this).val() == '2') { $('#panel_url_list-grp').hide(); } else { $('#panel_url_list-grp').show(); }
        });
        ");
        echo form_select('panel_restriction', self::$locale['PANEL_468'], $this->data['panel_restriction'], [
            'options' => [
                3 => self::$locale['PANEL_459'], // Display panel on all pages
                2 => self::$locale['PANEL_467'], // Display on Opening Page only
                1 => self::$locale['PANEL_464'], // Exclude on these pages only
                0 => self::$locale['PANEL_465'], // Include on these pages only
            ],
            'inline'  => TRUE
        ]);
        echo "<div id='panel_url_list-grp'>\n";
        echo form_textarea('panel_url_list', self::$locale['PANEL_462'], $this->data['panel_url_list'], [
            'inline' => FALSE,
            //'required' => TRUE
        ]);
        echo "<div class='text-smaller'>".self::$locale['PANEL_463']."<br>
        /index.php<br>
        /infusions/news*<br>
        /infusions/news/news.php<br>
        /infusions/forum*<br>
        /infusions/forum/index.php<br>
        </div>\n";
        echo "</div>\n";
        echo form_hidden('panel_display', '', $this->data['panel_display']);
        closeside();
        add_to_jquery("
        ".((!empty($this->data['panel_filename']) && $this->data['panel_filename'] !== "none") ? "$('#pgrp').hide();" : "$('#pgrp').show();")."
        $('#panel_filename').bind('change', function(e) {
            var panel_val = $(this).val();

            if ($(this).val() !='none') { $('#pgrp').hide(); } else { $('#pgrp').show(); }
        });
        ");

        echo "<div id='pgrp'>\n";
        echo form_textarea('panel_content', self::$locale['PANEL_455'], $this->data['panel_content'], [
            'html'      => TRUE,
            'form_name' => 'panel_form',
            'autosize'  => TRUE,
            'preview'   => !$this->data['panel_php_exe'],
            'descript'  => !$this->data['panel_php_exe']
        ]);
        echo "</div>\n";

        echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
        openside('');
        echo form_select('panel_access', self::$locale['PANEL_458'], $this->data['panel_access'], ['options' => self::getAccessOpts()]);
        closeside();
        openside('');
        echo "<label class='label-control m-b-10'>".self::$locale['PANEL_466']."</label>\n";

        $languages = !empty($this->data['panel_languages']) && stristr($this->data['panel_languages'], ".") ? explode('.',
            $this->data['panel_languages']) : $this->data['panel_languages'];
        if (!empty($languages) && is_array($languages)) {
            $languages = array_flip($languages);
        }

        foreach (fusion_get_enabled_languages() as $language_key => $language_name) {

            if (!empty($languages) && is_array($languages)) {
                $value = isset($languages[$language_key]) ? $language_key : "";
            } else {
                $value = $languages == $language_key ? $languages : "";
            }

            echo form_checkbox('panel_languages[]', $language_name, $value, [
                'class'         => 'm-b-0',
                'value'         => $language_key,
                "reverse_label" => TRUE,
                'input_id'      => 'panel_lang-'.$language_key
            ]);
        }
        closeside();
        openside('');
        echo alert(fusion_get_locale('admins_695', LOCALE.LOCALESET.'admin/settings.php'));
        echo form_checkbox('panel_php_exe', fusion_get_locale('admins_694', LOCALE.LOCALESET.'admin/settings.php'), $this->data['panel_php_exe'], ['toggle' => TRUE]);

        closeside();
        echo "</div>\n";
        echo "</div>\n";
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default m-r-5']);
        echo form_button('panel_save', self::$locale['PANEL_461'], self::$locale['PANEL_461'], ['class' => 'm-r-5 btn-success']);
        echo form_button('panel_preview', self::$locale['preview'], self::$locale['preview'], ['class' => 'btn-default']);
        echo closeform();

    }

    /**
     * Return list of panels
     *
     * @return array
     */
    private function getPanelOpts() {
        $current_panels = [];
        if (!empty($this->panel_data)) {
            foreach ($this->panel_data as $panels) {
                foreach ($panels as $data) {
                    $current_panels[$data['panel_filename']] = $data['panel_filename'];
                }
            }
        }

        // unset this panel if edit mode.
        if (check_get('panel_id') && get('panel_id', FILTER_SANITIZE_NUMBER_INT) && check_get('action') && get('action') == 'edit') {
            unset($current_panels[$this->data['panel_filename']]);
        }

        return \PHPFusion\Panels::getAvailablePanels($current_panels);
    }

    /**
     * Return user groups array
     *
     * @return array
     */
    static function getAccessOpts() {
        $ref = [];
        $user_groups = getusergroups();

        foreach ($user_groups as $user_group) {
            $ref[$user_group[0]] = $user_group[1];
        }

        return $ref;
    }

}

$panel = new PanelsAdministration();
$panel->displayAdmin();

require_once THEMES.'templates/footer.php';
