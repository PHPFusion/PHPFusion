<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Dashboard.php
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
if (!defined("IN_FUSION")) {
  die("Access Denied");
}

class Dashboard extends PHPFusion\Admins {
    public static function RenderDashboard() {
        global $members, $forum, $download, $news, $articles, $weblinks, $photos, $global_comments, $global_ratings, $global_submissions, $link_type, $submit_type, $comments_type, $locale, $aidlink, $settings, $infusions_count;

        opentable($locale['250']);
            $mobile  = '12';
            $tablet  = '12';
            $laptop  = '6';
            $desktop = '3';

            $panels = array(
                'registered'   => array('link' => '', 'title' => 251),
                'cancelled'    => array('link' => 'status=5', 'title' => 263),
                'unactivated'  => array('link' => 'status=2', 'title' => 252),
                'security_ban' => array('link' => 'status=4', 'title' => 253)
            );

            echo '<div class="members"><div class="row">';
                foreach ($panels as $panel => $block) {
                    $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                    openside('', $panel);
                        echo '<img class="pull-left m-r-10 dashboard-icon" src="'.get_image('ac_M').'" alt="'.$locale['M'].'"/>';
                        echo '<h4 class="text-right m-t-0 m-b-0">'.number_format($members[$panel]).'</h4>';
                        echo '<span class="text-smaller pull-right"><strong>'.$locale[$block['title']].'</strong></span>';

                        $content_ = '<div class="text-right text-uppercase">';
                            $content_ .= '<a href="'.ADMIN.'members.php'.$aidlink.$block['link'].'">'.$locale['255'].' <i class="entypo right-open-mini"></i></a>';
                        $content_ .= '</div>';
                    closeside((checkrights('M') ? $content_ : ''));
                    echo '</div>';
                }
            echo '</div></div>'; // .members

            $desktop = '4';

            echo '<div class="row">';
                if (db_exists(DB_FORUMS)) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                    openside("");
                    echo "<span class='text-smaller text-uppercase'><strong>".$locale['265']." ".$locale['258']."</strong></span>\n<br/>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                    echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_F")."' alt='".$locale['256']."'/>";
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

