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
// Just follow the display of the current admin language.
include LOCALE.LOCALESET."/setup.php";
$settings = fusion_get_settings();
if (!empty($locale['setup_3007'])) dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3007']."' WHERE mlt_rights='CP'");
if (!empty($locale['setup_3210'])) dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3210']."' WHERE mlt_rights='SL'");
if (!empty($locale['setup_3208'])) dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3208']."' WHERE mlt_rights='ET'");
if (!empty($locale['setup_3211'])) dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3211']."' WHERE mlt_rights='PN'");

$inf_result = dbquery("SELECT * FROM ".DB_INFUSIONS);
if (dbrows($inf_result)>0) {
	while ($cdata = dbarray($inf_result)) {
		include INFUSIONS.$cdata['inf_folder']."/infusion.php"; // there is a system language inside. // cant read into system language.
		if (isset($inf_mlt) && is_array($inf_mlt)) {
			$inf_mlt = flatten_array($inf_mlt);
			if (!empty($inf_mlt['title']) && !empty($inf_mlt['rights'])) {
				dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$inf_mlt['title']."' WHERE mlt_rights='".$inf_mlt['rights']."'");
			} else {
                //$defender->stop();
                addNotice("danger",
                          "Error due to incomplete locale translations in infusions folder ".$cdata['inf_folder'].". This infusion does not have the localized title and change is aborted. Please translate setup.php.");
			}
		}
		unset($inf_mlt);
	}
}

add_breadcrumb(array('link' => ADMIN."settings_languages.php".$aidlink, 'title' => $locale['682ML']));

