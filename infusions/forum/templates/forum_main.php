<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/templates/forum_main.php
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
 * Forum Page
 */
if (!function_exists('render_forum_main')) {
    /**
     * Main Forum Page - Recursive
     *
     * @param array $info
     * @param int   $id - counter nth
     */
    function render_forum_main(array $info, $id = 0) {
        require_once FORUM_CLASS."autoloader.php";
        $locale = fusion_get_locale();
        ?>
        <div class='spacer-sm'>
            <?php echo render_breadcrumbs() ?>
        </div>
        <div class='forum-header' style="background: url(<?php echo FORUM.'images/default_forum_bg.jpg' ?>) no-repeat; background-size:cover;">
            <div class='banner' style='display:block; height:180px; overflow:hidden;'>
                <h2 class='p-20 center-y text-white' style='z-index: 2'><?php echo $locale['forum_0013'] ?></h2>
            </div>
        </div>
        <div class='spacer-sm'>
            <div class='row'>
                <div class='col-xs-12 col-sm-9 col-lg-9'>
                    <table class='table table-responsive clear'>
                        <?php
                        if (!empty($info['forums'][$id])) {
                            $forums = $info['forums'][$id];
                            $x = 1;
                            foreach ($forums as $forum_id => $data) {
                                if ($data['forum_type']) {
                                    ?>
                                    <tr>
                                        <td style='padding-top:20px;'>
                                            <small class='text-uppercase'>
                                                <strong>
                                                    <?php echo $data['forum_link']['title'] ?>
                                                </strong>
                                            </small>
                                        </td>
                                        <td style='padding-top:20px;'>
                                            <small class='text-uppercase strong text-lighter'><?php echo $locale['forum_0002'] ?></small>
                                        </td>
                                        <td style='padding-top:20px;'>
                                            <small class='text-uppercase strong text-lighter'><?php echo $locale['forum_0003'] ?></small>
                                        </td>
                                        <td class='col-xs-4' style='padding-top:20px;'>
                                            <small class='text-uppercase strong text-lighter'><?php echo $locale['forum_0012'] ?></small>
                                        </td>
                                    </tr>
                                    <?php
                                    if (isset($info['forums'][0][$forum_id]['child'])) {
                                        echo "<!---subforums-->";
                                        $i = 1;
                                        $sub_forums = $info['forums'][0][$forum_id]['child'];
                                        foreach ($sub_forums as $sub_forum_id => $cdata) {
                                            render_forum_item($cdata, $i);
                                            $i++;
                                        }
                                    } else {
                                        echo "<div class='well'>\n";
                                        echo $locale['forum_0327'];
                                        echo "</div>\n";
                                    }
                                    ?>
                                    <?php
                                } else {
                                    echo "<div class='well text-center'>".$locale['forum_0328']."</div>\n";
                                }
                                /*
                                 * We no longer do this. Optimization deprecate
                                 */
                                /*echo "<div class='well'>";
                                render_forum_item($data, $x);
                                echo "</div>\n";
                                $x++;
                            }*/
                            }
                        } else {
                            echo "<div class='well text-center'>".$locale['forum_0328']."</div>\n";
                        }
                        ?>
                    </table>
                </div>
                <div class='col-xs-12 col-sm-3 col-lg-3'>
                    <?php //print_p($info, 1) ?>
                    <div class='spacer-sm m-b-50'>
                        <a class='btn btn-primary btn-block' href='<?php echo $info['new_topic_link']['link'] ?>'><i class='fa fa-comment m-r-10'></i><?php echo $info['new_topic_link']['title'] ?></a>
                    </div>
                    <?php
                    $threadTags = \PHPFusion\Forums\ForumServer::tag(TRUE, FALSE)->get_TagInfo();
                    if (!empty($threadTags['tags'])) : ?>
                        <!--Forum Tags-->
                        <h4 class='spacer-sm'><strong>Filter by Tags</strong></h4>
                        <ul class="list-group spacer-md">
                            <?php foreach ($threadTags['tags'] as $tag_id => $tag_data) : ?>
                                <li class='list-group-item<?php echo($tag_data['tag_active'] == TRUE ? ' active' : '') ?>'>
                                    <a href="<?php echo $tag_data['tag_link'] ?>">
                                        <div class="pull-left m-r-10"><i class="fa fa-square fa-lg" style="color:<?php echo $tag_data['tag_color'] ?>"></i></div>
                                        <div class="pull-left">
                                            <?php echo $tag_data['tag_title'] ?>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <!--//Forum Tags-->

                        <?php
                    endif;
                    // Run custom query
                    $custom_result = dbquery("SELECT thread_id, thread_subject, thread_author, thread_postcount FROM ".DB_FORUM_THREADS."
                        INNER JOIN ".DB_FORUMS.(multilang_column('FO') ? " WHERE forum_language='".LANGUAGE."' AND " : " WHERE ").groupaccess('forum_access')." and (thread_lastpost >=:one_week and thread_lastpost < :current) and thread_locked=:not_locked and thread_hidden=:not_hidden
                        GROUP BY thread_id ORDER BY thread_postcount DESC LIMIT 10",
                        [
                            ':one_week'   => TIME - (7 * 24 * 3600),
                            ':current'    => TIME,
                            ':not_locked' => 0,
                            ':not_hidden' => 0,
                        ]);
                    if (dbrows($custom_result)) : ?>
                        <h4 class='spacer-sm'><strong>Popular Threads This Week</strong></h4>
                        <div class='spacer-md'>
                            <?php while ($popular = dbarray($custom_result)) :
                                $user = fusion_get_user($popular['thread_author']);
                                ?>
                                <div>
                                    <a href='<?php echo FORUM."viewthread.php?thread_id=".$popular['thread_id'] ?>'><strong><?php echo $popular['thread_subject'] ?></strong></a><br/>
                                    <?php echo $locale['by'] ?> <?php echo profile_link($user['user_id'], $user['user_name'], $user['user_status']) ?>
                                    <span class='text-lighter'><i class='fa fa-comment'></i> <?php echo format_word($popular['thread_postcount'], $locale['fmt_post']) ?></span>
                                </div>
                                <hr/>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        //print_p($info, true);
    }
}

