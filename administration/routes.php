<?php
use PHPFusion\Administration\Api\Controllers\SettingsController;
use PHPFusion\Rest\Middleware\AdminAuth;
use PHPFusion\Rest\Router;

// Strictly Admin-only Group
Router::group(['prefix' => 'admin', 'middleware' => [AdminAuth::class]], function() {
	Router::post('/settings/update', [SettingsController::class, 'update']);
});