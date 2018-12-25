<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: email.php
| Author: MarcusG
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

if (!checkrights("MAIL") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/emails.php";

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "su") {
		$message = $locale['410']."\n";
	} elseif ($_GET['status'] == "snd") {
		$message = sprintf($locale['411'], $_GET['testmail']);
	}
	if ($message) {
		echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
	}
}

if (isset($_POST['save_template'])) {
	$template_id = $_POST['template_id'];
	$template_format = $_POST['template_format'];
	$template_subject = $_POST['template_subject'];
	$template_content = $_POST['template_content'];
	$template_active = $_POST['template_active'];
	$template_sender_name = $_POST['template_sender_name'];
	$template_sender_email = $_POST['template_sender_email'];
	$template_language = $_POST['template_language'];
	$result = dbquery("UPDATE ".DB_EMAIL_TEMPLATES." SET
		template_subject = '".$template_subject."',
		template_content = '".$template_content."',
		template_active = '".$template_active."',
		template_format = '".$template_format."',
		template_sender_name = '".$template_sender_name."',
		template_sender_email = '".$template_sender_email."',
		template_language = '".$template_language."'
		WHERE template_id = '".$template_id."'
	");
	redirect(FUSION_SELF.$aidlink."&amp;status=su&amp;template_id=".$template_id);
} elseif (isset($_POST['test_template'])) {
	$template_id = $_POST['template_id'];
	$template_key = $_POST['template_key'];
	$template_format = $_POST['template_format'];
	$template_subject = $_POST['template_subject'];
	$template_content = $_POST['template_content'];
	$template_active = $_POST['template_active'];
	$template_sender_name = $_POST['template_sender_name'];
	$template_sender_email = $_POST['template_sender_email'];
	$template_language = $_POST['template_language'];
	$result = dbquery("UPDATE ".DB_EMAIL_TEMPLATES." SET
		template_subject = '".$template_subject."',
		template_content = '".$template_content."',
		template_active = '".$template_active."',
		template_format = '".$template_format."',
		template_sender_name = '".$template_sender_name."',
		template_sender_email = '".$template_sender_email."',
		template_language = '".$template_language."'
		WHERE template_id = '".$template_id."'
	");

	require_once INCLUDES."sendmail_include.php";
	sendemail_template($template_key, $locale['412'], $locale['413'], $locale['414'], $locale['415'], $locale['416'], $userdata['user_email']);

	redirect(FUSION_SELF.$aidlink."&amp;status=snd&amp;template_id=".$template_id."&amp;testmail=".$userdata['user_email']);
}

$result = dbquery("SELECT template_id, template_key, template_name, template_language FROM ".DB_EMAIL_TEMPLATES." ".(multilang_table("ET") ? "WHERE template_language='".LANGUAGE."'" : "")." ORDER BY template_key");
if (dbrows($result) != 0) {
	$editlist = ""; $sel = "";
	while ($data = dbarray($result)) {
		if (isset($_POST['template_id'])) { $sel = ($_POST['template_id'] == $data['template_id'] ? " selected='selected'" : ""); }
		if (isset($_GET['template_id'])) { $sel = ($_GET['template_id'] == $data['template_id'] ? " selected='selected'" : ""); }
		$editlist .= "<option value='".$data['template_id']."'".$sel.">[".$data['template_key']."] ".$data['template_name']."</option>\n";
	}
	opentable($locale['401']);
	echo "<div style='text-align:center;'>\n<form name='selectform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
	echo "<select name='template_id' class='textbox' onchange='this.form.submit();' class='col-12' style='width:280px;'>\n".$editlist."</select><br />\n";
	echo "<input type='submit' value='".$locale['417']."' class='button' />\n";
	echo "</form>\n</div>\n";
	closetable();
}

if (isset($_GET['template_id']) && isnum($_GET['template_id']) || isset($_POST['template_id']) && isnum($_POST['template_id'])) {
	$template_id = (isset($_POST['template_id']) ? $_POST['template_id'] : $_GET['template_id']);
	$result = dbquery(
		"SELECT * FROM ".DB_EMAIL_TEMPLATES." WHERE template_id='".$template_id."' LIMIT 1"
	);
	if (dbrows($result)) {
		$data = dbarray($result);
		$template_id = $data['template_id'];
		$template_key = $data['template_key'];
		$template_format = $data['template_format'];
		$template_name = $data['template_name'];
		$template_subject = $data['template_subject'];
		$template_content = $data['template_content'];
		$template_sender_name = $data['template_sender_name'];
		$template_sender_email = $data['template_sender_email'];
		$template_language = $data['template_language'];
		if ($data['template_active'] == "1") {
			$template_active_chk = " checked='checked'";
			$template_inactive_chk = "";
			$template_active_info = "";
			$template_inactive_info = " display:none;";
			$template_active_bg = "#D7F9D7";
		} else {
			$template_active_chk = "";
			$template_inactive_chk = " checked='checked'";
			$template_active_info = " display:none;";
			$template_inactive_info = "";
			$template_active_bg = "#FFDBDB";
		}
		if ($data['template_format'] == "html") {
			$template_html_chk = " checked='checked'";
			$template_plain_chk = "";
			$html_active_info = " display:none;";
			$html_buttons = "";
			$html_text = $locale['418'];
		} else {
			$template_html_chk = "";
			$template_plain_chk = " checked='checked'";
			$html_active_info = "";
			$html_buttons = " display:none;";
			$html_text = $locale['419'];
		}
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
	opentable($locale['400']);
	require_once INCLUDES."html_buttons_include.php";
	echo "<form name='emailtemplateform' method='post' action='".FUSION_SELF.$aidlink."' onsubmit='return ValidateForm(this);'>\n";
	echo "<table class='tbl-border center' cellpadding='1' cellspacing='0' style='width:90%;'>\n";
	echo "<tr>\n<td class='tbl2' colspan='2' style='font-weight:bold; text-align:center;'>".$locale['420'].$template_name."</td></tr>\n";
	echo "<tr>\n";
	echo "<td class='tbl1' style='width:15%;vertical-align:top; color:#000 !important; background-color:".$template_active_bg.";'>".$locale['421']."</td>\n";
	echo "<td class='tbl1' style='color:#000 !important; background-color:".$template_active_bg.";'>\n";
	echo "<div style='margin-right:50px;float: right;width: 70%; text-align:right;'>\n";
	echo "<div id='active_info' style='".$template_active_info."'>".sprintf($locale['422'], $html_text)."</div>\n";
	echo "<div id='inactive_info' style='".$template_inactive_info."'>".$locale['423']."</div>\n";
	echo "</div>\n";
	echo "<label><input type='radio' name='template_active' class='template_active' value='1'".$template_active_chk." />".$locale['424']."</label>\n";
	echo "<label><input type='radio' name='template_active' class='template_active' value='0'".$template_inactive_chk." />".$locale['425']."</label>\n";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='width:15%;vertical-align:top;'>".$locale['426']."</td>\n";
	echo "<td class='tbl1'>\n";
	echo "<div style='margin-right:50px;float: right;width: 70%; text-align:right;'>\n";
	echo "<div id='html_info' style='".$html_active_info."'>".$locale['427']."</div>\n";
	echo "</div>\n";
	echo "<label><input type='radio' name='template_format' class='template_format' value='html'".$template_html_chk." />".$locale['418']."</label>\n";
	echo "<label><input type='radio' name='template_format' class='template_format' value='plain'".$template_plain_chk." />".$locale['419']."</label>\n";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='width:15%;vertical-align:top;'>\n";
	if ($template_key == "CONTACT") {
		echo $locale['428'];
	} else {
		echo $locale['429'];
	}
	echo "</td>\n";
	echo "<td class='tbl1'>\n";
	echo "<input type='text' name='template_sender_name' class='textbox' style='width:40%;' value='".$template_sender_name."' />\n";
	if ($template_key == "CONTACT") {
		echo "&nbsp;<span class='small'>(".$locale['430'].")</span>\n";
	}
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='width:15%;vertical-align:top;'>\n";
	if ($template_key == "CONTACT") {
		echo $locale['431'];
	} else {
		echo $locale['432'];
	}
	echo "</td>\n";
	echo "<td class='tbl1'>\n";
	echo "<input type='text' name='template_sender_email' class='textbox' style='width:40%;' value='".$template_sender_email."' />\n";
	if ($template_key == "CONTACT") {
		echo "&nbsp;<span class='small'>(".$locale['433'].")</span>\n";
	}
	echo "</td>\n";
	echo "</tr>\n";
	if (multilang_table("ET")) { 
	echo "<tr><td class='tbl'>".$locale['global_ML100']."</td>\n";
	$opts = get_available_languages_list($selected_language = "$template_language");
	echo "<td class='tbl'>
	<select name='template_language' class='textbox' style='width:200px;'>".$opts."</select></td>\n"; 
	echo "</tr>\n"; 
	} else {
	echo "<input type='hidden' name='tem plate_language' value='".$template_language."' />\n";	
	}
	echo "<tr>\n";
	echo "<td class='tbl1' style='width:15%;vertical-align:top;'>".$locale['434']."</td>\n";
	echo "<td class='tbl1'>\n";
	echo "<textarea name='template_subject' class='textbox' style='width:100%;' rows='1'>".$template_subject."</textarea>\n";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='width:15%;'></td>\n";
	echo "<td class='tbl1'>\n";
	echo "<input type='button' class='button' value='[SITENAME]' onclick=\"insertText('template_subject', '[SITENAME]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[SITEURL]' onclick=\"insertText('template_subject', '[SITEURL]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[SUBJECT]' onclick=\"insertText('template_subject', '[SUBJECT]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[USER]' onclick=\"insertText('template_subject', '[USER]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[SENDER]' onclick=\"insertText('template_subject', '[SENDER]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[RECEIVER]' onclick=\"insertText('template_subject', '[RECEIVER]', 'emailtemplateform');\" />\n";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='width:15%;vertical-align:top;'>".$locale['435']."</td>\n";
	echo "<td class='tbl1'>\n";
	echo "<textarea name='template_content' class='textbox' style='width:100%;' rows='15'>".$template_content."</textarea>\n";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='width:15%;'></td>\n";
	echo "<td class='tbl1'>\n";
	echo "<input type='button' class='button' value='[SUBJECT]' onclick=\"insertText('template_content', '[SUBJECT]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[MESSAGE]' onclick=\"insertText('template_content', '[MESSAGE]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[SENDER]' onclick=\"insertText('template_content', '[SENDER]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[RECEIVER]' onclick=\"insertText('template_content', '[RECEIVER]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[USER]' onclick=\"insertText('template_content', '[USER]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[SITENAME]' onclick=\"insertText('template_content', '[SITENAME]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[SITEURL]' onclick=\"insertText('template_content', '[SITEURL]', 'emailtemplateform');\" />\n";
	echo "<input type='button' class='button' value='[THREAD_URL]' onclick=\"insertText('template_content', '[THREAD_URL]', 'emailtemplateform');\" />\n";
	echo "<br />\n";
	echo "<div id='html_buttons' style='margin-top:5px;".$html_buttons."'>\n";
	echo "<input type='button' value='".$locale['436']."' class='button' onMousedown=\"javascript:this.form.template_content.focus();this.form.template_content.select();\" onmouseup=\"addText('template_content', '&lt;body style=\'background-color:#D7F9D7;\'&gt;', '&lt;/body&gt;', 'emailtemplateform');\" />\n";
	echo "<input type='button' value='DIV' class='button' style='text-decoration:underline;' onclick=\"addText('template_content', '&lt;div&gt;', '&lt;/div&gt;', 'emailtemplateform');\" />\n";
	echo "<input type='button' value='SPAN' class='button' onclick=\"addText('template_content', '&lt;span&gt;', '&lt;/span&gt;', 'emailtemplateform');\" />\n";
	echo display_html("emailtemplateform", "template_content", true, true);
	$folder = BASEDIR."images/";
	$image_files = makefilelist($folder, ".|..|index.php", true);
	$image_list = makefileopts($image_files);
	echo "<select name='insertimage' class='textbox' style='margin-top:5px' onchange=\"insertText('template_content', '&lt;img src=\'".$settings['siteurl']."images/' + this.options[this.selectedIndex].value + '\' alt=\'\' style=\'margin:5px;\' align=\'left\' /&gt;', 'emailtemplateform');this.selectedIndex=0;\">\n";
	echo "<option value=''>".$locale['html401']."</option>\n".$image_list."</select>\n";
	echo "</div>\n";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl2' colspan='2' style='text-align:center;'>\n";
	echo "<input type='hidden' name='template_id' value='".$template_id."' />\n";
	echo "<input type='hidden' name='template_key' value='".$template_key."' />\n";
	echo "<input type='submit' class='button' name='test_template' value='".$locale['437']."' onclick=\"return confirm('".sprintf($locale['438'], $userdata['user_email'])."');\" />\n";
	echo "<input type='submit' class='button' name='save_template' value='".$locale['439']."' />\n";
	echo "<input type='reset' class='button' value='".$locale['440']."' />\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";

	echo "<script type='text/javascript'>\n";
	echo "/* <![CDATA[ */\n";
	echo "jQuery(function() {
			jQuery('.template_active').change(function() {
				jQuery('#active_info').slideToggle('slow');
				jQuery('#inactive_info').slideToggle('slow');
			});
			jQuery('.template_format').change(function() {
				jQuery('#html_info').slideToggle('slow');
				jQuery('#html_buttons').slideToggle('slow');
			});
		});\n";
	echo "/* ]]>*/\n";
	echo "</script>\n";

	closetable();

	opentable($locale['450']);
	echo "<table class='tbl-border center' cellpadding='1' cellspacing='0' style='width:500px;'>\n";
	echo "<tr>\n<td class='tbl2' colspan='2' style='text-align:center;font-weight:bold;'>".$locale['451']."</td>\n</tr>\n";
	echo "<tr>\n";
	echo "<td class='tbl1' style='width:20%;text-align:center;font-weight:bold;'>".$locale['452']."</td>\n";
	echo "<td class='tbl1' style='text-align:center;font-weight:bold;'>".$locale['453']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>[SITENAME]</td>\n";
	echo "<td class='tbl1'>".$settings['sitename']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>[SITEURL]</td>\n";
	echo "<td class='tbl1'>".$settings['siteurl']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>[SUBJECT]</td>\n";
	echo "<td class='tbl1'>".$locale['454']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>[MESSAGE]</td>\n";
	echo "<td class='tbl1'>".$locale['455']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>[USER]</td>\n";
	echo "<td class='tbl1'>".$locale['456']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>[SENDER]</td>\n";
	echo "<td class='tbl1'>".$locale['457']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>[RECEIVER]</td>\n";
	echo "<td class='tbl1'>".$locale['458']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>[THREAD_URL]</td>\n";
	echo "<td class='tbl1'>".$locale['459']."</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	closetable();
}

echo "<script type='text/javascript'>\n";
echo "/* <![CDATA[ */\n";
echo "function ValidateForm(frm) {\n";
echo "if(frm.template_subject.value=='') {\n";
echo "alert('".$locale['470']."');\n"."return false;\n}\n";
echo "if(frm.template_content.value=='') {\n";
echo "alert('".$locale['471']."');\n";
echo "return false;\n}\n";
echo "if(frm.template_sender_name.value=='') {\n";
echo "alert('".$locale['472']."');\n"."return false;\n}\n";
echo "if(frm.template_sender_email.value=='') {\n";
echo "alert('".$locale['473']."');\n";
echo "return false;\n}\n";
echo "}\n";
echo "/* ]]>*/\n";
echo "</script>\n";

require_once THEMES."templates/footer.php";
?>