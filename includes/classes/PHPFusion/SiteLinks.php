<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
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

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

class SiteLinks {

    private static $position_opts = array();

    /**
     * Get Site Links Position Options
     * @return array
     */
    public static function get_SiteLinksPosition() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/sitelinks.php");
        if (empty(self::$position_opts)) {
            self::$position_opts = array(
                '1' => $locale['SL_0025'], // only css navigational panel
                '2' => $locale['SL_0026'], // both
                '3' => $locale['SL_0027'], // subheader
                '4' => $locale['custom']." ID",
            );
        }

        return (array)self::$position_opts;
    }

    /**
     * Get Sitelinks SQL Row
     * @param $id
     * @return array|bool
     */
    public static function get_SiteLinks($id) {
        $data = array();
        $link_query = "SELECT * FROM ".DB_SITE_LINKS." WHERE link_id=$id";
        $result = dbquery($link_query);
        if (dbrows($result) > 0) {
            $data = dbarray($result);
        }

        return $data;
    }

    /**
     * Given a matching URL, fetch Sitelinks data
     * @param string $url - url to match (link_url) column
     * @param string $key - column data to output, blank for all
     * @return array|bool
     */
    public static function get_current_SiteLinks($url = "", $key = NULL) {
        $url = stripinput($url);
        static $data = array();
        if (empty($data)) {
            if (!$url) {
                $pathinfo = pathinfo($_SERVER['PHP_SELF']);
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
     * Site Link Loader
     * @param $link_id
     * @return array
     */
    public static function load_sitelinks($link_id) {
        $array = array();
        if (isnum($link_id)) {
            $result = dbquery("SELECT * FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'");
            if (dbrows($result)) {
                return (array)dbarray($result);
            }
        }

        return (array)$array;
    }

    /**
     * Link ID validation
     * @param $link_id
     * @return bool|string
     */
    public static function verify_sitelinks($link_id) {
        if (isnum($link_id)) {
            return dbcount("(link_id)", DB_SITE_LINKS, "link_id='".intval($link_id)."'");
        }

        return FALSE;
    }

    /**
     * SQL Delete Site Link Action
     * @param $link_id
     * @return bool|mixed|null|PDOStatement|resource
     */
    public static function delete_sitelinks($link_id) {
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
     * @return array
     */
    public static function get_LinkVisibility() {
        static $visibility_opts = array();
        $user_groups = getusergroups();
        while (list($key, $user_group) = each($user_groups)) {
            $visibility_opts[$user_group['0']] = $user_group['1'];
        }

        return (array)$visibility_opts;
    }

    /**
     * Fetches Site Links Hierarchy Data - for a less support complexity
     * @param array $options
     * - join
     * - link_position (array)
     * - condition
     * - group
     * - order
     * @return array
     */
    public static function get_SiteLinksData(array $options = array()) {

        $default_position = array(2, 3);

        $default_link_filter = array(
            'join' => '',
            'position_condition' => '(sl.link_position='.($options['link_position'] ? implode(' OR sl.link_position=',
                                                                                              $options['link_position']) : implode(' OR sl.link_position=',
                                                                                                                                   $default_position)).')',
            'condition' => (multilang_table("SL") ? " AND link_language='".LANGUAGE."'" : "")." AND ".groupaccess('link_visibility'),
            'group' => '',
            'order' => "link_cat ASC, link_order ASC",
        );
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

}