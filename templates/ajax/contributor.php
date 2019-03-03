<?php
require_once __DIR__.'/../../../../maincore.php';
require_once THEMES.'templates/header.php';
if (isset($_GET['q'])) {
    $file = __DIR__.'/../panel/contributor_result.html';
    $tpl = \PHPFusion\Template::getInstance('contributor_result');
    $tpl->set_template($file);
    switch ($_GET['q']) {
        case 'week':
            $time = strtotime('-1 week');
            break;
        case 'month':
            $time = strtotime('-1 month');
            break;
        case 'year':
            $time = strtotime('-1 year');
            break;
        case 'all':
        default:
            $time = 0;
            break;
    }
    $result = dbquery("SELECT post_author, COUNT(post_id) 'post_count'  FROM ".DB_FORUM_POSTS." WHERE 
        post_datestamp BETWEEN :from_time AND :current_time
        GROUP BY post_author ORDER BY post_count DESC LIMIT 5
        ", [
        ':from_time'    => $time,
        ':current_time' => TIME,
    ]);
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $user = fusion_get_user($data['post_author']);
            if (!empty($user)) {
                $user_avatar = display_avatar($user, '35px', '', FALSE, 'img-circle');
                $tpl->set_block('contributor', [
                    'avatar'       => $user_avatar,
                    'profile_link' => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                    'post_count'   => number_format($data['post_count'], 0)
                ]);
                unset($user);
            }
        }
    } else {
        $tpl->set_block('no_result', [
            'text' => 'There are no contributors yet.'
        ]);
    }
    echo $tpl->get_output();
} else {
    echo 'Error! Please contact support.';
}