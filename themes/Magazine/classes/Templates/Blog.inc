<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Magazine/classes/Templates/Blog.inc
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
use \PHPFusion\Panels;

class Blog extends Core {
    public static function render_main_blog($info) {
        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');

        if (isset($_GET['readmore']) && !empty($info['blog_item'])) {
            self::display_blog_item($info);
        } else {
            self::display_blog_index($info);
        }
    }

    private static function display_blog_index($info) {
        $twig = twig_init();

        add_to_jquery('$("#blog-archive").submenupicker();');

        if (!empty($info['blog_item'])) {
            foreach ($info['blog_item'] as $id => $data) {
                if (!empty($data['blog_lowRes_image_path']) || !empty($data['blog_cat_image'])) {
                    if ($data['blog_lowRes_image_path'] && file_exists($data['blog_lowRes_image_path'])) {
                        $info['blog_item'][$id]['image'] = $data['blog_lowRes_image_path'];
                    } else if ($data['blog_cat_image']) {
                        $info['blog_item'][$id]['image'] = INFUSIONS.'blog/blog_cats/'.$data['blog_cat_image'];
                    } else {
                        $info['blog_item'][$id]['image'] = get_image('imagenotfound');
                    }
                } else {
                    $info['blog_item'][$id]['image'] = get_image('imagenotfound');
                }

                $cats = explode(', ', $data['blog_category_link']);
                foreach ($cats as $cat) {
                    $info['blog_item'][$id]['cats'] = str_replace('<a href=', '<a class="badge m-l-5" href=', $cat);
                }

                $info['blog_item'][$id]['avatar'] = display_avatar($data, '40px', '', TRUE, 'img-circle');

                if (fusion_get_settings('comments_enabled') && $data['blog_allow_comments']) {
                    $info['blog_item'][$id]['comments'] = '<a href="'.INFUSIONS.'blog/blog.php?readmore='.$id.'&amp;cat_id='.$data['blog_cat'].'#comments"><i class="fa fa-comment-o"></i> '.$data['blog_comments'].'</a>';
                }
                if (fusion_get_settings('ratings_enabled') && $data['blog_allow_ratings']) {
                    $info['blog_item'][$id]['ratings'] = ' &middot; <span><i class="fa fa-star-o"></i> '.$data['blog_count_votes'].'</span>';
                }
                $info['blog_item'][$id]['reads'] = ' &middot; <span><i class="fa fa-eye"></i> '.$data['blog_reads'].'</span> &middot; ';
                $info['blog_item'][$id]['time'] = timer($data['blog_datestamp']);

            }
        }

        $tpl = [
            'get'         => ['cat_id' => get('cat_id'), 'archive' => get('archive')],
            'locale'      => fusion_get_locale(),
            'breadcrumbs' => render_breadcrumbs(),
            'blogitems'   => $info['blog_item'],
            'filters'     => $info['blog_filter'],
            'categories'  => $info['blog_categories'],
            'authors'     => $info['blog_author'],
            'archive'     => $info['blog_archive'],
            'blog_nav'    => $info['blog_nav']
        ];

        echo $twig->render('blog/index.twig', $tpl);
    }

    public static function display_blog_item($info) {
        add_to_head('<link rel="stylesheet" type="text/css" href="'.INCLUDES.'jquery/colorbox/colorbox.css"/>');
        add_to_head('<script type="text/javascript" src="'.INCLUDES.'jquery/colorbox/jquery.colorbox.js"></script>');
        add_to_jquery('$(".blog-image-overlay").colorbox({
            transition: "elasic",
            height: "100%",
            width: "100%",
            maxWidth: "98%",
            maxHeight: "98%",
            scrolling: false,
            overlayClose: true,
            close: false,
            photo: true,
            onComplete: function(result) {
                $("#colorbox").live("click", function() {
                    $(this).unbind("click");
                    $.fn.colorbox.close();
                });
            }
        });');

        $twig = twig_init(THEME.'twig', TRUE);

        $cats = explode(', ', $info['blog_item']['blog_category_link']);
        foreach ($cats as $cat) {
            $info['blog_item']['cats'] = str_replace('<a href=', '<a class="badge m-l-5" href=', $cat);
        }

        $tpl = [
            'get'         => ['cat_id' => get('cat_id'), 'archive' => get('archive')],
            'locale'      => fusion_get_locale(),
            'breadcrumbs' => render_breadcrumbs(),
            'data'        => $info['blog_item'],
            'filters'     => $info['blog_filter'],
            'categories'  => $info['blog_categories'],
            'authors'     => $info['blog_author'],
            'archive'     => $info['blog_archive']
        ];

        echo $twig->render('blog/item.twig', $tpl);
    }
}
