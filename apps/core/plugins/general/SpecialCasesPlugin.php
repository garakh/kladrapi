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
     * Kladr\Core\Plugins\General\SpecialCasesPlugin
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
            if ($prevResult->error)
            {
                return $prevResult;
            }

            // Проверяем, что ищем город
            // с принадлежностью региону

            if ($request->getQuery('contentType') != 'city')
                return $prevResult;

            if (!$request->getQuery('regionId'))
                return $prevResult;

            // добавляем к результату поиск в другом регионе
            $arRegionCodeSpecialCases = array(
                //77 => 50, // Москва => Московская область
                50 => 77, // Московская область => Москва
                //78 => 47, // Санкт-Петербург => Ленинградская область
                47 => 78  // Ленинградская область => Санкт-Петербург
            );

            $objects = $this->cache->get('SpecialCasesPlugin', $request);

            if ($objects === null)
            {
                $objects = array();
                $arCodes = array();

                // regionId
                $regionId = $request->getQuery('regionId');
                if ($regionId)
                {
                    $arCodes = Regions::getCodes($regionId);
                }

                // districtId
                $districtId = $request->getQuery('districtId');
                if ($districtId)
                {
                    $arCodes = Districts::getCodes($districtId);
                }

                // cityId
                $cityId = $request->getQuery('cityId');
                if ($cityId)
                {
                    $arCodes = Cities::getCodes($cityId);
                }

                // streetId
                $streetId = $request->getQuery('streetId');
                if ($streetId)
                {
                    $arCodes = Streets::getCodes($streetId);
                }

                // buildingId
                $buildingId = $request->getQuery('buildingId');
                if ($buildingId)
                {
                    $arCodes = Buildings::getCodes($buildingId);
                }

                if (!isset($arCodes[KladrFields::CodeRegion]) &&
                        !isset($arRegionCodeSpecialCases[$arCodes[KladrFields::CodeRegion]]))
                    return $prevResult;
                $arCodes[KladrFields::CodeRegion] = $arRegionCodeSpecialCases[$arCodes[KladrFields::CodeRegion]];

                // query
                $query = $request->getQuery('query');
                $query = Tools::Key($query);
                $query = Tools::Normalize($query);

                // limit
                $limit = $request->getQuery('limit');
                $limit = intval($limit);
                $limit = $limit ? $limit : 5000;
                if ($limit > 400)
                    $limit = 400;

                switch ($request->getQuery('contentType'))
                {
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

                $this->cache->set('SpecialCasesPlugin', $request, $objects);
            }

            $result = $prevResult;
            $result->result = array_merge($prevResult->result, $objects);
            return $result;
        }

    }

}
