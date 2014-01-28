<?php

namespace Kladr\Core {

    require_once( dirname(__FILE__) . '/vendor/Racecore/GATracking/Autoloader.php');
    \Racecore\GATracking\Autoloader::register(dirname(__FILE__).'/vendor/');

    /**
     * Kladr\Core\Module
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
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
                    'Kladr\Core\Models'          => $config->application->modelsDir,
                    'Kladr\Core\Views'           => $config->application->viewsDir,
                    'Kladr\Core\Controllers'     => $config->application->controllersDir, 
                    'Kladr\Core\Services'        => $config->application->servicesDir, 
                    'Kladr\Core\Plugins'         => $config->application->pluginsDir,
                    'Kladr\Core\Plugins\Base'    => $config->application->pluginsBaseDir,
                    'Kladr\Core\Plugins\General' => $config->application->pluginsGeneralDir,
                    'Kladr\Core\Plugins\Tools'   => $config->application->pluginsToolsDir,
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

            // Setting up dispatcher
            $di->set('dispatcher', function() use ($di) {
                $dispatcher = new \Phalcon\Mvc\Dispatcher();
                $dispatcher->setDefaultNamespace('Kladr\Core\Controllers');
                return $dispatcher;
            });
            
            // Setting memcache
            $di->set('memcache', function() use ($config) {
                $frontCache = new \Phalcon\Cache\Frontend\Data(array(
                    "lifetime" => 86400
                ));
                $cache = new \Phalcon\Cache\Backend\Memcache($frontCache, array(
                    "host" => $config->memcache->host,
                    "port" => $config->memcache->port,
                ));                
                return $cache;
            });
            
            // Settings mongocache
            $di->set('mongocache', function() use ($config) {
                $frontCache = new \Phalcon\Cache\Frontend\Data(array(
                    "lifetime" => 86400
                ));
                $cache = new \Phalcon\Cache\Backend\Mongo($frontCache, array(
                    'server'     => 'mongodb://' . $config->mongocache->host,
                    'db'         => $config->mongocache->db,
                    'collection' => $config->mongocache->collection,
                ));                
                return $cache;
            });

            // Setting cache
            $di->set('cache', array(
                'className' => '\Kladr\Core\Plugins\Tools\Cache',
                'properties' => array(
                    array(
                        'name' => 'cache',
                        'value' => array('type' => 'service', 'name' => 'mongocache')
                    )
                )
            ));

            // Register validate plugin
            $di->set('validate', function(){
                return new \Kladr\Core\Plugins\General\ValidatePlugin();
            });

            // Register find plugin
            $di->set('find', array(
                'className' => '\Kladr\Core\Plugins\General\FindPlugin',
                'properties' => array(
                    array(
                        'name' => 'cache',
                        'value' => array('type' => 'service', 'name' => 'cache')
                    )
                )
            ));
            
            // Register special cases plugin
            $di->set('specialCases', array(
                'className' => '\Kladr\Core\Plugins\General\SpecialCasesPlugin',
                'properties' => array(
                    array(
                        'name' => 'cache',
                        'value' => array('type' => 'service', 'name' => 'cache')
                    )
                )
            ));
            
            // Register duplicate plugin
            $di->set('duplicate', array(
                'className' => '\Kladr\Core\Plugins\General\DuplicatePlugin',
                'properties' => array(
                    array(
                        'name' => 'cache',
                        'value' => array('type' => 'service', 'name' => 'cache')
                    )
                )
            ));

            // Register find parents plugin
            $di->set('findParents', array(
                'className' => '\Kladr\Core\Plugins\General\FindParentsPlugin',
                'properties' => array(
                    array(
                        'name' => 'cache',
                        'value' => array('type' => 'service', 'name' => 'cache')
                    )
                )
            ));
            
            // Register find parents plugin
            $di->set('parentsSpecialCases', array(
                'className' => '\Kladr\Core\Plugins\General\ParentsSpecialCasesPlugin',
                'properties' => array(
                    array(
                        'name' => 'cache',
                        'value' => array('type' => 'service', 'name' => 'cache')
                    )
                )
            ));

            $di->set('apiTracker', function() use($config){
                return new \Racecore\GATracking\GATracking($config->ga->code);
            });

            // Setting api
            $di->setShared('api', function() use ($di) {
                $api = new Services\ApiService($di->get('apiTracker'));
                $api->addPlugin($di->get('validate'));
                $api->addPlugin($di->get('find'));   
                $api->addPlugin($di->get('specialCases')); 
                $api->addPlugin($di->get('duplicate')); 
                $api->addPlugin($di->get('findParents'));
                $api->addPlugin($di->get('parentsSpecialCases'));
                return $api;
            });

            // Setting up the view component
            $di->set('view', function() use ($config) {
                $view = new \Phalcon\Mvc\View();
                $view->setViewsDir($config->application->viewsDir);
                return $view;
            });
        }
    }
 
} 