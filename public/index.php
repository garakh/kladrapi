<?php

error_reporting(0);

use Phalcon\Mvc\Router,
    Phalcon\Mvc\Application,
    Phalcon\DI\FactoryDefault,
    Phalcon\Exception;

$di = new FactoryDefault();

$di->set('router', function () {
    $router = new Router();
    $router->setDefaultModule("frontend");
    
    $router->add("/api.php", array(
        'module'     => 'core',
        'controller' => 'api',
        'action'     => 'index',
    ));

    // better to locate somewhere in the module
    $router->add(
        '/integration/{name:.*}/',
        array(
            'module'     => 'frontend',
            'controller' => 'integration',
            'action' => 'parts'
        )
    );

    return $router;
});

try {

	$phalconVersion = \Phalcon\Version::get();
	$major = $phalconVersion[0];
	$major = $major == '2' || $major == '3' ? $major : '';
	
    //Create an application
    $application = new Application($di);

    // Register the installed modules
    $application->registerModules(
        array(
            'frontend' => array(
                'className' => 'Kladr\Frontend\Module',
                'path'      => '../apps/frontend/Module' . $major . '.php',
            ),
            'core'  => array(
                'className' => 'Kladr\Core\Module',
                'path'      => '../apps/core/Module' . $major . '.php',
            )
        )
    );

    //Handle the request
    echo $application->handle()->getContent();

} catch(Exception $e){
    echo $e->getMessage();
}