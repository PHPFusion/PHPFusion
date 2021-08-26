<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: quick_reply.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums\Post;

use PHPFusion\Forums\ForumServer;

class QuickReply extends ForumServer {
    public static function displayQuickReply($info) {
        $locale = fusion_get_locale();
        $user_sig = fusion_get_userdata('user_sig');
        $notify_options = ForumServer::getForumSettings('thread_notify');
        require_once FORUM."templates/forum.tpl.php";

        $thread = self::thread();
        $forum_settings = ForumServer::getForumSettings();

        $options_field = form_checkbox('post_smileys', $locale['forum_0169'], '', ['class' => 'm-b-0', 'reverse_label' => TRUE]);
        if ($user_sig) {
            $options_field .= form_checkbox('post_showsig', $locale['forum_0170'], '', ['class' => 'm-b-0', 'reverse_label' => TRUE]);
        }
        if ($notify_options) {
            $options_field .= form_checkbox('notify_me', $locale['forum_0171'], $info['user_tracked'], ['class' => 'm-b-0', 'reverse_label' => TRUE]);
        }

        $info += [
            'description' => $locale['forum_0168'],
            'field'       => [
                'message'    => form_textarea('post_message', '', '', [
                    'placeholder' => $locale['forum_0601']."...",
                    'bbcode'      => TRUE,
                    'required'    => TRUE,
                    'preview'     => TRUE,
                    'form_name'   => 'quick_reply_form',
                    'height'      => '250px',
                    'descript'    => FALSE
                ]),
                'attachment' => $thread->getThreadPermission("can_upload_attach") ?
                    form_fileinput('file_attachments[]', $locale['forum_0557'], "", [
                        'input_id'    => 'file_attachments',
                        'upload_path' => INFUSIONS.'forum/attachments/',
                        'type'        => 'object',
                        'preview_off' => TRUE,
                        'multiple'    => TRUE,
                        'inline'      => FALSE,
                        'max_count'   => $forum_settings['forum_attachmax_count'],
                        'valid_ext'   => $forum_settings['forum_attachtypes'],
                        'class'       => 'm-b-0',
                        'max_width'   => $forum_settings['forum_attachmax_w'],
                        'max_height'  => $forum_settings['forum_attachmax_h'],
                        'max_byte'    => $forum_settings['forum_attachmax']
                    ])."
                    <div class='m-b-20'>\n<small>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n" : "",
                'button'     => form_button('post_quick_reply', $locale['forum_0172'], $locale['forum_0172'], ['class' => 'btn-primary']),
                'options'    => $options_field
            ]
        ];

        $html = openform('quick_reply_form', 'post', FORUM."viewthread.php?thread_id=".$info['thread_id'], [
            'enctype' => $thread->getThreadPermission("can_upload_attach"),
            'class'   => 'spacer-sm'
        ]);
        $html .= display_quick_reply($info);
        $html .= closeform();

        return $html;
    }
}
