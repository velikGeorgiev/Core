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

class Controller extends Router{
	/**
	 * @var string The full name of the controller. Consists in name + "Controller" sufix
	 */
	private $controllerName = "";

	/**
	 * @var string The full name of the Action. Consists in name + "Action" sufix
	 */
	private $actionName = "";

	/**
	 * @var string Controller path
	 */
	private $controllerPath = "./app/controllers/";

	/**
	 * @var string Default controller name
	 */
	private $defaultControllerName = "index";

	/**
	 * @var string Default action name
	 */
	private $defaultActionName = "index";

	/**
	 * @var string Default routes configuration file
	 */
	private $defaultRoutesConfig = "./configs/routes.php";

	/**
	 * @var array URL Params
	 */
	private $params = array();

	public function __construct($routesFile = "") {
		if($routesFile == "" || empty($routesFile)) {
			$routesFile = $this->defaultRoutesConfig;
		}

		if(!file_exists($routesFile)) {
			throw new ControllerException("Router configuration file not found.");
		}	

		$routes = include $routesFile;

		if(!is_array($routes) || !array_key_exists("routes", $routes)) {
			throw new ControllerException("Invlid routes configuration file");
		}

		// Check if defaults are given
		if(array_key_exists("default", $routes)) {
			if(array_key_exists("controller", $routes["default"])) {
				$this->defaultControllerName = $routes["default"]["controller"];
			}

			if(array_key_exists("action", $routes["default"])) {
				$this->defaultActionName = $routes["default"]["action"];
			}
		}

		$this->addRoutesFromFile($routes);
	}

	/**
	 * Returns the default controller name
	 * @return string Default controller name
	 */
	public function getDefaultControllerName() {
		return ucfirst(strtolower($this->defaultControllerName)) . "Controller";
	}

	/**
	 * Returns the default action name
	 * @return string Default action name
	 */
	public function getDefaultActionName() {
		return ucfirst(strtolower($this->defaultActionName)) . "Action";
	}

	/**
	 * Sets the controller full name.
	 *
	 * @param string $controllerName The name of the controller
	 */
	private function setControllerName($controllerName) {
		if(!is_string($controllerName)) {
			throw new ControllerException("Invalid Controller Name.");
		}

		$this->controllerName = ucfirst(strtolower($controllerName)) . "Controller";
	}

	/**
	 * Sets the action full name.
	 *
	 * @param string $actionName The name of the action
	 */
	private function setActionName($actionName) {
		if(!is_string($actionName)) {
			throw new ControllerException("Invalid Action Name.");
		}

		$this->actionName = ucfirst(strtolower($actionName)) . "Action";
	}


	/**
	 * Changes the controller default folder.
	 *
	 * @param string $path The directory that contains all the controllers.
	 * @return Controller
	 */
	public function setPath($path) {
		if(!is_dir($path)) {
			throw new ControllerException("Invalid Controller Path.");
		}

		$this->controllerPath = $path;

		return $this;
	}

	/**
	 * Provides the full controller name
	 *
	 * @return The controller name
	 */
	public function getControllerName() {
		return $this->controllerName;
	}

	/**
	 * Provides the full action name
	 *
	 * @return The action name
	 */
	public function getActionName() {
		return $this->actionName;
	}

	/**
	 * Executes the controller.
	 */
	public function run() {
		// Parse the URL to get the controller and the action
		$route = null;

		if(($route = $this->parseUriRouter()) != null) {
			$this->setControllerName($route["controller"]);
			$this->setActionName($route["action"]);
		}

		$controllerFile = $this->controllerPath . $this->controllerName . ".php";

		/**
		 * If the file doesnt exists we assign the default controller file
		 */
		if(!file_exists($controllerFile)) {
			$this->setControllerName($this->defaultControllerName);

			// The new path
			$controllerFile = $this->controllerPath . $this->controllerName . ".php";
		}

		// Include the file
		$this->includeController($this->controllerName);

		/**
		 * If the controller class is not found we throw an exception
		 */
		if(class_exists($this->controllerName) == false) {
			throw new ControllerException("Controller not found.");
		}

		// Create instance of the controller
		$controller = new $this->controllerName();

		// Check if the controller is child to the class Action
		if(($controller instanceof Action) == false) {
			throw new ControllerException("Invalid controller. The controller must extends core\MVC\Action.");
		}

		$controller->run($this->actionName, $this);
	}

	/**
	 * Includes controller file
	 * 
	 * @param string $controllerName THe name of the controller
	 */
	private function includeController($controllerName) {
		$controllerFile = $this->controllerPath . $controllerName . ".php";

		if(!file_exists($controllerFile)) {
			throw new ControllerException("Controller file cannot be found");
		}

		require_once($controllerFile);
	}
}

?>