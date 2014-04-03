<?php

namespace Kladr\Core\Models {

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
        public static function getCodes($id) {

            if(isset(self::$Cache[$id]))
                return self::$Cache[$id];

            $object = self::findFirst(array(
                array(KladrFields::Id => $id)
            ));

            if(!$object) return array();

            self::$Cache[$id] = array(
                KladrFields::CodeRegion => $object->readAttribute(KladrFields::CodeRegion),
                KladrFields::CodeDistrict => $object->readAttribute(KladrFields::CodeDistrict),
                KladrFields::CodeLocality => $object->readAttribute(KladrFields::CodeLocality),
                KladrFields::CodeStreet => $object->readAttribute(KladrFields::CodeStreet),
                KladrFields::CodeBuilding => $object->readAttribute(KladrFields::CodeBuilding),
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
        public static function findByQuery($name = null, $codes = array(), $limit = 5000)
        {
            //насчет оффсета: как вариант делать выборку по всем домам вплоть до лимита,а после отбрасывать первую часть в нижнем array splice
            $arQuery = array();       

            if ($codes){
                $codes = array_splice($codes, 0, 5);
                foreach($codes as $field => $code){
                    if($code){
                        $arQuery['conditions'][$field] = $code;
                    } else {
                        $arQuery['conditions'][$field] = null;
                    }
                }
            } else {
                return array();
            }

            if($name){
                $regexObj = new \MongoRegex('/^'.$name.'/');
                $arQuery['conditions'][KladrFields::NormalizedName] = $regexObj;
            }

            $arQuery['limit'] = $limit * 3; //почему *3?

            $regions = self::find($arQuery);

            $arReturn = array();
            foreach($regions as $region){
                $arReturn[] = array(
                    'id'          => $region->readAttribute(KladrFields::Id),
                    'name'        => $region->readAttribute(KladrFields::Name),
                    'zip'         => $region->readAttribute(KladrFields::ZipCode),
                    'type'        => $region->readAttribute(KladrFields::Type),
                    'typeShort'   => $region->readAttribute(KladrFields::TypeShort),
                    'okato'       => $region->readAttribute(KladrFields::Okato),
                    'contentType' => Buildings::ContentType,
                );
            }
            
            $arReturnBuilding = array();
            if($name){
                foreach($arReturn as $item){
                    $arNames = explode(',', $item['name']);
                    foreach($arNames as $buildingName){
                        if(preg_match('/^'.$name.'/iu', $buildingName)){
                            $item['name'] = $buildingName;
                            $arReturnBuilding[$buildingName] = $item;
                        }
                    }
                }
            } else {
                foreach($arReturn as $item){
                    $arNames = explode(',', $item['name']);
                    foreach($arNames as $buildingName){
                        $item['name'] = $buildingName;
                        $arReturnBuilding[$buildingName] = $item;
                    }
                }
            }
            
            ksort($arReturnBuilding);
            
            $arResult = array();
            for($i=1; $i<10; $i++){
                foreach($arReturnBuilding as $item){
                    if(mb_strlen($item['name'],mb_detect_encoding($item['name'])) == $i){
                        $arResult[] = $item;
                    }
                }
            }
            
            $arResult = array_slice($arResult, 0, $limit);            
            return $arResult;
        }

    }

}