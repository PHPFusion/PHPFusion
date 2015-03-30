<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_messages.php
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
pageAccess('S7');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
add_to_breadcrumbs(array('link'=>ADMIN."settings_messages.php".$aidlink, 'title'=>$locale['message_settings']));
$count = 0;
if (isset($_POST['saveoptions'])) {
	$error = 0;
	if (!defined('FUSION_NULL')) {
		dbquery("UPDATE ".DB_MESSAGES_OPTIONS." SET
		pm_email_notify = '".(isnum($_POST['pm_email_notify']) ? $_POST['pm_email_notify'] : 0)."',
		pm_save_sent = '".(isnum($_POST['pm_save_sent']) ? $_POST['pm_save_sent'] : 0)."',
		pm_inbox = '".(isnum($_POST['pm_inbox']) ? $_POST['pm_inbox'] : 0)."',
		pm_sentbox = '".(isnum($_POST['pm_sentbox']) ? $_POST['pm_sentbox'] : 0)."',
		pm_savebox = '".(isnum($_POST['pm_savebox']) ? $_POST['pm_savebox'] : 0)."'
		WHERE user_id='0'");
		if (!$result) {
			$error = 1;
		}
		redirect(FUSION_SELF.$aidlink."&error=".$error);
	}
}
$options = dbarray(dbquery("SELECT * FROM ".DB_MESSAGES_OPTIONS." WHERE user_id='0'"), 0);
$pm_inbox = $options['pm_inbox'];
$pm_sentbox = $options['pm_sentbox'];
$pm_savebox = $options['pm_savebox'];
opentable($locale['message_settings']);
if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	}
	if (isset($message)) {
		echo admin_message($message);
	}
}
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<div class='well'>".$locale['message_description']."</div>\n";
echo "<div class='row'>";
echo "<div class='col-xs-12 col-sm-6'>\n";
openside('');
echo "<span class='pull-right m-b-10 text-smaller'>".$locale['704']."</span>\n";
echo form_text('pm_inbox', $locale['701'], $pm_inbox, array('max_length' => 4, 'width' => '100px', 'inline'=>1));
echo form_text('pm_sentbox', $locale['702'], $pm_sentbox, array('max_length' => 4, 'width' => '100px', 'inline'=>1));
echo form_text('pm_savebox', $locale['703'], $pm_savebox, array('max_length' => 4, 'width' => '100px', 'inline'=>1));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
openside('');
$opts = array('0' => $locale['519'], '1' => $locale['518'],);
echo form_select($locale['709'], 'pm_email_notify', 'pm_email_notify', $opts, $options['pm_email_notify'], array('inline'=>1, 'width'=>'100%'));
echo form_select($locale['710'], 'pm_save_sent', 'pm_save_sent', $opts, $options['pm_save_sent'],  array('inline'=>1, 'width'=>'100%'));
closeside();
echo "</div>\n</div>\n";
echo form_button('saveoptions', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";