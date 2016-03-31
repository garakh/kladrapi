<?php

namespace Kladr\Core\Plugins\Tools {

    use \Phalcon\Http\Request;

    /**
     * Kladr\Core\Plugins\Tools\Cache
     * 
     * Обёртка для работы с кэшем фалкона
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class Cache
    {

        /**
         * Кэш phalcon
         * 
         * @var \Phalcon\Cache\Backend 
         */
        public $cache;

        /**
         * Конфиги
         * @var \Phalcon\Config\Adapter\Ini
         */
        public $config;

        /**
         * Возвращает ключ кэша для плагина
         * 
         * @param string $plugin Имя плагина
         * @param \Phalcon\Http\Request $request
         * @return string
         */
        private function getCacheKey($plugin, Request $request)
        {
            $key = $plugin;

            $key .= 'contentType=' . $request->getQuery('contentType');
            $key .= 'oneString='.$request->getQuery('oneString');
            
            $key .= 'regionId=' . $request->getQuery('regionId');
            $key .= 'districtId=' . $request->getQuery('districtId');
            $key .= 'cityId=' . $request->getQuery('cityId');
            $key .= 'streetId=' . $request->getQuery('streetId');
            $key .= 'buildingId=' . $request->getQuery('buildingId');
            $key .= 'zip=' . $request->getQuery('zip');
            $key .= 'strict=' . $request->getQuery('strict');

            $query = $request->getQuery('query');
            $query = Tools::Key($query);
            $query = Tools::Normalize($query);
            $key .= 'query=' . $query;

            $key .= 'withParent=' . $request->getQuery('withParent');
            $key .= 'limit=' . $request->getQuery('limit');
            $key .= 'offset=' . $request->getQuery('offset');
            $key .= 'typeCode=' . $request->getQuery('typeCode');

            return sha1($key);
        }

        /**
         * Возвращает значение кэша для плагина
         * 
         * @param string $plugin Название плагина
         * @param \Phalcon\Http\Request $request Запрос
         * @return array|null
         */
        public function get($plugin, Request $request)
        {

            if (!$this->useCache())
                return null;

            $key = $this->getCacheKey($plugin, $request);
            return $this->cache->get($key);
        }

        /**
         * Устанавливает значение кэша для плагина
         * 
         * @param string $plugin Название плагина
         * @param \Phalcon\Http\Request $request Запрос
         * @param array $result Значение
         */
        public function set($plugin, Request $request, $result)
        {
            if (!$this->useCache())
                return;		
		
            $key = $this->getCacheKey($plugin, $request);
            return $this->cache->save($key, $result);
        }

        /*
         * Определет, используется ли кэш.
         */

        private function useCache()
        {
            return $this->config->mongocache->enabled;
        }

    }

}