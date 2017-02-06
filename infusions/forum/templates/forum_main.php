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
            if (isset($_GET['section']) && $_GET['section'] == 'participated') {
                render_participated($info);
            } elseif (isset($_GET['section']) && $_GET['section'] == 'latest') {
                render_laft($info);
            } elseif (isset($_GET['section']) && $_GET['section'] == 'tracked') {
                render_tracked($info);
            } elseif (isset($_GET['section']) && $_GET['section'] == 'unanswered') {
                render_unanswered($info);
            } elseif (isset($_GET['section']) && $_GET['section'] == 'unsolved') {
                render_unsolved($info);
            } elseif (!isset($_GET['section']) or isset($_GET['section']) && $_GET['section'] == 'thread') {
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
     * @param array $info
     * @param int   $id - counter nth
     */
    function render_forum_main(array $info, $id = 0) {
        require_once FORUM_CLASS."autoloader.php";
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        echo "<div class='forum-title'>".$locale['forum_0013']."</div>\n";
        $threadTags = \PHPFusion\Forums\ForumServer::tag(TRUE, FALSE)->get_TagInfo();
        if (!empty($threadTags['tags'])) : ?>
            <!--Forum Tags-->
            <ul class="list-group-item clearfix m-b-10 m-t-10">
                <?php foreach ($threadTags['tags'] as $tag_id => $tag_data) : ?>
                    <li class='pull-left display-inline-block m-r-10 <?php echo($tag_data['tag_active'] == TRUE ? 'active' : '') ?>'>
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
        <?php endif;
        if (!empty($info['forums'][$id])) {
            $forums = $info['forums'][$id];
            $x = 1;
            foreach ($forums as $forum_id => $data) {
                if ($data['forum_type'] == '1') {
                    echo "<div class='panel panel-default'>\n";
                    echo "<div class='panel-heading' ".(isset($data['child']) ? 'style="border-bottom:0;"' : '').">\n";
                    echo "<a title='".$data['forum_link']['title']."' class='forum-subject' href='".$data['forum_link']['link']."'>
                    ".$data['forum_link']['title']."</a><br/>";
                    echo $data['forum_description'] ? "<span class='text-smaller'>".$data['forum_description']."</span>\n<br/>" : '';
                    echo "</div>\n";
                    if (isset($info['forums'][0][$forum_id]['child'])) {
                        echo "<!---subforums-->";
                        echo "<div class='m-10'>\n";
                        $i = 1;
                        $sub_forums = $info['forums'][0][$forum_id]['child'];
                        foreach ($sub_forums as $sub_forum_id => $cdata) {
                            render_forum_item($cdata, $i);
                            $i++;
                        }
                        echo "</div>\n";
                        echo "<!---subforums-->";
                    } else {
                        echo "<div class='panel-body text-center'>\n";
                        echo $locale['forum_0327'];
                        echo "</div>\n";
                    }
                    echo "</div>\n"; // end panel-default
                } else {
                    echo "<div class='list-group-item m-b-20'>";
                    render_forum_item($data, $x);
                    echo "</div>\n";
                    $x++;
                }
            }
        } else {
            echo "<div class='well text-center'>".$locale['forum_0328']."</div>\n";
        }
    }
}

/**
 * Forum Item
 */
