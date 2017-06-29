<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/gallery_actions.php
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
if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.$aidlink);
}
/**
 * Move up and down album
 */
if (isset($_GET['action']) && ($_GET['action'] == "mu" || $_GET['action'] == "md") && isset($_GET['cat_id']) && isnum($_GET['cat_id']) && isset($_GET['order']) && isnum($_GET['order'])) {
    $album_max_order = dbresult(dbquery("SELECT MAX(album_order) FROM ".DB_PHOTO_ALBUMS." WHERE album_language='".LANGUAGE."'"), 0) + 1;
    if (dbcount("('album_id')", DB_PHOTO_ALBUMS, "album_id=' ".intval($_GET['cat_id'])." '")) {
        switch ($_GET['action']) {
            case "mu": // -1 album order
                if ($_GET['order'] < $album_max_order && $_GET['order'] >= 1) {
                    dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order = album_order+1 WHERE album_order='".$_GET['order']."'");
                    dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order= '".$_GET['order']."' WHERE album_id ='".$_GET['cat_id']."'");
                    addNotice("success", $locale['album_0025']);
                    redirect(FUSION_SELF.$aidlink);
                }
                break;
            case "md": // +1 album order.
                echo 'here';
                if ($_GET['order'] <= $album_max_order && $_GET['order'] > 1) {
                    dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order = album_order-1 WHERE album_order = '".$_GET['order']."'");
                    dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_order= '".$_GET['order']."' WHERE album_id ='".$_GET['cat_id']."'");
                    addNotice("success", $locale['album_0026']);
                    redirect(FUSION_SELF.$aidlink);
                }
                break;
            default:
                redirect(FUSION_SELF.$aidlink);
        }
    }
}
/**
 * Move up and down photo
 */
if (isset($_GET['action']) && ($_GET['action'] == "pu" || $_GET['action'] == "pd") && isset($_GET['photo_id']) && isnum($_GET['photo_id']) && isset($_GET['album_id']) && isnum($_GET['album_id']) && isset($_GET['order']) && isnum($_GET['order'])) {
    $photo_max_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".intval($_GET['album_id'])."'"), 0) + 1;
    if (dbcount("('photo_id')", DB_PHOTOS, "photo_id=' ".intval($_GET['photo_id'])." '")) {
        switch ($_GET['action']) {
            case "pu":
                if ($_GET['order'] < $photo_max_order && $_GET['order'] >= 1) {
                    dbquery("UPDATE ".DB_PHOTOS." SET photo_order = photo_order+1 WHERE photo_order='".$_GET['order']."'");
                    dbquery("UPDATE ".DB_PHOTOS." SET photo_order= '".$_GET['order']."' WHERE photo_id ='".$_GET['photo_id']."'");
                    addNotice("success", $locale['photo_0022']);
                    redirect(clean_request("", array("album_id", "aid"), TRUE));
                }
                break;
            case "pd":
                if ($_GET['order'] <= $photo_max_order && $_GET['order'] > 1) {
                    dbquery("UPDATE ".DB_PHOTOS." SET photo_order = photo_order-1 WHERE photo_order = '".$_GET['order']."'");
                    dbquery("UPDATE ".DB_PHOTOS." SET photo_order= '".$_GET['order']."' WHERE photo_id ='".$_GET['photo_id']."'");
                    addNotice("success", $locale['photo_0023']); //change
                    redirect(clean_request("", array("album_id", "aid"), TRUE));
                }
                break;
            default:
                redirect(FUSION_SELF.$aidlink);
        }
    }
}

