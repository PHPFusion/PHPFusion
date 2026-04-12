<?php
/**
 * PHPFusion 10 - API Route Loader (Separated)
 */

// Get the current route from the GET parameter
$current_route = $_GET['route'] ?? '';
if (isset($route)) {
	$current_route = $route;
}
$current_route = trim($current_route, '/');

// --- SECTION A: ADMINISTRATION ROUTES ---
// Only load these if the route explicitly targets the admin namespace
// This works
if (str_starts_with($current_route, 'admin/') && file_exists(ADMIN."routes.php")) {

	require_once ADMIN . "routes.php";
}
// --- SECTION B: PUBLIC & INFUSION ROUTES ---
else {
	// Load Core Public Routes (e.g., login, site info)
	if (file_exists(INCLUDES . "api/routes.php")) {
		require_once INCLUDES . "api/routes.php";
	}
	
	// Load Public Infusion APIs (e.g., Student Portal, Syllabus info)
	$enabled_infusions = fusion_get_enabled_infusions();
	foreach ($enabled_infusions as $infusion) {
		$public_route_file = INFUSIONS . $infusion['inf_folder'] . "/api/routes_public.php";
		if (file_exists($public_route_file)) {
			require_once $public_route_file;
		}
	}
}