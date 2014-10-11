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

if (!checkrights("I") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/infusions.php";

$inf_title = "";
$inf_description = "";
$inf_version = "";
$inf_developer = "";
$inf_email = "";
$inf_weburl = "";
$inf_folder = "";
$inf_newtable = "";
$inf_insertdbrow = "";
$inf_droptable = "";
$inf_altertable = "";
$inf_deldbrow = "";
$inf_sitelink = "";
// changed from id defusing to folder defusing, because inf_folder is also unique.
if (!isset($_POST['infuse']) && !isset($_POST['infusion']) && !isset($_GET['defuse'])) {
	$temp = opendir(INFUSIONS);
	$inf = array();
	while ($folder = readdir($temp)) {
		if (!in_array($folder, array("..", "."))) {
			if (is_dir(INFUSIONS.$folder) && file_exists(INFUSIONS.$folder."/infusion.php")) {
				include INFUSIONS.$folder."/infusion.php";
				$result = dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder='".$inf_folder."'");
				if (dbrows($result)) {
					$data = dbarray($result);
					if (version_compare($inf_version, $data['inf_version'], ">")) {
						$inf[] = array('inf_name' => ucwords(str_replace('_', ' ', $inf_title)), 'inf_folder' => $folder, 'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '', 'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta', 'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion', 'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '', 'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '', 'inf_status' => 2);
					} else {
						$inf[] = array('inf_name' => ucwords(str_replace('_', ' ', $inf_title)), 'inf_folder' => $folder, 'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '', 'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta', 'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion', 'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '', 'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '', 'inf_status' => 1);
					}
				} else {
					$inf[] = array('inf_name' => ucwords(str_replace('_', ' ', $inf_title)), 'inf_folder' => $folder, 'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '', 'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta', 'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion', 'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '', 'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '', 'inf_status' => 0);
				}
				$inf_title = "";
				$inf_description = "";
				$inf_version = "";
				$inf_developer = "";
				$inf_email = "";
				$inf_weburl = "";
				$inf_folder = "";
				$inf_newtable = "";
				$inf_insertdbrow = "";
				$inf_droptable = "";
				$inf_altertable = "";
				$inf_deldbrow = "";
				$inf_sitelink = "";
			}
		}
	}
	closedir($temp);
	sort($inf);
	opentable($locale['400']);
	echo "<div>\n";
	if (count($inf)) {
		echo "<div class='list-group'>\n";
		if (count($inf)) {
			echo "<div class='list-group-item hidden-xs'>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-2 col-sm-2 col-md-1 col-lg-1'>\n<strong>".$locale['419']."</strong></div>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-5'>\n<strong>".$locale['400']."</strong></div>\n";
			echo "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>\n<strong>".$locale['418']."</strong></div>\n";
			echo "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>\n<strong>".$locale['420']."</strong></div>\n";
			echo "<div class='hidden-xs hidden-sm hidden-md col-lg-3 col-lg-offset-0'>\n<strong>".$locale['421']."</strong></div>\n";
			echo "</div>\n</div>\n";
			for ($i = 0; $i < count($inf); $i++) {
				echo openform('infuseform', 'infuseform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
				echo "<div class='list-group-item'>\n";
				echo "<div class='row'>\n";
				echo "<div class='col-xs-2 col-sm-2 col-md-1 col-lg-1'>\n";
				echo form_hidden('', 'infusion', 'infusion', $inf[$i]['inf_folder']);
				if ($inf[$i]['inf_status'] > 0) {
					if ($inf[$i]['inf_status'] > 1) {
						echo form_button('', 'infuse', "infuse-$i", $locale['401'], array('class' => 'btn-info m-t-5 infuse', 'icon' => 'entypo magnet'));
					} else {
						echo form_button('', 'defuse', "defuse-$i", $locale['401'], array('class' => 'btn-default btn-sm m-t-5 defuse', 'icon' => 'entypo trash'));
					}
				} else {
					echo form_button('', 'infuse', "infuse-$i", $locale['401'], array('class' => 'btn-primary btn-sm m-t-5 infuse', 'icon' => 'entypo install'));
				}
				echo "</div>\n";
				echo "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-5'><strong>".$inf[$i]['inf_name']."</strong><br/>".trimlink($inf[$i]['inf_description'], 30)."</div>\n";
				echo "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>".($inf[$i]['inf_status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['414']."</label></h5>")."</div>\n";
				echo "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($inf[$i]['inf_version'] ? $inf[$i]['inf_version'] : '')."</div>\n";
				echo "<div class='col-xs-10 col-xs-offset-2 col-sm-10 col-sm-offset-2 col-md-10 col-md-offset-1 col-lg-3 col-lg-offset-0'>".($inf[$i]['inf_url'] ? "<a href='".$inf[$i]['inf_url']."' target='_blank'>" : "")." ".($inf[$i]['inf_developer'] ? $inf[$i]['inf_developer'] : $locale['410'])." ".($inf[$i]['inf_url'] ? "</a>" : "")." <br/>".($inf[$i]['inf_email'] ? "<a href='mailto:".$inf[$i]['inf_email']."'>".$locale['409']."</a>" : '')."</div>\n";
				echo "</div>\n</div>\n";
				echo closeform();
			}
		}
	} else {
		echo "<br /><p class='text-center'>".$locale['417']."</p>\n";
	}
	echo "</div>\n</div>\n";
	closetable();
	echo "<div class='well text-center m-t-10'>\n";
	echo "<a class='btn btn-block btn-primary' href='https://www.php-fusion.co.uk/infusions/addondb/directory.php' title='".$locale['422']."' target='_blank'>".$locale['422']."</a>\n";
	echo "</div>\n";
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
					for ($i = 1; $i < (count($inf_altertable)+1); $i++) {
						$result = dbquery("ALTER TABLE ".$inf_altertable[$i]);
					}
				}
				$result2 = dbquery("UPDATE ".DB_INFUSIONS." SET inf_version='".$inf_version."' WHERE inf_id='".$data['inf_id']."'");
			}
		} else {
			if (isset($inf_adminpanel) && is_array($inf_adminpanel) && count($inf_adminpanel)) {
				for ($i = 1; $i < (count($inf_adminpanel)+1); $i++) {
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
					for ($i = 1; $i < (count($inf_sitelink)+1); $i++) {
						$link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS), 0)+1;
						$result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order) VALUES ('".$inf_sitelink[$i]['title']."', '".str_replace("../", "", INFUSIONS).$inf_folder."/".$inf_sitelink[$i]['url']."', '".$inf_sitelink[$i]['visibility']."', '1', '0', '".$link_order."')");
					}
				}
				//Multilang rights
				if (isset($inf_mlt) && is_array($inf_mlt) && count($inf_mlt)) {
					for ($i = 1; $i < (count($inf_mlt)+1); $i++) {
						$result = dbquery("INSERT INTO ".DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('".$inf_mlt[$i]['rights']."', '".$inf_mlt[$i]['title']."', '1')");
					}
				}
				if (isset($inf_newtable) && is_array($inf_newtable) && count($inf_newtable)) {
					for ($i = 1; $i < (count($inf_newtable)+1); $i++) {
						$result = dbquery("CREATE TABLE ".$inf_newtable[$i]);
					}
				}
				if (isset($inf_insertdbrow) && is_array($inf_insertdbrow) && count($inf_insertdbrow)) {
					for ($i = 1; $i < (count($inf_insertdbrow)+1); $i++) {
						$result = dbquery("INSERT INTO ".$inf_insertdbrow[$i]);
					}
				}
				$result = dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$inf_title."', '".$inf_folder."', '".$inf_version."')");
			}
		}
	}
	redirect(FUSION_SELF.$aidlink);
}
if (isset($_POST['defuse']) && isset($_POST['infusion'])) {
	$infusion = form_sanitizer($_POST['infusion'], '');
	$result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS." WHERE inf_folder='".$infusion."'");
	$data = dbarray($result);
	include INFUSIONS.$data['inf_folder']."/infusion.php";
	if (isset($inf_adminpanel) && is_array($inf_adminpanel) && count($inf_adminpanel)) {
		for ($i = 1; $i < (count($inf_adminpanel)+1); $i++) {
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
	if (isset($inf_mlt) && is_array($inf_mlt) && count($inf_mlt)) {
		for ($i = 1; $i < (count($inf_mlt)+1); $i++) {
			$result = dbquery("DELETE FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$inf_mlt[$i]['rights']."'");
		}
	}
	if (isset($inf_sitelink) && is_array($inf_sitelink) && count($inf_sitelink)) {
		for ($i = 1; $i < (count($inf_sitelink)+1); $i++) {
			$result2 = dbquery("SELECT link_id, link_order FROM ".DB_SITE_LINKS." WHERE link_url='".str_replace("../", "", INFUSIONS).$inf_folder."/".$inf_sitelink[$i]['url']."'");
			if (dbrows($result2)) {
				$data2 = dbarray($result2);
				$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 WHERE link_order>'".$data2['link_order']."'");
				$result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$data2['link_id']."'");
			}
		}
	}
	if (isset($inf_newtable) && is_array($inf_newtable) && count($inf_newtable)) {
		for ($i = 1; $i < (count($inf_newtable)+1); $i++) {
			$result = dbquery("DROP TABLE ".$inf_droptable[$i]);
		}
	}
	if (isset($inf_deldbrow) && is_array($inf_deldbrow) && count($inf_deldbrow)) {
		for ($i = 1; $i < (count($inf_deldbrow)+1); $i++) {
			$result = dbquery("DELETE FROM ".$inf_deldbrow[$i]);
		}
	}
	$result = dbquery("DELETE FROM ".DB_INFUSIONS." WHERE inf_folder='".$_POST['infusion']."'");
	redirect(FUSION_SELF.$aidlink);
}

add_to_jquery("
    $('.defuse').bind('click', function() {return confirm('".$locale['412']."');});
    ");


require_once THEMES."templates/footer.php";
?>
