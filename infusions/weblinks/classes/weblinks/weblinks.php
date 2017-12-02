<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/classes/weblinks/weblinks.php
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
namespace PHPFusion\Weblinks;

use \PHPFusion\BreadCrumbs;

/**
 * Class Weblinks
 *
 * @package PHPFusion\Weblinks
 */
abstract class Weblinks extends WeblinksServer {

    private static $locale = [];
    public $info = [];

    protected function __construct() {
    }

    /**
     * Executes main page information
     * @return array
     */
    public function set_WeblinksInfo() {

        self::$locale = fusion_get_locale("", WEBLINK_LOCALE);

        set_title(self::$locale['web_0000']);

        BreadCrumbs::getInstance()->addBreadCrumb(
            [
                "link"  => INFUSIONS."weblinks/weblinks.php",
                "title" => self::$locale['web_0000']
            ]
        );

        $info = [
            "weblink_cat_id"          => intval(0),
            "weblink_cat_name"        => self::$locale['web_0001'],
            "weblink_cat_description" => "",
            "weblink_cat_language"    => LANGUAGE,
            "weblink_categories"      => [],
            "weblink_item_rows"       => 0,
            "weblink_last_updated"    => 0,
            "weblink_items"           => []
        ];
        $info = array_merge($info, self::get_WeblinkFilters());
        $info = array_merge($info, self::get_WeblinkCategories());
        $info = array_merge($info, self::get_WeblinkItems());
        $this->info = $info;

        return (array)$info;

    }

    /**
     * Outputs core filters variables
     * @return array
     */
    private function get_WeblinkFilters() {
        $array['allowed_filters'] = [
            "latest" => self::$locale['web_0030'],
            "opened" => self::$locale['web_0031'],
        ];
        foreach ($array['allowed_filters'] as $type => $filter_name) {
            $filter_link = INFUSIONS."weblinks/weblinks.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : "")."type=".$type;
            $array['weblink_filter'][$filter_link] = $filter_name;
            unset($filter_link);
        }

        return (array)$array;
    }

