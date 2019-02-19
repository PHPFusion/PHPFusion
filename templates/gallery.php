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
defined('IN_FUSION') || exit;

if (!function_exists("render_gallery")) {
    function render_gallery($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        opentable($locale['gallery_400']);
        if (!empty($info['page_nav'])) {
            echo $info['page_nav'];
        }
        if (isset($info['item'])) {
            function render_photoAlbum(array $info = []) {
                // add admin edit.
                $gallery_settings = get_settings('gallery');
                $locale = fusion_get_locale();
                echo "<div class='panel panel-default'>\n";
                echo "<div class='panel-heading'>\n";
                echo "<a title='".$locale['gallery_430']."' href='".$info['album_link']['link']."'>\n<strong>".trim_text($info['album_link']['name'], 18)."</strong>\n</a>\n";
                echo "</div>\n";
                echo "<div class='overflow-hide album_thumbnail' style='background: #ccc; height: ".($gallery_settings['thumb_h'] - 15)."px'>\n";
                echo $info['image'];
                echo "</div>\n";
                echo "<div class='panel-body'>\n";
                echo "<span class='album_count'>".format_word($info['photo_rows'], $locale['gallery_461'])."</span>";
                echo "<br/><span><abbr title='".$locale['gallery_464'].showdate("shortdate", $info['album_datestamp'])."'><i class='fa fa-calendar text-lighter'></i></abbr> ".timer($info['album_datestamp']).'</span>';
                echo "</div>\n";

                if (!empty($info['album_edit']) && !empty($info['album_delete'])) {
                    echo "<div class='panel-footer text-center'><div class='btn-group btn-group-sm'>";
                    echo "<a class='btn btn-default' href='".$info['album_edit']['link']."' title='".$info['album_edit']['name']."'><i class='fa fa-edit fa-lg'></i></a>\n";
                    echo "<a class='btn btn-danger' href='".$info['album_delete']['link']."' title='".$info['album_delete']['name']."'><i class='fa fa-trash fa-lg'></i></a>\n";
                    echo '</div></div>';
                }
                echo "</div>\n";
            }

            echo "<div class='row m-t-20 m-b-20'>\n";
            foreach ($info['item'] as $data) {
                echo "<div class='col-xs-12 col-sm-6 col-md-4 col-lg-3'>\n";
                render_photoAlbum($data);
                echo "</div>\n";
            }
            echo "</div>\n";
        } else {
            echo "<div class='well m-t-20 m-b-20 text-center'>".$locale['gallery_406']."</div>\n";
        }
        if (!empty($info['page_nav'])) {
            echo $info['page_nav'];
        }
        closetable();
    }
}