if (isset($_POST['savesettings'])) {
	$inputData = array(
		"localeset" => form_sanitizer($_POST['localeset'], "English", "localeset"),
		"old_localeset" => form_sanitizer($_POST['old_localeset'], "", "old_localeset"),
        "enabled_languages" => form_sanitizer($_POST['enabled_languages'], "", "enabled_languages"),
        // returns Chinese_Simplified,English,Malay
        "old_enabled_languages" => form_sanitizer($_POST['old_enabled_languages'], "", "old_enabled_languages"),
        // returns Chinese_Simplified.English.Malay
	);

    // format both to .
    if (empty($inputData['enabled_languages'])) {
		$defender->stop();
		addNotice("danger", "You need to enable at least one language");
	}

	if (defender::safe()) {

        $inArray_SQLCond = array(
			"enabled_languages" => str_replace(".", "','", $inputData['enabled_languages']),
            "old_enabled_languages" => str_replace(".", "','", $inputData['old_enabled_languages'])
		);
        $core_SQLVal = array(
            "enabled_languages" => str_replace(",", ".", $inputData['enabled_languages']),
            "old_enabled_languages" => str_replace(",", ".", $inputData['old_enabled_languages'])
        );

        $array = array(
            "old_enabled_languages" => explode(".", $inputData['old_enabled_languages']),
            "enabled_languages" => explode(",", $inputData['enabled_languages'])
        );

        // update current system locale
		dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$inputData['localeset']."' WHERE settings_name='locale'"); // update on the new system locale.

        // Switch visible language to localeset
        if ($inputData['old_localeset'] !== $inputData['localeset']) {
            set_language($inputData['localeset']);
		}

        /**
		 * Part II : Insert and Purge actions when add or drop languages
		 */

        if ($inputData['old_enabled_languages'] != $inputData['enabled_languages']) { // language family have changed

			$added_language = array_diff($array['enabled_languages'], $array['old_enabled_languages']);

			$removed_language = array_diff($array['old_enabled_languages'], $array['enabled_languages']);

            // Add Home Links to additional languages
			if (!empty($added_language)) {
				foreach($added_language as $language) {
					include LOCALE.$language."/setup.php";
					$linkArray = array(
						"link_id" => 0,
						"link_name" => $locale['setup_3300'],
						"link_cat" => 0,
						"link_url" => "index.php",
						"link_icon" => "",
						"link_visibility" => 0,
						"link_position" => 2,
						"link_window" => 0,
						"link_order" => 1,
						"link_language" => $language
					);
					dbquery_insert(DB_SITE_LINKS, $linkArray, "save");
				}
			}

            dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$core_SQLVal['enabled_languages']."' WHERE settings_name='enabled_languages'");

            dbquery("UPDATE ".DB_PANELS." SET panel_languages='".$core_SQLVal['enabled_languages']."'");

            dbquery("UPDATE ".DB_USERS." SET user_language='Default' WHERE user_language NOT IN ('".$inArray_SQLCond['enabled_languages']."')");

            /**
			 * Email templates
			 */
			if (!empty($added_language)) {
				foreach($added_language as $language) {
					$language_exist = dbarray(dbquery("SELECT template_language FROM ".DB_EMAIL_TEMPLATES." WHERE template_language ='".$language."'"));
					if (is_null($language_exist['template_language'])) {
						include LOCALE.$language."/setup.php";
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'PM', 'html', '0', '".$locale['setup_3801']."', '".$locale['setup_3802']."', '".$locale['setup_3803']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'POST', 'html', '0', '".$locale['setup_3804']."', '".$locale['setup_3805']."', '".$locale['setup_3806']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'CONTACT', 'html', '0', '".$locale['setup_3807']."', '".$locale['setup_3808']."', '".$locale['setup_3809']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
					}
				}
			}

            // Remove any dropped language email templates
            if (!empty($removed_language)) {
                foreach ($removed_language as $language) {
                    $language_exist = dbarray(dbquery("SELECT template_language FROM ".DB_EMAIL_TEMPLATES." WHERE template_language ='".$language."'"));
                    if (!empty($language_exist['template_language'])) {
                        dbquery("DELETE FROM ".DB_EMAIL_TEMPLATES." WHERE template_language = '".$language."'");
                    }
                }
            }

			// Update all infusions and remove registered multilang table records
			$inf_result = dbquery("SELECT * FROM ".DB_INFUSIONS);
            $lang_cmd = array();

			if (dbrows($inf_result)>0) {
				while ($cdata = dbarray($inf_result)) {
                    $mlt_insertdbrow = array();
                    $mlt_deldbrow = array();

                    include INFUSIONS.$cdata['inf_folder']."/infusion.php";

					if (!empty($added_language)) {
						foreach($added_language as $language) {
							include LOCALE.$language."/setup.php";
							if (isset($mlt_insertdbrow[$language])) {
								foreach($mlt_insertdbrow[$language] as $sql) {
                                    $lang_cmd['insert'][] = $sql;
                                }
								unset($mlt_insertdbrow[$language]);
							}
                        }
					}

                    if (!empty($removed_language)) {
						foreach($removed_language as $language) {
							// include locale file
							include LOCALE.$language."/setup.php";
							if (isset($mlt_deldbrow[$language])) {
								foreach($mlt_deldbrow[$language] as $sql) {
                                    $lang_cmd['delete'][] = $sql;
								}
								unset($mlt_deldbrow[$language]);
							}
						}
					}
				}

                if (!empty($lang_cmd['delete'])) {
                    foreach ($lang_cmd['delete'] as $delete_sql) {

                        dbquery("DELETE FROM ".$delete_sql." ");

                    }
                }

                if (!empty($lang_cmd['insert'])) {
                    foreach ($lang_cmd['insert'] as $insert_sql) {

                        dbquery("INSERT INTO ".$insert_sql." ");

                    }
                }
            }
		}


		/**
		 * Part III - Set Checkboxes for on and off of mlt handler
		 */
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
		// reset back to current language
		include LOCALE.LOCALESET."setup.php";
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
echo form_lang_checkbox(makefilelist(LOCALE, ".|..", TRUE, "folders"));
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


/**
 * Create Language Selector Checkboxes.
 * @param string[] $language_list
 * @return string
 */
function form_lang_checkbox(array $language_list) {
    $enabled_languages = fusion_get_enabled_languages();
    $res = "";
    foreach ($language_list as $language) {
        $ischecked = isset($enabled_languages[$language]) ? TRUE : FALSE;
        $isDisabled = fusion_get_settings("locale") == $language ? TRUE : FALSE;
        $res .= form_checkbox("enabled_languages[]", translate_lang_names($language), $ischecked, array(
            "input_id" => "langcheck-".$language,
            "value" => $language,
            "class" => "m-b-0",
            "reverse_label" => TRUE,
            "disabled" => $isDisabled ? TRUE : FALSE,
        ));
        if ($isDisabled) {
            $res .= form_hidden("enabled_languages[]", "", $language);
        }
    }

    return $res;
}