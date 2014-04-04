<?php

namespace Kladr\Core\Services {



    use \Kladr\Core\Plugins\Base\IPlugin,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Phalcon\Http\Request,
        \Phalcon\Http\Response;

    /**
     * Kladr\Core\Services\ApiService
     * 
     * Сервис для работы с api
     * 
     * @author A. Yakovlev / I. Garakh Primepix (http://primepix.ru/)
     */
    class ApiService {

        private $_arPlugins;

        /**
         * Трекер гугло аналитики
         * @var \Racecore\GATracking\GATracking
         */
        protected $googleTracker;

        /**
         * Kladr\Core\Services\ApiService construct
         * @param \Racecore\GATracking\GATracking $googleTracker Трекер
         */
        public function __construct($googleTracker) {
            $this->_arPlugins = array();
            $this->googleTracker = $googleTracker;
        }

        /**
         * Добавляет плагин в цепочку плагинов
         * 
         * @param \Kladr\Core\Plugins\Base\IPlugin $plugin
         */
        public function addPlugin(IPlugin $plugin) {
            array_push($this->_arPlugins, $plugin);
        }

        /**
         * Выполняет все плагины по цепочке
         * 
         * @param \Phalcon\Http\Request $request
         * @return string
         */
        public function process(Request $request) {
            $response = new Response();
            $pluginResult = new PluginResult();

            foreach ($this->_arPlugins as $plugin) {
                $pluginResult = $plugin->process($request, $pluginResult);
                if ($pluginResult->terminate) {
                    break;
                }
            }

            $result = '';

            if ($pluginResult->error) {
                $response->setStatusCode($pluginResult->errorCode, $pluginResult->errorMessage);
                $result .= json_encode(array(
                    'errorCode' => $pluginResult->errorCode,
                    'errorMessage' => $pluginResult->errorMessage
                ));
            } else {
                $response->setStatusCode(200, "OK");
                $result .= json_encode(array(
                    'searchContext' => $pluginResult->searchContext,
                    'result' => $pluginResult->result
                ));
            }

            $callback = $request->getQuery('callback');

            if ($callback) {
                $response->setHeader('Content-Type', 'application/javascript');
                $result .= $callback . '(';
            } else {
                $response->setHeader('Content-Type', 'application/json');
            }

            if ($callback) {
                $result .= ');';
            }

            $response->setContent($result);
            return $response;
        }

        /**
         * Логирует запрос
         * @param \Phalcon\Http\Request $request
         */
        public function log(Request $request) {
            $this->googleTracker->setClientID($request->get('token'));

            $page = new \Racecore\GATracking\Tracking\Page();
            $page->setDocumentPath($_SERVER['HTTP_REFERER'] != '' ? $_SERVER['HTTP_REFERER'] : '/');
            $page->setDocumentTitle($_SERVER['HTTP_REFERER'] != '' ? $_SERVER['HTTP_REFERER'] : 'Direct');

            $this->googleTracker->addTracking($page);

            try {
                $this->googleTracker->send();
            } catch (Exception $e) {
                //echo 'Error: ' . $e->getMessage() . '<br />' . "\r\n";
                //echo 'Type: ' . get_class($e);
            }
        }

    }

}