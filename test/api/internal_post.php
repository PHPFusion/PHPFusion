<?php
require_once __DIR__.'/../../maincore.php';
require_once INCLUDES.'api_autoloader.php';

// Use the Service Namespace
use PHPFusion\Administration\Api\Services\SettingsService;

echo "<h3>Internal Service Test</h3>";

try {
	$service = new SettingsService();
	$status = $service->updateMainSettings([
		'sitename' => 'Elite Tuition (Service Test)'
	]);
	
	echo "Update Successful: " . ($status ? 'Yes' : 'No');
} catch (Exception $e) {
	echo "Error: " . $e->getMessage();
}