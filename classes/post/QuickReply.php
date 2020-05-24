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

use PHPFusion\Infusions\Forum\Classes\ForumServer;

/**
 * Class Quick_Reply
 *
 * @package PHPFusion\Infusions\Forum\Classes\Post
 */
class QuickReply extends ForumServer {

    /**
     * Forum Reply Text Box
     *
     * @param       $info
     * @param array $options
     *
     * @return string
     * @throws \ReflectionException
     */
    /**
     * @param       $info
     * @param array $options
     *
     * @return string
     */
    public function getForm($info, array $options = []) {

        $default_options = [
            "post_id"    => 0, // reply to post id
            "quote"      => FALSE, // attach reply as quote
            "remote_url" => FORUM."viewthread.php?thread_id=".$info['thread_id'],
        ];
        $user_id = fusion_get_userdata('user_id');
        $options += $default_options;

        $locale = fusion_get_locale();

        $post_message = '';
        $post_id = $options['post_id'];
        $form_name = "thread_reply";
        $textarea_id = "post_message";

        $quote = get('quote', FILTER_VALIDATE_INT);
        if ($quote) {
            $result = dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE post_id=:pid", [':pid' => (int)$quote]);
            if (dbrows($result)) {
                $quotedata = dbarray($result);
                $post_id = $quotedata['post_id'];
                $post_message = nl2br("[quote]".$quotedata['post_message']."[/quote]");
                $form_name = "post_reply_".$post_id;
                $textarea_id = "post_message_".$post_id;
            } else {
                addNotice('warning', 'Post to quote could not be found.');
                redirect(clean_request('', ['quote'], FALSE));
            }
        }

        $post_reply = get('reply', FILTER_VALIDATE_INT);

        // Buttons Checkbox
        $options_field = form_checkbox('post_smileys', $locale['forum_0169'], '', ['class' => 'm-r-10', 'type' => 'button', 'ext_tip' => $locale['forum_0622']]);
        if (fusion_get_userdata('user_sig')) {
            $options_field .= form_checkbox('post_showsig', $locale['forum_0264'], '1', ['class' => 'm-r-10', 'type' => 'button', 'ext_tip' => $locale['forum_0170']]);
        }
        if (parent::get_forum_settings('thread_notify')) {
            $options_field .= form_checkbox('notify_me', $locale['forum_0552'], $info['user_tracked'], ['class' => 'm-r-10', 'type' => 'button', 'ext_tip' => $locale['forum_0171']]);
        }

        $file_upload = form_button('file_upload_btn', 'Upload Attachments', 'file_upload_btn');
        $file_upload .= '<input id="file" type="file" style="display:none;" accept="image/*" multiple>';

        add_to_jquery("
        let addInTextarea = function(el, newText) {
          var start = el.prop('selectionStart')
          var end = el.prop('selectionEnd')
          var text = el.val()
          var before = text.substring(0, start)
          var after  = text.substring(end, text.length)
          el.val(before + newText + after)
          el[0].selectionStart = el[0].selectionEnd = start + newText.length
          el.focus()
        }
        $('#file_upload_btn').bind('click', function(e) {
            e.preventDefault();
            $('#file').click();
        });

        $('#file').on('change', function(e) {
            let formData = new FormData();
            var ins = this.files.length;
            for (var x = 0; x < ins; x++) {
                formData.append('upload[]', this.files[x]);
            }

            formData.append('fusion_token', '".fusion_get_token('post_attachments', 10)."');
            formData.append('form_id', 'post_attachments');
            formData.append('thread_id', '".$info['thread_id']."');
            formData.append('user_id', '".$user_id."');

            $.ajax({
                url: '".fusion_get_settings('siteurl')."infusions/forum/classes/post/attach.php',  //Server script to process data
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(html){
                   $.each(html, function(index) {
                        let image = html[index].html;
                        let error = html[index].error;
                        $('.quick-img-container').append(image);
                        if (error) {
                            alert(error);
                        }
                   });
                },
                error: function(e) {
                    console.log('Something went wrong!');
                }
            });
        });

        $('body').on('click', '.insert-image', function(e) {
            e.preventDefault();

            let aid = $(this).data('id');
            let size =  $(this).data('size');
            console.log(size);
            let formData = new FormData();
            formData.append('fusion_token', '".fusion_get_token('post_attachments', 10)."');
            formData.append('form_id', 'post_attachments');
            formData.append('thread_id', '".$info['thread_id']."');
            formData.append('user_id', '".$user_id."');
            formData.append('attach_size', size );
            formData.append('attach_id', aid );

            $.ajax({
                url: '".fusion_get_settings('siteurl')."infusions/forum/classes/post/attach-insert.php',  //Server script to process data
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(html){
                    // if remove
                   if (html.image_name) {
                        addInTextarea($('#$textarea_id'), html.image_name);
                   } else if (size == 'remove') {
                        $('#atc_' + aid).remove();
                   }
                },
                error: function(e) {
                    console.log('Something went wrong!');
                }
            });
        });
        ");

        // Attachments
        $result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_user=:uid AND thread_id=:tid AND post_id=0 ORDER BY attach_id ASC", [
            ':tid' => (int)$info['thread_id'],
            ':uid' => $user_id,
        ]);
        $attachments = [];
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $attachments[] = $data;
            }
        }
        $box_header = '';

        $remote_param = $options['remote_url'] !== $default_options['remote_url'] ? ["remote_url" => $options['remote_url']] : [];

        $info += [
            'openform'    => openform($form_name, 'post', $options['remote_url'], $remote_param).form_hidden('post_cat', '', $post_id),
            'description' => $locale['forum_0168'],
            'attachments' => (array)$attachments,
            'header'      => $box_header,
            'field'       => [
                'message'     => form_textarea("post_message", '', $post_message,
                        [
                            'input_id'    => $textarea_id,
                            'placeholder' => $locale['forum_0601']."...",
                            'required'    => TRUE,
                            'preview'     => TRUE,
                            'form_name'   => $form_name,
                            'height'      => '250px',
                            'descript'    => FALSE,
                            'class'       => 'm-b-20',
                            'bbcode'      => TRUE,
                            'grippie'     => TRUE,
                            'tab'         => TRUE,
                            'post_attach' => TRUE,
                        ]).form_hidden('post_cat', '', $post_reply, ['type' => 'number']),
                'button'      => form_button('post_quick_reply', $locale['forum_0172'], $locale['forum_0172'], ['class' => 'btn-primary']),
                'options'     => $options_field,
                'file_upload' => $file_upload
            ]
        ];

        $html = openform($form_name, 'post', $options['remote_url'], $remote_param).form_hidden('post_cat', '', $post_id);
        $html .= display_quick_reply($info);
        $html .= closeform();

        return (string)$html;
    }
}
