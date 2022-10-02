<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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

        $result = dbquery("SELECT download_title, download_description_short, download_keywords, download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_id = :download", [':download' => $download_id]);
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['title'] = $data['download_title'].' - '.$settings['sitename'];
            $info['description'] = !empty($data['download_description_short']) ? fusion_first_words(strip_tags(html_entity_decode($data['download_description_short'])), 50) : $settings['description'];
            $info['url'] = $settings['siteurl'].'infusions/downloads/downloads.php?download_id='.$download_id;
            $info['keywords'] = !empty($data['download_keywords']) ? $data['download_keywords'] : $settings['keywords'];
            $info['type'] = 'article';

            if (!empty($data['download_image_thumb']) && file_exists(INFUSIONS.'downloads/images/'.$data['download_image_thumb'])) {
                $info['image'] = $settings['siteurl'].'infusions/downloads/images/'.$data['download_image_thumb'];
            } else if (!empty($data['download_image']) && file_exists(INFUSIONS.'downloads/images/'.$data['download_image'])) {
                $info['image'] = $settings['siteurl'].'infusions/downloads/images/'.$data['download_image'];
            }
        }

        self::setValues($info);
    }

    public static function ogDownloadCat($cat_id = 0) {
        $settings = fusion_get_settings();
        $info = [];

        $result = dbquery("SELECT download_cat_name, download_cat_description FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id=:cat_id", [':cat_id' => $cat_id]);
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['title'] = $data['download_cat_name'].' - '.$settings['sitename'];
            $info['description'] = !empty($data['download_cat_description']) ? fusion_first_words(strip_tags(html_entity_decode($data['download_cat_description'])), 50) : $settings['description'];
            $info['url'] = $settings['siteurl'].'infusions/downloads/downloads.php?cat_id='.$cat_id;
            $info['keywords'] = $settings['keywords'];
        }

        self::setValues($info);
    }
}
