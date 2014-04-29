<?php

namespace Kladr\Core\Models {

    use \Phalcon\Mvc\Collection;

    class Users extends Collection
    {

        public function getSource()
        {
            return "users";
        }

        public function isPaid()
        {
            return isset($this->isPaid) && $this->isPaid === true;
        }

        public function getId()
        {
            return $this->_id->{'$id'};
        }

        public function initialize()
        {
            $this->setConnectionService('mongoUsers');
        }

    }

}