    /**
     * Outputs category variables
     * @return mixed
     */
    protected function get_WeblinkCategories() {
        $info['weblink_categories'] = [];
        $result = dbquery("
            SELECT wc.weblink_cat_id, wc.weblink_cat_name, wc.weblink_cat_description, w.weblink_status, count(w.weblink_id) 'weblink_count'
            FROM ".DB_WEBLINK_CATS." wc
            LEFT JOIN ".DB_WEBLINKS." w on w.weblink_cat = wc.weblink_cat_id AND ".groupaccess("weblink_visibility")."
            WHERE wc.weblink_cat_status='1' AND ".groupaccess("wc.weblink_cat_visibility")."
            ".(multilang_table("WL") ? " AND wc.weblink_cat_language='".LANGUAGE."'" : "")."
            GROUP BY wc.weblink_cat_id
            ORDER BY wc.weblink_cat_id ASC
        ");
        if (dbrows($result) > 0) {
            while ($cdata = dbarray($result)) {
                $info['weblink_categories'][$cdata['weblink_cat_id']] = [
                    "link"        => INFUSIONS."weblinks/weblinks.php?cat_id=".$cdata['weblink_cat_id'],
                    "name"        => $cdata['weblink_cat_name'],
                    "description" => parse_textarea($cdata['weblink_cat_description'], TRUE, TRUE, FALSE, '', TRUE),
                    "count"       => ($cdata['weblink_status'] == 1) ? $cdata['weblink_count'] : 0
                ];
            }
        }

        return (array)$info;
    }

    /**
     * Get article item
     * @param array $filter
     * @return array
     */
    public function get_WeblinkItems($filter = []) {

        $info['weblink_total_rows'] = dbcount("(weblink_id)", DB_WEBLINKS, groupaccess("weblink_visibility").(multilang_table("WL") ? " AND weblink_language='".LANGUAGE."'" : "")." AND weblink_status='1'");

        if ($info['weblink_total_rows']) {
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['weblink_total_rows'] ? intval($_GET['rowstart']) : 0;

            $result = dbquery($this->get_WeblinkQuery($filter));

            $info['weblink_item_rows'] = dbrows($result);
            if ($info['weblink_item_rows'] > 0) {
                $weblink_count = 0;
                $weblink_info = [];
                while ($data = dbarray($result)) {

                    $weblink_count++;
                    if ($weblink_count == 1) {
                        $info['weblink_last_updated'] = $data['weblink_datestamp'];
                    }

                    $weblinkData = self::get_WeblinksData($data);
                    $weblink_info[$weblink_count] = $weblinkData;

                }
                $info['weblink_items'] = $weblink_info;
            }
        }

        return (array)$info;
    }

    /**
     * @param array $filters array('condition', 'order', 'limit')
     * @return string
     */
    protected static function get_WeblinkQuery(array $filters = []) {

        $weblink_settings = self::get_weblink_settings();

        return "
            SELECT a.*, ac.*
            FROM ".DB_WEBLINKS." a
            LEFT JOIN ".DB_WEBLINK_CATS." ac ON ac.weblink_cat_id=a.weblink_cat
            WHERE a.weblink_status='1' AND ".groupaccess("a.weblink_visibility")." AND ac.weblink_cat_status='1' AND ".groupaccess("ac.weblink_cat_visibility")."
            ".(multilang_table("WL") ? " AND a.weblink_language='".LANGUAGE."' AND ac.weblink_cat_language='".LANGUAGE."'" : "")."
            ".(!empty($filters['condition']) ? " AND ".$filters['condition'] : "")."
            GROUP BY a.weblink_id
            ORDER BY ".self::check_WeblinksFilter()."
            LIMIT ".(!empty($filters['limit']) ? $filters['limit'] : "".$_GET['rowstart'].",".$weblink_settings['links_per_page']."")."
        ";

    }

    /**
     * Sql filter between $_GET['type']
     * latest
     * most open
     */
    private static function check_WeblinksFilter() {

        /* Filter Construct */
        $filter = ["latest", "opened"];

        if (isset($_GET['type']) && in_array($_GET['type'], $filter)) {
            switch ($_GET['type']) {
                case "latest":
                    $catfilter = "a.weblink_datestamp DESC";
                    break;
                case "opened":
                    $catfilter = "weblink_count DESC";
                    break;
                default:
                    $catfilter = "a.weblink_datestamp DESC";
            }
        } else {
            $catfilter = "a.weblink_datestamp DESC";
        }

        return (string)$catfilter;
    }

    /**
     * Parse MVC Data output
     * @param array $data - dbarray of articleQuery()
     * @return array
     */
    private static function get_WeblinksData(array $data) {

        self::$locale = fusion_get_locale("", WEBLINK_LOCALE);

        if (!empty($data)) {
            // Page Nav
            $articlePagenav = "";
            $pagecount = 1;

            // Admin Informations
            $adminActions = [];
            if (iADMIN && checkrights("W")) {
                $adminActions = [
                    "edit"   => [
                        "link"  => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;ref=weblinkform&amp;weblink_id=".$data['weblink_id'],
                        "title" => self::$locale['edit']
                    ],
                    "delete" => [
                        "link"  => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink()."&amp;action=delete&amp;ref=weblinkform&amp;weblink_id=".$data['weblink_id'],
                        "title" => self::$locale['delete']
                    ]
                ];
            }

            // Build Array
            $info = [
                # Links and Admin Actions
                "weblinks_url"     => INFUSIONS."weblinks/weblinks.php?weblink_id=".$data['weblink_id'],
                "weblinks_cat_url" => INFUSIONS."weblinks/weblinks.php?cat_id=".$data['weblink_cat_id'],
                "admin_actions"    => $adminActions,
                # Page Nav
                "page_count"       => $pagecount,
                "weblink_pagenav"  => $articlePagenav
            ];

            $data['weblink_description'] = parse_textarea($data['weblink_description'], FALSE, FALSE, FALSE, '', TRUE);

            $info += $data;

            return (array)$info;
        }

        return [];
    }

    /**
     * Executes category information - $_GET['cat_id']
     * @param $weblink_cat_id
     * @return array
     */
    public function set_WeblinkCatInfo($weblink_cat_id) {
        self::$locale = fusion_get_locale("", WEBLINK_LOCALE);

        $info = [
            "weblink_cat_id"          => intval(0),
            "weblink_cat_name"        => self::$locale['web_0001'],
            "weblink_cat_description" => "",
            "weblink_cat_language"    => LANGUAGE,
            "weblink_categories"      => [],
            "weblink_item_rows"       => 0,
            "weblink_last_updated"    => 0,
            "weblink_items"           => []
        ];
        $info = array_merge($info, self::get_WeblinkFilters());
        $info = array_merge($info, self::get_WeblinkCategories());

        // Filtered by Category ID.
        $result = dbquery("
            SELECT *
            FROM ".DB_WEBLINK_CATS."
            WHERE weblink_cat_id='".intval($weblink_cat_id)."' AND weblink_cat_status='1' AND ".groupaccess("weblink_cat_visibility")."
            ".(multilang_table("WL") ? " AND weblink_cat_language='".LANGUAGE."'" : "")."
            LIMIT 0,1
        ");

        if (dbrows($result) > 0) {
            $data = dbarray($result);

            if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_weblinks.php')) {
                add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('web_0000').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_weblinks.php"/>');
            }

            set_title(self::$locale['web_0000']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                "link"  => INFUSIONS."weblinks/weblinks.php",
                "title" => self::$locale['web_0000']
            ]);

            // Predefined variables, do not edit these values
            $weblink_cat_index = dbquery_tree(DB_WEBLINK_CATS, "weblink_cat_id", "weblink_cat_parent");

            // build categorial data.
            $info['weblink_cat_id'] = $data['weblink_cat_id'];
            $info['weblink_cat_name'] = $data['weblink_cat_name'];
            $info['weblink_cat_description'] = parse_textarea($data['weblink_cat_description'], TRUE, TRUE, TRUE, '', TRUE);
            $info['weblink_cat_language'] = $data['weblink_cat_language'];

            $max_weblink_rows = dbcount("(weblink_id)", DB_WEBLINKS, "weblink_cat='".$data['weblink_cat_id']."' AND ".groupaccess("weblink_visibility").(multilang_table("WL") ? " AND weblink_language='".LANGUAGE."'" : "")." AND weblink_status='1'");

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_weblink_rows ? intval($_GET['rowstart']) : 0;

            if ($max_weblink_rows) {
                $result = dbquery($this->get_WeblinkQuery(["condition" => "a.weblink_cat='".$data['weblink_cat_id']."'"]));
                $info['weblink_item_rows'] = dbrows($result);
                $info['weblink_total_rows'] = $max_weblink_rows;
                $this->weblink_cat_breadcrumbs($weblink_cat_index);
            }

        } else {
            redirect(INFUSIONS."weblinks/weblinks.php");
        }

        /**
         * Parse
         */
        if ($max_weblink_rows) {
            $weblink_count = 0;
            $weblink_info = [];

            while ($data = dbarray($result)) {
                $weblink_count++;
                if ($weblink_count == 1) {
                    $info['weblink_last_updated'] = $data['weblink_datestamp'];
                }
                $weblink_info[$weblink_count] = self::get_WeblinksData($data);
            }
            $info['weblink_items'] = $weblink_info;
        }

        $this->info = $info;
        return (array)$info;
    }

