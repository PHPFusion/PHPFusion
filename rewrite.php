<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rewrite.php
| Author: Ankur Thakur/Hien
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
define("IN_PERMALINK", TRUE);
require_once dirname(__FILE__)."/maincore.php";
require_once CLASSES."Rewrite.class.php";
// Starting Rewrite Object
$seo_rewrite = new Rewrite();
$seo_rewrite->rewritePage();
$filepath = $seo_rewrite->getFilePath();

if ($filepath != "") {
	// Set FUSION_SELF to File path
	$current_page = str_replace($settings['site_path'], "", $_SERVER['PHP_SELF']);
	if (preg_match("/\.php/", basename($filepath))) {
		// If it is a file
		/* DEVELOPMENT IN PROGRESS. Don't Update */
		/* Constant Debugs Output @ news.php
		TRUE_PHP_SELF = news.php
		$_SERVER['QUERY_STRING'] = 2;
		PERMALINK_CURRENT_PATH = ../../ definitions.
		ROOT is also ../../
		$filepath = news.php
		 */
		//define("FUSION_SELF", FUSION_ROOT.basename($filepath)); // form paths
		define("FUSION_SELF", FUSION_ROOT.$current_page); // form paths - this works for shoutbox.
		//define("FUSION_SELF", BASEDIR.basename($filepath));
		//define("FUSION_SELF", $current_page);
		//print_p(FUSION_SELF);
	} else {
		// If it is a directory that actually exists(like /forum/)
		define("FUSION_SELF", "index.php");
		//print_p(FUSION_SELF);
	}
	// Define FUSION_QUERY
	define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");
	// Define START_PAGE for Panels
	define("TRUE_PHP_SELF", $current_page);
	define("START_PAGE", TRUE_PHP_SELF.($_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING'] : ""));
	//define("FUSION_SELF", TRUE_PHP_SELF);
	// Include the corresponding File
	define("FUSION_REQUEST", START_PAGE);
	if ($_SERVER['PHP_SELF'] == $settings['opening_page']) {
		include_once $settings['opening_page'];
	} else {
		include_once $filepath;
	}
} else {
redirect(BASEDIR."error.php?code=404");
}
if (!defined("FUSION_SELF")) {
	define("FUSION_SELF", basename($_SERVER['PHP_SELF']));
}
?>