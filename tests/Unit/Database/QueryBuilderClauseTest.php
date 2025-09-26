<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Nucleus\Database\Connection;
use PHPUnit\Framework\TestCase;
use Nucleus\Database\QueryBuilder;

class QueryBuilderClauseTest extends TestCase
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

    public function testOrderByAscending()
    {
        $qb = new QueryBuilder($this->connection);
        $sql = $qb->table('users')
            ->select()
            ->orderBy('name', 'ASC')
            ->getQuery();

        $this->assertSame(
            "SELECT * FROM users ORDER BY name ASC",
            $sql
        );
    }

    public function testOrderByDescending()
    {
        $qb = new QueryBuilder($this->connection);
        $sql = $qb->table('users')
            ->select()
            ->orderBy('email', 'DESC')
            ->getQuery();

        $this->assertSame(
            "SELECT * FROM users ORDER BY email DESC",
            $sql
        );
    }

    public function testLimit()
    {
        $qb = new QueryBuilder($this->connection);
        $sql = $qb->table('users')
            ->select()
            ->limit(5)
            ->getQuery();

        $this->assertSame(
            "SELECT * FROM users LIMIT 5",
            $sql
        );
    }

    public function testOffset()
    {
        $qb = new QueryBuilder($this->connection);
        $sql = $qb->table('users')
            ->select()
            ->offset(10)
            ->getQuery();

        $this->assertSame(
            "SELECT * FROM users OFFSET 10",
            $sql
        );
    }

    public function testLimitWithOffset()
    {
        $qb = new QueryBuilder($this->connection);
        $sql = $qb->table('users')
            ->select()
            ->limit(5)
            ->offset(10)
            ->getQuery();

        $this->assertSame(
            "SELECT * FROM users LIMIT 5 OFFSET 10",
            $sql
        );
    }

    public function testComplexQueryBuilding()
    {
        $qb = new QueryBuilder($this->connection);
        $sql = $qb->table('users')
            ->select(['id', 'name'])
            ->where('email', '=', 'test@example.com')
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->offset(5)
            ->getQuery();

        $this->assertSame(
            "SELECT id, name FROM users WHERE email = :wemail0 ORDER BY name ASC LIMIT 10 OFFSET 5",
            $sql
        );
    }

    public function testOrderByWithData()
    {
        $qb = new QueryBuilder($this->connection);

        // Insert test data
        $qb->table('users')->insert(['name' => 'Zoe', 'email' => 'zoe@example.com']);
        $qb->table('users')->insert(['name' => 'Alice', 'email' => 'alice@example.com']);
        $qb->table('users')->insert(['name' => 'Bob', 'email' => 'bob@example.com']);

        // Test order by ASC
        $users = $qb->table('users')
            ->select()
            ->orderBy('name', 'ASC')
            ->get();

        $this->assertSame('Alice', $users[0]->name);
        $this->assertSame('Bob', $users[1]->name);
        $this->assertSame('Zoe', $users[2]->name);

        // Test order by DESC
        $users = $qb->table('users')
            ->select()
            ->orderBy('name', 'DESC')
            ->get();

        $this->assertSame('Zoe', $users[0]->name);
        $this->assertSame('Bob', $users[1]->name);
        $this->assertSame('Alice', $users[2]->name);
    }

    public function testLimitWithData()
    {
        $qb = new QueryBuilder($this->connection);

        // Insert test data
        $qb->table('users')->insert(['name' => 'User1', 'email' => 'user1@example.com']);
        $qb->table('users')->insert(['name' => 'User2', 'email' => 'user2@example.com']);
        $qb->table('users')->insert(['name' => 'User3', 'email' => 'user3@example.com']);

        $users = $qb->table('users')
            ->select()
            ->limit(2)
            ->get();

        $this->assertCount(2, $users);
    }

    public function testPaginationWithLimitAndOffset()
    {
        $qb = new QueryBuilder($this->connection);

        // Insert test data
        for ($i = 1; $i <= 10; $i++) {
            $qb->table('users')->insert([
                'name' => "User{$i}",
                'email' => "user{$i}@example.com"
            ]);
        }

        // Page 2, 3 items per page (offset 3, limit 3)
        $users = $qb->table('users')
            ->select()
            ->orderBy('id', 'ASC')
            ->limit(3)
            ->offset(3)
            ->get();

        $this->assertCount(3, $users);
        $this->assertSame('User4', $users[0]->name);
        $this->assertSame('User5', $users[1]->name);
        $this->assertSame('User6', $users[2]->name);
    }
}