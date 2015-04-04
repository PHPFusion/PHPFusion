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
if (!checkrights("SM") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/smileys.php";
if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['412'];
	} elseif ($_GET['status'] == "sue") {
		$message = $locale['413']."<br />\n<span class='small'>".$locale['415']."</span>";
	} elseif ($_GET['status'] == "sne") {
		$message = $locale['414']."<br />\n<span class='small'>".$locale['415']."</span>";
	}
	if ($message) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}
if (isset($_POST['save_smiley'])) {
	if (QUOTES_GPC) {
		$_POST['smiley_code'] = stripslashes($_POST['smiley_code']);
	}
	$smiley_code = str_replace(array("\"", "'", "\\", '\"', "\'", "<", ">"), "", $_POST['smiley_code']);
	$smiley_code = form_sanitizer($smiley_code, '', 'smiley_code');
	$smiley_image = form_sanitizer($_POST['smiley_image'], '', 'smiley_image');
	$smiley_text = form_sanitizer($_POST['smiley_text'], '', 'smiley_text');
	if (!defined('FUSION_NULL')) {
		if (isset($_GET['smiley_id']) && isnum($_GET['smiley_id'])) {
			if (!dbcount("(smiley_id)", DB_SMILEYS, "smiley_code='".$smiley_code."' AND smiley_id!='".$_GET['smiley_id']."'")) {
				$result = dbquery("UPDATE ".DB_SMILEYS." SET smiley_code='".$smiley_code."', smiley_image='".$smiley_image."', smiley_text='".$smiley_text."' WHERE smiley_id='".$_GET['smiley_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			} else {
				redirect(FUSION_SELF.$aidlink."&status=sue");
			}
		} else {
			if (!dbcount("(smiley_id)", DB_SMILEYS, "smiley_code='".$smiley_code."'") && $smiley_image) {
				$result = dbquery("INSERT INTO ".DB_SMILEYS." (smiley_code, smiley_image, smiley_text) VALUES ('".$smiley_code."', '".$smiley_image."', '".$smiley_text."')");
				redirect(FUSION_SELF.$aidlink."&status=sn");
			} else {
				redirect(FUSION_SELF.$aidlink."&status=sne");
			}
		}
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['smiley_id']) && isnum($_GET['smiley_id']))) {
	$result = dbquery("DELETE FROM ".DB_SMILEYS." WHERE smiley_id='".$_GET['smiley_id']."'");
	redirect(FUSION_SELF.$aidlink."&status=del");
}
if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['smiley_id']) && isnum($_GET['smiley_id']))) {
	$result = dbquery("SELECT smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS." WHERE smiley_id='".$_GET['smiley_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		$smiley_code = $data['smiley_code'];
		$smiley_image = $data['smiley_image'];
		$smiley_text = $data['smiley_text'];
		$form_action = FUSION_SELF.$aidlink."&amp;smiley_id=".$_GET['smiley_id'];
		$form_title = $locale['402'];
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} else {
	$smiley_code = "";
	$smiley_image = "";
	$smiley_text = "";
	$form_action = FUSION_SELF.$aidlink;
	$form_title = $locale['401'];
}
opentable($form_title);
$image_files = makefilelist(IMAGES."smiley/", ".|..|index.php", TRUE);
$smiley_opts = array();
if (count($image_files) > 0) {
	foreach ($image_files as $smileys) {
		$smiley_opts[$smileys] = $smileys;
	}
}
echo openform('smiley_form', 'post', $form_action, array('max_tokens' => 1));
echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tbody>\n<tr>\n";
echo "<td class='tbl'><label for='smiley_code'>".$locale['420']."</label></td>\n";
echo "<td class='tbl'>\n";
echo form_text('smiley_code', '', $smiley_code, array('required' => 1, 'error_text' => $locale['438']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl'><label for='smiley_image'>".$locale['421']."</label> <span class='required'>*</span></td>\n";
echo "<td class='tbl'>\n";
echo form_select('smiley_image', '', $smiley_opts, $smiley_image, array('placeholder' => $locale['choose'], 'required' => 1, 'error_text' => $locale['438']));
echo "</tr>\n<tr>\n";
echo "<td class='tbl'><strong>".$locale['424']."</strong></td>\n";
echo "<td class='tbl'><img class='img-thumbnail' src='".($smiley_image ? IMAGES."smiley/".$smiley_image : IMAGES."imagenotfound.jpg")."' alt='smiley' id='smiley_preview' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl'><label for='smiley_text'>".$locale['422']."</label> <span class='required'>*</span></td>\n";
echo "<td class='tbl'>\n";
echo form_text('smiley_text', '', $smiley_text, array('required' => 1, 'error_text' => $locale['439']));
echo "</td>\n<tr>\n<td align='center' colspan='2' class='tbl'><br />\n";
echo form_button('save_smiley', $locale['423'], $locale['423'], array('class' => 'btn-primary'));
echo "</td>\n</tr>\n</tbody>\n</table>\n";
echo closeform();
echo "<script type='text/javascript'>\n";
echo "/* <![CDATA[ */\n";
echo "function PreviewSmiley() {\n";
echo "\tvar selectSmiley = document.getElementById('smiley_image');\n";
echo "\tvar imageSmiley = document.getElementById('smiley_preview');\n";
echo "\tvar optionValue = selectSmiley.options[selectSmiley.selectedIndex].value;\n";
echo "\tif (optionValue!='') {\n";
echo "\t\timageSmiley.src = '".IMAGES."smiley/' + optionValue;\n";
echo "\t} else {\n";
echo "\t\timageSmiley.src = '".IMAGES."imagenotfound.jpg';\n";
echo "\t}\n";
echo "}\n";
echo "function ConfirmDelete() {\n";
echo "return confirm('".$locale['416']."');\n";
echo "}\n";
echo "/* ]]>*/\n";
echo "</script>\n";
closetable();
opentable($locale['400']);
$result = dbquery("SELECT smiley_id, smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS." ORDER BY smiley_text");
if (dbrows($result)) {
	$i = 0;
	echo "<table cellpadding='0' cellspacing='1' width='450' class='table table-responsive tbl-border center'>\n<thead>\n";
	echo "<tr>\n<th class='tbl2'><strong>".$locale['430']."</strong></th>\n";
	echo "<th class='tbl2'><strong>".$locale['431']."</strong></th>\n";
	echo "<th class='tbl2'><strong>".$locale['432']."</strong></th>\n";
	echo "<th class='tbl2' width='1%' style='white-space:nowrap'><strong>".$locale['433']."</strong></th>\n</tr>\n</thead>\n<tbody>\n";
	while ($data = dbarray($result)) {
		$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
		echo "<tr>\n<td class='".$row_color."'>".$data['smiley_code']."</td>\n";
		echo "<td class='".$row_color."'><img src='".IMAGES."smiley/".$data['smiley_image']."' alt='".$data['smiley_text']."' /></td>\n";
		echo "<td class='".$row_color."'>".$data['smiley_text']."</td>\n";
		echo "<td class='".$row_color."' width='1%' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;smiley_id=".$data['smiley_id']."'>".$locale['434']."</a> -\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;smiley_id=".$data['smiley_id']."' onclick=\"return ConfirmDelete();\">".$locale['435']."</a></td>\n</tr>\n";
		$i++;
	}
	echo "</tbody>\n</table>\n";
} else {
	echo "<div style='text-align:center'><br />\n".$locale['436']."<br /><br />\n</div>\n";
}
closetable();
require_once THEMES."templates/footer.php";
?>