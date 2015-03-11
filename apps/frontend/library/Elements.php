<?php

namespace Kladr\Frontend\Library {

    use \Phalcon\Mvc\User\Component,
    \Kladr\Frontend\Models\Users;

    class Elements extends Component
    {

        public function isAuthed()
        {
            $id = $this->session->get('user');
            $user = $id ? Users::findById($id) : null;
	    
	    return $user ? true : false;
        }

        public function getTopMenu()
        {
            $result = '<ul>';
            foreach ($this->config->menu as $key => $name) {
                $result .= '<li><a ' . (preg_match('/^\/' . $key . '/', $this->request->getServer('REQUEST_URI')) ? 'class="selected" ' : '') . 'href="/' . $key . '/">' . $name . '</a></li>';
            }
            $result .= '</ul>';
            return $result;
        }

        public function getBottomMenu()
        {
            $result = '<ul>';
            foreach ($this->config->menu as $key => $name) {
                $result .= '<li><a href="/' . $key . '/">' . $name . '</a></li>';
            }
            $result .= '</ul>';
            return $result;
        }

    }

}