<?php

use App\Providers\AppProvider;

return [
    // Chemin du fichier de routes
    'routes_path' => __DIR__ . '/../routes/web.php',

    'providers' => [
        // Les providers utilisateurs seront listés ici
        AppProvider::class,
    ],

    // Plus tard : on pourra ajouter d'autres options
    'env' => 'dev',
    'timezone' => 'UTC',
];
