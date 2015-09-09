<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: smileys.php
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
pageAccess('SM');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/smileys.php";
add_breadcrumb(array('link' => ADMIN.'smileys.php'.$aidlink, 'title' => $locale['403']));
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['smiley_id']) && isnum($_GET['smiley_id']))) {
	$result = dbquery("DELETE FROM ".DB_SMILEYS." WHERE smiley_id='".intval($_GET['smiley_id'])."'");
	addNotice("success", $locale['412']);
	redirect(FUSION_SELF.$aidlink);
}
$form_title = $locale['401'];
$data = array(
	"smiley_id" => 0,
	"smiley_code" => "",
	"smiley_image" => "",
	"smiley_text" => "",
);
$edit = FALSE;
if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['smiley_id']) && isnum($_GET['smiley_id']))) {
	$form_title = $locale['402'];
	$result = dbquery("SELECT * FROM ".DB_SMILEYS." WHERE smiley_id='".intval($_GET['smiley_id'])."'");
	if (dbrows($result) > 0) {
		$edit = TRUE;
		$data = dbarray($result);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
if (isset($_POST['save_smiley'])) {
	$smiley_code = $_POST['smiley_code'];
	if (QUOTES_GPC) {
		$_POST['smiley_code'] = stripslashes($_POST['smiley_code']);
		$smiley_code = str_replace(array("\"", "'", "\\", '\"', "\'", "<", ">"), "", $_POST['smiley_code']);
	}
	$data = array(
		"smiley_id" => form_sanitizer($_POST['smiley_id'], 0, "smiley_id"),
		"smiley_code" => form_sanitizer($smiley_code, "", "smiley_code"),
		"smiley_image" => form_sanitizer($_POST['smiley_image'], '', 'smiley_image'),
		"smiley_text" => form_sanitizer($_POST['smiley_text'], '', 'smiley_text')
	);
	$smiley_check = array(
		"update" => dbcount("(smiley_id)", DB_SMILEYS, "smiley_id !='".intval($data['smiley_id'])."' and smiley_code='".$data['smiley_code']."'"),
		"save" => dbcount("(smiley_id)", DB_SMILEYS, "smiley_code='".$data['smiley_code']."'"),
		"exists" => dbcount("(smiley_id)", DB_SMILEYS, "smiley_id='".intval($data['smiley_id'])."'"),
	);
	if (defender::safe()) {
		if ($smiley_check['exists']) {
			// update
			if ($smiley_check['update']) {
				// is being used
				addNotice("danger", $locale['413'].$locale['415']);
			} else {
				// clear to update
				dbquery_insert(DB_SMILEYS, $data, "update");
				addNotice("success", $locale['411']);
				redirect(FUSION_SELF.$aidlink);
			}
		} else {
			if ($smiley_check['save']) {
				// is being used
				addNotice("danger", $locale['414'].$locale['415']);
			} else {
				// clear to save
				dbquery_insert(DB_SMILEYS, $data, "save");
				addNotice("success", $locale['410']);
				redirect(FUSION_SELF.$aidlink);
			}
		}
	}
}
opentable($form_title);
$tab_title['title'][] = $locale['400'];
$tab_title['id'][] = "smiley_list";
$tab_title['icon'][] = "";
$tab_title['title'][] = $form_title;
$tab_title['id'][] = "smileyform";
$tab_title['icon'][] = "";
$tab_active = tab_active($tab_title, $edit ? 1 : 0);
echo opentab($tab_title, $tab_active, "smileyAdmin");
echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
$result = dbquery("SELECT smiley_id, smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS." ORDER BY smiley_text");
if (dbrows($result)) {
	echo "<table class='table table-hover table-striped'>\n";
	echo "<tr>\n<th class='tbl2'><strong>".$locale['430']."</strong></th>\n";
	echo "<th class='tbl2'><strong>".$locale['431']."</strong></th>\n";
	echo "<th class='tbl2'><strong>".$locale['432']."</strong></th>\n";
	echo "<th class='tbl2' width='1%' style='white-space:nowrap'><strong>".$locale['433']."</strong></th>\n</tr>\n<tbody>\n";
	while ($cdata = dbarray($result)) {
		echo "<tr>\n<td>".$cdata['smiley_code']."</td>\n";
		echo "<td><img src='".IMAGES."smiley/".$cdata['smiley_image']."' alt='".$data['smiley_text']."' /></td>\n";
		echo "<td>".$cdata['smiley_text']."</td>\n";
		echo "<td width='1%' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;smiley_id=".$cdata['smiley_id']."'>".$locale['434']."</a> -\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;smiley_id=".$cdata['smiley_id']."' onclick=\"return ConfirmDelete();\">".$locale['435']."</a></td>\n</tr>\n";
	}
	echo "</tbody>\n</table>\n";
} else {
	echo "<div class='well text-center m-t-20'>\n".$locale['436']."<br /><br />\n</div>\n";
}
echo closetabbody();
echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
echo openform('smiley_form', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
echo form_hidden("smiley_id", "", $data['smiley_id']);
$image_opts = array();
$image_files = makefilelist(IMAGES."smiley/", ".|..|index.php", TRUE);
foreach ($image_files as $filename) {
	$name = explode(".", $filename);
	$image_opts[$filename] = ucwords($name[0]);
}
echo form_select('smiley_image', $locale['421'], $data['smiley_image'], array(
	"options" => $image_opts,
	"required" => TRUE,
	"inline" => TRUE,
	'error_text' => $locale['438']
));
echo form_text('smiley_code', $locale['420'], $data['smiley_code'], array(
	'required' => TRUE,
	"inline" => TRUE,
	'error_text' => $locale['438']
));
echo form_text('smiley_text', $locale['422'], $data['smiley_text'], array(
	'required' => 1,
	"inline" => TRUE,
	'error_text' => $locale['439']
));
echo form_button('save_smiley', $locale['423'], $locale['423'], array('class' => 'btn-primary'));
echo closeform();
echo closetabbody();
echo closetab();
add_to_jquery("
function showMeSmileys(item) {
	return '<aside class=\"pull-left\" style=\"width:35px;\"><img style=\"height:15px;\" class=\"img-rounded\" src=\"".IMAGES."smiley/'+item.id+'\"/></aside> : ' + item.text;
}
$('#smiley_image').select2({
formatSelection: function(m) { return showMeSmileys(m); },
formatResult: function(m) { return showMeSmileys(m); },
escapeMarkup: function(m) { return m; },
});
");
require_once THEMES."templates/footer.php";