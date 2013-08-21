<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Mvc\Controller;

    class LogoutController extends Controller
    {

        public function indexAction()
        {
            $this->session->remove('user');
            $this->response->redirect();
        }

    }
    
}