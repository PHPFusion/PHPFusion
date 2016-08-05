<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ex_navigation_admin.php
| Author: Stas Beh (dialektika)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../../maincore.php";
require_once THEMES."templates/admin_header.php";
include INFUSIONS."ex_navigation_panel/infusion_db.php";

// Check if a locale file is available that match the selected locale.
if (file_exists(INFUSIONS."ex_navigation_panel/locale/".LANGUAGE.".php")) {
	include INFUSIONS."ex_navigation_panel/locale/".LANGUAGE.".php";
} else {
	include INFUSIONS."ex_navigation_panel/locale/English.php";
}

if (!checkrights("ENP") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../../index.php");
}

$nav = "<table cellpadding='0' cellspacing='0' class='tbl-border' align='center' style='width:300px; margin-bottom:20px; text-align:center;'>\n<tr>\n";
$nav .= "<td class='".(!isset($_GET['page']) || $_GET['page'] != "settings" ? "tbl2" : "tbl1")."'><a href='".FUSION_SELF.$aidlink."'>".$locale['ENP_admin1']."</a></td>\n";
$nav .= "<td class='".(isset($_GET['page']) && $_GET['page'] == "settings" ? "tbl2" : "tbl1")."'><a href='".FUSION_SELF.$aidlink."&amp;page=new'>".$locale['ENP_new_link']."</a></td>\n";
$nav .= "</tr>\n</table>\n";

