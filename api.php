<?php

use PHPFusion\Rest\Router;
require_once __DIR__."/maincore.php";
require_once INCLUDES."api_autoloader.php";

// 3. Identify Route & Method
$route = isset($_GET['route']) ? '/'.trim($_GET['route'], '/') : '/';
$method = $_SERVER['REQUEST_METHOD'];

// 4. Load the Route Switchboard (The firewall logic)
if (file_exists(INCLUDES . "route_include.php")) {
	require_once INCLUDES . "route_include.php";
}

// 5. Dispatch to the Controller
try {
	
	$response = Router::dispatch($method, $route);
	// Send the correct header
	header('Content-Type: application/json');
	// Echo the JSON encoded response
	echo json_encode($response);
	
} catch (\Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => $e->getMessage()]);
}