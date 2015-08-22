<?php
if (isset($_POST['cancel'])) {
	redirect(FUSION_SELF.$aidlink);
}
/**
 * Move up and down album
 */
if (isset($_GET['action']) && ($_GET['action'] == "mu" || $_GET['action'] == "md") && isset($_GET['cat_id']) && isnum($_GET['cat_id']) && isset($_GET['order']) && isnum($_GET['order'])) {
	$album_max_order = dbresult(dbquery("SELECT MAX(album_order) FROM ".DB_PHOTO_ALBUMS." WHERE album_language='".LANGUAGE."'"), 0)+1;
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
// delete album
if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
	/**
	 * Purge album images
	 * @param $album_id
	 */
	function purgeAlbumImage($albumData) {
		if (!empty($albumData['album_image']) && file_exists(IMAGES_G.$albumData['album_image'])) {
			unlink(IMAGES_G.$albumData['album_image']);
		}
		if (!empty($albumData['album_thumb1']) && file_exists(IMAGES_G_T.$albumData['album_thumb1'])) {
			unlink(IMAGES_G_T.$albumData['album_thumb1']);
		}
		if (!empty($albumData['album_thumb2']) && file_exists(IMAGES_G_T.$albumData['album_thumb2'])) {
			unlink(IMAGES_G_T.$albumData['album_thumb2']);
		}
	}

	/**
	 * Purge all photo images
	 * @param $photoData
	 */
	function purgePhotoImage($photoData) {
		if (!empty($photoData['photo_filename']) && file_exists(IMAGES_G.$photoData['photo_filename'])) {
			unlink(IMAGES_G.$photoData['photo_filename']);
		}
		if (!empty($photoData['photo_thumb1']) && file_exists(IMAGES_G_T.$photoData['photo_thumb1'])) {
			unlink(IMAGES_G_T.$photoData['photo_thumb1']);
		}
		if (!empty($photoData['photo_thumb2']) && file_exists(IMAGES_G_T.$photoData['photo_thumb2'])) {
			unlink(IMAGES_G_T.$photoData['photo_thumb2']);
		}
	}

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
			if (isset($_POST['confirm_delete'])) {
				$targetAlbum = form_sanitizer($_POST['target_album'], '0', 'target_album');
				// Purge or move photos
				$photosResult = dbquery("SELECT * FROM ".DB_PHOTOS." WHERE album_id = '".intval($_GET['cat_id'])."'");
				if (dbrows($photosResult) > 0) {
					if ($targetAlbum > 0) {
						// move picture to $move_album
						while ($photo_data = dbarray($result)) {
							dbquery("UPDATE ".DB_PHOTO_ALBUMS." SET album_id='".intval($targetAlbum)."' WHERE photo_id='".$photo_data['photo_id']."'");
						}
						addNotice("success", sprintf($locale['album_0031'], $albumArray[$targetAlbum]));
					} else {
						// delete all
						$photoRows = 0;
						while ($photo_data = dbarray($result)) {
							purgePhotoImage($photo_data);
							dbquery_insert(DB_PHOTOS, $photo_data, 'delete');
							$photoRows++;
						}
						addNotice("success", sprintf($locale['album_0032'], $photoRows));
					}
				}
				// End purge or move
				purgeAlbumImage($albumData);
				dbquery_insert(DB_PHOTO_ALBUMS, $albumData, "delete");
			} else {
				// Confirmation form
				echo openmodal('confirm_steps', $locale['album_0027']);
				echo openform('inputform', 'post', FUSION_REQUEST);
				echo form_select('target_album', $locale['choose'], '', array(
												   'options' => $albumArray,
												   'inline' => TRUE,
												   'width' => '300px'
											   ));
				echo form_button('confirm_delete', $locale['confirm'], $_GET['cat_id'], array(
					'class' => 'btn-sm btn-danger col-sm-offset-3',
					'icon' => 'fa fa-trash'
				));
				echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-sm btn-default m-l-10'));
				echo closeform();
				echo closemodal();
			}
		} else {
			purgeAlbumImage($albumData);
			dbquery_insert(DB_PHOTO_ALBUMS, $albumData, "delete");
			addNotice("success", $locale['album_0030']);
		}
	}
	redirect(FUSION_SELF.$aidlink);
}
