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
    public function __construct() {
        $pagenum = (int)filter_input(INPUT_GET, 'pagenum');

        if ((isset($pagenum) && $pagenum) > 0) {
            $html = $this->AdminIcons();
        } else {
            $html = $this->RenderDashboard();
        }

        echo $html;
    }

    private function RenderDashboard() {
        global $members, $forum, $download, $news, $articles, $weblinks, $photos,
               $global_comments, $global_ratings, $global_submissions, $global_infusions, $link_type, $submit_data, $comments_type, $infusions_count;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $settings = fusion_get_settings();

        $html = '';
        $html .= fusion_get_function('opentable', $locale['250'], '', FALSE);
            $grid = ['mobile' => 12, 'tablet' => 6, 'laptop' => 3, 'desktop' => 3];

            $panels = [
                'registered'   => ['link' => '', 'title' => $locale['251'], 'bg' => 'green', 'icon' => 'users'],
                'cancelled'    => ['link' => 'status=5', 'title' => $locale['263'], 'bg' => 'yellow', 'icon' => 'user-times'],
                'unactivated'  => ['link' => 'status=2', 'title' => $locale['252'], 'bg' => 'aqua', 'icon' => 'user-secret'],
                'security_ban' => ['link' => 'status=4', 'title' => $locale['253'], 'bg' => 'red', 'icon' => 'user-slash']
            ];

            $html .= '<div class="row">';
                foreach ($panels as $panel => $block) {
                    $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];

                    $html .= '<div class="col-xs-'.$grid['mobile'].' col-sm-'.$grid['tablet'].' col-md-'.$grid['laptop'].' col-lg-'.$grid['desktop'].' block">';
                        $html .= '<div class="small-box bg-'.$block['bg'].'">';
                            $html .= '<div class="inner">';
                                $html .= '<h3>'.number_format($members[$panel]).'</h3>';
                                $html .= '<p>'.$block['title'].'</p>';
                            $html .= '</div>';

                            $html .= '<div class="icon"><i class="fa fa-'.$block['icon'].'"></i></div>';

                            $content  = '<a href="'.ADMIN.'members.php'.$aidlink.$block['link'].'" class="small-box-footer">';
                            $content .= $locale['255'].' <i class="fa fa-arrow-circle-right"></i>';
                            $content .= '</a>';
                            $html .= checkrights('M') ? $content : '';
                        $html .= '</div>';
                    $html .= '</div>';
                }
            $html .= '</div>';

            $grid = ['mobile' => 12, 'tablet' => 6, 'laptop' => 6, 'desktop' => 4];

            $html .= '<div class="row">';
                $modules = [];

                if (defined('FORUM_EXIST')) {
                    $modules['forum'] = [
                        'title' => $locale['265'],
                        'icon' => 'fa fa-comments',
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
                        'icon' => 'fa fa-cloud-download',
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
                        'icon' => 'fa fa-newspaper-o',
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
                        'icon' => 'fa fa-book',
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
                        'icon' => 'fa fa-link',
                        'stats' => [
                            ['title' => $locale['271'], 'count' => $weblinks['weblink']],
                            ['title' => $locale['254'], 'count' => $weblinks['submit']]
                        ]
                    ];
                }

                if (defined('GALLERY_EXIST')) {
                    $modules['gallery'] = [
                        'title' => $locale['272'],
                        'icon' => 'fa fa-camera-retro',
                        'stats' => [
                            ['title' => $locale['261'], 'count' => $photos['photo']],
                            ['title' => $locale['257'], 'count' => $photos['comment']],
                            ['title' => $locale['254'], 'count' => $photos['submit']]
                        ]
                    ];
                }

                if (!empty($modules)) {
                    foreach ($modules as $name => $module) {
                        $html .= '<div class="col-xs-'.$grid['mobile'].' col-sm-'.$grid['tablet'].' col-md-'.$grid['laptop'].' col-lg-'.$grid['desktop'].' block">';
                            $html .= '<div class="info-box">';
                                $html .= '<span class="info-box-icon bg-light-blue"><i class="'.$module['icon'].'"></i></span>';
                                $html .= '<div class="info-box-content">';
                                    $html .= '<strong class="info-box-text">'.$module['title'].' '.$locale['258'].'</strong>';
                                    if (!empty($module['stats'])) {
                                        foreach ($module['stats'] as $stat) {
                                            $html .= '<div class="pull-left display-inline-block m-r-15">';
                                                $html .= '<span class="text-smaller">'.$stat['title'].'</span><br/>';
                                                $html .= '<h4 class="m-t-0">'.number_format($stat['count']).'</h4>';
                                            $html .= '</div>';
                                        }
                                    }
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    }
                }
            $html .= '</div>';

            $html .= '<div class="row">';
                $html .= '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
                    if ($settings['comments_enabled'] == 1) {
                        $html .= '<div id="comments">';
                            $html .= fusion_get_function('openside', '<i class="fa fa-comments-o"></i> <strong class="text-uppercase">'.$locale['277'].'</strong><span class="pull-right badge bg-blue">'.number_format($global_comments['rows']).'</span>');
                                if (count($global_comments['data']) > 0) {
                                    foreach ($global_comments['data'] as $i => $comment_data) {
                                        if (isset($comments_type[$comment_data['comment_type']]) && isset($link_type[$comment_data['comment_type']])) {
                                            $html .= '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                                $html .= '<div id="comment_action-'.$i.'" class="btn-group btn-group-xs pull-right m-t-10">';
                                                    $html .= '<a class="btn btn-primary" title="'.$locale['274'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-eye"></i></a>';
                                                    $html .= '<a class="btn btn-warning" title="'.$locale['275'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;action=edit&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-pencil"></i></a>';
                                                    $html .= '<a class="btn btn-danger" title="'.$locale['276'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;action=delete&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-trash"></i></a>';
                                                $html .= '</div>';
                                                $html .= '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($comment_data, '25px', '', FALSE, 'img-circle m-r-5').'</div>';
                                                $html .= '<strong>'.(!empty($comment_data['user_id']) ? profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']) : $comment_data['comment_name']).' </strong>';
                                                $html .= $locale['273'].' <a href="'.sprintf($link_type[$comment_data['comment_type']], $comment_data['comment_item_id']).'"><strong>'.$comments_type[$comment_data['comment_type']].'</strong></a> ';
                                                $html .= timer($comment_data['comment_datestamp']).'<br/>';
                                                $comment = trimlink(strip_tags(parse_textarea($comment_data['comment_message'], FALSE, TRUE)), 130);
                                                $html .= '<span class="text-smaller">'.parse_textarea($comment, TRUE, FALSE).'</span>';
                                            $html .= '</div>';
                                        }
                                    }

                                    if (isset($global_comments['comments_nav'])) {
                                        $html .= '<div class="clearfix">';
                                            $html .= '<span class="pull-right text-smaller">'.$global_comments['comments_nav'].'</span>';
                                        $html .= '</div>';
                                    }
                                } else {
                                    $html .= '<div class="text-center">'.$global_comments['nodata'].'</div>';
                                }
                            $html .= fusion_get_function('closeside', '');
                        $html .= '</div>'; // #comments
                    }

                    if ($settings['ratings_enabled'] == 1) {
                        $html .= '<div id="ratings">';
                            $html .= fusion_get_function('openside', '<i class="fa fa-star-o"></i> <strong class="text-uppercase">'.$locale['278'].'</strong><span class="pull-right badge bg-blue">'.number_format($global_ratings['rows']).'</span>');
                                if (count($global_ratings['data']) > 0) {
                                    foreach ($global_ratings['data'] as $i => $ratings_data) {
                                        if (isset($link_type[$ratings_data['rating_type']]) && isset($comments_type[$ratings_data['rating_type']])) {
                                            $html .= '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                                $html .= '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($ratings_data, '25px', '', FALSE, 'img-circle m-r-5').'</div>';
                                                $html .= '<strong>'.profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status']).' </strong>';
                                                $html .= $locale['273a'].' <a href="'.sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id']).'"><strong>'.$comments_type[$ratings_data['rating_type']].'</strong></a> ';
                                                $html .= timer($ratings_data['rating_datestamp']);
                                                $html .= '<span class="text-warning m-l-10">'.str_repeat('<i class="fa fa-star fa-fw"></i>', $ratings_data['rating_vote']).'</span>';
                                            $html .= '</div>';
                                        }
                                    }

                                    if (isset($global_ratings['ratings_nav'])) {
                                        $html .= '<div class="clearfix">';
                                            $html .= '<span class="pull-right text-smaller">'.$global_ratings['ratings_nav'].'</span>';
                                        $html .= '</div>';
                                    }
                                } else {
                                    $html .= '<div class="text-center">'.$global_ratings['nodata'].'</div>';
                                }
                            $html .= fusion_get_function('closeside', '');
                        $html .= '</div>'; // #ratings
                    }

                    $html .= '<div id="submissions">';
                        $html .= fusion_get_function('openside', '<i class="fa fa-cloud-upload"></i> <strong class="text-uppercase">'.$locale['279'].'</strong><span class="pull-right badge bg-blue">'.number_format($global_submissions['rows']).'</span>');
                            if (count($global_submissions['data']) > 0) {
                                foreach ($global_submissions['data'] as $i => $submit_date) {
                                    if (isset($submit_data[$submit_date['submit_type']])) {
                                        $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                                        $html .= '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                            $html .= '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($submit_date, '25px', '', FALSE, 'img-circle m-r-5').'</div>';
                                            $html .= '<strong>'.profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status']).' </strong>';
                                            $html .= $locale['273b'].' <strong>'.$submit_data[$submit_date['submit_type']]['submit_locale'].'</strong> ';
                                            $html .= timer($submit_date['submit_datestamp']);
                                            if (!empty($review_link)) {
                                                $html .= '<a class="btn btn-xs btn-default m-l-10 pull-right" href="'.$review_link.'">'.$locale['286'].'</a>';
                                            }
                                        $html .= '</div>';
                                    }
                                }

                                if (isset($global_submissions['submissions_nav'])) {
                                    $html .= '<div class="clearfix">';
                                        $html .= '<span class="pull-right text-smaller">'.$global_submissions['submissions_nav'].'</span>';
                                    $html .= '</div>';
                                }
                            } else {
                                $html .= '<div class="text-center">'.$global_submissions['nodata'].'</div>';
                            }
                        $html .= fusion_get_function('closeside', '');
                    $html .= '</div>'; // #submissions
                $html .= '</div>';

                $html .= '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
                    $html .= '<div id="infusions">';
                        $html .= fusion_get_function('openside', '<i class="fa fa-cubes"></i> <strong class="text-uppercase">'.$locale['283'].'</strong><span class="pull-right badge bg-blue">'.number_format((int)$infusions_count).'</span>');
                            $content = '';
                            $bg_colors = ['gray', 'red', 'yellow', 'aqua', 'blue', 'light-blue', 'green', 'navy', 'teal', 'olive', 'orange', 'fuschia', 'purple', 'maroon', ''];

                            if ($infusions_count > 0) {
                                if (!empty($global_infusions)) {
                                    foreach ($global_infusions as $inf_id => $inf_data) {
                                        $color = array_rand($bg_colors);

                                        $html .= '<span class="badge bg-'.$bg_colors[$color].' m-b-10 m-r-5">'.$inf_data['inf_title'].'</span>';
                                    }
                                }
                                $content = checkrights('I') ? '<div class="text-right text-uppercase"><a class="text-smaller" href="'.ADMIN.'infusions.php'.$aidlink.'">'.$locale['285'].' <i class="fa fa-arrow-circle-right"></i></a></div>' : '';
                            } else {
                                $html .= '<div class="text-center">'.$locale['284'].'</div>';
                            }
                        $html .= fusion_get_function('closeside', $content);
                    $html .= '</div>'; // #infusins
                $html .= '</div>';
            $html .= '</div>'; // .row

        $html .= fusion_get_function('closetable', FALSE);

        return $html;
    }

    private function AdminIcons() {
        global $admin_icons;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $admin_title = str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['200']);
        $admin_title = !empty($locale['200a']) ? $locale['200a'] : $admin_title;

        $html = fusion_get_function('opentable', $admin_title);
        $html .= '<div class="row">';
        if (count($admin_icons['data']) > 0) {
            foreach ($admin_icons['data'] as $data) {
                $html .= '<div class="icon-wrapper col-xs-6 col-sm-2 col-md-2 col-lg-2">';
                    $html .= '<a class="btn btn-app" href="'.$data['admin_link'].$aidlink.'">';
                        $html .= '<img class="display-block" src="'.get_image('ac_'.$data['admin_rights']).'" alt="'.$data['admin_title'].'"/>';
                        $html .= '<span>'.$data['admin_title'].'</span>';
                    $html .= '</a>';
                $html .= '</div>';
            }
        }
        $html .= '</div>';
        $html .= fusion_get_function('closetable');

        return $html;
    }
}
