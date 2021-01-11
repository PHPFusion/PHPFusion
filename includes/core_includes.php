<?php
(defined("IN_FUSION")||exit);
// Autoload extended core files

// set to true to run extended core functions
$load_extended_core = FALSE;

if ($load_extended_core) {
    if (is_file(__DIR__."/vendor/autoload.php")) {
        require_once __DIR__.'/vendor/autoload.php';
        $files = makefilelist(INCLUDES."core/", "index.php|._DS_Store|.|..", FALSE, "files");
        foreach ($files as $filename) {
            $path = INCLUDES."core/".$filename;
            require_once $path;
        }
    }
}
