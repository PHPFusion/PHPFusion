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

    /*
     * This is a multi ban
     */
    public function display_ban_form() {
        $query = "SELECT user_id, user_name, user_avatar, user_level, user_status FROM ".DB_USERS." WHERE user_id IN (".implode(',', $this->action_user_id).") AND user_level > ".USER_LEVEL_SUPER_ADMIN." GROUP BY user_id";
        $result = dbquery($query);
        if (dbrows($result)) {
            $user_to_ban = array();
            $user_to_unban = array();
            while ($u_data = dbarray($result)) {
                if ($u_data['user_status'] > 0) {
                    $user_to_unban[$u_data['user_id']] = $u_data;
                } else {
                    $user_to_ban[$u_data['user_id']] = $u_data;
                }
            }
            $tab_content = '';
            if (!empty($user_to_ban)) {
                $tab['title'][] = "Ban";
                $tab['id'][] = 'ban';
                $tab_content .= opentabbody($tab['title'][0], $tab['id'][0], 0);
                $tab_content .= openform('ban', 'post', FUSION_SELF.fusion_get_aidlink(), array('remote_url' => ADMIN.'members/sub_controllers/actions/ban.php'));
                $tab_content .= "<table class='table table-responsive table-striped'>\n";
                $tab_content .= "<thead>\n<tr><th>User</th><th>State Ban Reasons</th><th>Confirm Ban?</th></tr>\n</thead><tbody>";
                foreach ($user_to_ban as $user_data) {
                    $tab_content .= "<tr>                    
                    <td class='col-xs-3'>
                        <div class='pull-left m-r-10'>".display_avatar($user_data, '45px', '', '', '', '')."</div>\n                    
                        <div class='overflow-hide'>
                            <span class='va' style='height:45px;'></span>
                            <span class='va p-r-15'><strong>".$user_data['user_name']."</strong><br/>".getuserlevel($user_data['user_level'])."</span>                            
                        </div>                   
                    </td>
                    <td>".form_text('reason['.$user_data['user_id'].']', '', '', array('input_id' => 'reason_'.$user_data['user_id'], 'placeholder' => self::$locale['585a'], 'class'=>'m-b-0'))."</td>
                    <td>".form_button('ban_user', 'Ban '.$user_data['user_name'], $user_data['user_id'], array('input_id' => 'ban_'.$user_data['user_id'], 'class' => 'btn-danger'))."</td>
                    </tr>";
                }
                $tab_content .= "</tbody></table>\n";
                $tab_content .= closeform();
                $tab_content .= closetabbody();
            }

            if (!empty($user_to_unban)) {
                $tab['title'][] = "Unban";
                $tab['id'][] = 'unban';
                $tab_content .= opentabbody($tab['title'][0], $tab['id'][0], 0);
                $tab_content .= openform('ban', 'post', FUSION_SELF.fusion_get_aidlink(), array('remote_url' => fusion_get_settings('site_path').'administration/members/sub_controllers/actions/ban.php'));
                $tab_content .= "<table class='table table-responsive table-striped'>\n";
                $tab_content .= "<thead>\n<tr><th>User</th><th>State Reinstate Reasons</th><th>Confirm Reinstatement?</th></tr>\n</thead><tbody>";
                foreach ($user_to_ban as $user_data) {
                    $tab_content .= "<tr>                    
                    <td class='col-xs-3'>
                        <div class='pull-left m-r-10'>".display_avatar($user_data, '45px', '', '', '', '')."</div>\n                    
                        <div class='overflow-hide'>
                            <span class='va' style='height:45px;'></span>
                            <span class='va p-r-15'><strong>".$user_data['user_name']."</strong><br/>".getuserlevel($user_data['user_level'])."</span>                            
                        </div>                   
                    </td>
                    <td>".form_text('reason['.$user_data['user_id'].']', '', '', array('input_id' => 'reason_'.$user_data['user_id'], 'placeholder' => self::$locale['585a'], 'class'=>'m-b-0'))."</td>
                    <td>".form_button('ban_user', 'Reinstate '.$user_data['user_name'], $user_data['user_id'], array('input_id' => 'reinstate'.$user_data['user_id'], 'class' => 'btn-success'))."</td>
                    </tr>";
                }
                $tab_content .= "</tbody></table>\n";
                $tab_content .= closeform();
                $tab_content .= closetabbody();
            }

            ob_start();
            echo openmodal('userBan_modal', 'User Administration', array('static' => TRUE));
            echo opentab($tab, 0, 'ban_tab_id', FALSE, 'nav-tabs sm');
            echo $tab_content;
            echo closetab();
            echo closemodal();
            $modal = ob_get_contents();
            ob_end_clean();
            add_to_footer($modal);
            $javascript = "
                <script>
                $('button[name=ban_user]').bind('click', function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var data = { 'uid' : button.val() }
                    var sendData = $(this).closest('form').serialize() + '&' + $.param(data);                                    
                    $.ajax({
                        url: '".FUSION_ROOT.ADMIN."members/sub_controllers/actions/ban.php',
                        type: 'POST',
                        dataType: 'html',
                        data : sendData,
                        success: function(result){
                            console.log(result);
                            if (result.code == 'OK') {
                                button.addClass('disabled');
                            }                                                                                                               
                        },
                        error: function(result) {
                            console.log('Error ban');
                        }
                        });
                });
                </script>
                ";
            add_to_jquery(str_replace(array("<script>", "</script>"), '', $javascript));
        } else {
            redirect(USER_MANAGEMENT_SELF."&status=ber");
        }
    }

}

// add user actions - put this in members - ////display_suspend_log($this->user_id, 1, 0, 10);//$rowstart, 10);