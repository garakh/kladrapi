<?php

namespace Kladr\Frontend {

    class Module implements \Phalcon\Mvc\ModuleDefinitionInterface
    {
        /**
         * Регистрация автозагрузчика модуля
         */
        public function registerAutoloaders()
        {
            $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/config/config.ini');
            $loader = new \Phalcon\Loader();

            $loader->registerNamespaces(
                array(
                    'Kladr\Frontend\Models'       => $config->application->modelsDir,
                    'Kladr\Frontend\Views'        => $config->application->viewsDir,
                    'Kladr\Frontend\Controllers'  => $config->application->controllersDir,           
                    'Kladr\Frontend\Plugins'      => $config->application->pluginsDir,
                    'Kladr\Frontend\Library'      => $config->application->libraryDir,
                )
            );

            $loader->register();
        }

        /**
         * Регистрация сервисов модуля
         */
        public function registerServices($di)
        {
            $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/config/config.ini');

            // Set site config
            $di->set('config', $config);

            // Setting up mongo
            $di->set('mongo', function() use ($config) {
                $mongo = new \Mongo($config->database->host);
                return $mongo->selectDb($config->database->name);
            }, true);

            // Registering the collectionManager service
            $di->set('collectionManager', function() {
                $modelsManager = new \Phalcon\Mvc\Collection\Manager();
                return $modelsManager;
            }, true);

            // Start the session the first time when some component request the session service
            $di->set('session', function() {
                $session = new \Phalcon\Session\Adapter\Files();
                $session->start();
                return $session;
            });

            // Setting up dispatcher
            $di->set('dispatcher', function() use ($di) {
                $dispatcher = new \Phalcon\Mvc\Dispatcher();
                $dispatcher->setDefaultNamespace("Kladr\Frontend\Controllers");
                return $dispatcher;
            });

            // Register an user component
            $di->set('elements', function(){
                return new Library\Elements();
            });

            // Register key tools
            $di->set('keyTools', function(){
                return new Plugins\KeyTools();
            });

            // Register the flash service with custom CSS classes
            $di->set('flash', function(){
                $flash = new \Phalcon\Flash\Direct();
                return $flash;
            });

            // Setting up the view component
            $di->set('view', function(){
                $view = new \Phalcon\Mvc\View();
                $view->setViewsDir('../apps/frontend/views/');
                return $view;
            });
        }

    }

}