                if (db_exists(DB_DOWNLOADS)) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                    openside("");
                    echo "<span class='text-smaller text-uppercase'><strong>".$locale['268']." ".$locale['258']."</strong></span>\n<br/>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                    echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_D")."' alt='".$locale['268']."'/>\n";
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

                if (db_exists(DB_NEWS)) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                    openside("");
                    echo "<span class='text-smaller text-uppercase'><strong>".$locale['269']." ".$locale['258']."</strong></span>\n<br/>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                    echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_N")."' alt='".$locale['269']."'/>\n";
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

                if (db_exists(DB_ARTICLES)) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                    openside("");
                    echo "<span class='text-smaller text-uppercase'><strong>".$locale['270']." ".$locale['258']."</strong></span>\n<br/>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                    echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_A")."' alt='".$locale['270']."'/>\n";
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

                if (db_exists(DB_WEBLINKS)) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                    openside("");
                    echo "<span class='text-smaller text-uppercase'><strong>".$locale['271']." ".$locale['258']."</strong></span>\n<br/>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                    echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_W")."' alt='".$locale['271']."'/>\n";
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

                if (db_exists(DB_PHOTOS)) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                    openside("");
                    echo "<span class='text-smaller text-uppercase'><strong>".$locale['272']." ".$locale['258']."</strong></span>\n<br/>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                    echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_PH")."' alt='".$locale['272']."'/>\n";
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
            echo '</div>'; // .row

            echo '<div class="row">';
                echo "<div id='infusions' class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                    openside("<span class='text-smaller text-uppercase'><strong>".$locale['283']."</strong></span><span class='pull-right badge'>".number_format($infusions_count)."</span>");
                    if ($infusions_count > 0) {
                    global $global_infusions;

                    echo "<div class='comment_content'>\n";
                      if (!empty($global_infusions)) {
                        foreach ($global_infusions as $inf_id => $inf_data) {
                          echo "<span class='badge m-b-10 m-r-5'>".$inf_data['inf_title']."</span>\n";
                        }
                      }
                    echo "</div>\n";
                    closeside("".(checkrights("I") ? "<div class='text-right text-uppercase'>\n<a class='text-smaller' href='".ADMIN."infusions.php".$aidlink."'>".$locale['285']." <i class='entypo right-open-mini'></i></a></div>\n" : '')."");
                    } else {
                        echo "<div class='text-center'>".$locale['284']."</div>\n";
                        closeside();
                    }
                    echo "</div>\n"; // #infusions

                echo "<div id='comments' class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                    openside("<span class='text-smaller text-uppercase'><strong>".$locale['277']."</strong></span><span class='pull-right badge'>".number_format($global_comments['rows'])."</span>");
                    if (count($global_comments['data']) > 0) {
                    foreach ($global_comments['data'] as $i => $comment_data) {
                    echo "<!--Start Comment Item-->\n";
                    echo "<div data-id='$i' class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                    echo "<div class='pull-left display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($comment_data, "25px", "", FALSE, "", "")."</div>\n";
                    echo "<div id='comment_action-$i' class='btn-group pull-right display-none' style='position:absolute; right: 30px; margin-top:25px;'>\n<a class='btn btn-xs btn-default' title='".$locale['274']."' href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='entypo eye'></i></a><a class='btn btn-xs btn-default' title='".$locale['275']."' href='".ADMIN."comments.php".$aidlink."&amp;action=edit&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='entypo pencil'></i></a><a class='btn btn-xs btn-default' title='".$locale['276']."' href='".ADMIN."comments.php".$aidlink."&amp;action=delete&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='entypo trash'></i></a></div>\n";
                    echo "<strong>".profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status'])." </strong>\n";
                    echo "<span class='text-lighter'>".$locale['273']."</span> <a href='".sprintf($link_type[$comment_data['comment_type']], $comment_data['comment_item_id'])."'><strong>".$comments_type[$comment_data['comment_type']]."</strong></a>";
                    echo "<br/>\n".timer($comment_data['comment_datestamp'])."<br/>\n";
                    echo "<span class='text-smaller text-lighter'>".trimlink(parseubb($comment_data['comment_message']), 70)."</span>\n";
                    echo "</div>\n";
                    echo "<!--End Comment Item-->\n";
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
                echo "</div>\n"; // #comments

                echo "<div id='ratings' class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                    openside("<span class='text-smaller text-uppercase'><strong>".$locale['278']."</strong></span>");
                    if (count($global_ratings['data']) > 0) {
                      foreach ($global_ratings['data'] as $i => $ratings_data) {
                        echo "<!--Start Rating Item-->\n";
                        echo "<div class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                        echo "<div class='pull-left display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($ratings_data, "25px", "", FALSE, "", "")."</div>\n";
                    echo "<strong>".profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status'])." </strong>\n";
                    echo "<span class='text-lighter'>".$locale['273a']." </span>\n";
                    echo "<a href='".sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id'])."'><strong>".$comments_type[$ratings_data['rating_type']]."</strong></a>";
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
                echo "</div>\n"; // #ratings

                echo "<div id='submissions' class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                    openside("<span class='text-smaller text-uppercase'><strong>".$locale['279']."</strong></span><span class='pull-right badge'>".number_format($global_submissions['rows'])."</span>");
                    if (count($global_submissions['data']) > 0) {
                        foreach ($global_submissions['data'] as $i => $submit_data) {
                            switch ($submit_data['submit_type']) {
                              case "n":
                                $review_link = INFUSIONS."news/news_admin.php".$aidlink."&amp;section=submissions&amp;submit_id=".$submit_data['submit_id'];
                                break;
                              case "a":
                                $review_link = INFUSIONS."articles/articles_admin.php".$aidlink."&amp;section=submissions&amp;submit_id=".$submit_data['submit_id'];
                                break;
                              case "p":
                                $review_link = INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=submissions&amp;submit_id=".$submit_data['submit_id'];
                                break;
                                case "b":
                                    $review_link = INFUSIONS."blog/blog_admin.php".$aidlink."&amp;section=submissions&amp;submit_id=".$submit_data['submit_id'];
                                    break;
                                case "d":
                                    $review_link = INFUSIONS."downloads/downloads_admin.php".$aidlink."&amp;section=submissions&amp;submit_id=".$submit_data['submit_id'];
                                    break;
                                case "l":
                                    $review_link = INFUSIONS."weblinks/weblinks_admin.php".$aidlink."&amp;section=submissions&amp;submit_id=".$submit_data['submit_id'];
                                    break;
                                default:
                                    // @todo: add admin class API to use infusion_db.php to register submission link
                                    $review_link = "";
                            }
                            echo "<!--Start Submissions Item-->\n";
                            echo "<div data-id='$i' class='submission_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                            echo "<div class='pull-left display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($submit_data, "25px", "", FALSE, "", "")."</div>\n";
                            echo "<strong>".profile_link($submit_data['user_id'], $submit_data['user_name'], $submit_data['user_status'])." </strong>\n";
                            echo "<span class='text-lighter'>".$locale['273b']." <strong>".$submit_type[$submit_data['submit_type']]."</strong></span><br/>\n";
                            echo timer($submit_data['submit_datestamp'])."<br/>\n";
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
                echo "</div>\n"; // #submissions
            echo '</div>'; // .row
        closetable();

        add_to_jquery("
            $('.comment_content').hover(function() {
                $('#comment_action-'+$(this).data('id')).removeClass('display-none');
            },function(){
                $('#comment_action-'+$(this).data('id')).addClass('display-none');
            });
            $('.submission_content').hover(function() {
                $('#submission_action-'+$(this).data('id')).removeClass('display-none');
            },function() {
                $('#submission_action-'+$(this).data('id')).addClass('display-none');
            });
        ");
    }

    public static function AdminIcons() {
        global $admin_icons, $admin_images;
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $admin_title = str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['200']);

        opentable($admin_title);
            echo '<div class="admin-content-icons">';
                if (count($admin_icons['data']) > 0) {
                    foreach ($admin_icons['data'] as $i => $data) {
                        echo '<div class="icon-wrapper col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                            if ($admin_images) {
                                echo '<div class="icon-container">';
                                    echo "<a href='".$data['admin_link'].$aidlink."'>";
                                        echo "<img src='".get_image("ac_".$data['admin_rights'])."' alt='".$data['admin_title']."'/>";
                                    echo '</a>';
                                    echo '<div class="overflow-hide">';
                                        echo "<a class='icon-title' href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a>";
                                    echo '</div>';
                                echo '</div>';
                            } else {
                                echo '<span class="small"><a href="'.$data['admin_link'].$aidlink.'">'.$data['admin_title'].'</a></span>';
                            }
                        echo '</div>';
                    }
                }
            echo '</div>';
        closetable();
    }

    public static function AdminDashboard() {
        if (isset($_GET['os'])) {
            render_admin_icon();
        } elseif (isset($_GET['pagenum']) && $_GET['pagenum'] > 0) {
            render_admin_icon();
        } else {
            render_dashboard();
        }
    }
}
