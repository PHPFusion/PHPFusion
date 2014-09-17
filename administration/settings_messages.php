<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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

if (!checkRights("S7") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

$count = 0;

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

opentable($locale['400']);
echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['707']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl' width='50%'><label for='pm_inbox'>".$locale['701']."</label><br /><span class='small2'>".$locale['704']."</span></td>\n";
echo "<td class='tbl' width='50%'>\n";
echo form_text('', 'pm_inbox', 'pm_inbox', $pm_inbox, array('max_length' => 4, 'width' => '100px'));
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl' width='50%'><label for='pm_sentbox'>".$locale['702']."</label><br /><span class='small2'>".$locale['704']."</span></td>\n";
echo "<td class='tbl' width='50%'>\n";
echo form_text('', 'pm_sentbox', 'pm_sentbox', $pm_sentbox, array('max_length' => 4, 'width' => '100px'));
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl' width='50%'><label for='pm_savebox'>".$locale['703']."</label><br /><span class='small2'>".$locale['704']."</span></td>\n";
echo "<td class='tbl' width='50%'>\n";
echo form_text('', 'pm_savebox', 'pm_savebox', $pm_savebox, array('max_length' => 4, 'width' => '100px'));
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['708']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl' width='50%'><label for='pm_email_notify'>".$locale['709']."</label></td>\n";
echo "<td class='tbl' width='50%'>\n";
$opts = array('0' => $locale['519'], '1' => $locale['518'],);
echo form_select('', 'pm_email_notify', 'pm_email_notify', $opts, $options['pm_email_notify']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl' width='50%'><label for='pm_save_sent'>".$locale['710']."</label></td>\n";
echo "<td class='tbl' width='50%'>\n";
echo form_select('', 'pm_save_sent', 'pm_save_sent', $opts, $options['pm_save_sent']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><span class='small2'>".$locale['711']."</span></td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo form_button($locale['750'], 'saveoptions', 'saveoptions', $locale['750'], array('class' => 'btn-primary'));
echo "</td>\n</tr>\n</tbody>\n</table>\n";
echo closeform();
closetable();

require_once THEMES."templates/footer.php";
?>
