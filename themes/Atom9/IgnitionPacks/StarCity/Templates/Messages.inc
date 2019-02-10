<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/IgnitionPacks/StarCity/Templates/Messages.inc
| Author: Frederick MC Chan (Chan)
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Atom9Theme\IgnitionPacks\StarCity\Templates;

use Atom9Theme\Core;
class Messages extends Core {
    public static function display_inbox($info) {
        $locale = fusion_get_locale('', ATOM9_LOCALE);

        self::SetParam('panels', FALSE);

        add_to_head('<link rel="stylesheet" href="'.IGNITION_PACK.'Templates/css/messages.css"/>');

        echo '<div class="starmail">';
            echo '<div class="row equal-height mail-top">';
                echo '<div class="col-xs-12 col-sm-3"><div class="mail-left">';
                    $icons = [
                        'inbox'   => 'inbox',
                        'outbox'  => 'send-o',
                        'archive' => 'archive',
                        'options' => 'cog'
                    ];

                    echo '<ul class="mail-menu">';
                        $i = 0;
                        foreach ($info['folders'] as $key => $folder) {
                            $active = (isset($_GET['folder']) && $_GET['folder'] == $key) || (isset($_GET['msg_send']) && $_GET['msg_send'] == $key) ? ' class="active"' : '';
                            echo '<li'.$active.'><a href="'.$folder['link'].'" title="'.$folder['title'].'">';
                                echo '<i class="fa fa-'.$icons[$key].'"></i>';
                                if ($i < count($info['folders']) - 1) {
                                    $total_key = $key."_total";
                                    echo '<span class="badge">'.$info[$total_key].'</span>';
                                }
                            echo '</a></li>';
                            $i++;
                        }
                    echo '</ul>';
                echo '</div></div>';
                echo '<div class="col-xs-12 col-sm-9"><div class="mail-right clearfix">';
                    if (!isset($_GET['msg_send']) && (!empty($info['actions_form']) || isset($_GET['msg_read']))) {
                        if (isset($_GET['msg_read'])) {
                            echo '<a class="btn btn-default pull-left m-r-10" href="'.$info['button']['back']['link'].'" title="'.$info['button']['back']['title'].'"><i class="fa fa-long-arrow-left"></i></a>';
                        }

                        echo !empty($info['actions_form']) ? '<div class="display-inline-block pull-left">'.$info['actions_form'].'</div>' : '';
                        echo !empty($info['pagenav']) ? '<div class="display-inline-block">'.$info['pagenav'].'</div>' : '';
                    }
                    echo '<a class="btn btn-primary pull-right" href="'.$info['button']['new']['link'].'">'.$locale['401'].'</a>';
                echo '</div></div>';
            echo '</div>';

            echo '<div class="row equal-height mail-body">';

            if (isset($_GET['msg_send'])) {
                echo '<div class="col-xs-12 mail-form">';
                    echo $info['reply_form'];
                echo '</div>';
            } else if ($_GET['folder'] == "options") {
                echo '<div class="col-xs-12 mail-options">';
                    echo $info['options_form'];
                echo '</div>';
            } else {
                echo '<div class="col-xs-12 col-sm-3"><div class="mail-left">';
                    self::Inbox($info);
                echo '</div></div>';
                echo '<div class="col-xs-12 col-sm-9"><div class="mail-right">';
                    if (!empty($info['items'])) {
                        $message = !empty($_GET['msg_read']) && isset($info['items'][$_GET['msg_read']]) ? $info['items'][$_GET['msg_read']] : current($info['items']);

                        echo '<div class="message-header">';
                            echo '<div class="overflow-hide">';
                                echo '<div class="pull-right m-t-10">';
                                    echo showdate('longdate', $message['message_datestamp']).' '.timer($message['message_datestamp']);

                                    if (!isset($_GET['msg_read']) && isset($_GET['folder']) && $_GET['folder'] == 'inbox') {
                                        echo '<a class="btn btn-primary m-l-10" href="'.$message['message']['link'].'">'.$locale['433'].'</a>';
                                    }
                                echo '</div>';

                                echo '<div class="pull-left m-t-10">'.display_avatar($message, '40px', '', FALSE, 'img-rounded m-r-10').'</div>';
                                echo '<h4 class="m-b-0">'.$message['message_subject'].'</h4>';
                                echo '<span>'. $locale['406'].': '.profile_link($message['contact_user']['user_id'], $message['contact_user']['user_name'], $message['contact_user']['user_status']).'</span>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div class="message-detail">'.nl2br(parse_textarea($message['message']['message_text'], TRUE, TRUE, FALSE)).'</div>';

                        if (!empty($info['reply_form'])) {
                            echo '<div class="message-detail">'.$info['reply_form'].'</div>';
                        }
                    }
                echo '</div></div>';
            }
            echo '</div>';
        echo '</div>'; // .starmail
    }

