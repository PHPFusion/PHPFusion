<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads.php
| Author: Frederick MC Chan (Chan)
| Version : 9.00
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

if (!infusion_exists('downloads')) {
    redirect(BASEDIR."error.php?code=404");
}

require_once THEMES."templates/header.php";
require_once INCLUDES."infusions_include.php";

if (file_exists(INFUSIONS."downloads/locale/".LOCALESET."downloads.php")) {
    $locale += fusion_get_locale("", INFUSIONS."downloads/locale/".LOCALESET."downloads.php");
} else {
    $locale += fusion_get_locale("", INFUSIONS."downloads/locale/English/downloads.php");
}

include INFUSIONS."downloads/templates/downloads.php";
require_once INFUSIONS."downloads/classes/Functions.php";
require_once INFUSIONS."downloads/classes/OpenGraphDownloads.php";

$dl_settings = get_settings("downloads");
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
    'link'  => INFUSIONS.'downloads/downloads.php',
    'title' => \PHPFusion\SiteLinks::get_current_SiteLinks("infusions/downloads/downloads.php", "link_name")
]);

$result = NULL;

if (isset($_GET['file_id']) && isnum($_GET['file_id'])) {
    $res = 0;
    $data = dbarray(dbquery("SELECT download_url, download_file, download_cat, download_visibility FROM ".DB_DOWNLOADS." WHERE download_id='".intval($_GET['file_id'])."'"));
    if (checkgroup($data['download_visibility'])) {
        $result = dbquery("UPDATE ".DB_DOWNLOADS." SET download_count=download_count+1 WHERE download_id='".intval($_GET['file_id'])."'");

        if (!empty($data['download_file']) && file_exists(DOWNLOADS.'files/'.$data['download_file'])) {
            $res = 1;
            require_once INCLUDES."class.httpdownload.php";
            ob_end_clean();
            $object = new \PHPFusion\httpdownload;
            $object->set_byfile(DOWNLOADS.'files/'.$data['download_file']);
            $object->use_resume = TRUE;
            $object->download();
            exit;
        } elseif (!empty($data['download_url'])) {
            $res = 1;
            $url_prefix = (!strstr($data['download_url'], "http://") && !strstr($data['download_url'], "https://") ? "http://" : '');
            redirect($url_prefix.$data['download_url']);
        }
    }
    if ($res == 0) {
        redirect(DOWNLOADS."downloads.php");
    }
}

$info = array(
    'download_title'        => $locale['download_1001'],
    'download_language'     => LANGUAGE,
    'download_categories'   => get_downloadCat(),
    'allowed_filters'       => array(
        'download' => $locale['download_2003'],
        'recent'   => $locale['download_2002'],
        'comments' => $locale['download_2001'],
        'ratings'  => $locale['download_2004'],
    ),
    'download_last_updated' => 0,
    'download_max_rows'     => 0,
    'download_rows'         => 0,
    'download_nav'          => '',
);
/* Filter Construct */
$filter = array_keys($info['allowed_filters']);
$_GET['type'] = isset($_GET['type']) && in_array($_GET['type'],
    array_keys($info['allowed_filters'])) ? $_GET['type'] : '';
foreach ($info['allowed_filters'] as $type => $filter_name) {
    $filter_link = INFUSIONS."downloads/downloads.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '').(isset($_GET['archive']) ? "archive=".$_GET['archive']."&amp;" : '')."type=".$type;
    $active = isset($_GET['type']) && $_GET['type'] == $type ? 1 : 0;
    $info['download_filter'][$type] = array('title' => $filter_name, 'link' => $filter_link, 'active' => $active);
    unset($filter_link);
}

switch ($_GET['type']) {
    case 'recent':
        $filter_condition = 'download_datestamp DESC';
        break;
    case 'comments':
        $filter_condition = 'count_comment DESC';
        $filter_count = 'COUNT(td.comment_item_id) AS count_comment,';
		$filter_join = "LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = d.download_id AND td.comment_type='D' AND td.comment_hidden='0'";
        break;
    case 'ratings':
        $filter_condition = 'sum_rating DESC';
        $filter_count = 'IF(SUM(tr.rating_vote)>0, SUM(tr.rating_vote), 0) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes,';
		$filter_join = "LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = d.download_id AND tr.rating_type='D'";
        break;
    case 'download':
        $filter_condition = 'download_count DESC';
        break;
    default:
        $filter_condition = '';
}

