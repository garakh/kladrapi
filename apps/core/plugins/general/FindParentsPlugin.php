<?php

namespace Kladr\Core\Plugins\General
{

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
         * @param string $cityOwnerId ID города-родителя объекта / временный хак
         * @return array Массив родителей объекта
         */
        public static function findParents($arCodes, $cityOwnerId = null)
        {
            if (!$arCodes)
                return array();

            $arCodes = array_slice($arCodes, 0, count($arCodes) - 1);

            $arReturn = array();
            $object = array();
            foreach ($arCodes as $field => $code)
            {
                $contentType = '';
                switch ($field)
                {
                    case KladrFields::CodeRegion:
                        $object = Regions::findFirst(array(array(
                                        KladrFields::CodeRegion => $arCodes[KladrFields::CodeRegion],
                                        KladrFields::Bad => false
                        )));
                        $contentType = Regions::ContentType;
                        break;
                    case KladrFields::CodeDistrict:
                        $object = Districts::findFirst(array(array(
                                        KladrFields::CodeRegion => $arCodes[KladrFields::CodeRegion],
                                        KladrFields::CodeDistrict => $arCodes[KladrFields::CodeDistrict],
                                        KladrFields::Bad => false
                        )));
                        $contentType = Districts::ContentType;
                        break;
                    case KladrFields::CodeLocality:
                        $object = Cities::findFirst(array(array(
                                        KladrFields::CodeRegion => $arCodes[KladrFields::CodeRegion],
                                        KladrFields::CodeDistrict => $arCodes[KladrFields::CodeDistrict],
                                        KladrFields::CodeLocality => $arCodes[KladrFields::CodeLocality],
                                        KladrFields::Bad => false
                        )));
                        $contentType = Cities::ContentType;
                        break;
                    case KladrFields::CodeStreet:
                        $object = Streets::findFirst(array(array(
                                        KladrFields::CodeRegion => $arCodes[KladrFields::CodeRegion],
                                        KladrFields::CodeDistrict => $arCodes[KladrFields::CodeDistrict],
                                        KladrFields::CodeLocality => $arCodes[KladrFields::CodeLocality],
                                        KladrFields::CodeStreet => $arCodes[KladrFields::CodeStreet],
                                        KladrFields::Bad => false
                        )));
                        $contentType = Streets::ContentType;
                        break;
                }

                if ($object)
                {
                    $id = $object->readAttribute(KladrFields::Id);
                    $zip = $object->readAttribute(KladrFields::ZipCode);
                    $zip = (int) $zip;
                    if ($zip == 0)
                    {
                        $zip = Buildings::getZipById($id);
                    }

                    $arReturn[] = array(
                        'id' => $object->readAttribute(KladrFields::Id),
                        'name' => $object->readAttribute(KladrFields::Name),
                        'zip' => $zip,
                        'type' => $object->readAttribute(KladrFields::Type),
                        'typeShort' => $object->readAttribute(KladrFields::TypeShort),
                        'okato' => $object->readAttribute(KladrFields::Okato),
                        'contentType' => $contentType
                    );
                }
            }

            if ($cityOwnerId)
            {
                $owner = Cities::findByQuery(null, $cityOwnerId);
                if ($owner && is_array($owner) && count($owner) > 0)
                {
                    $owner = $owner[0];
                    $owner['contentType'] = 'cityOwner';
                    $arReturn[] = $owner;
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
            if ($prevResult->error)
            {
                return $prevResult;
            }

            if (!$request->getQuery('withParent'))
            {
                return $prevResult;
            }

            $objects = $this->cache->get('FindParentsPlugin', $request);

            $result = $prevResult;

            if ($objects === null)
            {
                $objects = $result->result;

                foreach ($objects as $key => $object)
                {
                    switch ($objects[$key]['contentType'])
                    {
                        case Regions::ContentType:
                            $objects[$key]['parents'] = self::findParents(Regions::getCodes($object['id']));
                            break;
                        case Districts::ContentType:
                            $objects[$key]['parents'] = self::findParents(Districts::getCodes($object['id']));
                            break;
                        case Cities::ContentType:
                            $objects[$key]['parents'] = self::findParents(Cities::getCodes($object['id']));
                            break;
                        case Streets::ContentType:
                            $objects[$key]['parents'] = self::findParents(Streets::getCodes($object['id']));
                            break;
                        case Buildings::ContentType:
                            $objects[$key]['parents'] = self::findParents(Buildings::getCodes($object['id']));
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
