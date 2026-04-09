<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: OpenGraphWiki.php
| Author: RobiNN
+--------------------------------------------------------*/

class OpenGraphWiki extends \PHPFusion\OpenGraph {
    public static function ogWiki($wiki_id = 0) {
        $settings = fusion_get_settings();
        $info = [];

        $result = dbquery("SELECT wiki_id, wiki_name, wiki_description FROM ".DB_WIKI." WHERE wiki_id = :wiki_id", [':wiki_id' => $wiki_id]);

        if (dbrows($result)) {
            $data = dbarray($result);
            $info['url'] = $settings['siteurl'].'infusions/wiki/documentation.php?page_id='.$wiki_id;
            $info['keywords'] = $settings['keywords'];
            $info['title'] = $data['wiki_name'].' - '.$settings['sitename'];
            $info['description'] = $settings['description'];
            $info['type'] = 'website';
            $info['image'] = defined('THEME_ICON') ? THEME_ICON.'mstile-150x150.png' : $settings['siteurl'].'images/favicons/mstile-150x150.png';
        }

        self::setValues($info);
    }

    public static function ogWikiCat($cat_id = 0) {
        $settings = fusion_get_settings();
        $info = [];

        $result = dbquery("SELECT wiki_cat_id, wiki_cat_name, wiki_cat_description FROM ".DB_WIKI_CATS." WHERE wiki_cat_id = :wiki_cat_id", [':wiki_cat_id' => $cat_id]);

        if (dbrows($result)) {
            $data = dbarray($result);
            $info['url'] = $settings['siteurl'].'infusions/wiki/documentation.php?page_id='.$cat_id;
            $info['keywords'] = $settings['keywords'];
            $info['title'] = $data['wiki_cat_name'].' - '.$settings['sitename'];
            $info['description'] = $settings['description'];
            $info['type'] = 'website';
            $info['image'] = defined('THEME_ICON') ? THEME_ICON.'mstile-150x150.png' : $settings['siteurl'].'images/favicons/mstile-150x150.png';
        }

        self::setValues($info);
    }
}
