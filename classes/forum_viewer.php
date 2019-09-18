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
namespace PHPFusion\Infusions\Forum\Classes;

use PHPFusion\Infusions\Forum\Classes\Forum\Forum;
use PHPFusion\Template;

/**
 * Class Forum_Viewer
 *
 *  Template names and functions
 *  Namespace Key               Callback function               Has CSS / Is a Page?
 *  forum-reports               forum_reports();                Yes
 *  forum-post                  render_post_item();             -
 *  forum-thread                forum_threads_item()            -
 *  forum-section               forum_section()                 Yes
 *  viewforum                   forum_viewforum()               Yes
 *  forum                       forum_index()                   Yes
 *  forum-contributor-list      popular_contributor_panel()     -
 *  forum-sticky-panel          sticky_discussions_panel()      -
 *  forum-subforum-item         forum_subforums_item()          -
 *  viewthreads                 render_thread()                 Yes
 *  forum-quick-reply           display_quick_reply             -
 *  forum-poll                  display_forum_pollform()        -
 *  forum-postify               display_forum_postify()         -
 *  forum-post-form             display_forum_postform()        Yes
 *  forum-bounty-form           display_forum_bountyform()      -
 *  forum-tags                  display_forum_tags()            Yes
 *
 * @package PHPFusion\Infusions\Forum\Classes
 */
class Forum_Viewer {

    private static $instance = NULL;

    protected $forum_id = 0;

    private $info = [];

    private $locale = [];

    private $forum_nav_callback = [];

    private $css_file_path = '';

    private $forum = NULL;

    /**
     * Forum_Viewer constructor.
     */
    public function __construct() {

        $dev_mode = FALSE;
        $css_min_file_path = FORUM.'templates/forum.min.css';
        $this->css_file_path = FORUM.'templates/forum.css';
        if (file_exists($css_min_file_path) && $dev_mode === FALSE) {
            $this->css_file_path = $css_min_file_path;
        }
        $this->forum = new Forum();
    }

    /**
     * Forum View Instance
     *
     * @return null|Forum_Viewer
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @param $info
     *
     * @return string
     */
    public function render_forum($info) {
        $this->info = $info;
        $this->locale = fusion_get_locale();
        $section = $this->forum->getForumSection();

        if ($section) {
            if ($section == 'moderator') {
                return $this->forum_report($info);
            }

            return $this->forum_section($info);

        } else {

            if (isset($_GET['viewforum'])) {

                if ($info['forum_type'] > 1) {
                    return render_viewforum($info);
                } else {
                    return $this->forum_index($info);
                }

            } else {
                return $this->forum_index($info);
            }
        }
    }

    /**
     * forum/index.php?section=moderator&rid={int}
     * Template name - forum-reports
     *
     * @param $info
     *
     * @return string
     */
    public function forum_report($info) {
        //print_p($info);
        $locale = fusion_get_locale();
        $html = \PHPFusion\Template::getInstance('forum-reports');
        $html->set_template(__DIR__.'/../templates/forum_reports.html');
        $html->set_css($this->css_file_path);
        $html->set_locale($locale);
        $html->set_tag("forum_navs", self::get_forum_navs());
        $html->set_tag("title", $info['title']);
        $html->set_tag('baselink', FORUM.'index.php');
        $html->set_tag("description", $info['description']);
        $html->set_tag('breadcrumb', render_breadcrumbs());
        if (!empty($info['section_links'])) {
            foreach ($info['section_links'] as $link_data) {
                $html->set_block("section_links", $link_data);
            }
        }

        if (isset($_GET['rid']) && isnum($_GET['rid'])) {
            $html->set_block("view_link", [
                    "post_link"   => $info['post_link'],
                    "thread_link" => $info['thread_link'],
                ]
            );

            $html->set_block("report_details", [
                "report_id"           => $info['report_id'],
                "report_status"       => $info['report_status'],
                "report_comment"      => $info['report_comment'],
                "user_profile_link"   => $info['reporter']['user_profile_link'],
                "report_date"         => $info['report_date'],
                "report_time"         => $info['report_time'],
                "report_updated_date" => $info['report_updated_date'],
                "report_updated_time" => $info['report_updated_time'],
                "report_action"       => $info['report_action']
            ]);
            // generate the post
            if (!empty($info['post_items'])) {
                $i = 1;
                foreach ($info['post_items'] as $post_id => $post_data) {
                    $html->set_block("view_post", ["content" => $this->render_post_item($post_data, $i)]);
                    $i++;
                }
            }
        }

        $html->set_block("view_thread", ["content" => $this->forum_threads_item($info)]);

        return $html->get_output();

    }

    public function get_forum_navs() {
        $locale = fusion_get_locale();
        if (empty($this->forum_nav_callback)) {
            // get all root forums
            $menu_arr = [];
            $forum_limit = 8;
            $forum_rows = 0;
            $total_forums = dbcount("(forum_id)", DB_FORUMS, "forum_cat=0 AND ".groupaccess('forum_access'));
            if ($total_forums) {
                $result = dbquery("SELECT forum_name, forum_id FROM ".DB_FORUMS." WHERE forum_cat=0 AND ".groupaccess('forum_access')." ORDER BY forum_name ASC LIMIT 0, $forum_limit");
                if ($forum_rows = dbrows($result)) {
                    while ($data = dbarray($result)) {
                        $color = "#".stringToColorCode($data['forum_name']);
                        $menu_arr['forum_'.$data['forum_id']] = self::sub_link_item('forum_'.$data['forum_id'],
                            "<span><i class='fas fa-circle' style='color:$color;'></i> ".$data['forum_name']."</span>", FORUM."?viewforum=true&forum_id=".$data['forum_id'], 99, iGUEST, FALSE, FALSE, "col-xs-12 col-sm-6 p-0", " text-overflow-hide");
                    }
                }
            }
            $forum_more = $total_forums > $forum_limit ? "(".($total_forums - $forum_rows)." more)" : '';
            $this->forum_nav_callback = [
                0 => [
                    1 => self::sub_link_item(1, "<span class='m-r-10'><i class='fas fa-bars'></i> Forum Sections</span>", "", 0, iGUEST, FALSE, FALSE)
                ],
                1 => [
                        "latest"       => self::sub_link_item("latest", "<span><i class='fas fa-comment'></i> Latest</span>", FORUM."index.php?section=latest", 1, iGUEST, FALSE, FALSE, "col-xs-6 p-0"),
                        "unsolved"     => self::sub_link_item("unsolved", "<span><i class='far fa-frown'></i> Unsolved</span>", FORUM."index.php?section=unsolved", 1, iGUEST, FALSE, FALSE, "col-xs-6 p-0"),
                        'unanswered'   => self::sub_link_item("unanswered", "<span><i class='fas fa-comment-slash'></i> Unanswered</span>", FORUM."index.php?section=unanswered", 1, iGUEST, FALSE, FALSE, "col-xs-6 p-0"),
                        'participated' => self::sub_link_item("participated", "<span><i class='fas fa-comments'></i> Participated</span>", FORUM."index.php?section=participated", 1, iGUEST, FALSE, FALSE, "col-xs-6 p-0"),
                        'tags'         => self::sub_link_item("tags", "<span><i class='fas fa-tags'></i> Tags</span>", FORUM."tags.php", 1, iGUEST, FALSE, FALSE, "col-xs-12 col-sm-6 p-0"),
                        'tracked'      => self::sub_link_item("tracked", "<span><i class='fas fa-dove'></i> Tracked</span>", FORUM."index.php?section=tracked", 1, iMEMBER, FALSE, FALSE, "col-xs-12 col-sm-6 p-0"),
                        "divider"      => self::sub_link_item("divider", "---", "#", 1, iGUEST, FALSE, FALSE),
                        "cat"          => self::sub_link_item("cat", "Categories $forum_more", FORUM, 1, iGUEST, FALSE, FALSE),
                    ] + $menu_arr,
            ];
            if (checkrights("F")) {
                $this->forum_nav_callback[0]['mod'] = self::sub_link_item('mod', "<span><i class='fas fa-flag-checkered'></i> Moderators Reports</span>", FORUM."index.php?section=moderator", 0);
            }
            //print_p($this->forum_nav_callback);
        }
        $menu_config = [
            'id'                 => 'forum-menu',
            'container'          => FALSE,
            'navbar_class'       => 'navbar-forum',
            'language_switcher'  => FALSE,
            'searchbar'          => FALSE,
            'caret_icon'         => 'fa fa-angle-down',
            'callback_data'      => $this->forum_nav_callback,
            'grouping'           => FALSE,
            'links_per_page'     => FALSE,
            'show_banner'        => TRUE,
            'show_header'        => TRUE,
            'custom_banner_link' => FORUM.'index.php',
            'custom_banner'      => '<h4>'.$locale['forum_0000'].'</h4>',
            //'html_content'      => openform('forum_searchFrm', 'post', FUSION_REQUEST, ["class" => "pull-right"]).form_text("forum_search", "", "", ["placeholder" => "Search...", "class" => "m-0 center-y", "feedback_icon" => TRUE, "icon" => "fas fa-search text-dark"]).closeform(),
        ];

        return \PHPFusion\SiteLinks::setSubLinks($menu_config)->showSubLinks();
    }

