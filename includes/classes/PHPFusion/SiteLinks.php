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
     * @param array $options
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
    private static $link_instances = [];
    private $menu_options;

    /**
     * Get Site Links Position Options
     *
     * @return array
     */
    public static function getSiteLinksPosition() {
        $locale = fusion_get_locale( '', LOCALE . LOCALESET . "admin/sitelinks.php" );
        if (empty( self::$position_opts )) {
            self::$position_opts = [
                '1' => $locale['SL_0025'], // only css navigational panel
                '2' => $locale['SL_0026'], // both
                '3' => $locale['SL_0027'], // subheader
                '4' => $locale['custom'] . " ID",
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
    public static function getSiteLinks( $id ) {
        $data = [];
        $link_query = "SELECT * FROM " . DB_SITE_LINKS . " " . (multilang_table( "SL" ) ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_id='$id'";
        $result = dbquery( $link_query );
        if (dbrows( $result ) > 0) {
            $data = dbarray( $result );
        }

        return $data;
    }

    /**
     * Link ID validation
     *
     * @param int $link_id
     *
     * @return int|null
     */
    public static function verifySiteLink( $link_id ) {
        if (isnum( $link_id )) {
            return dbcount( "(link_id)", DB_SITE_LINKS, "link_id='" . intval( $link_id ) . "'" );
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
    public static function deleteSiteLink( $link_id ) {
        if (isnum( $link_id )) {
            $data = dbarray( dbquery( "SELECT link_order FROM " . DB_SITE_LINKS . " " . (multilang_table( "SL" ) ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_id='" . $_GET['link_id'] . "'" ) );
            $result = dbquery( "UPDATE " . DB_SITE_LINKS . " SET link_order=link_order-1 " . (multilang_table( "SL" ) ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_order>'" . $data['link_order'] . "'" );
            if ($result) {
                $result = dbquery( "DELETE FROM " . DB_SITE_LINKS . " WHERE link_id='" . $_GET['link_id'] . "'" );
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
    public static function setSubLinks( array $options = [] ) {
        /*
         * If set an ID, it will re-run the class to create a new object again.
         */
        $default_options = [
            'id'                   => self::MENU_DEFAULT_ID,
            'container'            => FALSE,
            'container_fluid'      => FALSE,
            'responsive'           => TRUE,
            'navbar_class'         => defined( 'BOOTSTRAP4' ) ? 'navbar-expand-lg navbar-light' : 'navbar-default',
            'nav_class'            => defined( 'BOOTSTRAP4' ) ? 'navbar-nav ml-auto primary' : '',
            'additional_nav_class' => '',
            'item_class'           => defined( 'BOOTSTRAP4' ) ? 'nav-item' : '', // $class
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
            'caret_icon'           => defined( 'BOOTSTRAP4' ) ? '' : 'caret',
            'link_position'        => [2, 3],
            'html_pre_content'     => '',
            'html_content'         => '',
            'html_post_content'    => ''
        ];

        $options += $default_options;

        if (!isset( self::$instances[$options['id']]->menu_options )) {

            $options['locale'] += fusion_get_locale();

            if (!$options['links_per_page']) {
                $options['links_per_page'] = fusion_get_settings( 'links_per_page' );
            }

            if (empty( $options['grouping'] )) {
                $options['grouping'] = fusion_get_settings( 'links_grouping' );
            }

            if (!isset( $options['callback_data'] ) && empty( $options['callback_data'] )) {
                $options['callback_data'] = self::getSiteLinksData( ['link_position' => $options['link_position']] );
            }

            $options['banner'] = fusion_get_settings( 'sitebanner' ) && $options['show_banner'] == TRUE ? "<img src='" . BASEDIR . fusion_get_settings( "sitebanner" ) . "' alt='" . fusion_get_settings( "sitename" ) . "'/>" : fusion_get_settings( "sitename" );

            $pageInfo = pathinfo( $_SERVER['REQUEST_URI'] );
            $start_page = $pageInfo['dirname'] !== "/" ? ltrim( $pageInfo['dirname'], "/" ) . "/" : "";
            $site_path = ltrim( fusion_get_settings( "site_path" ), "/" );
            $start_page = str_replace( [$site_path, '\/'], ['', ''], $start_page );
            $start_page .= $pageInfo['basename'];

            if (fusion_get_settings( "site_seo" ) && defined( 'IN_PERMALINK' ) && !check_get( 'aid' )) {
                $filepath = Router::getRouterInstance()->getFilePath();
                $start_page = $filepath;
            }

            $options['start_page'] = $start_page;

            self::$instances[$options['id']] = self::getInstance( $options['id'] );

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
    public static function getSiteLinksData( array $options = [] ) {
        $default_position = [2, 3];

        /*
         * $options['link_position'] - accepts either string or array
         */
        $link_position = '';
        if (!empty( $options['link_position'] )) {
            $link_position = $options['link_position'];
            if (is_array( $link_position )) {
                $link_position = implode( ' OR sl.link_position=', $link_position );
            }
        }

        $default_link_filter = [
            'join'               => '',
            'position_condition' => '(sl.link_position=' . (!empty( $link_position ) ? $link_position : implode( ' OR sl.link_position=', $default_position )) . ')',
            'condition'          => (multilang_table( "SL" ) ? " AND link_language='" . LANGUAGE . "'" : "") . " AND " . groupaccess( 'link_visibility' ) . " AND link_status=1",
            'group'              => '',
            'order'              => "link_cat ASC, link_order ASC",
        ];
        $options += $default_link_filter;

        $query_replace = "";
        if (!empty( $options )) {
            $query_replace = "SELECT sl.* " . (!empty( $options['select'] ) ? ", " . $options['select'] : '') . " 
            FROM " . DB_SITE_LINKS . " sl
            " . $options['join'] . "
            WHERE " . $options['position_condition'] . $options['condition'] . "
            " . (!empty( $options['group'] ) ? " GROUP BY " . $options['group'] . " " : "") . " ORDER BY " . $options['order'];
        }

        return dbquery_tree_full( DB_SITE_LINKS, "link_id", "link_cat", "", $query_replace );
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public static function getInstance( $id = self::MENU_DEFAULT_ID ) {
        self::$id = $id;
        if (isset( self::$instances[$id] )) {
            return self::$instances[$id];
        } else {
            return self::$instances[$id] = new static();
        }
    }

    /**
     * Add a link to primary menu
     *
     * @param int $link_id
     * @param string $link_name
     * @param int $link_cat
     * @param string $link_url
     * @param string $link_icon
     * @param bool|FALSE $link_active
     * @param bool|FALSE $link_title
     * @param bool|FALSE $link_disabled
     * @param bool|FALSE $link_window
     * @param string $link_class
     */
    public static function addMenuLink( $link_id, $link_name, $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '' ) {
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
     * @param int $link_id
     * @param string $link_name
     * @param int $link_cat
     * @param string $link_url
     * @param string $link_icon
     * @param bool|FALSE $link_active
     * @param bool|FALSE $link_title
     * @param bool|FALSE $link_disabled
     * @param bool|FALSE $link_window
     * @param string $link_class
     */
    public static function addOptionalMenuLink( $link_id, $link_name, $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '' ) {
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
     * @param string $key
     * @param mixed $value
     */
    public static function setMenuParam( $key, $value ) {
        self::$instances[self::$id]->menu_options[$key] = (is_bool( $value )) ? $value : self::getMenuParam( $key ) . $value;
    }

    /**
     * @param mixed $key
     *
     * @return string
     */
    public static function getMenuParam( $key = FALSE ) {
        if ($key) {
            return !empty( self::$instances[self::$id]->menu_options[$key] ) ? self::$instances[self::$id]->menu_options[$key] : '';
        }

        return self::$instances[self::$id]->menu_options;
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
    public static function get_current_SiteLinks( $url = "", $key = NULL ) {
        return self::getCurrentSiteLinks( $url, $key );
    }

    /**
     * Given a matching URL, fetch Sitelinks data
     *
     * @param string $url url to match (link_url) column
     * @param string $key column data to output, blank for all
     *
     * @return array|bool
     */
    public static function getCurrentSiteLinks( $url = "", $key = NULL ) {
        $url = stripinput( $url );
        static $data = [];
        if (empty( $data )) {
            if (!$url) {
                $url = FUSION_FILELINK;
            }
            $result = dbquery( "SELECT * FROM " . DB_SITE_LINKS . " WHERE link_url='" . $url . "' AND link_language='" . LANGUAGE . "'" );
            if (dbrows( $result ) > 0) {
                $data = dbarray( $result );
            }
        }

        return $key === NULL ? $data : (isset( $data[$key] ) ? $data[$key] : NULL);
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function showSubLinks( int $id = 0 ) {
        $locale = (array)self::getMenuParam( 'locale' );
        $res = '';

        if (empty( $id )) {

            self::setLinks();

            $info = [
                'id'                     => self::getMenuParam( 'id' ),
                'container'              => self::getMenuParam( 'container' ),
                'container_fluid'        => self::getMenuParam( 'container_fluid' ),
                'show_header'            => self::getMenuParam( 'show_header' ),
                'responsive'             => self::getMenuParam( 'responsive' ),
                'show_banner'            => self::getMenuParam( 'show_banner' ),
                'custom_header'          => self::getMenuParam( 'custom_header' ),
                'nav_class'              => self::getMenuParam( 'nav_class' ),
                'navbar_class'           => self::getMenuParam( 'navbar_class' ),
                'html_pre_content'       => self::getMenuParam( 'html_pre_content' ),
                'html_content'           => self::getMenuParam( 'html_content' ),
                'additional_nav_class'   => self::getMenuParam( 'additional_nav_class' ),
                'language_switcher'      => self::getMenuParam( 'language_switcher' ),
                'searchbar'              => self::getMenuParam( 'searchbar' ),
                'html_post_content'      => self::getMenuParam( 'html_post_content' ),
                'callback_data'          => self::getMenuParam( 'callback_data' ),
                'additional_data'        => self::getMenuParam( 'additional_data' ),
                'navbar_link'            => BASEDIR . fusion_get_settings( 'opening_page' ),
                'search_input'           => form_text( 'stext', '', '',
                    [
                        'placeholder'        => $locale['search'],
                        'append_button'      => TRUE,
                        'append_type'        => "submit",
                        "append_form_value"  => $locale['search'],
                        "append_value"       => get_icon( 'search' ) . " " . $locale['search'],
                        "append_button_name" => "search",
                        "append_class"       => self::getMenuParam( 'searchbar_btn_class' ),
                        'class'              => 'm-0',
                    ]
                ),
                'search_uri'             => FUSION_ROOT . BASEDIR . 'search.php?stype=all',
                'primary_callback_nav'   => $this->showMenuLinks( $id, self::getMenuParam( 'callback_data' ) ),
                'secondary_callback_nav' => $this->showMenuLinks( $id, self::getMenuParam( 'additional_data' ) )
            ];

            return fusion_get_template( 'showsublinks', $info );
        }

        return $res;
    }

    /**
     * Init
     */
    private static function setLinks() {
        $primary_cache = (isset( self::$primary_cache_data[self::$id] )) ? self::$primary_cache_data[self::$id] : [];

        $secondary_cache = (isset( self::$optional_cache_data[self::$id] )) ? self::$optional_cache_data[self::$id] : [];
        if (!empty( self::getMenuParam( 'callback_data' ) ) && is_array( self::getMenuParam( 'callback_data' ) )) {
            if (isset( $primary_cache )) {

                self::replaceMenuParam( 'callback_data', array_replace_recursive( (array)self::getMenuParam( 'callback_data' ), $primary_cache ) );
            }
        } else {
            self::replaceMenuParam( 'callback_data', $primary_cache );
        }

        if (!empty( self::getMenuParam( 'additional_data' ) && is_array( self::getMenuParam( 'additional_data' ) ) )) {
            if (isset( $secondary_cache )) {
                self::replaceMenuParam( 'additional_data', array_replace_recursive( (array)self::getMenuParam( 'additional_data' ), $secondary_cache ) );
            }
        } else {
            self::replaceMenuParam( 'additional_data', $secondary_cache );
        }

        // Change hierarchy data when grouping is activated
        if (self::getMenuParam( 'grouping' )) {

            $callback_data = (array)self::getMenuParam( 'callback_data' );

            if (!empty( $callback_data[0] )) {

                if (count( $callback_data[0] ) > self::getMenuParam( 'links_per_page' )) {

                    $more_index = 9 * 10000000;
                    $base_data = $callback_data[0];
                    $data[$more_index] = array_slice( $base_data, self::getMenuParam( 'links_per_page' ), 9, TRUE );

                    $data[0] = array_slice( $base_data, 0, self::getMenuParam( 'links_per_page' ), TRUE );
                    $more[$more_index] = [
                        "link_id"         => $more_index,
                        "link_cat"        => 0,
                        "link_name"       => fusion_get_locale( 'global_700' ),
                        "link_url"        => "#",
                        "link_icon"       => "",
                        "link_visibility" => 0,
                        "link_position"   => 2,
                        "link_window"     => 0,
                        "link_order"      => self::getMenuParam( 'links_per_page' ),
                        "link_language"   => LANGUAGE
                    ];
                    $data[0] = $data[0] + $more;
                    $data = $data + $callback_data;
                    self::replaceMenuParam( 'callback_data', $data );
                }
            }
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function replaceMenuParam( $key, $value ) {
        self::$instances[self::$id]->menu_options[$key] = $value;
    }

    /**
     * I need a copy of this in template - let's copy it out.
     * @param $id
     * @param $data
     * @param string $linkclass
     * @param false $dropdown
     *
     * @return array
     */
    private function showMenuLinks( $id, $data, $linkclass = 'nav-link', $dropdown = FALSE ) {
        $res = '';

        if (!empty( $data[$id] )) {
            $i = 0;

            $default_link_data = [
                "link_id"         => 0,
                "link_name"       => "",
                "link_cat"        => 0,
                "link_url"        => "",
                "link_icon"       => "",
                "link_class"      => $linkclass,
                "link_item_class" => "",
                "link_active"     => '',
                "link_title"      => FALSE, // true to add dropdown-header class to li.
                "link_disabled"   => FALSE, // true to disable link
                "link_window"     => FALSE,
            ];

            foreach ($data[$id] as $link_id => $link_data) {

                $link_data += $default_link_data;

                $li_class = [];

                if (!empty( $link_data['link_item_class'] )) {
                    $li_class[] = $link_data['link_item_class'];
                }

                if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {

                    if (!empty( self::getMenuParam( 'item_class' ) ) && !$dropdown) {
                        $li_class[] = self::getMenuParam( 'item_class' );
                    }

                    if (empty( $link_data['link_url'] )) {
                        $li_class[] = "no-link";
                    }

                    if ($link_data['link_disabled']) {
                        $li_class[] = "disabled";
                    } else {
                        if ($link_data['link_title'] == TRUE) {
                            $li_class[] = "dropdown-header"; // this is bootstrap
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
                    if (!is_bool( $link_data['link_active'] )) {
                        // If the current link_url does not contain request parameters, this link should be active
                        if (!stristr( $link_data['link_url'], "?" )) {
                            if (defined( 'IN_PERMALINK' )) {
                                if (Router::getRouterInstance()->getFilePath() == $link_data['link_url']) {
                                    $secondary_active = TRUE;
                                }
                            } else {
                                // format the link
                                $data_link_url = $link_data['link_url'];
                                if (stristr( $link_data['link_url'], "index.php" )) {
                                    $data_link_url = str_replace( "index.php", "", $data_link_url );
                                }
                                $request_uri = str_replace( '//', '/', $_SERVER['REQUEST_URI'] );
                                $url = parse_url( htmlspecialchars_decode( $request_uri ) );
                                $url['path'] = !empty( $url['path'] ) ? $url['path'] : '';
                                $current_url = str_replace( fusion_get_settings( 'site_path' ), "", $url['path'] );
                                if (stristr( $url['path'], "index.php" )) {
                                    $current_url = str_replace( "index.php", "", $current_url );
                                }
                                if ($data_link_url == $current_url) {
                                    $secondary_active = TRUE;
                                }
                            }
                        }

                        // not the first link
                        if (self::getMenuParam( 'start_page' ) !== $link_data['link_url']) {
                            // All Sublinks will be compared to - stable
                            $linkRef = $this->getSubLinksUrl( $data, $link_data['link_id'] );
                            $linkRefURI = [];
                            if (!empty( $linkRef )) {
                                $linkRefURI = array_flip( $linkRef );
                            }

                            // The breadcrumb series of arrays - stable
                            $reference = $this->getLinkInstance();
                            if (!empty( $reference )) {

                                $uri = parse_url( htmlspecialchars_decode( $link_data['link_url'] ) );
                                $uriQuery = [];
                                if (!empty( $uri['query'] )) {
                                    parse_str( $uri['query'], $uriQuery );
                                }
                                foreach ($reference as $refData) {
                                    if (stristr( $refData['link'], '../' )) {
                                        $refData['link'] = str_replace( str_repeat( '../', substr_count( $refData['link'], '../' ) ), '', $refData['link'] );
                                    }
                                    if (!empty( $refData['link'] ) && $link_data['link_url'] !== "index.php") {
                                        //If child link is part of the current page breadcrumb then parent is active
                                        if (!empty( $refData['link'] )) {
                                            if (isset( $linkRefURI[$refData['link']] )) {
                                                $secondary_active = TRUE;
                                                break;
                                            }
                                        }
                                        // If parts of link url forms the breadcrumbs' link
                                        if (!empty( $link_data['link_url'] ) && stristr( $refData['link'], $link_data['link_url'] )) {
                                            $secondary_active = TRUE;
                                            break;
                                        }
                                        // If both links has the same uri requests string.
                                        if (!empty( $link_data['link_url'] ) && stristr( $link_data['link_url'], '?' )) {
                                            $ref_uri = parse_url( htmlspecialchars_decode( $refData['link'] ) );
                                            if (!empty( $uri['query'] ) && !empty( $ref_uri['query'] )) {
                                                parse_str( $ref_uri['query'], $ref_uriQuery );
                                                if (count( $ref_uriQuery ) == count( $uriQuery )) {
                                                    $diff = array_diff_assoc( $uriQuery, $ref_uriQuery );
                                                    $diff_2 = array_diff_assoc( $ref_uriQuery, $uriQuery );
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


                    $link_data['link_name'] = fusion_get_settings( 'link_bbcode' ) ? parseubb( $link_data['link_name'] ) : $link_data['link_name'];
                    $link_data["link_name"] = html_entity_decode( $link_data["link_name"], ENT_QUOTES );

                    $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : '');
                    $link_is_active = $link_data['link_active'];

                    if ($secondary_active) {
                        $link_is_active = TRUE;
                    } else if (strtr( FUSION_REQUEST, [fusion_get_settings( 'site_path' ) => '', '&amp;' => '&'] ) == str_replace( '../', '', $link_data['link_url'] )) {
                        $link_is_active = TRUE;
                    } else if (self::getMenuParam( 'start_page' ) == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } else if (fusion_get_settings( 'site_path' ) . self::getMenuParam( 'start_page' ) == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } else if ((self::getMenuParam( 'start_page' ) == fusion_get_settings( "opening_page" ) && $i == 0 && $id === 0)) {
                        $link_is_active = TRUE;
                    } else if ($link_data['link_url'] === '#') {
                        $link_is_active = FALSE;
                    }
                    if ($link_is_active) {
                        $li_class[] = "current-link active";
                    }

                    $itemlink = '';
                    if (!empty( $link_data['link_url'] )) {
                        $itemlink = " href='" . BASEDIR . $link_data['link_url'] . "' ";
                        // if link has site protocol
                        if (preg_match( "!^(ht|f)tp(s)?://!i", $link_data['link_url'] )
                            or (BASEDIR !== '' && stristr( $link_data['link_url'], BASEDIR ))
                        ) {
                            $itemlink = " href='" . $link_data['link_url'] . "' ";
                        }
                    }
                    $itemlink = str_replace( '%aidlink%', fusion_get_aidlink(), $itemlink );
                    $cloned_link = $itemlink;

                    $has_child = FALSE;
                    $l_1 = "";
                    $l_2 = "";

                    $link_class = (!empty( $link_data['link_class'] ) ? " class='" . $link_data['link_class'] . "'" : '');
                    if (isset( $data[$link_id] )) {
                        $has_child = TRUE;
                        $link_class = " class='" . $link_data['link_class'] . " dropdown-toggle'"; // has bootstrap elements
                        // has bootstrap elements
                        $l_1 = " id='ddlink" . $link_data['link_id'] . "' data-toggle='dropdown' data-bs-toggle='dropdown' data-bs-auto-close='outside' aria-haspopup='true' aria-expanded='false' role='presentation'";
                        // has bootstrap elements
                        $l_1 .= (empty( $id ) && $has_child ? " data-submenu " : "");

                        $l_2 = (empty( $id ) ? "<i class='" . self::getMenuParam( 'caret_icon' ) . "'></i>" : get_icon( 'caret-down' ));
                        // has bootstrap elements
                        $li_class[] = (!empty( $id ) ? "dropdown-submenu" : "dropdown");
                        $itemlink = " href='#'";
                    }

                    $li_class = array_filter( $li_class );

                    $rows[$id][$link_data['link_id']] = [
                        "li_class"         => implode( ' ', $li_class ),
                        "li_separator"     => self::getMenuParam( 'separator' ),
                        "link_attr"        => $itemlink ? $l_1 . $itemlink . $link_target . $link_class : '',
//                        "link_cloned_attr" => isset( $cloned_link ) ? $l_1 . $itemlink . $cloned_link . $link_class : '',
                        "link_id"          => $link_data['link_id'],
                        "link_name"        => $link_data['link_name'],
                        'link_content'     => $link_data['link_content'] ?? '',
                        "link_href"        => $itemlink,
                        "link_url"         => $link_data['link_url'],
                        "link_icon"        => !empty( $link_data['link_icon'] ) ? '<i class="' . $link_data['link_icon'] . ' m-r-5"></i>' : '',
                        "link_caret"       => $l_2,
                        "link_child"       => $has_child,
                        "link_child_attr"  => $itemlink && $has_child ? strtr( $itemlink . $link_target . $link_class, [
                            'nav-link'        => 'dropdown-item',
                            'dropdown-toggle' => '',
                            $itemlink         => $cloned_link,
                        ] ) : '',
                    ];

                    if ($has_child) {
                        $rows += $this->showMenuLinks( $link_data['link_id'], $data, $linkclass, TRUE );
                    }

                } else {
                    $rows[$id][$link_data['link_id']] = [
                        'separator' => TRUE
                    ];

                }
                $i++;
            }
        }

        return $rows ?? [];
    }

    /*
     * Recursion loop of data
     */

    /**
     * @param array $data
     * @param int $link_id
     *
     * @return array
     */
    private function getSubLinksUrl( $data, $link_id ) {
        $linkRef = [];
        if (isset( $data[$link_id] )) {
            foreach ($data[$link_id] as $link) {
                if (isset($link['link_id']) && isset($link['link_url'])) {
                    $linkRef[$link['link_id']] = $link['link_url'];
                }
                if (isset( $data[$link['link_id']] )) {
                    $linkRef = array_merge_recursive( $linkRef, $this->getSubLinksUrl( $data, $link['link_id'] ) );
                }
            }
        }

        return $linkRef;
    }

    /**
     * @return array
     */
    private function getLinkInstance() {
        if (empty( self::$link_instances )) {
            $linkInstance = BreadCrumbs::getInstance();
            $linkInstance->showHome( FALSE );
            $linkInstance->setLastClickable();
            self::$link_instances = $linkInstance->toArray();
        }

        return self::$link_instances;
    }
}