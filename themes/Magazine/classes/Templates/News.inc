<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Magazine/classes/Templates/News.inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Magazine\Templates;

use Magazine\Core;
use \PHPFusion\News\NewsServer;
use \PHPFusion\OutputHandler;
use \PHPFusion\Panels;

class News extends Core {
    public static function displayMainNews($info) {
        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');

        $twig = twig_init(THEME.'twig');

        if (!empty($info['news_items'])) {
            foreach ($info['news_items'] as $id => $data) {
                $info['news_items'][$id]['image'] = !empty($data['news_image_optimized']) ? $data['news_image_optimized'] : get_image('imagenotfound');
                $info['news_items'][$id]['date'] = showdate(fusion_get_settings('newsdate'), $data['news_date']);
                $info['news_items'][$id]['text'] = fusion_first_words($data['news_news'], 20);
                $info['news_items'][$id]['profile_link'] = profile_link($data['user_id'], $data['user_name'], $data['user_status']);
            }
        }

        $context = [
            'get'         => ['type' => get('type'), 'cat_id' => get('cat_id'), 'archive' => get('archive')],
            'locale'      => fusion_get_locale('', MG_LOCALE),
            'breadcrumbs' => render_breadcrumbs(),
            'newsitems'   => $info['news_items'],
            'filters'     => $info['news_filter'],
            'categories'  => $info['news_categories'],
            'news_nav'    => $info['news_nav']
        ];

        echo $twig->render('bootstrap3/news/index.twig', $context);
    }

    public static function renderNewsItem($info) {
        $theme_settings = get_theme_settings('Magazine');

        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');

        $twig = twig_init(THEME.'twig');

        OutputHandler::addToHead('<link rel="stylesheet" type="text/css" href="'.INCLUDES.'jquery/colorbox/colorbox.css"/>');
        OutputHandler::addToFooter('<script type="text/javascript" src="'.INCLUDES.'jquery/colorbox/jquery.colorbox.js"></script>');
        OutputHandler::addToJQuery('
            $(".news-image-overlay").colorbox({
                transition: "elasic",
                height: "100%",
                width: "100%",
                maxWidth: "95%",
                maxHeight: "95%",
                scrolling: false,
                overlayClose: true,
                close: false,
                photo: true,
                onComplete: function(result) {$("#colorbox").live("click", function() {$(this).unbind("click");$.fn.colorbox.close();});}
           });
        ');

        $data = $info['news_item'];

        if ($data['news_image_src']) {
            $data['image'] = $data['news_image_src'];
            $data['image_width'] = $data['news_image_align'] == 'news-img-center' ? '100%' : '200px';
            $data['image_align'] = ($data['news_image_align'] == 'news-img-center' ? 'center-x m-b-10' : $data['news_image_align'] == 0) ? 'pull-left' : '';
        }

        if (!empty($data['news_gallery'])) {
            foreach ($data['news_gallery'] as $id => $image) {
                $data['news_gallery'][$id]['image'] = colorbox(IMAGES_N.$image['news_image'], 'Image #'.$id, TRUE);
            }
        }

        $social_links = NULL;
        if (!empty($theme_settings['social_share']) && $theme_settings['social_share'] == 1) {
            $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $social_links = social_media_links($url);
        }

        $data['profile_link'] = profile_link($data['user_id'], $data['user_name'], $data['user_status']);
        $data['date'] = showdate('newsdate', $data['news_datestamp']);
        $data['reads'] = number_format($data['news_reads']);


        if ($data['news_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
            $data['comments'] = $data['news_display_comments'];
        }

        if ($data['news_allow_ratings'] && fusion_get_settings('ratings_enabled') == 1) {
            $data['ratings'] = $data['news_display_ratings'];
        }

        // Popular News
        $result = dbquery("SELECT n.*, nc.*, ni.news_image, count(c.comment_item_id) AS news_comments
            FROM ".DB_NEWS." n
            LEFT JOIN ".DB_NEWS_CATS." nc ON n.news_cat=nc.news_cat_id
            LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=n.news_id
            LEFT JOIN ".DB_COMMENTS." c ON (c.comment_item_id = n.news_id AND c.comment_type = 'N')
            ".(multilang_table('NS') ? "WHERE ".in_group('news_language', LANGUAGE)." AND " : "WHERE ").groupaccess('news_visibility')." AND (news_start='0'||news_start<='".TIME."')
            AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'
            GROUP BY n.news_id
            ORDER BY n.news_reads DESC, n.news_datestamp ASC
            LIMIT 6
        ");

        $popular_news = [];
        if (dbrows($result)) {
            while ($item = dbarray($result)) {
                $popular_news[] = [
                    'image' => \PHPFusion\News\News::get_NewsImage($item),
                    'name'  => $item['news_subject'],
                    'link'  => INFUSIONS.'news/news.php?readmore='.$item['news_id'],
                    'date'  => showdate('newsdate', $item['news_datestamp']),
                    'reads' => $item['news_reads'],
                    'category_name' => $item['news_cat_name'],
                    'category_link' => INFUSIONS.'news/news.php?cat_id='.$item['news_cat_id']
                ];
            }
        }

        $context = [
            'locale'       => fusion_get_locale('', MG_LOCALE),
            'breadcrumbs'  => render_breadcrumbs(),
            'data'         => $data,
            'filters'      => $info['news_filter'],
            'categories'   => $info['news_categories'],
            'social_links' => $social_links,
            'popular_news' => $popular_news
        ];

        echo $twig->render('bootstrap3/news/item.twig', $context);
    }
}