    // Modules to import into settings

    public static function sub_link_item($i, $link_name, $url = '', $link_cat = 0, $link_visibility = iGUEST, $as_title = FALSE, $is_disabled = FALSE, $li_class = '', $link_class = '', $link_icon = '', $link_active = FALSE) {
        return [
            'link_id'         => $i,
            'link_url'        => $url,
            'link_name'       => $link_name,
            'link_cat'        => $link_cat,
            'link_visibility' => $link_visibility,
            'link_title'      => $as_title,
            'link_disabled'   => $is_disabled,
            'link_li_class'   => $li_class,
            'link_class'      => $link_class,
            'link_icon'       => $link_icon,
            'link_active'     => $link_active
        ];
    }

    /**
     * Render the forum thread post item
     * Template name - forum-post
     *
     * @param mixed $data post data
     * @param int   $n    nth count
     *
     * @return string
     */
    public function render_post_item($data, $n = 0) {

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $settings = fusion_get_settings();
        $forum_settings = Forum_Server::get_forum_settings();
        $file_path = get_forum_template('forum_post');
        $html = \PHPFusion\Template::getInstance('forum-post');
        $html->set_template($file_path);
        //$html->set_css($this->css_file_path);
        $html->set_locale($locale);
        $html->set_tag("post_html_comment", "<!--forum_thread_prepost_".$data['post_id']."-->");
        $html->set_tag("post_date", $data['post_shortdate']);
        $html->set_tag("item_marker_id", $data['marker']['id']);
        $html->set_tag("item_id", $n);
        $html->set_tag("user_avatar", $data['user_avatar_image']);

        // label style
        $html->set_tag('user_rank', '');
        $html->set_tag('user_avatar_rank', '');
        if (!empty($data['user_rank']['rank_title'])) {
            $html->set_tag('user_rank', '<span class="forum-rank">'.$data['user_rank']['rank_title'].'</span>');
            // image style
            if ($forum_settings['forum_rank_style']) {
                $html->set_tag('user_rank', '');
                $html->set_tag('user_avatar_rank', '<span class="forum-rank"><img title="'.$data['user_rank']['rank_title'].'" src="'.$data['user_rank']['rank_image_src'].'"/></span>');
            }
        }

        $html->set_tag("user_profile_link", $data['user_profile_link']);
        $html->set_tag("user_online_status", ($data['user_online'] ? "fa fa-circle" : "fa fa-circle-thin"));
        $html->set_tag("user_signature", ($data['user_sig'] ? "<div class='forum-sig'>".$data['user_sig']."</div>" : ''));
        $html->set_tag("checkbox_input", (iMOD ? $data['post_checkbox'] : ''));
        $html->set_tag("post_message", fusion_parse_user($data['post_message']));
        $html->set_tag("post_edit_reason", $data['post_edit_reason']);
        $html->set_tag("post_reply_message", $data['post_reply_message']);
        $html->set_tag("post_mood_message", ($data['post_mood_message'] ? $data['post_mood_message'] : ''));
        if (!empty($data['post_bounty']['link'])) {
            $html->set_block("bounty_btn", [
                'link_url'   => $data['post_bounty']['link'],
                'link_title' => $data['post_bounty']['title'],
                'title'      => $data['post_bounty']['title'],
            ]);
        }
        $html->set_tag("post_class", "");
        if ($data['post_reported']) {
            $html->set_tag("post_class", " reported");
            if (iMOD) {
                $html->set_block("report_info", [
                    "link" => FORUM."index.php?section=moderator&rid=".$data['post_report_id'],
                ]);
            }
        }
        if ($data['post_mood']) {
            $html->set_block("mood_messages", ['btns' => $data['post_mood']]);
        }
        if ($data['post_attachments']) {
            $html->set_block("post_attachments", ['attach' => $data['post_attachments']]);
        }
        if ((isset($data['post_quote']) && !empty($data['post_quote']))) {
            $html->set_block("quote_btn", [
                'link_url'   => $data['post_quote']['link'],
                'link_title' => $data['post_quote']['title'],
                'title'      => $data['post_quote']['title'],
            ]);
        }
        if (isset($data['post_report'])) {
            $html->set_block("report_btn", [
                'link_url'   => $data['post_report']['link'],
                'link_title' => $data['post_report']['title'],
                'title'      => $data['post_report']['title'],
            ]);
        }

        if ((isset($data['print']) && !empty($data['print']))) {
            $html->set_block("li_print", [
                'link_url'   => $data['print']['link'],
                'link_title' => $data['print']['title'],
                'title'      => $data['print']['title'],
            ]);
        }
        if ((isset($data['post_reply']) && !empty($data['post_reply']))) {
            $html->set_block("reply_btn", [
                'link_url'   => $data['post_reply']['link'],
                'link_title' => $data['post_reply']['title'],
                'title'      => $data['post_reply']['title'],
            ]);
        }
        if ((isset($data['post_edit']) && !empty($data['post_edit']))) {
            $html->set_block("edit_btn", [
                'link_url'   => $data['post_edit']['link'],
                'link_title' => $data['post_edit']['title'],
                'title'      => $data['post_edit']['title'],
            ]);
        }
        // Dropdowns
        $html->set_block('li_post_count', ['title' => $data['user_post_count']]);
        $html->set_block("li_print_post", [
            'link_url'   => $data['print']['link'],
            'link_title' => $data['print']['title'],
            'title'      => $data['print']['title']
        ]);
        if ($data['user_ip']) {
            $html->set_block("li_ip", ['title' => $data['user_ip']]);
        }
        if ($data['user_message']['link']) {
            $html->set_block("li_um", [
                'link_url'   => $data['user_message']['link'],
                'link_title' => $data['user_message']['title'],
                'title'      => $data['user_message']['title']
            ]);
        }
        if (isset($data['post_quote']) && !empty($data['post_quote'])) {
            $html->set_block("li_quote", [
                'link_url'   => $data['post_quote']['link'],
                'link_title' => $data['post_quote']['title'],
                'title'      => $data['post_quote']['title']
            ]);
        }
        if (!empty($data['post_edit'])) {
            $html->set_block("li_edit", [
                'link_url'   => $data['post_edit']['link'],
                'link_title' => $data['post_edit']['title'],
                'title'      => $data['post_edit']['title'],
            ]);
        }
        if ($data['user_web']['link']) {
            $html->set_block("li_web", [
                'link_url'    => $data['user_message']['link'],
                'link_title'  => $data['user_message']['title'],
                'title'       => $data['user_message']['title'],
                'noindex_a'   => ($settings['index_url_userweb'] ? "" : "<!--noindex-->"),
                'noindex_b'   => ($settings['index_url_userweb'] ? "" : "<!--/noindex-->"),
                'noindex_rel' => ($settings['index_url_userweb'] ? "" : " rel='nofollow'")
            ]);
        }
        if ($data['user_level'] > USER_LEVEL_SUPER_ADMIN) {
            // thread id
            if (iSUPERADMIN || (iADMIN && checkrights('M'))) {
                $html->set_block("li_admin_title", [
                    'title' => $locale['forum_0662']
                ]);
                $html->set_block("li_edit_user", [
                    'link_url'   => ADMIN."members.php".$aidlink."&amp;step=edit&amp;user_id=".$data['user_id'],
                    'link_title' => $locale['forum_0663'],
                    'title'      => $locale['forum_0663'],
                ]);
                $html->set_block("li_ban_user", [
                    'link_url'   => $data['thread_link'].'&amp;step=ban_user&amp;user_id='.$data['user_id'],
                    'link_title' => $locale['forum_0664'],
                    'title'      => $locale['forum_0664'],
                ]);
                $html->set_block("li_delete_user", [
                    'link_url'   => $data['thread_link'].'&amp;step=delete_user&amp;user_id='.$data['user_id'],
                    'link_title' => $locale['forum_0665'],
                    'title'      => $locale['forum_0665'],
                ]);
            }
        }
        if (!empty($data['post_votes'])) {
            /*
            $html->set_block("votebox", [
                'input'  => $data['post_votebox'],
                'status' => $data['post_answer_check']
            ]);
            */
            $html->set_block("post_votes", [
                "up_link"     => $data['post_votes']['up']['link'],
                "up_active"   => ($data['post_votes']['up']['active'] ? " text-warning" : ""),
                "down_link"   => $data['post_votes']['down']['link'],
                "down_active" => ($data['post_votes']['down']['active'] ? " text-warning" : ''),
                "points"      => (!empty($data['vote_points']) ? format_num($data['vote_points']) : 0), // this need to shorten to 1.5k for 1,500
            ]);

        }
        //$callback = implode('', array_map(function($array) { return "<li><div class='col-xs-12 col-sm-3 strong'>".$array['title']."</div><div class='col-xs-12 col-sm-9'>".$array['value']."</div></li>";}, $data['user_profiles']));        if (!empty($callback)) {
        if (!empty($data['user_profiles'])) {
            $temp_name = '';
            $user_profiles = "";
            $user_profiles .= '<ul class="block">';
            // must nest for easier implementation?
            $i = 0;
            foreach ($data['user_profiles'] as $attr) {
                $open_user_profiles = '';
                if ($temp_name !== $attr['field_cat_name']) {
                    $open_user_profiles .= $i ? "</ul><ul class='block m-t-5'>" : "";
                    $open_user_profiles .= "<li class='strong'>".\PHPFusion\UserFieldsQuantum::parse_label($attr['field_cat_name'])."</li>";
                }

                $user_profiles .= $open_user_profiles;
                $user_profiles .= "<li>";
                $user_profiles .= "<strong>".$attr['title']."</strong>: ".$attr['value'];
                $user_profiles .= "</li>";
                $temp_name = $attr['field_cat_name'];
            }
            $user_profiles .= "</ul>";
            $html->set_block("user_profiles", ['profiles' => $user_profiles]);
        }

        return (string)$html->get_output();
    }

