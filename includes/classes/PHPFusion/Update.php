<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Update.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

use FilesystemIterator;
use PHPFusion\Installer\Batch;

class Update extends Installer\Infusions {
    /**
     * Temporary directory for downloads
     *
     * @var string
     */
    private $temp_dir = BASEDIR . 'temp/';

    /**
     * Install directory
     *
     * @var string
     */
    private $install_dir = BASEDIR;

    /**
     * Url to the update folder on the server
     *
     * @var string
     */
    private $update_url = 'https://raw.githubusercontent.com/PHPFusion/Archive/updates/';

    /**
     * Version filename on the server
     *
     * @var string
     */
    private $update_file = '9.json';

    /**
     * The URL from which the translations will be downloaded
     *
     * @var string
     */
    private $lang_url = 'https://www.php-fusion.co.uk/translations/tmp/v9/';

    /**
     * List of available languages
     *
     * @var string
     */
    private $available_languages = 'https://www.php-fusion.co.uk/translations/languages.php?version=9';

    /**
     * @var string
     */
    private $latest_version = '';

    /**
     * @var string
     */
    protected $current_version = '';

    /**
     * @var array
     */
    private $update = [];

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var array|string
     */
    private $locale;

    /**
     * AutoUpdate constructor
     */
    public function __construct() {
        $this->locale = fusion_get_locale( '', LOCALE . LOCALESET . 'admin/upgrade.php' );

        if (!is_dir( $this->temp_dir ) && !mkdir( $this->temp_dir, 0755, TRUE )) {
            $this->setError( sprintf( 'Could not create temporary directory %s.', $this->temp_dir ) );
        }

        ini_set( 'max_execution_time', 300 );

        $this->current_version = fusion_get_settings( 'version' );
    }

    /**
     * Set the update url
     *
     * @param string $update_url
     */
    public function setUpdateUrl( $update_url ) {
        $this->update_url = $update_url;
    }

    /**
     * Set the update file
     *
     * @param string $update_file
     */
    public function setUpdateFile( $update_file ) {
        $this->update_file = $update_file;
    }

    /**
     * Get the update url
     *
     * @return string
     */
    public function getUpdateUrl() {
        return $this->update_url . $this->update_file;
    }

    /**
     * Get the number of the latest version
     *
     * @return string
     */
    public function getLatestVersion() {
        return $this->latest_version;
    }

    /**
     * Check for a new version
     *
     * @param false $return_version
     *
     * @return array|bool
     */
    public function checkUpdate( $return_version = FALSE ) {
        if ($this->isValidUrl( $this->getUpdateUrl() )) {
            if (function_exists( 'curl_version' )) {
                $update = $this->downloadCurl( $this->getUpdateUrl() );
                if ($update === FALSE) {
                    $this->setError( sprintf( 'Could not download update file %s via curl!', $this->getUpdateUrl() ) );
                }
            } else {
                $update = @file_get_contents( $this->getUpdateUrl(), FALSE );
                if ($update === FALSE) {
                    $this->setError( sprintf( 'Could not download update file %s via file_get_contents!', $this->getUpdateUrl() ) );
                }
            }

            if ($update === FALSE) {
                return FALSE; // Could not check for updates
            }

            $versions = (array)json_decode( $update, FALSE );

            if (is_array( $versions )) {
                foreach ($versions as $version => $url) {
                    if (version_compare( $version, $this->current_version, '>' )) {
                        $this->latest_version = $version;
                        $this->update = ['version' => $version, 'url' => $url];
                    }
                }
            }

            if ($this->newVersionAvailable()) {
                if ($return_version == TRUE) {
                    return $this->update['version'];
                } else {
                    return TRUE;
                }
            }
        }

        return NULL; // No update available
    }

    /**
     * Check if a new version is available.
     *
     * @return bool
     */
    public function newVersionAvailable() {
        return version_compare( $this->latest_version, $this->current_version, '>' );
    }

    /**
     * Check if url is valid
     *
     * @param string $url
     *
     * @return bool
     */
    protected function isValidUrl( $url ) {
        if (filter_var( $url, FILTER_VALIDATE_URL ) !== FALSE) {
            return TRUE;
        } else {
            $this->setError( sprintf( 'Url %s is not valid.', $url ) );
            return FALSE;
        }
    }