if (!function_exists('render_forum_item')) {
    /**
     * Switch between different types of forum list containers
     * @param $data
     * @param $i
     */
    function render_forum_item($data, $i) {
        $locale = fusion_get_locale();
        if ($i > 0) {
            echo "<div id='forum_".$data['forum_id']."' class='forum-container'>\n";
        } else {
            echo "<div id='forum_".$data['forum_id']."' class='panel panel-default'>\n";
            echo "<div class='panel-body'>\n";
        }
        echo "<div class='pull-left forum-thumbnail'>\n";
        if ($data['forum_image'] && file_exists(FORUM."images/".$data['forum_image'])) {
            echo thumbnail(FORUM."images/".$data['forum_image'], '50px');
        } else {
            echo "<div class='forum-icon'>".$data['forum_icon_lg']."</div>\n";
        }
        echo "</div>\n";
        echo "<div class='overflow-hide'>\n";
        echo "<div class='row m-0'>\n";
        switch ($data['forum_type']) {
            case '3':
                echo "<div class='col-xs-12 col-sm-12'>\n";
                echo "<a class='display-inline-block forum-link' href='".$data['forum_link']['link']."'>".$data['forum_link']['title']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
                echo $data['forum_description'] ? "<div class='forum-description'>".$data['forum_description']."</div>\n" : '';
                echo ($data['forum_moderators'] ? "<span class='forum-moderators text-smaller'><strong>".$locale['forum_0007']."</strong>".$data['forum_moderators']."</span>\n" : "")."\n";
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
                echo $data['forum_description'] ? "<div class='forum-description'>".$data['forum_description']."</div>\n" : '';
                echo ($data['forum_moderators'] ? "<span class='forum-moderators text-smaller'><strong>".$locale['forum_0007']."</strong>".$data['forum_moderators']."</span>\n" : "")."\n";
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
                echo "<div class='hidden-xs col-sm-3 col-md-2 p-l-0 text-right'>\n";
                echo "<div class='text-lighter count'>".$data['forum_postcount_word']."</div>\n";
                echo "<div class='text-lighter count'>".$data['forum_threadcount_word']."</div>\n";
                echo "</div><div class='forum-lastuser hidden-xs hidden-sm col-md-4'>\n";
                if ($data['forum_lastpostid'] == 0) {
                    echo $locale['forum_0005'];
                } else {
                    echo "<div class='clearfix'>\n";
                    if (!empty($data['last_post']['avatar'])) {
                        echo "<div class='pull-left lastpost-avatar m-t-5'>".$data['last_post']['avatar']."</div>";
                    }
                    echo "<div class='overflow-hide'>\n";
                    echo "<span class='forum_profile_link'>".$data['last_post']['profile_link']." ".$data['last_post']['time']."</span>\n";
                    echo "<a class='lastpost-goto' href='".$data['last_post']['post_link']."' title='".$data['thread_subject']."'><i class='fa fa-external-link-square'></i></a><br />\n";
                    echo fusion_first_words(strip_tags($data['last_post']['message']), 10);
                    echo "</div>\n</div>\n";
                }
                echo "</div>\n";
        }
        echo "</div>\n"; // end row
        echo "</div>\n"; // end overflow-hide
        if ($i > 0) {
            echo "</div>\n";
        } else {
            echo "</div>\n</div>\n";
        }
    }
}

/**
 * For $_GET['viewforum'] view present.
 */
