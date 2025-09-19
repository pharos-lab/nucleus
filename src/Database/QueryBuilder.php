<?php

declare(strict_types=1);

namespace Nucleus\Database;

use Nucleus\Contracts\Database\QueryBuilderInterface;
use PDO;
use stdClass;

class QueryBuilder implements QueryBuilderInterface
{
    private PDO $pdo;
    private string $table;
    private array $columns = ['*'];
    private array $where = [];
    private array $bindings = [];
    private string $action = '';
    private array $data = [];

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns = ['*']): self
    {
        $this->action = 'SELECT';
        $this->columns = $columns;
        return $this;
    }

    public function where(string $column, string $operator, $value): self
    {
        $placeholder = ':w' . $column . count($this->bindings);
        $this->where[] = [$column, $operator, $placeholder];
        $this->bindings[$placeholder] = $value;

        return $this;
    }

    public function insert(array $data)
    {
        $this->action = 'INSERT';
        $this->data = $data;
        return $this->send();
    }

    public function update(array $data)
    {
        $this->action = 'UPDATE';
        $this->data = $data;
        return $this->send();
    }

    public function delete()
    {
        $this->action = 'DELETE';
        return $this->send();
    }

    public function get(): stdClass|array
    {
        $this->action = 'SELECT';
        return $this->send();
    }

    public function getQuery(): string
    {
        return match($this->action) {
            'SELECT' => $this->buildSelectQuery(),
            'INSERT' => $this->buildInsertQuery(),
            'UPDATE' => $this->buildUpdateQuery(),
            'DELETE' => $this->buildDeleteQuery(),
            default => throw new \Exception("Unknown action {$this->action}")
        };
    }

    // --- Private builders ---

    private function buildWhereClause(): string
    {
        if (!$this->where) return '';
        $clauses = array_map(fn($w) => "{$w[0]} {$w[1]} {$w[2]}", $this->where);
        return ' WHERE ' . implode(' AND ', $clauses);
    }

    private function buildSelectQuery(): string
    {
        return 'SELECT ' . implode(', ', $this->columns) .
               ' FROM ' . $this->table .
               $this->buildWhereClause();
    }

    private function buildInsertQuery(): string
    {
        $fields = implode(', ', array_keys($this->data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($this->data)));
        $this->bindings = array_combine(
            array_map(fn($k) => ':' . $k, array_keys($this->data)),
            $this->data
        );
        return "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
    }

    private function buildUpdateQuery(): string
    {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($this->data)));
        $this->bindings = array_merge(
            array_combine(array_map(fn($k) => ':' . $k, array_keys($this->data)), $this->data),
            $this->bindings
        );
        return "UPDATE {$this->table} SET {$set}" . $this->buildWhereClause();
    }

    private function buildDeleteQuery(): string
    {
        return "DELETE FROM {$this->table}" . $this->buildWhereClause();
    }

    // --- Execution ---

    private function send(): stdClass|array|int|false
    {
        $sql = $this->getQuery();
        $stmt = $this->pdo->prepare($sql);

        if (!$stmt->execute($this->bindings)) {
            return false;
        }

        return match ($this->action) {
            'SELECT' => $stmt->fetchAll(PDO::FETCH_OBJ),
            'INSERT' => (int)$this->pdo->lastInsertId(),
            'UPDATE', 'DELETE' => $stmt->rowCount(),
            default => false,
        };
    }
}