<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: AutoUpdate.php
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
namespace PHPFusion;

use Exception;
use RuntimeException;
use ZipArchive;

/**
 * Auto update class
 * Based on https://github.com/VisualAppeal/PHP-Auto-Update
 */
class AutoUpdate {
    /**
     * The latest version.
     *
     * @var string
     */
    private $latestVersion = '';

    /**
     * Current version.
     *
     * @var string
     */
    protected $currentVersion = '';

    /**
     * Updates not yet installed.
     *
     * @var array
     */
    private $updates;

    /**
     * Temporary download directory.
     *
     * @var string
     */
    private $tempDir = BASEDIR.'temp/';

    /**
     * Install directory.
     *
     * @var string
     */
    private $installDir = BASEDIR;

    /**
     * Url to the update folder on the server.
     *
     * @var string
     */
    protected $updateUrl = 'https://raw.githubusercontent.com/PHPFusion/Archive/updates/';

    /**
     * Version filename on the server.
     *
     * @var string
     */
    protected $updateFile = '9.json';

    /**
     * Create new folders with this privileges.
     *
     * @var int
     */
    public $dirPermissions = 0755;

    private $message;

    private $versions;

    /**
     * No update available.
     */
    const NO_UPDATE_AVAILABLE = 0;

    /**
     * Could not check for last version.
     */
    const ERROR_VERSION_CHECK = 20;

    /**
     * Temp directory does not exist or is not writable.
     */
    const ERROR_TEMP_DIR = 30;

    /**
     * Install directory does not exist or is not writable.
     */
    const ERROR_INSTALL_DIR = 35;

    /**
     * Could not download update.
     */
    const ERROR_DOWNLOAD_UPDATE = 40;

    /**
     * Could not delete zip update file.
     */
    const ERROR_DELETE_TEMP_UPDATE = 50;

    /**
     * Create new instance
     *
     * @param string|null $tempDir
     * @param string|null $installDir
     * @param int         $maxExecutionTime
     */
    public function __construct($tempDir = NULL, $installDir = NULL, $maxExecutionTime = 60) {
        if (!empty($tempDir)) {
            $this->setTempDir($tempDir);
        }

        if (!empty($installDir)) {
            $this->setInstallDir($installDir);
        }

        ini_set('max_execution_time', $maxExecutionTime);
    }

    /**
     * Set the temporary download directory.
     *
     * @param string $dir
     *
     * @return bool
     */
    public function setTempDir($dir) {
        if (!is_dir($dir)) {
            $this->setMessage(sprintf('Creating new temporary directory "%s"', $dir));

            if (!mkdir($dir, 0755, TRUE) && !is_dir($dir)) {
                $this->setMessage(sprintf('Could not create temporary directory "%s"', $dir));

                return FALSE;
            }
        }

        $this->tempDir = $dir;

        return TRUE;
    }

    /**
     * Set the install directory.
     *
     * @param string $dir
     *
     * @return bool
     */
    public function setInstallDir($dir) {
        if (!is_dir($dir)) {
            $this->setMessage(sprintf('Creating new install directory "%s"', $dir));

            if (!mkdir($dir, 0755, TRUE) && !is_dir($dir)) {
                $this->setMessage(sprintf('Could not create install directory "%s"', $dir));

                return FALSE;
            }
        }

        $this->installDir = $dir;

        return TRUE;
    }