    /**
     * Download file via curl
     *
     * @param string $url URL to file
     *
     * @return string|false
     */
    protected function downloadCurl( $url ) {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 5 );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $curl, CURLOPT_TIMEOUT, 5 );
        $update = curl_exec( $curl );

        $success = TRUE;
        if (curl_error( $curl )) {
            $success = FALSE;
        }
        curl_close( $curl );

        return ($success === TRUE) ? $update : FALSE;
    }

    /**
     * Download update
     *
     * @param string $update_url Url where to download from
     * @param string $update_file Path where to save the download
     *
     * @return bool
     */
    protected function downloadZip( $update_url, $update_file ) {
        $this->setMessage( sprintf( $this->locale['U_009'], $update_url ) );

        if ($this->isValidUrl( $update_url )) {
            if (function_exists( 'curl_version' )) {
                $update = $this->downloadCurl( $update_url );
                if ($update === FALSE) {
                    return FALSE;
                }
            } else if (ini_get( 'allow_url_fopen' )) {
                $update = @file_get_contents( $update_url, FALSE );
                if ($update === FALSE) {
                    $this->setError( sprintf( 'Could not download update "%s"!', $update_url ) );
                }
            }

            $handle = fopen( $update_file, 'wb' );
            if (!$handle) {
                $this->setError( sprintf( 'Could not open file handle to save update to "%s"!', $update_file ) );
                return FALSE;
            }

            if (!empty( $update )) {
                if (!fwrite( $handle, $update )) {
                    $this->setError( sprintf( 'Could not write update to file "%s"!', $update_file ) );
                    fclose( $handle );

                    return FALSE;
                }
            }

            fclose( $handle );
            return TRUE;
        }

        return NULL;
    }

    /**
     * @param string $zip_file Path to the update file
     * @param string $dest Destination directory
     *
     * @return bool
     */
    protected function extractFiles( $zip_file, $dest ) {
        $this->setMessage( $this->locale['U_010'] );

        $zip = new \ZipArchive();
        $resource = $zip->open( $zip_file );
        if ($resource !== TRUE) {
            $this->setError( sprintf( 'Could not open zip file "%s", error: %d', $zip_file, $resource ) );
            return FALSE;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $file_stats = $zip->statIndex( $i );
            $filename = str_replace( ['/', '\\'], DIRECTORY_SEPARATOR, $file_stats['name'] );
            $foldername = str_replace( ['/', '\\'], DIRECTORY_SEPARATOR, $dest . dirname( $filename ) );
            $absolute_filename = str_replace( ['/', '\\'], DIRECTORY_SEPARATOR, $dest );

            if (!is_dir( $foldername ) && !mkdir( $foldername, 0777, TRUE ) && !is_dir( $foldername )) {
                $this->setError( sprintf( 'Directory "%s" has to be writeable!', $foldername ) );
                return FALSE;
            }

            if ($filename[strlen( $filename ) - 1] === DIRECTORY_SEPARATOR) {
                continue;
            }

            if ($zip->extractTo( $absolute_filename, $file_stats['name'] ) === FALSE) {
                $this->setError( sprintf( 'Coud not read zip entry "%s"', $file_stats['filename'] ) );
            }
        }

        $zip->close();

        return TRUE;
    }

    /**
     * Copy files
     *
     * @param string $source
     * @param string $target
     * @param array $ignore
     */
    private function copyFiles( $source, $target, $ignore = [] ) {
        $this->setMessage( $this->locale['U_011'] );

        $directoryIterator = new \RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS );
        $iterator = new \RecursiveIteratorIterator( $directoryIterator, \RecursiveIteratorIterator::SELF_FIRST );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $dir = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                if (!is_dir( $dir )) {
                    mkdir( $dir );
                }
            } else {
                if (!in_array( $item->getFilename(), $ignore )) {
                    copy( $item, $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName() );
                }
            }
        }
    }

    /**
     * @param $method
     * @param $code_array
     *
     * @return bool
     *
     * @uses adminpanel_infuse
     * @uses dropcol_infuse
     * @uses sitelink_infuse
     * @uses mlt_insertdbrow_infuse
     * @uses mlt_adminpanel_infuse
     * @uses mlt_infuse
     * @uses altertable_infuse
     * @uses updatedbrow_infuse
     * @uses newtable_infuse
     * @uses newcol_infuse
     * @uses insertdbrow_infuse
     * @uses deldbrow_infuse
     */
    protected function doUpgradeBatch( $method, $code_array ) {
        try {
            $method = $method . '_infuse';
            return $this->$method( $code_array );
        } catch (\Exception $e) {
            if (!is_file( BASEDIR . 'installer_' . date( 'd-M-Y' ) . '.errors.log' )) {
                touch( BASEDIR . 'installer_' . date( 'd-M-Y' ) . '.errors.log' );
            }
            write_file( BASEDIR . 'installer_' . date( 'd-M-Y' ) . '.log.txt', $e->getMessage(), FILE_APPEND );
            return FALSE;
        }
    }

    /**
     * Run upgrade scripts
     *
     * @return bool
     */
    private function doDbUpgrade() {
        $to_upgrade = Batch::getInstance()->checkUpgrades();
        if (!empty( $to_upgrade )) {
            $this->setMessage( $this->locale['U_012'] );

            foreach ($to_upgrade as $file_upgrades) {
                if (!empty( $file_upgrades )) {
                    foreach ($file_upgrades as $callback_method => $upgrades) {
                        if (!empty( $upgrades )) {
                            $method = $callback_method . '_infuse';
                            if (method_exists( $this, $method )) {
                                $this->setMessage( 'Running db function' . $method );
                                $this->doUpgradeBatch( $callback_method, [$callback_method => $upgrades] );
                            }
                        }
                    }
                }
            }

            return TRUE;
        }

        return NULL;
    }

    /**
     * Get enabled languages
     *
     * @return false|string[]
     */
    public function getEnabledLanguages() {
        $enabled_languages = fusion_get_settings( 'enabled_languages' );

        if (!empty( $enabled_languages ) && $enabled_languages !== 'English') {
            return explode( '.', $enabled_languages );
        }

        return FALSE;
    }

    /**
     * Update languages
     *
     * @return bool
     */
    public function updateLanguages() {
        $enabled_languages = $this->getEnabledLanguages();

        if (is_array( $enabled_languages )) {
            foreach ($enabled_languages as $language) {
                if ($language !== 'English') {
                    $this->downloadLanguage( $language );
                }
            }

            $this->setMessage( $this->locale['U_017'] );

            return TRUE;
        }

        return NULL;
    }

    /**
     * Download language
     *
     * @param string $language
     *
     * @return bool
     */
    public function downloadLanguage( $language ) {
        $this->setMessage( sprintf( $this->locale['U_018'], $language ) );

        $lang_pack_zip = $this->temp_dir . $language . '.zip';

        if (!is_file( $lang_pack_zip )) {
            if (!$this->downloadZip( $this->lang_url . $language . '.zip', $lang_pack_zip )) {
                $this->setError( sprintf( 'Failed to download pack from %s to %s!', $this->lang_url . $language, $lang_pack_zip ) );
                return FALSE;
            }
        }

        $dest = $this->temp_dir . $language . '/';

        if (is_file( $lang_pack_zip )) {
            $this->extractFiles( $lang_pack_zip, $dest );
        }

        if (is_dir( $dest )) {
            $this->copyFiles( $dest, $this->install_dir );
        }

        if (!unlink( $lang_pack_zip )) {
            $this->setError( sprintf( 'Could not delete lang pack "%s"!', $lang_pack_zip ) );
        }

        if (is_dir( $dest )) {
            rrmdir( $dest );
        }

        return TRUE;
    }

    /**
     * Update core
     *
     * @return bool
     */
    private function updateCoreFiles() {
        if ($this->newVersionAvailable()) {
            if (empty( $this->temp_dir ) || !is_dir( $this->temp_dir ) || !is_writable( $this->temp_dir )) {
                $this->setError( sprintf( 'Temporary directory "%s" does not exist or is not writeable!', $this->temp_dir ) );
                return FALSE;
            }

            if (empty( $this->install_dir ) || !is_dir( $this->install_dir ) || !is_writable( $this->install_dir )) {
                $this->setError( sprintf( 'Install directory "%s" does not exist or is not writeable!', $this->install_dir ) );
                return FALSE;
            }

            $update_zip_file = $this->temp_dir . $this->update['version'] . '.zip';

            if (!is_file( $update_zip_file )) {
                if (!$this->downloadZip( $this->update['url'], $update_zip_file )) {
                    $this->setError( sprintf( 'Failed to download update from %s to %s!', $this->update['url'], $update_zip_file ) );
                    return FALSE;
                }
            }

            if (is_file( $update_zip_file ) && !$this->extractFiles( $update_zip_file, $this->temp_dir )) {
                return FALSE;
            }

            if (is_dir( $this->temp_dir . 'files/' )) {
                $this->copyFiles( $this->temp_dir . 'files/', $this->install_dir, ['robots.txt'] );
            }

            if (!unlink( $update_zip_file )) {
                $this->setError( sprintf( 'Could not delete update file "%s"!', $update_zip_file ) );
            }

            if (is_array( $this->getEnabledLanguages() )) {
                if (!$this->updateLanguages()) {
                    $this->setError( 'An error occurred while updating the translations. After the update, you can update the translations separately.' );
                }
            }

            if (!$this->doDbUpgrade()) {
                $this->setError( 'An error occurred while upgrading the database.' );
                return FALSE;
            }

            if (is_dir( $this->temp_dir )) {
                rrmdir( $this->temp_dir );
            }

            if (file_exists( BASEDIR . 'install.php' )) {
                @unlink( BASEDIR . 'install.php' );
            }

            return TRUE;
        }

        return NULL;
    }

    /**
     * Run upgrade
     */
    public function upgradeCms() {
        $result = $this->updateCoreFiles();

        if ($result == TRUE) {
            $this->setMessage( $this->locale['U_014'] );
        } else if ($result == FALSE) {
            $this->setMessage( $this->locale['U_015'] );
        }
    }

    /**
     * Get available languages
     *
     * @return array|false
     */
    public function getAvailableLanguages() {
        if ($this->isValidUrl( $this->available_languages )) {
            if (function_exists( 'curl_version' )) {
                $list = $this->downloadCurl( $this->available_languages );
                if ($list === FALSE) {
                    $this->setError( sprintf( 'Could not download update file %s via curl!', $this->available_languages ) );
                }
            } else {
                $list = @file_get_contents( $this->available_languages, FALSE );
                if ($list === FALSE) {
                    $this->setError( sprintf( 'Could not download update file %s via file_get_contents!', $this->available_languages ) );
                }
            }

            if ($list === FALSE) {
                return FALSE; // Could not check for available languages
            }

            return (array)json_decode( $list, FALSE );
        }

        return NULL;
    }

    /**
     * Ajax checker
     */
    public function ajaxChecker() {

        $this->locale += fusion_get_locale( '', LOCALE . LOCALESET . 'admin/main.php' );

        $settings = fusion_get_settings();

        $result = ['status' => 300];

        if (
            ($settings['update_checker'] == 1 && ($settings['update_last_checked'] < (time() - 21600))) || // check every 6 hours
            (get( 'force' ) == 'true')
        ) {

            dbquery( "UPDATE " . DB_SETTINGS . " SET settings_value=:time WHERE settings_name=:name", [':time' => time(), ':name' => 'update_last_checked'] );

            $version = $this->checkUpdate( TRUE );

            if (!empty( $version ) && version_compare( $version, $settings['version'], '>' )) {

                $text = sprintf( $this->locale['new_update_avalaible'], $version );

                $text .= ' <a class="btn btn-primary btn-sm m-l-10" href="' . $settings['siteurl'] . 'administration/upgrade.php' . fusion_get_aidlink() . '">' . $this->locale['update_now'] . '</a>';

                $result = ['result' => $text, 'status' => 200];

            } else {

                if (check_get( 'force' ) && get( 'force' ) == 'true') {

                    $result = ['result' => $this->locale['U_006'], 'status' => 400];
                }
            }

        }

        header( 'Content-Type: application/json' );
        echo json_encode( $result );
    }

    /**
     * Set message
     *
     * @param $message
     */
    private function setMessage( $message ) {
        $this->messages[] = $message;
    }

    /**
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * @param $message
     */
    private function setError( $message ) {
        set_error( 1, $message, debug_backtrace()[1]['file'], debug_backtrace()[1]['line'] );
    }
}
