<?php
namespace PHPFusion\Administration\Api\Controllers;

use PHPFusion\Administration\Api\Services\SettingsService;
use PHPFusion\Rest\Router;

class SettingsController {
	/**
	 * This handles the POST request from your Mobile App
	 */
	public function update($request) {
		
		// 1. In a mobile request, data comes in the body
		$data = $request['body'] ?? [];
		
		// 2. Instantiate your Service
		$settingsService = new SettingsService();
		
		try {
			// 3. Execute the shared logic
			$settingsService->updateMainSettings($data);
			
			return [
				'status' => 'success',
				'message' => 'Settings updated successfully via Mobile API'
			];
			
		} catch (\Exception $e) {
			
			return Router::errorResponse($e->getMessage(), 400);
		}
	}
}