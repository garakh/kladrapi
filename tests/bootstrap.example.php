<?php
define("URL", "http://example.com/api.php");
spl_autoload_register(function ($class) {    
    include  $class . '.php';
});

