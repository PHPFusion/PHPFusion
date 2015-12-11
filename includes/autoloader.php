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
 * Loads classes from ClassName.php
 */
spl_autoload_register(function ($className) {
	$baseDir = __DIR__.'/classes/';
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
	$fullPath = $baseDir.$path.'.php';
	if (is_file($fullPath)) {
		require $fullPath;
	}
});
/*
 * Autoloader for compatibility reason
 *
 * It loads only classes from ClassName.class.php in global namespace
 */
spl_autoload_register(function ($className) {
	if (strpos($className, '\\') !== FALSE) {
		return;
	}
	$baseDir = __DIR__.'/classes/';
	$fullPath = $baseDir.$className.'.class.php';
	if (is_file($fullPath)) {
		require $fullPath;
	}
});
