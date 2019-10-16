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

use PHPFusion\BreadCrumbs;

/**
 * Class Weblinks
 *
 * @package PHPFusion\Weblinks
 */
abstract class Weblinks extends WeblinksServer {
    private static $locale = [];
    public $cat_id;
    private $allowed_filters = ['latest', 'oldest', 'opened'];
    private $weblink_settings;
    private $type;
    private $rowstart;

    protected function __construct() {
        self::$locale = fusion_get_locale("", WEBLINK_LOCALE);
        $this->weblink_settings = self::get_weblink_settings();
        $this->type = filter_input(INPUT_GET, 'type', FILTER_DEFAULT);
        $this->rowstart = filter_input(INPUT_GET, 'rowstart', FILTER_VALIDATE_INT);
        $this->cat_id = filter_input(INPUT_GET, 'cat_id', FILTER_VALIDATE_INT);
        $this->rowstart = !empty($this->rowstart) ? $this->rowstart : 0;
    }

    /**
     * Executes main page information
     *
     * @return array
     */
    public function set_WeblinksInfo() {

        set_title(self::$locale['web_0000']);

        BreadCrumbs::getInstance()->addBreadCrumb(
            [
                'link'  => INFUSIONS."weblinks/weblinks.php",
                'title' => self::$locale['web_0000']
            ]
        );

        $this->def_cat['weblink_tablename'] = self::$locale['web_0000'];
        $this->def_cat['weblink_filter'] += self::get_WeblinkFilters();
        $this->def_cat['weblink_categories'] += self::get_WeblinkCategories();

        return (array)$this->def_cat;

    }

    /**
     * Outputs core filters variables
     *
     * @return array
     */
    private function get_WeblinkFilters() {
        $info = [];

        $filters = [self::$locale['web_0030'], self::$locale['web_0032'], self::$locale['web_0031']];

        $wdi = 0;
        foreach ($this->allowed_filters as $type_id => $type) {
            $filter_link = INFUSIONS."weblinks/weblinks.php?".(!empty($this->cat_id) ? "cat_id=".$this->cat_id."&amp;" : "")."type=".$type;
            $info[$type] = [
                'link'   => $filter_link,
                'name'   => $filters[$type_id],
                'type'   => $type,
                'active' => ((empty($this->type) && (!$wdi)) || (!empty($this->type) && $this->type === $type) ? "text-dark strong" : '')
            ];
            unset($filter_link);
            $wdi++;
        }

        return (array)$info;
    }

    /**
     * Outputs category variables
     *
     * @return mixed
     */
    protected function get_WeblinkCategories() {
        $info = [];
        $result = dbquery("
            SELECT wc.weblink_cat_id, wc.weblink_cat_name, wc.weblink_cat_parent, wc.weblink_cat_description, w.weblink_status, count(w.weblink_id) 'weblink_count'
            FROM ".DB_WEBLINK_CATS." AS wc
            LEFT JOIN ".DB_WEBLINKS." AS w ON w.weblink_cat = wc.weblink_cat_id AND ".groupaccess("weblink_visibility").(multilang_table("WL") ? " AND ".in_group('w.weblink_language', LANGUAGE) : "")."
            WHERE wc.weblink_cat_status='1' AND ".groupaccess("wc.weblink_cat_visibility")."
            ".(multilang_table("WL") ? " AND ".in_group('wc.weblink_cat_language', LANGUAGE) : "")."
            GROUP BY wc.weblink_cat_id
            ORDER BY wc.weblink_cat_id ASC
        ");

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $info[$data['weblink_cat_parent']][$data['weblink_cat_id']] = $data;
            }
        }

