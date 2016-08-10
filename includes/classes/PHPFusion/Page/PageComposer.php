<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: CustomPage.php
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
// Page builder?
// Rename custom_pages.php to page.php

namespace PHPFusion\Page;

if (!defined("IN_FUSION")) { die("Access Denied"); }

class PageComposer {

    private static $page_instance = null;

    private static $data = array(
        'page_id' => '',
        'page_title' => '',
        'page_link_cat' => 0,
        'page_access' => 0,
        'page_content' => '',
        'page_keywords' => '',
        'page_language' => LANGUAGE,
        'page_allow_comments' => 0,
        'page_allow_ratings' => 0,
        // to join instead and add publish or unpublished only
        'link_id' => 0,
        'link_order' => 0,
    );

    protected static $admin_composer_opts = array();

    /**
     * Return page composer object
     * @param bool|FALSE $set_info
     * @return null|static
     */
    public static function getInstance($set_info = false) {
        if (empty(self::$page_instance)) {
            self::$page_instance = new Static;
        }

        return self::$page_instance;
    }

    /**
     * Development notes:
     * Page layout is defined in layout.php where it uses
     * render_page(). To supercede the method, panel needs to be embedded into a container
     *
     */
    public static function getSitePanel() {

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
        // Declare panels side
        $p_name = array(array('name' => 'LEFT', 'side' => 'left'),
                        array('name' => 'U_CENTER', 'side' => 'upper'),
                        array('name' => 'L_CENTER', 'side' => 'lower'),
                        array('name' => 'RIGHT', 'side' => 'right'),
                        array('name' => 'AU_CENTER', 'side' => 'aupper'),
                        array('name' => 'BL_CENTER', 'side' => 'blower'),
        );

        // Get panels data to array
        $panels_cache = array();
        $p_result = dbquery("SELECT panel_name, panel_filename, panel_content, panel_side, panel_type, panel_access, panel_display, panel_url_list, panel_restriction, panel_languages FROM ".DB_PANELS." WHERE panel_status='1' ORDER BY panel_side, panel_order");
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

        foreach ($p_name as $p_key => $p_side) {

            if (isset($panels_cache[$p_key+1]) || defined("ADMIN_PANEL")) {
                ob_start();
                if (!defined("ADMIN_PANEL")) {
                    if (self::check_panel_status($p_side['side'])) {

                        // Panel display can be deprecated - For compatibility reasons.
                        foreach ($panels_cache[$p_key+1] as $p_data) {

                            $url_arr = explode("\r\n", $p_data['panel_url_list']);

                            $url = array();
                            foreach($url_arr as $url_list) {
                                $url[] = $url_list; //strpos($urldata, '/', 0) ? $urldata : '/'.
                            }

                            $show_panel = FALSE;
                            /*
                             * show only if the following conditions are met:
                             * */
                            switch($p_data['panel_restriction']) {
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

                            if ($show_panel) {

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
                } else if ($p_key == 0) {
                    //require_once ADMIN."navigation.php";
                }
                define($p_side['name'], ("<section='content_".$p_side['name']."'>".( $p_side['name'] === 'U_CENTER' ? $admin_mess : '').ob_get_contents() )."</section>");
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
            $base_url_count = substr_count(BASEDIR, "/")+1;
            $match_url = "";
            while ($base_url_count != 0) {
                $current = $url_count-$base_url_count;
                $match_url .= "/".$script_url[$current];
                $base_url_count--;
            }
            if (!in_array($match_url, $exclude_list) && !in_array($match_url.(FUSION_QUERY ? "?".FUSION_QUERY : ""), $exclude_list)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return TRUE;
        }
    }

    protected static $info = array(
        'title' => '',
        'error' => '',
        'body' => '',
        'count' => 0,
        'pagenav' => '',
        'show_comments' => '',
        'show_ratings' => '',
    );

    public static function set_PageInfo() {

        $locale = fusion_get_locale("", LOCALE.LOCALESET."custom_pages.php");

        if (!isset($_GET['page_id']) || !isnum($_GET['page_id'])) redirect("index.php");

        self::$info['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

        $page_query = "SELECT * FROM ".DB_CUSTOM_PAGES."
            WHERE page_id='".intval($_GET['page_id'])."' AND ".groupaccess('page_access')."
            ".(multilang_table("CP") ? "AND ".in_group("page_language", LANGUAGE) : "");

        $cp_result = dbquery($page_query);

        if (dbrows($cp_result) > 0) {

            $cp_data = dbarray($cp_result);

            add_to_title($locale['global_200'].$cp_data['page_title']);

            add_breadcrumb(array('link'=>BASEDIR."viewpage.php?page_id=".$_GET['page_id'], 'title'=>$cp_data['page_title']));

            if (!empty($cp_data['page_keywords'])) set_meta("keywords", $cp_data['page_keywords']);

            // build administration composer status
            if (checkrights('CP')) {

                self::$admin_composer_opts = array(
                    '0' => array(
                        '2' => array('link_id' => 'layout', 'link_cat'=>0, 'link_name' => 'Layout', 'link_url'=> clean_request('compose=layout', array('compose'), false), 'link_visibility' => USER_LEVEL_ADMIN),
                        '3' => array('link_id' => 'panel', 'link_cat'=>0, 'link_name' => 'Panels', 'link_url'=> clean_request('compose=panel', array('compose'), false), 'link_visibility' => USER_LEVEL_ADMIN),
                        '4' => array('link_id' => 'widget', 'link_cat'=>0, 'link_name' => 'Widgets', 'link_url'=> clean_request('compose=widget', array('compose'), false), 'link_visibility' => USER_LEVEL_ADMIN),
                    )
                );

            }



            self::$info['title'] = $cp_data['page_title'];

            ob_start();
            if (fusion_get_settings("allow_php_exe")) {
                eval("?>".stripslashes($cp_data['page_content'])."<?php ");
            } else {
                echo "<p>".parse_textarea($cp_data['page_content'])."</p>\n";
            }
            $eval = ob_get_contents();
            ob_end_clean();
            self::$info['body'] = preg_split("/<!?--\s*pagebreak\s*-->/i", (fusion_get_settings("tinymce_enabled") ? $eval : nl2br($eval)));
            self::$info['count'] = count(self::$info['body']);

            if (self::$info['count'] > 0) {
                if (self::$info['rowstart'] > self::$info['count']) redirect(BASEDIR."viewpage.php?page_id=".$_GET['page_id']);
                self::$info['pagenav'] = makepagenav(self::$info['rowstart'], 1, self::$info['count'], 1, BASEDIR."viewpage.php?page_id=".$_GET['page_id']."&amp;")."\n";
            }

            if ($cp_data['page_allow_comments']) {
                ob_start();
                require_once INCLUDES."comments_include.php";
                showcomments("C", DB_CUSTOM_PAGES, "page_id", $_GET['page_id'], BASEDIR."viewpage.php?page_id=".$_GET['page_id']);
                self::$info['show_comments'] = ob_get_contents();
                ob_end_clean();
            }

            if ($cp_data['page_allow_ratings']) {
                ob_start();
                require_once INCLUDES."ratings_include.php";
                showratings("C", $_GET['page_id'], BASEDIR."viewpage.php?page_id=".$_GET['page_id']);
                self::$info['show_ratings'] = ob_get_contents();
                ob_end_clean();
            }
            unset($cp_data);

        } else {

            add_to_title($locale['global_200'].$locale['401']);

            self::$info['title'] = $locale['401'];
            self::$info['error'] = $locale['402'];
        }

    }

    // need an administration interface - to couple with theme engine
    public static function render_composer() {
        echo "This is layout administration";
        echo "Give this a couple of grid options";
    }

    /**
     * Displays a single custom page data
     * @param $id - page_id
     * @return array;
     */
    public static function load_customPage($id) {
        $array = array();
        if (isnum($id)) {
            $array = dbarray(
                dbquery("
                    SELECT cp.*, link.link_id, link.link_order
                    FROM ".DB_CUSTOM_PAGES." cp
                    LEFT JOIN ".DB_SITE_LINKS." link on (cp.page_link_cat = link.link_cat AND link.link_url='viewpage.php?page_id=".intval($id)."' )
                    WHERE page_id= '".intval($id)."'
                    ")
            );
        }
        return (array) $array;
    }

    public static function query_customPage($id = null) {

        $result = dbquery("
                    SELECT cp.*, link.link_id, link.link_order
                    FROM ".DB_CUSTOM_PAGES." cp
                    LEFT JOIN ".DB_SITE_LINKS." link on (cp.page_link_cat = link.link_cat AND ".in_group("link.link_url", "viewpage.php?page_id=")."
                     AND ".in_group("link.link_url", "cp.page_id").")
                    ".($id !== NULL && isnum($id) ? " WHERE page_id= '".intval($id)."' " : "")."
                    ");

        return $result;
    }

    /**
     * SQL delete page
     * @param $page_id
     */
    protected function delete_customPage($page_id) {
        global $aidlink, $locale;
        if (isnum($page_id) && self::verify_customPage($page_id)) {
            $result = dbquery("DELETE FROM ".DB_CUSTOM_PAGES." WHERE page_id='".intval($page_id)."'");
            if ($result) $result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_url='viewpage.php?page_id=".intval($page_id)."'");
            if ($result) {
                addNotice("success", $locale['413']);
                redirect(FUSION_SELF.$aidlink);
            }
        }
    }

    /**
     * Authenticate the page ID is valid
     * @param $id
     * @return bool|string
     */
    protected function verify_customPage($id) {
        if (isnum($id)) {
            return dbcount("(page_id)", DB_CUSTOM_PAGES, "page_id='".intval($id)."'");
        }
        return FALSE;
    }

    /**
     * Displays Custom Page Selector
     */
    public static function display_customPage_selector() {
        global $aidlink;

        $locale = fusion_get_locale("", LOCALE.LOCALESET."custom_pages.php");

        $result = dbquery("SELECT page_id, page_title, page_language FROM ".DB_CUSTOM_PAGES." ".(multilang_table("CP") ? "WHERE page_language='".LANGUAGE."'" : "")." ORDER BY page_title");

        echo "<div class='pull-right'>\n";
        echo openform('selectform', 'get', ADMIN.'custom_pages.php'.$aidlink);
        echo "<div class='pull-left m-t-0'>\n";

        $edit_opts = array();
        if (dbrows($result) != 0) {
            while ($data = dbarray($result)) {
                $edit_opts[$data['page_id']] = $data['page_title'];
            }
        }
        echo form_select('cpid', '', isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : '',
                         array(
                             "options" => $edit_opts,
                             "class" => 'm-b-0',
                             "required" => TRUE,
                         ));
        echo form_hidden('section', '', 'cp2');
        echo form_hidden('aid', '', iAUTH);
        echo "</div>\n";
        echo form_button('action', $locale['edit'], 'edit', array('class' => 'btn-default pull-left m-l-10 m-r-10'));
        echo form_button('action', $locale['delete'], 'delete', array(
            'class' => 'btn-danger pull-left',
            'icon' => 'fa fa-trash'
        ));
        echo closeform();
        echo "</div>\n";
    }

}
