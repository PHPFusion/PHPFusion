<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: site_links.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess("SL");
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/sitelinks.php";
$site_links = new \PHPFusion\SiteLinks();
$edit = isset($_GET['link_id']) ? $site_links->verify_edit($_GET['link_id']) : 0;
$master_title['title'][] = $locale['SL_0001'];
$master_title['id'][] = "links";
$master_title['icon'][] = '';
$master_title['title'][] = $edit ? $locale['SL_0011'] : $locale['SL_0010'];
$master_title['id'][] = "link_form";
$master_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : "fa fa-plus-square m-r-10";

$section         = "links";
$allowed_section = array("links", "link_form");
if (isset($_GET['section']) && in_array($_GET['section'], $allowed_section)) {
    $section = $_GET['section'];
}

opentable($locale['SL_0012']);
echo opentab($master_title, $section, 'link', TRUE);
if (isset($_GET['section']) && $_GET['section'] == "nform") {
	$site_links->menu_form();
} else {
	$site_links->menu_listing();
}
echo closetab();
closetable();
require_once THEMES."templates/footer.php";