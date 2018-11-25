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
    const MenuDefaultID = 'DefaultMenu';
    protected static $position_opts = [];
    private static $id = '';
    private static $instances = [];
    private static $primary_cache_data = [];
    private static $optional_cache_data = [];

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
     * Get Sitelinks SQL Row
     *
     * @param $id
     *
     * @return array|bool
     */
    public static function get_SiteLinks($id) {
        $data = [];
        $link_query = "SELECT * FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='$id'";
        $result = dbquery($link_query);
        if (dbrows($result) > 0) {
            $data = dbarray($result);
        }

        return $data;
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
     * Link ID validation
     *
     * @param $link_id
     *
     * @return bool|string
     */
    public static function verify_SiteLinks($link_id) {
        if (isnum($link_id)) {
            return dbcount("(link_id)", DB_SITE_LINKS, "link_id='".intval($link_id)."'");
        }

        return FALSE;
    }

    /**
     * SQL Delete Site Link Action
     *
     * @param $link_id
     *
     * @return bool|mixed|null|resource
     */
    public static function delete_SiteLinks($link_id) {
        $result = NULL;
        if (isnum($link_id)) {
            $data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'"));
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order>'".$data['link_order']."'");
            if ($result) {
                $result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'");
            }

            return $result;
        }

        return $result;
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
     * @return static
     */
    public static function setSubLinks(array $options = []) {
        /*
         * If set an ID, it will re-run the class to create a new object again.
         */
        $default_options = [
            'id'                   => self::MenuDefaultID,
            'container'            => FALSE,
            'container_fluid'      => FALSE,
            'responsive'           => TRUE,
            'navbar_class'         => 'navbar-default',
            'nav_class'            => '',
            'additional_nav_class' => '',
            'item_class'           => '', // $class
            'locale'               => [],
            'separator'            => '', // $sep
            'links_per_page'       => '',
            'grouping'             => '',
            'show_banner'          => FALSE,
            'show_header'          => FALSE,
            'language_switcher'    => FALSE,
            'searchbar'            => FALSE,
            'search_icon'          => 'fa fa-search',
            'searchbar_btn_class'  => 'btn-primary',
            'caret_icon'           => 'caret',
            'callback_data'        => '',
            'additional_data'      => '',
            'link_position'        => [2, 3],
            'html_content'         => '',
            'header_content'       => '',
            'custom_banner'        => '',
            'affix_top'            => '',                   // Adds affix from top offset
            'affix_bottom'         => '',                   // Adds affix from bottom offset
        ];

        $options += $default_options;

        if (!isset(self::$instances[$options['id']]->menu_options)) {
            $settings = fusion_get_settings();
            $options['locale'] += fusion_get_locale();

            if (!$options['links_per_page']) {
                $options['links_per_page'] = $settings['links_per_page'];
            }

            if (empty($options['grouping'])) {
                $options['grouping'] = $settings['links_grouping'];
            }

            if (empty($options['callback_data'])) {
                $options['callback_data'] = self::get_SiteLinksData(['link_position' => $options['link_position']]);
            }

            $options['banner'] = $settings['sitebanner'] && $options['show_banner'] == TRUE ? "<img src='".BASEDIR.$settings['sitebanner']."' alt='".$settings['sitename']."'/>" : $settings['sitename'];

            $pageInfo = pathinfo($_SERVER['REQUEST_URI']);
            $start_page = $pageInfo['dirname'] !== "/" ? ltrim($pageInfo['dirname'], "/")."/" : "";
            $site_path = ltrim($settings['site_path'], "/");
            $start_page = str_replace([$site_path, '\/'], ['', ''], $start_page);
            $start_page .= $pageInfo['basename'];

            if ($settings['site_seo'] && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
                $filepath = Router::getRouterInstance()->getFilePath();
                $start_page = $filepath;
            }

            $options['start_page'] = $start_page;

            self::$instances[$options['id']] = self::getInstance($options['id']);

            self::$id = $options['id'];

            self::$instances[$options['id']]->menu_options = $options;
        }

        return (object)self::$instances[$options['id']];
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
    public static function get_SiteLinksData(array $options = []) {
        $default_position = [2, 3];

        /*
         * $options['link_position'] - accepts either string or array
         */
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

        $query_replace = "";
        if (!empty($options)) {
            $query_replace = "SELECT sl.* ".(!empty($options['select']) ? ", ".$options['select'] : '')." ";
            $query_replace .= "FROM ".DB_SITE_LINKS." sl ";
            $query_replace .= $options['join']." ";
            $query_replace .= "WHERE ".$options['position_condition'].$options['condition'];
            $query_replace .= (!empty($options['group']) ? " GROUP BY ".$options['group']." " : "")." ORDER BY ".$options['order'];
        }

        return (array)dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "", $query_replace);
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public static function getInstance($id = self::MenuDefaultID) {
        self::$id = $id;
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        } else {
            return self::$instances[$id] = new static();
        }
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
     */
    public static function addMenuLink($link_id = 0, $link_name, $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '') {
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
            'link_class'    => $link_class
        ];
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
     */
    public static function addOptionalMenuLink($link_id = 0, $link_name, $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '') {
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
        ];
    }

    private static function setLinks() {
        $primary_cache = (isset(self::$primary_cache_data[self::$id])) ? self::$primary_cache_data[self::$id] : [];

        $secondary_cache = (isset(self::$optional_cache_data[self::$id])) ? self::$optional_cache_data[self::$id] : [];
        if (!empty(self::getMenuParam('callback_data')) && is_array(self::getMenuParam('callback_data'))) {
            if (isset($primary_cache)) {
                self::replaceMenuParam('callback_data', array_replace_recursive(self::getMenuParam('callback_data'), $primary_cache));
            }
        } else {
            self::replaceMenuParam('callback_data', $primary_cache);
        }

        if (!empty(self::getMenuParam('additional_data') && is_array(self::getMenuParam('additional_data')))) {
            if (isset($secondary_cache)) {
                self::replaceMenuParam('additional_data', array_replace_recursive(self::getMenuParam('additional_data'), $secondary_cache));
            }
        } else {
            self::replaceMenuParam('additional_data', $secondary_cache);
        }

        // Change hierarchy data when grouping is activated
        if (self::getMenuParam('grouping')) {
            $callback_data = self::getMenuParam('callback_data');
            if (isset($callback_data[0])) {
                if (count($callback_data[0]) > self::getMenuParam('links_per_page')) {
                    $more_index = 9 * 10000000;
                    $base_data = $callback_data[0];
                    $data[$more_index] = array_slice($base_data, self::getMenuParam('links_per_page'), 9, TRUE);
                    $data[0] = array_slice($base_data, 0, self::getMenuParam('links_per_page'), TRUE);
                    $more[$more_index] = [
                        "link_id"         => $more_index,
                        "link_cat"        => 0,
                        "link_name"       => fusion_get_locale('global_700'),
                        "link_url"        => "#",
                        "link_icon"       => "",
                        "link_visibility" => 0,
                        "link_position"   => 2,
                        "link_window"     => 0,
                        "link_order"      => self::getMenuParam('links_per_page'),
                        "link_language"   => LANGUAGE
                    ];
                    $data[0] = $data[0] + $more;
                    $data = $data + $callback_data;
                    self::replaceMenuParam('callback_data', $data);
                }
            }
        }
    }

    /**
     * @return string
     */
    private function getaffix() {
        $options = self::getMenuParam();
        $html = '';
        if ($options['affix_top'] && isnum($options['affix_top']) or $options['affix_bottom'] && isnum($options['affix_bottom'])) {
            $html = " data-spy='affix'";
            if ($options['affix_top']) $html .= " data-offset-top='".$options['affix_top']."'";
            if ($options['affix_bottom']) $html .= " data-offset-bottom='".$options['affix_bottom']."'";
        }

        return (string)$html;
    }

    public function showSubLinks($id = 0) {
        $options = self::getMenuParam();
        $settings = fusion_get_settings();
        $locale = $options['locale'];
        if (empty($id)) {
            $id = self::MenuDefaultID;
            self::setLinks();
        }

        $bootstrap3_nav_li_dropdown = THEMES.'templates/boilers/bootstrap3/html/navbar/nav_li_dropdown.html';
        $bootstrap3_nav_li_link = THEMES.'templates/boilers/bootstrap3/html/navbar/nav_li_link.html';
        $bootstrap3_nav_li_no_link = THEMES.'templates/boilers/bootstrap3/html/navbar/nav_li_no_link.html';
        $bootstrap3_nav = THEMES.'templates/boilers/bootstrap3/html/navbar/navbar.html';

        // Secondary menu

        $language_dropdown = '';
        if ($options['language_switcher'] === TRUE) {
            if (count(fusion_get_enabled_languages()) > 1) {
                $language_switch = fusion_get_language_switch();
                $current_language = $language_switch[LANGUAGE];
                // Build the LI first.
                if (!empty($language_switch)) {
                    $nav_li_link = Template::getInstance('nav_li_link');
                    foreach ($language_switch as $folder => $langData) {
                        $nav_li_link->set_block('nav_li_link', [
                            'link'  => $langData['language_link'],
                            'title' => '<img alt="'.$langData['language_name'].'" class="m-r-5" src="'.$langData['language_icon_s'].'"/> '.$langData['language_name'],
                        ]);
                    }
                    $nav_li_link->set_template($bootstrap3_nav_li_link);
                    $language_items = $nav_li_link->get_output();

                    $language_li_dropdown_tpl = Template::getInstance('language_switch_li');
                    $ss = [
                        'title'               => translate_lang_names(LANGUAGE),
                        'link'                => '#',
                        'content'             => '<img src="'.$current_language["language_icon_s"].'">',
                        'caret_class'         => $options['caret_icon'],
                        'dropdown_menu_class' => ' dropdown-menu-right',
                        'item'                => $language_items,
                    ];
                    $language_li_dropdown_tpl->set_block('nav_li_dropdown', $ss);
                    $language_li_dropdown_tpl->set_template($bootstrap3_nav_li_dropdown);
                    $language_dropdown = $language_li_dropdown_tpl->get_output();
                }
            }
        }

        $searchbar_dropdown = '';
        if ($options['searchbar']) {
            // LI first
            $nav_li = Template::getInstance('nav_li');
            $nav_li->set_text($bootstrap3_nav_li_no_link);
            $nav_li->set_block('nav_li_no_link', [
                'content' => openform('searchform', 'post', FUSION_ROOT.BASEDIR.'search.php?stype=all',
                        [
                            'class'      => 'm-b-10',
                            'remote_url' => $settings['site_path'].'search.php'
                        ]
                    ).form_text('stext', '', '',
                        [
                            'placeholder'        => $locale['search'],
                            'append_button'      => TRUE,
                            'append_type'        => "submit",
                            "append_form_value"  => $locale['search'],
                            "append_value"       => "<i class='".$options['search_icon']."'></i> ".$locale['search'],
                            "append_button_name" => "search",
                            "append_class"       => $options['searchbar_btn_class'],
                            'class'              => 'm-0',
                        ]
                    ).closeform()
            ]);

            $searchbar_tpl = Template::getInstance('searchbar_li');
            $ss = [
                'title'               => $locale['search'],
                'link'                => '#',
                'content'             => '<i class="'.$options['search_icon'].'"></i>',
                'caret_class'         => $options['caret_icon'],
                'dropdown_menu_class' => ' dropdown-menu-right',
                'item'                => $nav_li->get_output(),
            ];
            $searchbar_tpl->set_block('nav_li_dropdown', $ss);
            $searchbar_tpl->set_template($bootstrap3_nav_li_dropdown);
            $searchbar_dropdown = $searchbar_tpl->get_output();
        }

        $primary_links = $this->showMenuLinks(0, $options['callback_data']);
        $secondary_links = $this->showMenuLinks(0, $options['additional_data']);

        $tpl = Template::getInstance($id);

        $tpl->set_tag('navbar_id', $id);
        $tpl->set_tag('navbar_class', $options['navbar_class']);
        $tpl->set_tag('affix', self::getaffix());

        if ($options['container'] || $options['container_fluid']) {
            if ($options['container']) {
                $tpl->set_block('open_container', ['class' => 'container']);
                $tpl->set_block('close_container', ['class' => 'container']);
            } elseif ($options['container_fluid']) {
                $tpl->set_block('open_container', ['class' => 'container-fluid']);
                $tpl->set_block('close_container', ['class' => 'container-fluid']);
            }
        }

        if ($options['show_header']) {
            $banner = ($options['show_banner'] ? ($options['custom_banner'] ? $options['custom_banner'] : $settings['sitebanner']) : '');
            $header_settings = [
                'id'              => $id.'_menu',
                'header_content'  => $options['header_content'],
                'banner_link'     => BASEDIR.$settings['opening_page'],
                'banner'          => $banner,
                'banner_position' => $settings['logoposition_xs']." ".$settings['logoposition_sm']." ".$settings['logoposition_md']." ".$settings['logoposition_lg'],
            ];
            $tpl->set_block('navbar_header', $header_settings);

        }

        $tpl->set_tag('html_content', $options['html_content']);
        $tpl->set_tag('menu_item', $primary_links);
        if (!empty($language_dropdown) || !empty($searchbar_dropdown) || !empty($secondary_links)) {
            $tpl->set_block('menu_alt', ['menu_alt_item' => $secondary_links.$language_dropdown.$searchbar_dropdown]);
        }

        $tpl->set_template($bootstrap3_nav);

        return $tpl->get_output();

    }

    public static function getMenuParam($key = FALSE) {
        if ($key) {
            return (isset(self::$instances[self::$id]->menu_options[$key])) ? self::$instances[self::$id]->menu_options[$key] : NULL;
        }

        return self::$instances[self::$id]->menu_options;
    }

    public static function replaceMenuParam($key, $value) {
        self::$instances[self::$id]->menu_options[$key] = $value;
    }

    public static function setMenuParam($key, $value) {
        self::$instances[self::$id]->menu_options[$key] = (is_bool($value)) ? $value : self::getMenuParam($key).$value;
    }

    private function get_SubLinks_URL($data, $link_id) {
        $linkRef = [];
        if (isset($data[$link_id])) {
            foreach ($data[$link_id] as $link) {
                $linkRef[$link['link_id']] = $link['link_url'];
                if (isset($data[$link['link_id']])) {
                    $linkRef = array_merge_recursive($linkRef, $this->get_SubLinks_URL($data, $link['link_id']));
                }
            }
        }

        return $linkRef;
    }

    private static $link_instances = [];

    private function getLinkInstance() {
        if (empty(self::$link_instances)) {
            $linkInstance = BreadCrumbs::getInstance();
            $linkInstance->showHome(FALSE);
            $linkInstance->setLastClickable(TRUE);
            self::$link_instances = $linkInstance->toArray();
        }

        return self::$link_instances;
    }

    /*
     * Recursion loop of data for navigational menu
     */
    private function showMenuLinks($id, $data) {
        $options = self::getMenuParam();
        $settings = fusion_get_settings();

        $bootstrap3_nav_li_dropdown = THEMES.'templates/boilers/bootstrap3/html/navbar/nav_li_dropdown.html';
        $bootstrap3_nav_li_link = THEMES.'templates/boilers/bootstrap3/html/navbar/nav_li_link.html';
        $bootstrap3_nav_li_no_link = THEMES.'templates/boilers/bootstrap3/html/navbar/nav_li_nolink.html';
        $bootstrap3_nav_li_divider = THEMES.'templates/boilers/bootstrap3/html/navbar/nav_li_divider.html';

        $res = '';
        if (!empty($data[$id])) {
            $i = 0;
            $default_link_data = [
                "link_id"       => 0,
                "link_name"     => "",
                "link_cat"      => 0,
                "link_url"      => "",
                "link_icon"     => "",
                "link_class"    => "",
                "link_active"   => '',
                "link_title"    => FALSE, // true to add dropdown-header class to li.
                "link_disabled" => FALSE, // true to disable link
                "link_window"   => FALSE,
            ];
            foreach ($data[$id] as $link_id => $link_data) {
                $tpl = Template::getInstance('link_item');

                $li_class = [];
                $link_data += $default_link_data;
                $link_data['link_name'] = $settings['link_bbcode'] ? parsesmileys(parseubb($link_data['link_name'])) : $link_data['link_name'];
                // Calculate link class
                if ($link_data['link_disabled']) {
                    $li_class[] = "disabled";
                } else {
                    if (!empty($options['item_class'])) $li_class[] = $options['item_class'];
                    if (empty($link_data['link_url'])) $li_class[] = "no-link";
                    if ($link_data['link_title'] == TRUE) $li_class[] = "dropdown-header";
                }

                /*
                 * Attempt to calculate a relative link
                 * Taking into account that current start page does not match
                 */
                $secondary_active = FALSE;
                // Active Helper Function
                // If developer does not set it as true/false deliberately, only then system takes into account to calculate.
                // The default values for link_active is blank, not false or true.
                // It is therefore encouraged to set true or false when adding links for best efficiency.
                if (!is_bool($link_data['link_active'])) {
                    // If the current link_url does not contain request parameters, this link should be active
                    if (!stristr($link_data['link_url'], "?")) {
                        if (defined('IN_PERMALINK')) {
                            if (Router::getRouterInstance()->getFilePath() == $link_data['link_url']) {
                                $secondary_active = TRUE;
                            }
                        } else {
                            // format the link
                            $data_link_url = $link_data['link_url'];
                            if (stristr($link_data['link_url'], "index.php")) {
                                $data_link_url = str_replace("index.php", "", $data_link_url);
                            }
                            $url = parse_url(htmlspecialchars_decode($_SERVER['REQUEST_URI']));
                            $current_url = str_replace(fusion_get_settings('site_path'), "", $url['path']);
                            if (stristr($url['path'], "index.php")) {
                                $current_url = str_replace("index.php", "", $current_url);
                            }
                            if ($data_link_url == $current_url) {
                                $secondary_active = TRUE;
                            }
                        }
                    }

                    // not the first link
                    if (self::getMenuParam('start_page') !== $link_data['link_url']) {
                        // All Sublinks will be compared to - stable
                        $linkRef = $this->get_SubLinks_URL($data, $link_data['link_id']);
                        $linkRefURI = [];
                        if (!empty($linkRef)) {
                            $linkRefURI = array_flip($linkRef);
                        }

                        // The breadcrumbs series of arrays - stable
                        $reference = $this->getLinkInstance();
                        if (!empty($reference)) {

                            $uri = parse_url(htmlspecialchars_decode($link_data['link_url']));
                            $uriQuery = [];
                            if (!empty($uri['query'])) {
                                parse_str($uri['query'], $uriQuery);
                            }
                            foreach ($reference as $refData) {
                                if (stristr($refData['link'], '../')) {
                                    $refData['link'] = str_replace(str_repeat('../', substr_count($refData['link'], '../')), '', $refData['link']);
                                }
                                if (!empty($refData['link']) && $link_data['link_url'] !== "index.php") {
                                    //If child link is part of the current page breadcrumb then parent is active
                                    if (!empty($refData['link'])) {
                                        if (isset($linkRefURI[$refData['link']])) {
                                            $secondary_active = TRUE;
                                            break;
                                        }
                                    }
                                    // If parts of link url forms the breadcrumbs' link
                                    if (!empty($link_data['link_url']) && stristr($refData['link'], $link_data['link_url'])) {
                                        $secondary_active = TRUE;
                                        break;
                                    }
                                    // If both links has the same uri requests string.
                                    if (!empty($link_data['link_url']) && stristr($link_data['link_url'], '?')) {
                                        $ref_uri = parse_url(htmlspecialchars_decode($refData['link']));
                                        if (!empty($uri['query']) && !empty($ref_uri['query'])) {
                                            parse_str($ref_uri['query'], $ref_uriQuery);
                                            if (count($ref_uriQuery) == count($uriQuery)) {
                                                $diff = array_diff_assoc($uriQuery, $ref_uriQuery);
                                                $diff_2 = array_diff_assoc($ref_uriQuery, $uriQuery);
                                                if ($diff == $diff_2) {
                                                    $secondary_active = TRUE;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                if ($secondary_active) {
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {
                    $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : '');
                    if ($i == 0 && $id > 0) {
                        $li_class[] = "first-link";
                    }
                    $link_is_active = $link_data['link_active'];
                    if ($secondary_active) {
                        $link_is_active = TRUE;
                    } else if (strtr(FUSION_REQUEST, [fusion_get_settings('site_path') => '', '&amp;' => '&']) == str_replace('../', '', $link_data['link_url'])) {
                        $link_is_active = TRUE;
                    } else if ($options['start_page'] == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } else if ($settings['site_path'].$options['start_page'] == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } else if (($options['start_page'] == $settings['opening_page'] && $i == 0 && $id === 0)) {
                        $link_is_active = TRUE;
                    } else if ($link_data['link_url'] === '#') {
                        $link_is_active = FALSE;
                    }
                    if ($link_is_active) {
                        $li_class[] = "current-link active";
                    }

                    $itemlink = '';
                    if (!empty($link_data['link_url'])) {
                        $link_data['link_url'] = str_replace('{%aidlink%}', fusion_get_aidlink(), $link_data['link_url']);
                        $itemlink = BASEDIR.$link_data['link_url'];
                        // if link has site protocol
                        if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url']) or (BASEDIR !== '' && stristr($link_data['link_url'], BASEDIR))) {
                            $itemlink = $link_data['link_url'];
                        }
                    }

                    if (isset($data[$link_id])) {

                        $template_path = $bootstrap3_nav_li_dropdown;
                        $template_obj = 'nav_li_dropdown';

                        $has_child = TRUE;
                        $tab_index = !empty($id) ? " tabindex='0'" : '';

                        $l_1 = " class='dropdown-toggle".(!empty($link_data['link_class']) ? " ".$link_data['link_class'] : '')."' data-toggle='dropdown' ";
                        $l_1 .= (empty($id) && $has_child ? "data-submenu " : "");

                        $li_class[] = (!empty($id) ? "dropdown-submenu" : "dropdown");
                        $li_class = array_filter($li_class);

                        $li_content = $link_data['link_icon'] ? "<i class='".$link_data['link_icon']."'></i>" : "";
                        $li_content .= $link_data['link_name'];

                        $first_item_link = '';
                        if (!empty($link_data['link_url']) and $link_data['link_url'] !== "#") {
                            $first_item_link .= "<li".(!$itemlink ? " class='no-link'" : '').">\n".self::getMenuParam('seperator');
                            $first_item_link .= ($itemlink ? "<a ".$itemlink." ".$link_target.">\n" : '');
                            $first_item_link .= (!empty($link_data['link_icon']) ? "<i class='".$link_data['link_icon']." m-r-5'></i>\n" : "");
                            $first_item_link .= $link_data['link_name'];
                            $first_item_link .= ($itemlink ? "\n</a>\n" : '');
                            $first_item_link .= "</li>\n";
                        }

                        $template_settings = [
                            'li_class'            => (!empty($li_class) ? 'class="'.implode(" ", $li_class).'"' : ''),
                            'link_class'          => $l_1,
                            'link'                => $itemlink,
                            'title'               => $link_data['link_name'],
                            'link_target'         => $link_target,
                            'tab_index'           => $tab_index,
                            'content'             => $li_content,
                            'caret_class'         => $options['caret_icon'],
                            'dropdown_menu_id'    => 'menu-'.$link_data['link_id'],
                            'dropdown_menu_class' => '',
                            'first_item_link'     => $first_item_link,
                            'submenu'             => 'data-submenu',
                            'item'                => $this->showMenuLinks($link_data['link_id'], $data)
                        ];

                    } else {

                        $link_class = (!empty($link_data['link_class']) ? " class='".$link_data['link_class']."'" : '');
                        $li_content = $link_data['link_icon'] ? "<i class='".$link_data['link_icon']."'></i>" : "";
                        $li_content .= $link_data['link_name'];

                        $template_path = $bootstrap3_nav_li_no_link;
                        $template_obj = 'nav_li_no_link';
                        $template_settings = [
                            'link_class' => $link_class,
                            'content'    => $li_content,
                        ];
                        if (!empty($itemlink)) {
                            $template_path = $bootstrap3_nav_li_link;
                            $template_obj = 'nav_li_link';
                            $template_settings = [
                                'link_class' => $link_class,
                                'link'       => $itemlink,
                                'title'      => $li_content,
                            ];
                        }
                    }

                } else {
                    $template_path = $bootstrap3_nav_li_divider;
                    $template_obj = 'nav_li_divider';
                }

                $tpl->set_template($template_path);
                $tpl->set_block($template_obj, $template_settings);
                $res .= $tpl->get_output();

                $i++;
            }
        }

        return $res;
    }

}
