<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/templates.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
/**
 *  To customize the forums template output, copy and declare the same function in your theme.php
 */

/**
 * Forum Page Control Layout
 */
if (!function_exists('render_forum')) {
    function render_forum($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
        if (isset($_GET['viewforum'])) {
            forum_viewforum($info);
        } else {
            if (isset($_GET['section'])) {
                render_section($info);
            } else {
                render_forum_main($info);
            }
        }
    }
}

/**
 * Main Forum Page - Recursive
 *
 * @param array $info
 * @param int   $id - counter nth
 *                  Template File    templates/index/forum_index.html
 */
if (!function_exists('render_forum_main')) {
    function render_forum_main(array $info, $id = 0) {
        $locale = fusion_get_locale();
        /**
         * WORK IN PROGRESS TO IMPLEMENT TEMPLATE CLASS
         */

        $html = \PHPFusion\Template::getInstance('forum_index');
        $html->set_template(FORUM.'templates/index/forum_index.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('forum_bg_src', FORUM.'images/default_forum_bg.jpg');
        $html->set_tag('title', $locale['forum_0013']);
        $html->set_tag('new_thread_link_url', $info['new_topic_link']['link']);
        $html->set_tag('new_thread_link_title', $info['new_topic_link']['title']);
        if (!empty($info['forums'][$id])) {
            $chtml = \PHPFusion\Template::getInstance('forum_su_index');
            $chtml->set_template(FORUM.'templates/index/forum_item.html');
            foreach ($info['forums'][$id] as $forum_id => $data) {
                if ($data['forum_type'] == 1) {

                    $chtml->set_block('category_header', [
                        'forum_title_link'  => $data['forum_link']['title'],
                        'threads_title'     => $locale['forum_0002'],
                        'post_title'        => $locale['forum_0003'],
                        'last_thread_title' => $locale['forum_0012'],
                    ]);

                    $category_header = $chtml->get_output();
                    // repeat this.
                    if (isset($info['forums'][0][$forum_id]['child'])) {
                        $i = 1;
                        $content = '';
                        foreach ($info['forums'][0][$forum_id]['child'] as $sub_forum_id => $cdata) {
                            $content .= render_forum_item($cdata, $i);
                            $i++;
                        }
                        $html->set_block('forum_content', ['forum_content' => $category_header.$content]);
                    } else {
                        $chtml->set_block('no_item', ['message' => $locale['forum_0327']]);
                    }
                }
            }
            $html->set_tag('forum_content', ['content' => 'TBA']);
        } else {
            $html->set_block('no_item', ['message' => $locale['forum_0328']]);
        }

        $threadTags = \PHPFusion\Forums\ForumServer::tag(TRUE, FALSE)->get_TagInfo();
        $html->set_tag('tags_title', $locale['forum_0272']);
        if (!empty($threadTags['tags'])) {
            foreach ($threadTags['tags'] as $tag_id => $tag_data) {
                $html->set_block('tags', [
                    'active_class' => ($tag_data['tag_active'] == TRUE ? ' active' : ''),
                    'tag_link'     => $tag_data['tag_link'],
                    'tag_color'    => $tag_data['tag_color'],
                    'tag_title'    => $tag_data['tag_title'],
                ]);
            }
        } else {
            $html->set_block('tags_no_item', ['message' => $locale['forum_0274']]);
        }

        // Popular Threads this Week
        // An example that you can run core codes still in the template controller function
        $html->set_tag('popular_threads_title', $locale['forum_0273']);
        $custom_result = dbquery("SELECT t.thread_id, t.thread_subject, t.thread_author, t.thread_postcount FROM ".DB_FORUMS." tf
        INNER JOIN ".DB_FORUM_THREADS." t ON tf.forum_id=t.forum_id
        ".(multilang_column('FO') ? " WHERE forum_language='".LANGUAGE."' AND " : " WHERE ").groupaccess('forum_access')." and (t.thread_lastpost >=:one_week and t.thread_lastpost < :current) and t.thread_locked=:not_locked and t.thread_hidden=:not_hidden
        GROUP BY t.thread_id ORDER BY t.thread_postcount DESC LIMIT 10",
            [
                ':one_week'   => TIME - (7 * 24 * 3600),
                ':current'    => TIME,
                ':not_locked' => 0,
                ':not_hidden' => 0,
            ]);
        if (dbrows($custom_result)) {
            while ($popular = dbarray($custom_result)) {
                $user = fusion_get_user($popular['thread_author']);
                $html->set_block('popular_threads_item', [
                    'p_link'         => FORUM."viewthread.php?thread_id=".$popular['thread_id'],
                    'p_title'        => $popular['thread_subject'],
                    'p_profile_link' => $locale['by']." ".profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                    'p_count'        => format_word($popular['thread_postcount'], $locale['fmt_post'])
                ]);
            }
        } else {
            $html->set_block('no_popular_threads', [
                'p_message' => $locale['forum_0275']
            ]);
        }
        echo $html->get_output();
    }
}

/**
 * Switch between different types of forum list containers
 *
 * @param $data
 * Template File    templates/index/forum_item.html
 */
if (!function_exists('render_forum_item')) {
    function render_forum_item($data) {
        $locale = fusion_get_locale();
        $html = \PHPFusion\Template::getInstance('forum_item');
        $html->set_template(FORUM.'templates/index/forum_item.html');

        $l_html = \PHPFusion\Template::getInstance('forum_item_lastpost');
        $l_html->set_template(FORUM.'templates/index/forum_item_lastpost.html'); // we have already cached it earlier?
        if (empty($data['thread_lastpost'])) {
            $l_html->set_block('forum_no_lastpost', [
                'message' => $locale['forum_0005']
            ]);
        } else {
            $l_html->set_block('forum_lastpost', [
                'avatar'               => (!empty($data['last_post']['avatar']) ? $data['last_post']['avatar'] : ''),
                'last_thread_link'     => $data['last_post']['post_link'],
                'last_thread_subject'  => $data['thread_subject'],
                'profile_link'         => $data['last_post']['profile_link'],
                'last_thread_activity' => $data['last_post']['time']
            ]);
        }

        $template_arr = [
            'forum_link_url'         => $data['forum_link']['link'],
            'forum_link_title'       => $data['forum_link']['title'],
            'forum_description'      => $data['forum_description'],
            'forum_moderators_title' => $locale['forum_0007'],
            'forum_moderators'       => $data['forum_moderators'],
            'forum_thread_count'     => $data['forum_threadcount_word'],
            'forum_post_count'       => $data['forum_postcount_word'],
            'forum_lastpost'         => $l_html->get_output()
        ];

        $html->set_block('forums', $template_arr);
        $output = $html->get_output();

        return $output;
    }
}

/**
 * Viewforum (Index)
 * Shows the forum threads and details
 * Template File    templates/forum_viewforum.html
 * Template File    templates/viewforum/forum_users.html
 */
if (!function_exists('forum_viewforum')) {
    function forum_viewforum($info) {
        $locale = fusion_get_locale();
        $tpl = \PHPFusion\Template::getInstance('viewforum');
        $tpl->set_template(FORUM.'templates/forum_viewforum.html');
        // Make it so it can get arrays and values
        $tpl->set_tag('background_src', FORUM.'images/default_forum_bg.jpg');
        $tpl->set_tag('breadcrumb', render_breadcrumbs());
        $tpl->set_tag('title', $info['forum_name']);
        $tpl->set_tag('description', $info['forum_description']);
        if ($info['forum_rules']) {
            $tpl->set_block('rules', ['forum_rules' => alert("<span class='strong'><i class='fa fa-exclamation fa-fw'></i>".$locale['forum_0350']."</span> ".$info['forum_rules'])]);
        }
        $i = 0;
        foreach ($info['forum_page_link'] as $view_keys => $page_link) {
            $tpl->set_block('navbar_item', [
                'active' => ((!isset($_GET['view']) && (!$i)) || (isset($_GET['view']) && $_GET['view'] === $view_keys) ? " class='active'" : ''),
                'link'   => $page_link['link'],
                'title'  => $page_link['title']
            ]);
            $i++;
        }
        if (iMEMBER && $info['permissions']['can_post'] && !empty($info['new_thread_link'])) {
            $tpl->set_block('post_button', [
                'new_thread_link_url'   => $info['new_thread_link']['link'],
                'new_thread_link_title' => $info['new_thread_link']['title']
            ]);
        }

        if ($info['forum_moderators']) {
            $tpl->set_block('moderator_list', ['moderators' => $info['forum_moderators'], 'title' => $locale['forum_0185']]);
        }
        // Draw the view
        if (isset($_GET['view'])) {
            switch ($_GET['view']) {
                default:
                case 'threads':
                    if ($info['forum_type'] > 1) {
                        $tpl->set_block('view', ['content' => render_forum_threads($info)]);
                    }
                    break;
                case 'subforums':
                    $ctpl = \PHPFusion\Template::getInstance('viewforum_subforums');
                    $ctpl->set_template(FORUM.'templates/viewforum/forum_subforums.html');
                    $ctpl->set_tag('title1', $locale['forum_0351']);
                    $ctpl->set_tag('title2', $locale['forum_0002']);
                    $ctpl->set_tag('title3', $locale['forum_0003']);
                    $ctpl->set_tag('title4', $locale['forum_0012']);
                    if (!empty($info['item'][$_GET['forum_id']]['child'])) {
                        $i = 1;
                        foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
                            $ctpl->set_block('subforums', ['content' => fusion_get_function('render_forum_item', $subforum_data, $i)]);
                            $i++;
                        }
                    } else {
                        $ctpl->set_block('no_item', ['message' => $locale['forum_0019']]);
                    }
                    break;
                case 'people':
                    $user_tpl = \PHPFusion\Template::getInstance('viewforum_users');
                    $user_tpl->set_template(FORUM.'templates/viewforum/forum_users.html');
                    $user_tpl->set_block('pagenav', ['pagenav' => $info['pagenav']]);
                    $user_tpl->set_tag('person_title', $locale['forum_0018']);
                    $user_tpl->set_tag('latest_thread_title', $locale['forum_0012']);
                    $user_tpl->set_tag('activity_title', $locale['forum_0016']);
                    if (!empty($info['item'])) {
                        foreach ($info['item'] as $user) {
                            $user_tpl->set_block('users_list', [
                                'avatar'        => display_avatar($user, '30px', '', '', ''),
                                'profile_link'  => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                                'thread_link'   => "<a href='".$user['thread_link']['link']."'>".$user['thread_link']['title']."</a>",
                                'activity_date' => showdate('forumdate', $user['post_datestamp']).", ".timer($user['post_datestamp'])
                            ]);
                        }
                    }
                    $tpl->set_block('view', ['content' => $user_tpl->get_output()]);
                    break;
                case 'activity':
                    $ctpl = \PHPFusion\Template::getInstance('viewforum_activity');
                    $ctpl->set_template(FORUM.'templates/viewforum/forum_activity.html');
                    if (!empty($info['item'])) {
                        $ctpl->set_block('pagenav', ['pagenav' => $info['pagenav']]);
                        $ctpl->set_tag('post_count', format_word($info['max_post_count'], $locale['fmt_post']));
                        $ctpl->set_tag('last_activity_link', "<a href='".$info['last_activity']['link']."'>".$locale['forum_0020']."</a>");
                        $ctpl->set_tag('last_activity_info', sprintf($locale['forum_0021'],
                            showdate('forumdate', $info['last_activity']['time']),
                            profile_link($info['last_activity']['user']['user_id'], $info['last_activity']['user']['user_name'], $info['last_activity']['user']['user_status'])
                        ));

                        $i = 0;
                        foreach ($info['item'] as $post_id => $postData) {
                            $ctpl->set_block('activity_items', [
                                'spacing'      => (!$i ? " m-t-0" : ''),
                                'avatar'       => display_avatar($postData['post_author'], '50px', FALSE, '', ''),
                                'profile_link' => profile_link($postData['post_author']['user_id'], $postData['post_author']['user_name'], $postData['post_author']['user_status']),
                                'post_date'    => showdate('forumdate', $postData['post_datestamp']),
                                'post_timer'   => timer($postData['post_datestamp']),
                                'post_link'    => $locale['forum_0022']." <a href='".$postData['thread_link']['link']."'>".$postData['thread_link']['title']."</a>",
                                'thread_link'  => $locale['forum_0023']." ".$postData['thread_link']['title'],
                                'post_message' => parse_textarea($postData['post_message'], TRUE, TRUE, TRUE, IMAGES, TRUE),
                                'post_link'    => "<a href='".$postData['thread_link']['link']."'>".$locale['forum_0024']."</a>\n"
                            ]);
                            $i++;
                        }
                    } else {
                        $ctpl->set_block('no_item', ['message' => $locale['forum_4121']]);
                    }
                    $tpl->set_block('view', ['content' => $ctpl->get_output()]);
                    break;
            }
        } else {
            $tpl->set_block('view', ['content' => render_forum_threads($info)]);
        }

        $tpl->set_tag('can_access', sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));
        $tpl->set_tag('can_post', sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));
        $tpl->set_tag('can_create_poll', sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));
        $tpl->set_tag('can_upload_attach', sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));
        $tpl->set_tag('can_download_attach', sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));

        echo $tpl->get_output();
    }
}
/**
 * Threads Item Display
 * Template File     templates/viewforum/forum_thread_item.html
 */
