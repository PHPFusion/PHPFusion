<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PrivateMessages.inc
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
namespace PHPFusion;

class PrivateMessages {

    private $info = array(
        'items' => array()
    );

    public $locale = array();

    /**
     * @return array
     */
    public function getInfo() {
        return $this->info;
    }

    public function display_inbox() {

        $userdata = fusion_get_userdata();

        if (isset($_POST['cancel'])) {
            redirect(BASEDIR."messages.php");
        }

        $this->setInbox();
        if ($_GET['folder'] == "options") {
            $this->display_settings();
        } else {

            add_to_title($this->locale['global_201'].$this->info['folders'][$_GET['folder']]['title']);

            set_meta("description", $this->info['folders'][$_GET['folder']]['title']);

            $query = array(
                "outbox"  => array($this->info['outbox_total'], "message_folder='1'"),
                "inbox"   => array($this->info['inbox_total'], "message_folder='0'"),
                "archive" => array($this->info['archive_total'], "message_folder='2'"),
            );

            if ($query[$_GET['folder']][0] > 0) {

                // Get messages
                $result = dbquery("SELECT m.*, u.user_id, u.user_name, u.user_status, u.user_avatar,
                max(m.message_id) as last_message
                FROM ".DB_MESSAGES." m
                INNER JOIN ".DB_USERS." u ON (m.message_from=u.user_id)
                WHERE message_to='".$userdata['user_id']."' and ".$query[$_GET['folder']][1]."
                group by message_id
                ORDER BY m.message_datestamp DESC
                ");

                $this->info['max_rows'] = dbrows($result);
                if ($this->info['max_rows']) {

                    while ($data = dbarray($result)) {
                        $data['contact_user'] = [
                            'user_id'     => $data['user_id'],
                            'user_name'   => $data['user_name'],
                            'user_status' => $data['user_status'],
                            'user_avatar' => $data['user_avatar']
                        ];
                        $data['message'] = [
                            'link'           => BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_read=".$data['message_id'],
                            'name'           => $data['message_subject'],
                            'message_header' => "<strong>".$this->locale['462'].":</strong> ".$data['message_subject'],
                            'message_text'   => $data['message_smileys'] == "y" ? parseubb(parsesmileys($data['message_message'])) : parseubb($data['message_message']),
                        ];
                        $this->info['items'][$data['message_id']] = $data;
                    }

                    // set read
                    if (isset($_GET['msg_read']) && isnum($_GET['msg_read']) && isset($this->info['items'][$_GET['msg_read']])) {
                        dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".intval($_GET['msg_read'])."'");
                    }

                } else {
                    $this->info['no_item'] = $this->locale['471'];
                }
            } else {
                $this->info['no_item'] = $this->locale['471'];
            }

            // Message Actions
            if (isset($_POST['archive_pm'])) {
                $this->archive_pm();
            } elseif (isset($_POST['unarchive_pm'])) {
                $this->unarchive_pm();
            } elseif (isset($_POST['delete_pm'])) {
                $this->delete_pm();
            } elseif (isset($_POST['mark'])) {
                $this->mark_pm();
            }
            // Actions buttons - archive, delete, mark all read, mark all unread, mark as read, mark as unread
            ob_start();
            if (isset($_GET['msg_read'])) {
                echo openform("actionform", "post", FUSION_REQUEST, array("class" => "display-inline-block m-l-10"));
                echo form_hidden("selectedPM", "", $_GET['msg_read']);
                echo "<div class='btn-group display-inline-block m-r-10'>\n";
                if ($_GET['folder'] == "archive") {
                    echo form_button("unarchive_pm", "", "archive_pm", array("icon" => "fa fa-unlock"));
                } elseif ($_GET['folder'] == "inbox") {
                    echo form_button("archive_pm", "", "archive_pm", array("icon" => "fa fa-lock"));
                }
                echo form_button("delete_pm", "", "delete_pm", array("icon" => "fa fa-trash-o"));
                echo "</div>\n";
                echo closeform();
            } else {
                echo openform("actionform", "post", FUSION_REQUEST);
                ?>
                <!-- pm_idx -->
                <div class="dropdown display-inline-block m-r-10">
                    <a href="#" data-toggle="dropdown" class="btn btn-default dropdown-toggle"><i id="chkv" class="fa fa-square-o"></i>
                        <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a id="check_all_pm" data-action="check" class="pointer"><?php echo $this->locale['418'] ?></a>
                        </li>
                        <li><a id="check_unread_pm" data-action="check" class="pointer"><?php echo $this->locale['415'] ?></a>
                        </li>
                        <li><a id="check_read_pm" data-action="check" class="pointer"><?php echo $this->locale['414'] ?></a>
                        </li>
                    </ul>
                </div>
                <?php
                echo form_hidden("selectedPM", "", "");
                echo "<div class='btn-group display-inline-block m-r-10'>\n";
                if ($_GET['folder'] == 'archive') {
                    echo form_button("unarchive_pm", "", "unarchive_pm", array("icon" => "fa fa-unlock"));
                } else {
                    if ($_GET['folder'] !== 'outbox') {
                        echo form_button("archive_pm", "", "archive_pm", array("icon" => "fa fa-lock"));
                    }
                }
                echo form_button("delete_pm", "", "delete_pm", array("icon" => "fa fa-trash-o"));
                echo "</div>\n";
                ?>
                <div class="dropdown display-inline-block m-r-10">
                    <a href="#" data-toggle="dropdown"
                       class="btn btn-default dropdown-toggle"><?php echo $this->locale['444'] ?>&hellip; <span
                            class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><?php echo form_button("mark", $this->locale['493'], "mark_all", array("class" => "btn-link")) ?></li>
                        <li><?php echo form_button("mark", $this->locale['494'], "mark_read", array("class" => "btn-link")) ?></li>
                        <li><?php echo form_button("mark", $this->locale['495'], "mark_unread", array("class" => "btn-link")) ?></li>
                        <li><?php echo form_button("mark", $this->locale['496'], "unmark_all", array("class" => "btn-link")) ?></li>
                    </ul>
                </div>
                <?php
                echo closeform();
                add_to_jquery("
            function checkedCheckbox() {
                var checkList = '';
                $('input[type=checkbox]').each(function() {
                    if (this.checked) {
                        checkList += $(this).val()+',';
                    }
                });
                return checkList;
            }
            $('#check_all_pm').bind('click', function() {
                var unread_checkbox = $('#unread_tbl tr').find(':checkbox');
                var read_checkbox = $('#read_tbl tr').find(':checkbox');
                var action = $(this).data('action');
                if (action == 'check') {
                    unread_checkbox.prop('checked', true);
                    read_checkbox.prop('checked', true);
                    $('#unread_tbl tr').addClass('warning');
                    $('#read_tbl tr').addClass('warning');
                    $('#chkv').removeClass('fa fa-square-o').addClass('fa fa-minus-square-o');
                    $(this).data('action', 'uncheck');
                    $('#selectedPM').val(checkedCheckbox());
                } else {
                    unread_checkbox.prop('checked', false);
                    read_checkbox.prop('checked', false);
                    $('#unread_tbl tr').removeClass('warning');
                    $('#read_tbl tr').removeClass('warning');
                    $('#chkv').removeClass('fa fa-minus-square-o').addClass('fa fa-square-o');
                    $(this).data('action', 'check');
                    $('#selectedPM').val(checkedCheckbox());
                }
            });
            $('#check_read_pm').bind('click', function() {
                var read_checkbox = $('#read_tbl tr').find(':checkbox');
                var action = $(this).data('action');
                if (action == 'check') {
                    read_checkbox.prop('checked', true);
                    $('#read_tbl tr').addClass('warning');
                    $('#chkv').removeClass('fa fa-square-o').addClass('fa fa-minus-square-o');
                    $(this).data('action', 'uncheck');
                    $('#selectedPM').val(checkedCheckbox());
                } else {
                    read_checkbox.prop('checked', false);
                    $('#read_tbl tr').removeClass('warning');
                    $('#chkv').removeClass('fa fa-minus-square-o').addClass('fa fa-square-o');
                    $(this).data('action', 'check');
                    $('#selectedPM').val(checkedCheckbox());
                }
            });
            $('#check_unread_pm').bind('click', function() {
                var unread_checkbox = $('#unread_tbl tr').find(':checkbox');
                var action = $(this).data('action');
                if (action == 'check') {
                    unread_checkbox.prop('checked', true);
                    $('#unread_tbl tr').addClass('warning');
                    $('#chkv').removeClass('fa fa-square-o').addClass('fa fa-minus-square-o');
                    $(this).data('action', 'uncheck');
                    $('#selectedPM').val(checkedCheckbox());
                } else {
                    unread_checkbox.prop('checked', false);
                    $('#unread_tbl tr').removeClass('warning');
                    $('#chkv').removeClass('fa fa-minus-square-o').addClass('fa fa-square-o');
                    $(this).data('action', 'check');
                    $('#selectedPM').val(checkedCheckbox());
                }
            });
            $('input[type=checkbox]').bind('click', function() {
                var checkList = $('#selectedPM').val();
                if ($(this).is(':checked')) {
                $(this).parents('tr').addClass('warning');
                    checkList += $(this).val()+',';
                } else {
                    $(this).parents('tr').removeClass('warning');
                    checkList = checkList.replace($(this).val()+',', '');
                }
                $('#selectedPM').val(checkList);
            });
            ");
            }
            $this->info['actions_form'] = ob_get_contents();
            ob_end_clean();
            // The mail forms
            if (isset($_GET['msg_read']) || isset($_GET['msg_send'])) {
                if (isset($_POST['send_pm']) || isset($_POST['send_message'])) {
                    $this->send_message();
                }
                if (isset($_GET['msg_read'])) {
                    $this->pm_form();
                } elseif (isset($_GET['msg_send'])) {
                    $this->pm_mainForm();
                }
            }
        }
    }

    private function setInbox() {

        $userdata = fusion_get_userdata();
        /**
         * Sanitize environment
         */
        $myStatus = self::get_pm_settings($userdata['user_id']);
        //print_p($myStatus);
        if (!isset($_GET['folder']) || !preg_check("/^(inbox|outbox|archive|options)$/", $_GET['folder'])) {
            $_GET['folder'] = "inbox";
        }
        function validate_user($user_id) {
            if (isnum($user_id) && dbcount("(user_id)", DB_USERS,
                                           "user_id='".intval($user_id)."' AND user_status ='0'")
            ) {
                return TRUE;
            }

            return FALSE;
        }

        if (isset($_POST['msg_send']) && isnum($_POST['msg_send']) && validate_user($_POST['msg_send'])) {
            $_GET['msg_send'] = $_POST['msg_send'];
        }
        // prohibits send message to non-existing group
        $user_group = fusion_get_groups();
        unset($user_group[0]);
        if (isset($_POST['msg_to_group']) && isnum($_POST['msg_to_group']) && isset($user_group[$_POST['msg_to_group']])) {
            $_GET['msg_to_group'] = $_POST['msg_to_group'];
        }
    $in_read = dbcount("(message_id)", DB_MESSAGES, "message_user='".$userdata['user_id']."' and message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
    $out_read = dbcount("(message_id)", DB_MESSAGES, "message_user='".$userdata['user_id']."' and message_to='".$userdata['user_id']."' AND message_folder='1'");
    $arc_read = dbcount("(message_id)", DB_MESSAGES, "message_user='".$userdata['user_id']."' and message_to='".$userdata['user_id']."' AND message_folder='2' AND message_read='0'");
        $this->info = [
            'folders' => [
                'inbox'   => ['link' => BASEDIR."messages.php?folder=inbox", 'title' => $this->locale['402']],
                'outbox'  => ['link' => BASEDIR."messages.php?folder=outbox", 'title' => $this->locale['403']],
                'archive' => ['link' => BASEDIR."messages.php?folder=archive", 'title' => $this->locale['404']],
                'options' => ['link' => BASEDIR."messages.php?folder=options", 'title' => $this->locale['425']],
            ],
            'inbox_total'   => dbcount("(message_id)", DB_MESSAGES, "message_user='".$userdata['user_id']."' AND message_to='".$userdata['user_id']."' AND message_folder='0'")."/".$in_read,
            'outbox_total'  => dbcount("(message_id)", DB_MESSAGES, "message_from='".$userdata['user_id']."' AND message_folder='0'")."/".$out_read,
            'archive_total' => dbcount("(message_id)", DB_MESSAGES, "message_user='".$userdata['user_id']."' AND message_to='".$userdata['user_id']."' AND message_folder='2'")."/".$arc_read,
            'button' => [
                'new' => [
                    'link' => BASEDIR."messages.php?msg_send=new",
                    'name' => $this->locale['401']
                ],
                'options' => ['link' => BASEDIR."messages.php?folder=options", 'name' => $this->locale['425']],
            ],
            'actions_form' => "",
        ];
        add_to_title($this->locale['global_200'].$this->locale['400']);
        add_to_meta("description", $this->locale['400']);
    }

    public static function get_pm_settings($user_id, $key = NULL) {
        if (iMEMBER) {
            $userdata = fusion_get_userdata();
            // make sure they have it when registering
            $settings = [
                'user_inbox'           => fusion_get_settings('pm_inbox_limit'),
                'user_outbox'          => fusion_get_settings('pm_outbox_limit'),
                'user_archive'         => fusion_get_settings('pm_archive_limit'),
                'user_pm_email_notify' => fusion_get_settings('pm_email_notify'),
                'user_pm_save_sent'    => fusion_get_settings('pm_save_sent'),
            ];
            if ($user_id !== $userdata) {
                $result = dbquery("
                    SELECT user_inbox, user_outbox, user_archive, user_pm_email_notify, user_pm_save_sent
                    FROM ".DB_USERS." WHERE user_id='".intval($user_id)."' and user_status = '0'
                ");
                if (dbrows($result)) {
                    $data = dbarray($result);
                    // What this does is that if any of the params is 0, we use default system values.
                    $settings = [
                        'user_inbox'           => !empty($data['user_inbox']) ? intval($data['user_inbox']) : intval($settings['user_inbox']),
                        'user_outbox'          => !empty($data['user_outbox']) ? intval($data['user_outbox']) : intval($settings['user_outbox']),
                        'user_archive'         => !empty($data['user_archive']) ? intval($data['user_archive']) : intval($settings['user_archive']),
                        'user_pm_email_notify' => !empty($data['user_pm_email_notify']) ? intval($data['user_pm_email_notify']) : intval($settings['user_pm_email_notify']),
                        'user_pm_save_sent'    => !empty($data['user_pm_save_sent']) ? intval($data['user_pm_save_sent']) : intval($settings['user_pm_save_sent']),
                    ];
                }
            } else {
                $settings = [
                    'user_inbox'           => $userdata['user_inbox'],
                    'user_outbox'          => $userdata['user_outbox'],
                    'user_archive'         => $userdata['user_archive'],
                    'user_pm_email_notify' => $userdata['user_pm_email_notify'],
                    'user_pm_save_sent'    => $userdata['user_pm_save_sent']
                ];
            }
            if (iADMIN || iSUPERADMIN) {
                $settings['user_inbox'] = 0;
                $settings['user_outbox'] = 0;
                $settings['user_archive'] = 0;
            }

            return $key === NULL ? $settings : (isset($settings[$key]) ? $settings[$key] : NULL);
        }

        return NULL;
    }

    /**
     * PM settings page
     */
    public function display_settings() {

        $userdata = fusion_get_userdata();

        if (isset($_POST['save_options'])) {
            $data = [
                'user_id'              => $userdata['user_id'],
                'user_pm_email_notify' => form_sanitizer($_POST['pm_email_notify'], 0, 'pm_email_notify'),
                'user_pm_save_sent'    => form_sanitizer($_POST['pm_save_sent'], 0, 'pm_save_sent'),
            ];
            dbquery_insert(DB_USERS, $data, "update");
            addNotice("success", $this->locale['445']);
            redirect(BASEDIR."messages.php?folder=options");
        }
        ob_start();
        echo openform('pm_form', 'post', FUSION_REQUEST);
        $options = [
            '0' => $this->locale['520'],
            '1' => $this->locale['521'],
            '2' => $this->locale['522'],
        ];
        echo form_select('pm_email_notify', $this->locale['621'], $userdata['user_pm_email_notify'], ['options' => $options]);
        $options = [
            '0' => $this->locale['520'],
            '1' => $this->locale['523'],
            '2' => $this->locale['524'],
        ];
        echo form_select('pm_save_sent', $this->locale['622'], $userdata['user_pm_save_sent'], ['options' => $options]);
        echo form_button('save_options', $this->locale['623'], $this->locale['623'], ['class' => 'btn btn-primary']);
        echo closeform();
        $this->info['options_form'] = ob_get_contents();
        ob_end_clean();
    }

    /**
     * Actions : archive
     * Require - $_POST selectedPM, delete_pm
     * SQL archive action
     */
    private function archive_pm() {

        $userdata = fusion_get_userdata();

        $messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                                           "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
                $within_limit = self::get_pm_settings($userdata['user_id'], "user_archive") == "0" || (self::get_pm_settings($userdata['user_id'],
                                                                                                                             "user_archive") > 0 && self::get_pm_settings($userdata['user_id'],
                                                                                                                                                                          "user_archive") - 1 > $this->info['archive_total']) ? TRUE : FALSE;
                if ($ownership && $within_limit && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    $moveData['message_folder'] = 2;
                    dbquery_insert(DB_MESSAGES, $moveData, 'update');
                }
            }
            addNotice("success", $this->locale['489']);
            redirect(clean_request("", array("folder"), TRUE));
        }
    }

// Add send to group using recursive statement.

    private function unarchive_pm() {

        $userdata = fusion_get_userdata();

        $messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                                           "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
                $within_limit = self::get_pm_settings($userdata['user_id'], "user_inbox") == "0" || (self::get_pm_settings($userdata['user_id'],
                                                                                                                           "user_inbox") > 0 && self::get_pm_settings($userdata['user_id'],
                                                                                                                                                                      "user_inbox") - 1 > $this->info['inbox_total']) ? TRUE : FALSE;
                if ($ownership && $within_limit && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    $moveData['message_folder'] = 0;
                    dbquery_insert(DB_MESSAGES, $moveData, 'update');
                }
            }
            addNotice("success", $this->locale['489b']);
            redirect(clean_request("", array("folder"), TRUE));
        }
    }

