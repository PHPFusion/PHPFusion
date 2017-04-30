<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery/gallery_admin.php
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
require_once "../../maincore.php";
pageAccess("PH");
require_once THEMES."templates/admin_header.php";
$aidlink = fusion_get_aidlink();
if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/settings.php");
} else {
    $locale = fusion_get_locale('', LOCALE."English/admin/settings.php");
}
$locale = fusion_get_locale('', GALLERY_ADMIN_LOCALE) + $locale;
require_once INCLUDES."photo_functions_include.php";
require_once INCLUDES."infusions_include.php";
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['gallery_0001']]);
add_to_title($locale['gallery_0001']);
$gll_settings = get_settings("gallery");
if (!empty($_GET['section'])){
	switch ($_GET['section']) {
    	case "photo_form":
        	\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $locale['gallery_0002']]);
	        break;
    	case "album_form":
        	\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $locale['gallery_0004']]);
	        break;
    	case "actions":
        	break;
	    case "settings":
    	    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $locale['gallery_0006']]);
        	break;
	    case "submissions":
    	    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $locale['gallery_0007']]);
        	break;
	    default:
	}
}

add_to_head("
<style>
.panel-default > .panel-image-wrapper {
    height: 150px;
    max-height: 150px;
    min-width: 100%;
    overflow: hidden;
}
.panel-default .album_title {
    width: 100%;
    margin-bottom: 5px;
    padding-bottom: 5px;
    height: 50px;
    overflow:hidden;
    text-overflow: ellipsis;
    content: \"\";
    position:relative;
}
.panel-default .album_title,
.panel-default .album_link {
    text-decoration: none;
}
.panel-default .album_title:hover, .panel-default .album_title:focus {
    color: #333;
}
.panel-default .album_title:before {
    content: '';
    width: 100%;
    height: 50px;
    position: absolute;
    left: 0;
    display: block;
    top: 0;
    background: linear-gradient(transparent 32px, white);
}
</style>
");
$album_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['cat_id']) && isnum($_GET['cat_id']) ? TRUE : FALSE;
$photo_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['photo_id']) && isnum($_GET['photo_id']) ? TRUE : FALSE;
$gallery_tab['title'][] = $locale['gallery_0001'];
$gallery_tab['id'][] = "gallery";
$gallery_tab['icon'][] = "fa fa-camera-retro";
$gallery_tab['title'][] = $photo_edit ? $locale['gallery_0003'] : $locale['gallery_0002'];
$gallery_tab['id'][] = "photo_form";
$gallery_tab['icon'][] = "fa fa-picture-o";
$gallery_tab['title'][] = $album_edit ? $locale['gallery_0005'] : $locale['gallery_0004'];
$gallery_tab['id'][] = "album_form";
$gallery_tab['icon'][] = "fa fa-plus";
$gallery_tab['title'][] = $locale['gallery_0007']."&nbsp;<span class='badge'>".dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='p'")."</span>";
$gallery_tab['id'][] = "submissions";
$gallery_tab['icon'][] = "fa fa-inbox";
$gallery_tab['title'][] = $locale['gallery_0006'];
$gallery_tab['id'][] = "settings";
$gallery_tab['icon'][] = "fa fa-cogs";
$allowed_pages = array("album_form", "photo_form", "settings", "submissions", "actions");
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : "gallery";
$_GET['album'] = 0;

// Need to put breadcrumb here before opentable, or else it won't cache in Artemis.
// @todo Class so that query data can be passed around.

if (isset($_GET['album_id']) && isnum($_GET['album_id'])) {
    $sql = "SELECT album_title FROM ".DB_PHOTO_ALBUMS." WHERE album_id=:id";
    $param = [':id' => $_GET['album_id']];
    \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
        'link'  => clean_request("album_id=".$_GET['album_id'], array("aid"), FALSE),
        "title" => dbresult(dbquery($sql, $param), 0)
    ]);
}

