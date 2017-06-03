<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news.php
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

if (!function_exists('display_main_news')) {
    /**
     * News Page Template
     * @param $info
     */
    function display_main_news($info) {

        $news_settings = \PHPFusion\News\NewsServer::get_news_settings();
        $locale = fusion_get_locale();

        add_to_head("<link href='".INFUSIONS."news/templates/css/news.css' rel='stylesheet'/>\n");
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");

        $cookie_expiry = time() + 7 * 24 * 3600;
        if (empty($_COOKIE['fusion_news_view'])) {
            setcookie("fusion_news_view", 1, $cookie_expiry);
        } elseif (isset($_GET['switchview']) && isnum($_GET['switchview'])) {
            setcookie("fusion_news_view", intval($_GET['switchview'] == 2 ? 2 : 1), $cookie_expiry);
            redirect(INFUSIONS.'news/news.php');
        }

        opentable($locale['news_0004']);
        echo render_breadcrumbs();

        /* Slideshow */
        $carousel_indicators = '';
        $carousel_item = '';
        $res = 0;
        $carousel_height = "300";
        if (!empty($info['news_items'])) {
            $i = 0;
            foreach ($info['news_items'] as $news_item) {

                if ($news_item['news_image_src'] && file_exists($news_item['news_image_src'])) {
                    $carousel_active = $res == 0 ? 'active' : '';
                    $res++;
                    $carousel_indicators .= "<li data-target='#news-carousel' data-slide-to='$i' class='".$carousel_active."'></li>\n";
                    $carousel_item .= "<div class='item ".$carousel_active."'>\n";
                    $carousel_item .= "<img class='img-responsive' style='position:absolute; width:100%;' src='".$news_item['news_image_src']."' alt='".$news_item['news_subject']."'>\n";
                    $carousel_item .= "
					<div class='carousel-caption'>
						<div class='overflow-hide'>
						<a class='text-white' href='".INFUSIONS."news/news.php?readmore=".$news_item['news_id']."'><h4 class='text-white m-t-10'>".$news_item['news_subject']."</h4></a>\n
						<span class='news-carousel-action m-r-10'><i class='fa fa-eye fa-fw'></i>".$news_item['news_reads']."</span>
						".($news_item['news_allow_comments'] ? "<span class='m-r-10'>".display_comments($news_item['news_comments'],
                                                                                                        INFUSIONS."news/news.php?readmore=".$news_item['news_id']."#comments")."</span>" : '')."
						".($news_item['news_allow_ratings'] ? "<span class='m-r-10'>".display_ratings($news_item['news_sum_rating'],
                                                                                                      $news_item['news_count_votes'],
                                                                                                      INFUSIONS."news/news.php?readmore=".$news_item['news_id']."#postrating")." </span>" : '')."
						</div>\n
					</div>\n</div>\n
					";
                    $i++;
                }
            }
        }

        if ($res) {
            echo "<div id='news-carousel' class='carousel slide'  data-interval='20000' data-ride='carousel'>\n";
            if ($res > 1) {
                echo "<ol class='carousel-indicators'>\n";
                echo $carousel_indicators;
                echo "</ol>";
            }
            echo "<div class='carousel-inner' style='height:".$carousel_height."px' role='listbox'>\n";
            echo $carousel_item;
            echo "</div>\n";
            echo "
				<a class='left carousel-control' href='#news-carousel' role='button' data-slide='prev'>
					<span class='glyphicon glyphicon-chevron-left' aria-hidden='true'></span>
					<span class='sr-only'>".$locale['previous']."</span>
			  	</a>
			  	<a class='right carousel-control' href='#news-carousel' role='button' data-slide='next'>
					<span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
					<span class='sr-only'>".$locale['next']."</span>
			  	</a>\n
				";
            echo "</div>\n";
        }
        echo "<div class='panel panel-default panel-news-header'>\n";
        echo "<div class='panel-body'>\n";
        echo "<div class='pull-right'>\n";
        echo "<a class='btn btn-sm btn-default text-dark' href='".INFUSIONS."news/news.php'><i class='fa fa-desktop fa-fw'></i> ".$locale['news_0004']."</a>\n";
        echo "<button type='button' class='btn btn-sm btn-primary' data-toggle='collapse' data-target='#newscat' aria-expanded='true' aria-controls='newscat'><i class='fa fa-newspaper-o'></i> ".$locale['news_0009']."</button>\n";
        echo "</div>\n";
        echo "<div class='pull-left m-r-10' style='position:relative; margin-top:-30px;'>\n";
        echo "<div style='max-width:80px;'>\n";
        echo $info['news_cat_image'];
        echo "</div>\n";
        echo "</div>\n";
        echo "<div class='overflow-hide'>\n";
        echo "<h3 class='display-inline text-dark'>".$info['news_cat_name']."</h3><br/><span class='strong'>".$locale['news_0008'].":</span> <span class='text-dark'>\n
			".($info['news_last_updated'] > 0 ? $info['news_last_updated'] : $locale['na'])."</span>";
        echo "</div>\n";
        echo "</div>\n";

        echo "<div id='newscat' class='panel-collapse collapse m-b-10'>\n";
        echo "<!--pre_news_cat_idx-->";
        echo "<ul class='list-group'>\n";
        echo "<li class='list-group-item'><hr class='m-t-0 m-b-5'>\n";
        echo "<span class='display-inline-block m-b-10 strong text-smaller text-uppercase'> ".$locale['news_0010']."</span><br/>\n";
        if (is_array($info['news_categories'][0])) {
            foreach ($info['news_categories'][0] as $cat_id => $cat_data) {
                echo isset($_GET['cat_id']) && $_GET['cat_id'] == $cat_id ? '' : "<a href='".INFUSIONS."news/news.php?cat_id=".$cat_id."' class='btn btn-sm btn-default'>".$cat_data['name']."</a>";
            }
        } else {
            echo "<p>".$locale['news_0016']."</p>";
        }
        echo "</li>";
        echo "</ul>\n";

        echo "<!--sub_news_cat_idx-->\n";
        echo "</div>\n</div>\n";

        echo "<div class='row m-b-20 m-t-20'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";

        $active = isset($_COOKIE['fusion_news_view']) && isnum($_COOKIE['fusion_news_view']) && $_COOKIE['fusion_news_view'] == 2 ? 2 : 1;
        echo "<div class='btn-group pull-right display-inline-block m-l-10'>\n";
        echo "<a class='btn btn-default snv".($active == 1 ? ' active ' : '')."' href='".INFUSIONS."news/news.php?switchview=1'><i class='fa fa-th-large'></i>".$locale['news_0014']."</a>";
        echo "<a class='btn btn-default snv".($active == 2 ? ' active ' : '')."' href='".INFUSIONS."news/news.php?switchview=2'><i class='fa fa-bars'></i>".$locale['news_0015']."</a>";
        echo "</div>\n";

        // Filters
        echo "<div class='display-inline-block'>\n";
        echo "<span class='text-dark strong m-r-10'>".$locale['show']." :</span>";
        $i = 0;
        foreach ($info['news_filter'] as $link => $title) {
            $filter_active = (!isset($_GET['type']) && $i == '0') || isset($_GET['type']) && stristr($link,
                                                                                                     $_GET['type']) ? 'text-dark strong' : '';
            echo "<a href='".$link."' class='display-inline $filter_active m-r-10'>".$title."</a>";
            $i++;
        }
        echo "</div>\n";
        // end filter.
        echo "</div>\n</div>\n";

        $news_span = $active == 2 ? 12 : 4;

        if (!empty($info['news_items'])) {
            echo "<div class='row'>\n";
            foreach ($info['news_items'] as $i => $news_info) {
                echo "<div class='col-xs-12 col-sm-$news_span col-md-$news_span col-lg-$news_span'>\n";
                echo (isset($_GET['cat_id'])) ? "<!--pre_news_cat_idx-->\n" : "<!--news_prepost_".$i."-->\n";
                render_news($news_info['news_subject'], $news_info['news_news'], $news_info, $active == 2);
                echo (isset($_GET['cat_id'])) ? "<!--sub_news_cat_idx-->" : "<!--sub_news_idx-->\n";
                echo "</div>\n";
            }
            echo "</div>\n";

            if ($info['news_total_rows'] > $news_settings['news_pagination']) {
                $type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : '';
                $cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '';
                echo "<div class='text-center m-t-10 m-b-10'>".makepagenav($_GET['rowstart'],
                                                                           $news_settings['news_pagination'],
                                                                           $info['news_total_rows'], 3,
                                                                           INFUSIONS."news/news.php?".$cat_start.$type_start)."</div>\n";
            }
        } else {
            echo "<div class='well text-center'>".$locale['news_0005']."</div>\n";
        }

        closetable();

    }
}

