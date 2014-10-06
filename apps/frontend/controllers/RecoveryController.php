<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\Controller,
        \Kladr\Frontend\Models\Users;

    class RecoveryController extends Controller
    {

        public function initialize()
        {
            Tag::setTitle('Восстановление пароля');
	    $this->view->setVar('description', 'Описание интеграции с Кладр в облаке. Модули для jQuery, PHP, .Net, 1C-Bitrix');
	    $this->view->setVar('keywords', 'КЛАДР, ФИАС, скачать КЛАДР, скачать ФИАС, скачать базу КЛАДР, скачать базу ФИАС, доступ к базе КЛАДР, доступ к базе ФИАС, КЛАДР онлайн, ФИАС онлайн, структура базы КЛАДР, структура базы ФИАС, описание базы КЛАДР, описание базы ФИАС, jQuery, php, net, 1c-bitrix');

	    $this->view->setVar('page', 'login');	    
        }

        public function indexAction()
        {
            $this->view->setVar("recovered", false);
            if ($this->request->isPost())
            {
                $email = $this->request->getPost('email');
                $user = Users::findFirst(array(
                            array(
                                'email' => $email,
                            )
                ));

                if (!$user)
                {
                    $this->flash->warning('Пользователь с таким email не зарегистрирован');
                    return;
                }

                $password = $this->keyTools->RandString(8, 12);
                $user->pass = sha1($password);

                if ($user->save())
                {
                    $headers = 'From: noreply@kladr-api.ru' . "\n" .
                            'Reply-To: noreply@kladr-api.ru' . "\n" .
                            'Content-Type: text/html; charset="utf-8"';

                    $subject = 'Восстановление пароля на сайте КЛАДР в облаке';
                    $subject = '=?utf-8?B?' . base64_encode($subject) . '?=';

                    $message = 'Ваш новый пароль на сайте КЛАДР в облаке: <strong>' . $password . '</strong>' . "<br/>" .
                            'Для входа на сайт пройдите по <a href="http://kladr-api.ru/login/">ссылке</a>';

                    $message = wordwrap($message, 70);

                    mail($email, $subject, $message, $headers);
                }
                else
                {
                    $this->flash->warning('Произошла ошибка при сбросе пароля');
                    return;
                }

                $this->view->setVar("recovered", true);
            }
        }

        public function changeAction()
        {
            $this->view->disable();

            if ($this->request->isPost())
            {

                $id = $this->session->get('user');

                if (!$id)
                {
                    $this->session->remove('user');
                    print 'Для смены пароля нужно авторизоваться';
                    return;
                }

                $user = Users::findById($id);

                $old = $this->request->getPost('old');
                $new = $this->request->getPost('new');
                $repeat = $this->request->getPost('repeat');

                if (empty($old))
                {
                    print('Введите старый пароль');
                    return;
                }

                $old = sha1($old);
                if ($old != $user->pass)
                {
                    print('Неверно введён старый пароль');
                    return;
                }

                if (empty($new))
                {
                    print('Введите новый пароль');
                    return;
                }

                if (empty($repeat))
                {
                    print('Повторите новый пароль');
                    return;
                }

                if ($new != $repeat)
                {
                    print('Неверно введён повтор нового пароля');
                    return;
                }

                $user->pass = sha1($new);
                if ($user->save())
                {
                    print('y');
                }
                else
                {
                    print('Произошла ошибка при сохранении нового пароля. Попробуйте ещё раз.');
                }
            }
        }

    }

}