<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: polls.php
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
require_once "../../maincore.php";
pageAccess('PO');

require_once THEMES."templates/admin_header.php";
if (file_exists(INFUSIONS."member_poll_panel/locale/".LOCALESET."member_poll_panel_admin.php")) {
	include INFUSIONS."member_poll_panel/locale/".LOCALESET."member_poll_panel_admin.php";
} else {
	include INFUSIONS."member_poll_panel/locale/English/member_poll_panel_admin.php";
}

add_breadcrumb(array('link'=>ADMIN.'polls.php'.$aidlink, 'title'=>$locale['439c']));

if (isset($_GET['poll_id']) && !isnum($_GET['poll_id'])) {
	redirect(FUSION_SELF);
}

if (isset($_GET['status'])) {
    switch($_GET['status']) {
        case "sn":
            addNotice("success", $locale['410']);
            break;
        case "su":
            addNotice("success", $locale['411']);
            break;
        case "del":
            addNotice("success", $locale['412']);
            break;
    }
}

if (isset($_POST['save'])) {
	$poll_title = form_sanitizer($_POST['poll_title'], '', 'poll_title');
	$poll_language = form_sanitizer($_POST['poll_language'], "", "poll_language");
	$poll_option = array();
	foreach ($_POST['poll_option'] as $key => $value) {
		$poll_option[$key] = trim(stripinput($_POST['poll_option'][$key]));
	}
    $poll_option = array_filter($poll_option);
	if (isset($_GET['poll_id']) && isnum($_GET['poll_id']) && $defender->safe()) {
		if ($poll_title && $poll_option) {
			$ended = (isset($_POST['close']) ? time() : 0);
			$values = "";
            $i = 1;
            for($x = 0; $x<=8; $x++) {
                $values .= ", poll_opt_".$i."='".(!empty($poll_option[$i]) ? $poll_option[$i] : "")."'";
                $i++;
            }
            dbquery("UPDATE ".DB_POLLS." SET poll_title='".$poll_title."' ".$values.", poll_ended='".$ended."' WHERE poll_id='".$_GET['poll_id']."'");
			redirect(FUSION_SELF.$aidlink."&amp;status=su");
		} else {
			$defender->stop();
			addNotice("danger", $locale['439b']);
		}
	} elseif ($defender->safe()) {
		if ($poll_title && $poll_option) {
			$values = "";
			for ($i = 0; $i < 10; $i++) {
				$values .= ", '".(isset($poll_option[$i]) ? $poll_option[$i] : "")."'";
			}
			dbquery("UPDATE ".DB_POLLS." SET poll_ended='".time()."' WHERE poll_ended='0'");
			dbquery("INSERT INTO ".DB_POLLS." (poll_title, poll_opt_0, poll_opt_1, poll_opt_2, poll_opt_3, poll_opt_4, poll_opt_5, poll_opt_6, poll_opt_7, poll_opt_8, poll_opt_9, poll_started, poll_ended, poll_language) VALUES ('".$poll_title."' ".$values.", '".time()."', '0', '".$poll_language."')");
			redirect(FUSION_SELF.$aidlink."&amp;status=sn");
		} else {
			$defender->stop();
			addNotice("danger", $locale['439b']);
		}
	}
} else if (isset($_POST['delete']) && (isset($_POST['poll_id']) && isnum($_POST['poll_id']))) {
	$result = dbcount("(poll_id)", DB_POLLS, "poll_id='".$_POST['poll_id']."'");
	if (!empty($result)) $result = dbquery("DELETE FROM ".DB_POLLS." WHERE poll_id='".$_POST['poll_id']."'");
	redirect(FUSION_SELF.$aidlink."&amp;status=del");
}

