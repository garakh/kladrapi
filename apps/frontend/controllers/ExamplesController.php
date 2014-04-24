<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\Controller;

    class ExamplesController extends Controller
    {

        public function initialize()
        {
            Tag::setTitle('Примеры использования Кладр в облаке и на вашем сайте');
            $this->view->setVar('host', $this->config->options->host);
            $this->view->setVar('description', 'Примеры использования Кладр в облаке и на вашем сайте используя jQuery');
            $this->view->setVar('keywords', 'КЛАДР, ФИАС, скачать КЛАДР, скачать ФИАС, скачать базу КЛАДР, скачать базу ФИАС, доступ к базе КЛАДР, доступ к базе ФИАС, КЛАДР онлайн, ФИАС онлайн, структура базы КЛАДР, структура базы ФИАС, описание базы КЛАДР, описание базы ФИАС');
        }

        public function indexAction()
        {
            
        }

    }

}