/* Photo Category Page */
if (!function_exists('render_photo_album')) {
    function render_photo_album($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();

        add_to_css("
        .panel-default > .panel-image-wrapper {
            height: 120px;
            max-height: 120px;
            min-width: 100%;
            overflow: hidden;
        }
        .panel-default > .panel-image-wrapper img {
            margin-top: inherit !important;
            margin-left: inherit !important;
        }
        .panel-default > .panel-image-wrapper .thumb > a > img {
            display: block;
            width: 100%;
        }");

        opentable($info['album_title']);
        echo "<!--pre_album_info-->\n";

        if (!empty($info['album_stats']) || !empty($info['album_description'])) {
            echo "<div class='clearfix well'>\n";
            if (isset($info['album_stats'])) {
                echo "<span class='album_stats'>\n".$info['album_stats']."</span>\n";
            }
            if ($info['album_description']) {
                echo "<div class='m-t-20'>\n";
                echo "<!--photogallery_album_desc-->\n";
                echo "<span class='album_description'>\n".parse_textarea($info['album_description'], TRUE, TRUE, FALSE, '', TRUE)."</span><br/>\n";
                echo "</div>\n";
            }
            echo "</div>\n";
        }

        if (isset($info['page_nav'])) {
            echo $info['page_nav'];
        }
        echo "<!--sub_album_info-->";
        $counter = 0;
        function render_photo_items(array $info = []) {
            $locale = fusion_get_locale();
            echo "<div class='panel panel-default'>\n";
            echo "<div class='panel-image-wrapper' title='".$locale['gallery_450']."'>\n";
            echo $info['image'];
            echo "</div>\n";

            echo "<div class='panel-footer'>\n";
            echo '<div class="clearfix text-center">';
            echo "<span class='m-r-5'><i class='fa fa-eye fa-fw'></i> ".$info['photo_views']."</span>\n";
            if ($info['photo_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
                echo "<span class='m-r-5'><i class='fa fa-comment-o fa-fw'></i> <a href='".$info['photo_comments']['link']."'>".$info['photo_comments']['name']."</a>\n</span>\n";
            }

            if ($info['photo_allow_ratings'] && fusion_get_settings('ratings_enabled') == 1) {
                echo "<span><i class='fa fa-star-o fa-fw'></i> <a href='".$info['photo_ratings']['link']."'>".$info['photo_ratings']['name']."</a>\n</span>\n";
            }
            echo '</div>';

            echo '</div>';
            echo "<div class='panel-footer'>\n";

            echo "<small><strong>".$locale['gallery_434']."</strong></small> ";
            echo display_avatar($info, "15px", "m-l-5 m-r-5", "", "img-rounded");
            echo ' '.profile_link($info['user_id'], $info['user_name'], $info['user_status']);
            echo "<br/><abbr title='".$locale['gallery_464'].showdate("shortdate", $info['photo_datestamp'])."'>
            <i class='fa fa-calendar text-lighter'></i></abbr> ".timer($info['photo_datestamp'])."";
            if (!empty($info['photo_edit']) && !empty($info['photo_delete'])) {
                echo "</div>\n<div class='panel-footer'>\n";
                echo '<div class="btn-group center-x">';
                echo "<a class='btn btn-default btn-sm' href='".$info['photo_edit']['link']."' title='".$info['photo_edit']['name']."'><i class='fa fa-edit'></i></a>\n";
                echo "<a class='btn btn-danger btn-sm' href='".$info['photo_delete']['link']."' title='".$info['photo_delete']['name']."'><i class='fa fa-trash'></i></a>\n";
                echo '</div>';
            }
            echo "</div></div>\n";
        }

        if (isset($info['item'])) {
            echo "<div class='row m-t-20 m-b-20'>\n";
            foreach ($info['item'] as $data) {
                echo "<div class='col-xs-12 col-sm-6 col-md-4 col-lg-3'>\n";
                render_photo_items($data);
                echo "</div>\n";
                $counter++;
            }
            echo "</div>\n";
        } else {
            echo "<div class='well m-t-20 m-b-20 text-center'>".$locale['gallery_425']."</div>\n";
        }
        if (isset($info['page_nav'])) {
            echo $info['page_nav'];
        }
        closetable();
    }
}

if (!function_exists('render_photo')) {
    function render_photo($info) {
        $locale = fusion_get_locale();

        opentable($locale['gallery_450']);
        echo render_breadcrumbs();
        echo "<!--pre_photo-->";
        echo "<a target='_blank' href='".$info['photo_filename']."' class='photogallery_photo_link' title='".(!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename'])."'><!--photogallery_photo_".$_GET['photo_id']."-->";
        echo "<img class='img-responsive' style='margin:0 auto;' src='".$info['photo_filename']."' alt='".(!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename'])."' style='border:0px' class='photogallery_photo' />";
        echo "</a>\n";
        echo "<div class='clearfix'>\n";
        echo "<div class='btn-group pull-right m-t-20'>\n";
        echo isset($info['nav']['first']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['first']['link']."' title='".$info['nav']['first']['name']."'><i class='fa fa-angle-double-left'></i></a>\n" : '';
        echo isset($info['nav']['prev']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['prev']['link']."' title='".$info['nav']['prev']['name']."'><i class='fa fa-angle-left'></i></a>\n" : '';
        echo isset($info['nav']['next']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['next']['link']."' title='".$info['nav']['next']['name']."'><i class='fa fa-angle-right'></i></a>\n" : '';
        echo isset($info['nav']['last']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['last']['link']."' title='".$info['nav']['last']['name']."'><i class='fa fa-angle-double-right'></i></a>\n" : '';
        echo "</div>\n";
        echo "<div class='overflow-hide m-b-20'>\n";
        echo "<h2 class='photo_title'>".$info['photo_title']."</span>\n</h2>\n";
        echo "</div>\n";
        if ($info['photo_description']) {
            echo "<span class='photo_description list-group-item'>".parse_textarea($info['photo_description'], TRUE, TRUE, TRUE, '', TRUE)."</span>";
        }
        echo "<div class='list-group-item m-b-20'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
        echo "<strong>".$locale['gallery_434']."</strong> ".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."<br/>\n";
        echo "<strong>".$locale['gallery_403']."</strong> <abbr title='".showdate("shortdate", $info['photo_datestamp'])."'>".timer($info['photo_datestamp'])."</abbr><br/>";
        echo "<strong>".$locale['gallery_454']."</strong> ".$info['photo_size'][0]." x ".$info['photo_size'][1]." ".$locale['gallery_455']."<br/>\n";
        echo "<strong>".$locale['gallery_456']."</strong> ".$info['photo_byte'];
        echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
        echo "<strong>".$locale['gallery_457']."</strong> ".number_format($info['photo_views'])."<br/>\n";

        if ($info['photo_allow_ratings'] && fusion_get_settings('ratings_enabled') == 1) {
            echo "<strong>".$locale['gallery_437']."</strong> ".$info['photo_ratings']."<br/>\n";
        }

        if ($info['photo_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
            echo "<strong>".$locale['gallery_436']."</strong> ".$info['photo_comment']."<br/>\n";
        }
        echo "</div>\n</div>\n";
        echo "</div>\n</div>\n";
        echo "<!--sub_photo-->";
        echo $info['photo_show_comments'];
        echo $info['photo_show_ratings'];
        closetable();
    }
}
