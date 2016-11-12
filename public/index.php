<?php

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

try {

    // Register an autoloader
    $loader = new Loader();
    $loader->registerDirs(array(
        '../app/controllers/',
        '../app/models/',
        '../app/plugins/',
        '../app/library/'
    ))->register();

    // Create a DI
    $di = new FactoryDefault();

    // Setup the database service
    $di->set('db', function () {
        return new DbAdapter(array(
            "host"     => "localhost",
            "username" => "root",
            "password" => "5ac5e8fabd0b77b56f029f0952fe6c6fef37880379b83820",
            "dbname"   => "socialcart"
        ));
    });

    // Setup the view component
    $di->set('view', function () {
        $view = new View();
        $view->setViewsDir('../app/views/');
        return $view;
    });

    // Setup a base URI so that all generated URIs include the "exchangewolf" folder
    $di->set('url', function () {
        $url = new UrlProvider();
        $url->setBaseUri('/socialcart/');
        return $url;
    });

    /* Start the session */
    $di->setShared('session', function() {
    	$session = new Phalcon\Session\Adapter\Files();
    	return $session;
    });

    /* make session data available to everyone */
    $di->setShared('acl', function() {
	$acl = new Acl();
    	return $acl;
    });

    /* make stocks data available to everyone */
    $di->setShared('stocks', function() {
        $stocks = new Stocks();
        return $stocks;
    });

    /* Setup plugins for Dispatcher */
    $di->set('dispatcher', function() use ($di) {
	$eventsManager = new Phalcon\Events\Manager;
    	$eventsManager->attach('dispatch:beforeDispatch', new Security);
    	$dispatcher = new Phalcon\Mvc\Dispatcher;
    	$dispatcher->setEventsManager($eventsManager);
        return $dispatcher;
    });

    // Handle the request
    $application = new Application($di);

    echo $application->handle()->getContent();

} catch (\Exception $e) {
     echo "PhalconException: ", $e->getMessage();
}
