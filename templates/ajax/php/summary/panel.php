<?php
require_once __DIR__.'/../../../../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

// print_P($_GET);
$user = get('uid', FILTER_VALIDATE_INT);
if ($user) {
    $user_data = fusion_get_user($user);
    $type = get('type');
    $class = new \PHPFusion\Infusions\Forum\Classes\Forum_Profile($user_data['user_id'], $locale = []);
    $sql = $class->getSQL($type);
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

