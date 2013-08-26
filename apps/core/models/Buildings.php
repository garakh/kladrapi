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
     * @property int $CodeStreet Код строения
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class Buildings extends Collection
    {

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
            $object = self::findFirst(array(
                array(KladrFields::Id => $id)
            ));

            if(!$object) return array();

            return array(
                KladrFields::CodeRegion => $object->readAttribute(KladrFields::CodeRegion),
                KladrFields::CodeDistrict => $object->readAttribute(KladrFields::CodeDistrict),
                KladrFields::CodeLocality => $object->readAttribute(KladrFields::CodeLocality),
                KladrFields::CodeStreet => $object->readAttribute(KladrFields::CodeStreet),
                KladrFields::CodeBuilding => $object->readAttribute(KladrFields::CodeBuilding),
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

            $arQuery['sort'] = array(KladrFields::Sort => 1, KladrFields::Name => 1);
            $arQuery['limit'] = $limit;

            $regions = self::find($arQuery);

            $arReturn = array();
            foreach($regions as $region){
                $arReturn[] = array(
                    'id'        => $region->readAttribute(KladrFields::Id),
                    'name'      => $region->readAttribute(KladrFields::Name),
                    'zip'       => $region->readAttribute(KladrFields::ZipCode),
                    'type'      => $region->readAttribute(KladrFields::Type),
                    'typeShort' => $region->readAttribute(KladrFields::TypeShort),
                    'okato'     => $region->readAttribute(KladrFields::Okato),
                );
            }

            $arReturnBuilding = array();
            $count = 0;
            foreach($arReturn as $item){
                $arNames = explode(',', $item['name']);
                foreach($arNames as $buildingName){
                    if($name){
                        if(preg_match('/^'.$name.'/iu', $buildingName)){
                            $count++;
                            $item['name'] = $buildingName;
                            $arReturnBuilding[] = $item;
                        }
                    } else {
                        $count++;
                        $item['name'] = $buildingName;
                        $arReturnBuilding[] = $item;
                    }

                    if($count >= $limit){
                        return $arReturnBuilding;
                    }
                }
            }

            return $arReturnBuilding;
        }

    }

}