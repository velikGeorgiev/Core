<?php

use \core\MVC;

class IndexController extends core\MVC\Action {
	/**
	 * @var Object Instance of the Xtemplate class
	 */
	private $template = null;

	public function init() {
		$this->template = $this->_globals->get("template");
	}

	public function IndexAction() {
		$indexModel = $this->getModel("index");
		$version = $indexModel->getVersion();
		$name = $indexModel->getName();

		$this->template->assign("version", $version);
		$this->template->assign("name", $name);
		$this->template->display("index.tpl.html");
	}
}

?>