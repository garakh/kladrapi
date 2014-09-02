<?php

namespace Kladr\Core\Plugins\Base {

    /**
     * Kladr\Core\Plugins\Base\PluginResult
     *
     * Класс результата работы плагина
     *
     * @property bool $error Возникла ли ошибка в ходе работы плагина
     * @property string $errorMessage Текст ошибки
     * @property string $errorCode Код ошибки
     * @property bool $terminate Был выполнен завершающий плагин, дальнейшая обработка не требуется
     * @property array $searchContext Параметры запроса
     * @property array $result Результат
     * @property string $fileToSend Файл, который надо отправить
     * @property \Kladr\Core\Models\Users $user Пользователь
     *
     * @author Primepix (http://primepix.ru/)
     */
    class PluginResult
    {

        private $_error;
        private $_errorMessage;
        private $_errorCode;
        private $_terminate;
        private $_searchContext;
        private $_result;
        private $_user;
        private $_fileToSend;
        private $_disablePlugins;

        public function __construct()
        {
            $this->_error = false;
            $this->_errorMessage = '';
            $this->_errorCode = '200';
            $this->_terminate = false;
            $this->_searchContext = array();
            $this->_result = array();
            $this->_user = null;
            $this->_fileToSend = null;
            $this->_disablePlugins = array();
        }

        public function disablePlugin($name)
        {
            $this->_disablePlugins[] = $name;
        }

        public function isPluginDisabled($name)
        {
            return in_array($name, $this->_disablePlugins);
        }

        public function __get($name)
        {
            switch ($name) {
                case 'error':
                    return $this->_error;
                case 'errorMessage':
                    return $this->_errorMessage;
                case 'errorCode':
                    return $this->_errorCode;
                case 'terminate':
                    return $this->_terminate;
                case 'searchContext':
                    return $this->_searchContext;
                case 'result':
                    return $this->_result;
                case 'user':
                    return $this->_user;
                case 'fileToSend':
                    return $this->_fileToSend;
                default:
                    return null;
            }
        }

        public function __set($name, $value)
        {
            switch ($name) {
                case 'error':
                    $this->_error = $value;
                    break;
                case 'errorMessage':
                    $this->_errorMessage = $value;
                    break;
                case 'errorCode':
                    $this->_errorCode = $value;
                    break;
                case 'terminate':
                    $this->_terminate = $value;
                    break;
                case 'searchContext':
                    $this->_searchContext = $value;
                    break;
                case 'result':
                    $this->_result = $value;
                    break;
                case 'user':
                    $this->_user = $value;
                    break;
                case 'fileToSend':
                    $this->_fileToSend = $value;
                    break;
            }
        }

    }

}