<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_reset.php
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
pageAccess('APWR');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/admin_reset.php";
add_breadcrumb(array('link' => ADMIN.'admin_reset.php'.$aidlink, 'title' => $locale['apw_title']));

if (isset($_GET['status']) && !isset($message)) {
    if ($_GET['status'] == "pw") {
        $message = $locale['411'];
        $status = 'info';
        $icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
        addNotice($status, $icon.$message);

    }
}
if (isset($_POST['reset_admins']) && isset($_POST['reset_message']) && isset($_POST['reset_admin'])) {
    if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
        require_once INCLUDES."sendmail_include.php";

        $reset_message = stripinput($_POST['reset_message']);
        $reset_admin = stripinput($_POST['reset_admin']);
        $reset_success = array();
        $reset_failed = array();

        if (isnum($reset_admin)) {
            $user_sql = "user_id='".$reset_admin."'";
        } elseif ($reset_admin == "all") {
            $user_sql = "user_level=".USER_LEVEL_ADMIN." OR user_level=".USER_LEVEL_SUPER_ADMIN;
        } elseif ($reset_admin == "sa") {
            $user_sql = "user_level=".USER_LEVEL_SUPER_ADMIN;
        } elseif ($reset_admin == "a") {
            $user_sql = "user_level=".USER_LEVEL_ADMIN;
        } else {
            redirect(FUSION_SELF.$aidlink."&error=1");
        }

        $result = dbquery("SELECT user_id, user_name, user_email FROM ".DB_USERS." WHERE ".$user_sql." ORDER BY user_level DESC, user_id");
        while ($data = dbarray($result)) {
            $loginPassIsReset = FALSE;
            $adminPassIsReset = FALSE;
            $adminPass = new PasswordAuth();
            $newLoginPass = "";
            $newAdminPass = $adminPass->getNewPassword(12);
            $adminPass->inputNewPassword = $newAdminPass;
            $adminPass->inputNewPassword2 = $newAdminPass;
            $adminPassIsReset = ($adminPass->isValidNewPassword() === 0 ? TRUE : FALSE);
            if (isset($_POST['reset_login']) && $_POST['reset_login'] == 1) {
                $loginPass = new PasswordAuth();
                $newLoginPass = $loginPass->getNewPassword(12);
                $loginPass->inputNewPassword = $newLoginPass;
                $loginPass->inputNewPassword2 = $newLoginPass;
                $message = str_replace(
                    array(
                        "[SITEURL]",
                        "[USER_NAME]",
                        "[NEW_PASS]",
                        "[NEW_ADMIN_PASS]",
                        "[ADMIN]",
                        "[RESET_MESSAGE]"
                    ),
                    array(
                        "<a href='".fusion_get_settings("siteurl")."'>".fusion_get_settings("sitename")."</a>",
                        $data['user_name'],
                        $newLoginPass,
                        $newAdminPass,
                        $userdata['user_name'],
                        $reset_message
                    ), $locale['409']);
                $loginPassIsReset = ($loginPass->isValidNewPassword() === 0 ? TRUE : FALSE);
            } else {
                $message = str_replace(
                    array(
                        "[SITEURL]",
                        "[USER_NAME]",
                        "[NEW_ADMIN_PASS]",
                        "[ADMIN]",
                        "[RESET_MESSAGE]"
                    ),
                    array(
                        "<a href='".fusion_get_settings("siteurl")."'>".fusion_get_settings("sitename")."</a>",
                        $data['user_name'],
                        $newAdminPass,
                        $userdata['user_name'],
                        $reset_message
                    ), $locale['408']
                );
                $loginPassIsReset = TRUE;
            }
            if ($loginPassIsReset && $adminPassIsReset && sendemail($data['user_name'], $data['user_email'], $userdata['user_name'],
                                                                    $userdata['user_email'], $locale['407'].$settings['sitename'], $message)
            ) {
                $result2 = dbquery("UPDATE ".DB_USERS." SET
						".($newLoginPass ? "user_algo='".$loginPass->getNewAlgo()."', user_salt='".$loginPass->getNewSalt()."',
											user_password='".$loginPass->getNewHash()."', " : "")."
						user_admin_algo='".$adminPass->getNewAlgo()."', user_admin_salt='".$adminPass->getNewSalt()."',
						user_admin_password='".$adminPass->getNewHash()."'
					WHERE user_id='".$data['user_id']."'");
                $reset_success[] = array($data['user_id'], $data['user_name'], $data['user_email']);
            } else {
                $reset_failed[] = array($data['user_id'], $data['user_name'], $data['user_email']);
            }
        }

        opentable($locale['410']);
        $sucess = count($reset_success);
        $sucess_ids = "";
        $failed = count($reset_failed);
        $failed_ids = "";
        echo "<table cellpadding='0' cellspacing='0' class='admin-reset tbl-border table table-responsive center'>\n";
        for ($i = 0; $i < $sucess; $i++) {
            $sucess_ids .= $sucess_ids != "" ? ".".$reset_success[$i][0] : $reset_success[$i][0];
            echo "<tr>\n";
            echo "<td class='tbl1' width='250'><strong>".($i == 0 ? $locale['424'] : "")."</strong></td>\n";
            echo "<td class='tbl1'>".$reset_success[$i][1]." (".$reset_success[$i][2].")</td>\n";
            echo "</tr>\n";
        }
        for ($i = 0; $i < $failed; $i++) {
            $failed_ids .= $failed_ids != "" ? ".".$reset_failed[$i][0] : $reset_failed[$i][0];
            echo "<tr>\n";
            echo "<td class='tbl1' width='250'><strong>".($i == 0 ? $locale['425'] : "")."</strong></td>\n";
            echo "<td class='tbl1'>".$reset_failed[$i][1]."(".$reset_failed[$i][2].")</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
        closetable();
        $result = dbquery("INSERT INTO ".DB_ADMIN_RESETLOG." (
				reset_admin_id,
				reset_timestamp,
				reset_sucess,
				reset_failed,
				reset_admins,
				reset_reason
			) VALUES (
				'".$userdata['user_id']."',
				'".time()."',
				'".$sucess_ids."',
				'".$failed_ids."',
				'".$reset_admin."',
				'".$reset_message."'
			)");
    } else {
        addNotice("success", $locale['411']);
    }
    redirect(FUSION_SELF.$aidlink);
}
$reset_opts = array('all' => $locale['401'], 'sa' => $locale['402'], 'a' => $locale['403']);
$result = dbquery("SELECT user_id, user_name, user_level FROM ".DB_USERS." WHERE user_level<=".USER_LEVEL_ADMIN." ORDER BY user_level DESC, user_name");
while ($data = dbarray($result)) {
    $reset_opts[$data['user_id']] = $data['user_name'];
}
opentable($locale['apw_title']);
echo openform('admin_reset', 'POST', FUSION_SELF.$aidlink);
echo "<table class='table table-responsive admin-reset center'>\n<tr>\n";
echo "<td class='tbl1' width='250'><label for='reset_admin'>".$locale['400']."</label></td>\n";
echo "<td class='tbl1'>".form_select('reset_admin', '', '', array(
        'options' => $reset_opts,
        'placeholder' => $locale['choose'],
        'allowclear' => 1
    ));
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' width='250' valign='top'><label for='reset_message'>".$locale['404']."</label></td>\n";
echo "<td class='tbl1'>".form_textarea('reset_message', '', '');
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' width='250' valign='top'></td>\n";
echo "<td class='tbl1'><label><input type='checkbox' name='reset_login' value='1' /> ".$locale['405']."</label></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' width='250' valign='top'></td>\n";
echo "<td class='tbl1'>".form_button('reset_admins', $locale['406'], $locale['406'], array('class' => 'button btn btn-primary'))."</td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();
$titles = array("all" => $locale['401'], "sa" => $locale['402'], "a" => $locale['403']);
opentable($locale['415']);
echo "<table cellpadding='0' cellspacing='0' class='table table-responsive admin-reset tbl-border center'>\n<tr>\n";
echo "<td class='tbl2' valign='top'><strong>".$locale['417']."</strong></td>\n";
echo "<td class='tbl2' valign='top'><strong>".$locale['418']."</strong></td>\n";
echo "<td class='tbl2' valign='top'><strong>".$locale['419']."</strong></td>\n";
echo "<td class='tbl2' valign='top'><strong>".$locale['420']."</strong></td>\n";
echo "<td class='tbl2' valign='top'><strong>".$locale['421']."</strong></td>\n";
echo "</tr>\n";
$result = dbquery("SELECT arl.*, u1.user_name, u1.user_id, u2.user_name as user_name_reset, u2.user_id as user_id_reset
        FROM ".DB_ADMIN_RESETLOG." arl
	LEFT JOIN ".DB_USERS." u1 ON arl.reset_admin_id=u1.user_id
	LEFT JOIN ".DB_USERS." u2 ON arl.reset_admins=u2.user_id
	ORDER BY arl.reset_timestamp DESC");
$i = 1;
while ($data = dbarray($result)) {
    $row_color = ($i % 2 == 0 ? "tbl2" : "tbl1");
    if (isnum($data['reset_admins'])) {
        $reset_passwords = "<a href='".BASEDIR."profile.php?lookup=".$data['user_id_reset']."'>".$data['user_name_reset']."</a>";
    } else {
        $reset_passwords = $titles[$data['reset_admins']];
    }
    $sucess = $data['reset_sucess'] ? count(explode(".", $data['reset_sucess'])) : 0;
    $failed = $data['reset_failed'] ? count(explode(".", $data['reset_failed'])) : 0;
    echo "<tr>\n";
    echo "<td class='".$row_color."' valign='top'>".showdate("shortdate", $data['reset_timestamp'])."</td>\n";
    echo "<td class='".$row_color."' valign='top'><a href='".BASEDIR."profile.php?lookup=".$data['user_id']."'>".$data['user_name']."</a></td>\n";
    echo "<td class='".$row_color."' valign='top'>".$reset_passwords."</td>\n";
    echo "<td class='".$row_color."' valign='top'>".$sucess." ".$locale['422']." ".($sucess + $failed)."</td>\n";
    echo "<td class='".$row_color."' valign='top'>".($data['reset_reason'] ? $data['reset_reason'] : $locale['423'])."</td>\n";
    echo "</tr>\n";
    $i++;
}
echo "</table>\n";
closetable();
require_once THEMES."templates/footer.php";
