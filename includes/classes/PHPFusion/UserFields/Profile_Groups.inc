<?php
namespace PHPFusion\UserFields;

use PHPFusion\UserGroups;

class Profile_Groups {

    private $user_data = [];
    private $profile_id = 0;

    public function __construct($profile_id, $user_data) {
        $this->profile_id = $profile_id;
        $this->user_data = $user_data;
    }
    
    /**
     * @return bool
     */
    private function groupModeratorAccess() {
        return ( checkrights( "UG" ) && fusion_get_userdata( "user_level" ) <= USER_LEVEL_ADMIN && $this->profile_id != '1' || iSUPERADMIN ) ? TRUE : FALSE;
    }
    
    public function showGroupProfile() {
        $group_admin = [];
        $aidlink = fusion_get_aidlink();
        $locale = fusion_get_locale();

        if ($this->groupModeratorAccess()) {
            if (post("add_to_group") && $user_group = post("user_group", FILTER_VALIDATE_INT)) {
                $response = UserGroups::getInstance()->setGroup($user_group)->add($this->profile_id);
                if ($response === TRUE) {
                    $step = get("step");
                    if (defined('ADMIN_PANEL') && $step == "view") {
                        redirect(ADMIN."members.php".fusion_get_aidlink()."&amp;step=view&amp;user_id=".$this->profile_id);
                    } else {
                        redirect(BASEDIR."profile.php?lookup=".$this->profile_id);
                    }
                }
            }

            $groups_cache = cache_groups();
            $user_groups_opts = [];
            if (count($groups_cache) > 0) {
                foreach ($groups_cache as $group) {
                    if (!preg_match("(^{$group['group_id']}|\.{$group['group_id']}\.|\.{$group['group_id']}$)", $this->user_data['user_groups'])) {
                        $user_groups_opts[$group['group_id']] = $group['group_name'];
                    }
                }
            }


            if (!empty($user_groups_opts)) {

                $submit_link = BASEDIR."profile.php?lookup=".$this->profile_id."&amp;profile_page=groups";
                if (defined('ADMIN_PANEL') && get("step") == "view") {
                    $submit_link = ADMIN."members.php".$aidlink."&amp;step=view&amp;user_id=".$this->profile_id."&amp;lookup=".$this->profile_id;
                }

                $group_admin['group_admin'] = [
                    'ug_openform'       => openform("admin_grp_form", "POST", $submit_link),
                    'ug_closeform'      => closeform(),
                    'ug_title'          => $locale['u061'],
                    'ug_dropdown_input' => form_select("user_group", '',
                        "", [
                            "select2_disabled" => TRUE,
                            "class" => "m-b-0",
                            "options"          => $user_groups_opts,
                            "width"            => "100%",
                            "inner_width"      => "100%",
                            "inline"           => TRUE,
                        ]),
                    'ug_button'         =>form_button("add_to_group", $locale['u059'], $locale['u059'], ["class" => "btn-default"])
                ];
            }
        }

        $ug_class = new UserGroups();
        $ug_class->user_data = $this->user_data;
        $ug_class->profile_id = $this->profile_id;
        $info = $ug_class->get_OutputInfo() + $group_admin;

        return $info;
    }
    
    
}
