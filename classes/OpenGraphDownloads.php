<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: OpenGraphDownloads.php
| Author: Chubatyj Vitalij (Rizado)
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

class OpenGraphDownloads extends OpenGraph {
    public static function ogDownload($download_id = 0) {
        $settings = fusion_get_settings();
        $info = [];

        $result = dbquery("SELECT `download_title`, `download_description_short`, `download_keywords`, `download_image_thumb` FROM `".DB_DOWNLOADS."` WHERE `download_id` = :download", [':download' => $download_id]);
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['url'] = $settings['siteurl'].'infusions/downloads/downloads.php?download_id='.$download_id;
            $info['keywords'] = $data['download_keywords'] ? $data['download_keywords'] : $settings['keywords'];
            $info['title'] = $data['download_title'].' - '.$settings['sitename'];
            $info['description'] = $data['download_description_short'] ? fusion_first_words(strip_tags(html_entity_decode($data['download_description_short'])), 50) : $settings['description'];
            $info['type'] = 'article';
            if (!empty($data['download_image_thumb'])) {
                $info['image'] = $settings['siteurl'].'infusions/downloads/images/'.$data['download_image_thumb'];
            } else {
                $info['image'] = defined('THEME_ICON') ? THEME_ICON.'mstile-150x150.png' : $settings['siteurl'].'images/favicons/mstile-150x150.png';
            }
        }

        OpenGraphDownloads::setValues($info);
    }

    public static function ogDownloadCat($cat_id = 0) {
        $settings = fusion_get_settings();
        $info = [];
        $result = dbquery("SELECT download_cat_name, download_cat_description FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id=:cat_id", [':cat_id' => $cat_id]);
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['url'] = $settings['siteurl'].'infusions/downloads/downloads.php?cat_id='.$cat_id;
            $info['keywords'] = $settings['keywords'];
            $info['title'] = $data['download_cat_name'].' - '.$settings['sitename'];
            $info['description'] = $data['download_cat_description'] ? fusion_first_words(strip_tags(html_entity_decode($data['download_cat_description'])), 50) : $settings['description'];
            $info['type'] = 'website';
            $info['image'] = defined('THEME_ICON') ? THEME_ICON.'mstile-150x150.png' : $settings['siteurl'].'images/favicons/mstile-150x150.png';
        }
        OpenGraphDownloads::setValues($info);
    }
}
