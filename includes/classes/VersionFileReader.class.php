<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: VersionFileReader.class.php
| Author: Hans Kristian Flaatten (Starefossen)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

class VersionFileReader {

	private $_currentVersion 						= false;
	private $_newestVersion 						= false;
	private $_versionURL;
	
	private $_errorNumber 							= 0;
	private $_errorMessage 							= "OK";
	
	private $_jsonCompabilityMode 					= true;
	private $_jsonVersionServer 					= "http://update.php-fusion.co.uk/";
	private $_jsonFirstObject 						= "version";
	
	public function __construct() { 
		$this->_setVersionURL();
		$this->_getVersionFile();
	}

	// Get errors
	public function getError() {
		return array("number" => $this->_errorNumber, "message" => $this->_errorMessage);
	}

	// Get current version
	public function getCurrentVersion() {
		return $this->_currentVersion;
	}
	
	// Get newest version
	public function getNewestVersion() {
		return $this->_newestVersion;
	}
	
	// Set json compability mode
	public function setCompabilityMode($compabilityMode) {
		if ($compabilityMode) {
			$this->_jsonCompabilityMode = true;
		} else {
			$this->_jsonCompabilityMode = false;
		}
	}
	
	// Set json version server
	public function setJsonVersionServer($jsonVersionServer) {
		if ($jsonVersionServer != "") {
			$this->_jsonVersionServer = $jsonVersionServer;
		}
	}
	
	// Set json first object
	public function setJsonFirstObject($jsonFirstObject) {
		if ($jsonFirstObject != "") {
			$this->_jsonFirstObject = $jsonFirstObject;
		}
	}
	
	// Set version URL
	private function _setVersionURL() {
		global $settings;
		
		$version = explode(".", $settings['version']);
		$this->_versionURL = $this->_jsonVersionServer."checker-".$version[0].$version[1].".json";
	}
	
	// Read the version file
	private function _getVersionFile() {
		$file = new RemoteFileReader($this->_versionURL);
		
		// Save any error
		$error = $file->getError();
		$this->_errorNumber = $error['number'];
		$this->_errorMessage = $error['message'];

		if ($this->_errorNumber == 0) {
			$this->_getJsonArray($file->getContent());
		}
	}

	// Jeson object to array
	private function _getJsonArray($content) {
		global $settings;
		
		// Make JSON object
		if ($this->_jsonCompabilityMode) { $content = substr($content, 1, -1); }
		$jsonObject = @json_decode($content);
		
		// Render the array
		if (is_object($jsonObject)) {
			$i = 0; $c = false; $versionsArr = array();
			$firstObject = $this->_jsonFirstObject;
			foreach ($jsonObject->$firstObject as $version) {
				$versionsArr[$i] = array();
				foreach ($version as $key => $data) {
					if (in_array($key, array("number", "severity", "meintenance", "date", "description")) && isnum($data)) {
						$versionsArr[$i][$key] = $data;
						if ($key == "number" && $data == str_replace(".", "", $settings['version'])) { $c = $i; }
					}
				}
				if ($i == 0) { $this->_newestVersion = $versionsArr[$i]; }
				if ($c == $i) { $this->_currentVersion = $versionsArr[$i]; }
				$i++;
			}
		} else {
			$this->_errorNumber = 600;
			$this->_errorMessage = "Version file is not a compatible json file.";
		}
	}
	
	// Get severity from number
	public static function getSeverity($severity) {
		$severities = array(
			1 => "High",
			2 => "Medium",
			3 => "Low"
		);
		if (isset($severities[$severity])) {
			return $severities[$severity];
		} else {
			return "N/A";
		}
	}
}
?>