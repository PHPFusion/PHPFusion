<?php

/**
 * Move up and down album
 */
if (isset($_GET['action']) &&
	($_GET['action'] == "mu" || $_GET['action'] == "md") &&
	isset($_GET['cat_id']) && isnum($_GET['cat_id']) &&
	isset($_GET['order']) && isnum($_GET['order']))
{
	$album_max_order = dbresult(dbquery("SELECT MAX(album_order) FROM ".DB_PHOTO_ALBUMS." WHERE album_language='".LANGUAGE."'"), 0)+1;
	if (dbcount("('album_id')", DB_PHOTO_ALBUMS, "album_id=' ".intval($_GET['cat_id'])." '")) {
		switch($_GET['action']) {
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



}
