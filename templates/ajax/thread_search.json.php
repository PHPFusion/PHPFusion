<?php
require_once dirname(__FILE__).'/../../../../../../../maincore.php';
require_once INCLUDES."theme_functions_include.php";
require_once FORUM_CLASS."autoloader.php";
require_once dirname(__FILE__).'/../forum_index.php';

fusion_get_locale('', FORUM_LOCALE);
$response['status'] = 0;
$response['request'] = $_REQUEST;
$response['info'] = array();
// get the filter
$response['search'] = '';
$response['select'] = '';
$response['join'] = '';
if (!empty($_REQUEST['filter_search'])) {
    // transform name to id
    $value = form_sanitizer($_REQUEST['filter_search']);
    $response['search_terms'][0] = "t.thread_subject LIKE '%$value%'";
    $user_result = dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_name='$value' LIMIT 1"); // user name is unique
    $user_ids = array();
    if (dbrows($user_result)) {
        $user_data = dbarray($user_result);
        $user_id = $user_data['user_id'];
    }
    if (!empty($user_ids)) {
        $response['search_terms'][0] .= " OR thread_author='$user_id' OR thread_lastuser='$user_id'";
    }
}

$forum_id = form_sanitizer($_REQUEST['section_value'], '0');
$response['search_terms'][] = "tf.forum_id='$forum_id'";

if (!empty($_REQUEST['filter_tag_search'])) {
    $value = form_sanitizer($_REQUEST['filter_tag_search']);
    $response['search_terms'][] = "thread_tags ='$value'";
}
switch ($_REQUEST['section_type']) {
    case 'attachments':
        $response['join_terms'][] = "INNER JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id=t.thread_id";
        break;
    case 'bounty':
        $response['search_terms'][] = "t.thread_bounty=1";
        break;
    case 'poll':
        $response['search_terms'][] = "t.thread_poll=1";
        break;
    default:
}

if (!empty($response['search_terms'])) {
    $response['search'] = " AND ".implode(" AND ", $response['search_terms']);
}
if (!empty($response['select_terms'])) {
    $response['select'] = ", ".implode(", ", $response['select_terms']);
}
if (!empty($response['join_terms'])) {
    $response['join'] = implode(" ", $response['join_terms']);
}

//print_p($response['search']);
$count_q = "SELECT t.thread_id
            FROM ".DB_FORUM_THREADS." t
            INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id ".$response['join']."
            ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ")." t.thread_hidden='0' ".$response['search']." AND ".groupaccess('tf.forum_access')." GROUP BY t.thread_id";
$select_q = "SELECT t.thread_id, t.thread_subject, t.thread_author, t.thread_lastuser, t.thread_lastpost,
            t.thread_lastpostid, t.thread_postcount, t.thread_locked, t.thread_sticky, t.thread_poll, t.thread_postcount, t.thread_views,
            t.forum_id 'forum_id', tf.* ".$response['select']."
            FROM ".DB_FORUMS." tf
            INNER JOIN ".DB_FORUM_THREADS." t ON t.forum_id=tf.forum_id
            ".$response['join']."
            ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('tf.forum_access')."
            ".$response['search']."
            GROUP BY t.thread_id ORDER BY t.thread_lastpost DESC";

$thread_obj = \PHPFusion\Infusions\Forum\ForumServer::thread(FALSE);
$info = $thread_obj->getThreadInfo($forum_id, array("count_query" => $count_q, "query" => $select_q));
$response['info'] = array_merge_recursive($response['info'], $info);

$tpl = \PHPFusion\Template::getInstance('forum_ajax_threads');

$userdata = fusion_get_userdata();
// Roadmap
/*
 * @Hide the thread from my view
 * @Track this thread - relocalized to save this thread
 * @Integrate with coins so people can reward real credits to another user.
 */
