<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\Controller;

    class ContactsController extends Controller
    {

        public function initialize()
        {
            Tag::setTitle('Обратная связь с разработчиками Кладр в облаке. Кладр, ФИАС в облаке.');
            $this->view->setVar('description', 'Мы — команда «Primepix». В ряде наших проектов пользователю необходимо заполнять формы с адресами, и специально для этих нужд мы разработали сервис «Кладр Api». С недавних пор мы решили поделиться данным сервисом с общественностью. 
Мы всегда рады конструктивному диалогу, предложениям и идеям, поэтому если у Вас появится желание с нами связаться, вот наши контактные данные:
Телефон:+7(952) 259-2014
Почта: info@kladr-api.ru
Адрес: г. Архангельск, пр. Ломоносова 81, 6 этаж, офис 614
Skype: beentech');
            $this->view->setVar('keywords', 'КЛАДР, ФИАС, скачать КЛАДР, скачать ФИАС, скачать базу КЛАДР, скачать базу ФИАС, доступ к базе КЛАДР, доступ к базе ФИАС, КЛАДР онлайн, ФИАС онлайн, структура базы КЛАДР, структура базы ФИАС, описание базы КЛАДР, описание базы ФИАС');
	    
	     $this->view->setVar('page', 'about');
        }

        public function indexAction()
        {
            
        }

        public function feedbackAction()
        {
            $this->view->disable();

            if ($this->request->isPost())
            {
                $name = $this->request->getPost('name');
                $email = $this->request->getPost('email');
                $comment = $this->request->getPost('comment');

                if (empty($name))
                    return;
                if (empty($email))
                    return;
                if (empty($comment))
                    return;

                $headers = 'From: noreply@kladr-api.ru' . "\n" .
                        'Reply-To: ' . $email . "\n" .
                        'Content-Type: text/html; charset="utf-8"';

                $subject = 'Новое сообщение в форме обратной связи';
                $subject = '=?utf-8?B?' . base64_encode($subject) . '?=';

                $message = 'Новое сообщение в форме обратной связи от ' . $name . '(' . $email . '):' . "<br/><br/>" . $comment;
                $message = wordwrap($message, 70);

                mail('garakh@primepix.ru', $subject, $message, $headers);

                print 'y';
            }
        }

    }

}