opentable($locale['gallery_0001']);
echo opentab($gallery_tab, $_GET['section'], "gallery_admin", TRUE, "nav-tabs m-t-20", 'section', ['album_id']);
switch ($_GET['section']) {
    case "photo_form":
        include "admin/photos.php";
        break;
    case "album_form":
        include "admin/gallery_cat.php";
        break;
    case "actions":
        include "admin/gallery_actions.php";
        break;
    case "settings":
        include "admin/gallery_settings.php";
        break;
    case "submissions":
        include "admin/photo_submissions.php";
        break;
    default:
        if (isset($_GET['album_id']) && isnum($_GET['album_id'])) {
            gallery_photo_listing();
        } else {
            gallery_album_listing();
        }
}
echo closetab();
closetable();
require_once THEMES."templates/footer.php";
/**
 * Gallery Photo Listing UI
 */
function rating_vote($id, $type) {
            $count_db = dbarray(dbquery("SELECT
				IF(SUM(rating_vote)>0, SUM(rating_vote), 0) AS total_votes
				FROM ".DB_RATINGS."
				WHERE rating_item_id='".$id."' AND rating_type='".$type."'
             "));
return $count_db['total_votes'];
}
function rating_count($id, $type) {
            $count_db = dbarray(dbquery("SELECT
				COUNT(rating_id) AS rating_count
				FROM ".DB_RATINGS."
				WHERE rating_item_id='".$id."' AND rating_type='".$type."'
             "));
return $count_db['rating_count'];
}
function gallery_photo_listing() {

    $locale = fusion_get_locale();
    $gll_settings = get_settings('gallery');
    $aidlink = fusion_get_aidlink();

    // xss
    $photoRows = dbcount("(photo_id)", DB_PHOTOS, "album_id='".intval($_GET['album_id'])."'");
    $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $photoRows ? $_GET['rowstart'] : 0;
    if (!empty($photoRows)) {
        $result = dbquery("
		select photos.*,
		album.*,
		photos.photo_user as user_id, u.user_name, u.user_status, u.user_avatar,
		count(comment_id) as comment_count
		FROM ".DB_PHOTOS." photos
		INNER JOIN ".DB_PHOTO_ALBUMS." album on photos.album_id = album.album_id
		INNER JOIN ".DB_USERS." u on u.user_id = photos.photo_user
		LEFT JOIN ".DB_COMMENTS." comment on comment.comment_item_id= photos.photo_id AND comment_type = 'P'
		WHERE ".groupaccess('album.album_access')." and photos.album_id = '".intval($_GET['album_id'])."'
		GROUP BY photo_id
		ORDER BY photos.photo_order ASC, photos.photo_datestamp DESC LIMIT ".intval($_GET['rowstart']).", ".intval($gll_settings['gallery_pagination'])."
		");
        $rows = dbrows($result);
        // Photo Album header

        $album_data = dbarray(dbquery("select album_id, album_title, album_description, album_datestamp, album_access from ".DB_PHOTO_ALBUMS." WHERE album_id='".intval($_GET['album_id'])."'"));

        echo "<h2><strong>\n".$album_data['album_title']."</strong></h2>\n";

        echo "<strong>".$locale['album_0003']."</strong> ".$album_data['album_description'];

        echo "<div class='clearfix m-t-10'>\n";
        echo "<div class='pull-right text-right col-xs-6 col-sm-6'>".sprintf($locale['gallery_0019'], $rows, $photoRows)."</div>\n";
        echo "<span class='m-r-15'><strong>".$locale['gallery_0020']."</strong> ".timer($album_data['album_datestamp'])."</span>\n";
        echo "<span class='m-r-15'><strong>".$locale['gallery_0021']."</strong> ".getgroupname($album_data['album_access'])."</span>\n";
        if ($photoRows > $rows) {
            echo "<div class='display-inline-block m-b-10'>\n";
            echo makepagenav($_GET['rowstart'], $gll_settings['gallery_pagination'], $photoRows, 3, FUSION_SELF.$aidlink."&amp;album_id=".$_GET['album_id']."&amp;");
            echo "</div>\n";
        }
        echo "</div>\n";

        echo "<hr/>\n";

        if ($rows > 0) {
            echo "<a class='m-t-10 btn btn-danger' href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=purge&amp;cat_id=".$_GET['album_id']."'>".$locale['photo_0025']."</a>\n";
            echo "<div class='row m-t-20'>\n";
            $i = 1;
            while ($data = dbarray($result)) {            	$rcount = rating_count($data['photo_id'], 'P');
            	$vcount = rating_vote($data['photo_id'], 'P');
                echo "<div class='col-xs-12' style='float:left; width:20%; padding:0 15px;'>\n";
                // <!-------panel------>
                echo "<div class='panel-default m-b-20'>\n";
                echo "<div class='panel-image-wrapper'>\n";
                echo displayPhotoImage($data['photo_filename'], $data['photo_thumb1'], $data['photo_thumb2'], IMAGES_G.$data['photo_filename']);
                echo "</div>\n";
                echo "<div class='panel-body p-0'>\n";
                echo "<div class='dropdown pull-right spacer-xs'>\n";
                echo "<button data-toggle='dropdown' class='btn btn-default dropdown-toggle' type='button'> ".$locale['gallery_0013']." <span class='caret'></span></button>\n";
                echo "<ul class='dropdown-menu'>\n";
                echo "<li><a href='".FUSION_SELF.$aidlink."&amp;section=photo_form&amp;action=edit&amp;photo_id=".$data['photo_id']."'><i class='fa fa-edit fa-fw'></i> ".$locale['gallery_0003']."</a></li>\n";
                echo ($i > 1) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=pu&amp;photo_id=".$data['photo_id']."&amp;album_id=".$data['album_id']."&amp;order=".($data['photo_order'] - 1)."'><i class='fa fa-arrow-left fa-fw'></i> ".$locale['gallery_0014']."</a></li>\n" : "";
                echo ($i !== $rows) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=pd&amp;photo_id=".$data['photo_id']."&amp;album_id=".$data['album_id']."&amp;order=".($data['photo_order'] + 1)."'><i class='fa fa-arrow-right fa-fw'></i> ".$locale['gallery_0015']."</a></li>\n" : "";
                echo "<li class='divider'></li>\n";
                echo "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=delete&amp;photo_id=".$data['photo_id']."'><i class='fa fa-trash fa-fw'></i> ".$locale['gallery_0017']."</a></li>\n";
                echo "</ul>\n";
                echo "</div>\n";
                echo "<div class='overflow-hide'>\n";
                echo "<h4 class='album_title'><strong>".$data['photo_title']."</strong>\n</h4>\n";
                echo "</div>\n";
                echo "<div class='display-block'>\n";
                echo "<span class='m-r-10'>\n<i class='fa fa-comments-o' title='".$locale['comments']."'></i> ".$data['comment_count']."</span>\n";
                echo "<span class='m-r-5'>\n<i class='fa fa-star' title='".$locale['ratings']."'></i> ".($rcount > 0 ? $rcount / $vcount * 10 : 0)." /10</span>\n";
                echo "</div>\n";
                echo "</div>\n";
                echo "</div>\n";
                // <!-------panel------>
                echo "</div>\n";
                $i++;
            }
            echo "</div>\n";
        } else {
            redirect(FUSION_SELF.$aidlink);
        }
    } else {
        redirect(FUSION_SELF.$aidlink);
    }
}

/**
 * Gallery Album Listing UI
 */
function gallery_album_listing() {

    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $gll_settings = get_settings('gallery');

    // xss
    $albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "");
    $photoRows = dbcount("(photo_id)", DB_PHOTOS, "");
    $update = dbarray(dbquery("select max(photo_datestamp) 'last_updated' from ".DB_PHOTOS.""));
    $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $albumRows ? $_GET['rowstart'] : 0;
    if (!empty($albumRows)) {
        // get albums.
        $gallery_sql = "SELECT album.album_id, album.album_title, album.album_image, album.album_thumb2, album.album_thumb1, album.album_order, album.album_user as user_id,
		u.user_name, u.user_status, u.user_avatar,
		COUNT(photo.photo_id) AS photo_count
		FROM ".DB_PHOTO_ALBUMS." album
		LEFT JOIN ".DB_PHOTOS." photo on photo.album_id=album.album_id
		INNER JOIN ".DB_USERS." u on u.user_id=album.album_user
		".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('album.album_access')."
		GROUP BY album.album_id
		ORDER BY album.album_order ASC, album.album_datestamp DESC LIMIT ".intval($_GET['rowstart']).", ".$gll_settings['gallery_pagination'];
        $result = dbquery($gallery_sql);
        $rows = dbrows($result);
        // Photo Album header
        echo "<div class='clearfix'>\n";
        echo "<h2><strong>\n".$locale['gallery_0022']."</strong></h2>\n";
        echo "<div class='pull-right text-right col-xs-6 col-sm-6'>".sprintf($locale['gallery_0018'], $rows, $albumRows)."</div>\n";
        echo "<span class='m-r-15'>".sprintf($locale['gallery_0023'], $albumRows, $photoRows, timer($update['last_updated']))."</span>";
        if ($albumRows > $rows) {
            echo "<div class='display-inline-block m-b-10'>\n";
            echo makepagenav($_GET['rowstart'], $gll_settings['gallery_pagination'], $albumRows, 3, FUSION_SELF.$aidlink."&amp;");
            echo "</div>\n";
        }
        echo "</div>\n";
        echo "<hr/>\n";
        if ($rows > 0) {
            echo "<div class='row m-t-20'>\n";
            $i = 1;
            while ($data = dbarray($result)) {
                echo "<div class='col-xs-12' style='float:left; width:20%; padding:0 15px;'>\n";
                // <!-------panel------>
                echo "<div class='panel-default m-b-20'>\n";
                echo "<div class='panel-image-wrapper'>\n";
                if ($data['photo_count']) {
                    $link = FUSION_SELF.$aidlink."&amp;album_id=".$data['album_id'];
                    echo displayAlbumImage($data['album_image'], $data['album_thumb1'], $data['album_thumb2'], $link);
                } else {
                    echo displayAlbumImage($data['album_image'], $data['album_thumb1'], $data['album_thumb2'], "");
                }
                echo "</div>\n";
                echo "<div class='panel-body p-0'>\n";
                echo "<div class='dropdown pull-right spacer-xs'>\n";
                echo "<button data-toggle='dropdown' class='btn btn-default dropdown-toggle' type='button'> ".$locale['album_0020']." <span class='caret'></span></button>\n";
                echo "<ul class='dropdown-menu'>\n";
                echo "<li><a href='".FUSION_SELF.$aidlink."&amp;section=album_form&amp;action=edit&amp;cat_id=".$data['album_id']."'><i class='fa fa-edit fa-fw'></i> ".$locale['gallery_0005']."</a></li>\n";
                echo ($i > 1) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=mu&amp;cat_id=".$data['album_id']."&amp;order=".($data['album_order'] - 1)."'><i class='fa fa-arrow-left fa-fw'></i> ".$locale['album_0021']."</a></li>\n" : "";
                echo ($i !== $rows) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=md&amp;cat_id=".$data['album_id']."&amp;order=".($data['album_order'] + 1)."'><i class='fa fa-arrow-right fa-fw'></i> ".$locale['album_0022']."</a></li>\n" : "";
                echo "<li class='divider'></li>\n";
                echo "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=delete&amp;cat_id=".$data['album_id']."'><i class='fa fa-trash fa-fw'></i> ".$locale['album_0023']."</a></li>\n";
                echo "</ul>\n";
                echo "</div>\n";
                echo "<div class='overflow-hide'>\n";
                echo ($data['photo_count'] ? "<a class='album_link' href='".FUSION_SELF.$aidlink."&amp;album_id=".$data['album_id']."'><h4 class='album_title'>\n<strong>".$data['album_title']."</strong>\n</h4></a>\n" : "<h4 class='album_title'><strong>".$data['album_title']."</strong></h4>\n")."</h4>\n";
                echo "</div>\n";
                echo "<div class='display-block'>\n";
                echo format_word($data['photo_count'], $locale['fmt_photo']);
                echo "</div>\n";

                echo "</div>\n";
                echo "</div>\n";
                // <!-------panel------>
                echo "</div>\n";
                $i++;
            }
            echo "</div>\n";
        } else {
            echo "<div class='well m-t-20 text-center'>\n";
            echo $locale['gallery_0024'];
            echo "</div>\n";
        }
    } else {
        echo "<div class='well m-t-20 text-center'>\n";
        echo $locale['gallery_0024'];
        echo "</div>\n";
    }
}

/**
 * Get all the album listing.
 *
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

/**
 * Delete and Purge Album Photos
 *
 * @param $albumData
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
 *
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

/**
 * Purge all submissions photo images
 *
 * @param $photoData
 */
function purgeSubmissionsPhotoImage($photoData) {
    $submissions_dir = INFUSIONS."gallery/submissions/";
    $submissions_dir_t = INFUSIONS."gallery/submissions/thumbs/";
    if (!empty($photoData['photo_filename']) && file_exists($submissions_dir.$photoData['photo_filename'])) {
        unlink($submissions_dir.$photoData['photo_filename']);
    }
    if (!empty($photoData['photo_thumb1']) && file_exists($submissions_dir_t.$photoData['photo_thumb1'])) {
        unlink($submissions_dir_t.$photoData['photo_thumb1']);
    }
    if (!empty($photoData['photo_thumb2']) && file_exists($submissions_dir_t.$photoData['photo_thumb2'])) {
        unlink($submissions_dir_t.$photoData['photo_thumb2']);
    }
}

/**
 * Displays the Album Image
 *
 * @param $album_image
 * @param $album_thumb1
 * @param $album_thumb2
 * @param $link
 *
 * @return string
 */
function displayAlbumImage($album_image, $album_thumb1, $album_thumb2, $link) {
    global $gll_settings;
    // Thumb will have 2 possible path following v7
    if (!empty($album_thumb1) && (file_exists(IMAGES_G_T.$album_thumb1) || file_exists(IMAGES_G.$album_thumb1))) {
        if (file_exists(IMAGES_G.$album_thumb1)) {
            // uncommon first
            $image = thumbnail(IMAGES_G.$album_thumb1, $gll_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix center-xy");
        } else {
            // sure fire if image is usually more than thumb threshold
            $image = thumbnail(IMAGES_G_T.$album_thumb1, $gll_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix center-xy");
        }

        return $image;
    }
    if (!empty($album_thumb2) && file_exists(IMAGES_G.$album_thumb2)) {
        return thumbnail(IMAGES_G.$album_thumb2, $gll_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix center-xy");
    }
    if (!empty($album_image) && file_exists(IMAGES_G.$album_image)) {
        return thumbnail(IMAGES_G.$album_image, $gll_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix center-xy");
    }

    return thumbnail(IMAGES_G."album_default.jpg", $gll_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix");
}

/**
 * Displays Album Thumb with Colorbox
 *
 * @param $photo_filename
 * @param $photo_thumb1
 * @param $photo_thumb2
 * @param $link
 *
 * @return string
 */
function displayPhotoImage($photo_filename, $photo_thumb1, $photo_thumb2, $link) {
    global $gll_settings;
    // Thumb will have 2 possible path following v7
    if (!empty($photo_thumb1) && (file_exists(IMAGES_G_T.$photo_thumb1) || file_exists(IMAGES_G.$photo_thumb1))) {
        if (file_exists(IMAGES_G.$photo_thumb1)) {
            // uncommon first
            return thumbnail(IMAGES_G.$photo_thumb1, $gll_settings['thumb_w']."px", $link, TRUE, FALSE, "cropfix");
        } else {
            // sure fire if image is usually more than thumb threshold
            return thumbnail(IMAGES_G_T.$photo_thumb1, $gll_settings['thumb_w']."px", $link, TRUE, FALSE, "cropfix");
        }
    }
    if (!empty($photo_thumb2) && file_exists(IMAGES_G.$photo_thumb2)) {
        return thumbnail(IMAGES_G.$photo_thumb2, $gll_settings['thumb_w']."px", $link, TRUE, FALSE, "cropfix");
    }
    if (!empty($photo_filename) && file_exists(IMAGES_G.$photo_filename)) {
        return thumbnail(IMAGES_G.$photo_filename, $gll_settings['thumb_w']."px", $link, TRUE, FALSE, "cropfix");
    }

    return thumbnail(IMAGES_G."album_default.jpg", $gll_settings['thumb_w']."px", "", FALSE, FALSE, "cropfix");
}
