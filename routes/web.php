<?php

echo 'web routes';

$router->get('/', function () {
    return 'Hello world from Nucleus 🚀';
});

$router->get('/about', function () {
    return 'About page';
});