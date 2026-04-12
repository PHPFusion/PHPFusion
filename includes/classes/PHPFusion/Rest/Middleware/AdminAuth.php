<?php
namespace PHPFusion\Rest\Middleware;

/**
 * PHPFusion 10 - Admin Authentication Middleware
 * Ensures the requester has valid admin credentials and the required rights.
 */
class AdminAuth {
	
	/**
	 * Handle the incoming request
	 * * @param array $request The request data from the Router
	 * @return bool|array Returns true to proceed, or an error array to block
	 */
	public static function handle($request) {
		
		// 1. Check if the user is logged in as an Admin
		// iADMIN is a core PHPFusion constant defined in maincore.php
		if (!defined('iADMIN') || !iADMIN) {
			return [
				'status'  => 'error',
				'code'    => 401,
				'message' => 'Unauthorized: Admin session required.'
			];
		}
		
		/**
		 * 2. Check for specific Admin Rights
		 * Since your route is admin/settings/update, we check for 'S' rights.
		 * You can expand this logic to be dynamic based on the route if needed.
		 */
		if (!checkrights("S")) {
			return [
				'status'  => 'error',
				'code'    => 403,
				'message' => 'Forbidden: You do not have the required "S" rights.'
			];
		}
		
		// 3. Optional: Verify PHPFusion Password (for high-security actions)
		// If you want to force the "Admin Password" check via API:
		/*
		if (!isset($_SESSION['fusion_admin_password'])) {
			 return [
				'status' => 'error',
				'code' => 403,
				'message' => 'Admin password verification required.'
			];
		}
		*/
		
		return true; // Access Granted
	}
}