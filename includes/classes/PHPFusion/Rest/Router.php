<?php
namespace PHPFusion\Rest;

class Router {
	
	private static $routes = [];
	private static $groupStack = []; // Added for grouping
	
	/**
	 * Register standard methods
	 */
	public static function get($uri, $action) { self::addRoute('GET', $uri, $action); }
	public static function post($uri, $action) { self::addRoute('POST', $uri, $action); }
	public static function put($uri, $action) { self::addRoute('PUT', $uri, $action); }
	public static function delete($uri, $action) { self::addRoute('DELETE', $uri, $action); }
	
	/**
	 * NEW: Grouping Helper
	 * Allows: Router::group(['prefix' => 'admin', 'middleware' => [AdminAuth::class]], function() { ... });
	 */
	public static function group($attributes, $callback) {
		self::$groupStack[] = $attributes;
		$callback();
		array_pop(self::$groupStack);
	}
	
	/**
	 * Internal helper to format and store routes
	 */
	private static function addRoute($method, $uri, $action) {
		$uri = '/' . trim($uri, '/');
		
		// Apply Group Attributes (Prefix & Middleware)
		if (!empty(self::$groupStack)) {
			foreach (self::$groupStack as $group) {
				if (isset($group['prefix'])) {
					$uri = '/' . trim($group['prefix'], '/') . $uri;
				}
				if (isset($group['middleware'])) {
					// Merge group middleware with specific route middleware
					$currentMiddleware = isset($action['middleware']) ? $action['middleware'] : [];
					// Handle case where $action was a simple array [Class, Method]
					if (is_array($action) && !isset($action['controller']) && !is_callable($action)) {
						$action = ['controller' => $action, 'middleware' => array_merge($group['middleware'], $currentMiddleware)];
					} else {
						$action['middleware'] = array_merge($group['middleware'], $currentMiddleware);
					}
				}
			}
		}
		
		// Normalize structure
		if (is_array($action) && !isset($action['controller']) && !is_callable($action)) {
			$normalizedAction = ['controller' => $action, 'middleware' => []];
		} elseif (is_callable($action)) {
			$normalizedAction = ['controller' => $action, 'middleware' => []];
		} else {
			$normalizedAction = $action;
		}
		
		self::$routes[$method][$uri] = $normalizedAction;
	}
	
	/**
	 * Dispatch the request
	 */
	public static function dispatch($method, $uri) {
		$method = strtoupper($method);
		$uri = '/' . trim($uri, '/');
		
		if (!isset(self::$routes[$method][$uri])) {
			return self::errorResponse("Endpoint $uri not found", 404);
		}
		
		$action = self::$routes[$method][$uri];
		$requestData = self::getInputs($method);
		
		try {
			// Handle Middleware
			if (isset($action['middleware']) && is_array($action['middleware'])) {
				foreach ($action['middleware'] as $middlewareClass) {
					$result = $middlewareClass::handle($requestData);
					if (is_array($result) && isset($result['status']) && $result['status'] === 'error') {
						return self::errorResponse($result['message'], $result['code'] ?? 403);
					}
					if (is_array($result)) { $requestData = array_merge($requestData, $result); }
				}
			}
			
			$target = isset($action['controller']) ? $action['controller'] : $action;
			
			if (is_array($target)) {
				$controller = new $target[0]();
				return $controller->{$target[1]}($requestData);
			} else if (is_callable($target)) {
				return $target($requestData);
			}
		} catch (\Exception $e) {
			return self::errorResponse($e->getMessage(), 500);
		}
	}
	
	/**
	 * NEW: Static Input Helper
	 * Use this in your controllers: Router::input('user_id');
	 */
	public static function input($key, $default = null) {
		$data = self::getInputs($_SERVER['REQUEST_METHOD']);
		if (isset($data['body'][$key])) return $data['body'][$key];
		if (isset($data['query'][$key])) return $data['query'][$key];
		return $default;
	}
	
	private static function getInputs($method) {
		$inputs = ['query' => $_GET, 'body' => []];
		if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
			$json = json_decode(file_get_contents('php://input'), true);
			$inputs['body'] = $json ?: $_POST;
		}
		return $inputs;
	}
	
	public static function errorResponse($message, $code) {
		http_response_code($code);
		
		return json_encode(['status' => 'error', 'code' => $code, 'message' => $message]);
	}
}