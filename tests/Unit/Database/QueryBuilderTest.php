<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Nucleus\Database\Connection;
use Nucleus\Database\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    protected Connection $conn;

    protected function setUp(): void
    {
        $this->conn = new Connection('sqlite::memory:');
        $this->conn->getPdo()->exec(
            'CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, age INTEGER)'
        );
    }

    public function test_insert_inserts_row()
    {
        $qb = new QueryBuilder($this->conn);
        $qb->table('users')->insert([
            'name' => 'Alice',
            'age' => 30,
        ]);

        $rows = $this->conn->getPdo()
            ->query('SELECT * FROM users')
            ->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $rows);
        $this->assertEquals('Alice', $rows[0]['name']);
    }

    public function test_select_returns_matching_rows()
    {
        $this->conn->getPdo()->exec("INSERT INTO users (name, age) VALUES ('Bob', 25)");

        $qb = new QueryBuilder($this->conn);
        $rows = $qb->table('users')
            ->select(['id', 'name'])
            ->where('age', '>=', 20)
            ->get();

        $this->assertCount(1, $rows);
        $this->assertEquals('Bob', $rows[0]['name']);
    }

    public function test_update_updates_rows()
    {
        $this->conn->getPdo()->exec("INSERT INTO users (name, age) VALUES ('Charlie', 40)");

        $qb = new QueryBuilder($this->conn);
        $qb->table('users')
            ->where('name', '=', 'Charlie')
            ->update(['age' => 41]);

        $rows = $this->conn->getPdo()
            ->query("SELECT * FROM users WHERE name = 'Charlie'")
            ->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals(41, $rows[0]['age']);
    }

    public function test_delete_removes_rows()
    {
        $this->conn->getPdo()->exec("INSERT INTO users (name, age) VALUES ('Dave', 50)");

        $qb = new QueryBuilder($this->conn);
        $qb->table('users')
            ->where('name', '=', 'Dave')
            ->delete();

        $rows = $this->conn->getPdo()
            ->query("SELECT * FROM users")
            ->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(0, $rows);
    }
}