    /**
     * Actions: delete
     * Require - $_POST selectedPM, delete_pm
     * SQL delete message pm
     */
    private function delete_pm() {

        $userdata = fusion_get_userdata();

        $messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                                           "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
                if ($ownership && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    dbquery_insert(DB_MESSAGES, $moveData, "delete");
                }
            }
            addNotice("success", $this->locale['490']);
            redirect(clean_request("", array("folder"), TRUE));
        }
    }

    /**
     * Actions : marking
     * Require - $_POST selectedPM, mark
     * SQL mark all, mark single (read or unread)
     */
    private function mark_pm() {

        $userdata = fusion_get_userdata();

        switch (form_sanitizer($_POST['mark'], "")) {
            case "mark_all": // mark all as read
                if (!empty($this->info['items'])) {
                    foreach ($this->info['items'] as $message_id => $array) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                                                   "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".intval($message_id)."'");
                        }
                    }
                    redirect(clean_request("", ["folder"], TRUE));
                }
                break;
            case "unmark_all": // mark all as unread
                if (!empty($this->info['items'])) {
                    foreach ($this->info['items'] as $message_id => $pmData) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                                                   "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id='".intval($message_id)."'");
                        }
                    }
                    redirect(clean_request("", ["folder"], TRUE));
                }
                break;
            case "mark_read":
                $messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
                if (!empty($messages)) {
                    foreach ($messages as $message_id) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                                                   "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".intval($message_id)."'");
                        }
                    }
                }
                redirect(clean_request("", ["folder"], TRUE));
                break;
            case "mark_unread":
                $messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
                if (!empty($messages)) {
                    foreach ($messages as $message_id) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                                                   "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id='".intval($message_id)."'");
                        }
                    }
                }
                redirect(clean_request("", ["folder"], TRUE));
        }
    }

    /**
     * Reply and send
     * SQL send pm
     */
    private function send_message() {

        $userdata = fusion_get_userdata();

        $inputData = [];
        if (iADMIN && isset($_POST['chk_sendtoall'])) {
            $inputData += [
                'to_group' => isset($_POST['msg_group_send']) ? form_sanitizer($_POST['msg_group_send'], 0, 'msg_group_send') : 0,
            ];
        } else {
            $inputData += [
                'to' => form_sanitizer($_POST['msg_send'], 0, 'msg_send'),
            ];
        }

        $inputData += [
            'from'     => $userdata['user_id'],
            'subject'  => form_sanitizer($_POST['subject'], '', 'subject'),
            'message'  => form_sanitizer($_POST['message'], '', 'message'),
            'smileys'  => isset($_POST['chk_disablesmileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['message']) ? "n" : "y",
            'to_group' => 0
        ];

        if (\defender::safe()) {

            if (iADMIN && isset($_POST['chk_sendtoall']) && $inputData['to_group']) {
                self::send_pm($inputData['to_group'], $inputData['from'], $inputData['subject'], $inputData['message'], $inputData['smileys'], TRUE);
            } else {
                self::send_pm($inputData['to'], $inputData['from'], $inputData['subject'], $inputData['message'], $inputData['smileys'], FALSE);
            }

            if (\defender::safe()) {
                addNotice("success", $this->locale['491']);
                redirect(BASEDIR."messages.php");
            }
        }
    }

    public static function send_pm($to, $from, $subject, $message, $smileys = 'y', $to_group = FALSE, $save_sent = TRUE) {
        require_once INCLUDES."sendmail_include.php";
        require_once INCLUDES."flood_include.php";

        $locale = fusion_get_locale();

        $strict = FALSE;
        $group_name = getgroupname($to);
        $to = isnum($to) || !empty($group_name) ? $to : 0;
        $from = isnum($from) ? $from : 0;
        if (!$from) {
            $from = 1; // Always the super administrator
        }
        $smileys = preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? "n" : $smileys;

        if (!$to_group) {

            // send to user
            $pmStatus = self::get_pm_settings($to);
            $myStatus = self::get_pm_settings($from);

            if (!flood_control("message_datestamp", DB_MESSAGES, "message_from='".intval($from)."'")) {

                // find receipient
                $result = dbquery("SELECT u.user_id, u.user_name, u.user_email, u.user_level,
                COUNT(m.message_id) 'message_count'
                FROM ".DB_USERS." u
                LEFT JOIN ".DB_MESSAGES." m ON m.message_user=u.user_id and message_folder='0'
                WHERE u.user_id='".intval($to)."' GROUP BY u.user_id
                ");

                if (dbrows($result) > 0) {
                    $data = dbarray($result);
                    $result2 = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id='".intval($from)."'");
                    if (dbrows($result2)) {
                        $userdata = dbarray($result2);
                        if ($to != $from) {
                            if ($data['user_id'] == 1 // recepient is SA
                                || $data['user_level'] < USER_LEVEL_MEMBER || //recepient is Admin
                                !$pmStatus['user_inbox'] || // have unlimited inbox
                                ($data['message_count'] + 1) <= $pmStatus['user_inbox'] // recepient inbox still within limit
                            ) {
                                $inputData = [
                                    'message_id'        => 0,
                                    'message_to'        => $to,
                                    'message_user'      => $to,
                                    'message_from'      => $from,
                                    'message_subject'   => $subject,
                                    'message_message'   => $message,
                                    'message_smileys'   => $smileys,
                                    'message_read'      => 0,
                                    'message_datestamp' => TIME,
                                    'message_folder'    => 0,
                                ];
                                dbquery_insert(DB_MESSAGES, $inputData, "save");

                                // this will flood the inbox when message is sent to group. -- fixed
                                if ($myStatus['user_pm_save_sent'] == '2' && $save_sent == TRUE) {
                                    // user_outbox.
                                    $cdata = dbarray(dbquery("SELECT COUNT(message_id) AS outbox_count, MIN(message_id) AS last_message FROM
                                    ".DB_MESSAGES." WHERE message_to='".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_folder='1' GROUP BY message_to"));
                                    // check my outbox limit and if surpass, remove oldest message
                                    if ($myStatus['user_outbox'] != "0" && ($cdata['outbox_count'] + 1) > $myStatus['user_outbox']) {
                                        dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_id='".$cdata['last_message']."' AND message_to='".$userdata['user_id']."'");
                                    }
                                    $inputData['message_user'] = $userdata['user_id'];
                                    $inputData['message_folder'] = 1;
                                    $inputData['message_from'] = $to;
                                    $inputData['message_to'] = $userdata['user_id'];
                                    dbquery_insert(DB_MESSAGES, $inputData, "save");
                                }

                                $send_email = $pmStatus['user_pm_email_notify'];

                                if ($send_email == "2") {
                                    $message_content = str_replace(
                                        ['[SUBJECT]', '[USER]', '[LINK]','[/LINK]', '[SITENAME]'],
                                        [$subject, $userdata['user_name'], "<a href='".fusion_get_settings('siteurl')."messages.php'>", "</a>", fusion_get_settings('sitename')],
                                        $locale['626']
                                    );

                                    $template_result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='PM' LIMIT 1");
                                    if (dbrows($template_result)) {
                                        $template_data = dbarray($template_result);
                                        if ($template_data['template_active'] == "1") {
                                            sendemail_template("PM", $subject, trimlink($message, 150), $userdata['user_name'], $data['user_name'],
                                                               "", $data['user_email']);
                                        } else {
                                            sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"),
                                                      fusion_get_settings("siteemail"), $locale['625'], $data['user_name'].$message_content);
                                        }
                                    } else {
                                        sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"),
                                                  fusion_get_settings("siteemail"), $locale['625'], $data['user_name'].$message_content);
                                    }
                                }
                            } else {
                                // Inbox is full
                                if ($strict) {
                                    die($locale['700']);
                                }
                                \defender::stop();
                                addNotice("danger", $locale['628']);
                            }
                        }

                    } else {
                        // Sender does not exist in DB
                        if ($strict) {
                            die($locale['701']);
                        }
                        \defender::stop();
                        addNotice("danger", $locale['482']);
                    }

                } else {
                    \defender::stop();
                    if ($strict) {
                        die($locale['702']);
                    }
                    addNotice("danger", $locale['482']);
                }

            } else {
                if ($strict) {
                    die($locale['703']);
                }
                \defender::stop();
                addNotice("danger", sprintf($locale['487'], fusion_get_settings("flood_interval")));
            }

        } else {
            $result = NULL;
            if ($to <= -101 && $to >= -103) { // -101, -102, -103 only
                $result = dbquery("SELECT user_id from ".DB_USERS." WHERE user_level <='".intval($to)."' AND user_status='0'");
            } else {
                $result = dbquery("SELECT user_id FROM ".DB_USERS." WHERE ".in_group("user_groups", $to)." AND user_status='0'");
            }
            if (dbrows($result) > 0) {

                while ($data = dbarray($result)) {
                    self::send_pm($data['user_id'], $from, $subject, $message, $smileys, FALSE, FALSE);
                }

            } else {
                \defender::stop();
                addNotice("danger", $locale['492']);
            }
        }
    }

    /**
     * Private message forms
     * pm_form (Short form)
     * pm_mainForm (Full composing environment)
     */
    public function pm_form() {

        $this->info['button'] += [
            'back' => array('link' => BASEDIR."messages.php?folder=".$_GET['folder'], 'title' => $this->locale['back']),
        ];
        $this->info['reply_form'] = openform('inputform', 'post', FUSION_REQUEST)
            .form_hidden('msg_send', '', $this->info['items'][$_GET['msg_read']]['message_from'])
            .form_hidden('subject', '', $this->info['items'][$_GET['msg_read']]['message_subject'])
            .form_textarea('message', '', '', [
                'required'    => TRUE,
                'placeholder' => $this->locale['422'],
                'preview'     => TRUE,
                'height'      => '300px',
                'form_name'   => 'inputform',
                'bbcode'      => TRUE
            ]).form_button('send_message', $this->locale['430'], $this->locale['430'], [
                'class' => 'btn btn-primary m-r-10'
            ]).form_button('cancel', $this->locale['cancel'], $this->locale['cancel'], ['class' => 'btn-link']).closeform();
    }

    /**
     * New message form
     */
    public function pm_mainForm() {

        $_GET['msg_send'] = isset($_GET['msg_send']) ? $_GET['msg_send'] : "";

        if (iADMIN) {
            $input_header = "<a class='pull-right m-b-10 display-inline-block' id='mass_send'>".$this->locale['434']."</a><br/>";
            $input_header .= form_user_select('msg_send', $this->locale['420a'], $_GET['msg_send'], array(
                'required'    => TRUE,
                'inner_width' => '100%',
                'width'       => '100%',
                'error_text'  => $this->locale['error_input_username'],
                'placeholder' => $this->locale['421']
            ));
            $input_header .= "<div id='msg_to_group-field' class='form-group display-none'>\n";
            $input_header .= "<label for='mg_to_group' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>".$this->locale['434']."
                            <input id='all_check' name='chk_sendtoall' type='checkbox' class='pull-left display-inline-block'
                           style='margin-right:10px !important;'/></label>\n";
            $input_header .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
            $user_groups = fusion_get_groups();
            unset($user_groups[0]);
            $input_header .= form_select('msg_group_send', "", "", array(
                'options'     => $user_groups,
                'inner_width' => '300px',
                'width'       => "100%",
            ));
            $input_header .= "</div>\n</div>\n";
            // Toggle "Send to All" link
            add_to_jquery("
            $('#mass_send').bind('click', function() {
            $('#msg_to_group-field').toggleClass('display-none');
            $('#msg_send-field').toggleClass('display-none');
            var invisible = $('#msg_to_group-field').hasClass('display-none');
            if (invisible) {
                $('#all_check').prop('checked', false);
            } else {
                $('#all_check').prop('checked', true);
            }
            });
            ");
        } else {
            $input_header = form_user_select('msg_send', $this->locale['420a'], $_GET['msg_send'], array(
                'required'    => TRUE,
                'input_id'    => 'msgsend2',
                'inline'      => TRUE,
                'width'       => '100%',
                'inner_width' => '100%',
                'error_text'  => $this->locale['error_input_username'],
                'placeholder' => $this->locale['421']
            ));
        }
        $this->info['reply_form'] = openform('inputform', 'post', FUSION_REQUEST)
            .$input_header.form_text('subject', '', '', [
                'placeholder'      => $this->locale['405'],
                'inner_class'      => 'input-lg',
                'required'         => TRUE,
                'inline'           => TRUE,
                'max_length'       => 100,
                'autocomplete_off' => TRUE,
                'width'            => '100%',
                'error_text'  => $this->locale['error_input_default'],
            ]).form_textarea('message', '', '', [
                'placeholder' => $this->locale['422'],
                'required'    => TRUE,
                'error_text'  => '',
                'autosize'    => TRUE,
                'no_resize'   => 0,
                'preview'     => TRUE,
                'form_name'   => 'inputform',
                'height'      => '150px',
                'error_text'  => $this->locale['error_input_default'],
                'bbcode'      => TRUE
            ]).form_button('cancel', $this->locale['cancel'], $this->locale['cancel']).form_button('send_pm', $this->locale['430'], $this->locale['430'], [
                'class' => 'btn m-l-10 btn-primary'
            ]).closeform();
    }
}
