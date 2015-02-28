<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: custom_pages.php
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

if (!checkrights("CP") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header_mce.php";
include LOCALE.LOCALESET."admin/custom_pages.php";

if (isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1 && $settings['tinymce_enabled']) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	require_once INCLUDES."html_buttons_include.php";
}

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410']."<br />\n".$locale['412']."\n";
		$message .= "<a href='".BASEDIR."viewpage.php?page_id=".intval($_GET['pid'])."'>viewpage.php?page_id=".intval($_GET['pid'])."</a>\n";
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411']."<br />\n".$locale['412']."\n";
		$message .= "<a href='".BASEDIR."viewpage.php?page_id=".intval($_GET['pid'])."'>viewpage.php?page_id=".intval($_GET['pid'])."</a>\n";
	} elseif ($_GET['status'] == "del") {
		$message = $locale['413'];
	} elseif ($_GET['status'] == "pw") {
		$message = $locale['global_182'];
	}
	if ($message) {
		$message = "<div class='admin-message'>".$message."</div>";
		if ($_GET['status'] == "sn" || $_GET['status'] == "su") {
			echo $message;
		} else {
			echo "<div id='close-message'>".$message."</div>\n";
		}
	}
}

if (isset($_POST['save'])) {
	$page_title = stripinput($_POST['page_title']);
	$page_access = isnum($_POST['page_access']) ? $_POST['page_access'] : "0";
	$page_content = addslash($_POST['page_content']);
	$comments = isset($_POST['page_comments']) ? "1" : "0";
	$ratings = isset($_POST['page_ratings']) ? "1" : "0";
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		if (isset($_POST['page_id']) && isnum($_POST['page_id'])) {
			$result = dbquery(
				"UPDATE ".DB_CUSTOM_PAGES." SET
					page_title='".$page_title."',
					page_access='".$page_access."',
					page_content='".$page_content."',
					page_allow_comments='".$comments."',
					page_allow_ratings='".$ratings."'
				WHERE page_id='".$_POST['page_id']."'"
			);
		} else {
			$result = dbquery(
				"INSERT INTO ".DB_CUSTOM_PAGES." (
					page_title, page_access, page_content, page_allow_comments, page_allow_ratings
				) VALUES (
					'".$page_title."', '".$page_access."', '".$page_content."', '".$comments."', '".$ratings."'
				)"
			);
			$page_id = mysql_insert_id();
			if (isset($_POST['add_link'])) {
				$data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ORDER BY link_order DESC LIMIT 1"));
				$link_order = $data['link_order'] + 1;
				$result = dbquery(
					"INSERT INTO ".DB_SITE_LINKS." (
						link_name, link_url, link_visibility, link_position, link_window, link_order
					) VALUES (
						'".$page_title."', 'viewpage.php?page_id=".$page_id."', '".$page_access."', '1', '0', '".$link_order."'
					)"
				);
			}
		}
		set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		if (isset($_POST['page_id']) && isnum($_POST['page_id'])) {
			redirect(FUSION_SELF.$aidlink."&status=su&pid=".$_POST['page_id'], true);
		} else {
			redirect(FUSION_SELF.$aidlink."&status=sn&pid=".$page_id, true);
		}
	} else {
		redirect(FUSION_SELF.$aidlink."&status=pw");
	}
} else if (isset($_POST['delete']) && (isset($_POST['page_id']) && isnum($_POST['page_id']))) {
	$result = dbquery("DELETE FROM ".DB_CUSTOM_PAGES." WHERE page_id='".$_POST['page_id']."'");
	$result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_url='viewpage.php?page_id=".$_POST['page_id']."'");
	redirect(FUSION_SELF.$aidlink."&status=del");
} else {
	if (isset($_POST['preview'])) {
		$addlink = isset($_POST['add_link']) ? " checked='checked'" : "";
		$page_title = stripinput($_POST['page_title']);
		$page_access = $_POST['page_access'];
		$page_content = stripslash($_POST['page_content']);
		$comments = isset($_POST['page_comments']) ? " checked='checked'" : "";
		$ratings = isset($_POST['page_ratings']) ? " checked='checked'" : "";
		if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
			opentable($page_title);
			eval("?>".$page_content."<?php ");
			closetable();
			set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		} else {
			echo "<div id='close-message'><div class='admin-message'>".$locale['global_182']."</div></div>\n";
		}
		$page_content = phpentities($page_content);
	}
	$result = dbquery("SELECT page_id, page_title FROM ".DB_CUSTOM_PAGES." ORDER BY page_title");
	if (dbrows($result) != 0) {
		$editlist = ""; $sel = "";
		while ($data = dbarray($result)) {
			if (isset($_POST['page_id'])) { $sel = ($_POST['page_id'] == $data['page_id'] ? " selected='selected'" : ""); }
			$editlist .= "<option value='".$data['page_id']."'$sel>[".$data['page_id']."] ".$data['page_title']."</option>\n";
		}
		opentable($locale['402']);
		echo "<div style='text-align:center'>\n<form name='selectform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
		echo "<select name='page_id' class='textbox' style='width:200px;'>\n".$editlist."</select>\n";
		echo "<input type='submit' name='edit' value='".$locale['420']."' class='button' />\n";
		echo "<input type='submit' name='delete' value='".$locale['421']."' onclick='return DeletePage();' class='button' />\n";
		echo "</form>\n</div>\n";
		closetable();
	}

	if (isset($_POST['edit']) && (isset($_POST['page_id']) && isnum($_POST['page_id']))) {
		$result = dbquery(
			"SELECT page_id, page_title, page_access, page_content, page_allow_comments, page_allow_ratings
			FROM ".DB_CUSTOM_PAGES." WHERE page_id='".$_POST['page_id']."' LIMIT 1"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$page_title = $data['page_title'];
			$page_access = $data['page_access'];
			$page_content = phpentities(stripslashes($data['page_content']));
			$comments = ($data['page_allow_comments'] == "1" ? " checked='checked'" : "");
			$ratings = ($data['page_allow_ratings'] == "1" ? " checked='checked'" : "");
			$addlink = "";
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
	if (isset($_POST['page_id']) && isnum($_POST['page_id'])) {
		opentable($locale['401'].": [".$_POST['page_id']."] ".$page_title);
	} else {
		if (!isset($_POST['preview'])) {
			$page_title = "";
			$page_access = "";
			$page_content = "";
			$comments = " checked='checked'";
			$ratings = " checked='checked'";
			$addlink = "";
		}
		opentable($locale['400']);
	}
	$user_groups = getusergroups(); $access_opts = ""; $sel = "";
	while(list($key, $user_group) = each($user_groups)){
		$sel = ($page_access == $user_group['0'] ? " selected='selected'" : "");
		$access_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
	}
	echo "<form name='inputform' method='post' action='".FUSION_SELF.$aidlink."' onsubmit='return ValidateForm(this);'>\n";
	echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
	if ($settings['tinymce_enabled']) {
		echo "<td width='100' class='tbl'>".$locale['460']."</td>\n";
		echo "<td width='80%' class='tbl'><input type='button' id='tinymce_switch' name='tinymce_switch' value='".(!isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? $locale['461'] : $locale['462'])."' class='button' style='width:75px;' onclick=\"SetTinyMCE(".(!isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? 1 : 0).");\"/>\n</td>\n";
		echo "</tr>\n<tr>\n";
	}
	echo "<td width='100' class='tbl'>".$locale['422']."</td>\n";
	echo "<td width='80%' class='tbl'><input type='text' name='page_title' value='".$page_title."' class='textbox' style='width:250px;' autocomplete='off' />\n";
	echo "&nbsp;".$locale['423']."<select name='page_access' class='textbox' style='width:150px;'>\n".$access_opts."</select></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td valign='top' width='100' class='tbl'>".$locale['424']."</td>\n";
	echo "<td width='80%' class='tbl'><textarea name='page_content' cols='95' rows='15' class='textbox' style='width:98%'>".$page_content."</textarea></td>\n";
	echo "</tr>\n<tr>\n";
	if (!isset($_COOKIE['custom_pages_tinymce']) || !$_COOKIE['custom_pages_tinymce'] || !$settings['tinymce_enabled']) {
	echo "<td class='tbl'></td><td class='tbl'>\n";
	echo "<input type='button' value='".$locale['431']."' class='button' onclick=\"insertText('page_content', '&lt;!--PAGEBREAK--&gt;');\" />\n";
	echo "<input type='button' value='&lt;?php?&gt;' class='button' onclick=\"addText('page_content', '&lt;?php\\n', '\\n?&gt;');\" />\n";
	echo "<input type='button' value='&lt;p&gt;' class='button' onclick=\"addText('page_content', '&lt;p&gt;', '&lt;/p&gt;');\" />\n";
	echo "<input type='button' value='&lt;br /&gt;' class='button' onclick=\"insertText('page_content', '&lt;br /&gt;');\" />\n";
	echo display_html("inputform", "page_content", true)."</td>\n";
	echo "</tr>\n<tr>\n";
	}
	if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		echo "<td class='tbl'>".$locale['425']."</td>\n";
		echo "<td class='tbl'><input type='password' name='admin_password' value='".(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")."' class='textbox' style='width:150px;' autocomplete='off' /></td>\n";
		echo "</tr>\n<tr>\n";
	}
	echo "<td class='tbl'></td><td class='tbl'>\n";
	if (!isset($_POST['page_id']) || !isnum($_POST['page_id'])) {
		echo "<label><input type='checkbox' name='add_link' value='1'".$addlink." />  ".$locale['426']."</label><br />\n";
	}
	echo "<label><input type='checkbox' name='page_comments' value='1'".$comments." /> ".$locale['427']."</label>";
	if ($settings['comments_enabled'] == "0") {
		echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
	}
	echo "<br />\n";
	echo "<label><input type='checkbox' name='page_ratings' value='1'".$ratings." /> ".$locale['428']."</label>\n";
	if ($settings['ratings_enabled'] == "0") {
		echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
	}
	echo "</td>\n</tr>\n";
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" &&  $settings['ratings_enabled'] == "0") {
			$sys = $locale['457'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['455'];
		} else {
			$sys = $locale['456'];
		}
		echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
		echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['454'], $sys);
		echo "</td>\n</tr>";
	}
	echo "<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'><br />\n";
	if (isset($_POST['page_id']) && isnum($_POST['page_id'])) {
		echo "<input type='hidden' name='page_id' value='".$_POST['page_id']."' />\n";
	}
	echo "<input type='submit' name='preview' value='".$locale['429']."' class='button' />\n";
	echo "<input type='submit' name='save' value='".$locale['430']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();
	echo "<script type='text/javascript'>\n"."function DeletePage() {\n";
	echo "return confirm('".$locale['450']."');\n}"."\n";
	echo "function ValidateForm(frm) {\n"."if(frm.page_title.value=='') {\n";
	echo "alert('".$locale['451']."');\n"."return false;\n}\n";
	echo "if(frm.admin_password.value=='') {\n"."alert('".$locale['452']."');\n";
	echo "return false;\n}\n}\n";
	if ($settings['tinymce_enabled']) {
		echo "function SetTinyMCE(val) {\n";
		echo "now=new Date();\n"."now.setTime(now.getTime()+1000*60*60*24*365);\n";
		echo "expire=(now.toGMTString());\n"."document.cookie=\"custom_pages_tinymce=\"+escape(val)+\";expires=\"+expire;\n";
		echo "location.href='".FUSION_SELF.$aidlink."';\n"."}\n";
	}
	echo "</script>\n";
}

require_once THEMES."templates/footer.php";
?>