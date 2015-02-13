<?php

use \core\MVC\Model as Model;

class IndexModel extends Model {

	public function getVersion() {
		return "0.1.0 alpha";
	}

	public function getName() {
		return "Core";
	}
} 

?>