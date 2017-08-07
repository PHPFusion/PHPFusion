<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: administrators.php
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
pageAccess('AD');
require_once THEMES."templates/admin_header.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/admins.php");

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link'=> ADMIN.'administrators.php'.fusion_get_aidlink(), "title"=> $locale['420']]);

$message = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'sn':
            $message = $locale['400'];
            $status = 'success';
            $icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
            break;
        case 'su':
            $message = $locale['401'];
            $status = 'info';
            $icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
            break;
        case 'del':
            $message = $locale['402'];
            $status = 'danger';
            $icon = "<i class='fa fa-trash fa-lg fa-fw'></i>";
            break;
        case 'pw':
            $message = $locale['global_182'];
            $status = 'success';
            $icon = "<i class='fa fa-trash fa-lg fa-fw'></i>";
            break;
    }
    if ($message) {
        addNotice($status, $icon.$message);
    }
}

if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.$aidlink);
}

if (isset($_POST['add_admin']) && (isset($_POST['user_id']) && isnum($_POST['user_id']))) {
    if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
        if (isset($_POST['all_rights']) || isset($_POST['make_super'])) {
            $admin_rights = "";
            $result = dbquery("SELECT DISTINCT admin_rights AS admin_right FROM ".DB_ADMIN." ORDER BY admin_right");
            while ($data = dbarray($result)) {
                $admin_rights .= (isset($admin_rights) ? "." : "").$data['admin_right'];
            }
            $result = dbquery("UPDATE ".DB_USERS." SET user_level='".(isset($_POST['make_super']) ? "-103" : "-102")."', user_rights='$admin_rights' WHERE user_id='".$_POST['user_id']."'");
        } else {
            $result = dbquery("UPDATE ".DB_USERS." SET user_level='-102' WHERE user_id='".$_POST['user_id']."'");
        }
        set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
        redirect(FUSION_SELF.$aidlink."&status=sn", TRUE);
    } else {
        redirect(FUSION_SELF.$aidlink."&status=pw");
    }
}

if (isset($_GET['remove']) && isnum($_GET['remove']) && $_GET['remove'] != 1) {
    if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
        $result = dbquery("UPDATE ".DB_USERS." SET user_admin_password='', user_admin_salt='', user_level='-101', user_rights='' WHERE user_id='".$_GET['remove']."' AND user_level<='-102'");
        set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
        redirect(FUSION_SELF.$aidlink."&status=del", TRUE);
    } else {
        if (isset($_POST['confirm'])) {
            echo "<div id='close-message'><div class='admin-message'>".$locale['global_182']."</div></div>\n";
        }
        opentable($locale['470']);
        echo openform('remove', 'post', FUSION_SELF.$aidlink."&amp;remove=".$_GET['remove']);
        echo form_text('admin_password', $locale['471'], '', array('type' => 'password', 'class' => 'pull-left'));
        echo form_button('confirm', $locale['472'], $locale['472'], array('class' => 'btn-primary m-r-10'));
        echo form_button('cancel', $locale['473'], $locale['473'], array('class' => 'btn-primary m-r-10'));
        closetable();
    }
}

if (isset($_POST['update_admin']) && (isset($_GET['user_id']) && isnum($_GET['user_id']) && $_GET['user_id'] != 1)) {
    if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
        if (isset($_POST['rights'])) {
            $user_rights = "";
            foreach ($_POST['rights'] as $right) {
                $user_rights .= ($user_rights != "" ? "." : "").stripinput($right);
            }
            $result = dbquery("UPDATE ".DB_USERS." SET user_rights='$user_rights' WHERE user_id='".$_GET['user_id']."' AND user_level<='-102'");
        } else {
            $result = dbquery("UPDATE ".DB_USERS." SET user_rights='' WHERE user_id='".$_GET['user_id']."' AND user_level<='-102'");
        }
        set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
        redirect(FUSION_SELF.$aidlink."&status=su", TRUE);
    } else {
        redirect(FUSION_SELF.$aidlink."&status=pw");
    }
}

