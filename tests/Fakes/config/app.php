<?php

use Tests\Fakes\FakeNoResgisterProvider;
use Tests\Fakes\FakeUserProvider;

return [
    // Chemin du fichier de routes
    'routes_path' => __DIR__ . '/../routes/web.php',

    'providers' => [
        FakeUserProvider::class,
    ],

    // Plus tard : on pourra ajouter d'autres options
    'env' => 'dev',
    'timezone' => 'UTC',
];
