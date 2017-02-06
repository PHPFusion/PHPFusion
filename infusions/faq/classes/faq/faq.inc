<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/classes/faq/faq.php
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
namespace PHPFusion\FAQ;

use PHPFusion\BreadCrumbs;
use PHPFusion\SiteLinks;

/**
 * Class Faq
 *
 * @package PHPFusion\FAQ
 */
abstract class Faq extends FaqServer {

    private static $locale = array();
    public $info = array();

    /**
     * Executes main page information
     *
     * @param int $category
     *
     * @return array
     */
    public function set_FaqInfo($category = 0) {
        self::$locale = fusion_get_locale("", FAQ_LOCALE);
        set_title(SiteLinks::get_current_SiteLinks("", 'link_name'));
        BreadCrumbs::getInstance()->addBreadCrumb(
            array(
                'link'  => INFUSIONS.'faq/faq.php',
                'title' => SiteLinks::get_current_SiteLinks('', 'link_name')
            )
        );

        $info = array(
            'faq_categories' => array(),
            'faq_items'      => array(),
            'faq_get'        => 0,
        );
        $info = array_merge($info, self::get_FaqData($category));
        if (!empty($_GET['cat_id']) && isnum($_GET['cat_id'])) {
            BreadCrumbs::getInstance()->addBreadCrumb(
                array(
                    'link'  => INFUSIONS.'faq/faq.php?cat_id='.$_GET['cat_id'],
                    'title' => $info['faq_categories'][$_GET['cat_id']]['faq_cat_name']
                )
            );
        }
        $this->info = $info;

        return (array)$info;
    }

    /**
     * Outputs category variables
     *
     * @param int $cat
     *
     * @return array
     */
    protected static function get_FaqData($cat = 0) {
        $info = [
            'faq_items'      => [],
            'faq_categories' => [
                0 => [
                    'faq_cat_id'          => 0,
                    'faq_cat_name'        => self::$locale['faqs_0003'],
                    'faq_cat_description' => ''
                ]
            ],
        ];
        $c_result = dbquery("SELECT * FROM ".DB_FAQ_CATS.(multilang_table('FQ') ? " WHERE faq_cat_language='".LANGUAGE."' " : '').($cat ? "AND faq_cat_id='".$cat."'" : '')."ORDER BY faq_cat_id ASC");
        if (dbrows($c_result)) {
            while ($c_data = dbarray($c_result)) {
                $info['faq_categories'][$c_data['faq_cat_id']] = $c_data;
            }
        }

        // Get Items
        $result = dbquery("SELECT ac.*, 
            au.user_id, au.user_name, au.user_status, au.user_avatar, au.user_level, au.user_joined
            FROM ".DB_FAQS." ac
            LEFT JOIN ".DB_USERS." AS au ON ac.faq_name=au.user_id
            WHERE ac.faq_status='1' AND ".groupaccess("ac.faq_visibility").
            (multilang_table('FQ') ? " AND ac.faq_language='".LANGUAGE."'" : '').($cat ? " AND faq_cat_id='$cat'" : ' AND faq_cat_id=0')."
            GROUP BY ac.faq_id ORDER BY ac.faq_cat_id ASC, ac.faq_id ASC
        ");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $data['faq_answer'] = parse_textarea($data['faq_answer'], FALSE, FALSE, TRUE, IMAGES, $data['faq_breaks'] == 'y' ? TRUE : FALSE);
                $info['faq_items'][$data['faq_id']] = $data;
            }
        }
        // Get Categories
        $info['faq_get'] = $cat;

        return (array)$info;
    }

    /**
     *  Display Ratings
     *
     * @param $data
     *
     * @return string
     */
    private static function get_FaqRatings($data) {
        $html = "";
        if (fusion_get_settings('ratings_enabled') && $data['faq_ratings'] == TRUE) {
            ob_start();
            require_once INCLUDES."ratings_include.php";
            showratings("FQ", $data['faq_id'], INFUSIONS."faq/faq.php?faq_id=".$data['faq_id']);
            $html = ob_get_contents();
            ob_end_clean();
        }

        return (string)$html;
    }

    /**
     * Display Comments
     *
     * @param $data
     *
     * @return string
     */
    private static function get_FaqComments($data) {
        $html = "";
        if (fusion_get_settings('comments_enabled') && $data['faq_comments'] == TRUE) {
            ob_start();
            require_once INCLUDES."comments_include.php";
            showcomments("FQ", DB_FAQS, "faq_id", $data['faq_id'], INFUSIONS."faq/faq.php?faq=".$data['faq_id'], $data['faq_ratings']);
            $html = ob_get_contents();
            ob_end_clean();
        }

        return (string)$html;
    }

    protected function __clone() {
    }
}