<?php
require_once "../../maincore.php";
pageAccess("PH");
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
include INFUSIONS."gallery/locale/".LOCALESET."gallery_admin.php";
require_once INCLUDES."photo_functions_include.php";
require_once INFUSIONS."gallery/classes/Admin.php";
require_once INCLUDES."infusions_include.php";

add_breadcrumb(array('link'=>INFUSIONS."gallery/gallery_admin.php".$aidlink, 'title'=>$locale['gallery_0001']));
$gll_settings = get_settings("gallery");

$album_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['cat_id']) && isnum($_GET['cat_id']) ? true : false;
$photo_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['photo_id']) && isnum($_GET['photo_id']) ? true : false;

$gallery_tab['title'][] = $locale['gallery_0001'];
$gallery_tab['id'][] = "gallery";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $photo_edit ? $locale['gallery_0003'] : $locale['gallery_0002'];
$gallery_tab['id'][] = "photo_form";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $album_edit ? $locale['gallery_0005'] : $locale['gallery_0004'];
$gallery_tab['id'][] = "album_form";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $locale['gallery_0006'];
$gallery_tab['id'][] = "settings";
$gallery_tab['icon'][] = "";

$gallery_tab['title'][] = $locale['gallery_0007'];
$gallery_tab['id'][] = "submissions";
$gallery_tab['icon'][] = "";

$allowed_pages = array("album_form", "photo_form", "settings", "submissions", "actions");
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : "gallery";

$_GET['album'] = 0;


echo opentab($gallery_tab, $_GET['section'], "gallery_admin", true, "m-t-20");
switch($_GET['section'])
{
	case "photo_form":
		// make breadcrumb
		add_breadcrumb(array("link"=>"", "title"=> $photo_edit ?  $locale['gallery_0003'] : $locale['gallery_0002']));
		// include file
		include "admin/photos.php";
		break;
	case "album_form":
		add_breadcrumb(array("link" => '', "title" => $album_edit ? $locale['gallery_0005'] : $locale['gallery_0004']));
		include "admin/gallery_cat.php";
		break;
	case "actions":
		include "admin/gallery_actions.php";
		break;
	case "settings":
		add_breadcrumb(array("link" => "", "title" => $locale['gallery_0006']));
		include "admin/gallery_settings.php";
		break;
	case "submissions":
		break;
	default:
		if (isset($_GET['album_id']) && isnum($_GET['album_id'])) {
			gallery_photo_listing();
		} else {
			gallery_album_listing();
		}
}
echo closetab();
require_once THEMES."templates/footer.php";

/**
 * Gallery Photo Listing UI
 */
