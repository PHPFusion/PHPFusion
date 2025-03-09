<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: gallery_admin.php
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
require_once __DIR__.'/../../maincore.php';
pageaccess("PH");
require_once THEMES.'templates/admin_header.php';
$aidlink = fusion_get_aidlink();
$locale = fusion_get_locale('', [GALLERY_ADMIN_LOCALE, LOCALE.LOCALESET."admin/settings.php"]);
require_once INCLUDES."photo_functions_include.php";
include INFUSIONS."gallery/functions.php";
require_once INCLUDES."infusions_include.php";
add_breadcrumb(['link' => INFUSIONS."gallery/gallery_admin.php".fusion_get_aidlink(), 'title' => $locale['gallery_0001']]);
add_to_title($locale['gallery_0001']);
$gll_settings = get_settings("gallery");

add_to_css("
.panel-default > .panel-image-wrapper {
    height: 120px;
    max-height: 120px;
    min-width: 100%;
    overflow: hidden;
}
.panel-default > .panel-image-wrapper img {
    margin-top: inherit !important;
    margin-left: inherit !important;
}
.panel-default > .panel-image-wrapper .thumb > a > img {
    display: block;
    width: 100%;
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
");
$allowed_pages = ['album_form', 'photo_form', 'settings', 'submissions', 'actions'];
if (isset($_GET['section']) && $_GET['section'] == "back") {
    redirect(clean_request("", ["ref", "section", "photo_id", "action", "cat_id", "submit_id"], FALSE));
}

$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : 'gallery';
$_GET['album'] = 0;
if (isset($_GET['section'])) {
    switch ($_GET['section']) {
        case "photo_form":
            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $locale['gallery_0002']]);
            break;
        case "album_form":
            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $locale['gallery_0004']]);
            break;
        case "settings":
            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $locale['gallery_0006']]);
            break;
        case "submissions":
            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $locale['gallery_0007']]);
            break;
        default:
            break;
    }
}
$album_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['cat_id']) && isnum($_GET['cat_id']);
$photo_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['photo_id']) && isnum($_GET['photo_id']);

if (!empty($_GET['ref']) || isset($_GET['photo_id']) || isset($_GET['cat_id'])) {
    $tab['title'][] = $locale['back'];
    $tab['id'][] = "back";
    $tab['icon'][] = "fa fa-fw fa-arrow-left";
}
$tab['title'][] = $locale['gallery_0001'];
$tab['id'][] = "gallery";
$tab['icon'][] = "fa fa-camera-retro";
$tab['title'][] = $photo_edit ? $locale['gallery_0003'] : $locale['gallery_0002'];
$tab['id'][] = "photo_form";
$tab['icon'][] = "fa fa-picture-o";
$tab['title'][] = $album_edit ? $locale['gallery_0005'] : $locale['gallery_0004'];
$tab['id'][] = "album_form";
$tab['icon'][] = "fa fa-plus";
$tab['title'][] = $locale['gallery_0007']."&nbsp;<span class='badge'>".dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='p'")."</span>";
$tab['id'][] = "submissions";
$tab['icon'][] = "fa fa-inbox";
$tab['title'][] = $locale['gallery_0006'];
$tab['id'][] = "settings";
$tab['icon'][] = "fa fa-cogs";

// Need to put breadcrumb here before opentable, or else it won't cache in Artemis.
if (isset($_GET['album_id']) && isnum($_GET['album_id'])) {
    $sql = "SELECT album_title FROM ".DB_PHOTO_ALBUMS." WHERE album_id=:id";
    $param = [':id' => $_GET['album_id']];
    add_breadcrumb([
        'link'  => clean_request("album_id=".$_GET['album_id'], ["aid"], FALSE),
        "title" => dbresult(dbquery($sql, $param), 0)
    ]);
}

opentable($locale['gallery_0001']);
echo opentab($tab, $_GET['section'], "gallery_admin", TRUE, "nav-tabs", 'section', ['album_id']);
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
require_once THEMES.'templates/footer.php';
/**
 * Gallery Photo Listing UI
 *
 * @param $id
 * @param $type
 *
 * @return int
 */
function gallery_rating_vote($id, $type) {
    $count_db = dbarray(dbquery("SELECT
                IF(SUM(rating_vote)>0, SUM(rating_vote), 0) AS total_votes
                FROM ".DB_RATINGS."
                WHERE rating_item_id=:ratingid AND rating_type=:ratingtype", [':ratingid' => $id, ':ratingtype' => $type]
    ));
    return $count_db['total_votes'];
}

