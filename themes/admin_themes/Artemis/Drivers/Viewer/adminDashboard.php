<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: adminDashboard.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Artemis\Viewer;

use Artemis\Model\resource;

/**
 * Class adminDashboard
 * Dashboard view
 *
 * @package Artemis\viewer
 */
class adminDashboard extends resource {
    public static function do_dashboard() {
        global $members, $forum, $download, $news, $articles, $weblinks, $photos,
               $global_comments, $global_ratings, $global_submissions, $global_infusions, $link_type, $submit_data, $comments_type, $infusions_count;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $settings = fusion_get_settings();

        $html = '';

        $html .= fusion_get_function('opentable', $locale['250']);
        $grid = ['mobile' => 12, 'tablet' => 6, 'laptop' => 3, 'desktop' => 3];

        $panels = [
            'registered'   => ['link' => '', 'title' => $locale['251']],
            'cancelled'    => ['link' => 'status=5', 'title' => $locale['263']],
            'unactivated'  => ['link' => 'status=2', 'title' => $locale['252']],
            'security_ban' => ['link' => 'status=4', 'title' => $locale['253']]
        ];

        $html .= '<div class="row">';
        foreach ($panels as $panel => $block) {
            $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
            $html .= '<div class="col-xs-'.$grid['mobile'].' col-sm-'.$grid['tablet'].' col-md-'.$grid['laptop'].' col-lg-'.$grid['desktop'].'">';
            $html .= fusion_get_function('openside', '');
            $html .= '<img class="pull-left m-r-10 dashboard-icon" src="'.get_image('ac_M').'" alt="'.$locale['M'].'"/>';
            $html .= '<h4 class="text-right m-t-0 m-b-0">'.number_format($members[$panel]).'</h4>';
            $html .= '<strong class="text-smaller pull-right" style="position: relative;z-index: 3;">'.$block['title'].'</strong>';

            $content = '<div class="text-right text-uppercase">';
            $content .= '<a href="'.ADMIN.'members.php'.$aidlink.$block['link'].'">'.$locale['255'].' <i class="fa fa-angle-right"></i></a>';
            $content .= '</div>';
            $html .= fusion_get_function('closeside', checkrights('M') ? $content : '');
            $html .= '</div>';
        }
        $html .= '</div>';

        $grid = ['mobile' => 12, 'tablet' => 6, 'laptop' => 6, 'desktop' => 4];

        $html .= '<div class="row" id="overview">';
        $modules = [];

        if (defined('FORUM_EXIST')) {
            $modules['forum'] = [
                'title' => $locale['265'],
                'image' => get_image('ac_F'),
                'stats' => [
                    ['title' => $locale['265'], 'count' => $forum['count']],
                    ['title' => $locale['256'], 'count' => $forum['thread']],
                    ['title' => $locale['259'], 'count' => $forum['post']],
                    ['title' => $locale['260'], 'count' => $forum['users']]
                ]
            ];
        }

        if (defined('DOWNLOADS_EXIST')) {
            $modules['downloads'] = [
                'title' => $locale['268'],
                'image' => get_image('ac_D'),
                'stats' => [
                    ['title' => $locale['268'], 'count' => $download['download']],
                    ['title' => $locale['257'], 'count' => $download['comment']],
                    ['title' => $locale['254'], 'count' => $download['submit']]
                ]
            ];
        }

        if (defined('NEWS_EXIST')) {
            $modules['news'] = [
                'title' => $locale['269'],
                'image' => get_image('ac_N'),
                'stats' => [
                    ['title' => $locale['269'], 'count' => $news['news']],
                    ['title' => $locale['257'], 'count' => $news['comment']],
                    ['title' => $locale['254'], 'count' => $news['submit']]
                ]
            ];
        }

        if (defined('ARTICLES_EXIST')) {
            $modules['articles'] = [
                'title' => $locale['270'],
                'image' => get_image('ac_A'),
                'stats' => [
                    ['title' => $locale['270'], 'count' => $articles['article']],
                    ['title' => $locale['257'], 'count' => $articles['comment']],
                    ['title' => $locale['254'], 'count' => $articles['submit']]
                ]
            ];
        }

        if (defined('WEBLINKS_EXIST')) {
            $modules['weblinks'] = [
                'title' => $locale['271'],
                'image' => get_image('ac_W'),
                'stats' => [
                    ['title' => $locale['271'], 'count' => $weblinks['weblink']],
                    ['title' => $locale['254'], 'count' => $weblinks['submit']]
                ]
            ];
        }

        if (defined('GALLERY_EXIST')) {
            $modules['gallery'] = [
                'title' => $locale['272'],
                'image' => get_image('ac_PH'),
                'stats' => [
                    ['title' => $locale['261'], 'count' => $photos['photo']],
                    ['title' => $locale['257'], 'count' => $photos['comment']],
                    ['title' => $locale['254'], 'count' => $photos['submit']]
                ]
            ];
        }

        if (!empty($modules)) {
            foreach ($modules as $module) {
                $html .= '<div class="col-xs-'.$grid['mobile'].' col-sm-'.$grid['tablet'].' col-md-'.$grid['laptop'].' col-lg-'.$grid['desktop'].'">';
                $html .= fusion_get_function('openside', '');
                $html .= '<strong class="text-uppercase">'.$module['title'].' '.$locale['258'].'</strong>';
                $html .= '<div class="clearfix m-t-10">';
                $html .= '<img class="img-responsive pull-right dashboard-icon" src="'.$module['image'].'" alt="'.$module['title'].'"/>';
                if (!empty($module['stats'])) {
                    foreach ($module['stats'] as $stat) {
                        $html .= '<div class="pull-left display-inline-block m-r-15">';
                        $html .= '<span class="text-smaller">'.$stat['title'].'</span><br/>';
                        $html .= '<h4 class="m-t-0">'.number_format($stat['count']).'</h4>';
                        $html .= '</div>';
                    }
                }
                $html .= '</div>';
                $html .= fusion_get_function('closeside', '');
                $html .= '</div>';
            }
        }
        $html .= '</div>';

        $html .= '<div class="row">';
        if ($settings['comments_enabled'] == 1) {
            $html .= '<div class="col-xs-12 co-sm-6 col-md-6 col-lg-3">';
            $html .= '<div id="comments">';
            $html .= fusion_get_function('openside', '<strong class="text-uppercase">'.$locale['277'].'</strong><span class="pull-right badge">'.number_format($global_comments['rows']).'</span>');
            if (count($global_comments['data']) > 0) {
                foreach ($global_comments['data'] as $i => $comment_data) {
                    if (isset($comments_type[$comment_data['comment_type']]) && isset($link_type[$comment_data['comment_type']])) {
                        $html .= "<div data-id='$i' class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >";
                        $html .= "<div class='pull-left display-inline-block' style='margin-top:5px; margin-bottom:10px;'>".display_avatar($comment_data, "25px", "", FALSE, "img-rounded m-r-5")."</div>";
                        $html .= "<div id='comment_action-$i' class='btn-group pull-right' style='position:absolute; right: 30px; margin-top:25px; display:none;'>
                                            <a class='btn btn-xs btn-default' title='".$locale['274']."' href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-eye'></i></a>
                                            <a class='btn btn-xs btn-default' title='".$locale['275']."' href='".ADMIN."comments.php".$aidlink."&amp;action=edit&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-pencil'></i></a>
                                            <a class='btn btn-xs btn-default' title='".$locale['276']."' href='".ADMIN."comments.php".$aidlink."&amp;action=delete&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-trash'></i></a></div>";
                        $html .= "<strong>".(!empty($comment_data['user_id']) ? profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']) : $comment_data['comment_name'])." </strong>";
                        $html .= "<span class='text-lighter'>".$locale['273']."</span> <a href='".sprintf($link_type[$comment_data['comment_type']], $comment_data['comment_item_id'])."'><strong>".$comments_type[$comment_data['comment_type']]."</strong></a>";
                        $html .= "<br/>".timer($comment_data['comment_datestamp'])."<br/>";
                        $comment = trimlink(strip_tags(parse_textarea($comment_data['comment_message'], FALSE, TRUE)), 70);
                        $html .= "<span class='text-smaller text-lighter'>".parse_textarea($comment, TRUE, FALSE)."</span>";
                        $html .= "</div>";
                    }
                }

                if (isset($global_comments['comments_nav'])) {
                    $html .= '<div class="clearfix"><span class="pull-right text-smaller">'.$global_comments['comments_nav'].'</span></div>';
                }
            } else {
                $html .= '<div class="text-center">'.$global_comments['nodata'].'</div>';
            }
            $html .= fusion_get_function('closeside', '');
            $html .= '</div>'; // #comments
            $html .= '</div>';
        }

        if ($settings['ratings_enabled'] == 1) {
            $html .= '<div class="col-xs-12 co-sm-6 col-md-6 col-lg-3">';
            $html .= '<div id="ratings">';
            $html .= fusion_get_function('openside', '<strong class="text-uppercase">'.$locale['278'].'</strong><span class="pull-right badge">'.number_format($global_ratings['rows']).'</span>');
            if (count($global_ratings['data']) > 0) {
                foreach ($global_ratings['data'] as $i => $ratings_data) {
                    if (isset($link_type[$ratings_data['rating_type']]) && isset($comments_type[$ratings_data['rating_type']])) {
                        $html .= '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                        $html .= '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($ratings_data, '25px', '', FALSE, 'img-rounded m-r-5').'</div>';
                        $html .= '<strong>'.profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status']).' </strong>';
                        $html .= $locale['273a'].' <a href="'.sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id']).'"><strong>'.$comments_type[$ratings_data['rating_type']].'</strong></a> ';
                        $html .= timer($ratings_data['rating_datestamp']);
                        $html .= '<span class="text-warning m-l-10">'.str_repeat('<i class="fa fa-star fa-fw"></i>', $ratings_data['rating_vote']).'</span>';
                        $html .= '</div>';
                    }
                }

                if (isset($global_ratings['ratings_nav'])) {
                    $html .= '<div class="clearfix"><span class="pull-right text-smaller">'.$global_ratings['ratings_nav'].'</span></div>';
                }
            } else {
                $html .= '<div class="text-center">'.$global_ratings['nodata'].'</div>';
            }
            $html .= fusion_get_function('closeside', '');
            $html .= '</div>'; // #ratings
            $html .= '</div>';
        }

        $html .= '<div class="col-xs-12 co-sm-6 col-md-6 col-lg-3">';
        $html .= '<div id="submissions">';
        $html .= fusion_get_function('openside', '<strong class="text-uppercase">'.$locale['279'].'</strong><span class="pull-right badge">'.number_format($global_submissions['rows']).'</span>');
        if (count($global_submissions['data']) > 0) {
            foreach ($global_submissions['data'] as $i => $submit_date) {
                if (isset($submit_data[$submit_date['submit_type']])) {
                    $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                    $html .= '<div data-id="'.$i.'" class="submission_content clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                    $html .= '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($submit_date, '25px', '', FALSE, 'img-rounded m-r-5').'</div>';
                    $html .= '<strong>'.profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status']).' </strong>';
                    $html .= $locale['273b'].' <strong>'.$submit_data[$submit_date['submit_type']]['submit_locale'].'</strong> ';
                    $html .= timer($submit_date['submit_datestamp']);
                    if (!empty($review_link)) {
                        $html .= '<a class="btn btn-xs btn-default m-l-10 pull-right" style="display:none;" id="submission_action-'.$i.'" href="'.$review_link.'">'.$locale['286'].'</a>';
                    }
                    $html .= '</div>';
                }
            }

            if (isset($global_submissions['submissions_nav'])) {
                $html .= '<div class="clearfix"><span class="pull-right text-smaller">'.$global_submissions['submissions_nav'].'</span></div>';
            }
        } else {
            $html .= '<div class="text-center">'.$global_submissions['nodata'].'</div>';
        }
        $html .= fusion_get_function('closeside', '');
        $html .= '</div>'; // #submissions
        $html .= '</div>';

        $html .= '<div class="col-xs-12 co-sm-6 col-md-6 col-lg-3">';
        $html .= '<div id="infusions">';
        $html .= fusion_get_function('openside', '<strong class="text-uppercase">'.$locale['283'].'</strong><span class="pull-right badge">'.number_format((int)$infusions_count).'</span>');
        $content = '';
        if ($infusions_count > 0) {
            if (!empty($global_infusions)) {
                foreach ($global_infusions as $inf_data) {
                    $html .= '<span class="badge m-b-10 m-r-5">'.$inf_data['inf_title'].'</span>';
                }
            }
            $content = checkrights('I') ? '<div class="text-right text-uppercase"><a class="text-smaller" href="'.ADMIN.'infusions.php'.$aidlink.'">'.$locale['285'].' <i class="fa fa-angle-right"></i></a></div>' : '';
        } else {
            $html .= '<div class="text-center">'.$locale['284'].'</div>';
        }
        $html .= fusion_get_function('closeside', $content);
        $html .= '</div>'; // #infusins
        $html .= '</div>';
        $html .= '</div>'; // .row
        $html .= fusion_get_function('closetable');

        add_to_jquery("
            $('.comment_content').hover(function() {
                $('#comment_action-'+$(this).data('id')).show();
            },function() {
                $('#comment_action-'+$(this).data('id')).hide();
            });
            $('.submission_content').hover(function() {
                $('#submission_action-'+$(this).data('id')).show();
            },function() {
                $('#submission_action-'+$(this).data('id')).hide();
            });
        ");

        return $html;
    }

    public static function do_admin_icons() {
        global $admin_icons, $admin_images;

        $aidlink = self::get_aidlink();
        $locale = parent::get_locale();

        $html = fusion_get_function('opentable', $locale['admin_apps']);
        $html .= "<div class='row'>\n";
        if (count($admin_icons['data']) > 0) {
            foreach ($admin_icons['data'] as $i => $data) {
                $html .= "<div class='display-table col-xs-12 col-sm-3 col-md-2' style='height:140px;'>\n";
                if ($admin_images) {
                    $html .= "<div class='panel-body align-middle text-center' style='width:100%;'>\n";
                    $html .= "<a href='".$data['admin_link'].$aidlink."'><img style='max-width:48px;' alt='".$data['admin_title']."' src='".get_image("ac_".$data['admin_rights'])."'/>\n</a>\n";
                    $html .= "<div class='overflow-hide'>\n";
                    $html .= "<a class='icon_title' href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a>\n";
                    $html .= "</div>\n";
                    $html .= "</div>\n";
                } else {
                    $html .= "<span class='small'>".THEME_BULLET." <a href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a></span>";
                }
                $html .= "</div>\n";
            }
        }
        $html .= fusion_get_function('closetable');

        return $html;
    }

}
