<?php

namespace Kladr\Core\Models
{

    use \Phalcon\Mvc\Collection;

    /**
     * Kladr\Core\Models\Buildings
     * 
     * Коллекция строений
     * 
     * @property string $Id Идентификатор
     * @property string $Name Название
     * @property string $NormalizedName Нормализованное название
     * @property string $ZipCode Почтовый индекс
     * @property string $Type Подпись
     * @property string $TypeShort Подпись коротко
     * @property string $Okato ОКАТО
     * @property int $CodeRegion Код региона
     * @property int $CodeDistrict Код района
     * @property int $CodeCity Код населённого пункта
     * @property int $CodeStreet Код улицы
     * @property int $CodeBuilding Код строения
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class Buildings extends Collection
    {

        /**
         * @var string Тип объекта
         */
        const ContentType = "building";

        /**
         * Кеш, чтоб снизить запросы к базе
         * @var array
         */
        private static $Cache = array();

        public function getSource()
        {
            return "buildings";
        }

        /**
         * Возвращает массив кодов текущего объекта
         * 
         * @param string $id
         * @return array
         */
        public static function getCodes($id)
        {

            if (isset(self::$Cache[$id]))
                return self::$Cache[$id];

            $object = self::findFirst(array(
                        array(KladrFields::Id => $id)
            ));

            if (!$object)
                return array();

            self::$Cache[$id] = array(
                KladrFields::CodeRegion => $object->readAttribute(KladrFields::CodeRegion),
                KladrFields::CodeDistrict => $object->readAttribute(KladrFields::CodeDistrict),
                KladrFields::CodeLocality => (int) $object->readAttribute(KladrFields::CodeLocality),
                KladrFields::CodeStreet => $object->readAttribute(KladrFields::CodeStreet),
                KladrFields::CodeBuilding => $object->readAttribute(KladrFields::CodeBuilding),
            );

            return self::$Cache[$id];
        }

        public static function getZipById($id)
        {

            /*
             * 
              СС РРР ГГГ ППП АА
              63 000 000 000 00 (13) (5)
              71 020 000 000 00 (13) (8)
              34 030 001 000 00 (13) (11)

              СС РРР ГГГ ППП УУУУ АА
              34 030 001 000 0081 00  (17) (15)

              СС РРР ГГГ ППП УУУУ ДДДД
              34 030 001 000 0000 0001  (19)


              6300000000000
              7102000000000
              3403000100000
              34030001000008100
              3403000100000000001
             */

            $id = "$id";

            $id = trim($id);
            if (!$id)
                return null;

            while (true)
            {

                $len = strlen($id);
                switch ($len)
                {
                    case 19:
                        $id = substr($id, 0, -4);  //СС РРР ГГГ ППП УУУУ ДДДД → СС РРР ГГГ ППП УУУУ
                        break;
                    case 17:
                        $id = substr($id, 0, -2); // СС РРР ГГГ ППП УУУУ АА → СС РРР ГГГ ППП УУУУ
                        break;
                    case 15:
                        $id = substr($id, 0, -4); // СС РРР ГГГ ППП УУУУ → СС РРР ГГГ ППП
                        break;
                    case 13:
                        $id = substr($id, 0, -2); // СС РРР ГГГ ППП АА → СС РРР ГГГ ППП
                        break;
                    case 11:
                        $id = substr($id, 0, -3); // СС РРР ГГГ ППП → СС РРР ГГГ
                        break;
                    case 8:
                        $id = substr($id, 0, -3); // СС РРР ГГГ → СС РРР
                        break;
                    case 5:
                        $id = substr($id, 0, -3); // СС РРР → СС
                        break;

                    default: return null;
                }

                $arQuery = array();
                $arQuery['conditions'] = array();
                $regexObj = new \MongoRegex('/^' . $id . '/');
                $arQuery['conditions'][KladrFields::Id] = $regexObj;
                $arQuery['conditions'][KladrFields::ZipCode] = array('$ne' => 0);
                $arQuery['limit'] = 1;
                $buildings = self::find($arQuery);
                if (empty($buildings))
                    continue;

                $zip = $buildings[0]->readAttribute(KladrFields::ZipCode);
                $zip = (int) $zip;
                if ($zip == 0)
                    continue;

                return $zip;
            }
        }

