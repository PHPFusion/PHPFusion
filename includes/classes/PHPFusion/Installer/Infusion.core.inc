<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Infusion.core.inc
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Installer;

/**
 * Class Infusion_Core
 *
 * @package PHPFusion\Installer
 */
class Infusion_Core {

    private static $locale = [];
    private static $instance = NULL;
    private static $inf = [];

    /**
     * Version 7 compatible infusions load constants.
     */
    public static function load_Configuration() {
        /*
         * Non-core infusions_db.php inclusion.
         * These files are supposed to be infusion_db.php using Infusion SDK
         * Cannot contain and make it global because it contains dangerous APIs and remote executions.
         */
        $inf_folder = makefilelist(INFUSIONS, '.|..|.htaccess|index.php|._DS_Store|.tmp', TRUE, 'folders');
        if (!empty($inf_folder)) {
            foreach ($inf_folder as $folder) {
                $inf_include = INFUSIONS.$folder."/infusion_db.php";
                if (file_exists($inf_include)) {
                    include $inf_include;
                }
            }
        }
    }

    protected static function adminpanel_infuse($inf) {
        $error = FALSE;

        if ($inf['adminpanel'] && is_array($inf['adminpanel'])) {

            foreach ($inf['adminpanel'] as $adminpanel) {
                // auto recovery
                if (!empty($adminpanel['rights'])) {
                    dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".$adminpanel['rights']."'");
                }
                $link_prefix = (defined('ADMIN_PANEL') ? '' : '../').INFUSIONS.$inf['folder'].'/';
                $inf_admin_image = ($adminpanel['image'] ?: "infusion_panel.png");
                if (empty($adminpanel['page'])) {
                    $item_page = 5;
                } else {
                    $item_page = isnum($adminpanel['page']) ? $adminpanel['page'] : 5;
                }
                if (!dbcount("(admin_id)", DB_ADMIN, "admin_rights='".$adminpanel['rights']."'")) {
                    $adminpanel += array(
                        'rights' => '',
                        'title'  => '',
                        'panel'  => '',
                    );
                    $insert_sql = "INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('".$adminpanel['rights']."', '".$link_prefix.$inf_admin_image."', '".$adminpanel['title']."', '".$link_prefix.$adminpanel['panel']."', '".$item_page."')";
                    $result = dbquery($insert_sql);
                    if (dbrows($result)) {
                        $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level <=:admin AND ".in_group('user_rights', 'I'), [':admin' => USER_LEVEL_ADMIN]);
                        while ($data = dbarray($result)) {
                            dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".".$adminpanel['rights']."' WHERE user_id='".$data['user_id']."'");
                        }
                    }
                } else {
                    $error = TRUE;
                }
            }
        }

