<?php
require_once("core/AutoLoad.php");

use \core;
use \core\MVC\Controller;
use \core\MVC\View;
use \core\Globals;
use \core\Db\MySQL as MySQL;

/**
 * Configurations
 */
$config = include "configs/config.php";

/**
 * Session
 */
session_start();

/**
 * Filter all POSTs and GETs
 */
core\Filter::htmlChars($_POST);
core\Filter::htmlChars($_GET);

/**
 * Set MySQL
 */
$db = new MySQL($config["mysql"]);

/**
 * Set debug mode
 */
\core\Debug::$enable = true;
\core\Debug::addDeveloper($config["developers"]);

/**
 * Set the template
 */
$templateObj = new View();
$templateObj->addDeveloper($config["developers"]);
$template = $templateObj->get();
$template->assign("site", $config["site"]);

/**
 * Register globals
 */
$globals = Globals::getInstance();
$globals->set("template", $template);
$globals->set("db", $db);
$globals->set("config", $config);

/** 
 * Set the controller
 */
$controller = new Controller();
$controller->run();


?>