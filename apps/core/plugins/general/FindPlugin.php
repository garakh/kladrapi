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
    class FindPlugin extends Plugin implements IPlugin
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

            $objects = $this->cache->get('FindPlugin', $request);

            if ($objects === null)
            {
                $objects = array();

                // query
                $query = $request->getQuery('query');
                $query = Tools::Key($query);
                $query = Tools::Normalize($query);

                $arCodes = null;

                // regionId
                $regionId = $request->getQuery('regionId');
                if ($regionId)
                {
                    $arCodes = $request->getQuery('contentType') == 'region' ?
                            $regionId : Regions::getCodes($regionId);
                }

                // districtId
                $districtId = $request->getQuery('districtId');
                if ($districtId)
                {
                    $arCodes = $request->getQuery('contentType') == 'district' ?
                            $districtId : Districts::getCodes($districtId);
                }

                // cityId
                $cityId = $request->getQuery('cityId');
                if ($cityId)
                {
                    $arCodes = $request->getQuery('contentType') == 'city' ?
                            $cityId : Cities::getCodes($cityId);
                }

                // streetId
                $streetId = $request->getQuery('streetId');
                if ($streetId)
                {
                    $arCodes = $request->getQuery('contentType') == 'street' ?
                            $streetId : Streets::getCodes($streetId);
                }

                // buildingId
                $buildingId = $request->getQuery('buildingId');
                if ($buildingId)
                {
                    $arCodes = Buildings::getCodes($buildingId);
                }

                // limit
                $limit = $request->getQuery('limit');
                $limit = intval($limit);
                $limit = $limit ? $limit : 400;
                if ($limit > 400)
                    $limit = 400;

                //offset
                $offset = $request->getQuery('offset');
                $offset = intval($offset);



                switch ($request->getQuery('contentType'))
                {
                    case Regions::ContentType:
                        $objects = Regions::findByQuery($query, $arCodes, $limit, $offset);
                        break;
                    case Districts::ContentType:
                        $objects = Districts::findByQuery($query, $arCodes, $limit, $offset);
                        break;
                    case Cities::ContentType:
                        $objects = Cities::findByQuery($query, $arCodes, $limit, $offset);
                        break;
                    case Streets::ContentType:
                        $objects = Streets::findByQuery($query, $arCodes, $limit, $offset);
                        break;
                    case Buildings::ContentType:
                        $objects = Buildings::findByQuery($query, $arCodes, $limit);
                        break;
                }

                $this->cache->set('FindPlugin', $request, $objects);
            }

            $result = $prevResult;
            $result->result = $objects;
            //$result->searchContext=$request; //test
            return $result;
        }

    }

}