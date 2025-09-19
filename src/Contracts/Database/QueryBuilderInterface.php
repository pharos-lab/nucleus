<?php

namespace Nucleus\Contracts\Database;

use stdClass;

interface QueryBuilderInterface
{
    public function table(string $table): self;

    public function select(array $columns = ['*']): self;

    public function where(string $column, string $operator, $value): self;

    public function insert(array $data);

    public function update(array $data);

    public function delete();

    public function get(): stdClass|array;

    public function getQuery(): string;
}
