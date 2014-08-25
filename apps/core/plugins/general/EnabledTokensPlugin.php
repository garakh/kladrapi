<?php

namespace Kladr\Core\Plugins\General {

    use \Phalcon\Http\Request,
        \Phalcon\Mvc\User\Plugin,
        \Kladr\Core\Plugins\Base\IPlugin,
        \Kladr\Core\Plugins\Base\PluginResult,
        \Kladr\Core\Plugins\Tools\Tools,
        \Kladr\Core\Models\Regions,
        \Kladr\Core\Models\Districts,
        \Kladr\Core\Models\Cities,
        \Kladr\Core\Models\Streets,
        \Kladr\Core\Models\Buildings;

    /**
     * Kladr\Core\Plugins\General\LogPaidUsersPlugin
     * 
     * Логирует активность платных пользователей
     * 
     * @author I. Garakh Primepix (http://primepix.ru/)
     */
    class EnabledTokensPlugin extends Plugin implements IPlugin
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

            $userKey = $request->getQuery('token');
            $tokens = array();
            include __DIR__.'/../../config/enabled_tokens.php';
            if(trim($userKey) == '' || !in_array($userKey, $tokens))
            {
                $prevResult->error = true;
                $prevResult->errorCode = 403;
                $prevResult->errorMessage = 'Неверный token';

                return $prevResult;
            }

            return $prevResult;
        }

    }

}