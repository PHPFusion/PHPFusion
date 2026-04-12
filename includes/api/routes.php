<?php
use PHPFusion\Rest\Router;

// These are accessible without the 'admin/' prefix
// URL: api.php?route=site/status
Router::get('/site/status', function() {
	return [
		'status' => 'online',
		'version' => '10.0',
		'region' => 'Sabah'
	];
});

// URL: api.php?route=login
Router::post('/login', [PHPFusion\Rest\Controllers\AuthController::class, 'login']);