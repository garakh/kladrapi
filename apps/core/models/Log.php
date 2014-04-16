<?php

namespace Kladr\Core\Models {

    use \Phalcon\Mvc\Collection;

    class Log extends Collection
    {

        public function getSource()
        {
            return "log";
        }        
        
        public function initialize()
        {
            $this->setConnectionService('mongoLog');
        }

    }

}