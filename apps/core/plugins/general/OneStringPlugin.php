<?php

namespace Kladr\Core\Plugins\General {

    use \Kladr\Core\Plugins\Base\IPlugin,
        \Phalcon\Mvc\User\Plugin,
        \Phalcon\Http\Request,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Plugins\Tools\Tools,
        \Kladr\Core\Models\Streets,
        \Kladr\Core\Models\Complex,
        \Kladr\Core\Models\KladrFields;

    /*
     * Kladr\Core\Plugins\General\OneStringPlugin
     * 
     * Плагин для поиска объектов одной строкой
     * 
     * @author Y. Lichutin
     */

    class OneStringPlugin extends Plugin implements IPlugin {

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
                
                //удаляем пустоту
                $arWords = array_diff($arWords, array(''));
                
                //заполняем массив ключевыми словами
                $arRegEx = array();
                foreach ($arWords as $word)
                {
                    $arRegEx[] = $word;                   
                }
                $arRegEx[count($arRegEx)-1] = new \MongoRegex('/^' . end($arWords). '/');
                
                //формируем поисковый запрос
                $arQuery = array(
                    array(
                        'Address' => array('$all' => $arRegEx), 
                        //KladrFields::Bad => false
                        ),
                    'limit' => $request->getQuery('limit') ? (int) $request->getQuery('limit') : 5,
                    'sort' => array(KladrFields::Sort => 1)
                );
                //$objects = Streets::find($arQuery);
                $objects = Complex::find($arQuery);
                $arReturn[] = $arQuery; //только для контроля

                foreach ($objects as $object) {
                    $arReturn[] = array(
                        'id' => $object->readAttribute(KladrFields::Id),
                        'name' => $object->readAttribute(KladrFields::Name),
                        'address' => $object->readAttribute('Address'),
                        'zip' => $object->readAttribute(KladrFields::ZipCode),
                        'type' => $object->readAttribute(KladrFields::Type),
                        'typeShort' => $object->readAttribute(KladrFields::TypeShort),
                        'okato' => $object->readAttribute(KladrFields::Okato),
                    );
                }
                
                $this->cache->set('OneStringPlugin', $request, $arReturn);
            } 

            $result = $prevResult;
            $result->result = $arReturn;
            $result->terminate = true;

            return $result;
        }

    }

}

