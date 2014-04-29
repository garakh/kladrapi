<?php

namespace Kladr\Frontend\Controllers {

    use \Phalcon\Tag,
        \Phalcon\Mvc\View,
        \Phalcon\Mvc\Controller,
        \Kladr\Frontend\Models\Users,
        \Kladr\Frontend\Models\Requests;

    class PersonalController extends Controller
    {

        public function initialize()
        {
            Tag::setTitle('Личный кабинет');
        }

        public function indexAction()
        {
            $id = $this->session->get('user');

            if (!$id)
            {
                $this->session->remove('user');
                $this->response->redirect();
            }

            $user = Users::findById($id);

            if (!$user)
            {
                $this->session->remove('user');
                $this->response->redirect();
            }

            $this->view->setVar("user", $user);

            $requests = Requests::find(array(
                        array("key" => (string) $id),
                        "sort" => array("time" => -1),
                        "limit" => 100
            ));

            $this->view->setVar("requests", $requests);
        }

        public function getAction()
        {
            $id = $this->session->get('user');

            if (!$id)
            {
                $this->session->remove('user');
                $this->response->redirect();
            }

            $user = Users::findById($id);

            if (!$user)
            {
                $this->session->remove('user');
                $this->response->redirect();
            }

            $this->view->setVar("user", $user);

            $requests = Requests::find(array(
                        array("key" => (string) $id),
                        "sort" => array("time" => -1),
                        "limit" => 100
            ));

            $this->view->setVar("requests", $requests);
            $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        }

        public function keyreloadAction()
        {
            $this->view->disable();

            if ($this->request->isAjax())
            {
                $id = $this->session->get('user');
                if (!$id)
                    return;

                $user = Users::findById($id);
                if (!$user)
                    return;

                $key = sha1($this->keyTools->RandString(10, 20));
                $user->key = $key;

                if ($user->save())
                {
                    print $key;
                }
            }
        }

        public function domainloadAction()
        {
            $this->view->disable();

            if ($this->request->isPost() && $this->request->isAjax())
            {
                $id = $this->session->get('user');
                if (!$id)
                    return;

                $user = Users::findById($id);
                if (!$user)
                    return;

                $user->domain = $this->request->getPost('domain');

                if ($user->save())
                {
                    print $user->domain;
                }
            }
        }

        public function domainkeyreloadAction()
        {
            $this->view->disable();

            if ($this->request->isPost() && $this->request->isAjax())
            {
                $id = $this->session->get('user');
                if (!$id)
                    return;

                $user = Users::findById($id);
                if (!$user)
                    return;

                $key = sha1($this->keyTools->RandString(10, 20));
                $user->domainkey = $key;

                if ($user->save())
                {
                    print $key;
                }
            }
        }

    }

}