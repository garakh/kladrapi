<?php

namespace Kladr\Core\Models {

    use \Phalcon\Mvc\Collection;

    /**
     * Kladr\Core\Models\Districts
     * 
     * Коллекция районов
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
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class Districts extends Collection
    {
    	/**
    	 * @var string Тип объекта
    	 */
		const ContentType = "district";
    	
        /**
         * Кеш, чтоб снизить запросы к базе
         * @var array
         */
        private static $Cache = array();

        public function getSource()
        {
            return "district";
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
            $arQuery = array();       
            $isEmptyQuery = true;

            if($name){
                $isEmptyQuery = false;
                $regexObj = new \MongoRegex('/^'.$name.'/');
                $arQuery['conditions'][KladrFields::NormalizedName] = $regexObj;
            }

            $searchById = $codes && !is_array($codes);

            if (is_array($codes)){
                $isEmptyQuery = false;
                $codes = array_splice($codes, 0, 2);
                foreach($codes as $field => $code){
                    if($code){
                        $arQuery['conditions'][$field] = $code;
                    } else {
                        $arQuery['conditions'][$field] = null;
                    }
                }
            }elseif($searchById)
            {
                $isEmptyQuery = false;
                $arQuery['conditions'][KladrFields::Id] = $codes;
            }

            if($isEmptyQuery){
                return array();
            }

            if(!$searchById){
                $arQuery['conditions'][KladrFields::Bad] = false;
            }

            $arQuery['sort'] = array(KladrFields::Name => 1);
            $arQuery['limit'] = $limit;

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
                	'contentType' => Districts::ContentType,
                );
            }

            return $arReturn;
        }

    }
    
}