        return (array)$info;
    }

    /**
     * Executes category information - $_GET['cat_id']
     *
     * @param $weblink_cat_id
     *
     * @return array
     */
    public function set_WeblinkCatInfo($weblink_cat_id) {
        $linktype = (!empty($this->type) ? "&amp;type=".$this->type : '');

        $this->def_data['weblink_tablename'] = self::$locale['web_0000'];
        $this->def_data['weblink_filter'] += self::get_WeblinkFilters();
        $this->def_data['weblink_categories'] += self::get_WeblinkCategories();

        // Filtered by Category ID.
        $result = dbquery("SELECT *
            FROM ".DB_WEBLINK_CATS."
            WHERE weblink_cat_id = :catid AND weblink_cat_status = :status AND ".groupaccess("weblink_cat_visibility")."
            ".(multilang_table("WL") ? " AND ".in_group('weblink_cat_language', LANGUAGE) : '')."
            LIMIT 0,1", [':catid' => (int)$weblink_cat_id, ':status' => '1']
        );

        if (dbrows($result) > 0) {
            $data = dbarray($result);

            if (defined('WEBLINKS_EXIST')) {
                if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_weblinks.php')) {
                    add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('web_0000').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_weblinks.php"/>');
                }
            }

            set_title(self::$locale['web_0000']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS."weblinks/weblinks.php",
                'title' => self::$locale['web_0000']
            ]);

            add_to_title(self::$locale['global_201'].$data['weblink_cat_name']);

            // Predefined variables, do not edit these values
            $weblink_cat_index = dbquery_tree(DB_WEBLINK_CATS, "weblink_cat_id", "weblink_cat_parent");

            $max_weblink_rows = dbcount("(weblink_id)", DB_WEBLINKS, "weblink_cat='".$data['weblink_cat_id']."' AND ".groupaccess("weblink_visibility").(multilang_table("WL") ? " AND ".in_group('weblink_language', LANGUAGE) : "")." AND weblink_status='1'");

            $this->def_data['pagenav'] = makepagenav($this->rowstart, $this->weblink_settings['links_per_page'], $max_weblink_rows, 3, INFUSIONS."weblinks/weblinks.php?cat_id=".$weblink_cat_id.$linktype."&amp;");

            $this->weblink_cat_breadcrumbs($weblink_cat_index);

            // build categorial data.
            $this->def_data += $data;
            if ($max_weblink_rows) {
                $result = dbquery($this->get_WeblinkQuery(['condition' => "w.weblink_cat='".$data['weblink_cat_id']."'"]));
                while ($wdata = dbarray($result)) {
                    $this->def_data['weblink_items'][$wdata['weblink_id']] = self::get_WeblinksData($wdata);
                }
            }

            return (array)$this->def_data;
        }

        redirect(INFUSIONS."weblinks/weblinks.php");

        return NULL;
    }

    /**
     * Weblinks Category Breadcrumbs Generator
     *
     * @param $weblink_cat_index
     */
    private function weblink_cat_breadcrumbs($weblink_cat_index) {

        /* Make an infinity traverse */
        function breadcrumb_arrays($index, $webid) {
            $crumb = [];
            if (isset($index[get_parent($index, $webid)])) {
                $_name = dbarray(dbquery("SELECT weblink_cat_id, weblink_cat_name, weblink_cat_parent FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_id='".$webid."' AND weblink_cat_status='1' AND ".groupaccess("weblink_cat_visibility").(multilang_table("WL") ? " AND ".in_group('weblink_cat_language', LANGUAGE) : "").""));
                $crumb = [
                    "link"  => INFUSIONS."weblinks/weblinks.php?cat_id=".$_name['weblink_cat_id'],
                    "title" => $_name['weblink_cat_name']
                ];
                if (isset($index[get_parent($index, $webid)])) {
                    if (get_parent($index, $webid) == 0) {
                        return $crumb;
                    }
                    $crumb_1 = breadcrumb_arrays($index, get_parent($index, $webid));
                    $crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
                }
            }

            return $crumb;
        }

        // then we make a infinity recursive function to loop/break it out.
        $crumb = breadcrumb_arrays($weblink_cat_index, $this->cat_id);
        $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;
        // then we sort in reverse.
        if ($title_count) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if ($title_count) {
            foreach ($crumb['title'] as $wbi => $value) {
                BreadCrumbs::getInstance()->addBreadCrumb(["link" => $crumb['link'][$wbi], "title" => $value]);
                if ($wbi == count($crumb['title']) - 1) {
                    add_to_title(self::$locale['global_201'].$value);
                }
            }
        } else if (isset($crumb['title'])) {
            add_to_title(self::$locale['global_201'].$crumb['title']);
            BreadCrumbs::getInstance()->addBreadCrumb(["link" => $crumb['link'], "title" => $crumb['title']]);
        }
    }

    protected function get_WeblinkQuery(array $filters = []) {

        return "SELECT w.*, wc.*
            FROM ".DB_WEBLINKS." AS w
            LEFT JOIN ".DB_WEBLINK_CATS." AS wc ON wc.weblink_cat_id = w.weblink_cat
            WHERE w.weblink_status='1' AND ".groupaccess("w.weblink_visibility")." AND wc.weblink_cat_status='1' AND ".groupaccess("wc.weblink_cat_visibility")."
            ".(multilang_table("WL") ? " AND ".in_group('w.weblink_language', LANGUAGE)." AND ".in_group('wc.weblink_cat_language', LANGUAGE) : "")."
            ".(!empty($filters['condition']) ? " AND ".$filters['condition'] : "")."
            GROUP BY w.weblink_id
            ORDER BY ".$this->check_WeblinksFilter()."
            LIMIT ".($this->rowstart.",".(!empty($this->weblink_settings['links_per_page']) ? $this->weblink_settings['links_per_page'] : 15))."
        ";
    }

    /**
     * Sql filter between $_GET['type']
     * latest
     * most open
     */
    private function check_WeblinksFilter() {

        /* Filter Construct */
        $catfilter = "w.weblink_datestamp DESC";

        if (isset($this->type) && in_array($this->type, $this->allowed_filters)) {
            switch ($this->type) {
                case 'latest':
                    $catfilter = "w.weblink_datestamp DESC";
                    break;
                case 'oldest':
                    $catfilter = "w.weblink_datestamp ASC";
                    break;
                case 'opened':
                    $catfilter = "weblink_count DESC";
                    break;
                default:
                    $catfilter = "w.weblink_datestamp DESC";
            }
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
                    'edit'   => [
                        'link'  => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;ref=weblinkform&amp;weblink_id=".$data['weblink_id'],
                        'title' => self::$locale['edit']
                    ],
                    'delete' => [
                        'link'  => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink()."&amp;action=delete&amp;ref=weblinkform&amp;weblink_id=".$data['weblink_id'],
                        'title' => self::$locale['delete']
                    ]
                ];
            }

            // Build Array
            $info = [
                # Links and Admin Actions
                'weblinks_url'     => INFUSIONS."weblinks/weblinks.php?weblink_id=".$data['weblink_id'],
                'weblinks_cat_url' => INFUSIONS."weblinks/weblinks.php?cat_id=".$data['weblink_cat_id'],
                'admin_actions'    => $adminActions
            ];
            $info += $data;
            return (array)$info;
        }

        return [];
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
        }
        redirect(clean_request('', ['weblink_id'], FALSE));
    }
}
