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
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC
            ";
            break;
        case 'answer-activity':
            $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC
            ";
            break;
        case 'answer-votes':
            $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC
            ";
            break;
        case 'question-latest':
            $sql = "SELECT thread_subject, thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$user_data['user_id']."'
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC
            ";
            break;
        case 'question-activity':
            $sql = "SELECT thread_subject, thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$user_data['user_id']."'
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC
            ";
            break;
        case 'question-votes':
            $sql = "SELECT thread_subject, thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$user_data['user_id']."'
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC
            ";
            break;
        default:
    }
}

$tpl = \PHPFusion\Template::getInstance('forum-profile-item');
$tpl->set_template(__DIR__.'/../../../../templates/profile/summary-item.html');

if ($sql) {
    $result = dbquery($sql);
    if ($row_count = dbrows($result)) {
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

