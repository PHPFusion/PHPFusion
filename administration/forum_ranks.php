<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_ranks.php
| Author: Nick Jones (Digitanium)
| Co-Author: Robert Gaudyn (Wooya)
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

if (!checkrights("FR") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/forum_ranks.php";

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['412'];
	} elseif ($_GET['status'] == "se") {
		$message = $locale['413'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if ($settings['forum_ranks']) {
	if (isset($_POST['save_rank'])) {
		$rank_title = stripinput($_POST['rank_title']);
		$rank_image = stripinput($_POST['rank_image']);
		$rank_posts = isnum($_POST['rank_posts']) ? $_POST['rank_posts'] : 0;
		$rank_type = isnum($_POST['rank_type']) ? $_POST['rank_type'] : 0;
		$rank_apply_normal = isset($_POST['rank_apply_normal']) && isnum($_POST['rank_apply_normal']) ? $_POST['rank_apply_normal'] : 101;
		$rank_apply_special = isset($_POST['rank_apply_special']) && isnum($_POST['rank_apply_special']) ? $_POST['rank_apply_special'] : 1;
		$rank_apply = $rank_type == 2 ? $rank_apply_special : $rank_apply_normal ;
		if ($rank_title) {
			if (isset($_GET['rank_id']) && isnum($_GET['rank_id'])) {
				$data = dbarray(dbquery("SELECT rank_apply FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['rank_id']."'"));
				if (($rank_apply > 101 && $rank_apply != $data['rank_apply']) && (dbcount("(rank_id)", DB_FORUM_RANKS, "rank_id!='".$_GET['rank_id']."' AND rank_apply='".$rank_apply."'"))) {
					redirect(FUSION_SELF.$aidlink."&status=se");
				} else {
					$result = dbquery("UPDATE ".DB_FORUM_RANKS." SET rank_title='".$rank_title."', rank_image='".$rank_image."', rank_posts='".$rank_posts."', rank_type='".$rank_type."', rank_apply='".$rank_apply."' WHERE rank_id='".$_GET['rank_id']."'");
					redirect(FUSION_SELF.$aidlink."&status=su");
				}
			} else {
				if ($rank_apply > 101 && dbcount("(rank_id)", DB_FORUM_RANKS, "rank_apply='".$rank_apply."'")) {
					redirect(FUSION_SELF.$aidlink."&status=se");
				} else {
					$result = dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply) VALUES ('$rank_title', '$rank_image', '$rank_posts', '$rank_type', '$rank_apply')");
					redirect(FUSION_SELF.$aidlink."&status=sn");
				}
			}
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else if (isset($_GET['delete']) && isnum($_GET['delete'])) {
		$result = dbquery("DELETE FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['delete']."'");
		redirect(FUSION_SELF.$aidlink."&status=del");
	}

	if (isset($_GET['rank_id']) && isnum($_GET['rank_id'])) {
		$result = dbquery("SELECT rank_id, rank_title, rank_image, rank_posts, rank_type, rank_apply FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['rank_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$rank_title = $data['rank_title'];
			$rank_image = $data['rank_image'];
			$rank_posts = $data['rank_posts'];
			$rank_type = $data['rank_type'];
			$rank_apply = $data['rank_apply'];
			$form_action = FUSION_SELF.$aidlink."&amp;rank_id=".$_GET['rank_id'];
			opentable($locale['401']);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$rank_title = "";
		$rank_image = "";
		$rank_posts = "0";
		$rank_type 	= "2";
		$rank_apply = "";
		$form_action = FUSION_SELF.$aidlink;
		opentable($locale['400']);
	}
	echo "<form name='rank_form' method='post' action='".$form_action."'>\n";
	echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
	echo "<td class='tbl'>".$locale['420']."</td>\n";
	echo "<td class='tbl'><input type='text' name='rank_title' value='".$rank_title."' class='textbox' style='width:150px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>".$locale['421']."</td>\n";
	echo "<td class='tbl'><select name='rank_image' class='textbox' style='width:150px;'>\n";
	$image_files = makefilelist(IMAGES."ranks", ".|..|index.php|.svn|.DS_Store", true);
	echo makefileopts($image_files, $rank_image)."</select></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>".$locale['429']."</td>\n";
	echo "<td class='tbl'>\n";
	echo "<label><input type='radio' name='rank_type' value='2'".($rank_type == 2 ? " checked='checked'" : "")." /> ".$locale['429a']."</label>\n";
	echo "<label><input type='radio' name='rank_type' value='1'".($rank_type == 1 ? " checked='checked'" : "")." /> ".$locale['429b']."</label>\n";
	echo "<label><input type='radio' name='rank_type' value='0'".($rank_type == 0 ? " checked='checked'" : "")." /> ".$locale['429c']."</label>\n";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>".$locale['422']."</td>\n";
	echo "<td class='tbl'><input type='text' id='rank_posts' name='rank_posts' value='".$rank_posts."' class='textbox' style='width:30px;'".($rank_type != 0 ? " readonly='readonly'" : "")." /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>".$locale['423']."</td>\n<td class='tbl'>";
	// Normal Select
	echo "<span id='select_normal'".($rank_type == 2 ? " style='display:none;'" : "")."><select name='rank_apply_normal' class='textbox' style='width:150px;'>\n";
	echo "<option value='101'".($rank_apply == 101 ? " selected='selected'" : "").">".$locale['424']."</option>\n";
	echo "<option value='104'".($rank_apply == 104 ? " selected='selected'" : "").">".$locale['425']."</option>\n";
	echo "<option value='102'".($rank_apply == 102 ? " selected='selected'" : "").">".$locale['426']."</option>\n";
	echo "<option value='103'".($rank_apply == 103 ? " selected='selected'" : "").">".$locale['427']."</option>\n";
	echo "</select></span>\n";
	// Special Select
	$groups_arr = getusergroups(); $groups_except = array(0, 101, 102, 103);
	echo "<span id='select_special'".($rank_type != 2 ? " style='display:none;'" : "")."><select name='rank_apply_special' class='textbox' style='width:150px;'>\n";
	foreach ($groups_arr as $group) {
		if (!in_array($group[0], $groups_except)) {
			echo "<option value='".$group[0]."'".($rank_apply == $group[0] ? " selected='selected'" : "").">".$group[1]."</option>\n";
		}
	}
	echo "</select></span>\n";
		echo "</td>\n</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo "<input class='button' type='submit' name='save_rank' value='".$locale['428']."' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();

	opentable($locale['402']);
	$result = dbquery("SELECT rank_id, rank_title, rank_image, rank_posts, rank_type, rank_apply FROM ".DB_FORUM_RANKS." ORDER BY rank_type DESC, rank_apply DESC, rank_posts");
	if (dbrows($result)) {
		echo "<table cellpadding='0' cellspacing='1' width='500' class='tbl-border center'>\n<tr>\n";
		echo "<td class='tbl2'><strong>".$locale['430']."</strong></td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['431']."</strong></td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['432']."</strong></td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['438']."</strong></td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['434']."</strong></td>\n";
		echo "</tr>\n";
		$i = 0;
		while ($data = dbarray($result)) {
			$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
			echo "<tr>\n";
			echo "<td class='".$row_color."'>".$data['rank_title']."</td>\n";
			echo "<td width='1%' class='".$row_color."' style='white-space:nowrap'>".($data['rank_apply'] == 104 ? $locale['425'] : getgroupname($data['rank_apply']))."</td>\n";
			echo "<td width='1%' class='".$row_color."' style='white-space:nowrap'><img src='".IMAGES."ranks/".$data['rank_image']."' alt='' style='border:0;' /></td>\n";
			echo "<td width='1%' class='".$row_color."' style='white-space:nowrap'>";
			if ($data['rank_type'] == 0) {
				echo $data['rank_posts'];
			} elseif ($data['rank_type'] == 1) {
				echo $locale['429b'];
			} else {
				echo $locale['429a'];
			}
			echo "</td>\n<td width='1%' class='".$row_color."' style='white-space:nowrap'>";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;rank_id=".$data['rank_id']."'>".$locale['435']."</a> ::\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;delete=".$data['rank_id']."'>".$locale['436']."</a></td>\n</tr>\n";
			$i++;
		}
		echo "</table>";
	} else {
		echo "<div style='text-align:center'>".$locale['437']."</div>\n";
	}
	closetable();
} else {
	opentable($locale['403']);
	echo "<div style='text-align:center'>\n".sprintf($locale['450'], "<a href='settings_forum.php".$aidlink."'>".$locale['451']."</a>")."</div>\n";
	closetable();
}

echo "<script language='JavaScript' type='text/javascript'>
jQuery(function(){
	jQuery('input:radio[name=rank_type]').change(function() {
		var val = jQuery('input:radio[name=rank_type]:checked').val(),
			special = jQuery('#select_special'),
			normal = jQuery('#select_normal'),
			posts = jQuery('#rank_posts');
		if (val == 2) {
			special.show();
			normal.hide();
			posts.attr('readonly', 'readonly');
		} else {
			if (val == 1) {
				posts.attr('readonly', 'readonly');
			} else {
				posts.removeAttr('readonly');
			}
			special.hide();
			normal.show();
		}
	});
});
</script>";

require_once THEMES."templates/footer.php";
?>
