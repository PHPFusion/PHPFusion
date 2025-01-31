<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_groups.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('UG');

/**
 * Class UserGroups
 * Administration
 */
class UserGroups {
    private static $instance = NULL;
    private static $locale = [];
    private static $limit = 20;
    private static $groups = [];
    private static $default_groups = [];
    private static $group_users = [];

    private $data = [
        'group_id'          => 0,
        'group_name'        => '',
        'group_description' => '',
        'group_icon'        => ''
    ];

    public function __construct() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/user_groups.php");

        add_breadcrumb(['link' => ADMIN.'user_groups.php'.fusion_get_aidlink(), "title" => self::$locale['GRP_420']]);

        self::$groups = array_slice(getusergroups(), 4); //delete 0,-101,-102,-103
        self::$default_groups = array_slice(getusergroups(), 0, 4); //delete 0,-101,-102,-103

        switch (get('action')) {
            case 'delete':
                if (self::verifyGroup(get('group_id'))) {
                    dbquery("DELETE FROM ".DB_USER_GROUPS." WHERE group_id='".intval(get('group_id'))."'");
                    addnotice('success', self::$locale['GRP_407']);
                } else {
                    addnotice('warning', self::$locale['GRP_405']." ".self::$locale['GRP_406']);
                }
                redirect(clean_request("", ["section=usergroup", "aid"]));
                break;
            case 'edit':
                if (check_get('group_id')) {
                    foreach (self::$groups as $groups) {
                        if (get('group_id') == $groups[0]) {
                            $this->data = [
                                'group_id'          => $groups[0],
                                'group_name'        => $groups[1],
                                'group_description' => $groups[2],
                                'group_icon'        => $groups[3],
                            ];
                        }
                    }
                }
                break;
            case 'user_edit':
                if (check_post('user_send') && empty(post('user_send'))) {
                    fusion_stop();
                    addnotice('danger', self::$locale['GRP_403']);
                    redirect(clean_request("section=user_group", ["", "aid"]));
                }
                if (check_post('user_send') && !empty(post('user_send'))) {
                    $group_userSend = sanitizer('user_send', '', 'user_send');
                    $group_userSender = explode(',', $group_userSend);
                    foreach ($group_userSender as $grp) {
                        self::$group_users[] = fusion_get_user($grp);
                    }
                }
                break;
            case 'user_add':
                if (empty(post('groups_add')) or empty(get('group_id'))) {
                    fusion_stop();
                    addnotice('danger', self::$locale['GRP_408']);
                    redirect(clean_request("", ["section=user_form", "aid"]));
                }
                break;
            case 'user_del':
                if (empty(post('group')) or empty(get('group_id'))) {
                    fusion_stop();
                    addnotice('danger', self::$locale['GRP_408']);
                    redirect(clean_request("", ["section=user_form", "aid"]));
                }
                break;
            default:
                break;
        }
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->updateGroup();
        }
        return self::$instance;
    }

    /*
     * Update add member, remove member
     */
    private function updateGroup() {
        if (check_post('save_group')) {
            $this->data = [
                'group_id'          => sanitizer('group_id', 0, "group_id"),
                'group_name'        => sanitizer('group_name', '', 'group_name'),
                'group_description' => sanitizer('group_description', '', 'group_description'),
                'group_icon'        => sanitizer('group_icon', '', "group_icon"),
            ];
            if (fusion_safe()) {
                dbquery_insert(DB_USER_GROUPS, $this->data, empty($this->data['group_id']) ? "save" : "update");
                addnotice("success", empty($this->data['group_id']) ? self::$locale['GRP_401'] : self::$locale['GRP_400']);
                redirect(clean_request("section=usergroup", ["", "aid"]));
            }
        }
        if (check_post('add_sel')) {
            $group_userSend = sanitizer(['groups_add'], '', 'groups_add');
            $group_userSender = explode(',', $group_userSend);
            $i = 0;
            if (check_get('group_id') && get('group_id', FILTER_SANITIZE_NUMBER_INT)) {
                $group = getgroupname(get('group_id'));
                if ($group) {
                    $added_user = [];
                    foreach ($group_userSender as $grp) {
                        $groupadduser = fusion_get_user($grp);
                        if (!in_array(get('group_id'), explode(".", $groupadduser['user_groups']))) {
                            $groupadduser['user_groups'] = $groupadduser['user_groups'].".".get('group_id');
                            $added_user[] = $groupadduser['user_name'];
                            dbquery_insert(DB_USERS, $groupadduser, "update");
                            $i++;
                        }
                    }
                    addnotice("success", sprintf(self::$locale['GRP_410'], implode(', ', $added_user), $group));
                    redirect(FUSION_REQUEST);
                }
            }
        }

        if (check_post('remove_sel')) {
            $group_userSend = sanitizer(['group'], '', 'group');
            $group_userSender = explode(',', $group_userSend);
            $i = 0;
            if (check_get('group_id') && get('group_id', FILTER_SANITIZE_NUMBER_INT)) {
                $group = getgroupname(get('group_id'));
                if ($group) {
                    $rem_user = [];
                    foreach ($group_userSender as $user) {
                        $groupadduser = fusion_get_user($user);
                        if (!empty($groupadduser['user_groups']) && in_array(get('group_id'), explode(".", $groupadduser['user_groups']))) {
                            $groupadduser['user_groups'] = self::addUserGroup(get('group_id'), $groupadduser['user_groups']);
                            $rem_user[] = $groupadduser['user_name'];
                            dbquery_insert(DB_USERS, $groupadduser, "update");
                            $i++;
                        }
                    }
                    addnotice("success", sprintf(self::$locale['GRP_411'], implode(', ', $rem_user), $group));
                    redirect(FUSION_REQUEST);
                }
            }
        }

        if (check_post('remove_all')) {
            if (check_get('group_id') && get('group_id', FILTER_SANITIZE_NUMBER_INT)) {
                $group_name = getgroupname(get('group_id'));
                if ($group_name) {
                    $group_id = get('group_id');
                    $result = dbquery("SELECT user_id, user_name, user_groups
                        FROM ".DB_USERS."
                        WHERE user_groups REGEXP('^\\\.$group_id$|\\\.$group_id\\\.|\\\.$group_id$')
                    ");
                    $i = 0;
                    if (dbrows($result)) {
                        while ($data = dbarray($result)) {
                            $data['user_groups'] = self::addUserGroup(get('group_id'), $data['user_groups']);
                            dbquery_insert(DB_USERS, $data, "update");
                            $i++;
                        }
                        addnotice("success", sprintf(self::$locale['GRP_411'], $i, $group_name));
                        redirect(FUSION_REQUEST);
                    }
                }
            }
        }

    }

    static function addUserGroup($group_id, $groups) {
        return preg_replace(["(^\.$group_id$)", "(\.$group_id\.)", "(\.$group_id$)"], ["", ".", ""], $groups);
    }

    static function countUserGroup($id) {
        if (isnum($id)) {
            return dbcount("(user_id)", DB_USERS, "user_groups REGEXP('^\\\.$id$|\\\.$id\\\.|\\\.$id$')");
        }

        return FALSE;
    }

    static function verifyGroup($id) {
        if (isnum($id)) {
            if (!dbcount("(user_id)", DB_USERS, "user_groups REGEXP('^\\\.$id$|\\\.$id\\\.|\\\.$id$')")
                && dbcount("(group_id)", DB_USER_GROUPS, "group_id='".intval($id)."'")
            ) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function displayAdmin() {
        $edit = (check_get('action') && get('action') == 'edit') && check_get('group_id');

        $tabs['title'][] = self::$locale['GRP_420'];
        $tabs['id'][] = "usergroup";
        $tabs['icon'][] = "";

        $tabs['title'][] = $edit ? self::$locale['GRP_421'] : self::$locale['GRP_428'];
        $tabs['id'][] = "usergroup_form";
        $tabs['icon'][] = "";

        if (check_get('group_id')) {
            $tabs['title'][] = self::$locale['GRP_423'];
            $tabs['id'][] = "user_form";
            $tabs['icon'][] = "";
        }

        $view = '';

        $allowed_sections = ["usergroup", "usergroup_form", "user_form"];
        $sections = in_array(get('section'), $allowed_sections) ? get('section') : 'usergroup';

        switch ($sections) {
            case "usergroup_form":
                add_breadcrumb(['link' => FUSION_REQUEST, "title" => $tabs['title'][1]]);
                $view = $this->groupForm();
                break;
            case "user_form":
                if (!empty(get('group_id'))) {
                    add_breadcrumb(['link' => FUSION_REQUEST, "title" => $tabs['title'][2]]);
                    $view = $this->userForm();
                } else {
                    redirect(clean_request('section=usergroup', ['section'], FALSE));
                }
                break;
            default:
                $view = $this->groupListing();
                break;
        }

        opentable(self::$locale['GRP_420']);
        echo opentab($tabs, $sections, "usergroup", TRUE, FALSE, 'section', ['action']).$view.closetab();
        closetable();
    }

    /*
     * Displays Group Listing
     */
    public function groupListing() {
        $aidlink = fusion_get_aidlink();

        $html = "<div class='clearfix'>\n";
        $html .= "<div class='pull-right'><a class='btn btn-success' href='".FUSION_SELF.$aidlink."&section=usergroup_form'><i class='fa fa-plus fa-fw'></i> ".self::$locale['GRP_428']."</a>\n</div>\n";
        $html .= "</div>\n";
        $html .= "<div class='table-responsive'><table class='table table-striped'>\n";
        $html .= "<thead>\n";
        $html .= "<tr>\n";

        $html .= "<th>".self::$locale['GRP_432']."</th>\n";
        $html .= "<th>".self::$locale['GRP_433']."</th>\n";
        $html .= "<th class='min'>".self::$locale['GRP_436']."</th>\n";
        $html .= "<th>".self::$locale['GRP_437']."</th>\n";
        $html .= "<th>".self::$locale['GRP_435']."</th>\n";
        $html .= "<tr>\n";
        $html .= "</thead>\n<tbody>\n";
        if (!empty(self::$groups)) {
            foreach (self::$groups as $groups) {
                $edit_link = FUSION_SELF.$aidlink."&section=usergroup_form&action=edit&group_id=".$groups[0];
                $member_link = FUSION_SELF.$aidlink."&section=user_form&action=user_edit&group_id=".$groups[0];
                $html .= "<tr>\n";
                $html .= "<td><a href='$edit_link'>".$groups[1]." (".self::countUserGroup($groups[0]).")</a></td>\n";
                $html .= "<td>".$groups[2]."</td>\n";
                $html .= "<td class='text-center'>".(!empty($groups[3]) ? "<i class='".$groups[3]."'></i> " : $groups[3])."</td>\n";
                $html .= "<td>";
                $html .= "<a href='$member_link'>".self::$locale['GRP_438']."</a> - ";
                $html .= "<a href='$edit_link'>".self::$locale['edit']."</a> - ";
                $html .= "<a href='".FUSION_SELF.$aidlink."&section=usergroup&action=delete&group_id=".$groups[0]."' onclick=\"return confirm('".self::$locale['GRP_425']."');\">".self::$locale['delete']."</a>\n";
                $html .= "</td>\n";
                $html .= "<td>".$groups[0]."</td>\n";
                $html .= "</tr>\n";
            }
        } else {
            $html .= "<tr>\n<td colspan='5 text-center'>".self::$locale['GRP_404']."</td>\n</tr>\n";
        }
        $html .= "</tbody>\n<tfoot>\n";
        $html .= "<tr><td colspan='5'><strong>".self::$locale['GRP_426']."</strong></td></tr>\n";
        foreach (self::$default_groups as $groups) {
            $html .= "<tr>\n";

            $html .= "<td>".$groups[1]."</td>\n";
            $html .= "<td>".$groups[2]."</td>\n";
            $html .= "<td class='text-center'>".(!empty($groups[3]) ? "<i class='".$groups[3]."'></i>" : $groups[3])."</td>\n";
            $html .= "<td>&nbsp;</td>\n";
            $html .= "<td>".$groups[0]."</td>\n";
            $html .= "</tr>\n";
        }
        $html .= "</tfoot>\n";
        $html .= "</table>\n</div>";

        return $html;
    }

    /*
     * Group Add/Edit Form
     */
    public function groupForm() {
        $html = openform('editform', 'post', FUSION_SELF.fusion_get_aidlink()."&section=usergroup_form");
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
        $total_rows = $this->countUserGroup(get('group_id'));
        $rowstart = check_get('rowstart') && get('rowstart', FILTER_SANITIZE_NUMBER_INT) <= $total_rows ? get('rowstart') : 0;

        $group = get('group_id', FILTER_SANITIZE_NUMBER_INT);
        $result = dbquery("SELECT user_id, user_name, user_level, user_avatar, user_status
            FROM ".DB_USERS."
            WHERE user_groups REGEXP('^\\\.$group$|\\\.$group\\\.|\\\.$group$')
            ORDER BY user_level DESC, user_name
            LIMIT ".intval($rowstart).", ".self::$limit
        );

        $rows = dbrows($result);

        $html = "<h4>".self::$locale['GRP_452'].getgroupname(get('group_id'))."</h4>\n";
        $html .= "<hr/>\n";
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-4'>\n";
        $html .= openform('searchuserform', 'post', FUSION_SELF.fusion_get_aidlink()."&section=user_form&action=user_edit&group_id=".get('group_id'), [
            'class' => 'list-group-item p-10 m-t-0 m-b-20'
        ]);
        $html .= form_user_select("user_send", self::$locale['GRP_440'], '', [
            'max_select'  => 10,
            'inline'      => FALSE,
            'inner_width' => '100%',
            'width'       => '100%',
            'required'    => TRUE,
            'allow_self'  => TRUE,
            'placeholder' => self::$locale['GRP_451'],
            'ext_tip'     => self::$locale['GRP_441']."<br />".self::$locale['GRP_442']
        ]);
        $html .= form_button('search_users', self::$locale['confirm'], self::$locale['confirm'], ['class' => 'btn-primary']);
        $html .= closeform();
        if (!empty(self::$group_users)) {
            $html .= openform('add_users_form', 'post', FUSION_SELF.fusion_get_aidlink()."&section=user_form&action=user_edit&group_id=".get('group_id'));
            $html .= "<div class='table-responsive'><table class='table table-striped table-hover'>\n";
            $html .= "<thead>\n";
            $html .= "<tr>\n";
            echo "<th>".self::$locale['GRP_446']."</th>\n";
            $html .= "<th></th>\n";
            $html .= "<th>".self::$locale['GRP_447']."</th>\n";
            $html .= "<th>".self::$locale['GRP_437']."</th>\n";
            $html .= "<tr>\n";
            $html .= "</thead>\n";
            $html .= "<tbody>\n";
            foreach (self::$group_users as $groupusers) {
                $html .= "<tr>\n";
                $html .= "<td>".form_checkbox("groups_add[]", '', '', ["inline" => FALSE, 'value' => $groupusers['user_id']])."</td>\n";
                $html .= "<td>".$groupusers['user_name']."</td>\n";
                $html .= "<td>".getuserlevel($groupusers['user_level'])."</td>\n";
                $html .= "</tr>\n";
            }
            $html .= "</tbody>\n";
            $html .= "</table>\n</div>";
            $html .= "<div class='spacer-xs'>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"setChecked('add_users_form','groups_add[]',1);return false;\">".self::$locale['GRP_448']."</a>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"setChecked('add_users_form','groups_add[]',0);return false;\">".self::$locale['GRP_449']."</a>\n";
            $html .= form_button('add_sel', self::$locale['GRP_450'], self::$locale['GRP_450'], ['class' => 'btn-primary']);
            $html .= "</div>\n";
            $html .= closeform();
        }
        $html .= "</div>\n";
        $html .= "<div class='col-xs-12 col-sm-8'>\n";

        if ($rows > 0) {
            $html .= fusion_get_function('openside', self::$locale['GRP_460']);
            $html .= "<div class='clearfix spacer-xs'>\n";
            $html .= ($total_rows > $rows ? "<div class='pull-right'>\n".makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", ['rowstart'], FALSE)."&")."</div>\n" : "");
            $html .= "<div class='overflow-hide'>".sprintf(self::$locale['GRP_427'], $rows, $total_rows)."</div>\n";
            $html .= "</div>\n";
            $html .= openform('rem_users_form', 'post', FUSION_SELF.fusion_get_aidlink()."&section=user_form&action=user_edit&group_id=".get('group_id'));
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
                $html .= "<td>".form_checkbox("group[]", '', '', ["inline" => FALSE, 'value' => $data['user_id']])."</td>\n";
                $html .= "<td>".$data['user_name']."</td>\n";
                $html .= "<td>".getuserlevel($data['user_level'])."</td>\n";
                $html .= "</tr>\n";
            }
            $html .= "</tbody></table>\n";
            $html .= "<div class='spacer-xs pull-right m-t-10'>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"setChecked('rem_users_form','group[]',1);return false;\">".self::$locale['GRP_448']."</a>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"setChecked('rem_users_form','group[]',0);return false;\">".self::$locale['GRP_449']."</a>\n";
            $html .= form_button('remove_sel', self::$locale['GRP_461'], self::$locale['GRP_461'], ['class' => 'btn-danger']);
            $html .= form_button('remove_all', self::$locale['GRP_462'], self::$locale['GRP_462'], ['class' => 'btn-danger']);
            $html .= "</div>\n";
            $html .= "</div>\n";
            $html .= closeform();
            $html .= fusion_get_function('closeside', '');
        } else {
            $html .= "<div class='well text-center'>".self::$locale['GRP_463']."</div>\n";
        }

        $html .= "</div>\n";

        add_to_footer("<script type='text/javascript'>\n
        function setChecked(frmName, chkName, val) {"."\n
            dml=document.forms[frmName];"."\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {"."\n
            if (dml.elements[i].name == chkName) {"."\n"."dml.elements[i].checked = val;"."\n
        }}}
        </script>\n");

        return $html;
    }
}

UserGroups::getInstance()->displayAdmin();
require_once THEMES.'templates/footer.php';
