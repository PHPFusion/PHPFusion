<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery/upgrade/1.0.5.upgrade.inc
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
$inf_version = '1.0.5';
require_once INCLUDES."infusions_include.php";

if (!column_exists(DB_PHOTOS, 'photo_keywords', FALSE)) {
    // Option to use keywords in photos
    $inf_altertable[] = DB_PHOTOS." ADD photo_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER photo_description";
}

$check_array = [
    'album_language' => " ADD album_language varchar(50) NOT NULL default '".fusion_get_settings('localeset')."' AFTER album_datestamp",
    'album_keywords' => " ADD album_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER album_description",
    'album_image'    => " ADD album_image VARCHAR(200) NOT NULL DEFAULT '' AFTER album_keywords",
    'album_thumb1'   => " ADD album_thumb1 VARCHAR(200) NOT NULL DEFAULT '' AFTER album_image",
    'album_thumb2'   => " ADD album_thumb2 VARCHAR(200) NOT NULL DEFAULT '' AFTER album_thumb1",
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_PHOTO_ALBUMS, $key, FALSE)) {
        dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ".$value);
    }
}
// Make sure access table are support for new levels
if (column_exists(DB_PHOTO_ALBUMS, 'album_access', FALSE)) {
    $inf_altertable[] = DB_PHOTO_ALBUMS." CHANGE album_access album_access CHAR(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."'";
}

// Update new access levels for album access
$result = dbquery("SELECT album_id, album_access, album_language FROM ".DB_PHOTO_ALBUMS);
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        $inf_updatedbrow[] = DB_PHOTO_ALBUMS." SET album_access='-".$data['album_access']."' WHERE album_id='".$data['album_id']."' AND album_access != 0";
    }
}

$check_array = [
    'thumb_w'                     => 640,
    'thumb_h'                     => 480,
    'photo_w'                     => 1920,
    'photo_h'                     => 1080,
    'photo_max_w'                 => 2400,
    'photo_max_h'                 => 2400,
    'photo_max_b'                 => 2000000,
    'thumbs_per_row'              => 4,
    'admin_thumbs_per_row'        => 6,
    'photo_watermark'             => 1,
    'photo_watermark_image'       => 'infusions/gallery/photos/watermark.png',
    'photo_watermark_text'        => 0,
    'photo_watermark_text_color1' => 'FF6600',
    'photo_watermark_text_color2' => 'FFFF00',
    'photo_watermark_text_color3' => 'FFFFFF',
    'photo_watermark_save'        => 0,
    'gallery_pagination'          => 24,
    'gallery_extended_required'   => 1,
    'gallery_allow_submission'    => 1,
    'gallery_file_types'          => '.pdf,.gif,.jpg,.png,.svg,.zip,.rar,.tar,.bz2,.7z',
];
$settings = get_settings('gallery');
foreach ($check_array as $key => $value) {
    // Clear old core settings if they are there regardless of current state
    $inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='$key'";
    if (!isset($settings[$key])) {
        // Insert new and old settings tables to Infusions table
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'gallery')";
    }
}

// Update main gallery image from thumb as it is called on old gallery
$result = dbquery("SELECT album_id, album_thumb FROM ".DB_PHOTO_ALBUMS);
if (dbrows($result) > 0) {
    while ($data = dbarray($result)) {
        if ($data['album_thumb']) {
            $inf_updatedbrow[] = DB_PHOTO_ALBUMS." SET album_image='".$data['album_thumb']."', album_language='".fusion_get_settings('locale')."' WHERE album_id='".$data['album_id']."'";
        }
    }
}

// Update the existing one, then add the remaining one.
$inf_updatedbrow[] = DB_ADMIN." SET admin_image='../infusions/gallery/gallery.svg', admin_link='../infusions/gallery/gallery_admin.php' WHERE admin_link='photoalbums.php'";

$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='photogallery.php'";
// always find and loop ALL languages
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
// Create a link for all installed languages
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale('', LOCALE.$language."/setup.php");
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3308']."', 'infusions/gallery/gallery.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3313']."', 'submit.php?stype=p', ".USER_LEVEL_MEMBER.", '1', '0', '24', '1', '".$language."')";

        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/gallery/gallery.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=p' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_PHOTO_ALBUMS." WHERE album_language='".$language."'"; //will not be able to delete photos tied to it.
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3308']."', 'infusions/gallery/gallery.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3313']."', 'submit.php?stype=p', ".USER_LEVEL_MEMBER.", '1', '0', '24', '1', '".LANGUAGE."')";
}
// Move all photos
include_once BASEDIR.'upgrade/upgrade_functions_include.php';
if (db_exists(DB_PREFIX."photo_albums")) {
    $result = dbquery("SELECT * FROM ".DB_PREFIX."photo_albums");

    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if (!empty($data['album_thumb']) && file_exists(IMAGES."photoalbum/".$data['album_thumb'])) {
                // Rename the album thumb here
                rename(IMAGES."photoalbum/".$data['album_thumb'], INFUSIONS."gallery/photos/".$data['album_thumb']);
                // Call the album directory rename function here
                move_photos($data['album_id']);
            }
        }
    }
}

// Remove the whole old photoalbum dir including rouge files
rrmdir(IMAGES.'photoalbum');