if (!function_exists('render_forum_threads')) {
    function render_forum_threads($info) {
        $locale = fusion_get_locale();
        // Ok, since this is a subpage and also require replacement, we need a new html file.
        $tpl = \PHPFusion\Template::getInstance('thread_items');
        $tpl->set_template(FORUM.'templates/viewforum/forum_thread_item.html');
        if (!empty($info['filters']['type'])) {
            foreach ($info['filters']['type'] as $key => $tabs) {
                $tpl->set_block('tab_filter', [
                    'filter_link' => $tabs['link'],
                    'active_text' => $tabs['active'] ? " text-active" : "",
                    'title'       => $tabs['icon'].$tabs['title'],
                    'count'       => $tabs['count']
                ]);
            }
        }
        $tpl->set_tag('forum_filter', forum_filter($info));
        if (!empty($info['threads']['pagenav'])) {
            $tpl->set_block('pagenav_a', ['navigation' => $info['threads']['pagenav']]);
        }
        $tpl->set_tag('title1', $locale['forum_0228']);
        $tpl->set_tag('title2', $locale['forum_0052']);
        $tpl->set_tag('title3', $locale['forum_0020']);
        $tpl->set_tag('title4', $locale['forum_0053']);
        if (!empty($info['threads'])) {
            if (!empty($info['threads']['sticky'])) {
                foreach ($info['threads']['sticky'] as $cdata) {
                    $tpl->set_block('sticky_threads', [
                        'thread_id'           => $cdata['thread_id'],
                        'avatar'              => $cdata['thread_last']['avatar'],
                        'thread_link_url'     => $cdata['thread_link']['link'],
                        'thread_link_title'   => $cdata['thread_link']['title'],
                        'thread_icons'        => implode('', $cdata['thread_icons']),
                        'thread_pages'        => $cdata['thread_pages'],
                        'author_profile_link' => $cdata['thread_starter']['profile_link'],
                        'last_activity_time'  => timer($cdata['thread_last']['time']),
                        'thread_views'        => number_format($cdata['thread_views']),
                        'thread_postcount'    => number_format($cdata['thread_postcount']),
                        'thread_votecount'    => number_format($cdata['vote_count']),
                        'track_button'        => (isset($cdata['track_button']) ? "<a class='btn btn-danger btn-sm' onclick=\"return confirm('".$locale['global_060']."');\" href='".$cdata['track_button']['link']."'>".$cdata['track_button']['title']."</a>" : '')
                    ]);
                }
            }
            if (!empty($info['threads']['item'])) {
                foreach ($info['threads']['item'] as $cdata) {
                    $tpl->set_block('normal_threads', [
                        'thread_id'           => $cdata['thread_id'],
                        'avatar'              => $cdata['thread_last']['avatar'],
                        'thread_link_url'     => $cdata['thread_link']['link'],
                        'thread_link_title'   => $cdata['thread_link']['title'],
                        'thread_icons'        => implode('', $cdata['thread_icons']),
                        'thread_pages'        => $cdata['thread_pages'],
                        'author_profile_link' => $cdata['thread_starter']['profile_link'],
                        'last_activity_time'  => timer($cdata['thread_last']['time']),
                        'thread_views'        => number_format($cdata['thread_views']),
                        'thread_postcount'    => number_format($cdata['thread_postcount']),
                        'thread_votecount'    => number_format($cdata['vote_count']),
                        'track_button'        => (isset($cdata['track_button']) ? "<a class='btn btn-danger btn-sm' onclick=\"return confirm('".$locale['global_060']."');\" href='".$cdata['track_button']['link']."'>".$cdata['track_button']['title']."</a>" : '')
                    ]);
                }
            }
        } else {
            $tpl->set_block('no_item', ['message' => $locale['forum_0269']]);
        }
        if (!empty($info['threads']['pagenav2'])) {
            $tpl->set_block('pagenav_b', ['navigation_2' => $info['threads']['pagenav2']]);
        }

        return $tpl->get_output();
    }
}

