<?php

namespace Kladr\Core\Plugins\General {

    use \Kladr\Core\Plugins\Base\IPlugin,
        \Phalcon\Mvc\User\Plugin,
        \Phalcon\Http\Request,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Plugins\Tools\Tools,
        \Kladr\Core\Models\Complex,
        \Kladr\Core\Models\KladrFields;

    /*
     * Kladr\Core\Plugins\General\OneStringPlugin
     * 
     * Плагин для поиска объектов одной строкой
     * 
     * @author Y. Lichutin
     */

    class OneStringPlugin extends Plugin implements IPlugin 
    {

        /**
         * Кэш
         * 
         * @var Kladr\Core\Plugins\Tools\Cache 
         */
        public $cache;

        /**
         * Массив, по которому будет производиться поиск
         * 
         * @var array 
         */
        public $searchArray = array();
        
        /**
         * Выполняет обработку запроса
         * 
         * @param \Phalcon\Http\Request $request
         * @param \Kladr\Core\Plugins\Base\PluginResult $prevResult
         * @return \Kladr\Core\Plugins\Base\PluginResult
         */
        public function process(Request $request, PluginResult $prevResult) {

            if ($prevResult->error) {
                return $prevResult;
            }

            if (!$request->getQuery('oneString')) {
                return $prevResult;
            }    
            
            $arReturn = $this->cache->get('OneStringPlugin', $request);

            if ($arReturn === null) {
                $objects = array();
                $query = $request->getQuery('query');
                
                //разбиваем строку запроса на слова
                $arWords = preg_split('/(\ |\.|\;|\,)+/', $query);
                
                //нормализуем
                foreach ($arWords as $key => $word)
                {
                    $arWords[$key] = Tools::Normalize($word);
                }
                
                //удаляем пустоту
                $arWords = array_diff($arWords, array(''));
                
                $this->Analysis($arWords);
                
                //формируем поисковый запрос
                $this->searchArray['limit'] = $request->getQuery('limit') ? (int) $request->getQuery('limit') : 5;
                     
                $this->searchArray['sort'] = array(KladrFields::Sort => 1);
                
                $objects = $this->Search($this->searchArray);
                $arReturn[] = $this->searchArray; //только для контроля               

                foreach ($objects as $object) {
                    $arReturn[] = array(
                        'id' => $object->readAttribute(KladrFields::Id),
                        'name' => $object->readAttribute(KladrFields::Name),
                        'address' => $object->readAttribute('Address'),
                        'zip' => $object->readAttribute(KladrFields::ZipCode),
                        'type' => $object->readAttribute(KladrFields::Type),
                        'typeShort' => $object->readAttribute(KladrFields::TypeShort),
                        'okato' => $object->readAttribute(KladrFields::Okato),
                        
                        'contentType' => $object->readAttribute(KladrFields::ContentType),
                        'buildingId' => $object->readAttribute(KladrFields::BuildingId),
                        'cityId' => $object->readAttribute(KladrFields::CityId),
                        'streetId' => $object->readAttribute(KladrFields::StreetId),
                        'districtId' => $object->readAttribute(KladrFields::DistrictId),
                        'regionId' => $object->readAttribute(KladrFields::RegionId),
                        
                        'fullName' => $object->readAttribute(KladrFields::FullName)                       
                    );
                }
                
                $this->cache->set('OneStringPlugin', $request, $arReturn);
            } 

            $result = $prevResult;
            $result->result = $arReturn;
            $result->terminate = true;

            return $result;
        }
        
        /*
         * Производит анализ массива поисковых слов, заполняет массив для поиска в БД
         */
        public function Analysis(array $words)
        {
            //массивы для сравнения с различными типами объектов. в будущем просмотреть все возможные типы через цикл из БД
            $regionPrefixArr = array('республика', 'респ', 'р');
            $cityPrefixArr = array('г', 'город', 'гор');
            $streetPrefixArr = array('улица', 'ул', 'проспект', 'пр', 'просп');
            $buildPrefixArr = array('д', 'дом');
            $districtSuffixArr = array('район', 'р', 'р-н');
            $regionSuffixArr = array('область', 'обл', 'об', 'край', 'кр');
            
            //для избегания конфликтов имён и т.п.
            $regionWasFound = false;
            $districtWasFound = false;
            $cityWasFound = false;
            $streetWasFound = false;
            $buildWasFound = false;
            
            $prevWord = '';
                      
            foreach ($words as &$word)
            {             
                if (!$regionWasFound)
                {
                    if (in_array($word, $regionPrefixArr))
                    { 
                        $this->RegionPrefixFound(current($words)); 
                        $regionWasFound = true;
                        continue;
                    }
                    elseif (in_array($word, $regionSuffixArr))
                    {
                        $this->RegionSuffixFound($prevWord);
                        $regionWasFound = true;
                        continue;
                    }
                }
                
                if (!$districtWasFound)
                {
                    if (in_array($word, $districtSuffixArr))
                    {
                        $this->DistrictSuffixFound($prevWord);
                        $districtWasFound = true;
                        continue;                      
                    }
                }
                
                if (!$cityWasFound)
                {
                    if (in_array($word, $cityPrefixArr))
                    {
                        $this->CityPrefixFound(current($words));
                        $cityWasFound = true;
                        continue;
                    }
                }
                
                if (!$streetWasFound)
                {
                    if (in_array($word, $streetPrefixArr))
                    {
                        $this->StreetPrefixFound(current($words));
                        $streetWasFound = true;
                        continue;
                    }
                }
                
                if (!$buildWasFound)
                {
                    if (in_array($word, $buildPrefixArr))
                    {
                        $this->BuildPrefixFound(current($words));
                        $buildWasFound = true;
                        continue;
                    }
                }
                               
                $this->AnotherWordFound($word);
                $prevWord = $word;
                
            }
        }