if (isset($_GET['download_id'])) {
    if (validate_download($_GET['download_id'])) {

        $download_query = "SELECT d.*, dc.*,
					tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
	 				SUM(tr.rating_vote) AS sum_rating,
					COUNT(tr.rating_item_id) AS count_votes,
					d.download_datestamp as last_updated
					FROM ".DB_DOWNLOADS." d
					INNER JOIN ".DB_DOWNLOAD_CATS." dc ON d.download_cat=dc.download_cat_id
					LEFT JOIN ".DB_USERS." tu ON d.download_user=tu.user_id
					LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = d.download_id AND tr.rating_type='D'
					".(multilang_table("DL") ? "WHERE dc.download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_visibility')." AND
					download_id='".intval($_GET['download_id'])."'
					GROUP BY download_id";

        $result = dbquery($download_query);
        $info['download_rows'] = dbrows($result);

        if ($info['download_rows'] > 0) {
            include INCLUDES."comments_include.php";
            include INCLUDES."ratings_include.php";
            $data = dbarray($result);
            $data['download_description_short'] = nl2br(parse_textarea($data['download_description_short']));
            $data['download_description'] = nl2br(parse_textarea($data['download_description'], FALSE, FALSE, TRUE, FALSE));
            $data['download_file_link'] = INFUSIONS."downloads/downloads.php?file_id=".$data['download_id'];
            $data['download_post_author'] = display_avatar($data, '25px', '', TRUE, 'img-rounded m-r-5').profile_link($data['user_id'], $data['user_name'], $data['user_status']);
            $data['download_post_cat'] = $locale['in']." <a href='".INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat']."'>".$data['download_cat_name']."</a>";
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
                $data['admin_link'] = array(
                    'edit'   => INFUSIONS."downloads/downloads_admin.php".$aidlink."&amp;action=edit&amp;section=download_form&amp;download_id=".$data['download_id'],
                    'delete' => INFUSIONS."downloads/downloads_admin.php".$aidlink."&amp;action=delete&amp;section=download_form&amp;download_id=".$data['download_id'],
                );
            }
            $info['download_title'] = $data['download_title'];
            $info['download_updated'] = $locale['global_049']." ".timer($data['download_datestamp']);
            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat_id'],
                'title' => $data['download_cat_name']
            ]);
            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS."downloads/downloads.php?download_id=".$_GET['download_id'],
                'title' => $data['download_title']
            ]);
            set_title(\PHPFusion\SiteLinks::get_current_SiteLinks("infusions/downloads/downloads.php", "link_name").$locale['global_201']);
            add_to_title($data['download_title']);
            add_to_meta($data['download_title'].($data['download_keywords'] ? ",".$data['download_keywords'] : ''));
            if ($data['download_keywords'] !== "") {
                set_meta("keywords", $data['download_keywords']);
            }
            $data['download_link'] = "<a class='text-dark' href='".INFUSIONS."downloads/downloads.php?cat_id=".$data['download_cat_id']."&download_id=".$data['download_id']."'>".$data['download_title']."</a>";

            $data['download_show_comments'] = \PHPFusion\Downloads\Functions::get_download_comments($data);
            $data['download_show_ratings'] = \PHPFusion\Downloads\Functions::get_download_ratings($data);

            $info['download_item'] = $data;

            \PHPFusion\OpenGraphDownloads::ogDownload($_GET['download_id']);
        } else {
            redirect(INFUSIONS."downloads/downloads.php");
        }
    } else {
        redirect(INFUSIONS."downloads/downloads.php");
    }
} else {

    $condition = '';
    if (isset($_GET['author']) && isnum($_GET['author'])) {
        $condition = "AND download_user = '".intval($_GET['author'])."'";
    }

    if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
        set_title($locale['download_1000']);
        set_meta("name", $locale['download_1000']);
        downloadCats_breadcrumbs(get_downloadCatsIndex());

        $res = dbarray(dbquery("SELECT * FROM ".DB_DOWNLOAD_CATS.(multilang_table('DL') ? " WHERE download_cat_language='".LANGUAGE."' AND " : " WHERE ")."download_cat_id='".intval($_GET['cat_id'])."'"));
        if (!empty($res)) {
            $info += $res;
        } else {
            redirect(FUSION_SELF);
        }

        $info['download_title'] = $info['download_cat_name'];
        $info['download_max_rows'] = dbcount("('download_id')", DB_DOWNLOADS, "download_cat='".intval($_GET['cat_id'])."' AND ".groupaccess('download_visibility'));
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['download_max_rows']) ? $_GET['rowstart'] : 0;
        if ($info['download_max_rows']) {
            switch ($_GET['type']) {
                case 'recent':
                    $filter_condition = 'download_datestamp DESC';
                    break;
                case 'comments':
                    $filter_condition = 'count_comment DESC';
                    $filter_count = 'COUNT(td.comment_item_id) AS count_comment,';
				$filter_join = "LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = d.download_id AND td.comment_type='D' AND td.comment_hidden='0'";
                    break;
                case 'ratings':
                    $filter_condition = 'sum_rating DESC';
                    $filter_count = 'IF(SUM(tr.rating_vote)>0, SUM(tr.rating_vote), 0) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes,';
				$filter_join = "LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = d.download_id AND tr.rating_type='D'";
                    break;
                case 'download':
                    $filter_condition = 'download_count DESC';
                    break;
                default:
                    $filter_condition = dbresult(dbquery("SELECT download_cat_sorting FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id='".intval($_GET['cat_id'])."'"),
                        0);
            }
            $sql = "SELECT d.*, dc.*,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				".(!empty($filter_count) ? $filter_count : '')."
				MAX(d.download_datestamp) as last_updated
				FROM ".DB_DOWNLOADS." d
				INNER JOIN ".DB_DOWNLOAD_CATS." dc ON d.download_cat=dc.download_cat_id
				LEFT JOIN ".DB_USERS." tu ON d.download_user=tu.user_id
				".(!empty($filter_join) ? $filter_join : '')."
				".(multilang_table("DL") ? " WHERE download_cat_language='".LANGUAGE."' AND " : " WHERE ")." ".groupaccess('download_visibility')."
				AND download_cat = '".intval($_GET['cat_id'])."'
				GROUP BY d.download_id
				ORDER BY ".(!empty($filter_condition) ? $filter_condition : "dc.download_cat_sorting")."
				LIMIT ".intval($_GET['rowstart']).",".intval($dl_settings['download_pagination']);

            $result = dbquery($sql);
            $info['download_rows'] = dbrows($result);

        }

        \PHPFusion\OpenGraphDownloads::ogDownloadCat($_GET['cat_id']);

    } else {

        set_title($locale['download_1000']);

        /**
         * Everyone's Download Posts
         */

        $info['download_max_rows'] = dbcount("('download_id')", DB_DOWNLOADS, groupaccess('download_visibility'));
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['download_max_rows']) ? $_GET['rowstart'] : 0;
        if ($info['download_max_rows'] > 0) {

            $download_query = "SELECT d.*, dc.*,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				".(!empty($filter_count) ? $filter_count : '')."
				max(d.download_datestamp) as last_updated
				FROM ".DB_DOWNLOADS." d
				INNER JOIN ".DB_DOWNLOAD_CATS." dc ON d.download_cat=dc.download_cat_id
				LEFT JOIN ".DB_USERS." tu ON d.download_user=tu.user_id
				".(!empty($filter_join) ? $filter_join : '')."
				".(multilang_table("DL") ? "WHERE dc.download_cat_language = '".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_visibility')."
				".$condition."
				GROUP BY d.download_id
				ORDER BY ".($filter_condition ? $filter_condition : "dc.download_cat_sorting")."
				LIMIT ".intval($_GET['rowstart']).",".intval($dl_settings['download_pagination']);

            $result = dbquery($download_query);
            $info['download_rows'] = dbrows($result);
        }
    }
}

