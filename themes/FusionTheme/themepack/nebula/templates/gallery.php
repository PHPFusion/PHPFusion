<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: global/photos.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace ThemePack\Nebula\Templates;
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

class Gallery {

    private static $gallery_settings = [];

    public static function render_gallery(array $info = array()) {
        $locale = fusion_get_locale();
        $html = render_breadcrumbs();
        $html .= fusion_get_function('opentable', $locale['400']);
        add_to_head("<link rel='stylesheet' href='".THEME."themepack/nebula/css/gallery.css' type='text/css' />");
        self::$gallery_settings = get_settings('gallery');
        if (!empty($info['page_nav'])) {
            $html .= $info['page_nav'];
        }
        if (isset($info['item'])) {
            $html .= "<div class='row m-t-20 m-b-20'>\n";
            foreach ($info['item'] as $data) {
                $html .= "<div class='gallery_item'>\n";
                $html .= self::render_photoAlbum($data);
                $html .= "</div>\n";
            }
            $html .= "</div>\n";
        } else {
            $html .= "<div class='well m-t-20 m-b-20 text-center'>".$locale['406']."</div>\n";
        }
        if (!empty($info['page_nav'])) {
            $html .= $info['page_nav'];
        }
        $html .= fusion_get_function('closetable');

        return $html;
    }

    public static function render_photo_album(array $info = array()) {
        add_to_head("<link rel='stylesheet' href='".THEME."themepack/nebula/css/gallery.css' type='text/css' />");
        $locale = fusion_get_locale();
        $html = render_breadcrumbs();
        self::$gallery_settings = get_settings('gallery');
        $html .= "<!--pre_album_info-->\n";
        $html .= "<div class='clearfix'>\n";
        $html .= "<h3 class='spacer-sm'>".$info['album_title']."</h3>\n";
        if ($info['album_description']) {
            $html .= "<div class='spacer-xs'>\n";
            $html .= "<span class='album_description'>\n".parse_textarea($info['album_description'], TRUE, TRUE, TRUE, '', TRUE)."</span><br/>\n";
            $html .= "</div>\n";
        }
        if (isset($info['album_stats'])) {
            $html .= "<span class='album_stats'>\n".$info['album_stats']."</span>\n";
        }

        $html .= "</div>\n";
        $html .= "<hr/>\n";
        if (isset($info['page_nav'])) {
            $html .= $info['page_nav'];
        }
        $html .= "<!--sub_album_info-->";
        $counter = 0;
        if (isset($info['item'])) {
            $html .= "<div class='row m-t-20 m-b-20'>\n";
            foreach ($info['item'] as $data) {
                $html .= "<div class='gallery_item'>\n";
                $html .= self::render_photo_items($data);
                $html .= "</div>\n";
                $counter++;
            }
            $html .= "</div>\n";
        } else {
            $html .= "<div class='well m-t-20 m-b-20 text-center'>".$locale['425']."</div>\n";
        }
        if (isset($info['page_nav'])) {
            $html .= $info['page_nav'];
        }

        return $html;
    }

