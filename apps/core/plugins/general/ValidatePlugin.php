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
            }
            else
            {
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
            if ($request->getQuery('query'))
            {
                $arSearchContext['query'] = $request->getQuery('query');

                switch ($arSearchContext['contentType'])
                {
                    case 'street':
                        if (empty($arSearchContext['cityId']))
                        {
                            $errorMessage = 'cityId required parameter';
                        }
                        break;
                    case 'building':
                        if (empty($arSearchContext['streetId']))
                        {
                            $errorMessage = 'streetId required parameter';
                        }
                        break;
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
                $arSearchContext['limit'] = $request->getQuery('limit');
                if (preg_match('/[^0-9]+/u', $arSearchContext['limit']))
                {
                    $errorMessage = 'limit incorrect';
                }
                else
                {
                    $arSearchContext['limit'] = intval($arSearchContext['limit']);
                }
            }

            //offset
            if ($request->getQuery('offset'))
            {
                $arSearchContext['offset'] = $request->getQuery('offset');
                if (preg_match('/[^0-9]+/u', $arSearchContext['limit']))
                {
                    $errorMessage = 'offset incorrect';
                }
                else
                {
                    $arSearchContext['offset'] = intval($arSearchContext['offset']);
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