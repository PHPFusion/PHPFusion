<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Panels.inc
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

namespace PHPFusion;

use PHPFusion\Rewrite\Router;

class Panels {
    const PANEL_LEFT = 1;
    const PANEL_U_CENTER = 2;
    const PANEL_L_CENTER = 3;
    const PANEL_RIGHT = 4;
    const PANEL_AU_CENTER = 5;
    const PANEL_BL_CENTER = 6;
    const PANEL_USER1 = 7;
    const PANEL_USER2 = 8;
    const PANEL_USER3 = 9;
    const PANEL_USER4 = 10;
    private static $panel_instance = NULL;
    private static $panel_name = array(
        array('name' => 'LEFT', 'side' => 'left'),
        array('name' => 'U_CENTER', 'side' => 'upper'),
        array('name' => 'L_CENTER', 'side' => 'lower'),
        array('name' => 'RIGHT', 'side' => 'right'),
        array('name' => 'AU_CENTER', 'side' => 'aupper'),
        array('name' => 'BL_CENTER', 'side' => 'blower'),
        array('name' => 'USER1', 'side' => 'user1'),
        array('name' => 'USER2', 'side' => 'user2'),
        array('name' => 'USER3', 'side' => 'user3'),
        array('name' => 'USER4', 'side' => 'user4')
    );

    private static $panel_excluded = array();
    private static $panels_cache = array();
    private static $panel_id = 0;
    private static $available_panels = array();

    public static function getInstance($set_info = TRUE, $panel_id = 0) {
        if (self::$panel_instance === NULL) {
            self::$panel_instance = new static();
            if ($set_info) {
                self::cachePanels();
            }
        }
        if ($panel_id) {
            self::$panel_id = 1;
        }

        return self::$panel_instance;
    }

    /**
     * Cache panels
     *
     * @return array
     */
    public static function cachePanels() {
	
		$panel_query = "SELECT * FROM ".DB_PANELS." WHERE panel_status=:panel_status ORDER BY panel_side ASC, panel_order ASC";
        $param = [
            ':panel_status' => 1,
        ];
        $p_result = dbquery($panel_query, $param);
        if (dbrows($p_result)) {
            $panel_side = 0;
            $panel_order = 0;
            while ($panel_data = dbarray($p_result)) {
                if ($panel_data['panel_side'] !== $panel_side) {
                    $panel_data['panel_order'] = 0;
                }
                $panel_side = $panel_data['panel_side'];
                $panel_order = $panel_order + 1;
                $panel_data['panel_order'] = $panel_order;
                if (multilang_table('PN')) {
                    $p_langs = explode('.', $panel_data['panel_languages']);
                    if (checkgroup($panel_data['panel_access']) && in_array(LANGUAGE, $p_langs)) {
                        self::$panels_cache[$panel_data['panel_side']][$panel_data['panel_order']] = $panel_data;
                    }
                } else {
                    if (checkgroup($panel_data['panel_access'])) {
                        self::$panels_cache[$panel_data['panel_side']][$panel_data['panel_order']] = $panel_data;
                    }
                }
            }
        }

        return (array)self::$panels_cache;
    }

    /**
     * Add Panel to List of Panels to be cached
     *
     * @param $panel_name
     * @param $panel_content
     * @param $panel_side
     * @param $panel_access
     * @param $panel_order
     */
    public static function addPanel($panel_name, $panel_content, $panel_side, $panel_access, $panel_order = 0) {

        self::$panels_cache[$panel_side][] = [
            'panel_id'          => str_replace(" ", "_", $panel_name).'-'.$panel_side,
            'panel_content'     => $panel_content,
            'panel_side'        => $panel_side,
            'panel_filename'    => '',
            'panel_type'        => 'custom',
            'panel_access'      => $panel_access,
            'panel_status'      => 1,
            'panel_display'     => 1,
            'panel_url_list'    => '',
            'panel_restriction' => 3,
            'panel_languages'   => implode('.', fusion_get_enabled_languages()),
            'panel_order'       => $panel_order,
        ];
    }

    /**
     * Display a panel given a panel id
     *
     * @param $panel_id
     *
     * @return string
     */
    public static function display_panel($panel_id) {
        $html = "";
        if (!empty(self::$panels_cache)) {
            $panels = flatten_array(self::$panels_cache);
            foreach ($panels as $panelData) {
                if ($panelData['panel_id'] == $panel_id) {
                    ob_start();
                    if ($panelData['panel_type'] == "file") {
                        if (file_exists(INFUSIONS.$panelData['panel_filename']."/".$panelData['panel_filename'].".php")) {
                            include INFUSIONS.$panelData['panel_filename']."/".$panelData['panel_filename'].".php";
                        }
                    } else {
                        if (fusion_get_settings("allow_php_exe")) {
                            eval(stripslashes($panelData['panel_content']));
                        } else {
                            echo parse_textarea($panelData['panel_content']);
                        }
                    }
                    $html = ob_get_contents();
                    ob_end_clean();

                    return $html;
                }
            }
        }

        return $html;
    }

