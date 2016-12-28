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

    private $action_map = array(
        'ban'       => [
            'check_operator'       => '==',
            'check_value'          => 0,
            'title'                => 409,
            'message'              => 'The following users will be banned',
            'success_message'      => '%s has been banned',
            'user_status_change'   => 1,
            'user_status_log_func' => 'suspend_log', // 1 for suspend, 2 for unsuspend
            'user_email_title'     => 'email_ban_subject',
            'user_email_message'   => 'email_ban_message',
        ],
        'reinstate' => [
            'check_operator'       => '>',
            'check_value'          => 1,
            'title'                => 408,
            'message'              => 'The following users will be reinstated',
            'success_message'      => '%s has been reinstated',
            'user_status_change'   => 0,
            'user_status_log_func' => 'unsuspend_log',
            'user_email_title'     => '',
            'user_email_message'   => '',
        ],
        'activate'  => [
            'check_operator'       => '!==',
            'check_value'          => 0,
            'title'                => 408,
            'message'              => 'The following users will be activated',
            'success_message'      => '%s has been activated',
            'user_status_change'   => 0,
            'user_status_log_func' => 'unsuspend_log',
            'user_email_title'     => 'email_activate_subject',
            'user_email_message'   => 'email_activate_message',
        ],
        'suspend'   => [
            'check_operator'       => '==',
            'check_value'          => 0,
            'title'                => 590,
            'message'              => 'The following users will be suspended',
            'success_message'      => '%s has been suspended for %s',
            'user_status_change'   => 0,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log',
            'user_email_title'     => 'email_suspend_subject',
            'user_email_message'   => 'email_suspend_message',
        ],
        'security' => [
            'check_operator'       => '==',
            'check_value'          => 0,
            'title'                => 590,
            'message'              => 'The following users will be security banned',
            'success_message'      => '%s has been security banned for %s',
            'user_status_change'   => 4,
            'user_status_log_func' => 'suspend_log',
            'user_email_title'     => 'email_secban_subject',
            'user_email_message'   => 'email_secban_message',
        ],
        'cancelled' => [
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

        $query = "SELECT user_id, user_name, user_avatar, user_level, user_status FROM ".DB_USERS." WHERE user_id IN (".implode(',', $this->action_user_id).") AND user_level > ".USER_LEVEL_SUPER_ADMIN." GROUP BY user_id";
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

                $reason = form_sanitizer($_POST['reason'], '', 'reason');

                $duration = 0;
                if (isset($this->action_map[$this->action]['action_time'])) {
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

                        if (!empty($this->action_map[$this->action]['user_status_log_func'])) {
                            $this->action_map[$this->action]['user_status_log_func']($user_id, $this->action, $reason);
                        }

                        if (!empty($this->action_map[$this->action]['user_email_title']) && !empty($this->action_map[$this->action]['user_email_message'])) {

                            $subject = strtr(self::$locale[$this->action_map[$this->action]]['user_email_title'],
                                [
                                    '[SITENAME]' => $settings['sitename']
                                ]
                            );

                            $message = strtr(self::$locale[$this->action_map[$this->action]['user_email_message']],
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
                    addNotice('success', sprintf($this->action_map[$this->action]['success_message'], implode(', ', $u_name)));
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }

            } else {

                $users_list = '';
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
                $form = form_textarea('reason', self::$locale['515'], '', array('placeholder' => self::$locale['585a']));
                $form .= form_hidden('action', '', $this->action);
                if (isset($this->action_map[$this->action]['action_time'])) {
                    $form .= form_textarea('duration', self::$locale['596'], '', array('append' => TRUE, 'append_value' => 'Days', 'required' => TRUE, 'inner_width' => '120px'));
                }
                $form .= form_button('post_action', 'Update', $this->action, array('class' => 'btn-primary'));

                ob_start();
                echo openmodal('uAdmin_modal', 'Ban User', array('static' => TRUE));
                echo openform('uAdmin_frm', 'post', FUSION_SELF.fusion_get_aidlink());
                echo strtr($this->action_form_template(), [
                    '{%message%}'   => $this->action_map[$this->action]['message'],
                    '{%user_list%}' => $users_list,
                    '{%form%}'      => $form,
                ]);
                echo closeform();
                echo closemodal();
                $modal = ob_get_contents();
                ob_end_clean();
                add_to_footer($modal);
            }
        } else {
            addNotice('danger', 'Error: Actions against selected users could not be completed');
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
    }

    private function action_form_template() {
        return "
        <p><strong>{%message%}</strong></p>
        {%users_list%}
        <hr/>
        <div class='well'>{%form%}</div>
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

require_once(dirname(__FILE__).'/../../includes/sendmail_include.php');
require_once(dirname(__FILE__).'/../../includes/suspend_include.php');
// add user actions - put this in members - ////display_suspend_log($this->user_id, 1, 0, 10);//$rowstart, 10);