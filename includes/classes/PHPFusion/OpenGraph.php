<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: OpenGraph.php
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

class OpenGraph {
    private static $data = [
        'title'       => '',
        'description' => '',
        'url'         => '',
        'keywords'    => '',
        'image'       => '',
        'site_name'   => '',
        'type'        => 'website'
    ];

    private static $og_added = FALSE;

    /**
     * @param int $pageid
     */
    public static function ogCustomPage($pageid = 0) {
        $settings = fusion_get_settings();

        $info = [];
        $result = dbquery("SELECT page_content, page_keywords, page_title FROM ".DB_CUSTOM_PAGES." WHERE page_id=:pageid LIMIT 1", [':pageid' => $pageid]);
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['title'] = $data['page_title'].' - '.$settings['sitename'];
            $info['description'] = !empty($data['page_content']) ? fusion_first_words(strip_tags($data['page_content']), 50) : $settings['description'];
            $info['url'] = $settings['siteurl'].'viewpage.php?page_id='.$pageid;
            $info['keywords'] = !empty($data['page_keywords']) ? $data['page_keywords'] : $settings['keywords'];
        }

        self::setValues($info);
    }

    /**
     * @param int $userid
     */
    public static function ogUserProfile($userid = 0) {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale('', LOCALE.LOCALESET."user_fields.php");

        $info = [];
        $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:userid LIMIT 1", [':userid' => $userid]);
        // I know that is not good idea, but some user fields may be disabled... See next code
        if (dbrows($result)) {
            $data = dbarray($result);
            $realname = "";
            if (isset($data['user_name_first']) && trim($data['user_name_first'])) {
                $realname .= trim($data['user_name_first']);
            }

            if (isset($data['user_name_last']) && trim($data['user_name_last'])) {
                $realname .= " ".trim($data['user_name_last']);
            }

            if (trim($realname)) {
                $data['user_name'] .= " (".$realname.")";
            }

            $info['title'] = $locale['u103'].$locale['global_201'].$data['user_name'];
            $info['description'] = $settings['description'];
            $info['url'] = $settings['siteurl'].'profile.php?lookup='.$userid;
            $info['keywords'] = $settings['keywords'];
            $info['image'] = $data['user_avatar'] ? $settings['siteurl'].'images/avatars/'.$data['user_avatar'] : $settings['siteurl'].'images/avatars/no-avatar.jpg';
        }

        self::setValues($info);
    }

    /**
     * @param array $values
     */
    public static function setCustom($values) {
        self::setValues($values);
    }

    /**
     * Get default data
     */
    public static function ogDefault() {
        self::setValues();
    }

    /**
     * @param array $values
     */
    protected static function setValues($values = []) {
        $settings = fusion_get_settings();

        if (!self::$og_added) {
            self::$data['site_name'] = $settings['sitename'];
            if (!empty($values['title']) && !empty($values['description']) && !empty($values['url']) && !empty($values['keywords'])) {
                self::$data['title'] = $values['title'];
                self::$data['description'] = str_replace("\n", ' ', strip_tags(htmlspecialchars_decode($values['description'])));
                self::$data['url'] = $values['url'];
                self::$data['keywords'] = $values['keywords'];

                if (
                    !empty($values['image']) &&
                    in_array(strtolower(pathinfo($values['image'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                ) {
                    self::$data['image'] = $values['image'];
                } else {
                    self::$data['image'] = defined('THEME_ICON') ? THEME_ICON.'mstile-150x150.png' : $settings['siteurl'].'images/favicons/mstile-150x150.png';
                }

                if (!empty($values['type'])) {
                    self::$data['type'] = $values['type'];
                }
            } else {
                self::setDefaults();
            }

            self::addToHead();
            self::$og_added = TRUE;
        }
    }

    /**
     * Set default data
     */
    private static function setDefaults() {
        $settings = fusion_get_settings();

        self::$data = [
            'title'       => get_title(),
            'description' => str_replace("\n", ' ', strip_tags(htmlspecialchars_decode($settings['description']))),
            'url'         => $settings['siteurl'],
            'keywords'    => $settings['keywords'],
            'image'       => defined('THEME_ICON') ? THEME_ICON.'mstile-150x150.png' : $settings['siteurl'].'images/favicons/mstile-150x150.png',
            'site_name'   => $settings['sitename'],
            'type'        => 'website'
        ];
    }

    /**
     * Add meta tags to head
     */
    private static function addToHead() {
        foreach (self::$data as $key => $value) {
            if (self::$data != '') {
                add_to_head('<meta property="og:'.$key.'" content="'.$value.'">');
            }
        }
    }
}
