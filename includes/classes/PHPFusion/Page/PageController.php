<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: PageController.php
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
namespace PHPFusion\Page;

use PHPFusion\OpenGraph;
use PHPFusion\Panels;

/**
 * Got html construct. So need to use PageView.
 * Class PageController
 *
 * @package PHPFusion\Page
 */
class PageController extends PageModel {

    protected static $info = [
        'title'       => '',
        'error'       => '',
        'body'        => '',
        'count'       => 0,
        'pagenav'     => '',
        'line_breaks' => ''
    ];

    /**
     * @param array $colData
     *
     * @return mixed|string|null
     */
    public static function displayWidget($colData) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."custom_pages.php");
        if ($colData['page_widget'] == 'content' || empty($colData['page_widget'])) {

            return self::displayContentHTML($colData);

        } else {
            // throw new \Exception('The form sanitizer could not handle the request! (input: '.$input_name.')');
            try {
                $current_widget = self::$widgets[$colData['page_widget']]['display_instance'];
                if (method_exists($current_widget, 'displayWidget')) {
                    return $current_widget->displayWidget($colData);
                } else {
                    return $locale['page_405'];
                }
            } catch (\Exception $e) {
                echo $locale['page_401'].' ', $locale['page_404'], "\n";
                return NULL;
            }
        }
    }

    /**
     * Core page content display driver
     *
     * @param array $colData
     *
     * @return string
     */
    public static function displayContentHTML($colData) {

        require_once THEMES."templates/global/custompage.tpl.php";

        $htmlArray = [];
        $html = parse_text($colData['page_content'], ['parse_bbcode' => FALSE, 'descript' => FALSE]);
        $htmlArray['pagenav'] = '';
        $htmlArray['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? intval($_GET['rowstart']) : 0;
        $htmlArray['body'] = preg_split("/<!?--\s*pagebreak\s*-->/i", self::$info['line_breaks'] == 'y' ? nl2br($html) : $html);
        $htmlArray['count'] = count($htmlArray['body']);

        if ($htmlArray['count'] > 0) {
            if ($htmlArray['rowstart'] > $htmlArray['count']) {
                redirect(BASEDIR."viewpage.php?page_id=".intval($_GET['page_id']));
            }
            $htmlArray['pagenav'] = makepagenav($htmlArray['rowstart'], 1, $htmlArray['count'], 1, BASEDIR."viewpage.php?page_id=".self::$data['page_id']."&amp;")."\n";
        }
        ob_start();

        display_page_content($htmlArray);

        return ob_get_clean();
    }

    /**
     * Set page variables
     *
     * @param int $page_id
     */
    protected static function setPageInfo($page_id) {
        $locale = fusion_get_locale("", LOCALE.LOCALESET."custom_pages.php");

        $page_id = (((!empty($page_id)) ? intval($page_id) : isset($_GET['page_id']) && isnum($_GET['page_id'])) ? intval($_GET['page_id']) : 0);

        self::$info['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

        OpenGraph::ogCustomPage($page_id);

        $query = "SELECT * FROM ".DB_CUSTOM_PAGES." WHERE page_id=:page_id AND ".groupaccess('page_access')." ".(multilang_table("CP") ? "AND ".in_group("page_language", LANGUAGE) : "");
        $parameters = [
            ':page_id' => $page_id
        ];
        $cp_result = dbquery($query, $parameters);

        self::$data['page_rows'] = dbrows($cp_result);

        if (self::$data['page_rows'] > 0) {

            self::$data = dbarray($cp_result);

            if (self::$data['page_status'] == 1 || checkrights('CP')) {

                if (empty(self::$data['page_left_panel'])) {
                    Panels::getInstance()->hidePanel('LEFT');
                }
                if (empty(self::$data['page_right_panel'])) {
                    Panels::getInstance()->hidePanel('RIGHT');
                }
                if (empty(self::$data['page_header_panel'])) {
                    Panels::getInstance()->hidePanel('AU_CENTER');
                }
                if (empty(self::$data['page_footer_panel'])) {
                    Panels::getInstance()->hidePanel('BL_CENTER');
                }
                if (empty(self::$data['page_top_panel'])) {
                    Panels::getInstance()->hidePanel('U_CENTER');
                }
                if (empty(self::$data['page_bottom_panel'])) {
                    Panels::getInstance()->hidePanel('L_CENTER');
                }

                self::loadComposerData();
                self::cacheWidget();

                // Construct Meta
                add_to_title(self::$data['page_title']);
                //add_breadcrumb(['link' => FUSION_REQUEST, 'title' => self::$data['page_title']]);
                $tree = dbquery_tree_full(DB_CUSTOM_PAGES, 'page_id', 'page_cat');
                $tree_index = tree_index($tree);
                make_page_breadcrumbs($tree_index, $tree, 'page_id', 'page_title', 'page_id');

                if (!empty(self::$data['page_keywords'])) {
                    set_meta("keywords", self::$data['page_keywords']);
                }
                self::$info['title'] = self::$data['page_title'];
                self::$info['line_breaks'] = self::$data['page_breaks'];
                self::$info['body'] = PageView::displayComposer();
            } else {
                add_to_title($locale['page_401']);
                self::$info['title'] = $locale['page_401'];
                self::$info['error'] = $locale['page_402'];
            }
        } else {
            add_to_title($locale['page_401']);
            self::$info['title'] = $locale['page_401'];
            self::$info['error'] = $locale['page_402'];
        }
    }
}
