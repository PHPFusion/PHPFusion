<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: downloads.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Downloads\Functions;
use PHPFusion\httpdownload;
use PHPFusion\OpenGraphDownloads;
use PHPFusion\SiteLinks;

require_once __DIR__.'/../../maincore.php';

if (!defined('DOWNLOADS_EXISTS')) {
    redirect(BASEDIR."error.php?code=404");
}

require_once THEMES.'templates/header.php';
require_once INCLUDES."infusions_include.php";

$locale = fusion_get_locale("", DOWNLOAD_LOCALE);
$aidlink = fusion_get_aidlink();

include INFUSIONS."downloads/templates/downloads.tpl.php";
require_once INFUSIONS."downloads/classes/Functions.php";
require_once INFUSIONS."downloads/classes/OpenGraphDownloads.php";

$dl_settings = get_settings("downloads");
$dl_settings['download_pagination'] = !empty($dl_settings['download_pagination']) ? $dl_settings['download_pagination'] : 15;

/** Filters $_GETs */
$_get_type = get("type");
$_get_cat_id = get("cat_id", FILTER_VALIDATE_INT);
$_get_archive = get("archive");
$_get_file_id = get("file_id", FILTER_VALIDATE_INT);
$_get_download_id = get("download_id", FILTER_VALIDATE_INT);
$_get_author = get("author", FILTER_VALIDATE_INT);


add_breadcrumb(array(
    'link'  => INFUSIONS.'downloads/downloads.php',
    'title' => SiteLinks::get_current_SiteLinks("infusions/downloads/downloads.php", "link_name")
));

if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_downloads.php')) {
    add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('download_1000').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_downloads.php"/>');
}

$result = NULL;

if ($_get_file_id) {
    $res = 0;
    $data = dbarray(dbquery("SELECT download_url, download_file, download_cat, download_visibility FROM ".DB_DOWNLOADS." WHERE download_id=$_get_file_id"));
    if (checkgroup($data['download_visibility'])) {
        $result = dbquery("UPDATE ".DB_DOWNLOADS." SET download_count=download_count+1 WHERE download_id=$_get_file_id");

        if (!empty($data['download_file']) && file_exists(DOWNLOADS.'files/'.$data['download_file'])) {
            $res = 1;
            require_once INCLUDES."class.httpdownload.php";
            ob_end_clean();
            $object = new httpdownload;
            $object->set_byfile(DOWNLOADS.'files/'.$data['download_file']);
            $object->use_resume = TRUE;
            $object->download();
            exit;
        } else if (!empty($data['download_url'])) {
            $res = 1;
            $url_prefix = (!strstr($data['download_url'], "http://") && !strstr($data['download_url'], "https://") ? "http://" : '');
            redirect($url_prefix.$data['download_url']);
        }
    }
    if ($res == 0) {
        redirect(DOWNLOADS."downloads.php");
    }
}

$info = [
    'download_title'        => $locale['download_1001'],
    'download_language'     => LANGUAGE,
    'download_categories'   => get_downloadCat(),
    'download_last_updated' => 0,
    'download_max_rows'     => 0,
    'download_rows'         => 0,
    'download_nav'          => '',
    "get"                   => array(
        "type"        => $_get_type,
        "cat_id"      => $_get_cat_id,
        "archive"     => $_get_archive,
        "file_id"     => $_get_file_id,
        "download_id" => $_get_download_id,
        "author"      => $_get_author
    ),
    "allowed_filters" => array(
        'download' => $locale['download_2003'],
        'recent'   => $locale['download_2002']
    )
];


if (fusion_get_settings('comments_enabled') == 1) {
    $info['allowed_filters']['comments'] = $locale['download_2001'];
}

if (fusion_get_settings('ratings_enabled') == 1) {
    $info['allowed_filters']['ratings'] = $locale['download_2004'];
}

/* Filter Construct */
$filter = array_keys($info['allowed_filters']);
$_get_type = isset($_get_type) && in_array($_get_type, array_keys($info['allowed_filters'])) ? $_get_type : '';
foreach ($info['allowed_filters'] as $type => $filter_name) {
    $filter_link = INFUSIONS."downloads/downloads.php?".(!empty($_get_cat_id) ? "cat_id=".(int)$_get_cat_id."&amp;" : '').($_get_archive ? "archive=$_get_archive&amp;" : '')."type=".$type;
    $active = isset($_get_type) && $_get_type == $type ? 1 : 0;
    $info['download_filter'][$type] = ['title' => $filter_name, 'link' => $filter_link, 'active' => $active];
    unset($filter_link);
}