if (!function_exists('forum_viewforum')) {
    function forum_viewforum($info) {
        $locale = fusion_get_locale();

        $data = $info['item'][$_GET['forum_id']];

        echo render_breadcrumbs();

        echo "<div class='forum-title'>\n";
        echo "<h4>".$data['forum_name']." <span class='sub-title'>".$data['forum_threadcount_word']."</span></h4>\n";
        echo "<div class='forum-description'>\n".$data['forum_description']."</div>\n";
        echo "</div>\n";

        if (iMEMBER && $info['permissions']['can_post'] && !empty($info['new_thread_link'])) {
            echo "
			<div class='clearfix m-b-20 m-t-20'>\n
				<a title='".$locale['forum_0264']."' class='btn btn-primary btn-sm' href='".$info['new_thread_link']."'>".$locale['forum_0264']."</a>\n
			</div>\n
			";
        }

        echo $data['forum_rules'] ? "<div class='well'><span class='strong'><i class='fa fa-exclamation fa-fw'></i>".$locale['forum_0350']."</span> ".$data['forum_rules']."</div>\n" : '';
        // subforums
        if (!empty($info['item'][$_GET['forum_id']]['child'])) {
            echo "<div class='forum-title m-t-20'>".$locale['forum_0351']."</div>\n";
            $i = 1;
            echo "<div class='list-group-item'>\n";
            foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
                render_forum_item($subforum_data, $i);
                $i++;
            }
            echo "</div>\n";
        }

        if ($info['forum_type'] > 1) {
            echo "<!--pre_forum-->\n";
            echo "<div class='forum-title m-t-20'>".$locale['forum_0341']."</div>\n";
            forum_filter($info);
            echo "<div id='forumThreads'>\n";
            render_forum_threads($info);
            echo "</div>\n";
        }
        echo "
		<div class='list-group-item m-t-20'>
			<span>".sprintf($locale['forum_perm_access'],
                            $info['permissions']['can_access'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			<span>".sprintf($locale['forum_perm_post'],
                            $info['permissions']['can_post'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			<span>".sprintf($locale['forum_perm_create_poll'],
                            $info['permissions']['can_create_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			<span>".sprintf($locale['forum_perm_upload'],
                            $info['permissions']['can_upload_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			<span>".sprintf($locale['forum_perm_download'],
                            $info['permissions']['can_download_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
		</div>
		";
        if ($info['forum_moderators']) {
            echo "<div class='list-group-item'>".$locale['forum_0185']." ".$info['forum_moderators']."</div>\n";
        }
    }
}

if (!function_exists('render_forum_threads')) {
    function render_forum_threads($info) {
        $locale = fusion_get_locale();

        if (!empty($info['threads']['pagenav'])) {
            echo "<div class='text-right'>\n";
            echo $info['threads']['pagenav'];
            echo "</div>\n";
        }

        if (!empty($info['threads'])) {
            echo "<div class='forum-container list-group-item'>\n";
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
            echo "</div>\n";
        } else {
            echo "<div class='text-center'>".$locale['forum_0269']."</div>\n";
        }

        if (!empty($info['threads']['pagenav'])) {
            echo "<div class='text-right hidden-xs m-t-15'>\n";
            echo $info['threads']['pagenav'];
            echo "</div>\n";
        }

        if (!empty($info['threads']['pagenav2'])) {
            echo "<div class='hidden-sm hidden-md hidden-lg m-t-15'>\n";
            echo $info['threads']['pagenav2'];
            echo "</div>\n";
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
			".fusion_first_words(strip_tags($info['thread_last']['post_message']), 10)."
		</div>\n";
        echo "</div>\n";
        echo "</div>\n";
    }
}

if (!function_exists("render_participated")) {
    function render_participated($info) {
        $locale = fusion_get_locale('', FORUM_LOCALE);
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            // sort by date.
            $last_date = '';
            foreach ($info['item'] as $data) {
                $cur_date = date('M d, Y', $data['post_datestamp']);
                if ($cur_date != $last_date) {
                    $last_date = $cur_date;
                    $title = "<div class='post_title m-b-10'>".$locale['forum_0525']." ".$last_date."</div>\n";
                    echo $title;
                }
                render_thread_item($data);
            }
            if ($info['post_rows'] > 20) {
                echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $info['post_rows'], 3, FUSION_REQUEST,
                                                                                  "rowstart")."\n</div>\n";
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_054']."</div>\n";
        }
    }
}

if (!function_exists("render_laft")) {
    function render_laft($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            $i = 0;
            foreach ($info['item'] as $data) {
                render_thread_item($data);
                $i++;
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
        }

        $opts = array(
            '0' => $locale['forum_p999'],
            '1' => $locale['forum_p001'],
            '7' => $locale['forum_p007'],
            '14' => $locale['forum_p014'],
            '30' => $locale['forum_p030'],
            '90' => $locale['forum_p090'],
            '180' => $locale['forum_p180'],
            '365' => $locale['forum_p365']
        );

        echo "<hr/>\n";
        echo openform('filter_form', 'post', INFUSIONS."forum/index.php?section=latest", array('downtime' => 1));
        echo form_select('filter', $locale['forum_0009'], isset($_POST['filter']) && $_POST['filter'] ? $_POST['filter'] : 0, array(
            'options' => $opts,
            'width' => '300px',
            'class' => 'pull-left m-r-10'
        ));
        echo form_button('go', $locale['go'], $locale['go'], array('class' => 'btn-default btn-sm m-b-20'));
        echo closeform();
    }
}

if (!function_exists("render_tracked")) {
    /* Tracked Section */
    function render_tracked($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            $i = 0;
            foreach ($info['item'] as $data) {
                // do a thread.
                render_thread_item($data);
                $i++;
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_059']."</div>\n";
        }
    }
}

if (!function_exists("render_unanswered")) {
    /* Unanswered Section */
    function render_unanswered($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            $i = 0;
            foreach ($info['item'] as $data) {
                // do a thread.
                render_thread_item($data);
                $i++;
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
        }
    }
}

if (!function_exists("render_unsolved")) {
    /* Unsolved Section */
    function render_unsolved($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            $i = 0;
            foreach ($info['item'] as $data) {
                // do a thread.
                render_thread_item($data);
                $i++;
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
        }
    }
}

/* Forum Filter */
if (!function_exists('forum_filter')) {
    function forum_filter($info) {
        $locale = fusion_get_locale();

        $selector = array(
            'today' => $locale['forum_p000'],
            '2days' => $locale['forum_p002'],
            '1week' => $locale['forum_p007'],
            '2week' => $locale['forum_p014'],
            '1month' => $locale['forum_p030'],
            '2month' => $locale['forum_p060'],
            '3month' => $locale['forum_p090'],
            '6month' => $locale['forum_p180'],
            '1year' => $locale['forum_p365']
        );
        $selector2 = array(
            'all' => $locale['forum_0374'],
            'discussions' => $locale['forum_0375'],
            'attachments' => $locale['forum_0376'],
            'poll' => $locale['forum_0377'],
            'solved' => $locale['forum_0378'],
            'unsolved' => $locale['forum_0379'],
        );
        $selector3 = array(
            'author' => $locale['forum_0380'],
            'time' => $locale['forum_0381'],
            'subject' => $locale['forum_0382'],
            'reply' => $locale['forum_0383'],
            'view' => $locale['forum_0384'],
        );
        $selector4 = array(
            'descending' => $locale['forum_0386'],
            'ascending' => $locale['forum_0385']
        );

        if (isset($_GET['tag_id']) && isnum($_GET['tag_id']) || isset($_GET['forum_id']) && isnum($_GET['forum_id'])) {
            echo "<div class='filter'>\n";

            echo $locale['forum_0388'];
            echo "<div class='forum-filter'>\n";
            echo "<button class='btn btn-xs btn-default dropdown-toggle' data-toggle='dropdown'>".(isset($_GET['time']) && in_array($_GET['time'],
                                                                                                                                    array_flip($selector)) ? $selector[$_GET['time']] : $locale['forum_0387'])." <span class='caret'></span></button>\n";
            echo "<ul class='dropdown-menu'>\n";
            foreach ($info['filter']['time'] as $filter_locale => $filter_link) {
                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
            }
            echo "</ul>\n";
            echo "</div>\n";
            echo $locale['forum_0389'];
            echo "<div class='forum-filter'>\n";
            echo "<button class='btn btn-xs btn-default dropdown-toggle' data-toggle='dropdown'>".(isset($_GET['type']) && in_array($_GET['type'],
                                                                                                                                    array_flip($selector2)) ? $selector2[$_GET['type']] : $locale['forum_0390'])." <span class='caret'></span></button>\n";
            echo "<ul class='dropdown-menu'>\n";
            foreach ($info['filter']['type'] as $filter_locale => $filter_link) {
                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
            }
            echo "</ul>\n";
            echo "</div>\n";
            echo $locale['forum_0225'];
            echo "<div class='forum-filter'>\n";
            echo "<button class='btn btn-xs btn-default dropdown-toggle' data-toggle='dropdown'>".(isset($_GET['sort']) && in_array($_GET['sort'],
                                                                                                                                    array_flip($selector3)) ? $selector3[$_GET['sort']] : $locale['forum_0391'])." <span class='caret'></span></button>\n";
            echo "<ul class='dropdown-menu'>\n";
            foreach ($info['filter']['sort'] as $filter_locale => $filter_link) {
                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
            }
            echo "</ul>\n";
            echo "</div>\n";
            echo "<div class='forum-filter'>\n";
            echo "<button class='btn btn-xs btn-default dropdown-toggle' data-toggle='dropdown'>".(isset($_GET['order']) && in_array($_GET['order'],
                                                                                                                                     array_flip($selector4)) ? $selector4[$_GET['order']] : $locale['forum_0386'])." <span class='caret'></span></button>\n";
            echo "<ul class='dropdown-menu'>\n";
            foreach ($info['filter']['order'] as $filter_locale => $filter_link) {
                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
            }
            echo "</ul>\n";
            echo "</div>\n";
            echo "</div>\n"; // .filter
        }
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
                'inline' => 1,
                'width' => '100%'
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
        echo (!empty($info['description']) ? $info['description']."<br/>\n" : "");
        foreach($info['link'] as $link) {
            echo "<p><a href='".$link['url']."'>".$link['title']."</a></p>\n";
        }
        echo "</div>\n";
        closetable();
    }
}
