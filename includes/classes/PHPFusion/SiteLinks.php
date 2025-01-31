<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: SiteLinks.php
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
 * Navigational Bar
 *
 * @package PHPFusion
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
    const MENU_DEFAULT_ID = 'DefaultMenu';
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
    public static function getSiteLinksPosition() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/sitelinks.php");
        if (empty(self::$position_opts)) {
            self::$position_opts = [
                '1' => $locale['SL_0025'], // only css navigational panel
                '2' => $locale['SL_0026'], // both
                '3' => $locale['SL_0027'], // subheader
                '4' => $locale['custom']." ID",
            ];
        }

        return self::$position_opts;
    }

    /**
     * Get Sitelinks SQL Row
     *
     * @param int $id
     *
     * @return array
     */
    public static function getSiteLinks($id) {
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
     * @param string $url url to match (link_url) column
     * @param string $key column data to output, blank for all
     *
     * @return array|bool
     */
    public static function getCurrentSiteLinks($url = "", $key = NULL) {
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

        return $key === NULL ? $data : (isset($data[$key]) ? $data[$key] : NULL);
    }

    /**
     * Link ID validation
     *
     * @param int $link_id
     *
     * @return int|null
     */
    public static function verifySiteLink($link_id) {
        if (isnum($link_id)) {
            return dbcount("(link_id)", DB_SITE_LINKS, "link_id='".intval($link_id)."'");
        }

        return NULL;
    }

    /**
     * SQL Delete Site Link Action
     *
     * @param int $link_id
     *
     * @return bool|mixed|null|resource
     */
    public static function deleteSiteLink($link_id) {
        if (isnum($link_id)) {
            $data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'"));
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order>'".$data['link_order']."'");
            if ($result) {
                $result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'");
            }

            return $result;
        }

        return NULL;
    }

    /**
     * Get Group Array
     *
     * @return array
     */
    public static function getLinkVisibility() {
        static $visibility_opts = [];
        $user_groups = getusergroups();
        foreach ($user_groups as $user_group) {
            $visibility_opts[$user_group['0']] = $user_group['1'];
        }

        return $visibility_opts;
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
            'id'                   => self::MENU_DEFAULT_ID,
            'container'            => FALSE,
            'container_fluid'      => FALSE,
            'responsive'           => TRUE,
            'navbar_class'         => defined('BOOTSTRAP4') ? 'navbar-expand-lg navbar-light' : 'navbar-default',
            'nav_class'            => defined('BOOTSTRAP4') ? 'navbar-nav ml-auto primary' : '',
            'additional_nav_class' => '',
            'item_class'           => defined('BOOTSTRAP4') ? 'nav-item' : '', // $class
            'locale'               => [],
            'separator'            => '', // $sep
            'links_per_page'       => '',
            'grouping'             => '',
            'show_banner'          => FALSE,
            'show_header'          => FALSE,
            'custom_header'        => '',
            'language_switcher'    => FALSE,
            'searchbar'            => FALSE,
            'search_icon'          => 'fa fa-search',
            'searchbar_btn_class'  => 'btn-primary',
            'caret_icon'           => defined('BOOTSTRAP4') ? '' : 'caret',
            'link_position'        => [2, 3],
            'html_pre_content'     => '',
            'html_content'         => '',
            'html_post_content'    => ''
        ];

        $options += $default_options;

        if (!isset(self::$instances[$options['id']]->menu_options)) {

            $options['locale'] += fusion_get_locale();

            if (!$options['links_per_page']) {
                $options['links_per_page'] = fusion_get_settings('links_per_page');
            }

            if (empty($options['grouping'])) {
                $options['grouping'] = fusion_get_settings('links_grouping');
            }

            if (!isset($options['callback_data']) && empty($options['callback_data'])) {
                $options['callback_data'] = self::getSiteLinksData(['link_position' => $options['link_position']]);
            }

            $options['banner'] = fusion_get_settings('sitebanner') && $options['show_banner'] == TRUE ? "<img src='".BASEDIR.fusion_get_settings("sitebanner")."' alt='".fusion_get_settings("sitename")."'/>" : fusion_get_settings("sitename");

            $pageInfo = pathinfo($_SERVER['REQUEST_URI']);
            $start_page = $pageInfo['dirname'] !== "/" ? ltrim($pageInfo['dirname'], "/")."/" : "";
            $site_path = ltrim(fusion_get_settings("site_path"), "/");
            $start_page = str_replace([$site_path, '\/'], ['', ''], $start_page);
            $start_page .= $pageInfo['basename'];

            if (fusion_get_settings("site_seo") && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
                $filepath = Router::getRouterInstance()->getFilePath();
                $start_page = $filepath;
            }

            $options['start_page'] = $start_page;

            self::$instances[$options['id']] = self::getInstance($options['id']);

            self::$id = $options['id'];

            self::$instances[$options['id']]->menu_options = $options;
        }

        return self::$instances[$options['id']];
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
    public static function getSiteLinksData(array $options = []) {
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
            'position_condition' => '(sl.link_position='.(!empty($link_position) ? $link_position : implode(' OR sl.link_position=', $default_position)).')',
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

        return dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "", $query_replace);
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public static function getInstance($id = self::MENU_DEFAULT_ID) {
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
    public static function addMenuLink($link_id, $link_name, $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '') {
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
    public static function addOptionalMenuLink($link_id, $link_name, $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '') {
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

    /**
     * Init
     */
    private static function setLinks() {
        $primary_cache = (isset(self::$primary_cache_data[self::$id])) ? self::$primary_cache_data[self::$id] : [];

        $secondary_cache = (isset(self::$optional_cache_data[self::$id])) ? self::$optional_cache_data[self::$id] : [];
        if (!empty(self::getMenuParam('callback_data')) && is_array(self::getMenuParam('callback_data'))) {
            if (isset($primary_cache)) {

                self::replaceMenuParam('callback_data', array_replace_recursive((array)self::getMenuParam('callback_data'), $primary_cache));
            }
        } else {
            self::replaceMenuParam('callback_data', $primary_cache);
        }

        if (!empty(self::getMenuParam('additional_data') && is_array(self::getMenuParam('additional_data')))) {
            if (isset($secondary_cache)) {
                self::replaceMenuParam('additional_data', array_replace_recursive((array)self::getMenuParam('additional_data'), $secondary_cache));
            }
        } else {
            self::replaceMenuParam('additional_data', $secondary_cache);
        }

        // Change hierarchy data when grouping is activated
        if (self::getMenuParam('grouping')) {

            $callback_data = (array)self::getMenuParam('callback_data');

            if (!empty($callback_data[0])) {

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
     * @param int $id
     *
     * @return string
     */
    public function showSubLinks($id = 0) {
        $locale = (array)self::getMenuParam('locale');
        $res = '';

        if (empty($id)) {
            self::setLinks();
            $res = "<div id='".self::getMenuParam('id')."' class='navbar ".self::getMenuParam('navbar_class')."' role='navigation'>\n";
            $res .= self::getMenuParam('container') ? "<div class='container'>\n" : "";
            $res .= self::getMenuParam('container_fluid') ? "<div class='container-fluid'>\n" : "";
            if (self::getMenuParam('show_header')) {
                $res .= !defined('BOOTSTRAP4') ? "<div class='navbar-header'>\n" : '';
                $res .= "<!--Menu Header Start-->\n";
                if (self::getMenuParam('responsive') && !defined('BOOTSTRAP4')) {
                    $res .= "<button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#".self::getMenuParam('id')."_menu' aria-expanded='false' aria-controls='#".self::getMenuParam('id')."_menu'>\n";
                    $res .= "<span class='sr-only'>".$locale['global_017']."</span>\n";
                    $res .= "<span class='icon-bar top-bar'></span>\n";
                    $res .= "<span class='icon-bar middle-bar'></span>\n";
                    $res .= "<span class='icon-bar bottom-bar'></span>\n";
                    $res .= "</button>\n";
                }
                if (self::getMenuParam('show_banner') === TRUE) {
                    $res .= "<a class='navbar-brand' href='".BASEDIR.fusion_get_settings('opening_page')."'>".self::getMenuParam('banner')."</a>\n";
                } else if (self::getMenuParam('show_header') === TRUE) {
                    $res .= "<a class='navbar-brand visible-xs hidden-sm hidden-md hidden-lg' href='".BASEDIR.fusion_get_settings('opening_page')."'>".fusion_get_settings("sitename")."</a>\n";
                } else {
                    $res .= self::getMenuParam('show_header');
                }

                if (self::getMenuParam('responsive') && defined('BOOTSTRAP4')) {
                    $res .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#'.self::getMenuParam('id').'_menu" aria-controls="'.self::getMenuParam('id').'_menu" aria-expanded="false">';
                    $res .= '<span class="navbar-toggler-icon"></span>';
                    $res .= '</button>';
                }

                $res .= "<!--Menu Header End-->\n";
                $res .= !defined('BOOTSTRAP4') ? "</div>\n" : '';
            }

            $res .= self::getMenuParam('custom_header');

            if (self::getMenuParam('responsive')) {
                $res .= "<div class='navbar-collapse collapse' id='".self::getMenuParam('id')."_menu'>\n";
            }
            $class = ((defined('BOOTSTRAP') && BOOTSTRAP == TRUE) ? " class='nav navbar-nav primary'" : " id='main-menu' class='primary sm sm-simple'");
            if (self::getMenuParam('nav_class')) {
                $class = " class='".self::getMenuParam('nav_class')."'";
            }

            $res .= self::getMenuParam('html_pre_content');

            $res .= "<ul$class>\n";
            $res .= "<!--Menu Item Start-->\n";

            // Show primary links
            $res .= $this->showMenuLinks($id, self::getMenuParam('callback_data'));
            $res .= "<!--Menu Item End-->\n";
            $res .= "</ul>\n";

            $res .= self::getMenuParam('html_content');

            if (self::getMenuParam('language_switcher') == TRUE || self::getMenuParam('searchbar') == TRUE || !empty(self::getMenuParam('additional_data'))) {
                $class = ((defined('BOOTSTRAP') && BOOTSTRAP == TRUE) ? "class='nav navbar-nav secondary navbar-right'" : "id='second-menu' class='secondary sm sm-simple'");
                if (self::getMenuParam('additional_nav_class')) {
                    $class = "class='".self::getMenuParam('additional_nav_class')."'";
                }

                $res .= "<ul $class>\n";

                $res .= $this->showMenuLinks($id, self::getMenuParam('additional_data'));

                if (self::getMenuParam('language_switcher') == TRUE) {
                    if (count(fusion_get_enabled_languages()) > 1) {
                        $language_switch = fusion_get_language_switch();
                        $current_language = $language_switch[LANGUAGE];
                        $language_opts = "<li class='nav-item dropdown' role='presentation'>";
                        $language_opts .= "<a id='ddlangs".$id."' href='#' class='nav-link dropdown-toggle pointer' role='menuitem' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' title='".translate_lang_names(LANGUAGE)."'><img class='m-r-5' src='".$current_language['language_icon_s']."' alt='".translate_lang_names(LANGUAGE)."'/> <span class='".self::getMenuParam('caret_icon')."'></span></a>";
                        $language_opts .= "<ul class='dropdown-menu dropdown-menu-right' aria-labelledby='ddlangs".$id."' role='menu'>\n";
                        if (!empty($language_switch)) {
                            foreach ($language_switch as $langData) {
                                $language_opts .= "<li class='text-left'><a href='".$langData['language_link']."'>";
                                $language_opts .= "<img alt='".$langData['language_name']."' class='m-r-5' src='".$langData['language_icon_s']."'/>";
                                $language_opts .= $langData['language_name'];
                                $language_opts .= "</a></li>\n";
                            }
                        }
                        $language_opts .= "</ul>\n";
                        $language_opts .= "</li>\n";
                        $res .= $language_opts;
                    }
                }

                if (self::getMenuParam('searchbar') == TRUE) {
                    $searchbar = "<li class='nav-item dropdown' role='presentation'>";
                    $searchbar .= "<a id='ddsearch".$id."' href='#' class='nav-link dropdown-toggle pointer' role='menuitem' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' title='".fusion_get_locale('search')."'><i class='".self::getMenuParam('search_icon')."'></i></a>";
                    $searchbar .= "<ul aria-labelledby='ddsearch".$id."' class='dropdown-menu dropdown-menu-right p-l-15 p-r-15 p-t-15' role='menu' style='min-width: 300px;'>\n";
                    $searchbar .= "<li class='text-left'>";
                    $searchbar .= openform('searchform', 'post', FUSION_ROOT.BASEDIR.'search.php?stype=all',
                        [
                            'class'      => 'm-b-10',
                            'remote_url' => fusion_get_settings('site_path').'search.php'
                        ]
                    );
                    $searchbar .= form_text('stext', '', '',
                        [
                            'placeholder'        => $locale['search'],
                            'append_button'      => TRUE,
                            'append_type'        => "submit",
                            "append_form_value"  => $locale['search'],
                            "append_value"       => "<i class='".self::getMenuParam('search_icon')."'></i> ".$locale['search'],
                            "append_button_name" => "search",
                            "append_class"       => self::getMenuParam('searchbar_btn_class'),
                            'class'              => 'm-0',
                        ]
                    );
                    $searchbar .= closeform();
                    $searchbar .= "</li>\n";
                    $searchbar .= "</ul>";
                    $res .= $searchbar;
                }
                $res .= "</ul>\n";
            }

            $res .= self::getMenuParam('html_post_content');

            $res .= (self::getMenuParam('responsive')) ? "</div>\n" : "";

            $res .= self::getMenuParam('container_fluid') ? "</div>\n" : "";

            $res .= self::getMenuParam('container') ? "</div>\n" : "";

            $res .= "</div>\n";
        }

        return $res;
    }

    /**
     * @param mixed $key
     *
     * @return string
     */
    public static function getMenuParam($key = FALSE) {
        if ($key) {
            return !empty(self::$instances[self::$id]->menu_options[$key]) ? self::$instances[self::$id]->menu_options[$key] : '';
        }

        return self::$instances[self::$id]->menu_options;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public static function replaceMenuParam($key, $value) {
        self::$instances[self::$id]->menu_options[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public static function setMenuParam($key, $value) {
        self::$instances[self::$id]->menu_options[$key] = (is_bool($value)) ? $value : self::getMenuParam($key).$value;
    }

    /**
     * @param array $data
     * @param int   $link_id
     *
     * @return array
     */
    private function getSubLinksUrl($data, $link_id) {
        $linkRef = [];
        if (isset($data[$link_id])) {
            foreach ($data[$link_id] as $link) {
                $linkRef[$link['link_id']] = $link['link_url'];
                if (isset($data[$link['link_id']])) {
                    $linkRef = array_merge_recursive($linkRef, $this->getSubLinksUrl($data, $link['link_id']));
                }
            }
        }

        return $linkRef;
    }

    private static $link_instances = [];

    /**
     * @return array
     */
    private function getLinkInstance() {
        if (empty(self::$link_instances)) {
            $linkInstance = BreadCrumbs::getInstance();
            $linkInstance->showHome(FALSE);
            $linkInstance->setLastClickable();
            self::$link_instances = $linkInstance->toArray();
        }

        return self::$link_instances;
    }

    /*
     * Recursion loop of data
     */
    private function showMenuLinks($id, $data, $linkclass = 'nav-link', $dropdown = FALSE) {
        $res = '';

        if (!empty($data[$id])) {
            $i = 0;

            $default_link_data = [
                "link_id"       => 0,
                "link_name"     => "",
                "link_cat"      => 0,
                "link_url"      => "",
                "link_icon"     => "",
                "link_class"    => $linkclass,
                "link_active"   => '',
                "link_title"    => FALSE, // true to add dropdown-header class to li.
                "link_disabled" => FALSE, // true to disable link
                "link_window"   => FALSE,
            ];

            foreach ($data[$id] as $link_id => $link_data) {
                $li_class = [];
                $link_data += $default_link_data;

                if (!empty(self::getMenuParam('item_class')) && !$dropdown) {
                    $li_class[] = self::getMenuParam('item_class');
                }

                if (empty($link_data['link_url'])) {
                    $li_class[] = "no-link";
                }

                if ($link_data['link_disabled']) {
                    $li_class[] = "disabled";
                } else {
                    if ($link_data['link_title'] == TRUE) {
                        $li_class[] = "dropdown-header";
                    }
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
                            $request_uri = str_replace('//', '/', $_SERVER['REQUEST_URI']);
                            $url = parse_url(htmlspecialchars_decode($request_uri));
                            $url['path'] = !empty($url['path']) ? $url['path'] : '';
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
                        $linkRef = $this->getSubLinksUrl($data, $link_data['link_id']);
                        $linkRefURI = [];
                        if (!empty($linkRef)) {
                            $linkRefURI = array_flip($linkRef);
                        }

                        // The breadcrumb series of arrays - stable
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

                    $link_data['link_name'] = fusion_get_settings('link_bbcode') ? parseubb($link_data['link_name']) : $link_data['link_name'];
                    $link_data["link_name"] = html_entity_decode($link_data["link_name"], ENT_QUOTES);

                    $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : '');
                    $link_is_active = $link_data['link_active'];

                    if ($secondary_active) {
                        $link_is_active = TRUE;
                    } else if (strtr(FUSION_REQUEST, [fusion_get_settings('site_path') => '', '&amp;' => '&']) == str_replace('../', '', $link_data['link_url'])) {
                        $link_is_active = TRUE;
                    } else if (self::getMenuParam('start_page') == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } else if (fusion_get_settings('site_path').self::getMenuParam('start_page') == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } else if ((self::getMenuParam('start_page') == fusion_get_settings("opening_page") && $i == 0 && $id === 0)) {
                        $link_is_active = TRUE;
                    } else if ($link_data['link_url'] === '#') {
                        $link_is_active = FALSE;
                    }
                    if ($link_is_active) {
                        $li_class[] = "current-link active";
                    }
                    $itemlink = '';
                    if (!empty($link_data['link_url'])) {
                        $itemlink = " href='".BASEDIR.$link_data['link_url']."' ";
                        // if link has site protocol
                        if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])
                            or (BASEDIR !== '' && stristr($link_data['link_url'], BASEDIR))
                        ) {
                            $itemlink = " href='".$link_data['link_url']."' ";
                        }
                    }

                    $itemlink = str_replace('%aidlink%', fusion_get_aidlink(), $itemlink);

                    $has_child = FALSE;
                    $l_1 = "";
                    $l_2 = "";

                    if (isset($data[$link_id])) {
                        $has_child = TRUE;
                        $link_class = " class='".$link_data['link_class']." dropdown-toggle'";
                        $l_1 = " id='ddlink".$link_data['link_id']."' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' role='presentation'";
                        $l_1 .= (empty($id) && $has_child ? " data-submenu " : "");
                        $l_2 = (empty($id) ? "<i class='".self::getMenuParam('caret_icon')."'></i>" : "");
                        $li_class[] = (!empty($id) ? "dropdown-submenu" : "dropdown");
                    } else {
                        $link_class = (!empty($link_data['link_class']) ? " class='".$link_data['link_class']."'" : '');
                    }

                    $li_class = array_filter($li_class);

                    $res .= "<li".(!empty($li_class) ? " class='".implode(" ", $li_class)."'" : '')." role='presentation'>".self::getMenuParam('seperator');

                    $res .= ($itemlink ? "<a".$l_1.$itemlink.$link_target.$link_class." role='menuitem'>" : "");
                    $res .= (!empty($link_data['link_icon']) ? "<i class='".$link_data['link_icon']." m-r-5'></i>" : "");
                    $res .= $link_data['link_name']." ".$l_2;
                    $res .= ($itemlink ? "</a>" : '');
                    if ($has_child) {
                        $res .= "\n<ul id='menu-".$link_data['link_id']."' aria-labelledby='ddlink".$link_data['link_id']."' class='dropdown-menu'>\n";
                        if (!empty($link_data['link_url']) and $link_data['link_url'] !== "#") {
                            $res .= "<li".(!$itemlink ? " class='no-link'" : '')." role='presentation'>\n".self::getMenuParam('seperator');
                            $link_class = strtr($link_class, [
                                'nav-link'        => 'dropdown-item',
                                'dropdown-toggle' => ''
                            ]);
                            $res .= ($itemlink ? "<a ".$itemlink.$link_target.$link_class." role='menuitem'>\n" : '');
                            $res .= (!empty($link_data['link_icon']) ? "<i class='".$link_data['link_icon']." m-r-5'></i>\n" : "");
                            $res .= $link_data['link_name'];
                            $res .= ($itemlink ? "\n</a>\n" : '');
                            $res .= "</li>\n";
                        }
                        $res .= $this->showMenuLinks($link_data['link_id'], $data, 'dropdown-item', TRUE);
                        $res .= "</ul>\n";
                    }
                    $res .= "</li>\n";
                } else {
                    $res .= "<li class='divider' role='separator'></li>\n";
                }
                $i++;
            }
        }

        return $res;
    }

    /**
     * Given a matching URL, fetch Sitelinks data
     *
     * @param string $url url to match (link_url) column
     * @param string $key column data to output, blank for all
     *
     * @return array|bool
     * @deprecated use getCurrentSiteLinks()
     */
    public static function get_current_SiteLinks($url = "", $key = NULL) {
        return self::getCurrentSiteLinks($url, $key);
    }
}
