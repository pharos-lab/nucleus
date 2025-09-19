<?php

declare(strict_types=1);

namespace Nucleus\Database;

use PDO;

class Connection
{
    protected PDO $pdo;

    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        array $options = []
    ) {
        $defaults = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $this->pdo = new PDO($dsn, $username, $password, $options + $defaults);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}