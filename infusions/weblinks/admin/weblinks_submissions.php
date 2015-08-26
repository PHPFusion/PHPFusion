<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/weblinks_submissions.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }
pageAccess('W');

include INFUSIONS."weblinks/locale/".LOCALESET."weblinks_submissions.php";
$links = "";

		$result = dbquery("SELECT submit_id, submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_type='l' ORDER BY submit_datestamp DESC");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$submit_criteria = unserialize($data['submit_criteria']);
				$links .= "<tr>\n<td class='tbl1'>".$submit_criteria['link_name']."</td>\n";
				$links .= "<td align='right' width='1%' class='tbl1' style='white-space:nowrap'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;section=submissions&amp;action=2&amp;t=l&amp;submit_id=".$data['submit_id']."'>".$locale['417']."</a></span> |\n";
				$links .= "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;section=submissions&amp;delete=".$data['submit_id']."'>".$locale['418']."</a></span></td>\n</tr>\n";
			}
		} else {
			$links = "<tr>\n<td colspan='2' class='tbl1'>".$locale['414']."</td>\n</tr>\n";
		}
		
		opentable($locale['410']);
		echo "<table class='table table-responsive tbl-border center'>\n<tbody>\n<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='link_submissions' name='link_submissions'></a>\n".$locale['411']."</td>\n";
		echo "</tr>".$links."\n";
		echo "</tbody>\n</table>\n";
		closetable();

if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "l")) {
	if (isset($_POST['add']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$link_name = stripinput($_POST['link_name']);
		$link_url = stripinput($_POST['link_url']);
		$link_description = stripinput($_POST['link_description']);
		$result = dbquery("INSERT INTO ".DB_WEBLINKS." (weblink_name, weblink_description, weblink_url, weblink_cat, weblink_datestamp, weblink_count) VALUES ('$link_name', '$link_description', '$link_url', '".$_POST['link_category']."', '".time()."', '0')");
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		opentable($locale['430']);
		echo "<br /><div style='text-align:center'>".$locale['431']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
		closetable();
	} else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		opentable($locale['432']);
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		echo "<br /><div style='text-align:center'>".$locale['433']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
		closetable();
	} else {
		$result = dbquery("SELECT ts.submit_criteria, ts.submit_datestamp, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$opts = "";
			$sel = "";
			$submit_criteria = unserialize($data['submit_criteria']);
			$posted = showdate("longdate", $data['submit_datestamp']);
			$result2 = dbquery("SELECT weblink_cat_id, weblink_cat_name FROM ".DB_WEBLINK_CATS." ORDER BY weblink_cat_name");
			if (dbrows($result2) != 0) {
				while ($data2 = dbarray($result2)) {
					if (isset($submit_criteria['link_category'])) {
						$sel = ($submit_criteria['link_category'] == $data2['weblink_cat_id'] ? " selected='selected'" : "");
					}
					$opts .= "<option value='".$data2['weblink_cat_id']."'$sel>".$data2['weblink_cat_name']."</option>\n";
				}
			} else {
				$opts .= "<option value='0'>".$locale['434']."</option>\n";
			}
			add_to_title($locale['global_200'].$locale['448'].$locale['global_201'].$submit_criteria['link_name']."?");
			opentable($locale['440']);
			echo "<form name='publish' method='post' action='".FUSION_SELF.$aidlink."&amp;action=2&amp;t=l&amp;submit_id=".$_GET['submit_id']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td style='text-align:center;' class='tbl'>".$locale['441'].profile_link($data['user_id'], $data['user_name'], $data['user_status']).$locale['442'].$posted."</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td style='text-align:center;' class='tbl'><a href='".ADMIN."go.php?id=".$_GET['submit_id']."' target='_blank'>".$submit_criteria['link_name']."</a> - ".$submit_criteria['link_url']."</td>\n";
			echo "</tr>\n</table>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td class='tbl'>".$locale['443']."</td>\n";
			echo "<td class='tbl'><select name='link_category' class='textbox'>\n".$opts."</select></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td class='tbl'>".$locale['444']."</td>\n";
			echo "<td class='tbl'><input type='text' name='link_name' value='".$submit_criteria['link_name']."' class='textbox' style='width:300px' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td class='tbl'>".$locale['445']."</td>\n";
			echo "<td class='tbl'><input type='text' name='link_url' value='".$submit_criteria['link_url']."' class='textbox' style='width:300px' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td class='tbl'>".$locale['446']."</td>\n";
			echo "<td class='tbl'><input type='text' name='link_description' value='".$submit_criteria['link_description']."' class='textbox' style='width:300px' /></td>\n";
			echo "</tr>\n</table>\n";
			echo "<div style='text-align:center'><br />\n";
			echo $locale['447']."<br />\n";
			echo "<input type='submit' name='add' value='".$locale['448']."' class='button' />\n";
			echo "<input type='submit' name='delete' value='".$locale['449']."' class='button' /></div>\n";
			echo "</form>\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}
