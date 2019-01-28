<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminLTE/classes/Dashboard.inc
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
namespace AdminLTE;

class Dashboard {
    private static function RenderDashboard() {
        global $members, $forum, $download, $news, $articles, $weblinks, $photos, $global_comments,
               $global_ratings, $global_submissions, $link_type, $submit_data,
               $submit_type, $submit_link, // for 9.0
               $comments_type, $infusions_count, $global_infusions;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $settings = fusion_get_settings();

        opentable($locale['250'], '', FALSE);
            $mobile  = 12;
            $tablet  = 6;
            $laptop  = 3;
            $desktop = 3;

            $panels = [
                'registered'   => ['link' => '', 'title' => 251, 'bg' => 'green', 'icon' => 'users'],
                'cancelled'    => ['link' => 'status=5', 'title' => 263, 'bg' => 'yellow', 'icon' => 'user-times'],
                'unactivated'  => ['link' => 'status=2', 'title' => 252, 'bg' => 'aqua', 'icon' => 'user-secret'],
                'security_ban' => ['link' => 'status=4', 'title' => 253, 'bg' => 'red', 'icon' => 'user-o']
            ];

            echo '<div class="row">';
                foreach ($panels as $panel => $block) {
                    $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];

                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        echo '<div class="small-box bg-'.$block['bg'].'">';
                            echo '<div class="inner">';
                                echo '<h3>'.number_format($members[$panel]).'</h3>';
                                echo '<p>'.$locale[$block['title']].'</p>';
                            echo '</div>';

                            echo '<div class="icon"><i class="fa fa-'.$block['icon'].'"></i></div>';

                            $content_  = '<a href="'.ADMIN.'members.php'.$aidlink.$block['link'].'" class="small-box-footer">';
                            $content_ .= $locale['255'].' <i class="fa fa-arrow-circle-right"></i>';
                            $content_ .= '</a>';
                            echo checkrights('M') ? $content_ : '';
                        echo '</div>';
                    echo '</div>';
                }
            echo '</div>';

            $laptop  = 6;
            $desktop = 4;

