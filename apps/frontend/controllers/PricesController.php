<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\Controller;

    class PricesController extends Controller
    {

        public function initialize()
        {
            Tag::setTitle('Условия пользования Кладр в облаке.  Кладр, ФИАС в облаке.');
            $this->view->setVar('description', 'Сервис является абсолютно бесплатным с открытыми исходными кодами доступными на гитхабе.Вы можете связаться с нами для получения платной консультации об установке и настройке базы на Ваших серверах.');
            $this->view->setVar('keywords', 'КЛАДР 2013, КЛАДР, ФИАС, скачать КЛАДР, скачать ФИАС, скачать базу КЛАДР, скачать базу ФИАС, доступ к базе КЛАДР, доступ к базе ФИАС, КЛАДР онлайн, ФИАС онлайн, структура базы КЛАДР, структура базы ФИАС, описание базы КЛАДР, описание базы ФИАС');
	    
	     $this->view->setVar('page', 'prices');
        }

        public function indexAction()
        {
            
        }

    }

}