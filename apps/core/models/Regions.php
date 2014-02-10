<?php

namespace Kladr\Core\Models {

    use \Phalcon\Mvc\Collection;

    /**
     * Kladr\Core\Models\Regions
     * 
     * Коллекция регионов
     * 
     * @property string $Id Идентификатор
     * @property string $Name Название
     * @property string $NormalizedName Нормализованное название
     * @property string $ZipCode Почтовый индекс
     * @property string $Type Подпись
     * @property string $TypeShort Подпись коротко
     * @property string $Okato ОКАТО
     * @property int $CodeRegion Код региона
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class Regions extends Collection
    {
    	/**
    	 * @var string Тип объекта
    	 */
		const ContentType = "region";
    	
        public function getSource()
        {
            return "regions";
        }

        /**
         * Возвращает массив кодов текущего объекта
         * 
         * @param string $id
         * @return array
         */
        public static function getCodes($id) {
            $object = self::findFirst(array(
                array(KladrFields::Id => $id)
            ));

            if(!$object) return array();

            return array(
                KladrFields::CodeRegion => $object->readAttribute(KladrFields::CodeRegion)
            );
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

            if ($codes){
                $isEmptyQuery = false;
                $codes = array_splice($codes, 0, 1);
                foreach($codes as $field => $code){
                    if($code){
                        $arQuery['conditions'][$field] = $code;
                    } else {
                        $arQuery['conditions'][$field] = null;
                    }
                }
            }

            if($isEmptyQuery){
                return array();
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
                	'contentType' => Regions::ContentType,
                );
            }

            return $arReturn;
        }

    }
    
}