/**
 * Forum Item
 */
if (!function_exists('render_forum_item')) {
    /**
     * Switch between different types of forum list containers
     *
     * @param $data
     * @param $i
     */
    function render_forum_item($data, $i) {
        $locale = fusion_get_locale();
        /*if ($i > 0) {
            echo "<div id='forum_".$data['forum_id']."' class='forum-container'>\n";
        } else {
            echo "<div id='forum_".$data['forum_id']."' class='panel panel-default'>\n";
            echo "<div class='panel-body'>\n";
        }*/
        ?>
        <tr>
            <td style='border-radius: 4px 0 0 4px; background: #f7f7f7; border-top:4px solid #fff; border-bottom:4px solid #fff;'>
                <a title='<?php echo $data['forum_link']['title'] ?>' class='forum-subject' href='<?php echo $data['forum_link']['link'] ?>'>
                    <strong>
                        <?php echo $data['forum_link']['title'] ?>
                    </strong>
                </a>
                <?php if ($data['forum_description']) : echo "<div class='forum-description'>".$data['forum_description']."</div>\n"; endif; ?>
                <?php if ($data['forum_moderators']) : echo "<span class='forum-moderators'><small><strong>".$locale['forum_0007']."</strong>".$data['forum_moderators']."</small></span>\n"; endif; ?>
            </td>
            <td style='background: #f7f7f7;  border-top:4px solid transparent; border-bottom:4px solid #fff;'>
                <?php echo $data['forum_threadcount_word'] ?>
            </td>
            <td style='background: #f7f7f7; border-radius: 0px 4px 4px 0; border-top:4px solid #fff; border-bottom:4px solid #fff; border-right: 4px solid #fff;'>
                <?php echo $data['forum_postcount_word'] ?>
            </td>
            <td style='background: #f7f7f7; border-radius: 4px; border-top:4px solid #fff; border-left:8px solid #fff; border-bottom:4px solid #fff;'>
                <?php
                if ($data['thread_lastpost'] == 0) {
                    echo $locale['forum_0005'];
                } else {
                    echo "<div class='clearfix'>\n";
                    if (!empty($data['last_post']['avatar'])) {
                        echo "<div class='pull-left lastpost-avatar m-r-10'>".$data['last_post']['avatar']."</div>";
                    }
                    echo "<div class='overflow-hide'>\n";
                    echo "<span class='forum_thread_link'><a style='font-weight: 400; color: #333; text-decoration:underline; font-size:85%;' href='".$data['last_post']['post_link']."'>".trim_text($data['thread_subject'], 35)."</a></span><br/>";
                    echo "<span class='forum_profile_link'>".$data['last_post']['profile_link']." - ".$data['last_post']['time']."</span>\n";
                    echo "</div>\n</div>\n";
                }
                ?>
            </td>
        </tr>

        <?php
        /*
        if ($data['forum_image'] && file_exists(FORUM."images/".$data['forum_image'])) {
            echo thumbnail(FORUM."images/".$data['forum_image'], '50px');
        } else {
            echo "<div class='forum-icon'>".$data['forum_icon_lg']."</div>\n";
        }*/
        /*
        switch ($data['forum_type']) {
            case '3':
                echo "<div class='col-xs-12 col-sm-12'>\n";
                echo "<a class='display-inline-block forum-link' href='".$data['forum_link']['link']."'>".$data['forum_link']['title']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
                if (isset($data['child'])) {
                    echo "<div class='clearfix sub-forum'>\n";
                    foreach ($data['child'] as $cdata) {
                        echo "<i class='entypo level-down'></i>\n";
                        echo "<span class='nowrap'>\n";
                        if (isset($cdata['forum_type'])) {
                            echo $data['forum_icon'];
                        }
                        echo "<a href='".INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$cdata['forum_id']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a></span>";
                        echo "<br/>\n";
                    }
                    echo "</div>\n";
                }
                echo "</div>\n";
                break;
            default:
                echo "<div class='col-xs-12 col-sm-6'>\n";
                echo "
				<a class='display-inline-block forum-link' href='".$data['forum_link']['link']."'>".$data['forum_link']['title']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
                if (isset($data['child'])) {
                    echo "<div class='clearfix sub-forum'>\n";
                    echo "<div class='pull-left'>\n";
                    echo "<i class='entypo level-down'></i>\n";
                    echo "</div>\n";
                    echo "<div class='overflow-hide'>\n";
                    foreach ($data['child'] as $cdata) {
                        if (isset($cdata['forum_type'])) {
                            echo $data['forum_icon'];
                        }
                        echo "<a href='".INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$cdata['forum_id']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a><br/>";
                    }
                    echo "</div>\n";
                    echo "</div>\n";
                }
                echo "</div>\n";
        }
        */
    }
}

