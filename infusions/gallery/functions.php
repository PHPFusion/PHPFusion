<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: functions.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Displays the Album Image
 *
 * @param string $album_image
 * @param string $album_thumb1
 * @param string $album_thumb2
 * @param string $link
 * @param int    $album_id
 *
 * @return string
 */
function display_album_image($album_image, $album_thumb1, $album_thumb2, $link, $album_id = 0) {
    $gallery_settings = get_settings("gallery");

    if ($gallery_settings['gallery_album_latest_photo']) {
        $result = dbquery("
            SELECT ph.photo_id, ph.photo_title, ph.photo_datestamp, ph.photo_filename, ph.photo_thumb1, ph.photo_thumb2, pa.album_id, pa.album_access FROM ".DB_PHOTOS." ph
            LEFT JOIN ".DB_PHOTO_ALBUMS." AS pa USING (album_id)
            WHERE album_id=".$album_id." AND ".groupaccess('pa.album_access')." ORDER BY ph.photo_datestamp DESC LIMIT 1
        ");

        if (dbrows($result) > 0) {
            $data = dbarray($result);
            return display_photo_image($data['photo_filename'], $data['photo_thumb1'], $data['photo_thumb2'], $link, $album_id);
        }

    } else {
        if (!empty($album_thumb1) && (file_exists(IMAGES_G_T.$album_thumb1) || file_exists(IMAGES_G.$album_thumb1))) {
            if (file_exists(IMAGES_G.$album_thumb1)) {
                // uncommon first
                $image = thumbnail(IMAGES_G.$album_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
            } else {
                // sure fire if image is usually more than thumb threshold
                $image = thumbnail(IMAGES_G_T.$album_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
            }

            return $image;
        }

        if (!empty($album_thumb2) && file_exists(IMAGES_G.$album_thumb2)) {
            return thumbnail(IMAGES_G.$album_thumb2, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        }
        if (!empty($album_image) && file_exists(IMAGES_G.$album_image)) {
            return thumbnail(IMAGES_G.$album_image, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        }

    }

    return thumbnail(IMAGES_G."album_default.jpg", $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
}

/**
 * Displays Album Thumb with Colorbox
 *
 * @param string $photo_filename
 * @param string $photo_thumb1
 * @param string $photo_thumb2
 * @param string $link
 * @param int    $album_id
 *
 * @return string
 */
function display_photo_image($photo_filename, $photo_thumb1, $photo_thumb2, $link, $album_id = 0) {
    $gallery_settings = get_settings('gallery');

    if (!empty($photo_thumb1) && (file_exists(IMAGES_G_T.$photo_thumb1) || file_exists(IMAGES_G.'album_'.$album_id.'/thumbs/'.$photo_thumb1) || file_exists(IMAGES_G.'album_'.$album_id.'/'.$photo_thumb1))) {
        $image = '';
        if (file_exists(IMAGES_G.$photo_thumb1)) {
            $image = thumbnail(IMAGES_G.$photo_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        } else if (file_exists(IMAGES_G_T.$photo_thumb1)) {
            $image = thumbnail(IMAGES_G_T.$photo_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        } else if (file_exists(IMAGES_G.'album_'.$album_id.'/thumbs/'.$photo_thumb1)) {
            $image = thumbnail(IMAGES_G.'album_'.$album_id.'/thumbs/'.$photo_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        } else if (file_exists(IMAGES_G.'album_'.$album_id.'/'.$photo_thumb1)) {
            $image = thumbnail(IMAGES_G.'album_'.$album_id.'/'.$photo_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        }

        return $image;
    }

    if (!empty($photo_thumb2) && (file_exists(IMAGES_G_T.$photo_thumb2) || file_exists(IMAGES_G.'album_'.$album_id.'/thumbs/'.$photo_thumb2) || file_exists(IMAGES_G.'album_'.$album_id.'/'.$photo_thumb2))) {
        $image = '';
        if (file_exists(IMAGES_G.$photo_thumb2)) {
            $image = thumbnail(IMAGES_G.$photo_thumb2, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        } else if (file_exists(IMAGES_G_T.$photo_thumb2)) {
            $image = thumbnail(IMAGES_G_T.$photo_thumb2, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        } else if (file_exists(IMAGES_G.'album_'.$album_id.'/thumbs/'.$photo_thumb2)) {
            $image = thumbnail(IMAGES_G.'album_'.$album_id.'/thumbs/'.$photo_thumb2, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        } else if (file_exists(IMAGES_G.'album_'.$album_id.'/'.$photo_thumb2)) {
            $image = thumbnail(IMAGES_G.'album_'.$album_id.'/'.$photo_thumb2, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        }

        return $image;
    }

    if (!empty($photo_filename) && (file_exists(IMAGES_G.$photo_filename) || file_exists(IMAGES_G.'album_'.$album_id.'/'.$photo_filename))) {
        $image = '';
        if (file_exists(IMAGES_G.$photo_filename)) {
            $image = thumbnail(IMAGES_G.$photo_filename, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        } else if (file_exists(IMAGES_G.'album_'.$album_id.'/'.$photo_filename)) {
            $image = thumbnail(IMAGES_G.'album_'.$album_id.'/'.$photo_filename, $gallery_settings['thumb_w']."px", $link, FALSE, TRUE, "cropfix");
        }

        return $image;
    }

    return thumbnail(IMAGES_G."album_default.jpg", $gallery_settings['thumb_w']."px", "", FALSE, TRUE, "cropfix");
}

/**
 * @param array $data
 *
 * @return string[]
 */
function return_photo_paths($data) {
    $photo_thumb2 = '';
    $photo_thumb1 = '';
    $photo_filename = '';

    if (!empty($data['photo_thumb2']) && (file_exists(IMAGES_G_T.$data['photo_thumb2']) || file_exists(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb2']) || file_exists(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_thumb2']))) {
        if (file_exists(IMAGES_G.$data['photo_thumb2'])) {
            $photo_thumb2 = IMAGES_G.$data['photo_thumb2'];
        } else if (file_exists(IMAGES_G_T.$data['photo_thumb2'])) {
            $photo_thumb2 = IMAGES_G_T.$data['photo_thumb2'];
        } else if (file_exists(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb2'])) {
            $photo_thumb2 = IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb2'];
        } else if (file_exists(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_thumb2'])) {
            $photo_thumb2 = IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_thumb2'];
        }
    }

    if (!empty($data['photo_thumb1']) && (file_exists(IMAGES_G_T.$data['photo_thumb1']) || file_exists(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb1']) || file_exists(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_thumb1']))) {
        if (file_exists(IMAGES_G.$data['photo_thumb1'])) {
            $photo_thumb1 = IMAGES_G.$data['photo_thumb1'];
        } else if (file_exists(IMAGES_G_T.$data['photo_thumb1'])) {
            $photo_thumb1 = IMAGES_G_T.$data['photo_thumb1'];
        } else if (file_exists(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb1'])) {
            $photo_thumb1 = IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb1'];
        } else if (file_exists(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_thumb1'])) {
            $photo_thumb1 = IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_thumb1'];
        }
    }

    if (!empty($data['photo_filename']) && (file_exists(IMAGES_G.$data['photo_filename']) || file_exists(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_filename']))) {
        if (file_exists(IMAGES_G.$data['photo_filename'])) {
            $photo_filename = IMAGES_G.$data['photo_filename'];
        } else if (file_exists(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_filename'])) {
            $photo_filename = IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_filename'];
        }
    }

    return [
        'photo_thumb2'   => $photo_thumb2,
        'photo_thumb1'   => $photo_thumb1,
        'photo_filename' => $photo_filename
    ];
}
