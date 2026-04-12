<?php
namespace PHPFusion\Rest;

interface Middleware {
	/**
	 * @param array $request The standardized request data
	 * @return bool|array Returns true to proceed, or an error array to stop.
	 */
	public static function handle($request);
}