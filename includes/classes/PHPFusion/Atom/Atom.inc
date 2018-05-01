<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom/Atom.inc
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
namespace PHPFusion\Atom;

require_once LOCALE.LOCALESET."admin/theme.php";

class Atom {
    public $target_folder = '';
    public $theme_name = '';
    public $compress = FALSE;
    public $debug = FALSE;
    public $Compiler = TRUE; // turn off compiler here.
    private $font_decoration_options = [];
    private $fills = [];
    /**
     * For internals CSS compilers - These are actual css properties. Do not translate.
     */
    private $text_weight = ['400', '600', '400', '400', '600', '600', '400', '600'];
    private $text_decoration = [
        'none',
        'none',
        'none',
        'underline',
        'underline',
        'none',
        'underline',
        'underline'
    ];
    private $text_style = ['normal', 'normal', 'italic', 'normal', 'normal', 'italic', 'italic', 'italic'];
    /**
     * Initialize Data
     *
     * @var array
     */
    private $data = [
        'theme_id'                      => 0,
        'theme_title'                   => '',
        'sans_serif_fonts'              => 'Helvetica Neue, Helvetica, Arial, sans-serif',
        'serif_fonts'                   => 'Georgia, Times New Roman, Times, serif',
        'monospace_fonts'               => 'Menlo, Monaco, Consolas, Courier New, monospace',
        'base_font'                     => 0,
        'base_font_size'                => 14,
        'base_font_height'              => 1.428571429,
        'base_font_color'               => '#333333',
        'base_font_size_l'              => 18,
        'base_font_size_s'              => 11,
        //h1
        'font_size_h1'                  => 36,
        'font_height_h1'                => 1.1,
        'font_color_h1'                 => '#333333',
        'font_decoration_h1'            => 0,
        //h2
        'font_size_h2'                  => 30,
        'font_height_h2'                => 1.1,
        'font_color_h2'                 => '#333333',
        'font_decoration_h2'            => 0,
        //h3
        'font_size_h3'                  => 24,
        'font_height_h3'                => 1.1,
        'font_color_h3'                 => '#333333',
        'font_decoration_h3'            => 0,
        // h4
        'font_size_h4'                  => 18,
        'font_height_h4'                => 1.1,
        'font_color_h4'                 => '#333333',
        'font_decoration_h4'            => 0,
        // h5
        'font_size_h5'                  => 14,
        'font_height_h5'                => 1.1,
        'font_color_h5'                 => '#333333',
        'font_decoration_h5'            => 0,
        // h6
        'font_size_h6'                  => 12,
        'font_height_h6'                => 1.1,
        'font_color_h6'                 => '#333333',
        'font_decoration_h6'            => 0,
        // link
        'link_color'                    => '#428bca',
        'link_hover_color'              => '#428bca',
        'link_decoration'               => 0,
        'link_hover_decoration'         => 0,
        // code
        'code_color'                    => '#c7254e',
        'code_bgcolor'                  => '#f9f2f4', //f9f2f4
        // quote
        'quote_size'                    => 14,
        'quote_height'                  => 1.1,
        'quote_color'                   => '#000000',
        'quote_decoration'              => 5,
        // components
        'container_sm'                  => 720,
        'container_md'                  => 940,
        'container_lg'                  => 1140,
        'btn_fill'                      => 0,
        'btn_border'                    => 1, //
        'btn_radius'                    => 4, //
        // colors
        'primary_color'                 => '#428bca',
        'warning_color'                 => '#f0ad4e',
        'success_color'                 => '#5cb85c',
        'danger_color'                  => '#d9534f',
        'info_color'                    => '#5bc0de',
        // btn-primary
        'btn_primary'                   => '#428bca',
        'btn_primary_color'             => '#ffffff',
        'btn_primary_hover'             => '#3276b1',
        'btn_primary_color_hover'       => '#ffffff',
        'btn_primary_active'            => '#3276b1',
        'btn_primary_color_active'      => '#ffffff',
        // btn-info
        'btn_info'                      => '#5bc0de',
        'btn_info_color'                => '#ffffff',
        'btn_info_hover'                => '#39b3d7',
        'btn_info_color_hover'          => '#ffffff',
        'btn_info_active'               => '#39b3d7',
        'btn_info_color_active'         => '#ffffff',
        // btn-success
        'btn_success'                   => '#5cb85c',
        'btn_success_color'             => '#ffffff',
        'btn_success_hover'             => '#47a447',
        'btn_success_color_hover'       => '#ffffff',
        'btn_success_active'            => '#47a447',
        'btn_success_color_active'      => '#ffffff',
        // btn-warning
        'btn_warning'                   => '#f0ad4e',
        'btn_warning_color'             => '#ffffff',
        'btn_warning_hover'             => '#ed9c28',
        'btn_warning_color_hover'       => '#ffffff',
        'btn_warning_active'            => '#ed9c28',
        'btn_warning_color_active'      => '#ffffff',
        // btn-danger
        'btn_danger'                    => '#d9534f',
        'btn_danger_color'              => '#ffffff',
        'btn_danger_hover'              => '#d2322d',
        'btn_danger_color_hover'        => '#ffffff',
        'btn_danger_active'             => '#d2322d',
        'btn_danger_color_active'       => '#ffffff',
        // global navbars
        'navbar_height'                 => 50,
        'navbar_border'                 => 1,
        'navbar_radius'                 => 4,
        // navbar 1
        'navbar_fill'                   => 0,
        'navbar_bg'                     => '#f8f8f8',
        'navbar_bg_hover'               => '#f8f8f8', // need this
        'navbar_bg_active'              => '#e7e7e7', // need this
        'navbar_link_border'            => 0,
        'navbar_link_radius'            => 0,
        'navbar_link_border_color'      => '#f8f8f8',
        'navbar_brand_color'            => '#777',
        'navbar_font_color'             => '#777',
        'navbar_brand_decoration'       => 0,
        'navbar_font_decoration'        => 0,
        'navbar_link_color'             => '#777',
        'navbar_link_decoration'        => 0,
        'navbar_link_color_hover'       => '#333',
        'navbar_link_decoration_hover'  => 0,
        'navbar_link_color_active'      => '#555',
        'navbar_link_decoration_active' => 0,
    ];
    private $less_var = [];
    private $theme_data = [];

    public function infuse_theme() {
        if (!empty($this->theme_data)) {
            add_to_head("<link href='".THEMES.$this->theme_data['theme_file']."' rel='stylesheet' media='screen' />\n");
        } else {
            add_to_head("<link href='".INCLUDES."bootstrap/bootstrap.css' rel='stylesheet' media='screen' />\n");
        }
    }

    /**
     * Theme Overview Page
     */
    public function display_theme_overview() {
        $locale = fusion_get_locale();
        $data = [
            "theme_name"        => $this->theme_name,
            "theme_screenshot"  => "",
            "theme_author"      => "",
            "theme_web"         => "",
            "theme_license"     => 'AGPL3',
            "theme_version"     => "",
            "theme_description" => "",
        ];
        $theme_dbfile = '/theme_db.php';
        $result = dbquery("SELECT * FROM ".DB_THEME." WHERE theme_name=:theme_name ORDER BY theme_datestamp DESC", [
            ':theme_name' => $this->theme_name,
        ]);

        if (file_exists(THEMES.$this->theme_name.$theme_dbfile)) { // new 9.00
            include THEMES.$this->theme_name.$theme_dbfile;
            $data = [
                'theme_name'        => !empty($theme_title) ? $theme_title : $data['theme_name'],
                'theme_screenshot'  => isset($theme_screenshot) && file_exists(THEMES.$this->theme_name."/".$theme_screenshot) ? THEMES.$this->theme_name."/".$theme_screenshot : IMAGES.'imagenotfound.jpg',
                'theme_author'      => !empty($theme_author) ? $theme_author : $data['theme_author'],
                'theme_web'         => !empty($theme_web) ? $theme_web : $data['theme_web'],
                'theme_license'     => !empty($theme_license) ? $theme_license : $data['theme_license'],
                'theme_version'     => !empty($theme_version) ? $theme_version : $data['theme_version'],
                'theme_description' => !empty($theme_description) ? $theme_description : $data['theme_description'],
                'theme_widgets'     => file_exists(THEMES.$this->theme_name.'/widget.php') ? TRUE : FALSE
            ];
        }

        echo "<div class='list-group-item p-20'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo "<span><strong>".$locale['theme_1001']."</strong> ".$data['theme_name']."</span><br/>";
        if ($data['theme_description']) {
            echo "<span><strong>".$locale['theme_1025']."</strong> ".$data['theme_description']."</span><br/>";
        }
        if (isset($data['theme_widgets'])) {
            echo "<span><strong>".$locale['theme_1027']."</strong> ".($data['theme_widgets'] ? $locale['yes'] : $locale['no'])."</span><br/>";
        }
        echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
        if ($data['theme_author']) {
            echo "<span><strong>".$locale['theme_1026']."</strong> ".$data['theme_author']."</span><br/>";
        }
        if ($data['theme_web']) {
            echo "<span><strong>".$locale['theme_1015']."</strong>: <a href='".$data['theme_web']."'>Link</a></span><br/>";
        }
        if ($data['theme_version']) {
            echo "<span><strong>".$locale['theme_1014']."</strong>: ".$data['theme_version']."</span><br/>";
        }
        if ($data['theme_license']) {
            echo "<span><strong>".$locale['theme_1013']."</strong> ".$data['theme_license']."</span><br/>";
        }
        echo "<span><strong>".$locale['theme_1028']."</strong> ".dbrows($result)."</span><br/>";
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n";


        if (dbrows($result) > 0) {
            echo "<div class='m-b-20 p-b-20 m-t-20'>\n";
            echo openform('preset-form', 'post', FUSION_REQUEST, ['notice' => 0, 'max_tokens' => 1]);
            while ($preset = dbarray($result)) {
                // @to fix: set as active, edit, delete options.
                echo "<div class='list-group-item m-t-10 display-inline-block clearfix text-center'>\n".thumbnail($data['theme_screenshot'], '150px')."
                <div class='display-block strong m-t-10 m-b-20'>".trimlink($preset['theme_title'], 30)."</div>";
                echo "<div class='btn-group m-t-10 m-b-10'>\n";
                if ($preset['theme_active'] == 1) {
                    echo form_button('active', $locale['theme_1003'], 'active', [
                        'class'      => 'btn-sm btn-default active',
                        'deactivate' => 1
                    ]);
                } else {
                    echo form_button('load_preset', $locale['theme_1004'], $preset['theme_id'], [
                        'class' => 'btn-sm btn-default',
                        'icon'  => 'fa fa-upload'
                    ]);
                }
                // dropdown
                echo "<a data-toggle='dropdown' class='btn btn-default btn-sm dropdown-toggle'><i class='fa fa-cog fa-fw'></i><span class='caret'></span></a>\n";
                echo "<ul class='dropdown-menu'>\n";
                echo "<li><a href='".clean_request("section=css&e_action=edit&preset=".$preset['theme_id'], [
                        "aid",
                        "action",
                        "theme"
                    ], TRUE)."'>".$locale['edit']."</a></li>\n";
                echo "<li><a href='".clean_request("delete_preset=".$preset['theme_id'], [
                        "aid",
                        "action",
                        "theme"
                    ], TRUE)."'>".$locale['delete']."</a></li>\n";
                echo "</ul>\n";
                echo form_hidden('theme', '', $preset['theme_name']);
                echo "</div>\n";
                echo "</div>\n";
            }
            echo closeform();
            echo "</div>\n";
        } else {
            echo "<div class='m-t-20 well text-center'>".$locale['theme_1030']."</div>\n";
        }

    }

