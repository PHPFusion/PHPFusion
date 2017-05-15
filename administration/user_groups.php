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

/**
 * Class UserGroups
 * Administration
 */
class UserGroups {
    private static $instance = NULL;
    private static $locale = array();
    private static $limit = 20;
    private static $Group = array();
    private static $DefaultGroup = array();
    private static $GroupUser = array();

    private $data = array(
        'group_id'          => 0,
        'group_name'        => '',
        'group_description' => '',
        'group_icon'        => '',
    );

    public function __construct() {
        pageAccess("UG");

        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/user_groups.php");
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
        self::$Group = array_slice(getusergroups(), 4); //delete 0,-101,-102,-103
        self::$DefaultGroup = array_slice(getusergroups(), 0, 4); //delete 0,-101,-102,-103
        switch ($_GET['action']) {
            case 'delete':
                $this->delete_group($_GET['group_id']);
                break;
            case 'edit':
                if (isset($_GET['group_id'])) {
                    foreach (self::$Group as $groups) {
                        if ($_GET['group_id'] == $groups[0]) {
                            $this->data = array(
                                'group_id'          => $groups[0],
                                'group_name'        => $groups[1],
                                'group_description' => $groups[2],
                                'group_icon'        => $groups[3],
                            );
                        }
                    }
                }
                break;
            case 'user_edit':
                if (isset($_POST['user_send']) && empty($_POST['user_send'])) {
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
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'user_groups.php'.fusion_get_aidlink(), "title" => self::$locale['GRP_420']]);
    }