    /**
     * Threads Item Display
     * Enable with ajax based filters for faster performance
     *
     * Template name    forum-thread
     * Template File     templates/viewforum/forum_thread_item.html
     *
     *
     * @param $info
     *
     * @return string
     */
    public function forum_threads_item($info) {
        $locale = fusion_get_locale();
        // Ok, since this is a subpage and also require replacement, we need a new html file.
        $file_path = get_forum_template('forum_thread');
        $html = \PHPFusion\Template::getInstance('forum-thread');
        $html->set_locale($locale);
        $html->set_template($file_path);
        $html->set_template(__DIR__.'/../templates/forum_thread_item.html');

        $html->set_tag('title1', $locale['forum_0228']);
        $html->set_tag('title2', $locale['forum_0052']);
        $html->set_tag('title3', $locale['forum_0020']);
        $html->set_tag('title4', $locale['forum_0053']);
        if (!empty($info['threads'])) {
            if (!empty($info['threads']['sticky'])) {
                foreach ($info['threads']['sticky'] as $cdata) {
                    $thread_buttons = "";
                    if (iMEMBER) {
                        $html3 = \PHPFusion\Template::getInstance('threads_'.$cdata['thread_id']);
                        $html3->set_block("track_button", [
                            "track_link"    => $cdata['track_button']['link'],
                            "track_title"   => $cdata['track_button']['title'],
                            "track_onclick" => $cdata['track_button']['onclick'],
                        ]);
                        $html3->set_template(__DIR__.'/../templates/forum_thread_button.html');
                        $thread_buttons = $html3->get_output();
                    }

                    $html->set_block('threads', [
                        'thread_id'             => $cdata['thread_id'],
                        'thread_link_url'       => $cdata['thread_link']['link'],
                        'thread_link_title'     => $cdata['thread_link']['title'],
                        'thread_icons'          => implode('', $cdata['thread_icons']),
                        'thread_pages'          => $cdata['thread_pages'],
                        'author_avatar'         => $cdata['thread_starter']['avatar'],
                        'author_avatar_sm'      => $cdata['thread_starter']['avatar_sm'],
                        'author_profile_link'   => $cdata['thread_starter']['profile_link'],
                        'thread_text'           => parse_textarea($cdata['post_message'], $cdata['post_smileys'], TRUE, FALSE, IMAGES, TRUE),
                        'thread_snippet'        => trim_text($cdata['post_message'], 80),
                        'thread_attachments'    => $cdata['post_attachments'],
                        'thread_date'           => $cdata['post_date'],
                        'thread_time'           => $cdata['post_time'],
                        // Format Stats
                        'thread_views'          => number_format($cdata['thread_views']),
                        'thread_postcount'      => number_format($cdata['thread_postcount']),
                        'thread_votecount'      => number_format($cdata['vote_count']),
                        'thread_views_word'     => format_word($cdata['thread_views'], 'view|views'),
                        'thread_postcount_word' => format_word($cdata['thread_postcount'], 'post|posts'),
                        'thread_votecount_word' => format_word($cdata['vote_count'], 'vote|votes'),
                        "thread_user_avatars"   => '<div class="img-circle m-r-5">'.implode('</div><div class="img-circle m-r-5">', $cdata['thread_user_avatars']).'</div>',
                        // Last info
                        'last_avatar'           => $cdata['thread_last']['avatar'],
                        'last_rank'             => $cdata['thread_last']['user']['user_rank'],
                        'last_profile_link'     => $cdata['thread_last']['profile_link'],
                        'last_avatar_sm'        => $cdata['thread_last']['avatar_sm'],
                        'last_post_message'     => trim_text($cdata['last_post_message'], 80),
                        'last_activity_time'    => $cdata['thread_last']['time'],
                        'last_activity_date'    => $cdata['thread_last']['date'],
                        'track_button'          => (isset($cdata['track_button']) ? "<a class='btn btn-danger btn-sm' ".$cdata['track_button']['onclick']." href='".$cdata['track_button']['link']."'>".$cdata['track_button']['title']."</a>" : ''),
                        "track_link"            => $cdata['track_button']['link'],
                        "track_title"           => $cdata['track_button']['title'],
                        "track_onclick"         => $cdata['track_button']['onclick'],
                        "thread_buttons"        => $thread_buttons
                    ]);
                }
            }
            if (!empty($info['threads']['item'])) {
                foreach ($info['threads']['item'] as $cdata) {
                    $thread_buttons = "";
                    if (iMEMBER) {
                        $html3 = Template::getInstance('threads_'.$cdata['thread_id']);
                        $html3->set_block("track_button", [
                            "track_link"    => $cdata['track_button']['link'],
                            "track_title"   => $cdata['track_button']['title'],
                            "track_onclick" => $cdata['track_button']['onclick'],
                        ]);

                        $html3->set_template(__DIR__.'/../templates/forum_thread_button.html');
                        $thread_buttons = $html3->get_output();
                    }

                    $threads_arr = [
                        'thread_id'             => $cdata['thread_id'],
                        'thread_link_url'       => $cdata['thread_link']['link'],
                        'thread_link_title'     => $cdata['thread_link']['title'],
                        'thread_icons'          => implode('', $cdata['thread_icons']),
                        'thread_pages'          => $cdata['thread_pages'],
                        'author_avatar'         => $cdata['thread_starter']['avatar'],
                        'author_avatar_sm'      => $cdata['thread_starter']['avatar_sm'],
                        'author_profile_link'   => $cdata['thread_starter']['profile_link'],
                        'thread_text'           => $cdata['post_message'],
                        'thread_snippet'        => trim_text($cdata['post_message'], 80),
                        'thread_attachments'    => $cdata['post_attachments'], // have prob at unsolved & type = attachments
                        'thread_date'           => $cdata['post_date'], // have prob
                        'thread_time'           => $cdata['post_time'], // have prob
                        // Format Stats
                        'thread_views'          => number_format($cdata['thread_views']),
                        'thread_postcount'      => number_format($cdata['thread_postcount']),
                        'thread_votecount'      => number_format($cdata['vote_count']),
                        'thread_views_word'     => format_word($cdata['thread_views'], 'view|views'),
                        'thread_postcount_word' => format_word($cdata['thread_postcount'], 'post|posts'),
                        'thread_votecount_word' => format_word($cdata['vote_count'], 'vote|votes'),
                        "thread_user_avatars"   => implode('', $cdata['thread_user_avatars']),
                        // Last info
                        'last_avatar'           => $cdata['thread_last']['avatar'],
                        'last_rank'             => $cdata['thread_last']['user']['user_rank']['rank_user_level'],
                        'last_profile_link'     => $cdata['thread_last']['profile_link'],
                        'last_avatar_sm'        => $cdata['thread_last']['avatar_sm'],
                        'last_post_message'     => trim_text($cdata['last_post_message'], 80),
                        'last_activity_time'    => $cdata['thread_last']['time'],
                        'last_activity_date'    => $cdata['thread_last']['date'],
                        'track_button'          => (isset($cdata['track_button']) ? "<a class='btn btn-danger btn-sm' ".$cdata['track_button']['onclick']." href='".$cdata['track_button']['link']."'>".$cdata['track_button']['title']."</a>" : ''),
                        "track_link"            => $cdata['track_button']['link'],
                        "track_title"           => $cdata['track_button']['title'],
                        "track_onclick"         => $cdata['track_button']['onclick'],
                        "thread_buttons"        => $thread_buttons
                    ];
                    //print_P($threads_arr);
                    //print_P($cdata['thread_last']['user']);
                    $html->set_block('threads', $threads_arr);

                }
            }
        } else {
            $html->set_block('no_item');
        }
        if (!empty($info['threads']['pagenav2'])) {
            $html->set_block('pagenav_b', ['navigation_2' => $info['threads']['pagenav2']]);
        }

        return $html->get_output();
    }

