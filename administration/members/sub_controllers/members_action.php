<?php

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
     * Step Actions to Take.
     * @var array
     */
    private $action_map = array(
        parent::USER_BAN => [
            'check_operator'       => '==',
            'check_value'          => 0,
            'title'                => 'ME_500',
            'a_message'       => 'ME_550',
            'user_status_change'   => 1,
            'user_status_log_func' => 'suspend_log',
            'email' => TRUE,
            'reason' => TRUE,
            'email_title'     => 'email_ban_subject',
            'email_message'   => 'email_ban_message',
        ],
        parent::USER_REINSTATE  => [
            'check_operator'       => '>',
            'check_value'          => 0,
            'title'                => 'ME_501',
            'a_message'              => 'ME_551',
            'user_status_change'   => 0,
            'reason' => FALSE,
            'user_status_log_func' => 'unsuspend_log',
            'email' => TRUE,
            'email_title' => 'email_activate_subject',
            'email_message' => 'email_activate_message',
        ],

        parent::USER_SUSPEND   => [
            'check_operator'       => '==',
            'check_value'          => 0,
            'title'                => 590,
            'message'              => 'The following users will be suspended',
            'success_message'      => '%s has been suspended for %s',
            'user_status_change'   => 0,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log',
            'email_title'     => 'email_suspend_subject',
            'email_message'   => 'email_suspend_message',
        ],

        parent::USER_SECURITY_BAN => [
            'check_operator'       => '==',
            'check_value'          => 0,
            'title'                => 590,
            'message'              => 'The following users will be security banned',
            'success_message'      => '%s has been security banned for %s',
            'user_status_change'   => 4,
            'user_status_log_func' => 'suspend_log',
            'email_title'     => 'email_secban_subject',
            'email_message'   => 'email_secban_message',
        ],

        parent::USER_CANCEL => [
            'check_operator'       => '==',
            'check_value'          => 0,
            'title'                => 590,
            'message'              => 'The following users have will be cancelled',
            'success_message'      => '%s has been cancelled. Response time given %s',
            'user_status_change'   => 5,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log',
            //'user_email_title'     => 'email_secban_subject', ?? why not email this guy?
            //'user_email_message'   => 'email_secban_message', ?? why not email this guy?
        ],
        parent::USER_ANON => [
            'check_operator'       => '==',
            'check_value'          => 0,
            'title'                => 590,
            'message'              => 'The following users have will be cancelled',
            'success_message'      => '%s has been cancelled. Response time given %s',
            'user_status_change'   => 5,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log',
            //'user_email_title'     => 'email_secban_subject', ?? why not email this guy?
            //'user_email_message'   => 'email_secban_message', ?? why not email this guy?
        ],
        parent::USER_DEACTIVATE  => [
            'check_operator'       => '!==',
            'check_value'          => 0,
            'title'                => 'ME_502',
            'a_message'              => 'ME_552',
            'user_status_change'   => 0,
            'user_status_log_func' => 'suspend_log',
            'email' => TRUE,
            'user_email_title'     => 'email_activate_subject',
            'user_email_message'   => 'email_activate_message',
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

    public function get_locale_map($key) {
        // Use comma to explode to fill up a sentence.


    }


    private $users = array();

    public function execute() {
        $form = '';
        $users_list = '';

        $query = "SELECT user_id, user_name, user_avatar, user_email, user_level, user_status FROM ".DB_USERS." WHERE user_id IN (".implode(',', $this->action_user_id).") AND user_level > ".USER_LEVEL_SUPER_ADMIN." GROUP BY user_id";
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
                            $this->action_map[$this->action]['user_status_log_func']($user_id, $this->action, $reason);
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
                                    '[USER_NAME]'      => $u_data['user_name'],
                                    '[REASON]'         => $reason,
                                    '[SITENAME]'       => $settings['sitename'],
                                    '[ADMIN_USERNAME]' => $userdata['user_name'],
                                    '[SITEUSERNAME]'   => $settings['siteusername'],
                                    '[DATE]'           => showdate('longdate', $duration)
                                ]
                            );
                            sendemail($u_data['user_name'], $u_data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
                        }
                        $u_name[] = $u_data['user_name'];
                    }
                    addNotice('success', sprintf(self::$locale['ME_432'], implode(', ', $u_name), self::$locale[$this->action_map[$this->action]['a_message']]));
                    redirect(FUSION_SELF.fusion_get_aidlink());
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
                if (!empty($this->action_map[$this->action]['reason'])) {
                    $form .= form_textarea('reason', self::$locale['ME_433'], '', array('required'=>TRUE, 'placeholder' => self::$locale['ME_434']));
                }
                if (isset($this->action_map[$this->action]['action_time'])) {
                    $form .= form_textarea('duration', self::$locale['ME_435'], '', array('append' => TRUE, 'append_value' => self::$locale['ME_436'], 'required' => TRUE, 'inner_width' => '120px'));
                }
                $form .= form_hidden('action', '', $this->action);
                foreach($this->action_user_id as $user_id) {
                    $form .= form_hidden('user_id[]', '', $user_id);
                }
                $form .= form_button('post_action', self::$locale['update'], $this->action, array('class' => 'btn-primary'));
                ob_start();
                echo openmodal('uAdmin_modal', self::$locale[$this->action_map[$this->action]['title']], array('static' => TRUE));
                echo openform('uAdmin_frm', 'post', FUSION_SELF.fusion_get_aidlink());
                echo strtr($this->action_form_template(), [
                    '{%message%}'   => sprintf(self::$locale['ME_431'], self::$locale[$this->action_map[$this->action]['a_message']]),
                    '{%users_list%}' => $users_list,
                    '{%form%}'      => $form,
                ]);
                echo closeform();
                echo closemodal();
                $modal = ob_get_contents();
                ob_end_clean();
                add_to_footer($modal);
            }
        } else {
            addNotice('danger', self::$locale['ME_430']);
            redirect(FUSION_SELF.fusion_get_aidlink());
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
// add user actions - put this in members - ////display_suspend_log($this->user_id, 1, 0, 10);//$rowstart, 10);