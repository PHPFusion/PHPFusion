<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: site_links.php
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
require_once __DIR__.'/../maincore.php';

if (!checkrights("SL") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/sitelinks.php";

add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui.js'></script>");
add_to_head("<link rel='stylesheet' href='".THEMES."templates/site_links.css' type='text/css' media='all' />");
add_to_head("<script type='text/javascript'>
    $(document).ready(function() {
        $('.site-links').sortable({
            handle : '.handle',
            placeholder: 'state-highlight',
            connectWith: '.connected',
            scroll: true,
            axis: 'y',
            update: function () {
                var ul = $(this),
                    order = ul.sortable('serialize'),
                    i = 0;
                $('#info').load('site_links_updater.php".$aidlink."&'+order);
                ul.find('.num').each(function(i) {
                    $(this).text(i+1);
                });
                ul.find('li').removeClass('tbl2').removeClass('tbl1');
                ul.find('li:odd').addClass('tbl2');
                ul.find('li:even').addClass('tbl1');
                window.setTimeout('closeDiv();',2500);
            }
        });
    });
    </script>");

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['412'];
	}
	if ($message) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}

if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
	$data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'"));
	$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order>'".$data['link_order']."'");
	$result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'");
	redirect(FUSION_SELF.$aidlink."&status=del");
} elseif (isset($_POST['savelink'])) {
	$link_name = stripinput($_POST['link_name']);
	$link_url = stripinput($_POST['link_url']);
	$link_language = stripinput($_POST['link_language']);
	$link_visibility = isnum($_POST['link_visibility']) ? $_POST['link_visibility'] : "0";
	$link_position = isset($_POST['link_position']) ? $_POST['link_position'] : "0";
	$link_window = isset($_POST['link_window']) ? $_POST['link_window'] : "0";
	$link_order = isnum($_POST['link_order']) ? $_POST['link_order'] : "";
	if (!defined('FUSION_NULL')) {
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
			$old_link_order = dbresult(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'"), 0);
			if ($link_order > $old_link_order) {
				$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order>'$old_link_order' AND link_order<='$link_order'");
			} elseif ($link_order < $old_link_order) {
				$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order+1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order<'$old_link_order' AND link_order>='$link_order'");
			}
			$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='$link_name', link_url='$link_url', link_visibility='$link_visibility', link_position='$link_position', link_window='$link_window', link_order='$link_order', link_language='$link_language' WHERE link_id='".$_GET['link_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=su");
		} else {
			if (!$link_order) {
				$link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : "").""), 0)+1;
			}
			$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order+1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order>='$link_order'");
			$result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('$link_name', '$link_url', '$link_visibility', '$link_position', '$link_window', '$link_order', '$link_language')");
			redirect(FUSION_SELF.$aidlink."&status=sn");
		}
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
	$result = dbquery("SELECT link_name, link_url, link_visibility, link_order, link_position, link_window, link_language FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		$link_name = $data['link_name'];
		$link_url = $data['link_url'];
		$link_language = $data['link_language'];
		$link_visibility = $data['link_visibility'];
		$link_order = $data['link_order'];
		$pos1_check = ($data['link_position'] == "1" ? " checked='checked'" : "");
		$pos2_check = ($data['link_position'] == "2" ? " checked='checked'" : "");
		$pos3_check = ($data['link_position'] == "3" ? " checked='checked'" : "");
		$window_check = ($data['link_window'] == "1" ? " checked='checked'" : "");
		$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;link_id=".$_GET['link_id'];
		opentable($locale['401']);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} else {
	$link_name = "";
	$link_url = "";
	$link_language = LANGUAGE;
	$link_visibility = "";
	$link_order = "";
	$pos1_check = " checked='checked'";
	$pos2_check = "";
	$pos3_check = "";
	$window_check = "";
	$formaction = FUSION_SELF.$aidlink;
	opentable($locale['400']);
}
$visibility_opts = ""; $sel = "";
$user_groups = getusergroups();
foreach($user_groups as $user_group) {
	$sel = ($link_visibility == $user_group['0'] ? " selected='selected'" : "");
	$visibility_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
}
require_once INCLUDES."bbcode_include.php";

echo "<form name='layoutform' method='post' action='".$formaction."'>\n";

echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tr>\n";
echo "<td class='tbl'><label for='link_name'>".$locale['420']."</label></td>\n";
echo "<td class='tbl'>\n";
echo "<input type='text' name='link_name' value='".$link_name."' maxlength='100' class='textbox' style='width:240px;' />\n";
echo "</td>\n</tr>\n";
if (multilang_table("SL")) { 
	echo "<tr><td class='tbl'>".$locale['global_ML100']."</td>\n";
	$opts = get_available_languages_list($selected_language = "$link_language");
	echo "<td class='tbl'>
	<select name='link_language' class='textbox' style='width:200px;'>".$opts."</select></td>\n"; 
	echo "</tr>\n"; 
} else {
	echo "<input type='hidden' name='link_language' value='".$link_language."' />\n";	
}
echo "<tr>\n";
echo "<td class='tbl'></td>\n<td class='tbl'>";
echo display_bbcodes("240px;", "link_name", "layoutform", "b|i|u|color|img")."\n";
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl'><label for='link_url'>".$locale['421']."</label></td>\n";
echo "<td class='tbl'><input type='text' name='link_url' value='".$link_url."' maxlength='200' class='textbox' style='width:240px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl'>".$locale['422']."</td>\n";
echo "<td class='tbl'><select name='link_visibility' class='textbox' style='width:150px;'>\n".$visibility_opts."</select>\n";
echo $locale['423']."\n<input type='text' name='link_order'  value='".$link_order."' maxlength='3' class='textbox' style='width:40px;' />";
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' class='tbl'><strong>".$locale['424']."</strong></td>\n";
echo "<td class='tbl'><label><input type='radio' name='link_position' value='1'".$pos1_check." /> ".$locale['425']."</label><br />\n";
echo "<label><input type='radio' name='link_position' value='2'".$pos2_check." /> ".$locale['426']."</label><br />\n";
echo "<label><input type='radio' name='link_position' value='3'".$pos3_check." /> ".$locale['427']."</label><hr />\n";
echo "<label><input type='checkbox' name='link_window' value='1'".$window_check." /> ".$locale['428']."</label></td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'>\n";
echo "<input type='submit' name='savelink' value='".$locale['429']."' class='bt-primary' /></td>\n";
echo "</td>\n</tr>\n</table></form>\n";
closetable();

opentable($locale['402']);
echo "<div id='info'></div>\n";
echo "<div style='width:100%;' class='panels tbl-border center floatfix'><div class='tbl2'>\n";
echo "<div style='float:left; padding-left:30px;'><strong>".$locale['440']."</strong></div>\n";
echo "<div style='float:right; width:100px; text-align:center;'><strong>".$locale['443']."</strong></div>\n";
echo "<div style='float:right; width:15%; text-align:center;'><strong>".$locale['442']."</strong></div>\n";
echo "<div style='float:right; width:15%; text-align:center;'><strong>".$locale['441']."</strong></div>\n";
echo "<div style='clear:both;'></div>\n</div>\n";
echo "<ul id='site-links' style='list-style: none;' class='list-group site-links connected'>\n";
$result = dbquery("SELECT link_id, link_name, link_url, link_visibility, link_order, link_position, link_language FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : "")." ORDER BY link_order");
if (dbrows($result)) {
	$i = 0;
	while ($data = dbarray($result)) {
		$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
		echo "<li id='listItem_".$data['link_id']."' class='list-group-item ".$row_color."'>\n";
		echo "<div style='float:left; width:30px;'><img src='".IMAGES."arrow.png' alt='move' class='handle' /></div>\n";
		echo "<div style='float:left;'>\n";
		if ($data['link_position'] == 3) echo "<i>";
		if ($data['link_name'] != "---" && $data['link_url'] == "---") {
			echo "<strong>".parseubb($data['link_name'], "b|i|u|color|img")."</strong>\n";
		} else if ($data['link_name'] == "---" && $data['link_url'] == "---") {
			echo "<hr />\n";
		} else {
			if (strstr($data['link_url'], "http://") || strstr($data['link_url'], "https://")) {
				echo "<a href='".$data['link_url']."'>".parseubb($data['link_name'], "b|i|u|color|img")."</a>\n";
			} else {
				echo "<a href='".BASEDIR.$data['link_url']."'>".parseubb($data['link_name'], "b|i|u|color|img")."</a>\n";
			}
		}
		if ($data['link_position'] == 3) echo "</i>";
		echo "</div>\n";
		echo "<div style='float:right; width:100px; text-align:center;'>";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;link_id=".$data['link_id']."'>".$locale['444']."</a> -\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;link_id=".$data['link_id']."' onclick=\"return confirm('".$locale['460']."');\">".$locale['445']."</a>\n";
		echo "</div>\n";
		echo "<div class='num' style='float:right; width:15%; text-align:center;'>".$data['link_order']."</div>\n";
		echo "<div style='float:right; width:15%; text-align:center;'>".getgroupname($data['link_visibility'])."</div>\n";
		echo "<div style='clear:both;'></div>\n";
		echo "</li>\n";
		$i++;
	}
	echo "</ul>\n</div>";
} else {
	echo "<div style='text-align:center;margin-top:5px'>".$locale['446']."</div>\n";
}
closetable();

require_once THEMES."templates/footer.php";
?>