    public static function getInstance($key = TRUE) {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->update_group();
        }
        return self::$instance;
    }

    /*
     * Update add member, remove member
     */
    private function update_group() {
        if (isset($_POST['save_group'])) {
            $this->data = array(
                'group_id'          => form_sanitizer($_POST['group_id'], 0, "group_id"),
                'group_name'        => form_sanitizer($_POST['group_name'], '', 'group_name'),
                'group_description' => form_sanitizer($_POST['group_description'], '', 'group_description'),
                'group_icon'        => form_sanitizer($_POST['group_icon'], '', "group_icon"),
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
            if (isnum($_GET['group_id'])) {
                $group = getgroupname($_GET['group_id']);
                if ($group) {
                    $added_user = array();
                    foreach ($group_userSender as $grp) {
                        $groupadduser = fusion_get_user($grp);
                        if (!in_array($_GET['group_id'], explode(".", $groupadduser['user_groups']))) {
                            $groupadduser['user_groups'] = $groupadduser['user_groups'].".".$_GET['group_id'];
                            $added_user[] = $groupadduser['user_name'];
                            dbquery_insert(DB_USERS, $groupadduser, "update");
                            $i++;
                        }
                    }
                    addNotice("success", sprintf(self::$locale['GRP_410'], implode(', ', $added_user), $group));
                    redirect(FUSION_REQUEST);
                }
            }
        }

        if (isset($_POST['remove_sel'])) {
            $group_userSend = form_sanitizer($_POST['group'], '', 'group');
            $group_userSender = explode(',', $group_userSend);
            $i = 0;
            if (isnum($_GET['group_id'])) {
                $group = getgroupname($_GET['group_id']);
                if ($group) {
                    $rem_user = array();
                    foreach ($group_userSender as $grp) {
                        $groupadduser = fusion_get_user($grp);
                        if (in_array($_GET['group_id'], explode(".", $groupadduser['user_groups']))) {
                            $groupadduser['user_groups'] = self::Addusergroup($_GET['group_id'], $groupadduser['user_groups']);
                            $rem_user[] = $groupadduser['user_name'];
                            dbquery_insert(DB_USERS, $groupadduser, "update");
                            $i++;
                        }
                    }
                    addNotice("success", sprintf(self::$locale['GRP_411'], implode(', ', $rem_user), $group));
                    redirect(FUSION_REQUEST);
                }
            }
        }

        if (isset($_POST['remove_all'])) {
            if (isnum($_GET['group_id'])) {
                $group_name = getgroupname($_GET['group_id']);
                if ($group_name) {
                    $result = dbquery("SELECT user_id, user_name, user_groups FROM ".DB_USERS." WHERE user_groups REGEXP('^\\\.{$_GET['group_id']}$|\\\.{$_GET['group_id']}\\\.|\\\.{$_GET['group_id']}$')");
                    $i = 0;
                    if (dbrows($result)) {
                        while ($data = dbarray($result)) {
                            $data['user_groups'] = self::Addusergroup($_GET['group_id'], $data['user_groups']);
                            dbquery_insert(DB_USERS, $data, "update");
                            $i++;
                        }
                        addNotice("success", sprintf(self::$locale['GRP_411'], $i, $group_name));
                        redirect(FUSION_REQUEST);
                    }
                }
            }
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
                && dbcount("(group_id)", DB_USER_GROUPS, "group_id='".intval($id)."'")
            ) {
                return TRUE;
            }
        }

        return FALSE;
    }

    private function delete_group($id) {
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
        $allowed_section = array("usergroup", "usergroup_form", "user_form");
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'usergroup';
        $_GET['group_id'] = isset($_GET['group_id']) && isnum($_GET['group_id']) ? $_GET['group_id'] : 0;
        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['group_id']) ? TRUE : FALSE;

        $master_tab_title['title'][] = self::$locale['GRP_420'];
        $master_tab_title['id'][] = "usergroup";
        $master_tab_title['icon'][] = "";

        $master_tab_title['title'][] = $edit ? self::$locale['GRP_421'] : self::$locale['GRP_428'];
        $master_tab_title['id'][] = "usergroup_form";
        $master_tab_title['icon'][] = "";

        if (!empty($_GET['group_id'])) {
            $master_tab_title['title'][] = self::$locale['GRP_423'];
            $master_tab_title['id'][] = "user_form";
            $master_tab_title['icon'][] = "";
        }

        switch ($_GET['section']) {
            case "usergroup_form":
                \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, "title" => $master_tab_title['title'][1]]);
                $view = $this->groupForm();
                break;
            case "user_form":
                if (!empty($_GET['group_id'])) {
                    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, "title" => $master_tab_title['title'][2]]);
                    $view = $this->userForm();
                } else {
                    redirect(clean_request('section=usergroup', ['section'], FALSE));
                }
                break;
            default:
                \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, "title" => $master_tab_title['title'][0]]);
                $view = $this->group_list();
                break;
        }

        opentable(self::$locale['GRP_420']);
        echo opentab($master_tab_title, $_GET['section'], "usergroup", TRUE, FALSE, 'section', ['action']).$view.closetab();
        closetable();
    }

    /*
     * Displays Group Listing
     */
    public function group_list() {
        $aidlink = fusion_get_aidlink();
        $total_rows = count(self::$Group);

        $html = "<div class='clearfix spacer-xs'>\n";
        $html .= "<div class='pull-right'><a class='btn btn-success' href='".FUSION_SELF.$aidlink."&amp;section=usergroup_form'><i class='fa fa-plus fa-fw'></i> ".self::$locale['GRP_428']."</a>\n</div>\n";
        $html .= "<div class='overflow-hide'>".sprintf(self::$locale['GRP_424'], $total_rows)."</div>\n";
        $html .= "</div>\n";
        $html .= "<table class='table table-responsive table-striped'>\n";
        $html .= "<thead>\n";
        $html .= "<tr>\n";

        $html .= "<th>".self::$locale['GRP_432']."</th>\n";
        $html .= "<th>".self::$locale['GRP_433']."</th>\n";
        $html .= "<th class='min'>".self::$locale['GRP_436']."</th>\n";
        $html .= "<th>".self::$locale['GRP_437']."</th>\n";
        $html .= "<th>".self::$locale['GRP_435']."</th>\n";
        $html .= "<tr>\n";
        $html .= "</thead>\n<tbody>\n";
        if (!empty(self::$Group)) {
            foreach (self::$Group as $key => $groups) {
                $edit_link = FUSION_SELF.$aidlink."&amp;section=usergroup_form&amp;action=edit&amp;group_id=".$groups[0];
                $member_link = FUSION_SELF.$aidlink."&amp;section=user_form&amp;action=user_edit&amp;group_id=".$groups[0];
                $html .= "<tr>\n";
                $html .= "<td><a href='$edit_link'>".$groups[1]." (".self::count_usergroup($groups[0]).")</a></td>\n";
                $html .= "<td>".$groups[2]."</td>\n";
                $html .= "<td class='text-center'>".(!empty($groups[3]) ? "<i class='".$groups[3]."'></i> " : $groups[3])."</td>\n";
                $html .= "<td>";
                $html .= "<a href='$member_link'>".self::$locale['GRP_438']."</a> - ";
                $html .= "<a href='$edit_link'>".self::$locale['edit']."</a> - ";
                $html .= "<a href='".FUSION_SELF.$aidlink."&amp;section=usergroup&amp;action=delete&amp;group_id=".$groups[0]."' onclick=\"return confirm('".self::$locale['GRP_425']."');\">".self::$locale['delete']."</a>\n";
                $html .= "</td>\n";
                $html .= "<td>".$groups[0]."</td>\n";
                $html .= "</tr>\n";
            }
        } else {
            $html .= "<tr>\n<td colspan='5 text-center'>".self::$locale['GRP_404']."</td>\n</tr>\n";
        }
        $html .= "</tbody>\n<tfoot>\n";
        $html .= "<tr><td colspan='5'><strong>".self::$locale['GRP_426']."</strong></td></tr>\n";
        foreach (self::$DefaultGroup as $key => $groups) {
            $html .= "<tr>\n";

            $html .= "<td>".$groups[1]."</td>\n";
            $html .= "<td>".$groups[2]."</td>\n";
            $html .= "<td class='text-center'>".(!empty($groups[3]) ? "<i class='".$groups[3]."'></i>" : $groups[3])."</td>\n";
            $html .= "<td>&nbsp;</td>\n";
            $html .= "<td>".$groups[0]."</td>\n";
            $html .= "</tr>\n";
        }
        $html .= "</tfoot>\n";
        $html .= "</table>\n";

        return $html;
    }

    /*
     * Group Add/Edit Form
     */
    public function groupForm() {
        $html = openform('editform', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=usergroup_form", ['class' => 'spacer-xs']);
        $html .= form_hidden('group_id', '', $this->data['group_id']);
        $html .= form_text('group_name', self::$locale['GRP_432'], $this->data['group_name'], ['required' => TRUE, 'maxlength' => '100', 'error_text' => self::$locale['GRP_464']]);
        $html .= form_textarea('group_description', self::$locale['GRP_433'], $this->data['group_description'], ['autosize' => TRUE, 'maxlength' => '200']);
        $html .= form_text('group_icon', self::$locale['GRP_439'], $this->data['group_icon'], ['maxlength' => '100', 'placeholder' => 'fa fa-user']);
        $html .= form_button('save_group', self::$locale['GRP_434'], self::$locale['GRP_434'], ['class' => 'btn-primary']);
        $html .= closeform();

        return $html;
    }

    /*
     * User Management Form
     */
    public function userForm() {
        $total_rows = $this->count_usergroup($_GET['group_id']);
        $rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
        $result = $this->_selectDB($rowstart, self::$limit);
        $rows = dbrows($result);

        $html = "<div class='spacer-xs'>\n";
        $html .= "<h4>".self::$locale['GRP_452'].getgroupname($_GET['group_id'], $return_desc = FALSE, $return_icon = FALSE)."</h4>\n";
        $html .= "<hr/>\n";
        $html .= "<div class='row flexbox'>\n";
        $html .= "<div class='col-xs-12 col-sm-4'>\n";
        $html .= openform('searchuserform', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=user_form&amp;action=user_edit&amp;group_id=".$_GET['group_id'],
            ['class' => 'list-group-item p-10 m-t-0 m-b-20'
            ]);
        $html .= form_user_select("user_send", self::$locale['GRP_440'], '', array('max_select'  => 10,
                                                                                   'inline'      => FALSE,
                                                                                   'inner_width' => '100%',
                                                                                   'width'       => '100%',
                                                                                   'required'    => TRUE,
                                                                                   'allow_self'  => TRUE,
                                                                                   'placeholder' => self::$locale['GRP_451'],
                                                                                   'ext_tip'     => self::$locale['GRP_441']."<br />".self::$locale['GRP_442']
        ));
        $html .= form_button('search_users', self::$locale['confirm'], self::$locale['confirm'], array('class' => 'btn-primary'));
        $html .= closeform();
        if (!empty(self::$GroupUser)) {
            $html .= openform('add_users_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=user_form&amp;action=user_edit&amp;group_id=".$_GET['group_id']);
            $html .= "<table class='table table-striped table-hover table-responsive'>\n";
            $html .= "<thead>\n";
            $html .= "<tr>\n";
            echo "<th>".self::$locale['GRP_446']."</th>\n";
            $html .= "<th></th>\n";
            $html .= "<th>".self::$locale['GRP_447']."</th>\n";
            $html .= "<th>".self::$locale['GRP_437']."</th>\n";
            $html .= "<tr>\n";
            $html .= "</thead>\n";
            $html .= "<tbody>\n";
            foreach (self::$GroupUser as $groupusers) {
                $html .= "<tr>\n";
                $html .= "<td>".form_checkbox("groups_add[]", '', '', array("inline" => FALSE, 'value' => $groupusers['user_id']))."</td>\n";
                $html .= "<td>".$groupusers['user_name']."</td>\n";
                $html .= "<td>".getuserlevel($groupusers['user_level'])."</td>\n";
                $html .= "</tr>\n";
            }
            $html .= "</tbody>\n";
            $html .= "</table>\n";
            $html .= "<div class='spacer-xs'>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"javascript:setChecked('add_users_form','groups_add[]',1);return false;\">".self::$locale['GRP_448']."</a>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"javascript:setChecked('add_users_form','groups_add[]',0);return false;\">".self::$locale['GRP_449']."</a>\n";
            $html .= form_button('add_sel', self::$locale['GRP_450'], self::$locale['GRP_450'], array('class' => 'btn-primary'));
            $html .= "</div>\n";
            $html .= closeform();
        }
        $html .= "</div>\n";
        $html .= "<div class='col-xs-12 col-sm-8'>\n";

        if ($rows > 0) {
            $html .= open_side(self::$locale['GRP_460']);
            $html .= "<div class='clearfix spacer-xs'>\n";
            $html .= ($total_rows > $rows ? "<div class='pull-right'>\n".makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", array("aid", "section"), TRUE)."&amp;")."</div>\n" : "");
            $html .= "<div class='overflow-hide'>".sprintf(self::$locale['GRP_427'], $rows, $total_rows)."</div>\n";
            $html .= "</div>\n";
            $html .= openform('rem_users_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=user_form&amp;action=user_edit&amp;group_id=".$_GET['group_id']);
            $html .= "<table class='table table-striped table-hover table-responsive'>\n";
            $html .= "<thead>\n";
            $html .= "<tr>\n";
            $html .= "<th>".self::$locale['GRP_437']."</th>\n";
            $html .= "<th>".self::$locale['GRP_446']."</th>\n";
            $html .= "<th>".self::$locale['GRP_447']."</th>\n";
            $html .= "<tr>\n";
            $html .= "</thead>\n";
            $html .= "<tbody>\n";
            while ($data = dbarray($result)) {
                $html .= "<tr>\n";
                $html .= "<td>".form_checkbox("group[]", '', '', array("inline" => FALSE, 'value' => $data['user_id']))."</td>\n";
                $html .= "<td>".$data['user_name']."</td>\n";
                $html .= "<td>".getuserlevel($data['user_level'])."</td>\n";
                $html .= "</tr>\n";
            }
            $html .= "</tbody></table>\n";
            $html .= "<div class='spacer-xs pull-right m-t-10'>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"javascript:setChecked('rem_users_form','group[]',1);return false;\">".self::$locale['GRP_448']."</a>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"javascript:setChecked('rem_users_form','group[]',0);return false;\">".self::$locale['GRP_449']."</a>\n";
            $html .= form_button('remove_sel', self::$locale['GRP_461'], self::$locale['GRP_461'], array('class' => 'btn-danger'));
            $html .= form_button('remove_all', self::$locale['GRP_462'], self::$locale['GRP_462'], array('class' => 'btn-danger'));
            $html .= "</div>\n";
            $html .= "</div>\n";
            $html .= closeform();
            $html .= close_side();
        } else {
            $html .= "<div class='well text-center'>".self::$locale['GRP_463']."</div>\n";
        }

        $html .= "</div>\n";
        $html .= "</div>\n";

        add_to_footer("<script type='text/javascript'>\n/* <![CDATA[ */\n
        function setChecked(frmName,chkName,val) {"."\n
        dml=document.forms[frmName];"."\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {"."\n
        if(dml.elements[i].name == chkName) {"."\n"."dml.elements[i].checked = val;"."\n
        }\n}\n}\n
        /* ]]>*/\n
        </script>\n
        ");

        return $html;
    }
}

UserGroups::getInstance(TRUE)->display_admin();
require_once THEMES."templates/footer.php";
