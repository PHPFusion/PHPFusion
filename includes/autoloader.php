<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/autoloader.php
| Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/*
 * Temporary Autoloader for only core classes until we implement a real 
 * PSR compatible file structure for the classes
 */

spl_autoload_register(function ($className) {
	$baseDir = __DIR__.'/classes/';

	$extensions = array(
		'class.php', 'php'
	);
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
	
	foreach ($extensions as $extension) {
		$fullPath = $baseDir.$path.'.'.$extension;
		if (is_file($fullPath)) {
			require $fullPath;
			break;
		}
	}
	
});

