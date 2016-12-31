<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/gallery_cat.php
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
pageAccess("PH");
$data = array(
    "album_id" => 0,
    "album_title" => "",
    "album_keywords" => "",
    "album_description" => "",
    "album_access" => "",
    "album_language" => LANGUAGE,
    "album_image" => "",
    "album_thumb1" => "",
    "album_thumb2" => "",
    "album_order" => dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "") + 1
);
if (isset($_POST['save_album'])) {
    $data = array(
        "album_id" => form_sanitizer($_POST['album_id'], 0, "album_id"),
        "album_title" => form_sanitizer($_POST['album_title'], "", "album_title"),
        "album_keywords" => form_sanitizer($_POST['album_keywords'], "", "album_keywords"),
        "album_description" => form_sanitizer($_POST['album_description'], "", "album_description"),
        "album_access" => form_sanitizer($_POST['album_access'], "", "album_access"),
        "album_language" => form_sanitizer($_POST['album_language'], "", "album_language"),
        "album_order" => form_sanitizer($_POST['album_order'], "", "album_order"),
        "album_image" => "",
        "album_thumb1" => "",
        "album_thumb2" => "",
        "album_user" => $userdata['user_id'],
        "album_datestamp" => time(),
    );
    if (empty($data['album_order'])) {
        $data['album_order'] = dbresult(dbquery("SELECT MAX(album_order) FROM ".DB_PHOTO_ALBUMS."
				".(multilang_table("PG") ? "where album_language='".LANGUAGE."'" : "").""), 0) + 1;
    }
    // do delete image
    if (defender::safe()) {
        if (!empty($_FILES['album_image']) && is_uploaded_file($_FILES['album_image']['tmp_name'])) {
            $upload = form_sanitizer($_FILES['album_image'], "", "album_image");
            if (empty($upload['error'])) {
                $data['album_image'] = $upload['image_name'];
                $data['album_thumb1'] = $upload['thumb1_name'];
                $data['album_thumb2'] = $upload['thumb2_name'];
            }
        } else {
            if (isset($_POST['del_image'])) {
                // album_id
                $result = dbquery("select album_image, album_thumb1, album_thumb2 FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$data['album_id']."'");
                if (dbrows($result) > 0) {
                    $pData = dbarray($result);
                    if ($pData['album_image'] && file_exists(IMAGES_G.$pData['album_image'])) {
                        unlink(IMAGES_G.$pData['album_image']);
                    }
                    if ($pData['album_thumb1'] && file_exists(IMAGES_G.$pData['album_thumb1'])) {
                        unlink(IMAGES_G_T.$pData['album_thumb1']);
                    }
                    if ($pData['album_thumb2'] && file_exists(IMAGES_G.$pData['album_thumb2'])) {
                        unlink(IMAGES_G_T.$pData['album_thumb2']);
                    }
                    $data['album_image'] = "";
                    $data['album_thumb1'] = "";
                    $data['album_thumb2'] = "";
                }
            } else {
                $data['album_image'] = form_sanitizer(isset($_POST['album_image']) ? $_POST['album_image'] : "", "", "album_image");
                $data['album_thumb2'] = form_sanitizer(isset($_POST['album_thumb2']) ? $_POST['album_thumb2'] : "", "", "album_thumb2");
                $data['album_thumb1'] = form_sanitizer(isset($_POST['album_thumb1']) ? $_POST['album_thumb1'] : "", "", "album_thumb1");
            }
        }
    }
    if (defender::safe()) {
        if (dbcount("(album_id)", DB_PHOTO_ALBUMS, "album_id='".intval($data['album_id'])."'")) {
            // update album
            $result = dbquery_order(DB_PHOTO_ALBUMS, $data['album_order'], 'album_order', $data['album_id'], 'album_id', FALSE, FALSE, TRUE,
                                    'album_language', 'update');
            dbquery_insert(DB_PHOTO_ALBUMS, $data, "update");
            addNotice('success', $locale['album_0013']);
            redirect(FUSION_SELF.$aidlink);
        } else {
            // create album
            $result = dbquery_order(DB_PHOTO_ALBUMS, $data['album_order'], 'album_order', 0, "album_id", FALSE, FALSE, TRUE, 'album_language',
                                    'save');
            dbquery_insert(DB_PHOTO_ALBUMS, $data, "save");
            addNotice('success', $locale['album_0014']);
            redirect(FUSION_SELF.$aidlink);
        }
    }
}
// callback
if ($album_edit) {
    $result = dbquery("SELECT * FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".intval($_GET['cat_id'])."'");
    if (dbrows($result) > 0) {
        $data = dbarray($result);
    }
}
// edit features - add more in roadmap.
// add features to purge all album photos and it's administration
// add features to move all album photos to another album.
echo openform('albumform', 'post', FUSION_REQUEST, array('enctype' => TRUE, 'class' => 'm-t-20'));
echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
echo form_hidden('album_id', '', $data['album_id']);
echo form_text('album_title', $locale['album_0001'], $data['album_title'], array(
    'placeholder' => $locale['album_0002'],
    'inline' => TRUE,
    'required' => TRUE,
    "error_text" => $locale['album_0015'],
));
echo form_select("album_keywords", $locale['album_0005'], $data['album_keywords'], array(
    'max_length' => 320,
    'width' => '100%',
    'placeholder' => $locale['album_0006'],
    'tags' => TRUE,
    'multiple' => TRUE,
    "inline" => TRUE,
));
echo form_textarea('album_description', $locale['album_0003'], $data['album_description'], array(
    'placeholder' => $locale['album_0004'],
    'inline' => 1
));
if ($data['album_image'] || $data['album_thumb1']) {
    echo "<div class='well col-sm-offset-3'>\n";
    echo form_hidden("album_image", "", $data['album_image']);
    echo form_hidden("album_thumb2", "", $data['album_thumb2']);
    echo form_hidden("album_thumb1", "", $data['album_thumb1']);
    echo "<label for='del_image'>\n";
    echo displayAlbumImage($data['album_image'], $data['album_thumb1'], $data['album_thumb2'], "");
    echo "</label>\n";
    echo form_checkbox("del_image", $locale['album_0016'], "");
    echo "</div>\n";
} else {
    $album_upload_settings = array(
        "upload_path" => INFUSIONS."gallery/photos/",
        'thumbnail_folder' => 'thumbs',
        'thumbnail' => TRUE,
        'thumbnail_w' => $gll_settings['thumb_w'],
        'thumbnail_h' => $gll_settings['thumb_h'],
        'thumbnail_suffix' => '_t1',
        'thumbnail2' => TRUE,
        'thumbnail2_w' => $gll_settings['photo_w'],
        'thumbnail2_h' => $gll_settings['photo_h'],
        'thumbnail2_suffix' => '_t2',
        'max_width' => $gll_settings['photo_max_w'],
        'max_height' => $gll_settings['photo_max_h'],
        'max_byte' => $gll_settings['photo_max_b'],
        'multiple' => 0,
        'delete_original' => FALSE,
        "inline" => TRUE,
        "template" => "modern",
        "class" => "m-b-0",
    );
    echo form_fileinput('album_image', $locale['album_0009'], "", $album_upload_settings);
    echo "<div class='m-b-10 col-xs-12 col-sm-offset-3'>".sprintf($locale['album_0010'], parsebytesize($gll_settings['photo_max_b']),
                                                                  str_replace(',', ' ', ".jpg,.gif,.png"), $gll_settings['photo_max_w'],
                                                                  $gll_settings['photo_max_h'])."</div>\n";
}
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
echo form_select('album_access', $locale['album_0007'], $data['album_access'], array(
    'options' => fusion_get_groups(),
    'inline' => TRUE
));
echo form_select('album_language', $locale['album_0008'], $data['album_language'], array(
    'options' => fusion_get_enabled_languages(),
    'inline' => TRUE
));
echo form_text('album_order', $locale['album_0011'], $data['album_order'], array("type" => "number", 'inline' => TRUE));
echo "</div>\n</div>\n";
echo form_button('save_album', $locale['album_0012'], $locale['album_0012'], array('class' => 'btn-success', 'icon' => 'fa fa-hdd-o'));
echo closeform();
