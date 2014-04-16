<?php

namespace Kladr\Frontend\Plugins {

    use \Phalcon\Mvc\User\Plugin;

    /**
     * Плагин для работы с ключами
     */
    class KeyTools extends Plugin
    {

        /**
         * Функция для генерации случайной строки
         * @param int $lenFrom Длина строки от
         * @param int $lenTo Длина строки до
         * @param string $base База для генерации строки
         * @return string
         */
        public function RandString($lenFrom, $lenTo = 0, $base = null)
        {
            $base = '1234567890' .
                    'qwertyuioplkjhgfdsazxcvbnm' .
                    'QWERTYUIOPLKJHGFDSAZXCVBNM';
            $baseLast = strlen($base) - 1;

            $password = '';
            $passwordLength = $lenTo ? rand($lenFrom, $lenTo) : $lenFrom;

            for ($i = 0; $i < $passwordLength; $i++)
            {
                $password .= $base[rand(0, $baseLast)];
            }

            return $password;
        }

    }

}
