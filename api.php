<?php

use PHPFusion\Rest\Router;

// 3. Identify Route & Method
$route = isset($_GET['route']) ? '/' . trim($_GET['route'], '/') : '/';
$method = $_SERVER['REQUEST_METHOD'];

// 4. Load the Route Switchboard (The firewall logic)
if (file_exists(INCLUDES . "route_include.php")) {
	require_once INCLUDES . "route_include.php";
}

// 5. Dispatch to the Controller
try {
	Router::dispatch($method, $route);
} catch (\Exception $e) {
	http_response_code(500);
	echo json_encode(["error" => $e->getMessage()]);
}