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

$gallery_tab['title'][] = $locale['600'];
$gallery_tab['id'][] = "album_form";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $locale['601'];
$gallery_tab['id'][] = "photo_form";
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


echo opentab($gallery_tab, $_GET['section'], "gallery_admin", true, "m-t-20");
switch($_GET['section'])
{
	default:
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
		gallery_listing();
}
echo closetab();
require_once THEMES."templates/footer.php";


function gallery_listing() {

}