        /*
         * Обработчик республики в массиве для поиска
         */
        public function RegionPrefixFound($word)
        {
            $this->searchArray['conditions'][KladrFields::NormalizedRegionName] = $word;   
        }
        
        /*
         * Обработчик города в массиве для поиска
         */
        public function CityPrefixFound($word)
        {
             $this->searchArray['conditions'][KladrFields::NormalizedCityName] = $word;
        }
        
        /*
         * Обработчик улицы в массиве для поиска
         */
        public function StreetPrefixFound($word)
        {
              $this->searchArray['conditions'][KladrFields::NormalizedStreetName] = $word;
        }
        
        /*
         * Обработчик дома в массиве для поиска
         */
        public function BuildPrefixFound($word)
        {
            $this->searchArray['conditions'][KladrFields::NormalizedBuldingName] = $word;
        }
        
        /*
         * Обработчик района в массиве для поиска
         */
        public function DistrictSuffixFound($word)
        {
            $this->searchArray['conditions'][KladrFields::NormalizedDistrictName] = $word;      
        }
        
        /*
         * Обработчик района в массиве для поиска
         */
        public function RegionSuffixFound($word)
        {         
            //область и край
            $this->searchArray['conditions'][KladrFields::NormalizedRegionName] = $word;            
        }

        /*
         * Обработчик слова, не попавшего под условия в массиве для поиска
         */
        public function AnotherWordFound($word)
        {
            $this->searchArray['conditions'][KladrFields::Address]['$all'][] = $word;           
        }
               
        /*
         * Выполняет поиск по базе данных. Возвращает найденные значения
         */
        public function Search(array &$searchArray)
        {
            if ($this->searchArray['conditions'] != null)
            {                              
                switch (end($searchArray['conditions'][KladrFields::Address]['$all']))
                {
                    case $searchArray['conditions'][KladrFields::NormalizedRegionName]:                       
                        $searchArray['conditions'][KladrFields::NormalizedRegionName] = new \MongoRegex('/^' . $searchArray['conditions'][KladrFields::NormalizedRegionName] . '/');
                        break;

                    case $searchArray['conditions'][KladrFields::NormalizedDistrictName]:
                        $searchArray['conditions'][KladrFields::NormalizedDistrictName] = new \MongoRegex('/^' . $searchArray['conditions'][KladrFields::NormalizedDistrictName] . '/');
                        break;

                    case $searchArray['conditions'][KladrFields::NormalizedCityName]:
                        $searchArray['conditions'][KladrFields::NormalizedCityName] = new \MongoRegex('/^' . $searchArray['conditions'][KladrFields::NormalizedCityName] . '/');
                        break;

                    case $searchArray['conditions'][KladrFields::NormalizedStreetName]:
                        $searchArray['conditions'][KladrFields::NormalizedStreetName] = new \MongoRegex('/^' . $searchArray['conditions'][KladrFields::NormalizedStreetName] . '/');
                        break;
                   
                    case $searchArray['conditions'][KladrFields::NormalizedBuldingName]:
                        $searchArray['conditions'][KladrFields::NormalizedBuldingName] = new \MongoRegex('/^' . $searchArray['conditions'][KladrFields::NormalizedBuldingName] . '/');
                        break;
               }
               reset($searchArray['conditions'][KladrFields::Address]['$all']);
               $willReg = array_pop($searchArray['conditions'][KladrFields::Address]['$all']);
               $searchArray['conditions'][KladrFields::Address]['$all'][] = new \MongoRegex('/^' . $willReg . '/');
               
               
               
               return Complex::find($searchArray);
           }
           else return null;
        }
        
    }
        
}



