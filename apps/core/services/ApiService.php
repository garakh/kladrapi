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
    class ApiService
    {

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
        public function __construct($googleTracker)
        {
            $this->_arPlugins = array();
            $this->googleTracker = $googleTracker;
        }

        /**
         * Добавляет плагин в цепочку плагинов
         * 
         * @param \Kladr\Core\Plugins\Base\IPlugin $plugin
         */
        public function addPlugin(IPlugin $plugin)
        {
            array_push($this->_arPlugins, $plugin);
        }

        /**
         * Выполняет все плагины по цепочке
         * 
         * @param \Phalcon\Http\Request $request
         * @return string
         */
        public function process(Request $request)
        {
            $response = new Response();
            $pluginResult = new PluginResult();

            foreach ($this->_arPlugins as $plugin)
            {
                $pluginResult = $plugin->process($request, $pluginResult);
                if ($pluginResult->terminate)
                {
                    break;
                }
            }

            if ($pluginResult->error)
            {
                $response->setStatusCode($pluginResult->errorCode, $pluginResult->errorMessage);
                $response->setContent($pluginResult->errorMessage);
                return $response;
            }
            else
            {
                $response->setStatusCode(200, "OK");
            }

            $callback = $request->getQuery('callback');
            $result = '';

            if ($callback)
            {
                $response->setHeader('Content-Type', 'application/javascript');
                $result .= $callback . '(';
            }
            else
            {
                $response->setHeader('Content-Type', 'application/json');
            }

            $result .= json_encode(array(
                'searchContext' => $pluginResult->searchContext,
                'result' => $pluginResult->result
            ));

            if ($callback)
            {
                $result .= ');';
            }

            if ($pluginResult->fileToSend)
            {
                $response->setFileToSend($pluginResult->fileToSend, 'data.txt');
            }
            else
            {
                $response->setContent($result);
            }
            return $response;
        }

        /**
         * Логирует запрос
         * @param \Phalcon\Http\Request $request
         */
        public function log(Request $request)
        {
	    if(!$this->googleTracker)
		return;

	    $token = trim($request->get('token'));
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $host = parse_url($referer);
            $host = $host['host'];

            if($token != '')
                $this->googleTracker->setClientID($token);

            $page = new \Racecore\GATracking\Tracking\Page();
            $page->setDocumentPath($referer != '' ? $referer : '/');
            $page->setDocumentTitle($referer != '' ? $referer : 'Direct');

            $this->googleTracker->addTracking($page);

            $event = new \Racecore\GATracking\Tracking\Event();
            $event->setEventCategory('Token_' . $request->get('token'));
            $event->setEventLabel($host);
            $event->setEventAction('Hit');

            $this->googleTracker->addTracking($event);

            try
            {
                $this->googleTracker->send();
            } catch (Exception $e)
            {
                //echo 'Error: ' . $e->getMessage() . '<br />' . "\r\n";
                //echo 'Type: ' . get_class($e);
            }
        }

    }

}