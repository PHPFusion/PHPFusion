<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery.php
| Author: PHP-Fusion Development Team
| Co-Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__).'/../../maincore.php';
if (!infusion_exists('gallery')) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES."templates/header.php";
$locale = fusion_get_locale('', GALLERY_LOCALE);
include INFUSIONS."gallery/templates/gallery.php";
require_once INCLUDES."infusions_include.php";

$gallery_settings = get_settings("gallery");
if (!defined('SAFEMODE')) {
    define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
}

/* View Photo */
if (isset($_GET['photo_id']) && isnum($_GET['photo_id'])) {
    include INCLUDES."comments_include.php";
    include INCLUDES."ratings_include.php";
    add_to_jquery("$('a.photogallery_photo_link').colorbox({width:'80%', height:'80%', photo:true});");

    $result = dbquery("SELECT tp.*, ta.album_id, ta.album_title, ta.album_access, ta.album_keywords,
		tu.user_id, tu.user_name, tu.user_status,
		SUM(tr.rating_vote) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes,
		count(tc.comment_id) AS comment_count
		FROM ".DB_PHOTOS." tp
		LEFT JOIN ".DB_PHOTO_ALBUMS." ta USING (album_id)
		LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
		LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tp.photo_id AND tr.rating_type='P'
		LEFT JOIN ".DB_COMMENTS." tc ON tc.comment_item_id=tp.photo_id AND comment_type='P'
		WHERE ".groupaccess('album_access')." AND photo_id='".intval($_GET['photo_id'])."' GROUP BY tp.photo_id");
    $info = array();
    if (dbrows($result) > 0) {
        $data = dbarray($result);
        /* Declaration */
        $result = dbquery("UPDATE ".DB_PHOTOS." SET photo_views=(photo_views+1) WHERE photo_id='".$_GET['photo_id']."'");
        $pres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='".($data['photo_order'] - 1)."' AND album_id='".$data['album_id']."'");
        $nres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='".($data['photo_order'] + 1)."' AND album_id='".$data['album_id']."'");
        $fres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='1' AND album_id='".$data['album_id']."'");
        $lastres = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".$data['album_id']."'"), 0);
        $lres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order>='".$lastres."' AND album_id='".$data['album_id']."'");
        if (dbrows($pres)) {
            $prev = dbarray($pres);
        }
        if (dbrows($nres)) {
            $next = dbarray($nres);
        }
        if (dbrows($fres)) {
            $first = dbarray($fres);
        }
        if (dbrows($lres)) {
            $last = dbarray($lres);
        }

        add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");

        set_title($locale['465']);
        add_to_title($locale['global_201'].$data['photo_title']);
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS."gallery/gallery.php",
            'title' => $locale['465']
        ]);
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS."gallery/gallery.php?album_id=".$data['album_id'],
            'title' => $data['album_title']
        ]);

        if ($data['album_keywords'] !== "") {
            set_meta("keywords", $data['album_keywords']);
            if ($data['photo_keywords'] !== "") {
                add_to_meta("keywords", $data['photo_keywords']);
            }
        } else {
            if ($data['photo_keywords'] !== "") {
                set_meta("keywords", $data['photo_keywords']);
            }
        }

        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id'],
            'title' => $data['photo_title']
        ]);
        // broken watermaking. how to do this?
        if ($gallery_settings['photo_watermark']) {
            // how does watermarking do?
            if ($gallery_settings['photo_watermark_save']) {
                $parts = explode(".", $data['photo_filename']);
                $wm_file1 = $parts[0]."_w1.".$parts[1];
                $wm_file2 = $parts[0]."_w2.".$parts[1];
                if (!file_exists(IMAGES_G_T.$wm_file1)) {
                    if ($data['photo_thumb2']) {
                        $info['photo_thumb'] = INFUSIONS."gallery/photo.php?photo_id=".$_GET['photo_id'];
                    }
                    $info['photo_filename'] = INFUSIONS."gallery/photo.php?photo_id=".$_GET['photo_id']."&amp;full";
                } else {
                    if ($data['photo_thumb2']) {
                        $info['photo_thumb'] = IMAGES_G."/".$wm_file1;
                    }
                    $info['photo_filename'] = IMAGES_G."/".$wm_file2;
                }
            } else {
                if ($data['photo_thumb2']) {
                    $info['photo_thumb'] = INFUSIONS."gallery/photo.php?photo_id=".$_GET['photo_id'];
                }
                $info['photo_filename'] = INFUSIONS."gallery/photo.php?photo_id=".$_GET['photo_id']."&amp;full";
            }
            $info['photo_size'] = @getimagesize(IMAGES_G.$data['photo_filename']);
        } else {
            $info += array(
                "photo_thumb2"   => $data['photo_thumb2'] ? IMAGES_G_T.$data['photo_thumb2'] : "",
                "photo_thumb1"   => $data['photo_thumb1'] ? IMAGES_G_T.$data['photo_thumb1'] : "",
                "photo_filename" => IMAGES_G.$data['photo_filename'],
                "photo_size"     => getimagesize(IMAGES_G.$data['photo_filename'])
            );
        }
        $info += array(
            "photo_description" => $data['photo_description'] ? nl2br(parse_textarea($data['photo_description'], FALSE, FALSE, TRUE, FALSE)) : '',
            "photo_byte"        => parsebytesize($gallery_settings['photo_watermark'] ? filesize(IMAGES_G.$data['photo_filename']) : filesize(IMAGES_G.$data['photo_filename'])),
            "photo_comment"     => $data['photo_allow_comments'] ? number_format($data['comment_count']) : 0,
            "photo_ratings"     => $data['photo_allow_ratings'] && $data['count_votes'] > 0 ? number_format(ceil($data['sum_rating'] / $data['count_votes'])) : '0',
        );

        if ((isset($prev['photo_id']) && isnum($prev['photo_id'])) || (isset($next['photo_id']) && isnum($next['photo_id']))) {
            if (isset($prev) && isset($first)) {
                $info['nav']['first'] = array(
                    'link' => INFUSIONS."gallery/gallery.php?photo_id=".$first['photo_id'],
                    'name' => $locale['459']
                );
            }
            if (isset($prev)) {
                $info['nav']['prev'] = array(
                    'link' => INFUSIONS."gallery/gallery.php?photo_id=".$prev['photo_id'],
                    'name' => $locale['451']
                );
            }
            if (isset($next)) {
                $info['nav']['next'] = array(
                    'link' => INFUSIONS."gallery/gallery.php?photo_id=".$next['photo_id'],
                    'name' => $locale['452']
                );
            }
            if (isset($next) && isset($last)) {
                $info['nav']['last'] = array(
                    'link' => INFUSIONS."gallery/gallery.php?photo_id=".$last['photo_id'],
                    'name' => $locale['460']
                );
            }
        }

        $data['photo_show_comments'] = get_photo_comments($data);
        $data['photo_show_ratings'] = get_photo_ratings($data);

        $info += $data;
        render_photo($info);
    } else {
        redirect(INFUSIONS.'gallery/gallery.php');
    }
} else {

    if (isset($_GET['album_id']) && isnum($_GET['album_id'])) {

        /* View Album */
        $result = dbquery("SELECT album_title, album_description, album_keywords, album_image, album_thumb1, album_thumb2, album_access
        FROM ".DB_PHOTO_ALBUMS." WHERE ".groupaccess('album_access')." AND album_id='".intval($_GET['album_id'])."'
        ");
        if (dbrows($result) > 0) {
            $info = dbarray($result);

            set_title($locale['465']);
            add_to_title($locale['global_201'].$info['album_title']);

            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'gallery/gallery.php',
                'title' => \PHPFusion\SiteLinks::get_current_SiteLinks("infusions/gallery/gallery.php", "link_name")
            ]);

            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'gallery/gallery.php?album_id='.$_GET['album_id'],
                'title' => $info['album_title']
            ]);
            if ($info['album_keywords'] !== "") {
                add_to_meta("keywords", $info['album_keywords']);
            }
            /* Category Info */
            $info['album_thumb'] = displayAlbumImage($info['album_image'], $info['album_thumb2'], $info['album_thumb1'], "");
            $info['album_link'] = array(
                'link' => INFUSIONS.'gallery/gallery.php?album_id='.$_GET['album_id'],
                'name' => $info['album_title']
            );
            $info['max_rows'] = dbcount("(photo_id)", DB_PHOTOS, "album_id='".$_GET['album_id']."'");
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['max_rows'] ? $_GET['rowstart'] : 0;
            if ($info['max_rows'] > 0) {
                // Album stats
                $latest_update = dbarray(dbquery("
					SELECT tp.photo_datestamp, tu.user_id, tu.user_name, tu.user_status
					FROM ".DB_PHOTOS." tp
					LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
					WHERE album_id='".intval($_GET['album_id'])."'
					ORDER BY photo_datestamp DESC LIMIT 1"));
                $info['album_stats'] = $locale['422']." ".$info['max_rows']."<br />\n";
                $info['album_stats'] .= $locale['423']." ".profile_link($latest_update['user_id'], $latest_update['user_name'], $latest_update['user_status'])." ".$locale['424']." ".showdate("longdate", $latest_update['photo_datestamp'])."\n";
                $result = dbquery("SELECT tp.*,
					tu.user_id, tu.user_name, tu.user_status, tu.user_avatar,
					SUM(tr.rating_vote) AS 'sum_rating',
					COUNT(tr.rating_vote) AS 'count_rating'
					FROM ".DB_PHOTOS." tp
					LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
					LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tp.photo_id AND tr.rating_type='P'
					WHERE album_id='".intval($_GET['album_id'])."'
					GROUP BY photo_id ORDER BY photo_order
					limit ".intval($_GET['rowstart']).",".intval($gallery_settings['gallery_pagination']));
                $info['photo_rows'] = dbrows($result);
                $info['page_nav'] = $info['max_rows'] > $gallery_settings['gallery_pagination'] ? makepagenav($_GET['rowstart'],
                    $gallery_settings['gallery_pagination'],
                    $info['max_rows'], 3,
                    INFUSIONS."gallery/gallery.php?album_id=".$_GET['album_id']."&amp;") : '';
                if ($info['photo_rows'] > 0) {
                    // this is photo
                    while ($data = dbarray($result)) {
                        // data manipulation
                        $data += array(
                            "photo_link"  => array(
                                'link' => INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id'],
                                'name' => $data['photo_title']
                            ),
                            "image"       => displayPhotoImage($data['photo_id'], $data['photo_filename'], $data['photo_thumb1'], $data['photo_thumb2'], INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id']),
                            "title"       => ($data['photo_title']) ? $data['photo_title'] : $data['image'],
                            "description" => ($data['photo_description']) ? nl2br(parse_textarea($data['photo_description'])) : '',
                            "photo_views" => format_word($data['photo_views'], $locale['fmt_views']),
                        );
                        if (iADMIN && checkrights("PH")) {
                            global $aidlink;
                            $data['photo_edit'] = array(
                                "link" => INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=photo_form&amp;action=edit&amp;photo_id=".$data['photo_id'],
                                "name" => $locale['edit']
                            );
                            $data['photo_delete'] = array(
                                "link" => INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=actions&amp;action=delete&amp;photo_id=".$data['photo_id'],
                                "name" => $locale['delete']
                            );
                        }
                        if ($data['photo_allow_comments']) {
                        	$count_v = sum_db($data['photo_id'], 'P');
                        	$count_c = count_db($data['photo_id'], 'P');
                            $data += array(
                                "photo_votes"    => $count_v > 0 ? $count_v : '0',
                                "photo_comments" => array(
                                    'link' => $data['photo_link']['link'].'#comments',
                                    'name' => $count_c,
                                    'word' => format_word($count_c, $locale['fmt_comment'])
                                )
                            );
                        }
                        if ($data['photo_allow_ratings']) {
                            $data += array(
                                "sum_rating"    => $data['sum_rating'] > 0 ? $data['sum_rating'] : '0',
                                "photo_ratings" => array(
                                    'link' => $data['photo_link']['link'].'#ratings',
                                    'name' => $data['sum_rating'],
                                    'word' => ($data['sum_rating'] > 0) ? ($data['sum_rating'] / $data['count_rating'] * 10)."/10" : "0/10",
                                )
                            );
                        }

                        $info['item'][] = $data;
                    }
                }
            }
            render_photo_album($info);
        } else {
            redirect(INFUSIONS.'gallery/gallery.php');
        }

    } else {

        /* Main Index */
        set_title(\PHPFusion\SiteLinks::get_current_SiteLinks('infusions/gallery/gallery.php', "link_name"));

        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'gallery/gallery.php',
            'title' => \PHPFusion\SiteLinks::get_current_SiteLinks(INFUSIONS.'gallery/gallery.php', "link_name")
        ]);

        $info['max_rows'] = dbcount("(album_id)", DB_PHOTO_ALBUMS, groupaccess('album_access'));
        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['max_rows'] ? $_GET['rowstart'] : 0;
        if ($info['max_rows'] > 0) {
            $info['page_nav'] = ($info['max_rows'] > $gallery_settings['gallery_pagination']) ? makepagenav($_GET['rowstart'],
                $gallery_settings['gallery_pagination'],
                $info['max_rows'], 3) : '';
            $result = dbquery("SELECT ta.album_id, ta.album_title, ta.album_description, ta.album_image, ta.album_thumb1, ta.album_thumb2, ta.album_datestamp,
			tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_PHOTO_ALBUMS." ta
			LEFT JOIN ".DB_USERS." tu ON ta.album_user=tu.user_id
			".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")."
			".groupaccess('album_access')." ORDER BY album_order
			LIMIT ".$_GET['rowstart'].", ".$gallery_settings['gallery_pagination']);
            while ($data = dbarray($result)) {
                $data['album_link'] = array(
                    "link" => INFUSIONS."gallery/gallery.php?album_id=".$data['album_id'],
                    "name" => $data['album_title']
                );
                if (iADMIN && checkrights("PH")) {
                    global $aidlink;
                    $data['album_edit'] = array(
                        "link" => INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=album_form&amp;action=edit&amp;cat_id=".$data['album_id'],
                        "name" => $locale['edit']
                    );
                    $data['album_delete'] = array(
                        "link" => INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=actions&amp;action=delete&amp;cat_id=".$data['album_id'],
                        "name" => $locale['delete']
                    );
                }
                $photo_directory = !SAFEMODE ? "album_".$data['album_id'] : '';

                // if ($data['album_image']) {
                $data['image'] = displayAlbumImage($data['album_image'], $data['album_thumb1'], $data['album_thumb2'],
                    INFUSIONS."gallery/gallery.php?album_id=".$data['album_id']);
                //}
                $data['title'] = $data['album_title'] ? $data['album_title'] : $locale['402'];
                $data['description'] = $data['album_description'] ? nl2br(parse_textarea($data['album_description'])) : '';
                $_photo = dbquery("SELECT pp.photo_user, u.user_id, u.user_name, u.user_status, u.user_avatar
			FROM ".DB_PHOTOS." pp
			LEFT JOIN ".DB_USERS." u on u.user_id=pp.photo_user
			WHERE album_id='".intval($data['album_id'])."'
			ORDER BY photo_datestamp
			");
                $data['photo_rows'] = dbrows($_photo);
                $user = array();
                if ($data['photo_rows'] > 0) {
                    while ($_photo_data = dbarray($_photo)) {
                        $user[$_photo_data['user_id']] = $_photo_data;
                    } // distinct value.
                }
                $data['photo_user'] = $user;
                $info['item'][] = $data;
            }
        }
        render_gallery($info);
    }
}
function sum_db($id, $type) {
            $count_db = dbarray(dbquery("SELECT
				COUNT(rating_item_id) AS count_votes
				FROM ".DB_RATINGS."
				WHERE rating_item_id='".$id."' AND rating_type='".$type."'
             "));
return $count_db['count_votes'];
}
function count_db($id, $type) {
            $count_db = dbarray(dbquery("SELECT
				COUNT(comment_item_id) AS count_comment
				FROM ".DB_COMMENTS."
				WHERE comment_item_id='".$id."' AND comment_type='".$type."' AND comment_hidden='0'
             "));
return $count_db['count_comment'];
}
function photo_thumbnail($data) {
    $locale = fusion_get_locale();
    echo "<div class='panel panel-default tbl-border'>\n";
    echo "<div class='p-0'>\n";
    echo "<!--photogallery_album_photo_".$data['photo_id']."-->";
    echo "<a href='".INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id']."' class='photogallery_album_photo_link'>\n";
    $thumb_img = ($data['photo_thumb1'] && file_exists(IMAGES_G.$data['photo_thumb1'])) ? IMAGES_G.$data['photo_thumb1'] : DOWNLOADS."images/no_image.jpg";
    $title = ($data['album_thumb1'] && file_exists(PHOTOS.$data['album_thumb1'])) ? $data['album_thumb1'] : $locale['402'];
    echo "<img class='photogallery_album_photo img-responsive' style='min-width: 100%;' src='".$thumb_img."' title='$title' alt='$title' />\n";
    echo "</a>\n";
    echo "</div>\n<div class='panel-body photogallery_album_photo_info'>\n";
    echo "<a href='".INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id']."' class='photogallery_album_photo_link'><strong>".$data['photo_title']."</strong></a>\n";
    echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
    echo "<!--photogallery_album_photo_info-->\n";
    echo "<span class='display-inline-block'>\n";
    echo($data['photo_allow_ratings'] ? $locale['437'].($data['count_votes'] > 0 ? str_repeat("<i class='fa fa-star'></i>", ceil($data['sum_rating'] / $data['count_votes'])) : $locale['438'])."<br />\n" : "");
    echo "</span>\n<br/>\n";
    echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
    echo "<span> ".$locale['434'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])." </span>";
    echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
    echo "<span class='m-r-10'><abbr title='".$locale['403'].showdate("shortdate", $data['photo_datestamp'])."'><i title='".$locale['403'].showdate("shortdate", $data['photo_datestamp'])."' class='fa fa-calendar text-lighter'></i></abbr></span>";
    $photo_comments = dbcount("(comment_id)", DB_COMMENTS, "comment_type='P' AND comment_item_id='".$data['photo_id']."'");
    $comments_text = ($data['photo_allow_comments'] ? ($photo_comments == 1 ? $locale['436b'] : $locale['436']).$photo_comments : "");
    echo "<span class='m-r-10'><abbr title='".$comments_text."'><i class='fa fa-comment text-lighter'></i></abbr> $photo_comments</abbr></span>";
    echo "<span class='m-r-10'><abbr title='".$locale['434'].$data['user_name']."'><i class='fa fa-user text-lighter'></i></span>";
    echo "<span><abbr title='".$locale['435'].$data['photo_views']."'><i class='fa fa-eye text-lighter'></i></abbr> ".$data['photo_views']."</span>";
    echo "</div></div>\n";
}

require_once THEMES."templates/footer.php";

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
    global $gallery_settings;
    // include generation of watermark which requires photo_id. but album doesn't have id.
    // Thumb will have 2 possible path following v7
    if (!empty($album_thumb1) && (file_exists(IMAGES_G_T.$album_thumb1) || file_exists(IMAGES_G.$album_thumb1))) {
        if (file_exists(IMAGES_G.$album_thumb1)) {
            // uncommon first
            $image = thumbnail(IMAGES_G.$album_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix");
        } else {
            // sure fire if image is usually more than thumb threshold
            $image = thumbnail(IMAGES_G_T.$album_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix");
        }

        return $image;
    }
    if (!empty($album_thumb2) && file_exists(IMAGES_G.$album_thumb2)) {
        return thumbnail(IMAGES_G.$album_thumb2, $gallery_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix");
    }
    if (!empty($album_image) && file_exists(IMAGES_G.$album_image)) {
        return thumbnail(IMAGES_G.$album_image, $gallery_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix");
    }

    return thumbnail(IMAGES_G."album_default.jpg", $gallery_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix");
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
function displayPhotoImage($photo_id, $photo_filename, $photo_thumb1, $photo_thumb2, $link) {
    global $gallery_settings;
    // Remove the whole of watermarking requirements in thumbnails.
    /*
    if ($gallery_settings['photo_watermark']) {
        // need photo_id.
        if ($gallery_settings['photo_watermark_save']) {
            $parts = explode(".", $photo_filename);
            $wm_file1 = $parts[0]."_w1.".$parts[1];  // big pic
            $wm_file2 = $parts[0]."_w2.".$parts[1]; // small pic
            if (!file_exists(IMAGES_G.$wm_file1)) {
                $photo_filename = INFUSIONS."gallery/photo.php?photo_id=".$photo_id."&amp;full";
                if ($photo_thumb2) {
                    $photo_thumb1 = INFUSIONS."gallery/photo.php?photo_id=".$photo_id;
                    return  thumbnail($photo_thumb1, $gallery_settings['thumb_w']."px", $photo_filename, TRUE, FALSE, "cropfix");
                }
                return  thumbnail($photo_filename, $gallery_settings['thumb_w']."px", $photo_filename, TRUE, FALSE, "cropfix");
            } else {
                $photo_filename = IMAGES_G.$wm_file2;
                if ($photo_thumb2) {
                    $photo_thumb1 = IMAGES_G.$wm_file1;
                    return  thumbnail($photo_thumb1, $gallery_settings['thumb_w']."px", $photo_filename, TRUE, FALSE, "cropfix");
                }
                return  thumbnail($photo_filename, $gallery_settings['thumb_w']."px", $photo_filename, TRUE, FALSE, "cropfix");
            }
        } else {
            if ($photo_thumb2 && file_exists(IMAGES_G.$photo_thumb2)) {
                $photo_thumb2 = INFUSIONS."gallery/photo.php?photo_id=".$photo_id;
                return  thumbnail($photo_thumb2, $gallery_settings['thumb_w']."px", $photo_thumb2, TRUE, FALSE, "cropfix");
            }
            $photo_filename = INFUSIONS."gallery/photo.php?photo_id=".$photo_id."&amp;full";
            return  thumbnail($photo_filename, $gallery_settings['thumb_w']."px", $photo_filename, TRUE, FALSE, "cropfix");
        }
    }
    */
    // Thumb will have 2 possible path following v7
    if (!empty($photo_thumb1) && (file_exists(IMAGES_G_T.$photo_thumb1) || file_exists(IMAGES_G.$photo_thumb1))) {
        if (file_exists(IMAGES_G.$photo_thumb1)) {
            // uncommon first
            $image = thumbnail(IMAGES_G.$photo_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix");
        } else {
            // sure fire if image is usually more than thumb threshold
            $image = thumbnail(IMAGES_G_T.$photo_thumb1, $gallery_settings['thumb_w']."px", $link, FALSE, FALSE, "cropfix");
        }

        return $image;
    }

    if (!empty($photo_thumb2) && file_exists(IMAGES_G.$photo_thumb2)) {
        return thumbnail(IMAGES_G.$photo_thumb2, $gallery_settings['thumb_w']."px", $link, TRUE, FALSE, "cropfix");
    }
    if (!empty($photo_filename) && file_exists(IMAGES_G.$photo_filename)) {
        return thumbnail(IMAGES_G.$photo_filename, $gallery_settings['thumb_w']."px", $link, TRUE, FALSE, "cropfix");
    }

    return thumbnail(IMAGES_G."album_default.jpg", $gallery_settings['thumb_w']."px", "", FALSE, FALSE, "cropfix");
}

function get_photo_comments($data) {
    $html = "";
    if (fusion_get_settings('comments_enabled') && $data['photo_allow_comments']) {
        ob_start();
        showcomments("P", DB_PHOTOS, "photo_id", $data['photo_id'], BASEDIR."infusions/gallery/gallery.php?photo_id=".$data['photo_id'], FALSE);
        $html = ob_get_contents();
        ob_end_clean();
    }

    return (string)$html;
}

function get_photo_ratings($data) {
    $html = "";
    if (fusion_get_settings('ratings_enabled') && $data['photo_allow_ratings']) {
        ob_start();
        showratings("P", $data['photo_id'], BASEDIR."infusions/gallery/gallery.php?photo_id=".$data['photo_id']);
        $html = ob_get_clean();
    }

    return (string)$html;
}