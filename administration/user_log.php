<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_log.php
| Author: gh0st2k
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

if (!checkrights("UL") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/user_log.php";

if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }

// Start $_GET Vars
// Set default values
$dbOrder = "ORDER BY userlog_timestamp DESC";
$dbWhere = "";
$dbWhereCount = "";
$getString = $aidlink;
$orderby = "userlog_timestamp";
$expr = "DESC";
$user = "";
$userField = "";

$orderbyArray = array($locale['102'] =>"userlog_timestamp", $locale['103'] => "user_name", $locale['104'] =>"userlog_field" );
$exprArray = array("DESC", "ASC");

if (isset($_GET) && !empty($_GET)) {
	if (isset($_GET['orderby']) && in_array($_GET['orderby'], $orderbyArray)) {
		$orderby = $_GET['orderby'];
		$dbOrder = "ORDER BY ".$_GET['orderby'];
		if (isset($_GET['expr']) && in_array($_GET['expr'], $exprArray)) {
			$expr = $_GET['expr'];
			$dbOrder .= " ".$_GET['expr'];
		}
	}

	if (isset($_GET['user'])) {
		if (isnum($_GET['user'])) {
			$user = $_GET['user'];
			$dbWhere = "userlog_user_id='".$_GET['user']."'";
		} elseif ($_GET['user'] != "") {
			$user = trim(stripinput($_GET['user']));
			$dbWhere = "user_name LIKE '".$user."%'";
		}
	}

	if (isset($_GET['userField']) && $_GET['userField'] != "---" && $_GET['userField'] != "") {
		$userField = trim(stripinput($_GET['userField']));
		$dbWhere .= ($dbWhere != "" ? " AND userlog_field='".$userField."'" : "userlog_field='".$userField."'");
	}
	$dbWhereCount = $dbWhere;
	$dbWhere = ($dbWhere != "" ? "WHERE ".$dbWhere : "");
	// build get string
	$getString .= "&amp;orderby=".$orderby."&amp;expr=".$expr."&amp;user=".$user."&amp;userField=".$userField;
}
// End $_GET Vars


if (isset($_POST['delete']) && isnum($_POST['delete'])) {
    $time = time()- $_POST['delete']*24*60*60;
    $result = dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_timestamp<".$time);
	echo "<div id='close-message'><div class='admin-message'>".sprintf($locale['118'], $_POST['delete'])."</div></div>\n";
}

if (isset($_GET['delete']) && isnum($_GET['delete'])) {
	$result = dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_id='".$_GET['delete']."'");
	echo "<div id='close-message'><div class='admin-message'>".$locale['119']."</div></div>\n";
}

function orderbyOptions ($select) {
	global $orderbyArray;
    $options = "";
    foreach ($orderbyArray AS $key => $value) {
        $sel = ($select == $value ? "selected='selected'" : "");
        $options .= "<option value='".$value."' ".$sel.">".$key."</option>\n";
    }
    return $options;
}

function exprOptions ($select) {
	global $exprArray;
	$options = "";
	foreach ($exprArray AS $value) {
		$sel = ($select == $value ? "selected='selected'" : "");
        $options .= "<option ".$sel.">".$value."</option>\n";
	}
	return $options;
}

function userFieldOptions($select) {
	$options = "<option>---</option>\n";
	$options .= "<option ".($select == "user_name" ? "selected='selected'" : "").">user_name</option>\n";
	$options .= "<option ".($select == "user_email" ? "selected='selected'" : "").">user_email</option>\n";
	$result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS." WHERE field_log='1'");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			$options .= "<option ".($select == $data['field_name'] ? "selected='selected'" : "").">".$data['field_name']."</option>\n";
		}
	}
	return $options;
}

