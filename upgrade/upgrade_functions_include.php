<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: upgrade_functions_include.php
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
/*
 * Remove all files, subdirs and ultimately the directory in a given dir
 */
function rrmdir($dir) {
    if (file_exists($dir) && is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") {
                    rrmdir($dir."/".$object);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/*
 * Get all detected language in the system
 */
function fusion_get_detected_languages() {
    static $detected_languages = NULL;
    if ($detected_languages === NULL) {
        $all_languages = makefilelist(LOCALE, ".svn|.|..", TRUE, "folders");
        foreach ($all_languages as $language_name) {
            $detected_languages[$language_name] = $language_name;
        }
    }

    return (array)$detected_languages;
}

/**
 * Returns all photos inside the album into an array
 *
 * @param $album_id
 */
function move_photos($album_id) {
    $result = dbquery("SELECT * FROM ".DB_PHOTOS." WHERE album_id='".$album_id."'");
    if (dbrows($result) > 0) {
        while ($photo_data = dbarray($result)) {
            if (file_exists(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_filename'])) {
                rename(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_filename'],
                    INFUSIONS."gallery/photos/".$photo_data['photo_filename']);
            }
            if (file_exists(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_thumb1'])) {
                rename(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_thumb1'], INFUSIONS."gallery/photos/".$photo_data['photo_thumb1']);
            }
            if (file_exists(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_thumb2'])) {
                rename(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_thumb2'], INFUSIONS."gallery/photos/".$photo_data['photo_thumb2']);
            }
        }
    }
}
