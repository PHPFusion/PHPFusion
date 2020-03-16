<?php
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

require_once __DIR__.'/../../../maincore.php';

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
    return strpos(basename($path), '.') === 0 ||
    strpos(basename($path), 'index.php') === 0 ||
    strpos(basename($path), 'imagelist.js') === 0
        ? !($attr == 'read' || $attr == 'write') : NULL;
}

$default = [
    'driver'        => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
    'accessControl' => 'access',
    'uploadDeny'    => ['all'], // All Mimetypes not allowed to upload
    'uploadAllow'   => ['image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/tiff', 'image/tif', 'image/x-ms-bmp', 'image/x-icon', 'image/svg', 'image/svg+xml', 'application/xml', 'text/xml'], // Mimetype `image` allowed to upload
    'uploadOrder'   => ['deny', 'allow'], // allowed Mimetype `image only
    'tmbPath'       => '.tmb',
    'quarantine'    => '.tmb'
];

// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options

$root_images = [
    'path'  => IMAGES,
    'URL'   => fusion_get_settings('siteurl').'images/',
    'alias' => 'root_images'
];

if (defined('ARTICLES_EXIST')) {
    $article_images = [
        'path'  => IMAGES_A,
        'URL'   => fusion_get_settings('siteurl').'infusions/articles/images/',
        'alias' => 'articles'
    ];
}

if (defined('BLOG_EXIST')) {
    $blog_images = [
        'path'  => IMAGES_B,
        'URL'   => fusion_get_settings('siteurl').'infusions/blog/images/',
        'alias' => 'blog'
    ];
}

if (defined('DOWNLOADS_EXIST')) {
    $download_images = [
        'path'  => IMAGES_D,
        'URL'   => fusion_get_settings('siteurl').'infusions/download/images/',
        'alias' => 'downloads'
    ];
}

if (defined('GALLERY_EXIST')) {
    $download_images = [
        'path'  => IMAGES_G,
        'URL'   => fusion_get_settings('siteurl').'infusions/gallery/photos/',
        'alias' => 'gallery'
    ];
}

if (defined('NEWS_EXIST')) {
    $news_images = [
        'path'  => IMAGES_N,
        'URL'   => fusion_get_settings('siteurl').'infusions/news/images/',
        'alias' => 'news'
    ];
}

$opts = [
    //'debug' => true,
    'roots' => [
        // Items volume
        array_merge($root_images, $default),
        is_array($article_images) ? array_merge($article_images, $default) : NULL,
        is_array($blog_images) ? array_merge($blog_images, $default) : NULL,
        is_array($download_images) ? array_merge($download_images, $default) : NULL,
        is_array($news_images) ? array_merge($news_images, $default) : NULL,

    ]
];

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();
