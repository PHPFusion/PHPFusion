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
    public static function renderMainBlog($info) {
        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');

        if (isset($_GET['readmore']) && !empty($info['blog_item'])) {
            self::displayBlogItem($info);
        } else {
            self::displayBlogIndex($info);
        }
    }

    private static function displayBlogIndex($info) {
        $twig = twig_init(THEME.'twig');

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

        $context = [
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

        echo $twig->render('bootstrap3/blog/index.twig', $context);
    }

    public static function displayBlogItem($info) {
        $twig = twig_init(THEME.'twig');

        $context = [
            'get'         => ['cat_id' => get('cat_id'), 'archive' => get('archive')],
            'locale'      => fusion_get_locale(),
            'breadcrumbs' => render_breadcrumbs(),
            'data'        => $info['blog_item'],
            'filters'     => $info['blog_filter'],
            'categories'  => $info['blog_categories'],
            'authors'     => $info['blog_author'],
            'archive'     => $info['blog_archive']
        ];

        echo $twig->render('bootstrap3/blog/item.twig', $context);
    }
}
