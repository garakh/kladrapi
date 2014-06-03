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
                
                $searchString = implode(" ", $arWords);
                
                $sphinxClient = $this->sphinxClient;               
                
                $limit = $searchArray['limit'] = $request->getQuery('limit') ? ((int) $request->getQuery('limit') >= 10 ? 10 : (int) $request->getQuery('limit')) : 10;               
                $sphinxClient->SetLimits(0, $limit);
                
                $sphinxClient->SetMatchMode(SPH_MATCH_EXTENDED2);
                
                $sphinxRes = $sphinxClient->Query($searchString);
                //$result = $res;
                
                if ($sphinxRes === false)
                {
                    $result = $prevResult;
                    $result->terminate = true;
                    $result->error = true;
                    $result->errorMessage = $sphinxClient->GetLastError();
                    return $result;
                }
                else
                {
                    if (!empty($sphinxRes['matches']))
                    {
                        foreach ( $sphinxRes['matches'] as $id => $arr)
                        {
                            if(preg_match("/^\d+$/", $id))
                            {
                                $objects[] = Complex::findFirst(array(
                                    array('Id' => $id)
                                ));
                            }
                        }
                    }   
                    else
                    {
                        if ( $sphinxClient->GetLastWarning() ) 
                        {
                            $result = $prevResult;
                            $result->terminate = true;
                            $result->error = true;
                            $result->errorMessage = $sphinxClient->GetLastWarning();
                            return $result;
                        }
                    }
                }
                
                //удаляем пустоту
//                $arWords = array_diff($arWords, array(''));               
                               
                //формируем поисковый запрос
//                $searchArray = array();
                
//                $this->analysis($arWords, $searchArray);              
                
                //если лимит больше 400 или не проставлен - ставим 400. 
//                $searchArray['limit'] = $request->getQuery('limit') ? ((int) $request->getQuery('limit') >= 400 ? 400 : (int) $request->getQuery('limit')) : 400;                    
//                $searchArray['sort'] = array(KladrFields::Sort => 1);
                
//                $objects = $this->search($searchArray);
                
                //$arReturn[] = $searchArray; //только для контроля               

                foreach ($objects as $object) {                    
                    $retObj = array(
                        'id' => $object->readAttribute(KladrFields::Id),
                        'name' => $object->readAttribute(KladrFields::Name),
                        'zip' => $object->readAttribute(KladrFields::ZipCode),
                        'type' => $object->readAttribute(KladrFields::Type),
                        'typeShort' => $object->readAttribute(KladrFields::TypeShort),
                        'okato' => $object->readAttribute(KladrFields::Okato),                       
                        'contentType' => $object->readAttribute(KladrFields::ContentType),
                        'fullName' => $object->readAttribute(KladrFields::FullName),  

                        'regionId' => $object->readAttribute(KladrFields::RegionId)                                                
                    );  
                                      
                    //$multBuilds = array(); //массив для разрешения множественных совпадений зданий в одной записи

                    switch ($retObj['contentType'])
                    {
                        case 'district':
                            $retObj['districtId'] = $object->readAttribute(KladrFields::DistrictId);
                            break;
                                
                        case 'city':
                            $retObj['districtId'] = $object->readAttribute(KladrFields::DistrictId);
                            $retObj['cityId'] = $object->readAttribute(KladrFields::CityId);
                            break;
                            
                        case 'street':
                            $retObj['districtId'] = $object->readAttribute(KladrFields::DistrictId);
                            $retObj['cityId'] = $object->readAttribute(KladrFields::CityId);
                            $retObj['streetId'] = $object->readAttribute(KladrFields::StreetId);
                            break;
                        
                        case 'building':
                            $retObj['districtId'] = $object->readAttribute(KladrFields::DistrictId);
                            $retObj['cityId'] = $object->readAttribute(KladrFields::CityId);
                            $retObj['streetId'] = $object->readAttribute(KladrFields::StreetId);
                            $retObj['buildingId'] = $object->readAttribute(KladrFields::BuildingId);
                            
                            //поиск совпадений с номерами домов//                            
//                            foreach ($object->readAttribute(KladrFields::NormalizedBuildingName) as $name)
//                            {
//                                //находим все совпадения с номерами домов в массиве поиска по регулярке
//                                $reg = '';
//                                if ($searchArray['conditions'][KladrFields::NormalizedBuildingName])
//                                {
//                                    $reg = (string)$searchArray['conditions'][KladrFields::NormalizedBuildingName];
//                                }
//                                else
//                                {    
//                                    $reg = (string)end($searchArray['conditions'][KladrFields::Address]['$all']);
//                                }
//                                
//                                $match = preg_match($reg, $name) ? $name : null;
//                                
//                                //убираем длинные строки из домов
//                                $match = preg_match('/\,/', $match) ? null : $match;
//           
//                                if ($match) 
//                                {
//                                    $multBuilds[] = $match;
//                                }
//                                }                                                      
                            break;
                            
                        default :
                            break;
                    }                 
                    
//                    if ($retObj['contentType'] == 'building')
//                    {
//                        foreach ($multBuilds as $buildName)
//                        {
//                            $building = $retObj;
//                            $name = $object->readAttribute(KladrFields::TypeShort) . '. ' . $buildName;
//                            $building['fullName'] .= ' ' . $name;
//                            $building['name'] = $name;
//                            $arReturn[] = $building;                                   
//                        }
//                    }
//                    else
//                    {
                        $arReturn[] = $retObj;  
//                    }
                }
                
