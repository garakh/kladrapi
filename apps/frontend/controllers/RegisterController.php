<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\Controller,
        \Kladr\Frontend\Models\Users;

    class RegisterController extends Controller
    {

        public function indexAction()
        {
            if ($this->session->get('user'))
            {
                $this->response->redirect('/');
            }

            if ($this->request->isPost())
            {

                if ($this->request->getPost('accept') != 'y')
                {
                    $this->flash->warning('Вы должны согласиться с условиями использования сервиса');
                    return;
                }

                $email = $this->request->getPost('email');
                $user = Users::findFirst(array(
                            array(
                                'email' => $email,
                            )
                ));

                if ($user)
                {
                    $this->flash->warning('Пользователь с таким email уже зарегистрирован');
                    return;
                }

                $user = new Users();
                $user->email = $email;

                $password = $this->keyTools->RandString(8, 12);
                $user->pass = sha1($password);

                $user->key = sha1($this->keyTools->RandString(10, 20));

                if ($user->save())
                {
                    $headers = 'From: noreply@kladr-api.ru' . "\n" .
                            'Reply-To: noreply@kladr-api.ru' . "\n" .
                            'Content-Type: text/html; charset="utf-8"';

                    $subject = 'Вы зарегистрированы на сайте КЛАДР API';
                    $subject = '=?utf-8?B?' . base64_encode($subject) . '?=';

                    $message = 'Вы зарегистрированы на сайте КЛАДР API' . "<br/><br/>" .
                            'Ваш пароль: <strong>' . $password . '</strong>' . "<br/>" .
                            'Для входа на сайт пройдите по <a href="http://kladr-api.ru/login/">ссылке</a>';

                    $message = wordwrap($message, 70);

                    mail($email, $subject, $message, $headers);
                }

                $this->session->set('user', $user->_id);
                $this->response->redirect("integration/");
            }
        }

    }

}