<?php
/**

Phalcon v2.x.x support

**/
namespace Kladr\Core {

    use Phalcon\DI\Service;

//GA
    require_once( dirname(__FILE__) . '/vendor/Racecore/GATracking/Autoloader.php');
    \Racecore\GATracking\Autoloader::register(dirname(__FILE__) . '/vendor/');

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
        public function registerAutoloaders(\Phalcon\DiInterface $dependencyInjector = NULL)
        {
            $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/config/config.ini');
            $loader = new \Phalcon\Loader();

            $loader->registerNamespaces(
                    array(
                        'Kladr\Core\Models' => $config->application->modelsDir,
                        'Kladr\Core\Views' => $config->application->viewsDir,
                        'Kladr\Core\Controllers' => $config->application->controllersDir,
                        'Kladr\Core\Services' => $config->application->servicesDir,
                        'Kladr\Core\Plugins' => $config->application->pluginsDir,
                        'Kladr\Core\Plugins\Base' => $config->application->pluginsBaseDir,
                        'Kladr\Core\Plugins\General' => $config->application->pluginsGeneralDir,
                        'Kladr\Core\Plugins\Tools' => $config->application->pluginsToolsDir,

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
                $mongo = new \MongoClient($config->database->host, array(
                    'connectTimeoutMS' => intval($config->database->timeout),
                ));
                return $mongo->selectDb($config->database->name);
            }, true);


            // Mongo with users
            $di->set('mongoUsers', function() use ($config) {
                $mongo = new \MongoClient($config->database->usersHost, array(
                    'connectTimeoutMS' => intval($config->database->timeout),
                ));
                return $mongo->selectDb($config->database->usersName);
            }, true);

            // Mongo with users
            $di->set('mongoLog', function() use ($config) {
                $mongo = new \MongoClient($config->database->logHost, array(
                    'connectTimeoutMS' => intval($config->database->timeout),
                ));
                return $mongo->selectDb($config->database->logName);
            }, true);

            // Service for running users
            $di->set('userService', '\Kladr\Core\Services\UserService');

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
                    'server' => 'mongodb://' . $config->mongocache->host,
                    'db' => $config->mongocache->db,
                    'collection' => $config->mongocache->collection,
                    'connectTimeoutMS' => intval($config->mongocache->timeout),
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
                    ),
                    array(
                        'name' => 'config',
                        'value' => array('type' => 'service', 'name' => 'config')
                    )
                )
            ));
            
            //setting sphinxapi
            $di->set('sphinxapi', function() use ($config) {
                    include (dirname(__FILE__) . '/plugins/tools/sphinxapi.php');
                    $sphinxapi = new \SphinxClient();
                    $sphinxapi->SetServer($config->sphinxapi->server, $config->sphinxapi->port);
                    return $sphinxapi;               
                }
            );

            // Register validate plugin
            $di->set('validate', function() {
                return new \Kladr\Core\Plugins\General\ValidatePlugin();
            });
            
            // Register oneString plugin
            $di->set('oneString', array(
                'className' => '\Kladr\Core\Plugins\General\OneStringPlugin',
                'properties' => array(
                    array(
                        'name' => 'cache',
                        'value' => array('type' => 'service', 'name' => 'cache')
                    ),
                    array(
                        'name' => 'sphinxClient',
                        'value' => array('type' => 'service', 'name' => 'sphinxapi')
                    )   
                )               
            ));

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

            // Register find parents plugin
            $di->set('logPaidUsersPlugin', array(
                'className' => '\Kladr\Core\Plugins\General\LogPaidUsersPlugin',
                'properties' => array(
                    array(
                        'name' => 'userService',
                        'value' => array('type' => 'service', 'name' => 'userService')
                    )
                )
            ));

            $di->set('allDataPlugin', array(
                'className' => '\Kladr\Core\Plugins\General\AllDataPlugin',
                'properties' => array(
                    array(
                        'name' => 'userService',
                        'value' => array('type' => 'service', 'name' => 'userService')
                    ),
                    array(
                        'name' => 'cacheDir',
                        'value' => array('type' => 'parameter', 'value' => $config->application->cacheDir)
                    ),
                    array(
                        'name' => 'disablePaid',
                        'value' => array('type' => 'parameter', 'value' => $config->application->disablePaid)
                    )                    
                )
            ));

            $di->set('enabledTokensPlugin', '\Kladr\Core\Plugins\General\EnabledTokensPlugin');

            // Register GA
            $di->set('apiTracker', function() use($config) {
		if($config->ga->code == '')
		    return false;
		
                return new \Racecore\GATracking\GATracking($config->ga->code);
            });

            // Setting api
            $di->setShared('api', function() use ($di, $config) {
                $api = new Services\ApiService($di->get('apiTracker'));

                if($config->application->enableTokens)
                    $api->addPlugin($di->get('enabledTokensPlugin'));

                if($config->application->enableUserLog)
                    $api->addPlugin($di->get('logPaidUsersPlugin'));

                $api->addPlugin($di->get('allDataPlugin'));
                $api->addPlugin($di->get('validate'));

                if($config->sphinxapi->enabled)
                    $api->addPlugin($di->get('oneString'));

                $api->addPlugin($di->get('find'));
                //$api->addPlugin($di->get('specialCases'));
                //$api->addPlugin($di->get('duplicate'));
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