function gallery_rating_count($id, $type) {
    $count_db = dbarray(dbquery("SELECT
                COUNT(rating_id) AS rating_count
                FROM ".DB_RATINGS."
                WHERE rating_item_id=:ratingid AND rating_type=:ratingtype", [':ratingid' => $id, ':ratingtype' => $type]
    ));
    return $count_db['rating_count'];
}

function gallery_photo_listing() {
    $locale = fusion_get_locale();
    $gll_settings = get_settings('gallery');
    $aidlink = fusion_get_aidlink();

    // xss
    $photoRows = dbcount("(photo_id)", DB_PHOTOS, "album_id=:albumid", [':albumid' => intval($_GET['album_id'])]);
    $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $photoRows ? $_GET['rowstart'] : 0;
    if (!empty($photoRows)) {
        $result = dbquery("SELECT photos.*, album.*,
        photos.photo_user as user_id, u.user_name, u.user_status, u.user_avatar,
        COUNT(comment_id) as comment_count
        FROM ".DB_PHOTOS." photos
        INNER JOIN ".DB_PHOTO_ALBUMS." album on photos.album_id = album.album_id
        INNER JOIN ".DB_USERS." u on u.user_id = photos.photo_user
        LEFT JOIN ".DB_COMMENTS." comment on comment.comment_item_id= photos.photo_id AND comment_type = 'P'
        WHERE ".groupaccess('album.album_access')." AND photos.album_id = '".intval($_GET['album_id'])."'
        GROUP BY photo_id
        ORDER BY photos.photo_order ASC, photos.photo_datestamp DESC LIMIT ".intval($_GET['rowstart']).", ".intval($gll_settings['gallery_pagination'])."
        ");
        $rows = dbrows($result);
        // Photo Album header

        $album_data = dbarray(dbquery("SELECT album_id, album_title, album_description, album_datestamp, album_access FROM ".DB_PHOTO_ALBUMS." WHERE album_id=:albumid", [':albumid' => intval($_GET['album_id'])]));

        echo "<h2><strong>\n".$album_data['album_title']."</strong></h2>\n";

        echo "<strong>".$locale['album_0003']."</strong> ".parse_text($album_data['album_description'], ['parse_smileys' => FALSE, 'decode' => FALSE]);

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
            while ($data = dbarray($result)) {
                $rcount = gallery_rating_count($data['photo_id'], 'P');
                $vcount = gallery_rating_vote($data['photo_id'], 'P');
                echo "<div class='col-xs-12' style='float:left; width:20%; padding:0 15px;'>\n";
                // <!-------panel------>
                echo "<div class='panel-default m-b-20'>\n";
                echo "<div class='panel-image-wrapper'>\n";
                echo display_photo_image($data['photo_filename'], $data['photo_thumb1'], $data['photo_thumb2'], '', $data['album_id']);
                echo "</div>\n";
                echo "<div class='panel-body p-0'>\n";
                echo "<div class='dropdown pull-right spacer-xs'>\n";
                echo "<button id='ddp".$data['photo_id']."' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' class='btn btn-default dropdown-toggle' type='button'> ".$locale['gallery_0013']." <span class='caret'></span></button>\n";
                echo "<ul class='dropdown-menu' aria-labelledby='ddp".$data['photo_id']."'>\n";
                echo "<li><a href='".FUSION_SELF.$aidlink."&amp;section=photo_form&amp;action=edit&amp;photo_id=".$data['photo_id']."'><i class='fa fa-edit fa-fw'></i> ".$locale['gallery_0003']."</a></li>\n";
                echo ($i > 1) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=pu&amp;photo_id=".$data['photo_id']."&amp;album_id=".$data['album_id']."&amp;order=".($data['photo_order'] - 1)."'><i class='fa fa-arrow-left fa-fw'></i> ".$locale['gallery_0014']."</a></li>\n" : "";
                echo ($i !== $rows) ? "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=pd&amp;photo_id=".$data['photo_id']."&amp;album_id=".$data['album_id']."&amp;order=".($data['photo_order'] + 1)."'><i class='fa fa-arrow-right fa-fw'></i> ".$locale['gallery_0015']."</a></li>\n" : "";
                echo "<li class='divider'></li>\n";
                echo "<li><a href='".FUSION_SELF.$aidlink."&amp;section=actions&amp;action=delete&amp;photo_id=".$data['photo_id']."'><i class='fa fa-trash fa-fw'></i> ".$locale['gallery_0017']."</a></li>\n";
                echo "</ul>\n";
                echo "</div>\n";
                echo "<div class='clearfix'>\n";
                echo "<h4 class='album_title'><strong>".$data['photo_title']."</strong>\n</h4>\n";
                echo "</div>\n";
                echo "<div class='display-block'>\n";
                echo "<span class='m-r-10'>\n<i class='fa fa-comments-o' title='".$locale['comments']."'></i> ".$data['comment_count']."</span>\n";
                echo "<span class='m-r-5'>\n<i class='fa fa-star' title='".$locale['ratings']."'></i> ".number_format(($rcount > 0 ? $rcount / $vcount * 10 : 0), 2)." /10</span>\n";
                echo "</div>\n";
                echo "</div>\n";
                echo "</div>\n";
                // <!-------panel------>
                echo "</div>\n";
                $i++;
            }
            echo "</div>\n";
        } else {
            redirect(INFUSIONS.'gallery/gallery_admin.php'.$aidlink);
        }
    } else {
        redirect(INFUSIONS.'gallery/gallery_admin.php'.$aidlink);
    }
}


