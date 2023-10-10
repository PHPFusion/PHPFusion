<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: PrivateMessages.php
| Author: Core Development Team
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

/**
 * Class PrivateMessages
 *
 * @package PHPFusion
 */
class PrivateMessages {

    private $info = [
        'items' => []
    ];

    /**
     * Reply and send
     * SQL send pm
     */
    private $data = [
        'chk_sendtoall'  => 0,
        'msg_group_send' => 0,
        'to_group'       => 0,
        'to'             => 0,
        'msg_send'       => 0,
        'from'           => 0,
        'subject'        => '',
        'message'        => '',
        'smileys'        => 'y',
    ];

    public $locale = [];
    private static $instances = NULL;

    private static $is_sent = FALSE;

    /**
     * @return array
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    protected static function validatePmUser($user_id) {
        if (isnum($user_id) && dbcount("(user_id)", DB_USERS,
                "user_id=:userid AND user_status =:status", [':userid' => $user_id, ':status' => '0'])
        ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Get the pm settings for users
     *
     * @param int  $user_id
     * @param null $key
     *
     * @return array|mixed|null
     */
    public static function getPmSettings($user_id, $key = NULL) {
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
                    SELECT u.*, us.user_inbox, us.user_outbox, us.user_archive, us.user_pm_email, us.user_pm_save_sent
                    FROM ".DB_USERS." AS u
                    LEFT JOIN ".DB_USER_SETTINGS." AS us ON us.user_id = u.user_id
                    WHERE u.user_id=:userid AND user_status=:status", [':userid' => $user_id, ':status' => '0']
                );
                if (dbrows($result)) {
                    $data = dbarray($result);
                    // What it does is that if any of the parameters is 0, we use the default system values.
                    $settings = [
                        'user_inbox'           => (!empty($data['user_inbox']) ? (int)$data['user_inbox'] : (int)$settings['user_inbox']),
                        'user_outbox'          => (!empty($data['user_outbox']) ? (int)$data['user_outbox'] : (int)$settings['user_outbox']),
                        'user_archive'         => (!empty($data['user_archive']) ? (int)$data['user_archive'] : (int)$settings['user_archive']),
                        'user_pm_email_notify' => (!empty($data['user_pm_email_notify']) ? (int)$data['user_pm_email_notify'] : (int)$settings['user_pm_email_notify']),
                        'user_pm_save_sent'    => (!empty($data['user_pm_save_sent']) ? (int)$data['user_pm_save_sent'] : (int)$settings['user_pm_save_sent'])
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
     * Public API to send message using the message system
     *
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $message
     * @param string $smileys
     * @param bool   $to_group
     * @param bool   $save_sent
     */
    public static function sendPm($to, $from, $subject, $message, $smileys = 'y', $to_group = FALSE, $save_sent = TRUE) {
        require_once INCLUDES."sendmail_include.php";
        require_once INCLUDES."flood_include.php";

        $locale = fusion_get_locale('', LOCALE.LOCALESET.'messages.php');

        $strict = FALSE;
        $group_name = getgroupname($to);
        $to = isnum($to) || !empty($group_name) ? $to : 0;
        $from = isnum($from) ? $from : 0;
        $smileys = preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? "n" : $smileys;

        if (!$to_group) {

            // send to user
            $pmStatus = self::getPmSettings($to);
            $myStatus = self::getPmSettings($from);

            if (!flood_control("message_datestamp", DB_MESSAGES, "message_from='".(int)$from."'")) {

                // find receipient
                $result = dbquery("SELECT u.user_id, u.user_name, u.user_email, u.user_level, COUNT(m.message_id) 'message_count'
                    FROM ".DB_USERS." u
                    LEFT JOIN ".DB_MESSAGES." m ON m.message_user=u.user_id AND message_folder='0'
                    WHERE u.user_id=:userid GROUP BY u.user_id", [':userid' => $to]
                );

                if (dbrows($result)) {
                    $data = dbarray($result);
                    // if 0 accept, if number accept.
                    if (!$from) {
                        // comes from system.
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
                                    'message_from'      => 0,
                                    'message_subject'   => $subject,
                                    'message_message'   => $message,
                                    'message_smileys'   => $smileys,
                                    'message_read'      => 0,
                                    'message_datestamp' => time(),
                                    'message_folder'    => 0,
                                ];
                                dbquery_insert(DB_MESSAGES, $inputData, 'save');
                                self::$is_sent = TRUE;

                                if ($pmStatus['user_pm_email_notify'] == "2") {

                                    $message_content = str_replace(
                                        ['[SUBJECT]', '[USER]', '[LINK]', '[/LINK]', '[SITENAME]'],
                                        [$subject, fusion_get_settings("siteusername"), "<a href='".fusion_get_settings('siteurl')."messages.php'>", "</a>", fusion_get_settings('sitename')],
                                        $locale['626']
                                    );

                                    $template_result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='PM' LIMIT 1");
                                    if (dbrows($template_result)) {
                                        $template_data = dbarray($template_result);
                                        if ($template_data['template_active'] == "1") {
                                            sendemail_template("PM", $subject, trimlink($message, 150), fusion_get_settings("siteusername"), $data['user_name'], "", $data['user_email']);
                                        } else {
                                            sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['625']), $data['user_name'].$message_content);
                                        }
                                    } else {
                                        sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['625']), $data['user_name'].$message_content);
                                    }
                                }
                            }
                        }
                    } else if (isnum($from)) {
                        // comes from user
                        $result2 = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => (int)$from]);
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
                                        'message_datestamp' => time(),
                                        'message_folder'    => 0,
                                    ];
                                    dbquery_insert(DB_MESSAGES, $inputData, 'save');
                                    self::$is_sent = TRUE;

                                    if ($myStatus['user_pm_save_sent'] == 2 && $save_sent == TRUE) {
                                        // user_outbox.
                                        $cdata = dbarray(dbquery("SELECT COUNT(message_id) AS outbox_count, MIN(message_id) AS last_message FROM
                                        ".DB_MESSAGES." WHERE message_to=:mto AND message_user=:muser AND message_folder=:mfolder GROUP BY message_to",
                                            [':mto' => $userdata['user_id'], ':muser' => $userdata['user_id'], ':mfolder' => 1]));
                                        // check my outbox limit and if surpassed, remove the oldest message
                                        if ($myStatus['user_outbox'] != 0 && (!empty($cdata['outbox_count']) && $cdata['outbox_count'] + 1) > $myStatus['user_outbox']) {
                                            dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_id=:mid AND message_to=:mto", [':mid' => $cdata['last_message'], ':mto' => $userdata['user_id']]);
                                        }
                                        $inputData['message_user'] = $userdata['user_id'];
                                        $inputData['message_folder'] = 1;
                                        $inputData['message_from'] = $to;
                                        $inputData['message_read'] = 1;
                                        $inputData['message_to'] = $userdata['user_id'];
                                        dbquery_insert(DB_MESSAGES, $inputData, 'save');
                                    }

                                    if ($pmStatus['user_pm_email_notify'] == "2") {

                                        $message_content = str_replace(
                                            ['[SUBJECT]', '[USER]', '[LINK]', '[/LINK]', '[SITENAME]'],
                                            [$subject, $userdata['user_name'], "<a href='".fusion_get_settings('siteurl')."messages.php'>", "</a>", fusion_get_settings('sitename')],
                                            $locale['626']
                                        );

                                        $template_result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='PM' LIMIT 1");
                                        if (dbrows($template_result)) {
                                            $template_data = dbarray($template_result);
                                            if ($template_data['template_active'] == "1") {
                                                sendemail_template("PM", $subject, trimlink($message, 150), $userdata['user_name'], $data['user_name'], "", $data['user_email']);
                                            } else {
                                                sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['625']), $data['user_name'].$message_content);
                                            }
                                        } else {
                                            sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['625']), $data['user_name'].$message_content);
                                        }
                                    }

                                } else {
                                    // Inbox is full
                                    if ($strict) {
                                        die($locale['700']);
                                    }
                                    fusion_stop($locale["628"]);
                                }
                            }

                        } else {
                            // Sender does not exist in DB
                            if ($strict) {
                                die($locale['701']);
                            }
                            fusion_stop($locale["482"]);
                        }
                    }
                } else {
                    // Recepient does not exist
                    if ($strict) {
                        die($locale['702']);
                    }
                    fusion_stop($locale["482"]);
                }

            } else {
                // Flood control in sending pm
                if ($strict) {
                    die($locale['703']);
                }
                fusion_stop(sprintf($locale['487'], fusion_get_settings('flood_interval')));
            }

        } else {
            if ($to <= USER_LEVEL_MEMBER && $to >= USER_LEVEL_SUPER_ADMIN) { // -101, -102, -103 only
                $result = dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_level <=:level AND user_status=:status", [':level' => $to, ':status' => '0']);
            } else {
                $result = dbquery("SELECT user_id FROM ".DB_USERS." WHERE ".in_group("user_groups", $to, '.')." AND user_status='0'");
            }
            if (dbrows($result) > 0) {

                while ($data = dbarray($result)) {
                    self::sendPm($data['user_id'], $from, $subject, $message, $smileys, FALSE, FALSE);
                }

            } else {
                fusion_stop();
                addnotice('danger', $locale['492']);
            }
        }
    }

    /**
     * Get PM Instances
     *
     * @param string $key
     *
     * @return static
     */
    public static function getInstance($key = 'default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }

        return self::$instances[$key];
    }

    /**
     * Set Message Listing for inbox, outbox and archive*
     */
    private function setListMessages() {
        // list messages
        $query = [
            'inbox'   => [$this->info['inbox_total'], "message_folder='0'"],
            'outbox'  => [$this->info['outbox_total'], "message_folder='1'"],
            'archive' => [$this->info['archive_total'], "message_folder='2'"]
        ];

        $totals = [
            'inbox'   => $this->info['inbox_count'],
            'outbox'  => $this->info['outbox_count'],
            'archive' => $this->info['archive_count']
        ];

        if ($totals[$_GET['folder']] > 0) {
            add_to_title($this->locale['global_201'].$this->info['folders'][$_GET['folder']]['title']);
            set_meta("description", $this->info['folders'][$_GET['folder']]['title']);

            $sql_table = DB_MESSAGES." m LEFT JOIN ".DB_USERS." u ON (m.message_from=u.user_id)";
            $sql_condition = "message_to=:uid AND ".$query[$_GET['folder']][1];
            $sql_limit = ":rowstart, :limit";
            // filter
            $sql_param = [':uid' => fusion_get_userdata('user_id')];
            if ($this->info['max_rows'] = dbcount("(message_id)", $sql_table, $sql_condition, $sql_param)) {

                $sql_param += [
                    ':rowstart' => (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->info['max_rows'] ? (int)$_GET['rowstart'] : 0),
                    ':limit'    => 20
                ];
                $result = dbquery("SELECT m.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, MAX(m.message_id) AS last_message
                                FROM $sql_table
                                WHERE $sql_condition GROUP BY message_id ORDER BY m.message_datestamp DESC LIMIT $sql_limit",
                    $sql_param
                );
                $this->info['rows'] = dbrows($result);
                if ($this->info['max_rows'] > $this->info['rows']) {
                    $url = ((array)parse_url(htmlspecialchars_decode($_SERVER['REQUEST_URI']))) + [
                            'path'  => '',
                            'query' => ''
                        ];
                    if ($url['query']) {
                        parse_str($url['query'], $fusion_query); // this is original.
                    }
                    $this->info['pagenav'] = makepagenav($sql_param[':rowstart'], $sql_param[':limit'], $this->info['max_rows'], 3, BASEDIR."messages.php?folder=".$_GET['folder']."&");
                }
                while ($data = dbarray($result)) {
                    if (!$data['user_id']) {
                        $data['user_name'] = $this->locale['632'];
                    }

                    $data['contact_user'] = [
                        'user_id'     => $data['user_id'],
                        'user_name'   => $data['user_name'],
                        'user_status' => $data['user_status'],
                        'user_avatar' => $data['user_avatar'],
                        'user_level'  => $data['user_level']
                    ];
                    $data['message'] = [
                        'link'           => BASEDIR."messages.php?folder=".$_GET['folder']."&msg_read=".$data['message_id'],
                        'name'           => $data['message_subject'],
                        'message_header' => "<strong>".$this->locale['462'].":</strong> ".$data['message_subject'],
                        'message_text'   => parse_text($data['message_message'], [
                            'parse_smileys'        => $data['message_smileys'] == 'y',
                            'decode'               => FALSE,
                            'default_image_folder' => NULL,
                            'add_line_breaks'      => TRUE
                        ])
                    ];
                    $this->info['items'][$data['message_id']] = $data;
                }
            } else {
                $this->info['no_item'] = $this->locale['471'];
            }

        } else {
            $this->info['no_item'] = $this->locale['471'];
        }

    }

    /**
     * Set message reader
     */
    private function setReadMessages() {

        // list messages
        $query = [
            'inbox'   => [$this->info['inbox_total'], "message_folder='0'"],
            'outbox'  => [$this->info['outbox_total'], "message_folder='1'"],
            'archive' => [$this->info['archive_total'], "message_folder='2'"]
        ];

        $sql_table = DB_MESSAGES." m LEFT JOIN ".DB_USERS." u ON (m.message_from=u.user_id)";
        $sql_condition = "message_to=:uid AND message_id=:mid AND ".$query[$_GET['folder']][1];
        $sql_param = [':uid' => fusion_get_userdata('user_id'), ':mid' => (int)$_GET['msg_read']];
        $result = dbquery("SELECT m.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level
        FROM $sql_table WHERE $sql_condition GROUP BY message_id ORDER BY m.message_datestamp DESC", $sql_param);
        if ($this->info['rows'] = dbrows($result)) {

            $data = dbarray($result);

            if (!$data["user_id"]) {
                $data["user_name"] = $this->locale['632'];
            }

            $data['contact_user'] = [
                'user_id'     => $data['user_id'],
                'user_name'   => $data['user_name'],
                'user_status' => $data['user_status'],
                'user_avatar' => $data['user_avatar'],
                'user_level'  => $data['user_level']
            ];

            $data['message'] = [
                'link'           => BASEDIR."messages.php?folder=".$_GET['folder']."&msg_read=".$data['message_id'],
                'name'           => $data['message_subject'],
                'message_header' => "<strong>".$this->locale['462'].":</strong> ".$data['message_subject'],
                'message_text'   => parse_text($data['message_message'], [
                    'parse_smileys'        => $data['message_smileys'] == 'y',
                    'decode'               => FALSE,
                    'default_image_folder' => NULL,
                    'add_line_breaks'      => TRUE
                ])
            ];

            $this->info['items'][$data['message_id']] = $data;

            // set read
            if (isset($this->info['items'][$_GET['msg_read']])) {
                dbquery("UPDATE ".DB_MESSAGES." SET message_read=1 WHERE message_id=:mrd", [':mrd' => (int)$_GET['msg_read']]);
            }

            $this->info['button'] += [
                'back' => ['link' => BASEDIR."messages.php?folder=".$_GET['folder'], 'title' => $this->locale['back']],
            ];


            $this->setReplyForm($data["user_id"]);


        } else {
            redirect(BASEDIR.'messages.php');
        }
    }

    /**
     * Set Message Options Viewer
     */
    private function setMessageOptions() {
        $userdata = fusion_get_userdata();
        if (isset($_POST['save_options'])) {
            $data = [
                'user_id'              => $userdata['user_id'],
                'user_pm_email_notify' => form_sanitizer($_POST['pm_email_notify'], 0, 'pm_email_notify'),
                'user_pm_save_sent'    => form_sanitizer($_POST['pm_save_sent'], 0, 'pm_save_sent'),
            ];
            dbquery_insert(DB_USERS, $data, 'update');
            addnotice('success', $this->locale['445']);
            redirect(BASEDIR."messages.php?folder=options");
        }
        $this->info['options_form'] = openform('pm_form', 'post', FUSION_REQUEST);
        $this->info['options_form'] .= form_select('pm_email_notify', $this->locale['621'], $userdata['user_pm_email_notify'], ['options' => [
            '0' => $this->locale['520'],
            '1' => $this->locale['521'],
            '2' => $this->locale['522'],
        ]]);
        $this->info['options_form'] .= form_select('pm_save_sent', $this->locale['622'], $userdata['user_pm_save_sent'], ['options' => [
            '0' => $this->locale['520'],
            '1' => $this->locale['523'],
            '2' => $this->locale['524'],
        ]]);
        $this->info['options_form'] .= form_button('save_options', $this->locale['623'], $this->locale['623'], ['class' => 'btn btn-primary']);
        $this->info['options_form'] .= closeform();
    }

    /**
     * Actions buttons - archive, delete, mark all read, mark all unread, mark as read, mark as unread
     */
    private function setActionMenu() {
        if (isset($_GET['msg_read'])) {
            $this->info['actions_form'] = [
                'openform'  => openform('actionform', 'post', FORM_REQUEST).form_hidden('selectedPM', '', (int)$_GET['msg_read']),
                'unlockbtn' => form_button('unarchive_pm', $this->locale['413'], 'unarchive_pm', ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-unlock']),
                'lockbtn'   => form_button('archive_pm', $this->locale['412'], 'archive_pm', ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-lock']),
                'deletebtn' => form_button('delete_pm', $this->locale['416'], 'delete_pm', ['icon' => 'fa fa-trash-o', 'class' => 'btn-danger btn-sm']),
                'closeform' => closeform()
            ];
        } else {
            if (!defined('PM_JS')) {
                define('PM_JS', TRUE);
                add_to_footer("<script src='".INCLUDES."jscripts/pm.min.js'></script>");
            }

            $this->info['actions_form'] = [
                'openform'    => openform('actionform', 'post', FORM_REQUEST).form_hidden('selectedPM'),
                'check'       => [
                    'check_all_pm'    => $this->locale['418'],
                    'check_unread_pm' => $this->locale['415'],
                    'check_read_pm'   => $this->locale['414']
                ],
                'unlockbtn'   => form_button('unarchive_pm', $this->locale['413'], 'unarchive_pm', ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-unlock']),
                'lockbtn'     => form_button('archive_pm', $this->locale['412'], 'archive_pm', ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-lock']),
                'deletebtn'   => form_button('delete_pm', $this->locale['416'], 'delete_pm', ['class' => 'btn-sm btn-danger', 'icon' => 'fa fa-trash-o']),
                'mark_all'    => form_button('mark', $this->locale['493'], 'mark_all', ['input_id' => 'mark_all', 'class' => 'btn-link btn-block align-left']),
                'mark_read'   => form_button('mark', $this->locale['494'], 'mark_read', ['input_id' => 'mark_read', 'class' => 'btn-link btn-block align-left']),
                'mark_unread' => form_button('mark', $this->locale['495'], 'mark_unread', ['input_id' => 'mark_unread', 'class' => 'btn-link btn-block align-left']),
                'unmark_all'  => form_button('mark', $this->locale['496'], 'unmark_all', ['input_id' => 'unmark_all', 'class' => 'btn-link btn-block align-left']),
                'closeform'   => closeform()
            ];
        }
    }

    /**
     * Private message server
     *
     * @return $this
     */
    public function server() {
        if (!iMEMBER) {
            redirect(BASEDIR.'index.php');
        }
        $userdata = fusion_get_userdata();
        $this->locale = fusion_get_locale('', LOCALE.LOCALESET.'messages.php');

        if (isset($_POST['cancel'])) {
            redirect(BASEDIR."messages.php");
        }

        if (!isset($_GET['folder']) || !preg_check("/^(inbox|outbox|archive|options)$/", $_GET['folder'])) {
            $_GET['folder'] = 'inbox';
        }

        if (isset($_POST['msg_send']) && isnum($_POST['msg_send']) && self::validatePmUser($_POST['msg_send'])) {
            $_GET['msg_send'] = $_POST['msg_send'];
        }

        // prohibits send message to non-existing group
        $user_group = fusion_get_groups();
        unset($user_group[0]);
        if (isset($_POST['msg_to_group']) && isnum($_POST['msg_to_group']) && isset($user_group[$_POST['msg_to_group']])) {
            $_GET['msg_to_group'] = $_POST['msg_to_group'];
        }

        //$unread_inbox = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_read=0 AND message_folder=0", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);
        $total_inbox = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_folder=0", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);
        //$unread_outbox = dbcount("(message_id)", DB_MESSAGES, "message_to=:mto AND message_folder=1 AND message_read=0", [':mto' => $userdata['user_id']]);
        $total_outbox = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_folder=1", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);
        //$unread_arc = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_folder=2 AND message_read=0", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);
        $total_arc = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_folder=2", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);

        $inbox_limit = user_pm_settings($userdata['user_id'], 'user_inbox');
        $outbox_limit = user_pm_settings($userdata['user_id'], 'user_outbox');
        $archive_limit = user_pm_settings($userdata['user_id'], 'user_archive');

        /**
         * Defaults
         */
        $this->info = [
            'folders'       => [
                'inbox'   => ['link' => BASEDIR."messages.php?folder=inbox", 'title' => $this->locale['402'], 'icon' => 'fa fa-inbox'],
                'outbox'  => ['link' => BASEDIR."messages.php?folder=outbox", 'title' => $this->locale['403'], 'icon' => 'fa fa-envelope-o'],
                'archive' => ['link' => BASEDIR."messages.php?folder=archive", 'title' => $this->locale['404'], 'icon' => 'fa fa-archive'],
                'options' => ['link' => BASEDIR."messages.php?folder=options", 'title' => $this->locale['425'], 'icon' => 'fa fa-cog'],
            ],
            'inbox_limit'   => $inbox_limit,
            'outbox_limit'  => $outbox_limit,
            'archive_limit' => $archive_limit,
            'inbox_count'   => $total_inbox,
            'outbox_count'  => $total_outbox,
            'archive_count' => $total_arc,
            'inbox_total'   => $total_inbox."/".($inbox_limit == 0 ? '&#8734;' : $inbox_limit),
            'outbox_total'  => $total_outbox."/".($outbox_limit == 0 ? '&#8734;' : $outbox_limit),
            'archive_total' => $total_arc."/".($archive_limit == 0 ? '&#8734;' : $archive_limit),
            'pagenav'       => '',
            'button'        => [
                'new'     => [
                    'link'  => BASEDIR."messages.php?msg_send=new",
                    'title' => $this->locale['401']
                ],
                'options' => ['link' => BASEDIR."messages.php?folder=options", 'name' => $this->locale['425']],
            ],
            'actions_form'  => '',
        ];

        add_to_title($this->locale['400']);

        return $this;
    }

    /**
     * Private message main viewer
     */
    public function view() {

        if ($_GET['folder'] == "options") {
            $this->setMessageOptions();

        } else {

            // Listener for Sending Messages
            $this->doSend();

            if (isset($_GET['msg_send']) && (isnum($_GET['msg_send']) || $_GET['msg_send'] === 'new')) {
                // Form 1
                $this->setSendForm();

            } else {
                if (isset($_GET['msg_read']) && isnum($_GET['msg_read'])) {
                    // Form 2 + Messages
                    $this->setReadMessages();

                } else {
                    $this->setListMessages();
                }
            }

            // Message Actions
            if (!empty($_POST)) {
                if (isset($_POST['archive_pm'])) {
                    $this->doArchive();
                } else if (isset($_POST['unarchive_pm'])) {
                    $this->doUnarchive();
                } else if (isset($_POST['delete_pm'])) {
                    $this->doDelete();
                } else if (isset($_POST['mark'])) {
                    $this->doMark();
                }
            }

            $this->setActionMenu();
        }

        display_inbox($this->info);

    }

    /**
     * Actions: archive messages
     */
    private function doArchive() {
        $userdata = fusion_get_userdata();

        $messages = !empty($_POST['selectedPM']) ? explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ",")) : '';
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id=:messageid AND message_user=:messageuser", [':messageid' => $message_id, ':messageuser' => $userdata['user_id']]);
                $within_limit = self::getPmSettings($userdata['user_id'], "user_archive") == "0" || (self::getPmSettings($userdata['user_id'], "user_archive") > 0 && self::getPmSettings($userdata['user_id'], "user_archive") - 1 > $this->info['archive_total']);
                if ($ownership && $within_limit && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    $moveData['message_folder'] = 2;
                    dbquery_insert(DB_MESSAGES, $moveData, 'update');
                }
            }
            addnotice('success', $this->locale['489']);
            redirect(clean_request('', ['folder'], TRUE));
        }
    }

    /**
     * Actions: unarchive messages
     */
    private function doUnarchive() {
        $userdata = fusion_get_userdata();

        $messages = !empty($_POST['selectedPM']) ? explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ",")) : '';
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id=:messageid AND message_user=:messageuser", [':messageid' => (int)$message_id, ':messageuser' => (int)$userdata['user_id']]);
                $within_limit = self::getPmSettings($userdata['user_id'], "user_inbox") == "0" || (self::getPmSettings($userdata['user_id'], "user_inbox") > 0 && self::getPmSettings($userdata['user_id'], "user_inbox") - 1 > $this->info['inbox_total']);
                if ($ownership && $within_limit && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    $moveData['message_folder'] = 0;
                    dbquery_insert(DB_MESSAGES, $moveData, 'update');
                }
            }
            addnotice('success', $this->locale['489b']);
            redirect(clean_request('', ['folder'], TRUE));
        }
    }

    /**
     * Actions: delete messages
     */
    private function doDelete() {
        $userdata = fusion_get_userdata();

        $messages = (!empty($_POST['selectedPM']) ? explode(",", rtrim(sanitizer("selectedPM", "", "selectedPM"), ",")) : "");
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id=:messageid AND message_user=:messageuser", [':messageid' => (int)$message_id, ':messageuser' => (int)$userdata['user_id']]);
                if ($ownership && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    dbquery_insert(DB_MESSAGES, $moveData, 'delete');
                }
            }
            addnotice('success', $this->locale['490']);
            redirect(BASEDIR.'messages.php');
        }
    }

    /**
     * Actions: marking messages
     */
    private function doMark() {
        $userdata = fusion_get_userdata();

        switch (form_sanitizer($_POST['mark'])) {
            case "mark_all": // mark all as read
                if (!empty($this->info['items'])) {
                    foreach ($this->info['items'] as $message_id => $array) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id=:messageid AND message_user=:messageuser", [':messageid' => (int)$message_id, ':messageuser' => (int)$userdata['user_id']]);
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".(int)$message_id."'");
                        }
                    }
                    redirect(clean_request('', ['folder'], TRUE));
                }
                break;
            case "unmark_all": // mark all as unread
                if (!empty($this->info['items'])) {
                    foreach ($this->info['items'] as $message_id => $pmData) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                "message_id=:messageid AND message_user=:messageuser", [':messageid' => (int)$message_id, ':messageuser' => (int)$userdata['user_id']]);
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id='".(int)$message_id."'");
                        }
                    }
                    redirect(clean_request('', ['folder'], TRUE));
                }
                break;
            case "mark_read":
                $messages = !empty($_POST['selectedPM']) ? explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ",")) : '';
                if (!empty($messages)) {
                    foreach ($messages as $message_id) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                "message_id=:messageid AND message_user=:messageuser", [':messageid' => $message_id, ':messageuser' => $userdata['user_id']]);
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".(int)$message_id."'");
                        }
                    }
                }
                redirect(clean_request('', ['folder'], TRUE));
                break;
            case "mark_unread":
                $messages = !empty($_POST['selectedPM']) ? explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ",")) : '';
                if (!empty($messages)) {
                    foreach ($messages as $message_id) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                                "message_id=:messageid AND message_user=:messageuser", [':messageid' => (int)$message_id, ':messageuser' => (int)$userdata['user_id']]);
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id='".(int)$message_id."'");
                        }
                    }
                }
                redirect(clean_request('', ['folder'], TRUE));
        }
    }

    /**
     * Actions: send messages
     */
    private function doSend() {

        if (isset($_POST['send_pm']) || isset($_POST['send_message'])) {

            $userdata = fusion_get_userdata();

            $this->data = [
                'msg_group_send' => 0,
                'chk_sendtoall'  => 0,
                'to'             => 0,
                'from'           => $userdata['user_id'],
                'subject'        => form_sanitizer($_POST['subject'], '', 'subject'),
                'message'        => form_sanitizer($_POST['message'], '', 'message'),
                'smileys'        => isset($_POST['chk_disablesmileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['message']) ? "n" : "y"
            ];

            if (iADMIN && !empty($_POST['chk_sendtoall'])) {
                $this->data['chk_sendtoall'] = isset($_POST['chk_sendtoall']) ? 1 : 0;
                $this->data['msg_group_send'] = isset($_POST['msg_group_send']) ? form_sanitizer($_POST['msg_group_send'], 0, 'msg_group_send') : 0;
            } else {
                $this->data['to'] = form_sanitizer($_POST['msg_send'], 0, 'msg_send');
            }

            if (fusion_safe()) {
                if (iADMIN && isset($_POST['chk_sendtoall']) && $this->data['msg_group_send']) {
                    self::sendPm($this->data['msg_group_send'], $this->data['from'], $this->data['subject'], $this->data['message'], $this->data['smileys'], TRUE);
                } else {
                    self::sendPm($this->data['to'], $this->data['from'], $this->data['subject'], $this->data['message'], $this->data['smileys']);
                }

                if (self::$is_sent == TRUE) {
                    addnotice('success', $this->locale['491']);
                    redirect(BASEDIR."messages.php");
                }
            }

        }
    }

    /**
     * Private message forms
     * pm_form (Short form)
     * pm_mainForm (Full composing environment)
     *
     * @param bool $show_form
     */
    private function setReplyForm($show_form = TRUE) {
        $this->info['reply_form'] = openform('inputform', 'post', FUSION_REQUEST)
            .form_hidden('msg_send', '', $this->info['items'][$_GET['msg_read']]['message_from'])
            .form_hidden('subject', '', $this->info['items'][$_GET['msg_read']]['message_subject'])
            .form_textarea('message', '', '', [
                'required'    => TRUE,
                'placeholder' => ($show_form ? $this->locale['422'] : $this->locale['631']),
                'preview'     => TRUE,
                'height'      => '300px',
                'form_name'   => 'inputform',
                'bbcode'      => TRUE,
                "deactivate"  => !$show_form
            ]).form_button('send_message', $this->locale['430'], $this->locale['430'], [
                'class'      => 'btn btn-primary m-r-10',
                "deactivate" => !$show_form,
            ]).form_button('cancel', $this->locale['cancel'], $this->locale['cancel'], [
                'class'      => 'btn-link',
                "deactivate" => !$show_form,
            ]).closeform();
    }

    /**
     * New message form
     */
    private function setSendForm() {

        $this->data['msg_send'] = isset($_GET['msg_send']) && !user_blacklisted($_GET['msg_send']) ? $_GET['msg_send'] : 0;

        if (iADMIN) {
            $input_header = "<div class='clearfix text-right'><a class='pointer' id='mass_send'><i class='fa fa-user-circle-o m-r-5'></i>".$this->locale['434']."</a></div>";
            $input_header .= form_user_select('msg_send', $this->locale['420a'], $this->data['msg_send'], [
                'required'    => TRUE,
                'inner_width' => '100%',
                'width'       => '100%',
                'error_text'  => $this->locale['error_input_username'],
                'placeholder' => $this->locale['421']
            ]);
            $input_header .= form_hidden('chk_sendtoall', '', $this->data['chk_sendtoall']);
            $input_header .= "<div id='msg_to_group-field' class='display-none'>\n";
            $user_groups = fusion_get_groups();
            unset($user_groups[0]);
            $input_header .= form_select('msg_group_send', $this->locale['420a'], $this->data['msg_group_send'], [
                'options'     => $user_groups,
                'inner_width' => '300px',
                'width'       => "100%",
            ]);
            $input_header .= "</div>\n";

            // Toggle "Send to All" link
            add_to_jquery("
            $('#mass_send').bind('click', function() {
            $('#msg_to_group-field').toggleClass('display-none');
            $('#msg_send-field').toggleClass('display-none');
            var invisible = $('#msg_to_group-field').hasClass('display-none');
            if (invisible) {
                $('#chk_sendtoall').val(0);
            } else {
                $('#chk_sendtoall').val(1);
            }
            });
            ");

        } else {
            $input_header = form_user_select('msg_send', $this->locale['420a'], $this->data['msg_send'], [
                'required'    => TRUE,
                'input_id'    => 'msgsend2',
                'inline'      => TRUE,
                'width'       => '100%',
                'inner_width' => '100%',
                'error_text'  => $this->locale['error_input_username'],
                'placeholder' => $this->locale['421']
            ]);
        }

        $this->info['reply_form'] = openform('inputform', 'post', FUSION_REQUEST).$input_header."<hr/>".
            form_text('subject', '', $this->data['subject'], [
                'placeholder' => $this->locale['405'],
                'class'       => 'form-group-lg display-block',
                'inline'      => FALSE,
                'required'    => TRUE,
                'max_length'  => 100,
                'width'       => '100%',
                'error_text'  => $this->locale['error_input_default'],
            ]).
            form_textarea('message', '', $this->data['message'], [
                'placeholder' => $this->locale['422'],
                'required'    => TRUE,
                'autosize'    => TRUE,
                'no_resize'   => 0,
                'preview'     => TRUE,
                'form_name'   => 'inputform',
                'height'      => '150px',
                'error_text'  => $this->locale['error_input_default'],
                'bbcode'      => TRUE
            ]).
            form_checkbox('chk_disablesmileys', $this->locale['427']).
            form_button('cancel', $this->locale['cancel'], $this->locale['cancel']).
            form_button('send_pm', $this->locale['430'], $this->locale['430'], [
                'class' => 'btn m-l-10 btn-primary'
            ]).closeform();
    }

    /**
     * PrivateMessages constructor.
     */
    private function __construct() {
    }

}
