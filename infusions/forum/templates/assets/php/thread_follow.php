<?php
require_once __DIR__.'/../../../../../../../maincore.php';
$arr = array();

if (isset($_REQUEST['thread_id']) && isnum($_REQUEST['thread_id'])) {
    $active = dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "notify_user=:uid AND thread_id=:tid", [
        ':uid'=>fusion_get_userdata('user_id'),
        ':tid'=>intval($_REQUEST['thread_id'])
    ]);
    if ($active) {
        // remove track
        dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user=:uid AND thread_id=:tid", [
            ':uid'=>fusion_get_userdata('user_id'),
            ':tid'=>intval($_REQUEST['thread_id'])
        ]);
        $arr = [
            'title' => "Follow",
            "js_id" => "follow-thread",
            'link' => "#",
            'count' => number_format(dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id=:tid", [':tid' => intval($_REQUEST['thread_id'])]),0),
        ];
    } else {
        $data = [
            'thread_id' => intval($_REQUEST['thread_id']),
            'notify_datestamp' => TIME,
            'notify_user' => fusion_get_userdata('user_id'),
            'notify_status' => 1,
        ];
        dbquery_insert(DB_FORUM_THREAD_NOTIFY, $data, 'save', ['no_unique'=>true]);
        $arr = [
            'title' => "Following",
            "js_id" => "follow-thread",
            'link' => "#",
            'count' => number_format(dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id=:tid", [':tid' => intval($_REQUEST['thread_id'])]),0),
        ];
    }
}
echo json_encode($arr);