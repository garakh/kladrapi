<?php

namespace Kladr\Core\Plugins\General {

    use \Phalcon\Http\Request,
        \Phalcon\Mvc\User\Plugin,
        \Kladr\Core\Plugins\Base\IPlugin,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Models\KladrFields,
        \Kladr\Core\Models\Regions,
        \Kladr\Core\Models\Districts,
        \Kladr\Core\Models\Cities,
        \Kladr\Core\Models\Streets,
        \Kladr\Core\Models\Buildings;

    /**
     * Kladr\Core\Plugins\General\FindParentsPlugin
     * 
     * Плагин для поиска родителей объектов
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class ParentsSpecialCasesPlugin extends Plugin implements IPlugin
    {

        /**
         * Кэш
         * 
         * @var Kladr\Core\Plugins\Tools\Cache 
         */
        public $cache;

        /**
         * Выполняет обработку запроса
         * 
         * @param \Phalcon\Http\Request $request
         * @param \Kladr\Core\Plugins\Base\PluginResult $prevResult
         * @return \Kladr\Core\Plugins\Base\PluginResult
         */
        public function process(Request $request, PluginResult $prevResult) 
        {
            if($prevResult->error){
                return $prevResult;
            }
            
            if(!$request->getQuery('withParent')){
                return $prevResult;
            }
            
            $arSpecialCases = array(
                '7700000000000',
                '7800000000000',
                '7800000000001',
                '9900000000000'
            );
            
            $result = $prevResult;            
            foreach($result->result as $key => $obj){
                if(in_array($obj['id'], $arSpecialCases)){
                    $result->result[$key]['parents'] = array();
                }
            }
            return $result;
        }
        
    }

}