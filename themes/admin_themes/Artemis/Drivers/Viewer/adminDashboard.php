<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Artemis Interface
| The Artemis Project - 2014 - 2016 (c)
| Network Data Model Development
| Filename: Artemis_ACP/acp_request.php
| Author: Guidlsquare , enVision Sdn Bhd
| Copyright patent 0517721 IPO
| Author's all rights reserved.
+--------------------------------------------------------+
| Released under PHP-Fusion EPAL
+--------------------------------------------------------*/
namespace Artemis\Viewer;

use Artemis\Model\resource;

/**
 * Class adminDashboard
 * Dashboard view
 * @package Artemis\viewer
 */
class adminDashboard extends resource {
    public static function do_dashboard() {
        global $members, $forum, $download, $news, $articles, $weblinks, $photos, $global_comments,
            $global_ratings, $global_submissions, $link_type,
            $comments_type, $infusions_count, $global_infusions, $submit_data;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();

        $mobile = '12';
        $tablet = '6';
        $laptop = '6';
        $desktop = '3';

        opentable($locale['250']);

            $panels = array(
                'registered'   => array('link' => '', 'title' => 251),
                'cancelled'    => array('link' => 'status=5', 'title' => 263),
                'unactivated'  => array('link' => 'status=2', 'title' => 252),
                'security_ban' => array('link' => 'status=4', 'title' => 253)
            );

            echo "<!--Start Members-->\n";
            echo "<div class='row'>\n";
                foreach ($panels as $panel => $block) {
                    $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
                    echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                    openside();
                        echo "<img class='pull-left m-r-10 dashboard-icon' src='".get_image('ac_M')."' alt='".$locale['M']."'/>\n";
                        echo "<h4 class='text-right m-t-0 m-b-0'>".number_format($members[$panel])."</h4>\n";
                        echo "<span class='m-t-10 text-uppercase text-lighter text-smaller pull-right'><strong>".$locale[$block['title']]."</strong></span>\n";
                        $content_ = "<div class='text-right text-uppercase'>\n";
                        $content_ .= "<a class='text-smaller' href='".ADMIN."members.php".$aidlink.$block['link']."'>".$locale['255']." <i class='fa fa-angle-right'></i></a>\n";
                        $content_ .= "</div>\n";
                    closeside(checkrights('M') ? $content_ : '');
                    echo "</div>\n";
                }
            echo "</div>\n";
            echo "<!--End Members-->\n";

            $desktop = '4';
            echo "<div class='row'>\n";
                if (infusion_exists('forum')) {
                    echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                    openside("", "well");
                        echo "<strong class='text-smaller text-uppercase'>".$locale['265']." ".$locale['258']."</strong>\n";
                        echo "<div class='clearfix m-t-10'>\n";
                            echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_F")."'/>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['265']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($forum['count'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['256']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($forum['thread'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['259']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($forum['post'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['260']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".($forum['users'])."</h4>\n";
                            echo "</div>\n";
                        echo "</div>\n";
                    closeside();
                    echo "</div>\n";
                }

                if (infusion_exists('downloads')) {
                    echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                    openside("", "well");
                        echo "<strong class='text-smaller text-uppercase'>".$locale['268']." ".$locale['258']."</strong>\n";
                        echo "<div class='clearfix m-t-10'>\n";
                            echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_D")."'/>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['268']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($download['download'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($download['comment'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($download['submit'])."</h4>\n";
                            echo "</div>\n";
                        echo "</div>\n";
                    closeside();
                    echo "</div>\n";
                }

                if (infusion_exists('news')) {
                    echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                    openside("", "well");
                        echo "<strong class='text-smaller text-uppercase'>".$locale['269']." ".$locale['258']."</strong>\n";
                        echo "<div class='clearfix m-t-10'>\n";
                            echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_N")."'/>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['269']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($news['news'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($news['comment'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($news['submit'])."</h4>\n";
                            echo "</div>\n";
                        echo "</div>\n";
                    closeside();
                    echo "</div>\n";
                }

                if (infusion_exists('articles')) {
                    echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                    openside("", "well");
                        echo "<strong class='text-smaller text-uppercase'>".$locale['270']." ".$locale['258']."</strong>\n";
                        echo "<div class='clearfix m-t-10'>\n";
                            echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_A")."'/>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['270']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($articles['article'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($articles['comment'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($articles['submit'])."</h4>\n";
                            echo "</div>\n";
                        echo "</div>\n";
                    closeside();
                    echo "</div>\n";
                }

                if (infusion_exists('weblinks')) {
                    echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                     openside("", "well");
                        echo "<strong class='text-smaller text-uppercase'>".$locale['271']." ".$locale['258']."</strong>\n";
                        echo "<div class='clearfix m-t-10'>\n";
                            echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_W")."'/>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['271']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($weblinks['weblink'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($weblinks['comment'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($weblinks['submit'])."</h4>\n";
                            echo "</div>\n";
                        echo "</div>\n";
                    closeside();
                    echo "</div>";
                }

                if (infusion_exists('gallery')) {
                    echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                    openside("", "well");
                        echo "<strong class='text-smaller text-uppercase'>".$locale['272']." ".$locale['258']."</strong>\n";
                        echo "<div class='clearfix m-t-10'>\n";
                            echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_PH")."'/>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['272']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($photos['photo'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($photos['comment'])."</h4>\n";
                            echo "</div>\n";
                            echo "<div class='pull-left display-inline-block m-r-10'>\n";
                                echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                                echo "<h4 class='m-t-0'>".number_format($photos['submit'])."</h4>\n";
                            echo "</div>\n";
                        echo "</div>\n";
                    closeside();
                    echo "</div>\n";
                }
            echo "</div>\n";

            echo "<div class='row'>\n";
                echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                    openside("<strong class='text-smaller text-uppercase'>".$locale['283']."</strong><span class='pull-right badge'>".number_format((int)$infusions_count)."</span>");
                    $content = '';
                    if ($infusions_count > 0) {
                        echo "<div class='comment_content'>\n";
                        if (!empty($global_infusions)) {
                            foreach ($global_infusions as $inf_id => $inf_data) {
                                echo "<span class='badge m-b-10 m-r-5'>".$inf_data['inf_title']."</span>\n";
                            }
                        }
                        echo "</div>\n";
                        $content = checkrights("I") ? "<div class='text-right text-uppercase'>\n<a class='text-smaller' href='".ADMIN."infusions.php".$aidlink."'>".$locale['285']."</a><i class='fa fa-angle-right'></i></div>\n" : '';
                    } else {
                        echo "<div class='text-center'>".$locale['284']."</div>\n";
                    }
                    closeside($content);
                echo "</div>\n";

                echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                    openside("<strong class='text-smaller text-uppercase'>".$locale['277']."</strong><span class='pull-right badge'>".number_format($global_comments['rows'])."</span>");
                    if (count($global_comments['data']) > 0) {
                        foreach ($global_comments['data'] as $i => $comment_data) {
                            $comment_item_url = (isset($link_type[$comment_data['comment_type']]) ? "<a href='".sprintf($link_type[$comment_data['comment_type']]."'", $comment_data['comment_item_id'])."'>{%item%}</a>" : '{%item%}');
                            $comment_item_name = (isset($comments_type[$comment_data['comment_type']])) ? $comments_type[$comment_data['comment_type']] : $locale['global_073b'];
                            echo "<div data-id='$i' class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                            echo "<div class='pull-left display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($comment_data, "25px", "", FALSE, "img-rounded m-r-5")."</div>\n";
                            echo "<div id='comment_action-$i' class='pull-right dropdown dropdown-menu-right'>\n
                                <a span class='dropdown-toggle btn btn-default' data-toggle='dropdown'><i class='fa fa-angle-down'></i></a>
                                <ul class='dropdown-menu'>
                                <li><a title='".$locale['274']."' href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-eye fa-fw m-r-10'></i>".$locale['274']."</a></li>
                                <li><a title='".$locale['275']."' href='".ADMIN."comments.php".$aidlink."&amp;action=edit&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-edit fa-fw m-r-10'></i>".$locale['275']."</a></li>
                                <li><a title='".$locale['276']."' href='".ADMIN."comments.php".$aidlink."&amp;action=delete&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-trash fa-fw m-r-10'></i>".$locale['276']."</a></li>
                                </ul>
                                ";
                            echo "</div>\n";
                            echo "<strong>".profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status'])." </strong>\n";
                            echo "<span class='text-lighter'>".$locale['273']."</span> ".strtr($comment_item_url, ['{%item%}' => $comment_item_name]);
                            echo "<br/>\n".timer($comment_data['comment_datestamp'])."<br/>\n";
                            $mess = trimlink(parse_textarea($comment_data['comment_message'], FALSE, FALSE), 70);
                            echo "<span class='text-smaller text-lighter'>".parse_textarea($mess, TRUE, FALSE)."</span>\n";
                            echo "</div>\n";
                        }
                        if (isset($global_comments['comments_nav'])) {
                            echo "<div class='clearfix'>\n";
                            echo "<span class='pull-right text-smaller'>".$global_comments['comments_nav']."</span>";
                            echo "</div>\n";
                        }
                    } else {
                        echo "<div class='text-center'>".$global_comments['nodata']."</div>\n";
                    }
                    closeside();
                echo "</div>\n";

                echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                    openside("<strong class='text-smaller text-uppercase'>".$locale['278']."</strong><span class='pull-right badge'>".number_format($global_ratings['rows'])."</span>");
                    if (count($global_ratings['data']) > 0) {
                        foreach ($global_ratings['data'] as $i => $ratings_data) {
                            $ratings_url = isset($link_type[$ratings_data['rating_type']]) ? "<a href='".sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id'])."'>{%item%}</a>\n" : "{%item%}";
                            $ratings_item = isset($comments_type[$ratings_data['rating_type']]) ? $comments_type[$ratings_data['rating_type']] : $locale['ratings'];
                            echo "<!--Start Rating Item-->\n";
                            echo "<div class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                            echo "<div class='pull-left display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($ratings_data, "25px", "", FALSE, "img-rounded m-r-5")."</div>\n";
                            echo "<strong>".profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status'])." </strong>\n";
                            echo "<span class='text-lighter'>".$locale['273a']." </span>\n";
                            echo strtr($ratings_url, ['{%item%}' => $ratings_item]);
                            echo "<span class='text-lighter m-l-10'>".str_repeat("<i class='fa fa-star fa-fw'></i>", $ratings_data['rating_vote'])."</span>\n<br/>";
                            echo timer($ratings_data['rating_datestamp'])."<br/>\n";
                            echo "</div>\n";
                            echo "<!--End Rating Item-->\n";
                        }
                        if (isset($global_ratings['ratings_nav'])) {
                            echo "<div class='clearfix'>\n";
                                echo "<span class='pull-right text-smaller'>".$global_ratings['ratings_nav']."</span>";
                            echo "</div>\n";
                        }
                    } else {
                        echo "<div class='text-center'>".$global_ratings['nodata']."</div>\n";
                    }
                    closeside();
                echo "</div>\n";

                echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                    openside("<strong class='text-smaller text-uppercase'>".$locale['279']."</strong><span class='pull-right badge'>".number_format($global_submissions['rows'])."</span>");
                    if (count($global_submissions['data']) > 0) {
                        foreach ($global_submissions['data'] as $i => $submit_date) {
                            $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                            echo "<!--Start Submissions Item-->\n";
                            echo "<div data-id='$i' class='submission_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                            echo "<div class='pull-left display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($submit_date, "25px", "", FALSE, "img-rounded m-r-5")."</div>\n";
                            echo "<strong>".profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status'])." </strong>\n";
                            echo "<span class='text-lighter'>".$locale['273b']." <strong>".$submit_data[$submit_date['submit_type']]['submit_locale']."</strong></span><br/>\n";
                            echo timer($submit_date['submit_datestamp'])."<br/>\n";
                            if (!empty($review_link)) {
                                echo "<a class='btn btn-xs btn-default m-t-5' title='".$locale['286']."' href='".$review_link."'>".$locale['286']."</a>\n";
                            }
                            echo "</div>\n";
                            echo "<!--End Submissions Item-->\n";
                        }

                        if (isset($global_submissions['submissions_nav'])) {
                            echo "<div class='clearfix'>\n";
                            echo "<span class='pull-right text-smaller'>".$global_submissions['submissions_nav']."</span>";
                            echo "</div>\n";
                        }
                    } else {
                        echo "<div class='text-center'>".$global_submissions['nodata']."</div>\n";
                    }
                    closeside();
                echo "</div>\n";
            echo "</div>\n"; // .row
        closetable();
    }

    public static function do_admin_icons() {
        global $admin_icons, $admin_images;

        $aidlink = self::get_aidlink();
        $locale = parent::get_locale();
        //add_to_head('<link href="'.THEME.'templates/css/autogrid.css" rel="stylesheet" />');
        opentable($locale['admin_apps']);
        echo "<div class='row'>\n";
        if (count($admin_icons['data']) > 0) {
            foreach ($admin_icons['data'] as $i => $data) {
                echo "<div class='display-table col-xs-12 col-sm-3 col-md-2' style='height:140px;'>\n";
                if ($admin_images) {
                    echo "<div class='panel-body align-middle text-center' style='width:100%;'>\n";
                    echo "<a href='".$data['admin_link'].$aidlink."'><img style='max-width:48px;' src='".get_image("ac_".$data['admin_rights'])."' alt='".$data['admin_title']."'/>\n</a>\n";
                    echo "<div class='overflow-hide'>\n";
                    echo "<a class='icon_title' href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a>\n";
                    echo "</div>\n";
                    echo "</div>\n";
                } else {
                    echo "<span class='small'>".THEME_BULLET." <a href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a></span>";
                }
                echo "</div>\n";
            }
        }
        echo "</div>\n";
        closetable();

    }

}
