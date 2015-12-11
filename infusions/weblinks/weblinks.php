<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks.php
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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_WEBLINKS)) { redirect(BASEDIR."error.php?code=404"); }

require_once THEMES."templates/header.php";
require_once INCLUDES."infusions_include.php";

if (file_exists(INFUSIONS."weblinks/locale/".LOCALESET."weblinks.php")) {
	include INFUSIONS."weblinks/locale/".LOCALESET."weblinks.php";
} else {
	include INFUSIONS."weblinks/locale/English/weblinks.php";
}

include INFUSIONS."weblinks/templates/weblinks.php";

$wl_settings = get_settings("weblinks");

if (!isset($_GET['weblink_id']) || !isset($_GET['weblink_cat_id'])) {
	set_title($locale['400']);
}

if (isset($_GET['weblink_id']) && isnum($_GET['weblink_id'])) {
	$res = 0;
	$data = dbarray(dbquery("SELECT weblink_url,weblink_cat, weblink_visibility FROM ".DB_WEBLINKS." WHERE weblink_id='".intval($_GET['weblink_id'])."'"));
	if (checkgroup($data['weblink_visibility'])) {
		$res = 1;
		$result = dbquery("UPDATE ".DB_WEBLINKS." SET weblink_count=weblink_count+1 WHERE weblink_id='".intval($_GET['weblink_id'])."'");
		redirect($data['weblink_url']);
	}
	if ($res == 0) {
		redirect(FUSION_SELF);
	}
}

$weblink_cat_index = dbquery_tree(DB_WEBLINK_CATS, 'weblink_cat_id', 'weblink_cat_parent');

add_breadcrumb(array('link' => INFUSIONS.'weblinks/weblinks.php', 'title' => $locale['400']));

if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
	$info = array();
	$info['item'] = array();

	$result = dbquery("SELECT weblink_cat_name, weblink_cat_sorting FROM
	".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."' AND" : "WHERE")." weblink_cat_id='".intval($_GET['cat_id'])."'");

	if (dbrows($result) != 0) {
		$cdata = dbarray($result);
		$info = $cdata;
		add_to_title($locale['global_201'].$cdata['weblink_cat_name']);
		weblink_cat_breadcrumbs($weblink_cat_index);
		add_to_meta("description", $cdata['weblink_cat_name']);
		$max_rows = dbcount("(weblink_id)", DB_WEBLINKS, "weblink_cat='".$_GET['cat_id']."' AND ".groupaccess('weblink_visibility'));
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0;
		if ($max_rows != 0) {
			$result = dbquery("SELECT weblink_id, weblink_name, weblink_description, weblink_datestamp, weblink_count
            FROM ".DB_WEBLINKS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('weblink_visibility')." AND weblink_cat='".intval($_GET['cat_id'])."' ORDER BY ".$cdata['weblink_cat_sorting']." LIMIT ".$_GET['rowstart'].",".$wl_settings['links_per_page']);
			$numrows = dbrows($result);
			$info['weblink_rows'] = $numrows;
			$info['page_nav'] = $max_rows > $wl_settings['links_per_page'] ? makepagenav($_GET['rowstart'], $wl_settings['links_per_page'], $max_rows, 3, INFUSIONS."weblinks/weblinks.php?cat_id=".$_GET['cat_id']."&amp;") : 0;
			if (dbrows($result) > 0) {
				while ($data = dbarray($result)) {
					$data['new'] = ($data['weblink_datestamp']+604800 > time()+($settings['timeoffset']*3600)) ? 1 : 0;
					$data['weblink'] = array(
						'link' => INFUSIONS."weblinks/weblinks.php?cat_id=".$_GET['cat_id']."&amp;weblink_id=".$data['weblink_id'],
						'name' => $data['weblink_name']
					);
					$info['item'][$data['weblink_id']] = $data;
				}
			}
		}
        render_weblinks_item($info);
	} else {
		redirect(FUSION_SELF);
	}
} else {

    /**
     * Main View
     * */

	$info['item'] = array();

    $result = dbquery("SELECT wc.weblink_cat_id, wc.weblink_cat_name, wc.weblink_cat_description, count(w.weblink_id) 'weblink_count'
	FROM ".DB_WEBLINK_CATS." wc
	LEFT JOIN ".DB_WEBLINKS." w on w.weblink_cat = wc.weblink_cat_id and ".groupaccess("weblink_visibility")."
	".(multilang_table("WL") ? "WHERE wc.weblink_cat_language='".LANGUAGE."'" : "")."
	GROUP BY wc.weblink_cat_id
	ORDER BY weblink_cat_name
	");

    $rows = dbrows($result);

    $info['weblink_cat_rows'] = $rows;

    if ($rows != 0) {
		while ($data = dbarray($result)) {
			$data['weblink_item'] = array(
				'link' => INFUSIONS."weblinks/weblinks.php?cat_id=".$data['weblink_cat_id'],
				'name' => $data['weblink_cat_name']
			);
			$info['item'][$data['weblink_cat_id']] = $data;
		}
	}
	render_weblinks($info);
}
require_once THEMES."templates/footer.php";

/**
 * Weblinks Category Breadcrumbs Generator
 * @param $forum_index
 */
function weblink_cat_breadcrumbs($weblink_cat_index) {
	global $locale;
	/* Make an infinity traverse */
	function breadcrumb_arrays($index, $id) {
		$crumb = & $crumb;
		if (isset($index[get_parent($index, $id)])) {
			$_name = dbarray(dbquery("SELECT weblink_cat_id, weblink_cat_name, weblink_cat_parent FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_id='".$id."'"));
			$crumb = array(
				'link' => INFUSIONS."weblinks/weblinks.php?cat_id=".$_name['weblink_cat_id'],
				'title' => $_name['weblink_cat_name']
			);
			if (isset($index[get_parent($index, $id)])) {
				if (get_parent($index, $id) == 0) {
					return $crumb;
				}
				$crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
				$crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
			}
		}
		return $crumb;
	}

	// then we make a infinity recursive function to loop/break it out.
	$crumb = breadcrumb_arrays($weblink_cat_index, $_GET['cat_id']);
	// then we sort in reverse.
	if (count($crumb['title']) > 1) {
		krsort($crumb['title']);
		krsort($crumb['link']);
	}
	if (count($crumb['title']) > 1) {
		foreach ($crumb['title'] as $i => $value) {
			add_breadcrumb(array('link' => $crumb['link'][$i], 'title' => $value));
			if ($i == count($crumb['title'])-1) {
				add_to_title($locale['global_201'].$value);
			}
		}
	} elseif (isset($crumb['title'])) {
		add_to_title($locale['global_201'].$crumb['title']);
		add_breadcrumb(array('link' => $crumb['link'], 'title' => $crumb['title']));
	}
}