    private static function Inbox($info) {
        $locale = fusion_get_locale();

        $is_inbox = isset($_GET['folder']) && $_GET['folder'] == 'inbox' ? TRUE : FALSE;

        if (!empty($info['items'])) {
            $unread = [];
            $read = [];

            if ($is_inbox) {
                foreach ($info['items'] as $message_id => $messageData) {
                    if ($messageData['message_read'] == 1) {
                        $read[$message_id] = $messageData;
                    } else {
                        $unread[$message_id] = $messageData;
                    }
                }
            } else {
                foreach ($info['items'] as $message_id => $messageData) {
                    $read[$message_id] = $messageData;
                }
            }

            add_to_jquery('
                var unread_checkbox = $("#unread_tbl .item").find(":checkbox");
                var read_checkbox = $("#read_tbl .item").find(":checkbox");

                $("#check_all_pm").unbind("click");
                $("#check_all_pm").bind("click", function () {
                    var action = $(this).data("action");
                    if (action === "check") {
                        unread_checkbox.prop("checked", true);
                        read_checkbox.prop("checked", true);
                        $("#unread_tbl .item").addClass("selected");
                        $("#read_tbl .item").addClass("selected");
                        $("#chkv").removeClass("fa fa-square-o").addClass("fa fa-minus-square-o");
                        $(this).data("action", "uncheck");
                        $("#selectedPM").val(checkedCheckbox());
                    } else {
                        unread_checkbox.prop("checked", false);
                        read_checkbox.prop("checked", false);
                        $("#unread_tbl .item").removeClass("selected");
                        $("#read_tbl .item").removeClass("selected");
                        $("#chkv").removeClass("fa fa-minus-square-o").addClass("fa fa-square-o");
                        $(this).data("action", "check");
                        $("#selectedPM").val(checkedCheckbox());
                    }
                });

                $("#check_read_pm").unbind("click");
                $("#check_read_pm").bind("click", function () {
                    var action = $(this).data("action");
                    if (action === "check") {
                        read_checkbox.prop("checked", true);
                        $("#read_tbl .item").addClass("selected");
                        $("#chkv").removeClass("fa fa-square-o").addClass("fa fa-minus-square-o");
                        $(this).data("action", "uncheck");
                        $("#selectedPM").val(checkedCheckbox());
                    } else {
                        read_checkbox.prop("checked", false);
                        $("#read_tbl .item").removeClass("selected");
                        $("#chkv").removeClass("fa fa-minus-square-o").addClass("fa fa-square-o");
                        $(this).data("action", "check");
                        $("#selectedPM").val(checkedCheckbox());
                    }
                });

                $("#check_unread_pm").unbind("click");
                $("#check_unread_pm").bind("click", function () {
                    var action = $(this).data("action");
                    if (action === "check") {
                        unread_checkbox.prop("checked", true);
                        $("#unread_tbl .item").addClass("selected");
                        $("#chkv").removeClass("fa fa-square-o").addClass("fa fa-minus-square-o");
                        $(this).data("action", "uncheck");
                        $("#selectedPM").val(checkedCheckbox());
                    } else {
                        unread_checkbox.prop("checked", false);
                        $("#unread_tbl .item").removeClass("selected");
                        $("#chkv").removeClass("fa fa-minus-square-o").addClass("fa fa-square-o");
                        $(this).data("action", "check");
                        $("#selectedPM").val(checkedCheckbox());
                    }
                });

                $("input[type=checkbox]").unbind("click");
                $("input[type=checkbox]").bind("click", function () {
                    var selectedpm = $("#selectedPM");
                    var checkList = selectedpm.val();
                    if ($(this).is(":checked")) {
                        $(this).parents(".item").addClass("selected");
                        checkList += $(this).val() + ",";
                    } else {
                        $(this).parents(".item").removeClass("selected");
                        checkList = checkList.replace($(this).val() + ",", "");
                    }
                    selectedpm.val(checkList);
                });
            ');

            if ($is_inbox) {
                // Inbox
                echo '<a data-target="#unread_inbox" class="pointer mail-list-title" data-toggle="collapse">'.$locale['446'].' <span class="caret"></span></a>';
                echo '<div id="unread_inbox" class="collapse in">';
                    if (!empty($unread)) {
                        echo '<ul class="mail-list" id="unread_tbl">';
                            $i = 0;
                            foreach ($unread as $id => $messageData) {
                                $active = !empty($_GET['msg_read']) && $_GET['msg_read'] == $id ? TRUE : FALSE;

                                echo '<li class="item'.($active == TRUE ? ' active' : '').'">';
                                    echo '<a href="'.$messageData['message']['link'].'">';
                                        echo '<div class="pull-left">'.form_checkbox('pmID', '', '', [
                                            'input_id' => 'pmID-'.$id,
                                            'value'    => $id,
                                            'class'    => 'm-t-10 m-r-5'
                                        ]).'</div>';

                                        echo '<div class="overflow-hide">';
                                            echo '<div class="msg-list-heading">';
                                                echo '<span class="text-uppercase pull-right">'.date('d M', $messageData['message_datestamp']).'</span>';
                                                echo '<span>'.$messageData['contact_user']['user_name'].'</span>';
                                            echo '</div>';
                                            echo '<strong>'.trim_text($messageData['message']['name'], 20).'</strong>';
                                        echo '</div>';
                                    echo '</a>';
                                echo '</li>';
                                $i++;
                            }
                        echo '</ul>';
                    } else {
                        echo '<div class="no-messages text-center">'.$locale['471'].'</div>';
                    }
                echo '</div>';

                echo '<a data-target="#read_inbox" class="pointer mail-list-title" data-toggle="collapse">'.$locale['447'].' <span class="caret"></span></a>';
                echo '<div id="read_inbox" class="collapse in">';
                    if (!empty($read)) {
                        echo '<ul class="mail-list m-b-0" id="read_tbl">';
                            $i = 0;
                            foreach ($read as $id => $messageData) {
                                $active = !empty($_GET['msg_read']) && $_GET['msg_read'] == $id ? TRUE : FALSE;

                                echo '<li class="item'.($active == TRUE ? ' active' : '').'">';
                                    echo '<a href="'.$messageData['message']['link'].'">';
                                        echo '<div class="pull-left">'.form_checkbox('pmID', '', '', [
                                            'input_id' => 'pmID-'.$id,
                                            'value'    => $id,
                                            'class'    => 'm-t-10 m-r-5'
                                        ]).'</div>';

                                        echo '<div class="overflow-hide">';
                                            echo '<div class="msg-list-heading">';
                                                echo '<span class="text-uppercase pull-right">'.date('d M', $messageData['message_datestamp']).'</span>';
                                                echo '<span>'.$messageData['contact_user']['user_name'].'</span>';
                                            echo '</div>';
                                            echo '<strong>'.trim_text($messageData['message']['name'], 20).'</strong>';
                                        echo '</div>';
                                    echo '</a>';
                                echo '</li>';
                                $i++;
                            }
                        echo '</ul>';
                    }
                echo '</div>';
            } else {
                // Outbox & Archive
                if (!empty($read)) {
                    echo '<ul class="mail-list m-b-0" id="read_tbl">';
                        $i = 0;
                        foreach ($read as $id => $messageData) {
                            $active = !empty($_GET['msg_read']) && $_GET['msg_read'] == $id ? TRUE : FALSE;

                            echo '<li class="item'.($active == TRUE ? ' active' : '').'">';
                                echo '<a href="'.$messageData['message']['link'].'">';
                                    echo '<div class="pull-left">'.form_checkbox('pmID', '', '', [
                                        'input_id' => 'pmID-'.$id,
                                        'value'    => $id,
                                        'class'    => 'm-t-10 m-r-5'
                                    ]).'</div>';

                                    echo '<div class="overflow-hide">';
                                        echo '<div class="msg-list-heading">';
                                            echo '<span class="text-uppercase pull-right">'.date('d M', $messageData['message_datestamp']).'</span>';
                                            echo '<span>'.$messageData['contact_user']['user_name'].'</span>';
                                        echo '</div>';
                                        echo '<strong>'.trim_text($messageData['message']['name'], 20).'</strong>';
                                    echo '</div>';
                                echo '</a>';
                            echo '</li>';
                            $i++;
                        }
                    echo '</ul>';
                }
            }
        } else {
            echo '<div class="no-messages text-center">'.$info['no_item'].'</div>';
        }
    }
}
