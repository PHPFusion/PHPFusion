<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Dashboard.inc
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
namespace Material;

class Dashboard {
    private static function RenderDashboard() {
        global $members, $forum, $download, $news, $articles, $weblinks, $photos, $global_comments,
            $global_ratings, $global_submissions, $link_type, $submit_data,
            $comments_type, $infusions_count, $global_infusions;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();

        opentable($locale['250']);
            $mobile  = '12';
            $tablet  = '6';
            $laptop  = '6';
            $desktop = '3';

            $panels = [
                'registered'   => ['link' => '', 'title' => 251],
                'cancelled'    => ['link' => 'status=5', 'title' => 263],
                'unactivated'  => ['link' => 'status=2', 'title' => 252],
                'security_ban' => ['link' => 'status=4', 'title' => 253]
            ];

            echo '<div class="members">';
                echo '<div class="row">';
                    foreach ($panels as $panel => $block) {
                        $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
                        echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        openside('', $panel);
                            echo '<img class="pull-left m-r-10 dashboard-icon" src="'.get_image('ac_M').'" alt="'.$locale['M'].'"/>';
                            echo '<h4 class="text-right m-t-0 m-b-0">'.number_format($members[$panel]).'</h4>';
                            echo '<span class="text-smaller pull-right"><strong>'.$locale[$block['title']].'</strong></span>';

                            $content_  = '<div class="text-right text-uppercase">';
                            $content_ .= '<a href="'.ADMIN.'members.php'.$aidlink.$block['link'].'">'.$locale['255'].' <i class="fa fa-angle-right"></i></a>';
                            $content_ .= '</div>';
                        closeside(checkrights('M') ? $content_ : '');
                        echo '</div>';
                    }
                echo '</div>';
            echo '</div>';

            $desktop = '4';

            echo '<div class="row">';
                if (infusion_exists('forum')) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        openside('');
                        echo '<strong class="text-uppercase">'.$locale['265'].' '.$locale['258'].'</strong>';
                        echo '<div class="clearfix m-t-10">';
                            echo '<img class="img-responsive pull-right dashboard-icon" src="'.get_image("ac_F").'" alt="'.$locale['265'].'"/>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['265'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($forum['count']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['256'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($forum['thread']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['259'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($forum['post']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['260'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($forum['users']).'</h4>';
                            echo '</div>';
                        echo '</div>';
                        closeside();
                    echo '</div>';
                }

