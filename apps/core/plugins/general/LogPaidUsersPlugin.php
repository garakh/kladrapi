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
    class LogPaidUsersPlugin extends Plugin implements IPlugin
    {

        /**
         * Сервис работы с пользователями
         * @var \Kladr\Core\Services\UserService
         */
        public $userService;

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
            $user = $this->userService->getUserByKey($userKey);
            $this->userService->logUser($user);

            $prevResult->user = $user;

            return $prevResult;
        }

    }

}