if (isset($_POST['preview'])) {
	$poll = "";
	$i = 0;
	$poll_title = stripinput($_POST['poll_title']);
	$poll_language = stripinput($_POST['poll_language']);
	foreach ($_POST['poll_option'] as $item) {
		$poll_option[$i] = trim(stripinput($item)) ? : $locale['439'];
		$poll .= "<label><input type='radio' name='option[]' /> ".$poll_option[$i]."</label><br /><br />\n";
		$i++;
	}
	$opt_count = (isset($_POST['opt_count']) && $_POST['opt_count'] != 10 ? count($poll_option) : $_POST['opt_count']);
	if ($poll_title) {
		opentable($locale['403']);
		echo "<table cellpadding='0' cellspacing='0' class='table table-responsive'>\n<tr>\n";
		echo "<td class='tbl'><strong>".$poll_title."</strong><br /><br />\n".$poll."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td align='center' class='tbl'>\n";
		echo form_button('blank', $locale['430'], $locale['430'], array('type' => 'button', 'class' => 'btn-primary btn-block'));
		echo "</td>\n</tr>\n</table>\n";
		closetable();
	}
}
$editlist = array();
$result = dbquery("SELECT poll_id, poll_title, poll_language FROM ".DB_POLLS." ".(multilang_table("PO") ? "WHERE poll_language='".LANGUAGE."'" : "")." ORDER BY poll_id DESC");
if (dbrows($result)) {
	while ($data = dbarray($result)) {
		$editlist[$data['poll_id']] = $data['poll_title'];
	}
	opentable($locale['402']);
	echo openform('editform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
	echo form_select('poll_id', '', '', array('options' => $editlist,
		'placeholder' => $locale['choose'],
		'class' => 'pull-left m-r-10'));
	echo form_button('edit', $locale['421'], $locale['421'], array('class' => 'btn-primary m-r-10 pull-left'));
	echo form_button('delete', $locale['422'], $locale['422'], array('class' => 'btn-primary pull-left'));
	echo closeform();
	closetable();
}
if (isset($_POST['edit']) && (isset($_POST['poll_id']) && isnum($_POST['poll_id']))) {
	$_GET['poll_id'] = $_POST['poll_id'];
	$data = dbarray(dbquery("SELECT poll_title, poll_opt_0, poll_opt_1, poll_opt_2, poll_opt_3, poll_opt_4, poll_opt_5, poll_opt_6, poll_opt_7, poll_opt_8, poll_opt_9, poll_started, poll_ended, poll_language FROM ".DB_POLLS." WHERE poll_id='".$_POST['poll_id']."'"));
	$poll_title = $data['poll_title'];
	$poll_language = $data['poll_language'];
	$poll_option = array();
	for ($i = 0; $i <= 9; $i++) {
		if ($data["poll_opt_".$i]) {
			$poll_option[$i] = $data["poll_opt_".$i];
		}
	}
	$opt_count = count($poll_option);
	$poll_started = $data['poll_started'];
	$_GET['poll_ended'] = $data['poll_ended'];
}
if (isset($_POST['addoption'])) {
	$poll_title = stripinput($_POST['poll_title']);
	if (isset($_POST['poll_option']) && is_array($_POST['poll_option'])) {
		foreach ($_POST['poll_option'] as $key => $value) {
			$poll_option[$key] = stripinput($_POST['poll_option'][$key]);
		}
		$opt_count = ($_POST['opt_count'] != 10 ? count($poll_option)+1 : $_POST['opt_count']);
	} else {
		$poll_option[0] = "";
		$opt_count = 1;
	}
}
$i = 0;
$opt = 1;
$poll_title = isset($poll_title) ? $poll_title : "";
$poll_language = isset($poll_language) ? $poll_language : LANGUAGE;
$opt_count = isset($opt_count) ? $opt_count : 2;
if (isset($poll_id)) $poll_ended = isset($poll_ended) ? $poll_ended : 0;
opentable((isset($_GET['poll_id']) ? $locale['401'] : $locale['400']));
$formaction = "".FUSION_SELF.$aidlink.(isset($_GET['poll_id']) ? "&amp;poll_id=".$_GET['poll_id']."&amp;poll_ended=".$_GET['poll_ended'] : "")."";
echo openform('pollform', 'post', $formaction, array('max_tokens' => 1, 'notice' => 0));
echo "<table cellpadding='0' cellspacing='0' class='table table-responsive'>\n<tr>\n";
echo "<td width='80' class='tbl'><label for='poll_title'>".$locale['431']."</label></td>\n";
echo "<td class='tbl'>\n";
echo form_text('poll_title', '', $poll_title, array('required' => 1, 'error_text' => $locale['439a']));
echo "</td>\n</tr>\n";
if (multilang_table("PO")) {
	echo "<tr><td class='tbl'><label for='poll_language'>".$locale['global_ML100']."</label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_select('poll_language', '', $poll_language, array('options' => $language_opts,
		'placeholder' => $locale['choose']));
	echo "</td>\n</tr>\n";
} else {
	echo form_hidden('poll_language', '', $poll_language);
}
while ($i != $opt_count) {
	$poll_opt = isset($poll_option[$i]) ? $poll_option[$i] : "";
	echo "<tr>\n<td width='80' class='tbl'><label for='poll_option[$i]'>".$locale['432']."$opt </label></td>\n";
	echo "<td class='tbl'>\n";
	echo form_text("poll_option[".$i."]", '',  $poll_opt);
	// <input type='text' name='poll_option[".$i."]' value='".$poll_opt."' class='textbox' style='width:200px' /></td>\n</tr>\n";
	$i++;
	$opt++;
	echo "</td></tr>\n";
}
echo "</table>\n";
echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tr>\n";
echo "<td align='center' class='tbl'><br />\n";
if (isset($_GET['poll_id']) && !$_GET['poll_ended']) {
	echo "<input type='checkbox' name='close' value='yes' />".$locale['433']."<br /><br />\n";
}
if (!isset($_GET['poll_id']) || (isset($_GET['poll_id']) && !$_GET['poll_ended'])) {
	echo form_hidden('opt_count', '', $opt_count);
	echo "<input type='hidden' name='opt_count' value='".$opt_count."' />\n";
	echo form_button('addoption', $locale['436'], $locale['436'], array('class' => 'btn-primary m-r-10'));
	echo form_button('preview', $locale['437'], $locale['437'], array('class' => 'btn-primary m-r-10'));
	echo form_button('save', $locale['438'], $locale['438'], array('class' => 'btn-primary'));
} else {
	echo $locale['434'].showdate("shortdate", $poll_started)."<br />\n";
	echo $locale['435'].showdate("shortdate", $_GET['poll_ended'])."<br />\n";
}
echo "</td>\n</tr>\n</table>\n</form>\n";
closetable();


require_once THEMES."templates/footer.php";

