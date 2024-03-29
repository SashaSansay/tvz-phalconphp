<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

try {
	//Include path for libraries folder
	set_include_path("../app/libraries/");
	/**
	 * Read the configuration
	 */
	$config = require __DIR__ . "/../app/config/config.php";

	/**
	 * Include loader
	 */
	require __DIR__ . '/../app/config/loader.php';

	/**
	 * Include services
	 */
	require __DIR__ . '/../app/config/services.php';

	/**
	 * Handle the request
	 */

	$application = new \Phalcon\Mvc\Application();
	$application->setDI($di);
	echo $application->handle()->getContent();

} catch (Phalcon\Exception $e) {
	echo '<pre>'.$e->getMessage().'</pre>';
} catch (PDOException $e){
	echo $e->getMessage();
}