/**
 * For $_GET['viewforum'] view present.
 */
if (!function_exists('forum_viewforum')) {
    function forum_viewforum($info) {
        $locale = fusion_get_locale();
        //print_P($info);
        ?>
        <div class='spacer-sm'>
            <?php echo render_breadcrumbs() ?>
        </div>
        <div class='forum-header' style="background: url(<?php echo FORUM.'images/default_forum_bg.jpg' ?>) no-repeat; background-size:cover;">
            <div class='banner' style='display:block; height:180px; overflow:hidden;'>
                <div class='center-y p-20'>
                    <!--- add forum image here --->
                    <h2 class='text-white'><?php echo $info['forum_name'] ?></h2>
                    <div class='forum-description text-white'><?php echo $info['forum_description'] ?></div>
                </div>
            </div>
        </div>
        <div class='navbar navbar-inverse' style='margin-top:-15px;'>
            <ul class='nav navbar-nav'>
                <?php
                // Group the link together under 'forum_page_link'
                $i = 0;
                foreach ($info['forum_page_link'] as $view_keys => $page_link) {
                    $a = (!isset($_GET['view']) && (!$i)) || (isset($_GET['view']) && $_GET['view'] === $view_keys) ? TRUE : FALSE;
                    $active = $a ? " class='active'" : "";
                    echo "<li$active><a href='".$page_link['link']."'>".$page_link['title']."</a></li>\n";
                    $i++;
                }
                ?>
            </ul>
        </div>
        <?php if ($info['forum_rules']) : alert("<span class='strong'><i class='fa fa-exclamation fa-fw'></i>".$locale['forum_0350']."</span> ".$info['forum_rules']); endif; ?>
        <div class='spacer-md'>
            <div class='row'>
                <div class='col-xs-12 col-sm-6 col-md-5 col-lg-2'>
                    <?php if (iMEMBER && $info['permissions']['can_post'] && !empty($info['new_thread_link'])) : ?>
                        <a class='btn btn-primary' href='<?php echo $info['new_thread_link']['link'] ?>'><i class='m-r-10 fa fa-comment'></i><?php echo $info['new_thread_link']['title'] ?></a>
                    <?php endif; ?>
                </div>
                <div class='col-xs-12 col-sm-6 col-md-7 col-lg-10'>
                    <?php
                    if (isset($_GET['view'])) {
                        switch ($_GET['view']) {
                            default:
                            case 'threads':
                                die('Unauthorized mode');
                                if ($info['forum_type'] > 1) {
                                    echo "<!--pre_forum-->\n";
                                    // Threads Render
                                    render_forum_threads($info);
                                }
                                break;
                            case 'subforums':
                                if (!empty($info['item'][$_GET['forum_id']]['child'])) {
                                    $i = 1;
                                    ?>
                                    <table class='table table-responsive clear'>
                                        <tr>
                                            <td style='padding-top:20px;'>
                                                <small class='text-uppercase strong text-lighter'><?php echo $locale['forum_0351'] ?></small>
                                            </td>
                                            <td style='padding-top:20px;'>
                                                <small class='text-uppercase strong text-lighter'><?php echo $locale['forum_0002'] ?></small>
                                            </td>
                                            <td style='padding-top:20px;'>
                                                <small class='text-uppercase strong text-lighter'><?php echo $locale['forum_0003'] ?></small>
                                            </td>
                                            <td class='col-xs-4' style='padding-top:20px;'>
                                                <small class='text-uppercase strong text-lighter'><?php echo $locale['forum_0012'] ?></small>
                                            </td>
                                        </tr>
                                        <?php
                                        foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
                                            render_forum_item($subforum_data, $i);
                                            $i++;
                                        }
                                        ?>
                                    </table>
                                    <?php
                                }
                                break;
                            case 'people':
                                render_forum_users($info);
                                break;
                            case 'activity':
                                render_forum_activity($info);
                                break;
                        }
                    } else {
                        render_forum_threads($info);
                    }
                    ?>
                    <div class='list-group-item m-t-20'>
                        <?php echo "
                        <span>".sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                        <span>".sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                        <span>".sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                        <span>".sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                        <span>".sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                        "; ?>
                    </div>
                    <?php
                    // Add people section
                    if ($info['forum_moderators']) : echo "<div class='list-group-item'>".$locale['forum_0185']." ".$info['forum_moderators']."</div>\n"; endif;
                    ?>
                </div>
            </div>
        </div>
        <?php

    }
}