    public function forum_filter($info) {
        // Put into core views
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


        $tpl = '
        <div class="clearfix">
        {time_filter.{
        <div class="pull-left">
        {[forum_0388]}
        <div class="forum-filter dropdown">
        <button class="btn btn-sm btn-default {%time_active%} dropdown-toggle" data-toggle="dropdown">
        <strong>{%time%}</strong><span class="caret m-l-5"></span>
        </button>
        <ul class="dropdown-menu">{%time_filter%}</ul>                        
        </div>
        </div>    
        }}
        <div class="pull-left">
        {sort_filter.{
        {[forum_0225]}
        <div class="forum-filter dropdown">      
        <button class="btn btn-sm btn-default {%sort_active%} dropdown-toggle" data-toggle="dropdown">
        <strong>{%sort%}</strong>
        <span class="caret m-l-5"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right">{%sort_filter%}</ul>                        
        </div>
        }}
        {order_filter.{
        <div class="forum-filter dropdown">      
        <button class="btn btn-sm btn-default {%order_active%} dropdown-toggle" data-toggle="dropdown">
        <strong>{%order%}</strong><span class="caret m-l-5"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right">{%order_filter%}</ul>                        
        </div>
        }}
        </div>
        {reset_filter.{
        <div class="pull-right">
        <a href="{%reset_link%}" class="btn btn-sm btn-default"><i class="fas fa-times-circle"></i> Reset</a>
        </div>              
        }}
        </div>';

        $html = Template::getInstance('forum-filter');
        $html->set_text($tpl);
        $html->set_locale($locale);


        if (!empty($info['filter']['time'])) {
            $get_time = get('time');
            $time_active = $get_time && isset($selector[$get_time]) ? 'active' : '';
            $time_value = $time_active ? $selector[$get_time] : $locale['forum_0211'];
            $time_filter = '';
            foreach ($info['filter']['time'] as $filter_locale => $filter_link) {
                $time_filter .= '<li><a href="'.$filter_link.'">'.$filter_locale.'</a></li>';
            }
            $html->set_block('time_filter', [
                'time'        => $time_value,
                'time_active' => $time_active,
                'time_filter' => $time_filter
            ]);
        }

        if (!empty($info['filter']['sort'])) {
            $get_sort = get('sort');
            $sort_active = $get_sort && isset($selector3[$get_sort]) ? 'active' : '';
            $sort_value = $sort_active ? $selector3[$get_sort] : $locale['forum_0381'];
            $sort_filter = '';
            foreach ($info['filter']['sort'] as $filter_locale => $filter_link) {
                $sort_filter .= '<li><a href="'.$filter_link.'">'.$filter_locale.'</a></li>';
            }
            $html->set_block('sort_filter', [
                'sort'        => $sort_value,
                'sort_active' => $sort_active,
                'sort_filter' => $sort_filter
            ]);
        }

        if (!empty($info['filter']['order'])) {
            $get_order = get('order');
            $order_active = $get_order && isset($selector4[$get_order]) ? 'active' : '';
            $order_value = $order_active ? $selector4[$get_order] : $locale['forum_0381'];
            $order_filter = '';
            foreach ($info['filter']['order'] as $filter_locale => $filter_link) {
                $order_filter .= '<li><a href="'.$filter_link.'">'.$filter_locale.'</a></li>';
            }
            $html->set_block('sort_filter', [
                'sort'        => $order_value,
                'sort_active' => $order_active,
                'sort_filter' => $order_filter
            ]);
        }

        if (!empty($get_sort) || !empty($get_order) || !empty($get_time)) {
            $reset_url = clean_request('', ['time', 'sort', 'order'], FALSE);
            $html->set_block('reset_filter', [
                    'reset_link' => $reset_url
            ]);
        }

        return $html->get_output();
    }

    /**
     * Forum Sections Item Display (Latest, Participated, Tracked, Unanswered, Unsolved)
     * Template name    forum-section
     * Template File     templates/viewforum/forum_section.html
     *
     * @param $info
     *
     * @return string
     */
    public function forum_section($info) {

        if (!$this->forum->getForumSection()) {
            redirect(FORUM.'index.php');
        }

        $locale = fusion_get_locale();
        $file_path = get_forum_template('forum_section');
        $html = Template::getInstance('forum-section');
        $html->set_template($file_path);
        $html->set_css($this->css_file_path);
        $html->set_locale($locale);
        $html->set_tag('baselink', $info['link']);
        $html->set_tag('forum_navs', $this->get_forum_navs());
        $html->set_tag('title', $info['title']);
        $html->set_tag('description', $info['description']);
        $html->set_tag('breadcrumb', render_breadcrumbs());

        if (!empty($info['filters']['type'])) {
            foreach ($info['filters']['type'] as $key => $tabs) {
                $html->set_block('tab_filter', [
                    'filter_link' => $tabs['link'],
                    'active_text' => $tabs['active'] ? " text-active" : "",
                    'title'       => $tabs['icon'].$tabs['title'],
                    'count'       => $tabs['count']
                ]);
            }
        }
        $html->set_tag('forum_filter', forum_filter($info));

        if (!empty($info['threads']['pagenav'])) {
            $html->set_block('section_pagenav_top', ['pagenav' => $info['threads']['pagenav']]);
            $html->set_block('section_pagenav_bottom', ['pagenav' => $info['threads']['pagenav']]);
        }

        if (!empty($info['threads_time_filter'])) {
            $html->set_block('filter_dropdown', ["content" => $info['threads_time_filter']]);
        }

        if (!empty($info['section_links'])) {
            foreach ($info['section_links'] as $link_data) {
                $html->set_block("section_links", $link_data);
            }
        }

        if (!empty($info['new_topic_link']['link'])) {
            $html->set_block('new_thread_link', [
                'new_thread_link_url'   => $info['new_topic_link']['link'],
                'new_thread_link_title' => $info['new_topic_link']['title']
            ]);
        }

        $html->set_tag("section_content", $this->forum_threads_item($info));

        return $html->get_output();
    }

