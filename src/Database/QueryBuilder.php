<?php

declare(strict_types=1);

namespace Nucleus\Database;

use PDO;

class QueryBuilder
{
    protected Connection $connection;
    protected string $table = '';
    protected array $columns = ['*'];
    protected array $wheres = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns = ['*']): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = [$column, $operator, $value];
        return $this;
    }

    public function get(): array
    {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        $bindings = [];
        if ($this->wheres) {
            $conditions = [];
            foreach ($this->wheres as $i => [$column, $operator, $value]) {
                $param = ":w{$i}";
                $conditions[] = "$column $operator $param";
                $bindings[$param] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->connection->getPdo()->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($data)));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->connection->getPdo()->prepare($sql);

        return $stmt->execute($data);
    }

    public function update(array $data): bool
    {
        $setClauses = [];
        $bindings = [];
        foreach ($data as $column => $value) {
            $param = ":set_$column";
            $setClauses[] = "$column = $param";
            $bindings[$param] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);

        if ($this->wheres) {
            $conditions = [];
            foreach ($this->wheres as $i => [$column, $operator, $value]) {
                $param = ":w{$i}";
                $conditions[] = "$column $operator $param";
                $bindings[$param] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->connection->getPdo()->prepare($sql);
        return $stmt->execute($bindings);
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";
        $bindings = [];

        if ($this->wheres) {
            $conditions = [];
            foreach ($this->wheres as $i => [$column, $operator, $value]) {
                $param = ":w{$i}";
                $conditions[] = "$column $operator $param";
                $bindings[$param] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->connection->getPdo()->prepare($sql);
        return $stmt->execute($bindings);
    }
}