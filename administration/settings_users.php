<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) PHP-Fusion Inc
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: settings_users.php
    | Author: Paul Beuk (muscapaul)
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

    if (!checkrights("S9") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
        redirect("../index.php");
    }

    require_once THEMES."templates/admin_header.php";
    include LOCALE.LOCALESET."admin/settings.php";

    if (isset($_POST['savesettings'])) {
        $error = 0;
        if (!defined('FUSION_NULL')) {
            if ($_POST['enable_deactivation'] == '0') {
                $result = dbquery("UPDATE ".DB_USERS." SET user_status='0' WHERE user_status='5'");
                if (!$result) {
                    $error = 1;
                }
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['enable_deactivation']) ? $_POST['enable_deactivation'] : "0")."' WHERE settings_name='enable_deactivation'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['deactivation_period']) ? $_POST['deactivation_period'] : "365")."' WHERE settings_name='deactivation_period'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['deactivation_response']) ? $_POST['deactivation_response'] : "14")."' WHERE settings_name='deactivation_response'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['deactivation_action']) ? $_POST['deactivation_action'] : "0")."' WHERE settings_name='deactivation_action'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['hide_userprofiles']) ? $_POST['hide_userprofiles'] : "0")."' WHERE settings_name='hide_userprofiles'");
            if (!$result) {
                $error = 1;
            }
            $avatar_filesize = form_sanitizer($_POST['calc_b'], '15', 'calc_b')*form_sanitizer($_POST['calc_c'], '100000', 'calc_c');
            $result          = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$avatar_filesize' WHERE settings_name='avatar_filesize'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['avatar_width']) ? $_POST['avatar_width'] : "100")."' WHERE settings_name='avatar_width'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['avatar_height']) ? $_POST['avatar_height'] : "100")."' WHERE settings_name='avatar_height'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['avatar_ratio']) ? $_POST['avatar_ratio'] : "0")."' WHERE settings_name='avatar_ratio'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['userNameChange']) ? $_POST['userNameChange'] : "0")."' WHERE settings_name='userNameChange'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['userthemes']) ? $_POST['userthemes'] : "0")."' WHERE settings_name='userthemes'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['multiple_logins']) ? $_POST['multiple_logins'] : "0")."' WHERE settings_name='multiple_logins'");
            if (!$result) {
                $error = 1;
            }
            redirect(FUSION_SELF.$aidlink."&error=".$error);
        }
    }

    if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
        if ($_GET['error'] == 0) {
            $message = $locale['900'];
        } elseif ($_GET['error'] == 1) {
            $message = $locale['901'];
        }
        if (isset($message)) {
            echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
        }
    }

    opentable($locale['400']);
    //echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
    echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));

    echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
    echo "<td width='40%' class='tbl'>\n<label for='enable_deactivation'>".$locale['1002']."</label>\n</td>\n";
    echo "<td class='tbl' width='60%'>\n";
    $yes_no_array = array('0' => $locale['519'], '1' => $locale['518']);
    echo form_select('', 'enable_deactivation', 'enable_deactivation', $yes_no_array, $settings['enable_deactivation']);
    //<select name='enable_deactivation' class='textbox'>\n";
    //echo "<option value='0'".($settings['enable_deactivation'] == "0" ? " selected='selected'" : "").">".$locale['519']."</option>\n";
    //echo "<option value='1'".($settings['enable_deactivation'] == "1" ? " selected='selected'" : "").">".$locale['518']."</option>\n";
    //echo "</select></td>\n";
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td class='tbl'>\n<label for='deactivation_period'>".$locale['1003']."</label>\n<br /><span class='small2'>(".$locale['1004'].")</span></td>\n";
    echo "<td class='tbl'>\n";
    echo form_text('', 'deactivation_period', 'deactivation_period', $settings['deactivation_period'], array('max_length' => 3, 'width' => '100px', 'number' => 1));
    //<input type='text' name='deactivation_period' value='".$settings['deactivation_period']."' maxlength='3' class='textbox' style='width:30px;' /></td>\n";
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td class='tbl'>\n<label for='deactivation_response'>".$locale['1005']."</label>\n<br /><span class='small2'>(".$locale['1006'].")</span></td>\n";
    echo "<td class='tbl'>\n";
    echo form_text('', 'deactivation_response', 'deactivation_response', $settings['deactivation_response'], array('max_length' => 3, 'width' => '100px', 'number' => 1));
    //<input type='text' name='deactivation_response' value='".$settings['deactivation_response']."' maxlength='3' class='textbox' style='width:30px;' /></td>\n";
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td  class='tbl'>\n<label for='deactivation_action'>".$locale['1011']."</label>\n</td>\n";
    echo "<td class='tbl'>\n";
    echo form_select('', 'deactivation_action', 'deactivation_action', $yes_no_array, $settings['deactivation_action']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['1007']."</strong></td>\n";
    echo "</tr>\n<tr>\n";
    echo "<td  class='tbl'>\n<label for='hide_userprofiles'>".$locale['673']."</label>\n</td>\n";
    echo "<td  class='tbl'>\n";
    echo form_select('', 'hide_userprofiles', 'hide_userprofiles', $yes_no_array, $settings['hide_userprofiles']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td  class='tbl'>\n<label for='avatar_width'>".$locale['1008']."</label>\n<br /><span class='small2'>(".$locale['1009'].")</span></td>\n";
    echo "<td  class='tbl'>\n";

    echo form_text('', 'avatar_width', 'avatar_width', $settings['avatar_width'], array('class' => 'pull-left', 'max_length' => 3, 'number' => 1));
    echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
    echo form_text('', 'avatar_height', 'avatar_height', $settings['avatar_height'], array('class' => 'pull-left', 'max_length' => 3, 'number' => 1));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td  class='tbl'><label for='calc_b'>".$locale['1010']."</label></td>\n";
    echo "<td  class='tbl'>\n";
    function calculate_byte($download_max_b) {
        $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
        foreach ($calc_opts as $byte => $val) {
            if ($download_max_b/$byte <= 999) {
                return $byte;
            }
        }
        return 1000000;
    }
    $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
    $calc_c = calculate_byte($settings['avatar_filesize']);
    $calc_b = $settings['avatar_filesize']/$calc_c;
    echo form_text('', 'calc_b', 'calc_b', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '100px', 'max_length' => '3', 'class' => 'pull-left m-r-10'));
    echo form_select('', 'calc_c', 'calc_c', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td  class='tbl'>\n<label for='avatar_ratio'>".$locale['1001']."</label></td>\n";
    echo "<td  class='tbl'>\n";
    $ratio_opts = array('0' => $locale['955'], '1' => $locale['956']);
    echo form_select('', 'avatar_ratio', 'avatar_ratio', $ratio_opts, $settings['avatar_ratio']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td  class='tbl'>\n<label for='userNameChange'>".$locale['691']."?</label></td>\n";
    echo "<td  class='tbl'>\n";
    echo form_select('', 'userNameChange', 'userNameChange', $yes_no_array, $settings['userNameChange']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td  class='tbl'>\n<label for='userthemes'>".$locale['668']."?</label></td>\n";
    echo "<td  class='tbl'>\n";
    echo form_select('', 'userthemes', 'userthemes', $yes_no_array, $settings['userthemes']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td  class='tbl'>\n<label for='multiple_logins'>".$locale['1014']."</label>\n<br /><span class='small2'>(".$locale['1014a'].")</span></td>\n";
    echo "<td  class='tbl'>\n";
    echo form_select('', 'multiple_logins', 'multiple_logins', $yes_no_array, $settings['multiple_logins']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td align='center' colspan='2' class='tbl'><br />\n";
    echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
    echo "</td>\n</tr>\n</tbody>\n</table>\n";
    echo closeform();
    closetable();

    require_once THEMES."templates/footer.php";
?>
