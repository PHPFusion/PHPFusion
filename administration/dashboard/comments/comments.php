<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: administration/dashboard/comments/comments.php
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
defined('IN_FUSION') || exit;

/**
 * Comments Admin Dashboard Widget
 * Widget to display comments latest activity
 *
 * @return string
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function display_comments_widget() {
    global $global_comments, $link_type, $comments_type;

    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();

    $info['locale'] = $locale;
    if (count($global_comments['data']) > 0) {
        foreach ($global_comments['data'] as $i => $comment_data) {

            $comment_item_url = (isset($link_type[$comment_data['comment_type']]) ? "<a href='".sprintf($link_type[$comment_data['comment_type']]."'", $comment_data['comment_item_id'])."'>{%item%}</a>" : '{%item%}');
            $comment_item_name = (isset($comments_type[$comment_data['comment_type']])) ? $comments_type[$comment_data['comment_type']] : $locale['global_073b'];
            $comments = trimlink(strip_tags(parse_textarea($comment_data['comment_message'], FALSE, TRUE)), 70);
            // why not use datatables.
            $info['comment_items'][] = [
                'comment_id'           => $comment_data['comment_id'],
                'avatar'               => display_avatar($comment_data, "50px", '', '', 'img-circle'),
                'date'                 => timer($comment_data['comment_datestamp']),
                'manage_comments_link' => ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id'],
                'edit_comments_link'   => ADMIN."comments.php".$aidlink."&amp;action=edit&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id'],
                'delete_comments_link' => ADMIN.'comments.php'.$aidlink.'&amp;action=delete&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'],
                'profile_link'         => (!empty($comment_data['user_id']) ? profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']) : $comment_data['comment_name']),
                'name'                 => strtr($comment_item_url, ["{%item%}" => $comment_item_name]),
                'comments'             => parse_textarea($comments, TRUE, FALSE),
            ];
        }

        //if (isset($global_comments['comments_nav'])) {
        //$tpl->set_tag('li_nav', $global_comments['comments_nav']);
        //}
    } else {
        $info['no_comment'] = $global_comments['nodata'];
    }
    $info['title'] = $locale['277']." - ".format_num($global_comments['rows']);
    $info['footer'] = '<div><a href="">All</a> | <a href="">Mine</a> | <a href="">Pending</a> | <a href="">Approved</a> | <a href="">Trash</a></div>';

    return fusion_render(ADMIN.'/dashboard/comments/', 'comments.twig', $info, TRUE);

}
