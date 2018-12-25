<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusions.php
| Author: PHP-Fusion Development Team
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
require_once __DIR__.'/../maincore.php';

if (!checkrights("I") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/infusions.php";

add_breadcrumb(array('link' => ADMIN.'infusions.php'.$aidlink, 'title' => $locale['400']));

$inf_title = ""; 
$inf_description = ""; 
$inf_version = ""; 
$inf_developer = ""; 
$inf_email = ""; 
$inf_weburl = "";
$inf_folder = ""; 

$inf_newtable = array(); 
$inf_insertdbrow = array(); 
$inf_droptable = array(); 
$inf_altertable = array();
$inf_deldbrow = array(); 
$inf_sitelink = array();

if (!isset($_POST['infuse']) && !isset($_POST['infusion']) && !isset($_POST['defuse'])) {
    $temp = opendir(INFUSIONS);
    $inf = array();
    while ($folder = readdir($temp)) {
        if (!in_array($folder, array("..", "."))) {
            if (is_dir(INFUSIONS.$folder) && file_exists(INFUSIONS.$folder."/infusion.php")) {
                include INFUSIONS.$folder."/infusion.php";
                $result = dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder='".$inf_folder."'");
                if (dbrows($result)) {
                    $data = dbarray($result);

                    $inf_image_tmp = !empty($inf_image) && file_exists(ADMIN."images/".$inf_image) ? ADMIN."images/".$inf_image : ADMIN."images/infusion_panel.png";
                    if (!empty($inf_image) && file_exists(INFUSIONS.$inf_folder."/".$inf_image)) {
                        $inf_image = INFUSIONS.$inf_folder."/".$inf_image;
                    } else {
                        $inf_image = $inf_image_tmp;
                    }

                    if (version_compare($inf_version, $data['inf_version'], ">")) {
                        $inf[] = array(
                            'inf_name' => str_replace('_', ' ', $inf_title),
                            'inf_folder' => $folder,
                            'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '',
                            'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta',
                            'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion',
                            'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '',
                            'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '',
                            'inf_status' => 2,
                            'inf_image' => $inf_image
                        );
                    } else {
                        $inf[] = array(
                            'inf_name' => str_replace('_', ' ', $inf_title),
                            'inf_folder' => $folder,
                            'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '',
                            'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta',
                            'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion',
                            'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '',
                            'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '',
                            'inf_status' => 1,
                            'inf_image' => $inf_image
                        );
                    }
                } else {

                    $inf_image_tmp = !empty($inf_image) && file_exists(ADMIN."images/".$inf_image) ? ADMIN."images/".$inf_image : ADMIN."images/infusion_panel.png";
                    if (!empty($inf_image) && file_exists(INFUSIONS.$inf_folder."/".$inf_image)) {
                        $inf_image = INFUSIONS.$inf_folder."/".$inf_image;
                    } else {
                        $inf_image = $inf_image_tmp;
                    }

                    $inf[] = array(
                        'inf_name' => str_replace('_', ' ', $inf_title),
                        'inf_folder' => $folder,
                        'inf_description' => isset($inf_description) && $inf_description ? $inf_description : '',
                        'inf_version' => isset($inf_version) && $inf_version ? $inf_version : 'beta',
                        'inf_developer' => isset($inf_developer) && $inf_developer ? $inf_developer : 'PHP-Fusion',
                        'inf_url' => isset($inf_weburl) && $inf_weburl ? $inf_weburl : '',
                        'inf_email' => isset($inf_email) && $inf_email ? $inf_email : '',
                        'inf_status' => 0,
                        'inf_image' => $inf_image
                    );
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
                $inf_image = "";
                $inf_image_tmp = "";
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
            echo "<div class='col-xs-2 col-sm-4 col-md-2'>\n<strong>".$locale['419']."</strong></div>\n";
            echo "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-4'>\n<strong>".$locale['400']."</strong></div>\n";
            echo "<div class='col-xs-2 col-sm-2 col-md-2'>\n<strong>".$locale['418']."</strong></div>\n";
            echo "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>\n<strong>".$locale['420']."</strong></div>\n";
            echo "<div class='hidden-xs hidden-sm hidden-md col-lg-3 col-lg-offset-0 col-lg-2'>\n<strong>".$locale['421']."</strong></div>\n";
            echo "</div>\n</div>\n";

            foreach ($inf as $i => $item) {
				echo "<form name='infuseform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
                echo "<div class='list-group-item'>\n";
                echo "<div class='row'>\n";
                echo "<div class='col-xs-2 col-sm-4 col-md-2'>\n";
				echo "<input type='hidden' name='infusion' value='".$item['inf_folder']."' />\n";
                if ($item['inf_status'] > 0) {
                    if ($item['inf_status'] > 1) {
						echo "<input type='submit' name='infuse' value='".$locale['401']."' class='btn-info m-t-5' />\n";
                    } else {
						echo "<input type='submit' name='defuse' value='".$locale['411']."' class='btn-info m-t-5' onclick='return Defuse();' />\n";
                    }
                } else {
						echo "<input type='submit' name='infuse' value='".$locale['401']."' class='btn-primary m-t-5' />\n";

                }
                echo "</div>\n";
                echo "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-4'>
                <div class='pull-left m-r-10'>
                    <img style='width:40px;' src='".$item['inf_image']."' alt='".$item['inf_name']."'/>
                </div>
                <div class='overflow-hide'>\n
                    <strong>".$item['inf_name']."</strong><br/>".$item['inf_description']."</div>\n
                </div>
                ";
                echo "<div class='col-xs-2 col-sm-2 col-md-2'>".($item['inf_status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['414']."</label></h5>")."</div>\n";
                echo "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($item['inf_version'] ? $item['inf_version'] : '')."</div>\n";
                echo "<div class='col-xs-10 col-xs-offset-2 col-sm-10 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-3 col-lg-offset-0'>".($item['inf_url'] ? "<a href='".$item['inf_url']."' target='_blank'>" : "")." ".($item['inf_developer'] ? $item['inf_developer'] : $locale['410'])." ".($item['inf_url'] ? "</a>" : "")." <br/>".($item['inf_email'] ? "<a href='mailto:".$item['inf_email']."'>".$locale['409']."</a>" : '')."</div>\n";
                echo "</div>\n</div>\n";
                echo "</form>\n";
            }
        }
    } else {
        echo "<br /><p class='text-center'>".$locale['417']."</p>\n";
    }
    echo "</div>\n</div>\n";
    closetable();
    echo "<div class='well text-center m-t-10'>\n";
    echo "<a class='btn btn-block btn-primary' href='https://www.php-fusion.co.uk/infusions/marketplace' title='".$locale['422']."' target='_blank'>".$locale['422']."</a>\n";
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
					$inf_admin_image = ($inf_adminpanel[$i]['image'] ? $inf_adminpanel[$i]['image'] : "infusion_panel.png");
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
				if (isset($inf_mlt) && is_array($inf_mlt) && count($inf_mlt)) {
					for ($i = 1; $i < (count($inf_mlt) + 1); $i++) {
						$result = dbquery("INSERT INTO ".DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('".$inf_mlt[$i]['rights']."', '".$inf_mlt[$i]['title']."', '1')");
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

if (isset($_POST['defuse']) && isset($_POST['infusion'])) {
	echo "yes";
	print_p($_POST);
	$infusion = stripinput($_POST['infusion']);
    $result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS." WHERE inf_folder='".$infusion."'");
    $data = dbarray($result);
	print_p($data);
    include INFUSIONS.$data['inf_folder']."/infusion.php";
    if (isset($inf_adminpanel) && is_array($inf_adminpanel)) {
        foreach ($inf_adminpanel as $item) {
            dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".($item['rights'] ?: "IP")."' AND admin_link='".INFUSIONS.$inf_folder."/".$item['panel']."' AND admin_page='5'");
            $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level<=102");
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
        foreach ($inf_mlt as $mlt) {
            dbquery("DELETE FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$mlt['rights']."'");
        }
    }
    if (isset($inf_sitelink) && is_array($inf_sitelink)) {
        foreach ($inf_sitelink as $sitelink) {
            $result2 = dbquery("SELECT link_id, link_order FROM ".DB_SITE_LINKS." WHERE link_url='".str_replace("../", "",
                                                                                                                INFUSIONS).$inf_folder."/".$sitelink['url']."'");
            if (dbrows($result2)) {
                $data2 = dbarray($result2);
                dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 WHERE link_order>'".$data2['link_order']."'");
                dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$data2['link_id']."'");
            }
        }
    }

    // Delete all mlt links that is enabled in the system configuration now.
    if (isset($mlt_deldbrow) && is_array($mlt_deldbrow)) {
        foreach (fusion_get_enabled_languages() as $current_language) {
            if (isset($mlt_deldbrow[$current_language])) {
                foreach ($mlt_deldbrow[$current_language] as $deldbrow) {
                    dbquery("DELETE FROM ".$deldbrow);
                }
            }
        }
    }

    if (isset($inf_droptable) && is_array($inf_droptable)) {
        foreach ($inf_droptable as $droptable) {
            dbquery("DROP TABLE IF EXISTS ".$droptable);
        }
    }

    if (isset($inf_dropcol) && is_array($inf_dropcol)) {
        foreach ($inf_dropcol as $dropCol) {
            if (is_array($dropCol) && !empty($dropCol['table']) && !empty($dropCol['column'])) {
                $columns = fieldgenerator($dropCol['table']);
                if (in_array($dropCol['column'], $columns)) {
                    dbquery("ALTER TABLE IF EXISTS ".$dropCol['table']." DROP COLUMN ".$dropCol['column']);
                }
            }
        }
    }

    if (isset($inf_deldbrow) && is_array($inf_deldbrow)) {
        foreach ($inf_deldbrow as $deldbrow) {
            dbquery("DELETE FROM ".$deldbrow);
        }
    }

    // clean up files
    if (isset($inf_delfiles) && is_array($inf_delfiles)) {
        foreach ($inf_delfiles as $folder) {
            if (file_exists($folder)) {
                $files = makefilelist($folder, ".|..|index.php", TRUE);
                if (!empty($files)) {
                    foreach ($files as $filename) {
                        // $folder must end with trailing slash /
                        unlink($folder.$filename);
                    }
                }
            }
        }
    }

    dbquery("DELETE FROM ".DB_INFUSIONS." WHERE inf_folder='".$_POST['infusion']."'");
    redirect(FUSION_SELF.$aidlink);
}

echo "<script type='text/javascript'>
function Defuse() {
	return confirm('".$locale['412']."');
}
</script>\n";

require_once THEMES."templates/footer.php";