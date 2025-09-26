<?php

declare(strict_types=1);

namespace Nucleus\Database;

trait ActionBuilder
{
    
    private function buildSelectQuery(): string
    {
        return 'SELECT ' . implode(', ', $this->columns) .
               ' FROM ' . $this->table .
               $this->buildWhereClause() .
               $this->buildOrderByClause() .
               $this->buildLimitClause() . 
               $this->buildOffsetClause();
    }

    private function buildInsertQuery(): string
    {
        $fields = implode(', ', array_keys($this->data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($this->data)));
        $this->bindings = $this->data;

        return "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
    }

    private function buildUpdateQuery(): string
    {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($this->data)));
        $this->bindings = array_merge(
            $this->data,
            $this->bindings
        );
        return "UPDATE {$this->table} SET {$set}" . $this->buildWhereClause();
    }

    private function buildDeleteQuery(): string
    {
        return "DELETE FROM {$this->table}" . $this->buildWhereClause();
    }
}