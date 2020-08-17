<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: QuantumModules.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\UserFields\Quantum;

use Exception;
use PHPFusion\UserFieldsQuantum;

/**
 * Class QuantumModules
 *
 * @package PHPFusion\UserFields\Quantum
 */
class QuantumModules {

    private $class;

    private $modules_list = [];

    private $enabled_modules = [];

    private $loaded_modules = [];

    private $available_field_info = [];

    /**
     * QuantumModules constructor.
     *
     * @param UserFieldsQuantum $class
     */
    public function __construct( UserFieldsQuantum $class ) {

        $this->class = $class;

        $this->enabled_modules = $class->getEnabledFields();

        $this->modules_list = $this->cacheModuleFiles();

        if ( !empty( $this->modules_list ) ) {

            $locale = fusion_get_locale();

            foreach ( $this->modules_list as $plugin_name => $plugin ) {

                $this->loadModule( $plugin_name, $plugin, $locale );

            }
        }
    }

    private function cacheModuleFiles() {

        static $modules_list = [];

        if ( empty( $modules_list ) ) {

            foreach ( $this->class->plugin_folder as $plugin_folder ) { // infusions/ user_fields.

                $plugin_folder = rtrim( $plugin_folder, '/' ).'/';

                $folders = makefilelist( $plugin_folder, '.|..|index.php', TRUE, 'folders' );

                if ( !empty( $folders ) ) {

                    foreach ( $folders as $module ) {

                        $path = $plugin_folder.$module.'/'.$module.'_include.php';

                        $var_path = $plugin_folder.$module.'/'.$module.'_include_var.php';

                        if ( $plugin_folder == INFUSIONS ) {

                            $uf_folder_path = $plugin_folder.$module.'/user_fields/';
                            if ( file_exists( $uf_folder_path ) ) {

                                $files = makefilelist( $uf_folder_path, '.|..|._DS_Store|index.php' );

                                if ( !empty( $files ) ) {

                                    $regex = '/(.*?)_include_var\.php/';
                                    $var_files_matches = preg_grep( $regex, $files );

                                    if ( !empty( $var_files_matches ) ) {

                                        foreach ( $var_files_matches as $var_files ) {

                                            $curent_var_path = $uf_folder_path.$var_files;

                                            $include_path = $uf_folder_path.str_replace( '_var', '', $var_files );

                                            if ( is_file( $include_path ) ) {

                                                $var_path = $curent_var_path;

                                                $module = $this->getModuleName( $var_path );

                                                $path = $include_path;

                                                if ( file_exists( $path ) && file_exists( $var_path ) ) {
                                                    // i also need var file
                                                    $modules_list[ $module ]['var'] = $var_path;
                                                    $modules_list[ $module ]['include'] = $path;

                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            if ( file_exists( $path ) && file_exists( $var_path ) ) {
                                // i also need var file
                                $modules_list[ $module ]['var'] = $var_path;
                                $modules_list[ $module ]['include'] = $path;

                            }
                        }
                    }
                }
            }
        }

        return $modules_list;
    }

    /**
     * @param $file_path
     *
     * @return string
     */
    private function getModuleName( $file_path ) {
        $user_field_dbname = '';
        include $file_path;
        return $user_field_dbname;

    }

    /**
     * Var File Loader
     *
     * @param $plugin_name
     * @param $plugin
     * @param $locale
     */
    private function loadModule( $plugin_name, $plugin, $locale ) {

        $user_field_name = '';
        $user_field_desc = '';
        $user_field_dbname = '';
        $user_field_group = '';
        $user_field_dbinfo = '';
        $user_field_author = '';
        $user_field_api_version = '';
        $user_field_image = '';

        try {

            include $plugin['var'];

            $this->loaded_modules[ $plugin_name ] = [
                'user_field_name'      => $user_field_name,
                'user_field_dbname'    => $user_field_name,
                'user_field_desc'      => $user_field_desc,
                'user_field_dbname'    => $user_field_dbname,
                'user_field_group'     => $user_field_group,
                'user_field_dbinfo'    => $user_field_dbinfo,
                'user_field_author'    => $user_field_author,
                'user_field_version'   => $user_field_api_version,
                'module_file_path'     => $plugin['include'],
                'module_var_file_path' => $plugin['var'],
                'module_image'         => $user_field_image,
            ];


            if ( !in_array( $plugin_name, $this->enabled_modules ) ) {

                $this->available_field_info[ $plugin_name ] = [
                    'title'       => $user_field_name,
                    'description' => $user_field_desc
                ];

            }

        } catch ( Exception $e ) {

            trigger_error( $e->getMessage() );
        }

    }

    /**
     * @return array
     */
    public function getAvailableModulesInfo() {
        return $this->available_field_info;
    }

    /**
     * @return mixed
     */
    public function getLoadedModules() {
        return $this->loaded_modules;

    }

    /**
     * @return array
     */
    public function getModulesList() {
        return $this->modules_list;
    }

}