function gallery_album_listing() {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $gll_settings = get_settings('gallery');

    // xss
    $albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? in_group('album_language', LANGUAGE) : "");
    $photoRows = dbcount("(photo_id)", DB_PHOTOS);
    $update = dbarray(dbquery("SELECT MAX(photo_datestamp) 'last_updated' FROM ".DB_PHOTOS.""));
    $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $albumRows ? $_GET['rowstart'] : 0;
    if (!empty($albumRows)) {
        // get albums.
        $gallery_sql = "SELECT album.album_id, album.album_title, album.album_image, album.album_thumb2, album.album_thumb1, album.album_order, album.album_user as user_id,
        u.user_name, u.user_status, u.user_avatar,
        COUNT(photo.photo_id) AS photo_count
        FROM ".DB_PHOTO_ALBUMS." album
        LEFT JOIN ".DB_PHOTOS." photo on photo.album_id=album.album_id
        INNER JOIN ".DB_USERS." u on u.user_id=album.album_user
        ".(multilang_table("PG") ? "WHERE ".in_group('album.album_language', LANGUAGE)." AND " : "WHERE ").groupaccess('album.album_access')."
        GROUP BY album.album_id
        ORDER BY album.album_order DESC, album.album_datestamp DESC LIMIT ".intval($_GET['rowstart']).", ".$gll_settings['gallery_pagination'];
        $result = dbquery($gallery_sql);
        $rows = dbrows($result);
        // Photo Album header
        echo "<div class='clearfix'>\n";
        echo "<h2><strong>\n".$locale['gallery_0022']."</strong></h2>\n";
        echo "<div class='pull-right text-right hidden-xs'>".sprintf($locale['gallery_0018'], $rows, $albumRows)."</div>\n";
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
                    echo display_album_image($data['album_image'], $data['album_thumb1'], $data['album_thumb2'], $link, $data['album_id']);
                } else {
                    echo display_album_image($data['album_image'], $data['album_thumb1'], $data['album_thumb2'], "", $data['album_id']);
                }
                echo "</div>\n";
                echo "<div class='panel-body p-0'>\n";
                echo "<div class='dropdown pull-right spacer-xs'>\n";
                echo "<button id='dda".$data['album_id']."' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' class='btn btn-default dropdown-toggle' type='button'> ".$locale['album_0020']." <span class='caret'></span></button>\n";
                echo "<ul class='dropdown-menu' aria-labelledby='dda".$data['album_id']."'>\n";
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
            echo "<div class='well text-center'>\n";
            echo $locale['gallery_0024'];
            echo "</div>\n";
        }
    } else {
        echo "<div class='well text-center'>\n";
        echo $locale['gallery_0024'];
        echo "</div>\n";
    }
}

/**
 * Get all the album listing.
 *
 * @return array
 */
