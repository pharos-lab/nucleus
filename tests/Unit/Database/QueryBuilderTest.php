<?php

declare(strict_types=1);

use Nucleus\Database\Connection;
use PHPUnit\Framework\TestCase;
use Nucleus\Database\QueryBuilder;
use PDO;

class QueryBuilderTest extends TestCase
{
    private connection $connection;

    protected function setUp(): void
    {
        // SQLite en mémoire
        $this->connection = new Connection('sqlite::memory:');

        // Création d’une table fictive
        $this->connection->getPdo()->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL
            )
        ");
    }

    public function testSelectQueryBuilding()
    {
        $qb = new QueryBuilder($this->connection);
        $sql = $qb->table('users')->select(['id', 'name'])->getQuery();

        $this->assertSame(
            "SELECT id, name FROM users",
            $sql
        );
    }

    public function testWhereClauseBuilding()
    {
        $qb = new QueryBuilder($this->connection);
        $sql = $qb->table('users')
            ->select()
            ->where('id', '=', 1)
            ->getQuery();

        $this->assertSame(
            "SELECT * FROM users WHERE id = :wid0",
            $sql
        );
    }

    public function testInsertAndFetch()
    {
        $qb = new QueryBuilder($this->connection);
        $id = $qb->table('users')->insert([
            'name' => 'Alice',
            'email' => 'alice@example.com'
        ]);

        $this->assertSame(1, $id);

        $users = $qb->table('users')->select()->get();
        $this->assertCount(1, $users);
        $this->assertSame('Alice', $users[0]->name);
    }

    public function testUpdate()
    {
        $qb = new QueryBuilder($this->connection);

        // Insert initial
        $qb->table('users')->insert([
            'name' => 'Bob',
            'email' => 'bob@example.com'
        ]);

        // Update
        $affected = $qb->table('users')
            ->where('name', '=', 'Bob')
            ->update(['email' => 'bobby@example.com']);

        $this->assertSame(1, $affected);

        // Vérifie
        $users = $qb->table('users')->select()->get();
        $this->assertSame('bobby@example.com', $users[0]->email);
    }

    public function testDelete()
    {
        $qb = new QueryBuilder($this->connection);

        // Insert
        $qb->table('users')->insert([
            'name' => 'Charlie',
            'email' => 'charlie@example.com'
        ]);

        // Delete
        $deleted = $qb->table('users')
            ->where('name', '=', 'Charlie')
            ->delete();

        $this->assertSame(1, $deleted);

        // Vérifie
        $users = $qb->table('users')->select()->get();
        $this->assertCount(0, $users);
    }
}