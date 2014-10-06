<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
	\Phalcon\Mvc\Controller;

    class ExamplesController extends Controller
    {

	public function initialize()
	{
	    Tag::setTitle('Примеры использования Кладр в облаке и на вашем сайте. Кладр, ФИАС в облаке.');
	    if ($this->request->getScheme() == 'http')
		$this->view->setVar('host', $this->config->options->host);
	    else
		$this->view->setVar('host', str_replace('http', 'https', $this->config->options->host));

	    $this->view->setVar('description', 'Примеры использования Кладр в облаке и на вашем сайте используя jQuery');
	    $this->view->setVar('keywords', 'КЛАДР, ФИАС, скачать КЛАДР, скачать ФИАС, скачать базу КЛАДР, скачать базу ФИАС, доступ к базе КЛАДР, доступ к базе ФИАС, КЛАДР онлайн, ФИАС онлайн, структура базы КЛАДР, структура базы ФИАС, описание базы КЛАДР, описание базы ФИАС');

	    $this->view->setVar('page', 'examples');
	}

	public function indexAction()
	{
	    $examples = file_get_contents(__DIR__ . '/../../../public/jsplugin/examples/all.html');
	    list(, $examples, ) = explode('<!--embedded-->', $examples, 3);
	    $this->view->setVar('examplesHtml', $examples);
	}

    }

}