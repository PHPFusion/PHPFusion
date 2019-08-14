<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Page/PageController.inc
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

use PHPFusion\BreadCrumbs;
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

    public static function display_Widget($colData) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."custom_pages.php");
        if ($colData['page_widget'] == 'content' || empty($colData['page_widget'])) {

            return self::displayContentHTML($colData);

        } else {
            // throw new \Exception('The form sanitizer could not handle the request! (input: '.$input_name.')');
            try {
                $current_widget = self::$widgets[$colData['page_widget']]['display_instance'];
                if (method_exists($current_widget, 'display_widget')) {
                    return $current_widget->display_widget($colData);
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
     * @param $colData
     *
     * @return string
     */
    public static function displayContentHTML($colData) {

        require_once THEMES."templates/global/custompage.php";

        $htmlArray = [];
        ob_start();
        if (fusion_get_settings("allow_php_exe")) {
            eval("?>".stripslashes($colData['page_content'])."<?php ");
        } else {
            echo "<p>".parse_textarea($colData['page_content'], TRUE, TRUE, TRUE, IMAGES, FALSE, FALSE)."</p>\n";
        }

        $eval = ob_get_contents();
        ob_end_clean();
        $htmlArray['pagenav'] = '';
        $htmlArray['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? intval($_GET['rowstart']) : 0;
        $htmlArray['body'] = preg_split("/<!?--\s*pagebreak\s*-->/i", self::$info['line_breaks'] == 'y' ? nl2br($eval) : $eval);
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
     * Set Page Variables
     *
     * @param $page_id
     */
    protected static function set_PageInfo($page_id) {
        $locale = fusion_get_locale("", LOCALE.LOCALESET."custom_pages.php");

        $page_id = ((!empty($page_id)) ? intval($page_id) : isset($_GET['page_id']) && isnum($_GET['page_id']) ? intval($_GET['page_id']) : 0);

        self::$info['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

        OpenGraph::ogCustomPage($page_id);

        $query = "SELECT * FROM ".DB_CUSTOM_PAGES." WHERE page_id=:page_id AND page_status=:page_status AND ".groupaccess('page_access')." ".(multilang_table("CP") ? "AND ".in_group("page_language", LANGUAGE) : "");
        $parameters = [
            ':page_id'     => $page_id,
            ':page_status' => 1,
        ];
        $cp_result = dbquery($query, $parameters);

        self::$data['page_rows'] = dbrows($cp_result);

        if (self::$data['page_rows'] > 0) {

            self::$data = dbarray($cp_result);

            if (empty(self::$data['page_left_panel'])) {
                Panels::getInstance()->hide_panel('LEFT');
            }
            if (empty(self::$data['page_right_panel'])) {
                Panels::getInstance()->hide_panel('RIGHT');
            }
            if (empty(self::$data['page_header_panel'])) {
                Panels::getInstance()->hide_panel('AU_CENTER');
            }
            if (empty(self::$data['page_footer_panel'])) {
                Panels::getInstance()->hide_panel('BL_CENTER');
            }
            if (empty(self::$data['page_top_panel'])) {
                Panels::getInstance()->hide_panel('U_CENTER');
            }
            if (empty(self::$data['page_bottom_panel'])) {
                Panels::getInstance()->hide_panel('L_CENTER');
            }

            self::load_ComposerData();
            self::cache_widget();

            // Construct Meta
            add_to_title($locale['global_200'].self::$data['page_title']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$data['page_title']]);
            if (!empty(self::$data['page_keywords'])) {
                set_meta("keywords", self::$data['page_keywords']);
            }
            self::$info['title'] = self::$data['page_title'];
            self::$info['line_breaks'] = self::$data['page_breaks'];
            self::$info['body'] = PageView::display_Composer();

        } else {
            add_to_title($locale['global_200'].$locale['page_401']);
            self::$info['title'] = $locale['page_401'];
            self::$info['error'] = $locale['page_402'];
        }
    }
}