    /**
     * Viewforum
     * Shows the forum threads and details
     * Template File    templates/forum_viewforum.html
     * Template File    templates/viewforum/forum_users.html
     *
     * @param $info
     *
     * @return string
     */
    public function viewforum($info) { // need the $info['get_view']....

        $locale = fusion_get_locale();

        $html = Template::getInstance('forum-viewforum');
        $html->set_template(get_forum_template('viewforum'));
        $html->set_css($this->css_file_path);
        $html->set_locale($locale);

        $view = get('view');
        $view = $view && in_array($view, ['threads', 'subforums', 'people', 'activity']) ? $view : '';
        switch ($view) {
            default:
                if (!empty($info['filters']['type'])) {
                    foreach ($info['filters']['type'] as $key => $tabs) {
                        $html->set_block('tab_filter', [
                            'filter_link' => $tabs['link'],
                            'active_text' => ($tabs['active'] ? ' strong' : ''),
                            'title'       => $tabs['icon'].$tabs['title'],
                            'count'       => $tabs['count']
                        ]);
                    }
                }

                $html->set_tag('forum_filter', forum_filter($info));

                if (!empty($info['threads']['pagenav'])) {
                    $html->set_block('pagenav_top', ['navigation' => $info['threads']['pagenav']]);
                    $html->set_block('pagenav_bottom', ['navigation' => $info['threads']['pagenav']]);
                }

                $html->set_block('view', ['content' => $this->forum_threads_item($info)]);

            case 'threads':
                if ($info['forum_type'] > 1) {
                    $html->set_block('view', ['content' => $this->forum_threads_item($info)]);
                }
                break;

            case 'subforums':

                $html->set_template(FORUM.'templates/viewforum/forum_subforums.html');

                if (!empty($info['item'][$_GET['forum_id']]['child'])) {
                    $i = 1;
                    foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
                        $html->set_block('subforums', [
                            'content' => forum_subforums_item($subforum_data)
                        ]);
                        $i++;
                    }
                } else {
                    $html->set_block('no_forum');
                }

                return $html->get_output();
                break;

            case 'people':

                $html->set_template(FORUM.'templates/viewforum/forum_users.html');
                // print_p('people setter now');
                $html->set_block('pagenav_top', ['pagenav' => $info['pagenav']]);
                $html->set_block('pagenav_bottom', ['pagenav' => $info['pagenav']]);
                $html->set_tag('person_title', $locale['forum_0018']);
                $html->set_tag('latest_thread_title', $locale['forum_0012']);
                $html->set_tag('activity_title', $locale['forum_0016']);

                if (!empty($info['item'])) {
                    foreach ($info['item'] as $user) {
                        $html->set_block('users_list', [
                            'avatar'        => display_avatar($user, '50px', '', '', ''),
                            'profile_link'  => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                            'thread_title'  => $user['thread_link']['title'],
                            'thread_link'   => $user['thread_link']['link'],
                            'activity_date' => showdate('forumdate', $user['post_datestamp']).", ".timer($user['post_datestamp'])
                        ]);
                    }
                } else {
                    $html->set_block('no_items');
                }


                break;

            case 'activity':
                $html->set_template(FORUM.'templates/viewforum/forum_activity.html');

                if (!empty($info['item'])) {
                    $html->set_block('pagenav_top', ['pagenav' => $info['pagenav']]);
                    $html->set_block('pagenav_bottom', ['pagenav' => $info['pagenav']]);
                    $html->set_block('activity', [
                        'post_count'         => format_word($info['max_post_count'], $locale['fmt_post']),
                        'last_activity_link' => "<a href='".$info['last_activity']['link']."'>".$locale['forum_0020']."</a>",
                        'last_activity_info' => sprintf($locale['forum_0021'],
                            showdate('forumdate', $info['last_activity']['time']),
                            profile_link($info['last_activity']['user']['user_id'], $info['last_activity']['user']['user_name'], $info['last_activity']['user']['user_status'])
                        )
                    ]);
                    $i = 0;
                    foreach ($info['item'] as $post_id => $postData) {
                        $html->set_block('activity_items', [
                            'spacing'      => (!$i ? " m-t-0" : ''),
                            'avatar'       => display_avatar($postData['post_author'], '50px', FALSE, '', ''),
                            'profile_link' => profile_link($postData['post_author']['user_id'], $postData['post_author']['user_name'], $postData['post_author']['user_status']),
                            'post_date'    => showdate('forumdate', $postData['post_datestamp']),
                            'post_timer'   => timer($postData['post_datestamp']),
                            'post_link'    => $locale['forum_0022']." <a href='".$postData['thread_link']['link']."'>".$postData['thread_link']['title']."</a>",
                            'thread_link'  => $locale['forum_0023']." ".$postData['thread_link']['title'],
                            'post_message' => parse_textarea($postData['post_message'], TRUE, TRUE, TRUE, IMAGES, TRUE),
                            //'post_link'    => "<a href='".$postData['thread_link']['link']."'>".$locale['forum_0024']."</a>"
                        ]);
                        $i++;
                    }
                } else {
                    $html->set_block('no_item', ['message' => $locale['forum_4121']]);
                }
                break;
        }

        $html->set_tag('forum_navs', $this->get_forum_navs());
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('title', $info['forum_name']);
        $html->set_tag('baselink', $info['link']);
        $html->set_tag('description', nl2br(parseubb($info['forum_description'])));

        if ($info['forum_rules']) {
            $html->set_block('rules', ['forum_rules' => alert("<span class='strong'><i class='fa fa-exclamation fa-fw'></i>".$locale['forum_0350']."</span> ".$info['forum_rules'])]);
        }

