<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: viewpage.php
| Author: Nick Jones (Digitanium)
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
include LOCALE.LOCALESET."custom_pages.php";

if (!isset($_GET['page_id']) || !isnum($_GET['page_id'])) { redirect("index.php"); }
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }

$cp_result = dbquery("SELECT * FROM ".DB_CUSTOM_PAGES." WHERE page_id='".$_GET['page_id']."'");
if (dbrows($cp_result)) {
	$cp_data = dbarray($cp_result);
	add_to_title($locale['global_200'].$cp_data['page_title']);
	echo "<!--custompages-pre-content-->\n";
	opentable($cp_data['page_title']);
	if (checkgroup($cp_data['page_access'])) {
		ob_start();
		eval("?>".stripslashes($cp_data['page_content'])."<?php ");
		$custompage = ob_get_contents();
		ob_end_clean();
		$custompage = preg_split("/<!?--\s*pagebreak\s*-->/i", $custompage);
		$pagecount = count($custompage);
		echo $custompage[$_GET['rowstart']];
	} else {
		echo "<div class='admin-message' style='text-align:center'><br /><img style='border:0px; vertical-align:middle;' src ='".BASEDIR."images/warn.png' alt=''/><br /> ".$locale['400']."<br /><a href='index.php' onclick='javascript:history.back();return false;'>".$locale['403']."</a>\n<br /><br /></div>\n";
	}
} else {
	add_to_title($locale['global_200'].$locale['401']);
	echo "<!--custompages-pre-content-->\n";
	opentable($locale['401']);
	echo "<div style='text-align:center'><br />\n".$locale['402']."\n<br /><br /></div>\n";
}
closetable();
if (isset($pagecount) && $pagecount > 1) {
    echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 1, $pagecount, 3, FUSION_SELF."?page_id=".$_GET['page_id']."&amp;")."\n</div>\n";
}
echo "<!--custompages-after-content-->\n";
if (dbrows($cp_result) && checkgroup($cp_data['page_access'])) {
	if ($cp_data['page_allow_comments']) { showcomments("C", DB_CUSTOM_PAGES, "page_id", $_GET['page_id'],FUSION_SELF."?page_id=".$_GET['page_id']); }
	if ($cp_data['page_allow_ratings']) { showratings("C", $_GET['page_id'], FUSION_SELF."?page_id=".$_GET['page_id']); }
}

require_once THEMES."templates/footer.php";
?>
