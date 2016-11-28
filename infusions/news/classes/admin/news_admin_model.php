<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news_admin_model.php
| Author: PHP-Fusion Development Team
| Version: 9.2 prototype
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\News;

class NewsAdminModel extends NewsServer {

    private static $admin_locale = array();

    /**
     * News Table
     * @var array
     */
    protected $default_news_data = array(
        'news_id' => 0,
        'news_draft' => 0,
        'news_sticky' => 0,
        'news_news' => '',
        'news_datestamp' => TIME, //time(),
        'news_extended' => '',
        'news_keywords' => '',
        'news_breaks' => 'n',
        'news_allow_comments' => 1,
        'news_allow_ratings' => 1,
        'news_language' => LANGUAGE,
        'news_visibility' => 0,
        'news_subject' => '',
        'news_start' => '',
        'news_end' => '',
        'news_cat' => 0,
        'news_image' => '',
        'news_image_full_default' => '',
        'news_image_front_defualt' => '',
        'news_image_align' => 'pull-left'
    );

    /**
     * News Gallery Table
     * @var array
     */
    protected $default_image_data = array(
        'news_image_id' => 0,
        'news_id' => '',
        'news_image_user' => 0,
        'news_image_name' => '',
        'news_image_thumb_t1' => '',
        'news_image_thumb_t2' => '',
        'news_image_datestamp' => TIME,
    );

    public static function get_newsAdminLocale() {
        if (empty(self::$admin_locale)) {
            $admin_locale_path = LOCALE."English/admin/settings.php";
            if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
                $admin_locale_path = LOCALE.LOCALESET."admin/settings.php";
            }
            $locale = fusion_get_locale('', NEWS_ADMIN_LOCALE);
            $locale += fusion_get_locale('', $admin_locale_path);
            self::$admin_locale = $locale;
        }

        return self::$admin_locale;
    }
    /*
    public function upgrade_news_gallery() {

        if (!db_exists(DB_NEWS_IMAGES)) {
            $inf_newtable = array();
            $inf_altertable = array();
            require_once INFUSIONS."news/infusion.php";

            dbquery("CREATE TABLE ".$inf_newtable[2]);

            // Port Photos into New Tables
            $query = "SELECT news_id, news_image, news_image_t1, news_image_t2,
                      news_ialign 'news_image_align', news_name 'news_image_user', news_datestamp 'news_image_datestamp' FROM ".DB_NEWS;
            $result = dbquery($query);
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $data += $this->default_image_data;
                    $data['news_full_default'] = 1;
                    $data['news_front_default'] = 1;
                    dbquery_insert(DB_NEWS_IMAGES, $data, 'save');
                }
            }

            // Drop existing columns
            dbquery("ALTER TABLE ".$inf_altertable[4]);
            dbquery("ALTER TABLE ".$inf_altertable[5]);
            dbquery("ALTER TABLE ".$inf_altertable[6]);
            dbquery("ALTER TABLE ".$inf_altertable[7]);
            addNotice('success', 'One time automatic news app upgrade complete');
        }
    }
    */
    /**
     * Returns nearest data unit
     * @param $total_bit
     * @return int
     */
    protected function calculate_byte($total_bit) {
        $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
        foreach ($calc_opts as $byte => $val) {
            if ($total_bit / $byte <= 999) {
                return (int)$byte;
            }
        }

        return 1000000;
    }

    /**
     * Function to progressively return closest full image_path
     * @param $news_image
     * @param $news_image_t1
     * @param $news_image_t2
     * @return string
     */
    protected function get_news_image_path($news_image, $news_image_t1, $news_image_t2, $hiRes = FALSE) {
        if (!$hiRes) {
            if ($news_image_t1 && file_exists(IMAGES_N_T.$news_image_t1)) {
                return IMAGES_N_T.$news_image_t1;
            }
            if ($news_image_t1 && file_exists(IMAGES_N.$news_image_t1)) {
                return IMAGES_N.$news_image_t1;
            }
            if ($news_image_t2 && file_exists(IMAGES_N_T.$news_image_t2)) {
                return IMAGES_N_T.$news_image_t2;
            }
            if ($news_image_t2 && file_exists(IMAGES_N.$news_image_t2)) {
                return IMAGES_N.$news_image_t2;
            }
            if ($news_image && file_exists(IMAGES_N.$news_image)) {
                return IMAGES_N.$news_image;
            }
        } else {
            if ($news_image && file_exists(IMAGES_N.$news_image)) {
                return IMAGES_N.$news_image;
            }
            if ($news_image_t2 && file_exists(IMAGES_N.$news_image_t2)) {
                return IMAGES_N.$news_image_t2;
            }
            if ($news_image_t2 && file_exists(IMAGES_N_T.$news_image_t2)) {
                return IMAGES_N_T.$news_image_t2;
            }
            if ($news_image_t1 && file_exists(IMAGES_N.$news_image_t1)) {
                return IMAGES_N.$news_image_t1;
            }
            if ($news_image_t1 && file_exists(IMAGES_N_T.$news_image_t1)) {
                return IMAGES_N_T.$news_image_t1;
            }
        }

        return FALSE;
    }

}