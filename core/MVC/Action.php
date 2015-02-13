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

abstract class Action {
	/**
	 * @var Object Define Globals
	 */
	protected $_globals;

	/**
	 * @var Controller The controller class
	 */ 
	private $controller = null;

	/**
	 * The method init
	 */
	public function init() {

	}

	/**
	 * This method is called by the Controller class to execute
	 * the controller and the action method.
	 *
	 * @param string $action The action that should be runed
	 * @param Controller The controller
	 */
	public function run($action, $controller) {
		// Set globals and params
		$this->_globals = core\Globals::getInstance();

		// Setting up the controller
		$this->controller = $controller;

		// Calling the init function
		$this->init();

		if(!method_exists($this, $action)) {
			$action = $controller->getDefaultActionName();
		}

		// Call the action
		$this->$action();
	}

	/**
	 * Return the param value.
	 * If the param doesnt exists it will return
	 * the default value or null.
	 *
	 * @param string $key The param key
	 * @param mixed $default Default value ( if the key doesnt exists )
	 * @return mixed
	 */
	protected function getParam($key, $default = null) {
		return $this->controller->getParam($key, $default);
	}

	protected function getModel($modelName) {
		$modelName = ucfirst(strtolower($modelName)) . "Model";
		$modelFullPath = "./app/models/" . $modelName . ".php";

		if(!file_exists($modelFullPath)) {
			throw new ControllerException("The model " . $modelName . " cannot be found.");
		}

		require_once($modelFullPath);

		$model = new $modelName();
		$model->init();

		return $model;
	}
}
?>