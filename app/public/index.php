<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nucleus\Core\Application;

$app = new Application(dirname(__DIR__));

$app->run();