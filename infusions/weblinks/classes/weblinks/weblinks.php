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
 * @package PHPFusion\Weblinks
 */
abstract class Weblinks extends WeblinksServer {

    private static $locale = [];
    public $info = [];

    protected function __construct() {
        self::$locale = fusion_get_locale("", WEBLINK_LOCALE);
    }

    /**
     * Executes main page information
     * @return array
     */
    public function set_WeblinksInfo() {

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
            "weblink_tablename"       => self::$locale['web_0000'],
        ];

        $info = array_merge($info, self::get_WeblinkFilters());
        $info = array_merge($info, self::get_WeblinkCategories());
        $this->info = $info;

        return (array)$info;

    }

    /**
     * Outputs core filters variables
     * @return array
     */
    private function get_WeblinkFilters() {
        $array = [];

        $allowed_filters = [
            'latest' => self::$locale['web_0030'],
            'oldest' => self::$locale['web_0032'],
            'opened' => self::$locale['web_0031'],
        ];
        foreach ($allowed_filters as $type => $filter_name) {
            $filter_link = INFUSIONS."weblinks/weblinks.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : "")."type=".$type;
            $array['weblink_filter'][$type]['link'] = $filter_link;
            $array['weblink_filter'][$type]['name'] = $filter_name;
            $array['weblink_filter'][$type]['type'] = $type;
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
                    "cat_id"      => $cdata['weblink_cat_id'],
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
     * @param array $filters array('condition', 'order', 'limit')
     *
     * @return string
     */
    /**
     * Executes category information - $_GET['cat_id']
     *
     * @param $weblink_cat_id
     *
     * @return array
     */
    public function set_WeblinkCatInfo($weblink_cat_id) {

        $weblink_settings = self::get_weblink_settings();
        $info = [
            "weblink_cat_id"          => intval(0),
            "weblink_cat_name"        => self::$locale['web_0001'],
            "weblink_cat_description" => "",
            "weblink_cat_language"    => LANGUAGE,
            "weblink_categories"      => [],
            "weblink_item_rows"       => 0,
            "weblink_tablename"       => self::$locale['web_0000'],
            "weblink_items"           => []
        ];
        $info = array_merge($info, self::get_WeblinkFilters());
        $info = array_merge($info, self::get_WeblinkCategories());
        $info = array_merge($info, self::weblink_cat_navbar());

        $max_weblink_rows = '';

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

            if (infusion_exists('weblinks')) {
                if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_weblinks.php')) {
                    add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('web_0000').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_weblinks.php"/>');
                }
            }

            set_title(self::$locale['web_0000']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                "link"  => INFUSIONS."weblinks/weblinks.php",
                "title" => self::$locale['web_0000']
            ]);
            add_to_title(self::$locale['global_201'].$data['weblink_cat_name']);

            // Predefined variables, do not edit these values
            $weblink_cat_index = dbquery_tree(DB_WEBLINK_CATS, "weblink_cat_id", "weblink_cat_parent");

            // build categorial data.
            $info['weblink_cat_id'] = $data['weblink_cat_id'];
            $info['weblink_cat_name'] = $data['weblink_cat_name'];
            $info['weblink_cat_description'] = parse_textarea($data['weblink_cat_description'], TRUE, TRUE, TRUE, '', TRUE);
            $info['weblink_cat_language'] = $data['weblink_cat_language'];

            $max_weblink_rows = dbcount("(weblink_id)", DB_WEBLINKS, "weblink_cat='".$data['weblink_cat_id']."' AND ".groupaccess("weblink_visibility").(multilang_table("WL") ? " AND weblink_language='".LANGUAGE."'" : "")." AND weblink_status='1'");

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_weblink_rows ? intval($_GET['rowstart']) : 0;
            $info['pagenav'] = makepagenav($_GET['rowstart'], $weblink_settings['links_per_page'], $max_weblink_rows, 3, INFUSIONS."weblinks/weblinks.php?cat_id=".$_GET['cat_id'].(isset($_GET['type']) ? "&amp;type=".$_GET['type'] : '')."&amp;");

            $this->weblink_cat_breadcrumbs($weblink_cat_index);

            if ($max_weblink_rows) {
                $result = dbquery($this->get_WeblinkQuery(["condition" => "w.weblink_cat='".$data['weblink_cat_id']."'"]));
                $info['weblink_item_rows'] = dbrows($result);
                $info['weblink_total_rows'] = $max_weblink_rows;
            }

        } else {
            redirect(INFUSIONS."weblinks/weblinks.php");
        }

        /**
         * Parse
         */
        if ($max_weblink_rows) {
            $weblink_info = [];

            while ($data = dbarray($result)) {
                $weblink_info[$data['weblink_id']] = self::get_WeblinksData($data);
            }
            $info['weblink_items'] = $weblink_info;
        }

        $this->info = $info;
        return (array)$info;
    }

    protected static function get_WeblinkQuery(array $filters = []) {

        $weblink_settings = self::get_weblink_settings();

        return "SELECT w.*, wc.*
            FROM ".DB_WEBLINKS." w
            LEFT JOIN ".DB_WEBLINK_CATS." wc ON wc.weblink_cat_id=w.weblink_cat
            WHERE w.weblink_status='1' AND ".groupaccess("w.weblink_visibility")." AND wc.weblink_cat_status='1' AND ".groupaccess("wc.weblink_cat_visibility")."
            ".(multilang_table("WL") ? " AND w.weblink_language='".LANGUAGE."' AND wc.weblink_cat_language='".LANGUAGE."'" : "")."
            ".(!empty($filters['condition']) ? " AND ".$filters['condition'] : "")."
            GROUP BY w.weblink_id
            ORDER BY ".self::check_WeblinksFilter()."
            LIMIT ".(!empty($filters['limit']) ? $filters['limit'] : $_GET['rowstart'].",".$weblink_settings['links_per_page'])."
        ";

    }

    /**
     * Sql filter between $_GET['type']
     * latest
     * most open
     */
    private static function check_WeblinksFilter() {

        /* Filter Construct */
        $filter = ['latest', 'oldest', 'opened'];

        if (isset($_GET['type']) && in_array($_GET['type'], $filter)) {
            switch ($_GET['type']) {
                case "latest":
                    $catfilter = "w.weblink_datestamp DESC";
                    break;
                case "oldest":
                    $catfilter = "w.weblink_datestamp ASC";
                    break;
                case "opened":
                    $catfilter = "weblink_count DESC";
                    break;
                default:
                    $catfilter = "w.weblink_datestamp DESC";
            }
        } else {
            $catfilter = "w.weblink_datestamp DESC";
        }

        return (string)$catfilter;
    }

    /**
     * Parse MVC Data output
     *
     * @param array $data - dbarray of articleQuery()
     *
     * @return array
     */
    private static function get_WeblinksData(array $data) {

        if (!empty($data)) {
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
                "admin_actions"    => $adminActions
            ];

            $data['weblink_description'] = parse_textarea($data['weblink_description'], FALSE, FALSE, FALSE, '', TRUE);

            $info += $data;

            return (array)$info;
        }

        return [];
    }

    /**
     * Weblinks Category Breadcrumbs Generator
     *
     * @param $weblink_cat_index
     */
    private function weblink_cat_breadcrumbs($weblink_cat_index) {

        /* Make an infinity traverse */
        function breadcrumb_arrays($index, $id) {
            $crumb = [];
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
                    add_to_title(self::$locale['global_201'].$value);
                }
            }
        } else if (isset($crumb['title'])) {
            add_to_title(self::$locale['global_201'].$crumb['title']);
            BreadCrumbs::getInstance()->addBreadCrumb(["link" => $crumb['link'], "title" => $crumb['title']]);
        }
    }

    /**
     * Executes single article item information - $_GET['readmore']
     *
     * @param $weblink_id
     */
    public function set_WeblinkCount($weblink_id) {

        $data = dbarray(dbquery("SELECT weblink_url, weblink_visibility FROM ".DB_WEBLINKS." WHERE weblink_id=:weblinkId", [':weblinkId' => $weblink_id]));
        if (checkgroup($data['weblink_visibility'])) {
            dbquery("UPDATE ".DB_WEBLINKS." SET weblink_count=weblink_count+1 WHERE weblink_id=:weblinkId", [':weblinkId' => $weblink_id]);
            redirect($data['weblink_url']);
        } else {
            redirect(clean_request('', ['weblink_id'], FALSE));
        }
    }

    private function weblink_cat_navbar() {
        $cookie_expiry = time() + 7 * 24 * 3600;
        if (empty($_COOKIE['fusion_weblinks_view'])) {
            setcookie("fusion_weblinks_view", 1, $cookie_expiry);
        } else if (isset($_GET['switchview']) && isnum($_GET['switchview'])) {
            setcookie("fusion_weblinks_view", intval($_GET['switchview']), $cookie_expiry);
            redirect(INFUSIONS.'weblinks/weblinks.php?cat_id='.$_GET['cat_id'].(isset($_GET['type']) ? "&amp;type=".$_GET['type'] : ""));
        }

        $active = isset($_COOKIE['fusion_weblinks_view']) && isnum($_COOKIE['fusion_weblinks_view']) && $_COOKIE['fusion_weblinks_view'] == 2 ? 2 : 1;

        $inf['span'] = $active == 2 ? 12 : 4;
        $titles = ['', self::$locale['web_0040'], self::$locale['web_0041']];

        for ($i = 1; $i < 3; $i++) {
            $inf['navbar'][$i] = [
                'links'  => "<a class='btn btn-default snv".($active == $i ? ' active' : '')."' href='".INFUSIONS."weblinks/weblinks.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : "").(isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : "")."switchview=".$i."'><i class='fa fa-th-large m-r-10'></i>".$titles[$i]."</a>"
            ];
        }

        return $inf;
    }
}