switch ($_get_type) {
    case 'recent':
        $filter_condition = 'd.download_datestamp DESC';
        break;
    case 'comments':
        $filter_condition = 'count_comment DESC';
        //$filter_count = 'COUNT(td.comment_item_id) AS count_comment,';
        //$filter_join = "LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = d.download_id AND td.comment_type='D' AND td.comment_hidden='0'";
        break;
    case 'ratings':
        $filter_condition = 'sum_rating DESC';
        //$filter_count = 'IF(SUM(tr.rating_vote)>0, SUM(tr.rating_vote), 0) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes,';
        //$filter_join = "LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = d.download_id AND tr.rating_type='D'";
        break;
    case 'download':
        $filter_condition = 'd.download_count DESC';
        break;
    default:
        $filter_condition = '';
}

if ($_get_download_id) {
    if (validate_download($_get_download_id)) {

        $pattern = "SELECT %s(dr.rating_vote) FROM ".DB_RATINGS." AS dr WHERE dr.rating_item_id = d.download_id AND dr.rating_type = 'B'";
        $sql_count = sprintf($pattern, 'COUNT');
        $sql_sum = sprintf($pattern, 'SUM');

        $result = dbquery("SELECT d.*, dc.*, du.user_id, du.user_name, du.user_status, du.user_avatar, du.user_level, du.user_joined,
            ($sql_sum) AS sum_rating,
            ($sql_count) AS count_votes,
            (SELECT COUNT(dcc.comment_id) FROM ".DB_COMMENTS." AS dcc WHERE dcc.comment_item_id = d.download_id AND dcc.comment_type = 'D' AND dcc.comment_hidden = '0') AS count_comment,
            d.download_datestamp AS last_updated
            FROM ".DB_DOWNLOADS." AS d
            INNER JOIN ".DB_DOWNLOAD_CATS." AS dc ON d.download_cat = dc.download_cat_id
            LEFT JOIN ".DB_USERS." AS du ON d.download_user = du.user_id
            ".(multilang_table("DL") ? "WHERE ".in_group('dc.download_cat_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('d.download_visibility')." AND
            d.download_id=$_get_download_id
            GROUP BY d.download_id
        ");

        $info['download_rows'] = dbrows($result);

        if ($info['download_rows'] > 0) {
            include INCLUDES."comments_include.php";
            include INCLUDES."ratings_include.php";
            $data = dbarray($result);

            $data['download_description_short'] = nl2br(parse_textarea($data['download_description_short'], TRUE, TRUE, FALSE));
            $data['download_description'] = nl2br(parse_textarea($data['download_description'], FALSE, FALSE, TRUE, FALSE));
            $data['download_file_link'] = INFUSIONS."downloads/downloads.php?file_id=".$data['download_id'];
            $data['download_post_author'] = display_avatar($data, '25px', '', TRUE, 'img-rounded m-r-5').profile_link($data['user_id'], $data['user_name'], $data['user_status']);
            $data['download_post_cat'] = $locale['in']." <a href='".INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat_id']."'>".$data['download_cat_name']."</a>";
            $data['download_post_time'] = showdate('shortdate', $data['download_datestamp']);
            $data['download_post_time2'] = $locale['global_049']." ".timer($data['download_datestamp']);
            $data['download_count'] = format_word($data['download_count'], $locale['fmt_download']);
            $data['download_version'] = $data['download_version'] ? $data['download_version'] : $locale['na'];
            $data['download_license'] = $data['download_license'] ? $data['download_license'] : $locale['na'];
            $data['download_os'] = $data['download_os'] ? $data['download_os'] : $locale['na'];
            $data['download_copyright'] = $data['download_copyright'] ? $data['download_copyright'] : $locale['na'];
            if ($data['download_homepage']) {
                $urlprefix = (!strstr($data['download_homepage'], "http://") && !strstr($data['download_homepage'], "https://")) ? 'http://' : '';
                $data['download_homepage'] = "<a href='".$urlprefix.$data['download_homepage']."' title='".$urlprefix.$data['download_homepage']."' target='_blank'>".$locale['download_1018']."</a>\n";
            } else {
                $data['download_homepage'] = $locale['na'];
            }
            /* Admin link */
            $data['admin_link'] = '';
            if (iADMIN && checkrights('D')) {
                $data['admin_link'] = [
                    'edit'   => INFUSIONS."downloads/downloads_admin.php".$aidlink."&amp;action=edit&amp;section=download_form&amp;download_id=".$data['download_id'],
                    'delete' => INFUSIONS."downloads/downloads_admin.php".$aidlink."&amp;action=delete&amp;section=download_form&amp;download_id=".$data['download_id'],
                ];
            }
            $info['download_title'] = $data['download_title'];
            $info['download_updated'] = $locale['global_049']." ".timer($data['download_datestamp']);

            add_breadcrumb([
                'link'  => INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat_id'],
                'title' => $data['download_cat_name']
            ]);
            add_breadcrumb([
                'link'  => INFUSIONS."downloads/downloads.php?download_id=$_get_download_id",
                'title' => $data['download_title']
            ]);

            $navbar_title = SiteLinks::get_current_SiteLinks("infusions/downloads/downloads.php", "link_name");
            if (!$navbar_title) {
                $navbar_title = $locale["download_1000"];
            }

            set_title($navbar_title.$locale['global_201']);
            add_to_title($data['download_title']);
            add_to_meta($data['download_title'].($data['download_keywords'] ? ",".$data['download_keywords'] : ''));
            if ($data['download_keywords'] !== "") {
                set_meta("keywords", $data['download_keywords']);
            }
            $data['download_link'] = "<a class='text-dark' href='".INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat_id']."&download_id=".$data['download_id']."'>".$data['download_title']."</a>";

            $data['download_show_comments'] = Functions::get_download_comments($data);
            $data['download_show_ratings'] = Functions::get_download_ratings($data);

            $info['download_item'] = $data;

            OpenGraphDownloads::ogDownload($data['download_id']);

        } else {
            redirect(INFUSIONS."downloads/downloads.php");
        }
    } else {
        redirect(INFUSIONS."downloads/downloads.php");
    }
} else {

    $condition = '';
    if ($_get_author) {
        $condition = "AND download_user =$_get_author";
    }

    if ($_get_cat_id) {
        add_to_title($locale['download_1000']);
        set_meta("name", $locale['download_1000']);

        $res = dbarray(dbquery("SELECT * FROM ".DB_DOWNLOAD_CATS.(multilang_table('DL') ? " WHERE ".in_group('download_cat_language', LANGUAGE)." AND " : " WHERE ")."download_cat_id=$_get_cat_id"));
        if (!empty($res)) {
            $info += $res;
        } else {
            redirect(clean_request('', array("cat_id"), FALSE));
        }

        downloadCats_breadcrumbs(get_downloadCatsIndex());

        $info['download_title'] = $info['download_cat_name'];
        $info['download_max_rows'] = dbcount("('download_id')", DB_DOWNLOADS, "download_cat=$_get_cat_id AND ".groupaccess('download_visibility'));

        $rowstart = get_rowstart("rowstart", $info["download_max_rows"]);

        if ($info['download_max_rows']) {
            switch ($_get_type) {
                case 'recent':
                    $filter_condition = 'd.download_datestamp DESC';
                    break;
                case 'comments':
                    $filter_condition = 'count_comment DESC';
                    break;
                case 'ratings':
                    $filter_condition = 'sum_rating DESC';
                    break;
                case 'download':
                    $filter_condition = 'd.download_count DESC';
                    break;
                default:
                    $filter_condition = dbresult(dbquery("SELECT download_cat_sorting FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id=$_get_cat_id"), 0);
            }
            $pattern = "SELECT %s(dr.rating_vote) FROM ".DB_RATINGS." AS dr WHERE dr.rating_item_id=d.download_id AND dr.rating_type='D'";
            $sql_count = sprintf($pattern, 'COUNT');
            $sql_sum = sprintf($pattern, 'SUM');

            $sql = "SELECT d.*, dc.*, du.user_id, du.user_name, du.user_status, du.user_avatar , du.user_level, du.user_joined,
                ($sql_sum) AS sum_rating,
                ($sql_count) AS count_votes,
                (SELECT COUNT(dcc.comment_id) FROM ".DB_COMMENTS." AS dcc WHERE dcc.comment_item_id = d.download_id AND dcc.comment_type = 'D' AND dcc.comment_hidden = '0') AS count_comment,
                MAX(d.download_datestamp) AS last_updated
                FROM ".DB_DOWNLOADS." AS d
                INNER JOIN ".DB_DOWNLOAD_CATS." AS dc ON d.download_cat=dc.download_cat_id
                LEFT JOIN ".DB_USERS." du ON d.download_user=du.user_id
                ".(multilang_table("DL") ? " WHERE ".in_group('download_cat_language', LANGUAGE)." AND " : " WHERE ")." ".groupaccess('download_visibility')."
                AND d.download_cat=$_get_cat_id
                GROUP BY d.download_id
                ORDER BY ".(!empty($filter_condition) ? $filter_condition : "dc.download_cat_sorting")."
                LIMIT $rowstart,".intval($dl_settings['download_pagination']);

            $result = dbquery($sql);
            $info['download_rows'] = dbrows($result);

        }

        OpenGraphDownloads::ogDownloadCat($_get_cat_id);

    } else {

        set_title($locale['download_1000']);

        /**
         * Everyone's Download Posts
         */

        $info['download_max_rows'] = dbcount("('download_id')", DB_DOWNLOADS, groupaccess('download_visibility'));

        if ($info['download_max_rows'] > 0) {

            $rowstart = get_rowstart("rowstart", $info["download_max_rows"]);

            $pattern = "SELECT %s(dr.rating_vote) FROM ".DB_RATINGS." AS dr WHERE dr.rating_item_id = d.download_id AND dr.rating_type = 'D'";
            $sql_count = sprintf($pattern, 'COUNT');
            $sql_sum = sprintf($pattern, 'SUM');
            $download_query = "SELECT d.*, dc.*, du.user_id, du.user_name, du.user_status, du.user_avatar , du.user_level, du.user_joined,
                ($sql_sum) AS sum_rating,
                ($sql_count) AS count_votes,
                (SELECT COUNT(dcc.comment_id) FROM ".DB_COMMENTS." AS dcc WHERE dcc.comment_item_id = d.download_id AND dcc.comment_type = 'D' AND dcc.comment_hidden = '0') AS count_comment,
                max(d.download_datestamp) as last_updated
                FROM ".DB_DOWNLOADS." AS d
                INNER JOIN ".DB_DOWNLOAD_CATS." AS dc ON d.download_cat=dc.download_cat_id
                LEFT JOIN ".DB_USERS." AS du ON d.download_user=du.user_id
                ".(multilang_table("DL") ? "WHERE ".in_group('dc.download_cat_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('download_visibility')."
                ".$condition."
                GROUP BY d.download_id
                ORDER BY ".($filter_condition ? $filter_condition : "dc.download_cat_sorting")."
                LIMIT $rowstart,".(int)$dl_settings['download_pagination'];

            $result = dbquery($download_query);
            $info['download_rows'] = dbrows($result);
        }
    }
}

if (!empty($info['download_max_rows']) && ($info['download_max_rows'] > $dl_settings['download_pagination']) && !check_get("download_id")) {
    $page_nav_link = (!empty($info["get"]["type"]) ? INFUSIONS."downloads/downloads.php?type=$_get_type&amp;" : '');

    if (!empty($_get_cat_id) && isnum($_get_cat_id)) {
        $page_nav_link = INFUSIONS."downloads/downloads.php?cat_id=".$_get_cat_id.(!empty($_get_type) ? "&amp;type=".$_get_type : '')."&amp;";
    } else if ($_get_author) {
        $info['download_max_rows'] = dbcount("('download_id')", DB_DOWNLOADS, "download_user='".$_get_author."' AND ".groupaccess('download_visibility'));
        $page_nav_link = INFUSIONS."downloads/downloads.php?author=".$_get_author."&amp;";
    }

    $rowstart = get_rowstart("rowstart", $info["download_max_rows"]);
    $info['download_nav'] = makepagenav($rowstart, $dl_settings['download_pagination'], $info['download_max_rows'], 3, $page_nav_link);
}

if (!empty($info['download_rows'])) {
    while ($data = dbarray($result)) {
        $data['count_comment'] = !empty($data['count_comment']) ? $data['count_comment'] : 0;
        $data['count_votes'] = !empty($data['count_votes']) ? $data['count_votes'] : 0;
        $data['sum_rating'] = !empty($data['sum_rating']) ? $data['sum_rating'] : 0;
        $data = array_merge($data, parseInfo($data));
        $info['download_item'][$data['download_id']] = $data;
    }
}
$author_result = dbquery("SELECT d.download_title, d.download_user, count(d.download_id) AS download_count, du.user_id, du.user_name, du.user_status
                FROM ".DB_DOWNLOADS." AS d
                INNER JOIN ".DB_USERS." AS du ON (d.download_user = du.user_id)
                GROUP BY d.download_user ORDER BY d.download_user");
if (dbrows($author_result)) {
    while ($at_data = dbarray($author_result)) {
        $active = ($_get_author == $at_data['download_user'] ? 1 : 0);
        $info['download_author'][$at_data['download_user']] = [
            'title'  => $at_data['user_name'],
            'link'   => INFUSIONS."downloads/downloads.php?author=".$at_data['download_user'],
            'count'  => $at_data['download_count'],
            'active' => $active
        ];
    }
}

render_downloads($info);
require_once THEMES.'templates/footer.php';

/**
 * Returns Downloads Category Hierarchy Tree Data
 *
 * @return array
 */
function get_downloadCat() {
    return Functions::get_downloadCatsData();
}

/**
 * Get Downloads Hierarchy Index
 *
 * @return array
 */
function get_downloadCatsIndex() {
    return PHPFusion\Downloads\Functions::get_downloadCatsIndex();
}

/**
 * Validate Downloads ID
 *
 * @param $download_id
 *
 * @return int
 */
function validate_download($download_id) {
    return PHPFusion\Downloads\Functions::validate_download($download_id);
}

/**
 * Validate Downloads Cat Id
 *
 * @param $download_cat_id
 *
 * @return int
 */
function validate_downloadCats($download_cat_id) {
    return PHPFusion\Downloads\Functions::validate_downloadCat($download_cat_id);
}

/**
 * Get the closest image available
 *
 * @param      $image
 * @param      $thumb1
 * @param bool $hires - true for image, false for thumbnail
 *
 * @return bool|string
 */
function get_download_image_path($image, $thumb1, $hires = FALSE) {
    return Functions::get_download_image_path($image, $thumb1, $hires);
}

function downloadCats_breadcrumbs($hierarchy_index) {
    Functions::downloadCats_breadcrumbs($hierarchy_index);
}

/**
 * Custom data formatter
 *
 * @param $data
 *
 * @return array
 */
function parseInfo($data) {
    global $dl_settings;
    $locale = fusion_get_locale();
    $download_image = '';
    if ($data['download_image'] && $dl_settings['download_screenshot'] == "1") {
        $lowRes_image_path = get_download_image_path($data['download_image'], $data['download_image_thumb'], FALSE);
        $download_image = "<a href='".INFUSIONS."downloads/downloads.php?download_id=".$data['download_id']."'>".thumbnail($lowRes_image_path,
                '100px')."</a>";
    }
    return [
        'download_anchor'            => "<a name='download_".$data['download_id']."' id='download_".$data['download_id']."'></a>",
        'download_description_short' => nl2br(parseubb(parsesmileys(html_entity_decode(stripslashes($data['download_description_short']))))),
        'download_description'       => nl2br(parseubb(parsesmileys(html_entity_decode(stripslashes($data['download_description']))))),
        'download_link'              => INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat_id']."&download_id=".$data['download_id'],
        'download_category_link'     => "<a href='".INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat']."'>".$data['download_cat_name']."</a>\n",
        'download_readmore_link'     => "<a href='".INFUSIONS."downloads/downloads.php?download_id=".$data['download_id']."'>".$locale['download_1006']."</a>\n",
        'download_title'             => stripslashes($data['download_title']),
        'download_image'             => $download_image,
        'download_thumb'             => get_download_image_path($data['download_image'], $data['download_image_thumb'], FALSE),
        "download_count"             => format_word($data['download_count'], $locale['fmt_download']),
        "download_comments"          => format_word($data['count_comment'], $locale['fmt_comment']),
        'download_sum_rating'        => format_word($data['sum_rating'], $locale['fmt_rating']),
        'download_count_votes'       => format_word($data['count_votes'], $locale['fmt_vote']),
        'download_user_avatar'       => display_avatar($data, '25px', '', TRUE, 'img-rounded'),
        'download_user_link'         => profile_link($data['user_id'], $data['user_name'], $data['user_status'], 'strong'),
        'download_post_time'         => showdate('shortdate', $data['download_datestamp']),
        'download_post_time2'        => $locale['global_049']." ".timer($data['download_datestamp']),
        'download_file_link'         => file_exists(DOWNLOADS.'files/'.$data['download_file']) ? INFUSIONS."downloads/downloads.php?file_id=".$data['download_id'] : '',
    ];
}
