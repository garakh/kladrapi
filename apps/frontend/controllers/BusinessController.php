<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
	\Phalcon\Mvc\Controller;

    class BusinessController extends Controller
    {

	public function initialize()
	{
	    Tag::setTitle('Кладр, ФИАС в облаке для бизнеса');
	    $this->view->setVar('description', 'Описание интеграции с Кладр в облаке. Модули для jQuery, PHP, .Net, 1C-Bitrix');
	    $this->view->setVar('keywords', 'КЛАДР, ФИАС, скачать КЛАДР, скачать ФИАС, скачать базу КЛАДР, скачать базу ФИАС, доступ к базе КЛАДР, доступ к базе ФИАС, КЛАДР онлайн, ФИАС онлайн, структура базы КЛАДР, структура базы ФИАС, описание базы КЛАДР, описание базы ФИАС, jQuery, php, net, 1c-bitrix');

	    $this->view->setVar('page', 'business');
	}

	public function indexAction()
	{
	}

	public function successAction()
	{
	    
	}

	public function feedbackAction()
	{
	    $email = $this->request->get('email');
	    $plan = $this->request->get('plan');
	    $company = $this->request->get('company');

	    $adminEmail = $this->config->options->email;

	    mail($adminEmail, 'Feedback', sprintf("From: %s, Plan: %s, Info:%s", $email, $plan, $company));
	    $this->view->disable();
	}

    }

}