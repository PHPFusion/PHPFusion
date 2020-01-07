<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PrivateMessages.inc
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

    /**
     * @return array
     */
    public function getInfo() {
        return $this->info;
    }

    protected static function validate_pm_user($user_id) {
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
     * @param      $user_id
     * @param null $key
     *
     * @return array|mixed|null
     */
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
                    FROM ".DB_USERS."
                    WHERE user_id=:userid AND user_status=:status", [':userid' => $user_id, ':status' => '0']
                );
                if (dbrows($result)) {
                    $data = dbarray($result);
                    // What this does is that if any of the params is 0, we use default system values.
                    $settings = [
                        'user_inbox'           => !empty($data['user_inbox']) ? intval($data['user_inbox']) : intval($settings['user_inbox']),
                        'user_outbox'          => !empty($data['user_outbox']) ? intval($data['user_outbox']) : intval($settings['user_outbox']),
                        'user_archive'         => !empty($data['user_archive']) ? intval($data['user_archive']) : intval($settings['user_archive']),
                        'user_pm_email_notify' => !empty($data['user_pm_email_notify']) ? intval($data['user_pm_email_notify']) : intval($settings['user_pm_email_notify']),
                        'user_pm_save_sent'    => !empty($data['user_pm_save_sent']) ? intval($data['user_pm_save_sent']) : intval($settings['user_pm_save_sent'])
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
     * @param        $to
     * @param        $from
     * @param        $subject
     * @param        $message
     * @param string $smileys
     * @param bool   $to_group
     * @param bool   $save_sent
     */
    public static function send_pm($to, $from, $subject, $message, $smileys = 'y', $to_group = FALSE, $save_sent = TRUE) {
        require_once INCLUDES."sendmail_include.php";
        require_once INCLUDES."flood_include.php";

        $locale = fusion_get_locale('', LOCALE.LOCALESET.'messages.php');

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
                $result = dbquery("SELECT u.user_id, u.user_name, u.user_email, u.user_level, COUNT(m.message_id) 'message_count'
                    FROM ".DB_USERS." u
                    LEFT JOIN ".DB_MESSAGES." m ON m.message_user=u.user_id AND message_folder='0'
                    WHERE u.user_id=:userid GROUP BY u.user_id", [':userid' => $to]
                );

                if (dbrows($result)) {
                    $data = dbarray($result);
                    $result2 = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => $from]);
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
                                dbquery_insert(DB_MESSAGES, $inputData, 'save');

                                // this will flood the inbox when message is sent to group. -- fixed
                                if ($myStatus['user_pm_save_sent'] == '2' && $save_sent == TRUE) {
                                    // user_outbox.
                                    $cdata = dbarray(dbquery("SELECT COUNT(message_id) AS outbox_count, MIN(message_id) AS last_message FROM
                                    ".DB_MESSAGES." WHERE message_to=:mto AND message_user=:muser AND message_folder=:mfolder GROUP BY message_to",
                                        [':mto' => $userdata['user_id'], ':muser' => $userdata['user_id'], ':mfolder' => '1']));
                                    // check my outbox limit and if surpass, remove oldest message
                                    if ($myStatus['user_outbox'] != "0" && ($cdata['outbox_count'] + 1) > $myStatus['user_outbox']) {
                                        dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_id=:mid AND message_to=:mto", [':mid' => $cdata['last_message'], ':mto' => $userdata['user_id']]);
                                    }
                                    $inputData['message_user'] = $userdata['user_id'];
                                    $inputData['message_folder'] = 1;
                                    $inputData['message_from'] = $to;
                                    $inputData['message_read'] = 1;
                                    $inputData['message_to'] = $userdata['user_id'];
                                    dbquery_insert(DB_MESSAGES, $inputData, 'save');
                                }

                                $send_email = $pmStatus['user_pm_email_notify'];

                                if ($send_email == "2") {
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
                                            sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), $locale['625'], $data['user_name'].$message_content);
                                        }
                                    } else {
                                        sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), $locale['625'], $data['user_name'].$message_content);
                                    }
                                }
                            } else {
                                // Inbox is full
                                if ($strict) {
                                    die($locale['700']);
                                }
                                \defender::stop();
                                addNotice('danger', $locale['628']);
                            }
                        }

                    } else {
                        // Sender does not exist in DB
                        if ($strict) {
                            die($locale['701']);
                        }
                        \defender::stop();
                        addNotice('danger', $locale['482']);
                    }

                } else {
                    \defender::stop();
                    if ($strict) {
                        die($locale['702']);
                    }
                    addNotice('danger', $locale['482']);
                }

            } else {
                if ($strict) {
                    die($locale['703']);
                }
                \defender::stop();
                addNotice('danger', sprintf($locale['487'], fusion_get_settings('flood_interval')));
            }

        } else {
            $result = NULL;
            if ($to <= USER_LEVEL_MEMBER && $to >= USER_LEVEL_SUPER_ADMIN) { // -101, -102, -103 only
                $result = dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_level <=:level AND user_status=:status", [':level' => $to, ':status' => '0']);
            } else {
                $result = dbquery("SELECT user_id FROM ".DB_USERS." WHERE ".in_group("user_groups", $to)." AND user_status='0'");
            }
            if (dbrows($result) > 0) {

                while ($data = dbarray($result)) {
                    self::send_pm($data['user_id'], $from, $subject, $message, $smileys, FALSE, FALSE);
                }

            } else {
                \defender::stop();
                addNotice('danger', $locale['492']);
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
     * @todo: expand to longpoll or implement node.js
     */
    private function set_list_messages() {
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

            $sql_table = DB_MESSAGES." m INNER JOIN ".DB_USERS." u ON (m.message_from=u.user_id)";
            $sql_condition = "message_to=:uid AND ".$query[$_GET['folder']][1];
            $sql_limit = ":rowstart, :limit";
            // filter
            $sql_param = [':uid' => fusion_get_userdata('user_id')];
            if ($this->info['max_rows'] = dbcount("(message_id)", $sql_table, $sql_condition, $sql_param)) {

                $sql_param += [
                    ':rowstart' => (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->info['max_rows'] ? intval($_GET['rowstart']) : 0),
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
                    $this->info['pagenav'] = makepagenav($sql_param[':rowstart'], $sql_param[':limit'], $this->info['max_rows'], 3, BASEDIR."messages.php?folder=".$_GET['folder']."&amp;");
                }
                while ($data = dbarray($result)) {
                    $data['contact_user'] = [
                        'user_id'     => $data['user_id'],
                        'user_name'   => $data['user_name'],
                        'user_status' => $data['user_status'],
                        'user_avatar' => $data['user_avatar'],
                        'user_level'  => $data['user_level']
                    ];
                    $data['message'] = [
                        'link'           => BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_read=".$data['message_id'],
                        'name'           => $data['message_subject'],
                        'message_header' => "<strong>".$this->locale['462'].":</strong> ".$data['message_subject'],
                        'message_text'   => $data['message_smileys'] == "y" ? nl2br(parseubb(parsesmileys($data['message_message']))) : nl2br(parseubb($data['message_message'])),
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
     * Set Message Reader
     */
    private function set_read_messages() {

        // list messages
        $query = [
            'inbox'   => [$this->info['inbox_total'], "message_folder='0'"],
            'outbox'  => [$this->info['outbox_total'], "message_folder='1'"],
            'archive' => [$this->info['archive_total'], "message_folder='2'"]
        ];

        $sql_table = DB_MESSAGES." m INNER JOIN ".DB_USERS." u ON (m.message_from=u.user_id)";
        $sql_condition = "message_to=:uid AND message_id=:mid AND ".$query[$_GET['folder']][1];
        $sql_param = [':uid' => fusion_get_userdata('user_id'), ':mid' => intval($_GET['msg_read'])];
        $result = dbquery("SELECT m.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level
                                FROM $sql_table WHERE $sql_condition GROUP BY message_id ORDER BY m.message_datestamp DESC",
            $sql_param
        );
        if ($this->info['rows'] = dbrows($result)) {

            $data = dbarray($result);

            $data['contact_user'] = [
                'user_id'     => $data['user_id'],
                'user_name'   => $data['user_name'],
                'user_status' => $data['user_status'],
                'user_avatar' => $data['user_avatar'],
                'user_level'  => $data['user_level']
            ];

            $data['message'] = [
                'link'           => BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_read=".$data['message_id'],
                'name'           => $data['message_subject'],
                'message_header' => "<strong>".$this->locale['462'].":</strong> ".$data['message_subject'],
                'message_text'   => $data['message_smileys'] == "y" ? nl2br(parseubb(parsesmileys($data['message_message']))) : nl2br(parseubb($data['message_message']))
            ];

            $this->info['items'][$data['message_id']] = $data;

            // set read
            if (isset($this->info['items'][$_GET['msg_read']])) {
                dbquery("UPDATE ".DB_MESSAGES." SET message_read=1 WHERE message_id=:mrd", [':mrd' => intval($_GET['msg_read'])]);
            }

            $this->info['button'] += [
                'back' => ['link' => BASEDIR."messages.php?folder=".$_GET['folder'], 'title' => $this->locale['back']],
            ];

            $this->set_reply_form();

        } else {
            redirect(BASEDIR.'messages.php');
        }
    }

    /**
     * Set Message Options Viewer
     */
    private function set_message_options() {
        $userdata = fusion_get_userdata();
        if (isset($_POST['save_options'])) {
            $data = [
                'user_id'              => $userdata['user_id'],
                'user_pm_email_notify' => form_sanitizer($_POST['pm_email_notify'], 0, 'pm_email_notify'),
                'user_pm_save_sent'    => form_sanitizer($_POST['pm_save_sent'], 0, 'pm_save_sent'),
            ];
            dbquery_insert(DB_USERS, $data, 'update');
            addNotice('success', $this->locale['445']);
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
     * @return string
     */
    private function set_action_menu() {
        if (isset($_GET['msg_read'])) {
            // this is the read menu.
            $html = openform('actionform', 'post', FUSION_REQUEST);
            $html .= form_hidden('selectedPM', '', intval($_GET['msg_read']));
            $html .= "<div class='btn-group display-inline-block m-r-10'>\n";
            if ($_GET['folder'] == "archive") {
                $html .= form_button('unarchive_pm', $this->locale['413'], 'unarchive_pm', ['icon' => 'fa fa-unlock']);
            } else if ($_GET['folder'] == "inbox") {
                $html .= form_button('archive_pm', $this->locale['412'], 'archive_pm', ['icon' => 'fa fa-lock']);
            }
            $html .= form_button('delete_pm', $this->locale['416'], 'delete_pm', ['icon' => 'fa fa-trash-o', 'class' => 'btn-danger']);
            $html .= "</div>\n";
            $html .= closeform();
        } else {
            add_to_footer("<script src='".INCLUDES."jscripts/pm.js'></script>");
            $html = openform('actionform', 'post', FUSION_REQUEST);
            $html .= form_hidden('selectedPM', '', '');
            $html .= "<div class='dropdown display-inline-block m-r-10'>
                <a href='#' data-toggle='dropdown' class='btn btn-default btn-sm dropdown-toggle'><i id='chkv' class='fa fa-square-o'></i><span class='caret m-l-5'></span></a>
                <ul class='dropdown-menu'>
                    <li><a id='check_all_pm' data-action='check' class='pointer'>".$this->locale['418']."</a></li>
                    <li><a id='check_unread_pm' data-action='check' class='pointer'>".$this->locale['415']."</a></li>
                    <li><a id='check_read_pm' data-action='check' class='pointer'>".$this->locale['414']."</a></li>
                </ul>
            </div>";
            $html .= "<div class='btn-group display-inline-block m-r-10'>\n";
            if ($_GET['folder'] == 'archive') {
                $html .= form_button('unarchive_pm', $this->locale['413'], 'unarchive_pm', ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-unlock']);
            } else if ($_GET['folder'] !== 'outbox') {
                $html .= form_button('archive_pm', $this->locale['412'], 'archive_pm', ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-lock']);
            }
            $html .= form_button('delete_pm', $this->locale['416'], 'delete_pm', ['class' => 'btn-sm btn-danger', 'icon' => 'fa fa-trash-o']);
            $html .= "</div>\n";
            $html .= "<div class='dropdown display-inline-block m-r-10'>
                <a href='#' data-toggle='dropdown' class='btn btn-default btn-sm dropdown-toggle'>".$this->locale['444']."&hellip; <span class='caret'></span></a>
                <ul class='dropdown-menu'>
                    <li>".form_button('mark', $this->locale['493'], 'mark_all', ['input_id' => 'mark_all', 'class' => 'btn-link btn-block align-left'])."</li>
                    <li>".form_button('mark', $this->locale['494'], 'mark_read', ['input_id' => 'mark_read', 'class' => 'btn-link btn-block align-left'])."</li>
                    <li>".form_button('mark', $this->locale['495'], 'mark_unread', ['input_id' => 'mark_unread', 'class' => 'btn-link btn-block align-left'])."</li>
                    <li>".form_button('mark', $this->locale['496'], 'unmark_all', ['input_id' => 'unmark_all', 'class' => 'btn-link btn-block align-left'])."</li>
                </ul>
            </div>\n";
            $html .= closeform();
        }

        $this->info['actions_form'] = $html;

    }

    /**
     * Private message server
     * @return $this
     */
    public function Server() {
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

        if (isset($_POST['msg_send']) && isnum($_POST['msg_send']) && self::validate_pm_user($_POST['msg_send'])) {
            $_GET['msg_send'] = $_POST['msg_send'];
        }

        // prohibits send message to non-existing group
        $user_group = fusion_get_groups();
        unset($user_group[0]);
        if (isset($_POST['msg_to_group']) && isnum($_POST['msg_to_group']) && isset($user_group[$_POST['msg_to_group']])) {
            $_GET['msg_to_group'] = $_POST['msg_to_group'];
        }

        $unread_inbox = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_read=0 AND message_folder=0", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);
        $total_inbox = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_folder=0", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);
        $unread_outbox = dbcount("(message_id)", DB_MESSAGES, "message_to=:mto AND message_folder=1 AND message_read=0", [':mto' => $userdata['user_id']]);
        $total_outbox = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_folder=1", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);
        $unread_arc = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_folder=2 AND message_read=0", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);
        $total_arc = dbcount("(message_id)", DB_MESSAGES, "message_user=:muser AND message_to=:mto AND message_folder=2", [':muser' => $userdata['user_id'], ':mto' => $userdata['user_id']]);

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
            'inbox_count'   => intval($total_inbox),
            'outbox_count'  => intval($total_outbox),
            'archive_count' => intval($total_arc),
            'inbox_total'   => $unread_inbox."/".$total_inbox,
            'outbox_total'  => $unread_outbox."/".$total_outbox,
            'archive_total' => $unread_arc."/".$total_arc,
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

        add_to_title($this->locale['global_200'].$this->locale['400']);
        add_to_meta("description", $this->locale['400']);

        return $this;

    }

    /**
     * Private message main viewer
     * @return string|void
     */
    public function View() {

        if ($_GET['folder'] == "options") {
            $this->set_message_options();

        } else {

            // Listener for Sending Messages
            $this->do_send();

            if (isset($_GET['msg_send']) && (isnum($_GET['msg_send']) || $_GET['msg_send'] === 'new')) {
                // Form 1
                $this->set_send_form();

            } else {
                if (isset($_GET['msg_read']) && isnum($_GET['msg_read'])) {
                    // Form 2 + Messages
                    $this->set_read_messages();

                } else {
                    $this->set_list_messages();
                }
            }

            // Message Actions
            if (!empty($_POST)) {
                if (isset($_POST['archive_pm'])) {
                    $this->do_archive();
                } else if (isset($_POST['unarchive_pm'])) {
                    $this->do_unarchive();
                } else if (isset($_POST['delete_pm'])) {
                    $this->do_delete();
                } else if (isset($_POST['mark'])) {
                    $this->do_mark();
                }
            }

            $this->set_action_menu();
        }

        return display_inbox($this->info);

    }

    /**
     * Actions : archive messages
     */
    private function do_archive() {
        $userdata = fusion_get_userdata();

        $messages = !empty($_POST['selectedPM']) ? explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ",")) : '';
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id=:messageid AND message_user=:messageuser", [':messageid' => $message_id, ':messageuser' => $userdata['user_id']]) ? TRUE : FALSE;
                $within_limit = self::get_pm_settings($userdata['user_id'], "user_archive") == "0" || (self::get_pm_settings($userdata['user_id'], "user_archive") > 0 && self::get_pm_settings($userdata['user_id'], "user_archive") - 1 > $this->info['archive_total']) ? TRUE : FALSE;
                if ($ownership && $within_limit && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    $moveData['message_folder'] = 2;
                    dbquery_insert(DB_MESSAGES, $moveData, 'update');
                }
            }
            addNotice('success', $this->locale['489']);
            redirect(clean_request('', ['folder'], TRUE));
        }
    }

    /**
     * Actions: unarchive messages
     */
    private function do_unarchive() {
        $userdata = fusion_get_userdata();

        $messages = !empty($_POST['selectedPM']) ? explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ",")) : '';
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id=:messageid AND message_user=:messageuser", [':messageid' => intval($message_id), ':messageuser' => intval($userdata['user_id'])]) ? TRUE : FALSE;
                $within_limit = self::get_pm_settings($userdata['user_id'], "user_inbox") == "0" || (self::get_pm_settings($userdata['user_id'], "user_inbox") > 0 && self::get_pm_settings($userdata['user_id'], "user_inbox") - 1 > $this->info['inbox_total']) ? TRUE : FALSE;
                if ($ownership && $within_limit && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    $moveData['message_folder'] = 0;
                    dbquery_insert(DB_MESSAGES, $moveData, 'update');
                }
            }
            addNotice('success', $this->locale['489b']);
            redirect(clean_request('', ['folder'], TRUE));
        }
    }

    /**
     * Actions: delete messages
     */
    private function do_delete() {
        $userdata = fusion_get_userdata();

        $messages = !empty($_POST['selectedPM']) ? explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ",")) : '';
        if (!empty($messages)) {
            foreach ($messages as $message_id) {
                $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id=:messageid AND message_user=:messageuser", [':messageid' => intval($message_id), ':messageuser' => intval($userdata['user_id'])]) ? TRUE : FALSE;
                if ($ownership && isset($this->info['items'][$message_id])) {
                    $moveData = $this->info['items'][$message_id];
                    dbquery_insert(DB_MESSAGES, $moveData, 'delete');
                }
            }
            addNotice('success', $this->locale['490']);
            redirect(clean_request('', ['folder'], TRUE));
        }
    }

    /**
     * Actions : marking messages
     */
    private function do_mark() {
        $userdata = fusion_get_userdata();

        switch (form_sanitizer($_POST['mark'], "")) {
            case "mark_all": // mark all as read
                if (!empty($this->info['items'])) {
                    foreach ($this->info['items'] as $message_id => $array) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id=:messageid AND message_user=:messageuser", [':messageid' => intval($message_id), ':messageuser' => intval($userdata['user_id'])]) ? TRUE : FALSE;
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".intval($message_id)."'");
                        }
                    }
                    redirect(clean_request('', ['folder'], TRUE));
                }
                break;
            case "unmark_all": // mark all as unread
                if (!empty($this->info['items'])) {
                    foreach ($this->info['items'] as $message_id => $pmData) {
                        $ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES,
                            "message_id=:messageid AND message_user=:messageuser", [':messageid' => intval($message_id), ':messageuser' => intval($userdata['user_id'])]) ? TRUE : FALSE;
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id='".intval($message_id)."'");
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
                            "message_id=:messageid AND message_user=:messageuser", [':messageid' => $message_id, ':messageuser' => $userdata['user_id']]) ? TRUE : FALSE;
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".intval($message_id)."'");
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
                            "message_id=:messageid AND message_user=:messageuser", [':messageid' => $message_id, ':messageuser' => $userdata['user_id']]) ? TRUE : FALSE;
                        if ($ownership && isset($this->info['items'][$message_id])) {
                            dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id='".intval($message_id)."'");
                        }
                    }
                }
                redirect(clean_request('', ['folder'], TRUE));
        }
    }

    /**
     * Actions: send messages
     */
    private function do_send() {

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

            if (\defender::safe()) {
                if (iADMIN && isset($_POST['chk_sendtoall']) && $this->data['msg_group_send']) {
                    self::send_pm($this->data['msg_group_send'], $this->data['from'], $this->data['subject'], $this->data['message'], $this->data['smileys'], TRUE);
                } else {
                    self::send_pm($this->data['to'], $this->data['from'], $this->data['subject'], $this->data['message'], $this->data['smileys'], FALSE);
                }
                addNotice('success', $this->locale['491']);
                redirect(BASEDIR."messages.php");
            }

        }
    }

    /**
     * Private message forms
     * pm_form (Short form)
     * pm_mainForm (Full composing environment)
     */
    private function set_reply_form() {
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
    private function set_send_form() {

        $this->data['msg_send'] = isset($_GET['msg_send']) ? $_GET['msg_send'] : 0;

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
            ]).form_textarea('message', '', $this->data['message'], [
                'placeholder' => $this->locale['422'],
                'required'    => TRUE,
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

    /**
     * PrivateMessages constructor.
     */
    private function __construct() {}

}
