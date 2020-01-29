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
        $locale = fusion_get_locale();

        echo '<div class="news-header">';
            echo '<h1>'.$locale['blog_1000'].'</h1>';

            echo render_breadcrumbs();
        echo '</div>';

        echo '<div class="card">';
            if (!empty($info['blog_item'])) {
                echo '<div class="clearfix m-b-20">';
                    echo '<span class="m-r-10">';
                    echo '<strong class="text-dark">'.$locale['show'].':</strong> ';
                    $i = 0;
                    foreach ($info['blog_filter'] as $filter_key => $filter) {
                        $filter_active = isset($_GET['type']) && $_GET['type'] === $filter_key ? ' text-dark' : '';
                        echo '<a href="'.$filter['link'].'" class="display-inline'.$filter_active.' m-r-10">'.$filter['title'].'</a>';
                        $i++;
                    }
                    echo '</span>';

                    if (!empty($info['blog_categories'])) {
                        echo '<div class="dropdown display-inline m-r-20">';
                            echo '<a href="#" id="blog-cats" data-toggle="dropdown">'.$locale['blog_1003'].' <span class="caret"></span></a>';
                            echo '<ul class="dropdown-menu" aria-labelledby="blog-cats">';
                                foreach ($info['blog_categories'][0] as $id => $data) {
                                    $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $id ? ' class="active"' : '';
                                    echo '<li'.$active.'><a href="'.INFUSIONS.'blog/blog.php?cat_id='.$id.'">'.$data['blog_cat_name'].'</a></li>';

                                    if ($id != 0 && $info['blog_categories'] != 0) {
                                        foreach ($info['blog_categories'] as $sub_cats_id => $sub_cats) {
                                            foreach ($sub_cats as $sub_cat_id => $sub_cat_data) {
                                                if (!empty($sub_cat_data['blog_cat_parent']) && $sub_cat_data['blog_cat_parent'] == $id) {
                                                    $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $sub_cat_id ? ' class="active"' : '';
                                                    echo '<li'.$active.'><a href="'.INFUSIONS.'blog/blog.php?cat_id='.$sub_cat_id.'">'.$sub_cat_data['blog_cat_name'].'</a></li>';
                                                }
                                            }
                                        }
                                    }
                                }
                            echo '</ul>';
                        echo '</div>';
                    }

                    if (!empty($info['blog_author'])) {
                        echo '<div class="dropdown display-inline m-r-20">';
                            echo '<a href="#" id="blog-authors" data-toggle="dropdown">'.$locale['blog_1005'].' <span class="caret"></span></a>';
                            echo '<ul class="dropdown-menu" aria-labelledby="blog-authors">';
                                foreach ($info['blog_author'] as $author_id => $author_info) {
                                    echo '<li'.($author_info['active'] ? ' class="active"' : '').'><a href="'.$author_info['link'].'">'.$author_info['title'].' <span class="badge m-l-10">'.$author_info['count'].'</span></a></li>';
                                }
                            echo '</ul>';
                        echo '</div>';
                    }

                    if (!empty($info['blog_archive'])) {
                        add_to_jquery('$("#blog-archive").submenupicker();');
                        echo '<div class="dropdown display-inline">';
                            echo '<a id="blog-archive" href="#" class="dropdown-toggle" data-toggle="dropdown" data-submenu>'.$locale['blog_1004'].' <span class="caret"></span></a>';
                            echo '<ul class="dropdown-menu" aria-labelledby="blog-archive">';
                                foreach ($info['blog_archive'] as $year => $archive_data) {
                                    $active = $year == date('Y') ? 'text-dark ' : '';
                                    $collaped_ = isset($_GET['archive']) && $_GET['archive'] == $year ? 'active strong ' : '';
                                    echo '<li class="'.$collaped_.'dropdown-submenu">';
                                        echo '<a class="'.$active.'dropdown-toggle" data-toggle="dropdown" href="#">'.$year.'</a>';

                                        echo '<ul class="dropdown-menu">';
                                            foreach ($archive_data as $month => $a_data) {
                                                echo '<li'.($a_data['active'] ? ' class="active string"' : '').'><a href="'.$a_data['link'].'">'.$a_data['title'].' <span class="badge">'.$a_data['count'].'</span></a></li>';
                                            }
                                        echo '</ul>';
                                    echo '</li>';
                                }
                            echo '</ul>';
                        echo '</div>';
                    }
                echo '</div>';

                echo '<div class="row equal-height">';
                foreach ($info['blog_item'] as $blog_id => $data) {
                    echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 m-b-20">';
                        echo '<div class="post-item">';
                            if (!empty($data['blog_lowRes_image_path']) || !empty($data['blog_cat_image'])) {
                                if ($data['blog_lowRes_image_path'] && file_exists($data['blog_lowRes_image_path'])) {
                                    $image = $data['blog_lowRes_image_path'];
                                } else if ($data['blog_cat_image']) {
                                    $image = INFUSIONS."blog/blog_cats/".$data['blog_cat_image'];
                                } else {
                                    $image = get_image('imagenotfound');
                                }
                            } else {
                                $image = get_image('imagenotfound');
                            }
                            echo '<a href="'.$data['blog_link'].'" class="thumb overflow-hide">';
                                echo '<img class="img-responsive" src="'.$image.'" alt="'.$data['blog_subject'].'"/>';

                                echo '<div class="cats">';
                                    $cats = explode(', ', $data['blog_category_link']);
                                    foreach ($cats as $cat) {
                                        echo str_replace('<a href=', '<a class="badge m-l-5" href=', $cat);
                                    }
                                echo '</div>';
                            echo '</a>';

                            echo '<div class="author float">'.display_avatar($data, '40px', '', TRUE, 'img-circle').'</div>';

                            echo '<div class="post-meta">';
                                echo '<h4 class="title m-t-0"><a href="'.$data['blog_link'].'">'.$data['blog_subject'].'</a></h4>';

                                echo '<p>'.$data['blog_blog'].'</p>';
                                echo $data['blog_readmore_link'];
                            echo '</div>';

                            echo '<div class="post-info text-center p-5 p-t-10 p-b-10" style="border-top: 1px solid #e0e0e0;">';
                                if (fusion_get_settings('comments_enabled') && $data['blog_allow_comments']) {
                                    echo '<a href="'.INFUSIONS.'blog/blog.php?readmore='.$blog_id.'&amp;cat_id='.$data['blog_cat'].'#comments"><i class="fa fa-comment-o "></i> '.$data['blog_comments'].'</a>';
                                }
                                if (fusion_get_settings('ratings_enabled') && $data['blog_allow_ratings']) {
                                    echo ' &middot; <span><i class="fa fa-star-o"></i> '.$data['blog_count_votes'].'</span>';
                                }
                                echo ' &middot; <span><i class="fa fa-eye"></i> '.$data['blog_reads'].'</span> &middot; ';
                                echo timer($data['blog_datestamp']);
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
                echo '</div>';

                echo !empty($info['blog_nav']) ? '<div class="text-center m-t-10">'.$info['blog_nav'].'</div>' : '';
            } else {
                echo '<div class="text-center">'.$locale['blog_3000'].'</div>';
            }

        echo '</div>';
    }

    public static function display_blog_item($info) {
        $blog_settings = get_settings('blog');
        $locale = fusion_get_locale();
        $data = $info['blog_item'];

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

        echo '<div class="news-header">';
            echo '<h1>'.$data['blog_subject'].'</h1>';

            echo render_breadcrumbs();
        echo '</div>';

        echo '<div class="card clearfix"><div class="row">';
            echo '<div class="col-xs-12 col-sm-9">';
                echo '<div class="clearfix m-b-20">';
                    echo '<div class="pull-right">';
                        $action = $data['admin_link'];
                        if (!empty($action)) {
                            echo '<div class="btn-group">';
                                echo '<a href="'.$data['print_link'].'" class="btn btn-primary btn-sm" title="'.$locale['print'].'" target="_blank"><i class="fa fa-print"></i></a>';
                                echo '<a href="'.$action['edit'].'" class="btn btn-warning btn-sm" title="'.$locale['edit'].'"><i class="fa fa-pencil"></i></a>';
                                echo '<a href="'.$action['delete'].'" class="btn btn-danger btn-sm" title="'.$locale['delete'].'"><i class="fa fa-trash"></i></a>';
                            echo '</div>';
                        } else {
                            echo '<a class="btn btn-primary btn-circle btn-sm" href="'.$data['print_link'].'" title="'.$locale['print'].'" target="_blank"><i class="fa fa-print"></i></a>';
                        }
                    echo '</div>';

                    echo '<div class="overflow-hide">';
                        echo $data['blog_post_author'].' ';
                        echo $data['blog_post_time'];
                        $cats = explode(', ', $data['blog_category_link']);
                        foreach ($cats as $cat) {
                            echo str_replace('<a href=', '<a class="badge m-l-5" href=', $cat);
                        }
                    echo '</div>';
                echo '</div>';

                echo '<div class="clearfix">';
                    if ($data['blog_image']) {
                        echo '<a class="m-10 '.$data['blog_ialign'].' blog-image-overlay" href="'.$data['blog_image_link'].'">';
                            echo '<img class="img-responsive" src="'.$data['blog_image_link'].'" alt="'.$data['blog_subject'].'" style="padding: 5px; max-height: '.$blog_settings['blog_photo_h'].'px; overflow: hidden;"/>';
                        echo '</a>';
                    }

                    echo $data['blog_blog'];
                    echo '<br>';
                    echo $data['blog_extended'];
                echo '</div>';

                echo $data['blog_nav'] ? '<div class="clearfix m-b-20">'.$data['blog_nav'].'</div>' : '';

                echo $data['blog_show_comments'];
                echo $data['blog_show_ratings'];
            echo '</div>';

            echo '<div class="col-xs-12 col-sm-3">';
                echo '<ul class="list-style-none">';
                    $i = 0;
                    foreach ($info['blog_filter'] as $filter_key => $filter) {
                        $filter_active = isset($_GET['type']) && $_GET['type'] === $filter_key ? ' class="text-dark"' : '';
                        echo '<li'.$filter_active.'><a href="'.$filter['link'].'" class="display-inline m-r-10">'.$filter['title'].'</a></li>';
                        $i++;
                    }
                echo '</ul>';

                if (!empty($info['blog_categories'])) {
                    openside('<i class="fa fa-list"></i> '.$locale['blog_1003'], 'shadow p-t-0');
                    echo '<ul class="list-style-none">';
                        foreach ($info['blog_categories'][0] as $id => $data) {
                            $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $id ? ' class="active"' : '';
                            echo '<li'.$active.'><a href="'.INFUSIONS.'blog/blog.php?cat_id='.$id.'">'.$data['blog_cat_name'].'</a></li>';

                            if ($id != 0 && $info['blog_categories'] != 0) {
                                foreach ($info['blog_categories'] as $sub_cats_id => $sub_cats) {
                                    foreach ($sub_cats as $sub_cat_id => $sub_cat_data) {
                                        if (!empty($sub_cat_data['blog_cat_parent']) && $sub_cat_data['blog_cat_parent'] == $id) {
                                            $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $sub_cat_id ? ' class="active"' : '';
                                            echo '<li'.$active.'><a href="'.INFUSIONS.'blog/blog.php?cat_id='.$sub_cat_id.'">'.$sub_cat_data['blog_cat_name'].'</a></li>';
                                        }
                                    }
                                }
                            }
                        }
                    echo '</ul>';
                    closeside();
                }

                if (!empty($info['blog_author'])) {
                    openside('<i class="fa fa-users"></i> '.$locale['blog_1005'], 'shadow p-t-0');
                    echo '<ul class="list-style-none">';
                        foreach ($info['blog_author'] as $author_id => $author_info) {
                            echo '<li'.($author_info['active'] ? ' class="active"' : '').'><a href="'.$author_info['link'].'">'.$author_info['title'].' <span class="badge m-l-10">'.$author_info['count'].'</span></a></li>';
                        }
                    echo '</ul>';
                    closeside();
                }

                if (!empty($info['blog_archive'])) {
                    openside('<i class="fa fa-calendar"></i> '.$locale['blog_1004'], 'shadow p-t-0');
                    echo '<ul class="list-style-none">';
                        foreach ($info['blog_archive'] as $year => $archive_data) {
                            $active = $year == date('Y') ? ' text-dark' : '';
                            echo '<li>';
                                $collaped_ = isset($_GET['archive']) && $_GET['archive'] == $year ? ' strong' : '';
                                echo '<a class="'.$active.$collaped_.'" data-toggle="collapse" data-parent="#blogarchive" href="#blog-'.$year.'">'.$year.'</a>';

                                $collaped = isset($_GET['archive']) && $_GET['archive'] == $year ? ' in' : '';
                                echo '<ul id="blog-'.$year.'" class="collapse m-l-15 '.$collaped.'">';
                                foreach ($archive_data as $month => $a_data) {
                                    echo '<li'.($a_data['active'] ? ' class="active strong"' : '').'><a href="'.$a_data['link'].'">'.$a_data['title'].' <span class="badge m-l-10">'.$a_data['count'].'</span></a></li>';
                                }
                                echo '</ul>';
                            echo '</li>';
                        }
                    echo '</ul>';
                    closeside();
                }
            echo '</div>';
        echo '</div>';
    }
}
