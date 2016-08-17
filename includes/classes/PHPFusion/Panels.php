<?php
namespace PHPFusion;

class Panels {

    private static $panel_instance = NULL;

    private static $panel_name = array(
        array('name' => 'LEFT', 'side' => 'left'),
        array('name' => 'U_CENTER', 'side' => 'upper'),
        array('name' => 'L_CENTER', 'side' => 'lower'),
        array('name' => 'RIGHT', 'side' => 'right'),
        array('name' => 'AU_CENTER', 'side' => 'aupper'),
        array('name' => 'BL_CENTER', 'side' => 'blower')
    );

    private static $panel_excluded = array();

    public static function getInstance() {
        if (self::$panel_instance === NULL) {
            self::$panel_instance = new static();
        }

        return (object)self::$panel_instance;
    }

    /**
     * Get excluded panel list
     * @return array
     */
    public static function getPanelExcluded() {
        return (array)self::$panel_excluded;
    }

    /**
     * Hides panel
     * @param $side - 'LEFT', 'RIGHT', 'U_CENTER', 'L_CENTER', 'AU_CENTER', 'BL_CENTER'
     */
    public function hide_panel($side) {
        foreach (self::$panel_name as $p_key => $p_side) {
            if ($p_side['name'] == $side) {
                self::$panel_excluded[$p_key + 1] = $side;
            }
        }
    }

    /**
     * Cache and generate Panel Constants
     */
    public function getSitePanel() {

        $settings = \fusion_get_settings();
        $locale = \fusion_get_locale();

        $site['path'] = ltrim(TRUE_PHP_SELF, '/').(FUSION_QUERY ? "?".FUSION_QUERY : "");
        if ($settings['site_seo'] == 1 && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
            global $filepath;
            $site['path'] = $filepath;
        }

        // Add admin message
        $admin_mess = '';
        $admin_mess .= "<noscript><div class='alert alert-danger noscript-message admin-message'><strong>".$locale['global_303']."</strong></div>\n</noscript>\n<!--error_handler-->\n";

        $panels_cache = array();
        $panel_query = "SELECT panel_name, panel_filename, panel_content, panel_side, panel_type, panel_access, panel_display, panel_url_list, panel_restriction, panel_languages FROM ".DB_PANELS." WHERE panel_status='1' ORDER BY panel_side, panel_order";
        $p_result = dbquery($panel_query);
        if (multilang_table("PN")) {
            while ($panel_data = dbarray($p_result)) {
                $p_langs = explode('.', $panel_data['panel_languages']);
                if (checkgroup($panel_data['panel_access']) && in_array(LANGUAGE, $p_langs)) {
                    $panels_cache[$panel_data['panel_side']][] = $panel_data;
                }
            }
        } else {
            while ($panel_data = dbarray($p_result)) {
                if (checkgroup($panel_data['panel_access'])) {
                    $panels_cache[$panel_data['panel_side']][] = $panel_data;
                }
            }
        }

        foreach (self::$panel_name as $p_key => $p_side) {

            if (isset($panels_cache[$p_key + 1]) || defined("ADMIN_PANEL")) {

                ob_start();
                if (!defined("ADMIN_PANEL")) {

                    if (self::check_panel_status($p_side['side']) && !isset(self::$panel_excluded[$p_key + 1])) {

                        foreach ($panels_cache[$p_key + 1] as $p_data) {

                            $show_panel = FALSE;

                            $url_arr = explode("\r\n", $p_data['panel_url_list']);
                            $url = array();
                            foreach ($url_arr as $url_list) {
                                $url[] = $url_list; //strpos($urldata, '/', 0) ? $urldata : '/'.
                            }


                            switch ($p_data['panel_restriction']) {
                                case 1:
                                    //  Exclude on current url only
                                    //  url_list is set, and panel_restriction set to 1 (Exclude) and current page does not match url_list.
                                    if (!empty($p_data['panel_url_list']) && !in_array($site['path'], $url)) {
                                        $show_panel = TRUE;
                                    }
                                    break;
                                case 2: // Display on home page only
                                    if (!empty($p_data['panel_url_list']) && $site['path'] == fusion_get_settings('opening_page')) {
                                        $show_panel = TRUE;
                                    }
                                    break;
                                case 3: // Display on all pages
                                    //  url_list must be blank
                                    if (empty($p_data['panel_url_list'])) {
                                        $show_panel = TRUE;
                                    }
                                    break;
                                default: // Include on defined url only
                                    //  url_list is set, and panel_restriction set to 0 (Include) and current page matches url_list.
                                    if (!empty($p_data['panel_url_list']) && in_array($site['path'], $url)) {
                                        $show_panel = TRUE;
                                    }
                                    break;
                            }

                            if ($show_panel === TRUE) {
                                if ($p_data['panel_type'] == "file") {
                                    if (file_exists(INFUSIONS.$p_data['panel_filename']."/".$p_data['panel_filename'].".php")) {
                                        include INFUSIONS.$p_data['panel_filename']."/".$p_data['panel_filename'].".php";
                                    }
                                } else {
                                    if (fusion_get_settings("allow_php_exe")) {
                                        eval(stripslashes($p_data['panel_content']));
                                    } else {
                                        echo parse_textarea($p_data['panel_content']);
                                    }
                                }
                            }

                        }
                        unset($p_data);

                        if (multilang_table("PN")) {
                            unset($p_langs);
                        }

                    }

                }

                $content = ob_get_contents();

                $html = "<section='content_".$p_side['name']."'>";
                $html .= ($p_side['name'] === 'U_CENTER' ? $admin_mess : '').$content;
                $html .= "</section>\n";

                define($p_side['name'], (!empty($content) ? $html : ''));
                ob_end_clean();

            } else {
                // This is in administration
                define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : ''));
            }
        }
        unset($panels_cache);

    }

    /**
     * Check panel exclusions in certain page, which will be dropped sooner or later
     * Because we will need page composition database soon
     * @param $side
     * @return bool
     */
    private static function check_panel_status($side) {

        $settings = fusion_get_settings();

        $exclude_list = "";
        if ($side == "left") {
            if ($settings['exclude_left'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_left']);
            }
            if (defined("LEFT_OFF")) {
                $exclude_list[] = FUSION_SELF;
            }
        } elseif ($side == "upper") {
            if ($settings['exclude_upper'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_upper']);
            }
        } elseif ($side == "aupper") {
            if ($settings['exclude_aupper'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_aupper']);
            }
        } elseif ($side == "lower") {
            if ($settings['exclude_lower'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_lower']);
            }
        } elseif ($side == "blower") {
            if ($settings['exclude_blower'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_blower']);
            }
        } elseif ($side == "right") {
            if ($settings['exclude_right'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_right']);
            }
        }
        if (is_array($exclude_list)) {
            $script_url = explode("/", $_SERVER['PHP_SELF']);
            $url_count = count($script_url);
            $base_url_count = substr_count(BASEDIR, "/") + 1;
            $match_url = "";
            while ($base_url_count != 0) {
                $current = $url_count - $base_url_count;
                $match_url .= "/".$script_url[$current];
                $base_url_count--;
            }
            if (!in_array($match_url, $exclude_list) && !in_array($match_url.(FUSION_QUERY ? "?".FUSION_QUERY : ""),
                                                                  $exclude_list)
            ) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return TRUE;
        }
    }

}