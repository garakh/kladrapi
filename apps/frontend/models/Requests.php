<?php

namespace Kladr\Frontend\Models {

    use \Phalcon\Mvc\Collection;

    class Requests extends Collection
    {

        public function getSource()
        {
            return "requests";
        }

    }

}