    /**
     * Weblinks Category Breadcrumbs Generator
     *
     * @param $weblink_cat_index
     */
    private function weblink_cat_breadcrumbs($weblink_cat_index) {

        $locale = fusion_get_locale("", WEBLINK_LOCALE);

        /* Make an infinity traverse */
        function breadcrumb_arrays($index, $id) {
            $crumb = &$crumb;
            if (isset($index[get_parent($index, $id)])) {
                $_name = dbarray(dbquery("SELECT weblink_cat_id, weblink_cat_name, weblink_cat_parent FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_id='".$id."' AND weblink_cat_status='1' AND ".groupaccess("weblink_cat_visibility").(multilang_table("WL") ? " AND weblink_cat_language='".LANGUAGE."'" : "").""));
                $crumb = [
                    "link"  => INFUSIONS."weblinks/weblinks.php?cat_id=".$_name['weblink_cat_id'],
                    "title" => $_name['weblink_cat_name']
                ];
                if (isset($index[get_parent($index, $id)])) {
                    if (get_parent($index, $id) == 0) {
                        return $crumb;
                    }
                    $crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
                    $crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
                }
            }

            return $crumb;
        }

        // then we make a infinity recursive function to loop/break it out.
        $crumb = breadcrumb_arrays($weblink_cat_index, $_GET['cat_id']);
        // then we sort in reverse.
        if (count($crumb['title']) > 1) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if (count($crumb['title']) > 1) {
            foreach ($crumb['title'] as $i => $value) {
                BreadCrumbs::getInstance()->addBreadCrumb(["link" => $crumb['link'][$i], "title" => $value]);
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                }
            }
        } else if (isset($crumb['title'])) {
            add_to_title($locale['global_201'].$crumb['title']);
            BreadCrumbs::getInstance()->addBreadCrumb(["link" => $crumb['link'], "title" => $crumb['title']]);
        }
    }

    /**
     * Executes single article item information - $_GET['readmore']
     * @param $weblink_id
     */
    public function set_WeblinkCount($weblink_id) {

        $data = dbarray(dbquery("SELECT weblink_url, weblink_cat, weblink_visibility FROM ".DB_WEBLINKS." WHERE weblink_id='".intval($weblink_id)."'"));
        if (checkgroup($data['weblink_visibility'])) {
            dbquery("UPDATE ".DB_WEBLINKS." SET weblink_count=weblink_count+1 WHERE weblink_id='".intval($weblink_id)."'");
            redirect($data['weblink_url']);
        } else {
            redirect(FUSION_SELF);
        }
    }

    protected function __clone() {
    }
}