function get_album_opts() {
    $list = [];
    $result = dbquery("SELECT * FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "where ".in_group('album_language', LANGUAGE) : "")." ORDER BY album_order DESC, album_datestamp DESC");
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
function purge_album_image($albumData) {
    if (!empty($albumData['album_image']) && file_exists(IMAGES_G.$albumData['album_image'])) {
        unlink(IMAGES_G.$albumData['album_image']);
    }
    sleep(0.3);
    if (!empty($albumData['album_thumb1']) && file_exists(IMAGES_G_T.$albumData['album_thumb1'])) {
        unlink(IMAGES_G_T.$albumData['album_thumb1']);
    }
    sleep(0.3);
    if (!empty($albumData['album_thumb2']) && file_exists(IMAGES_G_T.$albumData['album_thumb2'])) {
        unlink(IMAGES_G_T.$albumData['album_thumb2']);
    }
}

/**
 * Purge all photo images
 *
 * @param $photoData
 */
function purge_photo_image($photoData) {
    $photo_path = return_photo_paths($photoData);
    $parts = pathinfo($photo_path['photo_filename']);

    if (!empty($parts['extension'])) {
        // photos with watermark

        $wm_file1 = $parts['filename']."_w1.".$parts['extension'];
        $wm_file2 = $parts['filename']."_w2.".$parts['extension'];

        if (file_exists(IMAGES_G.'album_'.$photoData['album_id'].'/'.$wm_file1)) {
            unlink(IMAGES_G.'album_'.$photoData['album_id'].'/'.$wm_file1);
        } else if (file_exists(file_exists(IMAGES_G.$wm_file1))) {
            unlink(file_exists(IMAGES_G.$wm_file1));
        }

        sleep(0.3);

        if (file_exists(IMAGES_G.'album_'.$photoData['album_id'].'/'.$wm_file2)) {
            unlink(IMAGES_G.'album_'.$photoData['album_id'].'/'.$wm_file2);
        } else if (file_exists(file_exists(IMAGES_G.$wm_file2))) {
            unlink(file_exists(IMAGES_G.$wm_file2));
        }
    }

    sleep(0.3);

    if (!empty($photoData['photo_filename'])) {
        if (file_exists(IMAGES_G.$photoData['photo_filename'])) {
            unlink(IMAGES_G.$photoData['photo_filename']);
        } else if (file_exists(IMAGES_G.'album_'.$photoData['album_id'].'/'.$photoData['photo_filename'])) {
            unlink(IMAGES_G.'album_'.$photoData['album_id'].'/'.$photoData['photo_filename']);
        }
    }

    sleep(0.3);

    if (!empty($photoData['photo_thumb1'])) {
        if (file_exists(IMAGES_G_T.$photoData['photo_thumb1'])) {
            unlink(IMAGES_G_T.$photoData['photo_thumb1']);
        } else if (file_exists(IMAGES_G.'album_'.$photoData['album_id'].'/thumbs/'.$photoData['photo_thumb1'])) {
            unlink(IMAGES_G.'album_'.$photoData['album_id'].'/thumbs/'.$photoData['photo_thumb1']);
        }
    }

    sleep(0.3);

    if (!empty($photoData['photo_thumb2'])) {
        if (file_exists(IMAGES_G_T.$photoData['photo_thumb2'])) {
            unlink(IMAGES_G_T.$photoData['photo_thumb2']);
        } else if (file_exists(IMAGES_G.'album_'.$photoData['album_id'].'/thumbs/'.$photoData['photo_thumb2'])) {
            unlink(IMAGES_G.'album_'.$photoData['album_id'].'/thumbs/'.$photoData['photo_thumb2']);
        }
    }
}

/**
 * Purge all submissions photo images
 *
 * @param $photoData
 */
function purge_submissions_photo_image($photoData) {
    $submissions_dir = INFUSIONS."gallery/submissions/";
    $submissions_dir_t = INFUSIONS."gallery/submissions/thumbs/";
    if (!empty($photoData['photo_filename']) && file_exists($submissions_dir.$photoData['photo_filename'])) {
        unlink($submissions_dir.$photoData['photo_filename']);
    }

    sleep(0.3);
    if (!empty($photoData['photo_thumb1']) && file_exists($submissions_dir_t.$photoData['photo_thumb1'])) {
        unlink($submissions_dir_t.$photoData['photo_thumb1']);
    }

    sleep(0.3);
    if (!empty($photoData['photo_thumb2']) && file_exists($submissions_dir_t.$photoData['photo_thumb2'])) {
        unlink($submissions_dir_t.$photoData['photo_thumb2']);
    }
}
