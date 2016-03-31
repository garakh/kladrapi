<?php

namespace Kladr\Core\Plugins\General {

use \Phalcon\Http\Request,
    \Phalcon\Mvc\User\Plugin,
    \Kladr\Core\Plugins\Base\IPlugin,
    \Kladr\Core\Plugins\Base\PluginResult;
use Kladr\Core\Models\Regions;
use Kladr\Core\Models\Districts;
use Kladr\Core\Models\Cities;
use Kladr\Core\Models\Streets;
use Kladr\Core\Models\Buildings;
//use \Kladr\Core\Plugins\Tools;
																				
    /**
     * Kladr\Core\Plugins\General\ValidatePlugin
     * 
     * Плагин выполняющий валидацию запроса
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class ValidatePlugin extends Plugin implements IPlugin
    {

        /**
         * Выполняет обработку запроса
         * 
         * @param \Phalcon\Http\Request $request
         * @param \Kladr\Core\Plugins\Base\PluginResult $prevResult
         * @return \Kladr\Core\Plugins\Base\PluginResult
         */
        public function process(Request $request, PluginResult $prevResult)
        {
            if ($prevResult->error)
            {
                return $prevResult;
            }

            $arSearchContext = array();
            $errorMessage = '';
          
            //поиск одной строкой
            if($request->getQuery('oneString')) {
                $arSearchContext['oneString'] = $request->getQuery('oneString');                
            }
            
            // contentType
            if ($request->getQuery('contentType'))
            {
                $arSearchContext['contentType'] = $request->getQuery('contentType');

                if (!in_array($arSearchContext['contentType'], array(
                            Regions::ContentType,
                            Districts::ContentType,
                            Cities::ContentType,
                            Streets::ContentType,
                            Buildings::ContentType
                        )))
                {
                    $errorMessage = 'contentType incorrect';
                }
            } elseif (!$request->getQuery('oneString')) {
                	$errorMessage = 'contentType required parameter';
            }

            // regionId
            if ($request->getQuery('regionId'))
            {
                $arSearchContext['regionId'] = $request->getQuery('regionId');
                if (preg_match('/[^0-9]+/u', $arSearchContext['regionId']))
                {
                    $errorMessage = 'regionId incorrect';
                }
            }

            // districtId
            if ($request->getQuery('districtId'))
            {
                $arSearchContext['districtId'] = $request->getQuery('districtId');
                if (preg_match('/[^0-9]+/u', $arSearchContext['districtId']))
                {
                    $errorMessage = 'districtId incorrect';
                }
            }

            // cityId
            if ($request->getQuery('cityId'))
            {
                $arSearchContext['cityId'] = $request->getQuery('cityId');
                if (preg_match('/[^0-9]+/u', $arSearchContext['cityId']))
                {
                    $errorMessage = 'cityId incorrect';
                }
            }

            // streetId
            if ($request->getQuery('streetId'))
            {
                $arSearchContext['streetId'] = $request->getQuery('streetId');
                if (preg_match('/[^0-9]+/u', $arSearchContext['streetId']))
                {
                    $errorMessage = 'streetId incorrect';
                }
            }

            // buildingId
            if ($request->getQuery('buildingId'))
            {
                $arSearchContext['buildingId'] = $request->getQuery('buildingId');
                if (preg_match('/[^0-9]+/u', $arSearchContext['buildingId']))
                {
                    $errorMessage = 'buildingId incorrect';
                }
            }

            // query
            $arSearchContext['query'] = $request->getQuery('query');
            
            //contentType
            if ($request->getQuery('query') && !$request->getQuery('oneString'))
            {                           
                if (array_key_exists('contentType', $arSearchContext))
                {
                    switch ($arSearchContext['contentType'])
                    {
                        case 'street':
                            if (empty($arSearchContext['cityId']))
                            {
                                $errorMessage = 'cityId parameter required';
                            }
                            break;
                        case 'building':
                            if (empty($arSearchContext['streetId']) && empty($arSearchContext['cityId']))
                            {
                                $errorMessage = 'streetId or cityId parameters required';
                            }
                            break;
                    }
                }
            }

            // withParent
            if ($request->getQuery('withParent'))
            {
                $arSearchContext['withParent'] = $request->getQuery('withParent');
            }

            // limit
            if ($request->getQuery('limit'))
            {
                $arSearchContext['limit'] = (int)$request->getQuery('limit');

                if ($arSearchContext['limit'] == 0)
                {
                    $errorMessage = 'limit is incorrect. Should be numerable, greater than 0';
                }

                if($arSearchContext['limit'] > 400){
                    $errorMessage = 'limit > 400. Should be less than 400';
                }
            }

            //offset
            if ($request->getQuery('offset'))
            {
                $arSearchContext['offset'] = (int)$request->getQuery('offset');
                if ($arSearchContext['offset'] == 0)
                {
                    $errorMessage = 'offset incorrect. Should be numerable, greater than 0';
                }
            }

            // callback
            if ($request->getQuery('callback'))
            {
                $arSearchContext['callback'] = $request->getQuery('callback');
            }

            $result = new PluginResult();

            if ($errorMessage)
            {
                $result->error = true;
                $result->errorCode = 400;
                $result->errorMessage = $errorMessage;
            }

            $result->searchContext = $arSearchContext;

            return $result;
        }

    }

}