<?php
spl_autoload_register(function ($className) {
	// 1. Break the class into parts based on namespace slashes
	// Example: PHPFusion\Administration\Api\Services\SettingsService
	$parts = explode('\\', $className);
	
	// 2. Remove the vendor name (PHPFusion) if it's the first part
	if ($parts[0] === 'PHPFusion') {
		array_shift($parts);
	}
	
	// 3. Reconstruct the relative path
	// Example: Administration/Api/Services/SettingsService.php
	$relativePath = implode(DIRECTORY_SEPARATOR, $parts) . '.php';
	
	// 4. Define our search mappings
	// We map the first segment of our remaining namespace to a specific directory
	$baseMap = [
		'Administration' => BASEDIR . 'administration' . DIRECTORY_SEPARATOR,
		'Rest'           => INCLUDES . 'classes' . DIRECTORY_SEPARATOR . 'PHPFusion' . DIRECTORY_SEPARATOR . 'Rest' . DIRECTORY_SEPARATOR,
		'Api'            => INCLUDES . 'classes' . DIRECTORY_SEPARATOR . 'Api' . DIRECTORY_SEPARATOR
	];
	
	// 5. Check which base directory to use
	$firstSegment = $parts[0];
	
	if (isset($baseMap[$firstSegment])) {
		// If the first segment is "Administration", we need to strip "Administration"
		// from the relative path because the folder name is already in the BASEDIR
		$cleanPath = implode(DIRECTORY_SEPARATOR, array_slice($parts, 1)) . '.php';
		$fullPath = $baseMap[$firstSegment] . $cleanPath;
		
		if (file_exists($fullPath)) {
			require_once $fullPath;
			return;
		}
	}
	
	// 6. Fallback: Generic check in includes/classes if no map match
	$fallbackPath = INCLUDES . 'classes' . DIRECTORY_SEPARATOR . $relativePath;
	if (file_exists($fallbackPath)) {
		require_once $fallbackPath;
	}
});