<?php
(defined("IN_FUSION") || exit);
// Autoload extended core files
if (is_file(__DIR__."/vendor/autoload.php")) {
    require_once __DIR__.'/vendor/autoload.php';
    $files = makefilelist(INCLUDES."core/", "index.php|._DS_Store|.|..", FALSE, "files");
    foreach ($files as $filename) {
        $path = INCLUDES."core/".$filename;
        require_once $path;
    }
}