/* Forum Filter */
if (!function_exists('forum_filter')) {
    function forum_filter($info) {
        // Put into core views
        $locale = fusion_get_locale();
        // This one need to push to core.
        $selector = array(
            'today'  => $locale['forum_0212'],
            '2days'  => $locale['forum_p002'],
            '1week'  => $locale['forum_p007'],
            '2week'  => $locale['forum_p014'],
            '1month' => $locale['forum_p030'],
            '2month' => $locale['forum_p060'],
            '3month' => $locale['forum_p090'],
            '6month' => $locale['forum_p180'],
            '1year'  => $locale['forum_3015']
        );

        // This one take out from default filtrations
        // Type $_GET['type']
        $selector2 = array(
            'all'         => $locale['forum_0374'],
            'discussions' => $locale['forum_0222'],
            'attachments' => $locale['forum_0223'],
            'poll'        => $locale['forum_0314'],
            'solved'      => $locale['forum_0378'],
            'unsolved'    => $locale['forum_0379'],
        );

        $selector3 = array(
            'author'  => $locale['forum_0052'],
            'time'    => $locale['forum_0381'],
            'subject' => $locale['forum_0051'],
            'reply'   => $locale['forum_0054'],
            'view'    => $locale['forum_0053'],
        );

        // how to stack it.
        $selector4 = array(
            'descending' => $locale['forum_0230'],
            'ascending'  => $locale['forum_0231']
        );
        // temporarily fix before moving to TPL
        ob_start();
        if (isset($_GET['tag_id']) && isnum($_GET['tag_id']) || isset($_GET['forum_id']) && isnum($_GET['forum_id'])) {
            ?>
            <div class='clearfix'>
                <div class='pull-left'>
                    <?php echo $locale['forum_0388']; ?>
                    <div class='forum-filter'>
                        <button class='btn btn-xs <?php echo(isset($_GET['time']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                            <?php echo(isset($_GET['time']) && in_array($_GET['time'], array_flip($selector)) ? $selector[$_GET['time']] : $locale['forum_0211']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu'>
                            <?php
                            foreach ($info['filter']['time'] as $filter_locale => $filter_link) {
                                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class='pull-left'>
                    <div class='forum-filter'>
                        <?php echo $locale['forum_0225'] ?>
                        <button class='btn btn-xs <?php echo(isset($_GET['sort']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                            <?php echo(isset($_GET['sort']) && in_array($_GET['sort'], array_flip($selector3)) ? $selector3[$_GET['sort']] : $locale['forum_0381']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-right'>
                            <?php
                            foreach ($info['filter']['sort'] as $filter_locale => $filter_link) {
                                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                    <div class='forum-filter'>
                        <button class='btn btn-xs <?php echo(isset($_GET['order']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                            <?php echo(isset($_GET['order']) && in_array($_GET['order'], array_flip($selector4)) ? $selector4[$_GET['order']] : $locale['forum_0230']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-right'>
                            <?php
                            foreach ($info['filter']['order'] as $filter_locale => $filter_link) {
                                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>


            <?php
            /*echo "<div class='forum-filter'>\n";
            echo "<button class='btn btn-xs btn-default dropdown-toggle' data-toggle='dropdown'>".(isset($_GET['type']) && in_array($_GET['type'],
                    array_flip($selector2)) ? $selector2[$_GET['type']] : $locale['forum_0390'])." <span class='caret'></span></button>\n";
            echo "<ul class='dropdown-menu'>\n";
            foreach ($info['filter']['type'] as $filter_locale => $filter_link) {
                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
            }
            echo "</ul>\n";
            echo "</div>\n";
            */
            ?>


            <?php
        }

        return ob_get_clean();
    }
}

/**
 * Forum Sections Item Display (Latest, Participated, Tracked, Unanswered, Unsolved)
 * Template File     templates/viewforum/forum_thread_item.html
 */
if (!function_exists("render_section")) {
    function render_section($info) {
        $locale = fusion_get_locale();
        $html = \PHPFusion\Template::getInstance('forum_section');
        $html->set_template(FORUM.'templates/forum_section.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('filter_dropdown', $info['threads_time_filter']);
        $html->set_tag('title1', $locale['forum_0228']);
        $html->set_tag('title2', $locale['forum_0052']);
        $html->set_tag('title3', $locale['forum_0020']);
        $html->set_tag('title4', $locale['forum_0053']);
        if (!empty($info['threads']['pagenav'])) {
            $html->set_block('pagenav', ['pagenav' => $info['threads']['pagenav']]);
        }
        if (!empty($info['threads'])) {
            if (!empty($info['threads']['sticky'])) {
                foreach ($info['threads']['sticky'] as $cdata) {
                    $html->set_block('sticky_threads', [
                        'thread_id'           => $cdata['thread_id'],
                        'avatar'              => $cdata['thread_last']['avatar'],
                        'thread_link_url'     => $cdata['thread_link']['link'],
                        'thread_link_title'   => $cdata['thread_link']['title'],
                        'thread_icons'        => implode('', $cdata['thread_icons']),
                        'thread_pages'        => $cdata['thread_pages'],
                        'author_profile_link' => $cdata['thread_starter']['profile_link'],
                        'last_activity_time'  => timer($cdata['thread_last']['time']),
                        'thread_views'        => number_format($cdata['thread_views']),
                        'thread_postcount'    => number_format($cdata['thread_postcount']),
                        'thread_votecount'    => number_format($cdata['vote_count']),
                        'track_button'        => (isset($cdata['track_button']) ? "<a class='btn btn-danger btn-sm' onclick=\"return confirm('".$locale['global_060']."');\" href='".$cdata['track_button']['link']."'>".$cdata['track_button']['title']."</a>" : '')
                    ]);
                }
            }
            if (!empty($info['threads']['item'])) {
                foreach ($info['threads']['item'] as $cdata) {
                    $html->set_block('normal_threads', [
                        'thread_id'           => $cdata['thread_id'],
                        'avatar'              => $cdata['thread_last']['avatar'],
                        'thread_link_url'     => $cdata['thread_link']['link'],
                        'thread_link_title'   => $cdata['thread_link']['title'],
                        'thread_icons'        => implode('', $cdata['thread_icons']),
                        'thread_pages'        => $cdata['thread_pages'],
                        'author_profile_link' => $cdata['thread_starter']['profile_link'],
                        'last_activity_time'  => timer($cdata['thread_last']['time']),
                        'thread_views'        => number_format($cdata['thread_views']),
                        'thread_postcount'    => number_format($cdata['thread_postcount']),
                        'thread_votecount'    => number_format($cdata['vote_count']),
                        'track_button'        => (isset($cdata['track_button']) ? "<a class='btn btn-danger btn-sm' onclick=\"return confirm('".$locale['global_060']."');\" href='".$cdata['track_button']['link']."'>".$cdata['track_button']['title']."</a>" : '')
                    ]);
                }
            }
        } else {
            $html->set_block('no_item', ['message' => $locale['forum_0269']]);
        }
        echo $html->get_output();
    }
}

/**
 * Custom Modal New Topic
 * This is unused by the core but you can implement it.
 */
if (!function_exists('forum_newtopic')) {
    function forum_newtopic() {
        $locale = fusion_get_locale();
        if (isset($_POST['select_forum'])) {
            $_POST['forum_sel'] = isset($_POST['forum_sel']) && isnum($_POST['forum_sel']) ? $_POST['forum_sel'] : 0;
            redirect(FORUM.'post.php?action=newthread&forum_id='.$_POST['forum_sel']);
        }
        echo openmodal('newtopic', $locale['forum_0057'], array('button_id' => 'newtopic', 'class' => 'modal-md'));
        $index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
        $result = dbquery("SELECT a.forum_id, a.forum_name, b.forum_name as forum_cat_name, a.forum_post
		 FROM ".DB_FORUMS." a
		 LEFT JOIN ".DB_FORUMS." b ON a.forum_cat=b.forum_id
		 WHERE ".groupaccess('a.forum_access')." ".(multilang_table("FO") ? "AND a.forum_language='".LANGUAGE."' AND" : "AND")."
		 (a.forum_type ='2' or a.forum_type='4') AND a.forum_post < ".USER_LEVEL_PUBLIC." AND a.forum_lock !='1' ORDER BY a.forum_cat ASC, a.forum_branch ASC, a.forum_name ASC");
        $options = array();
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $depth = get_depth($index, $data['forum_id']);
                if (checkgroup($data['forum_post'])) {
                    $options[$data['forum_id']] = str_repeat("&#8212;", $depth).$data['forum_name']." ".($data['forum_cat_name'] ? "(".$data['forum_cat_name'].")" : '');
                }
            }

            echo "<div class='well clearfix m-t-10'>\n";
            echo form_select('forum_sel', $locale['forum_0395'], '', array(
                'options' => $options,
                'inline'  => 1,
                'width'   => '100%'
            ));
            echo "<div class='display-inline-block col-xs-12 col-sm-offset-3'>\n";
            echo form_button('select_forum', $locale['forum_0396'], 'select_forum', array('class' => 'btn-primary btn-sm'));
            echo "</div>\n";
            echo "</div>\n";
            echo closeform();
        } else {
            echo "<div class='well text-center'>\n";
            echo $locale['forum_0328'];
            echo "</div>\n";
        }
        echo closemodal();
    }
}

/**
 * Forum Confirmation Message Box
 * Template File    templates/forum_postify.html
 */
if (!function_exists('render_postify')) {
    function render_postify($info) {
        $html = \PHPFusion\Template::getInstance('forum_postify');
        $html->set_template(FORUM.'templates/forum_postify.html');
        $html->set_tag('opentable', fusion_get_function('opentable', $info['title']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('alert_class', ($info['error'] ? "alert alert-danger" : "well"));
        if (!empty($info['description'])) {
            $html->set_block('description', ['message' => $info['description']]);
        }
        foreach ($info['link'] as $link) {
            $html->set_block('links', ['link_url' => $link['url'], 'link_title' => $link['title']]);
        }
        echo $html->get_output();
    }
}

/**
 * Display the post reply form
 * Template File    templates/forms/post.html
 */
if (!function_exists("display_forum_postform")) {
    function display_forum_postform($info) {
        $locale = fusion_get_locale();
        $html = \PHPFusion\Template::getInstance('forum_postform');
        $html->set_template(FORUM.'templates/forms/post.html');
        $tab_title['title'][0] = $locale['forum_0602'];
        $tab_title['id'][0] = 'postopts';
        $tab_title['icon'][0] = '';
        $tab_active = tab_active($tab_title, 0);
        $tab_content = opentabbody($tab_title['title'][0], 'postopts', $tab_active); // first one is guaranteed to be available
        $tab_content .= "<div class='well m-t-20'>\n";
        $tab_content .= $info['delete_field'];
        $tab_content .= $info['sticky_field'];
        $tab_content .= $info['notify_field'];
        $tab_content .= $info['lock_field'];
        $tab_content .= $info['hide_edit_field'];
        $tab_content .= $info['smileys_field'];
        $tab_content .= $info['signature_field'];
        $tab_content .= "</div>\n";
        $tab_content .= closetabbody();
        if (!empty($info['attachment_field'])) {
            $tab_title['title'][1] = $locale['forum_0557'];
            $tab_title['id'][1] = 'attach_tab';
            $tab_title['icon'][1] = '';
            $tab_content .= opentabbody($tab_title['title'][1], 'attach_tab', $tab_active);
            $tab_content .= "<div class='well m-t-20'>\n".$info['attachment_field']."</div>\n";
            $tab_content .= closetabbody();
        }
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['title']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('description', $info['description']);
        $html->set_tag('forum_fields', $info['forum_field'].$info['forum_id_field'].$info['thread_id_field']);
        $html->set_tag('forum_subject_field', $info['subject_field']);
        $html->set_tag('forum_tag_field', $info['tags_field']);
        $html->set_tag('forum_message_field', $info['message_field']);
        $html->set_tag('forum_edit_reason_field', $info['edit_reason_field']);
        $html->set_tag('forum_poll_form', $info['poll_form']);
        $html->set_tag('forum_post_options', opentab($tab_title, $tab_active, 'newthreadopts').$tab_content.closetab());
        $html->set_tag('forum_post_button', $info['post_buttons']);

        if (!empty($info['last_posts_reply'])) {
            $html->set_block('lastpost', ['post_items' => $info['last_posts_reply']]);
        }

        echo $info['openform'];
        echo $html->get_output();
        echo $info['closeform'];
    }
}

/**
 * Display the poll creation form
 * Template File    templates/forms/poll.html
 */
if (!function_exists("display_forum_pollform")) {
    function display_forum_pollform($info) {
        $html = \PHPFusion\Template::getInstance('forum_pollform');
        $html->set_template(FORUM.'templates/forms/poll.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['title']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('description', $info['description']);
        $html->set_tag('pollform', $info['field']['poll_field'].$info['field']['poll_button']);
        echo $info['field']['openform'];
        echo $html->get_output();
        echo $info['field']['closeform'];
    }
}

/**
 * Display the bounty creation form
 * Template File        templates/forms/bounty.html
 */
if (!function_exists('display_form_bountyform')) {
    function display_forum_bountyform($info) {
        $html = \PHPFusion\Template::getInstance('forum_bntyform');
        $html->set_template(FORUM.'templates/forms/bounty.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['title']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('description', $info['description']);
        $html->set_tag('bountyform', $info['field']['bounty_select'].$info['field']['bounty_description'].$info['field']['bounty_button']);
        echo $info['field']['openform'];
        echo $html->get_output();
        echo $info['field']['closeform'];
    }
}

/**
 * Display the Quick Reply Form
 * Template File        templates/forms/quick_reply.html
 */
if (!function_exists("display_quick_reply")) {
    function display_quick_reply($info) {
        $html = \PHPFusion\Template::getInstance('forum_qrform');
        $html->set_template(FORUM.'templates/forms/quick_reply.html');
        $html->set_tag('description', $info['description']);
        $html->set_tag('message_field', $info['field']['message']);
        $html->set_tag('options_field', $info['field']['options']);
        $html->set_tag('button', $info['field']['button']);

        return $html->get_output();
    }
}

/**
 * Display The Tags and Threads
 * Template File        templates/tags/tags_threads.html
 */
if (!function_exists("display_forum_tags")) {
    function display_forum_tags($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
        $locale = fusion_get_locale();
        if (isset($_GET['tag_id'])) {
            $html = \PHPFusion\Template::getInstance('tags');
            $html->set_template(FORUM.'templates/tags/tag_threads.html');
            $html->set_tag('title', $locale['forum_0002']);
            $html->set_tag('filter', fusion_get_function('forum_filter', $info));
            $html->set_tag('breadcrumb', render_breadcrumbs());
            if (!empty($info['threads']['pagenav'])) {
                $html->set_block('pagenav', ['pagenav' => $info['threads']['pagenav']]);
                $html->set_block('pagenav_a', ['pagenav_a' => $info['threads']['pagenav']]);
            }
            if (!empty($info['threads'])) {
                $content = '';
                if (!empty($info['threads']['sticky'])) {
                    foreach ($info['threads']['sticky'] as $cdata) {
                        $content .= fusion_get_function('render_thread_item', $cdata);
                    }
                }
                if (!empty($info['threads']['item'])) {
                    foreach ($info['threads']['item'] as $cdata) {
                        $content .= fusion_get_function('render_thread_item', $cdata);
                    }
                }
                $html->set_block('threads', ['content' => $content]);
            } else {
                $html->set_block('no_threads', ['message' => $locale['forum_0269']]);
            }
            if (!empty($info['threads']['pagenav2'])) {
                $html->set_block('pagenav2', ['pagenav2' => $info['threads']['pagenav2']]);
            }
            echo $html->get_output();

        } else {
            $html = \PHPFusion\Template::getInstance('tags');
            $html->set_template(FORUM.'templates/tags/tag.html');
            $html->set_tag('breadcrumb', render_breadcrumbs());
            if (!empty($info['tags'])) {
                unset($info['tags'][0]);
                foreach ($info['tags'] as $tag_id => $tag_data) {
                    $html->set_block('tag_block', [
                        'tag_color'       => $tag_data['tag_color'],
                        'tag_link'        => $tag_data['tag_link'],
                        'tag_title'       => $tag_data['tag_title'],
                        'tag_description' => $tag_data['tag_description'],
                        'thread_subject'  => (!empty($tag_data['threads']) ? trim_text($tag_data['threads']['thread_subject'], 100) : ''),
                        'thread_activity' => (!empty($tag_data['threads']) ? timer($tag_data['threads']['thread_lastpost']) : ''),
                    ]);
                }
            } else {
                $html->set_block('no_tag', ['message' => $locale['forum_0276']]);
            }
            echo $html->get_output();
        }
    }
}

/* display threads -- need to simplify */
if (!function_exists('render_thread_item')) {
    function render_thread_item($info) {
        $locale = fusion_get_locale();

        echo "<div class='thread-item' id='thread_".$info['thread_id']."'>\n";
        echo "<div class='row m-0'>\n";
        echo "<div class='col-xs-12 col-sm-9 col-md-6 p-l-0'>\n";
        echo "<div class='pull-left m-r-10 m-t-5'>\n".$info['thread_last']['avatar']."</div>\n";
        $thead_icons = '';
        foreach ($info['thread_icons'] as $icon) {
            $thead_icons .= $icon;
        }
        echo "<div class='overflow-hide'>\n";
        echo "<a class='forum-link' href='".$info['thread_link']['link']."'>".$info['thread_link']['title']."</a>\n<span class='m-l-10 m-r-10 text-lighter'>".$thead_icons."</span>\n";
        echo "<div class='text-smaller'>".$info['thread_starter']."</div>\n";
        echo $info['thread_pages'];
        echo isset($info['track_button']) ? "<div class='forum_track'><a onclick=\"return confirm('".$locale['global_060']."');\" href='".$info['track_button']['link']."'>".$info['track_button']['name']."</a>\n</div>\n" : '';
        echo "</div>\n";
        echo "</div>\n"; // end grid
        echo "<div class='hidden-xs col-sm-3 col-md-3 p-l-0 p-r-0 text-center'>\n";
        echo "<div class='display-inline-block forum-stats p-5 m-r-5 m-b-0'>\n";
        echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($info['thread_views'])."</h4>\n";
        echo "<span>".format_word($info['thread_views'], $locale['fmt_views'], array('add_count'=>0))."</span>";
        echo "</div>\n";
        echo "<div class='display-inline-block forum-stats p-5 m-r-5 m-b-0'>\n";
        echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($info['thread_postcount'])."</h4>\n";
        echo "<span>".format_word($info['thread_postcount'], $locale['fmt_post'], array('add_count'=>0))."</span>";
        echo "</div>\n";
        if ($info['forum_type'] == '4') {
            echo "<div class='display-inline-block forum-stats p-5 m-r-5 m-b-0'>\n";
            echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($info['vote_count'])."</h4>\n";
            echo "<span>".format_word($info['vote_count'], $locale['fmt_vote'], array('add_count'=>0))."</span>";
            echo "</div>\n";
        }
        echo "</div>\n"; // end grid
        echo "<div class='forum-lastuser hidden-xs hidden-sm col-md-3'>
			".$info['thread_last']['profile_link']." ".timer($info['thread_last']['time'])."<br/>
			".trimlink(strip_tags($info['thread_last']['post_message']), 100)."
		</div>\n";
        echo "</div>\n";
        echo "</div>\n";
    }
}

/**
 * Display The Forum Thread Page
 * Template File        templates/forum_threads.html
 */
if (!function_exists('render_thread')) {
    function render_thread($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
        $html = \PHPFusion\Template::getInstance('viewthreads');
        $html->set_template(FORUM.'templates/forum_threads.html');

        $locale = fusion_get_locale();
        // Shorts in core
        $data = !empty($info['thread']) ? $info['thread'] : array();
        $pdata = !empty($info['post_items']) ? $info['post_items'] : array();
        // End inspection
        $html->set_tag('breadcrumb', render_breadcrumbs());
        // need to change to pagenav
        if (isset($info['page_nav'])) {
            $html->set_block('pagenav', ['pagenav' => $info['page_nav']]);
        }
        // Icons
        $html->set_tag('sticky_icon', ($data['thread_sticky'] == TRUE ? "<i title='".$locale['forum_0103']."' class='".get_forumIcons("sticky")."'></i>" : ''));
        $html->set_tag('locked_icon', ($data['thread_locked'] == TRUE ? "<i title='".$locale['forum_0102']."' class='".get_forumIcons("lock")."'></i>" : ''));
        // Texts and Labels
        $html->set_tag('thread_subject', $data['thread_subject']);
        $html->set_tag('time_updated', $locale['forum_0363'].' '.timer($data['thread_lastpost']));
        if (!empty($info['thread_tags_display'])) {
            $html->set_block('thread_tags', ['tags' => $info['thread_tags_display']]);
        }
        if (!empty($info['poll_form'])) {
            $html->set_block('poll_form', ['poll' => $info['poll_form']]);
        }
        // Filters
        $filter_dropdown = '';
        if (!empty($info['post-filters'])) {
            foreach ($info['post-filters'] as $i => $filters) {
                $filter_dropdown .= "<li><a href='".$filters['value']."'>".$filters['locale']."</a></li>\n";
            }
            $selector['oldest'] = $locale['forum_0180'];
            $selector['latest'] = $locale['forum_0181'];
            $selector['high'] = $locale['forum_0182'];
            $html->set_block('thread_filter', [
                'filter_label' => $locale['forum_0183'],
                'filter_word'  => (isset($_GET['sort_post']) && in_array($_GET['sort_post'], array_flip($selector)) ? $selector[$_GET['sort_post']] : $locale['forum_0180']),
                'filter_opts'  => $filter_dropdown,
            ]);
        }
        // Buttons
        if ($info['permissions']['can_create_poll']) {
            $html->set_block('poll_btn', [
                'link_title' => $info['buttons']['poll']['title'],
                'title'      => $info['buttons']['poll']['title'],
                'link_url'   => $info['buttons']['poll']['link'],
                'disabled'   => (!empty($info['thread']['thread_poll']) ? "disabled" : ""),
            ]);
        }
        if ($info['permissions']['can_start_bounty']) {
            $html->set_block('bounty_btn', [
                'link_title' => $info['buttons']['bounty']['title'],
                'title'      => $info['buttons']['bounty']['title'],
                'link_url'   => $info['buttons']['bounty']['link'],
                'disabled'   => (!empty($info['thread']['thread_bounty']) ? "disabled" : ""),
            ]);
        }
        if ($info['permissions']['can_post']) {
            $html->set_block('newthread_btn', [
                'link_title' => $info['buttons']['newthread']['title'],
                'title'      => $info['buttons']['newthread']['title'],
                'link_url'   => $info['buttons']['newthread']['link'],
                'disabled'   => (empty($info['buttons']['newthread']) ? "disabled" : ""),
            ]);
            $html->set_block('newthread_btn2', [
                'link_title' => $info['buttons']['newthread']['title'],
                'title'      => $info['buttons']['newthread']['title'],
                'link_url'   => $info['buttons']['newthread']['link'],
                'disabled'   => (empty($info['buttons']['newthread']) ? "disabled" : ""),
            ]);
            $html->set_block('reply_btn', [
                'link_title' => $info['buttons']['reply']['title'],
                'title'      => $info['buttons']['reply']['title'],
                'link_url'   => $info['buttons']['reply']['link'],
                'disabled'   => (empty($info['buttons']['reply']) ? "disabled" : ""),
            ]);
        }
        if (!empty($info['buttons']['notify'])) {
            $html->set_block('notify_btn', [
                'link_title' => $info['buttons']['notify']['title'],
                'title'      => $info['buttons']['notify']['title'],
                'link_url'   => $info['buttons']['notify']['link'],
                'disabled'   => '',
            ]);
        }
        $html->set_block('print_btn', [
            'link_title' => $info['buttons']['print']['title'],
            'title'      => $info['buttons']['print']['title'],
            'link_url'   => $info['buttons']['print']['link'],
        ]);
        if (iMOD) {
            $html->set_block('modform', ['form' => $info['mod_form']]);
        }
        if (!empty($pdata)) {
            foreach ($pdata as $post_id => $post_data) {
                // once i run this, the instance poofed because the cache...
                $post_items = render_post_item($post_data);
                //$post_items = '';
                if ($post_id == $info['post_firstpost']) {
                    $html->set_block('post_firstpost_item', ['content' => $post_items]);
                    if ($info['permissions']['can_post']) {
                        $html->set_block('thread_info', [
                            'thread_post' => $info['thread_posts'],
                            'disabled'    => (empty($info['buttons']['reply']) ? 'disabled' : ''),
                            'link_url'    => $info['buttons']['reply']['link'],
                            'link_title'  => $info['buttons']['reply']['title'],
                            'title'       => $info['buttons']['reply']['title'],
                        ]);
                        if ($info['thread_bounty']) {
                            $html->set_block('thread_bounty_info', [
                                'message' => $info['thread_bounty']
                            ]);
                        }
                    }
                } else {
                    $html->set_block('post_item', ['content' => $post_items]);
                }
            }
        }

        $html->set_tag('quick_reply_form', (!empty($info['quick_reply_form']) ? "<hr/>\n".$info['quick_reply_form'] : ''));
        $html->set_tag('info_access', (sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"));
        $html->set_tag('info_post', (sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"));
        $html->set_tag('info_reply', (sprintf($locale['forum_perm_reply'], $info['permissions']['can_reply'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"));
        $html->set_tag('info_edit_poll', ($data['thread_poll'] ? (sprintf($locale['forum_perm_edit_poll'], $info['permissions']['can_edit_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n") : ''));
        $html->set_tag('info_vote_poll', ($data['thread_poll'] ? (sprintf($locale['forum_perm_vote_poll'], $info['permissions']['can_vote_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"))."<br/>\n" : ''));
        $html->set_tag('info_create_poll', (!$data['thread_poll'] ? (sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n") : ''));
        $html->set_tag('info_upload', (sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"));
        $html->set_tag('info_download', (sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"));
        $html->set_tag('info_rate', ($data['forum_type'] == 4 ? (sprintf($locale['forum_perm_rate'], $info['permissions']['can_rate'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n") : ''));
        $html->set_tag('info_bounty', ($data['forum_type'] == 4 ? (sprintf($locale['forum_perm_bounty'], $info['permissions']['can_start_bounty'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n") : ''));
        $html->set_tag('info_moderators', ($info['forum_moderators'] ? "<div class='list-group-item m-b-20'>".$locale['forum_0185']." ".$info['forum_moderators']."</div>" : ''));

        if (!empty($info['thread_users'])) {
            $i = 1;
            $max = count($info['thread_users']);
            $participated_users = '';
            foreach ($info['thread_users'] as $user_id => $user) {
                $participated_users .= profile_link($user['user_id'], $user['user_name'], $user['user_status']);
                $participated_users .= $max == $i ? " " : ", ";
                $i++;
            }
            $html->set_block('participated_users', [
                'title'              => $locale['forum_0581'],
                'user_profile_links' => $participated_users
            ]);
        }

        echo $html->get_output();
    }
}

/* Post Item */
if (!function_exists('render_post_item')) {
    function render_post_item($data) {
        $html = \PHPFusion\Template::getInstance('forum_post');
        $html->set_template(FORUM.'templates/forum_post_item.html');
        $forum_settings = \PHPFusion\Forums\ForumServer::get_forum_settings();
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $html->set_tag('post_html_comment', "<!--forum_thread_prepost_".$data['post_id']."-->");
        $html->set_tag('post_date', $data['post_shortdate']);
        $html->set_tag('item_marker_id', $data['marker']['id']);
        $html->set_tag('user_avatar', $data['user_avatar_image']);
        $html->set_tag('user_avatar_rank', ($forum_settings['forum_rank_style'] == '1' ? "<div class='m-t-10'>".$data['user_rank']."</div>" : ''));
        $html->set_tag('user_rank', ($forum_settings['forum_rank_style'] == '0' ? "<span class='forum_rank'>".$data['user_rank']."</span>" : ''));
        $html->set_tag('user_profile_link', $data['user_profile_link']);
        $html->set_tag('user_online_status', ($data['user_online'] ? "fa fa-circle" : "fa fa-circle-thin"));
        $html->set_tag('user_signature', ($data['user_sig'] ? "<div class='forum_sig text-smaller'>".$data['user_sig']."</div>" : ""));
        $html->set_tag('checkbox_input', (iMOD ? $data['post_checkbox'] : ''));
        $html->set_tag('post_message', $data['post_message']);
        $html->set_tag('post_edit_reason', $data['post_edit_reason']);
        $html->set_tag('post_reply_message', $data['post_reply_message']);
        $html->set_tag('post_mood_message', $data['post_mood_message']);
        if ($data['post_bounty']) {
            $html->set_block('bounty_btn', [
                'link_url'   => $data['post_bounty']['link'],
                'link_title' => $data['post_bounty']['title'],
                'title'      => $data['post_bounty']['title'],
            ]);
        }
        if ($data['post_mood']) {
            $html->set_block('mood_messages', ['btns' => $data['post_mood']]);
        }
        if ($data['post_attachments']) {
            $html->set_block('post_attachments', ['attach' => $data['post_attachments']]);
        }
        if ((isset($data['post_quote']) && !empty($data['post_quote']))) {
            $html->set_block('quote_btn', [
                'link_url'   => $data['post_quote']['link'],
                'link_title' => $data['post_quote']['title'],
                'title'      => $data['post_quote']['title'],
            ]);
        }
        if ((isset($data['post_reply']) && !empty($data['post_reply']))) {
            $html->set_block('reply_btn', [
                'link_url'   => $data['post_reply']['link'],
                'link_title' => $data['post_reply']['title'],
                'title'      => $data['post_reply']['title'],
            ]);
        }
        if ((isset($data['post_edit']) && !empty($data['post_edit']))) {
            $html->set_block('edit_btn', [
                'link_url'   => $data['post_edit']['link'],
                'link_title' => $data['post_edit']['title'],
                'title'      => $data['post_edit']['title'],
            ]);
        }
        // Dropdowns
        $html->set_block('li_post_count', ['title' => $data['user_post_count']]);
        $html->set_block('li_print_post', [
            'link_url'   => $data['print']['link'],
            'link_title' => $data['print']['title'],
            'title'      => $data['print']['title']
        ]);
        if ($data['user_ip']) {
            $html->set_block('li_ip', ['title' => $data['user_ip']]);
        }
        if ($data['user_message']['link']) {
            $html->set_block('li_um', [
                'link_url'   => $data['user_message']['link'],
                'link_title' => $data['user_message']['title'],
                'title'      => $data['user_message']['title']
            ]);
        }
        if (isset($data['post_quote']) && !empty($data['post_quote'])) {
            $html->set_block('li_quote', [
                'link_url'   => $data['post_quote']['link'],
                'link_title' => $data['post_quote']['title'],
                'title'      => $data['post_quote']['title']
            ]);
        }
        if (!empty($data['post_edit'])) {
            $html->set_block('li_edit', [
                'link_url'   => $data['post_edit']['link'],
                'link_title' => $data['post_edit']['title'],
                'title'      => $data['post_edit']['title'],
            ]);
        }
        if ($data['user_web']['link']) {
            $html->set_block('li_web', [
                'link_url'    => $data['user_message']['link'],
                'link_title'  => $data['user_message']['title'],
                'title'       => $data['user_message']['title'],
                'noindex_a'   => (fusion_get_settings('index_url_userweb') ? "" : "<!--noindex-->"),
                'noindex_b'   => (fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->"),
                'noindex_rel' => (fusion_get_settings('index_url_userweb') ? "" : " rel='nofollow'")
            ]);
        }
        if ($data['user_level'] > USER_LEVEL_SUPER_ADMIN) {
            if (iSUPERADMIN || (iADMIN && checkrights('M'))) {
                $html->set_block('li_admin_title', [
                    'title' => $locale['forum_0662']
                ]);
                $html->set_block('li_edit_user', [
                    'link_url'   => ADMIN."members.php".$aidlink."&amp;step=edit&amp;user_id=".$data['user_id'],
                    'link_title' => $locale['forum_0663'],
                    'title'      => $locale['forum_0663'],
                ]);
                $html->set_block('li_ban_user', [
                    'link_url'   => ADMIN."members.php".$aidlink."&amp;user_id=".$data['user_id']."&amp;action=1",
                    'link_title' => $locale['forum_0664'],
                    'title'      => $locale['forum_0664'],
                ]);
                $html->set_block('li_delete_user', [
                    'link_url'   => ADMIN."members.php".$aidlink."&amp;step=delete&amp;status=0&amp;user_id=".$data['user_id'],
                    'link_title' => $locale['forum_0665'],
                    'title'      => $locale['forum_0665'],
                ]);
            }
        }
        if ($data['post_votebox']) {
            $html->set_block('votebox', [
                'input'  => $data['post_votebox'],
                'status' => $data['post_answer_check']
            ]);
        }
        //$callback = implode('', array_map(function($array) { return "<li><div class='col-xs-12 col-sm-3 strong'>".$array['title']."</div><div class='col-xs-12 col-sm-9'>".$array['value']."</div></li>";}, $data['user_profiles']));        if (!empty($callback)) {
        $user_profiles = "";
        if (!empty($data['user_profiles'])) {
            $temp_name = '';
            $user_profiles .= "<ul class='post_profiles'>\n";
            // must nest for easier implementation?
            $i = 0;
            foreach ($data['user_profiles'] as $attr) {
                $open_user_profiles = '';
                if ($temp_name !== $attr['field_cat_name']) {
                    $open_user_profiles .= $i ? "</ul><ul class='post_profiles'>\n" : "";
                    $open_user_profiles .= "<li class='title'>".\PHPFusion\QuantumFields::parse_label($attr['field_cat_name'])."</li>\n";
                }
                $user_profiles .= $open_user_profiles;
                $user_profiles .= "<li class='row'>\n";
                $user_profiles .= "<div class='col-xs-12 col-sm-4 strong'>\n".$attr['title'].":\n</div>\n";
                $user_profiles .= "<div class='col-xs-12 col-sm-8'>\n".strip_tags($attr['value'])."\n</div>\n";
                $user_profiles .= "</li>\n";
                $temp_name = $attr['field_cat_name'];
            }
            $user_profiles .= "</ul>\n";
            $html->set_block('user_profiles', ['profiles' => $user_profiles]);
        }

        return $html->get_output();
    }
}