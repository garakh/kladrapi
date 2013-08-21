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
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class ApiService
    {
        private $_arPlugins;

        /**
         * Kladr\Core\Services\ApiService construct
         */
        public function __construct() {
            $this->_arPlugins = array();
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

            foreach($this->_arPlugins as $plugin){
                $pluginResult = $plugin->process($request, $pluginResult);
                if($pluginResult->terminate){
                    break;
                }
            }

            if($pluginResult->error){
                $response->setStatusCode($pluginResult->errorCode, $pluginResult->errorMessage);
            } else {
                $response->setStatusCode(200, "OK");
            }

            $callback = $request->getQuery('callback');
            $result = '';

            if($callback){
                $response->setHeader('Content-Type', 'application/javascript');
                $result .= $callback . '(';
            } else {
                $response->setHeader('Content-Type', 'application/json');
            }

            $result .= json_encode(array(
                'searchContext' => $pluginResult->searchContext,
                'result' => $pluginResult->result
            ));

            if($callback){
                $result .= ');';
            }

            $response->setContent($result);        
            return $response;
        }

    }

}