$locale = fusion_get_locale();
$tpl = \PHPFusion\Template::getInstance('forum_index_latest');
//https://next.php-fusion.co.uk/production/infusions/forum/index.php?viewforum&forum_id=157
$site_path = fusion_get_settings("site_path");

// from file, we need to check the path

if (!empty($info['threads'])) {
    if (!empty($info['threads']['item'])) {
        foreach ($info['threads']['item'] as $cdata) {

            $report_url = $site_path."infusions/forum/index.php?viewforum=true&amp;forum_id=".$cdata['forum_id']."&amp;report=true&rtid=".$cdata['thread_id'];
            $track_url = $site_path.str_replace("../", "", $cdata['track_button']['link']);
            $thread_url = $site_path.str_replace("../", "", $cdata['thread_link']['link']);
            $profile_link = $site_path.str_replace("../", "", $cdata['thread_starter']['profile_link']);
            $l_profile_link = $site_path.str_replace("../", "", $cdata['thread_last']['profile_link']);

            $thread_buttons = "";
            if (iMEMBER) {
                $tpl3 = \PHPFusion\Template::getInstance('threads_'.$cdata['thread_id']);
                $tpl3->set_block("track_button", [
                    "track_link"    => $cdata['track_button']['link'],
                    "track_title"   => $cdata['track_button']['title'],
                    "track_onclick" => $cdata['track_button']['onclick'],
                ]);
                $tpl3->set_block("report_button", [
                    "report_url" => clean_request("report=true&rtid=".$cdata['thread_id'], ['report', 'rtid'], FALSE)
                ]);
                $tpl3->set_template(__DIR__.'/../templates/section_thread_btn.html');
                $thread_buttons = $tpl3->get_output();
            }

            $panels = \PHPFusion\Panels::getInstance();
            $panels->hide_panel(\PHPFusion\Panels::PANEL_AU_CENTER);

            $tpl->set_block('threads', [
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
                // Last info
                'last_avatar'           => $cdata['thread_last']['avatar'],
                'last_rank'             => $cdata['thread_last']['user']['user_rank'],
                'last_profile_link'     => $cdata['thread_last']['profile_link'],
                'last_avatar'           => $cdata['thread_last']['avatar'],
                'last_avatar_sm'        => $cdata['thread_last']['avatar_sm'],
                'last_post_message'     => $cdata['last_post_message'],
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
} else {
    $tpl->set_block('no_item', ['message' => $locale['forum_0269']]);
}
$tpl->set_template(__DIR__.'/../templates/legacy_threads.html');

echo $tpl->get_output();

/*


$tpl->set_template(__DIR__.'/../templates/section_threads.html');

if (!empty($response['info']['threads']['item'])) {
    foreach ($response['info']['threads']['item'] as $thread_id => $tdata) {

        $thread_link = str_replace('../../../../../../../', '../../', $tdata['thread_link']['link']);
        $profile_link = str_replace('../../../../../../../', '../../', $tdata['thread_last']['profile_link']);
        $starter_link = str_replace('../../../../../../../', '../../', $tdata['thread_starter_text']);

        $tpl->set_block('thread_block', [
            'thread_title'                  => $tdata['thread_subject'],
            'link'                   => $thread_link,
            'starter_avatar'         => $tdata['thread_starter']['avatar'],
            'starter_profile_link'   => $starter_link,
            'last_user_profile_link' => $profile_link,
            'time'                   => timer($tdata['thread_last']['time']),
            'view_count'             => number_format($tdata['thread_views'], 0),
            'like_count'             => '0',
            'user_count'             => '0',
            'post_count'             => number_format($tdata['thread_postcount'], 0),
            'thread_icons'           => implode($tdata['thread_icons'])
        ]);
    }
}

if (empty($response['info']['threads']['item']) && empty($response['info']['threads']['sticky'])) {
    $tpl->set_block('no_results_block', [
        'message' => 'There are no threads matching your request criteria.'
    ]);
}

echo $tpl->get_output();
*/