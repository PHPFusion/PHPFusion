<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_groups.php
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
require_once "../maincore.php";
require_once THEMES."templates/admin_header.php";
pageAccess("UG");

class UserGroups {
    private static $instance = NULL;
    private static $locale = array();
    private static $limit = 20;
    private static $Group = array();
    private static $DefaultGroup = array();
    private static $GroupUser = array();

    private $data = array(
	'group_id' 			=> 0,
    'group_name' 		=> '',
    'group_description'	=> '',
    'group_icon' 		=> '',
	);

    public function __construct() {
        $this->set_locale();
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
        self::$Group = array_slice(getusergroups(), 4); //delete 0,-101,-102,-103
        self::$DefaultGroup = array_slice(getusergroups(), 0,4); //delete 0,-101,-102,-103
        switch ($_GET['action']) {
            case 'delete':
        self::delete_group($_GET['group_id']);
                break;
            case 'edit':
            	if (isset($_GET['group_id'])) {
            		foreach (self::$Group as $groups){
            			if ($_GET['group_id'] == $groups[0]) {
        					$this->data = array(
								'group_id' 			=> $groups[0],
    							'group_name' 		=> $groups[1],
    							'group_description'	=> $groups[2],
    							'group_icon' 		=> $groups[3],
        					);
            			}
            		}
            	}
                break;
            case 'user_edit':
                if (isset($_POST['user_send']) && empty($_POST['user_send'])){
					\defender::stop();
					addNotice('danger', self::$locale['GRP_403']);
					redirect(clean_request("section=user_group", array("", "aid"), TRUE));
                }
            	if (isset($_POST['user_send']) && !empty($_POST['user_send'])) {
            		$group_userSend = form_sanitizer($_POST['user_send'], '', 'user_send');
            		$group_userSender = explode(',', $group_userSend);
            		foreach ($group_userSender as $grp) {
            			self::$GroupUser[] = fusion_get_user($grp);
            		}
            	}
                break;
            case 'user_add':
            	if (empty($_POST['groups_add']) or empty($_GET['group_id'])) {
					\defender::stop();
					addNotice('danger', self::$locale['GRP_408']);
					redirect(clean_request("", array("section=user_form", "aid"), TRUE));
            	}
                break;
            case 'user_del':
            	if (empty($_POST['group']) or empty($_GET['group_id'])) {
					\defender::stop();
					addNotice('danger', self::$locale['GRP_408']);
					redirect(clean_request("", array("section=user_form", "aid"), TRUE));
            	}
                break;
            default:
                 break;
        }
	\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link'=> ADMIN.'user_groups.php'.fusion_get_aidlink(), "title"=> self::$locale['GRP_420']]);
    }

    public static function getInstance($key = TRUE) {
        if (self::$instance === NULL) {
            self::$instance = new static();
        	self::$instance->set_groupdb();
        }
        return self::$instance;
    }