    public static function render_photo(array $info = array()) {
        $locale = fusion_get_locale();
        $html = opentable('opentable', $locale['450']);
        $html .= render_breadcrumbs();
        self::$gallery_settings = get_settings('gallery');
        $html .= "<!--pre_photo-->";
        $html .= "<a target='_blank' href='".$info['photo_filename']."' class='photogallery_photo_link' title='".(!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename'])."'><!--photogallery_photo_".$_GET['photo_id']."-->";
        $html .= "<img class='img-responsive' style='margin:0 auto;' src='".$info['photo_filename']."' alt='".(!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename'])."' style='border:0px' class='photogallery_photo' />";
        $html .= "</a>\n";
        $html .= "<div class='clearfix'>\n";
        $html .= "<div class='btn-group pull-right m-t-20'>\n";
        $html .= isset($info['nav']['first']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['first']['link']."' title='".$info['nav']['first']['name']."'><i class='fa fa-angle-double-left'></i></a>\n" : '';
        $html .= isset($info['nav']['prev']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['prev']['link']."' title='".$info['nav']['prev']['name']."'><i class='fa fa-angle-left'></i></a>\n" : '';
        $html .= isset($info['nav']['next']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['next']['link']."' title='".$info['nav']['next']['name']."'><i class='fa fa-angle-right'></i></a>\n" : '';
        $html .= isset($info['nav']['last']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['last']['link']."' title='".$info['nav']['last']['name']."'><i class='fa fa-angle-double-right'></i></a>\n" : '';
        $html .= "</div>\n";
        $html .= "<div class='overflow-hide m-b-20'>\n";
        $html .= "<h2 class='photo_title'>".$info['photo_title']."</span>\n</h2>\n";
        $html .= "</div>\n";
        if ($info['photo_description']) {
            $html .= "<span class='photo_description list-group-item'>".parse_textarea($info['photo_description'], TRUE, TRUE, TRUE, '', TRUE)."</span>";
        }
        $html .= "<div class='list-group-item m-b-20'>\n";
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
        $html .= "<strong>".$locale['434']."</strong> ".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."<br/>\n";
        $html .= "<strong>".$locale['403']."</strong> <abbr title='".showdate("shortdate", $info['photo_datestamp'])."'>".timer($info['photo_datestamp'])."</abbr><br/>";
        $html .= "<strong>".$locale['454']."</strong> ".$info['photo_size'][0]." x ".$info['photo_size'][1]." ".$locale['455']."<br/>\n";
        $html .= "<strong>".$locale['456']."</strong> ".$info['photo_byte'];
        $html .= "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
        $html .= "<strong>".$locale['457']."</strong> ".number_format($info['photo_views'])."<br/>\n";
        $html .= "<strong>".$locale['437']."</strong> ".$info['photo_ratings']."<br/>\n";
        $html .= "<strong>".$locale['436']."</strong> ".$info['photo_comment']."<br/>\n";
        $html .= "</div>\n</div>\n";
        $html .= "</div>\n</div>\n";
        $html .= "<!--sub_photo-->";
        $html .= $info['photo_show_comments'];
        $html .= $info['photo_show_ratings'];
        $html .= fusion_get_function('closetable');

        return $html;
    }

    private static function render_photoAlbum(array $info = array()) {
        // add admin edit.
        $locale = fusion_get_locale();
        $html = "<div class='panel panel-default'>\n";
        $html .= "<div class='panel-image-wrapper'>\n";
        $html .= "<div class='center-xy'>".$info['image']."</div>\n";
        $html .= "</div>\n";
        $html .= "<div class='panel-body'>\n";
        if (!empty($info['album_edit']) && !empty($info['album_delete'])) {
            $html .= "<div class='pull-right dropdown'>\n";
            $html .= "<a href='#' data-toggle='dropdown'><i class='fa fa-cog'></i></a>\n";
            $html .= "<ul class='dropdown-menu'>\n";
            $html .= "<li><a href='".$info['album_edit']['link']."' title='".$info['album_edit']['name']."'><i class='fa fa-edit fa-lg'></i> ".$info['album_edit']['name']."</a>\n</li>";
            $html .= "<li><a href='".$info['album_delete']['link']."' title='".$info['album_delete']['name']."'><i class='fa fa-trash fa-lg'></i> ".$info['album_delete']['name']."</a>\n</li>";
            $html .= "</ul>\n</div>\n";
        }
        $html .= "<div class='overflow-hide'>\n";
        $html .= "<a class='album_link' title='".$locale['430']."' href='".$info['album_link']['link']."'>\n<h4 class='album_title'><strong>".trim_text($info['album_link']['name'], 18)."</strong></h4>\n</a>\n";
        $html .= "</div>\n";
        $html .= "<span class='album_count m-r-10'>".format_word($info['photo_rows'], $locale['461'])."</span>";
        $html .= "<small>".timer($info['album_datestamp'])."</small>\n";
        $html .= "</div>\n";
        $html .= "</div>\n";

        return $html;
    }

    private static function render_photo_items(array $info = array()) {
        $locale = fusion_get_locale();
        $html = "<div class='panel panel-default'>\n";
        $html .= "<div class='panel-image-wrapper'>\n";
        $html .= "<div class='author'><strong>".$locale['434']."</strong> ".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</div>\n";
        $html .= "<div class='center-xy'>".$info['image']."</div>\n";
        $html .= "</div>\n";
        $html .= "<div class='panel-body clearfix'>\n";

        if (!empty($info['photo_edit']) && !empty($info['photo_delete'])) {
            $html .= "<div class='pull-right dropdown'>\n";
            $html .= "<a href='#' data-toggle='dropdown'><i class='fa fa-cog'></i></a>\n";
            $html .= "<ul class='dropdown-menu'>\n";
            $html .= "<li><a href='".$info['photo_edit']['link']."' title='".$info['photo_edit']['name']."'><i class='fa fa-edit fa-lg'></i> ".$info['photo_edit']['name']."</a></li>\n";
            $html .= "<li><a href='".$info['photo_delete']['link']."' title='".$info['photo_delete']['name']."'><i class='fa fa-trash fa-lg'></i> ".$info['photo_delete']['name']."</a></li>\n";
            $html .= "</ul>\n</div>\n";
        }

        $html .= "<span class='m-r-5'><i class='fa fa-eye fa-fw'></i>".$info['photo_views']."</span>\n";
        if (isset($info['photo_comments'])) {
            $html .= "<span class='m-r-5'><i class='fa fa-comment-o fa-fw'></i><a href='".$info['photo_comments']['link']."'>".$info['photo_comments']['name']."</a>\n</span>\n";
        }
        if (isset($info['photo_ratings']['name'])) {
            $html .= "<span><i class='fa fa-star-o fa-fw'></i><a href='".$info['photo_ratings']['link']."'>".$info['photo_ratings']['name']."</a>\n</span>\n";
        }
        $html .= "<br/><small><span>".timer($info['photo_datestamp'])."</span></small>\n<br/>\n";
        $html .= "</div></div>\n";

        return $html;
    }
}
