<?php
require_once __DIR__.'/../../../../maincore.php';
require_once INCLUDES."theme_functions_include.php";
require_once THEMES."templates/render_functions.php";

fusion_get_locale('', [FORUM_LOCALE, FORUM_TAGS_LOCALE]);
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
    $response['search_terms'][0] = "forum_name LIKE '%$value%'";
    $response['search_terms'][0] .= " OR thread_subject LIKE '%$value%'";
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

$time_sql = '';
if (!empty($_REQUEST['filter_date'])) {
    $time_filter = form_sanitizer($_POST['filter_date'], '', 'filter_date');
    $time_filter = (TIME - ($time_filter * 24 * 3600));
    $time_sql = "t.thread_lastpost < '$time_filter' AND ";
}

if (!empty($_REQUEST['filter_tag_search'])) {
    $value = form_sanitizer($_REQUEST['filter_tag_search']);
    $response['search_terms'][] = "thread_tags ='$value'";
}

switch ($_REQUEST['section_type']) {
    case 'attach':
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

switch ($_REQUEST['section_value']) {
    default:
    case 'latest':
        $count_q = "
        SELECT t.thread_id                  
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id ".$response['join']."                       
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ")." t.thread_hidden='0' ".$response['search']." AND ".groupaccess('tf.forum_access')." GROUP BY t.thread_id";

        $select_q = "SELECT t.*, tf.*, ".$response['select']."
        COUNT(pv.forum_vote_user_id) 'poll_voted',
        IF (n.thread_id > 0, 1 , 0) 'user_tracked'
        FROM ".DB_FORUMS." tf
        INNER JOIN ".DB_FORUM_THREADS." t ON t.forum_id=tf.forum_id
        ".$response['join']."
        LEFT JOIN ".DB_FORUM_POLL_VOTERS." pv ON pv.thread_id = t.thread_id AND pv.forum_vote_user_id='".$userdata['user_id']."' AND t.thread_poll=1
        LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n ON n.thread_id = t.thread_id AND n.notify_user = '".$userdata['user_id']."'
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").$time_sql." ".groupaccess('tf.forum_access').$response['search']."
        GROUP BY t.thread_id ORDER BY t.thread_lastpost DESC
        ";

        ## Checked
        //print_p($select_q);
        //$result2 = dbquery($select_q);
        //print_p(dbrows($result2));

        break;

    case 'unsolved':
        $count_q = "SELECT t.thread_id
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ")." tf.forum_type='4' AND t.thread_answered='0' AND t.thread_locked='0' AND t.thread_hidden='0' ".$response['search']." AND ".groupaccess('tf.forum_access')." GROUP BY t.thread_id";

        $select_q = "SELECT t.thread_id, t.thread_subject, t.thread_author, t.thread_lastuser, t.thread_lastpost, t.thread_lastpostid, t.forum_id, t.thread_postcount,
        t.thread_locked, t.thread_sticky, t.thread_poll, t.thread_postcount, t.thread_views, 
        tf.* ".$response['select']."
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=t.forum_id
        ".$response['join']."
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ")."  tf.forum_type='4' AND t.thread_answered='0' AND t.thread_locked='0' 
        AND t.thread_hidden='0' ".$response['search']." AND ".groupaccess('tf.forum_access')."
        GROUP BY t.thread_id
        ORDER BY t.thread_lastpost DESC";
        break;
    case 'participated':
        $count_q = "SELECT t.thread_id
        FROM ".DB_FORUMS." tf        
        INNER JOIN ".DB_FORUM_POSTS." p ON p.forum_id=tf.forum_id 
        INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id AND t.forum_id=tf.forum_id                       
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ")." p.post_author='".$userdata['user_id']."' AND ".groupaccess('tf.forum_access')." ".$response['search']." GROUP BY t.thread_id";

        "SELECT p.forum_id, p.thread_id, p.post_id, p.thread_id 'thread_id', p.forum_id 'forum_id',
        t.thread_subject, t.thread_author, t.thread_lastuser, t.thread_lastpost, t.thread_lastpostid, t.thread_postcount,
        t.thread_locked, t.thread_sticky, t.thread_poll, t.thread_postcount, t.thread_views,             
        tf.*, 
        COUNT(pv.forum_vote_user_id) 'poll_voted',
        IF (n.thread_id > 0, 1 , 0) 'user_tracked'
        FROM ".DB_FORUMS." tf
        INNER JOIN ".DB_FORUM_POSTS." p ON p.forum_id=tf.forum_id
        INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id AND t.forum_id=tf.forum_id
        LEFT JOIN ".DB_FORUM_POLL_VOTERS." pv ON pv.thread_id = t.thread_id AND pv.forum_vote_user_id='".$userdata['user_id']."' AND t.thread_poll=1
        LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n ON n.thread_id = t.thread_id AND n.notify_user = '".$userdata['user_id']."'           		
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").$time_sql." p.post_author='".$userdata['user_id']."' AND ".groupaccess('tf.forum_access').$response['search']."
        GROUP BY p.thread_id ORDER BY t.thread_sticky DESC, t.thread_lastpost DESC";
        break;

    case 'unanswered':
        $count_q = "SELECT t.thread_id                  
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id                        
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").$time_sql." t.thread_postcount='1' AND t.thread_locked='0' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access')." ".$response['search']." GROUP BY t.thread_id";

        $select_q = "SELECT t.thread_id, t.thread_subject, t.thread_author, t.thread_lastuser, t.thread_lastpost, t.thread_lastpostid, t.forum_id, t.thread_postcount, t.thread_locked, 
        t.thread_sticky, t.thread_poll, t.thread_postcount, t.thread_views, tf.*
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=t.forum_id
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ")." t.thread_postcount='1' AND t.thread_locked='0' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access').$response['search']."
        GROUP BY t.thread_id
        ORDER BY t.thread_lastpost DESC";
        break;
}

//print_p($count_q);
$thread_obj = \PHPFusion\Infusions\Forum\Classes\Forum_Server::thread(FALSE);
$threads = $thread_obj->getThreadInfo(0, array(
    "count_query" => $count_q,
    "query"       => $select_q
));
//print_p($threads);
$response['info'] = array_merge_recursive($response['info'], $threads);

$view = new \PHPFusion\Infusions\Forum\Classes\Forum_Viewer();
echo $view->forum_threads_item($response['info']);
