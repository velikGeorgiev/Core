<?php
/**
 * Core Framework
 *
 * @author Velik Georgiev Chelebiev
 * @version 0.1.0
 * @copyright Copyright (c) 2015 Velik Georgiev Chelebiev
 * @license MIT License
 */
 
namespace core\Utils;

use \core\Utils\Pattern as Pattern;
use \core\Utils;

class Form {
	const PARSE_DELIMITER = "_";
	const PARSE_PREFIX = "form";

	private $listWithRuls = array();

	public function _add(Pattern $patternObj, $data, $errMessage = "") {
		$form = array(
				"pattern" => $patternObj,
				"data" => $data,
				"errMessage" => $errMessage
			);

		array_push($this->listWithRuls, $form);
	}

	public function __call($name, $args) {
		if($name == "add" && $args[0] instanceof Pattern) {
			$this->_add($args[0], $args[1], $args[2]);
		} else {
			
		}
	}

	public function _multiAdd(array $data) {
		foreach ($data as $key => $value) {
			if(!is_array($value)) {
				$this->add(Pattern::get($value), $key);
			} else {
				$this->add(Pattern::get($value["pattern"]), $key, $value["err"]);
			}
		}
	}

	public function parseAdd(array $request, array $errMessage = array()) {
		foreach ($request as $key => $value) {
			$explodeKey = explode(self::PARSE_DELIMITER, $key);

			if(count($explodeKey) < 3 || $explodeKey[0] != self::PARSE_PREFIX) {
				continue;
			}

			if(array_key_exists($explodeKey[1], $errMessage)) {
				$this->add(Pattern::get($explodeKey[2]), $value, $errMessage[$explodeKey[1]]);
			} else {
				$this->add(Pattern::get($explodeKey[2]), $value);
			}
		}
	}

	public function validate() {
		$result = array(
			"err" => false,
			"messages" => array()
		);

		foreach ($this->listWithRuls as $value) {
			if($value["pattern"]->match($value["data"]) == false) {
				if($result["err"] == false) {
					$result["err"] = true;
				}

				array_push($result["messages"], $value["errMessage"]);
			}
		}

		return $result;
	}
}
?>