        return $error;
    }

    /**
     * Execute Installation according to Infusion Standard Developer Kit
     *
     * @param $folder
     *
     * @return bool
     */
    public function infuse($folder) {
        $error = FALSE;
        if ((self::$inf = self::load_infusion($folder))) {
            $result = dbquery("SELECT inf_id, inf_version FROM ".DB_INFUSIONS." WHERE inf_folder=:folder", array(':folder' => $folder));
            if (dbrows($result) > 0) {
                $data = dbarray($result);
                if (self::$inf['version'] > $data['inf_version']) {
                    $upgrade_folder_path = INFUSIONS.self::$inf['folder']."/upgrade/";
                    if (file_exists($upgrade_folder_path)) {
                        $upgrade_files = makefilelist($upgrade_folder_path, ".|..|index.php", TRUE);
                        if (!empty($upgrade_files) && is_array($upgrade_files)) {
                            foreach ($upgrade_files as $upgrade_file) {
                                /*
                                 * This will check file names (File name convention) against current infusion version
                                 * As we have multiple upgrade files - each will be called. As the query is not done in array
                                 * excepted for newcol method, please ensure you make checks before attaching the array into
                                 * the callback.
                                 *
                                 * The version of the CMS is irrelevant. Infusion can be upgraded as many times as the authors
                                 * make it available to be distributed. (i.e. they can say Version 1 is for Version 9 of the CMS)
                                 * in their own website, Version 2 is for Version 10 of the CMS etc). Apps and CMS are not tied
                                 * together in terms of version-ing, as PHP-Fusion does not track it as we do not maintain them.
                                 *
                                 * When developing upgrades, people should not just make insertions and declare without checking
                                 * if the table exist or column exist as renaming a non-existent table could not be performed.
                                 *
                                 */
                                $filename = rtrim($upgrade_file, 'upgrade.inc');
                                if (version_compare($filename, $data['inf_version'], ">")) {
                                    $upgrades = self::load_upgrade(INFUSIONS.$folder, $upgrade_folder_path.$upgrade_file);
                                    foreach ($upgrades as $callback_method => $statement_type) {
                                        $method = $callback_method."_infuse";
                                        if (method_exists(self::$instance, $method)) {
                                            $error = self::$instance->$method($upgrades);
                                        }
                                        if ($error) {
                                            \defender::stop();
                                            addNotice('danger', self::$locale['403']);

                                            return $error;
                                        }
                                    }
                                    if ($error === FALSE) {
                                        dbquery("UPDATE ".DB_INFUSIONS." SET inf_version=:version WHERE inf_id=:id", array(
                                            ':version' => self::$inf['version'],
                                            ':id'      => $data['inf_id'],
                                        ));
                                    }
                                }

                            }
                        }
                    }
                }
            } else {
                foreach (self::$inf as $callback_method => $statement_type) {
                    $method = $callback_method."_infuse";
                    if (method_exists(self::$instance, $method)) {
                        $error = self::getInstance()->$method(self::$inf);
                    }
                    if ($error) {
                        addNotice('danger', self::$locale['403']);

                        return $error;
                    }
                }
                if ($error === FALSE) {
                    if (dbcount("(inf_title)", DB_INFUSIONS, "inf_folder='".self::$inf['folder']."'")) {
                        dbquery("DELETE FROM ".DB_INFUSIONS." WHERE inf_folder='".self::$inf['folder']."'");
                    }
                    addNotice("success", sprintf(self::$locale['423'], self::$inf['title']));
                    dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".self::$inf['title']."', '".self::$inf['folder']."', '".self::$inf['version']."')");
                }
            }
        }

        redirect(FUSION_REQUEST);
    }

    /**
     * Load Infusion according to Infusion Standard Developer Kit
     *
     * @param string $folder
     *
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
        $db_prefix = DB_PREFIX;
        $cookie_prefix = COOKIE_PREFIX;

        if (is_dir(INFUSIONS.$folder) && file_exists(INFUSIONS.$folder."/infusion.php")) {

            include(INFUSIONS.$folder."/infusion.php");

            $inf_image_tmp = !empty($inf_image) && file_exists(ADMIN."images/".$inf_image) ? ADMIN."images/".$inf_image : ADMIN."images/infusion_panel.png";

            if (!empty($inf_image) && file_exists(INFUSIONS.$inf_folder."/".$inf_image)) {
                $inf_image = INFUSIONS.$inf_folder."/".$inf_image;
            } else {
                $inf_image = $inf_image_tmp;
            }

            $infusion = array(
                'name'            => str_replace('_', ' ', $inf_title),
                'title'           => $inf_title,
                'description'     => $inf_description,
                'version'         => $inf_version ?: 'beta',
                'developer'       => $inf_developer ?: 'PHP-Fusion',
                'email'           => $inf_email,
                'url'             => $inf_weburl,
                'image'           => $inf_image ? $inf_image : 'infusion_panel.png',
                'folder'          => $inf_folder,
                'newtable'        => $inf_newtable,
                'newcol'          => $inf_newcol,
                'dropcol'         => $inf_dropcol,
                'insertdbrow'     => $inf_insertdbrow,
                'droptable'       => $inf_droptable,
                'altertable'      => $inf_altertable,
                'deldbrow'        => $inf_deldbrow,
                'sitelink'        => $inf_sitelink,
                'adminpanel'      => $inf_adminpanel,
                'mlt'             => $inf_mlt,
                'mlt_insertdbrow' => $mlt_insertdbrow,
                'mlt_deldbrow'    => $mlt_deldbrow,
                'delfiles'        => $inf_delfiles
            );
            $result = dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder=:inf_folder", array(':inf_folder' => $folder));
            /*
             * Status Remarks
             * 2 - When upgrade is a must
             * 1 - Nothing to upgrade
             * 0 - Infusions not found.
             */
            $infusion['status'] = dbrows($result)
                ? (version_compare($infusion['version'], dbresult($result, 0), ">")
                    ? 2
                    : 1)
                : 0;
        }

        return $infusion;
    }

    /**
     * @param $folder
     * @param $upgrade_file_path
     *
     * @return array
     */
    public static function load_upgrade($folder, $upgrade_file_path) {
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
        $inf_altertable = array();
        $inf_newcol = array();
        $inf_updatedbrow = array();
        $inf_sitelink = array();
        $inf_adminpanel = array();
        $inf_mlt = array();
        $mlt_insertdbrow = array();
        $inf_insertdbrow = array();
        $inf_dropcol = array();
        $inf_droptable = array();
        $inf_delfiles = array();
        $inf_deldbrow = array();
        $mlt_deldbrow = array();
        $db_prefix = DB_PREFIX;
        $cookie_prefix = COOKIE_PREFIX;

        if (is_dir($folder) && file_exists($upgrade_file_path)) {

            include $upgrade_file_path;

            $inf_image_tmp = !empty($inf_image) && file_exists(ADMIN."images/".$inf_image) ? ADMIN."images/".$inf_image : ADMIN."images/infusion_panel.png";
            if (!empty($inf_image) && file_exists(INFUSIONS.$inf_folder."/".$inf_image)) {
                $inf_image = INFUSIONS.$inf_folder."/".$inf_image;
            } else {
                $inf_image = $inf_image_tmp;
            }

            $infusion = array(
                'name'            => str_replace('_', ' ', $inf_title),
                'title'           => $inf_title,
                'description'     => $inf_description,
                'version'         => $inf_version ?: 'beta',
                'developer'       => $inf_developer ?: 'PHP-Fusion',
                'email'           => $inf_email,
                'url'             => $inf_weburl,
                'image'           => $inf_image ? $inf_image : 'infusion_panel.png',
                'folder'          => $inf_folder,
                'newtable'        => $inf_newtable,
                'altertable'      => $inf_altertable,
                'newcol'          => $inf_newcol,
                'sitelink'        => $inf_sitelink,
                'adminpanel'      => $inf_adminpanel,
                'mlt'             => $inf_mlt,
                'insertdbrow'     => $inf_insertdbrow,
                'updatedbrow'     => $inf_updatedbrow,
                'mlt_insertdbrow' => $mlt_insertdbrow,
                'deldbrow'        => $inf_deldbrow,
                'mlt_deldbrow'    => $mlt_deldbrow,
                'delfiles'        => $inf_delfiles,
                'dropcol'         => $inf_dropcol,
                'droptable'       => $inf_droptable,
            );

            $folder = str_replace(INFUSIONS, '', $folder);
            $result = dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder=:inf_folder", array(':inf_folder' => $folder));
            $infusion['status'] = dbrows($result)
                ? (version_compare($infusion['version'], dbresult($result, 0), ">=")
                    ? 2
                    : 1)
                : 0;
        }

        return $infusion;
    }

    // Method : Insert admin panel links
    /**
     * @return null|static
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        self::$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");

        return self::$instance;
    }

    // Method: Install single site links
    /**
     * @param $folder
     */
    public function defuse($folder) {

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
                if (file_exists($folder) && is_dir($folder)) {
                    $files = makefilelist($folder, ".|..|index.php", TRUE);
                    if (!empty($files)) {
                        foreach ($files as $filename) {
                            unlink($folder.$filename);
                        }
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
        addNotice("success", sprintf(self::$locale['424'], $inf['title']));
        redirect(FUSION_REQUEST);
    }

    // Method: drop column
    protected function dropcol_infuse($inf) {
        $error = FALSE;
        if (isset($inf['dropcol']) && is_array($inf['dropcol'])) {
            foreach ($inf['dropcol'] as $dropCol) {
                if (is_array($dropCol) && !empty($dropCol['table']) && !empty($dropCol['column'])) {
                    $columns = fieldgenerator($dropCol['table']);
                    if (in_array($dropCol['column'], $columns)) {
                        if (!dbquery("ALTER TABLE ".$dropCol['table']." DROP COLUMN ".$dropCol['column'])) {
                            $error = TRUE;
                        }
                    }
                }
            }
        }

        return $error;
    }

    // Method: Multilanguage multiple insert rows
    protected function sitelink_infuse($inf) {
        $error = FALSE;
        if ($inf['sitelink'] && is_array($inf['sitelink'])) {
            $last_id = 0;

            foreach ($inf['sitelink'] as $sitelink) {

                $link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS), 0) + 1;

                $sitelink += array(
                    "title"      => "",
                    "cat"        => 0,
                    "url"        => "",
                    "icon"       => "",
                    "visibility" => 0,
                    "position"   => 3,
                );

                $link_url_path = "".str_replace("../", "", INFUSIONS).$inf['folder']."/";

                if (!empty($sitelink['cat']) && $sitelink['cat'] == "{last_id}" && !empty($last_id)) {

                    $sitelink['cat'] = $last_id;

                    $result = dbquery("INSERT INTO ".DB_SITE_LINKS."
                    (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order)
                    VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".$link_url_path.$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");

                } else {

                    $result = dbquery("INSERT INTO ".DB_SITE_LINKS."
                    (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order)
                    VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".$link_url_path.$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");

                    $last_id = dblastid();
                }

                if (!$result) {
                    $error = TRUE;
                }

            }
        }

        return $error;
    }

    // Method: Register Multilang rights

    protected function mlt_insertdbrow_infuse($inf) {
        $error = FALSE;
        if ($inf['mlt_insertdbrow'] && is_array($inf['mlt_insertdbrow'])) {
            foreach (fusion_get_enabled_languages() as $current_language => $language_translations) {
                if (isset($inf['mlt_insertdbrow'][$current_language])) {
                    $last_id = 0;
                    foreach ($inf['mlt_insertdbrow'][$current_language] as $insertdbrow) {
                        if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                            $result = dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                        } else {
                            $result = dbquery("INSERT INTO ".$insertdbrow);
                            $last_id = dblastid();
                        }
                        if (!$result) {
                            $error = TRUE;
                        }
                    }
                }
            }
        }

        return $error;
    }

    // Method: Alter table

    protected function mlt_infuse($inf) {
        $error = FALSE;
        if ($inf['mlt'] && is_array($inf['mlt'])) {
            foreach ($inf['mlt'] as $mlt) {
                if (dbcount("(mlt_rights)", DB_LANGUAGE_TABLES, "mlt_rights = '".$mlt['rights']."'")) {
                    dbquery("DELETE FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$mlt['rights']."'");
                }
                $result = dbquery("INSERT INTO ".DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('".$mlt['rights']."', '".$mlt['title']."', '1')");
                if (!$result) {
                    $error = TRUE;
                }
            }
        }

        return $error;
    }

    // Method: Update table rows

    protected function altertable_infuse($inf) {
        $error = FALSE;
        if ($inf['altertable'] && is_array($inf['altertable'])) {
            foreach ($inf['altertable'] as $altertable) {
                $result = dbquery("ALTER TABLE ".$altertable);
                if (!$result) {
                    $error = TRUE;
                }
            }
        }

        return $error;
    }

    // Method: Create new tables

    protected function updatedbrow_infuse($inf) {
        $error = FALSE;
        if ($inf['updatedbrow'] && is_array($inf['updatedbrow'])) {
            foreach ($inf['updatedbrow'] as $updatedbrow) {
                $result = dbquery("UPDATE ".$updatedbrow);
                if (!$result) {
                    $error = TRUE;
                }
            }
        }

        return $error;
    }

    // Method: Install new column

    protected function newtable_infuse($inf) {
        $error = FALSE;
        if ($inf['newtable'] && is_array($inf['newtable'])) {
            foreach ($inf['newtable'] as $newtable) {
                $table_name = fusion_first_words($newtable, 1, '');
                if (!db_exists($table_name)) {
                    $result = dbquery("CREATE TABLE ".$newtable);
                    if (!$result) {
                        $error = TRUE;
                    }
                }
            }
        }

        return $error;
    }

    // Method: Insert rows

    protected function newcol_infuse($inf) {
        $error = FALSE;
        static $table_schema = [];
        if (!empty($inf['newcol']) && is_array($inf['newcol'])) {
            foreach ($inf['newcol'] as $newCol) {
                if (is_array($newCol) && !empty($newCol['table']) && !empty($newCol['column']) && !empty($newCol['column_type'])) {
                    if (empty($table_schema[$newCol['table']])) {
                        $table_schema[$newCol['table']] = fieldgenerator($newCol['table']);
                    }
                    $count = count($table_schema[$newCol['table']]);
                    if (!in_array($newCol['column'], $table_schema[$newCol['table']])) {
                        $result = dbquery("ALTER TABLE ".$newCol['table']." ADD ".$newCol['column']." ".$newCol['column_type']." AFTER ".$table_schema[$newCol['table']][$count - 1]);
                        if (!$result) {
                            $error = TRUE;
                        }
                    }
                }
            }
        }

        return $error;
    }

    protected function insertdbrow_infuse($inf) {
        $error = FALSE;
        if ($inf['insertdbrow'] && is_array($inf['insertdbrow'])) {
            $last_id = 0;
            foreach ($inf['insertdbrow'] as $insertdbrow) {
                if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                    $result = dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                } else {
                    $result = dbquery("INSERT INTO ".$insertdbrow);
                    $last_id = dblastid();
                }
                if (!$result) {
                    $error = TRUE;
                }
            }
        }

        return $error;
    }

    protected function deldbrow_infuse($inf) {
        $error = FALSE;
        if ($inf['deldbrow'] && is_array($inf['deldbrow']) && isset($inf['status']) && $inf['status'] > 0) {
            foreach ($inf['deldbrow'] as $deldbrow) {
                $result = dbquery("DELETE FROM ".$deldbrow);
                if (!$result) {
                    $error = TRUE;
                }
            }
        }

        return $error;
    }
}
