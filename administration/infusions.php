<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusions.php
| Author: PHP-Fusion Development Team
| Co-Author: Christian Damsgaard Jorgensen (PMM)
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

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");
add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['412']."');});");
add_breadcrumb(array('link' => ADMIN.'infusions.php'.$aidlink, 'title' => $locale['400']));

if (($folder = filter_input(INPUT_POST, 'infuse'))) {
    PHPFusion\Installer\Infusion_Core::getInstance()->infuse($folder);
} elseif ($folder = filter_input(INPUT_POST, 'defuse')) {
    PHPFusion\Installer\Infusion_Core::getInstance()->defuse($folder);
}

opentable($locale['400']);

$temp = opendir(INFUSIONS);
$infs = array();
while ($folder = readdir($temp)) {
    if (!in_array($folder, array("..", ".")) && ($inf = PHPFusion\Installer\Infusion_Core::load_infusion($folder))) {
        $infs[] = $inf;
    }
}
closedir($temp);
sort($infs);

if (!isset($_POST['infuse']) && !isset($_POST['infusion']) && !isset($_GET['defuse'])) {

    $content = "";
    if ($infs) {
        $content .= "<div class='list-group'>\n";
        $content .= "<div class='list-group-item hidden-xs'>\n";
        $content .= "<div class='row'>\n";
        $content .= "<div class='col-xs-2 col-sm-4 col-md-2'>\n<strong>".$locale['419']."</strong></div>\n";
        $content .= "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-4'>\n<strong>".$locale['400']."</strong></div>\n";
        $content .= "<div class='col-xs-2 col-sm-2 col-md-2'>\n<strong>".$locale['418']."</strong></div>\n";
        $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>\n<strong>".$locale['420']."</strong></div>\n";
        $content .= "<div class='hidden-xs hidden-sm hidden-md col-lg-3 col-lg-offset-0 col-lg-2'>\n<strong>".$locale['421']."</strong></div>\n";
        $content .= "</div>\n</div>\n";


        foreach ($infs as $i => $inf) {

            $content .= openform('infuseform', 'post', FUSION_SELF.fusion_get_aidlink());

            $content .= "<div class='list-group-item'>\n";
            $content .= "<div class='row'>\n";
            $content .= "<div class='col-xs-2 col-sm-4 col-md-2'>\n";
            if ($inf['status'] > 0) {
                if ($inf['status'] > 1) {
                    $content .= form_button('infuse', $locale['401'], $inf['folder'],
                                            array('class' => 'btn-info m-t-5 infuse', 'icon' => 'entypo magnet', 'input_id' => 'infuse_'.$i));
                } else {
                    $content .= form_button('defuse', $locale['411'], $inf['folder'],
                                            array(
                                                'class' => 'btn-default btn-sm m-t-5 defuse', 'icon' => 'entypo trash', 'input_id' => 'defuse_'.$i
                                            ));
            }
            } else {
                $content .= form_button('infuse', $locale['401'], $inf['folder'],
                                        array('class' => 'btn-primary btn-sm m-t-5 infuse', 'icon' => 'entypo install', 'input_id' => 'infuse_'.$i));
        }
            $content .= "</div>\n";
            $content .= "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-4'>\n";
            $content .= "<div class='pull-left m-r-10'>\n<img style='width:48px;' src='".$inf['image']."' alt='".$inf['name']."'/></div>\n";
            $content .= "<div class='overflow-hide'>\n<strong>".$inf['title']."</strong><br/>".$inf['description']."</div>\n</div>\n";
            $content .= "<div class='col-xs-2 col-sm-2 col-md-2'>".($inf['status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['414']."</label></h5>")."</div>\n";
            $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($inf['version'] ? $inf['version'] : '')."</div>\n";
            $content .= "<div class='col-xs-10 col-xs-offset-2 col-sm-10 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-3 col-lg-offset-0'>".($inf['url'] ? "<a href='".$inf['url']."' target='_blank'>" : "")." ".($inf['developer'] ? $inf['developer'] : $locale['410'])." ".($inf['url'] ? "</a>" : "")." <br/>".($inf['email'] ? "<a href='mailto:".$inf['email']."'>".$locale['409']."</a>" : '')."</div>\n";

            $content .= "</div>\n</div>\n";
    }
    } else {
        $content .= "<br /><p class='text-center'>".$locale['417']."</p>\n";
    }

    $content .= "</div>\n</div>\n";
    echo $content;
    closetable();

    echo "<div class='well text-center m-t-20'>\n";
    echo "<a class='btn btn-block btn-primary' href='https://www.php-fusion.co.uk/infusions/addondb/directory.php' title='".$locale['422']."' target='_blank'>".$locale['422']."</a>\n";
    echo "</div>\n";

}


// Infusion Action
/*
if (isset($_POST['infuse']) && isset($_POST['infusion'])) {
    $error = "";
    $infusion = stripinput($_POST['infusion']);
    if (file_exists(INFUSIONS.$infusion."/infusion.php")) {
        include INFUSIONS.$infusion."/infusion.php";

        // Check for updates
        $result = dbquery("SELECT inf_id, inf_version FROM ".DB_INFUSIONS." WHERE inf_folder='".$inf_folder."'");
        if (dbrows($result)) {
            $data = dbarray($result);

            // to get to this point, we have an infusion version. we also have a build version
            $folder = INFUSIONS.$inf_folder."/upgrade/";
            if (file_exists($folder)) {
                $upgrade_files = makefilelist($folder, ".|..|index.php", TRUE);

                // first get our version
                if (!empty($upgrade_files) && is_array($upgrade_files)) {
                    foreach($upgrade_files as $upgrade_file) {
                        // file_name checks
                        $filename = rtrim($upgrade_file, 'upgrade.inc');
                        if (isnum($filename) && $filename > $data['inf_version']) {

                            // need to unset all the previous files

                            require_once $folder.$upgrade_file;

                            if (isset($inf_altertable) && is_array($inf_altertable)) {
                                foreach ($inf_altertable as $item) {
                                    $result = dbquery("ALTER TABLE ".$item);
                                }
                            }

                            if (isset($inf_adminpanel) && is_array($inf_adminpanel) && isset($inf_folder)) {
                                $error = 0;
                                foreach ($inf_adminpanel as $adminpanel) {
                                    // auto recovery
                                    if (!empty($adminpanel['rights'])) {
                                        dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".$adminpanel['rights']."'");
                                    }

                                    $inf_image = $adminpanel['image'];
                                    $inf_image_tmp = !empty($inf_image) && file_exists(ADMIN."images/".$inf_image) ? ADMIN."images/".$inf_image : ADMIN."images/infusion_panel.png";
                                    if (!empty($inf_image) && file_exists(INFUSIONS.$inf_folder."/".$inf_image)) {
                                        $adminpanel['image'] = INFUSIONS.$inf_folder."/".$inf_image;
                                    } else {
                                        $adminpanel['image'] = $inf_image_tmp;
                                    }

                                    if (empty($adminpanel['page'])) {
                                        $item_page = 5;
                                    } else {
                                        $item_page = isnum($adminpanel['page']) ? $adminpanel['page'] : 5;
                                    }
                                    if (!dbcount("(admin_id)", DB_ADMIN, "admin_rights='".$adminpanel['rights']."'")) {
                                        $adminpanel += array(
                                            "rights" => "",
                                            "title" => "",
                                            "panel" => "",
                                        );
                                        dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('".$adminpanel['rights']."', '".$adminpanel['image']."', '".$adminpanel['title']."', '".INFUSIONS.$inf_folder."/".$adminpanel['panel']."', '".$item_page."')");
                                        $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level=".USER_LEVEL_SUPER_ADMIN);
                                        while ($data = dbarray($result)) {
                                            dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".".$adminpanel['rights']."' WHERE user_id='".$data['user_id']."'");
                                        }
                                    } else {
                                        $error = 1;
                                    }
                                }
                            }

                            if (!$error) {
                                // Insert Site Links
                                if (isset($inf_sitelink) && is_array($inf_sitelink)) {
                                    $last_id = 0;
                                    foreach ($inf_sitelink as $sitelink) {
                                        $link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS), 0) + 1;
                                        $sitelink += array(
                                            "title" => "",
                                            "cat" => 0,
                                            "url" => "",
                                            "icon" => "",
                                            "visibility" => 0,
                                            "position" => 3,
                                        );
                                        if (!empty($sitelink['cat']) && $sitelink['cat'] == "{last_id}" && !empty($last_id)) {
                                            $sitelink['cat'] = $last_id;
                                            dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order) VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".str_replace("../",
                                                                                                                                                                                                                                                                              "",
                                                                                                                                                                                                                                                                              INFUSIONS).$inf_folder."/".$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");
                                        } else {
                                            dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order) VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".str_replace("../",
                                                                                                                                                                                                                                                                              "",
                                                                                                                                                                                                                                                                              INFUSIONS).$inf_folder."/".$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");
                                            $last_id = dblastid();
                                        }
                                    }
                                }

                                // Insert Multilanguage Rights
                                if (isset($inf_mlt) && is_array($inf_mlt)) {
                                    foreach ($inf_mlt as $mlt) {
                                        dbquery("INSERT INTO ".DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('".$mlt['rights']."', '".$mlt['title']."', '1')");
                                    }
                                }

                                // Create new tables
                                if (isset($inf_newtable) && is_array($inf_newtable)) {
                                    foreach ($inf_newtable as $newtable) {
                                        dbquery("CREATE TABLE IF NOT EXISTS ".$newtable);
                                    }
                                }

                                // Insert columns
                                if (isset($inf_newcol) && is_array($inf_newcol)) {
                                    foreach ($inf_newcol as $newCol) {
                                        if (is_array($newCol) && !empty($newCol['table']) && !empty($newCol['column']) && !empty($newCol['column_type'])) {
                                            $columns = fieldgenerator($newCol['table']);
                                            $count = count($columns);
                                            if (!in_array($newCol['column'], $columns)) {
                                                dbquery("ALTER TABLE IF EXISTS ".$newCol['table']." ADD ".$newCol['column']." ".$newCol['column_type']." AFTER ".$columns[$count - 1]);
                                            }
                                        }
                                    }
                                }

                                // Insert rows
                                if (isset($inf_insertdbrow) && is_array($inf_insertdbrow)) {
                                    $last_id = 0;
                                    foreach ($inf_insertdbrow as $insertdbrow) {
                                        if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                                            try {
                                                dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                                            } catch (ErrorException $e) {
                                                // cannot insert
                                            }
                                        } else {
                                            dbquery("INSERT INTO ".$insertdbrow);
                                            $last_id = dblastid();
                                        }
                                    }
                                }

                                // Insert all mlt rows that is enabled in the system configuration now.
                                // add $last_id configuration to support hierarchy insertions
                                if (isset($mlt_insertdbrow) && is_array($mlt_insertdbrow)) {

                                    foreach (fusion_get_enabled_languages() as $current_language => $translated_languages) {
                                        if (isset($mlt_insertdbrow[$current_language])) {
                                            $last_id = 0;
                                            foreach ($mlt_insertdbrow[$current_language] as $insertdbrow) {
                                                if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                                                    dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                                                } else {
                                                    dbquery("INSERT INTO ".$insertdbrow);
                                                    $last_id = dblastid();
                                                }
                                            }
                                        }
                                    }

                                }

                                if (isset($inf_updatedbrow) && is_array($inf_updatedbrow)) {
                                    foreach ($inf_updatedbrow as $updatedbrow) {
                                        dbquery("UPDATE ".$updatedbrow);
                                    }
                                }


                            }

                        }
                    }
                }

            }





        } else {

            // Clean insert

            // Insert Admin Pages Link into Admin Panel
            if (isset($inf_adminpanel) && is_array($inf_adminpanel) && isset($inf_folder)) {
                $error = 0;
                foreach ($inf_adminpanel as $adminpanel) {
                    // auto recovery
                    if (!empty($adminpanel['rights'])) {
                        dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".$adminpanel['rights']."'");
                    }

                    $inf_image = $adminpanel['image'];
                    $inf_image_tmp = !empty($inf_image) && file_exists(ADMIN."images/".$inf_image) ? ADMIN."images/".$inf_image : ADMIN."images/infusion_panel.png";
                    if (!empty($inf_image) && file_exists(INFUSIONS.$inf_folder."/".$inf_image)) {
                        $adminpanel['image'] = INFUSIONS.$inf_folder."/".$inf_image;
                    } else {
                        $adminpanel['image'] = $inf_image_tmp;
                    }

                    if (empty($adminpanel['page'])) {
                        $item_page = 5;
                    } else {
                        $item_page = isnum($adminpanel['page']) ? $adminpanel['page'] : 5;
                    }
                    if (!dbcount("(admin_id)", DB_ADMIN, "admin_rights='".$adminpanel['rights']."'")) {
                        $adminpanel += array(
                            "rights" => "",
                            "title" => "",
                            "panel" => "",
                        );
                        dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('".$adminpanel['rights']."', '".$adminpanel['image']."', '".$adminpanel['title']."', '".INFUSIONS.$inf_folder."/".$adminpanel['panel']."', '".$item_page."')");
                        $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level=".USER_LEVEL_SUPER_ADMIN);
                        while ($data = dbarray($result)) {
                            dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".".$adminpanel['rights']."' WHERE user_id='".$data['user_id']."'");
                        }
                    } else {
                        $error = 1;
                    }
                }
            }

            if (!$error) {
                // Insert Site Links
                if (isset($inf_sitelink) && is_array($inf_sitelink)) {
                    $last_id = 0;
                    foreach ($inf_sitelink as $sitelink) {
                        $link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS), 0) + 1;
                        $sitelink += array(
                            "title" => "",
                            "cat" => 0,
                            "url" => "",
                            "icon" => "",
                            "visibility" => 0,
                            "position" => 3,
                        );
                        if (!empty($sitelink['cat']) && $sitelink['cat'] == "{last_id}" && !empty($last_id)) {
                            $sitelink['cat'] = $last_id;
                            dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order) VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".str_replace("../",
                                                                                                                                                                                                                                                              "",
                                                                                                                                                                                                                                                              INFUSIONS).$inf_folder."/".$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");
                        } else {
                            dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order) VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".str_replace("../",
                                                                                                                                                                                                                                                              "",
                                                                                                                                                                                                                                                              INFUSIONS).$inf_folder."/".$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");
                            $last_id = dblastid();
                        }
                    }
                }

                // Insert Multilanguage Rights
                if (isset($inf_mlt) && is_array($inf_mlt)) {
                    foreach ($inf_mlt as $mlt) {
                        dbquery("INSERT INTO ".DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('".$mlt['rights']."', '".$mlt['title']."', '1')");
                    }
                }

                // Create new tables
                if (isset($inf_newtable) && is_array($inf_newtable)) {
                    foreach ($inf_newtable as $newtable) {
                        dbquery("CREATE TABLE IF NOT EXISTS ".$newtable);
                    }
                }

                // Insert columns
                if (isset($inf_newcol) && is_array($inf_newcol)) {
                    foreach ($inf_newcol as $newCol) {
                        if (is_array($newCol) && !empty($newCol['table']) && !empty($newCol['column']) && !empty($newCol['column_type'])) {
                            $columns = fieldgenerator($newCol['table']);
                            $count = count($columns);
                            if (!in_array($newCol['column'], $columns)) {
                                dbquery("ALTER TABLE IF EXISTS ".$newCol['table']." ADD ".$newCol['column']." ".$newCol['column_type']." AFTER ".$columns[$count - 1]);
                            }
                        }
                    }
                }

                // Insert rows
                if (isset($inf_insertdbrow) && is_array($inf_insertdbrow)) {
                    $last_id = 0;
                    foreach ($inf_insertdbrow as $insertdbrow) {
                        if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                            try {
                                dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                            } catch (ErrorException $e) {
                                // cannot insert
                            }
                        } else {
                            dbquery("INSERT INTO ".$insertdbrow);
                            $last_id = dblastid();
                        }
                    }
                }

                // Insert all mlt rows that is enabled in the system configuration now.
                // add $last_id configuration to support hierarchy insertions
                if (isset($mlt_insertdbrow) && is_array($mlt_insertdbrow)) {

                    foreach (fusion_get_enabled_languages() as $current_language => $translated_languages) {
                        if (isset($mlt_insertdbrow[$current_language])) {
                            $last_id = 0;
                            foreach ($mlt_insertdbrow[$current_language] as $insertdbrow) {
                                if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                                    dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                                } else {
                                    dbquery("INSERT INTO ".$insertdbrow);
                                    $last_id = dblastid();
                                }
                            }
                        }
                    }

                }

                if (isset($inf_updatedbrow) && is_array($inf_updatedbrow)) {
                    foreach ($inf_updatedbrow as $updatedbrow) {
                        dbquery("UPDATE ".$updatedbrow);
                    }
                }
            }
        }

        $error = true;
        // Register Infusion
        if (!$error) {
            if (isset($data['inf_version'])) {
                if ($inf_version > $data['inf_version']) {
                    $result2 = dbquery("UPDATE ".DB_INFUSIONS." SET inf_version='".$inf_version."' WHERE inf_id='".$data['inf_id']."'");
                }
            } else {
                dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$inf_title."', '".$inf_folder."', '".$inf_version."')");
            }
        }


    }

    //redirect(FUSION_SELF.$aidlink);
}
*/


/*
if (isset($_POST['defuse']) && isset($_POST['infusion'])) {
    $infusion = form_sanitizer($_POST['infusion'], '');
    $result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS." WHERE inf_folder='".$infusion."'");
    $data = dbarray($result);
    include INFUSIONS.$data['inf_folder']."/infusion.php";
    if (isset($inf_adminpanel) && is_array($inf_adminpanel)) {
        foreach ($inf_adminpanel as $item) {
            dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".($item['rights'] ?: "IP")."' AND admin_link='".INFUSIONS.$inf_folder."/".$item['panel']."' AND admin_page='5'");
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
*/


require_once THEMES."templates/footer.php";