    private static function set_locale() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/user_groups.php");
    }

    private function set_groupdb() {
		if (isset($_POST['save_group'])) {
			$this->data = array(
				'group_id' => form_sanitizer($_POST['group_id'], 0, "group_id"),
				'group_name' => form_sanitizer($_POST['group_name'], '', 'group_name'),
				'group_description' => form_sanitizer($_POST['group_description'], '', 'group_description'),
				'group_icon' => form_sanitizer($_POST['group_icon'], '', "group_icon"),
			);
				if (\defender::safe()) {
					dbquery_insert(DB_USER_GROUPS, $this->data, empty($this->data['group_id']) ? "save" : "update");
					addNotice("success", empty($this->data['group_id']) ? self::$locale['GRP_401'] : self::$locale['GRP_400']);
					redirect(clean_request("section=usergroup", array("", "aid"), TRUE));
				}
		}

		if (isset($_POST['add_sel'])) {
        	$group_userSend = form_sanitizer($_POST['groups_add'], '', 'groups_add');
			$group_userSender = explode(',', $group_userSend);
            $i = 0;
            $usercount = count($group_userSender);
			foreach ($group_userSender as $grp) {
				$groupadduser = fusion_get_user($grp);
				if (!in_array($_GET['group_id'], explode(".", $groupadduser['user_groups'])) ) {
					$groupadduser['user_groups'] = $groupadduser['user_groups'].".".$_GET['group_id'];
					dbquery_insert(DB_USERS, $groupadduser, "update");
					$i++;
				}
			}
			 addNotice("success", $usercount." / ".$i.self::$locale['GRP_409'].($i != $usercount ? "<br />".($usercount - $i).self::$locale['GRP_410'] : ""));
		}

		if (isset($_POST['remove_sel'])) {
        	$group_userSend = form_sanitizer($_POST['groups'], '', 'groups');
			$group_userSender = explode(',', $group_userSend);
            $i = 0;
            $usercount = count($group_userSender);
			foreach ($group_userSender as $grp) {
				$groupadduser = fusion_get_user($grp);
				if (in_array($_GET['group_id'], explode(".", $groupadduser['user_groups'])) ) {
        			$groupadduser['user_groups'] = self::Addusergroup($_GET['group_id'], $groupadduser['user_groups']);
					dbquery_insert(DB_USERS, $groupadduser, "update");
					$i++;
				}
			}
			 addNotice("success", $usercount." / ".$i.self::$locale['GRP_411'].($i != $usercount ? "<br />".($usercount - $i).self::$locale['GRP_412'] : ""));
		}

		if (isset($_POST['remove_all'])) {
    		$result = dbquery("SELECT user_id, user_name, user_groups
		    	FROM ".DB_USERS."
		    	WHERE user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')
		    	");
			$i = 0;
		    while ($data = dbarray($result)) {
	        	$data['user_groups'] = self::Addusergroup($_GET['group_id'], $data['user_groups']);
				dbquery_insert(DB_USERS, $data, "update");
				$i++;
    		}
			 addNotice("warning", $i.self::$locale['GRP_411']);
		}
    }

    static function Addusergroup($group_id, $groups) {
        return $user_groups = preg_replace(array(
                                        "(^\.{$group_id}$)",
                                        "(\.{$group_id}\.)",
                                        "(\.{$group_id}$)"
                                    ), array(
                                        "",
                                        ".",
                                        ""
                                    ), $groups);
    }

    static function count_usergroup($id) {
		if (isnum($id)) {
			return dbcount("(user_id)", DB_USERS, "user_groups REGEXP('^\\\.{$id}$|\\\.{$id}\\\.|\\\.{$id}$')");
		}
	return FALSE;
    }

    static function verify_group($id) {
        if (isnum($id)) {
    	if (!dbcount("(user_id)", DB_USERS, "user_groups REGEXP('^\\\.{$id}$|\\\.{$id}\\\.|\\\.{$id}$')")
            && dbcount("(group_id)", DB_USER_GROUPS, "group_id='".intval($id)."'")) {
            return TRUE;
            }
        }

        return FALSE;
    }

    private static function delete_group($id) {
        if (self::verify_group($id)) {
            dbquery("DELETE FROM ".DB_USER_GROUPS." WHERE group_id='".intval($id)."'");
            addNotice('warning', self::$locale['GRP_407']);
	        redirect(clean_request("", array("section=usergroup", "aid"), TRUE));
        } else {
            addNotice('warning', self::$locale['GRP_405']." ".self::$locale['GRP_406']);
	        redirect(clean_request("", array("section=usergroup", "aid"), TRUE));
        }
    }

    public function _selectDB($rows, $min) {
		$result = dbquery("SELECT user_id, user_name, user_level, user_avatar, user_status
			FROM ".DB_USERS."
			WHERE user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')
			ORDER BY user_level DESC, user_name
			LIMIT ".intval($rows).", ".$min
		);
        return $result;
    }

    public function display_admin() {
	$aidlink = fusion_get_aidlink();

	$allowed_section = array("usergroup", "usergroup_form", "user_form");
	$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'usergroup';
	$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['group_id']) ? TRUE : FALSE;
	$_GET['group_id'] = isset($_GET['group_id']) && isnum($_GET['group_id']) ? $_GET['group_id'] : 0;

	opentable(self::$locale['GRP_420']);
    $master_tab_title['title'][] = self::$locale['GRP_420'];
	$master_tab_title['id'][] = "usergroup";
	$master_tab_title['icon'][] = "";

    $master_tab_title['title'][] = $edit ? self::$locale['GRP_421'] : self::$locale['GRP_422'];
    $master_tab_title['id'][] = "usergroup_form";
    $master_tab_title['icon'][] = "";

	if (!empty($_GET['group_id'])){
		$master_tab_title['title'][] = self::$locale['GRP_423'];
		$master_tab_title['id'][] = "user_form";
		$master_tab_title['icon'][] = "";
	}

		echo opentab($master_tab_title, $_GET['section'], "usergroup", TRUE);
		switch ($_GET['section']) {
 		   case "usergroup_form":
		\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link'=> FUSION_REQUEST, "title"=> $master_tab_title['title'][1]]);
	        $this->groupForm();
	        break;
 		   case "user_form":
		if (!empty($_GET['group_id'])){
		\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link'=> FUSION_REQUEST, "title"=> $master_tab_title['title'][2]]);
	        $this->userForm();
		}
	        break;
	    default:
		\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link'=> FUSION_REQUEST, "title"=> $master_tab_title['title'][0]]);
	        $this->group_list();
	        break;
		}
		echo closetab();
	closetable();
    }

    public function group_list() {
	$aidlink = fusion_get_aidlink();
    $total_rows = count(self::$Group);

    echo "<div class='clearfix'>\n";
	echo "<span class='pull-right m-t-10'>".sprintf(self::$locale['GRP_424'], $total_rows)."</span>\n";
	echo "<span class='pull-left m-t-10'><a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=usergroup_form'><i class='fa fa-plus-square-o fa-fw'></i> ".self::$locale['GRP_422']."</a>\n</span>\n";
    echo "</div>\n";

		if (!empty(self::$Group)) {
			echo "<div class='row m-t-20'>\n";
    	    echo "<div class='col-md-1 m-t-20'>".self::$locale['GRP_435']."</div>\n";
    	    echo "<div class='col-md-2 m-t-20'>".self::$locale['GRP_432']."</div>\n";
    	    echo "<div class='col-md-3 m-t-20'>".self::$locale['GRP_433']."</div>\n";
    	    echo "<div class='col-md-1 m-t-20'>".self::$locale['GRP_436']."</div>\n";
    	    echo "<div class='col-md-5 m-t-20'>".self::$locale['GRP_437']."</div>\n";
        foreach (self::$Group as $key => $groups) {

            echo "<div class='col-md-1 m-t-20'>".$groups[0]."</div>\n";
            echo "<div class='col-md-2 m-t-20'>".$groups[1]." (".self::count_usergroup($groups[0]).")</div>\n";
            echo "<div class='col-md-3 m-t-20'>".$groups[2]."</div>\n";
            echo "<div class='col-md-2 m-t-20'>".(!empty($groups[3]) ? "<i class='".$groups[3]."'></i> ".$groups[3] : $groups[3])."</div>\n";
            echo "<div class='col-md-4 m-t-20'>";
            echo "<a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=user_form&amp;action=user_edit&amp;group_id=".$groups[0]."'><i class='fa fa-user-plus fa-fw'></i> ".self::$locale['GRP_438']."</a>\n";
            echo "<a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=usergroup_form&amp;action=edit&amp;group_id=".$groups[0]."'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>\n";
            echo "<a class='btn btn-danger btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=usergroup&amp;action=delete&amp;group_id=".$groups[0]."' onclick=\"return confirm('".self::$locale['GRP_425']."');\">".self::$locale['delete']."<i class='fa fa-trash m-l-10'></i></a>\n";
            echo "</div>\n";
        }
            echo "<div class='well text-center'>".self::$locale['GRP_426']."</div>\n";
        foreach (self::$DefaultGroup as $key => $groups) {
            echo "<div class='col-md-1 m-t-10'>".$groups[0]."</div>\n";
            echo "<div class='col-md-2 m-t-10'>".$groups[1]."</div>\n";
            echo "<div class='col-md-3 m-t-10'>".$groups[2]."</div>\n";
            echo "<div class='col-md-2 m-t-10'>".(!empty($groups[3]) ? "<i class='".$groups[3]."'></i> ".$groups[3] : $groups[3])."</div>\n";
            echo "<div class='col-md-4 m-t-10'>&nbsp;</div>\n";

        }

        	echo "</div>\n";
    	} else {
        	echo "<div class='text-center'>".self::$locale['GRP_404']."</div>\n";
    	}
    }

    public function userForm() {
		$total_rows = $this->count_usergroup($_GET['group_id']);
		$rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
		$result = $this->_selectDB($rowstart, self::$limit);
		$rows = dbrows($result);
    	$tx = "<div class='col-md-5 m-t-10'>".self::$locale['GRP_446']."</div>\n";
    	$tx .= "<div class='col-md-5 m-t-10'>".self::$locale['GRP_447']."</div>\n";
    	$tx .= "<div class='col-md-2 m-t-10'>".self::$locale['GRP_437']."</div>\n";

        echo "<div class='well text-center'>".self::$locale['GRP_452'].getgroupname($_GET['group_id'], $return_desc = FALSE, $return_icon = FALSE)."</div>\n";

    if (!isset($_POST['search_users'])) {
        openside(self::$locale['GRP_451']);
        echo "<div class='well text-center'>".self::$locale['GRP_441']."<br />".self::$locale['GRP_442']."</div>\n";
        echo openform('searchuserform', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=user_form&amp;action=user_edit&amp;group_id=".$_GET['group_id']);
		echo form_user_select("user_send", self::$locale['choose-user'], '', array('maxselect' => 10,
                "required" => TRUE,
                "inline" => TRUE,
                'placeholder' => self::$locale['GRP_446']
            ));
        echo form_button('search_users', self::$locale['search'], self::$locale['search'], array('class' => 'btn-primary'));
        echo closeform();
        closeside();
    }
        openside(self::$locale['GRP_460']);

		echo "<div class='clearfix'>\n";
		echo "<span class='pull-right m-t-10'>".sprintf(self::$locale['GRP_427'], $rows, $total_rows)."</span>\n";
		echo "</div>\n";
		echo ($total_rows > $rows) ? makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", array("aid", "section"), TRUE)."&amp;") : "";

        if (!empty(self::$GroupUser)){
		openside(self::$locale['GRP_440']);
        echo openform('add_users_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=user_form&amp;action=user_add&amp;group_id=".$_GET['group_id']);
		echo "<div class='row m-t-20'>\n";
        echo $tx;
        	foreach (self::$GroupUser as $groupusers) {
    	    echo "<div class='col-md-5 m-t-10'>".$groupusers['user_name']."</div>\n";
    	    echo "<div class='col-md-5 m-t-10'>".getuserlevel($groupusers['user_level'])."</div>\n";
    	    echo "<div class='col-md-2 m-t-10'>".form_checkbox("groups_add[]", '', '', array("inline" => FALSE, 'value' =>$groupusers['user_id']))."</div>\n";

        	}
        echo "<div class='pull-right m-t-10'>\n";
        echo "<div class='btn-group'>\n";
        echo "<a class='btn btn-primary' href='#' onclick=\"javascript:setChecked('add_users_form','groups_add[]',1);return false;\">".self::$locale['GRP_448']."</a>\n";
        echo "<a class='btn btn-primary' href='#' onclick=\"javascript:setChecked('add_users_form','groups_add[]',0);return false;\">".self::$locale['GRP_449']."</a>\n";
        echo form_button('add_sel', self::$locale['GRP_450'], self::$locale['GRP_450'], array('class' => 'btn-primary'));
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n";
        closeside();
        }
		if ($rows > 0) {
        openside(self::$locale['GRP_460']);
        echo openform('rem_users_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=user_form&amp;action=user_del&amp;group_id=".$_GET['group_id']);
			echo "<div class='row m-t-20'>\n";
        echo $tx;

        while ($data = dbarray($result)) {
    	    echo "<div class='col-md-5 m-t-10'>".$data['user_name']."</div>\n";
    	    echo "<div class='col-md-5 m-t-10'>".getuserlevel($data['user_level'])."</div>\n";
    	    echo "<div class='col-md-2 m-t-10'>".form_checkbox("group[]", '', '', array("inline" => FALSE, 'value' =>$data['user_id']))."</div>\n";

        }
        echo "<div class='pull-right m-t-10'>\n";
        echo "<div class='btn-group'>\n";
        echo "<a class='btn btn-primary' href='#' onclick=\"javascript:setChecked('rem_users_form','group[]',1);return false;\">".self::$locale['GRP_448']."</a>\n";
        echo "<a class='btn btn-primary' href='#' onclick=\"javascript:setChecked('rem_users_form','group[]',0);return false;\">".self::$locale['GRP_449']."</a>\n";
        echo form_button('remove_sel', self::$locale['GRP_461'], self::$locale['GRP_461'], array('class' => 'btn-primary'));
        echo form_button('remove_all', self::$locale['GRP_462'], self::$locale['GRP_462'], array('class' => 'btn-primary'));
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n";

    	echo closeform();
        closeside();
		} else {
        echo "<div class='well text-center'>".self::$locale['GRP_463']."</div>\n";
		}
        closeside();
add_to_footer("<script type='text/javascript'>\n/* <![CDATA[ */\n
function setChecked(frmName,chkName,val) {"."\n
dml=document.forms[frmName];"."\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {"."\n
if(dml.elements[i].name == chkName) {"."\n"."dml.elements[i].checked = val;"."\n
}\n}\n}\n
/* ]]>*/\n
</script>\n");

    }

    public function groupForm() {
    openside('');
		echo openform('editform', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=usergroup_form");
		echo form_hidden('group_id', '', $this->data['group_id']);
		echo form_text('group_name', self::$locale['GRP_432'], $this->data['group_name'], array('required' => 1, 'maxlength' => '100', 'error_text' => self::$locale['GRP_464']));
		echo form_textarea('group_description', self::$locale['GRP_433'], $this->data['group_description'], array("autosize" => TRUE, 'maxlength' => '200'));
		echo form_text('group_icon', self::$locale['GRP_439'], $this->data['group_icon'], array('maxlength' => '100', 'placeholder' => 'fa fa-user'));
		echo form_button('save_group', self::$locale['GRP_434'], self::$locale['GRP_434'], array('class' => 'btn-primary'));
		echo closeform();
	closeside();
    }

}

UserGroups::getInstance(TRUE)->display_admin();

require_once THEMES."templates/footer.php";
