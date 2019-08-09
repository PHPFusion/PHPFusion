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

namespace PHPFusion\Infusions\Forum\Classes\Post;

use PHPFusion\Infusions\Forum\Classes\Forum_Server;

class Quick_Reply extends Forum_Server {

    /**
     * Forum Reply Text Box
     *
     * @param       $info
     * @param array $options additional parameters to change behaviours
     *
     * @return string
     */
    public static function display_quickReply($info, array $options = []) {
        $default_options = [
            "post_id"    => 0, // reply to post id
            "quote"      => FALSE, // attach reply as quote
            "remote_url" => FORUM."viewthread.php?thread_id=".$info['thread_id'],
        ];

        $options += $default_options;

        $locale = fusion_get_locale();

        $post_message = "";
        $post_id = $options['post_id'];
        $form_name = "thread_reply";
        $textarea_id = "post_message";

        if ($options['quote']) {
            $result = dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE post_id=:post_id", [':post_id' => intval($options['post_id'])]);
            if (dbrows($result)) {
                $quotedata = dbarray($result);
                $post_id = $quotedata['post_id'];
                $post_message = nl2br("[quote]".$quotedata['post_message']."[/quote]");
                $form_name = "post_reply_".$post_id;
                $textarea_id = "post_message_".$post_id;
            }
        }

        // Buttons Checkbox
        $options_field = form_checkbox('post_smileys', $locale['forum_0169'], '', ['class' => 'm-r-10', 'type' => 'button', 'ext_tip' => $locale['forum_0622']]);
        if (!fusion_get_userdata('user_sig')) {
            $options_field .= form_checkbox('post_showsig', $locale['forum_0264'], '1', ['class' => 'm-r-10', 'type' => 'button', 'ext_tip' => $locale['forum_0170']]);
        }
        if (parent::get_forum_settings('thread_notify')) {
            $options_field .= form_checkbox('notify_me', $locale['forum_0552'], $info['user_tracked'], ['class' => 'm-r-10', 'type' => 'button', 'ext_tip' => $locale['forum_0171']]);
        }

        $remote_param = $options['remote_url'] !== $default_options['remote_url'] ? ["remote_url" => $options['remote_url']] : [];

        $info += [
            'openform'    => openform($form_name, 'post', $options['remote_url'], $remote_param).form_hidden('post_cat', '', $post_id),
            'description' => $locale['forum_0168'],
            'field'       => [
                'message' => form_textarea("post_message", '', $post_message,
                    [
                        'input_id'    => $textarea_id,
                        'placeholder' => $locale['forum_0601']."...",
                        'required'  => TRUE,
                        'preview'   => TRUE,
                        'input_id'  => 'thread-qr-form',
                        'form_name' => $form_name,
                        'height'    => '300px',
                        'bbcode'    => TRUE,
                        'grippie'   => TRUE,
                        'tab'       => TRUE,
                    ]),
                'button'  => form_button('post_quick_reply', $locale['forum_0172'], $locale['forum_0172'], ['class' => 'btn-primary']),
                'options' => $options_field
            ]
        ];

        $html = openform($form_name, 'post', $options['remote_url'], $remote_param).form_hidden('post_cat', '', $post_id);
        $html .= display_quick_reply($info);
        $html .= closeform();

        return (string)$html;
    }

}
