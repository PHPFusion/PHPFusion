<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news_admin_model.php
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
namespace PHPFusion\News;
/**
 * Class NewsAdminModel
 *
 * @package PHPFusion\News
 */
class NewsAdminModel extends NewsServer {

    /**
     * @var array
     */
    private static $admin_locale = [];

    /**
     * News Table
     *
     * @var array
     */
    protected $default_news_data = [
        'news_id'                  => 0,
        'news_draft'               => 0,
        'news_sticky'              => 0,
        'news_news'                => '',
        'news_datestamp'           => TIME,
        'news_extended'            => '',
        'news_keywords'            => '',
        'news_breaks'              => 'n',
        'news_allow_comments'      => 1,
        'news_allow_ratings'       => 1,
        'news_language'            => LANGUAGE,
        'news_visibility'          => 0,
        'news_subject'             => '',
        'news_start'               => '',
        'news_end'                 => '',
        'news_cat'                 => 0,
        'news_image'               => '',
        'news_image_full_default'  => '',
        'news_image_front_default' => '',
        'news_image_align'         => 'pull-left'
    ];

    /**
     * News Gallery Table
     *
     * @var array
     */
    protected $default_image_data = [
        'news_image_id'        => 0,
        'news_id'              => '',
        'news_image_user'      => 0,
        'news_image_name'      => '',
        'news_image_thumb_t1'  => '',
        'news_image_thumb_t2'  => '',
        'news_image_datestamp' => TIME,
    ];

    /**
     * Get the admin locale
     *
     * @return array|null
     */
    public static function get_newsAdminLocale() {
        if (empty(self::$admin_locale)) {
            $admin_locale_path = LOCALE."English/admin/settings.php";
            if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
                $admin_locale_path = LOCALE.LOCALESET."admin/settings.php";
            }
            $locale = fusion_get_locale('', [NEWS_ADMIN_LOCALE, $admin_locale_path]);
            self::$admin_locale = $locale;
        }

        return self::$admin_locale;
    }

    /**
     * Function to progressively return closest full image_path
     *
     * @param $news_image
     * @param $news_image_t1
     * @param $news_image_t2
     * @param $hiRes - forced full image (false by default)
     *
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
