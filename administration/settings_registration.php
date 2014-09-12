<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) PHP-Fusion Inc
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: settings_registration.php
    | Author: Nick Jones (Digitanium)
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

    if (!checkrights("S4") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
        redirect("../index.php");
    }

    require_once THEMES."templates/admin_header_mce.php";
    include LOCALE.LOCALESET."admin/settings.php";

    if ($settings['tinymce_enabled']) {
        echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
    } else {
        require_once INCLUDES."html_buttons_include.php";
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

    $settings2 = array();
    $result = dbquery("SELECT * FROM ".DB_SETTINGS);
    while ($data = dbarray($result)) {
        $settings2[$data['settings_name']] = $data['settings_value'];
    }

    if (isset($_POST['savesettings'])) {
        $error = 0;
        if (addslash($_POST['license_agreement']) != $settings2['license_agreement']) {
            $license_lastupdate = time();
        } else {
            $license_lastupdate = $settings2['license_lastupdate'];
        }
        $license_agreement = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['license_agreement']));
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['enable_registration']) ? $_POST['enable_registration'] : "1")."' WHERE settings_name='enable_registration'");
        if (!$result) {
            $error = 1;
        }
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['email_verification']) ? $_POST['email_verification'] : "1")."' WHERE settings_name='email_verification'");
        if (!$result) {
            $error = 1;
        }
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['admin_activation']) ? $_POST['admin_activation'] : "0")."' WHERE settings_name='admin_activation'");
        if (!$result) {
            $error = 1;
        }
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['display_validation']) ? $_POST['display_validation'] : "1")."' WHERE settings_name='display_validation'");
        if (!$result) {
            $error = 1;
        }
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['enable_terms']) ? $_POST['enable_terms'] : "0")."' WHERE settings_name='enable_terms'");
        if (!$result) {
            $error = 1;
        }
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$license_agreement' WHERE settings_name='license_agreement'");
        if (!$result) {
            $error = 1;
        }
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$license_lastupdate' WHERE settings_name='license_lastupdate'");
        if (!$result) {
            $error = 1;
        }
        redirect(FUSION_SELF.$aidlink."&error=".$error);
    }

    opentable($locale['400']);
    echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
    echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='enable_registration'>".$locale['551']."</label>\n</td>\n";
    echo "<td width='50%' class='tbl'>\n";
    $opts = array('1' => $locale['518'], '0' => $locale['519']);
    echo form_select('', 'enable_registration', 'enable_registration', $opts, $settings2['enable_registration']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='email_verification'>".$locale['552']."</label></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'email_verification', 'email_verification', $opts, $settings2['email_verification']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='admin_activation'>".$locale['557']."</td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'admin_activation', 'admin_activation', $opts, $settings2['admin_activation']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='display_validation'>".$locale['553']."</td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'display_validation', 'display_validation', $opts, $settings2['display_validation']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='enable_terms'>".$locale['558']."</td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'enable_terms', 'enable_terms', $opts, $settings2['enable_terms']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td class='tbl' colspan='2'><label for='email_license_agreement'>".$locale['559']."</td>\n";
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td class='tbl' colspan='2'>\n";
    echo form_textarea('', 'license_agreement', 'enable_license_agreement', $settings2['license_agreement']);
    echo "</td>\n</tr>\n";
    if (!$settings['tinymce_enabled']) {
        echo "<tr>\n<td class='tbl' colspan='2'>\n";
        echo display_html("settingsform", "license_agreement", TRUE, TRUE, TRUE);
        echo "</td>\n</tr>\n";
    }
    echo "<tr>\n";
    echo "<td align='center' colspan='2' class='tbl'><br />\n";
    echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
    echo "</td>\n</tr>\n</tbody>\n</table>\n</form>\n";
    closetable();

    require_once THEMES."templates/footer.php";
?>
