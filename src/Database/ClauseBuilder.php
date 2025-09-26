<?php

declare(strict_types=1);

namespace Nucleus\Database;

use Exception;

trait ClauseBuilder
{
   private function buildWhereClause(): string
    {
        if (!$this->where) return '';
        $clauses = array_map(fn($w) => "{$w[0]} {$w[1]} {$w[2]}", $this->where);
        return ' WHERE ' . implode(' AND ', $clauses);
    }

    public function buildOrderByClause(): string
    {
        if (!$this->orderBy) return '';

        return ' ORDER BY ' . $this->orderBy['column'] . ' ' . $this->orderBy['direction'];
    }

    public function buildLimitClause(): string
    {
        if (empty($this->limit)) return '';

        return ' LIMIT ' . $this->limit;
    }

    public function buildOffsetClause()
    {
        if (empty($this->limit)) {
            throw new Exception("You MUST have declared a 'limit' before using 'offset' clause");
        }
        
        if (empty($this->offset)) return '';

        return ' OFFSET ' . $this->offset;
    }
    
}