if (!function_exists('render_news')) {
    /**
     * News Item Container
     * @param      $info
     * @param bool $list_view
     */
    function render_news($subject, $news, $info, $list_view = FALSE) {

        $locale = fusion_get_locale();
        $news_settings = \PHPFusion\News\NewsServer::get_news_settings();
        $settings = fusion_get_settings();

        if ($list_view) {

            echo "<article class='panel panel-default overflow-hide clearfix' style='height:".$news_settings['news_thumb_h']."px;'>\n";
            echo ($info['news_sticky']) ? "<i class='pull-right fa fa-warning'></i>\n" : '';
            if ($info['news_image']) {

                echo "<div class='image-header pull-left overflow-hide' style='display:inline-block; width: 50%; height:".$news_settings['news_thumb_h']."px;'>\n";
                echo $info['news_image'];
                echo "</div>\n";

                echo "<div class='overflow-hide p-25'>\n";
            }
            echo "<h4 class='news-title panel-title'><a class='strong text-dark' href='".INFUSIONS."news/news.php?readmore=".$info['news_id']."' >".$info['news_subject']."</a></h4>\n";
            echo "<div class='m-t-10'>\n";
            echo "<span class='news-text m-t-10'>".$info['news_news']."</span>\n";
            echo "<div class='m-t-10'><span class='news-date'>".showdate($settings['newsdate'], $info['news_date'])." -- </span></div>\n";
            echo "<div class='news-category m-t-10'><span class='text-dark strong'>\n".ucwords($locale['in'])."</span> : ";
            echo $info['news_cat_name'] ? "<a href='".INFUSIONS."news/news.php?cat_id=".$info['news_cat_id']."'>".$info['news_cat_name']."</a>" : "<a href='".INFUSIONS."news/news.php?cat_id=0'>".$locale['news_0006']."</a>&nbsp;";
            echo "</div>\n";
            if ($info['news_image']) {
                echo "</div>\n";
            }
            echo "<div class='news-footer ".($info['news_image'] ? "m-t-20" : '')." p-15 p-l-0'>\n";
            echo "<span><i class='fa fa-eye'></i> ".number_format($info['news_reads'])."</span>";
            echo $info['news_allow_comments'] ? display_comments($info['news_comments'],
                                                                 INFUSIONS."news/news.php?readmore=".$info['news_id']."#comments") : '';
            echo $info['news_allow_ratings'] ? display_ratings($info['news_sum_rating'], $info['news_count_votes'],
                                                               INFUSIONS."news/news.php?readmore=".$info['news_id']."#postrating") : '';
            echo "<a class='m-r-10' title='".$locale['news_0002']."' href='".$info['print_link']."'><i class='fa fa-print'></i></a>";
            if (!empty($info['admin_actions'])) {
                $admin_actions = $info['admin_actions'];
                echo "<a title='".$locale['news_0003']."' href='".$admin_actions['edit']."' title='".$locale['news_0003']."' />".$locale['news_0003']."</a>\n";
            }
            echo "</div>\n";
            echo "</article>\n";
        } else {
            echo "<!--news_prepost_".$info['news_id']."-->\n";
            echo "<article class='panel panel-default' style='min-height:290px'>\n";
            echo "<div class='overflow-hide'>\n";
            echo "<div class='image-header'>\n";
            echo $info['news_image'];
            echo "</div>\n";
            echo "</div>\n";

            echo "<div class='panel-body'>\n";
            echo ($info['news_sticky']) ? "<i class='pull-right fa fa-warning icon-sm'></i>\n" : '';
            echo "<h4 class='news-title panel-title'><a class='strong text-dark' href='".INFUSIONS."news/news.php?readmore=".$info['news_id']."' >".$info['news_subject']."</a></h4>\n";
            echo "<div class='news-text m-t-5' style='height:200px;'>".trim_text(strip_tags($info['news_news']),
                                                                                 250)."</div>\n";
            echo "<div class='news-date m-t-5'>".showdate("newsdate", $info['news_date'])."</div>\n";
            echo "<div class='news-category m-t-5'><span class='text-dark strong'>\n".ucwords($locale['in'])."</span> : ";

            echo $info['news_cat_name'] ? "<a href='".INFUSIONS."news/news.php?cat_id=".$info['news_cat_id']."'>".$info['news_cat_name']."</a>" : "<a href='".INFUSIONS."news/news.php?cat_id=0&amp;filter=false'>".$locale['news_0006']."</a>&nbsp;";
            echo "</div>\n";
            echo "</div>\n";
            echo "<div class='news-footer panel-footer'>\n";
            echo "<span class='m-r-10'><i class='fa fa-eye'></i> ".number_format($info['news_reads'])."</span>";
            echo !empty($info['news_display_comments']) ? $info['news_display_comments'] : '';
            echo !empty($info['news_display_ratings']) ? $info['news_display_ratings'] : '';
            echo "<a title='".$locale['news_0002']."' href='".$info['print_link']."' target='_blank'><i class='fa fa-print'></i></a>";
            if (!empty($info['admin_actions'])) {
                $admin_actions = $info['admin_actions'];
                echo "<a class='pull-right' title='".$locale['news_0003']."' href='".$admin_actions['edit']."' title='".$locale['news_0003']."' /><i class='fa fa-pencil'></i></a>\n";
            }
            echo "</div>\n";
            echo "</article>\n";
        }
    }
}

