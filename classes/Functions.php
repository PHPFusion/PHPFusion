<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/Functions.php
| Author: Frederick MC CHan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Blog;

use PHPFusion\Feedback\Comments;

defined('IN_FUSION') || exit;

/**
 * Functions for Blog System
 * Class Functions
 *
 * @package PHPFusion\Blog
 */
class Functions {
    /**
     * Blog Category Hierarchy Full Data
     *
     * @return array
     */
    public static function get_blogCat() {
        return dbquery_tree_full(DB_BLOG_CATS, "blog_cat_id", "blog_cat_parent");
    }

    /**
     * Get Single Blog Category Data
     *
     * @param $id
     *
     * @return array|bool
     */
    public static function get_blogCatData($id) {
        if (self::validate_blogCat($id)) {
            return dbarray(dbquery("SELECT * FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".intval($id)."'"));
        }

        return FALSE;
    }

    /**
     * Validate Blog Cat
     *
     * @param $id
     *
     * @return bool|string
     */
    public static function validate_blogCat($id) {
        if (isnum($id)) {
            if ($id < 1) {
                return 1;
            } else {
                return dbcount("('blog_cat_id')", DB_BLOG_CATS, "blog_cat_id='".intval($id)."'");
            }
        }

        return FALSE;
    }

    /**
     * Get Blog Category Hierarchy Index
     *
     * @return array
     */
    public static function get_blogCatsIndex() {
        return dbquery_tree(DB_BLOG_CATS, 'blog_cat_id', 'blog_cat_parent');
    }

    /**
     * Format Blog Category Listing
     *
     * @return array
     */
    public static function get_blogCatsData() {
        $data = dbquery_tree_full(DB_BLOG_CATS, 'blog_cat_id', 'blog_cat_parent',
            "".(multilang_table("BL") ? "WHERE ".in_group('blog_cat_language', LANGUAGE) : '')."");
        foreach ($data as $index => $cat_data) {
            foreach ($cat_data as $blog_cat_id => $cat) {
                $data[$index][$blog_cat_id]['blog_cat_link'] = "<a href='".INFUSIONS."blog/blog.php?cat_id=".$cat['blog_cat_id']."'>".$cat['blog_cat_name']."</a>";
            }
        }

        return $data;
    }

    /**
     * Validate blog
     *
     * @param $id
     *
     * @return bool|string
     */
    public static function validate_blog($id) {
        if (isset($id) && isnum($id)) {
            return (int)dbcount("('blog_id')", DB_BLOG, "blog_id='".intval($id)."'");
        }

        return (int)FALSE;
    }

    /**
     * Session based blog reads updater
     * Not used at this moment
     *
     * @param $blog_id
     */
    public static function update_blogReads($blog_id) {
        $session_id = \Defender::set_sessionUserID();
        if (!isset($_SESSION['blog'][$blog_id][$session_id])) {
            $_SESSION['blog'][$blog_id][$session_id] = time();
            dbquery("UPDATE ".DB_BLOG." SET blog_reads=blog_reads+1 WHERE blog_id='".intval($blog_id)."'");
        } else {
            $days_to_keep_session = 30;
            $time = $_SESSION['blog'][$blog_id][$session_id];
            if ($time <= time() - ($days_to_keep_session * 3600 * 24)) {
                $_SESSION['blog'][$blog_id][$session_id] = time();
                dbquery("UPDATE ".DB_BLOG." SET blog_reads=blog_reads+1 WHERE blog_id='".intval($blog_id)."'");
            }
        }
    }

    /**
     * Get the best available paths for image and thumbnail
     *
     * @param      $blog_image
     * @param      $blog_image_t1
     * @param      $blog_image_t2
     * @param bool $hiRes -- true for image, false for thumb
     *
     * @return bool|string
     */
    public static function get_blog_image_path($blog_image, $blog_image_t1, $blog_image_t2, $hiRes = FALSE) {
        if (!$hiRes) {
            if ($blog_image_t1 && file_exists(IMAGES_B_T.$blog_image_t1)) {
                return IMAGES_B_T.$blog_image_t1;
            }
            if ($blog_image_t1 && file_exists(IMAGES_B.$blog_image_t1)) {
                return IMAGES_B.$blog_image_t1;
            }
            if ($blog_image_t2 && file_exists(IMAGES_B_T.$blog_image_t2)) {
                return IMAGES_B_T.$blog_image_t2;
            }
            if ($blog_image_t2 && file_exists(IMAGES_B.$blog_image_t2)) {
                return IMAGES_B.$blog_image_t2;
            }
            if ($blog_image && file_exists(IMAGES_B.$blog_image)) {
                return IMAGES_B.$blog_image;
            }
        } else {
            if ($blog_image && file_exists(IMAGES_B.$blog_image)) {
                return IMAGES_B.$blog_image;
            }
            if ($blog_image_t2 && file_exists(IMAGES_B.$blog_image_t2)) {
                return IMAGES_B.$blog_image_t2;
            }
            if ($blog_image_t2 && file_exists(IMAGES_B_T.$blog_image_t2)) {
                return IMAGES_B_T.$blog_image_t2;
            }
            if ($blog_image_t1 && file_exists(IMAGES_B.$blog_image_t1)) {
                return IMAGES_B.$blog_image_t1;
            }
            if ($blog_image_t1 && file_exists(IMAGES_B_T.$blog_image_t1)) {
                return IMAGES_B_T.$blog_image_t1;
            }
        }

        return FALSE;
    }

    public static function get_blog_comments($data) {
        $html = "";
        if (fusion_get_settings('comments_enabled') && $data['blog_allow_comments']) {
            $html = Comments::getInstance(
                [
                    'comment_item_type'     => 'B',
                    'comment_db'            => DB_BLOG,
                    'comment_col'           => 'blog_id',
                    'comment_item_id'       => $data['blog_id'],
                    'clink'                 => INFUSIONS."blog/blog.php?readmore=".$data['blog_id'],
                    'comment_count'         => TRUE,
                    'comment_allow_subject' => FALSE,
                    'comment_allow_reply'   => TRUE,
                    'comment_allow_post'    => TRUE,
                    'comment_once'          => FALSE,
                    'comment_allow_ratings' => FALSE,
                ], 'blog_comments'
            )->showComments();
        }

        return (string)$html;
    }

    public static function get_blog_ratings($data) {
        $html = "";
        if (fusion_get_settings('ratings_enabled') && $data['blog_allow_ratings']) {
            ob_start();
            echo showratings("B", $data['blog_id'], BASEDIR."infusions/blog/blog.php?readmore=".$data['blog_id']);
            $html = ob_get_contents();
            ob_end_clean();
        }

        return (string)$html;
    }
}
