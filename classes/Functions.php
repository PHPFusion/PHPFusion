<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Downloads.php
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
namespace PHPFusion\Downloads;

use PHPFusion\BreadCrumbs;

class Functions {
    /**
     * Download Category Hierarchy Full Data
     *
     * @return array
     */
    public static function get_downloadCats() {
        return dbquery_tree_full(DB_DOWNLOAD_CATS, 'download_cat_id', 'download_cat_parent',
            (multilang_table("DL") ? "WHERE ".in_group('download_cat_language', LANGUAGE) : "")."");
    }

    /**
     * Get Single Download Category Data
     *
     * @param $id
     *
     * @return array|bool
     */
    public static function get_downloadCatData($id) {
        if (self::validate_downloadCat($id)) {
            return dbarray(dbquery("SELECT * FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id=:catid", [':catid' => intval($id)]));
        }

        return FALSE;
    }

    /**
     * Validate Download Cat
     *
     * @param $id
     *
     * @return bool|string
     */
    public static function validate_downloadCat($id) {
        if (is_numeric($id)) {
            if ($id < 1) {
                return 1;
            } else {
                return dbcount("('download_cat_id')", DB_DOWNLOAD_CATS, "download_cat_id=:catid", [':catid' => intval($id)]);
            }
        }

        return FALSE;
    }

    /**
     * Get Download Category Hierarchy Index
     *
     * @return array
     */
    public static function get_downloadCatsIndex() {
        return dbquery_tree(DB_DOWNLOAD_CATS, 'download_cat_id', 'download_cat_parent',
            "".(multilang_table("DL") ? "WHERE ".in_group('download_cat_language', LANGUAGE) : '')."");
    }

    /**
     * Format Download Category Listing
     *
     * @return array
     */
    public static function get_downloadCatsData() {
        $data = dbquery_tree_full(DB_DOWNLOAD_CATS, 'download_cat_id', 'download_cat_parent', (multilang_table('DL') ? "WHERE ".in_group('download_cat_language', LANGUAGE) : ''));
        foreach ($data as $index => $cat_data) {
            foreach ($cat_data as $download_cat_id => $cat) {
                $data[$index][$download_cat_id]['download_cat_link'] = "<a href='".DOWNLOADS."downloads.php?cat_id=".$cat['download_cat_id']."'>".$cat['download_cat_name']."</a>";
            }
        }

        return $data;
    }

    /**
     * Validate Download
     *
     * @param $id
     *
     * @return bool|string
     */
    public static function validate_download($id) {
        if (isnum($id)) {
            return (int)dbcount("('download_id')", DB_DOWNLOADS, "download_id=:downloadid", [':downloadid' => intval($id)]);
        }

        return (int)FALSE;
    }

    /**
     * Download Category Breadcrumbs Generator
     *
     * @param $index
     */
    public static function downloadCats_breadcrumbs($index) {
        $locale = fusion_get_locale();

        function breadcrumb_arrays($index, $id) {
            $crumb = [];
            if (isset($index[get_parent($index, $id)])) {
                $_name = dbarray(dbquery("SELECT download_cat_id, download_cat_name, download_cat_parent FROM ".DB_DOWNLOAD_CATS.(multilang_table('DL') ? " WHERE ".in_group('download_cat_language', LANGUAGE)." AND " : " WHERE ")." download_cat_id='".intval($id)."'"));
                $crumb = [
                    'link'  => INFUSIONS."downloads/downloads.php?cat_id=".$_name['download_cat_id'],
                    'title' => $_name['download_cat_name']
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
        $crumb = breadcrumb_arrays($index, $_GET['cat_id']);
        $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;
        // then we sort in reverse.
        if ($title_count) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if ($title_count) {
            foreach ($crumb['title'] as $i => $value) {
                BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'][$i], 'title' => $value]);
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                    add_to_meta($value);
                }
            }
        } else if (isset($crumb['title'])) {
            add_to_title($locale['global_201'].$crumb['title']);
            add_to_meta($crumb['title']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
        }
    }

    /**
     * Get the best available paths for image and thumbnail
     *
     * @param            $download_image
     * @param            $download_image_thumb
     * @param bool|FALSE $hiRes
     *
     * @return bool|string
     */
    public static function get_download_image_path($download_image, $download_image_thumb, $hiRes = FALSE) {
        if (!$hiRes) {
            if ($download_image && file_exists(DOWNLOADS.'images/'.$download_image)) {
                return DOWNLOADS.'images/'.$download_image;
            }
            if ($download_image_thumb && file_exists(DOWNLOADS.'images/'.$download_image_thumb)) {
                return DOWNLOADS.'images/'.$download_image_thumb;
            }
        } else {
            if ($download_image && file_exists(DOWNLOADS.'images/'.$download_image)) {
                return DOWNLOADS.'images/'.$download_image;
            }
            if ($download_image_thumb && file_exists(DOWNLOADS.'images/'.$download_image_thumb)) {
                return DOWNLOADS.'images/'.$download_image_thumb;
            }
        }

        return FALSE;
    }

    public static function get_download_comments($data) {
        $html = "";
        if (fusion_get_settings('comments_enabled') && $data['download_allow_comments']) {
            ob_start();
            showcomments("D", DB_DOWNLOADS, "download_id", $data['download_id'], INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']);
            $html = ob_get_contents();
            ob_end_clean();
        }

        return (string)$html;
    }

    public static function get_download_ratings($data) {
        $html = "";
        if (fusion_get_settings('ratings_enabled') && $data['download_allow_ratings']) {
            ob_start();
            showratings("D", $data['download_id'], INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']);
            $html = ob_get_contents();
            ob_end_clean();
        }

        return (string)$html;
    }
}
