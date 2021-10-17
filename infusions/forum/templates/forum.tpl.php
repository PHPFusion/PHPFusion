<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: forum.tpl.php
| Author: Core Development Team
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

/**
 * Forum Page Control Layout
 */
if (!function_exists('render_forum')) {
    function render_forum($info) {
        $locale = fusion_get_locale();

        fusion_load_script(INFUSIONS.'forum/templates/css/forum.css', 'css');

        echo '<div class="forum-main-index">';
        opentable('');
        echo render_breadcrumbs();

        echo '<div class="row">';

        echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';

        if (isset($_GET['viewforum'])) {
            forum_viewforum($info);
        } else {
            if (isset($_GET['section'])) {
                render_section($info);
            } else {
                render_forum_main($info);
            }
        }
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
        if (iMEMBER) {
            echo '<a id="create_new_thread" href="'.FORUM.'newthread.php'.(check_get('forum_id') ? '?forum_id='.get('forum_id') : '').'" class="btn btn-primary btn-block m-b-20"><i class="fa fa-comment m-r-10"></i> '.$locale['forum_0057'].'</a>';
            forum_newtopic();
        }

        $thread_tags = \PHPFusion\Forums\ForumServer::tag(TRUE, FALSE)->getTagInfo();

        if (!empty($thread_tags['tags'])) {
            echo '<h4>'.$locale['forum_0272'].'</h4>';
            echo '<div class="list-group m-t-10 m-b-20">';
            foreach ($thread_tags['tags'] as $tag_id => $tag_data) {
                $active = isset($_GET['tag_id']) && $_GET['tag_id'] == $tag_id ? ' active' : '';
                echo '<a href="'.$tag_data['tag_link'].'" class="list-group-item clearfix p-5 p-l-15'.$active.'">';
                echo '<div class="pull-left m-r-10">';
                echo '<span class="fa-stack" style="font-size: 0.5em;"><i class="fa-stack-2x fa fa-square" style="color:'.$tag_data['tag_color'].';"></i>';
                if (!empty($tag_data['tag_icon'])) {
                    echo '<i class="text-white fa-stack-1x '.$tag_data['tag_icon'].'"></i>';
                }
                echo '</span>';
                echo '</div>';
                echo $tag_data['tag_title'];
                echo '</a>';
            }
            echo '</div>';
        }

        $result = dbquery("SELECT t.thread_id, t.thread_subject, t.thread_author, t.thread_postcount
            FROM ".DB_FORUMS." tf
            INNER JOIN ".DB_FORUM_THREADS." t ON tf.forum_id=t.forum_id
            ".(multilang_column('FO') ? " WHERE forum_language='".LANGUAGE."' AND " : " WHERE ").groupaccess('forum_access')." AND (t.thread_lastpost >=:one_week AND t.thread_lastpost < :current) AND t.thread_locked=:not_locked AND t.thread_hidden=:not_hidden
            GROUP BY t.thread_id ORDER BY t.thread_postcount DESC LIMIT 10
        ", [
            ':one_week'   => time() - (7 * 24 * 3600),
            ':current'    => time(),
            ':not_locked' => 0,
            ':not_hidden' => 0
        ]);

        echo '<h4>'.(!empty($locale['forum_0273']) ? $locale['forum_0273'] : $locale['forum_0002']).'</h4>';
        echo '<div class="list-group m-t-10 m-b-20">';
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $user = fusion_get_user($data['thread_author']);

                echo '<div class="list-group-item clearfix">';
                echo '<a href="'.FORUM.'viewthread.php?thread_id='.$data['thread_id'].'">'.$data['thread_subject'].'</a>';
                echo '<span class="m-l-5">'.$locale['by'].' '.profile_link($user['user_id'], $user['user_name'], $user['user_status']).'</span>';
                echo '<span class="pull-right text-lighter"><i class="fa fa-comment"></i> '.format_word($data['thread_postcount'], $locale['fmt_post']).'</span>';
                echo '</div>';
            }
        } else {
            echo '<div class="list-group-item clearfix text-center">'.(!empty($locale['forum_0275']) ? $locale['forum_0275'] : $locale['forum_0056']).'</div>';
        }
        echo '</div>';

        echo '</div>';
        echo '</div>';

        closetable();
        echo '</div>'; // .forum-main-index
    }
}

/**
 * Main Forum Page - Recursive
 *
 * @param array $info
 * @param int   $id - counter nth
 */