if (!empty($info['download_max_rows']) && ($info['download_max_rows'] > $dl_settings['download_pagination']) && !isset($_GET['download_id'])) {

    $page_nav_link = "";
    if (!empty($_GET['cat_id']) && isnum($_GET['cat_id'])) {
        $page_nav_link = INFUSIONS."downloads/downloads.php?cat_id=".$_GET['cat_id']."&amp;";
    } elseif (!empty($_GET['author']) && isnum($_GET['author'])) {
        $page_nav_link = INFUSIONS."downloads/downloads.php?author=".$_GET['author']."&amp;";
    }

    $info['download_nav'] = makepagenav($_GET['rowstart'], $dl_settings['download_pagination'],
        $info['download_max_rows'], 3, $page_nav_link);
}

if (!empty($info['download_rows'])) {
    while ($data = dbarray($result)) {
    	$data['count_comment'] = !empty($data['count_comment']) ? $data['count_comment'] : count_db($data['download_id'], 'D');
    	$data['count_votes'] = !empty($data['count_votes']) ? $data['count_votes'] : sum_db($data['download_id'], 'D');
    	$data['sum_rating'] = !empty($data['sum_rating']) ? $data['sum_rating'] : rating_db($data['download_id'], 'D');
        $data = array_merge($data, parseInfo($data));
        $info['download_item'][$data['download_id']] = $data;
    }
}
$author_result = dbquery("SELECT b.download_title, b.download_user, count(b.download_id) as download_count, u.user_id, u.user_name, u.user_status
				FROM ".DB_DOWNLOADS." b
				INNER JOIN ".DB_USERS." u on (b.download_user = u.user_id)
				GROUP BY b.download_user ORDER BY b.download_user ASC
				");
if (dbrows($author_result)) {
    while ($at_data = dbarray($author_result)) {
        $active = isset($_GET['author']) && $_GET['author'] == $at_data['download_user'] ? 1 : 0;
        $info['download_author'][$at_data['download_user']] = array(
            'title'  => $at_data['user_name'],
            'link'   => INFUSIONS."downloads/downloads.php?author=".$at_data['download_user'],
            'count'  => $at_data['download_count'],
            'active' => $active
        );
    }
}

render_downloads($info);
require_once THEMES."templates/footer.php";
function rating_db($id, $type) {
            $count_db = dbarray(dbquery("SELECT
				IF(SUM(rating_vote)>0, SUM(rating_vote), 0) AS sum_rating
				FROM ".DB_RATINGS."
				WHERE rating_item_id='".$id."' AND rating_type='".$type."'
             "));
return $count_db['sum_rating'];
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
/**
 * Returns Downloads Category Hierarchy Tree Data
 *
 * @return array
 */
function get_downloadCat() {
    return \PHPFusion\Downloads\Functions::get_downloadCatsData();
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
    return \PHPFusion\Downloads\Functions::get_download_image_path($image, $thumb1, $hires);
}

function downloadCats_breadcrumbs($hierarchy_index) {
    \PHPFusion\Downloads\Functions::downloadCats_breadcrumbs($hierarchy_index);
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
        $hiRes_image_path = get_download_image_path($data['download_image'], $data['download_image_thumb'], TRUE);
        $lowRes_image_path = get_download_image_path($data['download_image'], $data['download_image_thumb'], FALSE);
        $download_image = "<a href='".INFUSIONS."downloads/downloads.php?download_id=".$data['download_id']."'>".thumbnail($lowRes_image_path,
                '100px')."</a>";
    }
    return array(
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
        'download_file_link'         => file_exists(DOWNLOADS.'/files/'.$data['download_file']) ? INFUSIONS."downloads/downloads.php?file_id=".$data['download_id'] : '',
    );
}
