<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: viewpage.php
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
require_once "maincore.php";
require_once THEMES."templates/header.php";
require_once INCLUDES."comments_include.php";
require_once INCLUDES."ratings_include.php";
require_once THEMES."templates/global/custompage.php";

$locale = fusion_get_locale("", LOCALE.LOCALESET."custom_pages.php");

$cp_data = array();

if (!isset($_GET['page_id']) || !isnum($_GET['page_id'])) {
    redirect("index.php");
}
$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

$cp_result = dbquery("SELECT * FROM ".DB_CUSTOM_PAGES."
            WHERE page_id='".intval($_GET['page_id'])."' AND ".groupaccess('page_access')."
            ".(multilang_table("CP") ? "AND ".in_group("page_language", LANGUAGE) : ""));

$info = array(
    "title" => "",
    "error" => "",
    "body" => "",
    "count" => 0,
    "pagenav" => "",
    "show_comments" => "",
    "show_ratings" => "",
);

if (dbrows($cp_result) > 0) {

    $cp_data = dbarray($cp_result);

    add_to_title($locale['global_200'].$cp_data['page_title']);

    add_breadcrumb(array('link'=>BASEDIR."viewpage.php?page_id=".$_GET['page_id'], 'title'=>$cp_data['page_title']));

    if ($cp_data['page_keywords'] !=="") { set_meta("keywords", $cp_data['page_keywords']); }

    $info['title'] = $cp_data['page_title'];
	ob_start();
	if (fusion_get_settings("allow_php_exe")) {
		eval("?>".stripslashes($cp_data['page_content'])."<?php ");
	} else {
		echo "<p>".parse_textarea($cp_data['page_content'])."</p>\n";
	}
	$eval = ob_get_contents();
	ob_end_clean();
    $info['body'] = preg_split("/<!?--\s*pagebreak\s*-->/i", (fusion_get_settings("tinymce_enabled") ? $eval : nl2br($eval)));
    $info['count'] = count($info['body']);

    if ($info['count'] > 0) {
        if (isset($_GET['rowstart']) && $_GET['rowstart'] > $info['count']) {
            redirect(BASEDIR."viewpage.php?page_id=".$_GET['page_id']);
        }
        $info['pagenav'] = makepagenav($_GET['rowstart'], 1, $info['count'], 1, BASEDIR."viewpage.php?page_id=".$_GET['page_id']."&amp;")."\n";
    }

    if ($cp_data['page_allow_comments']) {
        ob_start();
        showcomments("C", DB_CUSTOM_PAGES, "page_id", $_GET['page_id'], BASEDIR."viewpage.php?page_id=".$_GET['page_id']);
        $info['show_comments'] = ob_get_contents();
        ob_end_clean();
    }

    if ($cp_data['page_allow_ratings']) {
        ob_start();
        showratings("C", $_GET['page_id'], BASEDIR."viewpage.php?page_id=".$_GET['page_id']);
        $info['show_ratings'] = ob_get_contents();
        ob_end_clean();
    }
    unset($cp_data);

} else {
	add_to_title($locale['global_200'].$locale['401']);
    $info['title'] = $locale['401'];
    $info['error'] = $locale['402'];
}

render_customPage($info);

require_once THEMES."templates/footer.php";