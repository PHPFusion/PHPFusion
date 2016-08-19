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

    public static function get_newsAdminLocale() {
        if (empty(self::$admin_locale)) {
            $locale_path = INFUSIONS."news/locale/English/news_admin.php";
            $admin_locale_path = LOCALE."English/admin/settings.php";
            if (file_exists(INFUSIONS."news/locale/".LOCALESET."news_admin.php")) {
                $locale_path = INFUSIONS."news/locale/".LOCALESET."news_admin.php";
            }
            if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
                $admin_locale_path = LOCALE.LOCALESET."admin/settings.php";
            }
            $locale = fusion_get_locale('', $locale_path);
            $locale += fusion_get_locale('', $admin_locale_path);
            self::$admin_locale = $locale;
        }

        return self::$admin_locale;
    }


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