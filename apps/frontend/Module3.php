<?php
/**

Phalcon v2.x.x support

**/
namespace Kladr\Frontend {

    class Module implements \Phalcon\Mvc\ModuleDefinitionInterface
    {

        /**
         * Регистрация автозагрузчика модуля
         */
        public function registerAutoloaders(\Phalcon\DiInterface $dependencyInjector = NULL)
        {
            $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/config/config.ini');
            $loader = new \Phalcon\Loader();

            $loader->registerNamespaces(
                    array(
                        'Kladr\Frontend\Models' => $config->application->modelsDir,
                        'Kladr\Frontend\Views' => $config->application->viewsDir,
                        'Kladr\Frontend\Controllers' => $config->application->controllersDir,
                        'Kladr\Frontend\Plugins' => $config->application->pluginsDir,
                        'Kladr\Frontend\Library' => $config->application->libraryDir,
                        'Phalcon' => __DIR__ .'/vendor/Phalcon/'
                    )
            );

            $loader->register();
        }

        /**
         * Регистрация сервисов модуля
         */
        public function registerServices(\Phalcon\DiInterface $di)
        {
            $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/config/config.ini');

            // Set site config
            $di->set('config', $config);

            // Setting up mongo
            $di->set('mongo', function() use ($config) {
                $mongo = new \MongoClient($config->database->host);
                return $mongo->selectDb($config->database->name);
            }, true);

            // Registering the collectionManager service
            $di->set('collectionManager', function() {
                $modelsManager = new \Phalcon\Mvc\Collection\Manager();
                return $modelsManager;
            }, true);

            // Start the session the first time when some component request the session service
            $di->set('session', function() use ($config) {

                if(isset($config->session->adapter))
                    switch($config->session->adapter){
                        case 'mongo' :
                            $mongo = new \Mongo($config->session->mongoHost);

                            $session = new \Phalcon\Session\Adapter\Mongo(array(
                                'collection' => $mongo->kladrapiSession->data
                            ));
                            break;

                        case 'file':
                            $session = new \Phalcon\Session\Adapter\Files();
                            break;
                    }
                else
                    $session = new \Phalcon\Session\Adapter\Files();


                $session->start();
                return $session;
            });

            // Setting up dispatcher
            $di->set('dispatcher', function() use ($di) {

                $evManager = $di->getShared('eventsManager');
                $evManager->attach(
                    "dispatch:beforeException",
                    function($event, $dispatcher, $exception)
                    {
                        switch ($exception->getCode()) {
                            case \Phalcon\Mvc\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                            case \Phalcon\Mvc\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                                $dispatcher->forward(
                                    array(
                                        'controller' => 'index',
                                        'action'     => 'show404',
                                    )
                                );
                                return false;
                        }
                    }
                );

                $dispatcher = new \Phalcon\Mvc\Dispatcher();
                $dispatcher->setDefaultNamespace("Kladr\Frontend\Controllers");
                $dispatcher->setEventsManager($evManager);
                return $dispatcher;
            });

            // Register an user component
            $di->set('elements', function() {
                return new Library\Elements();
            });

            // Register key tools
            $di->set('keyTools', function() {
                return new Plugins\KeyTools();
            });

            // Register the flash service with custom CSS classes
            $di->set('flash', function() {
                $flash = new \Phalcon\Flash\Direct();
                return $flash;
            });

            // Setting up the view component
            $di->set('view', function() {
                $view = new \Phalcon\Mvc\View();
                $view->setViewsDir('../apps/frontend/views/');
                return $view;
            });


        }

    }

}