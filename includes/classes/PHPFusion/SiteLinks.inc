<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: SiteLinks.inc
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

namespace PHPFusion;

use PHPFusion\Rewrite\Router;

/**
 * Class SiteLinks
 *
 * @package PHPFusion
 *          Navigational Bar
 */
class SiteLinks {

    const MenuDefaultID = 'DefaultMenu';

    protected static $position_opts = [];

    private static $default_options = [
        'id'                   => self::MenuDefaultID,
        'container'            => FALSE,
        'container_fluid'      => FALSE,
        'responsive'           => TRUE,
        'navbar_class'         => 'navbar-expand-lg navbar-light bg-light',
        'nav_class'            => '',
        'additional_nav_class' => '',
        'item_class'           => '', // $class
        'separator'            => '', // $sep
        'links_per_page'       => '',
        'grouping'             => '',
        'show_banner'          => TRUE,
        'show_header'          => TRUE,
        'custom_header'        => '',
        'header_content'       => '',
        'custom_banner'        => '',
        'custom_banner_link'   => '',
        'language'             => '',
        'searchbar'            => FALSE,
        'search_icon'          => 'fa fa-search',
        'search_btn_class'     => 'btn-primary',
        'caret_icon'           => 'caret',
        //'callback_data'        => '',
        'additional_data'      => '',
        'link_position'        => [2, 3],
        'html_content'         => '',
        'affix_top'            => '',                   // Adds affix from top offset
        'affix_bottom'         => '',                   // Adds affix from bottom offset
        'html_pre_content'     => '',
        'html_post_content'    => '',
        'nav_class_primary'    => '',
        'nav_class_secondary'  => '',
    ];
    /**
     * @param string $sep
     * @param string $class
     * @param array  $options
     *
     * @return static
     *
     * A blank static is set up once for each available $options['id']
     * If same instance exists, options can be mutated to alter the behavior of the menu
     *
     * Simple Usage: SiteLinks::setSublinks($sep, $class, $options)->showSubLinks();
     *
     * So in order to add a cart icon, we must declare at theme.
     *
     */
    private static $instances = [];

    private static $id = '';

    private static $primary_cache_data = [];

    private static $optional_cache_data = [];

    private static $current_url = "";

    /**
     * Get Site Links Position Options
     *
     * @return array
     */
    public static function get_SiteLinksPosition() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/sitelinks.php");
        if (empty(self::$position_opts)) {
            self::$position_opts = [
                '1' => $locale['SL_0025'], // only css navigational panel
                '2' => $locale['SL_0026'], // both
                '3' => $locale['SL_0027'], // subheader
                '4' => $locale['custom']." ID",
            ];
        }

