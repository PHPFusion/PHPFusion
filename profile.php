<?php
/**
 * Babylon user profile forum extensions
 */
defined('IN_FUSION') || exit;
$user_data = fusion_get_user( floatval(get('lookup', FILTER_VALIDATE_INT) ?:0) );

$tpl = \PHPFusion\Template::getInstance('uf-forum');

$tpl->set_template(__DIR__.'/templates/forum-uf.html');
$tpl->set_tag('reputation_count', format_num($user_data['user_reputation']));
$tpl->set_tag('post_count', format_num($user_data['user_posts']));

// Appreciators
$result = dbquery("SELECT ");

//print_P($user_data);




echo $tpl->get_output();
