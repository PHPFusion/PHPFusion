<?php
require_once __DIR__.'/../../../maincore.php';

if (!defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
    exit;
}

error_reporting(0); // Set E_ALL for debuging

// // Optional exec path settings (Default is called with command name only)
// define('ELFINDER_TAR_PATH',      '/PATH/TO/tar');
// define('ELFINDER_GZIP_PATH',     '/PATH/TO/gzip');
// define('ELFINDER_BZIP2_PATH',    '/PATH/TO/bzip2');
// define('ELFINDER_XZ_PATH',       '/PATH/TO/xz');
// define('ELFINDER_ZIP_PATH',      '/PATH/TO/zip');
// define('ELFINDER_UNZIP_PATH',    '/PATH/TO/unzip');
// define('ELFINDER_RAR_PATH',      '/PATH/TO/rar');
// define('ELFINDER_UNRAR_PATH',    '/PATH/TO/unrar');
// define('ELFINDER_7Z_PATH',       '/PATH/TO/7za');
// define('ELFINDER_CONVERT_PATH',  '/PATH/TO/convert');
// define('ELFINDER_IDENTIFY_PATH', '/PATH/TO/identify');
// define('ELFINDER_EXIFTRAN_PATH', '/PATH/TO/exiftran');
// define('ELFINDER_JPEGTRAN_PATH', '/PATH/TO/jpegtran');
// define('ELFINDER_FFMPEG_PATH',   '/PATH/TO/ffmpeg');

// define('ELFINDER_CONNECTOR_URL', 'URL to this connector script');  // see elFinder::getConnectorUrl()

// define('ELFINDER_DEBUG_ERRORLEVEL', -1); // Error reporting level of debug mode

// // load composer autoload before load elFinder autoload If you need composer
// // You need to run the composer command in the php directory.
is_readable('./vendor/autoload.php') && require './vendor/autoload.php';

// // elFinder autoload
require './autoload.php';
// ===============================================

// // Enable FTP connector netmount
elFinder::$netDrivers['ftp'] = 'FTP';

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param string $attr   attribute name (read|write|locked|hidden)
 * @param string $path   absolute file path
 * @param string $data   value of volume option `accessControlData`
 * @param object $volume elFinder volume driver object
 *
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
    return strpos(basename($path), '.') === 0 || strpos(basename($path), 'index.php') === 0 ? !($attr == 'read' || $attr == 'write') : NULL;
}

$site_url = fusion_get_settings('siteurl');

$default = [
    'driver'        => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
    'accessControl' => 'access',
    'uploadAllow'   => ['image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/tiff', 'image/tif', 'image/x-ms-bmp', 'image/webp', 'image/x-icon', 'image/svg', 'image/svg+xml', 'application/xml', 'text/xml'], // Mimetype `image` allowed to upload
    'uploadOrder'   => ['allow', 'deny'], // allowed Mimetype image only
    'tmbPath'       => BASEDIR.'cache/.tmb',
    'tmbURL'        => $site_url.'cache/.tmb',
    'quarantine'    => BASEDIR.'cache/.tmb'
];

// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options

$opts = [
    //'debug' => true,
    'roots' => [
        // Items volume
        array_merge([
            'path'  => IMAGES,
            'URL'   => $site_url.'images/',
            'alias' => 'root_images'
        ], $default),
    ]
];

$infusions = \PHPFusion\Admins::getInstance()->getCustomFolders();
foreach ($infusions as $rights => $infusion) {
    if (checkrights($rights)) {
        $folders = [];
        foreach ($infusion as $folder) {
            $folders[] = array_merge($folder, $default);
        }
        $opts['roots'] = array_merge($opts['roots'], $folders);
    }
}

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();
