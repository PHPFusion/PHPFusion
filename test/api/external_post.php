<?php

use PHPFusion\Rest\Router;

require_once __DIR__ . "/../../maincore.php";

run_router();

function run_router()
{
	// This is required for the router to stop infinite loop
	session_write_close();
	// You need to login to PHPFusion Administration for this to work
	$result = fusion_api_request('admin/settings/update', 'POST', [
		'sitename'  => 'PHPFusion Mobile (API Test)',
		'siteemail' => 'test@phpfusion.com',
	]);
	echo "<pre>";
	print_r($result);
	echo "</pre>";
}

function test_router()
{
	require_once INCLUDES . 'api_autoloader.php';
// 1. Force HTML mode so we can read the debug breadcrumbs
	header('Content-Type: text/html; charset=UTF-8');

// 2. Setup the "Fake" Request environment
	$request_route = 'admin/settings/update';
	$method = 'POST';
	$route = '/' . trim($request_route, '/');
	
	echo "<h2>PHPFusion 10 Router Debugger</h2>";

// 3. Load the Route Switchboard (Crucial step)
	if (file_exists(INCLUDES . "route_include.php")) {
		require_once INCLUDES . "route_include.php";
		echo "<p style='color:green;'>✓ route_include.php loaded successfully.</p>";
	} else {
		die("<p style='color:red;'>✗ Error: route_include.php not found at " . INCLUDES . "</p>");
	}
	
	echo "<h4>Registered Routes in Memory:</h4><pre>";
// Use reflection to peek at the private static $routes if you don't have a getter
	$reflection = new ReflectionProperty('\PHPFusion\Rest\Router', 'routes');
	$reflection->setAccessible(TRUE);
	print_r($reflection->getValue());
	echo "</pre>";

// 2. CRITICAL: Release the session lock!
// This saves the session and closes the file so api.php can read it.
//session_write_close();
	
	echo "<h3>API Request Result (External)</h3>";
// 4. Execution & Capture
	echo "Attempting to dispatch: <strong>$method $route</strong><hr>";
	
	try {
		// Catch any accidental 'echo' or 'print' inside the Controller/Service
		ob_start();
		
		$response = Router::dispatch($method, $route);
		
		$unexpected_output = ob_get_clean();
		
		
		echo "<h3>Router Response:</h3>";
		echo "<pre style='background:#eee; padding:10px;'>";
		print_r($response);
		echo "</pre>";
		
		if ($unexpected_output) {
			echo "<h3 style='color:orange;'>Warning: Unexpected Output</h3>";
			echo "<p>Something echoed text before the Router finished:</p>";
			echo "<pre style='background:#fff3cd; padding:10px;'>$unexpected_output</pre>";
		}
		
	} catch (\Exception $e) {
		// If the Router crashes, we see exactly why
		echo "<h3 style='color:red;'>Dispatch Error:</h3>";
		echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
		echo "<h4>Stack Trace:</h4>";
		echo "<pre style='font-size:11px; color:#666;'>" . $e->getTraceAsString() . "</pre>";
	}
	
}