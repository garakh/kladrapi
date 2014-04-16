<?php

namespace Kladr\Frontend\Models {

    use \Phalcon\Mvc\Collection;

    class Users extends Collection
    {

        public function getSource()
        {
            return "users";
        }

    }

}