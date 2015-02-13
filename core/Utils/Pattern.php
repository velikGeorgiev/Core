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

class Pattern {
	/**
	 * @var array List of default patterns
	 */
	private $regexList = array(
			"fullname" => "/^([a-zA-Z ]+){2,4}$/",
			"username" => "/^([a-zA-Z0-9_]{4,})$/",
			"tel"	   => "/^((00|\+?)?34\s?)?[6|7|9][\d\s]+$/"
			"email"    => "/^[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+\.[a-zA-Z]{2,4}$/",
			"age" 	   => "/^[\d]{0,1}[\d]{1}$/",
			"password" => "/((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{6,20})/"
		);

	/**
	 * @var boolean Indicates if there is a valid patter in use
	 */
	private $err = false;

	/**
	 * @var string Pattern that will be use in mathes methods.
	 */
	private $pattern = "";
	
	/**
	 * Sets the pattern that will be used
	 *
	 * @param string $patternName the pattern that should be used
	 */
	public function __construct($patternName) {
		$this->select($patternName);
	}

	/**
	 * Returns an instance of the class with the given pattern
	 * 
	 * @param string $name The name of the pattern that should be use
	 * @return \core\Utils\Pattern Instance of the Pattern object.
	 */
	public static function get($name) {
		return (new Pattern($name));
	}
	
	/**
	 * Add aditional regular expression to the list
	 *
	 * @param string $name The name of the pattern
	 * @param string @regex The regular expression
	 */
	public function add($name, $regex) {
		$this->regexList[$name] = $regex;
	}

	/**
	 * Sets the pattern that will be used
	 *
	 * @param string $patternName the pattern that should be used
	 */
	public function select($patternName) {
		if(!array_key_exists($patternName, $this->regexList)) {
			$this->err = true;
			return;
		}

		$this->pattern = $this->regexList[$patternName];
	}

	/**
	 * Checks if the pattern matches the given data
	 *
	 * @param string $data The data that must be match with the pattern
	 * @return boolean True if the data matches the pattern. False otherwise
	 */
	public function match($data) {
		// If $this->err is true then the given pattern is not valid
		if($this->err || empty($this->pattern)) return false;

		return preg_match($this->pattern, $data);
	}
}
?>