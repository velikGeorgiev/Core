<?php
/**
 * Core Framework
 *
 * @author Velik Georgiev Chelebiev
 * @version 0.1.0
 * @copyright Copyright (c) 2015 Velik Georgiev Chelebiev
 * @license MIT License
 */
 
namespace core\MVC;

use \core;

abstract class Model {
	public $mongo = null;
	public $db = null;

	/**
	 * @var Object Define Globals
	 */
	protected $_globals;

	public function init() {
		// Set globals and params
		$this->_globals = core\Globals::getInstance();

		// Set MongoDB and MySQL
		$this->mongo = $this->_globals->get("mongo");
		$this->db = $this->_globals->get("db");
	}
}
?>