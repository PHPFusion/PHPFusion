<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_languages.php
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
pageAccess("LANG");
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
include_once LOCALE.LOCALESET."defender.php";
add_breadcrumb(array('link' => ADMIN."settings_languages.php".$aidlink, 'title' => $locale['682ML']));

if (isset($_POST['savesettings'])) {
	$inputData = array(
		"localeset" => form_sanitizer($_POST['localeset'], "English", "localeset"),
		"old_localeset" => form_sanitizer($_POST['old_localeset'], "", "old_localeset"),
		"enabled_languages" => form_sanitizer($_POST['enabled_languages'], ""),
		"old_enabled_languages" => form_sanitizer($_POST['old_enabled_languages'], "", "old_enabled_languages"),
	);

	if (empty($inputData['enabled_languages'])) {
		$defender->stop();
		addNotice("danger", "You need to enable at least one language");
	}

	if (defender::safe()) {
		// Adds handler to multilang_table()
		$ml_tables = "";
		if (isset($_POST['multilang_tables'])) {
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_status='0'");
			for ($i = 0; $i < count($_POST['multilang_tables']); $i++) {
				$ml_tables .= stripinput($_POST['multilang_tables'][$i]);
				if ($i != (count($_POST['multilang_tables'])-1)) $ml_tables .= ".";
			}
			$ml_tables = explode('.', $ml_tables);
			for ($i = 0; $i < count($ml_tables); $i++) {
				$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_status='1' WHERE mlt_rights='".$ml_tables[$i]."'");
			}
		}
		// update current system locale.
		//print_p("UPDATE ".DB_SETTINGS." SET settings_value='".$inputData['localeset']."' WHERE settings_name='locale'");
		dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$inputData['localeset']."' WHERE settings_name='locale'");

		$sql_array = array(
			"old" => str_replace(".", ",", $inputData['old_enabled_languages']),
			"new" => str_replace(".", ",", $inputData['enabled_languages']),
		);

		$php_array = array(
			"old" => explode(",", $sql_array['old']),
			"new" => explode(",", $sql_array['new'])
		);

		if ($sql_array['new'] != $sql_array['old']) { // language family have changed

			// Resets everyone who has a deprecated language to system locale.
			dbquery("UPDATE ".DB_USERS." SET user_language='Default' WHERE user_language NOT IN ('".$sql_array['new']."')");

			// Adds enabled languages sets - using dots
			dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$inputData['enabled_languages']."' WHERE settings_name='enabled_languages'");
			// Update all panel languages -- using dots
			dbquery("UPDATE ".DB_PANELS." SET panel_languages='".$inputData['enabled_languages']."'");

			// Delete unused email templates
			dbquery("DELETE FROM ".DB_EMAIL_TEMPLATES." WHERE template_language NOT IN ('".$sql_array['new']."')");
			// Insert new language template
			foreach ($php_array['new'] as $language) {
				$language_exist = dbarray(dbquery("SELECT template_language FROM ".DB_EMAIL_TEMPLATES." WHERE template_language ='".$language."'"));
				if (is_null($language_exist['template_language'])) {
					include LOCALE.$language."/setup.php";
					$result = dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'PM', 'html', '0', '".$locale['setup_3801']."', '".$locale['setup_3802']."', '".$locale['setup_3803']."', '".fusion_get_settings("siteusername")."', '".fusion_get_settings("siteemail")."', '".$language."')");
					$result = dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'POST', 'html', '0', '".$locale['setup_3804']."', '".$locale['setup_3805']."', '".$locale['setup_3806']."', '".fusion_get_settings("siteusername")."', '".fusion_get_settings("siteemail")."', '".$language."')");
					$result = dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'CONTACT', 'html', '0', '".$locale['setup_3807']."', '".$locale['setup_3808']."', '".$locale['setup_3809']."', '".fusion_get_settings("siteusername")."', '".fusion_get_settings("siteemail")."', '".$language."')");
				}
			}
			// Update default core site links with the set language
			include LOCALE.$inputData['localeset']."/setup.php";
			$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['setup_3300']."' WHERE link_language='".$inputData['old_localeset']."' AND link_url='index.php'");
			$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['setup_3305']."' WHERE link_language='".$inputData['old_localeset']."' AND link_url='contact.php'");
			$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['setup_3309']."' WHERE link_language='".$inputData['old_localeset']."' AND link_url='search.php'");
			$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_language='".$inputData['localeset']."' WHERE link_language='".$inputData['old_localeset']."'");

			// Update multilanguage tables with a new language if we have it
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3002']."' WHERE mlt_rights='AR'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3007']."' WHERE mlt_rights='CP'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3010']."' WHERE mlt_rights='DL'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3055']."' WHERE mlt_rights='BL'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3303']."' WHERE mlt_rights='FQ'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3304']."' WHERE mlt_rights='FO'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3205']."' WHERE mlt_rights='NS'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3206']."' WHERE mlt_rights='PG'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3207']."' WHERE mlt_rights='PO'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3003']."' WHERE mlt_rights='SB'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3209']."' WHERE mlt_rights='WL'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3210']."' WHERE mlt_rights='SL'");
			$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3211']."' WHERE mlt_rights='PN'");
			//$result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT015']."' WHERE mlt_rights='ES'"); // Eshop

			// Update all infusions and remove registered multilang table records
			$cresult = dbquery("SELECT * FROM ".DB_INFUSIONS);
			if (dbrows($cresult) > 0) {
				while ($cdata = dbarray($cresult)) {
					include INFUSIONS.$cdata['inf_folder']."/infusion.php"; // Should inject the new news cat table.
					if (isset($mlt_insertdbrow)) {
						foreach ($mlt_insertdbrow as $language => $sql) {
							// if current language is in, push in
							if (in_array($language, $php_array['new']) && !in_array($language, $php_array['old'])) { // find new language only
								foreach($sql as $insert_sql) {
									addNotice('warning', $insert_sql);
									dbquery("INSERT INTO ".$insert_sql);
								}
							}
						}
					}
					if (isset($mlt_deldbrow)) {
						foreach ($mlt_deldbrow as $language => $sql) {
							// if current language is not and is in old localeset, delete
							if (!in_array($language, $php_array['new']) && in_array($language, $php_array['old'])) { // find removed language only
								print_p($language." will be deprecated");
								foreach($sql as $del_sql) {
									addNotice('warning', $del_sql);
									dbquery("DELETE FROM ".$del_sql);
								}
							}
						}
					}
				}
			}
		}
		addNotice('success', $locale['900']);
		redirect(FUSION_SELF.$aidlink);
	} else {
		addNotice('success', $locale['901']);
	}
}

opentable($locale['682ML']);
echo "<div class='well'>".$locale['language_description']."</div>";
echo openform('settingsform', 'post', FUSION_SELF.$aidlink);
echo form_hidden('old_localeset', '', fusion_get_settings("locale"));
echo form_hidden('old_enabled_languages', '', fusion_get_settings("enabled_languages"));
echo form_select('localeset', $locale['417'], fusion_get_settings("locale"), array(
	'options' => fusion_get_enabled_languages(),
	"inline" => TRUE
));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['684ML']."</strong>\n";
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo get_available_languages_array(makefilelist(LOCALE, ".|..", TRUE, "folders"));
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
echo "<div class='alert alert-info'>".$locale['685ML']."</div>";
echo "</div>\n";
echo "</div>\n";
echo "<div class='row m-t-20'>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['668ML']."</strong><br />".$locale['669ML'];
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
$result = dbquery("SELECT * FROM ".DB_LANGUAGE_TABLES."");
while ($data = dbarray($result)) {
	echo "<input type='checkbox' value='".$data['mlt_rights']."' name='multilang_tables[]'  ".($data['mlt_status'] == '1' ? "checked='checked'" : "")." /> ".$data['mlt_title']." <br />";
}
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
echo "</div>\n";
echo "</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";