<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: HomePage.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

class HomePage {
    private static $limit = 3;

    public static function displayHome() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'homepage.php');

        require_once THEMES.'templates/global/homepage.tpl.php';

        add_to_title($locale['home']);
        add_breadcrumb(['title' => $locale['home'], 'link' => BASEDIR.'index.php']);

        $contents = [];

        $modules = fusion_filter_hook('home_modules');

        if (!empty($modules) && !defined('DISABLE_HOME_MODULES')) {
            foreach ($modules as $module) {
                foreach ($module as $key => $data) {
                    foreach ($data['items'] as $item_key => $item) {
                        $item['content'] = str_replace('../../images', IMAGES, $item['content']);
                        $profile = profile_link($item['user_id'], $item['user_name'], $item['user_status']);
                        $cat = '<a href="'.$item['category_link'].'">'.$item['cat_name'].'</a>';
                        $date = showdate('shortdate', $item['datestamp']);
                        $data['items'][$item_key] += [
                            'author' => $profile,
                            'meta'   => $locale['home_0105'].$profile.' '.$date.$locale['home_0106'].$cat,
                            'date'   => $date
                        ];
                    }

                    $contents[$key] = $data;
                }
            }
        }

        display_home($contents);
    }

    /**
     * @param int $limit
     */
    public static function setLimit($limit) {
        self::$limit = $limit;
    }

    /**
     * @return int
     */
    public static function getLimit() {
        return self::$limit;
    }
}
