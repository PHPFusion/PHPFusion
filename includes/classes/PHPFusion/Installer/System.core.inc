<?php
namespace PHPFusion\Installer;

class System_Core {

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public static function infuse($folder) {

        $error = FALSE;

        if (($inf = self::load_infusion($folder))) {

            $result = dbquery("SELECT inf_id, inf_version FROM ".DB_INFUSIONS." WHERE inf_folder=:folder", array(':folder' => $folder));
            if (dbrows($result)) {

                $data = dbarray($result);
                if ($inf['version'] > $data['inf_version']) {
                    if ($inf['altertable'] && is_array($inf['altertable'])) {
                        foreach ($inf['altertable'] as $alter) {
                            $result = dbquery("ALTER TABLE ".$alter);
                        }
                    }
                    dbquery("UPDATE ".DB_INFUSIONS." SET inf_version=:
                        WHERE inf_id=:id", array(
                        ':version' => $inf['version'],
                        ':id' => $inf['id'],
                    ));
                }

            } else {

                if ($inf['adminpanel'] && is_array($inf['adminpanel'])) {
                    foreach ($inf['adminpanel'] as $adminpanel) {
                        // auto recovery
                        if (!empty($adminpanel['rights'])) {
                            dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".$adminpanel['rights']."'");
                        }

                        $inf_admin_image = ($adminpanel['image'] ?: "infusion_panel.png");
                        $link_url = '../'.INFUSIONS.$inf['folder'].'/';

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

                            dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) 
                            VALUES ('".$adminpanel['rights']."', '".$inf_admin_image."', '".$adminpanel['title']."', '".$link_url.$adminpanel['panel']."', '".$item_page."')");
                            $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level=".USER_LEVEL_SUPER_ADMIN);
                            while ($data = dbarray($result)) {
                                dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".".$adminpanel['rights']."' WHERE user_id='".$data['user_id']."'");
                            }
                        } else {
                            $error = TRUE;
                        }
                    }
                }

                if ($error === FALSE) {

                    // Install single site links
                    if ($inf['sitelink'] && is_array($inf['sitelink'])) {
                        $last_id = 0;
                        foreach ($inf['sitelink'] as $sitelink) {
                            $link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS), 0) + 1;
                            $sitelink += array(
                                "title" => "",
                                "cat" => 0,
                                "url" => "",
                                "icon" => "",
                                "visibility" => 0,
                                "position" => 3,
                            );
                            $link_url = "".str_replace("../", "", INFUSIONS).$folder."/";

                            if (!empty($sitelink['cat']) && $sitelink['cat'] == "{last_id}" && !empty($last_id)) {
                                $sitelink['cat'] = $last_id;

                                dbquery("INSERT INTO ".DB_SITE_LINKS."
                                (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order)
                                VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".$link_url.$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");

                            } else {
                                dbquery("INSERT INTO ".DB_SITE_LINKS."
                                (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order)
                                VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".$link_url.$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");

                                $last_id = dblastid();
                            }
                        }
                    }

                    //Multilang rights
                    if ($inf['mlt'] && is_array($inf['mlt'])) {
                        foreach ($inf['mlt'] as $mlt) {
                            if (dbcount("(mlt_rights)", DB_LANGUAGE_TABLES, "mlt_rights = '".$mlt['rights']."'")) {
                                dbquery("DELETE FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$mlt['rights']."'");
                            }
                            dbquery("INSERT INTO ".DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('".$mlt['rights']."', '".$mlt['title']."', '1')");
                        }
                    }

                    if ($inf['newtable'] && is_array($inf['newtable'])) {
                        foreach ($inf['newtable'] as $newtable) {
                            dbquery("CREATE TABLE ".$newtable);
                        }
                    }

                    // Install new column
                    if (isset($inf['newcol']) && is_array($inf['newcol'])) {
                        foreach ($inf['newcol'] as $newCol) {
                            if (is_array($newCol) && !empty($newCol['table']) && !empty($newCol['column']) && !empty($newCol['column_type'])) {
                                $columns = fieldgenerator($newCol['table']);
                                $count = count($columns);
                                if (!in_array($newCol['column'], $columns)) {
                                    dbquery("ALTER TABLE ".$newCol['table']." ADD ".$newCol['column']." ".$newCol['column_type']." AFTER ".$columns[$count - 1]);
                                }
                            }
                        }
                    }

                    // Insert records
                    if ($inf['insertdbrow'] && is_array($inf['insertdbrow'])) {
                        $last_id = 0;
                        foreach ($inf['insertdbrow'] as $insertdbrow) {
                            if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                                dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                            } else {
                                dbquery("INSERT INTO ".$insertdbrow);
                                $last_id = dblastid();
                            }
                        }
                    }

                    if ($inf['mlt_insertdbrow'] && is_array($inf['mlt_insertdbrow'])) {

                        foreach (fusion_get_enabled_languages() as $current_language => $language_translations) {

                            if (isset($inf['mlt_insertdbrow'][$current_language])) {

                                $last_id = 0;

                                foreach ($inf['mlt_insertdbrow'][$current_language] as $insertdbrow) {

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

                    if (dbcount("(inf_title)", DB_INFUSIONS, "inf_folder='".$inf['folder']."'")) {
                        dbquery("DELETE FROM ".DB_INFUSIONS." WHERE inf_folder='".$inf['folder']."'");
                    }
                    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$inf['title']."', '".$inf['folder']."', '".$inf['version']."')");
                }
            }
        }

        //redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));

    }

    public static function defuse($folder) {

        $result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS." WHERE inf_folder=:folder", array(':folder' => $folder));
        $infData = dbarray($result);


        $inf = self::load_infusion($folder);

        if ($inf['adminpanel'] && is_array($inf['adminpanel'])) {
            foreach ($inf['adminpanel'] as $adminpanel) {
                dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".($adminpanel['rights'] ?: "IP")."' AND admin_link='".INFUSIONS.$inf['folder']."/".$adminpanel['panel']."' AND admin_page='5'");
                $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level<=".USER_LEVEL_ADMIN);
                while ($data = dbarray($result)) {
                    $user_rights = explode(".", $data['user_rights']);
                    if (in_array($adminpanel['rights'], $user_rights)) {
                        $key = array_search($adminpanel['rights'], $user_rights);
                        unset($user_rights[$key]);
                    }
                    dbquery("UPDATE ".DB_USERS." SET user_rights='".implode(".", $user_rights)."' WHERE user_id='".$data['user_id']."'");
                }
            }
        }

        if ($inf['mlt'] && is_array($inf['mlt'])) {
            foreach ($inf['mlt'] as $mlt) {
                dbquery("DELETE FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$mlt['rights']."'");
            }
        }

        if ($inf['sitelink'] && is_array($inf['sitelink'])) {
            foreach ($inf['sitelink'] as $sitelink) {
                $result2 = dbquery("SELECT link_id, link_order FROM ".DB_SITE_LINKS." WHERE link_url='".str_replace("../", "",
                                                                                                                    INFUSIONS).$inf['folder']."/".$sitelink['url']."'");
                if (dbrows($result2)) {
                    $data2 = dbarray($result2);
                    dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 WHERE link_order>'".$data2['link_order']."'");
                    dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$data2['link_id']."'");
                }
            }
        }

        if (isset($inf['deldbrow']) && is_array($inf['deldbrow'])) {
            foreach ($inf['deldbrow'] as $deldbrow) {
                dbquery("DELETE FROM ".$deldbrow);
            }
        }

        if ($inf['mlt_deldbrow'] && is_array($inf['mlt_deldbrow'])) {
            foreach (fusion_get_enabled_languages() as $current_language) {
                if (isset($inf['mlt_deldbrow'][$current_language])) {
                    foreach ($inf['mlt_deldbrow'][$current_language] as $mlt_deldbrow) {
                        dbquery("DELETE FROM ".$mlt_deldbrow);
                    }
                }
            }
        }

        if (!empty($inf['delfiles']) && is_array($inf['delfiles'])) {
            foreach ($inf['delfiles'] as $folder) {
                $files = makefilelist($folder, ".|..|index.php", TRUE);
                if (!empty($files)) {
                    foreach ($files as $filename) {
                        unlink($folder.$filename);
                    }
                }
            }
        }

        if (isset($inf['dropcol']) && is_array($inf['dropcol'])) {
            foreach ($inf['dropcol'] as $dropCol) {
                if (is_array($dropCol) && !empty($dropCol['table']) && !empty($dropCol['column'])) {
                    $columns = fieldgenerator($dropCol['table']);
                    if (in_array($dropCol['column'], $columns)) {
                        dbquery("ALTER TABLE ".$dropCol['table']." DROP COLUMN ".$dropCol['column']);
                    }
                }
            }
        }

        if ($inf['droptable'] && is_array($inf['droptable'])) {
            foreach ($inf['droptable'] as $droptable) {
                dbquery("DROP TABLE IF EXISTS ".$droptable);
            }
        }


        dbquery("DELETE FROM ".DB_INFUSIONS." WHERE inf_folder=:folder", array(
            ':folder' => $infData['inf_folder']
        ));

        //redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));

    }

    /**
     * @param string $folder
     * @return array
     */
    public static function load_infusion($folder) {
        $infusion = array();
        $inf_title = "";
        $inf_description = "";
        $inf_version = "";
        $inf_developer = "";
        $inf_email = "";
        $inf_weburl = "";
        $inf_folder = "";
        $inf_image = "";
        $inf_newtable = array();
        $inf_insertdbrow = array();
        $inf_droptable = array();
        $inf_altertable = array();
        $inf_deldbrow = array();
        $inf_sitelink = array();
        $inf_adminpanel = array();
        $inf_mlt = array();
        $mlt_insertdbrow = array();
        $mlt_deldbrow = array();
        $inf_delfiles = array();
        $inf_newcol = array();
        $inf_dropcol = array();
        if (is_dir(INFUSIONS.$folder) && file_exists(INFUSIONS.$folder."/infusion.php")) {
            include INFUSIONS.$folder."/infusion.php";
            $infusion = array(
                'name' => str_replace('_', ' ', $inf_title),
                'title' => $inf_title,
                'description' => $inf_description,
                'version' => $inf_version ?: 'beta',
                'developer' => $inf_developer ?: 'PHP-Fusion',
                'email' => $inf_email,
                'url' => $inf_weburl,
                'image' => $inf_image ? $inf_image : 'infusion_panel.png',
                'folder' => $inf_folder,
                'newtable' => $inf_newtable,
                'newcol' => $inf_newcol,
                'dropcol' => $inf_dropcol,
                'insertdbrow' => $inf_insertdbrow,
                'droptable' => $inf_droptable,
                'altertable' => $inf_altertable,
                'deldbrow' => $inf_deldbrow,
                'sitelink' => $inf_sitelink,
                'adminpanel' => $inf_adminpanel,
                'mlt' => $inf_mlt,
                'mlt_insertdbrow' => $mlt_insertdbrow,
                'mlt_deldbrow' => $mlt_deldbrow,
                'delfiles' => $inf_delfiles
            );
            $result = dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder=:inf_folder", array(':inf_folder' => $folder));
            $infusion['status'] = dbrows($result)
                ? (version_compare($infusion['version'], dbresult($result, 0), ">")
                    ? 2
                    : 1)
                : 0;
        }

        return $infusion;
    }

}