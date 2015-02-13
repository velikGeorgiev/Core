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

class Debug {
	/**
	 * @var int $counter Debugger default id (for each debug it increases by 1)
	 */
	private static $counter = 0;

	/**
	 * @var array $developers IP list of all developers
	 */
	private static $developers = array();

	/**
	 * @var bool $enable Indicates if the debugger is enabled or not.
	 */
	public static $enable = false;

	/**
	 * @var array $benchmarks The benchmarks times
	 */
	private static $benchmarks = array();

	/**
	 * Add an developer ip or an array of ips that will be able
	 * to see the Debugger outputs
	 *
	 * @param mixed $dev IP list of existing developers.
	 */
	public static function addDeveloper($dev) {
		if(is_array($dev)) {
			foreach ($dev as $value) {
				self::addDeveloper($value);
			}
		} else {
			array_push(self::$developers, $dev);
		}
	}

	/**
	 * HTML/CSS code of an open HTML/CSS box.
	 *
	 * @param string $debugName The name of the debug task. (if not given default will be assign)
	 * @param boolean $visible Indicates if the box should be open or closed. 
	 * @return string Open HTML box
	 */
	private static function openHtmlBox($debugName = "", $visible = false) {
		$debugName = ($debugName == "") ? "ID: " . self::$counter : $debugName;
		$visible = ($visible) ? "block" : "none";

		$html = "<div style='font-family: \"Courier New\";'>
					<input type='button' 
						value='Debug (" . $debugName . ")' 
						onclick=\"document.getElementById('debug_" . self::$counter . "').style.display='block';\"
						style='display: block; 
							   width: 100%;
							   background-image: -webkit-linear-gradient(#E0EAEF, #A3C2D3);
							   padding: 10px 0;
							   border: 1px solid #A7C4D5; margin: 0;' />

				 <div ondblclick=\"this.style.display='none';\" 
				 	  id='debug_" . self::$counter . "' 
				 	  style='overflow: auto; width: 96.8%; font-size: 15px; 
				 	  		 background-color: #E9F1F5; padding: 5px 20px; display: " . $visible . ";
				 	  		 border-top: 2px solid #0079B5;'><br />";

		self::$counter++;

		return $html;
	}

	/**
	 * Close HTML/CSS box.
	 *
	 * @return string Close HTML box
	 */
	private static function closeHtmlBox() {
		return "</div></div>";
	}

	/**
	 * Checks if the user is a developer.
	 *
	 * @return Returns True if the user is developer or False if his not.
	 */
	private static function validateDeveloper() {
		foreach (self::$developers as $dev) {
			if($dev == $_SERVER["REMOTE_ADDR"]) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Prints the values and the keys of an array. (The box will be close)
	 *
	 * @param array $arr The array that should be printed
	 * @param boolean $visible Indicates if the box should be open or closed. 
	 * @param string $debugName The name of the debug task. (if not given default will be assign)
	 */
	public static function dump(array $arr, $debugName = "", $visible = false) {
		$call = function($arr, $depth = 1) use(&$call) {
					foreach ($arr as $key => $value) {
						echo str_repeat("&nbsp;", $depth);

						if(is_array($value)) {
							echo "[" . var_export($key, true) . "] => " . "<span style='font-weight: bold; color: #0079B5;'>" . gettype($value) . "</span>(" . count($value) . ") ";
							echo " {<Br />";

							$call($value, $depth + 5);

							echo str_repeat("&nbsp;", $depth) . "}<br />";
						} else {
							if(!is_callable($value) && !is_object($value)) {
								echo "[" . $key . "] => " . "<span style='font-weight: bold; color: #0079B5;'>" . gettype($value) . "</span> (" . strlen($value) . ") ". var_export($value, true) . "<Br />";
							} else{
								echo "<a onclick='this.nextSibling.nextSibling.nextSibling.style.display=\"block\"' href='javascript:;'>Object(Closure)</a><br />
										<div style='margin-left: " . $depth * 10 . "px; display: none; overflow: auto; width: 50%; height: 200px;'>
											<pre>";
											var_dump($value);
									  echo "</pre>
										</div>";
							}
						}
					}
				};

		self::exec(function() use($call, $arr) { $call($arr); }, $debugName, $visible);
	}

	/**
	 * Prints the values and the keys of an array. (The box will be open)
	 *
	 * @param array $arr The array that should be printed
	 * @param string $debugName The name of the debug task. (if not given default will be assign)
	 */
	public static function _dump(array $arr, $debugName = "") {
		self::dump($arr, $debugName, true);
	}

	/**
	 * Executes the result of a function in
	 * HTML box (div).
	 *
	 * @param Callable $func The function that will be called in the box
	 * @param boolean $visible Indicates if the box should be open or closed. 
	 * @param string $debugName The name of the debug task. (if not given default will be assign)
	 */
	public static function exec($func, $debugName = "", $visible = false) {
		// Check if the current user is developer
		// and if the Debug mode is enabled.
		if(self::validateDeveloper() == false || !self::$enable) return;
		if(!is_callable($func)) return;

		echo self::openHtmlBox($debugName, $visible);
		$func();
		echo self::closeHtmlBox();
	}

	/**
	 * When the method is called if the user is not in the developer list
	 * the script will "die" in this exact point. If the user is developer
	 * the script will continue.
	 *
	 * @param boolean $show 
	 */
	public static function onlyDevelopers($show = true) {
		// Check if the current user is developer
		// and if the Debug mode is enabled.
		if(self::validateDeveloper() == false && self::$enable) {
			die();
		} else {
			if($show) {
				echo "<div style='width: 60px; 
								  padding: 20px 0;
								  text-align: center;
								  background-color: #FFC675;
								  position: fixed;
								  box-shadow: 3px -3px 8px #888;
								  bottom: 0; left: 0;'>Dev</div>";
			}
		}
	}

	/**
	 * Calculates how much micro seconds takes to execute the given function	
	 *
	 * @param Callable $func The benchmark will be applied on this method
	 * @param boolean $show If its true a debug box will be printed otherwise
	 *                      it will return only the result
	 * @param boolean $visible Indicates if the box should be open or closed. 
	 */
	public static function benchmark($func, $times = 100, $show = true, $visible = true) {
		if(!is_callable($func)) {
			return 0;
		}

		$startTime = microtime(true);
		
		for ($i = 0; $i < $times; $i++) { 
			$func();
		}

		$endTime = microtime(true);

		if($show) {
			self::exec(function() use($startTime, $endTime) {
				echo "Benchmark result: " . ($endTime - $startTime);
			}, "", $visible);
		}

		return $endTime - $startTime;
	}

	/**
	 * Sets the start of a new benchmark
	 *
	 * @var mixed $id The ID of the benchmark
	 */
	public static function benchmarkStart($id) {
		self::$benchmarks[$id]["start"] = microtime(true);
	}

	/**
	 * Sets the end of the given benchmark and calculates the 
	 * total time.
	 *
	 * @param mixed $id The ID of the benchmark
	 * @return array Array that contains the start, the end time and the difference
	 */
	public static function benchmarkEnd($id) {
		// Get the end time in the begging.
		$endTime = microtime(true);

		if(!array_key_exists($id, self::$benchmarks)) {
			return array();
		}

		self::$benchmarks[$id]["end"] = $endTime;
		self::$benchmarks[$id]["time"] = self::$benchmarks[$id]["end"] - self::$benchmarks[$id]["start"];

		return self::$benchmarks[$id];
	}
}

?>