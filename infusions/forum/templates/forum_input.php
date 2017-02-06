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

if (!function_exists("display_forum_postform")) {

    function display_forum_postform($info) {

        $locale = fusion_get_locale();
        add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
        echo render_breadcrumbs();

        opentable($info['title']);
        // New template
        echo "<!--pre_form-->\n";
        echo "<h4 class='m-b-20'>".$info['description']."</h4>\n";
        echo $info['openform'];
        echo $info['forum_field'];
        echo $info['subject_field'];
        echo !empty($info['tags_field']) ? $info['tags_field'] : "";
        echo $info['message_field'];
        echo $info['edit_reason_field'];
        echo $info['forum_id_field'];
        echo $info['thread_id_field'];
        echo $info['poll_form'];

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

        echo opentab($tab_title, $tab_active, 'newthreadopts');
        echo $tab_content;
        echo closetab();

        echo $info['post_buttons'];
        echo $info['closeform'];

        echo "<!--end_form-->\n";
        closetable();
        if (!empty($info['last_posts_reply'])) {
            echo "<div class='well m-t-20'>\n";
            echo $info['last_posts_reply'];
            echo "</div>\n";
        }
    }

}

if (!function_exists("display_forum_pollform")) {

    function display_forum_pollform($info) {
        echo render_breadcrumbs();
        opentable($info['title']);
        echo "<h4 class='m-b-20'>".$info['description']."</h4>\n";
        echo "<!--pre_form-->\n";
        echo $info['field']['openform'];
        echo $info['field']['poll_field'];
        echo $info['field']['poll_button'];
        echo $info['field']['closeform'];
        closetable();
    }

}

if (!function_exists('display_form_bountyform')) {

    function display_forum_bountyform($info) {
        echo render_breadcrumbs();
        opentable($info['title']);
        echo "<h4 class='m-b-20'>".$info['description']."</h4>\n";
        echo "<!--pre_form-->\n";
        echo $info['field']['openform'];
        echo $info['field']['bounty_select'];
        echo $info['field']['bounty_description'];
        echo $info['field']['bounty_button'];
        echo $info['field']['closeform'];
        closetable();
    }
}


if (!function_exists("display_quickReply")) {

    function display_quickReply($info) {

        $locale = fusion_get_locale("", FORUM_LOCALE);
        $forum_settings = \PHPFusion\Forums\ForumServer::get_forum_settings();
        $userdata = fusion_get_userdata();
        $html = "<!--sub_forum_thread-->\n";
        $html .= openform('quick_reply_form', 'post', FUSION_SELF."?thread_id=".$info['thread_id'], array('class' => 'spacer-sm'));
        $html .= "<h4>".$locale['forum_0168']."</h4>\n";
        $html .= form_textarea('post_message', '', '',
                                  array(
                                      'placeholder' => $locale['forum_0601']."...",
                                      'bbcode'      => TRUE,
                                      'required'    => TRUE,
                                      'autosize'    => TRUE,
                                      'preview'     => TRUE,
                                      'form_name'   => 'quick_reply_form',
                                      'height'      => '250px'
                                  ));
        $html .= "<div class='m-t-10 pull-right'>\n";
        $html .= form_button('post_quick_reply', $locale['forum_0172'], $locale['forum_0172'], array('class' => 'btn-primary m-r-10'));
        $html .= "</div>\n";
        $html .= "<div class='overflow-hide'>\n";
        $html .= form_checkbox('post_smileys', $locale['forum_0169'], '', array('class' => 'm-b-0', 'reverse_label' => TRUE));
        if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
            $html .= form_checkbox('post_showsig', $locale['forum_0170'], '1', array('class' => 'm-b-0', 'reverse_label' => TRUE));
        }
        if ($forum_settings['thread_notify']) {
            $html .= form_checkbox('notify_me', $locale['forum_0171'], $info['user_tracked'], array('class' => 'm-b-0', 'reverse_label' => TRUE));
        }
        $html .= "</div>\n";
        $html .= closeform();
        return (string)$html;
    }
}