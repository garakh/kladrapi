<?php

namespace Kladr\Core\Plugins\General {
    
    require $_SERVER["DOCUMENT_ROOT"].'/sphinx/api/sphinxapi.php';

    use \Phalcon\Http\Request,
        \Phalcon\Mvc\User\Plugin,
        \Kladr\Core\Plugins\Base\IPlugin,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Plugins\Tools\Tools,
        \Kladr\Core\Models\Regions,
        \Kladr\Core\Models\Districts,
        \Kladr\Core\Models\Cities,
        \Kladr\Core\Models\Streets,
        \Kladr\Core\Models\Buildings,
        \SphinxClient;

    /**
     * Kladr\Core\Plugins\General\FindPlugin
     * 
     * Плагин для поиска объектов
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class SphinxFindPlugin extends Plugin implements IPlugin
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
        public function process(Request $request, PluginResult $prevResult) 
        {    
            if($prevResult->error){
                return $prevResult;
            }

            $objects = array();
            
            // sphinx client
            $cl = new SphinxClient();
            $cl->SetServer( "localhost", 9312 );
            
            // search settings
            $cl->SetMatchMode ( SPH_MATCH_EXTENDED2 );
            $cl->SetRankingMode ( SPH_SORT_RELEVANCE );
            
            // limit
            $limit = $request->getQuery('limit');
            $limit = intval($limit);
            $limit = $limit ? $limit : 5000;
            $cl->SetLimits(0, $limit);

            // query
            $query = $request->getQuery('query');
            $query = Tools::Key($query);  
            $result = $cl->Query('*'.$query.'*', 'regions');

            switch ($request->getQuery('contentType')) {
                case 'region':
                    $result = $cl->Query('*'.$query.'*', 'regions');
                    break;
                case 'district':
                    $result = $cl->Query('*'.$query.'*', 'districts');
                    break;
                case 'city':
                    $result = $cl->Query('*'.$query.'*', 'cities');
                    break;
                case 'street':
                    $result = $cl->Query('*'.$query.'*', 'streets');
                    break;
                case 'building':
                    $result = $cl->Query('*'.$query.'*', 'buildings');
                    break;
            }


            $result = $prevResult;
            $result->result = $objects;        
            return $result;
        }
        
    }

}