    /**
     * Set the update filename.
     *
     * @param string $updateFile
     *
     * @return AutoUpdate
     */
    public function setUpdateFile($updateFile) {
        $this->updateFile = $updateFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdateFile() {
        return $this->updateFile;
    }

    /**
     * Set the update filename.
     *
     * @param string $updateUrl
     *
     * @return AutoUpdate
     */
    public function setUpdateUrl($updateUrl) {
        $this->updateUrl = $updateUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdateUrl() {
        return $this->updateUrl;
    }

    /**
     * Set the version of the current installed software.
     *
     * @param string $currentVersion
     *
     * @return AutoUpdate
     */
    public function setCurrentVersion($currentVersion) {
        $this->currentVersion = $currentVersion;

        return $this;
    }

    /**
     * Get the name of the latest version.
     *
     * @return string
     */
    public function getLatestVersion() {
        return $this->latestVersion;
    }

    /**
     * Check for a new version
     *
     * @param int $timeout Download timeout in seconds (Only applied for downloads via curl)
     *
     * @return int|bool|array
     *         true: New version is available
     *         false: Error while checking for update
     *         int: Status code (i.e. AutoUpdate::NO_UPDATE_AVAILABLE)
     * @throws DownloadException
     * @throws ParserException
     */
    public function checkUpdate($timeout = 10, $return_file = FALSE) {
        $this->setMessage('Checking for a new update...');

        // Reset previous updates
        $this->latestVersion = '0.0.0';
        $this->updates = [];

        // Create absolute url to update file
        $updateFile = $this->updateUrl.$this->updateFile;

        // Check if cache is empty
        if ($this->versions === NULL || $this->versions === FALSE) {
            $this->setMessage(sprintf('Get new updates from %s', $updateFile));

            // Read update file from update server
            if (function_exists('curl_version') && $this->isValidUrl($updateFile)) {
                $update = $this->downloadCurl($updateFile, $timeout);

                if ($update === FALSE) {
                    $this->setMessage(sprintf('Could not download update file "%s" via curl!', $updateFile));

                    throw new DownloadException($updateFile);
                }
            } else {
                $update = @file_get_contents($updateFile, FALSE);

                if ($update === FALSE) {
                    $this->setMessage(sprintf('Could not download update file "%s" via file_get_contents!',
                        $updateFile));

                    throw new DownloadException($updateFile);
                }
            }

            $this->versions = (array)json_decode($update, FALSE);
            if (!is_array($this->versions)) {
                $this->setMessage('Unable to parse json update file!');

                throw new ParserException(sprintf('Could not parse update json file %s!', $this->updateFile));
            }
        } else {
            $this->setMessage('Got updates from cache');
        }

        if (!is_array($this->versions)) {
            $this->setMessage(sprintf('Could not read versions from server %s', $updateFile));

            return FALSE;
        }

        // Check for latest version
        foreach ($this->versions as $version => $updateUrl) {
            if (version_compare($version, $this->currentVersion, '>')) {
                if (version_compare($version, $this->latestVersion, '>')) {
                    $this->latestVersion = $version;
                }

                $this->updates[] = [
                    'version' => $version,
                    'url'     => $updateUrl,
                ];
            }
        }

        // Sort versions to install
        usort($this->updates, static function ($a, $b) {
            if (version_compare($a['version'], $b['version'], '==')) {
                return 0;
            }

            return version_compare($a['version'], $b['version'], '<') ? -1 : 1;
        });

        if ($this->newVersionAvailable()) {
            $this->setMessage(sprintf('New version "%s" available', $this->latestVersion));

            if ($return_file == TRUE) {
                return $this->updates;
            } else {
                return TRUE;
            }
        }

        $this->setMessage('No new version available');

        return self::NO_UPDATE_AVAILABLE;
    }

    /**
     * Check if a new version is available.
     *
     * @return bool
     */
    public function newVersionAvailable() {
        return version_compare($this->latestVersion, $this->currentVersion, '>');
    }

    /**
     * Check if url is valid.
     *
     * @param string $url
     *
     * @return bool
     */
    protected function isValidUrl($url) {
        return (filter_var($url, FILTER_VALIDATE_URL) !== FALSE);
    }

    /**
     * Download file via curl.
     *
     * @param string $url URL to file
     * @param int    $timeout
     *
     * @return string|false
     */
    protected function downloadCurl($url, $timeout = 10) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $update = curl_exec($curl);

        $success = TRUE;
        if (curl_error($curl)) {
            $success = FALSE;
            $this->setMessage(sprintf(
                'Could not download update "%s" via curl: %s!',
                $url,
                curl_error($curl)
            ));
        }
        curl_close($curl);

        return ($success === TRUE) ? $update : FALSE;
    }

    /**
     * Download the update
     *
     * @param string $updateUrl  Url where to download from
     * @param string $updateFile Path where to save the download
     *
     * @return bool
     * @throws DownloadException
     * @throws Exception
     */
    protected function downloadUpdate($updateUrl, $updateFile) {
        $this->setMessage(sprintf('Downloading update "%s" to "%s"', $updateUrl, $updateFile));
        if (function_exists('curl_version') && $this->isValidUrl($updateUrl)) {
            $update = $this->downloadCurl($updateUrl);
            if ($update === FALSE) {
                return FALSE;
            }
        } else if (ini_get('allow_url_fopen')) {
            $update = @file_get_contents($updateUrl, FALSE);

            if ($update === FALSE) {
                $this->setMessage(sprintf('Could not download update "%s"!', $updateUrl));

                throw new DownloadException($updateUrl);
            }
        } else {
            throw new RuntimeException('No valid download method found!');
        }

        $handle = fopen($updateFile, 'wb');
        if (!$handle) {
            $this->setMessage(sprintf('Could not open file handle to save update to "%s"!', $updateFile));

            return FALSE;
        }

        if (!fwrite($handle, $update)) {
            $this->setMessage(sprintf('Could not write update to file "%s"!', $updateFile));
            fclose($handle);

            return FALSE;
        }

        fclose($handle);

        return TRUE;
    }

    /**
     * Install update.
     *
     * @param string $updateFile Path to the update file
     * @param string $version
     *
     * @return bool
     */
    protected function install($updateFile, $version) {
        $this->setMessage(sprintf('Trying to install update "%s"', $updateFile));

        clearstatcache();

        // Check if zip file could be opened
        $zip = new ZipArchive();
        $resource = $zip->open($updateFile);
        if ($resource !== TRUE) {
            $this->setMessage(sprintf('Could not open zip file "%s", error: %d', $updateFile, $resource));

            return FALSE;
        }

        $temp = $this->tempDir.'latest/';

        // Read every file from archive
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileStats = $zip->statIndex($i);
            $filename = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileStats['name']);
            $foldername = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $temp.dirname($filename));
            $absoluteFilename = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $temp);
            //$this->setMessage(sprintf('Updating file "%s"', $filename));

            if (!is_dir($foldername) && !mkdir($foldername, $this->dirPermissions, TRUE) && !is_dir($foldername)) {
                $this->setMessage(sprintf('Directory "%s" has to be writeable!', $foldername));

                return FALSE;
            }

            // Skip if entry is a directory
            if ($filename[strlen($filename) - 1] === DIRECTORY_SEPARATOR) {
                continue;
            }

            // Extract file
            if ($zip->extractTo($absoluteFilename, $fileStats['name']) === FALSE) {
                $this->setMessage(sprintf('Coud not read zip entry "%s"', $fileStats['name']));
            }
        }

        $zip->close();

        // Move files to install dir
        $temp = is_dir($temp.'files/') ? $temp.'files/' : $temp;

        if ($dh = opendir($temp)) {
            while (($file = readdir($dh)) !== FALSE) {
                if (in_array($file, [".", ".."])) {
                    continue;
                }

                rename($temp.$file, $this->installDir.$file);
            }
            closedir($dh);

        }

        $this->setMessage(sprintf('Update "%s" successfully installed', $version));

        return TRUE;
    }

    /**
     * Update to the latest version
     *
     * @return integer|bool
     * @throws DownloadException
     * @throws ParserException
     */
    public function update() {
        $this->setMessage('Trying to perform update');

        // Check for latest version
        if ($this->latestVersion === NULL || count($this->updates) === 0) {
            $this->checkUpdate();
        }

        if ($this->latestVersion === NULL || count($this->updates) === 0) {
            $this->setMessage('Could not get latest version from server!');

            return self::ERROR_VERSION_CHECK;
        }

        // Check if current version is up to date
        if (!$this->newVersionAvailable()) {
            $this->setMessage('No update available!');

            return self::NO_UPDATE_AVAILABLE;
        }

        return TRUE;
    }

    /**
     * @param bool $deleteDownload Delete download after update (Default: true)
     *
     * @return false|int
     * @throws DownloadException
     */
    public function doUpdate($deleteDownload = TRUE) {
        if ($this->newVersionAvailable()) {
            foreach ($this->updates as $update) {
                $this->setMessage(sprintf('Update to version "%s"', $update['version']));

                // Check for temp directory
                if (empty($this->tempDir) || !is_dir($this->tempDir) || !is_writable($this->tempDir)) {
                    $this->setMessage(sprintf('Temporary directory "%s" does not exist or is not writeable!',
                        $this->tempDir));

                    return self::ERROR_TEMP_DIR;
                }

                // Check for install directory
                if (empty($this->installDir) || !is_dir($this->installDir) || !is_writable($this->installDir)) {
                    $this->setMessage(sprintf('Install directory "%s" does not exist or is not writeable!', $this->installDir));

                    return self::ERROR_INSTALL_DIR;
                }

                $updateFile = $this->tempDir.$update['version'].'.zip';

                // Download update
                if (!is_file($updateFile)) {
                    if (!$this->downloadUpdate($update['url'], $updateFile)) {
                        $this->setMessage(sprintf('Failed to download update from "%s" to "%s"!', $update['url'], $updateFile));

                        return self::ERROR_DOWNLOAD_UPDATE;
                    }

                    $this->setMessage(sprintf('Latest update downloaded to "%s"', $updateFile));
                } else {
                    $this->setMessage(sprintf('Latest update already downloaded to "%s"', $updateFile));
                }

                // Install update
                $result = $this->install($updateFile, $update['version']);
                if ($result === TRUE) {
                    if ($deleteDownload) {
                        $this->setMessage(sprintf('Trying to delete update file "%s" after successfull update', $updateFile));
                        if (unlink($updateFile)) {
                            $this->setMessage(sprintf('Update file "%s" deleted after successfull update', $updateFile));
                        } else {
                            $this->setMessage(sprintf('Could not delete update file "%s" after successfull update!', $updateFile));

                            return self::ERROR_DELETE_TEMP_UPDATE;
                        }
                    }
                } else {
                    if ($deleteDownload) {
                        $this->setMessage(sprintf('Trying to delete update file "%s" after failed update', $updateFile));
                        if (unlink($updateFile)) {
                            $this->setMessage(sprintf('Update file "%s" deleted after failed update', $updateFile));
                        } else {
                            $this->setMessage(sprintf('Could not delete update file "%s" after failed update!', $updateFile));
                        }
                    }

                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    private function setMessage($message) {
        $this->message .= $message.'<br>';
    }

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }
}

class ParserException extends Exception {
}

class DownloadException extends Exception {
}