                if (infusion_exists('downloads')) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        openside('');
                        echo '<strong class="text-uppercase">'.$locale['268'].' '.$locale['258'].'</strong>';
                        echo '<div class="clearfix m-t-10">';
                            echo '<img class="img-responsive pull-right dashboard-icon" src="'.get_image("ac_D").'" alt="'.$locale['268'].'"/>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['268'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($download['download']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($download['comment']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($download['submit']).'</h4>';
                            echo '</div>';
                        echo '</div>';
                        closeside();
                    echo '</div>';
                }

                if (infusion_exists('news')) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        openside('');
                        echo '<strong class="text-uppercase">'.$locale['269'].' '.$locale['258'].'</strong>';
                        echo '<div class="clearfix m-t-10">';
                            echo '<img class="img-responsive pull-right dashboard-icon" src="'.get_image("ac_N").'" alt="'.$locale['269'].'"/>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['269'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($news['news']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($news['comment']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($news['submit']).'</h4>';
                            echo '</div>';
                        echo '</div>';
                        closeside();
                    echo '</div>';
                }

                if (infusion_exists('articles')) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        openside('');
                        echo '<strong class="text-uppercase">'.$locale['270'].' '.$locale['258'].'</strong>';
                        echo '<div class="clearfix m-t-10">';
                            echo '<img class="img-responsive pull-right dashboard-icon" src="'.get_image("ac_A").'" alt="'.$locale['270'].'"/>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['270'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($articles['article']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($articles['comment']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($articles['submit']).'</h4>';
                            echo '</div>';
                        echo '</div>';
                        closeside();
                    echo '</div>';
                }

                if (infusion_exists('weblinks')) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        openside('');
                        echo '<strong class="text-uppercase">'.$locale['271'].' '.$locale['258'].'</strong>';
                        echo '<div class="clearfix m-t-10">';
                            echo '<img class="img-responsive pull-right dashboard-icon" src="'.get_image("ac_W").'" alt="'.$locale['271'].'"/>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['271'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($weblinks['weblink']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($weblinks['comment']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($weblinks['submit']).'</h4>';
                            echo '</div>';
                        echo '</div>';
                        closeside();
                    echo "</div>";
                }

                if (infusion_exists('gallery')) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        openside('');
                        echo '<strong class="text-uppercase">'.$locale['272'].' '.$locale['258'].'</strong>';
                        echo '<div class="clearfix m-t-10">';
                            echo '<img class="img-responsive pull-right dashboard-icon" src="'.get_image("ac_PH").'" alt="'.$locale['272'].'"/>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['272'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($photos['photo']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($photos['comment']).'</h4>';
                            echo '</div>';
                            echo '<div class="pull-left display-inline-block m-r-15">';
                                echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                echo '<h4 class="m-t-0">'.number_format($photos['submit']).'</h4>';
                            echo '</div>';
                        echo '</div>';
                        closeside();
                    echo '</div>';
                }
            echo '</div>';

            echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
                    echo '<div id="infusions">';
                        openside('<strong class="text-uppercase">'.$locale['283'].'</strong><span class="pull-right badge">'.number_format((int)$infusions_count).'</span>');
                            $content = '';
                            if ($infusions_count > 0) {
                                if (!empty($global_infusions)) {
                                    foreach ($global_infusions as $inf_id => $inf_data) {
                                        echo '<span class="badge m-b-10 m-r-5">'.$inf_data['inf_title'].'</span>';
                                    }
                                }
                                $content = checkrights('I') ? '<div class="text-right text-uppercase"><a class="text-smaller" href="'.ADMIN.'infusions.php'.$aidlink.'">'.$locale['285'].' <i class="fa fa-angle-right"></i></a></div>' : '';
                            } else {
                                echo '<div class="text-center">'.$locale['284'].'</div>';
                            }
                        closeside($content);
                    echo '</div>'; // #infusins
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
                    echo '<div id="comments">';
                        openside('<strong class="text-uppercase">'.$locale['277'].'</strong><span class="pull-right badge">'.number_format($global_comments['rows']).'</span>');
                            if (count($global_comments['data']) > 0) {
                                foreach ($global_comments['data'] as $i => $comment_data) {
                                    echo '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                        echo '<div id="comment_action-'.$i.'" class="btn-group btn-group-xs pull-right m-t-10">';
                                            echo '<a class="btn btn-default" title="'.$locale['274'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-eye"></i></a>';
                                            echo '<a class="btn btn-default" title="'.$locale['275'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;action=edit&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-pencil"></i></a>';
                                            echo '<a class="btn btn-default" title="'.$locale['276'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;action=delete&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-trash"></i></a>';
                                        echo '</div>';
                                        echo '<div class="pull-left display-inline-block m-t-0 m-b-0">'.display_avatar($comment_data, '25px', '', FALSE, 'img-rounded m-r-5').'</div>';
                                        echo '<strong>'.profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']).' </strong>';
                                        echo $locale['273'].' <a href="'.sprintf($link_type[$comment_data['comment_type']], $comment_data['comment_item_id']).'"><strong>'.$comments_type[$comment_data['comment_type']].'</strong></a> ';
                                        echo timer($comment_data['comment_datestamp']).'<br/>';
                                        $comment = trimlink(parse_textarea($comment_data['comment_message'], FALSE, FALSE), 130);
                                        echo '<span class="text-smaller">'.parse_textarea($comment, TRUE, FALSE).'</span>';
                                    echo '</div>';
                                }

                                if (isset($global_comments['comments_nav'])) {
                                    echo '<div class="clearfix">';
                                        echo '<span class="pull-right text-smaller">'.$global_comments['comments_nav'].'</span>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="text-center">'.$global_comments['nodata'].'</div>';
                            }
                        closeside();
                    echo '</div>'; // #comments

                    echo '<div id="ratings">';
                        openside('<strong class="text-uppercase">'.$locale['278'].'</strong><span class="pull-right badge">'.number_format($global_ratings['rows']).'</span>');
                            if (count($global_ratings['data']) > 0) {
                                foreach ($global_ratings['data'] as $i => $ratings_data) {
                                    echo '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                        echo '<div class="pull-left display-inline-block m-t-0 m-b-0">'.display_avatar($ratings_data, '25px', '', FALSE, 'img-rounded m-r-5').'</div>';
                                        echo '<strong>'.profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status']).' </strong>';
                                        echo $locale['273a'].' <a href="'.sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id']).'"><strong>'.$comments_type[$ratings_data['rating_type']].'</strong></a> ';
                                        echo timer($ratings_data['rating_datestamp']);
                                        echo '<span class="text-warning m-l-10">'.str_repeat('<i class="fa fa-star fa-fw"></i>', $ratings_data['rating_vote']).'</span>';
                                    echo '</div>';
                                }

                                if (isset($global_ratings['ratings_nav'])) {
                                    echo '<div class="clearfix">';
                                        echo '<span class="pull-right text-smaller">'.$global_ratings['ratings_nav'].'</span>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="text-center">'.$global_ratings['nodata'].'</div>';
                            }
                        closeside();
                    echo '</div>'; // #ratings

                    echo '<div id="submissions">';
                        openside('<strong class="text-uppercase">'.$locale['279'].'</strong><span class="pull-right badge">'.number_format($global_submissions['rows']).'</span>');
                            if (count($global_submissions['data']) > 0) {
                                foreach ($global_submissions['data'] as $i => $submit_date) {
                                    $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                                    echo '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                        echo '<div class="pull-left display-inline-block m-t-0 m-b-0">'.display_avatar($submit_date, '25px', '', FALSE, 'img-rounded m-r-5').'</div>';
                                        echo '<strong>'.profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status']).' </strong>';
                                        echo $locale['273b'].' <strong>'.$submit_data[$submit_date['submit_type']]['submit_locale'].'</strong> ';
                                        echo timer($submit_date['submit_datestamp']);
                                        if (!empty($review_link)) {
                                            echo '<a class="btn btn-sm btn-default m-l-10 pull-right" href="'.$review_link.'">'.$locale['286'].'</a>';
                                        }
                                    echo '</div>';
                                }

                                if (isset($global_submissions['submissions_nav'])) {
                                    echo '<div class="clearfix">';
                                        echo '<span class="pull-right text-smaller">'.$global_submissions['submissions_nav'].'</span>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="text-center">'.$global_submissions['nodata'].'</div>';
                            }
                        closeside();
                    echo '</div>'; // #submissions
                echo '</div>';
            echo '</div>'; // .row
        closetable();
    }

    private static function AdminIcons() {
        global $admin_icons, $admin_images;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $admin_title = str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['200']);

        opentable($admin_title);
            echo '<div class="admin-content-icons">';
                if (count($admin_icons['data']) > 0) {
                    foreach ($admin_icons['data'] as $i => $data) {
                        echo '<div class="icon-wrapper col-xs-6 col-sm-2 col-md-2 col-lg-2">';
                            if ($admin_images) {
                                echo '<a class="icon-container text-center" href="'.$data['admin_link'].$aidlink.'">';
                                    echo '<img class="display-block" src="'.get_image('ac_'.$data['admin_rights']).'" alt="'.$data['admin_title'].'"/>';
                                    echo '<div class="overflow-hide">';
                                        echo '<span class="icon-title">'.$data['admin_title'].'</span>';
                                    echo '</div>';
                                echo '</a>';
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
        if (isset($_GET['os']) or (isset($_GET['pagenum']) && $_GET['pagenum']) > 0) {
            self::AdminIcons();
        } else {
            self::RenderDashboard();
        }
    }
}
