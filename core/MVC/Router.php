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

use core\Utils\Arrays as Arrays;

class Router {
	private $params = array();
	private $routers = array();

	public function addRoute(array $data) {
		if(Arrays::keysExists(array("controller", "action", "route"), $data) == false) {
			throw new ControllerException("Invlid routes configuration.");
		}

		array_push($this->routers, $data);
	}

	public function getParam($key) {
		if(array_key_exists($key, $this->params)) {
			return $this->params[$key];
		}

		return null;
	}

	protected function addRoutesFromFile(array $routes) {
		if(!array_key_exists("routes", $routes)) {
			throw new ControllerException("Invlid routes configuration file");
		}

		foreach ($routes["routes"] as $currentRoute) {
			$this->addRoute($currentRoute);
		}
	}

	/**
	 * Parses the url and gets the controller, the action and the variables ( if there is any )
	 */
	protected function parseUriRouter() {
		$uri = trim($_SERVER["REQUEST_URI"], "/");
		$uriExplode = explode("/", trim($uri, '/'));
		$validRoute = null;

		/**
		 * Search for valid route.
		 */
		foreach($this->routers as $currentRoute) {
			$route = trim($currentRoute["route"], "/");
			$routerPattern = "#^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($route)) . "$#D";
			$routerPattern = str_replace('\[action\]', '(?<action>[a-zA-Z0-9\-\_]+)', $routerPattern);

			// Params values that will be assigned to there respective keys
			$matchesParams = array();

			// Check if the URI matches the current route
			// if a valid route is found parses the params
			// and if the Controller ACTION is dymanic/literal it is set.
			if(preg_match_all($routerPattern, $uri, $matchesParams)) {

				// Remove the first element 
				array_shift($matchesParams);
				/**
				 * If the action is dynamic, set the action
				 * using URL param.
				 */
				if($currentRoute["action"] == "*") { // the char "*" indicates a dynamic action
					if(!array_key_exists("action", $matchesParams)) {
						break;
					}

					// Set the new action name
					$currentRoute["action"] = $matchesParams["action"][0];

					/**
					 * Search the index of the "action" index
					 * and delete the next element sense is the same
					 */				
					$removeNext = false;

					foreach (array_keys($matchesParams) as $key => $value) {
						if($value === "action") {
							$removeNext = true;
						} else if($removeNext) {
							array_splice($matchesParams, (int)$key, 1);
							break;
						}
					}

					unset($matchesParams["action"]);
				}

				// Keys for the params
				$keys = array();

				// Getting the keys names
				preg_match_all('/\\:([a-zA-Z0-9\_\-]+)/', $route, $keys);
				
				// Remove the first element sense is no necesary
				array_shift($keys);

				// Assign value to key
				for ($i = 0; $i < count($keys[0]); $i++) {
					$this->params[$keys[0][$i]] = $matchesParams[$i][0];
				}

				// Save the valid route
				$validRoute = $currentRoute;

				// If the route was found we break the loop
				break;
			}
		}

		return $validRoute;
	}
}
?>