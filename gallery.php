<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery.php
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
require_once __DIR__.'/../../maincore.php';
if (!defined('GALLERY_EXIST')) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES.'templates/header.php';
$locale = fusion_get_locale('', GALLERY_LOCALE);
include INFUSIONS."gallery/templates/gallery.php";
require_once INCLUDES."infusions_include.php";

if (!defined('SAFEMODE')) {
    define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
}

$gallery_settings = get_settings("gallery");

$gallery_settings['gallery_pagination'] = !empty($gallery_settings['gallery_pagination']) ? $gallery_settings['gallery_pagination'] : 24;

/* View Photo */
if (isset($_GET['photo_id']) && isnum($_GET['photo_id'])) {
    include INCLUDES."comments_include.php";
    include INCLUDES."ratings_include.php";
    add_to_jquery("$('a.photogallery_photo_link').colorbox({width:'80%', height:'80%', photo:true});");

    $pattern = "SELECT %s(pr.rating_vote) FROM ".DB_RATINGS." AS pr WHERE pr.rating_item_id = p.photo_id AND pr.rating_type = 'P'";
    $sql_count = sprintf($pattern, 'COUNT');
    $sql_sum = sprintf($pattern, 'SUM');
    $result = dbquery("SELECT p.*, pa.album_id, pa.album_title, pa.album_access, pa.album_keywords, pu.user_id, pu.user_name, pu.user_status,
        ($sql_sum) AS sum_rating,
        ($sql_count) AS count_votes,
        (SELECT COUNT(pc.comment_id) FROM ".DB_COMMENTS." AS pc WHERE pc.comment_item_id = p.photo_id AND pc.comment_type = 'P' AND pc.comment_hidden = '0') AS count_comment
        FROM ".DB_PHOTOS." AS p
        LEFT JOIN ".DB_PHOTO_ALBUMS." AS pa USING (album_id)
        LEFT JOIN ".DB_USERS." AS pu ON p.photo_user=pu.user_id
        WHERE ".groupaccess('album_access')." AND photo_id='".intval($_GET['photo_id'])."' GROUP BY p.photo_id");
    $info = [];
    if (dbrows($result) > 0) {
        $data = dbarray($result);
        /* Declaration */
        $result = dbquery("UPDATE ".DB_PHOTOS." SET photo_views=(photo_views+1) WHERE photo_id=:photoid", [':photoid' => $_GET['photo_id']]);
        $pres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order=:porder AND album_id=:albumid", [':porder' => ($data['photo_order'] - 1), ':albumid' => $data['album_id']]);
        $nres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order=:porder AND album_id=:albumid", [':porder' => ($data['photo_order'] + 1), ':albumid' => $data['album_id']]);
        $fres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order=:porder AND album_id=:albumid", [':porder' => 1, ':albumid' => $data['album_id']]);
        $lastres = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id=:albumid", [':albumid' => $data['album_id']]), 0);
        $lres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order>=:porder AND album_id=:albumid", [':porder' => $lastres, ':albumid' => $data['album_id']]);
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

        set_title($locale['gallery_465']);
        add_to_title($locale['global_201'].$data['photo_title']);
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS."gallery/gallery.php",
            'title' => $locale['gallery_465']
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
            $info += [
                'photo_thumb2'   => $data['photo_thumb2'] ? IMAGES_G_T.$data['photo_thumb2'] : "",
                'photo_thumb1'   => $data['photo_thumb1'] ? IMAGES_G_T.$data['photo_thumb1'] : "",
                'photo_filename' => IMAGES_G.$data['photo_filename'],
                'photo_size'     => getimagesize(IMAGES_G.$data['photo_filename'])
            ];
        }
        $info += [
            'photo_description' => $data['photo_description'] ? nl2br(parse_textarea($data['photo_description'], FALSE, FALSE, TRUE, FALSE)) : '',
            'photo_byte'        => parsebytesize($gallery_settings['photo_watermark'] ? filesize(IMAGES_G.$data['photo_filename']) : filesize(IMAGES_G.$data['photo_filename'])),
            'photo_comment'     => $data['photo_allow_comments'] ? number_format($data['count_comment']) : 0,
            'photo_ratings'     => $data['photo_allow_ratings'] && $data['count_votes'] > 0 ? number_format(ceil($data['sum_rating'] / $data['count_votes'])) : '0'
        ];

        if ((isset($prev['photo_id']) && isnum($prev['photo_id'])) || (isset($next['photo_id']) && isnum($next['photo_id']))) {
            if (isset($prev) && isset($first)) {
                $info['nav']['first'] = [
                    'link' => INFUSIONS."gallery/gallery.php?photo_id=".$first['photo_id'],
                    'name' => $locale['gallery_459']
                ];
            }
            if (isset($prev)) {
                $info['nav']['prev'] = [
                    'link' => INFUSIONS."gallery/gallery.php?photo_id=".$prev['photo_id'],
                    'name' => $locale['gallery_451']
                ];
            }
            if (isset($next)) {
                $info['nav']['next'] = [
                    'link' => INFUSIONS."gallery/gallery.php?photo_id=".$next['photo_id'],
                    'name' => $locale['gallery_452']
                ];
            }
            if (isset($next) && isset($last)) {
                $info['nav']['last'] = [
                    'link' => INFUSIONS."gallery/gallery.php?photo_id=".$last['photo_id'],
                    'name' => $locale['gallery_460']
                ];
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
            FROM ".DB_PHOTO_ALBUMS."
            WHERE ".groupaccess('album_access')." AND album_id=:albumid", [':albumid' => intval($_GET['album_id'])]
        );
        if (dbrows($result) > 0) {
            $info = dbarray($result);

            set_title($locale['gallery_465']);
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
            $info['album_link'] = [
                'link' => INFUSIONS.'gallery/gallery.php?album_id='.$_GET['album_id'],
                'name' => $info['album_title']
            ];
            $info['max_rows'] = dbcount("(photo_id)", DB_PHOTOS, "album_id='".$_GET['album_id']."'");
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['max_rows'] ? $_GET['rowstart'] : 0;
            if ($info['max_rows'] > 0) {
                // Album stats
                $latest_update = dbarray(dbquery("
                    SELECT p.photo_datestamp, pu.user_id, pu.user_name, pu.user_status
                    FROM ".DB_PHOTOS." AS p
                    LEFT JOIN ".DB_USERS." AS pu ON p.photo_user = pu.user_id
                    WHERE album_id=:albumid
                    ORDER BY photo_datestamp DESC LIMIT 1", [':albumid' => intval($_GET['album_id'])]));
                $info['album_stats'] = $locale['gallery_422']." ".$info['max_rows']."<br />\n";
                $info['album_stats'] .= $locale['gallery_423']." ".profile_link($latest_update['user_id'], $latest_update['user_name'], $latest_update['user_status'])." ".$locale['gallery_424']." ".showdate("longdate", $latest_update['photo_datestamp'])."\n";
                $pattern = "SELECT %s(pr.rating_vote) FROM ".DB_RATINGS." AS pr WHERE pr.rating_item_id = p.photo_id AND pr.rating_type = 'P'";
                $sql_count = sprintf($pattern, 'COUNT');
                $sql_sum = sprintf($pattern, 'SUM');
                $result = dbquery("SELECT p.*, pu.user_id, pu.user_name, pu.user_status, pu.user_avatar,
                    ($sql_sum) AS sum_rating,
                    ($sql_count) AS count_votes,
                    (SELECT COUNT(pc.comment_id) FROM ".DB_COMMENTS." AS pc WHERE pc.comment_item_id = p.photo_id AND pc.comment_type = 'P' AND pc.comment_hidden = '0') AS count_comment
                    FROM ".DB_PHOTOS." AS p
                    LEFT JOIN ".DB_USERS." AS pu ON p.photo_user=pu.user_id
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
                        $data += [
                            'photo_link'  => [
                                'link' => INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id'],
                                'name' => $data['photo_title']
                            ],
                            'image'       => displayPhotoImage($data['photo_id'], $data['photo_filename'], $data['photo_thumb1'], $data['photo_thumb2'], INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id']),
                            'title'       => ($data['photo_title']) ? $data['photo_title'] : $data['image'],
                            'description' => ($data['photo_description']) ? nl2br(parse_textarea($data['photo_description'])) : '',
                            'photo_views' => format_word($data['photo_views'], $locale['fmt_views'])
                        ];
                        if (iADMIN && checkrights("PH")) {
                            global $aidlink;
                            $data['photo_edit'] = [
                                'link' => INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=photo_form&amp;action=edit&amp;photo_id=".$data['photo_id'],
                                'name' => $locale['edit']
                            ];
                            $data['photo_delete'] = [
                                'link' => INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=actions&amp;action=delete&amp;photo_id=".$data['photo_id'],
                                'name' => $locale['delete']
                            ];
                        }
                        if ($data['photo_allow_comments']) {
                            $data += [
                                'photo_votes'    => $data['count_votes'] > 0 ? $data['count_votes'] : '0',
                                'photo_comments' => [
                                    'link' => $data['photo_link']['link'].'#comments',
                                    'name' => $data['count_comment'],
                                    'word' => format_word($data['count_comment'], $locale['fmt_comment'])
                                ]
                            ];
                        }
                        if ($data['photo_allow_ratings']) {
                            $data += [
                                'sum_rating'    => $data['sum_rating'] > 0 ? $data['sum_rating'] : '0',
                                'photo_ratings' => [
                                    'link' => $data['photo_link']['link'].'#ratings',
                                    'name' => $data['count_votes'],
                                    'word' => ($data['sum_rating'] > 0) ? ($data['sum_rating'] / $data['count_votes'] * 10)."/10" : "0/10",
                                ]
                            ];
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
            FROM ".DB_PHOTO_ALBUMS." AS ta
            LEFT JOIN ".DB_USERS." AS tu ON ta.album_user=tu.user_id
            ".(multilang_table("PG") ? "WHERE ".in_group('ta.album_language', LANGUAGE)." AND" : "WHERE")."
            ".groupaccess('album_access')." ORDER BY album_order
            LIMIT ".$_GET['rowstart'].", ".$gallery_settings['gallery_pagination']);
            while ($data = dbarray($result)) {
                $data['album_link'] = [
                    'link' => INFUSIONS."gallery/gallery.php?album_id=".$data['album_id'],
                    'name' => $data['album_title']
                ];
                if (iADMIN && checkrights("PH")) {
                    global $aidlink;
                    $data['album_edit'] = [
                        'link' => INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=album_form&amp;action=edit&amp;cat_id=".$data['album_id'],
                        'name' => $locale['edit']
                    ];
                    $data['album_delete'] = [
                        'link' => INFUSIONS."gallery/gallery_admin.php".$aidlink."&amp;section=actions&amp;action=delete&amp;cat_id=".$data['album_id'],
                        'name' => $locale['delete']
                    ];
                }
                $photo_directory = !SAFEMODE ? "album_".$data['album_id'] : '';

                // if ($data['album_image']) {
                $data['image'] = displayAlbumImage($data['album_image'], $data['album_thumb1'], $data['album_thumb2'],
                    INFUSIONS."gallery/gallery.php?album_id=".$data['album_id']);
                //}
                $data['title'] = $data['album_title'] ? $data['album_title'] : $locale['gallery_402'];
                $data['description'] = $data['album_description'] ? nl2br(parse_textarea($data['album_description'])) : '';
                $_photo = dbquery("SELECT pp.photo_user, u.user_id, u.user_name, u.user_status, u.user_avatar
                    FROM ".DB_PHOTOS." AS pp
                    LEFT JOIN ".DB_USERS." AS u on u.user_id=pp.photo_user
                    WHERE album_id=:albumid
                    ORDER BY photo_datestamp", [':albumid' => intval($data['album_id'])]
                );
                $data['photo_rows'] = dbrows($_photo);
                $user = [];
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

function photo_thumbnail($data) {
    $locale = fusion_get_locale();
    echo "<div class='panel panel-default tbl-border'>\n";
    echo "<div class='p-0'>\n";
    echo "<!--photogallery_album_photo_".$data['photo_id']."-->";
    echo "<a href='".INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id']."' class='photogallery_album_photo_link'>\n";
    $thumb_img = ($data['photo_thumb1'] && file_exists(IMAGES_G.$data['photo_thumb1'])) ? IMAGES_G.$data['photo_thumb1'] : DOWNLOADS."images/no_image.jpg";
    $title = ($data['album_thumb1'] && file_exists(IMAGES_G.$data['album_thumb1'])) ? $data['album_thumb1'] : $locale['gallery_402'];
    echo "<img class='photogallery_album_photo img-responsive' style='min-width: 100%;' src='".$thumb_img."' title='$title' alt='$title' />\n";
    echo "</a>\n";
    echo "</div>\n<div class='panel-body photogallery_album_photo_info'>\n";
    echo "<a href='".INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id']."' class='photogallery_album_photo_link'><strong>".$data['photo_title']."</strong></a>\n";
    echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
    echo "<!--photogallery_album_photo_info-->\n";
    echo "<span class='display-inline-block'>\n";
    echo($data['photo_allow_ratings'] ? $locale['gallery_437'].($data['count_votes'] > 0 ? str_repeat("<i class='fa fa-star'></i>", ceil($data['sum_rating'] / $data['count_votes'])) : $locale['gallery_438'])."<br />\n" : "");
    echo "</span>\n<br/>\n";
    echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
    echo "<span> ".$locale['gallery_434'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])." </span>";
    echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
    echo "<span class='m-r-10'><abbr title='".$locale['gallery_403'].showdate("shortdate", $data['photo_datestamp'])."'><i title='".$locale['gallery_403'].showdate("shortdate", $data['photo_datestamp'])."' class='fa fa-calendar text-lighter'></i></abbr></span>";
    $photo_comments = dbcount("(comment_id)", DB_COMMENTS, "comment_type='P' AND comment_item_id='".$data['photo_id']."'");
    $comments_text = ($data['photo_allow_comments'] ? ($photo_comments == 1 ? $locale['gallery_436b'] : $locale['gallery_436']).$photo_comments : "");
    echo "<span class='m-r-10'><abbr title='".$comments_text."'><i class='fa fa-comment text-lighter'></i></abbr> $photo_comments</abbr></span>";
    echo "<span class='m-r-10'><abbr title='".$locale['gallery_434'].$data['user_name']."'><i class='fa fa-user text-lighter'></i></span>";
    echo "<span><abbr title='".$locale['gallery_435'].$data['photo_views']."'><i class='fa fa-eye text-lighter'></i></abbr> ".$data['photo_views']."</span>";
    echo "</div></div>\n";
}

require_once THEMES.'templates/footer.php';

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
    $gallery_settings = get_settings("gallery");
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
    $gallery_settings = get_settings("gallery");
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
        showcomments("P", DB_PHOTOS, "photo_id", $data['photo_id'], BASEDIR."infusions/gallery/gallery.php?photo_id=".$data['photo_id']);
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
