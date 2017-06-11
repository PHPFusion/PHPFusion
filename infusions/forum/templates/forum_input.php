<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/templates/forum_input.php
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

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

/**
 * Display the post reply form
 * To customize this form, declare the same function in your theme.php and use $info string
 */
if (!function_exists("display_forum_postform")) {

    function display_forum_postform($info) {
        $locale = fusion_get_locale();
        $template = fusion_get_template(FORUM.'templates/forms/post.html');

        $tab_title['title'][0] = $locale['forum_0602'];
        $tab_title['id'][0] = 'postopts';
        $tab_title['icon'][0] = '';
        $tab_active = tab_active($tab_title, 0);
        $tab_content = opentabbody($tab_title['title'][0], 'postopts', $tab_active); // first one is guaranteed to be available
        $tab_content .= "<div class='well m-t-20'>\n";
        $tab_content .= $info['delete_field'];
        $tab_content .= $info['sticky_field'];
        $tab_content .= $info['notify_field'];
        $tab_content .= $info['lock_field'];
        $tab_content .= $info['hide_edit_field'];
        $tab_content .= $info['smileys_field'];
        $tab_content .= $info['signature_field'];
        $tab_content .= "</div>\n";
        $tab_content .= closetabbody();
        if (!empty($info['attachment_field'])) {
            $tab_title['title'][1] = $locale['forum_0557'];
            $tab_title['id'][1] = 'attach_tab';
            $tab_title['icon'][1] = '';
            $tab_content .= opentabbody($tab_title['title'][1], 'attach_tab', $tab_active);
            $tab_content .= "<div class='well m-t-20'>\n".$info['attachment_field']."</div>\n";
            $tab_content .= closetabbody();
        }

        echo $info['openform'];
        echo(strtr($template, [
            '{%breadcrumb%}'              => render_breadcrumbs(),
            '{%opentable%}'               => fusion_get_function('opentable', $info['title']),
            '{%closetable%}'              => fusion_get_function('closetable'),
            '{%description%}'             => $info['description'],
            '{%forum_fields%}'            => $info['forum_field'].$info['forum_id_field'].$info['thread_id_field'],
            '{%forum_subject_field%}'     => $info['subject_field'],
            '{%forum_tag_field%}'         => $info['tags_field'],
            '{%forum_message_field%}'     => $info['message_field'],
            '{%forum_edit_reason_field%}' => $info['edit_reason_field'],
            '{%forum_poll_form%}'         => $info['poll_form'],
            '{%forum_post_options%}'      => opentab($tab_title, $tab_active, 'newthreadopts').$tab_content.closetab(),
            '{$forum_post_button%}'       => $info['post_buttons'],
            '{%display_last_posts%}'      => !empty($info['last_posts_reply']) ? $info['last_posts_reply'] : '',
        ]));
        echo $info['closeform'];
    }
}

/**
 * Display the poll creation form
 * To customize this form, declare the same function in your theme.php and use $info string
 */
if (!function_exists("display_forum_pollform")) {
    function display_forum_pollform($info) {
        $html = fusion_get_template(FORUM.'templates/forms/poll.html');
        echo strtr($html, [
            '{%breadcrumb%}'  => render_breadcrumbs(),
            '{%opentable%}'   => fusion_get_function('opentable', $info['title']),
            '{%closetable%}'  => fusion_get_function('closetable'),
            '{%description%}' => $info['description'],
            '{%pollform%}'    => $info['field']['openform'].$info['field']['poll_field'].$info['field']['poll_button'].$info['field']['closeform'],
        ]);
    }
}

/**
 * Display the bounty creation form
 * To customize this form, declare the same function in your theme.php and use $info string
 */
if (!function_exists('display_form_bountyform')) {
    function display_forum_bountyform($info) {
        $html = fusion_get_template(FORUM.'templates/forms/bounty.html');
        echo strtr($html, [
            '{%breadcrumb%}'  => render_breadcrumbs(),
            '{%opentable%}'   => fusion_get_function('opentable', $info['title']),
            '{%closetable%}'  => fusion_get_function('closetable'),
            '{%description%}' => $info['description'],
            '{%bountyform%}'  => $info['field']['openform'].$info['field']['bounty_select'].$info['field']['bounty_description'].$info['field']['bounty_button'].$info['field']['closeform'],
        ]);
    }
}

/**
 * Display the Quick Reply Form
 * To customize this form, declare the same function in your theme.php and use $info string
 */
if (!function_exists("display_quick_reply")) {
    function display_quick_reply($info) {
        $html = fusion_get_template(FORUM.'templates/forms/quick_reply.html');

        return strtr($html, [
            '{%description%}'   => $info['description'],
            '{%message_field%}' => $info['field']['message'],
            '{%options_field%}' => $info['field']['options'],
            '{%button%}'        => $info['field']['button'],
        ]);
    }
}