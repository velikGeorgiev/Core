<?php
/**
 * Core Framework
 *
 * @author Velik Georgiev Chelebiev
 * @version 0.1.0
 * @copyright Copyright (c) 2015 Velik Georgiev Chelebiev
 * @license MIT License
 */
 
namespace core;

class Globals {
	private $globals = array();
	private static $instance = null;

	private function __construct() { }

	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new Globals();
		}

		return self::$instance;
	}

	public function set($key, $value) {
		$this->globals[$key] = $value;
	}

	public function get($key) {
		if(array_key_exists($key, $this->globals)) {
			return $this->globals[$key];
		}

		return null;
	}
}
?>