if (!function_exists('render_news_item')) {
    /**
     * News Item Page Template
     * @param $info
     */
    function render_news_item($info) {

        $locale = fusion_get_locale();
        $news_settings = \PHPFusion\News\NewsServer::get_news_settings();
        $data = $info['news_item'];

        add_to_head("<link rel='stylesheet' href='".INFUSIONS."news/templates/css/news.css' type='text/css'>");
        add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
        add_to_footer('<script type="text/javascript">'.jsminify('
			$(document).ready(function() {
				$(".news-image-overlay").colorbox({
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

        opentable($locale['news_0004']);
        echo render_breadcrumbs();
        echo "<!--news_pre_readmore-->";
        echo "<article class='news-item' style='display:block; width:100%; overflow:hidden;'>\n";
        if (!empty($data['news_admin_actions'])) {
            $admin_actions = $data['news_admin_actions'];
            echo "<div class='btn-group m-l-10 pull-right'>";
            echo "<a class='btn btn-default btn-sm' title='".$locale['news_0003']."' href='".$admin_actions['edit']['link']."' title='".$admin_actions['edit']['title']."' /><i class='fa fa-pencil'></i> ".$admin_actions['edit']['title']."</a> \n";
            echo "<a class='btn btn-danger btn-sm' title='".$locale['news_0003']."' href='".$admin_actions['delete']['link']."' title='".$admin_actions['delete']['title']."' /><i class='fa fa-trash'></i> ".$admin_actions['delete']['title']."</a>\n";
            echo "</div>\n";
        }
        echo "<h2 class='text-left m-t-0 m-b-0'>".$data['news_subject']."</h2>\n";
        echo "<div class='news_news text-dark m-t-20 m-b-20 overflow-hide'>\n";
        if ($data['news_image_src']) {
            echo "<a class='".$data['news_image_align']." news-image-overlay' href='".$data['news_image_src']."'>
            <img class='img-responsive' src='".$data['news_image_src']."' alt='".$data['news_subject']."' style='padding:5px; width: 30%; max-height:".$news_settings['news_photo_h']."px; overflow:hidden;' /></a>";
        }
        echo $data['news_news'];
        echo $data['news_extended'];
        echo "</div>\n";
        echo $data['news_pagenav'];

        if (!empty($data['news_gallery'])) {
            $thumb_height = \PHPFusion\News\News::get_news_settings('news_thumb_h');
            $thumb_width = \PHPFusion\News\News::get_news_settings('news_thumb_w');
            echo '<hr/>';
            openside(fusion_get_locale('news_0019')) ?>
            <div class='post-gallery'>
                <?php foreach ($data['news_gallery'] as $news_image_id => $news_image) : ?>
                    <div class='post-gallery-item overflow-hide ' style='margin: -1px; width: 33%; max-height: <?php echo $thumb_height ?>px'>
                        <div class='center-xy'>
                            <?php echo colorbox(IMAGES_N.$news_image['news_image'], '', FALSE, 'pull-left') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php closeside();
        }

        echo "<div class='well m-t-15 text-center'>\n";
        echo "<span class='news-action'><i class='fa fa-user'></i> ".profile_link($data['user_id'], $data['user_name'],
                                                                                              $data['user_status'])."</span>\n";
        echo "<span class='news-action m-l-10'><i class='fa fa-calendar'></i> ".showdate("newsdate", $data['news_datestamp'])."</span>\n";
        echo "<span class='news-action m-l-10'><i class='fa fa-eye'></i> <span class='text-dark'>".number_format($data['news_reads'])."</span>\n</span>";
        echo '<i class="fa fa-comments-o m-l-10"></i> '.$data['news_display_comments'];
        echo '<i class="fa fa-star-o m-l-10"></i> '.$data['news_display_ratings'];
        echo "<i class='fa fa-print m-l-10'></i> <a title='".$locale['news_0002']."' href='".BASEDIR."print.php?type=N&amp;item_id=".$data['news_id']."' target='_blank'>".$locale['print']."</a>";
        echo "</div>";
        echo "<!--news_sub_readmore-->";
        echo !isset($_GET['readmore']) && $data['news_ext'] == "y" ? "<div class='m-t-20'>\n<a href='".INFUSIONS."news/news.php?readmore=".$data['news_id']."' class='button'>".$locale['news_0001']."</a>\n</div>\n" : "";

        echo $data['news_show_comments'] ? "<hr />".$data['news_show_comments']."\n" : '';
        echo $data['news_show_ratings'] ? "<hr />".$data['news_show_ratings']."\n" : '';

        echo "</article>\n";
        closetable();
    }
}
