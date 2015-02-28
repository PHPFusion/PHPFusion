<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusions.php
| Author: Nick Jones (Digitanium)
| Co-Author: Christian Damsgaard J�rgensen (PMM)
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

if (!checkrights("I") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/infusions.php";

$inf_title = ""; $inf_description = ""; $inf_version = ""; $inf_developer = ""; $inf_email = ""; $inf_weburl = "";
$inf_folder = ""; $inf_newtable = ""; $inf_insertdbrow = ""; $inf_droptable = ""; $inf_altertable = "";
$inf_deldbrow = ""; $inf_sitelink = "";

if (!isset($_POST['infuse']) && !isset($_POST['infusion']) && !isset($_GET['defuse'])) {
	$temp = opendir(INFUSIONS);
	$file_list = array();
	while ($folder = readdir($temp)) {
		if (!in_array($folder, array("..", "."))) {
			if (is_dir(INFUSIONS.$folder) && file_exists(INFUSIONS.$folder."/infusion.php")) {
				include INFUSIONS.$folder."/infusion.php";
				$result = dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder='".$inf_folder."'");
				if (dbrows($result)) {
					$data = dbarray($result);
					if (version_compare($inf_version, $data['inf_version'], ">")) {
						$file_list[] = "<option value='".$folder."' style='color:blue;'>".ucwords(str_replace("_", " ", $folder))."</option>\n";
					} else {
						$file_list[] = "<option value='".$folder."' style='color:green;'>".ucwords(str_replace("_", " ", $folder))."</option>\n";
					}
				} else {
					$file_list[] = "<option value='".$folder."' style='color:red;'>".ucwords(str_replace("_", " ", $folder))."</option>\n";
				}
				$inf_title = ""; $inf_description = ""; $inf_version = ""; $inf_developer = ""; $inf_email = ""; $inf_weburl = "";
				$inf_folder = ""; $inf_newtable = ""; $inf_insertdbrow = ""; $inf_droptable = ""; $inf_altertable = "";
				$inf_deldbrow = ""; $inf_sitelink = "";
			}
		}
	}
	closedir($temp);
	sort($file_list);

	opentable($locale['400']);
	echo "<div style='text-align:center'>\n";
	if (count($file_list)) {
		echo "<form name='infuseform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
		echo "<select name='infusion' class='textbox' style='width:200px;'>\n";
		for ($i = 0; $i < count($file_list); $i++) { echo $file_list[$i]; }
		echo "</select> <input type='submit' name='infuse' value='".$locale['401']."' class='button' />\n";
		if (isset($_GET['error'])) { echo "<br /><br />\n".($_GET['error'] == 1 ? $locale['402'] : $locale['403'])."<br /><br />\n"; }
		echo "<br /><br />\n".$locale['413']." <span style='color:red;'>".$locale['414']."</span> ::\n";
		echo "<span style='color:green;'>".$locale['415']."</span> ::\n";
		echo "<span style='color:blue;'>".$locale['416']."</span>\n";
		echo "</form>\n";
	} else {
		echo "<br />".$locale['417']."<br /><br />\n";
	}
	echo "</div>\n";
	closetable();
}

if (isset($_POST['infuse']) && isset($_POST['infusion'])) {
	$error = "";
	$infusion = stripinput($_POST['infusion']);
	if (file_exists(INFUSIONS.$infusion."/infusion.php")) {
		include INFUSIONS.$infusion."/infusion.php";
		$result = dbquery("SELECT inf_id, inf_version FROM ".DB_INFUSIONS." WHERE inf_folder='".$inf_folder."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			if ($inf_version > $data['inf_version']) {
				if (isset($inf_altertable) && is_array($inf_altertable) && count($inf_altertable)) {
					for ($i = 1; $i < (count($inf_altertable) + 1); $i++) {
						$result = dbquery("ALTER TABLE ".$inf_altertable[$i]);
					}
				}
				$result2 = dbquery("UPDATE ".DB_INFUSIONS." SET inf_version='".$inf_version."' WHERE inf_id='".$data['inf_id']."'");
			}
		} else {
			if (isset($inf_adminpanel) && is_array($inf_adminpanel) && count($inf_adminpanel)) {
				for ($i = 1; $i < (count($inf_adminpanel) + 1); $i++) {
					$error = 0;
					$inf_admin_image = ($inf_adminpanel[$i]['image'] ? $inf_adminpanel[$i]['image'] : "infusion_panel.gif");
					if (!dbcount("(admin_id)", DB_ADMIN, "admin_rights='".$inf_adminpanel[$i]['rights']."'")) {
						$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('".$inf_adminpanel[$i]['rights']."', '".$inf_admin_image."', '".$inf_adminpanel[$i]['title']."', '".INFUSIONS.$inf_folder."/".$inf_adminpanel[$i]['panel']."', '5')");
						$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
						while ($data = dbarray($result)) {
							$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".".$inf_adminpanel[$i]['rights']."' WHERE user_id='".$data['user_id']."'");
						}
					} else {
						$error = 1;
					}
				}
			}
			if (!$error) {
				if (isset($inf_sitelink) && is_array($inf_sitelink) && count($inf_sitelink)) {
					for ($i = 1; $i < (count($inf_sitelink) + 1); $i++) {
						$link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS),0) + 1;
						$result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order) VALUES ('".$inf_sitelink[$i]['title']."', '".str_replace("../","",INFUSIONS).$inf_folder."/".$inf_sitelink[$i]['url']."', '".$inf_sitelink[$i]['visibility']."', '1', '0', '".$link_order."')");
					}
				}
				if (isset($inf_newtable) && is_array($inf_newtable) && count($inf_newtable)) {
					for ($i = 1; $i < (count($inf_newtable) + 1); $i++) {
						$result = dbquery("CREATE TABLE ".$inf_newtable[$i]);
					}
				}
				if (isset($inf_insertdbrow) && is_array($inf_insertdbrow) && count($inf_insertdbrow)) {
					for ($i = 1; $i < (count($inf_insertdbrow) + 1); $i++) {
						$result = dbquery("INSERT INTO ".$inf_insertdbrow[$i]);
					}
				}
				$result = dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$inf_title."', '".$inf_folder."', '".$inf_version."')");
			}
		}
	}
	redirect(FUSION_SELF.$aidlink);
}


