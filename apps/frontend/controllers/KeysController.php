<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\Controller,
        \Kladr\Frontend\Models\Users;

    class KeysController extends Controller
    {

        public function initialize()
        {
            Tag::setTitle('Страница входа / регистрации');
            $this->view->setVar('description', 'Описание интеграции с Кладр в облаке. Модули для jQuery, PHP, .Net, 1C-Bitrix');
            $this->view->setVar('keywords', 'КЛАДР, ФИАС, скачать КЛАДР, скачать ФИАС, скачать базу КЛАДР, скачать базу ФИАС, доступ к базе КЛАДР, доступ к базе ФИАС, КЛАДР онлайн, ФИАС онлайн, структура базы КЛАДР, структура базы ФИАС, описание базы КЛАДР, описание базы ФИАС, jQuery, php, net, 1c-bitrix');
	    
	    $this->view->setVar('page', 'login');
        }

        public function indexAction()
        {
            $id = $this->session->get('user');

            if (!$id)
            {
                $this->session->remove('user');
                $this->response->redirect('register');
            }

            $user = Users::findById($id);

            if (!$user)
            {
                $this->session->remove('user');
                $this->response->redirect('register');
            }

            $this->view->setVar("user", $user);

        }

    }

}