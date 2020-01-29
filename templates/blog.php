<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: templates/blog.php
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

if (!function_exists('render_main_blog')) {
    function render_main_blog($info) {
        add_to_head("<link rel='stylesheet' href='".INFUSIONS."blog/templates/css/blog.css' type='text/css'>");

        opentable(fusion_get_locale('blog_1000'));
        echo render_breadcrumbs();
        if (isset($_GET['readmore']) && !empty($info['blog_item'])) {
            echo "<!--blog_pre_readmore-->";
            echo display_blog_item($info); // change this integration
            echo "<!--blog_sub_readmore-->";
        } else {
            echo display_blog_index($info);
        }
        closetable();

        // push the blog menu to the right panel
        if (!empty($info['blog_filter'])) {
            $pages = "<ul class='block spacer-sm'>\n";
            foreach ($info['blog_filter'] as $filter_key => $filter) {
                $pages .= "<li ".(isset($_GET['type']) && $_GET['type'] == $filter_key ? "class='active strong'" : '')." ><a href='".$filter['link']."'>".$filter['title']."</a></li>\n";
            }
            $pages .= "</ul>\n";
            \PHPFusion\Panels::addPanel('blog_menu_panel', $pages, \PHPFusion\Panels::PANEL_RIGHT, iGUEST, 0);
        }

        \PHPFusion\Panels::addPanel('blog_menu_panel', display_blog_menu($info), \PHPFusion\Panels::PANEL_RIGHT, iGUEST, 9);
    }
}

