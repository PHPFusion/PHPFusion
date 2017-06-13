<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: post/quick_reply.php
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

    public static function display_quickReply($info) {
        $locale = fusion_get_locale();
        $user_sig = fusion_get_userdata('user_sig');
        $notify_options = ForumServer::get_forum_settings('thread_notify');
        require_once FORUM."templates.php";

        $options_field = form_checkbox('post_smileys', $locale['forum_0169'], '', array('class' => 'm-b-0', 'reverse_label' => TRUE));
        if (!$user_sig) {
            $options_field .= form_checkbox('post_showsig', $locale['forum_0170'], '1', array('class' => 'm-b-0', 'reverse_label' => TRUE));
        }
        if ($notify_options) {
            $options_field .= form_checkbox('notify_me', $locale['forum_0171'], $info['user_tracked'], array('class' => 'm-b-0', 'reverse_label' => TRUE));
        }

        $info += [
            'openform'    => openform('quick_reply_form', 'post', FORUM."viewthread.php?thread_id=".$info['thread_id'], array('class' => 'spacer-sm')),
            'description' => $locale['forum_0168'],
            'field'       => [
                'message' => form_textarea('post_message', '', '',
                    array(
                        'placeholder' => $locale['forum_0601']."...",
                        'bbcode'      => TRUE,
                        'required'    => TRUE,
                        'preview'     => TRUE,
                        'form_name'   => 'quick_reply_form',
                        'height'      => '250px'
                    )),
                'button'  => form_button('post_quick_reply', $locale['forum_0172'], $locale['forum_0172'], array('class' => 'btn-primary')),
                'options' => $options_field
            ]
        ];

        $html = openform('quick_reply_form', 'post', FORUM."viewthread.php?thread_id=".$info['thread_id']);
        $html .= display_quick_reply($info);
        $html .= closeform();

        return $html;
    }

}