<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\Controller,
        \Kladr\Frontend\Models\Users;

    class LoginController extends Controller
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
            if ($this->request->isPost())
            {

                $email = $this->request->getPost('email');
                $password = $this->request->getPost('password');

                $password = sha1($password);

                $user = Users::findFirst(array(
                            array(
                                'email' => $email,
                                'pass' => $password
                            )
                ));

                if (!$user)
                {
                    $this->flash->warning('Ошибка входа: неверно введены email или пароль.');
                    return;
                }

                $this->session->set('user', $user->_id);
                $this->response->redirect("integration");
            }
        }

    }

}