        $html->set_tag('can_access', sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));
        $html->set_tag('can_post', sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));
        $html->set_tag('can_create_poll', sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));
        $html->set_tag('can_upload_attach', sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));
        $html->set_tag('can_download_attach', sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"));

        $i = 0;
        foreach ($info['forum_page_link'] as $view_keys => $page_link) {
            $html->set_block('navbar_item', [
                'active' => ((!isset($_GET['view']) && (!$i)) || (isset($_GET['view']) && $_GET['view'] === $view_keys) ? " class='active'" : ''),
                'link'   => $page_link['link'],
                'title'  => $page_link['title']
            ]);
            $i++;
        }

        if (iMEMBER && $info['permissions']['can_post'] && !empty($info['new_thread_link'])) {
            $html->set_block('new_thread_link', [
                'new_thread_link_url'   => $info['new_thread_link']['link'],
                'new_thread_link_title' => $info['new_thread_link']['title']
            ]);
        }

        if ($info['forum_moderators']) {
            $html->set_block('moderator_list', ['moderators' => $info['forum_moderators'], 'mod_title' => $locale['forum_0185']]);
        }

        return (string)$html->get_output();
    }

    /**
     *  Main Forum Page - Recursive
     *
     * @param array $info
     * @param int   $id -   counter nth
     *                  Template File templates/index/forum_index.html
     *
     * @return string
     */
    public function forum_index(array $info = [], $id = 0) {
        $file_path = get_forum_template('forum');
        $html = Template::getInstance('forum');
        $html->set_locale($this->locale);
        $html->set_template($file_path);
        $html->set_css($this->css_file_path);
        $html->set_tag('baselink', FORUM);
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag("forum_navs", $this->get_forum_navs());
        // the header navigation
        if (!empty($info['forums'][$id])) {
            $html->set_block('new_thread_link', [
                'new_thread_link_url'   => $info['new_topic_link']['link'],
                'new_thread_link_title' => $info['new_topic_link']['title']
            ]);
            foreach ($info['forums'][$id] as $forum_id => $data) {
                $forum_image = ($data['forum_image'] ? thumbnail($data['forum_image'], '50px') : $data['forum_icon']);
                if ($data['forum_type'] == 1) {

                    $chtml = Template::getInstance('forum_su_index');
                    $chtml->set_template(__DIR__.'/../templates/index/forum_item.html');
                    $chtml->set_tag('forum_name', $data['forum_link']['title']);
                    $chtml->set_tag('forum_link', $data['forum_link']['link']);
                    $chtml->set_tag('forum_description', $data['forum_description']);
                    $chtml->set_tag('forum_image', $forum_image);
                    if (!empty($data['child'])) {
                        foreach ($data['child'] as $child_id => $child_data) {
                            $forum_image = ($child_data['forum_image'] ? thumbnail($child_data['forum_image'], '80px') : $child_data['forum_icon_lg']);
                            $forum_image = $child_data['last_post']['avatar'] ?: $forum_image;
                            $forum_subforums = '';
                            if (isset($child_data['child'])) {
                                $shtml = Template::getInstance('forum_su_child');
                                $shtml->set_template(__DIR__.'/../templates/index/forum_subforums.html');
                                foreach ($child_data['child'] as $sub_forum_id => $sub_forum_data) {

                                    $sub_forum_color = stringToColorCode($sub_forum_data['forum_name']);
                                    $font_color = get_brightness($sub_forum_color) > 130 ? '000' : 'fff';
                                    $sub_forum_image = ($sub_forum_data['forum_image'] ? thumbnail($sub_forum_data['forum_image'], '10px') :
                                        "<div class='img-circle m-t-5 m-b-10' style='background:#$sub_forum_color; color:#$font_color;padding: 0 5px;font-size:12px;margin-right:10px;'>".$sub_forum_data['forum_icon']."</div>");

                                    $shtml->set_block("forum_block", [
                                        "forum_link"  => $sub_forum_data['forum_link']['link'],
                                        "forum_title" => $sub_forum_data['forum_link']['title'],
                                        "forum_icon"  => $sub_forum_image
                                    ]);

                                }
                                $forum_subforums = $shtml->get_output();
                            }
                            $subforums = [
                                'forum_title'           => $child_data['forum_name'],
                                'forum_link'            => $child_data['forum_link']['link'],
                                'forum_description'     => $child_data['forum_description'],
                                "forum_image"           => $forum_image,
                                "forum_postcount"       => format_num($child_data['forum_postcount']),
                                "forum_threadcount"     => format_num($child_data['forum_threadcount']),
                                "lastpost_avatar"       => $child_data['last_post']['avatar_sm'],
                                "lastpost_profile_link" => $child_data['last_post']['profile_link'],
                                "lastpost_time"         => $child_data['last_post']['time'],
                                "lastpost_link"         => isset($child_data['last_post']['post_link']) ? $child_data['last_post']['post_link'] : "",
                                "lastpost_title"        => isset($child_data['last_post']['thread_subject']) ? stripinput($child_data['last_post']['thread_subject']) : "",
                                "forum_subforums"       => $forum_subforums
                            ];
                            $chtml->set_block('subforums_block', $subforums);
                        }
                    } else {
                        $chtml->set_block("no_subforums_block");
                    }
                    $html->set_block('forum_content', [
                            'forum_content' => $chtml->get_output()
                        ]
                    );
                }
            }
        } else {
            $html->set_block('no_item', ['message' => $this->locale['forum_0328']]);
        }

        $threadTags = Forum_Server::tag(TRUE, FALSE)->get_TagInfo();
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
            $html->set_block('tags_no_item', ['message' => $this->locale['forum_0274']]);
        }

        // Popular Threads this Week
        // An example that you can run core codes still in the template controller function
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
                    'p_profile_link' => $this->locale['by'].' '.profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                    'p_count'        => format_word($popular['thread_postcount'], $this->locale['fmt_post'])
                ]);
            }
        } else {
            $html->set_block('no_popular_threads', [
                'p_message' => $this->locale['forum_0275']
            ]);
        }
        $html->set_block('forum_panel', ['forum_panel' => $this->popular_contributor_panel()]);
        $html->set_tag('forum_panel', $this->sticky_discussions_panel());

        return $html->get_output();
    }

    public function popular_contributor_panel() {
        // Week, Month, Year, All Time
        $html = Template::getInstance('forum-contributor-list');
        $html_file = __DIR__.'/../templates/panel/contributor_panel.html';
        $html->set_template($html_file);
        $result = dbquery("SELECT post_author, COUNT(post_id) 'post_count'  FROM ".DB_FORUM_POSTS." WHERE
        post_datestamp BETWEEN :from_time AND :current_time AND post_author > 0
        GROUP BY post_author ORDER BY post_count DESC LIMIT 5
        ", [
            ':from_time'    => strtotime('-1 week'),
            ':current_time' => TIME,
        ]);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $default_user = [
                    'user_id'     => -1,
                    'user_status' => 0,
                    'user_level'  => USER_LEVEL_ADMIN,
                    'user_name'   => fusion_get_locale('forum_0666')
                ];
                $user = fusion_get_user($data['post_author']);
                if (empty($user)) {
                    $user = $default_user;
                }
                $html->set_block('contributor', [
                    'avatar'       => display_avatar($user, '35px', '', FALSE, 'img-circle'),
                    'profile_link' => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                    'post_count'   => number_format($data['post_count'], 0)
                ]);
                unset($user);
            }
        }
        add_to_jquery("
        $('#contributor-tab li a').bind('click', function(e){
            var i = $(this).data('value');
            var t = $(this).attr('href');
            // only if it is empty
           if( !$.trim( $(t).html() ).length ) {
                $.ajax({
                'url' : '".FORUM."templates/ajax/contributor.php',
                'dataType': 'html',
                'data' : {q:i},
                'method' : 'get',
                'beforeSend': function(e) {
                    $(t).html('Loading...');
                },
                'success': function(e) {
                    setTimeout(function(f){
                        $(t).html(e);
                    },300);
                },
                'error': function() {
                    console.log('error fetching data');
                }
                });
           }
        });
        ");

        return $html->get_output();
    }

    public function sticky_discussions_panel() {
        $html = Template::getInstance('forum-sticky-panel');
        $html->set_template(__DIR__.'/../templates/panel/sticky_panel.html');
        $result = dbquery("SELECT thread_id, thread_subject, thread_author, thread_lastpost, thread_postcount FROM ".DB_FORUM_THREADS."
        WHERE thread_sticky=1 ORDER BY thread_lastpost DESC LIMIT 5");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $user = fusion_get_user($data['thread_author']);
                if (!empty($user['user_id'])) {
                    $html->set_block('thread_block', [
                        'thread_link'    => FORUM.'viewthread.php?thread_id='.$data['thread_id'],
                        'post_count'     => number_format($data['thread_postcount'], 0),
                        'thread_subject' => $data['thread_subject'],
                        'avatar'         => display_avatar($user, '30px', '', '', 'img-circle'),
                        'profile_link'   => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                        'date'           => date('d M y', $data['thread_lastpost']),
                    ]);
                }
            }
        }

        return $html->get_output();
    }

    /**
     * Subforums page on Viewforum
     *
     * @param $info
     *
     * @return string
     */
    public function forum_subforums_item($info) {
        $file_path = get_forum_template('forums');
        $html = Template::getInstance('forum-subforum-item');
        $html->set_template($file_path);
        $html->set_tag("forum_name", $info['forum_link']['title']);
        $html->set_tag("forum_link", $info['forum_link']['link']);
        $html->set_tag("forum_description", $info['forum_description']);
        $output = $html->get_output();

        return $output;
    }

    /**
     * Thread view on viewthread.php
     *
     * @param $info
     *
     * @return string
     */
    public function render_thread($info) {
        $locale = fusion_get_locale();

        $file_path = get_forum_template('viewthreads');
        $html = Template::getInstance('viewthreads');
        $html->set_css($this->css_file_path);
        $html->set_template($file_path);
        $html->set_locale($locale);
        $html->set_tag("forum_navs", $this->get_forum_navs());
        $html->set_tag("baselink", FORUM);

        $html->set_tag("author_avatar", $info['thread']['thread_author']['user_avatar']);
        $html->set_tag("author_profile_link", $info['thread']['thread_author']['profile_link']);
        $html->set_tag("category_name", $info['forum_link']['title']);
        $html->set_tag("category_link", $info['forum_link']['link']);

        // Shorts in core
        $data = !empty($info['thread']) ? $info['thread'] : [];
        $pdata = !empty($info['post_items']) ? $info['post_items'] : [];
        // End inspection
        $html->set_tag('breadcrumb', render_breadcrumbs());
        // need to change to pagenav
        $html->set_block('pagenav_top', ['navigation' => $info['threads']['pagenav_top']]);
        if (isset($info['page_nav'])) {
            $html->set_block('pagenav_bottom', ['pagenav' => $info['pagenav']]);
        }
        // Icons
        $html->set_tag('sticky_icon', ($data['thread_sticky'] == TRUE ? "<i title='".$locale['forum_0103']."' class='".get_forum_icons("sticky")."'></i>" : ''));
        $html->set_tag('locked_icon', ($data['thread_locked'] == TRUE ? "<i title='".$locale['forum_0102']."' class='".get_forum_icons("lock")."'></i>" : ''));
        // Texts and Labels
        $html->set_tag('thread_subject', $data['thread_subject']);
        $html->set_tag('time_updated', $locale['forum_0363'].' '.timer($data['thread_lastpost']));
        if (!empty($info['thread_tags_display'])) {
            $html->set_block('thread_tags', ['tags' => $info['thread_tags_display']]);
        }
        if (!empty($info['poll_form'])) {
            $html->set_block('poll_form', ['poll' => $info['poll_form']]);
        }
        $html->set_tag('thread_search_filter', $info['thread_search_filter']);
        // Filters
        $filter_dropdown = '';
        if (!empty($info['post-filters'])) {
            foreach ($info['post-filters'] as $i => $filters) {
                $filter_dropdown .= "<li><a href='".$filters['value']."'>".$filters['locale']."</a></li>";
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

            if (!empty($info['buttons']['reply'])) {
                $html->set_block('reply_btn', [
                    'link_title' => $info['buttons']['reply']['title'],
                    'title'      => $info['buttons']['reply']['title'],
                    'link_url'   => $info['buttons']['reply']['link'],
                    'disabled'   => (empty($info['buttons']['reply']) ? "disabled" : ""),
                ]);
            }
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
            $i = 1;
            foreach ($pdata as $post_id => $post_data) {

                $post_items = $this->render_post_item($post_data, $i + (isset($_GET['rowstart']) ? $_GET['rowstart'] : ''));

                if ($post_id == $info['post_firstpost']) {
                    $html->set_block('post_firstpost_item', ['content' => $post_items]);
                    if ($info['permissions']['can_post']) {
                        if (!empty($info['buttons']['reply'])) {
                            $html->set_block('thread_info', [
                                'thread_post' => $info['thread_posts'],
                                'disabled'    => (empty($info['buttons']['reply']) ? 'disabled' : ''),
                                'link_url'    => $info['buttons']['reply']['link'],
                                'link_title'  => $info['buttons']['reply']['title'],
                                'title'       => $info['buttons']['reply']['title']
                            ]);
                        }
                        if ($info['thread_bounty']) {
                            $html->set_block('thread_bounty_info', [
                                'message' => $info['thread_bounty']
                            ]);
                        }
                    }
                } else {
                    $html->set_block('post_item', ['content' => $post_items]);
                }

                $i++;
            }
        }

        $html->set_tag('quick_reply_form', (!empty($info['quick_reply_form']) ? $info['quick_reply_form'] : ''));
        $html->set_tag('info_access', (sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>"));
        $html->set_tag('info_post', (sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>"));
        $html->set_tag('info_reply', (sprintf($locale['forum_perm_reply'], $info['permissions']['can_reply'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>"));
        $html->set_tag('info_edit_poll', ($data['thread_poll'] ? (sprintf($locale['forum_perm_edit_poll'], $info['permissions']['can_edit_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>") : ''));
        $html->set_tag('info_vote_poll', ($data['thread_poll'] ? (sprintf($locale['forum_perm_vote_poll'], $info['permissions']['can_vote_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"))."<br/>" : ''));
        $html->set_tag('info_create_poll', (!$data['thread_poll'] ? (sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>") : ''));
        $html->set_tag('info_upload', (sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>"));
        $html->set_tag('info_download', (sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>"));
        $html->set_tag('info_rate', ($data['forum_type'] == 4 ? (sprintf($locale['forum_perm_rate'], $info['permissions']['can_rate'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>") : ''));
        $html->set_tag('info_bounty', ($data['forum_type'] == 4 ? (sprintf($locale['forum_perm_bounty'], $info['permissions']['can_start_bounty'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>") : ''));
        $html->set_tag('info_moderators', ($info['forum_moderators'] ? "<div class='list-group-item m-b-20'><strong>".$locale['forum_0185']."</strong> ".$info['forum_moderators']."</div>" : ''));

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

        return $html->get_output();
    }

    public function displayQuickReply($info) {
        $file_path = get_forum_template('forum_qrform');
        $html = Template::getInstance('forum-quick-reply');
        $html->set_tag('description', $info['description']);
        $html->set_tag('message_field', $info['field']['message']);
        $html->set_tag('options_field', $info['field']['options']);
        if (!empty($info['field']['file_upload'])) {
            $html->set_block('file_upload', ['field' => $info['field']['file_upload']]);
        }
        // post attachments handling
        if (!empty($info['attachments'])) {
            $tpl = Template::getInstance('attach');
            $tpl->set_template(get_forum_template('forum_qr_attach'));
            foreach ($info['attachments'] as $attach) {
                $tpl->set_block('attachments', [
                    'attach_container_id'   => 'atc_'.$attach['attach_id'],
                    'image_path'            => FORUM.'attachments/'.$attach['attach_name'],
                    'image_name'            => $attach['attach_name'],
                    'thumbnail_insert_link' => '<a href="#" class="insert-image" data-size="thumbnail" data-id="'.$attach['attach_id'].'">Thumbnail</a>',
                    'sm_insert_link'        => ' <a href="#" class="insert-image" data-size="sm" data-id="'.$attach['attach_id'].'">Small</a>',
                    'md_insert_link'        => '<a href="#" class="insert-image" data-size="md" data-id="'.$attach['attach_id'].'">Medium</a>',
                    'lg_insert_link'        => '<a href="#" class="insert-image" data-size="md" data-id="'.$attach['attach_id'].'">Large</a>',
                    'fs_insert_link'        => '<a href="#" class="insert-image" data-size="fs" data-id="'.$attach['attach_id'].'">Fullsize</a>',
                    'remove_link'           => '<a href="#" class="insert-image" data-size="remove" data-id="'.$attach['attach_id'].'">Remove</a>',
                ]);
            }
            $html->set_block('attachment', [
                'photos' => $tpl->get_output()
            ]);
        }
        $html->set_tag('options_field', $info['field']['options']);
        $html->set_tag('button', $info['field']['button']);
        $html->set_template($file_path);

        return $html->get_output();
    }

    /**
     * Display the poll creation form
     * Template File    templates/forms/poll.html
     *
     * @param $info
     *
     * @return string
     */
    public function display_forum_pollform($info) {
        $file_path = get_forum_template('forum_pollform');
        $html = Template::getInstance('forum-poll');
        $html->set_template($file_path);
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['title']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('description', $info['description']);
        $html->set_tag('pollform', $info['field']['poll_field'].$info['field']['poll_button']);

        return $info['field']['openform'].$html->get_output().$info['field']['closeform'];
    }

    /**
     * @param $info
     *
     * @return string
     */
    public function render_postify($info) {
        $locale = fusion_get_locale();
        $file_path = get_forum_template('forum_postify');
        $html = Template::getInstance('forum-postify');

        $html->set_template($file_path);
        $html->set_locale($locale);
        $html->set_tag('forum_navs', $this->get_forum_navs());
        $html->set_tag('title', $info['title']);
        $html->set_tag('opentable', fusion_get_function('opentable', $info['title']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('alert_class', ($info['error'] ? "alert alert-danger" : ""));
        $html->set_tag('delay', 3);
        if (!empty($info['description'])) {
            $html->set_block('message', ['message' => $info['description']]);
        }
        foreach ($info['link'] as $link) {
            $html->set_block('links', ['link_url' => $link['url'], 'link_title' => $link['title']]);
        }

        add_to_jquery("
        var delay_sec = 4;
        var delay = setInterval(function(e) {
            var count = 1;
            var delay_left = delay_sec - count;
            delay_sec = delay_left;
            $('.delay').text(delay_left);
            if (delay_left == 0) {
                clearInterval(delay);
            }
        }, 1000);
        ");

        return (string)$html->get_output();
    }

    /**
     * Display the post reply form
     * Template File    templates/forms/post.html
     *
     * @param $info
     *
     * @return string
     */
    public function display_forum_postform($info) {

        $locale = fusion_get_locale();

        $html = Template::getInstance('forum-post-form');

        $html->set_template(get_forum_template('forum_postform'));

        $html->set_locale($locale);

        $html->set_tag("baselink", FORUM);

        $html->set_tag("forum_navs", $this->get_forum_navs());

        $html->set_tag('breadcrumb', render_breadcrumbs());

        $html->set_tag("title", $info['title']);

        $html->set_tag("openform", $info['openform']);

        $html->set_tag("closeform", $info['closeform']);

        $html->set_tag('opentable', fusion_get_function('opentable', $info['title']));

        $html->set_tag('closetable', fusion_get_function('closetable'));

        $html->set_tag('description', $info['description']);

        $html->set_tag('forum_field', $info['forum_field'].$info['forum_id_field'].$info['thread_id_field']);

        $html->set_tag('forum_subject_field', $info['subject_field']);

        $html->set_tag('forum_tag_field', $info['tags_field']);

        $html->set_tag('forum_message_field', $info['message_field']);

        $html->set_tag('forum_edit_reason_field', $info['edit_reason_field']);

        $html->set_tag('forum_poll_form', $info['poll_form']);

        $html->set_tag("delete", $info['delete_field']);

        $html->set_tag("sticky", $info['sticky_field']);

        $html->set_tag("notify", $info['notify_field']);

        $html->set_tag("lock", $info['lock_field']);

        $html->set_tag("hide_edit", $info['hide_edit_field']);

        $html->set_tag("smiley", $info['smileys_field']);

        $html->set_tag("user_sig", $info['signature_field']);

        $html->set_tag('forum_post_button', $info['post_buttons']);

        //$html->set_tag('preview_box', $info['preview_box']);
        if (!empty($info['attachment_field'])) {
            $html->set_block("forum_attachment", ["field" => $info['attachment_field']]);
        }

        if (!empty($info['last_posts_reply'])) {
            $html->set_block('lastpost', ['post_items' => $info['last_posts_reply']]);
        }

        return $html->get_output();
    }

    /**
     * Display the bounty creation form
     * Instance name        forum-bounty-form
     * Template File        templates/forms/bounty.html
     *
     * @param $info
     *
     * @return string
     */
    public function display_forum_bountyform($info) {
        $file_path = get_forum_template('forum_bountyform');
        $html = Template::getInstance('forum-bounty-form');
        $html->set_template($file_path);
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag("forum_navs", $this->get_forum_navs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['title']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('description', $info['description']);
        $html->set_tag('bountyform', $info['field']['bounty_select'].$info['field']['bounty_description'].$info['field']['bounty_button']);

        return $info['field']['openform'].$html->get_output().$info['field']['closeform'];
    }

    public function display_forum_tags($info) {
        $locale = fusion_get_locale();
        //$tag_file_path = FORUM.'templates/forum_tags.html';
        // $tag_threads_file_path = FORUM.'templates/forum_tag_threads.html';

        if (get('tag_id', FILTER_VALIDATE_INT)) {

            $html = Template::getInstance('forum-tags-threads');
            $html->set_template( get_forum_template('forum_tag_threads')); //$tag_threads_file_path
            $html->set_locale($locale);
            $html->set_tag("baselink", FORUM);
            $html->set_tag("title", $info['title']);
            $html->set_tag("description", $info['description']);
            $html->set_tag("forum_navs", $this->get_forum_navs());
            $html->set_tag('breadcrumb', render_breadcrumbs());
            if (!empty($info['filters']['type'])) {
                foreach ($info['filters']['type'] as $key => $tabs) {
                    $html->set_block('tab_filter', [
                        'filter_link' => $tabs['link'],
                        'active_text' => $tabs['active'] ? " text-active" : "",
                        'title'       => $tabs['icon'].$tabs['title'],
                        'count'       => $tabs['count']
                    ]);
                }
            }

            $html->set_tag('forum_filter', forum_filter($info));

            if (!empty($info['threads']['pagenav_top'])) {
                $html->set_block('pagenav_top', ['navigation' => $info['threads']['pagenav_top']]);
            }

            if (!empty($info['threads']['pagenav'])) {
                $html->set_block('pagenav_bottom', ['navigation' => $info['threads']['pagenav']]);
            }

            if (!empty($info['threads_time_filter'])) {
                $html->set_block('filter_dropdown', ["content" => $info['threads_time_filter']]);
            }
            $html->set_tag("section_content", $this->forum_threads_item($info));

            return $html->get_output();

        } else {

            $html = Template::getInstance('forum-tags');
            $html->set_template(get_forum_template('forum_tags'));
            $html->set_locale($locale);
            $html->set_tag("baselink", FORUM);
            $html->set_tag("forum_navs", $this->get_forum_navs());
            $html->set_tag('breadcrumb', render_breadcrumbs());
            if (!empty($info['tags'])) {
                unset($info['tags'][0]);
                foreach ($info['tags'] as $tag_id => $tag_data) {
                    $html->set_block('tag_block', [
                        'tag_color'           => $tag_data['tag_color'],
                        'tag_link'            => $tag_data['tag_link'],
                        'tag_title'           => $tag_data['tag_title'],
                        'tag_description'     => $tag_data['tag_description'],
                        'thread_subject'      => (!empty($tag_data['threads']['thread_subject']) ? trim_text($tag_data['threads']['thread_subject'], 100) : ''),
                        'thread_link'         => (!empty($tag_data['threads']['thread_link'])) ? $tag_data['threads']['thread_link'] : '',
                        'thread_profile_link' => (!empty($tag_data['threads']['thread_profile_link'])) ? $tag_data['threads']['thread_profile_link'] : '',
                        'thread_avatar'       => (!empty($tag_data['threads']['thread_avatar'])) ? $tag_data['threads']['thread_avatar'] : '',
                        'thread_activity'     => (!empty($tag_data['threads']['thread_lastpost']) ? timer($tag_data['threads']['thread_lastpost']) : ''),
                    ]);
                }
            } else {
                $html->set_block('no_tag', ['message' => $locale['forum_0276']]);
            }

            return $html->get_output();
        }
    }

}
