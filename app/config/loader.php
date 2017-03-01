<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
	array(
		$config->application->controllersDir,
		$config->application->helpersDir
	)
)->register();

$loader->registerClasses(
	array(
		"Category" => $config->application->modelsDir."category.php",
		"CategorySerial" => $config->application->modelsDir."category_serial.php",
		"Commercial" => $config->application->modelsDir."commercial.php",
		"Options" => $config->application->modelsDir."options.php",
		"Serial" => $config->application->modelsDir."serial.php",
		"Series" => $config->application->modelsDir."series.php",
		"Film" => $config->application->modelsDir."film.php",
		"CategoryFilm" => $config->application->modelsDir."category_film.php",
	)
);