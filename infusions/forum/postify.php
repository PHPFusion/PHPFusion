<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/postify.php
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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";

if (!db_exists(DB_FORUMS)) {
    redirect(BASEDIR."error.php?code=404");
}

require_once INFUSIONS."forum/infusion_db.php";
require_once FORUM_CLASS."autoloader.php";
require_once THEMES."templates/header.php";
require_once INFUSIONS."forum/classes/mods.php";
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."forum/templates/forum_main.php";

$locale = fusion_get_locale('', FORUM_LOCALE);
$settings = fusion_get_settings();
$forum_settings = get_settings('forum');

add_to_title($locale['global_204']);

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(
    [
        'link'=>FORUM.'index.php',
        'title' => $locale['forum_0000']
    ]
);

$title = '';
$description = '';
$errorb = '';
$_GET['error'] = (!empty($_GET['error']) && isnum($_GET['error']) && $_GET['error'] <= 6 ? $_GET['error'] : 0);

if (!isset($_GET['forum_id'])) {
    throw new \Exception($locale['forum_0587']);
}

if (!isset($_GET['thread_id'])) {
    throw new \Exception($locale['forum_0588']);
}

$base_redirect_link = INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id'];

if (!empty($_GET['error'])) {
    switch($_GET['error']) {
        case 1:
            // Attachment file type is not allowed
            $errorb = $locale['forum_0540'];
            break;
        case 2:
            // Invalid attachment of filesize
            $errorb = $locale['forum_0541'];
            break;
        case 3:
            // Error: You did not specify a Subject and/or Message
            $errorb = $locale['forum_0542'];
            break;
        case 4:
            // Error: Your cookie session has expired, please login and repost
            $errorb = $locale['forum_0551'];
        case 5:
            // This post is locked. Contact the moderator for further information.
            $errorb = $locale['forum_0555'];
            break;
        case 6:
            // You may only edit a post for %d minute(s) after initial submission.
            $errorb = sprintf($locale['forum_0556'], $forum_settings['forum_edit_timelimit']);
            break;
    }
}

$valid_get = array("on", "off", "new", "reply", "edit", "newpoll", "editpoll", "deletepoll", "voteup", "votedown");
$valid_get = array_flip($valid_get);
if (!iMEMBER || !isset($valid_get[$_GET['post']])) {
    if (fusion_get_settings("site_seo")) {
        redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
    }
    redirect(FORUM."index.php");
}

/*
 * Voting on Post
 */
