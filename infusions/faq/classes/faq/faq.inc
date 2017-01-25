<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/classes/articles/articles.php
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

use PHPFusion\SiteLinks;
use \PHPFusion\BreadCrumbs;

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

        set_title(SiteLinks::get_current_SiteLinks("", "link_name"));

        BreadCrumbs::getInstance()->addBreadCrumb(
            array(
                "link" => INFUSIONS."faq/faq.php",
                "title" => SiteLinks::get_current_SiteLinks("", "link_name")
            )
        );

        $info = array(
            "faq_categories" => array(),
            "faq_data" => array(),
            "faq_items" => array()
        );
        $info = array_merge($info, self::get_FaqData($category));
            $faq_cat_index = dbquery_tree(DB_FAQS, "faq_id", "faq_cat_id");
        (!empty($category) ? self::faq_cat_breadcrumbs($faq_cat_index) : "");
        $this->info = $info;

        return (array)$info;

    }

    /**
     * Articles Category Breadcrumbs Generator
     *
     * @param $faq_cat_index
     */
    private function faq_cat_breadcrumbs($faq_cat_index) {
        $locale = fusion_get_locale("", FAQ_LOCALE);

        /* Make an infinity traverse */
        function breadcrumb_arrays($index, $id) {
            $crumb = &$crumb;
            if (isset($index[get_parent($index, $id)])) {
                $_name = dbarray(dbquery("SELECT faq_id, faq_question, faq_cat_id FROM ".DB_FAQS." WHERE faq_id='".$id."' AND faq_status='1' AND ".groupaccess("faq_visibility").""));
                $crumb = array(
                    "link" => INFUSIONS."faq/faq.php?cat_id=".$_name['faq_id'],
                    "title" => $_name['faq_question']
                );
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
        $crumb = breadcrumb_arrays($faq_cat_index, $_GET['cat_id']);
        // then we sort in reverse.
        if (count($crumb['title']) > 1) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if (count($crumb['title']) > 1) {
            foreach ($crumb['title'] as $i => $value) {
                BreadCrumbs::getInstance()->addBreadCrumb(array("link" => $crumb['link'][$i], "title" => $value));
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                }
            }
        } elseif (isset($crumb['title'])) {
            add_to_title($locale['global_201'].$crumb['title']);
            BreadCrumbs::getInstance()->addBreadCrumb(array("link" => $crumb['link'], "title" => $crumb['title']));
        }
    }

    /**
     * Outputs category variables
     *
     * @param int $cat
     *
     * @return array
     */
    protected static function get_FaqData($cat = 0) {

        //$article_settings = self::get_article_settings();
        $info['faq_items'] = dbquery_tree_full(DB_FAQS, "faq_id", "faq_cat_id", "",
            "SELECT ac.*,
                au.user_id, au.user_name, au.user_status, au.user_avatar, au.user_level, au.user_joined,
                SUM(ar.rating_vote) AS sum_rating,
                COUNT(ar.rating_item_id) AS count_votes,
                COUNT(ad.comment_item_id) AS count_comment
            FROM ".DB_FAQS." ac
            LEFT JOIN ".DB_USERS." AS au ON ac.faq_name=au.user_id
            LEFT JOIN ".DB_RATINGS." AS ar ON ar.rating_item_id=ac.faq_id AND ar.rating_type='FQ'
            LEFT JOIN ".DB_COMMENTS." AS ad ON ad.comment_item_id=ac.faq_id AND ad.comment_type='FQ' AND ad.comment_hidden='0'
            WHERE ac.faq_status='1' AND ".groupaccess("ac.faq_visibility").
            (multilang_table("FQ") ? " AND ac.faq_language='".LANGUAGE."'" : "")."

            GROUP BY ac.faq_id
            ORDER BY ac.faq_cat_id ASC, ac.faq_id ASC
        ");
        $info['faq_categories'] = $info['faq_items'][0];
        $info['faq_data'] = $info['faq_items'][$cat];
        $info['faq_get'] = $cat;

        return (array)$info;
    }

    /**
     *  Display Ratings
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