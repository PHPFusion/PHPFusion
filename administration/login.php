<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login.php
| Author: Dan (JoiNNN)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
if (!defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
$login_error = "";
$admin_password = "";
if ($userdata['user_admin_password']) {
	if (isset($_POST['admin_password'])) {
		$login_error = $locale['global_182'];
		$admin_password = form_sanitizer($_POST['admin_password'], '', 'admin_password');
		if (!defined("FUSION_NULL")) {
			set_admin_pass($admin_password);
		}
	}
} else {
	$defender->stop();
	$defender->addNotice($locale['global_199']);
}
// will not login in infusions admin pages.
$form_action = FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_SELF."?".FUSION_QUERY;
if (!check_admin_pass($admin_password) && !stristr($_SERVER['PHP_SELF'], $settings['site_path']."infusions")) {
	echo openform('admin-login-form', 'admin-login-form', 'post', $form_action, array('downtime' => 0));
	openside('');
	echo "<div class='m-t-10 clearfix row'>\n";
	echo "<div class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
	echo "<div class='pull-right'>\n";
	echo display_avatar($userdata, '90px');
	echo "</div>\n";
	echo "</div>\n<div class='col-xs-9 col-sm-9 col-md-8 col-lg-7'>\n";
	echo "<h5><strong>".$locale['welcome'].", ".(ucwords($userdata['user_name']))."</strong><br/>".getuserlevel($userdata['user_level'])."</h5>";
	echo "<div class='clearfix'>\n";
	echo form_text('', 'admin_password', 'admin_password', '', array('placeholder' => $locale['281'], 'autocomplete_off' => 1, 'password' => 1, 'required' => 1));
	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeside();
	echo form_button($locale['login'], 'admin_login', 'admin_login', 'Sign in', array('class' => 'btn-primary btn-block'));
	echo closeform();
} else {
	redirect($form_action);
}
unset($login_error, $admin_password);
?>