if ($_GET['post'] == 'voteup' or $_GET['post'] == 'votedown') {

    //@todo: extend on user's rank threshold before can vote. - Reputation threshold- Roadmap 9.1
    $thread_info = \PHPFusion\Forums\ForumServer::thread()->get_threadInfo();

    if ($thread_info['permissions']['can_rate']) {
        // init vars
        $data = array(
            'forum_id' => $thread_info['forum_id'],
            'thread_id' => $thread_info['thread_id'],
            'post_id' => $thread_info['post_id'],
            'vote_user' => $userdata['user_id'],
            'vote_datestamp' => time(),
        );
        if ($_GET['post'] == 'voteup') {
            $data['vote_points'] = 1;
        } elseif ($_GET['post'] == 'votedown') {
            $data['vote_points'] = -1;
        }
        $res = dbcount("('vote_user')", DB_FORUM_VOTES,
                       "vote_user='".intval($userdata['user_id'])."' AND thread_id='".intval($data['thread_id'])."'");
        if (!$res) { // has not voted
            $self_post = dbcount("('post_id')", DB_FORUM_POSTS,
                                 "post_id='".intval($data['post_id'])."' AND post_user='".$userdata['user_id']."");
            if (!$self_post) { // cannot vote at your own post.
                //print_p($data);
                dbquery_insert(DB_FORUM_VOTES, $data, 'save', array('noredirect' => 1, 'no_unique' => 1));
                addNotice('success', $locale['forum_0803']);
                // lock thread if point threshold reached on that specific post id.
                if ($thread_info['thread']['forum_answer_threshold'] > 0) { // if is 0, is unlimited and do nothing.
                    $vote_points_sql = "SELECT SUM('vote_points'), thread_id FROM ".DB_FORUM_VOTES." WHERE post_id = '".intval($data['post_id'])."'";
                    $vote_result = dbquery($vote_points_sql);
                    $v_data = dbarray($vote_result);
                    if ($v_data['vote_points'] >= $thread_info['thread']['forum_answer_threshold']) {
                        $update_sql = "UPDATE ".DB_FORUM_THREADS." SET 'thread_locked'='1', thread_answered='1' WHERE thread_id='".intval($v_data['thread_id'])."'";
                        $result = dbquery($update_sql);
                    }
                }
            } else {
                addNotice('danger', $locale['forum_0802']);
            }
        } else {
            addNotice('danger', $locale['forum_0801']);
        }
        redirect(INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id']."&amp;post_id=".$data['post_id']);
    }
}
/*
 * Tracking on or off
 */
elseif (($_GET['post'] == "on" || $_GET['post'] == "off") && $forum_settings['thread_notify']) {
    $output = FALSE;

    $access_sql = "SELECT tt.*, tf.forum_access, tf.forum_cat
    FROM ".DB_FORUM_THREADS." tt
    INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
    WHERE tt.thread_id='".intval($_GET['thread_id'])."'";

    $result = dbquery($access_sql);
    if (dbrows($result) > 0) {
        $data = dbarray($result);
        if (checkgroup($data['forum_access'])) {
            $output = TRUE;
            $notify_sql = array(
                "on" => "INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status)
                VALUES ('".$_GET['thread_id']."', NOW(), '".$userdata['user_id']."', '1')",
                "off" => "DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'"
            );
            if ($_GET['post'] == "on" &&
                !dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'")) {
                dbquery($notify_sql['on']);
                $description = $locale['forum_0553'];
            } elseif (isset($_GET['post']) && $_GET['post'] == 'off') {
                dbquery($notify_sql['off']);
                $description = $locale['forum_0554'];
            }
            redirect(INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id'], 3);
        }
    }
    if ($output == FALSE) {
        if (fusion_get_settings("site_seo")) {
            redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
        }
        redirect(INFUSIONS."forum/index.php");
    }

    $link[] = ['url'=>INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id'], 'title' => $locale['forum_0548']];
    $link[] = ['url'=>INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_GET['forum_id'], 'title' => $locale['forum_0549']];
    $link[] = ['url'=>INFUSIONS."forum/index.php", 'title' => $locale['forum_0550']];
    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(
        [
            'link'=> FUSION_REQUEST,
            'title' => $locale['forum_0552']
        ]
    );
    render_postify([
                       'title' => $locale['forum_0552'],
                       'description' => $description,
                       'error' => $errorb,
                       'link' => $link
                   ]);
}
/*
 * Submit New Thread
 */
elseif ($_GET['post'] == "new") {
    add_to_title($locale['global_201'].$locale['forum_0501']);
    if ($errorb) {
        $description = $errorb;
    } else {
        $description = $locale['forum_0543'];
    }
    if ($_GET['error'] < 3) {
        if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
            addNotice("danger", "URL Error");
            if (fusion_get_settings("site_seo")) {
                redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
            }
            redirect(INFUSIONS."forum/index.php");
        }
        $link[] = ['url'=>INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id'], 'title'=>$locale['forum_0548']];
        redirect(INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id'], 3);
    }
    $link[] = ['url'=>INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_GET['forum_id'], 'title'=>$locale['forum_0549']];
    $link[] = ['url'=>INFUSIONS."forum/index.php", 'title'=>$locale['forum_0550']];
    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(
        [
            'link'=> FUSION_REQUEST,
            'title' => $locale['forum_0501']
        ]
    );
    render_postify([
                       'title' => $locale['forum_0501'],
                       'message' => $description,
                       'error' => $errorb,
                       'link' => $link,
                   ]);
}
/*
 * When Submitting a Reply
 */
elseif ($_GET['post'] == "reply") {

    $post_sql = "SELECT tf.*, f.forum_id, f.forum_cat
    FROM ".DB_FORUM_THREADS." tf INNER JOIN ".DB_FORUMS." f ON tf.forum_id=f.forum_id
    WHERE thread_id='".intval($_GET['thread_id'])."'";

    $data = dbarray(dbquery($post_sql));
    add_to_title($locale['global_201'].$locale['forum_0503']);
    if ($errorb) {
        $description = $errorb;
    } else {
        $description = $locale['forum_0544'];
    }
    if ($_GET['error'] < "2") {
        if (!isset($_GET['post_id']) || !isnum($_GET['post_id'])) {
            throw new \Exception('$_GET[ post_id ] is blank, and not passed! Please report this.');
        }
        if ($forum_settings['thread_notify']) {
            $result = dbquery("SELECT tn.*, tu.user_id, tu.user_name, tu.user_email, tu.user_level, tu.user_groups
				FROM ".DB_FORUM_THREAD_NOTIFY." tn
				LEFT JOIN ".DB_USERS." tu ON tn.notify_user=tu.user_id
				WHERE thread_id='".intval($_GET['thread_id'])."' AND notify_user !='".intval($userdata['user_id'])."' AND notify_status='1'
			");
            if (dbrows($result) > 0) {
                require_once INCLUDES."sendmail_include.php";
                $data2 = dbarray(
                    dbquery("SELECT tf.forum_access, tt.thread_subject
					FROM ".DB_FORUM_THREADS." tt
					INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=tt.forum_id
					WHERE thread_id='".intval($_GET['thread_id'])."'")
                );
                $link = $settings['siteurl']."infusions/forum/viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."&pid=".$_GET['post_id']."#post_".$_GET['post_id'];
                $template_result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='POST' LIMIT 1");
                if (dbrows($template_result) > 0) {
                    $template_data = dbarray($template_result);
                    if ($template_data['template_active'] == "1") {
                        while ($data = dbarray($result)) {
                            if ($data2['forum_access'] == 0 || in_array($data2['forum_access'], explode(".", $data['user_level'].".".$data['user_groups']))
                            ) {
                                sendemail_template("POST", $data2['thread_subject'], "", "", $data['user_name'], $link, $data['user_email']);
                            }
                        }
                    } else {
                        while ($data = dbarray($result)) {
                            if ($data2['forum_access'] == 0 || in_array($data2['forum_access'], explode(".", $data['user_level'].".".$data['user_groups']))
                            ) {
                                $message_subject = str_replace("{THREAD_SUBJECT}", $data2['thread_subject'], $locale['forum_0660']);
                                $message_content = strtr($locale['forum_0661'], array(
                                    '{USERNAME}' => $data['user_name'],
                                    '{THREAD_SUBJECT}' => $data2['thread_subject'],
                                    '{THREAD_URL}' => $link,
                                    '{SITENAME}' => $settings['sitename'],
                                    '{SITEUSERNAME}' => $settings['siteusername'],
                                ));
                                sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $message_subject, $message_content);
                            }
                        }
                    }
                } else {
                    while ($data = dbarray($result)) {
                        if ($data2['forum_access'] == 0 || in_array($data2['forum_access'], explode(".", $data['user_level'].".".$data['user_groups']))) {
                            $message_subject = str_replace("{THREAD_SUBJECT}", $data2['thread_subject'], $locale['forum_0660']);
                            $message_content = strtr($locale['forum_0661'], array(
                                '{USERNAME}' => $data['user_name'],
                                '{THREAD_SUBJECT}' => $data2['thread_subject'],
                                '{THREAD_URL}' => $link,
                                '{SITENAME}' => $settings['sitename'],
                                '{SITEUSERNAME}' => $settings['siteusername'],
                            ));
                            sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $message_subject, $message_content);
                        }
                    }
                }
                $result = dbquery("UPDATE ".DB_FORUM_THREAD_NOTIFY." SET notify_status='0' WHERE thread_id='".$_GET['thread_id']."' AND notify_user!='".$userdata['user_id']."'");
            }
        }
        $thread_last_page = 0;
        $redirect_add = "";
        if ($data['thread_postcount'] > $forum_settings['posts_per_page']) {
            $thread_last_page = floor(floor($data['thread_postcount'] / $forum_settings['posts_per_page']) * $forum_settings['posts_per_page']);
        }
        if ($thread_last_page) {
            $redirect_add = "&amp;rowstart=".$thread_last_page;
        }
        $link[] = [
            'url'=>INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id'].$redirect_add."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id'],
            'title'=>$locale['forum_0548']
        ];
        redirect(INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id'].$redirect_add."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id'], 3);
    } else {
        $post_data = dbarray(dbquery("SELECT post_id FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($_GET['thread_id'])."' ORDER BY post_id DESC"));
        $link[] = [
            'url' => INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$post_data['post_id']."#post_".$post_data['post_id'],
            'title' => $locale['forum_0548']
        ];
        redirect(INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id'], 4);
    }
    $link[] = ['url' => INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_GET['forum_id'], 'title' => $locale['forum_0549']];
    $link[] = ['url' => INFUSIONS."forum/index.php", 'title' => $locale['forum_0550']];
    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(
        [
            'link'=> FUSION_REQUEST,
            'title' => $locale['forum_0503']
        ]
    );
    render_postify([
                       'title' => $locale['forum_0503'],
                       'error' => $errorb,
                       'description' => $description,
                       'link' => $link
                   ]);
}
/*
 * Editing a reply
 */
elseif ($_GET['post'] == "edit") {

    if (isset($_GET['post_count'])) {
        // Post deleted
        add_to_title($locale['global_201'].$locale['forum_0506']);
        $title = $locale['forum_0506'];
        $description = $locale['forum_0546'];
        if ($_GET['post_count'] > 0) {
            $link[] = ['url'=> INFUSIONS."viewthread.php?thread_id=".$_GET['thread_id'], 'title' => $locale['forum_0548']];

        }
        $link[] = ['url'=> INFUSIONS."forum/index.php?viewforum.php?forum_id=".$_GET['forum_id'], 'title' => $locale['forum_0549']];
        $link[] = ['url'=> INFUSIONS."forum/index.php", 'title' => $locale['forum_0550']];

    } else {

        // Post Edited
        redirect(INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id'], 3);
        add_to_title($locale['global_201'].$locale['forum_0508']);
        $title = $locale['forum_0508'];
        if ($errorb) {
            $description = $errorb;
        } else {
            $description = $locale['forum_0547'];
        }
        $link[] = ['url'=>INFUSIONS."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id'], 'title' => $locale['forum_0548']];
        $link[] = ['url'=>INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_GET['forum_id'], 'title' => $locale['forum_0549']];
        $link[] = ['url'=>INFUSIONS."forum/index.php", 'title' => $locale['forum_0550']];
    }
    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(
        [
            'link'=> FUSION_REQUEST,
            'title' => $title
        ]
    );
    render_postify([
                       'title' => $title,
                       'error' => $errorb,
                       'description' => $description,
                       'link' => $link
                   ]);
}
/*
 * Submit new Poll
 */
elseif ($_GET['post'] == 'newpoll') {
    add_to_title($locale['global_201'].$locale['forum_0607']);
    add_to_head("<meta http-equiv='refresh' content='2; url=".INFUSIONS."forum/viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."' />\n");
    $link[] = ['url'=> INFUSIONS."forum/viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id'], 'title'=> $locale['forum_0548']];
    $link[] = ['url'=> INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_GET['forum_id'], 'title'=> $locale['forum_0549']];
    $link[] = ['url'=> INFUSIONS."forum/index.php", 'title'=> $locale['forum_0550']];
    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(
        [
            'link'=> FUSION_REQUEST,
            'title' => $locale['forum_0607']
        ]
    );
    render_postify([
                       'title' => $locale['forum_0607'],
                       'error' => $errorb,
                       'description' => $description,
                       'link' => $link
                   ]);
}
/*
 * Edit a Poll
 */
elseif ($_GET['post'] == 'editpoll') {
    add_to_title($locale['global_201'].$locale['forum_0612']);
    add_to_head("<meta http-equiv='refresh' content='2; url=".INFUSIONS."forum/viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."' />\n");
    $link[] = ['url'=> INFUSIONS."forum/viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id'], 'title'=> $locale['forum_0548']];
    $link[] = ['url'=> INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_GET['forum_id'], 'title'=> $locale['forum_0549']];
    $link[] = ['url'=> INFUSIONS."forum/index.php", 'title'=> $locale['forum_0550']];
    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(
        [
            'link'=> FUSION_REQUEST,
            'title' => $locale['forum_0612']
        ]
    );
    render_postify([
                       'title' => $locale['forum_0612'],
                       'error' => $errorb,
                       'description' => $description,
                       'link' => $link
                   ]);
}
/*
 * Deleting a Poll
 */
elseif ($_GET['post'] == 'deletepoll') {
    add_to_title($locale['global_201'].$locale['forum_0615']);
    add_to_head("<meta http-equiv='refresh' content='2; url=".INFUSIONS."forum/viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."' />\n");
    $link[] = ['url'=> INFUSIONS."forum/viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id'], 'title'=> $locale['forum_0548']];
    $link[] = ['url'=> INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_GET['forum_id'], 'title'=> $locale['forum_0549']];
    $link[] = ['url'=> INFUSIONS."forum/index.php", 'title'=> $locale['forum_0550']];
    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(
        [
            'link'=> FUSION_REQUEST,
            'title' => $locale['forum_0615']
        ]
    );
    render_postify([
                       'title' => $locale['forum_0615'],
                       'error' => $errorb,
                       'description' => $description,
                       'link' => $link
                   ]);
}
require_once THEMES."templates/footer.php";
