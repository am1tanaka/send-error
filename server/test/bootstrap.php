<?php
define('PROJECT_DIR', dirname(__FILE__) . '/..');
require PROJECT_DIR . '/vendor/autoload.php';

function createContainer() {
    $container = new \Pimple();
    \Slim\Environment::mock();

    return $container;
}
 ?>
