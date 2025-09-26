<?php

namespace Nucleus\Contracts\Database;

use stdClass;

interface QueryBuilderInterface
{
    public function table(string $table): self;

    public function select(array $columns = ['*']): self;

    public function where(string $column, string $operator, $value): self;

    public function insert(array $data): int;

    public function update(array $data): int;

    public function delete(): int;

    public function orderBy(string $column, string $direction): self;

    public function limit(int $limit): self;

    public function offset(int $offset): self;

    public function get(): stdClass|array;

    public function getQuery(): string;
}
