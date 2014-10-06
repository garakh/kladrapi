<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\Controller,
        \Kladr\Frontend\Models\Users;

    class IndexController extends Controller
    {

        public function initialize()
        {
            Tag::setTitle(' Кладр, ФИАС в облаке.');
            $this->view->setVar('description', 'Обработанные базы адресов «КЛАДР» и «ФИАС» с удобным и простым доступом. Возможность скачать базы КЛАДР и ФИАС');
            $this->view->setVar('keywords', 'КЛАДР 2014, КЛАДР, ФИАС, скачать КЛАДР, скачать ФИАС, скачать базу КЛАДР, скачать базу ФИАС, доступ к базе КЛАДР, доступ к базе ФИАС, КЛАДР онлайн, ФИАС онлайн, структура базы КЛАДР, структура базы ФИАС, описание базы КЛАДР, описание базы ФИАС');
	    
	    $this->view->setVar('page', 'index');
	    $this->view->setVar('hideSmallHeader', true);

        }

        public function indexAction()
        {
            $id = $this->session->get('user');
            $user = $id ? Users::findById($id) : null;
            $this->view->setVar("authorized", $user ? true : false);
        }

        public function show404Action()
        {
            $this->response->setStatusCode('404', 'Not found :(');
            echo "<h1>Not found :(</h1>";
        }

    }

}