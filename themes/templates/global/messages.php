<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: messages.php
| Author: Frederick MC Chan (Chan)
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

if (!function_exists('display_inbox')) {

    function display_inbox($info) {

        $locale = fusion_get_locale();
        /**
         * Message Reader Functions for Inbox, Outbox, Archive
         *
         * @param $info
         */
        $tpl = \PHPFusion\Template::getInstance('pm');
        $tpl->set_template(__DIR__.'/tpl/messages.html');
        $tpl->set_locale(fusion_get_locale());
        $tpl->set_tag('opentable', fusion_get_function('opentable', $locale['400']));
        $tpl->set_tag('closetable', fusion_get_function('closetable'));
        $tpl->set_tag('pagenav', $info['pagenav']);

        $folder_icons = [
            'inbox'   => 'fas fa-inbox',
            'outbox'  => 'fas fa-reply',
            'archive' => 'fas fa-file-alt',
        ];

        // Navigation
        $i = 1;
        foreach ($info['folders'] as $key => $folders) {
            $folders['class'] = ($info['current_folder'] == $key ? "class='active'" : "");
            $folders['total'] = '';
            $folders['icon'] = (isset($folder_icons[$key]) ? $folder_icons[$key] : "");
            if ($i < count($info['folders'])) {
                $total_key = $key."_total";
                $folders['total'] = $info[$total_key];
            }
            $tpl->set_block('folders', $folders);
            $i++;
        }

        //print_P($info['current_folder']);

        if ($info['current_folder']) {

            if ($info['current_folder'] === 'options') {
                // Configuration Page
                $tpl->set_block('settings', ['content' => $info['options_form']]);

            } else {

                if ($info['msg_read'] && isset($info['items'][$info['msg_read']])) { // read view

                    $data = $info['items'][$info['msg_read']];

                    $tpl->set_block('compose_button', $info['button']['new']);

                    $form = $info['actions_form']['openform'];
                    $form .= "<div class='btn-group display-inline-block m-r-10'>\n";
                    if (get('folder') == 'archive') {
                        $form .= $info['actions_form']['unlockbtn'];
                    } else if (get('folder') == "inbox") {
                        $form .= $info['actions_form']['lockbtn'];
                    }
                    $form .= $info['actions_form']['deletebtn'];
                    $form .= "</div>\n";

                    $form .= $info['actions_form']['closeform'];

                    $tpl->set_block('actions', ['form' => $form]);

                    $tpl->set_block('mail_read', [
                        'title'        => $data['message']['message_header'],
                        'avatar'       => display_avatar($data, "40px", '', FALSE, 'img-circle'),
                        'profile_link' => profile_link($data['user_id'], $data['user_name'], $data['user_status']),
                        'user_level'   => getgroupname($data['user_level']),
                        'date'         => showdate($locale['date_day'], $data['message_datestamp']),
                        'timer'        => timer($data['message_datestamp']),
                        'message'      => $data['message']['message_text'],
                        'reply_form'   => $info['reply_form']
                    ]);

                }
                else if ($info['msg_send']) { // send new message form

                    $tpl->set_block('send_form', ['content' => $info['reply_form']]);

                } else { // display view

                    $tpl->set_block('compose_button', $info['button']['new']);

                    $unread = \PHPFusion\Template::getInstance('pm-unread');
                    $unread->set_template(__DIR__.'/tpl/message_list.html');

                    $read = \PHPFusion\Template::getInstance('pm-read');
                    $read->set_template(__DIR__.'/tpl/message_list.html');

                    if (!empty($info['items'])) {

                        foreach ($info['items'] as $message_id => $message) {

                            $user = $message['contact_user'];
                            $message_arr = [
                                'checkbox_input' => form_checkbox('pmID', '', '', [
                                    'input_id' => 'mid-'.$message_id,
                                    'value'    => $message_id,
                                    'class'    => 'm-b-0'
                                ]),
                                'class'          => (empty($message['message_read']) ? " class='strong'" : ''),
                                'avatar'         => display_avatar($user, '20px', '', TRUE, 'img-rounded'),
                                'profile_link'   => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                                'message_link'   => $message['message']['link'],
                                'message_title'  => $message['message']['name'],
                                'datestamp'      => $message['message_datestamp'] > TIME - 86400 ? timer($message['message_datestamp']) : showdate($locale['date_day'], $message['message_datestamp']),
                                'timer'          => timer($message['message_datestamp']),
                            ];

                            if ($message['message_read']) {
                                $read_items[] = $message_arr;
                            } else {
                                $unread_items[] = $message_arr;
                            }
                        }
                    }

                    if (!empty($read_items)) {
                        foreach ($read_items as $item) {
                            $read->set_block('message', $item);
                        }
                    } else {
                        $read->set_block('no_item', ['text' => $locale['471']]);
                    }
                    if (!empty($unread_items)) {
                        foreach ($unread_items as $item) {
                            $unread->set_block('message', $item);
                        }
                    } else {
                        $unread->set_block('no_item', ['text' => $locale['471']]);
                    }

                    $form = $info['actions_form']['openform'];

                    $form .= "<div class='dropdown display-inline-block m-r-10'>
                        <a href='#' data-toggle='dropdown' class='btn btn-default btn-sm dropdown-toggle'><i id='chkv' class='fa fa-square-o'></i><span class='caret m-l-5'></span></a>
                        <ul class='dropdown-menu'>";
                    foreach ($info['actions_form']['check'] as $id => $title) {
                        $form .= "<li><a id='".$id."' data-action='check' class='pointer'>".$title."</a></li>";
                    }
                    $form .= "</ul>
                    </div>";

                    $form .= "<div class='btn-group display-inline-block m-r-10'>\n";
                    if (get('folder') == 'archive') {
                        $form .= $info['actions_form']['unlockbtn'];
                    } else if (get('folder') !== 'outbox') {
                        $form .= $info['actions_form']['lockbtn'];
                    }
                    $form .= $info['actions_form']['deletebtn'];
                    $form .= "</div>\n";

                    $form .= "<div class='dropdown display-inline-block m-r-10'>
                        <a href='#' data-toggle='dropdown' class='btn btn-default btn-sm dropdown-toggle'>".$locale['444']."&hellip; <span class='caret'></span></a>
                        <ul class='dropdown-menu'>
                            <li>".$info['actions_form']['mark_all']."</li>
                            <li>".$info['actions_form']['mark_read']."</li>
                            <li>".$info['actions_form']['mark_unread']."</li>
                            <li>".$info['actions_form']['unmark_all']."</li>
                        </ul>
                    </div>\n";

                    $form .= $info['actions_form']['closeform'];

                    $tpl->set_block('actions', ['form' => $form]);

                    $tpl->set_block('mailbox', [
                            'unread_content' => $unread->get_output(),
                            'read_content'   => $read->get_output()
                        ]
                    );
                }
            }
        }

        return (string)$tpl->get_output();
    }
}
