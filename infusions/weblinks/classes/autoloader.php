<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusions/weblinks/classes/autoloader.php
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
require_once INCLUDES."infusions_include.php";

spl_autoload_register(function ($className) {
    $autoload_register_paths = [
		"PHPFusion\\Weblinks\\weblinksBackendModel"                 => WEBLINKS_CLASSES."Model/weblinksBackendModel.class.php",
		"PHPFusion\\Weblinks\\weblinksBackendController"            => WEBLINKS_CLASSES."Controller/weblinksBackendController.class.php",
		"PHPFusion\\Weblinks\\Subcontroller\\weblinksBackendItemsController"       => WEBLINKS_CLASSES."Controller/Subcontroller/items.class.php",
		"PHPFusion\\Weblinks\\Subcontroller\\weblinksBackendCategoriesController"  => WEBLINKS_CLASSES."Controller/Subcontroller/categories.class.php",
		"PHPFusion\\Weblinks\\Subcontroller\\weblinksBackendSubmissionsController" => WEBLINKS_CLASSES."Controller/Subcontroller/submissions.class.php",
		"PHPFusion\\Weblinks\\Subcontroller\\weblinksBackendSettingsController"    => WEBLINKS_CLASSES."Controller/Subcontroller/settings.class.php",    //PHPFusion\\Weblinks\\Subview\\weblinksBackendSettingsView
		"PHPFusion\\Weblinks\\Subview\\weblinksBackendSettingsView" => WEBLINKS_CLASSES.'View/subview/settings.class.php',
		"PHPFusion\\Weblinks\\weblinksBackendView"                  => WEBLINKS_CLASSES."View/weblinksBackendView.class.php",
	];
    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});