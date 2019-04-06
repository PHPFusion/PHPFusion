<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: threads/forum_reports.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Infusions\Forum\Classes\Threads;

/**
 * Class Forum_Reports
 *
 * @package PHPFusion\Infusions\Forum\Classes\Threads
 */
class Forum_Reports {

    public static function render_report_form() {
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();
        if (iMEMBER) {
            $comment_opts = [
                1 => "This is Abusive or Harassing",
                2 => "This is Spam",
                3 => "It breaks Forum Rules",
                4 => "It breaks the Code of Conduct"
            ];
            $comment2_opts = [
                1 => "It infringes my copyright",
                2 => "It infringes my trademark rights",
                3 => "It's personal and confidential information",
                4 => "It's sexual or suggestive content involving minors",
                5 => "It's involuntary pornography",
                6 => "It's a transaction for prohibited goods or services"
            ];

            // Share Copy Link Action
            $exit_uri = clean_request("", ["report", "rtid", "pid"], FALSE);

            if (isset($_POST['submit_report'])) {

                $selected_comments = $comment_opts;
                if ($_POST['report_type'] == 2) {
                    $selected_comments = $comment2_opts;
                }
                $report_comment = "";
                $report_reason = form_sanitizer($_POST['report_reason'], "", "report_reason");
                if (isset($selected_comments[$report_reason])) {
                    $report_comment = $selected_comments[$report_reason];
                } else {
                    \Defender::stop();
                    addNotice("danger", "Your report selection could not be determined. Please try again.");
                    redirect($exit_uri);
                }

                if (isset($_GET['rtid']) && isnum($_GET['rtid']) && $post_id = dbcount("(post_id)", DB_FORUM_POSTS, "post_id=:pid", [":pid" => intval($_GET['rtid'])])) {
                    // Redirect if user has reported on this earlier
                    if (dbcount("(report_id)", DB_FORUM_REPORTS, "report_user=:uid AND post_id=:rtid AND report_status=0", [':uid' => $userdata['user_id'], ":rtid" => intval($_GET['rtid'])])) {
                        \Defender::stop();
                        addNotice("danger", "There are open reports that you  made on this post earlier. Please wait for moderator to review the post.");
                        redirect($exit_uri);
                    }
                    $rdata = [
                        "report_id"        => 0,
                        "post_id"          => intval($_GET['rtid']),
                        "report_user"      => intval($userdata['user_id']),
                        "report_comment"   => stripinput($report_comment),
                        "report_datestamp" => TIME,
                        "report_updated"   => TIME,
                    ];
                    if (\Defender::safe()) {
                        dbquery_insert(DB_FORUM_REPORTS, $rdata, "save");
                        addNotice("success", "Your feedback has been received. Thank you.");
                        redirect($exit_uri);
                    }
                }

            }

            // in the info we can see the thread.
            if (isset($_GET['report']) && $_GET['report'] == "true" && isset($_GET['rtid']) && isnum($_GET['rtid'])) {

                //$rep_thread_id = $_GET['rtid'];
                // get the first post.
                // the post id
                $policy_link = "";
                $rules_link = "";
                // Ajax Report Action
                add_to_jquery("
                $('[data-toggle]').on('click', function(e) {
                    var selected = $(this).attr('href');
                    var value = 1;
                    if (selected == '#repb-report_coll') {
                        value = 2;
                    }
                    $('#report_type').val(value);
                });
                ");
                // let us do a hidden input here, and see which tab is active.
                $modal = openmodal("report_frm", "<h4 class='strong m-0'>Report Thread</h4>", ["class" => "modal-md", "static" => TRUE]);
                $modal .= "<h5 class='p-15 p-t-0 strong'>We are sorry something is wrong. How can we help?</h5>";
                $modal .= "<div class='p-0'>\n";
                $modal .= openform("report_frm", "post", FUSION_REQUEST);
                $modal .= form_hidden("report_type", "", "", ["deactivate" => TRUE]);
                $modal .= opencollapse("report_coll");
                $modal .= opencollapsebody("It's spam or abuse", "repa", "report_coll", FALSE);
                $modal .= form_select("report_reason", "", "", ["options"     => $comment_opts,
                                                                "width"       => "100%",
                                                                "inner_width" => "100%",
                                                                "allowclear"  => TRUE,
                                                                "class"       => "m-t-15"
                ]);
                $modal .= closecollapsebody();
                $modal .= opencollapsebody("Other issues", "repb", "report_coll", FALSE);
                $modal .= form_checkbox("report_reason", "", "", ["options"     => $comment2_opts,
                                                                  "width"       => "100%",
                                                                  "inner_width" => "100%",
                                                                  "class"       => "m-t-5 text-sm",
                                                                  "type"        => "radio",
                                                                  "allowclear"  => TRUE
                ]);
                $modal .= closecollapsebody();
                $modal .= "</div>\n";
                $modal .= modalfooter("<div class='display-inline-block pull-left'><small>Please read the
                <a class='strong' href='$policy_link'>PHP-Fusion Content Policy</a> and the
                <a class='strong' href='$rules_link'>Forum Rules</a>.</small></div>
                ".form_button("submit_report", "Submit Report", "submit_report", ["class" => "btn btn-primary"])."<a class='btn btn-default' href='".clean_request("", ["report", "rtid"], FALSE)."'>".$locale['close']."</a>",
                    FALSE);
                $modal .= closeform();
                $modal .= closemodal();
                add_to_footer($modal);
            }
        }
    }

    /**
     * Displays a Single Report
     *
     * @return string
     * @throws \Exception
     */
    public static function display_report() {
        $locale = fusion_get_locale();
        $tpl = \PHPFusion\Template::getInstance('forum_reports');
        $tpl->set_locale(fusion_get_locale());
        add_to_title($locale['global_201']."Post Moderation");
        add_breadcrumb([
            "link"  => FORUM."index.php?ref=moderator",
            "title" => "Forum Reports",
        ]);
        /*
         * Info modelling
         */
        $section_type = [
            "active" => [
                "title"            => "Active Reports",
                "link"             => FORUM."index.php?ref=moderator&amp;type=active",
                "active"           => "",
                "dropdown"         => "",
                "dropdown_toggle"  => "",
                "dropdown_content" => "",
            ],
            "closed" => [
                "title"            => "Closed Reports",
                "link"             => FORUM."index.php?ref=moderator&amp;type=closed",
                "active"           => "",
                "dropdown"         => "",
                "dropdown_toggle"  => "",
                "dropdown_content" => "",
            ],
            "search" => [
                "title"            => "Find Reports <i class='caret'></i>",
                "link"             => FORUM."index.php?ref=moderator&amp;type=search",
                "active"           => "",
                "dropdown"         => " class='dropdown'",
                "dropdown_toggle"  => " data-toggle='dropdown' class='dropdown-toggle'",
                "dropdown_content" => "<div class='dropdown-menu p-15' style='min-width:250px;'>".openform("reportsearchfrm", "post", FUSION_REQUEST).form_text("users", "Find reports for member", "", ['required' => TRUE, "placeholder" => "Enter user name"]).form_button("search_users", "Search", "search_users", [
                        'icon'  => "fas fa-search",
                        "class" => "btn btn-primary pull-right"
                    ]).closeform()."</div>\n",
            ]
        ];

        $_GET['type'] = isset($_GET['type']) && isset($section_type[$_GET['type']]) ? $_GET['type'] : "active";
        foreach ($section_type as $key => $options) {
            if ($_GET['type'] == $key) {
                $tpl->set_block("section_block", ["title" => $options['title']]);
                $options['active'] = " class='active'";
            }
            $tpl->set_block("moderate_options", $options);
        }

        $report_status = [
            0 => "New",
            1 => "Rejected",
            2 => "Resolved",
        ];

        $report_action = [
            0 => "Do not change",
            1 => "Delete Post",
            2 => "Delete and Ban",
            3 => "Rejected"
        ];

        if (!empty($threads['threads']['item'])) {
            $slice = array_slice($threads['threads']['item'], 0, 1);
            $data = array_shift($slice);

            add_breadcrumb([
                "link"  => FORUM."index.php?ref=moderator&amp;id=".$data['report_id'],
                "title" => "Post in '".$data['thread_subject']."'",
            ]);

            $rdata = [
                "report_alerts"  => 0,
                "report_comment" => "",
                "report_actions" => 0,
            ];

            if (isset($_POST['report_submit'])) {
                // toggle if closed or open

                $rdata = [
                    "report_alerts"  => isset($_POST['report_alerts']) ? 1 : 0,
                    "report_comment" => form_sanitizer($_POST['report_comment'], "", "report_comment"),
                    "report_actions" => form_sanitizer($_POST['report_actions'], "", "report_actions")
                ];
                if (\Defender::safe()) {
                    $first_postid = dbresult(dbquery("SELECT MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid", [':tid' => $data['thread_id']]), 0);
                    switch ($rdata['report_actions']) {
                        // check whether it is a first post.
                        case "1": // Delete Post
                            if ($data['post_id'] === $first_postid) {
                                dbquery("UPDATE ".DB_FORUM_POSTS." SET post_author=:aid, post_message=:removed WHERE post_id=:pid", [
                                    ":aid"     => -1,
                                    ":removed" => $locale['forum_0666'],
                                    ":pid"     => $data['post_id']
                                ]);
                            } else {
                                dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_id=:id", [":id" => $data['post_id']]);
                            }

                            \PHPFusion\Infusions\Forum\Classes\Forum_Moderator::refresh_forum($data['forum_id']);
                            \PHPFusion\Infusions\Forum\Classes\Forum_Moderator::refresh_thread($data['thread_id']);
                            // set the issue as resolved.
                            dbquery("UPDATE ".DB_FORUM_REPORTS." SET report_status=:status, report_comment=:msg, report_updated=:updated, report_archive=:data  WHERE post_id=:id", [
                                ":id"      => intval($data['post_id']),
                                ":status"  => 2,
                                ":msg"     => $rdata['report_comment'],
                                ":updated" => TIME,
                                ":data"    => \Defender::encode($data)
                            ]);
                            break;
                        case 2: // Delete Post and Ban Account
                            if ($data['post_id'] === $first_postid) {
                                dbquery("UPDATE ".DB_FORUM_POSTS." SET post_author=:aid, report_comment=:removed WHERE post_id=:pid", [
                                    ":aid"     => -1,
                                    ":removed" => $locale['forum_0666'],
                                    ":pid"     => $data['post_id']
                                ]);
                            } else {
                                dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_id=:id", [":id" => $data['post_id']]);
                            }
                            dbquery("UPDATE ".DB_USERS." SET user_status=1 WHERE user_id=:aid", [":aid" => $data['post_author']]);
                            \PHPFusion\Infusions\Forum\Classes\Forum_Moderator::refresh_forum($data['forum_id']);
                            \PHPFusion\Infusions\Forum\Classes\Forum_Moderator::refresh_thread($data['thread_id']);
                            // set the issue as resolved.
                            dbquery("UPDATE ".DB_FORUM_REPORTS." SET report_status=:status, report_comment=:msg, report_updated=:updated, report_archive=:data WHERE post_id=:id", [
                                ":id"      => intval($data['post_id']),
                                ":status"  => 2,
                                ":msg"     => $rdata['report_comment'],
                                ":updated" => TIME,
                                ":data"    => \Defender::encode($data)
                            ]);
                            break;
                        case 3: // Rejected the report - nobody can report on this post again.
                            dbquery("UPDATE ".DB_FORUM_REPORTS." SET report_status=:status, report_comment=:msg, report_updated=:updated WHERE post_id=:id", [
                                ":id"      => intval($data['post_id']),
                                ":status"  => 1,
                                ":msg"     => $rdata['report_comment'],
                                ":updated" => TIME,
                            ]);
                            break;
                        default:
                            // set as resolved, but other reports will still be active.
                            dbquery("UPDATE ".DB_FORUM_REPORTS." SET report_status=:status, report_comment=:msg, report_updated=:updated, report_archive=:data WHERE report_id=:id", [
                                ":id"      => intval($_GET['id']),
                                ":status"  => 2,
                                ":msg"     => $rdata['report_comment'],
                                ":updated" => TIME,
                                ":data"    => \Defender::encode($data)
                            ]);
                    }
                    addNotice("success", "Post moderation has been completed successfully.");
                    redirect(clean_request("", ["id"], FALSE));
                }
            }

            $tpl->set_tag("title", $data['thread_subject']);
            // post replacement
            $post_user = fusion_get_user($data['post_author']);
            $post_message = $data['post_message'];

            if ($data['report_status'] > 0 && !empty($data['report_archive'])) {
                $arc_data = \Defender::decode($data['report_archive']);
                $post_message = $arc_data['post_message'];
                $post_user = fusion_get_user($arc_data['post_author']);
            }

            if (empty($post_user)) {
                $post_user = [
                    "user_id"         => 0,
                    "user_name"       => $locale['forum_0667'],
                    "user_status"     => USER_LEVEL_ADMIN,
                    "user_avatar"     => IMAGES."avatars/noavatar50.png",
                    "user_level"      => USER_LEVEL_ADMIN,
                    "user_posts"      => '0',
                    "user_reputation" => 0,
                ];
            }

            $tpl->set_tag("profile_link", profile_link($post_user['user_id'], $post_user['user_name'], $post_user['user_status']));
            $tpl->set_tag("avatar", display_avatar($post_user, "50px", "", FALSE, "", "img-rounded"));
            $tpl->set_tag("user_level", getuserlevel($post_user['user_level']));
            $tpl->set_tag("user_post_count", $post_user['user_posts']);
            $tpl->set_tag("user_reputation", $post_user['user_reputation']);
            $tpl->set_tag("post_message", parse_textarea($post_message, $data['post_smileys'], TRUE, FALSE, IMAGES, TRUE));
            $tpl->set_tag("forum_name", $data['forum_name']);
            $tpl->set_tag("forum_link", FORUM."index.php?viewforum=true&amp;forum_id=".$data['forum_id']);
            $tpl->set_tag("post_url", FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['post_id']);
            $tpl->set_tag("post_date", showdate("forumdate", $data['post_datestamp']));
            $tpl->set_tag("report_count", format_num($data['report_count']));
            $tpl->set_tag("report_status", $report_status[$data['report_status']]);
            $tpl->set_tag("update_datestamp", showdate("forumdate", $data['report_updated']));
            if ($data['report_status'] > 0) {
                $tpl->set_block("report_comment", [
                        "comment" => "<strong>".$report_status[$data['report_status']]."</strong> - ".nl2br($data['report_comment'])
                    ]
                );
            }
            $tpl->set_tag("comments", \PHPFusion\Feedback\Comments::getInstance(
                [
                    'comment_item_type'     => "FR",
                    'comment_db'            => DB_FORUM_REPORTS,
                    'comment_col'           => "report_id",
                    'comment_item_id'       => intval($_GET['id']),
                    'clink'                 => FORUM."index.php?ref=moderator&amp;id=".intval($_GET['id']),
                    'comment_echo'          => FALSE,
                    'comment_allow_subject' => FALSE,
                    'comment_allow_ratings' => FALSE
                ], "_FR_".intval($_GET['id']))->showComments());

            if ($data['report_status'] == 0) {
                $tpl->set_block("report_open", [
                    "openform"          => openform("mdrfrm", "post", FUSION_REQUEST),
                    "closeform"         => closeform(),
                    "checkbox"          => form_checkbox("report_alerts", "Send resolution/rejection alert", $rdata['report_alerts'], ["reverse_label" => TRUE]),
                    "moderator_message" => form_textarea("report_comment", "", $rdata['report_comment']),
                    "moderator_actions" => form_checkbox("report_actions", "Status", $rdata['report_actions'], ["type" => "radio", "options" => $report_action, "inline" => TRUE]),
                    "submit"            => form_button("report_submit", "Close Report", "", ["class" => "btn btn-primary"]),
                ]);
            }
        } else {
            redirect(clean_request("", ["id", "c_start"], FALSE));
        }

        $tpl->set_template(__DIR__.'/../../templates/forum_reports.html');

        return $tpl->get_output();
    }

    // Moderation Threads Viewer
    public function render_modpost($info) {
        $locale = fusion_get_locale();
        $tpl = \PHPFusion\Template::getInstance('forum_modpost');
        $tpl->set_locale($locale);

        if (!empty($info['threads']['item'])) {
            foreach ($info['threads']['item'] as $cdata) {
                $tpl->set_block('threads', [
                    'thread_id'              => $cdata['thread_id'],
                    'thread_link_url'        => FORUM.'index.php?ref=moderator&amp;id='.$cdata['report_id'],
                    'thread_link_title'      => $cdata['thread_link']['title'],
                    'thread_icons'           => implode('', $cdata['thread_icons']),
                    'thread_pages'           => $cdata['thread_pages'],
                    'author_profile_link'    => $cdata['thread_starter']['profile_link'],
                    'thread_text'            => parse_textarea($cdata['post_message'], $cdata['post_smileys'], TRUE, FALSE, IMAGES, TRUE),
                    'thread_attachments'     => $cdata['post_attachments'],
                    'last_activity_time'     => timer($cdata['thread_last']['time']),
                    'last_acitivty_date'     => showdate('forumdate', $cdata['thread_last']['time']),
                    'thread_views'           => number_format($cdata['thread_views']),
                    'thread_postcount'       => number_format($cdata['thread_postcount']),
                    'thread_votecount'       => number_format($cdata['vote_count']),
                    'thread_views_word'      => format_word($cdata['thread_views'], 'view|views'),
                    'thread_postcount_word'  => format_word($cdata['thread_postcount'], 'post|posts'),
                    'thread_votecount_word'  => format_word($cdata['vote_count'], 'vote|votes'),
                    'avatar'                 => $cdata['thread_last']['avatar'],
                    'rank'                   => $cdata['thread_last']['user']['user_rank'],
                    'last_user_profile_link' => $cdata['thread_last']['profile_link'],
                    'last_user_avatar'       => $cdata['thread_last']['avatar'],
                    'track_button'           => (isset($cdata['track_button']) ? "<a class='btn btn-danger btn-sm' ".$cdata['track_button']['onclick']." href='".$cdata['track_button']['link']."'>".$cdata['track_button']['title']."</a>" : ''),
                    "track_link"             => $cdata['track_button']['link'],
                    "track_title"            => $cdata['track_button']['title'],
                    "track_onclick"          => $cdata['track_button']['onclick']
                ]);
            }
        } else {
            $tpl->set_block('no_item', ['message' => $locale['forum_0269']]);
        }
        $tpl->set_template(__DIR__.'/templates/mod_threads.html');

        return $tpl->get_output();
    }

}
