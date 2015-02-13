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

class View {
	private $template = null;

	public function __construct() {
		require_once("./core/MVC/Xtemplate/Xtemplate.php");

		$this->template = new \Xtemplate();
		$this->template->template_dir   = "./app/templates";
		$this->template->compile_dir    = "./temp/templates_c";
		$this->template->developer_ips = array();
	}

	public function get() {
		return $this->template;
	}

	public function addDeveloper($ip) {
		if(is_array($ip)){
			foreach ($ip as $value) {
				$this->addDeveloper($value);
			}
		} else {
			array_push($this->template->developer_ips, $ip);
		}
	}
}
?>