<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: footer.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

require_once INCLUDES."footer_includes.php";
require_once THEMES."templates/panels.php";

// Error handling site front
if (!defined('ADMIN_PANEL')) {
	if (iADMIN && checkrights("ERRO") && count($_errorHandler) > 0) {
		echo "<div class='admin-message'>".str_replace("[ERROR_LOG_URL]", ADMIN."errors.php".$aidlink, $locale['err_101'])."</div>\n";
	}
}

define("CONTENT", ob_get_clean());

require_once __DIR__.'/cron.php';

if (!isset($fusion_jquery_tags)) {
    $fusion_jquery_tags = '';
}

// Load layout
if (defined('ADMIN_PANEL')) {
    require_once __DIR__.'/admin_layout.php';
} else {
    require_once __DIR__.'/layout.php';
}

// Catch the output
$output = ob_get_contents();
if (ob_get_length() !== FALSE) {
    ob_end_clean();
}

// Do the final output manipulation
$output = handle_output($output);

// Search in output and replace normal links with SEF links
if (!defined('ADMIN_PANEL')) {
    if (fusion_get_settings('site_seo')) {
        \PHPFusion\Rewrite\Permalinks::getPermalinkInstance()->handle_url_routing($output);
        $output = \PHPFusion\Rewrite\Permalinks::getPermalinkInstance()->getOutput($output);
    }
}

if (isset($permalink)) {
    unset($permalink);
}

// Output the final complete page content
echo $output;

// Flush
if ((ob_get_length() > 0)) { 
    ob_end_flush();
}

dbclose();