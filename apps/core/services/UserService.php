<?php

namespace Kladr\Core\Services {

    use \Kladr\Core\Models\Users,
        \Kladr\Core\Models\Log;

    /**
     * Kladr\Core\Services\UserService
     * 
     * Сервис для работы с пользователями
     * 
     * @author I. Garakh Primepix (http://primepix.ru/)
     */
    class UserService
    {

        /**
         * Получает пользователя по токену
         * @param type $key
         * @return \Kladr\Core\Models\Users
         */
        public function getUserByKey($key)
        {
            return Users::findById($key);
        }

        /**
         * Логируем пользователя.
         * 
         * @param \Kladr\Core\Models\Users $user Пользователь, может быть null
         * @return void
         */
        public function logUser($user)
        {
            if ($user == null)
                return;

            if (!$user->isPaid())
                return;

            $log = new Log();
            $log->date = new \MongoDate();
            $log->userId = $user->getId();
            $log->save();
        }

    }

}