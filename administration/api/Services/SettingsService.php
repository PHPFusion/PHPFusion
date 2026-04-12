<?php
namespace PHPFusion\Administration\Api\Services;

class SettingsService {
	
	/**
	 * Update main system settings
	 * @param array $data Sanitized key-value pairs
	 * @return bool
	 * @throws \Exception
	 */
	public function updateMainSettings(array $data) {
		if (empty($data)) {
			throw new \Exception("No settings data provided.");
		}
		
		dbquery("BEGIN");
		try {
			foreach ($data as $name => $value) {
				// We use the standard PHPFusion dbquery with parameters
				$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:value WHERE settings_name=:name", [
					':value' => $value,
					':name'  => $name
				]);
				
				if (!$result) {
					throw new \Exception("Failed to update setting: $name");
				}
			}
			dbquery("COMMIT");
			return true;
		} catch (\Exception $e) {
			dbquery("ROLLBACK");
			throw $e;
		}
	}
}