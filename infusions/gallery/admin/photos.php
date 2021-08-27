<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: photos.php
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
pageaccess("PH");

$locale = fusion_get_locale();

$tab_photo['title'][] = $locale['gallery_0009'];
$tab_photo['id'][] = "single_photo";
$tab_photo['icon'][] = "";
$tab_photo['title'][] = $locale['gallery_0010'];
$tab_photo['id'][] = "mass_photo";
$tab_photo['icon'][] = "";
$tab_photo_active = tab_active($tab_photo, 0);
echo opentab($tab_photo, $tab_photo_active, "phototabs", FALSE, "nav-tabs");
echo opentabbody($tab_photo['title'][0], $tab_photo['id'][0], $tab_photo_active);
photo_form();
echo closetabbody();
echo opentabbody($tab_photo['title'][1], $tab_photo['id'][1], $tab_photo_active);
mass_photo_form();
echo closetabbody();
echo closetab();
// done.
function photo_form() {
    $photo_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['photo_id']) && isnum($_GET['photo_id']);
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();
    $gallery_settings = get_settings('gallery');
    $albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? in_group('album_language', LANGUAGE) : "");
    if ($albumRows) {
        $data = [
            'photo_id'             => 0,
            'photo_title'          => '',
            'album_id'             => 0,
            'photo_description'    => '',
            'photo_keywords'       => '',
            'photo_filename'       => '',
            'photo_thumb1'         => '',
            'photo_thumb2'         => '',
            'photo_datestamp'      => time(),
            'photo_user'           => $userdata['user_id'],
            'photo_views'          => 0,
            'photo_order'          => 0,
            'photo_allow_comments' => 1,
            'photo_allow_ratings'  => 1,
        ];
        if (isset($_POST['save_photo'])) {
            $data = [
                'photo_id'             => form_sanitizer($_POST['photo_id'], 0, 'photo_id'),
                'photo_title'          => form_sanitizer($_POST['photo_title'], '', 'photo_title'),
                'album_id'             => form_sanitizer($_POST['album_id'], 0, 'album_id'),
                'photo_description'    => form_sanitizer($_POST['photo_description'], '', 'photo_description'),
                'photo_keywords'       => form_sanitizer($_POST['photo_keywords'], '', 'photo_keywords'),
                'photo_order'          => form_sanitizer($_POST['photo_order'], 0, 'photo_order'),
                'photo_datestamp'      => form_sanitizer($_POST['photo_datestamp'], '', 'photo_datestamp'),
                'photo_user'           => form_sanitizer($_POST['photo_user'], '', 'photo_user'),
                'photo_allow_comments' => isset($_POST['photo_allow_comments']),
                'photo_allow_ratings'  => isset($_POST['photo_allow_ratings']),
                'photo_views'          => 0,
                'photo_filename'       => '',
                'photo_thumb1'         => '',
                'photo_thumb2'         => '',
            ];
            if (empty($data['photo_order'])) {
                $data['photo_order'] = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." where album_id=:albumid", [':albumid' => $data['album_id']]), 0) + 1;
            }
            if (fusion_safe()) {
                if (!empty($_FILES['photo_image']) && is_uploaded_file($_FILES['photo_image']['tmp_name'])) {
                    $upload_dir = is_dir(IMAGES_G.'album_'.$data['album_id'].'/') && $data['album_id'] > 0 ? IMAGES_G.'album_'.$data['album_id'].'/' : IMAGES_G;

                    \Defender::getInstance()->add_field_session([
                        'input_name'        => 'photo_image',
                        'type'              => 'image',
                        'title'             => $locale['photo_0004'],
                        'id'                => 'photo_image',
                        'required'          => TRUE,
                        'safemode'          => FALSE,
                        'error_text'        => $locale['photo_0014'],
                        'path'              => $upload_dir,
                        'thumbnail_folder'  => 'thumbs',
                        'thumbnail'         => TRUE,
                        'thumbnail_suffix'  => '_t1',
                        'thumbnail_w'       => $gallery_settings['thumb_w'],
                        'thumbnail_h'       => $gallery_settings['thumb_h'],
                        'thumbnail_ratio'   => 0,
                        'thumbnail2'        => TRUE,
                        'thumbnail2_w'      => $gallery_settings['photo_w'],
                        'thumbnail2_h'      => $gallery_settings['photo_h'],
                        'thumbnail2_suffix' => '_t2',
                        'thumbnail2_ratio'  => 0,
                        'delete_original'   => FALSE,
                        'max_width'         => $gallery_settings['photo_max_w'],
                        'max_height'        => $gallery_settings['photo_max_h'],
                        'max_count'         => 1,
                        'max_byte'          => $gallery_settings['photo_max_b'],
                        'multiple'          => FALSE,
                        'valid_ext'         => $gallery_settings['gallery_file_types'],
                        'replace_upload'    => FALSE
                    ]);

                    $upload = form_sanitizer($_FILES['photo_image'], '', 'photo_image');
                    if (empty($upload['error'])) {
                        $data['photo_filename'] = $upload['image_name'];
                        $data['photo_thumb1'] = $upload['thumb1_name'];
                        $data['photo_thumb2'] = $upload['thumb2_name'];
                    }
                } else if ($data['photo_id'] > 0) { // during edit, photo_id is not 0.
                    // delete image
                    if (!empty($_POST['del_image'])) {
                        // album_id
                        $result = dbquery("select album_id, photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE photo_id=:photoid", [':photoid' => $data['photo_id']]);
                        if (dbrows($result) > 0) {
                            $pData = dbarray($result);
                            purge_photo_image($pData);
                            $data['photo_filename'] = "";
                            $data['photo_thumb1'] = "";
                            $data['photo_thumb2'] = "";
                        }
                    } else {
                        $data['photo_filename'] = form_sanitizer($_POST['photo_filename'], '', 'photo_filename');
                        $data['photo_thumb2'] = !empty($_POST['photo_thumb2']) ? form_sanitizer($_POST['photo_thumb2'], '', 'photo_thumb2') : '';
                        $data['photo_thumb1'] = !empty($_POST['photo_thumb1']) ? form_sanitizer($_POST['photo_thumb1'], '', 'photo_thumb1') : '';
                    }
                } else {
                    // because we require the photo image must be uploaded.
                    fusion_stop();
                    \Defender::setInputError("photo_image");
                    addnotice('danger', $locale['photo_0014']);
                }
            }

            if (fusion_safe()) {
                if (dbcount("(photo_id)", DB_PHOTOS, "photo_id=:photoid", [':photoid' => intval($data['photo_id'])])) {
                    // update album
                    dbquery_order(DB_PHOTOS, $data['photo_order'], 'photo_order', $data['photo_id'], 'photo_id', FALSE, FALSE);
                    dbquery_insert(DB_PHOTOS, $data, 'update');
                    addnotice('success', $locale['photo_0015']);
                } else {
                    // create album
                    dbquery_order(DB_PHOTOS, $data['photo_order'], 'photo_order', 0, "photo_id", FALSE, FALSE, FALSE, '', 'save');
                    dbquery_insert(DB_PHOTOS, $data, 'save');
                    addnotice('success', $locale['photo_0016']);
                }
                redirect(clean_request('album_id='.$data['album_id'], ['ref', 'action', 'album_id', 'photo_id', 'section'], FALSE));
            }
        }
        if ($photo_edit) {
            $result = dbquery("SELECT * FROM ".DB_PHOTOS." WHERE photo_id=:photoid", [':photoid' => intval($_GET['photo_id'])]);
            if (dbrows($result) > 0) {
                $data = dbarray($result);
            } else {
                redirect(FUSION_REQUEST);
            }
        }

        echo openform('photoform', 'post', FUSION_REQUEST, ['enctype' => TRUE]);
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
        echo form_hidden('photo_id', '', $data['photo_id']);
        echo form_hidden('photo_datestamp', '', $data['photo_datestamp']);
        echo form_hidden('photo_user', '', $data['photo_user']);

        echo form_text('photo_title', $locale['photo_0001'], $data['photo_title'], [
            'required'    => TRUE,
            'placeholder' => $locale['photo_0002'],
            'class'       => 'form-group-lg'
        ]);

        echo form_select('album_id', $locale['photo_0003'], $data['album_id'], [
            'options'     => get_album_opts(),
            'inner_width' => "100%",
        ]);

        openside('');
        if ($data['photo_filename'] || $data['photo_thumb1']) {
            echo "<div class='well col-sm-offset-3'>\n";
            $image = '';

            if (!empty($data['photo_filename']) && (file_exists(IMAGES_G.$data['photo_filename']) || file_exists(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_filename']))) {
                if (file_exists(IMAGES_G.$data['photo_filename'])) {
                    $image = thumbnail(IMAGES_G.$data['photo_filename'], $gallery_settings['thumb_w']."px");
                } else if (file_exists(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_filename'])) {
                    $image = thumbnail(IMAGES_G.'album_'.$data['album_id'].'/'.$data['photo_filename'], $gallery_settings['thumb_w']."px");
                }
                echo form_hidden('photo_filename', '', $data['photo_filename']);
            }

            if (!empty($data['photo_thumb2']) && (file_exists(IMAGES_G_T.$data['photo_thumb2']) || file_exists(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb2']))) {
                if (file_exists(IMAGES_G.$data['photo_thumb2'])) {
                    $image = thumbnail(IMAGES_G.$data['photo_thumb2'], $gallery_settings['thumb_w']."px");
                } else if (file_exists(IMAGES_G_T.$data['photo_thumb2'])) {
                    $image = thumbnail(IMAGES_G_T.$data['photo_thumb2'], $gallery_settings['thumb_w']."px");
                } else if (file_exists(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb2'])) {
                    $image = thumbnail(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb2'], $gallery_settings['thumb_w']."px");
                }
                echo form_hidden('photo_thumb2', '', $data['photo_thumb2']);
            }

            if (!empty($data['photo_thumb1']) && (file_exists(IMAGES_G_T.$data['photo_thumb1']) || file_exists(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb1']))) {
                if (file_exists(IMAGES_G.$data['photo_thumb1'])) {
                    $image = thumbnail(IMAGES_G.$data['photo_thumb1'], $gallery_settings['thumb_w']."px");
                } else if (file_exists(IMAGES_G_T.$data['photo_thumb1'])) {
                    $image = thumbnail(IMAGES_G_T.$data['photo_thumb1'], $gallery_settings['thumb_w']."px");
                } else if (file_exists(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb1'])) {
                    $image = thumbnail(IMAGES_G.'album_'.$data['album_id'].'/thumbs/'.$data['photo_thumb1'], $gallery_settings['thumb_w']."px");
                }
                echo form_hidden('photo_thumb1', '', $data['photo_thumb1']);
            }

            echo "<label for='del_image'>\n";
            echo $image;
            echo "</label>\n";
            echo form_checkbox('del_image', $locale['gallery_0017'], '');
            echo "</div>\n";
        } else {
            $upload_settings = [
                'inline'   => FALSE,
                'template' => 'modern',
                'ext_tip'  => sprintf($locale['album_0010'], parsebytesize($gallery_settings['photo_max_b']), $gallery_settings['gallery_file_types'], $gallery_settings['photo_max_w'], $gallery_settings['photo_max_h'])
            ];

            echo form_fileinput('photo_image', $locale['photo_0004'], '', $upload_settings);
        }
        closeside();

        $snippetSettings = [
            'required'    => FALSE,
            'preview'     => TRUE,
            'type'        => 'bbcode',
            'autosize'    => TRUE,
            'form_name'   => "photoform",
            'placeholder' => $locale['photo_0009'],
            'path'        => []
        ];
        if (fusion_get_settings("tinymce_enabled")) {
            $snippetSettings = [
                'form_name'   => 'inputform',
                'required'    => FALSE,
                'inline'      => TRUE,
                'placeholder' => $locale['photo_0009']
            ];
        }
        echo form_textarea('photo_description', $locale['photo_0008'], $data['photo_description'], $snippetSettings);

        echo form_text('photo_order', $locale['photo_0013'], $data['photo_order'], [
            'type'        => 'number',
            'inner_width' => '100px',
            'inline'      => TRUE
        ]);

        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-4'>\n";
        openside('');
        echo form_select('photo_keywords', $locale['album_0005'], $data['photo_keywords'], [
            'placeholder' => $locale['album_0006'],
            'multiple'    => TRUE,
            'tags'        => TRUE,
            'width'       => '100%',
            'inner_width' => '100%',
        ]);

        echo form_checkbox('photo_allow_comments', $locale['photo_0010'], $data['photo_allow_comments']);
        echo form_checkbox('photo_allow_ratings', $locale['photo_0011'], $data['photo_allow_ratings']);
        closeside();
        echo "</div>\n</div>\n";
        echo form_button('save_photo', $locale['photo_0012'], $locale['photo_0012'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
        echo closeform();
    } else {
        echo "<div class='well text-center'>\n";
        echo str_replace(
            ['[link]', '[/link]'], ['<a href="'.FUSION_SELF.$aidlink.'&amp;section=album_form">', '</a>'], $locale['gallery_0012']
        );

        echo "</div>\n";
    }
}

function mass_photo_form() {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();
    $gallery_settings = get_settings('gallery');
    $albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? in_group('album_language', LANGUAGE) : "");
    if ($albumRows) {
        if (isset($_POST['upload_photo'])) {
            $data['album_id'] = form_sanitizer($_POST['album_id'], 0, 'album_id');
            if (fusion_safe()) {
                $upload_dir = is_dir(IMAGES_G.'album_'.$data['album_id'].'/') && $data['album_id'] > 0 ? IMAGES_G.'album_'.$data['album_id'].'/' : IMAGES_G;

                \Defender::getInstance()->add_field_session([
                    'input_name'        => 'photo_mass_image',
                    'type'              => 'image',
                    'title'             => $locale['photo_0004'],
                    'id'                => 'photo_mass_image',
                    'required'          => TRUE,
                    'safemode'          => FALSE,
                    'error_text'        => $locale['photo_0014'],
                    'path'              => $upload_dir,
                    'thumbnail_folder'  => 'thumbs',
                    'thumbnail'         => TRUE,
                    'thumbnail_suffix'  => '_t1',
                    'thumbnail_w'       => $gallery_settings['thumb_w'],
                    'thumbnail_h'       => $gallery_settings['thumb_h'],
                    'thumbnail_ratio'   => 0,
                    'thumbnail2'        => TRUE,
                    'thumbnail2_w'      => $gallery_settings['photo_w'],
                    'thumbnail2_h'      => $gallery_settings['photo_h'],
                    'thumbnail2_suffix' => '_t2',
                    'thumbnail2_ratio'  => 0,
                    'delete_original'   => FALSE,
                    'max_width'         => $gallery_settings['photo_max_w'],
                    'max_height'        => $gallery_settings['photo_max_h'],
                    'max_count'         => 20,
                    'max_byte'          => $gallery_settings['photo_max_b'],
                    'multiple'          => TRUE,
                    'valid_ext'         => $gallery_settings['gallery_file_types'],
                    'replace_upload'    => FALSE
                ]);

                $upload = form_sanitizer($_FILES['photo_mass_image'], '', 'photo_mass_image');
                $success_upload = 0;
                $failed_upload = 0;
                if (!empty($upload)) {
                    $total_files_uploaded = count($upload);
                    for ($i = 0; $i < $total_files_uploaded; $i++) {
                        $current_upload = $upload[$i];
                        if ($current_upload['error'] == 0) {
                            $current_photos = [
                                'album_id'             => $data['album_id'],
                                'photo_title'          => $current_upload['image_name'],
                                'photo_filename'       => $current_upload['image_name'],
                                'photo_thumb1'         => $current_upload['thumb1_name'],
                                'photo_thumb2'         => $current_upload['thumb2_name'],
                                'photo_datestamp'      => time(),
                                'photo_user'           => $userdata['user_id'],
                                'photo_order'          => dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." where album_id=:albumid", [':albumid' => $data['album_id']]), 0) + 1,
                                'photo_allow_comments' => 1,
                                'photo_allow_ratings'  => 1
                            ];
                            dbquery("INSERT INTO ".DB_PHOTOS." (".implode(", ", array_keys($current_photos)).") VALUES ('".implode("','", array_values($current_photos))."')");
                            $success_upload++;
                        } else {
                            $failed_upload++;
                        }
                    }
                    addnotice('success', sprintf($locale['photo_0021'], $success_upload));
                    if ($failed_upload) {
                        addnotice('warning', sprintf($locale['photo_0021a'], $failed_upload));
                    }
                    redirect(clean_request('album_id='.$data['album_id'], ['ref', 'action', 'album_id', 'photo_id', 'section'], FALSE));
                }
            }
        }

        echo openform('mass_form', 'post', FUSION_REQUEST, ['enctype' => TRUE, 'class' => 'clearfix']);
        echo "<div class='well text-center m-t-10'>\n".$locale['photo_0019']."</div>\n";
        echo form_select('album_id', $locale['photo_0003'], '', [
            'input_id' => 'album',
            'options'  => get_album_opts(),
        ]);

        $upload_settings = [
            'inline'    => FALSE,
            'multiple'  => TRUE,
            'max_count' => 20,
            'template'  => 'modern',
            'ext_tip'   => sprintf($locale['album_0010'], parsebytesize($gallery_settings['photo_max_b']), $gallery_settings['gallery_file_types'], $gallery_settings['photo_max_w'], $gallery_settings['photo_max_h'])
        ];
        echo form_fileinput('photo_mass_image[]', $locale['photo_0004'], '', $upload_settings);
        echo form_button('upload_photo', $locale['photo_0020'], $locale['photo_0020'], ['class' => 'btn-primary']);
        echo closeform();
    } else {
        echo "<div class='well text-center'>\n";
        echo str_replace(
            ['[link]', '[/link]'], ['<a href="'.FUSION_SELF.$aidlink.'&amp;section=album_form">', '</a>'], $locale['gallery_0012']
        );
        echo "</div>\n";
    }
}
