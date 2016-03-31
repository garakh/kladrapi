<?php

namespace Kladr\Core\Plugins\General {

    use \Phalcon\Http\Request,
        \Phalcon\Mvc\User\Plugin,
        \Kladr\Core\Plugins\Base\IPlugin,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Plugins\Tools\Tools,
        \Kladr\Core\Models\Regions,
		\Kladr\Core\Models\KladrFields,
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

            if ($prevResult->error || $prevResult->isPluginDisabled('FindPlugin'))
            {
                return $prevResult;
            }

            $objects = null;
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
                    if($request->getQuery('contentType') == 'city')
					{
						$arCodes = $cityId;
					}
					else
					{
						$arCodes = Cities::getCodes($cityId);
						$cityCode = $arCodes[KladrFields::CodeLocality];
						$cityCodeOwner = $cityCode - ($cityCode % 1000);
						if($cityCode == $cityCodeOwner)
						{
							$cityCodeOwnerNext = $cityCodeOwner + 1000;
							$arCodes[KladrFields::CodeLocality] = array(
								'$gte' => $cityCodeOwner,
								'$lt' => $cityCodeOwnerNext
							);
						}
					}
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

                // zip
                $zip = (int) $request->getQuery('zip');
                if ($zip <= 0 || $zip > 999999)
                    $zip = null;

                // limit
                $limit = $request->getQuery('limit');
                $limit = intval($limit);
                $limit = $limit ? $limit : 400;
                if ($limit > 400)
                    $limit = 400;

                //offset
                $offset = $request->getQuery('offset');
                $offset = intval($offset);
                
                //strict 
                $strict = trim($request->getQuery('strict'));
                $strict = $strict && $strict != '' ? true : false;

                $typeCodes = self::ConvertCodeTypeToArray($request->getQuery('typeCode'));

                switch ($request->getQuery('contentType'))
                {
                    case Regions::ContentType:
                        $objects = Regions::findByQuery($query, $arCodes, $limit, $offset);
                        break;
                    case Districts::ContentType:
                        $objects = Districts::findByQuery($query, $arCodes, $limit, $offset);
                        break;
                    case Cities::ContentType:
                        $objects = Cities::findByQuery($query, $arCodes, $limit, $offset, $typeCodes, $strict);
                        break;
                    case Streets::ContentType:
                        $objects = Streets::findByQuery($query, $arCodes, $limit, $offset);
                        break;
                    case Buildings::ContentType:
                        $objects = Buildings::findByQuery($query, $arCodes, $limit, $zip);
                        break;
                }

                $this->cache->set('FindPlugin', $request, $objects);
            }

            $result = $prevResult;
            $result->result = $objects;

            return $result;
        }

        /**
         * Преобразует TypeCode в массив для поиска
         * 
         * @param int $typeCode
         * @return array | null Массив из TypeCode или null, если TypeCode учитывать не надо
         */
        public static function ConvertCodeTypeToArray($typeCode)
        {
            $typeCode = (int) $typeCode;

            // проверяем валидность. typeCode = 7 так же не нужен, т.к. это 0111, т.е. все варианты
            if ($typeCode <= 0 || $typeCode > 6)
                return null;


            $result = array();
            foreach (array(1, 2, 4) as $code)
                if (($typeCode & $code) > 0)
                    $result [] = $code;

            return $result;
        }

    }

}