            echo '<div class="row">';
                $forum_ = function_exists('infusion_exists') ? infusion_exists('forum') : db_exists(DB_PREFIX.'forums');
                if ($forum_) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        echo '<div class="info-box">';
                            echo '<span class="info-box-icon bg-light-blue"><i class="fa fa-comments"></i></span>';
                            echo '<div class="info-box-content">';
                                echo '<strong class="info-box-text">'.$locale['265'].' '.$locale['258'].'</strong>';

                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['265'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($forum['count']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['256'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($forum['thread']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['259'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($forum['post']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['260'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($forum['users']).'</h4>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }

                $downloads_ = function_exists('infusion_exists') ? infusion_exists('downloads') : db_exists(DB_PREFIX.'downloads');
                if ($downloads_) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        echo '<div class="info-box">';
                            echo '<span class="info-box-icon bg-light-blue"><i class="fa fa-cloud-download"></i></span>';
                            echo '<div class="info-box-content">';
                                echo '<strong class="info-box-text">'.$locale['268'].' '.$locale['258'].'</strong>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['268'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($download['download']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($download['comment']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($download['submit']).'</h4>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }

                $news_ = function_exists('infusion_exists') ? infusion_exists('news') : db_exists(DB_PREFIX.'news');
                if ($news_) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        echo '<div class="info-box">';
                            echo '<span class="info-box-icon bg-light-blue"><i class="fa fa-newspaper-o"></i></span>';
                            echo '<div class="info-box-content">';
                                echo '<strong class="info-box-text">'.$locale['269'].' '.$locale['258'].'</strong>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['269'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($news['news']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($news['comment']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($news['submit']).'</h4>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }

                $articles_ = function_exists('infusion_exists') ? infusion_exists('articles') : db_exists(DB_PREFIX.'articles');
                if ($articles_) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        echo '<div class="info-box">';
                            echo '<span class="info-box-icon bg-light-blue"><i class="fa fa-book"></i></span>';
                            echo '<div class="info-box-content">';
                                echo '<strong class="info-box-text">'.$locale['270'].' '.$locale['258'].'</strong>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['270'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($articles['article']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($articles['comment']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($articles['submit']).'</h4>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }

                $weblinks_ = function_exists('infusion_exists') ? infusion_exists('weblinks') : db_exists(DB_PREFIX.'weblinks');
                if ($weblinks_) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        echo '<div class="info-box">';
                            echo '<span class="info-box-icon bg-light-blue"><i class="fa fa-link"></i></span>';
                            echo '<div class="info-box-content">';
                                echo '<strong class="info-box-text">'.$locale['271'].' '.$locale['258'].'</strong>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['271'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($weblinks['weblink']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($weblinks['submit']).'</h4>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }

                $gallery_ = function_exists('infusion_exists') ? infusion_exists('gallery') : db_exists(DB_PREFIX.'photos');
                if ($gallery_) {
                    echo '<div class="col-xs-'.$mobile.' col-sm-'.$tablet.' col-md-'.$laptop.' col-lg-'.$desktop.' block">';
                        echo '<div class="info-box">';
                            echo '<span class="info-box-icon bg-light-blue"><i class="fa fa-camera-retro"></i></span>';
                            echo '<div class="info-box-content">';
                                echo '<strong class="info-box-text">'.$locale['272'].' '.$locale['258'].'</strong>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['261'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($photos['photo']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['257'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($photos['comment']).'</h4>';
                                echo '</div>';
                                echo '<div class="pull-left display-inline-block m-r-5">';
                                    echo '<span class="text-smaller">'.$locale['254'].'</span><br/>';
                                    echo '<h4 class="m-t-0">'.number_format($photos['submit']).'</h4>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
            echo '</div>';

            echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
                    if ($settings['comments_enabled'] == 1) {
                        echo '<div id="comments">';
                            openside('<i class="fa fa-comments-o"></i> <strong class="text-uppercase">'.$locale['277'].'</strong><span class="pull-right badge bg-blue">'.number_format($global_comments['rows']).'</span>');
                                if (count($global_comments['data']) > 0) {
                                    foreach ($global_comments['data'] as $i => $comment_data) {
                                        echo '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                            echo '<div id="comment_action-'.$i.'" class="btn-group btn-group-xs pull-right m-t-10">';
                                                echo '<a class="btn btn-primary" title="'.$locale['274'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-eye"></i></a>';
                                                echo '<a class="btn btn-warning" title="'.$locale['275'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;action=edit&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-pencil"></i></a>';
                                                echo '<a class="btn btn-danger" title="'.$locale['276'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;action=delete&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-trash"></i></a>';
                                            echo '</div>';
                                            echo '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($comment_data, '25px', '', FALSE, 'img-circle m-r-5').'</div>';
                                            echo '<strong>'.(!empty($comment_data['user_id']) ? profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']) : $comment_data['comment_name']).' </strong>';
                                            echo $locale['273'].' <a href="'.sprintf($link_type[$comment_data['comment_type']], $comment_data['comment_item_id']).'"><strong>'.$comments_type[$comment_data['comment_type']].'</strong></a> ';
                                            echo timer($comment_data['comment_datestamp']).'<br/>';
                                            $comment = trimlink(strip_tags(parse_textarea($comment_data['comment_message'], FALSE, TRUE)), 130);
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
                    }

                    if ($settings['ratings_enabled'] == 1) {
                        echo '<div id="ratings">';
                            openside('<i class="fa fa-star-o"></i> <strong class="text-uppercase">'.$locale['278'].'</strong><span class="pull-right badge bg-blue">'.number_format($global_ratings['rows']).'</span>');
                                if (count($global_ratings['data']) > 0) {
                                    foreach ($global_ratings['data'] as $i => $ratings_data) {
                                        echo '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                            echo '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($ratings_data, '25px', '', FALSE, 'img-circle m-r-5').'</div>';
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
                    }

                    echo '<div id="submissions">';
                        openside('<i class="fa fa-cloud-upload"></i> <strong class="text-uppercase">'.$locale['279'].'</strong><span class="pull-right badge bg-blue">'.number_format($global_submissions['rows']).'</span>');
                            if (count($global_submissions['data']) > 0) {
                                if (!empty($submit_data)) {
                                    foreach ($global_submissions['data'] as $i => $submit_date) {
                                        $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                                        echo '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                            echo '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($submit_date, '25px', '', FALSE, 'img-circle m-r-5').'</div>';
                                            echo '<strong>'.profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status']).' </strong>';
                                            echo $locale['273b'].' <strong>'.$submit_data[$submit_date['submit_type']]['submit_locale'].'</strong> ';
                                            echo timer($submit_date['submit_datestamp']);
                                            if (!empty($review_link)) {
                                                echo '<a class="btn btn-sm btn-default m-l-10 pull-right" href="'.$review_link.'">'.$locale['286'].'</a>';
                                            }
                                        echo '</div>';
                                    }
                                } else { // for 9.0
                                    foreach ($global_submissions['data'] as $i => $submit_data) {
                                        $review_link = sprintf($submit_link[$submit_data['submit_type']], $submit_data['submit_id']);
                                        echo '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                            echo '<div class="pull-left display-inline-block m-t-0 m-b-0">'.display_avatar($submit_data, '25px', '', FALSE, 'img-rounded m-r-5').'</div>';
                                            echo '<strong>'.profile_link($submit_data['user_id'], $submit_data['user_name'], $submit_data['user_status']).' </strong>';
                                            echo $locale['273b'].' <strong>'.$submit_type[$submit_data['submit_type']].'</strong> ';
                                            echo timer($submit_data['submit_datestamp']);
                                            if (!empty($review_link)) {
                                                echo '<a class="btn btn-sm btn-default m-l-10 pull-right" href="'.$review_link.'">'.$locale['286'].'</a>';
                                            }
                                        echo '</div>';
                                    }
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

                echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
                    echo '<div id="infusions">';
                        openside('<i class="fa fa-cubes"></i> <strong class="text-uppercase">'.$locale['283'].'</strong><span class="pull-right badge bg-blue">'.number_format((int)$infusions_count).'</span>');
                            $content = '';
                            $bg_colors = ['gray', 'red', 'yellow', 'aqua', 'blue', 'light-blue', 'green', 'navy', 'teal', 'olive', 'orange', 'fuschia', 'purple', 'maroon', ''];

                            if ($infusions_count > 0) {
                                if (!empty($global_infusions)) {
                                    foreach ($global_infusions as $inf_id => $inf_data) {
                                        $color = array_rand($bg_colors);

                                        echo '<span class="badge bg-'.$bg_colors[$color].' m-b-10 m-r-5">'.$inf_data['inf_title'].'</span>';
                                    }
                                }
                                $content = checkrights('I') ? '<div class="text-right text-uppercase"><a class="text-smaller" href="'.ADMIN.'infusions.php'.$aidlink.'">'.$locale['285'].' <i class="fa fa-arrow-circle-right"></i></a></div>' : '';
                            } else {
                                echo '<div class="text-center">'.$locale['284'].'</div>';
                            }
                        closeside($content);
                    echo '</div>'; // #infusins
                echo '</div>';
            echo '</div>'; // .row

        closetable(FALSE);
    }

    private static function AdminIcons() {
        global $admin_icons, $admin_images;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $admin_title = str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['200']);

        opentable($admin_title);
            echo '<div class="row">';
            if (count($admin_icons['data']) > 0) {
                foreach ($admin_icons['data'] as $i => $data) {
                    echo '<div class="icon-wrapper col-xs-6 col-sm-2 col-md-2 col-lg-2">';
                    if ($admin_images) {
                        echo '<a class="btn btn-app" href="'.$data['admin_link'].$aidlink.'">';
                            echo '<img class="display-block" src="'.get_image('ac_'.$data['admin_rights']).'" alt="'.$data['admin_title'].'"/>';
                            echo '<span>'.$data['admin_title'].'</span>';
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