if (!function_exists('render_forum_main')) {
    function render_forum_main($info = [], $id = 0) {
        $locale = fusion_get_locale();

        if (!empty($info['forums'][$id])) {
            $forums = $info['forums'][$id];

            foreach ($forums as $data) {
                if ($data['forum_type'] == 1) {
                    echo '<div class="panel panel-default">';
                    echo '<div class="panel-heading">';
                    echo '<h4 class="panel-title"><a class="text-bold" href="'.$data['forum_link']['link'].'">'.$data['forum_link']['title'].'</a></h4>';

                    if ($data['forum_description']) {
                        echo '<span class="text-smaller">'.$data['forum_description'].'</span>';
                    }
                    echo '</div>';

                    if (isset($data['child'])) {
                        echo '<div class="list-group">';
                        $sub_forums = $data['child'];

                        foreach ($sub_forums as $cdata) {
                            echo '<div class="list-group-item clearfix">';
                            render_forum_item($cdata);
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<div class="panel-body text-center">';
                        echo $locale['forum_0327'];
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="list-group">';
                    if ($data['forum_type'] != 1) {
                        echo '<div class="list-group-item clearfix">';
                        render_forum_item($data);
                        echo '</div>';
                    }
                    echo '</div>';
                }
            }
        } else {
            echo '<div class="text-center">'.$locale['forum_0328'].'</div>';
        }
    }
}

/**
 * Switch between different types of forum list containers
 */
if (!function_exists('render_forum_item')) {
    function render_forum_item($data) {
        $locale = fusion_get_locale();
        $forum_settings = \PHPFusion\Forums\ForumServer::getForumSettings();

        echo '<div id="forum_'.$data['forum_id'].'">';
        echo '<div class="pull-left forum_icon">';
        if ($forum_settings['picture_style'] == 'image' && ($data['forum_image'] && file_exists(INFUSIONS."forum/images/".$data['forum_image']))) {
            echo '<img class="img-responsive" style="width:30px;" src="'.FORUM.'images/'.$data['forum_image'].'">';
        } else if ($forum_settings['picture_style'] == 'icon' && !empty($data['forum_icon'])) {
            echo '<div class="forum-icon"><i class="'.$data['forum_icon'].'"></i></div>';
        } else {
            echo '<div class="forum-icon"><i class="'.$data['forum_icon_alt'].'"></i></div>';
        }
        echo '</div>';

        echo '<div class="overflow-hide">';
        echo '<div class="row m-0">';
        echo '<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">';
        echo '<a class="display-inline-block forum-link text-bold" href="'.$data['forum_link']['link'].'">'.$data['forum_link']['title'].'</a>';

        if ($data['forum_new_status']) {
            echo '<span class="m-l-5">'.$data['forum_new_status'].'</span>';
        }

        if ($data['forum_description']) {
            echo '<div class="forum-description">'.$data['forum_description'].'</div>';
        }

        if ($data['forum_moderators']) {
            echo '<div class="forum-moderators text-smaller">';
            echo '<strong>'.$locale['forum_0007'].'</strong> '.$data['forum_moderators'];
            echo '</div>';
        }

        if (isset($data['child'])) {
            echo '<div class="clearfix sub-forum">';
            echo '<div class="overflow-hide">';
            foreach ($data['child'] as $cdata) {
                echo isset($cdata['forum_type']) ? '<i class="'.$cdata['forum_icon'].'"></i> ' : '';
                echo '<a href="'.INFUSIONS.'forum/index.php?viewforum&forum_id='.$cdata['forum_id'].'" class="forum-subforum display-inline-block">'.$cdata['forum_name'].'</a><br/>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 hidden-xs">';
        echo '<div class="display-inline-block m-l-10">';
        echo format_word($data['forum_postcount'], $locale['fmt_post']);
        echo '</div>';
        echo '<div class="display-inline-block m-l-10">';
        echo format_word($data['forum_threadcount'], $locale['fmt_thread']);
        echo '</div>';
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">';
        if (!empty($data['forum_lastpost'])) {
            if (!empty($data['last_post']['avatar'])) {
                echo '<div class="pull-left m-r-5">';
                echo display_avatar($data, '30px', '', '', 'img-circle');
                echo '</div>';
            }

            if (!empty($data['thread_subject'])) {
                echo '<a href="'.$data['last_post']['post_link'].'">'.$data['thread_subject'].'</a>';
            }
            echo '<br/><span class="forum_profile_link">';
            echo $data['last_post']['profile_link'].' '.$data['last_post']['time'];
            echo '</span>';
        } else {
            echo '<strong>'.$locale['forum_0005'].'</strong>';
        }
        echo '</div>';
        echo '</div>'; // .row
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Viewforum (Index)
 */
if (!function_exists('forum_viewforum')) {
    function forum_viewforum($info) {
        $locale = fusion_get_locale();
        if (!empty($info['forum_name'])) {
            echo '<div class="m-b-15">';
            echo '<h4 class="forum-title">'.$info['forum_name'].'</h4>';
            if (!empty($info['forum_description'])) {
                echo '<div class="forum-description">'.$info['forum_description'].'</div>';
            }
            echo '</div>';
        }

        if ($info['forum_type'] > 1 && !empty($info['forum_page_link'])) {
            echo '<ul class="nav nav-pills">';
            $i = 0;
            unset($info['forum_page_link']['subforums']); // hide subforums section
            foreach ($info['forum_page_link'] as $view_keys => $page_link) {
                $active = (!isset($_GET['view']) && !$i) || (isset($_GET['view']) && $_GET['view'] === $view_keys) ? ' active' : '';

                echo '<li class="nav-item'.$active.'"><a class="nav-link p-t-10 p-b-10" href="'.$page_link['link'].'">'.$page_link['title'].'</a></li>';
                $i++;
            }
            echo '</ul>';
        }

        if (!empty($info['forum_rules'])) {
            echo '<div class="well m-t-20 text-white" style="background-color: #F44336;">';
            echo '<div class="strong"><i class="fa fa-exclamation"></i> '.$locale['forum_0350'].'</div>';
            echo $info['forum_rules'];
            echo '</div>';
        }

        if (isset($_GET['view'])) {
            switch ($_GET['view']) {
                default:
                case 'threads':
                    if ($info['forum_type'] > 1) {
                        echo '<div class="forum-title m-t-20">'.$locale['forum_0002'].'</div>';

                        forum_filter($info);

                        render_forum_threads($info);
                    }
                    break;
                case 'subforums':
                    if (!empty($info['item'][$_GET['forum_id']]['child'])) {
                        echo '<div class="forum-title m-t-20">'.$locale['forum_0351'].'</div>';

                        forum_filter($info);

                        echo '<div class="panel panel-default">';
                        echo '<div class="list-group">';
                        foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_data) {
                            echo '<div class="list-group-item clearfix">';
                            render_forum_item($subforum_data);
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<div class=" text-center">'.$locale['forum_0019'].'</div>';
                    }
                    break;
                case 'people':
                    if (!empty($info['item'])) {
                        echo '<div class=" table-responsive"><table class="table table-striped">';
                        echo '<thead><tr>';
                        echo '<th>'.$locale['forum_0018'].'</th>';
                        echo '<th>'.$locale['forum_0012'].'</th>';
                        echo '<th>'.$locale['forum_0016'].'</th>';
                        echo '</tr></thead>';
                        echo '<tbody>';
                        foreach ($info['item'] as $user) {
                            echo '<tr>';
                            echo '<td>'.display_avatar($user, '30px', '', FALSE, 'img-rounded m-r-10').profile_link($user['user_id'], $user['user_name'], $user['user_status']).'</td>';
                            echo '<td><a href="'.$user['thread_link']['link'].'">'.$user['thread_link']['title'].'</a></td>';
                            echo '<td>'.showdate('forumdate', $user['post_datestamp']).', '.timer($user['post_datestamp']).'</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table></div>';

                        echo $info['pagenav'];
                    }
                    break;
                case 'activity':
                    if (!empty($info['item'])) {
                        if (!empty($info['max_post_count'])) {
                            echo '<div class="list-group-item clearfix m-b-10"><strong>';
                            echo format_word($info['max_post_count'], $locale['fmt_post']);
                            echo ' | <a href="'.$info['last_activity']['link'].'">'.$locale['forum_0020'].'</a> ';
                            echo sprintf($locale['forum_0021'],
                                showdate('forumdate', $info['last_activity']['time']),
                                profile_link($info['last_activity']['user']['user_id'], $info['last_activity']['user']['user_name'], $info['last_activity']['user']['user_status'])
                            );
                            echo '</strong></div>';
                        }

                        $i = 0;
                        foreach ($info['item'] as $postData) {
                            echo '<div class="forum-activity well m-b-20">';
                            echo '<div class="pull-left">';
                            echo display_avatar($postData['post_author'], '50px', FALSE, '', 'm-r-10');
                            echo '</div>';

                            echo '<div class="overflow-hide">';

                            echo '<div class="m-b-10">';
                            echo profile_link($postData['post_author']['user_id'], $postData['post_author']['user_name'], $postData['post_author']['user_status']).' ';
                            echo showdate('forumdate', $postData['post_datestamp']).', ';
                            echo timer($postData['post_datestamp']);
                            echo '</div>';

                            echo '<div class="list-group">';
                            echo '<div class="list-group-item clearfix">';
                            echo '<div class="text-smaller text-lighter m-b-10"><b>'.$locale['forum_0023'].' '.$postData['thread_link']['title'].'</b></div>';
                            echo $postData['post_message'];
                            echo '</div>';

                            echo '<div class="list-group-item clearfix">';
                            echo '<div class="text-smaller strong">'.$locale['forum_0022'].' <a href="'.$postData['thread_link']['link'].'">'.$postData['thread_link']['title'].'</a> <i class="fa fa-external-link-alt"></i></div>';
                            echo '</div>';
                            echo '</div>';

                            echo '</div>';

                            echo '</div>';
                            $i++;
                        }

                        echo $info['pagenav'];
                    } else {
                        echo '<div class="text-center">'.$locale['forum_4121'].'</div>';
                    }
                    break;
            }
        } else {
            if (!empty($info['subforums'])) {

                if ($info['forum_type'] != 1) {
                    echo '<div class="forum-title m-t-20">'.$locale['forum_0351'].'</div>';
                }

                echo '<div class="panel panel-default">';
                    echo '<div class="list-group">';
                        foreach ($info['subforums'] as $subforum_data) {
                            echo '<div class="list-group-item clearfix">';
                            render_forum_item($subforum_data);
                            echo '</div>';
                        }
                    echo '</div>';
                echo '</div>';
            } else {
                if ($info['forum_type'] == 1) {
                    echo '<div class="text-center">'.$locale['forum_0327'].'</div>';
                }
            }

            if ($info['forum_type'] > 1 && !empty($info['filters']['type'])) {
                echo '<div class="m-b-20">';
                foreach ($info['filters']['type'] as $tab) {
                    $active = $tab['active'] == 1 ? ' strong' : '';
                    echo '<a class="m-r-10'.$active.'" href="'.$tab['link'].'">'.$tab['icon'].''.$tab['title'].' ('.$tab['count'].')</a>';
                }
                echo '</div>';
            }

            if ($info['forum_type'] > 1) {
                echo '<div class="list-group">';
                    render_forum_threads($info);
                echo '</div>';
            }
        }

        openside('');
        $prm = $info['permissions'];
        $can = '<strong class="text-success">'.$locale['can'].'</strong>';
        $cannot = '<strong class="text-danger">'.$locale['cannot'].'</strong>';

        echo '<span>'.sprintf($locale['forum_perm_access'], $prm['can_access'] == TRUE ? $can : $cannot).'</span><br/>';
        echo '<span>'.sprintf($locale['forum_perm_post'], $prm['can_post'] == TRUE ? $can : $cannot).'</span><br/>';
        echo '<span>'.sprintf($locale['forum_perm_create_poll'], $prm['can_create_poll'] == TRUE ? $can : $cannot).'</span><br/>';
        echo '<span>'.sprintf($locale['forum_perm_upload'], $prm['can_upload_attach'] == TRUE ? $can : $cannot).'</span><br/>';
        echo '<span>'.sprintf($locale['forum_perm_download'], $prm['can_download_attach'] == TRUE ? $can : $cannot).'</span>';

        if ($info['forum_moderators']) {
            echo '<div class="m-b-20"><span class="text-dark">'.$locale['forum_0185'].' '.$info['forum_moderators'].'</span></div>';
        }

        closeside();
    }
}

/**
 * Threads Item Display
 */
if (!function_exists('render_forum_threads')) {
    function render_forum_threads($info) {
        $locale = fusion_get_locale();
        $data = $info['threads'];

        if (!empty($data)) {
            echo '<div class="list-group">';
            if (!empty($data['sticky'])) {
                foreach ($data['sticky'] as $cdata) {
                    echo '<div class="list-group-item clearfix sticky">';
                    render_thread_item($cdata);
                    echo '</div>';
                }
            }

            if (!empty($data['item'])) {
                foreach ($data['item'] as $cdata) {
                    echo '<div class="list-group-item clearfix">';
                    render_thread_item($cdata);
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="text-center">'.$locale['forum_0269'].'</div>';
        }

        echo !empty($data['pagenav']) ? '<div class="text-right hidden-xs m-t-15">'.$data['pagenav'].'</div>' : '';
    }
}

if (!function_exists('render_thread_item')) {
    function render_thread_item($info) {
        $locale = fusion_get_locale();

        $thead_icons = implode('', $info['thread_icons']);

        echo '<div id="thread_'.$info['thread_id'].'" class="row">';
        echo '<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">';
        if ($info['forum_type'] == '4' && !empty($info['thread_bounty']) && $info['thread_bounty'] !== 0) {
            echo '<i title="'.$locale['forum_4124'].'" class="fas fa-award m-r-5"></i>';
        }
        echo '<a class="display-inline-block forum-link strong text-dark" href="'.$info['thread_link']['link'].'">';
        echo $info['thread_link']['title'];
        echo '</a>';
        echo($thead_icons ? '<span class="text-lighter m-l-10 m-r-10">'.$thead_icons.'</span>' : '');
        echo '<div class="text-lighter">'.(!empty($info['thread_starter_text']) ? $info['thread_starter_text'] : $info['thread_starter']).'</div>';

        if (!empty($info['thread_last'])) {
            echo '<div class="text-lighter">';
                echo $locale['forum_0373'].' ';
                echo profile_link($info['thread_last']['user']['user_id'], $info['thread_last']['user']['user_name'], $info['thread_last']['user']['user_status']);
                echo ' - '.timer($info['thread_lastpost']);
            echo '</div>';
        }

        echo $info['thread_pages'];
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
        echo '<div class="display-inline-block m-l-10">';
        echo format_word($info['thread_postcount'], $locale['fmt_post']);
        echo '</div>';

        if ($info['forum_type'] == '4') {
            echo '<div class="display-inline-block m-l-10">';
            echo format_word($info['vote_count'], $locale['fmt_vote']);
            echo '</div>';
        }

        echo '<div class="display-inline-block m-l-10">';
        echo format_word($info['thread_views'], $locale['fmt_views']);
        echo '</div>';

        echo '</div>';

        echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
        if (isset($info['track_button'])) {
            echo '<div class="forum_track">';
            echo '<a '.$info['track_button']['onclick'].' href="'.$info['track_button']['link'].'">'.$info['track_button']['title'].'</a>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }
}

if (!function_exists('forum_filter')) {
    function forum_filter($info) {
        $locale = fusion_get_locale();
        // This one need to push to core.
        $selector = [
            'today'  => $locale['forum_0212'],
            '2days'  => $locale['forum_p002'],
            '1week'  => $locale['forum_p007'],
            '2week'  => $locale['forum_p014'],
            '1month' => $locale['forum_p030'],
            '2month' => $locale['forum_p060'],
            '3month' => $locale['forum_p090'],
            '6month' => $locale['forum_p180'],
            '1year'  => $locale['forum_3015']
        ];

        // This one take out from default filtrations
        // Type $_GET['type']
        /*$selector2 = [
            'all'         => $locale['forum_0374'],
            'discussions' => $locale['forum_0222'],
            'attachments' => $locale['forum_0223'],
            'poll'        => $locale['forum_0314'],
            'solved'      => $locale['forum_0378'],
            'unsolved'    => $locale['forum_0379'],
        ];*/

        $selector3 = [
            'author'  => $locale['forum_0052'],
            'time'    => $locale['forum_0381'],
            'subject' => $locale['forum_0051'],
            'reply'   => $locale['forum_0054'],
            'view'    => $locale['forum_0053'],
        ];

        // how to stack it.
        $selector4 = [
            'descending' => $locale['forum_0230'],
            'ascending'  => $locale['forum_0231']
        ];

        ob_start();
        if (isset($_GET['tag_id']) && isnum($_GET['tag_id']) || isset($_GET['forum_id']) && isnum($_GET['forum_id'])) {
            ?>
            <div class='clearfix'>
                <div class='pull-left'>
                    <?php echo $locale['forum_0388']; ?>
                    <div class='forum-filter dropdown'>
                        <button id='ddfilter1' class='btn btn-xs <?php echo(isset($_GET['time']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                            <?php echo(isset($_GET['time']) && in_array($_GET['time'], array_flip($selector)) ? $selector[$_GET['time']] : $locale['forum_0211']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu' aria-labelledby='ddfilter1'>
                            <?php
                            foreach ($info['filter']['time'] as $filter_locale => $filter_link) {
                                echo "<li class='dropdown-item''><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class='pull-left'>
                    <?php echo $locale['forum_0225'] ?>
                    <div class='forum-filter dropdown'>
                        <button id='ddfilter2' class='btn btn-xs <?php echo(isset($_GET['sort']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                            <?php echo(isset($_GET['sort']) && in_array($_GET['sort'], array_flip($selector3)) ? $selector3[$_GET['sort']] : $locale['forum_0381']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-right' aria-labelledby='ddfilter2'>
                            <?php
                            foreach ($info['filter']['sort'] as $filter_locale => $filter_link) {
                                echo "<li class='dropdown-item'><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                    <div class='forum-filter dropdown'>
                        <button id='ddfilter3' class='btn btn-xs <?php echo(isset($_GET['order']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                            <?php echo(isset($_GET['order']) && in_array($_GET['order'], array_flip($selector4)) ? $selector4[$_GET['order']] : $locale['forum_0230']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-right' aria-labelledby='ddfilter3'>
                            <?php
                            foreach ($info['filter']['order'] as $filter_locale => $filter_link) {
                                echo "<li class='dropdown-item''><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

            <?php
            /*echo "<div class='forum-filter'>\n";
            echo "<button id='ddfilter4' class='btn btn-xs btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>".(isset($_GET['type']) && in_array($_GET['type'],
                    array_flip($selector2)) ? $selector2[$_GET['type']] : $locale['forum_0390'])." <span class='caret'></span></button>\n";
            echo "<ul class='dropdown-menu' aria-labelledby='ddfilter4'>\n";
            foreach ($info['filter']['type'] as $filter_locale => $filter_link) {
                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
            }
            echo "</ul>\n";
            echo "</div>\n";
            */
        }

        return ob_get_clean();
    }
}

/**
 * Forum Sections Item Display (Latest, Participated, Tracked, Unanswered, Unsolved)
 */
if (!function_exists("render_section")) {
    function render_section($info) {
        $locale = fusion_get_locale();
        $data = $info['threads'];

        if (!empty($info['threads_time_filter'])) {
            echo '<div class="clearfix"><div class="pull-left">'.$info['threads_time_filter'].'</div></div>';
        }

        echo !empty($data['pagenav']) ? '<div class="text-right m-b-20">'.$data['pagenav'].'</div>' : '';

        if (!empty($data)) {
            echo '<div class="list-group">';
            if (!empty($data['sticky'])) {
                foreach ($data['sticky'] as $cdata) {
                    echo '<div class="list-group-item clearfix">';
                    render_thread_item($cdata);
                    echo '</div>';
                }
            }

            if (!empty($data['item'])) {
                foreach ($data['item'] as $cdata) {
                    echo '<div class="list-group-item clearfix">';
                    render_thread_item($cdata);
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="text-center">'.$locale['forum_0269'].'</div>';
        }

        echo !empty($data['pagenav']) ? '<div class="text-right hidden-xs m-t-15">'.$data['pagenav'].'</div>' : '';
    }
}

/**
 * Custom Modal New Topic
 */
if (!function_exists('forum_newtopic')) {
    function forum_newtopic() {
        $locale = fusion_get_locale();

        if (isset($_POST['select_forum'])) {
            $_POST['forum_sel'] = isset($_POST['forum_sel']) && isnum($_POST['forum_sel']) ? $_POST['forum_sel'] : 0;
            redirect(fusion_get_settings('siteurl').'infusions/forum/newthread.php?forum_id='.$_POST['forum_sel']);
        }

        echo openmodal('newtopic', $locale['forum_0057'], ['class' => 'modal-md', 'button_id' => 'create_new_thread']);
        echo openform('newtopic', 'post');

        $disabled_opts = [];
        $disable_query = dbquery("SELECT forum_id FROM ".DB_FORUMS." WHERE forum_type=1 ".(multilang_table("FO") ? "AND ".in_group('forum_language', LANGUAGE) : ''));
        if (dbrows($disable_query) > 0) {
            while ($d_forum = dbarray($disable_query)) {
                $disabled_opts[] = $d_forum['forum_id'];
            }
        }

        echo '<div class="clearfix">';

        echo form_select_tree('forum_sel', $locale['forum_0395'], get('forum_id'), [
            'width'        => '100%',
            'inline'       => TRUE,
            'no_root'      => TRUE,
            'disable_opts' => $disabled_opts,
            'query'        => (multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE) : ''),
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');

        echo '<div class="display-inline-block col-xs-12 col-sm-offset-3">';
        echo form_button('select_forum', $locale['forum_0396'], 'select_forum', ['class' => 'btn-primary btn-sm']);
        echo '</div>';

        echo '</div>';

        echo closeform();
        echo closemodal();
    }
}

/**
 * Forum Confirmation Message Box
 */
if (!function_exists('render_postify')) {
    function render_postify($info) {
        opentable($info['title'], ($info['error'] ? 'alert alert-danger' : ''));
        echo '<div class="text-center">';
        echo '<div class="forum-postify-loading-dots">'.$info['title'].'</div>';
        echo !empty($info['message']) ? $info['message'].'<br/>' : '';
        foreach ($info['link'] as $link) {
            echo '<p><a href="'.$link['url'].'">'.$link['title'].'</a></p>';
        }
        echo '</div>';
        closetable();
    }
}

/**
 * Display the post reply form
 */
if (!function_exists("display_forum_postform")) {
    function display_forum_postform($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();

        echo '<h3 class="m-t-0">'.$info['title'].'</h3>';
        opentable('');
        echo $info['description'] ? '<h4>'.$info['description'].'</h4>' : '';

        echo $info['openform'];
        echo $info['forum_field'];
        echo $info['subject_field'];
        echo !empty($info['tags_field']) ? $info['tags_field'] : '';
        echo $info['message_field'];
        echo $info['edit_reason_field'];
        echo $info['forum_id_field'];
        echo $info['thread_id_field'];
        echo $info['poll_form'];

        $tab_title['title'][0] = $locale['forum_0602'];
        $tab_title['id'][0] = 'postopts';
        $tab_title['icon'][0] = '';
        $tab_active = tab_active($tab_title, 0);
        $tab_content = opentabbody($tab_title['title'][0], 'postopts', $tab_active);
        $tab_content .= '<div class="well m-t-20">';
        $tab_content .= $info['delete_field'];
        $tab_content .= $info['sticky_field'];
        $tab_content .= $info['notify_field'];
        $tab_content .= $info['lock_field'];
        $tab_content .= $info['hide_edit_field'];
        $tab_content .= $info['smileys_field'];
        $tab_content .= $info['signature_field'];
        $tab_content .= '</div>';
        $tab_content .= closetabbody();

        if (!empty($info['attachment_field'])) {
            $tab_title['title'][1] = $locale['forum_0557'];
            $tab_title['id'][1] = 'attach_tab';
            $tab_title['icon'][1] = '';
            $tab_content .= opentabbody($tab_title['title'][1], 'attach_tab', $tab_active);
            $tab_content .= '<div class="well m-t-20">'.$info['attachment_field'].'</div>';
            $tab_content .= closetabbody();
        }

        echo opentab($tab_title, $tab_active, 'newthreadopts');
        echo $tab_content;
        echo closetab();
        echo $info['post_buttons'];
        echo $info['closeform'];
        closetable();

        echo !empty($info['last_posts_reply']) ? $info['last_posts_reply'] : '';
    }
}

/**
 * Display the poll creation form
 */
if (!function_exists("display_forum_pollform")) {
    function display_forum_pollform($info) {
        echo render_breadcrumbs();

        opentable($info['title']);
        echo $info['field']['openform'];
        echo '<h4 class="spacer-sm">'.$info['description'].'</h4>';
        echo $info['field']['poll_field'].$info['field']['poll_button'];
        echo $info['field']['closeform'];
        closetable();
    }
}

/**
 * Display the bounty creation form
 */
if (!function_exists('display_form_bountyform')) {
    function display_forum_bountyform($info) {
        echo render_breadcrumbs();

        opentable($info['title']);
        echo $info['field']['openform'];
        echo '<h4 class="spacer-sm">'.$info['description'].'</h4>';
        echo $info['field']['bounty_select'].$info['field']['bounty_description'].$info['field']['bounty_button'];
        echo $info['field']['closeform'];
        closetable();
    }
}

/**
 * Display the Quick Reply Form
 */
if (!function_exists("display_quick_reply")) {
    function display_quick_reply($info) {
        $locale = fusion_get_locale();

        $html = '<h4 class="spacer-sm">'.$info['description'].'</h4>';

        $html .= $info['field']['message'];

        $tab_title['title'][0] = $locale['forum_0602'];
        $tab_title['id'][0] = 'replyopts';
        $tab_title['icon'][0] = '';
        $tab_active = tab_active($tab_title, 0);
        $tab_content = opentabbody($tab_title['title'][0], 'replyopts', $tab_active);
        $tab_content .= '<div class="well m-t-20">'.$info['field']['options'].'</div>';
        $tab_content .= closetabbody();

        if (!empty($info['field']['attachment'])) {
            $tab_title['title'][1] = $locale['forum_0557'];
            $tab_title['id'][1] = 'attachtab';
            $tab_title['icon'][1] = '';
            $tab_content .= opentabbody($tab_title['title'][1], 'attachtab', $tab_active);
            $tab_content .= '<div class="well m-t-20">';
            $tab_content .= $info['field']['attachment'];
            $tab_content .= '</div>';
            $tab_content .= closetabbody();
        }

        $html .= opentab($tab_title, $tab_active, 'quickreplyfoem');
        $html .= $tab_content;
        $html .= closetab();

        $html .= $info['field']['button'];

        return $html;
    }
}

/**
 * Display The Tags and Threads
 */
if (!function_exists("display_forum_tags")) {
    function display_forum_tags($info) {
        fusion_load_script(INFUSIONS.'forum/templates/css/forum.css', 'css');
        $locale = fusion_get_locale();

        echo '<div class="forum-tags">';
        opentable('');

        echo render_breadcrumbs();

        echo '<h3>'.$locale['forum_tag_0100'].'</h3>';

        if (isset($_GET['tag_id'])) {
            echo forum_filter($info);

            if (!empty($info['threads']['pagenav'])) {
                echo '<div class="text-right">'.$info['threads']['pagenav'].'</div>';
            }

            echo '<div class="panel panel-primary forum-panel m-t-10">';
            if (!empty($info['threads'])) {
                echo '<div class="list-group">';
                if (!empty($info['threads']['sticky'])) {
                    foreach ($info['threads']['sticky'] as $cdata) {
                        echo '<div class="list-group-item clearfix">';
                        render_thread_item($cdata);
                        echo '</div>';
                    }
                }

                if (!empty($info['threads']['item'])) {
                    foreach ($info['threads']['item'] as $cdata) {
                        echo '<div class="list-group-item clearfix">';
                        render_thread_item($cdata);
                        echo '</div>';
                    }
                }
                echo '</div>';
            } else {
                echo '<div class="text-center p-20">'.$locale['forum_0269'].'</div>';
            }
            echo '</div>';

            if (!empty($info['threads']['pagenav'])) {
                echo '<div class="text-right hidden-xs m-t-15">'.$info['threads']['pagenav'].'</div>';
            }

            if (!empty($info['threads']['pagenav2'])) {
                echo '<div class="hidden-sm hidden-md hidden-lg m-t-15">'.$info['threads']['pagenav2'].'</div>';
            }
        } else {
            echo '<div class="row">';
            if (!empty($info['tags'])) {
                unset($info['tags'][0]);

                foreach ($info['tags'] as $tag_data) {
                    echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">';
                    $color = $tag_data['tag_color'];
                    echo '<div class="panel-body" style="height: 200px; background: '.$color.';">';
                    echo '<a href="'.$tag_data['tag_link'].'">';
                    echo '<h4 class="text-white">'.$tag_data['tag_title'].'</h4>';
                    echo '</a>';
                    echo '<p class="text-white">'.$tag_data['tag_description'].'</p>';

                    if (!empty($tag_data['threads'])) {
                        echo '<hr/><span class="tag_result text-white">';
                        $link = FORUM.'viewthread.php?thread_id='.$tag_data['threads']['thread_id'];
                        echo '<a class="text-white" href="'.$link.'">';
                        echo trim_text($tag_data['threads']['thread_subject'], 100);
                        echo '</a> - '.timer($tag_data['threads']['thread_lastpost']);
                        echo '</span>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            }
            echo '</div>';
        }

        closetable();
        echo '</div>';
    }
}

/**
 * Display The Forum Thread Page
 */
if (!function_exists('render_thread')) {
    function render_thread($info) {
        $locale = fusion_get_locale();

        fusion_load_script(INFUSIONS.'forum/templates/css/forum.css', 'css');

        echo '<div class="forum-viewthread">';
        opentable('');
        echo render_breadcrumbs();

        $buttons = !empty($info['buttons']) ? $info['buttons'] : [];
        $data    = !empty($info['thread']) ? $info['thread'] : [];
        $pdata   = !empty($info['post_items']) ? $info['post_items'] : [];

        echo '<h2>';
            if ($data['thread_sticky'] == TRUE) {
                echo '<i title="'.$locale['forum_0103'].'" class="'.get_forum_icons('sticky').'"></i>';
            }

            if ($data['thread_locked'] == TRUE) {
                echo '<i title="'.$locale['forum_0102'].'" class="'.get_forum_icons('lock').'"></i>';
            }

            echo $data['thread_subject'];
        echo '</h2>';

        echo '<div class="clearfix">';

        echo '<span class="last-updated">'.$locale['forum_0363'].' '.timer($data['thread_lastpost']).'</span>';

        if (!empty($info['thread_tags_display'])) {
            echo ' <i class="fa fa-tags"></i> '.$info['thread_tags_display'];
        }

        echo '</div>';

        echo !empty($info['poll_form']) ? '<div class="polls-block m-t-20">'.$info['poll_form'].'</div>' : '';

        echo '<div class="clearfix m-t-20">';
            echo '<div class="dropdown display-inline-block m-r-10">';
                echo '<a id="ddfilter5" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    echo '<strong>'.$locale['forum_0183'].'</strong> ';
                    $selector['oldest'] = $locale['forum_0180'];
                    $selector['latest'] = $locale['forum_0181'];
                    $selector['high'] = $locale['forum_0182'];
                    echo isset($_GET['sort_post']) && in_array($_GET['sort_post'], array_flip($selector)) ? $selector[$_GET['sort_post']] : $locale['forum_0180'];
                    echo '<span class="caret"></span>';
                echo '</a>';

                if (!empty($info['post-filters'])) {
                    echo '<ul class="dropdown-menu" aria-labelledby="ddfilter5">';
                        foreach ($info['post-filters'] as $filters) {
                            echo '<li class="dropdown-item"><a class="text-smaller" href="'.$filters['value'].'">'.$filters['locale'].'</a></li>';
                        }
                    echo '</ul>';
                }
            echo '</div>'; // dropdown

            if (!empty($buttons['notify'])) {
                echo '<a class="btn btn-default btn-sm m-r-10" href="'.$buttons['notify']['link'].'">'.$buttons['notify']['title'].'</a>';
            }

            echo '<a class="btn btn-default btn-sm m-r-10" href="'.$buttons['print']['link'].'">'.$buttons['print']['title'].'</a>';

            if ($info['permissions']['can_start_bounty']) {
                $active = !empty($info['thread']['thread_bounty']) ? ' disabled' : '';
                echo '<a class="btn btn-primary btn-sm m-r-10'.$active.'" href="'.$buttons['bounty']['link'].'">'.$buttons['bounty']['title'].'</a>';
            }

            if ($info['permissions']['can_create_poll'] && $info['permissions']['can_post']) {
                $active = !empty($info['thread']['thread_poll']) ? ' disabled' : '';
                echo '<a class="btn btn-success btn-sm m-r-10'.$active.'" href="'.$buttons['poll']['link'].'">'.$buttons['poll']['title'].'</a>';
            }

            if ($info['permissions']['can_post']) {
                $active = empty($buttons['newthread']) ? ' disabled' : '';
                echo '<a class="btn btn-primary btn-sm'.$active.'" href="'.$buttons['newthread']['link'].'">'.$buttons['newthread']['title'].'</a>';
            }

            echo !empty($info['page_nav']) ? '<div class="pull-right">'.$info['page_nav'].'</div>' : '';
        echo '</div>';

        if (!empty($pdata)) {
            $i = get('sort_post') == 'latest' ? count($pdata) : 1;
            foreach ($pdata as $post_id => $post_data) {
                render_post_item($post_data, $i + (isset($_GET['rowstart']) ? $_GET['rowstart'] : ''));

                if ($post_id == $info['post_firstpost']) {
                    if (!empty($info['thread_bounty'])) {
                        echo '<div class="block-bounty list-group m-b-20"><div class="list-group-item list-group-item-info">';
                            if (!empty($info['thread_bounty']['bounty_edit'])) {
                                echo '<a href="'.$info['thread_bounty']['bounty_edit']['link'].'">'.$info['thread_bounty']['bounty_edit']['title'].'</a>';
                            }
                            echo '<h4>'.$info['thread_bounty']['bounty_title'].'</h4>';
                            echo $locale['forum_4102'];
                            echo '<p class="text-dark">'.$info['thread_bounty']['bounty_description'].'</p>';
                        echo '</div></div>';
                    }
                }

                if (get('sort_post') == 'latest') {
                    $i--;
                } else {
                    $i++;
                }
            }
        }

        if (iMOD) {
            echo $info['mod_form'];
        }

        echo '<div class="clearfix m-t-20">';
            echo '<div class="pull-left">';
                if ($info['permissions']['can_post']) {
                    $active = empty($buttons['newthread']) ? ' disabled' : '';
                    echo '<a class="btn btn-primary btn-sm m-r-10'.$active.'" href="'.$buttons['newthread']['link'].'">'.$buttons['newthread']['title'].'</a>';
                }

                if ($info['permissions']['can_post']) {
                    if (!empty($buttons['reply'])) {
                        $active = empty($buttons['reply']) ? ' disabled' : '';
                        echo '<a class="btn btn-primary btn-sm'.$active.'" href="'.$buttons['reply']['link'].'">'.$buttons['reply']['title'].'</a>';
                    }
                }
            echo '</div>';

            echo !empty($info['page_nav']) ? '<div class="pull-right clearfix">'.$info['page_nav'].'</div>' : '';
        echo '</div>';

        if (!empty($info['quick_reply_form'])) {
            echo '<div class="m-t-10 p-t-5 p-b-0">'.$info['quick_reply_form'].'</div>';
        }

        echo '<div class="m-t-20 m-b-20">';
            $prm = $info['permissions'];
            $can = '<strong class="text-success">'.$locale['can'].'</strong>';
            $cannot = '<strong class="text-danger">'.$locale['cannot'].'</strong>';
            $poll = $data['thread_poll'];

            echo sprintf($locale['forum_perm_access'], $prm['can_access'] ? $can : $cannot).'<br/>';
            echo sprintf($locale['forum_perm_post'], $prm['can_post'] ? $can : $cannot).'<br/>';
            echo sprintf($locale['forum_perm_reply'], $prm['can_reply'] ? $can : $cannot).'<br/>';
            echo !$poll ? sprintf($locale['forum_perm_create_poll'], $prm['can_create_poll'] ? $can : $cannot).'<br/>' : '';
            echo $poll ? sprintf($locale['forum_perm_edit_poll'], $prm['can_edit_poll'] ? $can : $cannot).'<br/>' : '';
            echo $poll ? sprintf($locale['forum_perm_vote_poll'], $prm['can_vote_poll'] ? $can : $cannot).'<br/>' : '';
            echo sprintf($locale['forum_perm_upload'], $prm['can_upload_attach'] ? $can : $cannot).'<br/>';
            echo sprintf($locale['forum_perm_download'], $prm['can_download_attach'] ? $can : $cannot).'<br/>';
            echo $data['forum_type'] == 4 ? sprintf($locale['forum_perm_rate'], $prm['can_rate'] ? $can : $cannot).'<br/>' : '';
            echo $data['forum_type'] == 4 ? sprintf($locale['forum_perm_bounty'], $prm['can_start_bounty'] ? $can : $cannot) : '';
        echo '</div>';

        if ($info['forum_moderators']) {
            echo '<div class="m-b-10">'.$locale['forum_0185'].' '.$info['forum_moderators'].'</div>';
        }

        if (!empty($info['thread_users'])) {
            echo '<div class="clearfix"><strong>'.$locale['forum_0581'].'</strong> ';
                foreach ($info['thread_users'] as $user_id => $user) {
                    echo '<a href="'.BASEDIR.'profile.php?lookup='.$user_id.'">'.$user['user_name'].'</a>';
                    if (next($info['thread_users'])) {
                        echo ', ';
                    }
                }
            echo '</div>';
        }

        closetable();

        echo '</div>'; // .forum-viewthread
    }
}

/* Post Item */
if (!function_exists('render_post_item')) {
    function render_post_item($data, $i = 0) {
        $locale = fusion_get_locale();
        $forum_settings = \PHPFusion\Forums\ForumServer::getForumSettings();

        echo '<!-- forum-thread-prepost-'.$data['marker']['id'].' -->';
        echo '<div class="post-item m-t-20" id="'.$data['marker']['id'].'">';
            echo '<div class="clearfix">';
                echo '<div class="forum_avatar">';
                    echo display_avatar($data, '30px', FALSE, FALSE, 'img-rounded m-r-10 avatar');
                    echo '<span class="text-bold m-r-10">'.$data['user_profile_link'].'</span>';

                    if ($forum_settings['forum_rank_style'] == '0') {
                        echo '<span class="forum_rank">'.$data['user_rank'].'</span>';
                    } else {
                        echo $data['user_rank'];
                    }
                echo '</div>';

                echo $data['post_shortdate'];

                echo '<div class="pull-right">';
                if (isset($data['post_quote']) && !empty($data['post_quote'])) {
                    $quote = $data['post_quote'];
                    echo '<a href="'.$quote['link'].'" title="'.$quote['title'].'" ><i class="fa fa-quote-right"></i></a>';
                }

                /*if (isset($data['post_reply']) && !empty($data['post_reply'])) {
                    $reply = $data['post_reply'];
                    echo '<a href="'.$reply['link'].'" title="'.$reply['title'].'" class="m-l-5"><i class="fa fa-reply"></i></a>';
                }*/

                $print = $data['print'];
                echo '<a href="'.$print['link'].'" title="'.$print['title'].'" class="m-l-5"><i class="fa fa-print"></i></a>';

                if (isset($data['post_edit']) && !empty($data['post_edit'])) {
                    echo '<a href="'.$data['post_edit']['link'].'" title="'.$locale['forum_0507'].'" class="m-l-5"><i class="fa fa-pen"></i></a>';
                }

                if ($data['user_level'] > USER_LEVEL_SUPER_ADMIN && $data['user_id'] !== fusion_get_userdata('user_id')) {
                    if (iSUPERADMIN || (iADMIN && checkrights('M'))) {
                        $aidlink = fusion_get_aidlink();

                        echo '<div class="dropdown display-inline-block text-bold m-l-5">';
                            echo '<a href="#" id="ddpost'.$data['marker']['id'].'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">'.$locale['forum_0662'].'</a>';

                            echo '<ul class="dropdown-menu" aria-labelledby="ddpost'.$data['marker']['id'].'">';
                                echo '<li class="dropdown-item"><a href="'.ADMIN.'members.php'.$aidlink.'&ref=edit&lookup='.$data['user_id'].'">'.$locale['forum_0663'].'</a></li>';
                                echo '<li class="dropdown-item"><a href="'.ADMIN.'members.php'.$aidlink.'&lookup='.$data['user_id'].'&action=1">'.$locale['forum_0664'].'</a></li>';
                                echo '<li class="dropdown-item"><a href="'.ADMIN.'members.php'.$aidlink.'&ref=delete&lookup='.$data['user_id'].'">'.$locale['forum_0665'].'</a></li>';
                            echo '</ul>';
                        echo '</div>';
                    }
                }

                if (iMOD) {
                    echo '<input class="m-l-5" type="checkbox" id="check-'.$data['post_id'].'" name="delete_post[]" value="'.$data['post_id'].'">';
                }

                echo '<a href="#post_'.$data['post_id'].'" class="m-l-5">#'.$i.'</a>';
                echo '</div>';

            echo '</div>';

            echo '<div class="overflow-hide">';
                if ($data['post_votebox']) {
                    echo '<div class="pull-left m-r-10 vote-box">'.$data['post_votebox'].$data['post_answer_check'].'</div>';
                }

                echo '<div class="overflow-hide post-message">'.$data['post_message'].'</div>';
            echo '</div>';

            echo '<div class="m-t-20">';
                if (!empty($data['user_profiles'])) {
                    echo '<div class="post_profiles clearfix">';
                    foreach ($data['user_profiles'] as $attr) {
                        if ((!empty($attr['type']) && $attr['type'] == 'social') || !empty($attr['link']) && !empty($attr['icon'])) {
                            echo '<a class="social-link" href="'.$attr['link'].'"'.(fusion_get_settings('index_url_userweb') ? '' : 'rel="nofollow noopener noreferrer" ').'target="_blank">'.$attr['icon'].'</a>';
                        } else {
                            echo '<b>'.$attr['title'].'</b>: '.$attr['value'].' ';
                        }
                    }
                    echo '</div>';
                }

                echo !empty($data['user_sig']) ? '<div>'.$data['user_sig'].'</div>' : '';
                echo $data['post_edit_reason'];

                if (!empty($data['post_moods'])) {
                    $users = '';
                    foreach ($data['post_moods'] as $mdata) {
                        if (!empty($mdata['users'])) {
                            $users .= '<div class="mood_users" title="'.$mdata['mood_name'].'">';
                            $users .= '<i class="'.$mdata['mood_icon'].' fa-fw"></i> ';
                            $users .= implode(', ', array_map(function ($user) { return $user['profile_link']; }, $mdata['users']));
                            $users .= '</div>';
                        }
                    }

                    $count = format_word($data['post_moods']['users_count'], $locale['fmt_user']);
                    echo '<div class="forum-mood">';
                    echo '<a data-toggle="collapse" aria-expanded="false" aria-controls="#moods'.$data['post_id'].'" href="#moods'.$data['post_id'].'">'.$count.' '.$locale['forum_0528'].' <span class="caret"></span></a>';
                    echo '<div id="moods'.$data['post_id'].'" class="moods collapse">'.$users.'</div>';
                    echo '</div>';
                }

                echo !empty($data['post_mood_buttons']) ? $data['post_mood_buttons'] : '';

                if (!empty($data['post_bounty'])) {
                    echo '<a href="'.$data['post_bounty']['link'].'">'.$data['post_bounty']['title'].'</a>';
                }

                if ($data['post_attachments']) {
                    echo '<div class="forum_attachments clearfix m-t-10">'.$data['post_attachments'].'</div>';
                }
            echo '</div>';
        echo '</div>';
    }
}

if (!function_exists('render_last_posts_reply')) {
    function render_last_posts_reply($info) {
        $locale = fusion_get_locale();
        $forum_settings = get_settings('forum');

        echo "<p><strong>".$info['title']."</strong>\n</p>\n";
        echo "<div class='table-responsive'><table class='table'>\n";
        $i = $forum_settings['posts_per_page'];

        foreach ($info['last_post_items'] as $data) {
            $message = $data['post_message'];
            if ($data['post_smileys']) {
                $message = parsesmileys($message);
            }
            $message = parseubb($message);
            echo "<tr>\n<td class='tbl2 forum_thread_user_name' style='width:10%'><!--forum_thread_user_name-->".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
            echo "<td class='tbl2 forum_thread_post_date'>\n";
            echo "<div style='float:right' class='small'>\n";
            echo $i.($i == $forum_settings['forum_last_posts_reply'] ? " (".$locale['forum_0525'].")" : "");
            echo "</div>\n";
            echo "<div class='small'>".$locale['forum_0524'].showdate("forumdate", $data['post_datestamp'])."</div>\n";
            echo "</td>\n";
            echo "</tr>\n<tr>\n<td valign='top' class='tbl2 forum_thread_user_info' style='width:10%'>\n";
            echo display_avatar($data, '50px');
            echo "</td>\n<td valign='top' class='tbl1 forum_thread_user_post'>\n";
            echo nl2br($message);
            echo "</td>\n</tr>\n";
            $i--;
        }

        echo "</table></div>\n";
    }
}