    /**
     * Theme Widget Page
     */
    public function display_theme_widgets() {
        $locale = fusion_get_locale();
        if (Admin::theme_widget_exists($this->theme_name)) {
            echo "<div class='m-t-20 m-b-20'>\n";
            require_once THEMES.$this->theme_name."/theme_db.php";
            /**
             * Infuse Widget Action
             */
            if (isset($_POST['infuse_widget']) && fusion_get_settings('theme') == $_POST['infuse_widget'] && !dbcount("(settings_name)",
                    DB_SETTINGS_THEME,
                    "settings_theme='".$this->theme_name."'")
            ) {
                if (isset($theme_newtable) && is_array($theme_newtable)) {
                    foreach ($theme_newtable as $item) {
                        $result = dbquery("CREATE TABLE ".$item);
                        if (!$result) {
                            \defender::stop();
                        }
                    }
                }
                // insertion ok
                if (isset($theme_insertdbrow) && is_array($theme_insertdbrow)) {
                    foreach ($theme_insertdbrow as $item) {
                        $result = dbquery("INSERT INTO ".$item);
                        if (!$result) {
                            \defender::stop();
                        }
                    }
                }
                $widgetData = [
                    "settings_theme" => $this->theme_name,
                    "settings_name"  => $this->theme_name,
                    "settings_value" => 1
                ];
                dbquery_insert(DB_SETTINGS_THEME, $widgetData, "save");
                addNotice('success', sprintf($locale['theme_1019'], ucwords($this->theme_name)));
                redirect(FUSION_REQUEST);
            }
            /**
             * Defuse Widget Action
             */
            if (isset($_POST['defuse_widget']) && fusion_get_settings('theme') == $_POST['defuse_widget'] && dbcount("(settings_name)",
                    DB_SETTINGS_THEME,
                    "settings_theme='".$this->theme_name."'")
            ) {
                if (isset($theme_droptable) && is_array($theme_droptable)) {
                    foreach ($theme_droptable as $item) {
                        $result = dbquery("DROP TABLE ".$item);
                        if (!$result) {
                            \defender::stop();
                        }
                    }
                }
                // row deletion ok
                if (isset($theme_deldbrow) && is_array($theme_deldbrow)) {
                    foreach ($theme_deldbrow as $item) {
                        $result = dbquery("DELETE FROM ".$item);
                        if (!$result) {
                            \defender::stop();
                        }
                    }
                }
                addNotice('success', sprintf($locale['theme_1019b'], ucwords($this->theme_name)));
                redirect(FUSION_REQUEST);
            }
            if ((isset($theme_newtable) || isset($theme_insertdbrow)) && !dbcount("(settings_name)", DB_SETTINGS_THEME, "settings_theme='".$this->theme_name."'")) {
                echo openform("widget_infuse", "post", FUSION_REQUEST);
                echo "<div>".$locale['theme_1032']."</div>";
                echo form_button("infuse_widget", $locale['theme_1016'], $this->theme_name, ["class" => "btn-primary m-t-10"]);
                echo closeform();
            } else {
                $html = openform("widget_defuse", "post", FUSION_REQUEST, ["class" => "text-right"]);
                $html .= form_button("defuse_widget", $locale['theme_1017'], $this->theme_name, ["class" => "btn-danger"]);
                $html .= closeform();
                $html .= "<hr/>\n";
                add_to_jquery("
                $('#defuse_widget').bind('click', function(e) {
                    var val = confirm('".$locale['theme_1033']."');
                    if (val == false) {
                        e.preventDefault();
                    }
                });
                ");
                echo $html;
                echo "<!---start widget form--->\n";
                include THEMES.$this->theme_name."/widget.php";
                echo "<!---end widget form--->\n";
            }
            echo "</div>\n";
        } else {
            echo "<div class='m-t-20 well text-center'>".$locale['theme_1031']."</div>\n";
        }
    }

    /* Write CSS file - get bootstrap, fill in values, add to atom.min.css */

    /**
     * Theme Styler Page
     * Edit done, save done. Now load.
     */
    public function theme_editor() {
        $locale = fusion_get_locale();
        if (isset($_GET['e_action']) && ($_GET['e_action'] == "edit") && isset($_GET['preset']) && isnum($_GET['preset'])) {
            $result = dbquery("SELECT * FROM ".DB_THEME." WHERE theme_name='".$this->theme_name."' AND theme_id='".intval($_GET['preset'])."'");
            if (dbrows($result) > 0) {
                $this->data = dbarray($result);
                if ($this->data['theme_config']) {
                    $this->data += unserialize(stripslashes($this->data['theme_config']));
                }
            }
        }
        self::save_theme();
        $this->font_decoration_options = [
            $locale['theme_5000'],
            $locale['theme_5001'],
            $locale['theme_5002'],
            $locale['theme_5003'],
            $locale['theme_5004'],
            $locale['theme_5005'],
            $locale['theme_5006'],
            $locale['theme_5007'],
        ];
        $this->fills = [
            $locale['theme_5008'],
            $locale['theme_5009'],
            $locale['theme_5010'],
            $locale['theme_5011'],
            $locale['theme_5012'],
        ];
        $tab_title['title'][] = $locale['theme_2001'];
        $tab_title['id'][] = 'font';
        $tab_title['icon'][] = 'fa fa-text-width m-r-10';
        $tab_title['title'][] = $locale['theme_2002'];
        $tab_title['id'][] = 'grid';
        $tab_title['icon'][] = 'fa fa-magic m-r-10';
        $tab_title['title'][] = $locale['theme_2003'];
        $tab_title['id'][] = 'nav';
        $tab_title['icon'][] = 'fa fa-navicon m-r-10';
        $tab_active = tab_active($tab_title, 0);
        if ($this->debug) {
            print_p($_POST);
        }
        // how come my multiple preset missing now?
        echo openform('theme_edit', 'post', FUSION_REQUEST, ["class" => "m-t-20"]);
        echo "<div class='list-group-item m-b-20 clearfix'>\n";
        echo "<div class='pull-right m-l-10'>\n";
        echo form_button('save_theme', $locale['theme_5013'], 'save_theme', ['class' => 'btn-primary m-r-10']);
        echo form_button('close_theme', $locale['close'], 'close_theme', ['class' => 'btn-default']);
        echo "</div>\n";
        echo "<div class='overflow-hide'>\n";
        echo form_hidden('theme_id', '', $this->data['theme_id']);
        echo form_hidden("theme_datestamp", '', time());
        echo form_text('theme_title', $locale['theme_2007'], $this->data['theme_title'], [
            'inline'   => 1,
            'required' => TRUE
        ]);
        echo form_hidden('theme_name', $locale['theme_2008'], $this->theme_name, [
            'inline'     => 1,
            'deactivate' => 1
        ]);
        echo "</div>\n";
        echo "</div>\n";
        echo opentab($tab_title, $tab_active, 'atom');
        echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
        echo "<div class='m-t-20'>\n";
        $this->font_admin();
        echo "</div>\n";
        echo closetabbody();
        echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
        echo "<div class='m-t-20'>\n";
        $this->layout_admin();
        echo "</div>\n";
        echo closetabbody();
        echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
        echo "<div class='m-t-20'>\n";
        $this->nav_admin();
        echo "</div>\n";
        echo closetabbody();
        echo closetab();
        echo closeform();
    }

    public function save_theme() {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        if (isset($_POST['save_theme'])) {

            /*$modal = openmodal('dbi', sprintf($locale['theme_2005'], ucwords($this->theme_name)), [
                'class'     => 'zindex-boost modal-center',
                'button_id' => 'save_theme',
                'static'    => 1
            ]);
            $modal .= "<div class='overflow-hide'>".$locale['theme_2006']."</div>\n";
            $modal .= closemodal();
            add_to_footer($modal);*/

            $fieldArrays = $this->data;
            foreach ($fieldArrays as $fieldNames => $fieldDefaults) {
                $this->data[$fieldNames] = isset($_POST[$fieldNames]) ? form_sanitizer($_POST[$fieldNames], $fieldDefaults, $fieldNames) : "";
            }
            $old_file = !empty($this->data['theme_file']) ? $this->data['theme_file'] : '';
            if (isset($this->data['theme_config'])) {
                unset($this->data['theme_config']);
            } // will need to rebuild. unset it.
            if (isset($this->data['theme_file'])) {
                unset($this->data['theme_file']);
            } // important to unset.

            // rebuild entire structure
            $data = [
                "theme_name"      => $this->theme_name,
                "theme_title"     => form_sanitizer($_POST['theme_title'], '', 'theme_title'),
                "theme_id"        => form_sanitizer($_POST['theme_id'], '0', 'theme_id'),
                "theme_user"      => $userdata['user_id'],
                "theme_datestamp" => time()
            ];

            if (\defender::safe()) {

                $data['theme_file'] = $this->buildCss();

                if (dbcount("(theme_id)", DB_THEME, "theme_name='".$data['theme_name']."' AND theme_id='".intval($data['theme_id'])."'")) {
                    if (!empty($data['theme_file'])) {
                        $data['theme_active'] = 1;
                        $data['theme_config'] = addslashes(serialize($this->data));
                        if (!$this->debug && $data['theme_file']) {
                            if (file_exists(THEMES.$old_file) && !is_dir(THEMES.$old_file)) {
                                unlink(THEMES.$old_file);
                            }
                            dbquery_insert(DB_THEME, $data, 'update');
                            if (\defender::safe()) {
                                addNotice('success', $locale['theme_success_003']);
                                redirect(clean_request("", ["aid", "action", "theme"], TRUE));
                            }
                        } else {
                            // debug messages
                            print_p('Update Mode');
                            print_p($data);
                        }
                    }
                } else {
                    if (!$this->debug && !empty($data['theme_file'])) {
                        $rows = dbcount("(theme_id)", DB_THEME, "theme_name='".$data['theme_name']."'");
                        $data['theme_active'] = $rows < 1 ? 1 : 0;
                        $data['theme_config'] = addslashes(serialize($this->data));
                        dbquery_insert(DB_THEME, $data, 'save');
                        if (\defender::safe()) {
                            addNotice('success', $locale['theme_success_004']);
                            redirect(clean_request("", ["aid", "action", "theme"], TRUE));
                        }
                    } else {
                        // debug messages
                        $rows = dbcount("(theme_id)", DB_THEME, "theme_name='".$data['theme_name']."'");
                        $data['theme_active'] = $rows < 1 ? 1 : 0;
                        $data['theme_config'] = addslashes(serialize($this->data));
                        print_p($data);
                    }
                }
            }
        }
    }

    /* Handling Posts and Feedback. Watch out for Unsets */

    protected function buildCss() {
        $this->debug = FALSE;

        $locale = fusion_get_locale();
        $inputFile = CLASSES."PHPFusion/Atom/less/atom.less";
        $outputFolder = THEMES.$this->target_folder."/";
        $outputFile = THEMES.$this->target_folder."/fusion_".$this->target_folder."_".time().".css";
        $returnFile = str_replace(THEMES, '', $outputFile);
        $options = ['output' => $outputFile, 'compress' => $this->compress,];
        $this->set_less_variables();

        if (!empty($this->less_var) && \defender::safe() && $this->Compiler) {
            if ($this->debug) {
                print_p("current less var");
                print_p($this->less_var);
                print_p($inputFile);
                print_p($outputFile);
            }
            try {
                require_once "lessc.inc.php";
                $parser = new \Less_Parser($options);
                //$parser->SetImportDirs($directories);
                $parser->parseFile($inputFile, $outputFolder);
                $parser->ModifyVars($this->less_var);
                $css = $parser->getCss();
                if (!$this->debug) {
                    $css_file = fopen($outputFile, "w");
                    if (fwrite($css_file, $css)) {
                        fclose($css_file);
                    }
                    if ($css_file) {
                        return (string)$returnFile;
                    }
                } else {
                    print_p($outputFile);
                    print_p($css); // this is your css
                }
            } catch (\Exception $e) {
                $error_message = $e->getMessage();
                \defender::stop();
                addNotice('danger', $error_message);
            }
        } else {
            if (!$this->Compiler) {
                \defender::stop();
                addNotice('danger', $locale['theme_error_008']);
            } else {
                \defender::stop();
                addNotice('danger', $locale['theme_error_007']);
            }
        }

        return NULL;
    }

    private function set_less_variables() {
        $this->less_var = $this->data;
        // css which requires atom's custom parse rules.
        // base foot parsing.
        $this->less_var['sans_serif_fonts'] = $this->parse_fonts($this->data['sans_serif_fonts']);
        $this->less_var['serif_fonts'] = $this->parse_fonts($this->data['serif_fonts']);
        $this->less_var['monospace_fonts'] = $this->parse_fonts($this->data['monospace_fonts']);
        $this->less_var['base_font'] = $this->parse_font_set($this->data['base_font']);
        $this->less_var['base_font_size'] = $this->parse_size($this->data['base_font_size']);
        $this->less_var['base_font_size_l'] = $this->parse_size($this->data['base_font_size_l']);
        $this->less_var['base_font_size_s'] = $this->parse_size($this->data['base_font_size_s']);
        //h1
        $this->less_var['font_size_h1'] = $this->parse_size($this->data['font_size_h1']);
        $this->less_var['font_weight_h1'] = $this->parse_font_weight($this->data['font_decoration_h1']);
        $this->less_var['font_style_h1'] = $this->parse_font_style($this->data['font_decoration_h1']);
        $this->less_var['font_decoration_h1'] = $this->parse_font_decoration($this->data['font_decoration_h1']);
        //h2
        $this->less_var['font_size_h2'] = $this->parse_size($this->data['font_size_h2']);
        $this->less_var['font_weight_h2'] = $this->parse_font_weight($this->data['font_decoration_h2']);
        $this->less_var['font_style_h2'] = $this->parse_font_style($this->data['font_decoration_h2']);
        $this->less_var['font_decoration_h2'] = $this->parse_font_decoration($this->data['font_decoration_h2']);
        //h3
        $this->less_var['font_size_h3'] = $this->parse_size($this->data['font_size_h3']);
        $this->less_var['font_weight_h3'] = $this->parse_font_weight($this->data['font_decoration_h3']);
        $this->less_var['font_style_h3'] = $this->parse_font_style($this->data['font_decoration_h3']);
        $this->less_var['font_decoration_h3'] = $this->parse_font_decoration($this->data['font_decoration_h3']);
        //h4
        $this->less_var['font_size_h4'] = $this->parse_size($this->data['font_size_h4']);
        $this->less_var['font_weight_h4'] = $this->parse_font_weight($this->data['font_decoration_h4']);
        $this->less_var['font_style_h4'] = $this->parse_font_style($this->data['font_decoration_h4']);
        $this->less_var['font_decoration_h4'] = $this->parse_font_decoration($this->data['font_decoration_h4']);
        //h5
        $this->less_var['font_size_h5'] = $this->parse_size($this->data['font_size_h5']);
        $this->less_var['font_weight_h5'] = $this->parse_font_weight($this->data['font_decoration_h5']);
        $this->less_var['font_style_h5'] = $this->parse_font_style($this->data['font_decoration_h5']);
        $this->less_var['font_decoration_h5'] = $this->parse_font_decoration($this->data['font_decoration_h5']);
        //h6
        $this->less_var['font_size_h6'] = $this->parse_size($this->data['font_size_h6']);
        $this->less_var['font_weight_h6'] = $this->parse_font_weight($this->data['font_decoration_h6']);
        $this->less_var['font_style_h6'] = $this->parse_font_style($this->data['font_decoration_h6']);
        $this->less_var['font_decoration_h6'] = $this->parse_font_decoration($this->data['font_decoration_h6']);
        // link
        $this->less_var['link_weight'] = $this->parse_font_weight($this->data['link_decoration']);
        $this->less_var['link_style'] = $this->parse_font_style($this->data['link_decoration']);
        $this->less_var['link_decoration'] = $this->parse_font_decoration($this->data['link_decoration']);
        $this->less_var['link_hover_weight'] = $this->parse_font_weight($this->data['link_decoration']);
        $this->less_var['link_hover_style'] = $this->parse_font_style($this->data['link_decoration']);
        $this->less_var['link_hover_decoration'] = $this->parse_font_decoration($this->data['link_decoration']);
        // code follow back $data.
        //quote decorations
        $this->less_var['quote_weight'] = $this->parse_font_weight($this->data['quote_decoration']);
        $this->less_var['quote_style'] = $this->parse_font_style($this->data['quote_decoration']);
        $this->less_var['quote_decoration'] = $this->parse_font_decoration($this->data['quote_decoration']);
        // max screen
        $this->less_var['container_sm'] = $this->parse_size($this->data['container_sm']);
        $this->less_var['container_md'] = $this->parse_size($this->data['container_md']);
        $this->less_var['container_lg'] = $this->parse_size($this->data['container_lg']);
        $this->less_var['btn_border'] = $this->parse_size($this->data['btn_border']);
        $this->less_var['btn_radius'] = $this->parse_size($this->data['btn_radius']);
        // global navbars
        $this->less_var['navbar_height'] = $this->parse_size($this->data['navbar_height']);
        $this->less_var['navbar_border'] = $this->parse_size($this->data['navbar_border']);
        $this->less_var['navbar_radius'] = $this->parse_size($this->data['navbar_radius']);
        $this->less_var['navbar_link_border'] = $this->parse_size($this->data['navbar_link_border']);
        $this->less_var['navbar_link_radius'] = $this->parse_size($this->data['navbar_link_radius']);
        $this->less_var['navbar_brand_weight'] = $this->parse_font_weight($this->data['navbar_brand_decoration']);
        $this->less_var['navbar_brand_style'] = $this->parse_font_style($this->data['navbar_brand_decoration']);
        $this->less_var['navbar_brand_decoration'] = $this->parse_font_decoration($this->data['navbar_brand_decoration']);
        $this->less_var['navbar_font_weight'] = $this->parse_font_weight($this->data['navbar_font_decoration']);
        $this->less_var['navbar_font_style'] = $this->parse_font_style($this->data['navbar_font_decoration']);
        $this->less_var['navbar_font_decoration'] = $this->parse_font_decoration($this->data['navbar_font_decoration']);
        $this->less_var['navbar_link_weight'] = $this->parse_font_weight($this->data['navbar_link_decoration']);
        $this->less_var['navbar_link_style'] = $this->parse_font_style($this->data['navbar_link_decoration']);
        $this->less_var['navbar_link_decoration'] = $this->parse_font_decoration($this->data['navbar_link_decoration']);
        $this->less_var['navbar_link_weight_hover'] = $this->parse_font_weight($this->data['navbar_link_decoration_hover']);
        $this->less_var['navbar_link_style_hover'] = $this->parse_font_style($this->data['navbar_link_decoration_hover']);
        $this->less_var['navbar_link_decoration_hover'] = $this->parse_font_decoration($this->data['navbar_link_decoration_hover']);
        $this->less_var['navbar_link_weight_active'] = $this->parse_font_weight($this->data['navbar_link_decoration_active']);
        $this->less_var['navbar_link_style_active'] = $this->parse_font_style($this->data['navbar_link_decoration_active']);
        $this->less_var['navbar_link_decoration_active'] = $this->parse_font_decoration($this->data['navbar_link_decoration_active']);
    }

    /* Administration Menus - Part I - Font Settings */

    static function parse_fonts($font) {
        $_parsedFonts = [];
        if ($font) {
            $font = explode(',', $font);
            if (count($font)) {
                foreach ($font as $font_name) {
                    $_parsedFonts[] = (preg_match('/\s/', $font_name)) ? '"'.$font_name.'"' : $font_name;
                }

                return implode(', ', $_parsedFonts);
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    /* Administration Menus - Part II - Components & Layout Settings */

    static function parse_font_set($font) {
        $fonts_family_opts = [
            '0' => '@font-family-sans-serif',
            '1' => '@font-family-monospace',
            '2' => '@font-family-serif'
        ];

        return $fonts_family_opts[$font];
    }

    /* Administration Menus - Part III - Navigation Settings */

    static function parse_size($font) {
        return $font > 0 ? $font.'px' : 0;
    }

    /* Returns list of google_fonts */
    /*@todo: allow return of jquery Google Font real-time parsing via Google API */

    private function parse_font_weight($font) {
        if (!$font) {
            return $this->text_weight[0];
        } else {
            return $this->text_weight[$font];
        }
    }

    /* Returns list of common base fonts */

    private function parse_font_style($font) {
        if (!$font) {
            return $this->text_style[0];
        } else {
            return $this->text_style[$font];
        }
    }

    /* add quotes for font name with whitespace */

    private function parse_font_decoration($font) {
        if (!$font) {
            return $this->text_decoration[0];
        } else {
            return $this->text_decoration[$font];
        }
    }

    /* return the font sets */

    private function font_admin() {
        $locale = fusion_get_locale();
        $base_font = array_values(array_flip($this->base_font()));
        $web_font = array_values(array_flip($this->google_font()));
        $font_list = array_merge($base_font, $web_font);
        $color_options = ["placeholder" => $locale['theme_2009'], 'width' => '100%', "format" => "hex"];
        $font_options = [
            'width'       => '100%',
            'placeholder' => $locale['theme_2010'],
            'tags'        => TRUE,
            'multiple'    => TRUE,
            'max_select'  => 6,
            'inline'      => TRUE,
            'inner_width' => '100%'
        ];
        $font_type_options = ['placeholder' => $locale['theme_2011'], 'width' => '280px', 'inline' => 1];
        $font_size_options = [
            'placeholder' => '(px)',
            'width'       => '100%',
            'number'      => 1,
            'class'       => 'pull-left display-inline m-r-10'
        ];
        $fonts_family_opts = [
            '0' => $locale['theme_2012'],
            '1' => $locale['theme_2013'],
            '2' => $locale['theme_2014'],
        ];
        echo form_hidden('theme', '', $this->theme_name);
        openside('');
        $font_options['options'] = $font_list;
        $font_type_options['options'] = $fonts_family_opts;
        echo form_select("sans_serif_fonts", $locale['theme_2015'], $this->data['sans_serif_fonts'], $font_options);
        echo form_select("serif_fonts", $locale['theme_2016'], $this->data['serif_fonts'], $font_options);
        echo form_select("monospace_fonts", $locale['theme_2017'], $this->data['monospace_fonts'], $font_options);
        echo form_select("base_font", $locale['theme_2001'], $this->data['base_font'], $font_type_options);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2018'], 'base-font-size');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("base_font_size", $locale['theme_2019'], $this->data['base_font_size'], $font_size_options);
        echo form_text("base_font_height", $locale['theme_2019'], $this->data['base_font_height'], $font_size_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("base_font_size_l", $locale['theme_2021'], $this->data['base_font_size_l'], $font_size_options);
        echo form_colorpicker("base_font_color", $locale['theme_2022'], $this->data['base_font_color'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("base_font_size_s", $locale['theme_2023'], $this->data['base_font_size_s'], $font_size_options);
        echo "</div>\n</div>\n";
        closeside();
        // h1
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2024'].' 1', 'h1');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("font_size_h1", $locale['theme_2019'], $this->data['font_size_h1'], $font_size_options);
        echo form_text("font_height_h1", $locale['theme_2020'], $this->data['font_height_h1'], $font_size_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("font_color_h1", $locale['theme_2022'], $this->data['font_color_h1'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        $color_options['options'] = $this->font_decoration_options;
        echo form_select("font_decoration_h1", $locale['theme_2025'], $this->data['font_decoration_h1'], $color_options);
        echo "</div>\n</div>\n";
        closeside();
        // h2
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2024'].' 2', 'h2');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("font_size_h2", $locale['theme_2019'], $this->data['font_size_h2'], $font_size_options);
        echo form_text("font_height_h2", $locale['theme_2020'], $this->data['font_height_h2'], $font_size_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("font_color_h2", $locale['theme_2022'], $this->data['font_color_h2'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_select("font_decoration_h2", $locale['theme_2025'], $this->data['font_decoration_h2'], $color_options);
        echo "</div>\n</div>\n";
        closeside();
        // h3
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2024'].' 3', 'h3');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("font_size_h3", $locale['theme_2019'], $this->data['font_size_h3'], $font_size_options);
        echo form_text("font_height_h3", $locale['theme_2020'], $this->data['font_height_h3'], $font_size_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("font_color_h3", $locale['theme_2022'], $this->data['font_color_h3'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_select("font_decoration_h3", $locale['theme_2025'], $this->data['font_decoration_h3'], $color_options);
        echo "</div>\n</div>\n";
        closeside();
        // h4
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2024'].' 4', 'h4');
        echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
        echo form_text("font_size_h4", $locale['theme_2019'], $this->data['font_size_h4'], $font_size_options);
        echo form_text("font_height_h4", $locale['theme_2020'], $this->data['font_height_h4'], $font_size_options);
        echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("font_color_h4", $locale['theme_2022'], $this->data['font_color_h4'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
        echo form_select("font_decoration_h4", $locale['theme_2025'], $this->data['font_decoration_h4'], $color_options);
        echo "</div>\n</div>\n";
        closeside();
        // h5
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2024'].' 5', 'h5');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("font_size_h5", $locale['theme_2019'], $this->data['font_size_h5'], $font_size_options);
        echo form_text("font_height_h5", $locale['theme_2020'], $this->data['font_height_h5'], $font_size_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("font_color_h5", $locale['theme_2022'], $this->data['font_color_h5'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_select("font_decoration_h5", $locale['theme_2025'], $this->data['font_decoration_h5'], $color_options);
        echo "</div>\n</div>\n";
        closeside();
        // h6
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2024'].' 6', 'h6');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("font_size_h6", $locale['theme_2019'], $this->data['font_size_h6'], $font_size_options);
        echo form_text("font_height_h6", $locale['theme_2020'], $this->data['font_height_h6'], $font_size_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("font_color_h6", $locale['theme_2022'], $this->data['font_color_h6'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_select("font_decoration_h6", $locale['theme_2025'], $this->data['font_decoration_h6'], $color_options);
        echo "</div>\n</div>\n";
        closeside();
        // link
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2026'], 'link');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("link_color", $locale['theme_2027'], $this->data['link_color'], $color_options);
        echo form_colorpicker("link_hover_color", $locale['theme_2028'], $this->data['link_hover_color'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_select("link_decoration", $locale['theme_2025'], $this->data['link_decoration'], $color_options);
        echo form_select("link_hover_decoration", $locale['theme_2029'], $this->data['link_hover_decoration'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo "</div>\n</div>\n";
        closeside();
        // code
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2030'], 'link');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("code_color", $locale['theme_2027'], $this->data['code_color'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("code_bgcolor", $locale['theme_2031'], $this->data['code_bgcolor'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo "</div>\n</div>\n";
        closeside();
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_2032'], 'blqte');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("quote_size", $locale['theme_2019'], $this->data['quote_size'], $font_size_options);
        echo form_text("quote_height", $locale['theme_2020'], $this->data['quote_height'], $font_size_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("quote_color", $locale['theme_2022'], $this->data['quote_color'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_select("quote_decoration", $locale['theme_2029'], $this->data['quote_decoration'], $color_options);
        echo "</div>\n</div>\n";
        closeside();
    }

    /* parse the font size metrics - can be edited to use 'px', 'em', 'rem' */

    static function base_font() {
        // OS Font Defaults
        return [
            "Arial"               => "Arial",
            "Avant Garde"         => "Avant+Garde",
            "Cambria"             => "Cambria",
            "Copse"               => "Copse",
            "Garamond"            => "Garamond",
            "Georgia"             => "Georgia",
            "Heofler_Text"        => "Hoefler+Text",
            "Helvetica"           => "Helvetica",
            "Helvetica Neue"      => "Helvetica+Neue",
            "Tahoma"              => "Tahoma",
            "Times New Roman"     => "Times+New+Roman",
            "Times"               => "Times",
            "Lucida Grande"       => "Lucida+Grande",
            "Lucida Sans Unicode" => "Lucida+Sans+Unicode",
            "Verdana"             => "Verdana",
            "sans-serif"          => "sans-serif",
            "serif"               => "serif"
        ];
        //print_p($os_faces);
    }

    /* parse font-weight */

    static function google_font() {
        $google_font = [
            "ABeeZee"                  => "ABeeZee",
            "Abel"                     => "Abel",
            "Abril Fatface"            => "Abril+Fatface",
            "Aclonica"                 => "Aclonica",
            "Acme"                     => "Acme",
            "Actor"                    => "Actor",
            "Adamina"                  => "Adamina",
            "Advent Pro"               => "Advent+Pro",
            "Aguafina Script"          => "Aguafina+Script",
            "Akronim"                  => "Akronim",
            "Aladin"                   => "Aladin",
            "Aldrich"                  => "Aldrich",
            "Alegreya"                 => "Alegreya",
            "Alegreya SC"              => "Alegreya+SC",
            "Alex Brush"               => "Alex+Brush",
            "Alfa Slab One"            => "Alfa+Slab+One",
            "Alice"                    => "Alice",
            "Alike"                    => "Alike",
            "Alike Angular"            => "Alike+Angular",
            "Allan"                    => "Allan",
            "Allerta"                  => "Allerta",
            "Allerta Stencil"          => "Allerta+Stencil",
            "Allura"                   => "Allura",
            "Almendra"                 => "Almendra",
            "Almendra Display"         => "Almendra+Display",
            "Almendra SC"              => "Almendra+SC",
            "Amarante"                 => "Amarante",
            "Amaranth"                 => "Amaranth",
            "Amatic SC"                => "Amatic+SC",
            "Amethysta"                => "Amethysta",
            "Anaheim"                  => "Anaheim",
            "Andada"                   => "Andada",
            "Andika"                   => "Andika",
            "Angkor"                   => "Angkor",
            "Annie Use Your Telescope" => "Annie+Use+Your+Telescope",
            "Anonymous Pro"            => "Anonymous+Pro",
            "Antic"                    => "Antic",
            "Antic Didone"             => "Antic+Didone",
            "Antic Slab"               => "Antic+Slab",
            "Anton"                    => "Anton",
            "Arapey"                   => "Arapey",
            "Arbutus"                  => "Arbutus",
            "Arbutus Slab"             => "Arbutus+Slab",
            "Architects Daughter"      => "Architects+Daughter",
            "Archivo Black"            => "Archivo+Black",
            "Archivo Narrow"           => "Archivo+Narrow",
            "Arimo"                    => "Arimo",
            "Arizonia"                 => "Arizonia",
            "Armata"                   => "Armata",
            "Artifika"                 => "Artifika",
            "Arvo"                     => "Arvo",
            "Asap"                     => "Asap",
            "Asset"                    => "Asset",
            "Astloch"                  => "Astloch",
            "Asul"                     => "Asul",
            "Atomic Age"               => "Atomic+Age",
            "Aubrey"                   => "Aubrey",
            "Audiowide"                => "Audiowide",
            "Autour One"               => "Autour+One",
            "Average"                  => "Average",
            "Average Sans"             => "Average+Sans",
            "Averia Gruesa Libre"      => "Averia+Gruesa+Libre",
            "Averia Libre"             => "Averia+Libre",
            "Averia Sans Libre"        => "Averia+Sans+Libre",
            "Averia Serif Libre"       => "Averia+Serif+Libre",
            "Bad Script"               => "Bad+Script",
            "Balthazar"                => "Balthazar",
            "Bangers"                  => "Bangers",
            "Basic"                    => "Basic",
            "Battambang"               => "Battambang",
            "Baumans"                  => "Baumans",
            "Bayon"                    => "Bayon",
            "Belgrano"                 => "Belgrano",
            "Belleza"                  => "Belleza",
            "BenchNine"                => "BenchNine",
            "Bentham"                  => "Bentham",
            "Berkshire Swash"          => "Berkshire+Swash",
            "Bevan"                    => "Bevan",
            "Bigelow Rules"            => "Bigelow+Rules",
            "Bigshot One"              => "Bigshot+One",
            "Bilbo"                    => "Bilbo",
            "Bilbo Swash Caps"         => "Bilbo+Swash+Caps",
            "Bitter"                   => "Bitter",
            "Black Ops One"            => "Black+Ops+One",
            "Bokor"                    => "Bokor",
            "Bonbon"                   => "Bonbon",
            "Boogaloo"                 => "Boogaloo",
            "Bowlby One"               => "Bowlby+One",
            "Bowlby One SC"            => "Bowlby+One+SC",
            "Brawler"                  => "Brawler",
            "Bree Serif"               => "Bree+Serif",
            "Bubblegum Sans"           => "Bubblegum+Sans",
            "Bubbler One"              => "Bubbler+One",
            "Buda"                     => "Buda",
            "Buenard"                  => "Buenard",
            "Butcherman"               => "Butcherman",
            "Butterfly Kids"           => "Butterfly+Kids",
            "Cabin"                    => "Cabin",
            "Cabin Condensed"          => "Cabin+Condensed",
            "Cabin Sketch"             => "Cabin+Sketch",
            "Caesar Dressing"          => "Caesar+Dressing",
            "Cagliostro"               => "Cagliostro",
            "Calligraffitti"           => "Calligraffitti",
            "Cambo"                    => "Cambo",
            "Candal"                   => "Candal",
            "Cantarell"                => "Cantarell",
            "Cantata One"              => "Cantata+One",
            "Cantora One"              => "Cantora+One",
            "Capriola"                 => "Capriola",
            "Cardo"                    => "Cardo",
            "Carme"                    => "Carme",
            "Carrois Gothic"           => "Carrois+Gothic",
            "Carrois Gothic SC"        => "Carrois+Gothic+SC",
            "Carter One"               => "Carter+One",
            "Caudex"                   => "Caudex",
            "Cedarville Cursive"       => "Cedarville+Cursive",
            "Ceviche One"              => "Ceviche+One",
            "Changa One"               => "Changa+One",
            "Chango"                   => "Chango",
            "Chau Philomene One"       => "Chau+Philomene+One",
            "Chela One"                => "Chela+One",
            "Chelsea Market"           => "Chelsea+Market",
            "Chenla"                   => "Chenla",
            "Cherry Cream Soda"        => "Cherry+Cream+Soda",
            "Cherry Swash"             => "Cherry+Swash",
            "Chewy"                    => "Chewy",
            "Chicle"                   => "Chicle",
            "Chivo"                    => "Chivo",
            "Cinzel"                   => "Cinzel",
            "Cinzel Decorative"        => "Cinzel+Decorative",
            "Clicker Script"           => "Clicker+Script",
            "Coda"                     => "Coda",
            "Coda Caption"             => "Coda+Caption",
            "Codystar"                 => "Codystar",
            "Combo"                    => "Combo",
            "Comfortaa"                => "Comfortaa",
            "Coming Soon"              => "Coming+Soon",
            "Concert One"              => "Concert+One",
            "Condiment"                => "Condiment",
            "Content"                  => "Content",
            "Contrail One"             => "Contrail+One",
            "Convergence"              => "Convergence",
            "Cookie"                   => "Cookie",
            "Copse"                    => "Copse",
            "Corben"                   => "Corben",
            "Courgette"                => "Courgette",
            "Cousine"                  => "Cousine",
            "Coustard"                 => "Coustard",
            "Covered By Your Grace"    => "Covered+By+Your+Grace",
            "Crafty Girls"             => "Crafty+Girls",
            "Creepster"                => "Creepster",
            "Crete Round"              => "Crete+Round",
            "Crimson Text"             => "Crimson+Text",
            "Croissant One"            => "Croissant+One",
            "Crushed"                  => "Crushed",
            "Cuprum"                   => "Cuprum",
            "Cutive"                   => "Cutive",
            "Cutive Mono"              => "Cutive+Mono",
            "Damion"                   => "Damion",
            "Dancing Script"           => "Dancing+Script",
            "Dangrek"                  => "Dangrek",
            "Dawning of a New Day"     => "Dawning+of+a+New+Day",
            "Days One"                 => "Days+One",
            "Delius"                   => "Delius",
            "Delius Swash Caps"        => "Delius+Swash+Caps",
            "Delius Unicase"           => "Delius+Unicase",
            "Della Respira"            => "Della+Respira",
            "Denk One"                 => "Denk+One",
            "Devonshire"               => "Devonshire",
            "Didact Gothic"            => "Didact+Gothic",
            "Diplomata"                => "Diplomata",
            "Diplomata SC"             => "Diplomata+SC",
            "Domine"                   => "Domine",
            "Donegal One"              => "Donegal+One",
            "Doppio One"               => "Doppio+One",
            "Dorsa"                    => "Dorsa",
            "Dosis"                    => "Dosis",
            "Dr Sugiyama"              => "Dr+Sugiyama",
            "Droid Sans"               => "Droid+Sans",
            "Droid Sans Mono"          => "Droid+Sans+Mono",
            "Droid Serif"              => "Droid+Serif",
            "Duru Sans"                => "Duru+Sans",
            "Dynalight"                => "Dynalight",
            "EB Garamond"              => "EB+Garamond",
            "Eagle Lake"               => "Eagle+Lake",
            "Eater"                    => "Eater",
            "Economica"                => "Economica",
            "Electrolize"              => "Electrolize",
            "Elsie"                    => "Elsie",
            "Elsie Swash Caps"         => "Elsie+Swash+Caps",
            "Emblema One"              => "Emblema+One",
            "Emilys Candy"             => "Emilys+Candy",
            "Engagement"               => "Engagement",
            "Englebert"                => "Englebert",
            "Enriqueta"                => "Enriqueta",
            "Erica One"                => "Erica+One",
            "Esteban"                  => "Esteban",
            "Euphoria Script"          => "Euphoria+Script",
            "Ewert"                    => "Ewert",
            "Exo"                      => "Exo",
            "Expletus Sans"            => "Expletus+Sans",
            "Fanwood Text"             => "Fanwood+Text",
            "Fascinate"                => "Fascinate",
            "Fascinate Inline"         => "Fascinate+Inline",
            "Faster One"               => "Faster+One",
            "Fasthand"                 => "Fasthand",
            "Federant"                 => "Federant",
            "Federo"                   => "Federo",
            "Felipa"                   => "Felipa",
            "Fenix"                    => "Fenix",
            "Finger Paint"             => "Finger+Paint",
            "Fjalla One"               => "Fjalla+One",
            "Fjord One"                => "Fjord+One",
            "Flamenco"                 => "Flamenco",
            "Flavors"                  => "Flavors",
            "Fondamento"               => "Fondamento",
            "Fontdiner Swanky"         => "Fontdiner+Swanky",
            "Forum"                    => "Forum",
            "Francois One"             => "Francois+One",
            "Freckle Face"             => "Freckle+Face",
            "Fredericka the Great"     => "Fredericka+the+Great",
            "Fredoka One"              => "Fredoka+One",
            "Freehand"                 => "Freehand",
            "Fresca"                   => "Fresca",
            "Frijole"                  => "Frijole",
            "Fruktur"                  => "Fruktur",
            "Fugaz One"                => "Fugaz+One",
            "GFS Didot"                => "GFS+Didot",
            "GFS Neohellenic"          => "GFS+Neohellenic",
            "Gabriela"                 => "Gabriela",
            "Gafata"                   => "Gafata",
            "Galdeano"                 => "Galdeano",
            "Galindo"                  => "Galindo",
            "Gentium Basic"            => "Gentium+Basic",
            "Gentium Book Basic"       => "Gentium+Book+Basic",
            "Geo"                      => "Geo",
            "Geostar"                  => "Geostar",
            "Geostar Fill"             => "Geostar+Fill",
            "Germania One"             => "Germania+One",
            "Gilda Display"            => "Gilda+Display",
            "Give You Glory"           => "Give+You+Glory",
            "Glass Antiqua"            => "Glass+Antiqua",
            "Glegoo"                   => "Glegoo",
            "Gloria Hallelujah"        => "Gloria+Hallelujah",
            "Goblin One"               => "Goblin+One",
            "Gochi Hand"               => "Gochi+Hand",
            "Gorditas"                 => "Gorditas",
            "Goudy Bookletter 1911"    => "Goudy+Bookletter+1911",
            "Graduate"                 => "Graduate",
            "Grand Hotel"              => "Grand+Hotel",
            "Gravitas One"             => "Gravitas+One",
            "Great Vibes"              => "Great+Vibes",
            "Griffy"                   => "Griffy",
            "Gruppo"                   => "Gruppo",
            "Gudea"                    => "Gudea",
            "Habibi"                   => "Habibi",
            "Hammersmith One"          => "Hammersmith+One",
            "Hanalei"                  => "Hanalei",
            "Hanalei Fill"             => "Hanalei+Fill",
            "Handlee"                  => "Handlee",
            "Hanuman"                  => "Hanuman",
            "Happy Monkey"             => "Happy+Monkey",
            "Headland One"             => "Headland+One",
            "Henny Penny"              => "Henny+Penny",
            "Herr Von Muellerhoff"     => "Herr+Von+Muellerhoff",
            "Holtwood One SC"          => "Holtwood+One+SC",
            "Homemade Apple"           => "Homemade+Apple",
            "Homenaje"                 => "Homenaje",
            "IM Fell DW Pica"          => "IM+Fell+DW+Pica",
            "IM Fell DW Pica SC"       => "IM+Fell+DW+Pica+SC",
            "IM Fell Double Pica"      => "IM+Fell+Double+Pica",
            "IM Fell Double Pica SC"   => "IM+Fell+Double+Pica+SC",
            "IM Fell English"          => "IM+Fell+English",
            "IM Fell English SC"       => "IM+Fell+English+SC",
            "IM Fell French Canon"     => "IM+Fell+French+Canon",
            "IM Fell French Canon SC"  => "IM+Fell+French+Canon+SC",
            "IM Fell Great Primer"     => "IM+Fell+Great+Primer",
            "IM Fell Great Primer SC"  => "IM+Fell+Great+Primer+SC",
            "Iceberg"                  => "Iceberg",
            "Iceland"                  => "Iceland",
            "Imprima"                  => "Imprima",
            "Inconsolata"              => "Inconsolata",
            "Inder"                    => "Inder",
            "Indie Flower"             => "Indie+Flower",
            "Inika"                    => "Inika",
            "Irish Grover"             => "Irish+Grover",
            "Istok Web"                => "Istok+Web",
            "Italiana"                 => "Italiana",
            "Italianno"                => "Italianno",
            "Jacques Francois"         => "Jacques+Francois",
            "Jacques Francois Shadow"  => "Jacques+Francois+Shadow",
            "Jim Nightshade"           => "Jim+Nightshade",
            "Jockey One"               => "Jockey+One",
            "Jolly Lodger"             => "Jolly+Lodger",
            "Josefin Sans"             => "Josefin+Sans",
            "Josefin Slab"             => "Josefin+Slab",
            "Joti One"                 => "Joti+One",
            "Judson"                   => "Judson",
            "Julee"                    => "Julee",
            "Julius Sans One"          => "Julius+Sans+One",
            "Junge"                    => "Junge",
            "Jura"                     => "Jura",
            "Just Another Hand"        => "Just+Another+Hand",
            "Just Me Again Down Here"  => "Just+Me+Again+Down+Here",
            "Kameron"                  => "Kameron",
            "Karla"                    => "Karla",
            "Kaushan Script"           => "Kaushan+Script",
            "Kavoon"                   => "Kavoon",
            "Keania One"               => "Keania+One",
            "Kelly Slab"               => "Kelly+Slab",
            "Kenia"                    => "Kenia",
            "Khmer"                    => "Khmer",
            "Kite One"                 => "Kite+One",
            "Knewave"                  => "Knewave",
            "Kotta One"                => "Kotta+One",
            "Koulen"                   => "Koulen",
            "Kranky"                   => "Kranky",
            "Kreon"                    => "Kreon",
            "Kristi"                   => "Kristi",
            "Krona One"                => "Krona+One",
            "La Belle Aurore"          => "La+Belle+Aurore",
            "Lancelot"                 => "Lancelot",
            "Lato"                     => "Lato",
            "League Script"            => "League+Script",
            "Leckerli One"             => "Leckerli+One",
            "Ledger"                   => "Ledger",
            "Lekton"                   => "Lekton",
            "Lemon"                    => "Lemon",
            "Libre Baskerville"        => "Libre+Baskerville",
            "Life Savers"              => "Life+Savers",
            "Lilita One"               => "Lilita+One",
            "Limelight"                => "Limelight",
            "Linden Hill"              => "Linden+Hill",
            "Lobster"                  => "Lobster",
            "Lobster Two"              => "Lobster+Two",
            "Londrina Outline"         => "Londrina+Outline",
            "Londrina Shadow"          => "Londrina+Shadow",
            "Londrina Sketch"          => "Londrina+Sketch",
            "Londrina Solid"           => "Londrina+Solid",
            "Lora"                     => "Lora",
            "Love Ya Like A Sister"    => "Love+Ya+Like+A+Sister",
            "Loved by the King"        => "Loved+by+the+King",
            "Lovers Quarrel"           => "Lovers+Quarrel",
            "Luckiest Guy"             => "Luckiest+Guy",
            "Lusitana"                 => "Lusitana",
            "Lustria"                  => "Lustria",
            "Macondo"                  => "Macondo",
            "Macondo Swash Caps"       => "Macondo+Swash+Caps",
            "Magra"                    => "Magra",
            "Maiden Orange"            => "Maiden+Orange",
            "Mako"                     => "Mako",
            "Marcellus"                => "Marcellus",
            "Marcellus SC"             => "Marcellus+SC",
            "Marck Script"             => "Marck+Script",
            "Margarine"                => "Margarine",
            "Marko One"                => "Marko+One",
            "Marmelad"                 => "Marmelad",
            "Marvel"                   => "Marvel",
            "Mate"                     => "Mate",
            "Mate SC"                  => "Mate+SC",
            "Maven Pro"                => "Maven+Pro",
            "McLaren"                  => "McLaren",
            "Meddon"                   => "Meddon",
            "MedievalSharp"            => "MedievalSharp",
            "Medula One"               => "Medula+One",
            "Megrim"                   => "Megrim",
            "Meie Script"              => "Meie+Script",
            "Merienda"                 => "Merienda",
            "Merienda One"             => "Merienda+One",
            "Merriweather"             => "Merriweather",
            "Merriweather Sans"        => "Merriweather+Sans",
            "Metal"                    => "Metal",
            "Metal Mania"              => "Metal+Mania",
            "Metamorphous"             => "Metamorphous",
            "Metrophobic"              => "Metrophobic",
            "Michroma"                 => "Michroma",
            "Milonga"                  => "Milonga",
            "Miltonian"                => "Miltonian",
            "Miltonian Tattoo"         => "Miltonian+Tattoo",
            "Miniver"                  => "Miniver",
            "Miss Fajardose"           => "Miss+Fajardose",
            "Modern Antiqua"           => "Modern+Antiqua",
            "Molengo"                  => "Molengo",
            "Molle"                    => "Molle",
            "Monda"                    => "Monda",
            "Monofett"                 => "Monofett",
            "Monoton"                  => "Monoton",
            "Monsieur La Doulaise"     => "Monsieur+La+Doulaise",
            "Montaga"                  => "Montaga",
            "Montez"                   => "Montez",
            "Montserrat"               => "Montserrat",
            "Montserrat Alternates"    => "Montserrat+Alternates",
            "Montserrat Subrayada"     => "Montserrat+Subrayada",
            "Moul"                     => "Moul",
            "Moulpali"                 => "Moulpali",
            "Mountains of Christmas"   => "Mountains+of+Christmas",
            "Mouse Memoirs"            => "Mouse+Memoirs",
            "Mr Bedfort"               => "Mr+Bedfort",
            "Mr Dafoe"                 => "Mr+Dafoe",
            "Mr De Haviland"           => "Mr+De+Haviland",
            "Mrs Saint Delafield"      => "Mrs+Saint+Delafield",
            "Mrs Sheppards"            => "Mrs+Sheppards",
            "Muli"                     => "Muli",
            "Mystery Quest"            => "Mystery+Quest",
            "Neucha"                   => "Neucha",
            "Neuton"                   => "Neuton",
            "New Rocker"               => "New+Rocker",
            "News Cycle"               => "News+Cycle",
            "Niconne"                  => "Niconne",
            "Nixie One"                => "Nixie+One",
            "Nobile"                   => "Nobile",
            "Nokora"                   => "Nokora",
            "Norican"                  => "Norican",
            "Nosifer"                  => "Nosifer",
            "Nothing You Could Do"     => "Nothing+You+Could+Do",
            "Noticia Text"             => "Noticia+Text",
            "Nova Cut"                 => "Nova+Cut",
            "Nova Flat"                => "Nova+Flat",
            "Nova Mono"                => "Nova+Mono",
            "Nova Oval"                => "Nova+Oval",
            "Nova Round"               => "Nova+Round",
            "Nova Script"              => "Nova+Script",
            "Nova Slim"                => "Nova+Slim",
            "Nova Square"              => "Nova+Square",
            "Numans"                   => "Numans",
            "Nunito"                   => "Nunito",
            "Odor Mean Chey"           => "Odor+Mean+Chey",
            "Offside"                  => "Offside",
            "Old Standard TT"          => "Old+Standard+TT",
            "Oldenburg"                => "Oldenburg",
            "Oleo Script"              => "Oleo+Script",
            "Oleo Script Swash Caps"   => "Oleo+Script+Swash+Caps",
            "Open Sans"                => "Open+Sans",
            "Open Sans Condensed"      => "Open+Sans+Condensed",
            "Oranienbaum"              => "Oranienbaum",
            "Orbitron"                 => "Orbitron",
            "Oregano"                  => "Oregano",
            "Orienta"                  => "Orienta",
            "Original Surfer"          => "Original+Surfer",
            "Oswald"                   => "Oswald",
            "Over the Rainbow"         => "Over+the+Rainbow",
            "Overlock"                 => "Overlock",
            "Overlock SC"              => "Overlock+SC",
            "Ovo"                      => "Ovo",
            "Oxygen"                   => "Oxygen",
            "Oxygen Mono"              => "Oxygen+Mono",
            "PT Mono"                  => "PT+Mono",
            "PT Sans"                  => "PT+Sans",
            "PT Sans Caption"          => "PT+Sans+Caption",
            "PT Sans Narrow"           => "PT+Sans+Narrow",
            "PT Serif"                 => "PT+Serif",
            "PT Serif Caption"         => "PT+Serif+Caption",
            "Pacifico"                 => "Pacifico",
            "Paprika"                  => "Paprika",
            "Parisienne"               => "Parisienne",
            "Passero One"              => "Passero+One",
            "Passion One"              => "Passion+One",
            "Patrick Hand"             => "Patrick+Hand",
            "Patrick Hand SC"          => "Patrick+Hand+SC",
            "Patua One"                => "Patua+One",
            "Paytone One"              => "Paytone+One",
            "Peralta"                  => "Peralta",
            "Permanent Marker"         => "Permanent+Marker",
            "Petit Formal Script"      => "Petit+Formal+Script",
            "Petrona"                  => "Petrona",
            "Philosopher"              => "Philosopher",
            "Piedra"                   => "Piedra",
            "Pinyon Script"            => "Pinyon+Script",
            "Pirata One"               => "Pirata+One",
            "Plaster"                  => "Plaster",
            "Play"                     => "Play",
            "Playball"                 => "Playball",
            "Playfair Display"         => "Playfair+Display",
            "Playfair Display SC"      => "Playfair+Display+SC",
            "Podkova"                  => "Podkova",
            "Poiret One"               => "Poiret+One",
            "Poller One"               => "Poller+One",
            "Poly"                     => "Poly",
            "Pompiere"                 => "Pompiere",
            "Pontano Sans"             => "Pontano+Sans",
            "Port Lligat Sans"         => "Port+Lligat+Sans",
            "Port Lligat Slab"         => "Port+Lligat+Slab",
            "Prata"                    => "Prata",
            "Preahvihear"              => "Preahvihear",
            "Press Start 2P"           => "Press+Start+2P",
            "Princess Sofia"           => "Princess+Sofia",
            "Prociono"                 => "Prociono",
            "Prosto One"               => "Prosto+One",
            "Puritan"                  => "Puritan",
            "Purple Purse"             => "Purple+Purse",
            "Quando"                   => "Quando",
            "Quantico"                 => "Quantico",
            "Quattrocento"             => "Quattrocento",
            "Quattrocento Sans"        => "Quattrocento+Sans",
            "Questrial"                => "Questrial",
            "Quicksand"                => "Quicksand",
            "Quintessential"           => "Quintessential",
            "Qwigley"                  => "Qwigley",
            "Racing Sans One"          => "Racing+Sans+One",
            "Radley"                   => "Radley",
            "Raleway"                  => "Raleway",
            "Raleway Dots"             => "Raleway+Dots",
            "Rambla"                   => "Rambla",
            "Rammetto One"             => "Rammetto+One",
            "Ranchers"                 => "Ranchers",
            "Rancho"                   => "Rancho",
            "Rationale"                => "Rationale",
            "Redressed"                => "Redressed",
            "Reenie Beanie"            => "Reenie+Beanie",
            "Revalia"                  => "Revalia",
            "Ribeye"                   => "Ribeye",
            "Ribeye Marrow"            => "Ribeye+Marrow",
            "Righteous"                => "Righteous",
            "Risque"                   => "Risque",
            "Roboto"                   => "Roboto",
            "Roboto Condensed"         => "Roboto+Condensed",
            "Rochester"                => "Rochester",
            "Rock Salt"                => "Rock+Salt",
            "Rokkitt"                  => "Rokkitt",
            "Romanesco"                => "Romanesco",
            "Ropa Sans"                => "Ropa+Sans",
            "Rosario"                  => "Rosario",
            "Rosarivo"                 => "Rosarivo",
            "Rouge Script"             => "Rouge+Script",
            "Ruda"                     => "Ruda",
            "Rufina"                   => "Rufina",
            "Ruge Boogie"              => "Ruge+Boogie",
            "Ruluko"                   => "Ruluko",
            "Rum Raisin"               => "Rum+Raisin",
            "Ruslan Display"           => "Ruslan+Display",
            "Russo One"                => "Russo+One",
            "Ruthie"                   => "Ruthie",
            "Rye"                      => "Rye",
            "Sacramento"               => "Sacramento",
            "Sail"                     => "Sail",
            "Salsa"                    => "Salsa",
            "Sanchez"                  => "Sanchez",
            "Sancreek"                 => "Sancreek",
            "Sansita One"              => "Sansita+One",
            "Sarina"                   => "Sarina",
            "Satisfy"                  => "Satisfy",
            "Scada"                    => "Scada",
            "Schoolbell"               => "Schoolbell",
            "Seaweed Script"           => "Seaweed+Script",
            "Sevillana"                => "Sevillana",
            "Seymour One"              => "Seymour+One",
            "Shadows Into Light"       => "Shadows+Into+Light",
            "Shadows Into Light Two"   => "Shadows+Into+Light+Two",
            "Shanti"                   => "Shanti",
            "Share"                    => "Share",
            "Share Tech"               => "Share+Tech",
            "Share Tech Mono"          => "Share+Tech+Mono",
            "Shojumaru"                => "Shojumaru",
            "Short Stack"              => "Short+Stack",
            "Siemreap"                 => "Siemreap",
            "Sigmar One"               => "Sigmar+One",
            "Signika"                  => "Signika",
            "Signika Negative"         => "Signika+Negative",
            "Simonetta"                => "Simonetta",
            "Sintony"                  => "Sintony",
            "Sirin Stencil"            => "Sirin+Stencil",
            "Six Caps"                 => "Six+Caps",
            "Skranji"                  => "Skranji",
            "Slackey"                  => "Slackey",
            "Smokum"                   => "Smokum",
            "Smythe"                   => "Smythe",
            "Sniglet"                  => "Sniglet",
            "Snippet"                  => "Snippet",
            "Snowburst One"            => "Snowburst+One",
            "Sofadi One"               => "Sofadi+One",
            "Sofia"                    => "Sofia",
            "Sonsie One"               => "Sonsie+One",
            "Sorts Mill Goudy"         => "Sorts+Mill+Goudy",
            "Source Code Pro"          => "Source+Code+Pro",
            "Source Sans Pro"          => "Source+Sans+Pro",
            "Special Elite"            => "Special+Elite",
            "Spicy Rice"               => "Spicy+Rice",
            "Spinnaker"                => "Spinnaker",
            "Spirax"                   => "Spirax",
            "Squada One"               => "Squada+One",
            "Stalemate"                => "Stalemate",
            "Stalinist One"            => "Stalinist+One",
            "Stardos Stencil"          => "Stardos+Stencil",
            "Stint Ultra Condensed"    => "Stint+Ultra+Condensed",
            "Stint Ultra Expanded"     => "Stint+Ultra+Expanded",
            "Stoke"                    => "Stoke",
            "Strait"                   => "Strait",
            "Sue Ellen Francisco"      => "Sue+Ellen+Francisco",
            "Sunshiney"                => "Sunshiney",
            "Supermercado One"         => "Supermercado+One",
            "Suwannaphum"              => "Suwannaphum",
            "Swanky and Moo Moo"       => "Swanky+and+Moo+Moo",
            "Syncopate"                => "Syncopate",
            "Tangerine"                => "Tangerine",
            "Taprom"                   => "Taprom",
            "Tauri"                    => "Tauri",
            "Telex"                    => "Telex",
            "Tenor Sans"               => "Tenor+Sans",
            "Text Me One"              => "Text+Me+One",
            "The Girl Next Door"       => "The+Girl+Next+Door",
            "Tienne"                   => "Tienne",
            "Tinos"                    => "Tinos",
            "Titan One"                => "Titan+One",
            "Titillium Web"            => "Titillium+Web",
            "Trade Winds"              => "Trade+Winds",
            "Trocchi"                  => "Trocchi",
            "Trochut"                  => "Trochut",
            "Trykker"                  => "Trykker",
            "Tulpen One"               => "Tulpen+One",
            "Ubuntu"                   => "Ubuntu",
            "Ubuntu Condensed"         => "Ubuntu+Condensed",
            "Ubuntu Mono"              => "Ubuntu+Mono",
            "Ultra"                    => "Ultra",
            "Uncial Antiqua"           => "Uncial+Antiqua",
            "Underdog"                 => "Underdog",
            "Unica One"                => "Unica+One",
            "UnifrakturCook"           => "UnifrakturCook",
            "UnifrakturMaguntia"       => "UnifrakturMaguntia",
            "Unkempt"                  => "Unkempt",
            "Unlock"                   => "Unlock",
            "Unna"                     => "Unna",
            "VT323"                    => "VT323",
            "Vampiro One"              => "Vampiro+One",
            "Varela"                   => "Varela",
            "Varela Round"             => "Varela+Round",
            "Vast Shadow"              => "Vast+Shadow",
            "Vibur"                    => "Vibur",
            "Vidaloka"                 => "Vidaloka",
            "Viga"                     => "Viga",
            "Voces"                    => "Voces",
            "Volkhov"                  => "Volkhov",
            "Vollkorn"                 => "Vollkorn",
            "Voltaire"                 => "Voltaire",
            "Waiting for the Sunrise"  => "Waiting+for+the+Sunrise",
            "Wallpoet"                 => "Wallpoet",
            "Walter Turncoat"          => "Walter+Turncoat",
            "Warnes"                   => "Warnes",
            "Wellfleet"                => "Wellfleet",
            "Wendy One"                => "Wendy+One",
            "Wire One"                 => "Wire+One",
            "Yanone Kaffeesatz"        => "Yanone+Kaffeesatz",
            "Yellowtail"               => "Yellowtail",
            "Yeseva One"               => "Yeseva+One",
            "Yesteryear"               => "Yesteryear",
            "Zeyada"                   => "Zeyada"
        ];

        //api at google : <link href=http://fonts.googleapis.com/css?family=Signika Negative rel=stylesheet type=text/css>
        return $google_font;
    }

    /* parse text-decoration */

    private function layout_admin() {
        $locale = fusion_get_locale();
        $width_options = ["width" => "100%", 'placeholder' => 'px'];
        $color_options = ["placeholder" => $locale['theme_2009'], "width" => "100%", "format" => "hex"];
        // max widths
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3001'], 'max_width');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("container_sm", $locale['theme_3002'], $this->data['container_sm'], $width_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("container_md", $locale['theme_3003'], $this->data['container_md'], $width_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("container_lg", $locale['theme_3004'], $this->data['container_lg'], $width_options);
        echo "</div>\n</div>\n";
        closeside();
        // primary color themes
        openside('');
        echo "<hr>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3005'], 'info-default');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("primary_color", $locale['theme_3006'], $this->data['primary_color'], $color_options);
        echo form_colorpicker("warning_color", $locale['theme_3007'], $this->data['warning_color'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("success_color", $locale['theme_3008'], $this->data['success_color'], $color_options);
        echo form_colorpicker("danger_color", $locale['theme_3009'], $this->data['danger_color'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("info_color", $locale['theme_3010'], $this->data['info_color'], $color_options);
        echo "</div>\n</div>\n";
        closeside();
        //buttons
        openside('');
        echo "<hr>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3011'], 'btneff');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_select("btn_fill", $locale['theme_3012'], $this->data['btn_fill'], [
            "placeholder" => $locale['theme_2033'],
            "width"       => "100%",
            "options"     => $this->fills
        ]);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("btn_border", $locale['theme_3013'], $this->data['btn_border'], $width_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("btn_radius", $locale['theme_3014'], $this->data['btn_radius'], $width_options);
        echo "</div>\n</div>\n";
        echo "<hr>\n";
        // button primary
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3015'], 'btn-p');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3016'], 'btn-p-normal');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_primary", $locale['theme_2031'], $this->data['btn_primary'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_primary_color", $locale['theme_2022'], $this->data['btn_primary_color'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3017'], 'btn-p-hover');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_primary_hover", $locale['theme_3019'], $this->data['btn_primary_hover'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_primary_color_hover", $locale['theme_2022'], $this->data['btn_primary_color_hover'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3018'], 'btn-p-active');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_primary_active", $locale['theme_3020'], $this->data['btn_primary_active'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_primary_color_active", $locale['theme_2022'], $this->data['btn_primary_color_active'], $color_options);
        echo "</div></div>\n";
        echo "</div>\n</div>\n";
        closeside();
        // button info
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3021'], 'btn-p');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3016'], 'btn-info-normal');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_info", $locale['theme_2031'], $this->data['btn_info'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_info_color", $locale['theme_2022'], $this->data['btn_info_color'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3017'], 'btn-p-hover');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_info_hover", $locale['theme_2022'], $this->data['btn_info_hover'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_info_color_hover", $locale['theme_2022'], $this->data['btn_info_color_hover'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3018'], 'btn-p-active');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_info_active", $locale['theme_3020'], $this->data['btn_info_active'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_info_color_active", $locale['theme_2022'], $this->data['btn_info_color_active'], $color_options);
        echo "</div></div>\n";
        echo "</div>\n</div>\n";
        closeside();
        // success
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3022'], 'btn-scs');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3016'], 'btn-success-normal');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_success", $locale['theme_2031'], $this->data['btn_success'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_success_color", $locale['theme_2022'], $this->data['btn_success_color'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3017'], 'btn-p-hover');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_success_hover", $locale['theme_3019'], $this->data['btn_success_hover'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_success_color_hover", $locale['theme_2022'], $this->data['btn_success_color_hover'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3018'], 'btn-p-active');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_success_active", $locale['theme_3020'], $this->data['btn_success_active'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_success_color_active", $locale['theme_2022'], $this->data['btn_success_color_active'], $color_options);
        echo "</div></div>\n";
        echo "</div>\n</div>\n";
        closeside();
        // warning
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3023'], 'btn-warning');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3016'], 'btn-warning-normal');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_warning", $locale['theme_2031'], $this->data['btn_warning'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_warning_color", $locale['theme_2022'], $this->data['btn_warning_color'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3017'], 'btn-p-hover');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_warning_hover", $locale['theme_3019'], $this->data['btn_warning_hover'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_warning_color_hover", $locale['theme_2022'], $this->data['btn_warning_color_hover'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3018'], 'btn-p-active');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_warning_active", $locale['theme_3020'], $this->data['btn_warning_active'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_warning_color_active", $locale['theme_2022'], $this->data['btn_warning_color_active'], $color_options);
        echo "</div></div>\n";
        echo "</div>\n</div>\n";
        closeside();
        // danger
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3024'], 'btn-danger');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3016'], 'btn-danger-normal');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_danger", $locale['theme_2031'], $this->data['btn_danger'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_danger_color", $locale['theme_2022'], $this->data['btn_danger_color'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3017'], 'btn-p-hover');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_danger_hover", $locale['theme_3017'], $this->data['btn_danger_hover'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_danger_color_hover", $locale['theme_2022'], $this->data['btn_danger_color_hover'], $color_options);
        echo "</div></div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_3018'], 'btn-p-active');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_danger_active", $locale['theme_3018'], $this->data['btn_danger_active'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("btn_danger_color_active", $locale['theme_2022'], $this->data['btn_danger_color_active'], $color_options);
        echo "</div></div>\n";
        echo "</div>\n</div>\n";
        closeside();
    }

    /* parse font-style */

    private function nav_admin() {
        $locale = fusion_get_locale();
        $width_options = ["width" => "100%", 'placeholder' => 'px'];
        $color_options = ["placeholder" => $locale['theme_2009'], "width" => "100%", "format" => "hex"];
        $fill_options = ["placeholder" => $locale['theme_2033'], "width" => "280px"];
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_4001'], 'navbar-h');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("navbar_height", $locale['theme_4002'], $this->data['navbar_height'], $width_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("navbar_border", $locale['theme_4003'], $this->data['navbar_border'], $width_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("navbar_radius", $locale['theme_4004'], $this->data['navbar_radius'], $width_options);
        echo "</div>\n</div>\n";
        closeside();
        openside('');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_4005'], 'navbar-h2a');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n";
        $fill_options['options'] = $this->fills;
        echo form_select("navbar_fill", $locale['theme_4006'], $this->data['navbar_fill'], $fill_options);
        echo "</div>\n</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("navbar_bg", $locale['theme_2031'], $this->data['navbar_bg'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("navbar_bg_hover", $locale['theme_3019'], $this->data['navbar_bg_hover'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("navbar_bg_active", $locale['theme_3020'], $this->data['navbar_bg_active'], $color_options);
        echo "</div>\n</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_4007'], 'navbar-h2');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("navbar_link_border", $locale['theme_4008'], $this->data['navbar_link_border'], $width_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_text("navbar_link_radius", $locale['theme_4009'], $this->data['navbar_link_radius'], $width_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("navbar_link_border_color", $locale['theme_4010'], $this->data['navbar_link_border_color'], $color_options);
        echo "</div>\n</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_4011'], 'navbar-h3');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("navbar_brand_color", $locale['theme_4012'], $this->data['navbar_brand_color'], $color_options);
        echo form_colorpicker("navbar_font_color", $locale['theme_4013'], $this->data['navbar_font_color'], $color_options);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_select("navbar_brand_decoration", $locale['theme_4014'], $this->data['navbar_brand_decoration'], [
            "width"   => "100%",
            "options" => $this->font_decoration_options
        ]);
        echo form_select("navbar_font_decoration", $locale['theme_2025'], $this->data['navbar_font_decoration'], [
            "width"   => "100%",
            "options" => $this->font_decoration_options
        ]);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo "</div>\n</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_para($locale['theme_4015'], 'navbar-h4');
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("navbar_link_color", $locale['theme_4013'], $this->data['navbar_link_color'], $color_options);
        echo form_select("navbar_link_decoration", $locale['theme_4016'], $this->data['navbar_link_decoration'], [
            "width"   => "100%",
            "options" => $this->font_decoration_options
        ]);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("navbar_link_color_hover", $locale['theme_2028'], $this->data['navbar_link_color_hover'], $color_options);
        echo form_select("navbar_link_decoration_hover", $locale['theme_2025'], $this->data['navbar_link_decoration_hover'], [
            "width"   => "100%",
            "options" => $this->font_decoration_options
        ]);
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
        echo form_colorpicker("navbar_link_color_active", $locale['theme_2034'], $this->data['navbar_link_color_active'], $color_options);
        echo form_select("navbar_link_decoration_active", $locale['theme_2035'], $this->data['navbar_link_decoration_active'], [
            "width"   => "100%",
            "options" => $this->font_decoration_options
        ]);
        echo "</div>\n</div>\n";
        closeside();
    }

    /* Outputs adjusted colors */
    /*private function parse_background($hex, $fill_type) {
        // possible fill types in atom
        // darken or lighten functions via steps of 255 max.
        $hex_value = str_replace('#', '', $hex);
        $stop_hex = $this->adjustBrightness($hex_value, -20);
        switch ($fill_type) {
            case 0: // flat
                return $hex;
                break;
            case 1: // horizontal
                return "
                    background: ".$hex.";
                    background: -moz-linear-gradient(left, ".$hex." 0%, ".$stop_hex." 100%);
                    background: -webkit-gradient(linear, left top, right top, color-stop(0%, ".$hex."), color-stop(100%, ".$stop_hex."));
                    background: -webkit-linear-gradient(left, ".$hex." 0%,".$stop_hex." 100%);
                    background: -o-linear-gradient(left, ".$hex." 0%,".$stop_hex." 100%);
                    background: -ms-linear-gradient(left, ".$hex." 0%,".$stop_hex." 100%);
                    background: linear-gradient(to right, ".$hex." 0%,".$stop_hex." 100%);
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$hex."', endColorstr='".$stop_hex."',GradientType=1 );
                ";
                break;
            case 2: // vertical
                return "
                    background: ".$hex.";
                    background: -moz-linear-gradient(top, ".$hex." 0%, ".$stop_hex." 100%);
                    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,".$hex."), color-stop(100%,".$stop_hex."));
                    background: -webkit-linear-gradient(top, ".$hex." 0%,".$stop_hex." 100%);
                    background: -o-linear-gradient(top, ".$hex." 0%,".$stop_hex." 100%);
                    background: -ms-linear-gradient(top, ".$hex." 0%,".$stop_hex." 100%);
                    background: linear-gradient(to bottom, ".$hex." 0%,".$stop_hex." 100%);
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$hex."', endColorstr='".$stop_hex."',GradientType=0 );
                ";
                break;
            case 3: //radial
                return "
                    background: ".$hex.";
                    background: -moz-radial-gradient(center, ellipse cover, ".$hex." 0%, ".$stop_hex." 100%);
                    background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%,".$hex."), color-stop(100%,".$stop_hex."));
                    background: -webkit-radial-gradient(center, ellipse cover, ".$hex." 0%,".$stop_hex." 100%);
                    background: -o-radial-gradient(center, ellipse cover, ".$hex." 0%,".$stop_hex." 100%);
                    background: -ms-radial-gradient(center, ellipse cover, ".$hex." 0%,".$stop_hex." 100%);
                    background: radial-gradient(ellipse at center, ".$hex." 0%,".$stop_hex." 100%);
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$hex."', endColorstr='".$stop_hex."',GradientType=1 );
                ";
                break;
            case 4: // diagonal
                return "
                    background: ".$hex.";
                    background: -moz-linear-gradient(-45deg, ".$hex." 0%, ".$stop_hex." 100%);
                    background: -webkit-gradient(linear, left top, right bottom, color-stop(0%,".$hex."), color-stop(100%,".$stop_hex."));
                    background: -webkit-linear-gradient(-45deg, ".$hex." 0%,".$stop_hex." 100%);
                    background: -o-linear-gradient(-45deg, ".$hex." 0%,".$stop_hex." 100%);
                    background: -ms-linear-gradient(-45deg, ".$hex." 0%,".$stop_hex." 100%);
                    background: linear-gradient(135deg, ".$hex." 0%,".$stop_hex." 100%);
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$hex."', endColorstr='".$stop_hex."',GradientType=1);
                ";
                break;
            default:
                return $hex;
        }
    }*/

    /* Background parser */
    static function adjustBrightness($hex, $percent) {
        /* Function by Torkill Johnsen */
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        //$steps = max(-255, min(255));
        $steps = $percent > 0 ? 255 * ($percent / 100) : -255 * ($percent / 100);
        $steps = $steps >= 255 ? 255 : $steps;
        $steps = $steps <= 255 ? -255 : $steps;
        // Format the hex color string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2).str_repeat(substr($hex, 1, 1), 2).str_repeat(substr($hex, 2, 1), 2);
        }
        // Get decimal values
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        // Adjust number of steps and keep it inside 0 to 255
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
        $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
        $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

        return '#'.$r_hex.$g_hex.$b_hex;
    }
}
