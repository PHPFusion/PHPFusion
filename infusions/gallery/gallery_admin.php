<?php
require_once "../../maincore.php";
pageAccess("PH");
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
include INFUSIONS."gallery/locale/".LOCALESET."gallery_admin.php";
require_once INCLUDES."photo_functions_include.php";
require_once INFUSIONS."gallery/classes/Admin.php";
require_once INCLUDES."infusions_include.php";

add_breadcrumb(array('link'=>INFUSIONS."gallery/gallery_admin.php".$aidlink, 'title'=>$locale['photo_000']));
$gll_settings = get_settings("gallery");

$gallery_tab['title'][] = $locale['photo_000'];
$gallery_tab['id'][] = "gallery";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $locale['601'];
$gallery_tab['id'][] = "photo_form";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $locale['600'];
$gallery_tab['id'][] = "album_form";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $locale['435'];
$gallery_tab['id'][] = "settings";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $locale['602'];
$gallery_tab['id'][] = "submissions";
$gallery_tab['icon'][] = "";

$allowed_pages = array("album_form", "photo_form", "settings", "submissions");
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : "gallery";

$_GET['album'] = 0;

$album_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['cat_id']) && isnum($_GET['cat_id']) ? true : false;

echo opentab($gallery_tab, $_GET['section'], "gallery_admin", true, "m-t-20");
switch($_GET['section'])
{
	case "album_form":
		add_breadcrumb(array('link' => '', 'title' => $album_edit ? $locale['606'] : $locale['605']));
		include "admin/gallery_cat.php";
		break;
	case "settings":
		add_breadcrumb(array('link' => INFUSIONS.'gallery/settings_gallery.php'.$aidlink,
						   'title' => $locale['photo_settings']));
		include "admin/gallery_settings.php";
		break;
	default:
		gallery_listing();
}
echo closetab();
require_once THEMES."templates/footer.php";


// for convenience sake, use cat_id.

function gallery_listing() {
	global $locale, $gll_settings, $aidlink;
	if ($_GET['album'] > 0) {
		//$gallery_info = self::get_album($_GET['gallery']);
		add_breadcrumb(
			array(
				'link' => clean_request("gallery=".$_GET['gallery'], array('gallery','action'), FALSE),
				"title" => "",
				//'title' => $gallery_info['album_title']
			)
		);
	}

	// xss
	$albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "");
	$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $albumRows ? $_GET['rowstart'] : 0;
	if ($albumRows>0) {
		// get albums.
		$result = dbquery("
		SELECT album.*, album.album_user as user_id,
		u.user_name, u.user_status, u.user_avatar,
		count(photo.photo_id) as photo_count
		FROM ".DB_PHOTO_ALBUMS." album
		LEFT JOIN ".DB_PHOTOS." photo on photo.album_id=album.album_id
		INNER JOIN ".DB_USERS." u on u.user_id=album.album_user
		".(multilang_table("PG") ? "where album_language='".LANGUAGE."' and" : "where")."
		".groupaccess('album.album_access')."
		GROUP BY album.album_id
		ORDER BY album.album_order ASC, album.album_datestamp DESC LIMIT ".intval($_GET['rowstart']).", ".intval($gll_settings['gallery_pagination'])."
		");
		$rows = dbrows($result);
		if ($rows>0) {
			// nav
			if ($albumRows > $rows) {
				echo "<div class='display-inline-block m-b-10'>\n";
				echo makepagenav($_GET['rowstart'], $gll_settings['thumbs_per_page'], $albumRows, 3, FUSION_SELF.$aidlink, "rowstart");
				echo "</div>\n";
			}

			echo "<div class='row'>\n";
			while ($data = dbarray($result)) {
				// Pending inspiration for new container layout.
				echo "<div class='col-xs-12 col-sm-2'>\n";
				echo "";

				echo "</div>\n";
			}
			echo "</div>\n";



		} else {
			echo "<div class='well m-t-20 text-center'>\n";
			echo $locale['471'];
			echo "</div>\n";
		}
	}

//	$row_count = isset($_GET['gallery']) && isnum($_GET['gallery']) ? dbcount("('photo_id')", $this->photo_db, "album_id='".intval($_GET['gallery'])."'") : "";
}