if (isset($_GET['defuse']) && isnum($_GET['defuse'])) {
	$result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS." WHERE inf_id='".$_GET['defuse']."'");
	$data = dbarray($result);
	include INFUSIONS.$data['inf_folder']."/infusion.php";
	if (isset($inf_adminpanel) && is_array($inf_adminpanel) && count($inf_adminpanel)) {
		for ($i = 1; $i < (count($inf_adminpanel) + 1); $i++) {
			$result = dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".($inf_adminpanel[$i]['rights'] ? $inf_adminpanel[$i]['rights'] : "IP")."' AND admin_link='".INFUSIONS.$inf_folder."/".$inf_adminpanel[$i]['panel']."' AND admin_page='5'");
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level>='102'");
			while ($data = dbarray($result)) {
				$user_rights = explode(".", $data['user_rights']);
				if (in_array($inf_adminpanel[$i]['rights'], $user_rights)) {
					$key = array_search($inf_adminpanel[$i]['rights'], $user_rights);
					unset($user_rights[$key]);
				}
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".implode(".", $user_rights)."' WHERE user_id='".$data['user_id']."'");
			}
		}
	}
	if (isset($inf_sitelink) && is_array($inf_sitelink) && count($inf_sitelink)) {
		for ($i = 1; $i < (count($inf_sitelink) + 1); $i++) {
			$result2 = dbquery("SELECT link_id, link_order FROM ".DB_SITE_LINKS." WHERE link_url='".str_replace("../", "", INFUSIONS).$inf_folder."/".$inf_sitelink[$i]['url']."'");
			if (dbrows($result2)) {
				$data2 = dbarray($result2);
				$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 WHERE link_order>'".$data2['link_order']."'");
				$result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$data2['link_id']."'");
			}
		}
	}
	if (isset($inf_newtable) && is_array($inf_newtable) && count($inf_newtable)) {
		for ($i = 1; $i < (count($inf_newtable) + 1); $i++) {
			$result = dbquery("DROP TABLE ".$inf_droptable[$i]);
		}
	}
	if (isset($inf_deldbrow) && is_array($inf_deldbrow) && count($inf_deldbrow)) {
		for ($i = 1; $i < (count($inf_deldbrow) + 1); $i++) {
			$result = dbquery("DELETE FROM ".$inf_deldbrow[$i]);
		}
	}
	$result = dbquery("DELETE FROM ".DB_INFUSIONS." WHERE inf_id='".$_GET['defuse']."'");
	redirect(FUSION_SELF.$aidlink);
}

$result = dbquery("SELECT inf_id, inf_title, inf_folder, inf_version FROM ".DB_INFUSIONS." ORDER BY inf_title");
if (dbrows($result)) {
	$i = 0;
	opentable($locale['404']);
	echo "<table cellpadding='0' cellspacing='1' width='500' class='tbl-border center'>\n<tr>\n";
	echo "<td class='tbl2'><strong>".$locale['405']."</strong></td>\n";
	echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['406']."</strong></td>\n";
	echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['407']."</strong></td>\n";
	echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['408']."</strong></td>\n";
	echo "<td align='center' width='1%' class='tbl2'> </td>\n";
	echo "</tr>\n";
	while ($data = dbarray($result)) {
		$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
		if (@include INFUSIONS.$data['inf_folder']."/infusion.php") {
			echo "<tr>\n";
			echo "<td class='".$row_color."'><span title='".$inf_description."' style='cursor:hand;'>".$data['inf_title']."</span></td>\n";
			echo "<td align='center' width='1%' class='".$row_color."' style='white-space:nowrap'>".$data['inf_version']."</td>\n";
			echo "<td align='center' width='1%' class='".$row_color."' style='white-space:nowrap'>".$inf_developer."</td>\n";
			echo "<td align='center' width='1%' class='".$row_color."' style='white-space:nowrap'><a href='mailto:".$inf_email."'>".$locale['409']."</a> / <a href='".$inf_weburl."' target='_blank' rel='nofollow'>".$locale['410']."</a></td>\n";
			echo "<td align='center' width='1%' class='".$row_color."' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;defuse=".$data['inf_id']."' onclick='return Defuse();'>".$locale['411']."</a></td>\n";
			echo "</tr>\n";
			$i++;
		}
		$inf_title = ""; $inf_description = ""; $inf_version = ""; $inf_developer = ""; $inf_email = ""; $inf_weburl = "";
		$inf_folder = ""; $inf_newtable = ""; $inf_insertdbrow = ""; $inf_droptable = ""; $inf_altertable = "";
		$inf_deldbrow = ""; $inf_sitelink = "";
	}
	echo "</table>\n";
	closetable();
}

echo "<script type='text/javascript'>
function Defuse() {
	return confirm('".$locale['412']."');
}
</script>\n";

require_once THEMES."templates/footer.php";
?>
