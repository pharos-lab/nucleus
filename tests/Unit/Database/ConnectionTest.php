<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use Nucleus\Database\Connection;

class ConnectionTest extends TestCase
{
    protected function getSqliteConnection(): Connection
    {
        return new Connection('sqlite::memory:');
    }

    public function test_can_connect_with_sqlite_memory()
    {
        $conn = $this->getSqliteConnection();

        $this->assertInstanceOf(Connection::class, $conn);
        $this->assertNotNull($conn->getPdo());
    }
}