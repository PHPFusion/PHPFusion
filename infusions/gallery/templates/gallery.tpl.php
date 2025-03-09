<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: gallery.tpl.php
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
defined('IN_FUSION') || exit;

if (!function_exists('render_gallery')) {
    function render_gallery($info) {
        $locale = fusion_get_locale();

        opentable($locale['gallery_400']);
        echo '<div class="gallery-index">';
        echo render_breadcrumbs();

        if (!empty($info['page_nav'])) {
            echo $info['page_nav'];
        }
        if (isset($info['item'])) {
            echo '<div class="row equal-height">';
            foreach ($info['item'] as $data) {
                echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 gallery-index-item">';
                    echo '<div class="panel panel-default">';
                        echo $data['image'];

                        echo '<div class="panel-body text-center">';
                            echo '<a href="'.$data['album_link']['link'].'"><h4 class="m-t-0 m-b-5">'.$data['album_link']['name'].'</h4></a>';
                            echo '<div class="album-meta">';
                                echo format_word($data['photo_rows'], $locale['gallery_461']);
                                echo ' &middot; '.showdate('shortdate', $data['album_datestamp']);
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="well m-t-20 m-b-20 text-center">'.$locale['gallery_406'].'</div>';
        }
        if (!empty($info['page_nav'])) {
            echo $info['page_nav'];
        }
        echo '</div>';
        closetable();
    }
}

if (!function_exists('render_photo_album')) {
    function render_photo_album($info) {
        $locale = fusion_get_locale();

        opentable($info['album_title']);
        echo '<div class="gallery-item">';
        echo render_breadcrumbs();

        if (!empty($info['album_edit']) && !empty($info['album_delete'])) {
            echo '<div class="btn-group btn-group-sm m-b-20">';
                echo '<a class="btn btn-primary" href="'.$info['album_edit']['link'].'" title="'.$info['album_edit']['name'].'"><i class="fas fa-edit"></i></a>';
                echo '<a class="btn btn-danger" href="'.$info['album_delete']['link'].'" title="'.$info['album_delete']['name'].'"><i class="fas fa-trash"></i></a>';
            echo '</div>';
        }

        if (!empty($info['album_stats']) || !empty($info['album_description'])) {
            echo '<div class="well">';
            if (isset($info['album_stats'])) {
                echo '<span class="album-stats">'.$info['album_stats'].'</span>';
            }
            if ($info['album_description']) {
                echo '<span class="album-description">'.$info['album_description'].'</span>';
            }
            echo '</div>';
        }

        if (isset($info['page_nav'])) {
            echo $info['page_nav'];
        }

        if (!empty($info['item'])) {
            echo '<div class="row equal-height">';
            foreach ($info['item'] as $data) {
                echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 gallery-album-item">';
                    echo '<div class="panel panel-default">';
                        echo $data['image'];

                        echo '<div class="panel-body text-center">';
                            echo '<a href="'.$data['photo_link']['link'].'"><h4 class="m-t-0 m-b-5">'.$data['photo_title'].'</h4></a>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="well m-t-20 m-b-20 text-center">'.$locale['gallery_425'].'</div>';
        }

        if (isset($info['page_nav'])) {
            echo $info['page_nav'];
        }

        echo '</div>';
        closetable();
    }
}

if (!function_exists('render_photo')) {
    function render_photo($info) {
        $locale = fusion_get_locale();

        opentable($locale['gallery_450']);
        echo '<div class="gallery-photo">';
        echo render_breadcrumbs();

        if (!empty($info['photo_edit']) && !empty($info['photo_delete'])) {
            echo '<div class="btn-group btn-group-sm m-b-20">';
            echo '<a class="btn btn-primary" href="'.$info['photo_edit']['link'].'" title="'.$info['photo_edit']['name'].'"><i class="fas fa-edit"></i></a>';
            echo '<a class="btn btn-danger" href="'.$info['photo_delete']['link'].'" title="'.$info['photo_delete']['name'].'"><i class="fas fa-trash"></i></a>';
            echo '</div>';
        }

        $photo_title = (!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename']);

        add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css'>");
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
        add_to_jquery("$('a.photogallery_photo_link').colorbox({width:'80%', height:'80%', photo:true});");

        echo '<a class="photogallery_photo_link" href="'.$info['photo_filename'].'" target="_bank" title="'.$photo_title.'">';
            echo '<img class="img-responsive" src="'.$info['photo_filename'].'" alt="'.$photo_title.'">';
        echo '</a>';

        if (!empty($info['nav'])) {
            $nav = $info['nav'];
            echo '<div class="clearfix m-t-20">';
                echo '<div class="btn-group pull-right">';
                    echo isset($nav['first']) ? '<a class="btn btn-default btn-sm" href="'.$nav['first']['link'].'" title="'.$nav['first']['name'].'"><i class="fa fa-angle-double-left"></i></a>' : '';
                    echo isset($nav['prev']) ? '<a class="btn btn-default btn-sm" href="'.$nav['prev']['link'].'" title="'.$nav['prev']['name'].'"><i class="fa fa-angle-left"></i></a>' : '';
                    echo isset($nav['next']) ? '<a class="btn btn-default btn-sm" href="'.$nav['next']['link'].'" title="'.$nav['next']['name'].'"><i class="fa fa-angle-right"></i></a>': '';
                    echo isset($nav['last']) ? '<a class="btn btn-default btn-sm" href="'.$nav['last']['link'].'" title="'.$nav['last']['name'].'"><i class="fa fa-angle-double-right"></i></a>' : '';
                echo '</div>';
            echo '</div>';
        }

        echo '<h3>'.$info['photo_title'].'</h3>';

        if (!empty($info['photo_description'])) {
            echo '<div class="photo-description">'.$info['photo_description'].'</div>';
        }

        echo '<div class="m-t-20 m-b-20">';
            echo '<span class="m-r-10"><i class="fas fa-user"></i> '.profile_link($info['user_id'], $info['user_name'], $info['user_status']).'</span>';
            echo '<span class="m-r-10"><i class="fas fa-clock"></i> '.showdate('shortdate', $info['photo_datestamp']).'</span>';
            echo '<span><i class="fas fa-eye"></i> '.number_format($info['photo_views']).'</span>';
        echo '</div>';

        echo $info['photo_show_comments'];
        echo $info['photo_show_ratings'];

        echo '</div>';
        closetable();
    }
}
