<?php

use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new \Phalcon\DI\FactoryDefault();

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function() use ($config) {
	$url = new \Phalcon\Mvc\Url();
	$url->setBaseUri($config->application->baseUri);
	return $url;
});

/**
 * Setting up the router component
 */
$di->set('router', function() {

	$router = new \Phalcon\Mvc\Router();

	$router->add(
			"/serial/{label:([a-zA-Z0-9\_\-]+)}/{season:[0-9]+}/{num:[0-9]+}",
			array(
					'controller' => 'serial',
					'action' => 'index'
			)
	);

	$router->add(
			"/serial/{label:([a-zA-Z0-9\_\-]+)}/{season:[0-9]+}",
			array(
					'controller' => 'serial',
					'action' => 'season'
			)
	);

	$router->add(
			"/serial/{label:([a-zA-Z0-9\_\-]+)}",
			array(
					'controller' => 'serial',
					'action' => 'serial'
			)
	);

	$router->add(
			"/serial/{label:([a-zA-Z0-9\_\-]+)}/",
			array(
					'controller' => 'serial',
					'action' => 'serial'
			)
	);

	$router->add(
			"/serial/list/",
			array(
					'controller' => 'serial',
					'action' => 'list'
			)
	);

	$router->add(
			"/serial/list",
			array(
					'controller' => 'serial',
					'action' => 'list'
			)
	);

	$router->add(
			"/film/{label:([a-zA-Z0-9\_\-]+)}",
			array(
				'controller' => 'film',
				'action' => 'index'
			)
	);

	$router->add(
			'/sitemap.xml',
			array(
					'controller' => 'sitemap',
					'action' => 'index'
			)
	);

	$router->add(
			'/i/{type:([a-zA-Z]+)}/{label:([a-zA-Z0-9\_\-]+)}/{image:([0-9]+)}',
			array(
					'controller' => 'image',
					'action' => 'index'
			)
	);

    $router->add(
        '/i/p/{id:([0-9]+)}',
        array(
            'controller' => 'image',
            'action' => 'preview'
        )
    );


	return $router;
});

/**
 * Setting up the view component
 */
$di->set('view', function() use ($config) {
	$view = new \Phalcon\Mvc\View();
	$view->setViewsDir($config->application->viewsDir);
	return $view;
});

$adapter = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
		"host" => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname" => $config->database->name,
		'options' => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		)
));

if($config->options->debug_sql){
	$eventsManager = new \Phalcon\Events\Manager();
	/** @var $event \Phalcon\Events\Event */
	/** @var $phalconConnection \Phalcon\Db\Adapter\Pdo\Mysql */
	$eventsManager->attach('db', function ($event, $connection) {
		if ($event->getType() == 'afterQuery') {
			echo "<pre>".$connection->getSQLStatement()."</pre>";
		}
	});
	$adapter->setEventsManager($eventsManager);
}

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function() use ($adapter) {
	return $adapter;
});

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function() {
	$session = new \Phalcon\Session\Adapter\Files();
	$session->start();
	return $session;
});

$di->setShared('config', $config);

$di->set('CTags',function(){
    return new CTags();
});

//$di->set('ApiValidator',function(){
//    return new ApiV();
//});

$di->setShared('dispatcher', function () {
	$eventsManager = new EventsManager();
	$eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, $exception) {
		if ($exception instanceof DispatchException) {
			$dispatcher->forward(
					[
							'controller' => 'index',
							'action'     => 'show404'
					]
			);

			return false;
		}
		switch ($exception->getCode()) {
			case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
			case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
				$dispatcher->forward(
						[
								'controller' => 'index',
								'action'     => 'show404'
						]
				);

				return false;
		}
	});

	$dispatcher = new MvcDispatcher();
	$dispatcher->setEventsManager($eventsManager);
	return $dispatcher;

});