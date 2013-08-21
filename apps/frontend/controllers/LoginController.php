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
        }

        public function indexAction()
        {
            if ($this->request->isPost()) {

                $email = $this->request->getPost('email');
                $password = $this->request->getPost('password');

                $password = sha1($password);

                $user = Users::findFirst(array(
                    array(
                        'email' => $email,
                        'pass' => $password
                    )
                ));

                if(!$user){
                    $this->flash->warning('Ошибка входа: неверно введены email или пароль.');
                    return;
                }

                $this->session->set('user', $user->_id);
                $this->response->redirect("integration");
            }
        }
        
    }
    
}