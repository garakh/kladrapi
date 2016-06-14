<?php

namespace Kladr\Core\Models
{

    use \Phalcon\Mvc\Collection;

    /**
     * Kladr\Core\Models\Cities
     *
     * Коллекция населённых пунктов
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
     * @property int $Sort Сортировка
     *
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class Cities extends Collection
    {

        /**
         * @var string Тип объекта
         */
        const ContentType = "city";

        /**
         * Кеш, чтоб снизить запросы к базе
         * @var array
         */
        private static $Cache = array();

        public function getSource()
        {
            return "cities";
        }

        /**
         * Возвращает массив кодов текущего объекта
         *
         * @param string $id ID
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
                KladrFields::CodeLocality => $object->readAttribute(KladrFields::CodeLocality),
            );

            return self::$Cache[$id];
        }

        /**
         * Получает ID потенциального города родителя
         * 16 031 001 xxx xx → 16 031 001 000 00
         * @param string $id
         * @return string | null Вернет null, если строка неверная
         */
        public static function getCityOwnerId($id)
        {
            /*
              01 234 567 89A BC
              16 031 001 001 00
             */

            if (strlen($id) < 13)
                return null;

            $id2 = $id;

            $id2[8] = '0';
            $id2[9] = '0';
            $id2[10] = '0';
            $id2[11] = '0';
            $id2[12] = '0';

            if ($id == $id2)
                return null;

            $id2 = substr($id2, 0, 13);

            return $id2;
        }

        /**
         * Поиск объекта по названию
         *
         * @param string $name Название объекта
         * @param array $codes Коды родительского объекта
         * @param int $limit Максимальное количество возвращаемых объектов
         * @param int $offset Сдвиг
         * @param array $typeCodes Массив TypeCode для фильтрации
         * @param bool $strict Точный режим поиска
         * @return array
         */
        public static function findByQuery($name = null, $codes = array(), $limit = 5000, $offset = 0, $typeCodes = null, $strict = false)
        {
            $arQuery = array();
            $isEmptyQuery = true;

            if ($name)
            {
                $isEmptyQuery = false;
                $regexObj = new \MongoRegex('/^' . $name . '/');
                $arQuery['conditions'][KladrFields::NormalizedName] = $regexObj;
            }

            $searchById = $codes && !is_array($codes);

            if (is_array($codes))
            {
                $isEmptyQuery = false;
                $codes = array_splice($codes, 0, 3);
                $arRegionCodeSpecialCases = array(
                    50 => 77, // Московская область => Москва
                    47 => 78  // Ленинградская область => Санкт-Петербург
                );

                foreach ($codes as $field => $code)
                {
                    if (!$code)
                    {
                        $arQuery['conditions'][$field] = array('$in' => array(
                                null,
                                0
                        ));
                        continue;
                    }

                    if ($field == KladrFields::CodeRegion && isset($arRegionCodeSpecialCases[$code]))
                    {
                        $arQuery['conditions'][$field] = array('$in' => array(
                                $code,
                                $arRegionCodeSpecialCases[$code]
                        ));
                    } else
                    {
                        $arQuery['conditions'][$field] = $code;
                    }
                }
            } elseif ($searchById)
            {
                $isEmptyQuery = false;
                $arQuery['conditions'][KladrFields::Id] = $codes;
            }

            //строги режим: если не указан район, то ищем города у которых нет района
            //в противном случае: поле район просто игнорируется
            if ($strict && !isset($codes[KladrFields::CodeDistrict]))
            {
                $arQuery['conditions'][KladrFields::CodeDistrict] = 0;
            }

            if ($isEmptyQuery)
            {
                return array();
            }

            if (!$searchById)
            {
                $arQuery['conditions'][KladrFields::Bad] = false;
            }

            if ($typeCodes != null)
            {
                $arQuery['conditions'][KladrFields::TypeCode] = array('$in' => $typeCodes);
            }

            $arQuery['sort'] = array(KladrFields::Sort => 1);

            $arQuery['skip'] = $offset;
            $arQuery['limit'] = $limit;
//            $arQuery['limit'] = 4;


            $cities = self::find($arQuery);

            $arReturn = array();
            foreach ($cities as $city)
            {
                $id = $city->readAttribute(KladrFields::Id);
                $zip = $city->readAttribute(KladrFields::ZipCode);
                $zip = (int) $zip;
                if ($zip == 0)
                {
                    $zip = Buildings::getZipById($id);

                }


                $arReturn[] = array(
                    'id' => $city->readAttribute(KladrFields::Id),
                    'name' => $city->readAttribute(KladrFields::Name),
                    'zip' => $zip,
                    'type' => $city->readAttribute(KladrFields::Type),
                    'typeShort' => $city->readAttribute(KladrFields::TypeShort),
                    'okato' => $city->readAttribute(KladrFields::Okato),
                    'contentType' => Cities::ContentType,
                );
            }

            return $arReturn;
        }

    }

}