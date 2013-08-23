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
    class FindParentsPlugin extends Plugin implements IPlugin
    {

        /**
         * Кэш
         * 
         * @var Kladr\Core\Plugins\Tools\Cache 
         */
        public $cache;

        /**
         * Метод для поиска родителей объекта
         * 
         * @param array $arCodes Массив кодов объекта
         * @return array Массив родителей объекта
         */
        public function findParents($arCodes)
        {
            if(!$arCodes) return array();

            $arCodes = array_slice($arCodes, 0, count($arCodes)-1);

            $arReturn =  array();
            $object = array();
            foreach($arCodes as $field => $code){
                switch($field)
                {
                    case KladrFields::CodeRegion:
                        $object = Regions::findFirst(array(array(
                            KladrFields::CodeRegion   => $arCodes[KladrFields::CodeRegion],
                        )));
                        break;
                    case KladrFields::CodeDistrict:
                        $object = Districts::findFirst(array(array(
                            KladrFields::CodeRegion   => $arCodes[KladrFields::CodeRegion],
                            KladrFields::CodeDistrict => $arCodes[KladrFields::CodeDistrict],
                        )));
                        break;
                    case KladrFields::CodeLocality:
                        $object = Cities::findFirst(array(array(
                            KladrFields::CodeRegion   => $arCodes[KladrFields::CodeRegion],
                            KladrFields::CodeDistrict => $arCodes[KladrFields::CodeDistrict],
                            KladrFields::CodeLocality => $arCodes[KladrFields::CodeLocality],
                        )));
                        break;
                    case KladrFields::CodeStreet:
                        $object = Streets::findFirst(array(array(
                            KladrFields::CodeRegion   => $arCodes[KladrFields::CodeRegion],
                            KladrFields::CodeDistrict => $arCodes[KladrFields::CodeDistrict],
                            KladrFields::CodeLocality => $arCodes[KladrFields::CodeLocality],
                            KladrFields::CodeStreet   => $arCodes[KladrFields::CodeStreet],
                        )));
                        break;
                }

                if($object){
                    $arReturn[] = array(
                        'id'        => $object->readAttribute(KladrFields::Id),
                        'name'      => $object->readAttribute(KladrFields::Name),
                        'zip'       => $object->readAttribute(KladrFields::ZipCode),
                        'type'      => $object->readAttribute(KladrFields::Type),
                        'typeShort' => $object->readAttribute(KladrFields::TypeShort),
                        'okato'     => $object->readAttribute(KladrFields::Okato),
                    );
                }
            }

            return $arReturn;
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

            $objects = $this->cache->get('FindParentsPlugin', $request);

            $result = $prevResult;

            if($objects === null)
            {
                $objects = $result->result;

                if($request->getQuery('withParent')){
                    switch ($request->getQuery('contentType')) {
                        case 'region':
                            foreach($objects as $key => $object){
                                $objects[$key]['parents'] = $this->findParents(Regions::getCodes($object['id']));
                            }
                            break;
                        case 'district':
                            foreach($objects as $key => $object){
                                $objects[$key]['parents'] = $this->findParents(Districts::getCodes($object['id']));
                            }
                            break;
                        case 'city':
                            foreach($objects as $key => $object){
                                $objects[$key]['parents'] = $this->findParents(Cities::getCodes($object['id']));
                            }
                            break;
                        case 'street':
                            foreach($objects as $key => $object){
                                $objects[$key]['parents'] = $this->findParents(Streets::getCodes($object['id']));
                            }
                            break;
                        case 'building':
                            foreach($objects as $key => $object){
                                $objects[$key]['parents'] = $this->findParents(Buildings::getCodes($object['id']));
                            }
                            break;
                    }
                }

                $this->cache->set('FindParentsPlugin', $request, $objects);
            }

            $result->result = $objects;        
            return $result;
        }
        
    }

}