opentable($locale['100']);
echo "<form action='".FUSION_SELF."' method='get'>\n";
echo "<input type='hidden' name='aid' value='".iAUTH."' />\n";
echo "<table cellpadding='0' cellspacing='1' class='tbl-border center' style='width:400px;'>\n";
echo "<tr>\n";
echo "<td class='tbl1' align='left'>".$locale['107']."</td>\n";
echo "<td class='tbl1' align='right'>\n";
echo "<select name='orderby' size='1' class='textbox' style='width:150px;'>".orderbyOptions($orderby)."</select>\n";
echo " <select name='expr' size='1' class='textbox' style='width:100px;'>".exprOptions($expr)."</select>\n";
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl1' align='left'>".$locale['108']."</td>\n";
echo "<td class='tbl1' align='right'><input type='text' name='user' value='".$user."' class='textbox' style='width:252px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['115']."</td>";
echo "<td class='tbl1' align='right'><select name='userField' size='1' class='textbox' style='width:254px;'>".userFieldOptions($userField)."</select></td>";
echo "</tr>\n<tr>\n";
echo "<td class='tbl' align='left'></td>\n<td class='tbl' align='right'><input type='submit' value=' ".$locale['109']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
echo "<br />";

$result = dbquery("SELECT SQL_CALC_FOUND_ROWS userlog_id, userlog_user_id, userlog_field, userlog_value_old, userlog_value_new, userlog_timestamp, user_name, user_status
				   FROM ".DB_USER_LOG."
				   LEFT JOIN ".DB_USERS." ON userlog_user_id=user_id
				   ".$dbWhere."
				   ".$dbOrder."
				   LIMIT ".$_GET['rowstart'].",20");
$rows = dbresult(dbquery("SELECT FOUND_ROWS()"), 0);
if (dbrows($result)) {
    echo "<table cellpadding='0' cellspacing='1' class='tbl-border center' style='width: 700px;'>\n";
    echo "<tr>\n";
    echo "<td class='tbl2' style='white-space:nowrap; width:100px;'>".$locale['102']."</td>\n";
    echo "<td class='tbl2' style='white-space:nowrap; width:150px;'>".$locale['103']."</td>\n";
    echo "<td class='tbl2' style='white-space:nowrap; width:140px;'>".$locale['104']."</td>\n";
    echo "<td class='tbl2' style='white-space:nowrap; width:160px;'>".$locale['105']."</td>\n";
    echo "<td class='tbl2' style='white-space:nowrap; width:160px;'>".$locale['106']."</td>\n";
	echo "<td class='tbl2' style='white-space:nowrap; width:160px;'>".$locale['117']."</td>\n";
    echo "</tr>\n";
    $i = 1;
    while ($data = dbarray($result)) {
        $class = ($i % 2 ? "tbl1" : "tbl2");
        echo "<tr>\n";
        echo "<td class='".$class."'>".showdate("shortdate", $data['userlog_timestamp'])."</td>\n";
        echo "<td class='".$class."'>".profile_link($data['userlog_user_id'], $data['user_name'], $data['user_status'])."</td>\n";
        echo "<td class='".$class."'>".$data['userlog_field']."</td>\n";
        echo "<td class='".$class."'>".trimlink($data['userlog_value_old'], 100)."</td>\n";
        echo "<td class='".$class."'>".trimlink($data['userlog_value_new'], 100)."</td>\n";
		echo "<td class='".$class."'><a href='".FUSION_SELF.$getString."&amp;delete=".$data['userlog_id']."'>".$locale['116']."</a></td>\n";
        echo "</tr>\n";
        $i++;
    }
    echo "</table>\n";
} else {
    echo "<center>".$locale['112']."</center>\n";
}

if ($rows > 20) echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20,$rows, 3, FUSION_SELF.$getString."&amp;")."\n</div>\n";

echo "<br />";
echo "<form action='".FUSION_SELF.$aidlink."' method='post'>\n";
echo "<table cellpadding='0' cellspacing='1' class='tbl-border center' style='width: 400px;'>\n";
echo "<tr>\n";
echo "<td class='tbl' width='50%'>".$locale['110'].":</td>\n";
echo "<td class='tbl1' align='right'>\n";
echo "<input type='text' name='delete' value='90' maxlength='3' class='textbox' style='width:35px;' /> ".$locale['111'];
echo " <input type='submit' value='".$locale['109']."' class='button' />";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";

closetable();

require_once THEMES."templates/footer.php";
?>