//                if (count($arReturn) > $searchArray['limit'])//правим лимит домов
//                {
//                    $arReturn = array_slice($arReturn, 0, $searchArray['limit']);
//                }
                
//                $this->cache->set('OneStringPlugin', $request, $arReturn);
            } 

            $result = $prevResult;
            $result->result = $arReturn;
            $result->terminate = true;

            return $result;
        }
        
        /*
         * Производит анализ массива поисковых слов, заполняет массив для поиска в БД
         */
        public function analysis(array $words, array &$searchArray)
        {
            //массивы для сравнения с различными типами объектов. в будущем просмотреть все возможные типы через цикл из БД
            $regionPrefixArr = array('республика', 'респ', 'р');
            $cityPrefixArr = array('г', 'город', 'территория', 'тер', 'улус', 'у', 'волость', 'дп', 'кп', 'пгт', 'по', 'рп', 'са', 'стер', 'со', 'смо', 'спос', 'сс', 'сельсовет', 'аал', 'аул', 'высел', 'городок', 'д', 'деревня', 'оп', 'будка', 'казарм', 'казарма', 'платф', 'ст', 'пост', 'заимка', 'микрорайон', 'мкр', 'нп', 'остров', 'пр', 'пст', 'п', 'посёлок', 'поселок', 'починок', 'по', 'промзона', 'рп', 'рзд', 'с', 'село', 'сл', 'слобода', 'ст-ца', 'х', 'высел', 'выселок', 'кв-л', 'квартал', 'местечко', 'м', 'пр', 'полуст', 'полустанок');
            $streetPrefixArr = array('улица', 'ул', 'проспект', 'пр', 'просп', 'аллея', 'бр', 'бульвар', 'въезд', 'дорога', 'дор', 'рзд', 'разъезд', 'заезд', 'км', 'километр', 'наб', 'набережная', 'городок','парк', 'переезд', 'д', 'деревня', 'переулок', 'пер', 'площадка','оп', 'будка', 'казарм', 'казарма', 'платф', 'ст', 'пл-ка', 'проезд', 'просек', 'пост', 'проселок', 'проулок', 'сад', 'сквер', 'стр', 'мкр', 'микрорайон', 'строение', 'тракт', 'туп', 'тупик', 'п', 'уч-к', 'ш', 'пр', 'м', 'местечко', 'кв-л', 'квартал', 'рзд', 'жт', 'высел', 'выселок', 'х', 'сл', 'слобода', 'с', 'село');
            $buildPrefixArr = array('д', 'дом');
            $districtSuffixArr = array('район', 'р', 'рн');
            $regionSuffixArr = array('область', 'обл', 'об', 'край', 'кр' , 'ао');//поля "автономный округ" и "автономная область" вычеркнуты
            
            //для избегания конфликтов имён и т.п.
//            $regionWasFound = false;
//            $districtWasFound = false;
//            $cityWasFound = false;
//            $streetWasFound = false;
//            $buildWasFound = false;
            
            $prevWord = '';
            
            $continue = false;
                      
            foreach ($words as &$word)
            {    
                if ($continue) 
                {
                    $continue = false;
                    continue;
                }

                if (!$searchArray[KladrFields::NormalizedRegionName])
                {
                    if (in_array($word, $regionPrefixArr))
                    { 
                        $this->regionPrefixFound(current($words), $searchArray); 
                        $regionWasFound = true;
                        $continue = true;
                        continue;
                    }
                    elseif (in_array($word, $regionSuffixArr))
                    {
                        $this->regionSuffixFound($prevWord, $searchArray);
                        $regionWasFound = true;
                        continue;
                    }
                }
                
                if (!$searchArray[KladrFields::NormalizedDistrictName])
                {
                    if (in_array($word, $districtSuffixArr))
                    {
                        $this->districtSuffixFound($prevWord, $searchArray);
                        $districtWasFound = true;
                        continue;                      
                    }
                }
                
                if (!$searchArray[KladrFields::NormalizedCityName])
                {
                    if (in_array($word, $cityPrefixArr))
                    {
                        $this->cityPrefixFound(current($words), $searchArray);
                        $continue = true;
                        $cityWasFound = true;
                        continue;
                    }
                }
                
                if (!$searchArray[KladrFields::NormalizedStreetName])
                {
                    if (in_array($word, $streetPrefixArr))
                    {
                        $this->streetPrefixFound(current($words), $searchArray);
                        $continue = true;
                        $streetWasFound = true;
                        continue;
                    }
                }
                
                if (!$searchArray[KladrFields::NormalizedBuildingName])
                {
                    if (in_array($word, $buildPrefixArr))
                    {
                        $this->buildPrefixFound(current($words), $searchArray);
                        $continue = true;
                        $buildWasFound = true;
                        continue;
                    }
                }
                               
                $this->anotherWordFound($word, $searchArray);
                $prevWord = $word;
                
            }
        }

        /*
         * Обработчик республики в массиве для поиска
         */
        public function regionPrefixFound($word, array &$searchArray)
        {
            $searchArray['conditions'][KladrFields::NormalizedRegionName] = $word;
            $searchArray['conditions'][KladrFields::Address]['$all'][] = $word;   
        }
        
        /*
         * Обработчик города в массиве для поиска
         */
        public function cityPrefixFound($word, array &$searchArray)
        {
             $searchArray['conditions'][KladrFields::NormalizedCityName] = $word;
             $searchArray['conditions'][KladrFields::Address]['$all'][] = $word;   
        }
        
        /*
         * Обработчик улицы в массиве для поиска
         */
        public function streetPrefixFound($word, array &$searchArray)
        {
              $searchArray['conditions'][KladrFields::NormalizedStreetName] = $word;
              $searchArray['conditions'][KladrFields::Address]['$all'][] = $word;   
        }
        
        /*
         * Обработчик дома в массиве для поиска
         */
        public function buildPrefixFound($word, array &$searchArray)
        {
            $searchArray['conditions'][KladrFields::NormalizedBuildingName] = $word;    
            $searchArray['conditions'][KladrFields::Address]['$all'][] = $word;
        }
        
        /*
         * Обработчик района в массиве для поиска
         */
        public function districtSuffixFound($word, array &$searchArray)
        {
            $searchArray['conditions'][KladrFields::NormalizedDistrictName] = $word;      
        }
        
        /*
         * Обработчик района в массиве для поиска
         */
        public function regionSuffixFound($word, array &$searchArray)
        {         
            //область и край
            $searchArray['conditions'][KladrFields::NormalizedRegionName] = $word;            
        }       

        /*
         * Обработчик слова, не попавшего под условия в массиве для поиска
         */
        public function anotherWordFound($word, array &$searchArray)
        {
            $searchArray['conditions'][KladrFields::Address]['$all'][] = $word;           
        }
               
        /*
         * Выполняет поиск по базе данных. Возвращает найденные значения
         */
        public function search(array &$searchArray)
        {
            if ($searchArray['conditions'] != null)
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
                   
                    case $searchArray['conditions'][KladrFields::NormalizedBuildingName]:
                        $searchArray['conditions'][KladrFields::NormalizedBuildingName] = new \MongoRegex('/^' . $searchArray['conditions'][KladrFields::NormalizedBuildingName] . '/');
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



