<?php

namespace Kladr\Core\Controllers {

    use \Phalcon\Mvc\Controller;

    /**
     * Kladr\Core\Controllers\ApiController 
     * 
     * Контроллер api сервиса
     * 
     * @author A. Yakovlev. Primepix (http://primepix.ru/)
     */
    class ApiController extends Controller {

        public function indexAction() {
            $api = $this->di->get('api');
            $this->response = $api->process($this->request);
            $this->response->send();
            
            $api->log($this->request);
        }

    }

}