if (isset($_GET['edit']) && isnum($_GET['edit']) && $_GET['edit'] != 1) {
    $result = dbquery("SELECT user_name, user_rights FROM ".DB_USERS." WHERE user_id='".$_GET['edit']."' AND user_level<='-102' ORDER BY user_id");
    if (dbrows($result)) {
        $data = dbarray($result);
        $user_rights = explode(".", $data['user_rights']);
        $result2 = dbquery("SELECT admin_rights, admin_title, admin_page FROM ".DB_ADMIN." ORDER BY admin_page ASC,admin_title");
        opentable($locale['440']." [".$data['user_name']."]");
        $columns = 2;
        $counter = 0;
        $page = 1;
        $admin_page = array($locale['441'], $locale['442'], $locale['443'], $locale['449'], $locale['444']);
        $risky_rights = array("CP", "AD", "SB", "DB", "IP", "P", "S11", "S3", "ERRO");
        echo openform('rightsform', 'post', FUSION_SELF.$aidlink."&amp;user_id=".$_GET['edit']);
        echo "<div class='alert alert-warning'><strong>".$locale['462']."</strong></div>\n";
        echo "<table cellpadding='0' cellspacing='1' class='tbl-border center table table-responsive'>\n";
        echo "<thead><tr>\n<th colspan='2' class='tbl2'><strong>".$admin_page['0']."</strong></th>\n</tr>\n</thead>\n<tbody>\n<tr>\n";
        while ($data2 = dbarray($result2)) {
            if ($page != $data2['admin_page']) {
                echo($counter % $columns == 0 ? "</tr>\n" : "<td width='50%' class='tbl1'></td>\n</tr>\n");
                echo "<tr>\n<td colspan='2' class='tbl2'><strong>".$admin_page[$page]."</strong></td>\n</tr>\n<tr>\n";
                $page++;
                $counter = 0;
            }
            if ($counter != 0 && ($counter % $columns == 0)) {
                echo "</tr>\n<tr>\n";
            }
            echo "<td width='50%' class='tbl1'><label title='".$data2['admin_rights']."'>";
                echo "<input type='checkbox' name='rights[]' value='".$data2['admin_rights']."'".(in_array($data2['admin_rights'], $risky_rights) ? " class='insecure'" : "").(in_array($data2['admin_rights'], $user_rights) ? " checked='checked'" : "")." /> ";
                echo $data2['admin_title']."</label>".(in_array($data2['admin_rights'], $risky_rights) ? "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>" : "")."</td>\n";
            $counter++;
        }
        echo "</tr>\n";
        echo "</tbody>\n</table>\n";
        echo "<div style='text-align:center'><br />\n";
        echo "<div class='btn-group m-b-0 m-r-5'>\n";
        echo "<input type='button' class='btn btn-default' onclick=\"setChecked('rightsform','rights[]',1);\" value='".$locale['445']."' />\n";
        echo "<input type='button' class='btn btn-default' onclick=\"setCheckedSecure('rightsform','rights[]',1);\" value='".$locale['450']."' />\n";
        echo "<input type='button' class='btn btn-default' onclick=\"setChecked('rightsform','rights[]',0);\" value='".$locale['446']."' />\n";
        echo "</div>\n";
        if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
            echo "<div class='panel panel-default'>\n<div class='panel-body'>\n";
            echo form_text('admin_password', $locale['447'], $locale['447']);
            echo "</div>\n</div>\n";
        }
        echo form_button('update_admin', $locale['448'], $locale['448'], array('class' => 'btn-primary'));
        echo "</div>\n";
        echo closeform();
        closetable();
        echo "<script type='text/javascript'>".jsminify("
            function setChecked(frmName, chkName, val) {
                dml = document.forms[frmName];
                len = dml.elements.length;
                for (i=0;i < len;i++) {
                    if (dml.elements[i].name == chkName) {
                        dml.elements[i].checked = val;
                    }
                }
            }").jsminify("
            function setCheckedSecure(frmName, chkName, val) {
                setChecked(frmName, chkName, 0);
                dml = document.forms[frmName];
                len = dml.elements.length;
                for (i=0;i < len;i++) {
                    if (dml.elements[i].name == chkName && !dml.elements[i].classList.contains('insecure')) {
                        dml.elements[i].checked = val;
                    }
                }
            }
        ")."</script>";
    }
} else {
    opentable($locale['410']);
    if (!isset($_POST['search_users']) || !isset($_POST['search_criteria'])) {
        echo openform('searchform', 'post', FUSION_SELF.$aidlink);
        echo "<div class='table-responsive'><table cellpadding='0' cellspacing='0' width='450' class='center table'>\n";
        echo "<tr>\n<td align='center' class='tbl'><strong>".$locale['411']."</strong><br /><br />\n";
        echo form_text('search_criteria', '', '', array('width' => '300px'));
        echo "</td>\n</tr>\n<tr>\n<td align='center' class='tbl'>\n";
        echo "<label><input type='radio' name='search_type' value='user_name' class='m-r-10' checked='checked' />&nbsp;".$locale['413']."</label>\n";
        echo "<label><input type='radio' name='search_type' value='user_id' class='m-r-10' />&nbsp;".$locale['412']."</label></td>\n";
        echo "</tr>\n<tr>\n<td align='center' class='tbl'>\n";
        echo form_button('search_users', $locale['414'], $locale['414']);
        echo "</td>\n</tr>\n</table>\n</div>\n";
        echo closeform();
    } elseif (isset($_POST['search_users']) && isset($_POST['search_criteria'])) {
        $mysql_search = "";
        if ($_POST['search_type'] == "user_id" && isnum($_POST['search_criteria'])) {
            $mysql_search .= "user_id='".$_POST['search_criteria']."' ";
        } elseif ($_POST['search_type'] == "user_name" && preg_match("/^[-0-9A-Z_@\s]+$/i", $_POST['search_criteria'])) {
            $mysql_search .= "user_name LIKE '".$_POST['search_criteria']."%' ";
        }
        if ($mysql_search) {
            $result = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE ".$mysql_search." AND user_level='-101' ORDER BY user_name");
        }
        if (isset($result) && dbrows($result)) {
            echo openform('add_users_form', 'post', FUSION_SELF.$aidlink);
            echo "<div class='table-responsive'><table cellpadding='0' cellspacing='1' class='tbl-border center table'>\n";
            $i = 0;
            $users = "";
            while ($data = dbarray($result)) {
                $row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
                $i++;
                $users .= "<tr>\n<td class='$row_color'><label><input type='radio' name='user_id' value='".$data['user_id']."' /> ".$data['user_name']."</label></td>\n</tr>";
            }
            if ($i > 0) {
                echo "<thead>\n<tr>\n<th class='tbl2'><strong>".$locale['413']."</strong></th>\n</tr></thead>\n<tbody>\n";
                echo $users;
                echo "</tbody></table>\n";
                echo "<div class='panel panel-default'>\n";
                echo "<div class='panel-body'>\n";
                echo "<div class='alert alert-warning'><strong>".$locale['462']."</strong></div>\n";
                echo "<label><input type='checkbox' name='all_rights' value='1' /> ".$locale['415']."</label> <span class='required m-l-5'>*</span><br />\n";
                if ($userdata['user_level'] == -103) {
                    echo "<label><input type='checkbox' name='make_super' value='1' /> ".$locale['416']."</label> <span class='required m-l-5'>*</span><br />\n";
                }
                if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
                    echo form_text('admin_password', $locale['447'], '', array('required' => 1, 'inline' => 1));
                }
                echo form_button('add_admin', $locale['461'], $locale['461'], array('class' => 'btn-primary m-t-10'));
                add_to_jquery("
                $('#add_admin').bind('click', function() { confirm('".$locale['461']."'); });
                ");
                echo "</div>\n";
                echo "</div>\n";
                echo "<br />\n";
            } else {
                echo "<tr>\n<td align='center' class='tbl'>".$locale['418']."<br /><br />\n";
                echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['419']."</a>\n</td>\n</tr>\n";
            }
            echo "</table>\n</div>";
            echo closeform();
        } else {
            echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n";
            echo "<tr>\n<td align='center' class='tbl'>".$locale['418']."<br /><br />\n";
            echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['419']."</a>\n</td>\n</tr>\n</table>\n";
        }
    }
    closetable();
    opentable($locale['420']);
    $i = 0;
    $result = dbquery("SELECT user_id, user_name, user_rights, user_level FROM ".DB_USERS." WHERE user_level<='-102' ORDER BY user_level DESC, user_name");
    echo "<div class='table-responsive'><table cellpadding='0' cellspacing='1' class='table tbl-border center'>\n<thead>\n<tr>\n";
    echo "<th class='tbl2'>".$locale['421']."</th>\n";
    echo "<th align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['422']."</th>\n";
    echo "<th align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$locale['423']."</th>\n";
    echo "</tr>\n</thead>\n<tbody>\n";
    while ($data = dbarray($result)) {
        $row_color = $i % 2 == 0 ? "tbl1" : "tbl2";
        echo "<tr>\n<td class='$row_color'><span title='".($data['user_rights'] ? str_replace(".", " ",
                                                                                              $data['user_rights']) : "".$locale['425']."")."' style='cursor:hand;'>".$data['user_name']."</span></td>\n";
        echo "<td align='center' width='1%' class='$row_color' style='white-space:nowrap'>".getuserlevel($data['user_level'])."</td>\n";
        echo "<td align='center' width='1%' class='$row_color' style='white-space:nowrap'>\n";
        if ($data['user_level'] == "-103" && $userdata['user_id'] == "1") {
            $can_edit = TRUE;
        } elseif ($data['user_level'] != "-103") {
            $can_edit = TRUE;
        } else {
            $can_edit = FALSE;
        }
        if ($can_edit == TRUE && $data['user_id'] != "1") {
            echo "<a href='".FUSION_SELF.$aidlink."&amp;edit=".$data['user_id']."'>".$locale['426']."</a> |\n";
            echo "<a href='".FUSION_SELF.$aidlink."&amp;remove=".$data['user_id']."' onclick=\"return confirm('".$locale['460']."');\">".$locale['427']."</a>\n";
        }
        echo "</td>\n</tr>\n";
        $i++;
    }
    echo "</tbody>\n</table>\n</div>";
    closetable();
}

require_once THEMES."templates/footer.php";
