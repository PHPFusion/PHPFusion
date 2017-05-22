<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: panels.php
| Author: PHP-Fusion Development Team
| Author: Robert Gaudyn (Wooya)
| Author: Joakim Falk (Falk)
| Author: Frederick MC Chan (Chan)
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
pageAccess("P");
require_once THEMES."templates/admin_header.php";

/**
 * Class fusion_panels
 */
class fusion_panel_admin {
    /**
     * @var array|bool
     */
    private static $locale = array();

    private $data = array(
        'panel_id'          => 0,
        'panel_name'        => '',
        'panel_filename'    => '',
        'panel_content'     => '',
        'panel_type'        => 'php',
        'panel_side'        => TRUE,
        'panel_order'       => 0,
        'panel_access'      => 0,
        'panel_display'     => 0,
        'panel_status'      => 0,
        'panel_url_list'    => '',
        'panel_restriction' => 3,
        'panel_languages'   => ''
    );

    /**
     * @var string
     */
    private $formaction = '';
    /**
     * @var array
     */
    private $panel_data = array();

    /**
     * Sanitization Globals Vars
     */
    public function __construct() {

        $aidlink = fusion_get_aidlink();

        $this->set_locale();

        $this->data['panel_languages'] = LANGUAGE;
        $this->data['panel_content'] = stripslashes($this->data['panel_content']);
        $_GET['panel_side'] = isset($_GET['panel_side']) && in_array($_GET['panel_side'],
            array_flip(self::get_panel_grid())) ? $_GET['panel_side'] : 0;
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
        $_GET['status'] = isset($_GET['status']) ? $_GET['status'] : '';
        $_GET['panel_status'] = isset($_GET['panel_status']) ? $_GET['panel_status'] : 0;

        $this->panel_data = self::load_all_panels();

        switch ($_GET['action']) {
            case 'edit':
                if (isset($_GET['panel_id'])) {
                    $this->data = self::load_panel($_GET['panel_id']);
                    $this->formaction = FUSION_SELF.$aidlink."&amp;section=panelform&amp;action=edit&amp;panel_id=".$_GET['panel_id'];
                } else {
                    redirect(FUSION_SELF.$aidlink);
                }
                break;
            case 'setstatus' :
                self::set_panel_status();
                break;
            case 'delete':
                self::delete_panel($_GET['panel_id']);
                break;
            default:
                $this->formaction = FUSION_SELF.$aidlink."&amp;section=panelform";
        }
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'panels.php'.$aidlink, 'title' => self::$locale['600']]);
        self::set_paneldb();
    }

    private static function set_locale() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/panels.php");
    }

    /**
     * Return panel positions array
     *
     * @return array
     */
    private static function get_panel_grid() {

        return array(
            1  => self::$locale['420'],
            2  => self::$locale['421'],
            3  => self::$locale['425'],
            4  => self::$locale['422'],
            5  => self::$locale['426'],
            6  => self::$locale['427'],
            7  => self::$locale['428a'],
            8  => self::$locale['428b'],
            9  => self::$locale['428c'],
            10 => self::$locale['428d']
        );
    }

    /**
     * Load entire DB_PANELS table
     *
     * @return array
     */
    private function load_all_panels() {
        $list = array();
        $result = dbquery("SELECT * FROM ".DB_PANELS." ORDER BY panel_side ASC, panel_order ASC");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $list[$data['panel_side']][] = $data;
            }
        }

        return $list;
    }

    /**
     * Fetch data for one panel
     *
     * @param $id
     *
     * @return array|bool
     */
    static function load_panel($id) {
        if (isnum($id)) {
            $result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_id='".intval($id)."'");
            if (dbrows($result) > 0) {
                return dbarray($result);
            }
        }

        return array();
    }

    /**
     * MYSQL actions set active or inactive
     */
    private static function set_panel_status() {

        $id = $_GET['panel_id'];
        if (self::verify_panel($id) && isnum($_GET['panel_status'])) {
            dbquery("UPDATE ".DB_PANELS." SET panel_status='".intval($_GET['panel_status'])."' WHERE panel_id='".intval($id)."'");
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
    }

    /**
     * Checks if a panel id is valid
     *
     * @param $id
     *
     * @return bool|string
     */
    static function verify_panel($id) {
        if (isnum($id)) {
            return dbcount("(panel_id)", DB_PANELS, "panel_id='".intval($id)."'");
        }

        return FALSE;
    }

    /**
     * MYSQL actions delete panel
     *
     * @param $id
     */
    private static function delete_panel($id) {
        if (self::verify_panel($id)) {
            $data = dbarray(dbquery("SELECT panel_side, panel_order FROM ".DB_PANELS." WHERE panel_id='".intval($_GET['panel_id'])."'"));
            dbquery("DELETE FROM ".DB_PANELS." WHERE panel_id='".intval($_GET['panel_id'])."'");
            dbquery("UPDATE ".DB_PANELS." SET panel_order=panel_order-1 WHERE panel_side='".intval($data['panel_side'])."' AND panel_order>='".intval($data['panel_order'])."'");
            addNotice('warning', self::$locale['489']);
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
    }

    /**
     * MYSQL save/update panels
     */
    private function set_paneldb() {
        global $defender;

        $aidlink = fusion_get_aidlink();

        if (isset($_POST['panel_save'])) {
            $this->data['panel_id'] = isset($_POST['panel_id']) ? form_sanitizer($_POST['panel_id'], '0', 'panel_id') : 0;
            $this->data['panel_name'] = isset($_POST['panel_name']) ? form_sanitizer($_POST['panel_name'], '', 'panel_name') : '';
            $this->data['panel_side'] = isset($_POST['panel_side']) ? form_sanitizer($_POST['panel_side'], 1, 'panel_side') : 1;
            $this->data['panel_access'] = isset($_POST['panel_access']) ? form_sanitizer($_POST['panel_access'], '0', 'panel_access') : 0;
            // panel name is unique
            $result = dbcount("(panel_id)", DB_PANELS, "panel_name='".$this->data['panel_name']."' AND panel_id !='".$this->data['panel_id']."'");
            if ($result) {
                $defender->stop();
                addNotice('danger', self::$locale['471']);
            }
            $this->data['panel_filename'] = isset($_POST['panel_filename']) ? form_sanitizer($_POST['panel_filename'], '', 'panel_filename') : '';
            // panel content formatting
            if ($this->data['panel_filename'] == 'none') {
                $this->data['panel_type'] = "php";
                $this->data['panel_content'] = isset($_POST['panel_content']) ? addslashes($_POST['panel_content']) : '';
                if (!$this->data['panel_content']) {
                    $this->data['panel_content'] = "opentable(\"name\");\n"."echo \"".$locale['469a']."\";\n"."closetable();";
                    if ($this->data['panel_side'] == 1 || $this->data['panel_side'] == 4) {
                        $this->data['panel_content'] = "openside(\"name\");\n"."echo \"".$locale['469a']."\";\n"."closeside();";
                    }
                }
            } else {
                $this->data['panel_content'] = '';
                $this->data['panel_type'] = "file";
            }
            // need to add fourth option. only show in front page.
            $this->data['panel_restriction'] = isset($_POST['panel_restriction']) ? form_sanitizer($_POST['panel_restriction'], '', 'panel_restriction') : 0;
            // 3, show on all, 2 = show on home page. 1 = exclude , 0 = include
            //  post 0 to include all , 1 to exclude all, show all.
            if ($this->data['panel_restriction'] == '3') { // show on all
                $this->data['panel_display'] = ($this->data['panel_side'] !== 1 && $this->data['panel_side'] !== 4) ? 1 : 0;
                $this->data['panel_url_list'] = '';
            } elseif ($this->data['panel_restriction'] == '2') {
                // show on homepage only
                $this->data['panel_display'] = 0;
                $this->data['panel_url_list'] = '';
                if ($this->data['panel_side'] == 1 || $this->data['panel_side'] == 4) {
                    $this->data['panel_url_list'] = fusion_get_settings('opening_page'); // because 1 and 4 directly overide panel_display.
                }
            } else {
                // require panel_url_list in this case
                $this->data['panel_url_list'] = isset($_POST['panel_url_list']) ? form_sanitizer($_POST['panel_url_list'], '', 'panel_url_list') : '';
                if ($this->data['panel_url_list']) {
                    $this->data['panel_url_list'] = str_replace(",", "\r\n", $this->data['panel_url_list']);
                    if ($this->data['panel_restriction'] == 1) { // exclude mode
                        $this->data['panel_display'] = ($this->data['panel_side'] !== 1 && $this->data['panel_side'] !== 4) ? 1 : 0;
                    } else { // include mode
                        $this->data['panel_display'] = ($this->data['panel_side'] !== 1 && $this->data['panel_side'] !== 4) ? 1 : 0;
                    }
                } else {
                    $defender->stop();
                    addNotice('danger', self::$locale['475']);
                }
            }
            $panel_languages = isset($_POST['panel_languages']) ? \defender::sanitize_array($_POST['panel_languages']) : array();
            if (!empty($panel_languages)) {
                $this->data['panel_languages'] = implode('.', $panel_languages);
            }

            if ($this->data['panel_id'] && self::verify_panel($this->data['panel_id'])) {
                // Panel Update
                dbquery_insert(DB_PANELS, $this->data, 'update');
                addNotice('success', self::$locale['482']);
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
                addNotice('success', self::$locale['485']);
            }

            // Regulate Panel Ordering
            $result = dbquery("SELECT panel_id, panel_side FROM ".DB_PANELS." ORDER BY panel_side ASC, panel_order ASC");
            if (dbrows($result)) {
                $current_side = 0;
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
            if (\defender::safe()) {
                redirect(FUSION_SELF.$aidlink."&amp;section=listpanel");
            }
        }
    }

    public function display_admin() {
        // do the table
        opentable(self::$locale['600']);
        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $this->verify_panel($_GET['panel_id']) : 0;

        // build a new interface
        $tab_title['title'][] = self::$locale['407'];
        $tab_title['id'][] = 'listpanel';
        $tab_title['icon'][] = '';
        $tab_title['title'][] = $edit ? self::$locale['409'] : self::$locale['408'];
        $tab_title['id'][] = 'panelform';
        $tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
        $tab_active = tab_active($tab_title, $edit ? 1 : 0, 'section');

        echo opentab($tab_title, $tab_active, 'id', TRUE);

        echo opentabbody($tab_title['title'][0], 'listpanel', $tab_active, 1);
        $this->panel_listing();
        echo closetabbody();

        if (isset($_GET['section']) && $_GET['section'] == 'panelform') {
            echo opentabbody($tab_title['title'][1], 'panelform', $tab_active, 1);
            $this->add_panel_form();
            echo closetabbody();
        }

        echo closetab();
        closetable();
    }

    /**
     * Current Panel Template
     */
    public function panel_listing() {
        $aidlink = fusion_get_aidlink();

        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui.js'></script>");
        add_to_jquery("
        $('.panels-list').sortable({
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
                        if (pdata == 1) { var psidetext = '".self::$locale['420']."'; }
                        if (pdata == 2) { var psidetext = '".self::$locale['421']."'; }
                        if (pdata == 3) { var psidetext = '".self::$locale['425']."'; }
                        if (pdata == 4) { var psidetext = '".self::$locale['422']."'; }
                    ul.find('.pside').each(function() {
                        $(this).text(psidetext);
                    });
                    $('#info').load('panels_updater.php".$aidlink."&panel_side='+pdata+'&'+order);
                }
            });
        ");
        echo "<div class='m-t-20'>\n";
        echo "<div id='info'></div>\n";
        echo "<div class='well text-center'>".self::$locale['410']."</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
        echo self::panel_reactor(5);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panel_reactor(1);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-6'>\n";
        echo self::panel_reactor(2);
        echo "<div class='well text-center strong text-dark'>".self::$locale['606']."</div>\n";
        echo self::panel_reactor(3);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panel_reactor(4);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
        echo self::panel_reactor(6);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panel_reactor(7);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panel_reactor(8);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panel_reactor(9);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo self::panel_reactor(10);
        echo "</div>\n</div>\n";
        echo "</div>\n";
        //Unused Panels in the directory
        $panel_list = self::panels_list();
        $string = \PHPFusion\Locale::format_word(count($panel_list), self::$locale['604']);
        $title = self::$locale['602'].": ".$string;
        echo "<div class='panel panel-default'>\n";
        echo "<div class='panel-heading'>".$title."</div>\n";
        echo "<div class='panel-body text-dark'>\n";
        $k = 0;
        foreach ($panel_list as $panel) {
            echo "<div style='float:left;'>".$panel."</div>\n";
            echo "<div style='float:right; width:250px;'>";
            echo "</div>\n";
            echo "<div style='float:right; width:10%;'>".self::$locale['607']."</div>\n";
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
    private function panel_reactor($side) {
        global $aidlink;

        $grid_opts = self::get_panel_grid();
        $type = $grid_opts[$side];
        $k = 0;
        $count = dbcount("('panel_id')", DB_PANELS, "panel_side='".$side."'");
        $title = $type." <span id='side-".$side."' class='badge num pull-right'>".$count."</span>";
        $html = '';
        $html .= "<div class='panel panel-default' style='border-style: dashed'>\n<div class='panel-body clearfix'>\n";
        $html .= "<i class='fa fa-desktop m-r-10'></i> $title ";
        $html .= "</div>\n";
        $html .= "<ul id='panel-side".$side."' data-side='".$side."' style='list-style: none;' class='panels-list connected list-group p-10'>\n";
        if (isset($this->panel_data[$side])) {
            foreach ($this->panel_data[$side] as $data) {
                $row_color = ($k % 2 == 0 ? "tbl1" : "tbl2");
                $type = $data['panel_type'] == "file" ? self::$locale['423'] : self::$locale['424'];
                $html .= "<li id='listItem_".$data['panel_id']."' style='border:1px solid #ddd;' class='pointer list-group-item ".$row_color.($data['panel_status'] == 0 ? " pdisabled" : '')."'>\n";
                $html .= "<div class='handle'>\n";
                $html .= "<i class='pull-right display-inline-block m-t-5 m-r-10 fa fa-arrows-alt' title='move'></i>\n";
                $html .= "<div class='overflow-hide'>\n";
                $html .= "<a class='dropdown-toggle' data-toggle='dropdown'>\n";
                $html .= "<strong>".$data['panel_name']."</strong> <span class='caret'></span>\n\n";
                $html .= "</a>\n";
                $html .= "<ul class='dropdown-menu' role='panel-options'>\n";
                $html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-bullseye m-r-10 m-t-5'></i> ".getgroupname($data['panel_access'])."</li>\n";
                $html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-file-o m-r-10 m-t-5'></i><span class='badge'>".$type."</span></li>\n";
                $html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-arrows-v m-r-10'></i> ".$data['panel_order']."</li>\n";
                $html .= "<li class='divider'></li>\n";
                $html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;section=panelform&amp;action=edit&amp;panel_id=".$data['panel_id']."'><i class='fa fa-pencil m-r-10 m-t-5'></i>".self::$locale['434']."</a>\n</li>\n";
                if ($data['panel_status'] == 0) {
                    $html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;panel_status=1&amp;panel_id=".$data['panel_id']."'><i class='fa fa-check m-r-10 m-t-5'></i>".self::$locale['435']."</a>\n</li>\n";
                } else {
                    $html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;panel_status=0&amp;panel_id=".$data['panel_id']."'><i class='fa fa-close m-r-10 m-t-5'></i>".self::$locale['436']."</a>\n</li>\n";
                }
                $html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;panel_id=".$data['panel_id']."' onclick=\"return confirm('".self::$locale['440']."');\"><i class='fa fa-trash m-r-10 m-t-5'></i>".self::$locale['437']."</a>\n</li>\n";
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
     * @param int|null $panel_id
     *
     * @return array|string
     */
    private function panels_list($panel_id = NULL) {
        $panel_list = array();
        $panels = array();
        $result = dbquery("SELECT panel_id, panel_filename FROM ".DB_PANELS." ORDER BY panel_id");
        while ($data = dbarray($result)) {
            $panels[] = $data['panel_filename'];
        }
        $temp = opendir(INFUSIONS);
        if (!empty($panels)) {
            while ($folder = readdir($temp)) {
                if (!in_array($folder, array(".", "..")) && strstr($folder, "_panel")) {
                    if (is_dir(INFUSIONS.$folder)) {
                        if (!in_array($folder, $panels)) {
                            $panel_list[] = ucwords(str_replace('_', ' ', $folder));
                        }
                    }
                }
            }
        }
        closedir($temp);
        if ($panel_id != NULL) {
            return $panel_list[$panel_id];
        }
        sort($panel_list);

        return (array)$panel_list;
    }

    /**
     * The Panel Editor Form
     */
    public function add_panel_form() {

        fusion_confirm_exit();

        $settings = fusion_get_settings();

        if (isset($_POST['cancel'])) {
            redirect(clean_request('section=listpanel', ['action', 'panel_id', 'section'], FALSE));
        }

        if (isset($_POST['panel_preview']) && $settings['allow_php_exe']) {
            $panel_title = form_sanitizer($_POST['panel_name'], "", "panel_name");
            if (\defender::safe()) {
                ob_start();
                echo openmodal("cp_preview", $panel_title);
                if (fusion_get_settings("allow_php_exe")) {
                    ob_start();
                    eval("?>".stripslashes($_POST['panel_content'])."<?php ");
                    $eval = ob_get_contents();
                    ob_end_clean();
                    echo $eval;
                } else {
                    echo "<p>".nl2br(parse_textarea($_POST['panel_content'], FALSE, FALSE))."</p>\n";
                }
                echo closemodal();
                add_to_footer(ob_get_contents());
                ob_end_clean();
            }
            $this->data = array(
                "panel_id"          => form_sanitizer($_POST['panel_id'], 0, "panel_id"),
                "panel_name"        => form_sanitizer($_POST['panel_name'], "", "panel_name"),
                "panel_filename"    => form_sanitizer($_POST['panel_filename'], "", "panel_filename"),
                "panel_side"        => form_sanitizer($_POST['panel_side'], "", "panel_side"),
                "panel_content"     => form_sanitizer($_POST['panel_content'], "", "panel_content"),
                "panel_restriction" => form_sanitizer($_POST['panel_restriction'], "", "panel_restriction"),
                "panel_url_list"    => form_sanitizer($_POST['panel_url_list'], "", "panel_url_list"),
                "panel_display"     => form_sanitizer($_POST['panel_display'], "", "panel_display"),
                "panel_access"      => form_sanitizer($_POST['panel_access'], iGUEST, "panel_access"),
                "panel_languages"   => !empty($_POST['panel_languages']) ? form_sanitizer($_POST['panel_languages'], "", "panel_languages") : LANGUAGE
            );
        }

        echo openform('panel_form', 'post', $this->formaction, ['class' => 'spacer-sm']);

        echo "<div class='spacer-xs'>\n";
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default m-r-10', 'input_id' => 'btn1']);
        if ($settings['allow_php_exe']) {
            echo form_button('panel_preview', self::$locale['preview'], self::$locale['preview'], array('class' => 'm-l-10 btn-default', 'input_id' => 'btn2'));
        }
        echo form_button('panel_save', self::$locale['461'], self::$locale['460'], array('class' => 'btn-success', 'input_id' => 'btn3'));
        echo "</div>\n";


        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-8'>\n";
        openside('');
        echo form_hidden('panel_id', '', $this->data['panel_id']);
        echo form_text('panel_name', self::$locale['452'], $this->data['panel_name'], array(
            'inline'   => TRUE,
            'required' => TRUE
        ));
        echo form_select('panel_filename', self::$locale['453'], $this->data['panel_filename'], array(
            'options' => self::get_panelOpts(),
            'inline'  => TRUE
        ));
        $grid_opts = self::get_panel_grid();
        echo form_select('panel_side', self::$locale['457'], $this->data['panel_side'], array(
            'options' => $grid_opts,
            'inline'  => TRUE
        ));
        closeside();
        openside('');
        add_to_jquery("
        ".(($this->data['panel_restriction'] == 3 || $this->data['panel_restriction'] == 2) ? "$('#panel_url_list-grp').hide();" : '')."
        $('#panel_restriction').bind('change', function(e) {
            if ($(this).val() == '3' || $(this).val() == '2') { $('#panel_url_list-grp').hide(); } else { $('#panel_url_list-grp').show(); }
        });
        ");
        echo form_select('panel_restriction', self::$locale['468'], $this->data['panel_restriction'], array(
            'options' => self::get_includeOpts(),
            'inline'  => TRUE
        ));
        echo "<div id='panel_url_list-grp'>\n";
        echo "<div class='text-smaller'></div>\n";
        echo form_select('panel_url_list', self::$locale['462'], $this->data['panel_url_list'], array(
            'options'     => self::get_panel_url_list(),
            'inline'      => TRUE,
            'tags'        => TRUE,
            'delimiter'   => "\r\n",
            'multiple'    => TRUE,
            'width'       => '100%',
            'inner_width' => '100%'
        ));
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
        echo form_textarea('panel_content', self::$locale['455'], $this->data['panel_content'], array(
            'html'      => fusion_get_settings("allow_php_exe") ? FALSE : TRUE,
            'form_name' => 'panel_form',
            'autosize'  => TRUE,
            'preview'   => fusion_get_settings("allow_php_exe") ? FALSE : TRUE,
        ));
        echo "</div>\n";

        echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
        openside('');
        echo form_select('panel_access', self::$locale['458'], $this->data['panel_access'], array("options" => self::get_accessOpts()));
        closeside();
        openside('');
        echo "<label class='label-control m-b-10'>".self::$locale['466']."</label>\n";

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

            echo form_checkbox('panel_languages[]', $language_name, $value, array(
                'class'         => 'm-b-0',
                'value'         => $language_key,
                "reverse_label" => TRUE,
                'input_id'      => 'panel_lang-'.$language_key
            ));
        }
        closeside();
        echo "</div>\n";
        echo "</div>\n";
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default m-r-10']);
        if ($settings['allow_php_exe']) {
            echo form_button('panel_preview', self::$locale['preview'], self::$locale['preview'], array('class' => 'm-l-10 btn-default'));
        }
        echo form_button('panel_save', self::$locale['461'], self::$locale['460'], array('class' => 'btn-success'));
        echo closeform();

    }

    /**
     * Return list of panels
     *
     * @return array
     */
    private function get_panelOpts() {
        $panel_list = array();
        $current_panels = array();
        foreach ($this->panel_data as $side => $panels) {
            foreach ($panels as $data) {
                $current_panels[$data['panel_filename']] = $data['panel_filename'];
            }
        }
        // unset this panel if edit mode.
        if (isset($_GET['panel_id']) && isnum($_GET['panel_id']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
            unset($current_panels[$this->data['panel_filename']]);
        }

        return \PHPFusion\Panels::get_available_panels($current_panels);
    }

    /**
     * Return restrictions type array
     *
     * @return array
     */
    private static function get_includeOpts() {

        return array(
            3 => self::$locale['459'],
            2 => self::$locale['467'],
            1 => self::$locale['464'],
            0 => self::$locale['465'],
        );
    }

    /**
     * Return page urls array
     *
     * @return array
     */
    static function get_panel_url_list() {
        $list = array();
        $file_list = makefilelist(BASEDIR, ".|..|.htaccess|.DS_Store|config.php|config.temp.php|.gitignore|LICENSE|README.md|robots.txt|reactivate.php|rewrite.php|maintenance.php|maincore.php|lostpassword.php|index.php|error.php");
        foreach ($file_list as $files) {
            $list[] = $files;
        }

        return $list;
    }

    /**
     * Return user groups array
     *
     * @return array
     */
    static function get_accessOpts() {
        $ref = array();
        $user_groups = getusergroups();
        while (list($key, $user_group) = each($user_groups)) {
            $ref[$user_group[0]] = $user_group[1];
        }

        return $ref;
    }

}

$panel = new fusion_panel_admin();
$panel->display_admin();

require_once THEMES."templates/footer.php";