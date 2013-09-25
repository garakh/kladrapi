<?php

namespace Kladr\Core\Plugins\General {

    use \Phalcon\Http\Request,
        \Phalcon\Mvc\User\Plugin,
        \Kladr\Core\Plugins\Base\IPlugin,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Plugins\Tools\Tools,
        \Kladr\Core\Models\Regions,
        \Kladr\Core\Models\Districts,
        \Kladr\Core\Models\Cities,
        \Kladr\Core\Models\Streets,
        \Kladr\Core\Models\Buildings;

    /**
     * Kladr\Core\Plugins\General\FindPlugin
     * 
     * Плагин для поиска объектов
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class SpecialCasesPlugin extends Plugin implements IPlugin
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
            
            $arSpecialCases = array(
                'regionId' => array(
                    '7700000000000' => '5000000000000',
                    '7800000000000' => '4700000000000',
                    '7800000000001' => '4700000000000',
                )
            );
            
            $paramName = null;
            $paramValue = null;
            
            foreach($arSpecialCases as $param => $cases){
                $value = $request->getQuery($param);
                if($cases[$value]){
                    $paramName = $param;
                    $paramValue = $cases[$value];
                }
            }
            
            if(!$paramName || !$paramValue){
                return $prevResult;
            }

            $objects = $this->cache->get('SpecialCasesPlugin', $request);

            if($objects === null)
            {
                $objects = array();

                // query
                $query = $request->getQuery('query');
                $query = Tools::Key($query);
                $query = Tools::Normalize($query);       

                $arCodes = array();                
                switch($paramName)
                {
                    case 'regionId':
                        $arCodes = Regions::getCodes($paramValue);
                        break;
                    case 'districtId':
                        $arCodes = Districts::getCodes($paramValue);
                        break;
                    case 'cityId':
                        $arCodes = Cities::getCodes($paramValue);
                        break;
                    case 'streetId':
                        $arCodes = Streets::getCodes($paramValue);
                        break;
                    case 'buildingId':
                        $arCodes = Buildings::getCodes($paramValue);
                        break;
                }

                // limit
                $limit = $request->getQuery('limit');
                $limit = intval($limit);
                $limit = $limit ? $limit : 5000;

                switch ($request->getQuery('contentType')) {
                    case 'region':
                        $objects = Regions::findByQuery($query, $arCodes, $limit);
                        break;
                    case 'district':
                        $objects = Districts::findByQuery($query, $arCodes, $limit);
                        break;
                    case 'city':
                        $objects = Cities::findByQuery($query, $arCodes, $limit);
                        break;
                    case 'street':
                        $objects = Streets::findByQuery($query, $arCodes, $limit);
                        break;
                    case 'building':
                        $objects = Buildings::findByQuery($query, $arCodes, $limit);
                        break;
                }

                $this->cache->set('SpecialCasesPlugin', $request, $objects);
            }

            $result = $prevResult;
            $result->result = array_merge($prevResult->result, $objects); 
            return $result;
        }
        
    }

}