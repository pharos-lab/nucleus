<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nucleus\Application;


$app = new Application(dirname(__DIR__));

$app->run();