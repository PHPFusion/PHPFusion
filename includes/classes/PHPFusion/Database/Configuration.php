<?php

namespace PHPFusion\Database;

use BadMethodCallException;

/**
 * @method string getHost()
 * @method string getDatabase()
 * @method string getUser()
 * @method string getPassword()
 * @method string getCharset()
 * @method string getDriver()
 */
class Configuration {
	private $configuration = array();

	/**
	 * @param array $configuration
	 */
	public function __construct(array $configuration = array()) {
		$this->configuration = $configuration + array(
			'host' => '',
			'database' => '',
			'user' => '',
			'password' => '',
			'charset' => 'utf8',
			'driver' => DatabaseFactory::getDefaultDriver(),
			'debug' => FALSE
		);
		$this->configuration['driver'] = strtolower($this->configuration['driver']);
	}

	/**
	 * @return bool
	 */
	public function isDebug() {
		return (bool) $this->configuration['debug'];
	}

	public function __call($method, $arguments) {
		$method = strtolower($method);
		if (substr($method, 0, 3) !== 'get') {
			throw new BadMethodCallException(sprintf("This method does not exist: '%s' ", $method));
		}
		$index = substr($method, 3);
		if (!isset($this->configuration[$index])) {
			throw new BadMethodCallException(sprintf("This method does not exist: '%s' ", $method));
		}
		return $this->configuration[$index];
	}

	public function toArray() {
		return $this->configuration;
	}
}