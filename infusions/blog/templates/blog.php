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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists('render_main_blog')) {
    function render_main_blog($info) {
        add_to_head("<link rel='stylesheet' href='".INFUSIONS."blog/templates/css/blog.css' type='text/css'>");

        echo opentable(fusion_get_locale('blog_1000'));
        echo render_breadcrumbs();
        if (isset($_GET['readmore']) && !empty($info['blog_item'])) {
            echo "<!--blog_pre_readmore-->";
            echo display_blog_item($info); // change this integration
            echo "<!--blog_sub_readmore-->";
        } else {
            echo display_blog_index($info);
        }
        echo closetable();

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
        add_to_footer('<script type="text/javascript">'.jsminify('
			$(document).ready(function() {
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
			});
			').'</script>');
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
        echo $data['blog_extended'];
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
							<div class='col-xs-12 col-sm-4'>
								<div class='pull-left m-r-5'>".$data['blog_user_avatar']."</div>
								<div class='overflow-hide'>
									".$data['blog_user_link']." <br/>
									<span class='m-r-10 text-lighter'><i class='fa fa-comment-o fa-fw'></i> ".$data['blog_comments']."</span><br/>
									<span class='m-r-10 text-lighter'><i class='fa fa-star-o fa-fw'></i> ".$data['blog_count_votes']."</span><br/>
									<span class='m-r-10 text-lighter'><i class='fa fa-eye fa-fw'></i> ".$data['blog_reads']."</span><br/>
								</div>
							</div>
							<div class='col-xs-12 col-sm-8'>
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

            echo !empty($info['blog_nav']) ? '<div class="m-t-5">'.$info['blog_nav'].'</div>' : '';
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
        echo "<div class='text-bigger strong text-dark m-b-20 m-t-20'><i class='fa fa-list m-r-10'></i> ".$locale['blog_1003']."</div>\n";
        echo "<ul class='block spacer-sm'>\n";
        $blog_cat_menu = find_cat_menu($info['blog_categories']);
        if (!empty($blog_cat_menu)) {
            echo $blog_cat_menu;
        } else {
            echo "<li>".$locale['blog_3001']."</li>\n";
        }
        echo "</ul>\n";
        echo "<div class='text-bigger strong text-dark m-t-20 m-b-20'><i class='fa fa-calendar m-r-10'></i> ".$locale['blog_1004']."</div>\n";
        echo "<ul class='block spacer-sm'>\n";
        if (!empty($info['blog_archive'])) {
            $current_year = 0;
            foreach ($info['blog_archive'] as $year => $archive_data) {
                if ($current_year !== $year) {
                    echo "<li class='text-dark strong'>".$year."</li>\n";
                }
                if (!empty($archive_data)) {
                    foreach ($archive_data as $month => $a_data) {
                        echo "<li ".($a_data['active'] ? "class='active strong'" : '').">
						<a href='".$a_data['link']."'>".$a_data['title']."</a> <span class='badge m-l-10'>".$a_data['count']."</span>
						</li>\n";
                    }
                }
                $current_year = $year;
            }
        } else {
            echo "<li>".$locale['blog_3002']."</li>\n";
        }
        echo "</ul>\n";
        echo "<div class='text-bigger strong text-dark m-t-20 m-b-20'><i class='fa fa-users m-r-10'></i> ".$locale['blog_1005']."</div>\n";
        echo "<ul class='block spacer-sm'>\n";
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
        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }
}
