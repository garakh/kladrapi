<?php

namespace Kladr\Core\Models
{

    use \Phalcon\Mvc\Collection;

    /**
     * Kladr\Core\Models\Streets
     * 
     * Коллекция улиц
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
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class Streets extends Collection
    {

        /**
         * @var string Тип объекта
         */
        const ContentType = "street";

        /**
         * Кеш, чтоб снизить запросы к базе
         * @var array
         */
        private static $Cache = array();

        public function getSource()
        {
            return "streets";
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
            );

            return self::$Cache[$id];
        }

        /**
         * Поиск объекта по названию
         * 
         * @param string $name Название объекта
         * @param array $codes Коды родительского объекта
         * @param int $limit Максимальное количество возвращаемых объектов
         * @return array
         */
        public static function findByQuery($name = null, $codes = array(), $limit = 5000, $offset = 0, $zip = null)
        {
            $arQuery = array();

            $searchById = $codes && !is_array($codes);

            if (is_array($codes))
            {
                $codes = array_splice($codes, 0, 4);
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
            } elseif ($searchById)
            {
                $arQuery['conditions'][KladrFields::Id] = $codes;
            } else
            {
                if ($zip == null)
                    return array();
            }

            if (!$searchById)
            {
                $arQuery['conditions'][KladrFields::Bad] = false;
            }

            if ($name)
            {
                $regexObj = new \MongoRegex('/^' . $name . '/');
                $arQuery['conditions'][KladrFields::NormalizedName] = $regexObj;
            }

            if ($zip)
            {
                $arQuery['conditions'][KladrFields::ZipCode] = "$zip";
            }

//            $arQuery['sort'] = array(KladrFields::Id => 1);
            $arQuery['skip'] = $offset;
            $arQuery['limit'] = $limit;

            $streets = self::find($arQuery);

            $arReturn = array();
            foreach ($streets as $street)
            {
                $id = $street->readAttribute(KladrFields::Id);
                $zip = $street->readAttribute(KladrFields::ZipCode);
                $zip = (int)$zip;
                if($zip == 0)
                {
                    $zip = Buildings::getZipById($id);
                    if($zip == null)
                    {
                        $zip = Regions::getZipById($id);
                    }
                }
                
                $arReturn[] = array(
                    'id' => $street->readAttribute(KladrFields::Id),
                    'name' => $street->readAttribute(KladrFields::Name),
                    'zip' => $zip,
                    'type' => $street->readAttribute(KladrFields::Type),
                    'typeShort' => $street->readAttribute(KladrFields::TypeShort),
                    'okato' => $street->readAttribute(KladrFields::Okato),
                    'contentType' => Streets::ContentType,
                );
            }

            return $arReturn;
        }

    }

}