    /**
     * Get excluded panel list
     *
     * @return array
     */
    public static function getPanelExcluded() {
        return (array)self::$panel_excluded;
    }

    /**
     * Get all available panels
     *
     * @param string $excluded_panels
     *
     * @return array
     */
    public static function get_available_panels($excluded_panels = '') {
        // find current installed panels.
        if (empty(self::$available_panels)) {
            $temp = opendir(INFUSIONS);
            self::$available_panels['none'] = fusion_get_locale('469');
            while ($folder = readdir($temp)) {
                if (!in_array($folder, array(
                        ".",
                        ".."
                    )) && strstr($folder, "_panel")
                ) {

                    if (is_dir(INFUSIONS.$folder)) {
                        self::$available_panels[$folder] = $folder;
                    }
                    if ((!empty($excluded_panels) && in_array($folder, $excluded_panels))) {
                        unset(self::$available_panels[$folder]);
                    }
                }
            }
            closedir($temp);
        }

        return (array)self::$available_panels;
    }

    /**
     * Hides panel
     *
     * @param $side - 'LEFT', 'RIGHT', 'U_CENTER', 'L_CENTER', 'AU_CENTER', 'BL_CENTER', 'USER1', 'USER2', 'USER3', 'USER4'
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

        if (empty(self::$panels_cache)) {
            self::cachePanels();
        }

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
        add_to_footer($admin_mess);
        // Optimize this part to cache_panels
        foreach (self::$panel_name as $p_key => $p_side) {

            if (isset(self::$panels_cache[$p_key + 1]) || defined("ADMIN_PANEL")) {

                ob_start();
                if (!defined("ADMIN_PANEL")) {

                    if (self::check_panel_status($p_side['side']) && !isset(self::$panel_excluded[$p_key + 1])) {

                        foreach (self::$panels_cache[$p_key + 1] as $p_data) {

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

                            if ($show_panel === TRUE) { // Prevention of rendering unnecessary files
                                if ($p_data['panel_type'] == "file") {
                                    if (file_exists(INFUSIONS.$p_data['panel_filename']."/".$p_data['panel_filename'].".php")) {
                                        include INFUSIONS.$p_data['panel_filename']."/".$p_data['panel_filename'].".php";
                                    }
                                } else {
                                    if (fusion_get_settings("allow_php_exe")) {
                                        // This is slowest of em all.
                                        $panelStart = '';
                                        $panelEnd = '';
                                        if ($p_data['panel_type'] == 'custom') {
                                            if (!strpos($p_data['panel_content'], '<?php')) {
                                                //$panelContent .= "<?php ".PHP_EOL;
                                                $panelStart .= "echo \"".PHP_EOL;
                                            }
                                            if (!strpos($p_data['panel_content'], '?>')) {
                                                $panelEnd .= "\";".PHP_EOL;
                                            }
                                            $p_data['panel_content'] = str_replace("\"", "\\'", $p_data['panel_content']);
                                        }
                                        $panelContent = $panelStart.stripslashes($p_data['panel_content']).$panelEnd;
                                        eval($panelContent);

                                    } else {
                                        echo $p_data['panel_content'];
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

                $html = "<div class='content".ucfirst($p_side['side'])."'>";
                $html .= $content;
                $html .= "</div>\n";

                define($p_side['name'], (!empty($content) ? $html : ''));
                ob_end_clean();

            } else {
                // This is in administration
                define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : ''));

            }
        }

    }

    /**
     * Check panel exclusions in certain page, which will be dropped sooner or later
     * Because we will need page composition database soon
     *
     * @param $side
     *
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
        } elseif ($side == "user1") {
            if ($settings['exclude_user1'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user1']);
            }
        } elseif ($side == "user2") {
            if ($settings['exclude_user2'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user2']);
            }
        } elseif ($side == "user3") {
            if ($settings['exclude_user3'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user3']);
            }
        } elseif ($side == "user4") {
            if ($settings['exclude_user4'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user4']);
            }
        }

        if (is_array($exclude_list)) {
            if (fusion_get_settings('site_seo')) {
                $params = http_build_query(Router::getRouterInstance()->get_FileParams());
                $file_path = Router::getRouterInstance()->getFilePath().($params ? "?" : '').$params;
                $script_url = explode("/", $file_path);
            } else {
				$script_url = explode("/", $_SERVER['PHP_SELF']);
            }
            $url_count = count($script_url);
            $base_url_count = substr_count(BASEDIR, "/") + 1;
            $match_url = "";
            while ($base_url_count != 0) {
                $current = $url_count - $base_url_count;
                $match_url .= "/".$script_url[$current];
                $base_url_count--;
            }

            return (in_array($match_url, $exclude_list)) ? FALSE : TRUE;
        } else {
            return TRUE;
        }
    }
}