        /**
         * Поиск объекта по названию
         * 
         * @param string $name Название объекта
         * @param array $codes Коды родительского объекта
         * @param int $limit Максимальное количество возвращаемых объектов
         * @param int $zip Почтовый индекс
         * @return array
         */
        public static function findByQuery($name = null, $codes = array(), $limit = 5000, $zip = null)
        {

            $arQuery = array();
            $arQuery['conditions'] = array();

            if ($codes)
            {
                // если не передается улица, значит ищем дома без улиц
                // у таких домов родитель = null
                // для того, чтоб проверка на null попала в условия проверяем:
                if (!isset($codes[KladrFields::CodeStreet]))
                    $codes[KladrFields::CodeStreet] = null;

                $codes = array_splice($codes, 0, 5);
                foreach ($codes as $field => $code)
                {
                    if ($code)
                    {
                        $arQuery['conditions'][$field] = $code;
                    } else
                    {
                        $arQuery['conditions'][$field] = 0;
                    }
                }
            } else
            {
                if ($zip == null)
                    return array();
            }

            if ($name)
            {
                $name2 = preg_replace('/_/u', '', $name);
                $regexObj = new \MongoRegex('/^' . $name2 . '/');
                $arQuery['conditions'][KladrFields::NormalizedName] = $regexObj;
            }

            if ($zip)
            {
                $arQuery['conditions'][KladrFields::ZipCode] = $zip;
            }

            $arQuery['limit'] = $limit * 3; //почему *3?

            $buildings = self::find($arQuery);

            $arReturn = array();
            foreach ($buildings as $building)
            {
                $id = $building->readAttribute(KladrFields::Id);
                $zip2 = (int)$building->readAttribute(KladrFields::ZipCode);
                if($zip2 == 0)
                {
                    $zip2 = self::getZipById($id);
                }
                
                $arReturn[] = array(
                    'id' => $building->readAttribute(KladrFields::Id),
                    'name' => $building->readAttribute(KladrFields::Name),
                    'zip' => $zip2,
                    'type' => $building->readAttribute(KladrFields::Type),
                    'typeShort' => $building->readAttribute(KladrFields::TypeShort),
                    'okato' => $building->readAttribute(KladrFields::Okato),
                    'contentType' => Buildings::ContentType,
                );
            }

            $arReturnBuilding = array();
            if ($name)
            {
                foreach ($arReturn as $item)
                {
                    $arNames = explode(',', $item['name']);
                    foreach ($arNames as $buildingName)
                    {
                        if (preg_match('/^' . $name . '/iu', $buildingName))
                        {
                            $item['name'] = $buildingName;
                            $arReturnBuilding[' ' . $buildingName] = $item;
                        }
                    }
                }
            } else
            {
                foreach ($arReturn as $item)
                {
                    $arNames = explode(',', $item['name']);
                    foreach ($arNames as $buildingName)
                    {
                        $item['name'] = $buildingName;
                        $arReturnBuilding[' ' . $buildingName] = $item;
                    }
                }
            }
            
            ksort($arReturnBuilding);

           $arReturnBuilding = array_values($arReturnBuilding);
            
            /*
            $arResult = array();
            for ($i = 1; $i < 10; $i++)
            {
                foreach ($arReturnBuilding as $item)
                {
                    if (mb_strlen($item['name'], mb_detect_encoding($item['name'])) == $i)
                    {
                        $arResult[] = $item;
                    }
                }
            }
             */

            $arResult = array_slice($arReturnBuilding, 0, $limit);
            
            if ($zip != null && empty($arResult))
            {
                return Streets::findByQuery(null, false, 10, 0, $zip);
            }

            return $arResult;
        }

    }

}