if (!function_exists('display_blog_item')) {
    function display_blog_item($info) {
        global $blog_settings;

        $locale = fusion_get_locale();

        add_to_head("<link rel='stylesheet' href='".INFUSIONS."blog/templates/css/blog.css' type='text/css'>");
        add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
        add_to_jquery('
            $(".blog-image-overlay").colorbox({
                transition: "elasic",
                height:"100%",
                width:"100%",
                maxWidth:"98%",
                maxHeight:"98%",
                scrolling:false,
                overlayClose:true,
                close:false,
                photo:true,
                onComplete: function(result) {
                    $("#colorbox").live("click", function(){
                    $(this).unbind("click");
                    $.fn.colorbox.close();
                    });
                },
                onLoad: function () {
                }
            });
        ');
        ob_start();

        $data = $info['blog_item'];

        echo "<div class='clearfix'>
                <div class='btn-group pull-right'>
                <a class='btn btn-default btn-sm' href='".$data['print_link']."' target='_blank'><i class='fa fa-print'></i> ".$locale['print']."</a>";
        if ($data['admin_link']) {
            $admin_actions = $data['admin_link'];
            echo "<a class='btn btn-default btn-sm' href='".$admin_actions['edit']."'><i class='fa fa-pencil'></i> ".$locale['edit']."</a>\n";
            echo "<a class='btn btn-danger btn-sm' href='".$admin_actions['delete']."'><i class='fa fa-trash'></i> ".$locale['delete']."</a>\n";
        }
        echo "</div>";
        echo "<div class='overflow-hide'>
                <h2 class='strong m-t-0 m-b-0'>".$data['blog_subject']."</h2>
                <div class='blog-category'>".$data['blog_category_link']."</div>
                <div class='m-t-20 m-b-20'>".$data['blog_post_author']." ".$data['blog_post_time']."</div>
            </div>
        </div>";

        echo "<div class='clearfix m-b-20'>\n";
        if ($data['blog_image']) {
            echo "<a class='m-10 ".$data['blog_ialign']." blog-image-overlay' href='".$data['blog_image_link']."'>";
            echo "<img class='img-responsive' src='".$data['blog_image_link']."' alt='".$data['blog_subject']."' style='padding:5px; max-height:".$blog_settings['blog_photo_h']."px; overflow:hidden;' />
            </a>";
        }

        echo $data['blog_blog'];
        echo '<br>';
        echo $data['blog_extended'];

        if (!empty($data['blog_nav'])) {
            echo '<div class="text-center">'.$data['blog_nav'].'</div>';
        }

        echo "</div>\n";
        echo "<div class='m-b-20 well'>".$data['blog_author_info']."</div>";

        echo $data['blog_allow_comments'] ? "<hr/>".$data['blog_show_comments'] : '';
        echo $data['blog_allow_ratings'] ? "<hr/>".$data['blog_show_ratings'] : '';
        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }
}

if (!function_exists('display_blog_index')) {
    function display_blog_index($info) {
        add_to_head("<link rel='stylesheet' href='".INFUSIONS."blog/templates/css/blog.css' type='text/css'>");
        $locale = fusion_get_locale();
        ob_start();
        if (!empty($info['blog_item'])) {
            foreach ($info['blog_item'] as $blog_id => $data) {
                echo (isset($_GET['cat_id'])) ? "<!--pre_blog_cat_idx-->\n" : "<!--blog_prepost_".$blog_id."-->\n";
                echo "
                    <div class='clearfix m-b-20'>
                        <div class='row'>
                            <div class='col-xs-12 col-sm-3'>
                                <div class='pull-left m-r-5'>".$data['blog_user_avatar']."</div>
                                <div class='overflow-hide'>
                                    ".$data['blog_user_link']." <br/>";
                                    if ($data['blog_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
                                        echo "<span class='m-r-10 text-lighter'><i class='fa fa-comment-o fa-fw'></i> ".$data['blog_comments']."</span><br/>";
                                    }

                                    if ($data['blog_allow_ratings'] && fusion_get_settings('ratings_enabled') == 1) {
                                        echo "<span class='m-r-10 text-lighter'><i class='fa fa-star-o fa-fw'></i> ".$data['blog_count_votes']."</span><br/>";
                                    }
                                    echo "<span class='m-r-10 text-lighter'><i class='fa fa-eye fa-fw'></i> ".$data['blog_reads']."</span><br/>
                                </div>
                            </div>
                            <div class='col-xs-12 col-sm-9'>
                                <h2 class='strong m-b-20 m-t-0'><a class='text-dark' href='".$data['blog_link']."'>".$data['blog_subject']."</a></h2>
                                <div class='display-block'>".$data['blog_category_link']."</div>
                                <div class='display-block'><i class='fa fa-clock-o m-r-5'></i> ".$locale['global_049']." ".timer($data['blog_datestamp'])."</div>
                                ".($data['blog_image'] ? "<div class='blog-image m-10 ".$data['blog_ialign']."'>".$data['blog_image']."</div>" : '')."
                                <div class='m-t-20'>".$data['blog_blog']."<br/>".$data['blog_readmore_link']."</div>
                            </div>
                        </div>
                        <hr>
                    </div>
                ";

                echo (isset($_GET['cat_id'])) ? "<!--sub_blog_cat_idx-->" : "<!--sub_blog_idx-->\n";
            }

            echo !empty($info['blog_nav']) ? '<div class="text-center m-t-5">'.$info['blog_nav'].'</div>' : '';
        } else {
            echo "<div class='well text-center'>".$locale['blog_3000']."</div>\n";
        }
        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }
}

if (!function_exists('display_blog_menu')) {
    function display_blog_menu($info) {
        $locale = fusion_get_locale();
        function find_cat_menu($info, $cat_id = 0, $level = 0) {
            $html = '';
            if (!empty($info[$cat_id])) {
                foreach ($info[$cat_id] as $blog_cat_id => $cdata) {
                    $unCat_active = ($blog_cat_id == 0 && (isset($_GET['cat_id']) && ($_GET['cat_id'] == 0))) ? TRUE : FALSE;
                    $active = ($_GET['cat_id'] !== NULL && $blog_cat_id == $_GET['cat_id']) ? TRUE : FALSE;
                    $html .= "<li ".($active || $unCat_active ? "class='active strong'" : '')." >".str_repeat('&nbsp;', $level)." ".$cdata['blog_cat_link']."</li>\n";
                    if ($active && $blog_cat_id != 0) {
                        if (!empty($info[$blog_cat_id])) {
                            $html .= find_cat_menu($info, $blog_cat_id, $level++);
                        }
                    }
                }
            }

            return $html;
        }

        ob_start();
        openside('<i class="fa fa-list"></i> '.$locale['blog_1003']);
        echo "<ul class='block'>\n";
        $blog_cat_menu = find_cat_menu($info['blog_categories']);
        if (!empty($blog_cat_menu)) {
            echo $blog_cat_menu;
        } else {
            echo "<li>".$locale['blog_3001']."</li>\n";
        }
        echo "</ul>\n";
        closeside();
        openside('<i class="fa fa-calendar"></i> '.$locale['blog_1004']);
        echo "<ul id='blog-archive'>\n";
        if (!empty($info['blog_archive'])) {
            foreach ($info['blog_archive'] as $year => $archive_data) {
                $active = $year == date('Y') ? " text-dark" : '';
                echo "<li>";
                    $collaped_ = isset($_GET['archive']) && $_GET['archive'] == $year ? ' strong' : '';
                    echo "<a class='".$active.$collaped_."' data-toggle='collapse' data-parent='#blog-archive' href='#blog-".$year."'>".$year."</a>";
                    $collaped = isset($_GET['archive']) && $_GET['archive'] == $year ? 'in' : '';
                    echo "<ul id='blog-".$year."' class='collapse m-l-15 ".$collaped."'>";
                        if (!empty($archive_data)) {
                            foreach ($archive_data as $month => $a_data) {
                                echo "<li ".($a_data['active'] ? "class='active strong'" : '')."><a href='".$a_data['link']."'>".$a_data['title']."</a> <span class='badge m-l-10'>".$a_data['count']."</span></li>\n";
                            }
                        }
                    echo "</ul>";
                echo "</li>";
            }
        } else {
            echo "<li>".$locale['blog_3002']."</li>\n";
        }
        echo "</ul>\n";
        closeside();
        openside('<i class="fa fa-users"></i> '.$locale['blog_1005']);
        echo "<ul class='block'>\n";
        if (!empty($info['blog_author'])) {
            foreach ($info['blog_author'] as $author_id => $author_info) {
                echo "<li ".($author_info['active'] ? "class='active strong'" : '').">
                    <a href='".$author_info['link']."'>".$author_info['title']."</a> <span class='badge m-l-10'>".$author_info['count']."</span>
                    </li>\n";
            }
        } else {
            echo "<li>".$locale['blog_3003']."</li>\n";
        }
        echo "</ul>\n";
        closeside();
        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }
}

if (!function_exists('display_blog_submit')) {
    function display_blog_submit($criteriaArray) {
        $blog_settings = get_settings('blog');
        $locale = fusion_get_locale();
        if ($blog_settings['blog_allow_submission']) {
            if ($criteriaArray['submitted']) {

                echo "<div class='well text-center'><p><strong>".$locale['blog_0701']."</strong></p>";
                echo "<p><a href='submit.php?stype=b'>".$locale['blog_0702']."</a></p>";
                echo "<p><a href='index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"),
                        $locale['blog_0704'])."</a></p>\n";
                echo "</div>\n";

            } else {
                opentable("<i class='fa fa-commenting-o fa-lg m-r-10'></i>".$locale['blog_0700']);
                echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
                echo "<div class='alert alert-info m-b-20 submission-guidelines'>".str_replace("[SITENAME]", fusion_get_settings("sitename"),
                        $locale['blog_0703'])."</div>\n";
                echo openform('submit_form', 'post', BASEDIR."submit.php?stype=b",
                    ["enctype" => $blog_settings['blog_allow_submission_files'] ? TRUE : FALSE]);
                echo form_text('blog_subject', $locale['blog_0422'], $criteriaArray['blog_subject'], [
                    "required" => TRUE,
                    "inline"   => TRUE
                ]);
                if (multilang_table("BL")) {
                    echo form_select('blog_language[]', $locale['global_ML100'], $criteriaArray['blog_language'], [
                        "options"     => fusion_get_enabled_languages(),
                        "placeholder" => $locale['choose'],
                        "width"       => "250px",
                        "inline"      => TRUE,
                        'multiple'    => TRUE,
                        'delimeter'   => '.'
                    ]);
                } else {
                    echo form_hidden('blog_language', '', $criteriaArray['blog_language']);
                }
                echo form_select('blog_keywords', $locale['blog_0443'], $criteriaArray['blog_keywords'], [
                    "max_length"  => 320,
                    "inline"      => TRUE,
                    "placeholder" => $locale['blog_0444'],
                    "width"       => "100%",
                    "error_text"  => $locale['blog_0457'],
                    "tags"        => TRUE,
                    "multiple"    => TRUE
                ]);
                echo form_select_tree("blog_cat", $locale['blog_0423'], $criteriaArray['blog_cat'], [
                    "width"        => "250px",
                    "inline"       => TRUE,
                    "parent_value" => $locale['blog_0424'],
                    "query"        => (multilang_table("BL") ? "WHERE ".in_group('blog_cat_language', LANGUAGE) : "")
                ], DB_BLOG_CATS, "blog_cat_name", "blog_cat_id", "blog_cat_parent");
                if ($blog_settings['blog_allow_submission_files']) {
                    $file_input_options = [
                        'upload_path'      => IMAGES_B,
                        'max_width'        => $blog_settings['blog_photo_max_w'],
                        'max_height'       => $blog_settings['blog_photo_max_h'],
                        'max_byte'         => $blog_settings['blog_photo_max_b'],
                        // set thumbnail
                        'thumbnail'        => 1,
                        'thumbnail_w'      => $blog_settings['blog_thumb_w'],
                        'thumbnail_h'      => $blog_settings['blog_thumb_h'],
                        'thumbnail_folder' => 'thumbs',
                        'delete_original'  => 0,
                        // set thumbnail 2 settings
                        'thumbnail2'       => 1,
                        'thumbnail2_w'     => $blog_settings['blog_photo_w'],
                        'thumbnail2_h'     => $blog_settings['blog_photo_h'],
                        'type'             => 'image',
                        "inline"           => TRUE,
                    ];
                    echo form_fileinput("blog_image", $locale['blog_0439'], "", $file_input_options);
                    echo "<div class='small col-sm-offset-3 m-b-10'><span class='p-l-15'>".sprintf($locale['blog_0440'],
                            parsebytesize($blog_settings['blog_photo_max_b']))."</span></div>\n";
                    $alignOptions = [
                        'pull-left'       => $locale['left'],
                        'news-img-center' => $locale['center'],
                        'pull-right'      => $locale['right']
                    ];
                    echo form_select('blog_ialign', $locale['blog_0442'], $criteriaArray['blog_ialign'], [
                        "options" => $alignOptions,
                        "inline"  => TRUE
                    ]);
                }

                $textArea_opts = [
                    "required"  => TRUE,
                    "type"      => fusion_get_settings("tinymce_enabled") ? "tinymce" : "html",
                    "tinymce"   => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "simple",
                    "autosize"  => TRUE,
                    "form_name" => "submit_form",
                    'path'      => IMAGES_B
                ];

                echo form_textarea('blog_blog', $locale['blog_0425'], $criteriaArray['blog_blog'], $textArea_opts);

                $textArea_opts['required'] = $blog_settings['blog_extended_required'] ? TRUE : FALSE;

                echo form_textarea('blog_body', $locale['blog_0426'], $criteriaArray['blog_body'], $textArea_opts);

                echo form_button('submit_blog', $locale['blog_0700'], $locale['blog_0700'], ['class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o']);

                echo fusion_get_settings("site_seo") ? "" : form_button('preview_blog', $locale['blog_0141'], $locale['blog_0141'],
                    ['class' => 'btn-primary m-r-10', 'icon' => 'fa fa-eye']);
                echo closeform();

                echo "</div>\n</div>\n";
                closetable();
            }
        } else {
            echo "<div class='well text-center'>".$locale['blog_0138']."</div>\n";
        }
    }
}
