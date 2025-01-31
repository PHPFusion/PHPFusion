<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: HomePage.php
| Author: Core Development Team
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

    /**
     * Display homepage
     */
    public static function displayHome() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'homepage.php');

        require_once THEMES.'templates/global/homepage.tpl.php';

        add_to_title($locale['home']);
        add_breadcrumb(['title' => $locale['home'], 'link' => BASEDIR.'index.php']);

        $contents = [];

        $modules = fusion_filter_hook('home_modules', self::$limit);

        if (!empty($modules) && !defined('DISABLE_HOME_MODULES')) {
            foreach ($modules as $module) {
                foreach ($module as $key => $data) {
                    $data['norecord'] = !empty($data['norecord']) ? $data['norecord'] : '';
                    if (!empty($data['data'])) {
                        foreach ($data['data'] as $item_key => $item) {
                            $data['data'][$item_key]['content'] = str_replace('../../images', IMAGES, $item['content']);
                            $profile = profile_link($item['user_id'], $item['user_name'], $item['user_status']);
                            $cat = '<a href="'.$item['category_link'].'">'.$item['cat_name'].'</a>';
                            $date = showdate('shortdate', $item['datestamp']);

                            if (fusion_get_settings('comments_enabled') == 1) {
                                $data['data'][$item_key]['comments'] = format_word($item['comments_count'], $locale['fmt_comment']);
                            }

                            if (fusion_get_settings('ratings_enabled') == 1) {
                                $data['data'][$item_key]['ratings'] = format_word($item['ratings_count'], $locale['fmt_rating']);
                            }

                            $data['data'][$item_key] += [
                                'author' => $profile,
                                'meta'   => $locale['home_0105'].$profile.' '.$date.$locale['home_0106'].$cat,
                                'date'   => $date,
                                'cat'    => !empty($item['cat_id']) ? $cat : $locale['home_0102']
                            ];
                        }
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
