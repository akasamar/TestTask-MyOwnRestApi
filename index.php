<?php
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	header('Content-Type: application/json');

	define('ROOT', dirname(__FILE__));
	require_once(ROOT.'/components/consts.php');
	require_once(ROOT.'/components/Router.php');
	require_once(ROOT.'/components/Db.php');
	require_once(ROOT.'/components/Helper.php');
	//require_once(ROOT.'/configs/install.php');

	if (!Db::$connection)
		Db::getConnection();
	
	$router = new Router();
	$router->run();


?>


