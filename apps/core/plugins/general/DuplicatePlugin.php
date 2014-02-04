<?php

namespace Kladr\Core\Plugins\General {

    use \Phalcon\Http\Request,
        \Phalcon\Mvc\User\Plugin,
        \Kladr\Core\Plugins\Base\IPlugin,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Plugins\Tools\Tools,
        \Kladr\Core\Models\KladrFields,
        \Kladr\Core\Models\Regions,
        \Kladr\Core\Models\Districts,
        \Kladr\Core\Models\Cities,
        \Kladr\Core\Models\Streets,
        \Kladr\Core\Models\Buildings;

    /**
     * Kladr\Core\Plugins\General\DuplicatePlugin
     * 
     * Плагин для работы с дубликатами объектов
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class DuplicatePlugin  extends Plugin implements IPlugin
    {
        /**
         * Массив дубликатов
         * 
         * @var string[] 
         */
        private $arDuplicates;
        
        /**
         * Кэш
         * 
         * @var Kladr\Core\Plugins\Tools\Cache 
         */
        public $cache;
        
        public function __construct() 
        {
            $this->arDuplicates = array(
                
                // г. Тверь
                '6900000100000' => '6900100100051',
                '6900100100051' => '6900000100000',
                
                // г. Орел 
                '5700000100000' => '5700100100051',
                '5700100100051' => '5700000100000',
                
                // г. Великий Новгород
                '5300000100000' => '5300100100051',
                '5300100100051' => '5300000100000'
            );
        }

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

            $objects = $this->cache->get('DuplicatePlugin', $request);

            if($objects === null)
            {
                $objects = array();
                $arCodes = array();
                
                $existDuplicate = false;

                // regionId
                $regionId = $request->getQuery('regionId');
                if ($regionId && $this->arDuplicates[$regionId]) {
                    $arCodes = Regions::getCodes($this->arDuplicates[$regionId]);
                    $existDuplicate = true;
                }

                // districtId
                $districtId = $request->getQuery('districtId');
                if ($districtId && $this->arDuplicates[$districtId]) {
                    $arCodes = Districts::getCodes($this->arDuplicates[$districtId]);
                    $existDuplicate = true;
                }

                // cityId
                $cityId = $request->getQuery('cityId');
                if ($cityId && $this->arDuplicates[$cityId]) {
                    $arCodes = Cities::getCodes($this->arDuplicates[$cityId]);
                    $existDuplicate = true;
                }

                // streetId
                $streetId = $request->getQuery('streetId');
                if ($streetId && $this->arDuplicates[$streetId]) {
                    $arCodes = Streets::getCodes($streetId);
                    $existDuplicate = true;
                }

                // buildingId
                $buildingId = $request->getQuery('buildingId');
                if ($buildingId && $this->arDuplicates[$buildingId]) {
                    $arCodes = Buildings::getCodes($this->arDuplicates[$buildingId]);
                    $existDuplicate = true;
                }
                
                if($existDuplicate) {
                    // query
                    $query = $request->getQuery('query');
                    $query = Tools::Key($query);
                    $query = Tools::Normalize($query);  

                    // limit
                    $limit = $request->getQuery('limit');
                    $limit = intval($limit);
                    $limit = $limit ? $limit : 5000;

                    switch ($request->getQuery('contentType')) {
                        case Regions::ContentType:
                            $objects = Regions::findByQuery($query, $arCodes, $limit);
                            break;
                        case Districts::ContentType:
                            $objects = Districts::findByQuery($query, $arCodes, $limit);
                            break;
                        case Cities::ContentType:
                            $objects = Cities::findByQuery($query, $arCodes, $limit);
                            break;
                        case Streets::ContentType:
                            $objects = Streets::findByQuery($query, $arCodes, $limit);
                            break;
                        case Buildings::ContentType:
                            $objects = Buildings::findByQuery($query, $arCodes, $limit);
                            break;
                    }
                }

                $this->cache->set('DuplicatePlugin', $request, $objects);
            }

            $result = $prevResult;
            $objects = array_merge($prevResult->result, $objects);
            
            $arResult = array();
            $arIgnore = array();
            foreach($objects as $object){
                $ignoreId = $this->arDuplicates[$object['id']];
                
                if($ignoreId){
                    $arIgnore[] = $ignoreId;
                }
                
                if(!in_array($object['id'], $arIgnore)){
                    $arResult[] = $object;
                }
            }
            
            $result->result = $objects;          
            return $result;
        }
        
    }
    
}