// delete album
if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
    $result = dbquery("select * from ".DB_PHOTO_ALBUMS." where album_id='".intval($_GET['cat_id'])."'");
    if (dbrows($result) > 0) { // album verified
        $albumData = dbarray($result);
        // photo existed
        if (dbcount("('photo_id')", DB_PHOTOS, "album_id = '".intval($_GET['cat_id'])."'")) {
            $list = get_albumOpts();
            $albumArray[0] = $locale['album_0028'];
            foreach ($list as $album_id => $album_title) {
                $albumArray[$album_id] = sprintf($locale['album_0029'], $album_title);
            }

            // unset own album
            unset($albumArray[$_GET['cat_id']]);

            if (isset($_POST['confirm_delete'])) {
                $targetAlbum = form_sanitizer($_POST['target_album'], '0', 'target_album');
                // Purge or move photos
                $photosResult = dbquery("SELECT * FROM ".DB_PHOTOS." WHERE album_id = '".intval($_GET['cat_id'])."'");
                if (dbrows($photosResult) > 0) {
                    if ($targetAlbum > 0) {
                        // move picture to $move_album
                        $target_max_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".intval($targetAlbum)."'"),
                                0) + 1;
                        while ($photo_data = dbarray($photosResult)) {
                            $photo_data['photo_order'] = $target_max_order;
                            dbquery("UPDATE ".DB_PHOTOS." SET album_id='".intval($targetAlbum)."' WHERE photo_id='".$photo_data['photo_id']."'");
                            $target_max_order++;
                        }
                        addNotice("success", sprintf($locale['album_0031'], $albumArray[$targetAlbum]));
                    } else {
                        // delete all
                        $photoRows = 0;
                        while ($photo_data = dbarray($photosResult)) {
                            purgePhotoImage($photo_data);
                            dbquery("delete from ".DB_COMMENTS." where comment_item_id='".intval($photo_data['photo_id'])."' and comment_type='P'");
                            dbquery("delete from ".DB_RATINGS." where rating_item_id='".intval($photo_data['photo_id'])."' and rating_type='P'");
                            dbquery_insert(DB_PHOTOS, $photo_data, 'delete');
                            $photoRows++;
                        }
                        addNotice("success", sprintf($locale['album_0032'], $photoRows));
                    }
                }
                purgeAlbumImage($albumData);
                dbquery_insert(DB_PHOTO_ALBUMS, $albumData, "delete");
                redirect(FUSION_SELF.$aidlink);
            } else {
                // Confirmation form
                echo openmodal('confirm_steps', $locale['album_0027']);
                echo openform('inputform', 'post', FUSION_REQUEST);
                echo form_select('target_album', $locale['choose'], '', array(
                    'options' => $albumArray,
                    'inline'  => TRUE,
                    'width'   => '300px'
                ));
                echo form_button('confirm_delete', $locale['confirm'], $_GET['cat_id'], array(
                    'class' => 'btn-sm btn-danger col-sm-offset-3',
                    'icon'  => 'fa fa-trash'
                ));
                echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-sm btn-default m-l-10'));
                echo closeform();
                echo closemodal();
            }
        } else {
            purgeAlbumImage($albumData);
            dbquery_insert(DB_PHOTO_ALBUMS, $albumData, "delete");
            addNotice("success", $locale['album_0030']);
            redirect(FUSION_SELF.$aidlink);
        }
    }
}

// delete photo
if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['photo_id']) && isnum($_GET['photo_id'])) {
    if (dbcount("(photo_id)", DB_PHOTOS, "photo_id='".intval($_GET['photo_id'])."'")) {
        $photo_data = dbarray(dbquery("SELECT photo_id, album_id, photo_title, photo_filename, photo_thumb1, photo_thumb2, photo_order FROM ".DB_PHOTOS." WHERE photo_id='".intval($_GET['photo_id'])."'"));
        purgePhotoImage($photo_data);
        dbquery("delete from ".DB_COMMENTS." where comment_item_id='".intval($photo_data['photo_id'])."' and comment_type='P'");
        dbquery("delete from ".DB_RATINGS." where rating_item_id='".intval($photo_data['photo_id'])."' and rating_type='P'");
        dbquery_order(DB_PHOTOS, $photo_data['photo_order'], "photo_order", $photo_data['photo_id'], "photo_id", $photo_data['album_id'], "album_id",
            FALSE, FALSE, "delete");
        dbquery_insert(DB_PHOTOS, $photo_data, 'delete');
        addNotice("success", $locale['photo_0024']);
        redirect(clean_request("", array("aid", "album_id"), TRUE));
    }
}
// purge photos
if (isset($_GET['action']) && $_GET['action'] == "purge" && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
    $result = dbquery("select * from ".DB_PHOTO_ALBUMS." where album_id='".intval($_GET['cat_id'])."'");
    if (dbrows($result) > 0) { // album verified
        $albumData = dbarray($result);
        $photoResult = dbquery("select photo_id, photo_filename, photo_thumb1, photo_thumb2
		from ".DB_PHOTOS." where album_id='".intval($_GET['cat_id'])."'");
        if (dbrows($photoResult) > 0) {
            if (!isset($_POST['purge_confirm'])) {
                echo str_replace(['[STRONG]', '[/STRONG]'], ['<strong>', '</strong>'], $locale['photo_0026'])."<br/><br/>\n";
                echo openform("purgephotos", "post", FUSION_REQUEST);
                echo form_button("purge_confirm", $locale['photo_0027'], $locale['photo_0027'], array("class" => "btn-danger m-r-10"));
                echo form_button("cancel", $locale['photo_0028'], $locale['photo_0028'], array("class" => "btn-default m-r-10"));
                echo closeform();
            } else {
                while ($pData = dbarray($photoResult)) {
                    purgePhotoImage($pData);
                    // purging everything, order is not relevant
                    dbquery_insert(DB_PHOTOS, $pData, "delete");
                }
                redirect(clean_request("album_id=".$_GET['cat_id'], array("aid")), TRUE);
            }
        }
    }
}
