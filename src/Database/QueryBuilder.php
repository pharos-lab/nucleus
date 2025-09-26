<?php

declare(strict_types=1);

namespace Nucleus\Database;

use Exception;
use Nucleus\Contracts\Database\QueryBuilderInterface;
use PDO;
use stdClass;

class QueryBuilder implements QueryBuilderInterface
{
    use ActionBuilder, ClauseBuilder;

    private PDO $pdo;
    private string $table;
    private array $columns = ['*'];
    private array $where = [];
    private array $bindings = [];
    private string $action = '';
    private array $data = [];
    private array $orderBy = [];
    private int $limit;
    private int $offset;

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

    public function insert(array $data): int
    {
        $this->action = 'INSERT';
        $this->data = $data;
        return $this->send();
    }

    public function update(array $data): int
    {
        $this->action = 'UPDATE';
        $this->data = $data;
        return $this->send();
    }

    public function delete(): int
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

     public function where(string $column, string $operator, $value): self
    {
        $placeholder = ':w' . $column . count($this->bindings);
        $this->where[] = [$column, $operator, $placeholder];
        $this->bindings[ltrim($placeholder, ':')] = $value;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $directionUpper = strtoupper($direction);

        if (!in_array($directionUpper, ['ASC', 'DESC'])) {
            throw new Exception("The 'direction' argument must be 'ASC' or 'DESC', $direction given!");
        }
        $this->orderBy = ["column" => $column, "direction" => $directionUpper];

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    // --- Execution ---

    private function send(): stdClass|array|int|false
    {
        $sql = $this->getQuery();

        $stmt = $this->pdo->prepare($sql);

        if (!$stmt->execute($this->bindings)) {
            return false;
        }

        $this->bindings = [];
        $this->where = [];
        $this->orderBy= [];

        return match ($this->action) {
            'SELECT' => $stmt->fetchAll(PDO::FETCH_OBJ),
            'INSERT' => (int)$this->pdo->lastInsertId(),
            'UPDATE', 'DELETE' => $stmt->rowCount(),
            default => false,
        };
    }
}