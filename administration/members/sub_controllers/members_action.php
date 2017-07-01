<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members/sub_controllers/members_action.php
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
namespace Administration\Members\Sub_Controllers;

use Administration\Members\Members_Admin;

/**
 * Class Members_Action
 * All function are in the form of multiples user_id
 *
 * @package Administration\Members\Sub_Controllers
 */
class Members_Action extends Members_Admin {

    private $action_user_id = array();
    private $action = 0;
    private $users = array();

    /**
     * Setter of the class user_id
     *
     * @param array $value
     */
    public function set_userID(array $value = array()) {
        foreach ($value as $id) {
            if (isnum($id)) {
                $user_id[$id] = $id;
            }
        }
        $this->action_user_id = $user_id;
    }

    public function set_action($value) {
        $this->action = $value;
    }

    /**
     * Action Script Configurations
     *
     * @var array
     */
    private $action_map = array(
        // OK
        /*
         * Eligible is anyone not 1
         * Anything that is 1-5
         */
        parent::USER_BAN          => [
            'check_operator'       => '!==', // Make it fluid.
            'check_value'          => self::USER_BAN,
            'title'                => 'ME_500',
            'a_message'            => 'ME_550',
            'user_status_change'   => parent::USER_BAN,
            'user_status_log_func' => 'suspend_log',
            'reason'               => TRUE,
            'email'                => TRUE,
            'email_title'          => 'email_ban_subject',
            'email_message'        => 'email_ban_message',
        ],
        // OK
        parent::USER_REINSTATE    => [
            'check_operator'       => '>',
            'check_value'          => parent::USER_MEMBER,
            'title'                => 'ME_501',
            'a_message'            => 'ME_551',
            'user_status_change'   => parent::USER_MEMBER,
            'reason'               => TRUE,
            'user_status_log_func' => 'unsuspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_activate_subject',
            'email_message'        => 'email_activate_message',
        ],
        // OK
        parent::USER_SUSPEND      => [
            'check_operator'       => '!==',
            'check_value'          => self::USER_SUSPEND,
            'title'                => 'ME_503',
            'a_message'            => 'ME_553',
            'user_status_change'   => parent::USER_SUSPEND,
            'action_time'          => TRUE,
            'reason'               => TRUE,
            'user_status_log_func' => 'suspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_suspend_subject',
            'email_message'        => 'email_suspend_message',
        ],
        // OK
        parent::USER_SECURITY_BAN => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_SECURITY_BAN,
            'title'                => 'ME_504',
            'a_message'            => 'ME_554',
            'user_status_change'   => parent::USER_SECURITY_BAN,
            'user_status_log_func' => 'suspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_secban_subject',
            'email_message'        => 'email_secban_message',
        ],
        // OK
        parent::USER_CANCEL       => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_CANCEL,
            'title'                => 'ME_505',
            'a_message'            => 'ME_555',
            'user_status_change'   => parent::USER_CANCEL,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log',
        ],
        // OK
        parent::USER_ANON         => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_ANON,
            'title'                => 'ME_506',
            'a_message'            => 'ME_556',
            'user_status_change'   => parent::USER_ANON,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log'
        ],
        // OK
        parent::USER_DEACTIVATE   => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_DEACTIVATE,
            'title'                => 'ME_502',
            'a_message'            => 'ME_552',
            'user_status_change'   => parent::USER_DEACTIVATE,
            'user_status_log_func' => 'suspend_log',
            'action_time'          => TRUE,
            'email'                => TRUE,
            'email_title'     => 'email_deactivate_subject',
            'email_message'   => 'email_deactivate_message',
        ]
    );

    public function user_check($x, $y, $z) {
        switch ($z) {
            case '>':
                return ($x > $y);
                break;
            case '<':
                return ($x < $y);
                break;
            case '==':
                return ($x == $y);
                break;
            case '!==':
                return ($x !== $y);
                break;
        }

        return FALSE;
    }


    public function execute() {
        $form = '';
        $users_list = '';

        $query = "SELECT user_id, user_name, user_avatar, user_email, user_level, user_password, user_status FROM ".DB_USERS." WHERE user_id IN (".implode(',', $this->action_user_id).") AND user_level > ".USER_LEVEL_SUPER_ADMIN." GROUP BY user_id";
        $result = dbquery($query);
        if (dbrows($result)) {
            while ($u_data = dbarray($result)) {
                if ($this->user_check(
                    $u_data['user_status'],
                    $this->action_map[$this->action]['check_value'],
                    $this->action_map[$this->action]['check_operator'])
                ) {
                    $this->users[$u_data['user_id']] = $u_data;
                }
            }
        }

        if (!empty($this->users)) {

            if (isset($_POST['post_action'])) {
                $settings = fusion_get_settings();
                $userdata = fusion_get_userdata();
                $reason = '';
                if (!empty($this->action_map[$this->action]['reason'])) {
                    $reason = form_sanitizer($_POST['reason'], '', 'reason');
                }
                $duration = 0;
                if (!empty($this->action_map[$this->action]['action_time'])) {
                    $duration = form_sanitizer($_POST['duration'], '', 'duration');
                    $duration = ($duration * 86400) + TIME;
                }
                if (\defender::safe()) {

                    foreach ($this->users as $user_id => $u_data) {

                        dbquery("UPDATE ".DB_USERS." SET user_status=:user_status, user_actiontime=:action_time WHERE user_id=:user_id", array(
                            ':user_status' => $this->action_map[$this->action]['user_status_change'],
                            ':action_time' => $duration,
                            ':user_id'     => $user_id
                        ));
                        /*
                         * Executes log
                         */
                        if (!empty($this->action_map[$this->action]['user_status_log_func'])) {
                            $log_value = ($this->action_map[$this->action]['user_status_log_func'] == 'suspend_log' ? $this->action : $u_data['user_status']);
                            $this->action_map[$this->action]['user_status_log_func']($user_id, $log_value, $reason);
                        }
                        /*
                         * Email users
                         */
                        if (!empty($this->action_map[$this->action]['email'])) {
                            $email_locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/members_email.php');
                            $subject = strtr($email_locale[$this->action_map[$this->action]['email_title']],
                                [
                                    '[SITENAME]' => $settings['sitename']
                                ]
                            );
                            $message = strtr($email_locale[$this->action_map[$this->action]['email_message']],
                                [
                                    '[USER_NAME]'           => $u_data['user_name'],
                                    '[REASON]'              => $reason,
                                    '[SITENAME]'            => $settings['sitename'],
                                    '[ADMIN_USERNAME]'      => $userdata['user_name'],
                                    '[SITEUSERNAME]'        => $settings['siteusername'],
                                    '[DATE]'                => showdate('longdate', $duration),
                                    '[DEACTIVATION_PERIOD]' => $settings['deactivation_period'],
                                    '[REACTIVATION_LINK]'   => $settings['siteurl']."reactivate.php?user_id=".$u_data['user_id']."&code=".md5($duration.$u_data['user_password'])
                                ]
                            );

                            sendemail($u_data['user_name'], $u_data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
                        }
                        $u_name[] = $u_data['user_name'];
                    }
                    addNotice('success', sprintf(self::$locale['ME_432'], implode(', ', $u_name), self::$locale[$this->action_map[$this->action]['a_message']]));
                    redirect(FUSION_REQUEST);
                }
            } else {

                $height = '45px';
                foreach ($this->users as $user_data) {
                    $users_list .= strtr($this->user_block_template(),
                        [
                            '{%user_avatar%}' => display_avatar($user_data, $height, '', '', '', ''),
                            '{%height%}'      => $height,
                            '{%user_name%}'   => $user_data['user_name']
                        ]
                    );
                }
                if (isset($this->action_map[$this->action]['action_time'])) {
                    $form .= form_text('duration', self::$locale['ME_435'], '', array('type' => 'number', 'append' => TRUE, 'append_value' => self::$locale['ME_436'], 'required' => TRUE, 'inner_width' => '120px'));
                }
                if (!empty($this->action_map[$this->action]['reason'])) {
                    $form .= form_textarea('reason', self::$locale['ME_433'], '', array('required' => TRUE, 'placeholder' => self::$locale['ME_434']));
                }
                $form .= form_hidden('action', '', $this->action);
                foreach ($this->action_user_id as $user_id) {
                    $form .= form_hidden('user_id[]', '', $user_id);
                }
                $form .= form_button('post_action', self::$locale['update'], $this->action, array('class' => 'btn-primary'));
                ob_start();
                echo openmodal('uAdmin_modal', self::$locale[$this->action_map[$this->action]['title']].self::$locale['ME_413'], array('static' => TRUE));
                echo openform('uAdmin_frm', 'post', FUSION_REQUEST);
                echo strtr($this->action_form_template(), [
                    '{%message%}'    => sprintf(self::$locale['ME_431'], self::$locale[$this->action_map[$this->action]['a_message']]),
                    '{%users_list%}' => $users_list,
                    '{%form%}'       => $form,
                ]);
                echo closeform();
                echo closemodal();
                $modal = ob_get_contents();
                ob_end_clean();
                add_to_footer($modal);
            }
        } else {
            addNotice('danger', self::$locale['ME_430']);
            redirect(FUSION_REQUEST);
        }
    }

    private function action_form_template() {
        return "
        <p><strong>{%message%}</strong></p>
        {%users_list%}
        <hr/>
        {%form%}
        ";
    }

    private function user_block_template() {
        return "
        <div class='display-inline-block list-group-item p-0'>\n
        <div class='pull-left m-r-10'>{%user_avatar%}</div>\n
        <div class='overflow-hide'>\n
        <span class='va' style='height:{%height%};'></span>\n
        <span class='va p-r-15'>\n<strong>{%user_name%}</strong>\n</span>\n
        </div>\n
        </div>\n
        ";
    }

}

require_once(dirname(__FILE__).'/../../../includes/sendmail_include.php');
require_once(dirname(__FILE__).'/../../../includes/suspend_include.php');
