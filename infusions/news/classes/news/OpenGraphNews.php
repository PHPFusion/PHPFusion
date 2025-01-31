<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: OpenGraphNews.php
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

class OpenGraphNews extends OpenGraph {
    public static function ogNews($news_id = 0) {
        $settings = fusion_get_settings();
        $info = [];

        $result = dbquery("SELECT ns.news_subject, ns.news_news, ns.news_keywords, ni.news_image, ni.news_image_t1, ni.news_image_t2
            FROM ".DB_NEWS." AS ns
            LEFT JOIN ".DB_NEWS_IMAGES." AS ni ON ni.news_id=ns.news_id
            WHERE ns.news_id = :newsid
        ", [':newsid' => $news_id]);
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['title'] = $data['news_subject'].' - '.$settings['sitename'];
            $info['description'] = !empty($data['news_news']) ? fusion_first_words(strip_tags(html_entity_decode($data['news_news'])), 50) : $settings['description'];
            $info['url'] = $settings['siteurl'].'infusions/news/news.php?readmore='.$news_id;
            $info['keywords'] = !empty($data['news_keywords']) ? $data['news_keywords'] : $settings['keywords'];
            $info['type'] = 'article';

            if (!empty($data['news_image_t1']) && file_exists(INFUSIONS.'news/images/thumbs/'.$data['news_image_t1'])) {
                $info['image'] = $settings['siteurl'].'infusions/news/images/thumbs/'.$data['news_image_t1'];
            } else if (!empty($data['news_image_t2']) && file_exists(INFUSIONS.'news/images/thumbs/'.$data['news_image_t2'])) {
                $info['image'] = $settings['siteurl'].'infusions/news/images/thumbs/'.$data['news_image_t2'];
            } else if (!empty($data['news_image']) && file_exists(INFUSIONS.'news/images/'.$data['news_image'])) {
                $info['image'] = $settings['siteurl'].'infusions/news/images/'.$data['news_image'];
            }
        }

        self::setValues($info);
    }

    public static function ogNewsCat($cat_id = 0) {
        $settings = fusion_get_settings();
        $info = [];

        $result = dbquery("SELECT news_cat_name, news_cat_image FROM ".DB_NEWS_CATS." WHERE news_cat_id = :catid", [':catid' => $cat_id]);
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['title'] = $data['news_cat_name'].' - '.$settings['sitename'];
            $info['description'] = $settings['description'];
            $info['url'] = $settings['siteurl'].'infusions/news/news.php?cat_id='.$cat_id;
            $info['keywords'] = $settings['keywords'];

            if (!empty($data['news_cat_image']) && file_exists(IMAGES_NC.$data['news_cat_image'])) {
                $info['image'] = $settings['siteurl'].'infusions/news/news_cats/'.$data['news_cat_image'];
            }
        }

        self::setValues($info);
    }
}
