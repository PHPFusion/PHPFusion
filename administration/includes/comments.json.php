<?php
require_once __DIR__.'/../../maincore.php';
require_once INCLUDES.'theme_functions_include.php';
require_once INCLUDES.'ajax_include.php';

//print_P($_GET);
$list = [];
$types = get('commentType');
if (!empty($types)) {
    $types = fusion_decode($types);
}
$auth = get('auth');
if ($user_id = fusion_authenticate_user($auth)) {
    //pageAccess('C');
    $result = dbquery("SELECT * FROM ".DB_COMMENTS);
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            // check settings is open to public or not.
            $user = fusion_get_user($data['comment_name']);
            $list[] = [
                'user'              => display_avatar($user, '30px', 'm-r-10').profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                'comment'           => parse_textarea($data['comment_message']),
                'comment_in'        => $types[$data['comment_type']],
                'comment_datestamp' => showdate('shortdate', $data['comment_datestamp']).', '.date('j:m', $data['comment_datestamp']),
            ];
        }
    }
}


echo json_encode(['data' => $list, 'get' => $_GET, 'post' => $_POST]);

