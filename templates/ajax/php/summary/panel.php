<?php

require_once __DIR__.'/../../../../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

// print_P($_GET);
$user = get('uid', FILTER_VALIDATE_INT);
if ($user) {
    $user_data = fusion_get_user($user);
    $type = get('type');
    switch($type) {
        case 'answer-latest':
            $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
            WHERE fp.post_answer=1 AND fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC LIMIT 6
            ";
            break;
        case 'answer-activity':
            $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC LIMIT 6
            ";
            break;
        case 'answer-votes':
            $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC LIMIT 6
            ";
            break;
        case 'question-latest':
            $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$user_data['user_id']."'
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC LIMIT 6
            ";
            break;
        case 'question-activity':
            $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$user_data['user_id']."'
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC LIMIT 6
            ";
            break;
        case 'question-votes':
            $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$user_data['user_id']."'
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC LIMIT 6
            ";
            break;
        case 'reputation-latest':
            $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views 
            FROM ".DB_FORUM_USER_REP." frp 
            INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$user_data['user_id']."' 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
            WHERE frp.user_id='".$user_data['user_id']."'
            GROUP BY frp.thread_id LIMIT 6";
            break;
        case 'reputation-activity':
            $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views 
            FROM ".DB_FORUM_USER_REP." frp 
            INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$user_data['user_id']."' 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
            WHERE frp.user_id='".$user_data['user_id']."'
            GROUP BY frp.thread_id LIMIT 6";
            break;
        case 'reputation-votes':
            $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views 
            FROM ".DB_FORUM_USER_REP." frp 
            INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$user_data['user_id']."' 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
            WHERE frp.user_id='".$user_data['user_id']."'
            GROUP BY frp.thread_id LIMIT 6";
        default:
    }
}

$tpl = \PHPFusion\Template::getInstance('forum-profile-item');
$tpl->set_template(__DIR__.'/../../../../templates/profile/summary-item.html');

if ($sql) {
    $result = dbquery($sql);
    $row_count = 0;

    if (dbrows($result)) {

        $row_count = dbrows(dbquery(str_replace('LIMIT 6', '', $sql)));

        while ($data = dbarray($result)) {
            $tpl->set_block('thread_item', [
                'thread_views' => $data['thread_views'],
                'thread_subject' => $data['thread_subject'],
                'thread_link' => FORUM.'viewthread.php?thread_id='.$data['thread_id']
            ]);
        }
    } else {
        $tpl->set_block('no_thread_item');
    }
} else {
    $tpl->set_block('no_thread_item');
}

$response =[
    'count' => number_format($row_count,0),
    'html' =>$tpl->get_output(),
];

echo json_encode($response);

