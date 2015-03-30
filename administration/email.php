<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
require_once "../maincore.php";
if (!checkrights("MAIL") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/emails.php";
if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "su") {
		$message = $locale['410']."\n";
	} elseif ($_GET['status'] == "snd") {
		$message = sprintf($locale['411'], $_GET['testmail']);
	}
	if ($message) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}
if (isset($_POST['save_template'])) {
	$template_id = form_sanitizer($_POST['template_id'], '', 'template_id');
	$template_format = form_sanitizer($_POST['template_format'], '', 'template_format');
	$template_subject = form_sanitizer($_POST['template_subject'], '', 'template_subject');
	$template_content = form_sanitizer($_POST['template_content'], '', 'template_content');
	$template_active = form_sanitizer($_POST['template_active'], '', 'template_active');
	$template_sender_name = form_sanitizer($_POST['template_sender_name'], '', 'template_sender_name');
	$template_sender_email = form_sanitizer($_POST['template_sender_email'], '', 'template_sender_email');
	$template_language = form_sanitizer($_POST['template_language'], '', 'template_language');
	if (!defined('FUSION_NULL')) {
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
	}
} elseif (isset($_POST['test_template'])) {
	$template_id = form_sanitizer($_POST['template_id'], '', 'template_id');
	$template_key = form_sanitizer($_POST['template_key'], '', 'template_key');
	$template_format = form_sanitizer($_POST['template_format'], '', 'template_format');
	$template_subject = form_sanitizer($_POST['template_subject'], '', 'template_subject');
	$template_content = form_sanitizer($_POST['template_content'], '', 'template_content');
	$template_active = form_sanitizer($_POST['template_active'], '', 'template_active');
	$template_sender_name = form_sanitizer($_POST['template_sender_name'], '', 'template_sender_name');
	$template_sender_email = form_sanitizer($_POST['template_sender_email'], '', 'template_sender_email');
	$template_language = form_sanitizer($_POST['template_language'], '', 'template_language');
	if (!defined('FUSION_NULL')) {
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
}
$result = dbquery("SELECT template_id, template_key, template_name, template_language FROM ".DB_EMAIL_TEMPLATES." ".(multilang_table("ET") ? "WHERE template_language='".LANGUAGE."'" : "")." ORDER BY template_id ASC");
$template = array();
if (dbrows($result) != 0) {
	$editlist = array();
	while ($data = dbarray($result)) {
		$template[$data['template_id']] = $data['template_name'];
	}
}
$tab_title = array();
foreach ($template as $id => $tname) {
	$tab_title['title'][$id] = $tname;
	$tab_title['id'][$id] = $id;
	$tab_title['icon'][$id] = '';
}

$_GET['section'] = isset($_GET['section']) ? $_GET['section'] : 1;
$tab_active = isset($_GET['section']) ? $_GET['section'] : tab_active($tab_title, $_GET['section'], 1);

echo opentab($tab_title, $tab_active, 'menu', 1);
echo opentabbody($tab_title['title'][$_GET['section']], $_GET['section'], $tab_active);
$template_id = isset($_GET['section']) && isnum($_GET['section']) ? $_GET['section'] : 0;
$result = dbquery("SELECT * FROM ".DB_EMAIL_TEMPLATES." WHERE template_id='".$template_id."' LIMIT 1");
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
		$template_active_info = "";
		$template_inactive_info = "class='display:none'";
	} else {
		$template_active_info = "class='display-none'";
		$template_inactive_info = "";
	}
	if ($data['template_format'] == "html") {
		$html_active_info = "display-none";
		$html_buttons = '';
		$html_text = $locale['418'];
	} else {
		$html_active_info = "";
		$html_buttons = "display-none";
		$html_text = $locale['419'];
	}
} else {
	//redirect(FUSION_SELF.$aidlink);
}
add_to_breadcrumbs(array('link'=>ADMIN.$aidlink, 'title'=>$locale['400']));
opentable($locale['400']);
require_once INCLUDES."html_buttons_include.php";
echo openform('emailtemplateform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<h4>".$locale['420'].$template_name."</h4>\n";
echo "<table class='table table-responsive'>\n<tbody>\n";
echo "<td class='tbl1'><label for='template_active'>".$locale['421']."</label></td>\n";
echo "<td class='tbl1'>\n";
$opts = array('1' => $locale['424'], // yes
	'0' => $locale['425'] // no
);
echo form_select('', 'template_active', 'template_active', $opts, $data['template_active'], array('placeholder' => $locale['choose']));
echo "<div class='m-t-10'>\n";
echo "<div id='active_info' ".$template_active_info." >".sprintf($locale['422'], $html_text)."</div>\n";
echo "<div id='inactive_info' ".$template_inactive_info." >".$locale['423']."</div>\n";
echo "</div>\n";
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' width='1%' style='vertical-align:top; white-space:nowrap;'><label for='template_format'>".$locale['426']."</label></td>\n";
echo "<td class='tbl1'>\n";
$opts = array('html' => $locale['418'], 'plain' => $locale['419']);
echo form_select('', 'template_format', 'template_format', $opts, $data['template_format'], array('placeholder' => $locale['choose']));
echo "<div id='html_info' class='m-t-10 ".$html_active_info."' >".$locale['427']."</div>\n";
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' style='width:15%;vertical-align:top;'>\n<label for='template_sender_name'>\n";
if ($template_key == "CONTACT") {
	echo $locale['428'];
} else {
	echo $locale['429'];
}
echo "</label>\n <span class='required'>*</span></td>\n";
echo "<td class='tbl1'>\n";
echo form_text('template_sender_name', '', $template_sender_name, array('required' => 1, 'error_text' => $locale['472']));
if ($template_key == "CONTACT") {
	echo "&nbsp;<span class='small'>(".$locale['430'].")</span>\n";
}
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' style='width:15%;vertical-align:top;'>\n<label for='template_sender_email'>";
if ($template_key == "CONTACT") {
	echo $locale['431'];
} else {
	echo $locale['432'];
}
echo "</label> <span class='required'>*</span></td>\n";
echo "<td class='tbl1'>\n";
echo form_text('template_sender_email', '', $template_sender_email, array('required' => 1, 'error_text' => $locale['473']));
if ($template_key == "CONTACT") {
	echo "&nbsp;<span class='small'>(".$locale['433'].")</span>\n";
}
echo "</td>\n";
echo "</tr>\n";
if (multilang_table("ET")) {
	echo "<tr><td class='tbl'><label for='template_language'>".$locale['global_ML100']."</label></td>\n";
	$opts = get_available_languages_list($selected_language = "$template_language");
	echo "<td class='tbl'>\n";
	echo form_select('', 'template_language', 'template_language', $language_opts, $template_language, array('placeholder' => $locale['choose']));
	echo "</td>\n</tr>\n";
} else {
	echo form_hidden('', 'template_language', 'template_language', $template_language);
}
echo "<tr>\n";
echo "<td class='tbl1' style='width:15%;vertical-align:top;'><label for='template_subject'>".$locale['434'].":</label>  <span class='required'>*</span></td>\n";
echo "<td class='tbl1'>\n";
echo form_textarea('', 'template_subject', 'template_subject', $template_subject, array('required' => 1, 'error_text' => $locale['470']));
echo "<div class='btn-group'>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[SITENAME]' onclick=\"insertText('template_subject', '[SITENAME]', 'emailtemplateform');\">SITENAME</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[SITEURL]' onclick=\"insertText('template_subject', '[SITEURL]', 'emailtemplateform');\">SITEURL</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[SUBJECT]' onclick=\"insertText('template_subject', '[SUBJECT]', 'emailtemplateform');\">SUBJECT</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[USER]' onclick=\"insertText('template_subject', '[USER]', 'emailtemplateform');\">USER</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[SENDER]' onclick=\"insertText('template_subject', '[SENDER]', 'emailtemplateform');\">SENDER</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[RECEIVER]' onclick=\"insertText('template_subject', '[RECEIVER]', 'emailtemplateform');\">RECEIVER</button>\n";
echo "</div>\n";
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' style='width:15%;vertical-align:top;'><label for='template_content'>".$locale['435'].":</label> <span class='required'>*</span></td>\n";
echo "<td class='tbl1'>\n";
echo form_textarea('', 'template_content', 'template_content', $template_content, array('required' => 1, 'error_text' => $locale['471']));
echo "<div class='btn-group'>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[SUBJECT]' onclick=\"insertText('template_content', '[SUBJECT]', 'emailtemplateform');\">SUBJECT</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[MESSAGE]' onclick=\"insertText('template_content', '[MESSAGE]', 'emailtemplateform');\">MESSAGE</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[SENDER]' onclick=\"insertText('template_content', '[SENDER]', 'emailtemplateform');\">SENDER</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[RECEIVER]' onclick=\"insertText('template_content', '[RECEIVER]', 'emailtemplateform');\">RECEIVER</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[USER]' onclick=\"insertText('template_content', '[USER]', 'emailtemplateform');\">USER</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[SITENAME]' onclick=\"insertText('template_content', '[SITENAME]', 'emailtemplateform');\">SITENAME</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[SITEURL]' onclick=\"insertText('template_content', '[SITEURL]', 'emailtemplateform');\">SITEURL</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='[THREAD_URL]' onclick=\"insertText('template_content', '[THREAD_URL]', 'emailtemplateform');\">THREAD URL</button>\n";
echo "</div>\n";
echo "<div id='html_buttons' class='m-t-5 ".$html_buttons."'>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='".$locale['436']."' onMousedown=\"javascript:this.form.template_content.focus();this.form.template_content.select();\" onmouseup=\"addText('template_content', '&lt;body style=\'background-color:#D7F9D7;\'&gt;', '&lt;/body&gt;', 'emailtemplateform');\">\n".$locale['436']."</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='DIV' style='text-decoration:underline;' onclick=\"addText('template_content', '&lt;div&gt;', '&lt;/div&gt;', 'emailtemplateform');\">DIV</button>\n";
echo "<button type='button' class='btn btn-sm btn-default button' value='SPAN'  onclick=\"addText('template_content', '&lt;span&gt;', '&lt;/span&gt;', 'emailtemplateform');\">SPAN</button>\n";
echo display_html("emailtemplateform", "template_content", TRUE, TRUE);
$folder = BASEDIR."images/";
$image_files = makefilelist($folder, ".|..|index.php", TRUE);
$opts = array();
foreach ($image_files as $image) {
	$opts[$image] = $image;
}
echo form_select('', 'insertimage', 'insertimage', $opts, '', array('placeholder' => $locale['469'], 'allowclear' => 1));
echo "</div>\n";
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td colspan='2' style='text-align:center;'>\n";
echo form_hidden('', 'template_id', 'template_id', $template_id);
echo form_hidden('', 'template_key', 'template_key', $template_key);
echo form_button('test_template', $locale['437'], $locale['437'], array('class' => 'btn-primary m-r-10'));
echo form_button('save_template', $locale['439'], $locale['439'], array('class' => 'btn-primary m-r-10'));
echo form_button('reset', $locale['440'], $locale['440'], array('class' => 'btn-primary'));
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";
echo closetabbody();
echo closetab();
closetable();
if (isset($_GET['section']) && isnum($_GET['section']) || isset($_POST['section']) && isnum($_POST['section'])) {
	opentable($locale['450']);
	echo "<table class='table table-responsive center' cellpadding='1' cellspacing='0'>\n<tbody>\n";
	echo "<tr>\n<td class='tbl2' colspan='2' style='text-align:center;font-weight:bold;'><strong>".$locale['451']."</strong></td>\n</tr>\n";
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
	echo "</tbody>\n</table>\n";
	closetable();
}
$loc = $locale['469'];
add_to_jquery("
     $('#template_active').bind('change', function() {
        var value = $(this).select2().val();
        if (value == 1) {
        $('#active_info').show();
        $('#inactive_info').hide();
        } else {
        $('#active_info').hide();
        $('#inactive_info').show();
        }
     });
     $('#template_format').bind('change', function() {
        var value = $(this).select2().val();
        if (value == 'plain') {
        $('#html_info').show();
        $('#html_buttons').hide();
        } else {
        $('#html_info').hide();
        $('#html_buttons').show();
        }
     });
     $('#insertimage').on('change', function(e) {
        insertText('template_content', '<img src=\'".$settings['siteurl']."images/' + this.options[this.selectedIndex].value + '\' alt=\'\' style=\'margin:5px;\' align=\'left\' />', 'emailtemplateform'); this.selectedIndex=0;
        $(this).select2({
                formatSelection: color,
                escapeMarkup: function(m) { return m; },
                formatResult: color,
                placeholder:'$loc',
                allowClear:true}).val('');
        });
    ");
require_once THEMES."templates/footer.php";
?>