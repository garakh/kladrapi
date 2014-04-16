<?php

namespace Kladr\Core\Plugins\Base {

    use \Phalcon\Http\Request;

    /**
     * Kladr\Core\Plugins\Base\IPlugin
     * 
     * Интерфейс плагина
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    interface IPlugin
    {

        /**
         * Выполняет обработку запроса
         * 
         * @param \Phalcon\Http\Request $request Запрос
         * @param \Kladr\Core\Plugins\Base\PluginResult $prevResult Результат работы предыдущего плагина в цепочке
         * @return \Kladr\Core\Plugins\Base\PluginResult
         */
        public function process(Request $request, PluginResult $prevResult);
    }

}