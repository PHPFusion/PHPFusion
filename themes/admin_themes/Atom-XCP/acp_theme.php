<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-XCP/acp_theme.php
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
defined('IN_FUSION') || exit;

use \PHPFusion\Admins;

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

function render_admin_panel() {
    $locale = fusion_get_locale();
    $userdata = fusion_get_userdata();
    $settings = fusion_get_settings();
    $aidlink = fusion_get_aidlink();

    $pagenum = (int)filter_input(INPUT_GET, 'pagenum');

    add_to_head('<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,300,700"/>');

    $html = '<div class="page-box">';
        $html .= '<section id="topcontent"><div class="container-fluid display-inline-block">';
            $html .= '<a href="'.$settings['siteurl'].'"><img class="img-responsive logo" src="'.IMAGES.'php-fusion-logo.png" alt="Logo"></a>';
        $html .= '</div></section>'; // #topcontent

        $html .= '<header id="header"><div class="container-fluid"><div class="col-xs-12 col-md-12 col-lg-12">';
            $html .= '<div id="atom-menu" class="navbar navbar-default" role="navigation"><div class="container-fluid">';
                $html .= '<div class="navbar-header">';
                    $html .= '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#atom-menu_menu" aria-expanded="false">';
                        $html .= '<span class="sr-only">'.$locale['global_017'].'</span><span class="icon-bar top-bar"></span><span class="icon-bar middle-bar"></span><span class="icon-bar bottom-bar"></span>';
                    $html .= '</button>';
                    $html .= '<a class="navbar-brand visible-xs hidden-sm hidden-md hidden-lg" href="'.$settings['siteurl'].'">'.$settings['sitename'].'</a>';
                $html .= '</div>'; // .navbar-header

                $html .= '<div class="navbar-collapse collapse" id="atom-menu_menu">';
                    $html .= '<ul class="nav navbar-nav primary">';
                        $admin_sections = Admins::getInstance()->getAdminSections();
                        $admin_pages = Admins::getInstance()->getAdminPages();

                        foreach ($admin_sections as $i => $section_name) {
                            $active = ((isset($_GET['pagenum']) && $pagenum === $i) || (!$pagenum && Admins::getInstance()->_isActive() === $i)) ? TRUE : FALSE;

                            $html .= '<li class="'.($i > 0 ? 'dropdown' : '').($active ? ' active' : '').'">';
                            if (!empty($admin_pages[$i])) {
                                $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.Admins::getInstance()->get_admin_section_icons($i).' '.$section_name.($i > 4 ? ' <span class="label label-primary">'.count($admin_pages[$i]).'</span>' : '').($i > 0 ? ' <span class="caret"></span>' : '').'</a>';
                                $html .= '<ul class="dropdown-menu">';
                                    $html .= '<li><a class="adl-link" href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'">'.$section_name.'</a></li>';

                                    foreach ($admin_pages[$i] as $key => $data) {
                                        $title = $data['admin_title'];

                                        if ($data['admin_page'] !== 5) {
                                            $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                                        }

                                        $secondary_active = $data['admin_link'] == Admins::getInstance()->_currentPage() ? TRUE : FALSE;
                                        $icons = Admins::getInstance()->get_admin_icons($data['admin_rights']);

                                        if (!empty($admin_pages[$data['admin_rights']])) {
                                            if (checkrights($data['admin_rights'])) {
                                                $html .= '<li><a href="'.ADMIN.$data['admin_link'].$aidlink.'">'.$icons.' '.$title.'</a></li>';
                                                foreach ($admin_pages[$data['admin_rights']] as $sub_key => $sub_page) {
                                                    $html .= '<li><a style="padding-left: 45px;" href="'.$sub_page['admin_link'].'">'.$sub_page['admin_title'].'</a></li>';
                                                }
                                            }
                                        } else {
                                            $html .= checkrights($data['admin_rights']) ? '<li'.($secondary_active ? ' class="active"' : '').'><a href="'.ADMIN.$data['admin_link'].$aidlink.'">'.$icons.' '.$title.'</a></li>' : '';
                                        }
                                    }

                                $html .= '</ul>';
                            } else {
                                $html .= '<a class="adl-link" href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum=0">'.Admins::getInstance()->get_admin_section_icons($i).' <span class="adl-section-name">'.$section_name.'</span></a>';
                            }

                            $html .= '</li>';
                        }

                        $html .= '<li class="dropdown nav-search-dropdown">';
                            $html .= '<div class="navbar-form m-t-10 m-b-0"><input class="form-control input-sm" type="text" id="search_pages" name="search_pages" placeholder="'.$locale['search'].'"/></div>';
                            $html .= '<ul class="dropdown-menu m-l-15" id="search_result"></ul>';
                        $html .= '</li>';
                    $html .= '</ul>';

                    add_to_jquery('
                        search_ajax("'.ADMIN.'includes/acp_search.php'.$aidlink.'");
                        function search_ajax(url) {
                            $("#search_pages").bind("keyup", function () {
                                $.ajax({
                                    url: url,
                                    get: "GET",
                                    data: $.param({"pagestring": $(this).val()}),
                                    dataType: "json",
                                    success: function (e) {
                                        if ($("#search_pages").val() === "") {
                                            $(".nav-search-dropdown").removeClass("open");
                                        } else {
                                            var result = "";

                                            if (!e.status) {
                                                $.each(e, function (i, data) {
                                                    if (data) {
                                                        result += "<li><a href=\"" + data.link + "\"><img class=\"admin-image\" alt=\"" + data.title + "\" src=\"" + data.icon + "\"/> " + data.title + "</a></li>";
                                                    }
                                                });
                                            } else {
                                                result = "<li class=\"p-10\"><span>" + e.status + "</span></li>";
                                            }

                                            $("#search_result").html(result);
                                            $(".nav-search-dropdown").addClass("open");
                                        }
                                    }
                                });
                            });
                        }
                    ');

                    $html .= '<ul class="nav navbar-nav secondary navbar-right m-r-0">';
                        $languages = fusion_get_enabled_languages();

                        if (count($languages) > 1) {
                            $html .= '<li class="dropdown language-switcher">';
                                $html .= '<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown" title="'.LANGUAGE.'">';
                                    $html .= '<i class="fa fa-globe"></i> ';
                                    $html .= '<img src="'.BASEDIR.'locale/'.LANGUAGE.'/'.LANGUAGE.'-s.png" alt="'.translate_lang_names(LANGUAGE).'"/>';
                                    $html .= '<span class="caret"></span>';
                                $html .= '</a>';
                                $html .= '<ul class="dropdown-menu">';
                                    foreach ($languages as $language_folder => $language_name) {
                                        $html .= '<li><a class="display-block" href="'.clean_request('lang='.$language_folder, ['lang'], FALSE).'">';
                                            $html .= '<img class="m-r-5" src="'.BASEDIR.'locale/'.$language_folder.'/'.$language_folder.'-s.png" alt="'.$language_folder.'"/> ';
                                            $html .= $language_name;
                                        $html .= '</a></li>';
                                    }
                                $html .= '</ul>';
                            $html .= '</li>';
                        }

                        $html .= '<li class="dropdown user">';
                            $html .= '<button class="dropdown-toggle btn btn-primary btn-sm m-t-10" data-toggle="dropdown">'.display_avatar($userdata, '16px', '', FALSE, 'img-rounded').' '.$userdata['user_name'].' <span class="caret"></span></button>';
                            $html .= '<ul class="dropdown-menu">';
                                $html .= '<li><a href="'.BASEDIR.'edit_profile.php"><i class="fa fa-pencil fa-fw"></i> '.$locale['UM080'].'</a></li>';
                                $html .= '<li><a href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'"><i class="fa fa-eye fa-fw"></i> '.$locale['view'].' '.$locale['profile'].'</a></li>';
                                $html .= '<li class="divider"></li>';
                                $html .= '<li><a href="'.FUSION_REQUEST.'&amp;logout"><i class="fa fa-sign-out fa-fw"></i> '.$locale['admin-logout'].'</a></li>';
                                $html .= '<li><a href="'.BASEDIR.'index.php?logout=yes"><i class="fa fa-sign-out fa-fw"></i> <span class="text-danger">'.$locale['logout'].'</span></a></li>';
                            $html .= '</ul>';
                        $html .= '</li>';
                    $html .= '</ul>';
                $html .= '</div>'; // #atom-menu_menu

            $html .= '</div></div>'; // #atom-menu
        $html .= '</div></div></header>'; // #header

        $html .= '<section id="banner"><div class="row">';
            $html .= '<div class="col-xs-12 col-sm-6">';
                $html .= '<h1>'.$locale['ac10'].'</h1>';
                $html .= '<h4>PHP-Fusion v'.$settings['version'].'</h4>';
            $html .= '</div>';

            $html .= '<div class="col-xs-12 col-sm-6">';
                $html .= render_breadcrumbs();
            $html .= '</div>';
        $html .= '</div></section>'; // #banner

        /*$html .= '<section id="quicklaunch">';
            $html .= '<ul class="links">';
                $quick_launch = [
                    ['link' => ADMIN.'members.php', 'icon' => 'far fa-user-circle', 'title' => $locale['M'], 'rights' => 'M'],
                    ['link' => ADMIN.'blacklist.php', 'icon' => 'fa fa-ban', 'title' => $locale['B'], 'rights' => 'B'],
                    ['link' => ADMIN.'errors.php', 'icon' => 'fa fa-bug', 'title' => $locale['ERRO'], 'rights' => 'ERRO'],
                    ['link' => ADMIN.'settings_main.php', 'icon' => 'fa fa-cog', 'title' => $locale['S1'], 'rights' => 'S1'],
                    ['link' => ADMIN.'infusions.php', 'icon' => 'fa fa-cubes', 'title' => $locale['I'], 'rights' => 'I']
                ];

                foreach ($quick_launch as $item) {
                    if (checkrights($item['rights'])) {
                        $html .= '<li><a href="'.$item['link'].$aidlink.'"><i class="'.$item['icon'].'"></i> '.$item['title'].'</a></li>';
                    }
                }
            $html .= '</ul>';
        $html .= '</section>'; // #quicklaunch*/

        $html .= '<section id="content">';
            $html .= renderNotices(getNotices());

            $html .= CONTENT;
        $html .= '</section>'; // #content

        $html .= '<footer id="footer"><div class="container-fluid">';
            $html .= showFooterErrors();

            $html .= '<div class="row">';
                $html .= '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                    $html .= '<i class="fa fa-rocket"></i> Atom-XCP Created by <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a>';
                    $html .= ' Design by <a href="https://www.php-fusion.co.uk" target="_blank">PHP-Fusion Inc</a>';

                    if ($settings['rendertime_enabled']) {
                        $html .= '<br/>'.showrendertime().'<br/>'.showMemoryUsage();
                    }
                $html .= '</div>';
                $html .= '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 text-right">';
                    $html .= showcopyright();
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div></footer>'; // #footer
    $html .= '</div>';

    echo $html;
}

function render_admin_login() {
    $locale = fusion_get_locale();
    $userdata = fusion_get_userdata();

    add_to_jquery('$("#admin_password").focus();');

    $html = '<div class="login-container">';
        $html .= '<div class="logo">';
            $html .= '<img src="'.IMAGES.'php-fusion-logo.png" class="pf-logo img-responsive" alt="PHP-Fusion"/>';
            $html .= '<h1><strong>'.$locale['280'].'</strong></h1>';
        $html .= '</div>';

        $html .= '<div class="login-box">';
            $html .= '<div class="clearfix m-b-20">';
                $html .= '<div class="pull-left m-r-10">';
                    $html .= display_avatar($userdata, '90px', '', FALSE, 'img-rounded');
                $html .= '</div>';
                $html .= '<div class="text-left">';
                    $html .= '<h3><strong>'.$locale['welcome'].', '.$userdata['user_name'].'</strong></h3>';
                    $html .= '<p>'.getuserlevel($userdata['user_level']).'</p>';
                $html .= '</div>';
            $html .= '</div>';

            $form_action = FUSION_SELF.fusion_get_aidlink() == ADMIN.'index.php'.fusion_get_aidlink() ? FUSION_SELF.fusion_get_aidlink().'&amp;pagenum=0' : FUSION_REQUEST;
            $html .= openform('admin-login-form', 'post', $form_action);
                $html .= form_text('admin_password', '', '', ['type' => 'password', 'callback_check' => 'check_admin_pass', 'placeholder' => $locale['281'], 'error_text' => $locale['global_182'], 'autocomplete_off' => TRUE, 'required' => TRUE]);
                $html .= form_button('admin_login', $locale['login'], $locale['login'], ['class' => 'btn-primary btn-block']);
            $html .= closeform();
        $html .= '</div>';
    $html .='</div>';

    echo $html;
}

function render_admin_dashboard() {
    $pagenum = (int)filter_input(INPUT_GET, 'pagenum');

    if ((isset($pagenum) && $pagenum) > 0) {
        global $admin_icons;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();

        $admin_title = str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['200']);
        $admin_title = !empty($locale['200a']) ? $locale['200a'] : $admin_title;

        $html = fusion_get_function('opentable', $admin_title);
        $html .= '<div class="row">';
            if (count($admin_icons['data']) > 0) {
                foreach ($admin_icons['data'] as $i => $data) {
                    $html .= '<div class="icon-wrapper col-xs-6 col-sm-2 col-md-2 col-lg-2">';
                        $html .= '<a class="text-center" href="'.$data['admin_link'].$aidlink.'">';
                            $html .= '<img class="display-block" src="'.get_image('ac_'.$data['admin_rights']).'" alt="'.$data['admin_title'].'"/>';
                            $html .= '<div>'.$data['admin_title'].'</div>';
                        $html .= '</a>';
                    $html .= '</div>';
                }
            }
        $html .= '</div>';
        $html .= fusion_get_function('closetable', '');
    } else {
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

            $html .= '<div class="members">';
                $html .= '<div class="row">';
                    foreach ($panels as $panel => $block) {
                        $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
                        $html .= '<div class="col-xs-'.$grid['mobile'].' col-sm-'.$grid['tablet'].' col-md-'.$grid['laptop'].' col-lg-'.$grid['desktop'].' block">';
                        $html .= fusion_get_function('openside', '', $panel);
                            $html .= '<img class="pull-left m-r-10 dashboard-icon" src="'.get_image('ac_M').'" alt="'.$locale['M'].'"/>';
                            $html .= '<h4 class="text-right m-t-0 m-b-0">'.number_format($members[$panel]).'</h4>';
                            $html .= '<strong class="text-smaller pull-right" style="position: relative;z-index: 3;">'.$block['title'].'</strong>';

                            $content  = '<div class="text-right text-uppercase">';
                            $content .= '<a href="'.ADMIN.'members.php'.$aidlink.$block['link'].'">'.$locale['255'].' <i class="fa fa-angle-right"></i></a>';
                            $content .= '</div>';
                        $html .= fusion_get_function('closeside', checkrights('M') ? $content : '');
                        $html .= '</div>';
                    }
                $html .= '</div>';
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
                    foreach ($modules as $name => $module) {
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
                $html .= '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
                    if ($settings['comments_enabled'] == 1) {
                        $html .= '<div id="comments">';
                            $html .= fusion_get_function('openside', '<strong class="text-uppercase">'.$locale['277'].'</strong><span class="pull-right badge">'.number_format($global_comments['rows']).'</span>');
                                if (count($global_comments['data']) > 0) {
                                    foreach ($global_comments['data'] as $i => $comment_data) {
                                        if (isset($comments_type[$comment_data['comment_type']]) && isset($link_type[$comment_data['comment_type']])) {
                                            $html .= '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                                $html .= '<div id="comment_action-'.$i.'" class="btn-group btn-group-xs pull-right m-t-10">';
                                                    $html .= '<a class="btn btn-primary" title="'.$locale['274'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-eye"></i></a>';
                                                    $html .= '<a class="btn btn-warning" title="'.$locale['275'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;action=edit&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-pencil"></i></a>';
                                                    $html .= '<a class="btn btn-danger" title="'.$locale['276'].'" href="'.ADMIN.'comments.php'.$aidlink.'&amp;action=delete&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'].'"><i class="fa fa-trash"></i></a>';
                                                $html .= '</div>';
                                                $html .= '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($comment_data, '25px', '', FALSE, 'img-rounded m-r-5').'</div>';
                                                $html .= '<strong>'.(!empty($comment_data['user_id']) ? profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']) : $comment_data['comment_name']).' </strong>';
                                                $html .= $locale['273'].' <a href="'.sprintf($link_type[$comment_data['comment_type']], $comment_data['comment_item_id']).'"><strong>'.$comments_type[$comment_data['comment_type']].'</strong></a> ';
                                                $html .= timer($comment_data['comment_datestamp']).'<br/>';
                                                $comment = trimlink(strip_tags(parse_textarea($comment_data['comment_message'], FALSE, TRUE)), 130);
                                                $html .= '<span class="text-smaller">'.parse_textarea($comment, TRUE, FALSE).'</span>';
                                            $html .= '</div>';
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
                    }

                    if ($settings['ratings_enabled'] == 1) {
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
                    }

                    $html .= '<div id="submissions">';
                        $html .= fusion_get_function('openside', '<strong class="text-uppercase">'.$locale['279'].'</strong><span class="pull-right badge">'.number_format($global_submissions['rows']).'</span>');
                            if (count($global_submissions['data']) > 0) {
                                foreach ($global_submissions['data'] as $i => $submit_date) {
                                    if (isset($submit_data[$submit_date['submit_type']])) {
                                        $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                                        $html .= '<div data-id="'.$i.'" class="clearfix p-b-10'.($i > 0 ? ' p-t-10' : '').'"'.($i > 0 ? ' style="border-top: 1px solid #ddd;"' : '').'>';
                                            $html .= '<div class="pull-left display-inline-block m-t-5 m-b-0">'.display_avatar($submit_date, '25px', '', FALSE, 'img-rounded m-r-5').'</div>';
                                            $html .= '<strong>'.profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status']).' </strong>';
                                            $html .= $locale['273b'].' <strong>'.$submit_data[$submit_date['submit_type']]['submit_locale'].'</strong> ';
                                            $html .= timer($submit_date['submit_datestamp']);
                                            if (!empty($review_link)) {
                                                $html .= '<a class="btn btn-sm btn-default m-l-10 pull-right" href="'.$review_link.'">'.$locale['286'].'</a>';
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

                $html .= '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
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
    }

    echo $html;
}

function openside($title = FALSE, $class = NULL) {
    $html = '<div class="panel panel-default openside '.$class.'">';
    $html .= $title ? '<div class="panel-heading">'.$title.'</div>' : '';
    $html .= '<div class="panel-body">';

    echo $html;
}

function closeside($footer = FALSE) {
    $html = '</div>';
    $html .= $footer ? '<div class="panel-footer">'.$footer.'</div>' : '';
    $html .= '</div>';

    echo $html;
}

function opentable($title, $class = NULL) {
    $html = '<div class="panel panel-default '.$class.'">';
    $html .= $title ? '<div class="panel-heading"><h3 class="m-0">'.$title.'</h3></div>' : '';
    $html .= '<div class="panel-body">';

    echo $html;
}

function closetable() {
    $html = '</div>';
    $html .= '</div>';

    echo $html;
}
