<?php
require_once "../../maincore.php";
require_once THEMES."templates/admin_header.php";

if (!checkrights("STFF") || !defined("iAUTH") || $_GET['aid'] != iAUTH) {
    redirect("../index.php");
}

$locale = fusion_get_locale('', STF_LOCALE);

if (isset($_GET['rowstart']) && isnum($_GET['rowstart'])) {
    $rowstart = $_GET['rowstart'];
} else {
    $rowstart = 0;
}
$limit = "20";
$sender = "11";
$counter = (dbcount("(stf_id)", DB_STF_APPLICATIONS));
add_to_title(" | ".$locale['stf_001']);

add_to_head("<script type='text/javascript'>
function DeleteApplication() {
		return confirm('".$locale['stf_084']."');
}
</script>");

$form_title = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] == "del") {
        $title = $locale['stf_028'];
        $message = "<strong>".$locale['stf_068']."</strong>";
    } else if ($_GET['status'] == "apr") {
        $title = $locale['stf_069'];
        $message = "<strong>".$locale['stf_070']."</strong>";
    }

    echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
}
if (isset($_POST['edit_app']) && (isset($_GET['stf_id']))) {
    $get = dbarray(dbquery("SELECT * FROM ".DB_STF_APPLICATIONS." WHERE stf_id='".$_GET['stf_id']."'"));
    $stf_status = stripinput($_POST['stf_status']);
    $stf_approver_comment = stripinput($_POST['stf_approver_comment']);
    $approve = stripinput(isset($_POST['approve']) ? 1 : 0);

    if ($_POST['stf_status'] == '0' && $approve != '1') {
        $result = dbquery("UPDATE ".DB_STF_APPLICATIONS." SET stf_status = '$stf_status', stf_admin='".$userdata['user_id']."', stf_approver_comment='$stf_approver_comment' WHERE stf_id='".$_GET['stf_id']."'");
    }
    if ($_POST['stf_status'] == '1' && $approve == '1') {
        $result = dbquery("UPDATE ".DB_STF_APPLICATIONS." SET stf_status = '$stf_status', stf_admin='".$userdata['user_id']."', stf_approver_comment='$stf_approver_comment' WHERE stf_id='".$_GET['stf_id']."'");
        require_once INCLUDES."infusions_include.php";

        $users_ug = dbarray(dbquery("SELECT user_groups FROM ".DB_USERS." WHERE user_id = '".$get['stf_user_id']."'"));
        $result = dbquery("UPDATE ".DB_USERS." SET user_groups='".$users_ug['user_groups'].".".$get['stf_type']."' WHERE user_id='".$get['stf_user_id']."'");

        $success = ($locale['stf_102'].$get['stf_real_name']."<br /><br />".$locale['stf_103']);
        send_pm($get['stf_user_id'], $sender, $locale['stf_079'], $success);
    }

    if ($_POST['stf_status'] == '2' && $approve == '1') {
        $result = dbquery("UPDATE ".DB_STF_APPLICATIONS." SET stf_status = '$stf_status', stf_admin='".$userdata['user_id']."', stf_approver_comment='$stf_approver_comment' WHERE stf_id='".$_GET['stf_id']."'");
        require_once INCLUDES."infusions_include.php";

        $unsuccess = ($locale['stf_102'].$get['stf_real_name']."<br /><br />".$locale['stf_104']);
        send_pm($get['stf_user_id'], $sender, $locale['stf_079'], $unsuccess);
    }
    redirect(FUSION_SELF.$aidlink."&status=apr");

} else if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['stf_id']) && isnum($_GET['stf_id']))) {
    $result = dbquery("DELETE FROM ".DB_STF_APPLICATIONS." WHERE stf_id='".$_GET['stf_id']."'");
    redirect(FUSION_SELF.$aidlink."&status=del");
} else {
    if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['stf_id']) && isnum($_GET['stf_id']))) {
        $result = dbquery("SELECT * FROM ".DB_STF_APPLICATIONS." WHERE stf_id='".$_GET['stf_id']."'");
        if (dbrows($result)) {
            $data = dbarray($result);
            $stf_id = $data['stf_id'];
            $stf_user_id = $data['stf_user_id'];
            $stf_real_name = $data['stf_real_name'];
            $stf_main_name = $data['stf_main_name'];
            $stf_main_name_id = $data['stf_main_name_id'];
            $stf_email = $data['stf_email'];
            $stf_type = $data['stf_type'];
            $stf_ip = $data['stf_ip'];
            $stf_status = $data['stf_status'];
            $stf_admin = $data['stf_admin'];
            $stf_text = $data['stf_text'];
            $stf_datestamp = $data['stf_datestamp'];
            $stf_approver_comment = $data['stf_approver_comment'];
            $form_title = $locale['stf_037'];
            $form_action = FUSION_SELF.$aidlink."&amp;action=edit&amp;stf_id=".$data['stf_id'];
        } else {
            redirect(FUSION_SELF.$aidlink);
        }
    } else {
        $stf_id = "";
        $stf_user_id = "";
        $stf_real_name = "";
        $stf_main_name = "";
        $stf_main_name_id = "";
        $stf_email = "";
        $stf_type = "";
        $stf_ip = "";
        $stf_status = "";
        $stf_admin = "";
        $stf_text = "";
        $stf_datestamp = "";
        $stf_approver_comment = "";
        $form_title = $locale['stf_033'];
        $form_action = (FUSION_SELF.$aidlink);
    }

    if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['stf_id']) && isnum($_GET['stf_id']))) {
        opentable($form_title);

        echo "<form name='appform' method='post' action='".$form_action."' >\n";
        echo "<br />\n<table cellpadding='0' align='center' cellspacing='0' width='100%' class='tbl-border'>\n<tr>\n";
        echo "<th colspan='2' class='forum-caption'>".$locale['stf_001']."</th>\n";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' align='right' width='20%'>".$locale['stf_003'].":</td>";
        echo "<td class='tbl1'>".$data['stf_real_name']."</td>";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' align='right' width='20%'>".$locale['stf_004'].":</td>";
        echo "<td class='tbl1'><a target='_blank' href='http://www.php-fusion.co.uk/profile.php?lookup=".$data['stf_main_name_id']."'>".$data['stf_main_name']."</a></td>";
        echo "</tr>\n<tr>\n";
        $get_user = dbarray(dbquery("SELECT user_name, user_status FROM ".DB_USERS." WHERE user_id = '".$stf_user_id."'"));
        echo "<td class='tbl1' align='right' width='20%'>".$locale['stf_042'].":</td>";
        echo "<td class='tbl1'>".profile_link($stf_user_id, $get_user['user_name'], $get_user['user_status'])."</td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' align='right' width='20%'>".$locale['stf_005'].":</td>";
        echo "<td class='tbl1'><a href='mailto:".$data['stf_email']."'>".$data['stf_email']."</a></td>";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' align='right' width='20%' valign='top'>".$locale['stf_059'].":</td>";
        echo "<td class='tbl1'>\n";

        $get_ugroups = dbarray(dbquery("SELECT user_groups FROM ".DB_USERS." WHERE user_id = '".$stf_user_id."'"));
        $resulta = dbquery("SELECT 
                                group_id, 
                                group_name 
                                FROM ".DB_USER_GROUPS." 
                                WHERE group_id ='".$stf_type."'
                                ");

        if (dbrows($resulta)) {

            while ($datab = dbarray($resulta)) {
                if (!in_array($datab['group_id'], explode(".", $get_ugroups['user_groups']))) {
                    echo "<a target='_blank' href='".BASEDIR."profile.php?group_id=".$stf_type."'>".$datab['group_name']."</a></small><br />\n";
                } else {
                    echo $locale['stf_057'].$datab['group_name']."<br />\n";
                }
            }
        } else {
            echo "<div align='center'><br />".$locale['stf_063']."</div>\n";
        }
        echo "</td>";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' align='right' width='20%'>".$locale['stf_038'].":</td>";
        echo "<td class='tbl1'>".$data['stf_ip']."</td>";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' align='right' valign='top' width='20%'>".$locale['stf_006'].":</td>";
        echo "<td class='tbl1'>";
        $text = nl2br(parseubb(censorwords($data['stf_text'])));
        echo(isset($text) ? $text : "");
        echo "</td>";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' width='20%'>".$locale['stf_067'].":</td>";
        echo "<td class='tbl1' nowrap valign='top'>";
        if ($stf_status != '1') {
            echo "<select name='stf_status' class='textbox'>\n";
            echo "<option value='0' ".($stf_status == 0 ? "selected" : "").">".$locale['stf_064']."</option>\n";
            echo "<option value='1' ".($stf_status == 1 ? "selected" : "").">".$locale['stf_065']."</option>\n";
            echo "<option value='2' ".($stf_status == 2 ? "selected" : "").">".$locale['stf_066']."</option>\n";
            echo "</select>\n";
        } else {
            $get_admin = dbarray(dbquery("SELECT user_name, user_status FROM ".DB_USERS." WHERE user_id = '".$data['stf_admin']."'"));
            echo $locale['stf_085'].profile_link($data['stf_admin'], $get_admin['user_name'], $get_admin['user_status']);
        }
        echo "</td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' colspan='2'><hr /></td>";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' valign='top' width='20%'>".$locale['stf_039'].":</td>";
        echo "<td class='tbl1' valign='top'>";
        if ($stf_status != '1') {
            echo "<textarea class='textbox' name='stf_approver_comment' style='width:400px; height:50px;'>".$data['stf_approver_comment']."</textarea><br />".$locale['stf_040'];
        } else {
            echo $data['stf_approver_comment'];
        }
        echo "</td>";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' colspan='2' align='center'>";
        if ($stf_status != '1') {
            echo "<input type='submit' name='edit_app' value='".$locale['stf_056']."' class='button' />\n";
            echo "<input type='checkbox' name='approve' value='1' class='textbox' /><br />".$locale['stf_078'];
        } else {
            echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['stf_086']."</a>";
        }

        echo "</td>\n</tr>\n</table>\n</form>\n<br />\n";
        closetable();
        echo "<br />\n";
    }


    // Pending Applications
    opentable($locale['stf_033']);
    $rows = dbcount("(stf_id)", DB_STF_APPLICATIONS);
    if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
        $_GET['rowstart'] = 0;
    }
    $result = dbquery(
        "SELECT stf_id, stf_user_id, stf_real_name, stf_main_name, stf_main_name_id, stf_email, stf_type, stf_ip, stf_status, stf_admin, stf_text, stf_datestamp 
			FROM ".DB_STF_APPLICATIONS." 
			WHERE stf_status = '0'
			ORDER BY stf_datestamp DESC
			LIMIT ".$_GET['rowstart'].",20"
    );
    if (dbrows($result)) {
        $i = 0;
        echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border center'>\n<tr>\n";
        echo "<td class='tbl1'>".$locale['stf_042']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_006']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_005']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_059']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_018']."</td>\n";
        echo "<td align='center' width='1%' class='tbl1' style='white-space:nowrap'>".$locale['stf_027']."</td>\n";
        echo "</tr>\n";
        while ($data = dbarray($result)) {
            $get_group = dbarray(dbquery("SELECT group_name FROM ".DB_USER_GROUPS." WHERE group_id = '".$data['stf_type']."'"));
            $row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
            echo "<tr>\n";
            $get_user = dbarray(dbquery("SELECT user_name, user_status FROM ".DB_USERS." WHERE user_id = '".$data['stf_user_id']."'"));
            echo "<td class='$row_color'>".profile_link($stf_user_id, $get_user['user_name'], $get_user['user_status'])."</td>\n";
            echo "<td class='$row_color'>".trimlink($data['stf_text'], 80)."</td>\n";
            echo "<td class='$row_color'><a href='mailto:".$data['stf_email']."'>".$data['stf_email']."</a></td>\n";
            echo "<td class='$row_color' align='center'>".($data['stf_type'] == 0 ? $locale['stf_057'] : $get_group['group_name'])."</td>\n";
            echo "<td class='$row_color'>".showdate("shortdate", $data['stf_datestamp'])."</td>\n";
            echo "<td align='center' width='1%' class='$row_color' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;stf_id=".$data['stf_id']."'>".$locale['stf_058']."</a> -\n";
            echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;stf_id=".$data['stf_id']."' onclick=\"return DeleteApplication();\">".$locale['stf_028']."</a></td>\n";
            echo "</tr>\n";
            $i++;
        }
        echo "</table>\n";
    } else {
        echo "<div style='text-align:center'><br />\n".$locale['stf_032'].$locale['stf_033']."<br /><br />\n</div>\n";
    }
    closetable();
    echo "<br />\n";

    // Approved applications
    opentable($locale['stf_034']);

    $result = dbquery(
        "SELECT stf_id, stf_user_id, stf_real_name, stf_main_name, stf_main_name_id, stf_email, stf_type, stf_ip, stf_status, stf_admin, stf_text, stf_approver_comment, stf_datestamp 
			FROM ".DB_STF_APPLICATIONS." 
			WHERE stf_status = '1'
			ORDER BY stf_datestamp DESC
			LIMIT ".$_GET['rowstart'].",20"
    );
    if (dbrows($result)) {
        $i = 0;
        echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border center'>\n<tr>\n";
        echo "<td class='tbl1'>".$locale['stf_042']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_039']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_019']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_041']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_018']."</td>\n";
        echo "<td align='center' width='1%' class='tbl1' style='white-space:nowrap'>".$locale['stf_027']."</td>\n";
        echo "</tr>\n";
        while ($data = dbarray($result)) {
            $get_group = dbarray(dbquery("SELECT group_name FROM ".DB_USER_GROUPS." WHERE group_id = '".$data['stf_type']."'"));
            $row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
            echo "<tr>\n";
            $get_user = dbarray(dbquery("SELECT user_name, user_status FROM ".DB_USERS." WHERE user_id = '".$data['stf_user_id']."'"));
            echo "<td class='$row_color'>".profile_link($data['stf_user_id'], $get_user['user_name'], $get_user['user_status'])."</td>\n";
            echo "<td class='$row_color'>".trimlink($data['stf_approver_comment'], 60)."</td>\n";
            $get_admin = dbarray(dbquery("SELECT user_name, user_status FROM ".DB_USERS." WHERE user_id = '".$data['stf_admin']."'"));
            echo "<td class='$row_color'>".profile_link($data['stf_admin'], $get_admin['user_name'], $get_admin['user_status'])."</td>\n";
            echo "<td class='$row_color'>".($data['stf_type'] == 0 ? $locale['stf_057'] : $get_group['group_name'])."</td>\n";
            echo "<td class='$row_color'>".showdate("shortdate", $data['stf_datestamp'])."</td>\n";
            echo "<td align='center' width='1%' class='$row_color' style='white-space:nowrap'>\n";
            echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;stf_id=".$data['stf_id']."'>".$locale['stf_058']."</a> -\n";
            echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;stf_id=".$data['stf_id']."' onclick=\"return DeleteApplication();\">".$locale['stf_028']."</a></td>\n";
            echo "</tr>\n";
            $i++;
        }
        echo "</table>\n";
    } else {
        echo "<div style='text-align:center'><br />\n".$locale['stf_032'].$locale['stf_034']."<br /><br />\n</div>\n";
    }
    closetable();


    // Denied applications
    echo "<br />\n";
    opentable($locale['stf_035']);

    $result = dbquery(
        "SELECT stf_id, stf_user_id, stf_real_name, stf_main_name, stf_main_name_id, stf_status, stf_admin, stf_text, stf_datestamp, stf_approver_comment 
			FROM ".DB_STF_APPLICATIONS." 
			WHERE stf_status = '2'
			ORDER BY stf_datestamp DESC
			LIMIT ".$_GET['rowstart'].",20"
    );
    if (dbrows($result)) {
        $i = 0;
        echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border center'>\n<tr>\n";
        echo "<td class='tbl1'>".$locale['stf_042']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_003']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_039']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_013']."</td>\n";
        echo "<td class='tbl1'>".$locale['stf_018']."</td>\n";
        echo "<td align='center' width='1%' class='tbl1' style='white-space:nowrap'>".$locale['stf_027']."</td>\n";
        echo "</tr>\n";
        while ($data = dbarray($result)) {
            $row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
            echo "<tr>\n";
            $get_user = dbarray(dbquery("SELECT user_name, user_status FROM ".DB_USERS." WHERE user_id = '".$data['stf_user_id']."'"));
            echo "<td class='$row_color'>".profile_link($data['stf_user_id'], $get_user['user_name'], $get_user['user_status'])."</td>\n";
            echo "<td class='$row_color'>".$data['stf_real_name']."</td>\n";
            echo "<td class='$row_color'>".trimlink($data['stf_approver_comment'], 60)."</td>\n";
            $get_admin = dbarray(dbquery("SELECT user_name, user_status FROM ".DB_USERS." WHERE user_id = '".$data['stf_admin']."'"));
            echo "<td class='$row_color'>".profile_link($data['stf_admin'], $get_admin['user_name'], $get_admin['user_status'])."</td>\n";
            echo "<td class='$row_color'>".showdate("shortdate", $data['stf_datestamp'])."</td>\n";
            echo "<td align='center' width='1%' class='$row_color' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;stf_id=".$data['stf_id']."'>".$locale['stf_058']."</a> -\n";
            echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;stf_id=".$data['stf_id']."'>".$locale['stf_028']."</a></td>\n";
            echo "</tr>\n";
            $i++;
        }
        echo "</table>\n";
    } else {
        echo "<div style='text-align:center'><br />\n".$locale['stf_032'].$locale['stf_035']."<br /><br />\n</div>\n";
    }
    closetable();
    if (($rows) > 20) {
        echo "<div align='center' style=';margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $rows, 3, FUSION_SELF.$aidlink."&amp;")."\n</div>\n";
    }
}

require_once THEMES."templates/footer.php";