function gallery_photo_listing()
{
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
	$photoRows = dbcount("(photo_id)", DB_PHOTOS, "album_id='".intval($_GET['album_id'])."'");

	$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $photoRows ? $_GET['rowstart'] : 0;
	if (!empty($photoRows)) {
		$result = dbquery("
		select photos.*,
		album.*,
		photos.photo_user as user_id, u.user_name, u.user_status, u.user_avatar,
		count(comment_id) as comment_count,
		sum(rating_vote) as total_votes,
		count(rating_id) as rating_count
		FROM ".DB_PHOTOS." photos
		INNER JOIN ".DB_PHOTO_ALBUMS." album on photos.album_id = album.album_id
		INNER JOIN ".DB_USERS." u on u.user_id = photos.photo_user
		LEFT JOIN ".DB_COMMENTS." comment on comment.comment_item_id= photos.photo_id AND comment_type = 'PH'
		LEFT JOIN ".DB_RATINGS." rating on rating.rating_item_id = photos.photo_id AND rating_type = 'PH'
		WHERE ".groupaccess('album.album_access')." and photos.album_id = '".intval($_GET['album_id'])."'
		GROUP BY photo_id
		ORDER BY photos.photo_order ASC, photos.photo_datestamp DESC LIMIT ".intval($_GET['rowstart']).", ".intval($gll_settings['gallery_pagination'])."
		");
		$rows = dbrows($result);

		if ($rows>0) {

			// nav
			if ($photoRows > $rows) {
				echo "<div class='display-inline-block m-b-10'>\n";
				echo makepagenav($_GET['rowstart'], $gll_settings['thumbs_per_page'], $photoRows, 3, FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id'], "rowstart");
				echo "</div>\n";
			}

			echo "<div class='row m-t-20'>\n";
			$i = 1;
			while ($data = dbarray($result)) {
				echo "<div class='col-xs-12 col-sm-2'>\n";
				echo "<div class='panel panel-default'>\n";
				echo "<div class='overflow-hide' style='height:100px'>\n";
				if (!empty($data['photo_thumb1']) && file_exists(IMAGES_G_T.$data['photo_thumb1'])) {
					echo thumbnail(IMAGES_G_T.$data['photo_thumb1'], $gll_settings['thumb_w']."px", FUSION_SELF.$aidlink."&amp;photo_id=".$data['photo_id'], FALSE, TRUE, "");
				} elseif (!empty($data['photo_thumb2']) && file_exists(IMAGES_G.$data['photo_thumb2'])) {
					echo thumbnail(IMAGES_G.$data['photo_thumb2'], $gll_settings['thumb_w']."px", FUSION_SELF.$aidlink."&amp;photo_id=".$data['photo_id'], FALSE, TRUE, "");
				} elseif (!empty($data['photo_filename']) && file_exists(IMAGES_G.$data['photo_filename'])) {
					echo thumbnail(IMAGES_G.$data['photo_filename'], $gll_settings['thumb_w']."px", FUSION_SELF.$aidlink."&amp;photo_id=".$data['photo_id'], FALSE, TRUE, "");
				}
				echo "</div>\n";
				echo "<div class='panel-body overflow-hide' style='max-height:60px;'>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;photo_id=".$data['photo_id']."'><strong>\n".trim_text($data['photo_title'],20)."</strong>\n</a><br/>";
				echo "<small><strong>".trim_text($data['album_title'], 25)."</strong></small>\n";
				echo "</div>\n";
				echo "<div class='panel-footer'>\n";
				echo "<div class='dropdown'>\n";
				echo "<button data-toggle='dropdown' class='btn btn-default dropdown-toggle btn-block' type='button'> ".$locale['gallery_0013']." <span class='caret'></span></button>\n";
				echo "<ul class='dropdown-menu'>\n";
				echo "<li><a href='".FUSION_SELF.$aidlink."&amp;section=photo_form&amp;action=edit&amp;photo_id=".$data['photo_id']."'><i class='fa fa-edit fa-fw'></i> ".$locale['gallery_0016']."</a></li>\n";
				echo ($i > 1) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=mup&amp;photo_id=".$data['photo_id']."&amp;order=".($data['photo_order']-1)."'><i class='fa fa-arrow-left fa-fw'></i> ".$locale['gallery_0014']."</a></li>\n" : "";
				echo ($i !== $rows) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=mud&amp;photo_id=".$data['photo_id']."&amp;order=".($data['photo_order']+1)."'><i class='fa fa-arrow-right fa-fw'></i> ".$locale['gallery_0015']."</a></li>\n" : "";
				echo  "<li class='divider'></li>\n";
				echo  "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=delete&amp;photo_id=".$data['photo_id']."'><i class='fa fa-trash fa-fw'></i> ".$locale['gallery_0017']."</a></li>\n";
				echo "</ul>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "<div class='panel-footer'>\n";
				echo "<span class='m-r-10'>\n<i class='fa fa-comments-o' title='".$locale['comments']."'></i> ".$data['comment_count']."</span>\n";
				echo "<span class='m-r-5'>\n<i class='fa fa-star' title='".$locale['ratings']."'></i> ".($data['rating_count'] > 0 ? $data['total_votes']/$data['rating_count']*10 : 0)." /10</span>\n";
				echo "</div>\n</div>\n";
				echo "</div>\n";
				$i++;
			}
			echo "</div>\n";



			if ($photoRows > $rows) {
				echo "<div class='display-inline-block m-b-10'>\n";
				echo makepagenav($_GET['rowstart'], $gll_settings['thumbs_per_page'], $photoRows, 3, FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id'], "rowstart");
				echo "</div>\n";
			}

		} else {
	//		redirect(FUSION_SELF.$aidlink);
		}
	} else {
	//	redirect(FUSION_SELF.$aidlink);
	}



}

/**
 * Gallery Album Listing UI
 */
function gallery_album_listing() {
	global $locale, $gll_settings, $aidlink;

	// xss
	$albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "");
	$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $albumRows ? $_GET['rowstart'] : 0;

	if (!empty($albumRows)) {
		// get albums.
		$result = dbquery("
		SELECT album.album_id, album.album_title, album.album_thumb1, album.album_order, album.album_user as user_id,
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

			echo "<div class='row m-t-20'>\n";
			$i = 1;
			while ($data = dbarray($result)) {
				echo "<div class='col-xs-12 col-sm-2'>\n";
				echo "<div class='panel panel-default'>\n";
				echo "<div class='panel-heading'>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;album_id=".$data['album_id']."'><strong>".trimlink($data['album_title'], 20)."</strong>\n";
				echo "</div>\n";
				echo "<div class='overflow-hide' style='height:100px'>\n";
				if (!empty($data['album_thumb1']) && file_exists(IMAGES_G_T.$data['album_thumb1'])) {
					echo thumbnail(IMAGES_G_T.$data['album_thumb1'], $gll_settings['thumb_w']."px", FUSION_SELF.$aidlink."&amp;album_id=".$data['album_id'], FALSE, TRUE, "");
				} elseif (!empty($data['album_image']) && file_exists(IMAGES_G.$data['album_image'])) {
					echo thumbnail(IMAGES_G.$data['album_image'], $gll_settings['thumb_w']."px", FUSION_SELF.$aidlink."&amp;album_id=".$data['album_id'], FALSE, TRUE, "");
				} else {
					echo thumbnail(IMAGES_G."album_default.jpg", $gll_settings['thumb_w']."px", FUSION_SELF.$aidlink."&amp;album_id=".$data['album_id'], FALSE, TRUE, "");
				}
				echo "</div>\n";
				echo "<div class='panel-body'>\n";
				echo "<div class='dropdown'>\n";
				echo "<button data-toggle='dropdown' class='btn btn-default dropdown-toggle btn-block' type='button'> ".$locale['album_0020']." <span class='caret'></span></button>\n";
				echo "<ul class='dropdown-menu'>\n";
				echo "<li><a href='".FUSION_SELF.$aidlink."&amp;section=album_form&amp;action=edit&amp;cat_id=".$data['album_id']."'><i class='fa fa-edit fa-fw'></i> ".$locale['album_0024']."</a></li>\n";
				echo ($i > 1) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=mu&amp;cat_id=".$data['album_id']."&amp;order=".($data['album_order']-1)."'><i class='fa fa-arrow-left fa-fw'></i> ".$locale['album_0021']."</a></li>\n" : "";
				echo ($i !== $rows) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=md&amp;cat_id=".$data['album_id']."&amp;order=".($data['album_order']+1)."'><i class='fa fa-arrow-right fa-fw'></i> ".$locale['album_0022']."</a></li>\n" : "";
				echo  "<li class='divider'></li>\n";
				echo  "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=delete&amp;cat_id=".$data['album_id']."'><i class='fa fa-trash fa-fw'></i> ".$locale['album_0023']."</a></li>\n";
				echo "</ul>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "<div class='panel-footer'>\n";
				echo format_word($data['photo_count'], $locale['fmt_photo']);
				echo "</div>\n</div>\n";
				echo "</div>\n";
				$i++;
			}
			echo "</div>\n";
		} else {
			echo "<div class='well m-t-20 text-center'>\n";
			echo $locale['gallery_0011'];
			echo "</div>\n";
		}
	} else {
		echo "<div class='well m-t-20 text-center'>\n";
		echo $locale['gallery_0011'];
		echo "</div>\n";
	}
}


/**
 * Get all the album listing.
 * @return array
 */
function get_albumOpts() {
	$list = array();
	$result = dbquery("SELECT * FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "where album_language='".LANGUAGE."'" : "")." order by album_order asc");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			$list[$data['album_id']] = $data['album_title'];
		}
	}
	return $list;
}