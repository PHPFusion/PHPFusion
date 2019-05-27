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
 * @return string
 */
function display_comments_widget() {
    global $global_comments, $link_type, $comments_type;

    $locale = fusion_get_locale();
    $aidlink= fusion_get_aidlink();

    $tpl = \PHPFusion\Template::getInstance('comments-widget');
    $tpl->set_locale($locale);
    $tpl->set_template(__DIR__.'/comments.html');

    if (count($global_comments['data']) > 0) {
        foreach ($global_comments['data'] as $i => $comment_data) {
            $comment_item_url = (isset($link_type[$comment_data['comment_type']]) ? "<a href='".sprintf($link_type[$comment_data['comment_type']]."'", $comment_data['comment_item_id'])."'>{%item%}</a>" : '{%item%}');
            $comment_item_name = (isset($comments_type[$comment_data['comment_type']])) ? $comments_type[$comment_data['comment_type']] : $locale['global_073b'];
            $comments = trimlink(strip_tags(parse_textarea($comment_data['comment_message'], FALSE, TRUE)), 70);

            $info['border_style'] = ($i > 0 ? "style='border-top:1px solid #ddd;'" : '');
            $info['avatar'] = display_avatar($comment_data, "50px", '', '', '');
            $info['date'] = timer($comment_data['comment_datestamp']);
            $info['manage_comments_link'] = ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id'];
            $info['edit_comments_link'] = ADMIN."comments.php".$aidlink."&amp;action=edit&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id'];
            $info['delete_comments_link'] = ADMIN.'comments.php'.$aidlink.'&amp;action=delete&amp;comment_id='.$comment_data['comment_id'].'&amp;ctype='.$comment_data['comment_type'].'&amp;comment_item_id='.$comment_data['comment_item_id'];
            $info['profile_link'] = (!empty($comment_data['user_id']) ? profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']) : $comment_data['comment_name']);
            $info['name'] = strtr($comment_item_url, ["{%item%}" => $comment_item_name]);
            $info['comments'] = parse_textarea($comments, TRUE, FALSE);
            $tpl->set_block('li', $info);
        }
        if (isset($global_comments['comments_nav'])) {
            $tpl->set_tag('li_nav', $global_comments['comments_nav']);
        }
    } else {
        $info = [
            'text' => $global_comments['nodata'],
        ];
        $tpl->set_block('li_na', $info);
    }

    $content = fusion_get_function("open_sidex", $locale['277']." - ".format_num($global_comments['rows']));
    $content .= $tpl->get_output();
    $content .= fusion_get_function("close_sidex");

    return (string)$content;

}