include_once INCLUDES."bbcode_include.php";
if (!isset($_GET['page']) || $_GET['page'] != "new") {
	if (isset($_GET['status']) && !isset($message)) {
		if ($_GET['status'] == "su") {
			$message = $locale['ENP_exlink_updated'];
		} elseif ($_GET['status'] == "del") {
			$message = $locale['ENP_exlink_deleted'];
		}
		$message = $message.$locale['ENP_return']."<a href='".FUSION_SELF.$aidlink."'>".$locale['ENP_admin1']."</a>";
		if ($message) {
			echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
		}
	} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['exlink_id']) && isnum($_GET['exlink_id']))) {
		$result = dbquery("DELETE FROM ".DB_EXNAVPANEL." WHERE exlink_id='".$_GET['exlink_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=del");
	} else {
		if (isset($_POST['saveexlink']) && (isset($_GET['exlink_id']) && isnum($_GET['exlink_id']))) {
			$exlink_name = form_sanitizer($_POST['exlink_name'], '', 'exlink_name');
			if (!defined('FUSION_NULL')) {
				$result = dbquery("UPDATE ".DB_EXNAVPANEL." SET 
				exlink_name='".$exlink_name."', exlink_url='".$_POST['exlink_url']."', exlink_page='".$_POST['exlink_page']."', exlink_position='".$_POST['exlink_position']."', exlink_window='".$_POST['exlink_window']."', exlink_language='".$_POST['exlink_language']."'   
				WHERE exlink_id='".$_GET['exlink_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			}
		}
		if (isset($_POST['newexlink'])) {
			$exlink_name = form_sanitizer($_POST['exlink_name'], '', 'exlink_name');
			$locale_files = makefilelist(LOCALE, ".|..", TRUE, "folders");
			if (!defined('FUSION_NULL')) {
				$result = dbquery("SELECT MAX(exlink_position) AS exlink_position FROM ".DB_EXNAVPANEL." WHERE exlink_page='".$_POST['exlink_page']."'");
				$max_pos = dbarray($result);
				$result = dbquery("INSERT INTO ".DB_EXNAVPANEL." (exlink_name, exlink_url, exlink_page, exlink_position, exlink_window, exlink_language ) VALUES 
				('".$exlink_name."', '".$_POST['exlink_url']."', '".$_POST['exlink_page']."', '".($max_pos['exlink_position']+1)."', '".$_POST['exlink_window']."', '".$locale_files[$_POST['exlink_language']]."')");
				redirect(FUSION_SELF.$aidlink."&status=su");
			}
		}
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['exlink_id']) && isnum($_GET['exlink_id']))) {
			$result1 = dbquery("SELECT exlink_id, exlink_name, exlink_url, exlink_page, exlink_position, exlink_window, exlink_language FROM ".DB_EXNAVPANEL." WHERE exlink_id='".$_GET['exlink_id']."'");
			if (dbrows($result1)) {
				$data1 = dbarray($result1);
				opentable($locale['ENP_edit_link']);
				$cp_result = dbquery("SELECT page_id, page_title FROM ".DB_CUSTOM_PAGES." ORDER BY page_id");
				while ($cdata = dbarray($cp_result))
					{
					$cpages[$cdata['page_id']]=$cdata['page_title'];
					}
				echo openform('input_form', 'input_form', 'post', FUSION_SELF.$aidlink."&amp;exlink_id=".$data1['exlink_id']."", array('downtime' => 0, 'notice' => 0));
				echo form_text($locale['ENP_name'], 'exlink_name', 'exlink_name', $data1['exlink_name'], array('required' => 1, 'inline' => 1));
				echo form_text($locale['ENP_url'], 'exlink_url', 'exlink_url', $data1['exlink_url'], array('required' => 1, 'inline' => 1));
				echo form_select($locale['ENP_page'], 'exlink_page', 'exlink_page', $cpages, $data1['exlink_page'], array('required' => 1, 'inline' => 1));
				echo form_text($locale['ENP_position'], 'exlink_position', 'exlink_position', $data1['exlink_position'], array('required' => 1, 'inline' => 1));
				echo form_text($locale['ENP_window'], 'exlink_window', 'exlink_window', $data1['exlink_window'], array('required' => 1, 'inline' => 1));
				echo form_text($locale['ENP_language'], 'exlink_language', 'exlink_language', $data1['exlink_language'], array('required' => 1, 'inline' => 1));
				echo form_button($locale['ENP_save_exlink'], 'saveexlink', 'saveexlink', $locale['ENP_save_exlink'], array('class' => 'btn-primary'));
				echo closeform();
				closetable();
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		}
		opentable($locale['ENP_edit_link']);
		echo $nav;
		$result = dbquery("SELECT * FROM ".DB_EXNAVPANEL);
		$rows = dbrows($result);
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
			$_GET['rowstart'] = 0;
		}
		if ($rows != 0) {
			$i = 0;
			// exnavpanel
			$result1 = dbquery("SELECT l.exlink_id, l.exlink_name, l.exlink_url, l.exlink_position, l.exlink_window, l.exlink_page, l.exlink_language
				FROM ".DB_EXNAVPANEL." l 
				ORDER BY l.exlink_id ASC LIMIT ".$_GET['rowstart'].",20");
			echo "<div class='list-group'>\n";
			while ($data1 = dbarray($result1)) {
				echo "<div class='list-group-item' style='min-height:50px;'>\n";
				echo "<div class='comment-name'>";
				echo "(".$data1['exlink_id'].")";
				echo "</span>\n";
				echo "<span class='small'><a href=".$data1['exlink_url'].">".$data1['exlink_name']."</a> - link # ".$data1['exlink_position']." on page # ".$data1['exlink_page']." with lang=".$data1['exlink_language']."</div>\n";
				echo "<div class='m-t-5'><small>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;exlink_id=".$data1['exlink_id']."'>".$locale['ENP_edit']."</a> -\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;exlink_id=".$data1['exlink_id']."' onclick=\"return confirm('".$locale['ENP_warning_exlinks']."');\">".$locale['ENP_delete']."</a>";
				echo "</small></div>\n";
				echo "</div>\n";
			}
			echo "</div>\n";
			
			echo "<div align='center' style='margin-top:5px;'>\n".makePageNav($_GET['rowstart'], 20, $rows, 3, FUSION_SELF.$aidlink."&amp;")."\n</div>\n";
		} else {
			echo "<div style='text-align:center'><br />\n".$locale['ENP_no_links']."<br /><br />\n</div>\n";
		}
		closetable();
	}
} else {
	require_once INCLUDES."infusions_include.php";
	if (isset($_GET['status'])) {
		if ($_GET['status'] == "delall" && isset($_GET['numr']) && isnum($_GET['numr'])) {
			$message = number_format(intval($_GET['numr']))." ".$locale['ENP_exlinks_deleted'];
		} elseif ($_GET['status'] == "update_ok") {
			$message = $locale['ENP_update_ok'];
		}
	}
	if (isset($message) && $message != "") {
		echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
	}
	opentable($locale['ENP_new_link']);
	echo $nav;
	$cp_result = dbquery("SELECT page_id, page_title FROM ".DB_CUSTOM_PAGES." ORDER BY page_id");
	while ($cdata = dbarray($cp_result))
		{
		$cpages[$cdata['page_id']]=$cdata['page_title'];
		}
	echo openform('input_form', 'input_form', 'post', FUSION_SELF.$aidlink."", array('downtime' => 0, 'notice' => 0));
				echo form_text($locale['ENP_name'], 'exlink_name', 'exlink_name', "", array('required' => 1, 'inline' => 1));
				echo form_text($locale['ENP_url'], 'exlink_url', 'exlink_url', "", array('required' => 1, 'inline' => 1));
				echo "<div class='panel panel-default'>\n<div class='panel-body'>\n";
				echo form_select($locale['ENP_page'], 'exlink_page', 'exlink_page', $cpages, "", array('required' => 1, 'inline' => 1)); 
				//echo form_text($locale['ENP_position'], 'exlink_position', 'exlink_position', "", array('required' => 1, 'inline' => 1));
				$opts = array('1' => $locale['ENP_new'], '0' => $locale['ENP_same'],);
				echo form_select($locale['ENP_window'], 'exlink_window', 'exlink_window', $opts, "", array('inline' => 1));
				$locale_files = makefilelist(LOCALE, ".|..", TRUE, "folders");
				echo form_select($locale['ENP_language'], 'exlink_language', 'exlink_language', $locale_files, "", array('inline' => 1)); 
				echo form_button($locale['ENP_save_new_link'], 'newexlink', 'newexlink', $locale['ENP_save_new_link'], array('class' => 'btn-primary'));
				echo closeform();
	closetable();
}

require_once THEMES."templates/footer.php";
?>
