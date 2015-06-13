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
pageAccess('I');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/infusions.php";

add_breadcrumb(array('link'=>ADMIN.'infusions.php'.$aidlink, 'title'=>$locale['400']));

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
						$inf[] = array('inf_name' => str_replace('_', ' ', $inf_title), 'inf_folder' => $folder, 'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '', 'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta', 'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion', 'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '', 'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '', 'inf_status' => 2);
					} else {
						$inf[] = array('inf_name' => str_replace('_', ' ', $inf_title), 'inf_folder' => $folder, 'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '', 'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta', 'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion', 'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '', 'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '', 'inf_status' => 1);
					}
				} else {
					$inf[] = array('inf_name' => str_replace('_', ' ', $inf_title), 'inf_folder' => $folder, 'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '', 'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta', 'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion', 'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '', 'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '', 'inf_status' => 0);
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
	if ($inf) {
		echo "<div class='list-group'>\n";
		if ($inf) {
			echo "<div class='list-group-item hidden-xs'>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-2 col-sm-2 col-md-1 col-lg-1'>\n<strong>".$locale['419']."</strong></div>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-5'>\n<strong>".$locale['400']."</strong></div>\n";
			echo "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>\n<strong>".$locale['418']."</strong></div>\n";
			echo "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>\n<strong>".$locale['420']."</strong></div>\n";
			echo "<div class='hidden-xs hidden-sm hidden-md col-lg-3 col-lg-offset-0'>\n<strong>".$locale['421']."</strong></div>\n";
			echo "</div>\n</div>\n";
			foreach ($inf as $i => $item) {
				echo openform('infuseform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
				echo "<div class='list-group-item'>\n";
				echo "<div class='row'>\n";
				echo "<div class='col-xs-2 col-sm-2 col-md-1 col-lg-1'>\n";
				echo form_hidden('', 'infusion', 'infusion', $item['inf_folder']);
				if ($item['inf_status'] > 0) {
					if ($item['inf_status'] > 1) {
						echo form_button('infuse', $locale['401'], "infuse-$i", array('class' => 'btn-info m-t-5 infuse', 'icon' => 'entypo magnet'));
					} else {
						echo form_button('defuse', $locale['411'], "defuse-$i", array('class' => 'btn-default btn-sm m-t-5 defuse', 'icon' => 'entypo trash'));
					}
				} else {
					echo form_button('infuse', $locale['401'], "infuse-$i", array('class' => 'btn-primary btn-sm m-t-5 infuse', 'icon' => 'entypo install'));
				}
				echo "</div>\n";
				echo "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-5'><strong>".$item['inf_name']."</strong><br/>".trimlink($item['inf_description'], 45)."</div>\n";
				echo "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>".($item['inf_status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['414']."</label></h5>")."</div>\n";
				echo "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($item['inf_version'] ? $item['inf_version'] : '')."</div>\n";
				echo "<div class='col-xs-10 col-xs-offset-2 col-sm-10 col-sm-offset-2 col-md-10 col-md-offset-1 col-lg-3 col-lg-offset-0'>".($item['inf_url'] ? "<a href='".$item['inf_url']."' target='_blank'>" : "")." ".($item['inf_developer'] ? $item['inf_developer'] : $locale['410'])." ".($item['inf_url'] ? "</a>" : "")." <br/>".($item['inf_email'] ? "<a href='mailto:".$item['inf_email']."'>".$locale['409']."</a>" : '')."</div>\n";
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
				if (isset($inf_altertable) && is_array($inf_altertable)) {
					foreach ($inf_altertable as $item) {
						$result = dbquery("ALTER TABLE ".$item);
					}
				}
				$result2 = dbquery("UPDATE ".DB_INFUSIONS." SET inf_version='".$inf_version."' WHERE inf_id='".$data['inf_id']."'");
			}
		} else {
			if (isset($inf_adminpanel) && is_array($inf_adminpanel)) {
				$error = 0;
				foreach ($inf_adminpanel as $item) {
					$inf_admin_image = ($item['image'] ? : "infusion_panel.gif");
					if (!dbcount("(admin_id)", DB_ADMIN, "admin_rights='".$item['rights']."'")) {
						dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('".$item['rights']."', '".$inf_admin_image."', '".$item['title']."', '".INFUSIONS.$inf_folder."/".$item['panel']."', '5')");
						$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level=".USER_LEVEL_SUPER_ADMIN);
						while ($data = dbarray($result)) {
							dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".".$item['rights']."' WHERE user_id='".$data['user_id']."'");
						}
					} else {
						$error = 1;
					}
				}
			}
			if (!$error) {
				if (isset($inf_sitelink) && is_array($inf_sitelink)) {
					foreach ($inf_sitelink as $item) {
						$link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS), 0)+1;
						dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order) VALUES ('".$item['title']."', '".str_replace("../", "", INFUSIONS).$inf_folder."/".$item['url']."', '".$item['icon']."', '".$item['visibility']."', '".$item['position']."', '0', '".LANGUAGE."', '".$link_order."')");
					}
				}
				//Multilang rights
				if (isset($inf_mlt) && is_array($inf_mlt)) {
					foreach ($inf_mlt as $item) {
						dbquery("INSERT INTO ".DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('".$item['rights']."', '".$item['title']."', '1')");
					}
				}
				if (isset($inf_newtable) && is_array($inf_newtable)) {
					foreach ($inf_newtable as $item) {
						dbquery("CREATE TABLE ".$item);
					}
				}
				if (isset($inf_insertdbrow) && is_array($inf_insertdbrow)) {
					foreach ($inf_insertdbrow as $item) {
						dbquery("INSERT INTO ".$item);
					}
				}
				dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$inf_title."', '".$inf_folder."', '".$inf_version."')");
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
	if (isset($inf_adminpanel) && is_array($inf_adminpanel)) {
		foreach ($inf_adminpanel as $item) {
			dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".($item['rights'] ? : "IP")."' AND admin_link='".INFUSIONS.$inf_folder."/".$item['panel']."' AND admin_page='5'");
			$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level<=".USER_LEVEL_ADMIN);
			while ($data = dbarray($result)) {
				$user_rights = explode(".", $data['user_rights']);
				if (in_array($item['rights'], $user_rights)) {
					$key = array_search($item['rights'], $user_rights);
					unset($user_rights[$key]);
				}
				dbquery("UPDATE ".DB_USERS." SET user_rights='".implode(".", $user_rights)."' WHERE user_id='".$data['user_id']."'");
			}
		}
	}
	if (isset($inf_mlt) && is_array($inf_mlt)) {
		foreach ($inf_mlt as $item) {
			dbquery("DELETE FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$item['rights']."'");
		}
	}
	if (isset($inf_sitelink) && is_array($inf_sitelink)) {
		foreach ($inf_sitelink as $item) {
			$result2 = dbquery("SELECT link_id, link_order FROM ".DB_SITE_LINKS." WHERE link_url='".str_replace("../", "", INFUSIONS).$inf_folder."/".$item['url']."'");
			if (dbrows($result2)) {
				$data2 = dbarray($result2);
				dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 WHERE link_order>'".$data2['link_order']."'");
				dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$data2['link_id']."'");
			}
		}
	}
	if (isset($inf_droptable) && is_array($inf_droptable)) {
		foreach ($inf_droptable as $item) {
			dbquery("DROP TABLE ".$item);
		}
	}
	if (isset($inf_deldbrow) && is_array($inf_deldbrow)) {
		foreach ($inf_deldbrow as $item) {
			dbquery("DELETE FROM ".$item);
		}
	}
	dbquery("DELETE FROM ".DB_INFUSIONS." WHERE inf_folder='".$_POST['infusion']."'");
	redirect(FUSION_SELF.$aidlink);
}

add_to_jquery("
    $('.defuse').bind('click', function() {return confirm('".$locale['412']."');});
    ");

require_once THEMES."templates/footer.php";