        return (array)self::$position_opts;
    }

    /**
     * Given a matching URL, fetch Sitelinks data
     *
     * @param string $url - url to match (link_url) column
     * @param string $key - column data to output, blank for all
     *
     * @return array|bool
     */
    public static function get_current_SiteLinks($url = "", $key = NULL) {
        $url = stripinput($url);
        static $data = [];
        if (empty($data)) {
            if (!$url) {
                $url = FUSION_FILELINK;
            }
            $result = dbquery("SELECT * FROM ".DB_SITE_LINKS." WHERE link_url='".$url."' AND link_language='".LANGUAGE."'");
            if (dbrows($result) > 0) {
                $data = dbarray($result);
            }
        }

        return $key === NULL ? (array)$data : (isset($data[$key]) ? $data[$key] : NULL);
    }

    /**
     * Get Group Array
     *
     * @return array
     */
    public static function get_LinkVisibility() {
        static $visibility_opts = [];
        $user_groups = getusergroups();
        foreach ($user_groups as $key => $user_group) {
            $visibility_opts[$user_group['0']] = $user_group['1'];
        }

        return (array)$visibility_opts;
    }

    /**
     * Calling the SiteLinks instance with Custom Parameters
     *
     * @param array $options
     *
     * @return mixed
     * @throws \Exception
     */
    public static function setSubLinks(array $options = []) {
        $options += self::$default_options;
        $instance = self::getInstance($options['id']);

        if (!isset($instance->menu_options)) {
            $settings = fusion_get_settings();
            // System link settings
            $link_settings = [
                'start_page'     => $instance->getStartPage(),
                'links_per_page' => $settings['links_per_page'],
                'links_grouping' => $settings['links_grouping'],
                'items'          => $options['callback_data'] !== FALSE ? $instance->fetchSiteLinks(['link_position' => $options['link_position']]) : []
            ];
            $options += $link_settings;

            $instance->id = $options['id'];
            $instance->menu_options = $options;
        }

        return $instance;

    }

    /**
     * @param string $id
     *
     * @return SiteLinks
     */
    private static function getInstance($id = self::MenuDefaultID) {
        self::$id = $id;
        if (empty(self::$instances[$id])) {
            self::$instances[$id] = new static();
        }
        return self::$instances[$id];
    }

    private function getStartPage() {
        $settings = fusion_get_settings();
        if ($settings['site_seo'] && defined('IN_PERMALINK') && get("aid") === NULL) {
            return Router::getRouterInstance()->getFilePath();
        }
        $pageInfo = pathinfo(server('REQUEST_URI'));
        $site_path = ltrim($settings['site_path'], "/");
        $start_page = $pageInfo['dirname'] !== "/" ? ltrim($pageInfo['dirname'], "/")."/" : "";
        $start_page = str_replace([$site_path, '\/'], ['', ''], $start_page);
        $start_page .= $pageInfo['basename'];

        return $start_page;
    }

    /**
     * Fetches Site Links Hierarchy Data - for a less support complexity
     *
     * @param array $options
     * - join
     * - link_position (array)
     * - condition
     * - group
     * - order
     *
     * @return array
     */
    public static function fetchSiteLinks(array $options = []) {
        $default_position = [2, 3];

        $link_position = '';
        if (!empty($options['link_position'])) {
            $link_position = $options['link_position'];
            if (is_array($link_position)) {
                $link_position = implode(' OR sl.link_position=', $link_position);
            }
        }

        $default_link_filter = [
            'join'               => '',
            'position_condition' => '(sl.link_position='.($link_position ? $link_position : implode(' OR sl.link_position=', $default_position)).')',
            'condition'          => (multilang_table("SL") ? " AND link_language='".LANGUAGE."'" : "")." AND ".groupaccess('link_visibility')." AND link_status=1",
            'group'              => '',
            'order'              => "link_cat ASC, link_order ASC",
        ];

        $options += $default_link_filter;

        $query_replace = "SELECT sl.* {%SELECT%} FROM ".DB_SITE_LINKS." sl {%JOIN%} WHERE {%POST_CONDITION%}{%CONDITION%} {%GROUP%} ORDER BY {%ORDER%}";

        if (!empty($options)) {
            $query_replace = strtr($query_replace, [
                '{%SELECT%}'         => (!empty($options['select']) ? ", ".$options['select'] : ''),
                '{%JOIN%}'           => $options['join'],
                '{%POST_CONDITION%}' => $options['position_condition'],
                '{%CONDITION%}'      => $options['condition'],
                '{%GROUP%}'          => (!empty($options['group']) ? " GROUP BY ".$options['group']." " : ""),
                '{%ORDER%}'          => $options['order']
            ]);
        }

        return (array)dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "", $query_replace);
    }

    /**
     * Add a link to secondary menu
     *
     * @param int        $link_id
     * @param string     $link_name
     * @param int        $link_cat
     * @param string     $link_url
     * @param string     $link_icon
     * @param bool|FALSE $link_active
     * @param bool|FALSE $link_title
     * @param bool|FALSE $link_disabled
     * @param bool|FALSE $link_window
     * @param string     $link_class
     * @param bool|FALSE $link_content
     */
    public static function addOptionalMenuLink($link_id = 0, $link_name = '', $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '', $link_content = FALSE) {
        self::$optional_cache_data[self::$id][$link_cat][$link_id] = [
            'link_id'       => $link_id,
            'link_name'     => $link_name,
            'link_cat'      => $link_cat,
            'link_url'      => $link_url,
            'link_icon'     => $link_icon,
            'link_active'   => $link_active,
            'link_title'    => $link_title,
            'link_disabled' => $link_disabled,
            'link_window'   => $link_window,
            'link_class'    => $link_class,
            'link_content'  => $link_content,
        ];
    }

    public static function setMenuParam($key, $value) {
        self::$instances[self::$id]->menu_options[$key] = (is_bool($value)) ? $value : self::getMenuParam($key).$value;
    }

    private static function getMenuParam($key = FALSE) {
        if ($key) {
            return (isset(self::$instances[self::$id]->menu_options[$key])) ? self::$instances[self::$id]->menu_options[$key] : NULL;
        }

        return self::$instances[self::$id]->menu_options;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private static function getBoilerFiles() {
        $boiler_files = boilerplate_get_files();
        if (!empty($boiler_files)) {
            list(self::$nav_path, self::$nav_li_path, self::$nav_li_link_path, self::$nav_li_dropdown_path, self::$nav_divider_path) = array_values($boiler_files['nav']);
        }
        return $boiler_files;
    }

    /**
     * Add a link to primary menu
     *
     * @param int        $link_id
     * @param string     $link_name
     * @param int        $link_cat
     * @param string     $link_url
     * @param string     $link_icon
     * @param bool|FALSE $link_active
     * @param bool|FALSE $link_title
     * @param bool|FALSE $link_disabled
     * @param bool|FALSE $link_window
     * @param string     $link_class
     * @param bool|FALSE $link_content
     */
    public function addMenuLink($link_id = 0, $link_name = '', $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '', $link_content = FALSE) {
        self::$primary_cache_data[self::$id][$link_cat][$link_id] = [
            'link_id'       => $link_id,
            'link_name'     => $link_name,
            'link_cat'      => $link_cat,
            'link_url'      => $link_url,
            'link_icon'     => $link_icon,
            'link_active'   => $link_active,
            'link_title'    => $link_title,
            'link_disabled' => $link_disabled,
            'link_window'   => $link_window,
            'link_class'    => $link_class,
            'link_content'  => $link_content,
        ];
    }

    public function showSubLinks() {
        $this->cacheSiteLinks();
        $options = self::getMenuParam();
        $options['language'] = $this->getLanguage($options);
        $options['search'] = $this->getSearch($options);
        $options['callback_data'] = $this->parseLinks(0, 'callback_data');
        $options['additional_data'] = $this->parseLinks(0, 'additional_data');
        //print_P($options);
        $fusion_steam = Steam::getInstance();

        return $fusion_steam->load('Navigation')->render($options);
    }

    private function cacheSiteLinks() {

        $options = self::getMenuParam();

        $callback_data = $options['callback_data'];
        $primary_cache = (isset(self::$primary_cache_data[self::$id]) ? self::$primary_cache_data[self::$id] : []);

        if (!empty($options['callback_data']) && is_array($callback_data)) {
            self::replaceMenuParam('callback_data', array_replace_recursive($callback_data, $primary_cache));
        } else {
            self::replaceMenuParam('callback_data', $primary_cache);
        }

        $callback_data = $options['additional_data'];
        $secondary_cache = (isset(self::$optional_cache_data[self::$id]) ? self::$optional_cache_data[self::$id] : []);

        if (!empty($callback_data && is_array($callback_data))) {
            self::replaceMenuParam('additional_data', array_replace_recursive($callback_data, $secondary_cache));
        } else {
            self::replaceMenuParam('additional_data', $secondary_cache);
        }

        // Change hierarchy data when grouping is activated
        if ($options['grouping']) {
            // Fetch the latest callback data changes
            $callback_data = self::getMenuParam('callback_data');
            if (isset($callback_data[0])) {
                if (count($callback_data[0]) > $options['links_per_page']) {
                    $more_index = 9 * 1000;
                    $base_data = $callback_data[0];
                    $data[$more_index] = array_slice($base_data, $options['links_per_page'], 9, TRUE);
                    $data[0] = array_slice($base_data, 0, $options['links_per_page'], TRUE);
                    $more[$more_index] = [
                        "link_id"         => $more_index,
                        "link_cat"        => 0,
                        "link_name"       => $options['locale']['global_700'],
                        "link_url"        => "#",
                        "link_icon"       => "",
                        "link_visibility" => 0,
                        "link_position"   => 2,
                        "link_window"     => 0,
                        "link_order"      => $options['links_per_page'],
                        "link_language"   => LANGUAGE
                    ];
                    $data[0] = $data[0] + $more;
                    $data = $data + $callback_data;
                    self::replaceMenuParam('callback_data', $data);
                }
            }
        }
    }

    private function replaceMenuParam($key, $value) {
        self::$instances[self::$id]->menu_options[$key] = $value;
    }

    private function getLanguage($info) {
        // Secondary menu
        if ($info['language_switcher'] === TRUE) {
            if (count(fusion_get_enabled_languages()) > 1) {

                $language_switch = fusion_get_language_switch();
                $language_opts = [];
                $current_language = $language_switch[LANGUAGE];

                if (!empty($language_switch)) {

                    foreach ($language_switch as $lang_folder => $lang_data) {
                        $language_opts[$lang_folder] = [
                            'link'  => $lang_data['language_link'],
                            'title' => $lang_data['language_name'],
                            'image' => '<img alt="'.$lang_data['language_name'].'" class="m-r-5" src="'.$lang_data['language_icon_s'].'"/>'
                        ];
                    }

                    return [
                        'title'           => translate_lang_names(LANGUAGE),
                        'link'            => '#',
                        'content'         => '<img src="'.$current_language["language_icon_s"].'">',
                        'caret_class'     => $info['caret_icon'],
                        'item'            => $language_opts,
                        'first_item_link' => '',
                    ];

                }
                return [];
            }
            return [];
        }
        return [];
    }

    private function getSearch($info) {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        if ($info['searchbar']) {
            // Add in a new default search form.
            // LI first
            return openform('searchform', 'post', FUSION_ROOT.BASEDIR.'search.php?stype='.$settings['default_search'],
                    [
                        'inline' => TRUE,
                        //'remote_url' => $settings['site_path'].'search.php'
                    ]
                ).form_text('stext', '', '',
                    [
                        'placeholder'        => $locale['search'],
                        'append_button'      => TRUE,
                        'append_type'        => "submit",
                        "append_form_value"  => $locale['search'],
                        "append_value"       => "<i class='".$info['search_icon']."' title='".$locale['search']."'></i>",
                        "append_button_name" => "search",
                        "append_class"       => $info['search_btn_class'],
                        'class'              => 'm-0',
                    ]
                ).closeform();
        }
        return '';
    }

    private function parseLinks($id = 0, $key = 'callback_data') {
        $options = self::getMenuParam();
        $settings = fusion_get_settings();
        $data = $options[$key];

        if (!empty($data[$id])) {

            $default_link_data = [
                "link_id"       => 0,
                "link_name"     => "",
                "link_cat"      => 0,
                "link_url"      => "",
                "link_icon"     => "",
                "link_li_class" => "",
                "link_class"    => "",
                "link_active"   => "",
                "link_title"    => FALSE, // true to add dropdown-header class to li.
                "link_disabled" => FALSE, // true to disable link
                "link_window"   => FALSE,
                "link_content"  => FALSE, // true to make link_name as HTML content
            ];
            $counter = 0;

            foreach ($data[$id] as $link_id => $link_data) {

                $link_data += $default_link_data;

                // get link title in formatted bbcode
                $link_name = ($settings['link_bbcode'] ? parsesmileys(parseubb($link_data['link_name'])) : $link_data['link_name']);
                // check whether the link is active.
                $link_active = $this->checkLinkActive($link_data['link_id'], $link_data['link_url'], $data); // Data would have not inherited all default param.

                $link_disabled = ($link_data['link_disabled'] ? TRUE : FALSE);

                $link_url = $this->getLinkUrl();

                $link_class = $this->getLinkClass($link_data, $options, $counter);

                $data[$id][$link_id] = [
                        'link_name'     => $link_name,
                        'link_active'   => $link_active,
                        'link_disabled' => $link_disabled,
                        'link_url'      => $link_url,
                        'link_class'    => $link_class,
                    ] + $link_data;

                $counter++;

                //if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {
                //
                //    $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : '');
                //

                //    $itemlink = '';
                //
                //
                //
                //    if (isset($data[$link_id])) {
                //
                //        // Child Item
                //        $template_path = self::$nav_li_dropdown_path;
                //        $template_obj = 'nav_li_dropdown';
                //        $has_child = TRUE;
                //
                //        $tab_index = !empty($id) ? " tabindex='0'" : '';
                //
                //        $l_1 = " class='dropdown-toggle".(!empty($link_data['link_class']) ? " ".$link_data['link_class'] : '')."' data-toggle='dropdown' ";
                //        $l_1 .= (empty($id) && $has_child ? "data-submenu " : '');
                //
                //        $li_class[] = (!empty($id) ? "dropdown-submenu" : "dropdown");
                //        $li_class = array_filter($li_class);
                //
                //        $li_content = ($link_data['link_icon'] ? "<i class='".$link_data['link_icon']."'></i>" : "");
                //        $li_content .= $link_data['link_name'];
                //
                //        $first_item_link = '';
                //        if (!empty($link_data['link_url']) and $link_data['link_url'] !== "#") {
                //            $first_item_link .= "<li".(!$itemlink ? " class='no-link'" : '').">\n".self::getMenuParam('seperator');
                //            $first_item_link .= ($itemlink ? "<a href='".$itemlink."' ".$link_target.">\n" : '');
                //            $first_item_link .= (!empty($link_data['link_icon']) ? "<i class='".$link_data['link_icon']." m-r-5'></i>\n" : "");
                //            $first_item_link .= $link_data['link_name'];
                //            $first_item_link .= ($itemlink ? "\n</a>\n" : '');
                //            $first_item_link .= "</li>\n";
                //        }
                //
                //        $template_settings = [
                //            'li_class'            => (!empty($li_class) ? 'class="'.implode(" ", $li_class).'"' : ''),
                //            'link_class'          => $l_1,
                //            'link'                => $itemlink,
                //            'title'               => $link_data['link_name'],
                //            'link_target'         => $link_target,
                //            'tab_index'           => $tab_index,
                //            'content'             => $li_content,
                //            'caret_class'         => $options['caret_icon'],
                //            'dropdown_menu_id'    => 'menu-'.$link_data['link_id'],
                //            'dropdown_menu_class' => '',
                //            'first_item_link'     => $first_item_link,
                //            'submenu'             => 'data-submenu',
                //            'item'                => $this->showMenuLinks($link_data['link_id'], $data)
                //        ];
                //
                //    } else {
                //
                //        $li_class = (!empty($li_class)) ? " class='".implode(" ", $li_class)."'" : "";
                //
                //        $link_class = (!empty($link_data['link_class']) ? " class='".$link_data['link_class']."'" : '');
                //
                //        $li_content = $link_data['link_icon'] ? "<i class='".$link_data['link_icon']."'></i>" : "";
                //
                //        $li_content .= $link_data['link_name'];
                //
                //        $template_path = self::$nav_li_path;
                //
                //        $template_obj = 'nav_li_no_link';
                //
                //        $template_settings = [
                //            'link_class' => $link_class,
                //            'content'    => $li_content,
                //        ];
                //
                //        if (!empty($itemlink)) {
                //
                //            $template_path = self::$nav_li_link_path;
                //            $template_obj = 'nav_li_link';
                //
                //            $template_settings = [
                //                'li_class'   => $li_class,
                //                'link_class' => $link_class,
                //                'link'       => $itemlink,
                //                'title'      => $li_content,
                //            ];
                //
                //        }
                //    }
                //} else {
                //    $template_path = self::$nav_divider_path;
                //    $template_obj = 'nav_li_divider';
                //}
                //$tpl->set_template($template_path);
                //$tpl->set_block($template_obj, $template_settings);
                //$res .= $tpl->get_output();
                //
                //$i++;
            }
            return $data;
        }

        return [];
    }

    /**
     * Attempt to calculate a relative link
     * Taking into account that current start page does not match
     *
     * @param $link_id
     * @param $link_url
     * @param $data
     *
     * @return bool
     */
    private function checkLinkActive($link_id, $link_url, $data) {
        $current_url = $this->getCurrentPage();
        if (!empty($link_url) && $link_url != '#') {
            if (defined('IN_PERMALINK')) {
                if (Router::getRouterInstance()->getFilePath() == $link_url) {
                    return TRUE;
                }
            }

            // compares to current url
            $data_link_url = str_replace("index.php", "", $link_url);
            if ($data_link_url == $current_url) {
                return TRUE;
            }

            // compares page requests
            if (!empty($_GET)) {
                // check which is active.
                $link_ref = explode("?", $link_url);
                if (isset($link_ref[1])) {
                    $l = [];
                    $r = explode('&', htmlspecialchars_decode($link_ref[1]));
                    foreach ($r as $ur) {
                        $u = explode('=', $ur);
                        $l[$u[0]] = isset($u[1]) ? $u[1] : "";
                    }
                    if (empty(array_diff_assoc($l, $_GET))) {
                        return TRUE;
                    }
                }
            }
        }

        if (self::getMenuParam("start_page") !== $link_url) {
            // Find the link_active by checking child active. (Parent must also be active)
            $child_links = $this->getChildPages($data, $link_id);
            if (!empty($child_links)) {
                foreach ($child_links as $child_link_url) {
                    if ($child_link_url != '#') {
                        $data_link_url = str_replace("index.php", '', $child_link_url);
                        if ($data_link_url == $current_url) {
                            return TRUE;
                        }
                        // Find the link_active by comparing with the current page request
                        if (!empty($_GET)) {
                            // check which is active.
                            $link_ref = explode("?", $child_link_url);
                            if (isset($link_ref[1])) {
                                $l = [];
                                $r = explode('&', htmlspecialchars_decode($link_ref[1]));
                                foreach ($r as $ur) {
                                    $u = explode('=', $ur);
                                    $l[$u[0]] = isset($u[1]) ? $u[1] : "";
                                }
                                if (empty(array_diff_assoc($l, $_GET))) {
                                    return TRUE;
                                }
                            }
                        }
                    }
                }
            }
        }
        return FALSE;
    }

    /**
     * Get current URL of the page
     *
     * @return string
     */
    private function getCurrentPage() {
        if (empty(self::$current_url)) {
            $current_url = ltrim(htmlspecialchars_decode(server('REQUEST_URI')), '/');
            $site_path = fusion_get_settings('site_path');
            if ($site_path != '/') {
                $current_url = str_replace($site_path, "", $current_url);
            }
            if (stristr($current_url, "index.php")) {
                $current_url = str_replace("index.php", "", $current_url);
            }
            if (defined('IN_PERMALINK')) {
                $current_url = Router::getRouterInstance()->getFilePath();
            }
            self::$current_url = $current_url;
        }

        return (string)self::$current_url;
    }

    /**
     * @param $data
     * @param $link_id
     *
     * @return array
     */
    private function getChildPages($data, $link_id) {
        $linkRef = [];
        if (isset($data[$link_id])) {
            foreach ($data[$link_id] as $link) {
                if (isset($link['link_url'])) {
                    $linkRef[$link['link_id']] = $link['link_url'];
                    if (isset($data[$link['link_id']])) {
                        $linkRef = array_merge_recursive($linkRef, $this->get_SubLinks_URL($data, $link['link_id']));
                    }
                }
            }
        }

        return (array)$linkRef;
    }

    private function getLinkUrl() {
        if (!empty($link_data['link_url'])) {
            $link_data['link_url'] = str_replace('{%aidlink%}', fusion_get_aidlink(), $link_data['link_url']);
            $itemlink = BASEDIR.$link_data['link_url'];
            // if link has site protocol
            if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url']) or (BASEDIR !== '' && stristr($link_data['link_url'], BASEDIR))) {
                $itemlink = $link_data['link_url'];
            }
            return $itemlink;
        }
        return '';
    }

    private function getLinkClass($link_data, $options, $counter) {

        if ($link_data['link_disabled']) {
            return 'disabled';
        }
        if ($link_data['link_title']) {
            return 'dropdown-header';
        }

        $li_class[] = $link_data['link_li_class'];
        $li_class[] = $options['item_class'];

        if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {

            if (!$counter && !$link_data['link_cat']) {
                $li_class[] = 'first-link';
            }

            if ($link_data['link_active']) {
                $li_class[] = 'current-link active';
            }

            if (empty($link_data['link_url'])) {
                $li_class[] = 'no-link';
            }
        }

        return array_filter($li_class);
    }

}