if (!function_exists('render_forum_users')) {
    function render_forum_users($info) {
        $locale = fusion_get_locale();
        ?>
        <div class='list-group-item'>
            <?php
            if (!empty($info['pagenav'])) {
                ?>
                <div class='text-right'><?php echo $info['pagenav'] ?></div>
                <hr/>
                <?php
            }
            ?>
            <table class='table table-responsive table-striped clear'>
                <thead>
                <tr>
                    <th class='col-xs-2'>
                        <small><strong><?php echo $locale['forum_0018'] ?></strong></small>
                    </th>
                    <th>
                        <small><strong><?php echo $locale['forum_0012'] ?></strong></small>
                    </th>
                    <th>
                        <small><strong><?php echo $locale['forum_0016'] ?></strong></small>
                    </th>
                </tr>
                </thead>
                <?php
                if (!empty($info['item'])) {
                    foreach ($info['item'] as $user) {
                        ?>
                        <tr>
                            <td class='no-break'>
                                <div class='clearfix'>
                                    <div class='pull-left m-r-10'><?php echo display_avatar($user, '30px', '', '', '') ?></div>
                                    <?php echo profile_link($user['user_id'], $user['user_name'], $user['user_status']) ?>
                                </div>
                            </td>
                            <td>
                                <span class='text-smaller'><a href='<?php echo $user['thread_link']['link'] ?>'><?php echo $user['thread_link']['title'] ?></a></span>
                            </td>
                            <td class='no-break'>
                                <span class='text-smaller'><?php echo showdate('forumdate', $user['post_datestamp']).", ".timer($user['post_datestamp']) ?></span>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>

        <?php
    }
}

/**
 * Shows Forum Activity
 */
if (!function_exists('render_forum_activity')) {
    function render_forum_activity($info) {
        $locale = fusion_get_locale();
        if (!empty($info['item'])) {
            $i = 0;
            ?>
            <div class='clearfix'>
                <?php if ($info['pagenav']) : ?>
                    <div class='pull-right m-t-5'><?php echo $info['pagenav'] ?></div><?php endif; ?>
                <div class='pull-left'>
                    <div class='list-group-item'>
                        <strong><?php echo format_word($info['max_post_count'], $locale['fmt_post']) ?> |
                            <a href='<?php echo $info['last_activity']['link'] ?>'><?php echo $locale['forum_0020'] ?></a>
                            <?php
                            echo sprintf($locale['forum_0021'],
                                showdate('forumdate', $info['last_activity']['time']),
                                profile_link($info['last_activity']['user']['user_id'], $info['last_activity']['user']['user_name'], $info['last_activity']['user']['user_status'])
                            )
                            ?>
                        </strong>
                    </div>
                </div>
            </div>
            <hr/>
            <?php
            foreach ($info['item'] as $post_id => $postData) {
                ?>
                <div class='well spacer-md<?php echo !$i ? " m-t-0" : "" ?> '>
                    <div class='pull-left m-r-15'>
                        <?php echo display_avatar($postData['post_author'], '50px', FALSE, '', '') ?>
                    </div>
                    <div class='overflow-hide'>
                        <div class='m-b-10'>
                            <?php echo profile_link($postData['post_author']['user_id'], $postData['post_author']['user_name'], $postData['post_author']['user_status']) ?>
                            <span class='text-smaller strong'>
                                <?php echo showdate('forumdate', $postData['post_datestamp']) ?>, <?php echo timer($postData['post_datestamp']) ?> <?php echo $locale['forum_0022'] ?> <a
                                        href='<?php echo $postData['thread_link']['link'] ?>'><?php echo $postData['thread_link']['title'] ?></a>
                            </span>
                        </div>
                        <div class='list-group-item'>
                            <div class='m-b-10 text-smaller text-lighter'><strong><?php echo $locale['forum_0023']." ".$postData['thread_link']['title'] ?></strong></div>
                            <?php echo parse_textarea($postData['post_message'], TRUE, TRUE, TRUE, IMAGES, TRUE) ?>
                        </div>
                        <div class='list-group-item'>
                            <a class='text-smaller strong' href='<?php echo $postData['thread_link']['link'] ?>'><?php echo $locale['forum_0024'] ?><i class='fa fa-external-link-square m-l-5'></i></a>
                        </div>
                    </div>
                </div>
                <?php
                $i++;
            }
        } else {
            ?>
            <div class='well text-center'>There are no activity in this forum.</div>
            <?php
        }
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
    }

}

if (!function_exists('render_forum_threads')) {
    function render_forum_threads($info) {
        $locale = fusion_get_locale();
        ?>
        <div class='list-group-item p-t-15 p-b-15'>
            <?php
            if (!empty($info['filters']['type'])) {
                foreach ($info['filters']['type'] as $key => $tabs) {
                    ?>
                    <a href='<?php echo $tabs['link'] ?>' class='m-r-10<?php echo $tabs['active'] ? " text-active" : "" ?> strong'><?php echo $tabs['icon'].$tabs['title'] ?> (<?php echo $tabs['count'] ?>)</a>
                    <?php
                }
            }
            ?>
            <hr/>
            <div class='clearfix'>
                <div class='pull-left'>
                    <?php forum_filter($info); ?>
                </div>
                <div class='pull-right'>
                    <?php
                    if (!empty($info['threads']['pagenav'])) {
                        echo "<div class='text-right'>\n";
                        echo $info['threads']['pagenav'];
                        echo "</div>\n";
                    }
                    ?>
                </div>
            </div>
            <hr/>
            <!---forumthreads-->
            <table class='table table-striped table-responsive clear'>
                <thead>
                <tr>
                    <th>
                        <small><strong><?php echo $locale['forum_0228'] ?></strong></small>
                    </th>
                    <th>
                        <small><strong><?php echo $locale['forum_0052'] ?></strong></small>
                    </th>
                    <th class='no-break'>
                        <small><strong><?php echo $locale['forum_0020'] ?></strong></small>
                    </th>
                    <th>
                        <small><strong><?php echo $locale['forum_0053'] ?></strong></small>
                    </th>
                    <th>
                        <small><i class='fa fa-comment'></i></small>
                    </th>
                    <th>
                        <small><i class='fa fa-thumbs-o-up'></i></small>
                    </th>
                    <th></th>
                </tr>
                <tbody class='text-smaller'>
                <?php
                if (!empty($info['threads'])) {
                    if (!empty($info['threads']['sticky'])) {
                        foreach ($info['threads']['sticky'] as $cdata) {
                            render_thread_item($cdata);
                        }
                    }
                    if (!empty($info['threads']['item'])) {
                        foreach ($info['threads']['item'] as $cdata) {
                            render_thread_item($cdata);
                        }
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>".$locale['forum_0269']."</td></tr>\n";
                }
                ?>
                </tbody>
                </thead>
            </table>
        </div>
        <?php
        if (!empty($info['threads']['pagenav2'])) {
            echo "<div class='hidden-sm hidden-md hidden-lg m-t-15'>\n";
            echo $info['threads']['pagenav2'];
            echo "</div>\n";
        }

    }
}

if (!function_exists('render_thread_item')) {
    function render_thread_item($info) {
        $locale = fusion_get_locale();
        ?>
        <tr id='thread_<?php echo $info['thread_id'] ?>'>
            <td>
                <div class='clearfix'>
                    <div class='pull-left m-r-10'><?php echo $info['thread_last']['avatar'] ?></div>
                    <div class='overflow-hide'>
                        <a class='forum-link' href='<?php echo $info['thread_link']['link'] ?>'><?php echo $info['thread_link']['title'] ?></a>
                        <span class='m-l-10 m-r-10 text-lighter'><?php echo implode('', $info['thread_icons']) ?></span>
                        <?php echo $info['thread_pages']; ?>
                    </div>
                </div>
            </td>
            <td><?php echo $info['thread_starter']['profile_link'] ?></td>
            <td>
                <small><?php echo timer($info['thread_last']['time']) ?></small>
            </td>
            <td><strong><?php echo number_format($info['thread_views']) ?></strong></td>
            <td><strong><?php echo number_format($info['thread_postcount']) ?></strong></td>
            <td><strong><?php echo number_format($info['vote_count']) ?></strong></td>
            <td>
                <?php if (isset($info['track_button'])) : ?>
                    <a class='btn btn-danger btn-sm' onclick="return confirm('<?php echo $locale['global_060'] ?>');" href='<?php echo $info['track_button']['link'] ?>'><?php echo $info['track_button']['title'] ?></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
}

if (!function_exists("render_section")) {
    function render_section($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        ?>
        <div class='list-group-item'>
            <div class='clearfix' style='height:60px;'>
                <div class='pull-left'><?php echo $info['threads_time_filter']; ?></div>
                <?php if (!empty($info['threads']['pagenav'])) : ?>
                    <div class='pull-right center-y'><?php echo $info['threads']['pagenav'] ?></div> <?php endif; ?>
            </div>
            <hr/>
            <table class='table table-responsive clear'>
                <thead>
                <tr>
                    <th>
                        <small><strong><?php echo $locale['forum_0228'] ?></strong></small>
                    </th>
                    <th>
                        <small><strong><?php echo $locale['forum_0052'] ?></strong></small>
                    </th>
                    <th class='no-break'>
                        <small><strong><?php echo $locale['forum_0020'] ?></strong></small>
                    </th>
                    <th>
                        <small><strong><?php echo $locale['forum_0053'] ?></strong></small>
                    </th>
                    <th>
                        <small><i class='fa fa-comment'></i></small>
                    </th>
                    <th>
                        <small><i class='fa fa-thumbs-o-up'></i></small>
                    </th>
                    <th></th>
                </tr>
                <tbody class='text-smaller'>
                <?php
                if (!empty($info['threads'])) {
                    if (!empty($info['threads']['sticky'])) {
                        foreach ($info['threads']['sticky'] as $cdata) {
                            render_thread_item($cdata);
                        }
                    }
                    if (!empty($info['threads']['item'])) {
                        foreach ($info['threads']['item'] as $cdata) {
                            render_thread_item($cdata);
                        }
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>".$locale['forum_0269']."</td></tr>\n";
                }
                ?>
            </table>
        </div>
        <?php
    }
}

/* Custom Modal New Topic */
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
                    $options[$data['forum_id']] = str_repeat("&#8212;",
                            $depth).$data['forum_name']." ".($data['forum_cat_name'] ? "(".$data['forum_cat_name'].")" : '');
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

if (!function_exists('render_postify')) {
    function render_postify($info) {
        opentable($info['title']);
        echo "<div class='".($info['error'] ? "alert alert-danger" : "well")." text-center'>\n";
        echo(!empty($info['description']) ? $info['description']."<br/>\n" : "");
        foreach ($info['link'] as $link) {
            echo "<p><a href='".$link['url']."'>".$link['title']."</a></p>\n";
        }
        echo "</div>\n";
        closetable();
    }
}
